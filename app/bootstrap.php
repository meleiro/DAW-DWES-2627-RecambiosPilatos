<?php
declare(strict_types=1);

/**
 * Inicialización común de la aplicación.
 * public/index.php incluye este archivo en todas las peticiones.
 *
 * No se consulta PostgreSQL aquí: solo se preparan PHP, sesiones y clases.
 */

// Evitamos mostrar al navegador rutas, consultas o detalles internos.
ini_set('display_errors', '0');
// Los errores se registran para poder diagnosticarlos durante la práctica.
ini_set('log_errors', '1');

// Rechazar identificadores de sesión que no hayan sido creados por PHP.
ini_set('session.use_strict_mode', '1');
// La sesión se transporta mediante cookies, no mediante parámetros en la URL.
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0, // Cookie de sesión: termina al cerrar el navegador.
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true, // JavaScript no puede leer esta cookie.
    'samesite' => 'Lax', // Reduce peticiones cruzadas no deseadas.
]);

// La sesión almacena mensajes temporales y el token de formularios.
if (!session_start()) {
    http_response_code(500);
    exit('No se puede iniciar la sesión. Revisa session.save_path y sus permisos en php.ini.');
}

// ??= solo asigna un valor si todavía no existe.
// El token aleatorio se enviará como campo oculto en los formularios POST.
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

// Cargamos los módulos necesarios. require_once evita inclusiones duplicadas.
$modulos = [
    'helpers',
    'validation',
    'Database',
    'ProductoRepository',
    'FichaRepository',
    'PedidoService',
    'InformeRepository',
    'ImportadorCsv',
];

foreach ($modulos as $modulo) {
    require_once __DIR__ . '/' . $modulo . '.php';
}

// Indica a las vistas que estamos en la versión con ejercicios pendientes.
const VERSION_AULA = true;
