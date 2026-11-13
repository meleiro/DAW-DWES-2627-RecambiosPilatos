-- AMPLIACIÓN: índices y rendimiento en PostgreSQL.
-- Ejecutar conectado a recambios_pilatos, como propietario de las tablas.
-- IF NOT EXISTS permite repetir la creación de estos índices sin error.

BEGIN;

-- Acelera la unión de productos con categorías cuando el catálogo crece.
CREATE INDEX IF NOT EXISTS idx_productos_categoria
    ON productos(categoria_id);

-- Acelera la búsqueda de pedidos por cliente.
CREATE INDEX IF NOT EXISTS idx_pedidos_cliente
    ON pedidos(cliente_id);

-- Índices sobre claves foráneas de la tabla de detalle.
CREATE INDEX IF NOT EXISTS idx_lineas_pedido
    ON lineas_pedido(pedido_id);

CREATE INDEX IF NOT EXISTS idx_lineas_producto
    ON lineas_pedido(producto_id);

-- Ayuda a ordenar productos por nombre e identificador.
CREATE INDEX IF NOT EXISTS idx_productos_nombre_id
    ON productos(nombre, id);

COMMIT;

-- EXPLAIN enseña el plan elegido por PostgreSQL.
-- Con solo siete productos es normal que prefiera un recorrido secuencial.
EXPLAIN
SELECT id, nombre
FROM productos
WHERE categoria_id = 1
ORDER BY nombre, id
LIMIT 5;

-- Un índice B-tree sobre nombre no resuelve por sí solo ILIKE '%texto%'.
