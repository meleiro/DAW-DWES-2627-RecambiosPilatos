# Recorrido de aula · Recambios Pilatos

Docente: José Manuel Villar Ferradal

La versión base y la resuelta tienen la misma estructura. En la base, doce bloques delimitados `EJxx:INICIO` / `EJxx:FIN` contienen una excepción `Pendiente`. Esa excepción permite abrir la portada y navegar sin errores de sintaxis. Al llegar a un bloque pendiente, la web indica qué actividad falta. No devuelve listas vacías simulando datos ni afirma haber guardado registros.

La base incluye las firmas, las vistas, el controlador frontal, el CSS, los ayudantes de seguridad y las validaciones. El alumnado implementa el acceso a datos. No hay una copia oculta de las soluciones dentro de la base.

## Secuencia sugerida: seis sesiones de unas dos horas

El orden de ejercicios puede variar. Para abrir los formularios de productos hacen falta EJ01 y EJ07 (categorías del desplegable). Se recomienda completar EJ07 justo después de EJ01.

| Sesión | Archivos / ejercicios | Resultado comprobable |
|---|---|---|
| 1 | Configuración, `Database.php` EJ01, `FichaRepository.php` EJ07 | Portada y conexión real, categorías recuperadas |
| 2 | `ProductoRepository.php` EJ02 y EJ03 | WHERE con parámetros, JOIN, recuento y paginación |
| 3 | `ProductoRepository.php` EJ04 y EJ05 | Detalle, alta y edición con RETURNING |
| 4 | `ProductoRepository.php` EJ06 y `FichaRepository.php` EJ08 | Baja lógica, borrado físico y formularios de clientes/categorías |
| 5 | `PedidoService.php` EJ09 y EJ10 | Pedido atómico, bloqueo de filas y cancelación |
| 6 | `InformeRepository.php` EJ11, `ImportadorCsv.php` EJ12 | Informe agregado, importación y pruebas |

## EJ01 · Conexión PDO

Firma: `bd(): PDO` en `app/Database.php`.

Leer la configuración local o las variables del entorno, construir un DSN `pgsql`, configurar excepciones, arrays asociativos y preparaciones nativas. Reutilizar la conexión con una variable static. Fijar UTC. No devolver credenciales ni mensajes SQL al navegador.

Pruebas: Conexión correcta, contraseña incorrecta, puerto incorrecto y extensión desactivada. Comparar el log local y la respuesta web.

## EJ07 · Primer SELECT y desplegable

Firma: `FichaRepository::todos(): array`.

Usar la entidad y columnas ya validadas por el constructor. Ejecutar SELECT de columnas explícitas, ordenar por nombre e ID y devolver `fetchAll()`. La UI utiliza el resultado en los desplegables de categoría y cliente. Es un listado auxiliar pensado para el volumen pequeño de la práctica; en un catálogo grande se sustituiría por autocompletado paginado.

Prueba: deben aparecer las cinco categorías iniciales al crear un producto.

## EJ02 · Condiciones y parámetros

Firma: `ProductoRepository::condiciones(array $f): array`.

Devolver dos elementos: el fragmento WHERE y un array de parámetros. Los filtros posibles están validados por `filtrosProductos()`: q, categoria, min, max y activo. La búsqueda compara nombre o referencia mediante ILIKE. Usar nombres distintos para los dos marcadores del texto. Escapar `%`, `_` y la barra inversa si se desea buscar texto literal. La categoría, rango y estado se combinan con AND.

Prueba: combinar Frenos, precio máximo 50 y activos; revisar por qué COUNT y listado necesitan exactamente el mismo WHERE.

## EJ03 · Listado paginado

Firma: `ProductoRepository::listar(array $f, int $tam = 5): array`.

La vista espera: `filas`, `total`, `pagina` y `paginas`.

Hacer un COUNT con fetchColumn. Calcular páginas y ajustar la solicitada. Unir productos y categorías. Utilizar una lista blanca para nombre, referencia, precio y stock. Añadir el ID como desempate para mantener un orden estable. Enlazar LIMIT y OFFSET con PARAM_INT. Devolver fetchAll de la página, no de todos los productos.

Pruebas: siete productos, páginas de cinco, dos páginas. Filtros sin resultados. Orden ascendente/descendente. Probar un valor de orden inventado y comprobar que solo se usa una columna permitida.

## EJ04 · Buscar por ID

Firma: `ProductoRepository::buscar(int $id): ?array`.

Usar prepare/execute, fetch y devolver null cuando no existe. El controlador ya transforma null en 404.

Pruebas: ID existente, ID válido inexistente y petición con id[]=1.

## EJ05 · Crear y editar

Firma: `ProductoRepository::guardar(?int $id, array $d): int`.

Si id es null, INSERT. Si contiene ID, UPDATE con WHERE. En ambos casos, RETURNING id y fetchColumn. Convertir activo a un valor inequívoco de PostgreSQL. El precio ya llega como cadena decimal y stock como entero. No interpolar los campos del formulario en el SQL.

Pruebas: alta correcta, edición, duplicado de referencia y categoría inexistente. Actualizar un ID que ya se ha borrado debe acabar en 404.

## EJ06 · Borrado y baja lógica

Firma: `ProductoRepository::borrar(int $id, bool $logico): bool`.

