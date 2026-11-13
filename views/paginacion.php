<?php
// Plantilla parcial: se reutiliza en productos, categorías, clientes y pedidos.
// $rutaPaginacion y $parametros se definen en la vista que la incluye.
?>
<nav class="paginacion" aria-label="Paginación">
    <span>
        <?= e($datos['total']) ?> registros.
        Página <?= e($datos['pagina']) ?> de <?= e($datos['paginas']) ?>.
    </span>

    <?php if ($datos['pagina'] > 1): ?>
        <!-- array_merge conserva los filtros y modifica solo el número de página. -->
        <a href="<?= e(url(
            $rutaPaginacion,
            array_merge($parametros, ['pagina' => $datos['pagina'] - 1])
        )) ?>">Anterior</a>
    <?php endif; ?>

    <?php if ($datos['pagina'] < $datos['paginas']): ?>
        <a href="<?= e(url(
            $rutaPaginacion,
            array_merge($parametros, ['pagina' => $datos['pagina'] + 1])
        )) ?>">Siguiente</a>
    <?php endif; ?>
</nav>
