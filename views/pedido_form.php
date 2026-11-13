<?php
// El controlador envía $clientes, $errores y $d (valores del formulario).
require __DIR__ . '/errores.php';

// Los campos [] de HTML llegarán como arrays en $_POST.
$ids = is_array($d['producto_id'] ?? null) ? $d['producto_id'] : [];
$cants = is_array($d['cantidad'] ?? null) ? $d['cantidad'] : [];
?>

<p>
    Consulta los identificadores en el
    <a href="<?= e(url('productos')) ?>" target="_blank" rel="noopener">catálogo (nueva pestaña)</a>.
    El precio y el stock se comprueban en el servidor al guardar.
</p>

<form method="post" action="<?= e(url('pedido_form')) ?>">
    <?= csrf() ?>

    <label for="cliente_id">Cliente</label>
    <select id="cliente_id" name="cliente_id" required>
        <option value="">Selecciona</option>
        <?php foreach ($clientes as $c): ?>
            <option
                value="<?= e($c['id']) ?>"
                <?= is_scalar($d['cliente_id'] ?? '')
                    && (string) ($d['cliente_id'] ?? '') === (string) $c['id']
                    ? 'selected' : '' ?>
            ><?= e($c['nombre'] . ' ' . $c['apellidos']) ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Cinco líneas como máximo. Las que no tengan producto se ignorarán. -->
    <div class="tabla">
        <table>
            <thead>
                <tr><th>Línea</th><th>ID del producto</th><th>Cantidad</th></tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 5; ++$i): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <label for="producto_<?= $i ?>">ID producto <?= $i + 1 ?></label>
                            <input
                                id="producto_<?= $i ?>"
                                type="number"
                                name="producto_id[]"
                                min="1"
                                value="<?= e(is_scalar($ids[$i] ?? '') ? $ids[$i] : '') ?>"
                            >
                        </td>
                        <td>
                            <label for="cantidad_<?= $i ?>">Cantidad <?= $i + 1 ?></label>
                            <input
                                id="cantidad_<?= $i ?>"
                                type="number"
                                name="cantidad[]"
                                min="1"
                                max="1000"
                                value="<?= e(is_scalar($cants[$i] ?? '1') ? ($cants[$i] ?? '1') : '1') ?>"
                            >
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <p class="nota">
        Se omiten líneas sin identificador. Los productos repetidos se agrupan.
        Máximo 1000 unidades por producto.
    </p>
    <button>Registrar pedido</button>
</form>
