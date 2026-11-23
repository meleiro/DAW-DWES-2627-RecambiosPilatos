<?php
declare(strict_types=1);

/**
 * CONTROLADOR FRONTAL (FRONT CONTROLLER)
 *
 * Todas las peticiones web llegan aquí porque el servidor PHP publica
 * exclusivamente la carpeta public/. El parámetro GET 'r' indica la ruta.
 *
 * Responsabilidades:
 * 1. Recibir GET, POST y archivos enviados por formularios.
 * 2. Validar los datos con app/validation.php.
 * 3. Delegar el acceso a PostgreSQL en repositorios y servicios.
 * 4. Seleccionar una vista o redirigir después de un POST correcto.
 *
 * No escribimos consultas de productos aquí: están en sus repositorios.
 */
require dirname(__DIR__) . '/app/bootstrap.php';

try {
    // Ejemplo: index.php?r=productos -> $ruta vale 'productos'.
    // Si falta el parámetro, abrimos la portada.
    $ruta = texto($_GET, 'r', 'inicio');
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // El proyecto de aula solo admite GET y POST.
    if (!in_array($metodo, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        throw new ErrorHttp(405, 'Método no permitido.');
    }

    $post = $metodo === 'POST';

    // El switch actúa como un enrutador sencillo, sin framework.
    switch ($ruta) {
        // ------------------------------------------------------------
        // PORTADA: no requiere acceder a la base de datos.
        // ------------------------------------------------------------
        case 'inicio':
            vista('inicio', ['titulo' => 'Aula de acceso a datos']);
            break;

        // ------------------------------------------------------------
        // CONEXIÓN: primera prueba después de completar EJ01.
        // ------------------------------------------------------------
        case 'conexion':
            // bd() es el ejercicio EJ01 de app/Database.php.
            $st = bd()->query(
                'SELECT current_database() AS base,
                        current_user AS usuario,
                        version() AS version'
            );

            vista('conexion', [
                'titulo' => 'Comprobar conexión',
                'conexion' => $st->fetch(),
            ]);
            break;

        // ------------------------------------------------------------
        // CATÁLOGO: validar filtros, consultar repositorio y dibujar tabla.
        // ------------------------------------------------------------
        case 'productos':
            $f = filtrosProductos($_GET);
            $datos = (new ProductoRepository(bd()))->listar($f);
            $categorias = (new FichaRepository(bd(), 'categorias'))->todos();

            // compact() crea un array con las variables indicadas.
            vista('productos', compact('f', 'datos', 'categorias') + [
                'titulo' => 'Catálogo de recambios',
            ]);
            break;

        // ------------------------------------------------------------
        // DETALLE: consultar un producto concreto o devolver HTTP 404.
        // ------------------------------------------------------------
        case 'producto':
            $id = entero($_GET['id'] ?? '');
            $p = (new ProductoRepository(bd()))->buscar($id);

            if (!$p) {
                throw new ErrorHttp(404, 'Producto inexistente.');
            }

            vista('producto', compact('p') + ['titulo' => $p['nombre']]);
            break;

        // ------------------------------------------------------------
        // FORMULARIO DE PRODUCTO: GET muestra; POST guarda.
        // ------------------------------------------------------------
        case 'producto_form':
            $id = isset($_GET['id']) ? entero($_GET['id']) : null;
            $repo = new ProductoRepository(bd());

            // Si hay ID, recuperamos el producto. Si no, campos vacíos.
            $d = $id
                ? $repo->buscar($id)
                : [
                    'referencia' => '',
                    'nombre' => '',
                    'descripcion' => '',
                    'precio' => '',
                    'stock' => '0',
                    'categoria_id' => '',
                    'activo' => true,
                ];

            if (!$d) {
                throw new ErrorHttp(404, 'Producto inexistente.');
            }

            $errores = [];

            if ($post) {
                // Solo se permite modificar datos con POST y token CSRF.
                exigirPost();
                $d = $_POST;

                try {
                    // Primero validamos; después guardamos con el repositorio.
                    $validado = validarProducto($_POST);
                    $nuevoId = $repo->guardar($id, $validado);

                    flash($id ? 'Producto actualizado.' : 'Producto creado.');
                    redirigir('producto', ['id' => $nuevoId]);
                } catch (ErrorValidacion $e) {
                    // Mostramos el formulario con errores y código HTTP 422.
                    $errores = $e->errores;
                    http_response_code(422);
                } catch (PDOException $e) {
                    // Registrar SQLSTATE, sin mostrar la consulta ni la contraseña.
                    error_log('Producto SQLSTATE=' . $e->getCode());
                    $errores = [mensajeBD($e)];
                    http_response_code(409);
                }
            }

            $categorias = (new FichaRepository(bd(), 'categorias'))->todos();

            vista('producto_form', compact('d', 'id', 'errores', 'categorias') + [
                'titulo' => $id ? 'Editar recambio' : 'Nuevo recambio',
            ]);
            break;

        // ------------------------------------------------------------
        // BAJA LÓGICA O BORRADO FÍSICO: nunca mediante un enlace GET.
        // ------------------------------------------------------------
        case 'producto_borrar':
            exigirPost();
            $id = entero($_POST['id'] ?? '');
            $modo = texto($_POST, 'modo');

            if (!in_array($modo, ['logico', 'fisico'], true)) {
                throw new ErrorHttp(400, 'Operación no válida.');
            }

            $borrado = (new ProductoRepository(bd()))
                ->borrar($id, $modo === 'logico');

            if (!$borrado) {
                throw new ErrorHttp(404, 'Producto inexistente.');
            }

            flash(
                $modo === 'logico'
                    ? 'Producto dado de baja. Su histórico se conserva.'
                    : 'Producto eliminado.'
            );
            redirigir('productos');

        // ------------------------------------------------------------
        // CATEGORÍAS Y CLIENTES: mismo repositorio para ambas entidades.
        // ------------------------------------------------------------
        case 'fichas':
            $tipo = texto($_GET, 'tipo', 'categorias');
            $repo = new FichaRepository(bd(), $tipo);
            $datos = $repo->pagina(entero($_GET['pagina'] ?? 1, 1, 100000));

            vista('fichas', compact('datos', 'tipo') + [
                'titulo' => ucfirst($tipo),
            ]);
            break;

        // ------------------------------------------------------------
        // ALTA Y EDICIÓN DE CATEGORÍAS/CLIENTES.
        // ------------------------------------------------------------
        case 'ficha_form':
            $tipo = texto($_GET, 'tipo', 'categorias');
            $repo = new FichaRepository(bd(), $tipo);
            $id = isset($_GET['id']) ? entero($_GET['id']) : null;
            $d = $id ? $repo->buscar($id) : [];
            $errores = [];

            if ($id && !$d) {
                throw new ErrorHttp(404, 'Registro inexistente.');
            }

            if ($post) {
                exigirPost();
                $d = $_POST;

                try {
                    $repo->guardar($id, validarFicha($tipo, $d));
                    flash('Registro guardado.');
                    redirigir('fichas', ['tipo' => $tipo]);
                } catch (ErrorValidacion $e) {
                    $errores = $e->errores;
                    http_response_code(422);
                } catch (PDOException $e) {
                    error_log('Ficha SQLSTATE=' . $e->getCode());
                    $errores = [mensajeBD($e)];
                    http_response_code(409);
                }
            }

            vista('ficha_form', compact('tipo', 'id', 'd', 'errores') + [
                'titulo' => ($id ? 'Editar ' : 'Crear ')
                    . ($tipo === 'clientes' ? 'cliente' : 'categoría'),
            ]);
            break;

        // ------------------------------------------------------------
        // ELIMINAR UNA CATEGORÍA O CLIENTE.
        // ------------------------------------------------------------
        case 'ficha_borrar':
            exigirPost();
            $tipo = texto($_POST, 'tipo');
            $repo = new FichaRepository(bd(), $tipo);

            if (!$repo->borrar(entero($_POST['id'] ?? ''))) {
                throw new ErrorHttp(404, 'Registro inexistente.');
            }

            flash('Registro eliminado.');
            redirigir('fichas', ['tipo' => $tipo]);

        // ------------------------------------------------------------
        // LISTADO DE PEDIDOS.
        // ------------------------------------------------------------
        case 'pedidos':
            $datos = (new PedidoService(bd()))
                ->listar(entero($_GET['pagina'] ?? 1, 1, 100000));

            vista('pedidos', compact('datos') + ['titulo' => 'Pedidos']);
            break;

        // ------------------------------------------------------------
        // NUEVO PEDIDO: procesar arrays producto_id[] y cantidad[].
        // ------------------------------------------------------------
        case 'pedido_form':
            $errores = [];
            $d = $post ? $_POST : [];

            if ($post) {
                exigirPost();

                try {
                    $cliente = entero($_POST['cliente_id'] ?? '');
                    $ids = $_POST['producto_id'] ?? [];
                    $cantidades = $_POST['cantidad'] ?? [];

                    if (
                        !is_array($ids)
                        || !is_array($cantidades)
                        || count($ids) > 5
                    ) {
                        throw new ErrorValidacion([
                            'lineas' => 'Formato de líneas no válido.',
                        ]);
                    }

                    $lineas = [];

                    foreach ($ids as $i => $producto) {
                        if (!is_string($producto)) {
                            throw new ErrorValidacion([
                                'producto' => 'Identificador no válido.',
                            ]);
                        }

                        // Las filas sin identificador de producto se ignoran.
                        if (trim($producto) === '') {
                            continue;
                        }

                        $lineas[] = [
                            'producto_id' => $producto,
                            'cantidad' => $cantidades[$i] ?? '',
                        ];
                    }

                    // El servicio implementará la transacción completa (EJ09).
                    $id = (new PedidoService(bd()))->crear($cliente, $lineas);
                    flash('Pedido registrado y existencias descontadas.');
                    redirigir('pedido', ['id' => $id]);
                } catch (ErrorValidacion $e) {
                    $errores = $e->errores;
                    http_response_code(422);
                } catch (PDOException $e) {
                    error_log('Pedido SQLSTATE=' . $e->getCode());
                    $errores = [mensajeBD($e)];
                    http_response_code(409);
                }
            }

            $clientes = (new FichaRepository(bd(), 'clientes'))->todos();

            vista('pedido_form', compact('clientes', 'errores', 'd') + [
                'titulo' => 'Crear pedido',
            ]);
            break;

        // ------------------------------------------------------------
        // VER UN PEDIDO Y SUS LÍNEAS.
        // ------------------------------------------------------------
        case 'pedido':
            $id = entero($_GET['id'] ?? '');
            $pedido = (new PedidoService(bd()))->detalle($id);

            if (!$pedido) {
                throw new ErrorHttp(404, 'Pedido inexistente.');
            }

            vista('pedido', compact('pedido') + ['titulo' => "Pedido #$id"]);
            break;

        // ------------------------------------------------------------
        // CAMBIAR ESTADO: EJ10 valida la transición y la reposición de stock.
        // ------------------------------------------------------------
        case 'pedido_estado':
            exigirPost();
            $id = entero($_POST['id'] ?? '');

            (new PedidoService(bd()))
                ->cambiarEstado($id, texto($_POST, 'estado'));

            flash('Estado actualizado.');
            redirigir('pedido', ['id' => $id]);

        // ------------------------------------------------------------
        // INFORME: la agregación SQL está pendiente en EJ11.
        // ------------------------------------------------------------
        case 'informe':
            $filas = (new InformeRepository(bd()))->ventas();

            vista('informe', compact('filas') + [
                'titulo' => 'Importes de pedidos por categoría',
            ]);
            break;

        // ------------------------------------------------------------
        // IMPORTACIÓN CSV: el formulario usa multipart/form-data.
        // ------------------------------------------------------------
        case 'importar':
            $resultado = null;

            if ($post) {
                exigirPost();

                // PHP informa del resultado de la subida en $_FILES['csv'].
                if (($_FILES['csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    throw new ErrorValidacion([
                        'csv' => 'No se pudo recibir el CSV. Máximo 1 MB.',
                    ]);
                }

                $f = $_FILES['csv'];

                // El límite y is_uploaded_file se comprueban en el servidor.
                if (
                    ($f['size'] ?? 0) > 1048576
                    || !is_string($f['tmp_name'])
                    || !is_uploaded_file($f['tmp_name'])
                ) {
                    throw new ErrorValidacion([
                        'csv' => 'Archivo no válido o superior a 1 MB.',
                    ]);
                }

                // EJ12 implementará la lectura del CSV y los SAVEPOINT.
                $resultado = (new ImportadorCsv(bd()))->importar($f['tmp_name']);

                // El resumen se conserva una petición para mostrarlo tras PRG.
                $_SESSION['importacion'] = $resultado;
                flash('Importación terminada.');
                redirigir('importar');
            }

            $resultado = $_SESSION['importacion'] ?? null;
            unset($_SESSION['importacion']);

            vista('importar', compact('resultado') + [
                'titulo' => 'Importar clientes desde CSV',
            ]);
            break;

        // No existe una ruta con ese nombre.
        default:
            throw new ErrorHttp(404, 'Página inexistente.');
    }
} catch (Pendiente $e) {
    // Los ejercicios sin resolver muestran una pantalla informativa.
    error_log((string) $e);
    http_response_code(501);
    vista('error', [
        'titulo' => 'Actividad por completar',
        'mensaje' => $e->getMessage(),
    ]);
} catch (ErrorHttp $e) {
    error_log((string) $e);
    // Por ejemplo: 404 no encontrado, 403 CSRF o 405 método incorrecto.
    http_response_code($e->estado);
    vista('error', [
        'titulo' => 'No se puede completar la acción',
        'mensaje' => $e->getMessage(),
    ]);
} catch (ErrorValidacion $e) {
    error_log((string) $e);
    http_response_code(422);
    vista('error', [
        'titulo' => 'Revisa los datos',
        'mensaje' => implode(' ', $e->errores),
    ]);
} catch (InvalidArgumentException $e) {
    error_log((string) $e);
    http_response_code(400);
    vista('error', [
        'titulo' => 'Petición no válida',
        'mensaje' => 'Entidad no permitida.',
    ]);
} catch (PDOException $e) {
    error_log((string) $e);
    // SQLSTATE de PostgreSQL: 23505 UNIQUE, 23503 FK, 23514 CHECK.
    error_log('Recambios Pilatos SQLSTATE=' . $e->getCode());
    $estado = in_array((string) $e->getCode(), ['23505', '23503', '23514'], true)
        ? 409
        : 500;

    http_response_code($estado);
    vista('error', [
        'titulo' => 'Operación pendiente',
        'mensaje' => mensajeBD($e),
    ]);
} catch (Throwable $e) {
    error_log((string) $e);
    // No revelar consultas, credenciales, rutas internas ni datos personales.
    error_log('Recambios Pilatos: ' . get_class($e) . ': ' . $e->getMessage());
    http_response_code(500);

    vista('error', [
        'titulo' => 'No se pudo continuar',
        'mensaje' => 'Comprueba PHP, pdo_pgsql y config/database.local.php. Consulta el registro local del servidor.',
    ]);
}
