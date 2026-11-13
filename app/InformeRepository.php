<?php
declare(strict_types=1);

/**
 * Consultas de informe: agrupaciones y cálculos que realiza PostgreSQL.
 *
 * Las vistas solo muestran las filas devueltas; no hacen SUM en PHP.
 */
final class InformeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** EJ11 · Unidades e importes de pedidos por categoría. */
    public function ventas(): array
    {
        // EJ11:INICIO

        // TODO EJ11.1: Partir de categorias para conservar las que no venden.
        // TODO EJ11.2: Usar LEFT JOIN con productos y lineas_pedido.
        // TODO EJ11.3: Excluir pedidos cancelados sin perder filas vacías.
        // TODO EJ11.4: Calcular SUM, GROUP BY y COALESCE.
        // La vista espera: id, nombre, unidades e importe.

        throw new Pendiente(
            'EJ11: Agregar unidades e importes con LEFT JOIN y GROUP BY.'
        );

        // EJ11:FIN
    }
}
