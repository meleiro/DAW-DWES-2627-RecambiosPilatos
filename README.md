# Recambios Pilatos · base para clase (código indentado y comentado)

PHP sin frameworks + PostgreSQL + PDO. Material didáctico de la Unidad 5: Acceso a almacenes de datos desde el servidor.

**Docente: José Manuel Villar Ferradal**

## Versión comentada

Esta edición mantiene los **12 ejercicios EJ01–EJ12 pendientes**. Se han
reformateado los archivos PHP, las vistas y los estilos, y se han añadido
comentarios explicativos sin convertirla en la versión resuelta.
Consulta `docs/LEEME_CODIGO_COMENTADO.md` para estudiar cada archivo.

## Punto de partida

Esta versión contiene la estructura y los formularios, pero deja doce bloques de acceso a datos pendientes (EJ01–EJ12). Arranca en la portada sin conexión. Al entrar en una función pendiente, muestra la actividad que falta. Completa EJ01 y EJ07 primero para abrir los formularios; sigue después el recorrido de docs/GUIA_AULA.md. No incluye las soluciones de esos doce bloques.

La lista siguiente describe el objetivo al completar las actividades.

## Funcionalidades que construirás

- Una puerta de conexión PDO por petición, configuración fuera del directorio público y errores controlados.
- CRUD de productos, categorías y clientes; detalle de producto; baja lógica y borrado físico sujeto a claves foráneas.
- Búsqueda por nombre o referencia, categoría, rango de precios y estado. Ordenación con lista blanca y paginación que conserva filtros.
- Pedidos con cinco filas de formulario, agrupación de productos repetidos, precios tomados de la base, líneas, stock y total en una transacción.
- Bloqueos de filas, control de existencias y cancelación con reposición de stock una sola vez.
- Informe agregado por categoría que mantiene las categorías sin pedidos y excluye cancelados.
- Importación CSV de clientes con lectura por registros, sentencia reutilizada, lotes y SAVEPOINT para errores por fila.
- Vistas HTML escapadas, POST con token CSRF y redirección 303 tras cambios correctos.
- Guía de aula, plan de pruebas, documentación de consultas y ejemplos CSV.

Es un panel local de gestión con datos ficticios. No incluye cuentas de usuarios, login ni autorización. Mantén el servidor enlazado a 127.0.0.1. ORM, NoSQL, caché y operación en producción se plantean como ampliaciones en la guía, sin añadir esas dependencias al proyecto.

## 1. Requisitos

- PHP 8.2 o posterior, de 64 bits, con PDO y `pdo_pgsql`.
- PostgreSQL 15 o posterior y una herramienta como pgAdmin.
- Navegador. No requiere Composer, Node, npm ni JavaScript.

Comprueba en una terminal:

```bash
php -v
php --ini
php -r "print_r(PDO::getAvailableDrivers());"
```

Debe aparecer `pgsql` en la lista. En Windows, edita el php.ini que muestra `php --ini`, revisa `extension_dir` y activa `extension=pdo_pgsql` quitando el punto y coma si lo tiene. Reinicia el servidor PHP. Si falla el inicio de sesión, revisa también que `session.save_path` apunte a una carpeta existente y escribible. La extensión `pgsql` es otra interfaz y no sustituye a `pdo_pgsql`. Si `php` no se reconoce, añade su carpeta al PATH o usa la ruta completa de php.exe.

## 2. Base de datos

Si ya ejecutaste el SQL anterior de Recambios Pilatos, **no recrees la base ni vuelvas a insertar los datos**. La aplicación utiliza exactamente esas cinco tablas y columnas.

Para una instalación nueva, utiliza `sql/01_recambios_pilatos.sql`:

1. Conéctate como administrador a `postgres` y ejecuta solo `CREATE DATABASE recambios_pilatos;`, fuera de una transacción.
2. Abre Query Tool conectado a `recambios_pilatos`.
3. Ejecuta el bloque desde `BEGIN;` hasta `COMMIT;` para crear tablas y datos.
4. Ejecuta los bloques de usuario y permisos, sustituyendo la contraseña de ejemplo. Si el usuario ya existe, omite CREATE USER y revisa sus permisos.
5. Comprueba que existen cinco categorías, siete productos y tres clientes. No hay pedidos iniciales.
6. Opcional: ejecuta `sql/02_indices_opcionales.sql` como propietario de las tablas.

`01` es el script inicial ya entregado y no es una migración repetible. `02` es un cambio adicional con índices creados mediante IF NOT EXISTS. No se necesitan columnas nuevas para usar la web.

## 3. Credenciales

Copia `config/database.example.php` como **`config/database.local.php`** y escribe los mismos datos que usaste en PostgreSQL:

```php
<?php
return [
    'host' => '127.0.0.1',
    'port' => '5432',
    'database' => 'recambios_pilatos',
    'user' => 'pilatos_app',
    'password' => 'TU_CONTRASEÑA_REAL',
];
```

No cambies el nombre de la base en SQL sin cambiarlo también aquí. No uses el administrador para ejecutar la aplicación. El archivo local está excluido de Git y queda fuera de `public/`.

También se admiten `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASS` como variables del entorno del proceso; tienen prioridad sobre el archivo. No se carga un `.env` automáticamente.

## 4. Arranque

Abre una terminal en la carpeta que contiene este README:

```bash
php -S 127.0.0.1:8000 -t public
```

