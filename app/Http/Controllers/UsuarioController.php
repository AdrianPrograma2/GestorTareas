<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

/**
 * Controlador de usuarios
 * Gestiona el login, logout y el CRUD de usuarios (solo admin).
 * Usa sesiones PHP nativas y cookies para recordar al usuario.
 *
 * @author Adrian
 * @date 01/12/2024
 * @version 1.0
 */
class UsuarioController extends Controller
{
    /**
     * Comprueba si el usuario ha iniciado sesion.
     */
    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario'])) {
            return redirect('login');
        }
        return null;
    }

    /**
     * Comprueba si el usuario es administrador.
     */
    private function soloAdmin()
    {
        if ($_SESSION['rol'] != 'admin') {
            return redirect('tareas')->with('error', 'Solo los administradores pueden gestionar usuarios.');
        }
        return null;
    }

    // -------------------------------------------------------
    // LOGIN / LOGOUT
    // -------------------------------------------------------

    /**
     * Muestra el formulario de login.
     * Si ya hay sesion activa va directo al listado de tareas.
     * Lee la cookie del ultimo usuario para rellenar el campo.
     */
    public function loginForm()
    {
        // Si ya esta logueado no tiene que volver a entrar
        if (isset($_SESSION['usuario'])) {
            return redirect('tareas');
        }

        // Recuperamos el ultimo usuario de la cookie si existe
        $usuarioRecordado = '';
        if (isset($_COOKIE['ultimo_usuario'])) {
            $usuarioRecordado = $_COOKIE['ultimo_usuario'];
        }

        // Login automatico si tiene las credenciales guardadas en cookie (recordarme 3 dias)
        if (isset($_COOKIE['recordar_usuario']) && isset($_COOKIE['recordar_pass'])) {
            $user = Usuario::buscarPorCredenciales($_COOKIE['recordar_usuario'], $_COOKIE['recordar_pass']);
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
     * Inicia la sesion y guarda cookies segun la opcion elegida.
     * @param Request $request
     */
    public function login(Request $request)
    {
        $user = Usuario::buscarPorCredenciales($request->usuario, $request->password);

        if (!$user) {
            // Aunque falle guardamos el nombre en cookie para recordarlo
            setcookie('ultimo_usuario', $request->usuario, time() + (86400 * 30), '/');
            return back()->with('error', 'Usuario o contrasena incorrectos.');
        }

        // Iniciamos la sesion PHP con los datos del usuario
        $_SESSION['usuario']    = $user->usuario;
        $_SESSION['nombre']     = $user->nombre;
        $_SESSION['rol']        = $user->rol;
        $_SESSION['hora_login'] = date('H:i:s');

        // Cookie para recordar el ultimo usuario en el campo de login
        setcookie('ultimo_usuario', $user->usuario, time() + (86400 * 30), '/');

        // Si marco el checkbox "recordarme" guardamos las credenciales 3 dias
        if ($request->has('recordar') && $request->recordar == '1') {
            $expira = time() + (86400 * 3);
            setcookie('recordar_usuario', $user->usuario, $expira, '/');
            setcookie('recordar_pass',    $user->password, $expira, '/');
        } else {
            // Borramos las cookies de credenciales si no marco el checkbox
            setcookie('recordar_usuario', '', time() - 3600, '/');
            setcookie('recordar_pass',    '', time() - 3600, '/');
        }

        return redirect('tareas');
    }

    /**
     * Cierra la sesion del usuario y borra las cookies.
     */
    public function logout()
    {
        session_destroy();
        setcookie('recordar_usuario', '', time() - 3600, '/');
        setcookie('recordar_pass',    '', time() - 3600, '/');
        return redirect('login');
    }

    // -------------------------------------------------------
    // CRUD DE USUARIOS (solo admin)
    // -------------------------------------------------------

    /**
     * Muestra la lista de todos los usuarios del sistema.
     */
    public function listar()
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $usuarios = Usuario::todos();
        return view('usuarios.lista', compact('usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function crear()
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        return view('usuarios.formulario', ['usuario' => null, 'errores' => []]);
    }

    /**
     * Guarda el nuevo usuario en la base de datos.
     * @param Request $r
     */
    public function guardar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $errores = [];
        if (trim($r->nombre)   == '') $errores[] = 'El nombre es obligatorio.';
        if (trim($r->usuario)  == '') $errores[] = 'El nombre de usuario es obligatorio.';
        if (trim($r->password) == '') $errores[] = 'La contrasena es obligatoria.';
        if ($r->rol != 'admin' && $r->rol != 'operario') $errores[] = 'El rol no es valido.';

        if (!empty($errores)) {
            return view('usuarios.formulario', ['usuario' => (object)$r->all(), 'errores' => $errores]);
        }

        Usuario::crear($r->nombre, $r->usuario, $r->password, $r->rol);
        return redirect('usuarios')->with('exito', 'Usuario creado correctamente.');
    }

    /**
     * Muestra el formulario de edicion de un usuario.
     * @param int $id
     */
    public function editar($id)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $usuario = Usuario::buscarPorId($id);
        return view('usuarios.formulario', ['usuario' => $usuario, 'errores' => []]);
    }

    /**
     * Actualiza los datos del usuario.
     * @param Request $r
     */
    public function actualizar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $errores = [];
        if (trim($r->nombre)  == '') $errores[] = 'El nombre es obligatorio.';
        if (trim($r->usuario) == '') $errores[] = 'El nombre de usuario es obligatorio.';
        if ($r->rol != 'admin' && $r->rol != 'operario') $errores[] = 'El rol no es valido.';

        if (!empty($errores)) {
            $usuario = (object)$r->all();
            return view('usuarios.formulario', compact('usuario', 'errores'));
        }

        Usuario::actualizar((int)$r->id, $r->nombre, $r->usuario, $r->password ?? '', $r->rol);
        return redirect('usuarios')->with('exito', 'Usuario actualizado correctamente.');
    }

    /**
     * Muestra la pagina de confirmacion antes de borrar un usuario.
     * @param int $id
     */
    public function borrar($id)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $usuario = Usuario::buscarPorId($id);

        // No podemos borrar nuestro propio usuario
        if ($usuario && $usuario->usuario == $_SESSION['usuario']) {
            return redirect('usuarios')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        return view('usuarios.confirmar_borrar', compact('usuario'));
    }

    /**
     * Elimina definitivamente el usuario tras la confirmacion del servidor.
     * @param Request $r
     */
    public function borrarConfirmar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        Usuario::borrar((int)$r->id);
        return redirect('usuarios')->with('exito', 'Usuario eliminado correctamente.');
    }
}
