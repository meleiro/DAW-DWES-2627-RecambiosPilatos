<?php
declare(strict_types=1);

/**
 * Capa de acceso a datos de productos.
 *
 * El controlador valida la petición; este repositorio se encarga del SQL.
 * Los cinco métodos pendientes se completarán en los ejercicios EJ02–EJ06.
 */
final class ProductoRepository
{
    // Inyección de dependencias: recibimos la conexión, no la creamos aquí.
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * EJ02 · Crear filtros SQL reutilizables por el COUNT y el SELECT.
     *
     * @param array $f Filtros previamente validados.
     * @return array Fragmento WHERE y parámetros asociados.
     */
    private function condiciones(array $f): array
    {
    
        $condiciones=[];

        $parametros=[];

        if ($f['q'] !== ''){

        $texto= str_replace(
           ['!', '%', '_'],
           ['!!', '!%', '!_'],
           $f['q']);
       

        $patron = '%'. $texto . '%';

        $condiciones[]= 
           "(p.nombre ILIKE :q_nombre ESCAPE '!'
             OR p.referencia ILIKE :q_referencia ESCAPE '!') ";

        $parametros['q_nombre'] = $patron;
        $parametros['q_referencia'] = $patron;   
        }  

        if ($f['categoria'] !== ''){
            $condiciones[] = "p.categoria_id = :categoria ";

            $parametros['categoria'] = $f['categoria'];

        }

        if($f['max'] !== ''){
            $condiciones[] = 'p.precio <= :max';

            $parametros['max'] = $f['max'];
        }

         if($f['min'] !== ''){
            $condiciones[] = 'p.precio >= :in';

            $parametros['min'] = $f['min'];
        }

        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';





    }

    /**
     * EJ03 · Listar productos con JOIN, orden y paginación.
     * La vista espera: filas, total, pagina y paginas.
     */
    public function listar(array $f, int $tam = 5): array
    {
        // EJ03:INICIO

        // TODO EJ03.1: Obtener WHERE y parámetros de condiciones().
        // TODO EJ03.2: Calcular total con COUNT(*) y fetchColumn().
        // TODO EJ03.3: Calcular LIMIT/OFFSET y páginas.
        // TODO EJ03.4: JOIN con categorias y ordenación por lista blanca.
        // TODO EJ03.5: Devolver las filas con fetchAll().
        // Evitar SELECT * y mantener un ORDER BY estable.

        throw new Pendiente(
            'EJ03: Hacer COUNT, JOIN, lista blanca de ordenación y paginación.'
        );

        // EJ03:FIN
    }

    /** EJ04 · Recuperar un producto por su identificador o devolver null. */
    public function buscar(int $id): ?array
    {
        // EJ04:INICIO

        // TODO EJ04.1: Preparar SELECT ... WHERE id = :id.
        // TODO EJ04.2: Ejecutar con el identificador validado.
        // TODO EJ04.3: Usar fetch() y convertir false en null.

        throw new Pendiente(
            'EJ04: Buscar producto por ID con fetch y devolver null si falta.'
        );

        // EJ04:FIN
    }

    /**
     * EJ05 · Crear o actualizar un producto.
     * @param int|null $id null indica alta; un ID indica edición.
     * @param array $d Datos ya validados por validarProducto().
     * @return int ID del producto insertado o actualizado.
     */
    public function guardar(?int $id, array $d): int
    {
        // EJ05:INICIO

        // TODO EJ05.1: INSERT si $id es null; UPDATE en caso contrario.
        // TODO EJ05.2: Usar parámetros, nunca concatenar valores de $_POST.
        // TODO EJ05.3: Utilizar RETURNING id (propio de PostgreSQL).
        // TODO EJ05.4: Comprobar el caso de identificador inexistente.

        throw new Pendiente(
            'EJ05: Implementar INSERT/UPDATE de productos con RETURNING id.'
        );

        // EJ05:FIN
    }

    /**
     * EJ06 · Borrado lógico o físico.
     * Baja lógica: activo = FALSE. Borrado físico: DELETE FROM productos.
     */
    public function borrar(int $id, bool $logico): bool
    {
        // EJ06:INICIO

        // TODO EJ06.1: Elegir UPDATE o DELETE según $logico.
        // TODO EJ06.2: Preparar la sentencia y enlazar :id.
        // TODO EJ06.3: Devolver true si rowCount() > 0.
        // Si hay pedidos relacionados, PostgreSQL impedirá el DELETE físico.

        throw new Pendiente(
            'EJ06: Baja lógica o DELETE y comprobación de rowCount.'
        );

        // EJ06:FIN
    }
}
