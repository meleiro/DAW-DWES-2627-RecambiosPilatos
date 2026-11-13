<!-- Enlace para crear un producto: GET abre el formulario. -->
<p><a class="boton" href="<?= e(url('producto_form')) ?>">Nuevo recambio</a></p>

<!-- Los filtros son GET porque solo consultan información, no la modifican. -->
<form method="get" class="filtros">
    <input type="hidden" name="r" value="productos">

    <div class="campo ancho">
        <label for="q">Nombre o referencia</label>
        <input id="q" name="q" value="<?= e($f['q']) ?>" maxlength="150">
    </div>

    <div class="campo">
        <label for="categoria">Categoría</label>
        <select id="categoria" name="categoria">
            <option value="">Todas</option>
            <?php foreach ($categorias as $c): ?>
                <option
                    value="<?= e($c['id']) ?>"
                    <?= (string) $f['categoria'] === (string) $c['id'] ? 'selected' : '' ?>
                ><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Los límites de precio comparten estructura HTML. -->
    <?php foreach (['min' => 'Precio mínimo', 'max' => 'Precio máximo'] as $k => $etiqueta): ?>
        <div class="campo">
            <label for="<?= e($k) ?>"><?= e($etiqueta) ?></label>
            <input id="<?= e($k) ?>" name="<?= e($k) ?>" value="<?= e($f[$k]) ?>" inputmode="decimal">
        </div>
    <?php endforeach; ?>

    <div class="campo">
        <label for="activo">Estado</label>
        <select name="activo" id="activo">
            <?php foreach (['' => 'Todos', '1' => 'Activos', '0' => 'Inactivos'] as $k => $v): ?>
                <option
                    value="<?= e($k) ?>"
                    <?= (string) $k === $f['activo'] ? 'selected' : '' ?>
                ><?= e($v) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="campo">
        <label for="orden">Ordenar por</label>
        <select name="orden" id="orden">
            <?php foreach (['nombre', 'precio', 'stock', 'referencia'] as $k): ?>
                <option <?= $k === $f['orden'] ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="campo">
        <label for="dir">Dirección</label>
        <select id="dir" name="dir">
            <option value="asc">Ascendente</option>
            <option value="desc" <?= $f['dir'] === 'desc' ? 'selected' : '' ?>>Descendente</option>
        </select>
    </div>

    <button>Filtrar</button>
    <a href="<?= e(url('productos')) ?>">Limpiar</a>
</form>

<!-- La tabla muestra filas obtenidas por ProductoRepository::listar(). -->
<div class="tabla">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Referencia y nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datos['filas'] as $p): ?>
                <tr>
                    <td><?= e($p['id']) ?></td>
                    <td>
                        <?= e($p['referencia']) ?>
                        <br>
                        <a href="<?= e(url('producto', ['id' => $p['id']])) ?>">
                            <?= e($p['nombre']) ?>
                        </a>
                    </td>
                    <td><?= e($p['categoria']) ?></td>
                    <td><?= e(euros($p['precio'])) ?></td>
                    <td><?= e($p['stock']) ?></td>
                    <td><?= booleano($p['activo']) ? 'Activo' : 'Inactivo' ?></td>
                    <td><a href="<?= e(url('producto_form', ['id' => $p['id']])) ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$datos['filas']): ?>
                <tr><td colspan="7">No hay resultados para los filtros indicados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// La paginación conserva el texto, categoría, precios y demás filtros.
$rutaPaginacion = 'productos';
$parametros = $f;
require __DIR__ . '/paginacion.php';
?>
