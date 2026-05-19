@extends('layout.app')
@section('titulo', 'Confirmar borrado de usuario')
@section('contenido')

<h2>Confirmar borrado de usuario</h2>

<div class="confirmacion-caja">
    <h3>¿Seguro que quieres eliminar este usuario?</h3>
    <p style="color:#aaa;font-size:14px;margin-bottom:14px;">Esta accion no se puede deshacer.</p>

    <table class="detalle-tabla" style="margin-bottom:16px;">
        <tr><th>ID</th><td>#{{ $usuario->id }}</td></tr>
        <tr><th>Nombre</th><td>{{ $usuario->nombre }}</td></tr>
        <tr><th>Usuario</th><td>{{ $usuario->usuario }}</td></tr>
        <tr><th>Rol</th><td>{{ $usuario->rol }}</td></tr>
    </table>

    <form method="post" action="{{ url('usuarios/borrar_confirmar') }}">
        @csrf
        <input type="hidden" name="id" value="{{ $usuario->id }}">
        <div class="fila-botones">
            <button type="submit" class="btn btn-peligro">Si, eliminar definitivamente</button>
            <a href="{{ url('usuarios') }}" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

@endsection
