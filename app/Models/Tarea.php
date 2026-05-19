<?php

namespace App\Models;

use App\Database\ConexionDB;

/**
 * Modelo Tarea - Gestiona todas las operaciones CRUD sobre la tabla 'tareas'.
 *
 * Utiliza el patrón Singleton a través de ConexionDB para acceder
 * a la base de datos sin depender del ORM de Laravel.
 *
 * @author  Alumno DWES
 * @version 1.2
 * @date    2024-12-01
 */
class Tarea
{
    /**
     * Obtiene todas las tareas ordenadas por fecha de realización descendente.
     *
     * @return array Lista de objetos tarea.
     */
    public static function todas(): array
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            'SELECT * FROM tareas ORDER BY fecha_realizacion DESC'
        )->fetchAll();
    }

    /**
     * Obtiene solo las tareas en estado Pendiente (estado = P).
     *
     * @return array Lista de tareas pendientes.
     */
    public static function pendientes(): array
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            "SELECT * FROM tareas WHERE estado = 'P' ORDER BY fecha_realizacion DESC"
        )->fetchAll();
    }

    /**
     * Obtiene las tareas paginadas.
     *
     * @param  int $pagina    Número de página (empieza en 1).
     * @param  int $porPagina Registros por página.
     * @return array          Lista paginada de tareas.
     */
    public static function paginadas(int $pagina = 1, int $porPagina = 5): array
    {
        $db = ConexionDB::getInstance();
        $offset = ($pagina - 1) * $porPagina;
        return $db->ejecutar(
            'SELECT * FROM tareas ORDER BY fecha_realizacion DESC LIMIT ? OFFSET ?',
            [$porPagina, $offset]
        )->fetchAll();
    }

    /**
     * Cuenta el total de tareas en la base de datos.
     *
     * @return int Total de tareas.
     */
    public static function total(): int
    {
        $db = ConexionDB::getInstance();
        return (int) $db->ejecutar('SELECT COUNT(*) as total FROM tareas')
                        ->fetch()->total;
    }

    /**
     * Busca una tarea por su ID.
     *
     * @param  int $id Identificador de la tarea.
     * @return object|false Objeto tarea o false si no existe.
     */
    public static function buscar(int $id)
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            'SELECT * FROM tareas WHERE id = ?', [$id]
        )->fetch();
    }

    /**
     * Inserta una nueva tarea en la base de datos.
     *
     * @param  array $datos Datos de la tarea a insertar.
     * @return bool  True si se insertó correctamente.
     */
    public static function crear(array $datos): bool
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
        return true;
    }

    /**
     * Actualiza los datos de una tarea existente.
     *
     * @param  int   $id    ID de la tarea a actualizar.
     * @param  array $datos Nuevos datos.
     * @return bool  True si se actualizó correctamente.
     */
    public static function actualizar(int $id, array $datos): bool
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
        return true;
    }

    /**
     * Actualiza los campos que puede modificar un operario al completar tarea.
     *
     * @param  int         $id               ID de la tarea.
     * @param  string      $estado           Nuevo estado.
     * @param  string      $fechaRealizacion Fecha de realización.
     * @param  string      $anotacionesPost  Anotaciones del operario.
     * @param  string|null $ficheroResumen   Nombre del fichero guardado.
     * @return bool True si se actualizó correctamente.
     */
    public static function completar(
        int $id,
        string $estado,
        string $fechaRealizacion,
        string $anotacionesPost,
        ?string $ficheroResumen = null
    ): bool {
        $db = ConexionDB::getInstance();
        $db->ejecutar(
            'UPDATE tareas SET estado=?, fecha_realizacion=?,
             anotaciones_posteriores=?, fichero_resumen=?
             WHERE id=?',
            [$estado, $fechaRealizacion, $anotacionesPost, $ficheroResumen, $id]
        );
        return true;
    }

    /**
     * Elimina una tarea de la base de datos.
     *
     * @param  int $id ID de la tarea a eliminar.
     * @return bool True si se eliminó correctamente.
     */
    public static function borrar(int $id): bool
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar('DELETE FROM tareas WHERE id=?', [$id]);
        return true;
    }
}
