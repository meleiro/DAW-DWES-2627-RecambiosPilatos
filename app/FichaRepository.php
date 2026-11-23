<?php
declare(strict_types=1);

/**
 * Repositorio compartido por dos entidades sencillas: categorías y clientes.
 *
 * Solo se admiten nombres de tabla de una lista cerrada. En PDO podemos
 * parametrizar VALORES, pero no nombres de tablas ni nombres de columnas.
 */
final class FichaRepository
{
    /** @var string[] Columnas autorizadas para la entidad seleccionada. */
    private array $columnas;

    public function __construct(
        private PDO $pdo,     // Conexión inyectada desde el controlador.
        private string $tipo  // 'categorias' o 'clientes'.
    ) {
        // match elige el conjunto de columnas permitido para cada entidad.
        $this->columnas = match ($tipo) {
            'categorias' => ['nombre', 'descripcion'],
            'clientes' => ['nombre', 'apellidos', 'email', 'telefono'],
            default => throw new InvalidArgumentException('Entidad no permitida.'),
        };
    }

    /**
     * EJ07 · Recuperar todos los registros para los desplegables HTML.
     *
     * @return array<int, array<string, mixed>> Filas en arrays asociativos.
     * 
     * 
     * ['nombre', 'descripcion']
     * "nombre, descripcion"
     * 
     */
    public function todos(): array
    {
       $columnas = implode(', ', $this->columnas);

       $sql="SELECT id, {$columnas}
       FROM {$this->tipo} 
       ORDER BY nombre ASC, id ASC
       ";

       $sentencia = $this->pdo->query($sql);

       return $sentencia->fetchAll();

    }

    /**
     * [
     * 
     *   [
     *          'id'=> 1,
     *          'nombre' => 'Frenos',
     *          'descripcion' => 'disbuidisuduisbdiusbuibsiudbisu'
     * 
     * ],
     * []
     * 
     * 
     * ]
     * 
     * 
     * 
     * 
     * Listado paginado de categorías o clientes.
     * Este método ya está resuelto para mostrar un ejemplo de SELECT y OFFSET.
     */
    public function pagina(int $pagina): array
    {
        // COUNT(*) devuelve el número total, no solo el de una página.
        $total = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM {$this->tipo}")
            ->fetchColumn();

        // Cada página contiene diez registros. Siempre existe al menos la 1.
        $paginas = max(1, (int) ceil($total / 10));
        $pagina = min($pagina, $paginas);

        // Las columnas proceden de la lista blanca del constructor.
        $cols = implode(',', $this->columnas);

        $st = $this->pdo->prepare(
            "SELECT id, $cols
             FROM {$this->tipo}
             ORDER BY nombre, id
             LIMIT 10 OFFSET :off"
        );

        // OFFSET se enlaza como entero. Nunca interpolamos el valor del usuario.
        $st->bindValue(':off', ($pagina - 1) * 10, PDO::PARAM_INT);
        $st->execute();

        return [
            'filas' => $st->fetchAll(),
            'total' => $total,
            'pagina' => $pagina,
            'paginas' => $paginas,
        ];
    }

    /** Buscar un registro por su clave primaria. */
    public function buscar(int $id): ?array
    {
        $cols = implode(',', $this->columnas);
        $st = $this->pdo->prepare(
            "SELECT id, $cols FROM {$this->tipo} WHERE id = :id"
        );
        $st->execute(['id' => $id]);

        // fetch() devuelve false si no hay fila; el controlador espera null.
        return $st->fetch() ?: null;
    }

    /**
     * EJ08 · Insertar o actualizar una categoría o un cliente.
     *
     * @param int|null $id null = alta; entero = modificación.
     * @param array $d Datos validados por validarFicha().
     * @return int Identificador del registro guardado.
     */
    public function guardar(?int $id, array $d): int
    {
        // EJ08:INICIO

        // TODO EJ08.1: Usar $this->columnas para generar los campos admitidos.
        // TODO EJ08.2: Preparar INSERT o UPDATE según el valor de $id.
        // TODO EJ08.3: Enlazar los valores con parámetros de PDO.
        // TODO EJ08.4: Recuperar el identificador con RETURNING id.
        // Importante: no concatenar nunca datos de formulario en SQL.

        throw new Pendiente(
            'EJ08: Implementar INSERT/UPDATE de categorías y clientes.'
        );

        // EJ08:FIN
    }

    /**
     * Eliminar un registro. La base puede impedirlo por una clave foránea.
     * DELETE devuelve 0 filas si el identificador no existe.
     */
    public function borrar(int $id): bool
    {
        $st = $this->pdo->prepare(
            "DELETE FROM {$this->tipo} WHERE id = :id"
        );
        $st->execute(['id' => $id]);

        return $st->rowCount() > 0;
    }
}
