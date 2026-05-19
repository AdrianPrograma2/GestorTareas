<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarea;
use App\Models\Usuario;

/**
 * Controlador de tareas
 * Gestiona el CRUD de tareas: listar, ver, crear, editar, completar y borrar.
 *
 * @author Adrian
 * @date 01/12/2024
 * @version 1.0
 */
class TareaController extends Controller
{
    /**
     * Comprueba si el usuario ha iniciado sesion.
     * Si no lo ha hecho redirige al login.
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
     * Si no lo es redirige al listado con un error.
     */
    private function soloAdmin()
    {
        if ($_SESSION['rol'] != 'admin') {
            return redirect('tareas')->with('error', 'Solo los administradores pueden hacer esto.');
        }
        return null;
    }

    /**
     * Devuelve el array de provincias con su codigo INE.
     */
    private function provincias()
    {
        return [
            ''   => '-- Selecciona provincia --',
            '02' => 'Albacete',      '03' => 'Alicante',
            '04' => 'Almeria',       '05' => 'Avila',
            '06' => 'Badajoz',       '07' => 'Baleares',
            '08' => 'Barcelona',     '09' => 'Burgos',
            '10' => 'Caceres',       '11' => 'Cadiz',
            '12' => 'Castellon',     '13' => 'Ciudad Real',
            '14' => 'Cordoba',       '15' => 'A Coruna',
            '16' => 'Cuenca',        '17' => 'Girona',
            '18' => 'Granada',       '19' => 'Guadalajara',
            '20' => 'Guipuzcoa',     '21' => 'Huelva',
            '22' => 'Huesca',        '23' => 'Jaen',
            '24' => 'Leon',          '25' => 'Lleida',
            '26' => 'La Rioja',      '27' => 'Lugo',
            '28' => 'Madrid',        '29' => 'Malaga',
            '30' => 'Murcia',        '31' => 'Navarra',
            '32' => 'Ourense',       '33' => 'Asturias',
            '34' => 'Palencia',      '35' => 'Las Palmas',
            '36' => 'Pontevedra',    '37' => 'Salamanca',
            '38' => 'S.C. Tenerife', '39' => 'Cantabria',
            '40' => 'Segovia',       '41' => 'Sevilla',
            '42' => 'Soria',         '43' => 'Tarragona',
            '44' => 'Teruel',        '45' => 'Toledo',
            '46' => 'Valencia',      '47' => 'Valladolid',
            '48' => 'Vizcaya',       '49' => 'Zamora',
            '50' => 'Zaragoza',      '51' => 'Ceuta',
            '52' => 'Melilla',
        ];
    }

    /**
     * Valida el NIF, NIE o CIF con expresiones regulares.
     * @param string $valor
     */
    private function validarNifCif($valor)
    {
        // NIF: 8 digitos + 1 letra
        if (preg_match('/^\d{8}[A-Za-z]$/', $valor)) {
            return true;
        }
        // NIE: empieza por X, Y o Z
        if (preg_match('/^[XYZxyz]\d{7}[A-Za-z]$/', $valor)) {
            return true;
        }
        // CIF de empresa
        if (preg_match('/^[ABCDEFGHJNPQRSUVWabcdefghjnpqrsuvw]\d{7}[A-Za-z0-9]$/', $valor)) {
            return true;
        }
        return false;
    }

