<?php
declare(strict_types=1);

/**
 * Pruebas de integración contra PostgreSQL real.
 *
 * ATENCIÓN: ejecutar SOLO en una base de datos exclusiva de pruebas.
 * El script crea registros con prefijo QA y elimina los suyos al terminar.
 *
 * PowerShell:
 *   $env:PILATOS_TEST='1'
 *   $env:DB_NAME='recambios_pilatos_test'
 *   php tests/integracion.php
 *
 * Esta prueba solo se podrá completar al resolver EJ01–EJ12.
 */
if (getenv('PILATOS_TEST') !== '1') {
    fwrite(STDERR, "Establece PILATOS_TEST=1 y DB_NAME con una base de pruebas.\n");
    exit(2);
}

// Cargamos clases sin iniciar una sesión HTTP.
$modulos = [
    'helpers', 'validation', 'Database', 'ProductoRepository',
    'FichaRepository', 'PedidoService', 'InformeRepository', 'ImportadorCsv',
];

foreach ($modulos as $f) {
    require dirname(__DIR__) . "/app/$f.php";
}

/** Registrar una comprobación superada o detener las pruebas si falla. */
function ok(bool $v, string $m): void
{
    if (!$v) {
        throw new RuntimeException($m);
    }

    echo "OK: $m\n";
}

$pdo = bd();
$marca = 'QA' . bin2hex(random_bytes(5));
$productos = [];
$pedidos = [];
$cliente = null;
$cat = null;
$tmp = null;

