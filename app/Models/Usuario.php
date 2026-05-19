<?php

namespace App\Models;

use App\Database\ConexionDB;

/**
 * Modelo Usuario - Gestiona las operaciones CRUD sobre la tabla 'usuarios'.
 *
 * Utiliza el patrón Singleton (ConexionDB) para el acceso a datos.
 * No usa el ORM de Laravel ni su sistema de autenticación.
 *
 * @author  Alumno DWES
 * @version 1.2
 * @date    2024-12-01
 */
class Usuario
{
    /**
     * Busca un usuario por sus credenciales (usuario y contraseña).
     *
     * @param  string $usuario  Nombre de usuario.
     * @param  string $password Contraseña en texto plano.
     * @return object|false Objeto usuario o false si no existe.
     */
    public static function buscarPorCredenciales(string $usuario, string $password)
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            'SELECT * FROM usuarios WHERE usuario = ? AND password = ?',
            [$usuario, $password]
        )->fetch();
    }

    /**
     * Obtiene todos los usuarios registrados en el sistema.
     *
     * @return array Lista de objetos usuario.
     */
    public static function todos(): array
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar('SELECT * FROM usuarios ORDER BY nombre')->fetchAll();
    }

    /**
     * Obtiene solo los usuarios con rol de administrador.
     *
     * @return array Lista de administradores.
     */
    public static function admins(): array
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            "SELECT * FROM usuarios WHERE rol = 'admin' ORDER BY nombre"
        )->fetchAll();
    }

    /**
     * Obtiene solo los usuarios con rol de operario.
     *
     * @return array Lista de operarios.
     */
    public static function operarios(): array
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            "SELECT * FROM usuarios WHERE rol = 'operario' ORDER BY nombre"
        )->fetchAll();
    }

    /**
     * Busca un usuario por su ID.
     *
     * @param  int $id ID del usuario.
     * @return object|false Objeto usuario o false si no existe.
     */
    public static function buscarPorId(int $id)
    {
        $db = ConexionDB::getInstance();
        return $db->ejecutar(
            'SELECT * FROM usuarios WHERE id = ?', [$id]
        )->fetch();
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     *
     * @param  string $nombre   Nombre completo.
     * @param  string $usuario  Nombre de usuario (login).
     * @param  string $password Contraseña en texto plano.
     * @param  string $rol      Rol del usuario: 'admin' u 'operario'.
     * @return bool True si se creó correctamente.
     */
    public static function crear(string $nombre, string $usuario, string $password, string $rol): bool
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar(
            'INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?,?,?,?)',
            [$nombre, $usuario, $password, $rol]
        );
        return true;
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param  int $id ID del usuario a eliminar.
     * @return bool True si se eliminó correctamente.
     */
    public static function borrar(int $id): bool
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar('DELETE FROM usuarios WHERE id=?', [$id]);
        return true;
    }

    /**
     * Actualiza el nombre, usuario y contraseña de un usuario existente.
     *
     * @param  int    $id       ID del usuario.
     * @param  string $nombre   Nuevo nombre.
     * @param  string $usuario  Nuevo nombre de usuario.
     * @param  string $password Nueva contraseña.
     * @param  string $rol      Nuevo rol.
     * @return bool True si se actualizó correctamente.
     */
    public static function actualizar(int $id, string $nombre, string $usuario, string $password, string $rol): bool
    {
        $db = ConexionDB::getInstance();
        $db->ejecutar(
            'UPDATE usuarios SET nombre=?, usuario=?, password=?, rol=? WHERE id=?',
            [$nombre, $usuario, $password, $rol, $id]
        );
        return true;
    }
}
