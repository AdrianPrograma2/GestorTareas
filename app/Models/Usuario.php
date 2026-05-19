<?php

namespace App\Models;

use App\Database\ConexionDB;

/**
 * Modelo Usuario
 * Gestiona las consultas SQL sobre la tabla 'usuarios'.
 * Usa el Singleton ConexionDB para acceder a la base de datos.
 *
 * @author Adrian
 * @date 01/12/2024
 * @version 1.0
 */
class Usuario
{
    /**
     * Busca un usuario por su nombre de usuario y contrasena.
     * Devuelve el objeto usuario o false si no existe.
     * @param string $usuario
     * @param string $password
     */
    public static function buscarPorCredenciales($usuario, $password)
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            'SELECT * FROM usuarios WHERE usuario = ? AND password = ?',
            [$usuario, $password]
        )->fetch();
    }

    /**
     * Devuelve todos los usuarios ordenados por nombre.
     */
    public static function todos()
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar('SELECT * FROM usuarios ORDER BY nombre')->fetchAll();
    }

    /**
     * Devuelve solo los usuarios con rol administrador.
     */
    public static function admins()
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar("SELECT * FROM usuarios WHERE rol = 'admin' ORDER BY nombre")->fetchAll();
    }

    /**
     * Devuelve solo los usuarios con rol operario.
     */
    public static function operarios()
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar("SELECT * FROM usuarios WHERE rol = 'operario' ORDER BY nombre")->fetchAll();
    }

    /**
     * Busca un usuario por su ID.
     * @param int $id
     */
    public static function buscarPorId($id)
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar('SELECT * FROM usuarios WHERE id = ?', [$id])->fetch();
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param string $nombre
     * @param string $usuario
     * @param string $password
     * @param string $rol
     */
    public static function crear($nombre, $usuario, $password, $rol)
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar(
            'INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?,?,?,?)',
            [$nombre, $usuario, $password, $rol]
        );
    }

    /**
     * Elimina un usuario por su ID.
     * @param int $id
     */
    public static function borrar($id)
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar('DELETE FROM usuarios WHERE id=?', [$id]);
    }

    /**
     * Actualiza los datos de un usuario.
     * Si la contrasena viene vacia no se cambia.
     * @param int    $id
     * @param string $nombre
     * @param string $usuario
     * @param string $password
     * @param string $rol
     */
    public static function actualizar($id, $nombre, $usuario, $password, $rol)
    {
        $db = ConexionDB::getInstance();

        if ($password != '') {
            // Si mando contrasena nueva la actualizamos
            $db->ejecutar(
                'UPDATE usuarios SET nombre=?, usuario=?, password=?, rol=? WHERE id=?',
                [$nombre, $usuario, $password, $rol, $id]
            );
        } else {
            // Si no mando contrasena no la tocamos
            $db->ejecutar(
                'UPDATE usuarios SET nombre=?, usuario=?, rol=? WHERE id=?',
                [$nombre, $usuario, $rol, $id]
            );
        }
    }
}
