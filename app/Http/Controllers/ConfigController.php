<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * ConfigController - Gestiona el panel de configuración de la aplicación.
 *
 * Permite al administrador modificar parámetros de funcionamiento
 * (tareas por página, provincia y población por defecto) sin tocar el código.
 * Los valores se persisten en app/config.php, que la aplicación lee al arrancar.
 *
 * @author  Alumno DWES
 * @version 1.0
 * @date    2024-12-01
 */
class ConfigController extends Controller
{
    /** @var string Ruta absoluta al fichero de configuración de la app. */
    private const RUTA_CONFIG = __DIR__ . '/../../config.php';

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
            return redirect('tareas')->with('error', 'Solo los administradores pueden acceder a la configuración.');
        }
        return null;
    }

    /**
     * Lee el fichero app/config.php y devuelve el array de configuración.
     * Si el fichero no existe o es inválido, devuelve los valores por defecto.
     *
     * @return array Configuración actual de la aplicación.
     */
    public static function leerConfig(): array
    {
        $ruta = base_path('app/config.php');
        if (file_exists($ruta)) {
            $config = require $ruta;
            if (is_array($config)) {
                return $config;
            }
        }
        return [
            'por_pagina'        => 5,
            'provincia_defecto' => '',
            'poblacion_defecto' => '',
        ];
    }

    /**
     * Muestra el panel de configuración con los valores actuales.
     * Solo accesible para administradores.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function mostrar()
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin())      return $redir;

        $config     = self::leerConfig();
        $provincias = $this->provincias();

        return view('config.panel', compact('config', 'provincias'));
    }

    /**
     * Procesa el formulario y genera un nuevo fichero app/config.php
     * con los valores enviados por el administrador.
     *
     * @param  Request $r Petición HTTP con los nuevos valores de configuración.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function guardar(Request $r)
    {
        if ($redir = $this->verificarSesion()) return $redir;
        if ($redir = $this->soloAdmin())      return $redir;

        $porPagina       = max(1, min(100, (int) $r->por_pagina));
        $provinciaDefecto = trim($r->provincia_defecto ?? '');
        $poblacionDefecto = trim($r->poblacion_defecto ?? '');

        // Generar contenido PHP del fichero de configuración
        $contenido = "<?php\n";
        $contenido .= "/**\n";
        $contenido .= " * Fichero de configuración de la aplicación Gestor de Tareas.\n";
        $contenido .= " * Generado automáticamente el " . date('d/m/Y H:i:s') . "\n";
        $contenido .= " */\n";
        $contenido .= "return [\n";
        $contenido .= "    'por_pagina'        => " . $porPagina . ",\n";
        $contenido .= "    'provincia_defecto' => '" . addslashes($provinciaDefecto) . "',\n";
        $contenido .= "    'poblacion_defecto' => '" . addslashes($poblacionDefecto) . "',\n";
        $contenido .= "];\n";

        file_put_contents(base_path('app/config.php'), $contenido);

        return redirect('configuracion')->with('exito', 'Configuración guardada correctamente.');
    }

    /**
     * Devuelve el array de provincias españolas con sus códigos INE.
     *
     * @return array Mapa código => nombre.
     */
    private function provincias(): array
    {
        return [
            ''   => '-- Sin provincia por defecto --',
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
}
