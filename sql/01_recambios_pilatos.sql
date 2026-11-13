-- RECAMBIOS PILATOS - PostgreSQL
-- PASO 1: ejecutar esta sentencia conectado a la base postgres,
-- fuera de un bloque de transaccion. Despues conectar a recambios_pilatos.
-- CREATE DATABASE debe ejecutarse conectado a postgres, sin BEGIN.
-- En Docker, si POSTGRES_DB ya creó la base, NO ejecutar esta sentencia.
CREATE DATABASE recambios_pilatos;

-- PASOS 2-6: ejecutar lo siguiente conectado a recambios_pilatos.
-- En pgAdmin, seleccionar solo desde BEGIN hasta el final del archivo.

BEGIN;
-- Tabla maestra: una categoría puede contener muchos productos.
CREATE TABLE categorias (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
);
-- Catálogo: cada producto pertenece a una categoría (clave foránea).
-- UNIQUE impide referencias duplicadas; CHECK evita precios/stock negativos.
CREATE TABLE productos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    referencia VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio NUMERIC(10,2) NOT NULL CHECK (precio >= 0),
    stock INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
    categoria_id BIGINT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias(id) ON DELETE RESTRICT
);
-- Clientes: el correo es único y fecha_alta tiene valor por defecto.
CREATE TABLE clientes (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    fecha_alta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
-- Cabecera de pedidos: un pedido pertenece a un cliente.
-- CHECK limita los estados posibles; total se guarda como NUMERIC.
CREATE TABLE pedidos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cliente_id BIGINT NOT NULL,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    total NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (total >= 0),
    CONSTRAINT chk_estado_pedido CHECK (estado IN ('pendiente', 'preparando', 'enviado', 'entregado', 'cancelado')),
    CONSTRAINT fk_pedido_cliente FOREIGN KEY (cliente_id)
        REFERENCES clientes(id) ON DELETE RESTRICT
);
-- Detalle: relación entre pedidos y productos con cantidad y precio histórico.
-- ON DELETE CASCADE borra las líneas si se borra el pedido desde SQL.
CREATE TABLE lineas_pedido (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pedido_id BIGINT NOT NULL,
    producto_id BIGINT NOT NULL,
    cantidad INTEGER NOT NULL CHECK (cantidad > 0),
    precio_unitario NUMERIC(10,2) NOT NULL CHECK (precio_unitario >= 0),
    CONSTRAINT fk_linea_pedido FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_linea_producto FOREIGN KEY (producto_id)
        REFERENCES productos(id) ON DELETE RESTRICT
);

-- Datos de prueba: cinco categorías. Ejecutar solo una vez.
INSERT INTO categorias (nombre, descripcion) VALUES
('Frenos', 'Discos, pastillas y componentes de frenado'),
('Filtros', 'Filtros de aceite, aire y combustible'),
('Baterías', 'Baterías para vehículos'),
('Iluminación', 'Bombillas y componentes de iluminación'),
('Motor', 'Componentes y accesorios del motor');

-- Siete productos de ejemplo; categoria_id referencia las categorías anteriores.
INSERT INTO productos (referencia, nombre, descripcion, precio, stock, categoria_id) VALUES
('FRE001', 'Pastillas de freno delanteras', 'Juego de pastillas delanteras', 45.90, 25, 1),
('FRE002', 'Discos de freno ventilados', 'Discos de freno delanteros', 89.50, 12, 1),
('FIL001', 'Filtro de aceite', 'Filtro de aceite para turismo', 12.95, 50, 2),
('FIL002', 'Filtro de aire', 'Filtro de aire de motor', 18.75, 40, 2),
('BAT001', 'Batería 12V 70Ah', 'Batería para automóvil', 119.99, 15, 3),
('ILU001', 'Bombilla H7', 'Bombilla halógena H7', 9.50, 60, 4),
('MOT001', 'Correa de distribución', 'Kit de correa de distribución', 135.00, 8, 5);

-- Tres clientes ficticios para las pruebas de pedidos.
INSERT INTO clientes (nombre, apellidos, email, telefono) VALUES
('Ana', 'García López', 'ana@example.com', '600111222'),
('Pedro', 'Fernández Ruiz', 'pedro@example.com', '600333444'),
('Lucía', 'Martínez Pérez', 'lucia@example.com', '600555666');
COMMIT;

-- Comprobaciones
SELECT * FROM categorias;
SELECT * FROM productos;
SELECT * FROM clientes;
SELECT p.id, p.referencia, p.nombre, c.nombre AS categoria, p.precio, p.stock
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
ORDER BY p.nombre;

-- Usuario de aplicación: sustituir la contraseña de ejemplo.
-- GRANT limita el acceso a SELECT, INSERT, UPDATE y DELETE.
-- Si pilatos_app ya existe, no ejecutar de nuevo CREATE USER.
CREATE USER pilatos_app WITH PASSWORD 'CAMBIAR_POR_UNA_CLAVE_SEGURA';
GRANT CONNECT ON DATABASE recambios_pilatos TO pilatos_app;
GRANT USAGE ON SCHEMA public TO pilatos_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO pilatos_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO pilatos_app;

-- Verificar tablas
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
ORDER BY table_name;
