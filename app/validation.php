<?php
declare(strict_types=1);

/**
 * Validación de datos recibidos desde formularios HTML.
 *
 * La validación de PHP mejora los mensajes, pero NO sustituye las
 * restricciones CHECK, UNIQUE y FOREIGN KEY de PostgreSQL.
 */

/** Validar y normalizar los campos de un producto antes de guardarlo. */
function validarProducto(array $d): array
{
    $r = [];

    // Referencia y nombre son obligatorios y tienen una longitud máxima.
    foreach (['referencia' => 30, 'nombre' => 150] as $campo => $max) {
        $r[$campo] = texto($d, $campo);

        if ($r[$campo] === '' || longitud($r[$campo]) > $max) {
            throw new ErrorValidacion([
                $campo => "Obligatorio, máximo $max caracteres.",
            ]);
        }
    }

    // La descripción es opcional, pero no puede ser ilimitada.
    $r['descripcion'] = texto($d, 'descripcion');

    if (longitud($r['descripcion']) > 5000) {
        throw new ErrorValidacion(['descripcion' => 'Máximo 5000 caracteres.']);
    }

    // Se validan los decimales en céntimos para evitar errores de float.
    $importe = centimos(texto($d, 'precio'));

    if ($importe > 9999999999) {
        throw new ErrorValidacion(['precio' => 'Máximo 99999999.99 €.']);
    }

    $r['precio'] = decimal($importe);
    $r['stock'] = entero($d['stock'] ?? '', 0, 1000000);
    $r['categoria_id'] = entero($d['categoria_id'] ?? '');

    // Un checkbox desmarcado no envía valor: lo interpretamos como '0'.
    $activo = $d['activo'] ?? '0';

    if (!is_string($activo) || !in_array($activo, ['0', '1'], true)) {
        throw new ErrorValidacion(['activo' => 'Estado no válido.']);
    }

    $r['activo'] = $activo === '1';

    return $r;
}

/** Validar los campos de una categoría o de un cliente. */
function validarFicha(string $tipo, array $d): array
{
    // Los nombres y límites coinciden con las columnas del esquema SQL.
    $campos = $tipo === 'categorias'
        ? ['nombre' => 100, 'descripcion' => 5000]
        : ['nombre' => 100, 'apellidos' => 150, 'email' => 150, 'telefono' => 20];

    $r = [];

    foreach ($campos as $campo => $max) {
        $r[$campo] = texto($d, $campo);

        // Descripción y teléfono son opcionales; los demás, obligatorios.
        $opcional = in_array($campo, ['descripcion', 'telefono'], true);

        if (longitud($r[$campo]) > $max || (!$opcional && $r[$campo] === '')) {
            throw new ErrorValidacion([
                $campo => "Revisa el valor, máximo $max caracteres.",
            ]);
        }
    }

    if ($tipo === 'clientes' && !filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
        throw new ErrorValidacion(['email' => 'Correo no válido.']);
    }

    return $r;
}

/**
 * Leer y validar los filtros GET del catálogo.
 * Devuelve valores normalizados, no fragmentos de SQL.
 */
function filtrosProductos(array $d): array
{
    $f = [
        'q' => texto($d, 'q'),
        'categoria' => texto($d, 'categoria'),
        'min' => texto($d, 'min'),
        'max' => texto($d, 'max'),
        'activo' => texto($d, 'activo'),
        'orden' => texto($d, 'orden', 'nombre'),
        'dir' => texto($d, 'dir', 'asc'),
    ];

    if (longitud($f['q']) > 150) {
        throw new ErrorValidacion(['q' => 'Máximo 150 caracteres.']);
    }

    if ($f['categoria'] !== '') {
        $f['categoria'] = entero($f['categoria']);
    }

    // Convertir los límites de precio a cadenas decimales homogéneas.
    foreach (['min', 'max'] as $k) {
        if ($f[$k] !== '') {
            $f[$k] = decimal(centimos($f[$k]));
        }
    }

    if (
        $f['min'] !== ''
        && $f['max'] !== ''
        && centimos($f['min']) > centimos($f['max'])
    ) {
        throw new ErrorValidacion(['precio' => 'El mínimo supera al máximo.']);
    }

    if (!in_array($f['activo'], ['', '1', '0'], true)) {
        throw new ErrorValidacion(['activo' => 'Estado no válido.']);
    }

    // La página se valida antes de calcular el OFFSET de la consulta.
    $f['pagina'] = entero($d['pagina'] ?? 1, 1, 100000);

    return $f;
}