    /**
     * Valida los campos del formulario de tarea.
     * Devuelve un array asociativo con los errores por campo.
     * @param Request $r
     */
    private function validarCampos(Request $r)
    {
        $errores = [];

        // NIF obligatorio y con formato correcto
        if (trim($r->nif) == '') {
            $errores['nif'] = 'El NIF/CIF es obligatorio.';
        } elseif (!$this->validarNifCif(trim($r->nif))) {
            $errores['nif'] = 'El NIF/CIF no tiene un formato valido.';
        }

        // Persona de contacto obligatoria
        if (trim($r->persona_contacto) == '') {
            $errores['persona_contacto'] = 'La persona de contacto es obligatoria.';
        }

        // Descripcion obligatoria
        if (trim($r->descripcion) == '') {
            $errores['descripcion'] = 'La descripcion es obligatoria.';
        }

        // Email obligatorio y con formato correcto
        if (trim($r->email) == '') {
            $errores['email'] = 'El correo electronico es obligatorio.';
        } elseif (!filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El correo electronico no tiene un formato valido.';
        }

        // Telefono: solo numeros, espacios y guiones (campo opcional)
        if (trim($r->telefono) != '') {
            if (!preg_match('/^[0-9\s\-\+]{7,15}$/', $r->telefono)) {
                $errores['telefono'] = 'El telefono solo puede tener numeros, espacios o guiones.';
            }
        }

        // Codigo postal: 5 digitos (campo opcional)
        if (trim($r->cp) != '') {
            if (!preg_match('/^\d{5}$/', $r->cp)) {
                $errores['cp'] = 'El codigo postal debe tener 5 digitos.';
            }
        }

        // Provincia obligatoria
        if (trim($r->provincia) == '') {
            $errores['provincia'] = 'Debes seleccionar una provincia.';
        }

        // Fecha de realizacion: debe ser posterior a hoy
        if (trim($r->fecha_realizacion) != '') {
            $fecha = strtotime($r->fecha_realizacion);
            $hoy   = strtotime('today');
            if ($fecha === false || $fecha <= $hoy) {
                $errores['fecha_realizacion'] = 'La fecha debe ser valida y posterior a hoy.';
            }
        }

        return $errores;
    }

    // -------------------------------------------------------
    // LISTADO
    // -------------------------------------------------------

    /**
     * Muestra el listado paginado de todas las tareas.
     * @param Request $request
     */
    public function index(Request $request)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        // Leer cuantas tareas por pagina desde la configuracion
        $config    = require base_path('app/config.php');
        $porPagina = (int)($config['por_pagina'] ?? 5);
        if ($porPagina < 1) $porPagina = 5;

        $pagina = (int)$request->get('pagina', 1);
        if ($pagina < 1) $pagina = 1;

        $total     = Tarea::total();
        $totalPags = (int)ceil($total / $porPagina);
        $tareas    = Tarea::paginadas($pagina, $porPagina);

