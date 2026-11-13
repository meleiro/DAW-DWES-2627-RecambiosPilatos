<?php
declare(strict_types=1);

/**
 * EJ01 · Conexión a PostgreSQL mediante PDO.
 *
 * Esta función será la única puerta de acceso a la base de datos.
 * Todas las clases Repository y Service recibirán el mismo objeto PDO.
 *
 * @return PDO Conexión preparada para ejecutar consultas SQL.
 */
function bd(): PDO
{
    // EJ01:INICIO

    // TODO EJ01.1: Leer config/database.local.php o las variables de entorno.
    // TODO EJ01.2: Construir el DSN: pgsql:host=...;port=...;dbname=...
    // TODO EJ01.3: Crear PDO con ERRMODE_EXCEPTION y FETCH_ASSOC.
    // TODO EJ01.4: Desactivar la emulación de sentencias preparadas.
    // TODO EJ01.5: Fijar la zona horaria UTC y reutilizar una conexión por petición.
    //
    // Sugerencia: una variable `static $pdo = null` conserva el objeto entre
    // llamadas a bd() durante la misma petición HTTP.
    // Consulta docs/GUIA_AULA.md para las pruebas y el resultado esperado.

    throw new Pendiente(
        'EJ01: Crear la conexión PDO con configuración y reutilización por petición.'
    );

    // EJ01:FIN
}
