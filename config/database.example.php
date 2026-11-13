<?php
/**
 * Plantilla de configuración de PostgreSQL.
 *
 * Copiar este archivo como config/database.local.php y poner la clave real.
 * No subir database.local.php a Git (está en .gitignore).
 *
 * PostgreSQL instalado en Windows: puerto habitual 5432.
 * PostgreSQL con el Docker Compose de Recambios Pilatos: puerto externo 5433.
 * Este archivo está fuera de public/, así que no debe servirse por HTTP.
 */
return [
    'host' => '127.0.0.1',       // Servidor PostgreSQL accesible desde PHP.
    'port' => '5432',            // Cambiar a '5433' si usas nuestro Docker.
    'database' => 'recambios_pilatos',
    'user' => 'pilatos_app',     // Usuario limitado, no administrador postgres.
    'password' => 'CAMBIAR_POR_UNA_CLAVE_SEGURA',
];