        return view('tareas.lista', compact('tareas', 'pagina', 'totalPags', 'total'));
    }

    /**
     * Muestra solo las tareas con estado Pendiente.
     */
    public function pendientes()
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $tareas    = Tarea::pendientes();
        $pagina    = 1;
        $totalPags = 1;
        $total     = count($tareas);

        return view('tareas.lista', compact('tareas', 'pagina', 'totalPags', 'total'));
    }

    // -------------------------------------------------------
    // VER DETALLE
    // -------------------------------------------------------

    /**
     * Muestra la informacion completa de una tarea.
     * @param int $id
     */
    public function ver($id)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $tarea = Tarea::buscar($id);
        return view('tareas.ver', compact('tarea'));
    }

    // -------------------------------------------------------
    // CREAR
    // -------------------------------------------------------

    /**
     * Muestra el formulario para crear una nueva tarea.
     * Solo pueden acceder los administradores.
     */
    public function crear()
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        // Leer valores por defecto desde la configuracion
        $config = require base_path('app/config.php');

        $tarea = (object)[
            'nif'                   => '',
            'persona_contacto'      => '',
            'telefono'              => '',
            'email'                 => '',
            'descripcion'           => '',
            'anotaciones_anteriores'=> '',
            'direccion'             => '',
            'poblacion'             => $config['poblacion_defecto'] ?? '',
            'cp'                    => '',
            'provincia'             => $config['provincia_defecto'] ?? '',
            'estado'                => 'P',
            'fecha_realizacion'     => '',
            'operario'              => '',
        ];

        $provincias = $this->provincias();
        $operarios  = Usuario::operarios();
        $errores    = [];

        return view('tareas.formulario', compact('tarea', 'provincias', 'operarios', 'errores'));
    }

    /**
     * Guarda la nueva tarea despues de validar los datos.
     * @param Request $r
     */
    public function guardar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $errores = $this->validarCampos($r);

        if (!empty($errores)) {
            $tarea      = (object)$r->all();
            $provincias = $this->provincias();
            $operarios  = Usuario::operarios();
            return view('tareas.formulario', compact('tarea', 'provincias', 'operarios', 'errores'));
        }

        Tarea::crear($r->all());
        return redirect('tareas')->with('exito', 'Tarea creada correctamente.');
    }

    // -------------------------------------------------------
    // EDITAR
    // -------------------------------------------------------

    /**
     * Muestra el formulario de edicion con los datos actuales.
     * @param int $id
     */
    public function editar($id)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $tarea      = Tarea::buscar($id);
        $provincias = $this->provincias();
        $operarios  = Usuario::operarios();
        $errores    = [];

        return view('tareas.formulario_editar', compact('tarea', 'provincias', 'operarios', 'errores'));
    }

    /**
     * Actualiza la tarea con los nuevos datos del formulario.
     * @param Request $r
     */
    public function actualizar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $errores = $this->validarCampos($r);

        if (!empty($errores)) {
            $tarea      = (object)$r->all();
            $provincias = $this->provincias();
            $operarios  = Usuario::operarios();
            return view('tareas.formulario_editar', compact('tarea', 'provincias', 'operarios', 'errores'));
        }

        Tarea::actualizar((int)$r->id, $r->all());
        return redirect('tareas')->with('exito', 'Tarea actualizada correctamente.');
    }

    // -------------------------------------------------------
    // COMPLETAR (operario)
    // -------------------------------------------------------

    /**
     * Muestra el formulario para que el operario complete la tarea.
     * @param int $id
     */
    public function completar($id)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $tarea = Tarea::buscar($id);
        return view('tareas.completar', compact('tarea'));
    }

    /**
     * Guarda los cambios del operario: estado, anotaciones y fichero adjunto.
     * @param Request $r
     */
    public function completarGuardar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $ficheroNombre = null;

        // Si se ha subido un fichero lo guardamos en storage/app/adjuntos
        if ($r->hasFile('fichero_resumen') && $r->file('fichero_resumen')->isValid()) {
            $carpeta = storage_path('app/adjuntos');
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0755, true);
            }
            $extension     = $r->file('fichero_resumen')->getClientOriginalExtension();
            $ficheroNombre = $r->id . '.' . $extension;
            $r->file('fichero_resumen')->move($carpeta, $ficheroNombre);
        }

        Tarea::completar(
            (int)$r->id,
            $r->estado,
            $r->fecha_realizacion ?? '',
            $r->anotaciones_posteriores ?? '',
            $ficheroNombre
        );

        return redirect('tareas')->with('exito', 'Tarea completada correctamente.');
    }

    // -------------------------------------------------------
    // DESCARGA DE ADJUNTO
    // -------------------------------------------------------

    /**
     * Descarga el fichero adjunto de una tarea.
     * Solo accesible si el usuario ha iniciado sesion.
     * El fichero no es accesible desde una URL publica.
     * @param int $id
     */
    public function descargarAdjunto($id)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $tarea = Tarea::buscar($id);

        if (!$tarea || !$tarea->fichero_resumen) {
            return redirect('tareas')->with('error', 'Esta tarea no tiene fichero adjunto.');
        }

        $ruta = storage_path('app/adjuntos/' . $tarea->fichero_resumen);

        if (!file_exists($ruta)) {
            return redirect('tareas')->with('error', 'El fichero no se encontro en el servidor.');
        }

        // Enviamos el fichero con cabeceras HTTP para forzar la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $tarea->fichero_resumen . '"');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
        exit;
    }

    // -------------------------------------------------------
    // BORRAR
    // -------------------------------------------------------

    /**
     * Muestra la pagina de confirmacion antes de borrar.
     * @param int $id
     */
    public function borrar($id)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $tarea = Tarea::buscar($id);
        return view('tareas.formulario_borrar', compact('tarea'));
    }

    /**
     * Borra definitivamente la tarea tras la confirmacion del usuario.
     * La confirmacion se hace en el servidor, no con JavaScript.
     * @param Request $r
     */
    public function borrarConfirmar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        Tarea::borrar((int)$r->id);
        return redirect('tareas')->with('exito', 'Tarea eliminada correctamente.');
    }
}
