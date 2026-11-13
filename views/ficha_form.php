<?php
// El mismo formulario se utiliza para categorías y clientes.
// $id es null para crear y contiene un entero para editar.
require __DIR__ . '/errores.php';

$campos = $tipo === 'clientes'
    ? ['nombre' => 100, 'apellidos' => 150, 'email' => 150, 'telefono' => 20]
    : ['nombre' => 100, 'descripcion' => 5000];
?>

<form
    method="post"
    class="ficha"
    action="<?= e(url('ficha_form', ['tipo' => $tipo] + ($id ? ['id' => $id] : []))) ?>"
>
    <!-- Token oculto para evitar peticiones POST no autorizadas. -->
    <?= csrf() ?>

    <?php foreach ($campos as $k => $max): ?>
        <label for="<?= e($k) ?>"><?= e(ucfirst($k)) ?></label>

        <?php if ($k === 'descripcion'): ?>
            <!-- La descripción puede ocupar varias líneas. -->
            <textarea
                name="descripcion"
                id="descripcion"
                maxlength="5000"
            ><?= e(is_string($d[$k] ?? '') ? $d[$k] : '') ?></textarea>
        <?php else: ?>
            <input
                id="<?= e($k) ?>"
                name="<?= e($k) ?>"
                type="<?= $k === 'email' ? 'email' : 'text' ?>"
                maxlength="<?= e($max) ?>"
                value="<?= e(is_string($d[$k] ?? '') ? $d[$k] : '') ?>"
                <?= $k !== 'telefono' ? 'required' : '' ?>
            >
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="acciones">
        <button>Guardar</button>
        <a href="<?= e(url('fichas', ['tipo' => $tipo])) ?>">Cancelar</a>
    </div>
</form>
