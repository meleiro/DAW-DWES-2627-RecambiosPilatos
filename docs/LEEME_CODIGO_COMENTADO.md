# Cómo estudiar esta versión base comentada

**Recambios Pilatos · Unidad 5 de DWES**  
Docente: José Manuel Villar Ferradal

Esta entrega mantiene el **mismo proyecto base** y los **12 ejercicios sin resolver**.
Se ha aplicado indentación consistente, se han separado las instrucciones que
antes estaban en una sola línea y se han añadido comentarios didácticos en PHP,
plantillas HTML, CSS, SQL y pruebas.

## Orden sugerido para leer los archivos

1. `public/index.php`: controlador frontal, rutas GET/POST y manejo de errores.
2. `app/bootstrap.php`: sesión, token CSRF y carga de clases.
3. `app/helpers.php`: funciones de escape, validación, redirección y formato.
4. `app/validation.php`: validación de los formularios.
5. `views/layout.php` y `views/inicio.php`: estructura HTML compartida.
6. `config/database.example.php`: configuración externa a `public/`.
7. `app/Database.php`: **EJ01**, primera conexión real con PDO.
8. `app/FichaRepository.php`: **EJ07**, primer SELECT con fetchAll.
9. `app/ProductoRepository.php`: **EJ02–EJ06**, filtros y CRUD.
10. `app/PedidoService.php`: **EJ09–EJ10**, transacciones y stock.
11. `app/InformeRepository.php`: **EJ11**, agregaciones SQL.
12. `app/ImportadorCsv.php`: **EJ12**, lotes y SAVEPOINT.

Los comentarios `TODO` indican las operaciones que debe escribir el alumnado.
Las marcas `EJxx:INICIO` y `EJxx:FIN` delimitan los bloques a completar.

## Cómo arrancar

Desde la raíz de la carpeta descomprimida:

```bash
php -S 127.0.0.1:8000 -t public
```

Abrir `http://127.0.0.1:8000`. La portada funciona antes de resolver EJ01.
Al acceder a una operación pendiente, aparece el aviso correspondiente.

## Si PostgreSQL está en Docker

Copia `config/database.example.php` a `config/database.local.php`.
Con el Docker Compose que utiliza `127.0.0.1:5433:5432`, configura:

```php
'host' => '127.0.0.1',
'port' => '5433',
'database' => 'recambios_pilatos',
'user' => 'pilatos_app',
'password' => 'LA_CLAVE_DE_APP_DB_PASSWORD',
```

No vuelvas a ejecutar el SQL de creación si la base y las tablas ya existen.
No guardes contraseñas reales en archivos que subas a Git.

## Prueba sin base de datos

```bash
php tests/unidades.php
```

La prueba de integración necesita una base de pruebas separada y que los
12 ejercicios estén resueltos. Consulta `docs/PRUEBAS.md`.
