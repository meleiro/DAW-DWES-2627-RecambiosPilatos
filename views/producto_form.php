<?php
// Este formulario sirve tanto para INSERT (sin ID) como para UPDATE (con ID).
require __DIR__ . '/errores.php';

$campos = [
    'referencia' => ['Referencia', 30],
    'nombre' => ['Nombre', 150],
    'precio' => ['Precio (€)', 11],
    'stock' => ['Stock', 7],
];
?>

<form
    method="post"
    class="ficha"
    action="<?= e(url('producto_form', $id ? ['id' => $id] : [])) ?>"
>
    <?= csrf() ?>

    <!-- Los cuatro campos comparten estructura; foreach evita repetir HTML. -->
    <?php foreach ($campos as $k => [$etiqueta, $max]): ?>
        <label for="<?= e($k) ?>"><?= e($etiqueta) ?></label>
        <input
            id="<?= e($k) ?>"
            name="<?= e($k) ?>"
            maxlength="<?= e($max) ?>"
            value="<?= e(is_scalar($d[$k] ?? '') ? $d[$k] : '') ?>"
            required
        >
    <?php endforeach; ?>

    <label for="descripcion">Descripción</label>
    <textarea
        name="descripcion"
        id="descripcion"
        maxlength="5000"
    ><?= e(is_string($d['descripcion'] ?? '') ? $d['descripcion'] : '') ?></textarea>

    <!-- Las opciones de categoría se recuperan de PostgreSQL con EJ07. -->
    <label for="categoria_id">Categoría</label>
    <select name="categoria_id" id="categoria_id" required>
        <option value="">Selecciona</option>
        <?php foreach ($categorias as $c): ?>
            <option
                value="<?= e($c['id']) ?>"
                <?= is_scalar($d['categoria_id'] ?? '')
                    && (string) ($d['categoria_id'] ?? '') === (string) $c['id']
                    ? 'selected' : '' ?>
            ><?= e($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Un checkbox sin marcar no envía el campo activo. -->
    <label>
        <input
            type="checkbox"
            name="activo"
            value="1"
            <?= booleano($d['activo'] ?? false) ? 'checked' : '' ?>
        >
        Producto activo
    </label>

    <div class="acciones">
        <button>Guardar producto</button>
        <a href="<?= e(url('productos')) ?>">Cancelar</a>
    </div>
</form>
