<!-- La consulta agregada la implementará EJ11 en InformeRepository::ventas(). -->
<p>
    Unidades e importes de pedidos no cancelados, incluidos los pendientes.
    Es un informe de pedidos, no de cobros ni de facturas.
</p>

<div class="tabla">
    <table>
        <thead>
            <tr><th>Categoría</th><th>Unidades</th><th>Importe</th></tr>
        </thead>
        <tbody>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <td><?= e($f['nombre']) ?></td>
                    <td><?= e($f['unidades']) ?></td>
                    <td><?= e(euros($f['importe'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p>
    El SQL utiliza <code>LEFT JOIN</code>, <code>GROUP BY</code>,
    <code>SUM</code> y <code>COALESCE</code> para conservar también categorías sin ventas.
</p>
