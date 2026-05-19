<?php

namespace App\Models;

use App\Database\ConexionDB;

/**
 * Modelo Tarea
 * Gestiona todas las consultas SQL sobre la tabla 'tareas'.
 * Usa el Singleton ConexionDB para acceder a la base de datos.
 *
 * @author Adrian
 * @date 01/12/2024
 * @version 1.0
 */
class Tarea
{
    /**
     * Devuelve todas las tareas ordenadas por fecha de realizacion descendente.
     */
    public static function todas()
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar('SELECT * FROM tareas ORDER BY fecha_realizacion DESC')->fetchAll();
    }

    /**
     * Devuelve solo las tareas con estado Pendiente.
     */
    public static function pendientes()
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar("SELECT * FROM tareas WHERE estado = 'P' ORDER BY fecha_realizacion DESC")->fetchAll();
    }

    /**
     * Devuelve las tareas de una pagina concreta.
     * @param int $pagina    Numero de pagina
     * @param int $porPagina Cuantas tareas por pagina
     */
    public static function paginadas($pagina = 1, $porPagina = 5)
    {
        $db     = ConexionDB::getInstance();
        $offset = ($pagina - 1) * $porPagina;
        return $db->ejecutar(
            'SELECT * FROM tareas ORDER BY fecha_realizacion DESC LIMIT ? OFFSET ?',
            [$porPagina, $offset]
        )->fetchAll();
    }

    /**
     * Cuenta el total de tareas en la base de datos.
     */
    public static function total()
    {
        $db = ConexionDB::getInstance();
        return (int)$db->ejecutar('SELECT COUNT(*) as total FROM tareas')->fetch()->total;
    }

    /**
     * Busca una tarea por su ID.
     * @param int $id
     */
    public static function buscar($id)
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar('SELECT * FROM tareas WHERE id = ?', [$id])->fetch();
    }

    /**
     * Inserta una nueva tarea en la base de datos.
     * @param array $datos Array con los datos del formulario
     */
    public static function crear($datos)
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar(
            'INSERT INTO tareas
             (nif, persona_contacto, telefono, email, descripcion,
              anotaciones_anteriores, direccion, poblacion, cp, provincia,
              estado, fecha_realizacion, operario, fecha_creacion)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())',
            [
                $datos['nif'],
                $datos['persona_contacto'],
                $datos['telefono'],
                $datos['email'],
                $datos['descripcion'],
                $datos['anotaciones_anteriores'],
                $datos['direccion'],
                $datos['poblacion'],
                $datos['cp'],
                $datos['provincia'],
                $datos['estado'],
                $datos['fecha_realizacion'],
                $datos['operario'],
            ]
        );
    }

    /**
     * Actualiza los datos de una tarea existente.
     * @param int   $id    ID de la tarea
     * @param array $datos Nuevos datos
     */
    public static function actualizar($id, $datos)
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar(
            'UPDATE tareas SET
             nif=?, persona_contacto=?, telefono=?, email=?,
             descripcion=?, anotaciones_anteriores=?, anotaciones_posteriores=?,
             direccion=?, poblacion=?, cp=?, provincia=?,
             estado=?, fecha_realizacion=?, operario=?
             WHERE id=?',
            [
                $datos['nif'],
                $datos['persona_contacto'],
                $datos['telefono'],
                $datos['email'],
                $datos['descripcion'],
                $datos['anotaciones_anteriores'],
                $datos['anotaciones_posteriores'] ?? '',
                $datos['direccion'],
                $datos['poblacion'],
                $datos['cp'],
                $datos['provincia'],
                $datos['estado'],
                $datos['fecha_realizacion'],
                $datos['operario'],
                $id,
            ]
        );
    }

    /**
     * Actualiza solo los campos que puede modificar el operario.
     * @param int    $id               ID de la tarea
     * @param string $estado           Nuevo estado
     * @param string $fechaRealizacion Fecha de realizacion
     * @param string $anotacionesPost  Anotaciones del operario
     * @param string $ficheroResumen   Nombre del fichero adjunto (puede ser null)
     */
    public static function completar($id, $estado, $fechaRealizacion, $anotacionesPost, $ficheroResumen = null)
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar(
            'UPDATE tareas SET estado=?, fecha_realizacion=?, anotaciones_posteriores=?, fichero_resumen=? WHERE id=?',
            [$estado, $fechaRealizacion, $anotacionesPost, $ficheroResumen, $id]
        );
    }

    /**
     * Elimina una tarea de la base de datos.
     * @param int $id
     */
    public static function borrar($id)
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar('DELETE FROM tareas WHERE id=?', [$id]);
    }
}
