<?php
declare(strict_types=1);

/**
 * Importación de clientes desde un archivo CSV separado por punto y coma.
 *
 * La subida del fichero se valida en public/index.php. Esta clase recibe
 * la ruta temporal del archivo ya recibido por PHP.
 */
final class ImportadorCsv
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * EJ12 · Leer registros, insertar por lotes y continuar tras errores.
     *
     * @param string $ruta Ruta del archivo CSV temporal.
     * @return array Claves: aceptadas, rechazadas, errores.
     */
    public function importar(string $ruta): array
    {
        // EJ12:INICIO

        // TODO EJ12.1: Abrir archivo y validar cabecera con fgetcsv().
        // TODO EJ12.2: Preparar una única sentencia INSERT reutilizable.
        // TODO EJ12.3: Agrupar inserciones en transacciones de 100 filas.
        // TODO EJ12.4: Crear SAVEPOINT antes de cada registro.
        // TODO EJ12.5: ROLLBACK TO SAVEPOINT cuando falle una fila.
        // TODO EJ12.6: Devolver contadores y errores por registro.
        // En PostgreSQL, una sentencia fallida aborta la transacción
        // hasta hacer ROLLBACK o ROLLBACK TO SAVEPOINT.

        throw new Pendiente(
            'EJ12: Leer CSV por registros y usar SAVEPOINT para errores por fila.'
        );

        // EJ12:FIN
    }
}
