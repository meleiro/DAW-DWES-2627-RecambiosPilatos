<?php
// La variable $p contiene un producto recuperado con ProductoRepository::buscar().
// Recorremos las propiedades sencillas y después mostramos precio y estado.
$campos = [
    'id' => 'ID',
    'referencia' => 'Referencia',
    'nombre' => 'Nombre',
    'descripcion' => 'Descripción',
    'stock' => 'Stock',
    'categoria_id' => 'ID categoría',
];
?>

<dl>
    <?php foreach ($campos as $k => $v): ?>
        <dt><?= e($v) ?></dt>
        <dd><?= e($p[$k] ?? '') ?></dd>
    <?php endforeach; ?>

    <dt>Precio</dt>
    <dd><?= e(euros($p['precio'])) ?></dd>

    <dt>Estado</dt>
    <dd><?= booleano($p['activo']) ? 'Activo' : 'Inactivo' ?></dd>
</dl>

<p>
    <a class="boton" href="<?= e(url('producto_form', ['id' => $p['id']])) ?>">
        Editar producto
    </a>
</p>

<details>
    <summary>Dar de baja o eliminar</summary>
    <p>
        La baja lógica conserva pedidos históricos.
        El borrado físico falla si el producto aparece en un pedido.
    </p>

    <!-- Dos botones en el mismo formulario envían valores diferentes de modo. -->
    <form method="post" action="<?= e(url('producto_borrar')) ?>">
        <?= csrf() ?>
        <input type="hidden" name="id" value="<?= e($p['id']) ?>">
        <button name="modo" value="logico" class="secundario">Dar de baja</button>
        <button name="modo" value="fisico" class="peligro">Eliminar definitivamente</button>
    </form>
</details>
