<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

/**
 * UsuarioController - Gestiona login, logout y CRUD de usuarios.
 *
 * Usa $_SESSION de PHP nativo (NO el sistema de sesiones de Laravel).
 * Implementa cookies para recordar el usuario y opcionalmente las credenciales.
 *
 * @author  Alumno DWES
 * @version 2.0
 * @date    2024-12-01
 */
class UsuarioController extends Controller
{
    /** @var int Días que se recuerdan las credenciales con "Recordarme". */
    private const DIAS_RECORDAR = 3;

    /**
     * Verifica que el usuario esté autenticado.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario'])) {
            return redirect('login');
        }
        return null;
    }

    /**
     * Verifica que el usuario sea administrador.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function soloAdmin()
    {
        if (($_SESSION['rol'] ?? '') !== 'admin') {
            return redirect('tareas')->with('error', 'Solo los administradores pueden gestionar usuarios.');
        }
        return null;
    }

    /* ================================================================
       LOGIN / LOGOUT
       ================================================================ */

    /**
     * Muestra el formulario de inicio de sesión.
     * Si ya hay sesión activa redirige al listado de tareas.
     * Lee la cookie de usuario recordado para prerellenar el campo.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function loginForm()
    {
        if (isset($_SESSION['usuario'])) {
            return redirect('tareas');
        }

        // Leer cookie de usuario recordado (R4.4)
        $usuarioRecordado = $_COOKIE['ultimo_usuario'] ?? '';

        // Si hay credenciales guardadas en cookie, login automático (R4.5)
        if (isset($_COOKIE['recordar_usuario']) && isset($_COOKIE['recordar_pass'])) {
            $user = Usuario::buscarPorCredenciales(
                $_COOKIE['recordar_usuario'],
                $_COOKIE['recordar_pass']
            );
            if ($user) {
                $_SESSION['usuario']    = $user->usuario;
                $_SESSION['nombre']     = $user->nombre;
                $_SESSION['rol']        = $user->rol;
                $_SESSION['hora_login'] = date('H:i:s');
                return redirect('tareas');
            }
        }

        return view('usuarios.login', compact('usuarioRecordado'));
    }

    /**
     * Procesa el formulario de login.
     * Inicia la sesión PHP nativa, guarda cookie con el nombre de usuario
     * y opcionalmente las credenciales si se marcó "Recordarme".
     *
     * @param  Request $request Petición HTTP con usuario, password y recordar.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $user = Usuario::buscarPorCredenciales(
            $request->usuario,
            $request->password
        );

        if (!$user) {
            // Guardar igualmente el nombre en cookie aunque falle (R4.4)
            setcookie('ultimo_usuario', $request->usuario, time() + (86400 * 30), '/');
            return back()->with('error', 'Usuario o contraseña incorrectos.');
        }

        // Iniciar sesión PHP nativa (R4.1)
        $_SESSION['usuario']    = $user->usuario;
        $_SESSION['nombre']     = $user->nombre;
        $_SESSION['rol']        = $user->rol;
        $_SESSION['hora_login'] = date('H:i:s');

        // Cookie: recordar último usuario en el campo de login (R4.4)
        setcookie('ultimo_usuario', $user->usuario, time() + (86400 * 30), '/');

        // Cookie: recordar credenciales 3 días si se marcó el checkbox (R4.5)
        if ($request->has('recordar') && $request->recordar == '1') {
            $expira = time() + (86400 * self::DIAS_RECORDAR);
            setcookie('recordar_usuario', $user->usuario, $expira, '/');
            setcookie('recordar_pass',    $user->password, $expira, '/');
        } else {
            // Borrar cookies de credenciales si no se marcó
            setcookie('recordar_usuario', '', time() - 3600, '/');
            setcookie('recordar_pass',    '', time() - 3600, '/');
        }

        return redirect('tareas');
    }

    /**
     * Cierra la sesión del usuario actual y borra las cookies de credenciales.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        session_destroy();
        setcookie('recordar_usuario', '', time() - 3600, '/');
        setcookie('recordar_pass',    '', time() - 3600, '/');
        return redirect('login');
    }

    /* ================================================================
       CRUD DE USUARIOS (solo admin) - R4.2
       ================================================================ */

    /**
     * Lista todos los usuarios del sistema.
     * Solo accesible para administradores.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function listar()
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $usuarios = Usuario::todos();
        return view('usuarios.lista', compact('usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     * Solo accesible para administradores.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function crear()
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        return view('usuarios.formulario', ['usuario' => null, 'errores' => []]);
    }

    /**
     * Procesa el formulario y guarda el nuevo usuario en la BD.
     *
     * @param  Request $r Petición HTTP con los datos del usuario.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function guardar(Request $r)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $errores = [];
        if (trim($r->nombre)   === '') $errores[] = 'El nombre es obligatorio.';
        if (trim($r->usuario)  === '') $errores[] = 'El nombre de usuario es obligatorio.';
        if (trim($r->password) === '') $errores[] = 'La contraseña es obligatoria.';
        if (!in_array($r->rol, ['admin', 'operario'])) $errores[] = 'El rol no es válido.';

        if (!empty($errores)) {
            return view('usuarios.formulario', ['usuario' => (object)$r->all(), 'errores' => $errores]);
        }

        Usuario::crear($r->nombre, $r->usuario, $r->password, $r->rol);
        return redirect('usuarios')->with('exito', 'Usuario creado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un usuario existente.
     *
     * @param  int $id ID del usuario a editar.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editar(int $id)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $usuario = Usuario::buscarPorId($id);
        return view('usuarios.formulario', compact('usuario') + ['errores' => []]);
    }

    /**
     * Procesa el formulario de edición y actualiza el usuario.
     *
     * @param  Request $r Petición HTTP con los nuevos datos.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function actualizar(Request $r)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $errores = [];
        if (trim($r->nombre)  === '') $errores[] = 'El nombre es obligatorio.';
        if (trim($r->usuario) === '') $errores[] = 'El nombre de usuario es obligatorio.';
        if (!in_array($r->rol, ['admin', 'operario'])) $errores[] = 'El rol no es válido.';

        if (!empty($errores)) {
            $usuario = (object) $r->all();
            return view('usuarios.formulario', compact('usuario', 'errores'));
        }

        Usuario::actualizar((int)$r->id, $r->nombre, $r->usuario, $r->password ?? '', $r->rol);
        return redirect('usuarios')->with('exito', 'Usuario actualizado correctamente.');
    }

    /**
     * Elimina un usuario de la base de datos.
     * No permite borrar el usuario actualmente autenticado.
     *
     * @param  int $id ID del usuario a eliminar.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function borrar(int $id)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        if ($_SESSION['usuario'] === Usuario::buscarPorId($id)->usuario ?? '') {
            return redirect('usuarios')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        Usuario::borrar($id);
        return redirect('usuarios')->with('exito', 'Usuario eliminado correctamente.');
    }
}