Ejecutar UPDATE activo=FALSE o DELETE físico, según la opción validada. Devolver si rowCount es mayor que cero. No capturar la clave foránea como un éxito. El controlador exige POST y CSRF.

Pruebas: producto libre, producto con pedido y reenvío con ID inexistente. El histórico debe conservarse con baja lógica.

## EJ08 · CRUD de fichas

Firma: `FichaRepository::guardar(?int $id, array $d): int`.

El constructor limita las tablas a categorias y clientes. Usar exclusivamente los nombres de columnas de esa lista y marcadores para los valores. Implementar INSERT/UPDATE RETURNING. Los métodos de paginación, búsqueda y borrado se incluyen como ejemplos resueltos en la base.

Pruebas: correo duplicado, categoría con productos y cliente con pedidos. Explicar por qué los nombres de tabla no se pueden enlazar como valores PDO.

## EJ09 · Pedido completo con transacción

Firma: `PedidoService::crear(int $cliente, array $lineas): int`.

1. Validar y agrupar cantidades repetidas. Ordenar los IDs.
2. beginTransaction.
3. Insertar cabecera y recuperar ID.
4. Leer productos con FOR UPDATE y comprobar activo.
5. Descontar existencias con condición stock >= cantidad y comprobar rowCount.
6. Insertar las líneas con el precio leído de PostgreSQL.
7. Acumular en céntimos y actualizar total.
8. commit. Ante cualquier Throwable, rollBack si hay transacción y relanzar.

No confiar en precios enviados por el navegador. El formulario envía únicamente cliente, producto y cantidad. No utilizar floats para acumular el dinero.

Prueba clave: primera línea válida y segunda sin existencias. No debe persistir la cabecera ni descontarse el stock de la primera.

## EJ10 · Estados y cancelación

Firma: `PedidoService::cambiarEstado(int $id, string $nuevo): void`.

Bloquear el pedido durante la transición. Aplicar solo la secuencia prevista y, al cancelar, devolver stock de todas las líneas en la misma transacción. Un estado cancelado no admite una segunda cancelación.

Prueba: dos peticiones de cancelación concurrentes no deben duplicar el stock. Tras enviado solo se permite entregado.

## EJ11 · Informe agregado

Firma: `InformeRepository::ventas(): array`.

Unir categorías, productos, líneas y pedidos, mantener categorías sin ventas y excluir cancelados del importe. Agrupar por categoría, sumar cantidades e importes y sustituir NULL por cero con COALESCE. Las condiciones de un LEFT JOIN requieren especial atención: llevar filtros a WHERE puede eliminar las filas sin coincidencias.

Actividad: añadir un filtro por fecha. Añadir HAVING para mostrar solo categorías con importe superior a 100. Comparar con WHERE y explicar cuándo actúa cada uno.

## EJ12 · Importación CSV y recuperación tras error

Firma: `ImportadorCsv::importar(string $ruta): array`.

El resultado esperado es `aceptadas`, `rechazadas` y `errores` (cada error incluye registro y motivo). Validar cabecera, leer con fgetcsv y preparar un único INSERT. Procesar lotes de 100. Dentro de cada lote usar SAVEPOINT por registro, ROLLBACK TO SAVEPOINT al fallar y RELEASE al terminar.

En PostgreSQL no basta capturar PDOException y seguir insertando: una sentencia fallida deja la transacción abortada. El punto de guardado permite recuperar el lote. Es una adaptación necesaria respecto a ejemplos que se apoyan en otros gestores.

Prueba: válido, duplicado y otro válido. El tercero debe entrar. Diferenciar esta confirmación parcial por lotes de la atomicidad total de un pedido.

## Conceptos que ya están resueltos en la base

HTML/CSS, POST/Redirect/GET, mensajes flash, sesiones y CSRF, escape HTML, validaciones de servidor, dinero en céntimos y manejo de errores. Revisarlos sin mezclarlos con la escritura de las consultas facilita la progresión desde ELECTROLATINO.

## Ampliaciones del tema

- Mapeo: crear una clase Producto que reciba la fila del repositorio. Comparar data mapper con active record. No introducir un ORM antes de entender el SQL.
- ORM: reproducir la consulta con Doctrine y revisar el SQL generado y el problema N+1.
- Rendimiento: usar el script de índices, EXPLAIN y más datos ficticios. Comparar OFFSET con paginación por ID. El `ILIKE '%texto%'` no aprovecha un B-tree común.
- Migraciones: registrar cambios por versiones, separando usuario de migraciones y usuario de aplicación. No recrear tablas para actualizar el proyecto.
- Copias: `pg_dump -h 127.0.0.1 -U pilatos_app -d recambios_pilatos -Fc -f pilatos.dump`. Restaurar con pg_restore a una base aparte usando un rol con permisos apropiados. Evitar poner la contraseña en el comando. Comprobar recuentos y funcionamiento tras restaurar.
- Redis/API/NoSQL: diseñar una caché de informes con caducidad e invalidación. Analizar qué datos deben seguir en PostgreSQL. No hay conexión externa incorporada.
- Producción: sustituir el servidor integrado, añadir login y permisos por recurso, registrar errores sin datos personales y probar recuperación de copias. Las colisiones al editar productos requieren control de versión si se amplía a varios administradores.
