<!-- Detalle de un pedido: la cabecera y sus líneas proceden de PedidoService. -->
<dl>
    <dt>Cliente</dt>
    <dd><?= e($pedido['nombre'] . ' ' . $pedido['apellidos']) ?></dd>

    <dt>Correo</dt>
    <dd><?= e($pedido['email']) ?></dd>

    <dt>Fecha UTC</dt>
    <dd><?= e($pedido['fecha']) ?></dd>

    <dt>Estado</dt>
    <dd><?= e($pedido['estado']) ?></dd>
</dl>

<div class="tabla">
    <table>
        <thead>
            <tr><th>Producto</th><th>Cantidad</th><th>Precio guardado</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($pedido['lineas'] as $l): ?>
                <tr>
                    <td><?= e($l['referencia'] . ' · ' . $l['nombre']) ?></td>
                    <td><?= e($l['cantidad']) ?></td>
                    <td><?= e(euros($l['precio_unitario'])) ?></td>
                    <td><?= e(euros($l['subtotal'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2>Total: <?= e(euros($pedido['total'])) ?></h2>

<?php
// Las opciones que se ofrecen dependen del estado actual.
// EJ10 deberá validar estas mismas reglas también en el servidor.
$siguientes = [
    'pendiente' => ['preparando', 'cancelado'],
    'preparando' => ['enviado', 'cancelado'],
    'enviado' => ['entregado'],
    'entregado' => [],
    'cancelado' => [],
];
?>

<?php if ($siguientes[$pedido['estado']]): ?>
    <!-- Los cambios de estado se envían con POST y token CSRF. -->
    <form method="post" action="<?= e(url('pedido_estado')) ?>">
        <?= csrf() ?>
        <input type="hidden" name="id" value="<?= e($pedido['id']) ?>">

        <label for="estado">Nuevo estado</label>
        <select name="estado" id="estado">
            <?php foreach ($siguientes[$pedido['estado']] as $estado): ?>
                <option><?= e($estado) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="acciones"><button>Aplicar cambio</button></div>
    </form>
<?php endif; ?>

<p class="nota">
    Cancelar desde pendiente o preparando devuelve el stock una sola vez.
    Los pedidos enviados o entregados conservan su histórico.
</p>
