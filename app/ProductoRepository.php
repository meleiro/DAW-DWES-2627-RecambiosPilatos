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
    public function condiciones(array $f): array
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
            $condiciones[] = 'p.precio >= :min';

            $parametros['min'] = $f['min'];
        }
        
        if ($f['activo'] !== ''){
            $condiciones[] = 'p.activo = :activo';

            $parametros['activo'] = $f['activo'] === '1' ? 'true' : 'false';
        }


        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        return [$where, $parametros];

    }

    /**
     * EJ03 · Listar productos con JOIN, orden y paginación.
     * La vista espera: filas, total, pagina y paginas.
     */
   
public function listar(array $f, int $tam = 5): array
{
    // EJ03:INICIO

    // Comprobar que el tamaño de página sea válido.
    if ($tam < 1) {
        throw new InvalidArgumentException(
            'El tamaño de página debe ser positivo.'
        );
    }

    // 1. OBTENER LAS CONDICIONES Y LOS PARÁMETROS

    [$where, $parametros] = $this->condiciones($f);


    // 2. CONTAR LOS PRODUCTOS

    // Preparar la consulta de recuento.
    $st = $this->pdo->prepare(
        'SELECT COUNT(*) FROM productos p ' . $where
    );

    // Ejecutar la consulta con los filtros.
    $st->execute($parametros);

    // Recuperar el número total de productos.
    $total = (int) $st->fetchColumn();


    // 3. CALCULAR LA PAGINACIÓN

    // Número total de páginas.
    $paginas = max(1, (int) ceil($total / $tam));

    // Ajustar la página solicitada.
    $pagina = max(1, min($f['pagina'], $paginas));

    // Número de registros que debemos saltar.
    $offset = ($pagina - 1) * $tam;


    // 4. PREPARAR LA ORDENACIÓN

    // Lista  de columnas permitidas.
    $columnas = [
        'nombre' => 'p.nombre',
        'precio' => 'p.precio',
        'stock' => 'p.stock',
        'referencia' => 'p.referencia'
    ];


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
