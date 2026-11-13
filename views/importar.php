<!-- El formulario de subida requiere multipart/form-data. -->
<p>
    CSV de clientes en UTF-8, separado por punto y coma.
    Cabecera exacta: <code>nombre;apellidos;email;telefono</code>.
</p>

<form
    method="post"
    enctype="multipart/form-data"
    action="<?= e(url('importar')) ?>"
>
    <?= csrf() ?>
    <!-- Límite orientativo del navegador; el servidor vuelve a comprobarlo. -->
    <input type="hidden" name="MAX_FILE_SIZE" value="1048576">

    <label for="csv">Archivo CSV (máximo 1 MB)</label>
    <input type="file" name="csv" id="csv" accept=".csv,text/csv" required>

    <div class="acciones"><button>Importar clientes</button></div>
</form>

<p class="nota">
    Las filas válidas se confirman en lotes de 100. Las rechazadas se indican
    por número de registro CSV (la cabecera es el 1). En archivos con campos
    multilínea, este número no equivale a una línea física.
</p>

<?php if ($resultado): ?>
    <p class="aviso">
        <?= e($resultado['aceptadas']) ?> aceptadas y
        <?= e($resultado['rechazadas']) ?> rechazadas.
    </p>

    <?php if ($resultado['errores']): ?>
        <div class="tabla">
            <table>
                <thead><tr><th>Registro CSV</th><th>Motivo</th></tr></thead>
                <tbody>
                    <?php foreach ($resultado['errores'] as $error): ?>
                        <tr>
                            <td><?= e($error['registro']) ?></td>
                            <td><?= e($error['motivo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
