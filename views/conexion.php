<?php
/**
 * @var array<string, string> $conexion
 */
?>

<!-- Esta vista solo se muestra cuando EJ01 ya devuelve una conexión válida. -->
<p class="aviso">Conexión establecida mediante PDO_PGSQL.</p>

<!-- $conexion es la fila devuelta por SELECT current_database(), etc. -->
<dl>
    <dt>Base</dt>
    <dd><?= e($conexion['base']) ?></dd>

    <dt>Usuario</dt>
    <dd><?= e($conexion['usuario']) ?></dd>

    <dt>Motor</dt>
    <dd><?= e($conexion['version']) ?></dd>
</dl>

<p>La función <code>bd()</code> reutiliza una conexión durante la petición.</p>
