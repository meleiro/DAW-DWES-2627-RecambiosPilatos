<?php
declare(strict_types=1);

/**
 * Pruebas unitarias: no necesitan conexión a PostgreSQL.
 *
 * Se ejecutan desde la raíz del proyecto con:
 *     php tests/unidades.php
 */
require dirname(__DIR__) . '/app/helpers.php';
require dirname(__DIR__) . '/app/validation.php';

/** Lanzar una excepción cuando una comprobación no se cumple. */
function comprobar(bool $ok, string $mensaje): void
{
    if (!$ok) {
        throw new RuntimeException($mensaje);
    }

    echo "OK: $mensaje\n";
}

// Los precios se convierten a enteros para no sumar importes con float.
comprobar(centimos('119.99') === 11999, 'importe exacto en céntimos');
comprobar(decimal(centimos('12,9')) === '12.90', 'normaliza coma y decimales');

// Escapar etiquetas HTML previene la interpretación de texto como marcado.
comprobar(e('<script>') === '&lt;script&gt;', 'escape HTML');

// PostgreSQL puede devolver booleanos representados como 't' y 'f'.
comprobar(!booleano('f') && booleano('t'), 'booleanos PostgreSQL');

// Un producto puede tener stock 0, pero no stock negativo.
comprobar(entero('0', 0) === 0, 'stock cero válido');

// Los precios con signo, exceso de decimales o notación no admitida fallan.
foreach (['-1', '1.001', '1e3', 'NaN'] as $v) {
    try {
        centimos($v);
        throw new RuntimeException('Se aceptó un precio inválido.');
    } catch (ErrorValidacion) {
        echo "OK: rechaza precio $v\n";
    }
}

// Un campo de texto no puede recibirse como array malformado.
try {
    texto(['nombre' => []], 'nombre');
    throw new RuntimeException('Se aceptó un array.');
} catch (ErrorValidacion) {
    echo "OK: rechaza un array como nombre\n";
}

echo "Pruebas sin base de datos completadas.\n";
