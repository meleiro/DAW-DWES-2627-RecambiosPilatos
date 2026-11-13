<!-- Las excepciones se traducen a mensajes sin información sensible. -->
<p class="aviso error" role="alert"><?= e($mensaje) ?></p>
<p>
    <a href="<?= e(url('inicio')) ?>">Volver al inicio</a>
    ·
    <a href="<?= e(url('productos')) ?>">Ir a productos</a>
</p>
