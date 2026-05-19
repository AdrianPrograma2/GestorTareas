<?php

namespace App\Database;

use PDO;
use PDOException;

/**
 * Clase ConexionDB - Patrón Singleton para gestionar la conexión PDO a MySQL.
 *
 * Garantiza que solo exista una instancia de la conexión durante toda
 * la ejecución de la aplicación, evitando conexiones redundantes.
 *
 * @author  Alumno DWES
 * @version 1.0
 * @date    2024-12-01
 */
class ConexionDB
{
    /**
     * @var ConexionDB|null Instancia única de la clase (Singleton).
     */
    private static ?ConexionDB $instancia = null;

    /**
     * @var PDO Objeto PDO con la conexión activa a la base de datos.
     */
    private PDO $pdo;

    /**
     * Constructor privado: impide instanciar la clase desde fuera.
     * Lee la configuración de las variables de entorno de Laravel.
     *
     * @throws PDOException Si la conexión a la base de datos falla.
     */
    private function __construct()
    {
        $host     = env('DB_HOST', '127.0.0.1');
        $puerto   = env('DB_PORT', '3306');
        $baseDatos = env('DB_DATABASE', 'gestor_tareas');
        $usuario  = env('DB_USERNAME', 'root');
        $clave    = env('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$puerto};dbname={$baseDatos};charset=utf8mb4";

        $this->pdo = new PDO($dsn, $usuario, $clave, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    /**
     * Devuelve la instancia única de ConexionDB (Singleton).
     * Si no existe, la crea.
     *
     * @return ConexionDB Instancia única.
     */
    public static function getInstance(): ConexionDB
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Devuelve el objeto PDO para ejecutar consultas.
     *
     * @return PDO Conexión activa.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prepara y ejecuta una consulta con parámetros.
     *
     * @param  string $sql    Consulta SQL con marcadores de posición (?).
     * @param  array  $params Parámetros para la consulta preparada.
     * @return \PDOStatement  Statement ejecutado.
     */
    public function ejecutar(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Impide la clonación de la instancia (Singleton).
     */
    private function __clone() {}
}
