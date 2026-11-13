<?php
declare(strict_types=1);

/**
 * Reglas de negocio de los pedidos.
 *
 * A diferencia de un repositorio sencillo, aquí coordinamos varias tablas.
 * Por eso la creación y la cancelación necesitan transacciones.
 */
final class PedidoService
{
    // Estados reconocidos por la restricción CHECK de PostgreSQL.
    public const ESTADOS = [
        'pendiente',
        'preparando',
        'enviado',
        'entregado',
        'cancelado',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    /** Consultar los pedidos y el nombre del cliente, de diez en diez. */
    public function listar(int $pagina): array
    {
        $total = (int) $this->pdo
            ->query('SELECT COUNT(*) FROM pedidos')
            ->fetchColumn();

        $paginas = max(1, (int) ceil($total / 10));
        $pagina = min($pagina, $paginas);

        // JOIN recupera el nombre del cliente asociado a cada pedido.
        $st = $this->pdo->prepare(
            'SELECT p.id, p.fecha, p.estado, p.total,
                    c.nombre, c.apellidos
             FROM pedidos p
             JOIN clientes c ON c.id = p.cliente_id
             ORDER BY p.id DESC
             LIMIT 10 OFFSET :off'
        );
        $st->bindValue(':off', ($pagina - 1) * 10, PDO::PARAM_INT);
        $st->execute();

        return [
            'filas' => $st->fetchAll(),
            'pagina' => $pagina,
            'paginas' => $paginas,
            'total' => $total,
        ];
    }

    /** Recuperar la cabecera del pedido y, después, sus líneas. */
    public function detalle(int $id): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT p.id, p.fecha, p.estado, p.total,
                    c.nombre, c.apellidos, c.email
             FROM pedidos p
             JOIN clientes c ON c.id = p.cliente_id
             WHERE p.id = :id'
        );
        $st->execute(['id' => $id]);
        $pedido = $st->fetch();

        if (!$pedido) {
            return null;
        }

        // El precio_unitario se guarda en la línea: no cambia si cambia
        // el precio actual del producto en el catálogo.
        $st = $this->pdo->prepare(
            'SELECT l.producto_id, l.cantidad, l.precio_unitario,
                    l.cantidad * l.precio_unitario AS subtotal,
                    p.nombre, p.referencia
             FROM lineas_pedido l
             JOIN productos p ON p.id = l.producto_id
             WHERE l.pedido_id = :id
             ORDER BY l.id'
        );
        $st->execute(['id' => $id]);
        $pedido['lineas'] = $st->fetchAll();

        return $pedido;
    }

    /**
     * EJ09 · Crear un pedido completo de forma atómica.
     * Si falla una línea, no debe quedar cabecera ni stock descontado.
     */
    public function crear(int $cliente, array $lineas): int
    {
        // EJ09:INICIO

        // TODO EJ09.1: Validar cantidades y agrupar productos repetidos.
        // TODO EJ09.2: Iniciar una transacción con beginTransaction().
        // TODO EJ09.3: Insertar cabecera y obtener ID con RETURNING id.
        // TODO EJ09.4: Leer productos con FOR UPDATE y verificar 'activo'.
        // TODO EJ09.5: Descontar stock con condición stock >= cantidad.
        // TODO EJ09.6: Insertar líneas, sumar céntimos y actualizar total.
        // TODO EJ09.7: commit() si todo sale bien; rollBack() ante un error.
        // Los precios se leen de PostgreSQL, nunca de $_POST.

        throw new Pendiente(
            'EJ09: Registrar cabecera, líneas y stock en una transacción atómica.'
        );

        // EJ09:FIN
    }

    /**
     * EJ10 · Cambiar el estado de un pedido.
     * Al cancelar, se devuelve el stock exactamente una vez.
     */
    public function cambiarEstado(int $id, string $nuevo): void
    {
        // EJ10:INICIO

        // TODO EJ10.1: Iniciar transacción y bloquear pedido FOR UPDATE.
        // TODO EJ10.2: Comprobar las transiciones de estado permitidas.
        // TODO EJ10.3: Si se cancela, devolver stock de todas las líneas.
        // TODO EJ10.4: Actualizar el estado y confirmar la transacción.
        // TODO EJ10.5: rollBack() si falla alguna operación.
        // No permitir una segunda cancelación ni cancelar un enviado.

        throw new Pendiente(
            'EJ10: Cambiar estado y reponer stock al cancelar una sola vez.'
        );

        // EJ10:FIN
    }
}