Abre **http://127.0.0.1:8000** y pulsa **Conexión**. La portada funciona incluso antes de configurar la base.

Sirve siempre `public`, no la carpeta raíz. Ahí están el controlador `index.php` y los estilos. El código de datos, SQL, configuración y pruebas queda fuera del acceso HTTP.

## 5. Recorrido de prueba

1. Conexión: comprobar base y usuario.
2. Productos: siete registros en dos páginas de cinco. Buscar `freno` y ordenar por precio.
3. Crear un producto, editarlo, probar una referencia duplicada y un precio negativo.
4. Crear un pedido de 2 unidades de FRE001 y 1 de FIL001: total **104,75 €**. Usa los IDs reales mostrados en el catálogo.
5. Comprobar el descenso del stock. Cambiar a preparando o cancelar. Cancelar restaura las cantidades y no puede repetirse.
6. Intentar borrar físicamente un producto del pedido. PostgreSQL lo impide; darlo de baja conserva el histórico.
7. Importar `ejemplos/clientes.csv` sobre los datos iniciales: dos aceptadas y dos rechazadas (correo duplicado e inválido). Volver a importar producirá más duplicados.

El stock se reserva al crear el pedido. Los estados permitidos son pendiente → preparando → enviado → entregado. Se puede cancelar desde pendiente o preparando. Los pedidos no se borran desde la web.

## Organización

| Carpeta | Responsabilidad |
|---|---|
| `public/` | Entrada HTTP y CSS |
| `app/Database.php` | Conexión PDO |
| `app/*Repository.php` | Consultas de productos, fichas e informes |
| `app/PedidoService.php` | Transacciones y reglas de pedidos |
| `app/ImportadorCsv.php` | Carga por lotes y errores por registro |
| `app/validation.php`, `helpers.php` | Validación, escape, dinero, CSRF y respuestas |
| `views/` | Plantillas sin consultas SQL |
| `config/` | Configuración de conexión |
| `sql/` | Esquema y cambio opcional de índices |
| `docs/` | Recorrido de clase y pruebas |
| `tests/` | Verificaciones ejecutables |

## Pruebas ejecutables

En esta base, las pruebas de integración necesitan que completes los ejercicios. Las pruebas de validación sí funcionan desde el principio.

Sin base de datos:

```bash
php tests/unidades.php
```

Integración: crea **otra base exclusiva de pruebas**, por ejemplo `recambios_pilatos_test`, con las tablas del SQL inicial. Da al usuario de prueba los permisos necesarios sobre ella. El script crea y elimina únicamente sus propios registros QA, pero no debe ejecutarse sobre datos reales.

PowerShell:

```powershell
$env:PILATOS_TEST='1'
$env:DB_NAME='recambios_pilatos_test'
php tests/integracion.php
Remove-Item Env:PILATOS_TEST
Remove-Item Env:DB_NAME
```

macOS/Linux:

```bash
PILATOS_TEST=1 DB_NAME=recambios_pilatos_test php tests/integracion.php
```

Las pruebas heredan el resto de credenciales de tu configuración o entorno. Comprueban altas, lectura, modificación, relaciones, paginación, entradas SQL, cálculo exacto, rollback tras fallo intermedio, cancelación y continuidad del CSV tras duplicados. El plan manual está en `docs/PRUEBAS.md`.

## Decisiones que conviene explicar

- En PostgreSQL se usa `INSERT ... RETURNING id` en lugar de depender del nombre de una secuencia en lastInsertId.
- Los valores recibidos van en parámetros. Solo se concatenan identificadores de listas cerradas y fragmentos SQL internos.
- Los precios NUMERIC llegan como cadenas. PHP opera con céntimos enteros y devuelve cadenas decimales a PostgreSQL.
- `FOR UPDATE` y `UPDATE ... stock >= ...` protegen los pedidos concurrentes. La edición manual de stock sigue siendo una sustitución del valor y se reserva al ejercicio administrativo.
- Las claves únicas y foráneas protegen los datos aunque se escriba desde pgAdmin. Se traducen SQLSTATE de PostgreSQL como 23505 y 23503.
- Las fechas del esquema son TIMESTAMP sin zona horaria. Esta aplicación fija UTC al conectar y muestra UTC. Para ampliar a varias zonas, migrar de forma explícita a TIMESTAMPTZ.
- El informe agrupa las categorías actuales de los productos. Cambiar de categoría un producto cambia su agrupación histórica. La línea conserva el precio, pero no una copia del nombre o categoría.
- El CSV confirma cada lote de 100. Un fallo general posterior no deshace lotes anteriores. El proceso no guarda el archivo subido ni registra datos de clientes en el log.

## Referencias

Material base: Unidad 5, «Acceso a almacenes de datos desde el servidor», 0613 DWES, PROMETEO / thePower, facilitado para esta práctica. Los ejemplos orientados a MySQL se han adaptado a PostgreSQL.

- PHP PDO: https://www.php.net/manual/es/book.pdo.php
- PDOStatement::execute: https://www.php.net/manual/es/pdostatement.execute.php
- PostgreSQL SAVEPOINT: https://www.postgresql.org/docs/17/sql-savepoint.html
- PostgreSQL RETURNING: https://www.postgresql.org/docs/17/dml-returning.html

No incluye IVA, cobros, facturación, gestión de devoluciones ni conexión a servicios externos. La versión resuelta es un ZIP independiente; esta base mantiene los ejercicios pendientes.
