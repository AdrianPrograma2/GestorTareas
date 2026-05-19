<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarea;
use App\Models\Usuario;
use App\Http\Controllers\ConfigController;

/**
 * TareaController - Controlador para la gestión de tareas/incidencias.
 *
 * Gestiona todas las operaciones CRUD: listar, ver, crear, editar,
 * completar y borrar tareas. Utiliza sesiones PHP nativas para el
 * control de acceso y diferenciación de roles.
 *
 * @author  Alumno DWES
 * @version 2.0
 * @date    2024-12-01
 */
class TareaController extends Controller
{
    /** @var int Número de tareas por página en el listado paginado. */
    private const POR_PAGINA = 5;

    /**
     * Verifica que el usuario esté autenticado mediante sesión PHP nativa.
     * Si no lo está, redirige al login.
     *
     * @return \Illuminate\Http\RedirectResponse|null Redirección o null.
     */
    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario'])) {
            return redirect('login');
        }
        return null;
    }

    /**
     * Verifica que el usuario autenticado tenga rol de administrador.
     * Si no, redirige al listado con un mensaje de error.
     *
     * @return \Illuminate\Http\RedirectResponse|null Redirección o null.
     */
    private function soloAdmin()
    {
        if ($_SESSION['rol'] !== 'admin') {
            return redirect('tareas')->with('error', 'Acceso restringido a administradores.');
        }
        return null;
    }

    /**
     * Devuelve el array de provincias españolas con sus códigos INE.
     *
     * @return array Mapa código => nombre de provincia.
     */
    private function provincias(): array
    {
        return [
            ''   => '-- Selecciona provincia --',
            '02' => 'Albacete',      '03' => 'Alicante',
            '04' => 'Almería',       '05' => 'Ávila',
            '06' => 'Badajoz',       '07' => 'Baleares',
            '08' => 'Barcelona',     '09' => 'Burgos',
            '10' => 'Cáceres',       '11' => 'Cádiz',
            '12' => 'Castellón',     '13' => 'Ciudad Real',
            '14' => 'Córdoba',       '15' => 'A Coruña',
            '16' => 'Cuenca',        '17' => 'Girona',
            '18' => 'Granada',       '19' => 'Guadalajara',
            '20' => 'Guipúzcoa',     '21' => 'Huelva',
            '22' => 'Huesca',        '23' => 'Jaén',
            '24' => 'León',          '25' => 'Lleida',
            '26' => 'La Rioja',      '27' => 'Lugo',
            '28' => 'Madrid',        '29' => 'Málaga',
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
     * Valida los campos del formulario de tarea.
     * Realiza validaciones de formato para NIF, teléfono, CP, email y fecha.
     *
     * @param  Request $r Objeto de la petición HTTP.
     * @return array      Lista de mensajes de error (vacía si todo es correcto).
     */
    private function validarCampos(Request $r): array
    {
        $errores = [];

        // NIF / CIF
        if (trim($r->nif) === '') {
            $errores['nif'] = 'El NIF/CIF es obligatorio.';
        } elseif (!$this->validarNifCif(trim($r->nif))) {
            $errores['nif'] = 'El NIF/CIF no tiene un formato válido.';
        }

        // Persona de contacto
        if (trim($r->persona_contacto) === '') {
            $errores['persona_contacto'] = 'La persona de contacto es obligatoria.';
        }

        // Descripción
        if (trim($r->descripcion) === '') {
            $errores['descripcion'] = 'La descripción es obligatoria.';
        }

        // Correo electrónico
        if (trim($r->email) === '') {
            $errores['email'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El correo electrónico no tiene un formato válido.';
        }

        // Teléfono: solo números, espacios, guiones y +
        if (trim($r->telefono) !== '' && !preg_match('/^[0-9\s\-\+]{7,15}$/', $r->telefono)) {
            $errores['telefono'] = 'Solo números, espacios, guiones o +. Entre 7 y 15 caracteres.';
        }

        // Código postal: 5 dígitos
        if (trim($r->cp) !== '' && !preg_match('/^\d{5}$/', $r->cp)) {
            $errores['cp'] = 'El código postal debe tener exactamente 5 dígitos.';
        }

        // Provincia
        if (trim($r->provincia) === '') {
            $errores['provincia'] = 'Debe seleccionar una provincia.';
        }

        // Fecha de realización: debe ser posterior a hoy
        if (trim($r->fecha_realizacion) !== '') {
            $fecha = \DateTime::createFromFormat('Y-m-d', $r->fecha_realizacion);
            $hoy   = new \DateTime('today');
            if (!$fecha || $fecha < $hoy) {
                $errores['fecha_realizacion'] = 'La fecha debe ser válida y posterior a la fecha actual.';
            }
        }

        return $errores;
    }

    /**
     * Valida el formato de un NIF, CIF o NIE español de forma básica.
     *
     * @param  string $valor El valor a validar.
     * @return bool True si el formato es aceptable.
     */
    private function validarNifCif(string $valor): bool
    {
        // NIF: 8 dígitos + 1 letra
        if (preg_match('/^\d{8}[A-Za-z]$/', $valor)) return true;
        // NIE: X/Y/Z + 7 dígitos + letra
        if (preg_match('/^[XYZxyz]\d{7}[A-Za-z]$/', $valor)) return true;
        // CIF: letra + 7 dígitos + letra/dígito
        if (preg_match('/^[ABCDEFGHJNPQRSUVWabcdefghjnpqrsuvw]\d{7}[A-Za-z0-9]$/', $valor)) return true;
        return false;
    }

    /* ================================================================
       LISTADO
       ================================================================ */

    /**
     * Muestra el listado paginado de todas las tareas.
     * Solo accesible si el usuario está autenticado.
     *
     * @param  Request $request Petición HTTP (incluye parámetro 'pagina').
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($redir = $this->verificarSesion()) return $redir;

        $cfg       = ConfigController::leerConfig();
        $porPagina = max(1, (int) ($cfg['por_pagina'] ?? self::POR_PAGINA));

        $pagina    = max(1, (int) $request->get('pagina', 1));
        $total     = Tarea::total();
        $totalPags = (int) ceil($total / $porPagina);
        $tareas    = Tarea::paginadas($pagina, $porPagina);

        return view('tareas.lista', compact('tareas', 'pagina', 'totalPags', 'total'));
    }

    /**
     * Muestra únicamente las tareas con estado Pendiente.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function pendientes()
    {
        if ($redir = $this->verificarSesion()) return $redir;

        $tareas    = Tarea::pendientes();
        $pagina    = 1;
        $totalPags = 1;
        $total     = count($tareas);

        return view('tareas.lista', compact('tareas', 'pagina', 'totalPags', 'total'));
    }

    /* ================================================================
       VER DETALLE
       ================================================================ */

    /**
     * Muestra la información completa y detallada de una tarea.
     *
     * @param  int $id ID de la tarea a mostrar.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function ver(int $id)
    {
        if ($redir = $this->verificarSesion()) return $redir;

        $tarea = Tarea::buscar($id);
        return view('tareas.ver', compact('tarea'));
    }

    /* ================================================================
       CREAR
       ================================================================ */

    /**
     * Muestra el formulario para crear una nueva tarea.
     * Solo accesible para administradores.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function crear()
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $cfg   = ConfigController::leerConfig();
        $tarea = (object)[
            'nif' => '', 'persona_contacto' => '', 'telefono' => '',
            'email' => '', 'descripcion' => '', 'anotaciones_anteriores' => '',
            'direccion' => '', 'cp' => '',
            'poblacion' => $cfg['poblacion_defecto'] ?? '',
            'provincia' => $cfg['provincia_defecto'] ?? '',
            'estado' => 'P', 'fecha_realizacion' => '', 'operario' => ''
        ];
        $provincias = $this->provincias();
        $operarios  = Usuario::operarios();

        return view('tareas.formulario', compact('tarea', 'provincias', 'operarios'));
    }

    /**
     * Procesa el formulario de creación y guarda la nueva tarea.
     * Realiza validación completa en servidor antes de guardar.
     *
     * @param  Request $r Petición HTTP con los datos del formulario.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function guardar(Request $r)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $errores = $this->validarCampos($r);

        if (!empty($errores)) {
            $tarea      = (object) $r->all();
            $provincias = $this->provincias();
            $operarios  = Usuario::operarios();
            return view('tareas.formulario', compact('tarea', 'provincias', 'operarios', 'errores'));
        }

        Tarea::crear($r->all());
        return redirect('tareas')->with('exito', 'Tarea creada correctamente.');
    }

    /* ================================================================
       EDITAR
       ================================================================ */

    /**
     * Muestra el formulario de edición con los datos actuales de la tarea.
     * Solo accesible para administradores.
     *
     * @param  int $id ID de la tarea a editar.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editar(int $id)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $tarea      = Tarea::buscar($id);
        $provincias = $this->provincias();
        $operarios  = Usuario::operarios();

        return view('tareas.formulario_editar', compact('tarea', 'provincias', 'operarios'));
    }

    /**
     * Procesa el formulario de edición y actualiza la tarea en la BD.
     *
     * @param  Request $r Petición HTTP con los nuevos datos.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function actualizar(Request $r)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $errores = $this->validarCampos($r);

        if (!empty($errores)) {
            $tarea      = (object) $r->all();
            $provincias = $this->provincias();
            $operarios  = Usuario::operarios();
            return view('tareas.formulario_editar', compact('tarea', 'provincias', 'operarios', 'errores'));
        }

        Tarea::actualizar((int) $r->id, $r->all());
        return redirect('tareas')->with('exito', 'Tarea actualizada correctamente.');
    }

    /* ================================================================
       COMPLETAR (operario)
       ================================================================ */

    /**
     * Muestra el formulario para que un operario complete/cierre una tarea.
     * Solo muestra los campos que puede modificar el operario.
     *
     * @param  int $id ID de la tarea a completar.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function completar(int $id)
    {
        if ($redir = $this->verificarSesion()) return $redir;

        $tarea = Tarea::buscar($id);
        return view('tareas.completar', compact('tarea'));
    }

    /**
     * Procesa el formulario de completar: actualiza estado, anotaciones
     * y guarda el fichero adjunto en el servidor.
     *
     * El fichero se guarda en storage/app/adjuntos/ con el nombre
     * del ID de la tarea para facilitar su recuperación.
     *
     * @param  Request $r Petición HTTP con datos del operario.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completarGuardar(Request $r)
    {
        if ($redir = $this->verificarSesion()) return $redir;

        $ficheroNombre = null;

        // Gestión de fichero adjunto
        if ($r->hasFile('fichero_resumen') && $r->file('fichero_resumen')->isValid()) {
            $carpeta       = storage_path('app/adjuntos');
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
            $extension     = $r->file('fichero_resumen')->getClientOriginalExtension();
            $ficheroNombre = $r->id . '.' . $extension;
            $r->file('fichero_resumen')->move($carpeta, $ficheroNombre);
        }

        Tarea::completar(
            (int) $r->id,
            $r->estado,
            $r->fecha_realizacion ?? '',
            $r->anotaciones_posteriores ?? '',
            $ficheroNombre
        );

        return redirect('tareas')->with('exito', 'Tarea completada correctamente.');
    }

    /* ================================================================
       DESCARGA DE ADJUNTOS
       ================================================================ */

    /**
     * Sirve el fichero adjunto de una tarea de forma protegida.
     * Solo accesible si el usuario ha iniciado sesión.
     * El fichero nunca es accesible directamente desde una URL pública.
     *
     * @param  int $id ID de la tarea cuyo adjunto se descarga.
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function descargarAdjunto(int $id)
    {
        if ($redir = $this->verificarSesion()) return $redir;

        $tarea = Tarea::buscar($id);

        if (!$tarea || !$tarea->fichero_resumen) {
            return redirect('tareas')->with('error', 'No hay fichero adjunto para esta tarea.');
        }

        $ruta = storage_path('app/adjuntos/' . $tarea->fichero_resumen);

        if (!file_exists($ruta)) {
            return redirect('tareas')->with('error', 'El fichero adjunto no se encontró en el servidor.');
        }

        return response()->download($ruta, $tarea->fichero_resumen);
    }

    /* ================================================================
       BORRAR
       ================================================================ */

    /**
     * Muestra la página de confirmación antes de borrar una tarea.
     * Solo accesible para administradores.
     *
     * @param  int $id ID de la tarea a borrar.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function borrar(int $id)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        $tarea = Tarea::buscar($id);
        return view('tareas.formulario_borrar', compact('tarea'));
    }

    /**
     * Ejecuta el borrado definitivo de la tarea tras confirmación del usuario.
     * La confirmación se realiza en el servidor (no en JavaScript).
     *
     * @param  Request $r Petición HTTP con el ID de la tarea.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function borrarConfirmar(Request $r)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin()) return $redir;

        Tarea::borrar((int) $r->id);
        return redirect('tareas')->with('exito', 'Tarea eliminada correctamente.');
    }
}
