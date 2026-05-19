<?php

namespace App\Database;

use PDO;
use PDOException;

/**
 * Clase ConexionDB
 * Implementa el patron Singleton para la conexion a la base de datos.
 * Solo se crea una instancia de la conexion durante toda la ejecucion.
 *
 * @author Adrian
 * @date 01/12/2024
 * @version 1.0
 */
class ConexionDB
{
    /** @var ConexionDB Instancia unica de la clase (Singleton) */
    private static $instancia = null;

    /** @var PDO Objeto PDO con la conexion a la base de datos */
    private $pdo;

    /**
     * Constructor privado para que no se pueda instanciar desde fuera.
     * Lee la configuracion del fichero .env de Laravel.
     */
    private function __construct()
    {
        $host      = env('DB_HOST', '127.0.0.1');
        $puerto    = env('DB_PORT', '3306');
        $baseDatos = env('DB_DATABASE', 'gestor_tareas');
        $usuario   = env('DB_USERNAME', 'root');
        $clave     = env('DB_PASSWORD', '');

        $dsn = "mysql:host=$host;port=$puerto;dbname=$baseDatos;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $usuario, $clave);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            // Necesario para que LIMIT y OFFSET funcionen bien con parametros
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            die('Error de conexion: ' . $e->getMessage());
        }
    }

    /**
     * Devuelve la instancia unica de la conexion.
     * Si no existe la crea (Singleton).
     */
    public static function getInstance()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Prepara y ejecuta una consulta SQL con parametros.
     * @param string $sql    Consulta con marcadores ?
     * @param array  $params Valores para los marcadores
     */
    public function ejecutar($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Evitamos que se pueda clonar la instancia */
    private function __clone() {}
}
