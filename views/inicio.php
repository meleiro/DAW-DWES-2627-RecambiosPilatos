<!-- La portada se puede visitar antes de resolver la conexión PDO. -->
<p>Gestión de recambios de automóvil con PHP sin frameworks, PostgreSQL y PDO.</p>

<!-- Recorrido propuesto para completar los ejercicios de acceso a datos. -->
<ol class="pasos">
    <li>
        <a href="<?= e(url('conexion')) ?>">Comprobar la conexión</a>:
        DSN, credenciales y errores como excepciones.
    </li>
    <li>
        <a href="<?= e(url('productos')) ?>">Consultar productos</a>:
        JOIN, filtros preparados, ordenación y paginación.
    </li>
    <li>
        <a href="<?= e(url('producto_form')) ?>">Crear un recambio</a>:
        formulario, validación e INSERT RETURNING.
    </li>
    <li>
        Editar, dar de baja o eliminar un producto.
        Comprobar las restricciones de la base de datos.
    </li>
    <li>
        <a href="<?= e(url('pedido_form')) ?>">Registrar un pedido</a>:
        cabecera, líneas y stock en una transacción.
    </li>
    <li>
        <a href="<?= e(url('informe')) ?>">Consultar importes por categoría</a>
        y practicar la importación CSV.
    </li>
</ol>

<p class="nota">Consulta README.md y docs/GUIA_AULA.md para preparar el entorno y seguir las actividades.</p>
<p class="aviso">Panel didáctico para uso local con datos ficticios. No incorpora autenticación ni permisos de usuarios.</p>
