<?php // Fragmento reutilizado por los formularios para mostrar errores de validación. ?>
<?php if ($errores): ?>
    <div class="aviso error" role="alert">
        <strong>Revisa el formulario</strong>
        <ul>
            <?php foreach ($errores as $error): ?>
                <!-- Escapar cada mensaje antes de insertarlo en HTML. -->
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