try {
    // 1. Crear una categoría y un cliente exclusivos de esta ejecución.
    $cat = (new FichaRepository($pdo, 'categorias'))->guardar(null, [
        'nombre' => $marca,
        'descripcion' => 'Prueba',
    ]);

    $cliente = (new FichaRepository($pdo, 'clientes'))->guardar(null, [
        'nombre' => $marca,
        'apellidos' => 'Prueba',
        'email' => "$marca@example.com",
        'telefono' => '',
    ]);

    // 2. Insertar dos productos y comprobar el precio exacto recuperado.
    $repo = new ProductoRepository($pdo);

    foreach ([['A', '10.10', 5], ['B', '2.05', 1]] as [$ref, $precio, $stock]) {
        $productos[] = $repo->guardar(null, validarProducto([
            'referencia' => $marca . $ref,
            'nombre' => $marca . $ref,
            'precio' => $precio,
            'stock' => (string) $stock,
            'categoria_id' => (string) $cat,
            'activo' => '1',
        ]));
    }

    ok($repo->buscar($productos[0])['precio'] === '10.10', 'crear y recuperar precio exacto');

    // 3. Comprobar filtros y paginación.
    $f = filtrosProductos([
        'q' => $marca,
        'categoria' => (string) $cat,
        'orden' => 'precio',
        'dir' => 'desc',
    ]);

    ok(
        $repo->listar($f, 1)['total'] === 2
            && $repo->listar($f, 1)['paginas'] === 2,
        'filtros y paginación'
    );

    // La entrada de aspecto malicioso debe tratarse como texto, no como SQL.
    ok(
        $repo->listar(filtrosProductos(['q' => "' OR 1=1 --"]))['total'] === 0,
        'entrada SQL tratada como texto'
    );

    // 4. PostgreSQL debe rechazar una referencia duplicada (UNIQUE, 23505).
    try {
        $repo->guardar(null, validarProducto([
            'referencia' => $marca . 'A',
            'nombre' => 'duplicado',
            'precio' => '1',
            'stock' => '1',
            'categoria_id' => (string) $cat,
            'activo' => '1',
        ]));
        throw new RuntimeException('No detectó UNIQUE');
    } catch (PDOException $e) {
        ok($e->getCode() === '23505', 'referencia duplicada');
    }

    // 5. Pedido correcto: 2 × 10.10 + 1 × 2.05 = 22.25 €.
    $servicio = new PedidoService($pdo);
    $pedidos[] = $servicio->crear($cliente, [
        ['producto_id' => $productos[0], 'cantidad' => 2],
        ['producto_id' => $productos[1], 'cantidad' => 1],
    ]);

    $id = $pedidos[0];
    ok($servicio->detalle($id)['total'] === '22.25', 'total de pedido exacto');
    ok((int) $repo->buscar($productos[0])['stock'] === 3, 'descuento de stock');

    // 6. Fallo intermedio: rollback de cabecera y de stock.
    $antes = (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();

    try {
        $servicio->crear($cliente, [
            ['producto_id' => $productos[0], 'cantidad' => 1],
            ['producto_id' => $productos[1], 'cantidad' => 2],
        ]);
        throw new RuntimeException('No detectó stock');
    } catch (ErrorValidacion) {
        ok(
            (int) $repo->buscar($productos[0])['stock'] === 3
                && (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn() === $antes,
            'rollback de stock y cabecera tras fallo intermedio'
        );
    }

    // 7. Cancelar repone stock; una segunda cancelación debe rechazarse.
    $servicio->cambiarEstado($id, 'cancelado');
    ok((int) $repo->buscar($productos[0])['stock'] === 5, 'cancelación repone stock');

    try {
        $servicio->cambiarEstado($id, 'cancelado');
        throw new RuntimeException('Doble cancelación admitida');
    } catch (ErrorValidacion) {
        ok(
            (int) $repo->buscar($productos[0])['stock'] === 5,
            'cancelación no repone dos veces'
        );
    }

    // 8. La clave foránea impide borrar físicamente un producto vendido.
    try {
        $repo->borrar($productos[0], false);
        throw new RuntimeException('No detectó relación');
    } catch (PDOException $e) {
        ok($e->getCode() === '23503', 'borrado físico restringido');
    }

    ok(
        $repo->borrar($productos[0], true)
            && !booleano($repo->buscar($productos[0])['activo']),
        'baja lógica conserva producto'
    );

    // 9. Modificar un producto existente.
    $d = $repo->buscar($productos[1]);
    unset($d['id']);
    $d['nombre'] = 'Editado ' . $marca;
    $d['activo'] = booleano($d['activo']);
    $repo->guardar($productos[1], $d);
    ok($repo->buscar($productos[1])['nombre'] === $d['nombre'], 'UPDATE de producto');

    // 10. CSV: válido, duplicado, válido. SAVEPOINT permite continuar.
    $tmp = tempnam(sys_get_temp_dir(), 'pilatos');
    file_put_contents(
        $tmp,
        "nombre;apellidos;email;telefono\n"
        . "$marca;CSV;$marca-csv@example.com;\n"
        . "$marca;Duplicado;$marca@example.com;\n"
        . "$marca;Despues;$marca-despues@example.com;\n"
    );

    $r = (new ImportadorCsv($pdo))->importar($tmp);
    ok(
        $r['aceptadas'] === 2 && $r['rechazadas'] === 1,
        'SAVEPOINT permite continuar después de duplicado'
    );

    // 11. El informe no debe contar los pedidos cancelados.
    $ventas = (new InformeRepository($pdo))->ventas();
    $fila = array_values(array_filter(
        $ventas,
        fn ($v) => (int) $v['id'] === $cat
    ))[0];

    ok(centimos($fila['importe']) === 0, 'informe excluye pedido cancelado');
    echo "Integración completada.\n";
} finally {
    // Eliminar solo los registros QA creados por esta ejecución.
    // Respetamos las claves foráneas: primero pedidos, luego productos...
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($pedidos as $id) {
        $pdo->prepare('DELETE FROM pedidos WHERE id=?')->execute([$id]);
    }

    foreach ($productos as $id) {
        $pdo->prepare('DELETE FROM productos WHERE id=?')->execute([$id]);
    }

    $pdo->prepare('DELETE FROM clientes WHERE nombre=?')->execute([$marca]);

    if ($cat) {
        $pdo->prepare('DELETE FROM categorias WHERE id=?')->execute([$cat]);
    }

    if ($tmp && is_file($tmp)) {
        unlink($tmp);
    }
}
