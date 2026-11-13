<!doctype html>
<html lang="es">
<head>
    <!-- Plantilla común: se reutiliza para todas las páginas. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · Recambios Pilatos</title>
    <link rel="stylesheet" href="assets/estilos.css">
</head>
<body>
    <!-- Enlace de accesibilidad: permite saltar directamente al contenido. -->
    <a href="#contenido" class="salto">Saltar al contenido</a>

    <header>
        <strong>
            <a href="<?= e(url('inicio')) ?>">Recambios Pilatos</a>
        </strong>
        <div>PHP y PostgreSQL · Unidad 5</div>

        <!-- Todas las rutas pasan por el controlador frontal index.php. -->
        <nav aria-label="Navegación principal">
            <a href="<?= e(url('conexion')) ?>">Conexión</a>
            <a href="<?= e(url('productos')) ?>">Productos</a>
            <a href="<?= e(url('fichas', ['tipo' => 'categorias'])) ?>">Categorías</a>
            <a href="<?= e(url('fichas', ['tipo' => 'clientes'])) ?>">Clientes</a>
            <a href="<?= e(url('pedidos')) ?>">Pedidos</a>
            <a href="<?= e(url('informe')) ?>">Informe</a>
            <a href="<?= e(url('importar')) ?>">Importar CSV</a>
        </nav>
    </header>

    <main id="contenido">
        <!-- e() evita interpretar como HTML los datos de la aplicación. -->
        <h1><?= e($titulo) ?></h1>

        <?php if (isset($_SESSION['flash'])): ?>
            <!-- Mensaje de una sola petición: patrón POST/Redirect/GET. -->
            <p class="aviso" role="status"><?= e($_SESSION['flash']) ?></p>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if (VERSION_AULA): ?>
            <p class="aviso">
                Versión de aula. Completa los ejercicios EJ01 a EJ12
                para activar el acceso a datos.
            </p>
        <?php endif; ?>

        <?php
        // El controlador indica qué plantilla específica debemos incorporar.
        // Ejemplo: $archivo = 'productos' carga views/productos.php.
        require __DIR__ . '/' . $archivo . '.php';
        ?>
    </main>

    <footer>Docente: José Manuel Villar Ferradal · Recambios Pilatos</footer>
</body>
</html>
