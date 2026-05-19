@extends('layout.app')
@section('titulo', $usuario ? 'Editar usuario' : 'Nuevo usuario')
@section('contenido')

<h2>{{ $usuario ? 'Editar usuario' : 'Nuevo usuario' }}</h2>

@if(!empty($errores))
<div class="errores-caja">
    <p>Faltan datos obligatorios:</p>
    <ul>@foreach($errores as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="contenedor-formulario">
@if($usuario)
<form action="{{ url('usuarios/actualizar') }}" method="post">
@csrf
<input type="hidden" name="id" value="{{ $usuario->id }}">
@else
<form action="{{ url('usuarios/guardar') }}" method="post">
@csrf
@endif
    <label>Nombre completo *<input type="text" name="nombre" value="{{ $usuario->nombre ?? '' }}"></label>
    <label>Nombre de usuario *<input type="text" name="usuario" value="{{ $usuario->usuario ?? '' }}"></label>
    <label>Contrasena {{ $usuario ? '(dejar vacio para no cambiar)' : '*' }}
        <input type="password" name="password">
    </label>
    <label>Rol *
        <select name="rol">
            <option value="operario" {{ ($usuario->rol ?? '') === 'operario' ? 'selected' : '' }}>Operario</option>
            <option value="admin"    {{ ($usuario->rol ?? '') === 'admin'    ? 'selected' : '' }}>Administrador</option>
        </select>
    </label>
    <div class="fila-botones">
        <button type="submit">Guardar</button>
        <a href="{{ url('usuarios') }}" class="btn btn-secundario">Cancelar</a>
    </div>
</form>
</div>

@endsection
