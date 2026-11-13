# Modelo, tipos y consultas

## Relaciones y diccionario resumido

| Tabla | Columnas | Restricciones y significado |
|---|---|---|
| categorias | id BIGINT identity; nombre VARCHAR(100); descripcion TEXT | PK, nombre único, descripción opcional |
| productos | id BIGINT identity; referencia VARCHAR(30); nombre VARCHAR(150); descripcion TEXT; precio NUMERIC(10,2); stock INTEGER; categoria_id BIGINT; activo BOOLEAN | Referencia única, precio y stock >=0, categoría obligatoria, baja lógica con activo=false |
| clientes | id BIGINT identity; nombre VARCHAR(100); apellidos/email VARCHAR(150); telefono VARCHAR(20); fecha_alta TIMESTAMP | Correo único, teléfono opcional, fecha de alta por defecto |
| pedidos | id BIGINT identity; cliente_id BIGINT; fecha TIMESTAMP; estado VARCHAR(20); total NUMERIC(12,2) | Cliente obligatorio, fecha por defecto, estado de lista cerrada, total >=0 |
| lineas_pedido | id BIGINT identity; pedido_id/producto_id BIGINT; cantidad INTEGER; precio_unitario NUMERIC(10,2) | Cantidad >0, precio histórico >=0 |

Categoría 1:N productos. Cliente 1:N pedidos. Pedido 1:N líneas. Producto 1:N líneas. Borrar categoría, cliente o producto con dependencias está restringido. Borrar un pedido desde SQL elimina sus líneas en cascada; la web no ofrece esa operación porque no devolvería stock automáticamente.

BIGINT se recibe como ID entero en PHP de 64 bits. NUMERIC se trata como cadena y se convierte a céntimos para operar. PostgreSQL booleano se normaliza con booleano(). Las fechas son cadenas UTC según la configuración de conexión. NULL y cadena vacía no son idénticos; esta práctica admite ambos en columnas opcionales y los muestra como vacío.

## Consultas por responsabilidad

| Código | Tablas / entrada | Resultado |
|---|---|---|
| bd / conexión | Sin datos del formulario | PDO, current_database, current_user |
| ProductoRepository::listar | productos + categorias; filtros validados, orden permitido, página | COUNT con fetchColumn y página con fetchAll |
| ProductoRepository::buscar | productos; id entero | fetch de una fila o null |
| ProductoRepository::guardar | productos; campos validados | INSERT/UPDATE RETURNING id |
| ProductoRepository::borrar | productos; id, modalidad interna | rowCount; DELETE restringido o activo=false |
| FichaRepository | categorias o clientes de lista cerrada | Consulta, paginación y CRUD |
| PedidoService::crear | pedidos, productos, lineas_pedido; cliente y cantidades | Transacción con stock protegido y total exacto |
| PedidoService::cambiarEstado | pedidos, lineas_pedido, productos | Bloqueo del pedido y devolución de stock al cancelar |
| InformeRepository::ventas | Las cinco tablas excepto clientes | SUM, GROUP BY y LEFT JOIN |
| ImportadorCsv | clientes; registros CSV validados | INSERT preparado reutilizable y puntos de guardado |

## Permisos

El SQL inicial crea pilatos_app con CONNECT, USAGE del esquema, SELECT/INSERT/UPDATE/DELETE de las tablas existentes y USAGE/SELECT de secuencias. No es propietario de las tablas. Las tablas nuevas requieren nuevos GRANT o una política de ALTER DEFAULT PRIVILEGES emitida por el propietario apropiado.

Esquema inicial: sql/01_recambios_pilatos.sql (aplicar una vez). Índices opcionales: sql/02_indices_opcionales.sql. Crear tablas, índices y cambios estructurales corresponde al administrador o al rol de migraciones.
