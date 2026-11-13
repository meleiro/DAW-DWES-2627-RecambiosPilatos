<!-- Listado reutilizado por las entidades categorias y clientes. -->
<p>
    <a class="boton" href="<?= e(url('ficha_form', ['tipo' => $tipo])) ?>">
        Crear <?= $tipo === 'clientes' ? 'cliente' : 'categoría' ?>
    </a>
</p>

<div class="tabla">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th><?= $tipo === 'clientes' ? 'Contacto' : 'Descripción' ?></th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datos['filas'] as $ficha): ?>
                <!-- Cada fila procede del SELECT del repositorio. -->
                <tr>
                    <td><?= e($ficha['id']) ?></td>
                    <td>
                        <?= e($ficha['nombre']) ?>
                        <?= e($ficha['apellidos'] ?? '') ?>
                    </td>
                    <td>
                        <?= e($ficha['email'] ?? $ficha['descripcion'] ?? '') ?>
                        <br>
                        <?= e($ficha['telefono'] ?? '') ?>
                    </td>
                    <td>
                        <div class="fila">
                            <a href="<?= e(url('ficha_form', [
                                'tipo' => $tipo,
                                'id' => $ficha['id'],
                            ])) ?>">Editar</a>

                            <!-- El borrado se realiza con POST, no con un enlace GET. -->
                            <form method="post" action="<?= e(url('ficha_borrar')) ?>">
                                <?= csrf() ?>
                                <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                                <input type="hidden" name="id" value="<?= e($ficha['id']) ?>">
                                <button class="peligro">Eliminar #<?= e($ficha['id']) ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$datos['filas']): ?>
                <tr><td colspan="4">Todavía no hay registros.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<p class="nota">La base impide borrar categorías con productos y clientes con pedidos.</p>

<?php
// Preparar las variables que necesita el fragmento paginacion.php.
$rutaPaginacion = 'fichas';
$parametros = ['tipo' => $tipo];
require __DIR__ . '/paginacion.php';
?>
