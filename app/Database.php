<?php

/*
 * Activamos el modo estricto de tipos de PHP.
 *
 * Con strict_types=1, PHP aplica comprobaciones más estrictas
 * sobre los tipos de los argumentos y valores devueltos.
 *
 * Por ejemplo, nuestra función bd() declara que devuelve PDO,
 * por lo que debe devolver un objeto de esa clase.
 */
declare(strict_types=1);


/**
 * EJ01 · Conexión a PostgreSQL mediante PDO.
 *
 * Esta función será la única puerta de acceso a la base de datos.
 *
 * El objetivo es que las diferentes clases Repository y Service
 * no tengan que crear sus propias conexiones.
 *
 * En su lugar, llamarán a:
 *
 *     bd()
 *
 * y recibirán un objeto PDO preparado para ejecutar SQL.
 *
 * @return PDO Objeto que representa la conexión a PostgreSQL.
 */
function bd(): PDO
{
    // EJ01:INICIO


    /*
     * ============================================================
     * 1. DECLARAR UNA VARIABLE ESTÁTICA
     * ============================================================
     *
     * Una variable local normal se inicializa cada vez que
     * ejecutamos una función.
     *
     * Sin embargo, una variable declarada con static conserva
     * su valor entre las distintas llamadas a esta función
     * durante una misma petición PHP.
     *
     * Inicialmente:
     *
     *     $pdo = null;
     *
     * Esto significa que todavía no tenemos una conexión.
     *
     * Cuando posteriormente guardemos un objeto PDO en esta
     * variable, podremos reutilizarlo.
     *
     * IMPORTANTE:
     *
     * static no significa que la conexión se comparta entre
     * todos los usuarios o entre diferentes peticiones HTTP.
     *
     * Su valor se conserva durante la petición actual.
     */
    static $pdo = null;


    /*
     * ============================================================
     * 2. COMPROBAR SI YA EXISTE UNA CONEXIÓN
     * ============================================================
     *
     * instanceof comprueba si una variable contiene un objeto
     * perteneciente a una determinada clase.
     *
     * En nuestro caso:
     *
     *     $pdo instanceof PDO
     *
     * devuelve true si $pdo contiene un objeto PDO.
     *
     * PRIMERA LLAMADA:
     *
     *     $pdo = null;
     *
     * La condición será false y continuaremos ejecutando
     * el código para crear una conexión.
     *
     * SIGUIENTES LLAMADAS EN LA MISMA PETICIÓN:
     *
     * Si ya hemos guardado una conexión en $pdo,
     * devolveremos directamente ese objeto.
     *
     * De esta forma evitamos abrir conexiones innecesarias.
     */
    if ($pdo instanceof PDO) {

        // Devolvemos la conexión que ya estaba creada.
        return $pdo;

    }


    /*
     * ============================================================
     * 3. CONSTRUIR LA RUTA DEL ARCHIVO DE CONFIGURACIÓN
     * ============================================================
     *
     * __DIR__ es una constante especial de PHP.
     *
     * Contiene la ruta del directorio donde se encuentra
     * el archivo PHP que se está ejecutando.
     *
     * En nuestro proyecto, este archivo está en:
     *
     *     Recambios_Pilatos_Base/app/Database.php
     *
     * Por tanto, __DIR__ representa la carpeta app.
     *
     * dirname() devuelve el directorio padre de una ruta.
     *
     * Así:
     *
     *     dirname(__DIR__)
     *
     * nos lleva a la raíz del proyecto:
     *
     *     Recambios_Pilatos_Base/
     *
     * Finalmente, concatenamos:
     *
     *     /config/database.local.php
     *
     * para obtener la ruta completa del archivo donde
     * guardamos los datos de conexión.
     *
     * Utilizamos el operador punto (.) para concatenar cadenas.
     */
    $ruta = dirname(__DIR__) . '/config/database.local.php';


    /*
     * ============================================================
     * 4. CARGAR LA CONFIGURACIÓN
     * ============================================================
     *
     * is_file($ruta) comprueba si existe un archivo normal
     * en la ruta indicada.
     *
     * Devuelve:
     *
     *     true  -> el archivo existe.
     *     false -> el archivo no existe.
     *
     * Aquí utilizamos el operador ternario:
     *
     *     condición ? valor_si_true : valor_si_false
     *
     * Si el archivo existe:
     *
     *     require $ruta
     *
     * lo ejecuta y recupera el valor que devuelve.
     *
     * Nuestro archivo database.local.php contiene:
     *
     *     return [
     *         'host' => '127.0.0.1',
     *         'port' => '55434',
     *         'database' => 'recambios_pilatos',
     *         'user' => 'pilatos_app',
     *         'password' => '...'
     *     ];
     *
     * Por tanto, require devuelve ese array.
     *
     * Si el archivo no existe, utilizamos un array vacío [].
     *
     * Más adelante comprobaremos si tenemos todos los
     * valores necesarios.
     */
    $config = is_file($ruta) ? require $ruta : [];


    /*
     * ============================================================
     * 5. COMPROBAR QUE LA CONFIGURACIÓN ES UN ARRAY
     * ============================================================
     *
     * is_array() permite comprobar el tipo de una variable.
     *
     * Devuelve true si contiene un array.
     *
     * El operador ! significa negación lógica.
     *
     * Por tanto:
     *
     *     !is_array($config)
     *
     * significa:
     *
     *     "Si $config NO es un array..."
     *
     * En ese caso lanzamos una excepción.
     *
     * RuntimeException es una clase de excepción de PHP.
     *
     * throw interrumpe el flujo normal y comunica
     * que se ha producido un problema.
     */
    if (!is_array($config)) {

        throw new RuntimeException(
            "La configuración de la base de datos no es válida"
        );

    }


    /*
     * ============================================================
     * 6. DEFINIR LAS VARIABLES DE ENTORNO ADMITIDAS
     * ============================================================
     *
     * Creamos un array asociativo.
     *
     * Un array asociativo relaciona claves con valores:
     *
     *     'clave' => 'valor'
     *
     * En nuestro caso:
     *
     * CLAVE:
     *     Nombre utilizado en el array $config.
     *
     * VALOR:
     *     Nombre de la variable de entorno correspondiente.
     *
     * Por ejemplo:
     *
     *     'host' => 'DB_HOST'
     *
     * significa que el campo 'host' puede obtener su valor
     * de la variable de entorno DB_HOST.
     *
     * Esto nos permite configurar la aplicación de dos formas:
     *
     * 1. Mediante config/database.local.php.
     * 2. Mediante variables de entorno.
     *
     * Las variables de entorno tendrán prioridad si existen.
     */
    $variables = [
        'host'     => 'DB_HOST',
        'port'     => 'DB_PORT',
        'database' => 'DB_NAME',
        'user'     => 'DB_USER',
        'password' => 'DB_PASSWORD',
    ];


    /*
     * ============================================================
     * 7. RECORRER LAS VARIABLES DE ENTORNO
     * ============================================================
     *
     * foreach permite recorrer los elementos de un array.
     *
     * Cuando escribimos:
     *
     *     foreach ($variables as $campo => $variable)
     *
     * obtenemos tanto la clave como el valor.
     *
     * PRIMERA ITERACIÓN:
     *
     *     $campo = 'host';
     *     $variable = 'DB_HOST';
     *
     * SEGUNDA ITERACIÓN:
     *
     *     $campo = 'port';
     *     $variable = 'DB_PORT';
     *
     * Y así sucesivamente.
     *
     * getenv() intenta recuperar el valor de una
     * variable de entorno.
     *
     * Por ejemplo:
     *
     *     getenv('DB_HOST')
     *
     * Si existe y vale 127.0.0.1, devolverá esa cadena.
     *
     * Si no existe, devolverá false.
     *
     * Utilizamos !== para comparar estrictamente,
     * comprobando también el tipo del valor.
     *
     * Si la variable existe, sustituimos el valor
     * correspondiente dentro de $config.
     */
    foreach ($variables as $campo => $variable) {

        // Recuperamos el valor de la variable de entorno.
        $valor = getenv($variable);

        // Comprobamos si la variable existe.
        if ($valor !== false) {

            /*
             * Actualizamos el array de configuración.
             *
             * Por ejemplo:
             *
             *     $campo = 'host';
             *     $valor = '127.0.0.1';
             *
             * La asignación será:
             *
             *     $config['host'] = '127.0.0.1';
             *
             * Si ya existía un valor en database.local.php,
             * será sustituido por el valor del entorno.
             */
            $config[$campo] = $valor;

        }

    }


    /*
     * ============================================================
     * 8. VALIDAR LOS DATOS OBLIGATORIOS
     * ============================================================
     *
     * Necesitamos comprobar que tenemos todos los datos:
     *
     *     host
     *     port
     *     database
     *     user
     *     password
     *
     * array_keys() devuelve las claves de un array.
     *
     * En nuestro caso:
     *
     *     array_keys($variables)
     *
     * devuelve:
     *
     *     [
     *         'host',
     *         'port',
     *         'database',
     *         'user',
     *         'password'
     *     ]
     *
     * foreach recorrerá esos cinco nombres.
     */
    foreach (array_keys($variables) as $campo) {

        /*
         * La condición combina dos comprobaciones.
         *
         * PRIMERA:
         *
         *     !isset($config[$campo])
         *
         * isset() comprueba si existe una clave
         * y si su valor no es null.
         *
         * Con ! preguntamos si NO está definida.
         *
         * SEGUNDA:
         *
         *     trim((string) $config[$campo]) === ''
         *
         * (string) convierte el valor a cadena.
         *
         * trim() elimina espacios en blanco al principio
         * y al final.
         *
         * === '' comprueba si el resultado está vacío.
         *
         * El operador || significa OR lógico.
         *
         * Basta con que una de las dos condiciones
         * sea verdadera para lanzar la excepción.
         *
         * PHP utiliza evaluación de cortocircuito:
         * si la primera condición es verdadera,
         * no necesita evaluar la segunda.
         */
        if (
            !isset($config[$campo])
            || trim((string) $config[$campo]) === ''
        ) {

            /*
             * Interrumpimos la ejecución indicando
             * qué dato obligatorio falta.
             *
             * {$campo} permite introducir el valor
             * de una variable dentro de una cadena
             * delimitada por comillas dobles.
             *
             * Por ejemplo:
             *
             *     Falta el dato : password
             */
            throw new RuntimeException(
                "Falta el dato : {$campo}"
            );

        }

    }


    /*
     * ============================================================
     * 9. CONSTRUIR EL DSN
     * ============================================================
     *
     * DSN significa Data Source Name.
     *
     * Es una cadena que indica a PDO cómo localizar
     * la base de datos a la que queremos conectarnos.
     *
     * Para PostgreSQL comienza con:
     *
     *     pgsql:
     *
     * Después añadimos:
     *
     *     host   -> dirección del servidor.
     *     port   -> puerto de conexión.
     *     dbname -> nombre de la base de datos.
     *
     * En nuestro proyecto PHP se ejecuta en Windows,
     * mientras PostgreSQL se ejecuta en Docker.
     *
     * Por eso utilizamos 127.0.0.1 y el puerto
     * publicado por Docker, por ejemplo 55434.
     *
     * El resultado sería:
     *
     * pgsql:host=127.0.0.1;port=55434;dbname=recambios_pilatos
     *
     * Observa que en PHP utilizamos el operador punto (.)
     * para concatenar las distintas partes de la cadena.
     */
    $dsn = 'pgsql:host=' . $config['host']
         . ';port=' . $config['port']
         . ';dbname=' . $config['database'];


    /*
     * ============================================================
     * 10. CREAR LA CONEXIÓN PDO
     * ============================================================
     *
     * PDO significa PHP Data Objects.
     *
     * Es una interfaz de PHP para trabajar con diferentes
     * gestores de bases de datos mediante un conjunto
     * común de métodos.
     *
     * new PDO() crea un objeto que representa
     * una conexión con PostgreSQL.
     *
     * Recibe cuatro argumentos:
     *
     * 1. $dsn:
     *    Cadena con la información del servidor.
     *
     * 2. Usuario:
     *    En nuestro proyecto, pilatos_app.
     *
     * 3. Contraseña:
     *    Contraseña del usuario de aplicación.
     *
     * 4. Opciones:
     *    Array que configura el comportamiento de PDO.
     *
     * Si los datos son incorrectos o el servidor
     * no está disponible, PDO lanzará una excepción.
     */
    $conexion = new PDO(

        // Primer argumento: dirección de la base de datos.
        $dsn,

        // Segundo argumento: usuario de PostgreSQL.
        (string) $config['user'],

        // Tercer argumento: contraseña del usuario.
        (string) $config['password'],

        // Cuarto argumento: opciones de configuración.
        [

            /*
             * PDO::ATTR_ERRMODE
             *
             * Define cómo comunica PDO los errores.
             *
             * ERRMODE_EXCEPTION hace que PDO lance
             * excepciones cuando se producen errores.
             *
             * Por ejemplo:
             *
             * - Una consulta SQL incorrecta.
             * - Una tabla que no existe.
             * - Un problema de permisos.
             */
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,


            /*
             * PDO::ATTR_DEFAULT_FETCH_MODE
             *
             * Define cómo se recuperan las filas
             * obtenidas mediante consultas SELECT.
             *
             * FETCH_ASSOC devuelve arrays asociativos.
             *
             * Por ejemplo:
             *
             * [
             *     'id' => 1,
             *     'nombre' => 'Pastillas de freno'
             * ]
             *
             * Podemos acceder a una columna mediante:
             *
             *     $fila['nombre']
             *
             * en lugar de utilizar su posición numérica.
             */
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,


            /*
             * PDO::ATTR_EMULATE_PREPARES
             *
             * Controla la emulación de las
             * sentencias preparadas.
             *
             * Al establecer false, indicamos que
             * no queremos que PDO emule la preparación
             * de consultas.
             *
             * Con el controlador PostgreSQL, las
             * consultas preparadas pueden gestionarse
             * mediante el protocolo nativo del servidor.
             *
             * Esto facilita separar la estructura SQL
             * de los valores enviados como parámetros.
             *
             * Ejemplo:
             *
             * SELECT * FROM productos WHERE id = :id
             */
            PDO::ATTR_EMULATE_PREPARES => false,

        ]

    );

    $conexion->exec("SET TIME ZONE 'UTC'");
    $conexion->exec("SET client_encoding TO 'UTF8'");


    $pdo = $conexion;

    return $pdo;

    // EJ01:FIN
}
