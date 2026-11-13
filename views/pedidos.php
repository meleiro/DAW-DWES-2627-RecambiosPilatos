<!-- Enlace GET al formulario de creación de pedidos. -->
<p><a class="boton" href="<?= e(url('pedido_form')) ?>">Nuevo pedido</a></p>

<div class="tabla">
    <table>
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Fecha (UTC)</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <!-- El JOIN con clientes se ejecuta en PedidoService::listar(). -->
            <?php foreach ($datos['filas'] as $p): ?>
                <tr>
                    <td>
                        <a href="<?= e(url('pedido', ['id' => $p['id']])) ?>">#<?= e($p['id']) ?></a>
                    </td>
                    <td><?= e($p['nombre'] . ' ' . $p['apellidos']) ?></td>
                    <td><?= e($p['fecha']) ?></td>
                    <td><?= e($p['estado']) ?></td>
                    <td><?= e(euros($p['total'])) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$datos['filas']): ?>
                <tr><td colspan="5">Todavía no hay pedidos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$rutaPaginacion = 'pedidos';
$parametros = [];
require __DIR__ . '/paginacion.php';
?>
