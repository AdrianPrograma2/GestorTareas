@extends('layout.app')
@section('titulo', 'Nueva tarea')
@section('contenido')

<h2>Nueva tarea</h2>

@php $e = $errores; @endphp

<div class="contenedor-formulario">
<form action="{{ url('tareas/guardar') }}" method="post" enctype="multipart/form-data">
@csrf

    <label>NIF / CIF *
        <input type="text" name="nif" value="{{ $tarea->nif }}">
        @isset($e['nif'])<span class="error-campo">{{ $e['nif'] }}</span>@endisset
    </label>

    <label>Persona de contacto *
        <input type="text" name="persona_contacto" value="{{ $tarea->persona_contacto }}">
        @isset($e['persona_contacto'])<span class="error-campo">{{ $e['persona_contacto'] }}</span>@endisset
    </label>

    <label>Telefono
        <input type="text" name="telefono" value="{{ $tarea->telefono }}">
        @isset($e['telefono'])<span class="error-campo">{{ $e['telefono'] }}</span>@endisset
    </label>

    <label>Correo electronico *
        <input type="text" name="email" value="{{ $tarea->email }}">
        @isset($e['email'])<span class="error-campo">{{ $e['email'] }}</span>@endisset
    </label>

    <label>Descripcion *
        <textarea name="descripcion">{{ $tarea->descripcion }}</textarea>
        @isset($e['descripcion'])<span class="error-campo">{{ $e['descripcion'] }}</span>@endisset
    </label>

    <label>Anotaciones anteriores
        <textarea name="anotaciones_anteriores">{{ $tarea->anotaciones_anteriores }}</textarea>
    </label>

    <label>Direccion
        <input type="text" name="direccion" value="{{ $tarea->direccion }}">
    </label>

    <label>Poblacion
        <input type="text" name="poblacion" value="{{ $tarea->poblacion }}">
    </label>

    <label>Codigo postal
        <input type="text" name="cp" value="{{ $tarea->cp }}" maxlength="5">
        @isset($e['cp'])<span class="error-campo">{{ $e['cp'] }}</span>@endisset
    </label>

    <label>Provincia *
        <select name="provincia">
            @foreach($provincias as $cod => $nombre)
                <option value="{{ $cod }}" {{ ($tarea->provincia ?? '') == $cod ? 'selected' : '' }}>{{ $nombre }}</option>
            @endforeach
        </select>
        @isset($e['provincia'])<span class="error-campo">{{ $e['provincia'] }}</span>@endisset
    </label>

    <label>Estado
        <select name="estado">
            <option value="P" {{ ($tarea->estado ?? '') == 'P' ? 'selected' : '' }}>Pendiente</option>
            <option value="B" {{ ($tarea->estado ?? '') == 'B' ? 'selected' : '' }}>Bloqueada</option>
            <option value="R" {{ ($tarea->estado ?? '') == 'R' ? 'selected' : '' }}>Realizada</option>
            <option value="C" {{ ($tarea->estado ?? '') == 'C' ? 'selected' : '' }}>Cancelada</option>
        </select>
    </label>

    <label>Fecha de realizacion
        <input type="date" name="fecha_realizacion" value="{{ $tarea->fecha_realizacion }}">
        @isset($e['fecha_realizacion'])<span class="error-campo">{{ $e['fecha_realizacion'] }}</span>@endisset
    </label>

    <label>Operario asignado
        <select name="operario">
            <option value="">-- Sin asignar --</option>
            @foreach($operarios as $o)
                <option value="{{ $o->usuario }}" {{ ($tarea->operario ?? '') == $o->usuario ? 'selected' : '' }}>{{ $o->nombre }}</option>
            @endforeach
        </select>
    </label>

    <div class="fila-botones">
        <button type="submit">Guardar tarea</button>
        <a href="{{ url('tareas') }}" class="btn btn-secundario">Cancelar</a>
    </div>
</form>
</div>

@endsection
