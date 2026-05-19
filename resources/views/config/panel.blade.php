@extends('layout.app')
@section('titulo', 'Configuración de la aplicación')
@section('contenido')

<h2>Configuración de la aplicación</h2>
<p style="color:#888;font-size:13px;margin-bottom:20px;">
    Los cambios se guardan en <code>app/config.php</code> y se aplican de forma inmediata sin reiniciar el servidor.
</p>

<div class="contenedor-formulario">
<form action="{{ url('configuracion/guardar') }}" method="post">
@csrf

    {{-- Tareas por página --}}
    <label>Tareas por pagina en el listado
        <input type="number" name="por_pagina"
               value="{{ $config['por_pagina'] }}"
               min="1" max="100" style="max-width:120px;">
        <span style="color:#888;font-size:12px;display:block;margin-top:4px;">
            Actualmente se muestran <strong style="color:#4da3ff;">{{ $config['por_pagina'] }}</strong> tareas por pagina.
        </span>
    </label>

    {{-- Provincia por defecto --}}
    <label>Provincia por defecto al crear una tarea
        <select name="provincia_defecto">
            @foreach($provincias as $cod => $nombre)
                <option value="{{ $cod }}" {{ $config['provincia_defecto'] == $cod ? 'selected' : '' }}>
                    {{ $nombre }}
                </option>
            @endforeach
        </select>
        <span style="color:#888;font-size:12px;display:block;margin-top:4px;">
            Aparecerá preseleccionada al crear una nueva tarea.
        </span>
    </label>

    {{-- Población por defecto --}}
    <label>Poblacion por defecto al crear una tarea
        <input type="text" name="poblacion_defecto" value="{{ $config['poblacion_defecto'] }}">
        <span style="color:#888;font-size:12px;display:block;margin-top:4px;">
            Se rellenará automáticamente en el campo "Población" del formulario.
        </span>
    </label>

    <div class="fila-botones" style="margin-top:24px;">
        <button type="submit">Guardar configuracion</button>
        <a href="{{ url('tareas') }}" class="btn btn-secundario">Cancelar</a>
    </div>
</form>
</div>


@endsection
