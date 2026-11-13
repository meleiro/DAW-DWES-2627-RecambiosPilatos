# Plan de pruebas

Usar datos ficticios y una base de pruebas para los casos destructivos.

| Caso | Pasos | Resultado esperado |
|---|---|---|
| Conexión | Revisar pantalla Conexión | Base y rol correctos, sin mostrar contraseña |
| Configuración errónea | Cambiar puerto o contraseña local | Error controlado, sin SQL ni credenciales en HTML |
| Alta/edición | Crear recambio y cambiar nombre y precio | Persiste y aparece tras recargar |
| Sin resultados | Buscar texto inexistente | Tabla vacía y recuento 0 |
| Paginación | Recorrer páginas con filtro activo | Filtro conservado y orden estable |
| Límites | precio -1, 0.001 o 100000000; stock negativo | Rechazo del servidor, sin cambios |
| Entradas manipuladas | q[]=x, id[]=1, cantidad no entera | Error controlado, sin warnings visibles |
| SQL | Buscar `' OR 1=1 --` | Texto tratado como valor, sin ampliar resultados |
| HTML | Guardar nombre `<b>prueba</b>` | Muestra etiquetas como texto |
| Duplicado | Repetir referencia o correo | Mensaje útil por SQLSTATE 23505 |
| Integridad | Borrar categoría usada o cliente con pedidos | Mensaje por clave foránea, sin borrar |
| CSRF | Enviar POST sin token o con token cambiado | 403 y datos intactos |
| Métodos | GET a producto_borrar o pedido_estado | 405 y datos intactos |
| Pedido exacto | FRE001 ×2 y FIL001 ×1 | 104,75 €, líneas y stock consistentes |
| Precio manipulado | Añadir campo precio falso al POST | El servidor usa precio de PostgreSQL |
| Rollback | Primera línea con stock, segunda sin stock | Ninguna modificación persiste |
| Concurrencia | Dos procesos compran la última unidad | Solo uno confirma, stock nunca negativo |
| Cancelación | Cancelar un pedido pendiente dos veces | Stock repuesto una vez; segundo intento rechazado |
| Baja lógica | Dar de baja un producto vendido | No se puede pedir, histórico conservado |
| Informe | Categoría sin ventas y pedido cancelado | Categoría visible con cero, cancelado no suma |
| CSV | Válido, duplicado, válido | Dos aceptadas, una rechazada; continúa tras error |
| CSV multilínea | Apellido con salto de línea entre comillas | Un registro CSV; número de registro coherente |
| CSV repetido | Importar el mismo archivo dos veces | UNIQUE evita duplicar correos |
| Sin resultados por ID | Abrir producto con ID inexistente | 404 |

## Concurrencia de verdad

El servidor integrado de PHP suele atender de forma secuencial. Para una prueba concurrente, usa dos procesos CLI conectados a la misma base o dos procesos web independientes; no basta con abrir dos pestañas en el mismo servidor de desarrollo.

Prepara un producto con stock=1. En cada proceso, carga los archivos app necesarios y llama a PedidoService::crear con una unidad. Usa clientes existentes. Después consulta pedidos, lineas_pedido y stock: una operación debe confirmar y la otra fallar por falta de existencias.

## Observaciones

La edición administrativa de productos reemplaza el stock por el valor del formulario. La reserva de pedidos está protegida por bloqueos; el formulario administrativo no incorpora control optimista de versiones. Evitar editar manualmente existencias mientras se hacen pedidos en esta práctica.

Los errores de base se registran con su SQLSTATE, no con campos personales. Una importación excepcionalmente interrumpida puede haber confirmado lotes previos; verificar los correos antes de reintentar.
