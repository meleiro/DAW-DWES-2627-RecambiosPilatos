<?php
declare(strict_types=1);

/**
 * Funciones auxiliares y excepciones reutilizadas en todo el proyecto.
 *
 * Separarlas del controlador evita repetir código de validación, salida
 * HTML, redirecciones y tratamiento de errores.
 */

/** Error HTTP con el código de estado que debe devolver el servidor. */
class ErrorHttp extends RuntimeException
{
    public function __construct(public readonly int $estado, string $mensaje)
    {
        parent::__construct($mensaje);
    }
}

/** Señala un ejercicio de la versión base que el alumnado aún no ha resuelto. */
class Pendiente extends RuntimeException
{
}

/** Recoge los errores de validación para mostrarlos junto al formulario. */
class ErrorValidacion extends RuntimeException
{
    public function __construct(public readonly array $errores)
    {
        parent::__construct('Revisa los campos del formulario.');
    }
}

/**
 * Escapar una cadena antes de insertarla en HTML.
 * Evita interpretar como etiquetas el texto procedente de PostgreSQL.
 */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Leer una cadena de $_GET o $_POST y quitar espacios exteriores.
 * Si llega un array inesperado (por ejemplo, nombre[]=...), se rechaza.
 */
function texto(array $datos, string $clave, string $defecto = ''): string
{
    $v = $datos[$clave] ?? $defecto;

    if (!is_string($v)) {
        throw new ErrorValidacion([$clave => 'Debe ser texto.']);
    }

    return trim($v);
}

/** Convertir y validar un entero dentro de los límites permitidos. */
function entero(mixed $v, int $min = 1, int $max = PHP_INT_MAX): int
{
    if (!is_string($v) && !is_int($v)) {
        throw new ErrorValidacion(['numero' => 'Introduce un entero válido.']);
    }

    $n = filter_var($v, FILTER_VALIDATE_INT);

    if ($n === false || $n < $min || $n > $max) {
        throw new ErrorValidacion([
            'numero' => "El entero debe estar entre $min y $max.",
        ]);
    }

    return $n;
}

/**
 * Convertir una cantidad monetaria a céntimos enteros.
 * Ejemplo: '19,95' -> 1995. No usamos float para operar con dinero.
 */
function centimos(string $v): int
{
    $v = str_replace(',', '.', trim($v));

    // Solo se admiten cifras, con hasta dos decimales y sin signo negativo.
    if (!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/D', $v)) {
        throw new ErrorValidacion([
            'precio' => 'Precio no negativo, hasta 12 enteros y 2 decimales.',
        ]);
    }

    [$entero, $decimal] = array_pad(explode('.', $v, 2), 2, '');

    return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
}

/** De céntimos enteros a cadena decimal: 1995 -> '19.95'. */
function decimal(int $c): string
{
    return intdiv($c, 100) . '.'
        . str_pad((string) ($c % 100), 2, '0', STR_PAD_LEFT);
}

/** Formato español de una cantidad: '19.95' -> '19,95 €'. */
function euros(string $decimal): string
{
    $c = centimos($decimal);

    return number_format(intdiv($c, 100), 0, ',', '.')
        . ','
        . str_pad((string) ($c % 100), 2, '0', STR_PAD_LEFT)
        . ' €';
}

/** Convertir los valores booleanos típicos que devuelve PostgreSQL. */
function booleano(mixed $v): bool
{
    return in_array($v, [true, 1, '1', 't', 'true'], true);
}

/** Contar caracteres Unicode (no bytes) para validar longitudes. */
function longitud(string $s): int
{
    return preg_match_all('/./us', $s) ?: 0;
}

/** Construir una URL interna con la ruta r y sus parámetros GET. */
function url(string $ruta, array $parametros = []): string
{
    return 'index.php?' . http_build_query(['r' => $ruta] + $parametros);
}

/**
 * Redirigir después de un POST correcto (patrón POST/Redirect/GET).
 * HTTP 303 indica al navegador que debe visitar el destino mediante GET.
 */
function redirigir(string $ruta, array $parametros = []): never
{
    header('Location: ' . url($ruta, $parametros), true, 303);
    exit;
}

/** Guardar un mensaje temporal para mostrarlo en la siguiente petición. */
function flash(string $mensaje): void
{
    $_SESSION['flash'] = $mensaje;
}

/** Generar el campo oculto con el token anti-CSRF de la sesión. */
function csrf(): string
{
    return '<input type="hidden" name="csrf" value="'
        . e($_SESSION['csrf'])
        . '">';
}

/**
 * Proteger las acciones que modifican datos.
 * Deben ser POST y presentar el mismo token generado en la sesión.
 */
function exigirPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        throw new ErrorHttp(405, 'Esta acción requiere POST.');
    }

    $token = $_POST['csrf'] ?? null;

    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        throw new ErrorHttp(403, 'Token CSRF no válido. Recarga el formulario.');
    }
}

/**
 * Pasar datos a una vista sin mezclar SQL con el HTML.
 * extract() convierte las claves del array en variables locales de la vista.
 */
function vista(string $archivo, array $datos = []): void
{
    extract($datos, EXTR_SKIP);
    require dirname(__DIR__) . '/views/layout.php';
}

/**
 * Traducir códigos SQLSTATE de PostgreSQL a mensajes seguros.
 * Los detalles internos se registran, pero no se muestran al usuario.
 */
function mensajeBD(PDOException $e): string
{
    return match ((string) $e->getCode()) {
        '23505' => 'Ya existe un registro con esa referencia, nombre o correo.',
        '23503' => 'Hay registros relacionados: revisa la categoría o el cliente. Para un producto vendido, utiliza la baja lógica.',
        '23514', '22003', '22001', '23502' => 'Un valor no cumple los límites de la base de datos.',
        '40P01', '40001' => 'Otra operación ha coincidido con la tuya. Vuelve a intentarlo.',
        default => 'No se ha podido completar la operación con la base de datos. Revisa la configuración o el registro del servidor.',
    };
}
