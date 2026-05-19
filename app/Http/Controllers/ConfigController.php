<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controlador de configuracion
 * Permite al administrador cambiar parametros de la aplicacion.
 * Los valores se guardan en el fichero app/config.php.
 *
 * @author Adrian
 * @date 01/12/2024
 * @version 1.0
 */
class ConfigController extends Controller
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
            return redirect('tareas')->with('error', 'Solo los administradores pueden acceder a la configuracion.');
        }
        return null;
    }

    /**
     * Lee el fichero app/config.php y devuelve el array de configuracion.
     * Si el fichero no existe devuelve los valores por defecto.
     */
    public static function leerConfig()
    {
        $ruta = base_path('app/config.php');
        if (file_exists($ruta)) {
            $config = require $ruta;
            if (is_array($config)) {
                return $config;
            }
        }
        // Valores por defecto si no existe el fichero
        return [
            'por_pagina'        => 5,
            'provincia_defecto' => '',
            'poblacion_defecto' => '',
        ];
    }

    /**
     * Muestra el panel de configuracion con los valores actuales.
     */
    public function mostrar()
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        $config     = self::leerConfig();
        $provincias = $this->provincias();

        return view('config.panel', compact('config', 'provincias'));
    }

    /**
     * Guarda la configuracion en el fichero app/config.php.
     * El fichero se genera automaticamente con el contenido PHP.
     * @param Request $r
     */
    public function guardar(Request $r)
    {
        $redir = $this->verificarSesion();
        if ($redir != null) return $redir;

        $redir = $this->soloAdmin();
        if ($redir != null) return $redir;

        // Recogemos los valores del formulario
        $porPagina        = (int)$r->por_pagina;
        $provinciaDefecto = trim($r->provincia_defecto ?? '');
        $poblacionDefecto = trim($r->poblacion_defecto ?? '');

        if ($porPagina < 1) $porPagina = 5;

        // Escribimos el fichero de configuracion con PHP
        $contenido  = "<?php\n";
        $contenido .= "// Fichero de configuracion generado el " . date('d/m/Y H:i') . "\n";
        $contenido .= "return [\n";
        $contenido .= "    'por_pagina'        => " . $porPagina . ",\n";
        $contenido .= "    'provincia_defecto' => '" . addslashes($provinciaDefecto) . "',\n";
        $contenido .= "    'poblacion_defecto' => '" . addslashes($poblacionDefecto) . "',\n";
        $contenido .= "];\n";

        file_put_contents(base_path('app/config.php'), $contenido);

        return redirect('configuracion')->with('exito', 'Configuracion guardada correctamente.');
    }

    /**
     * Devuelve el array de provincias con su codigo INE.
     */
    private function provincias()
    {
        return [
            ''   => '-- Sin provincia por defecto --',
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
}
