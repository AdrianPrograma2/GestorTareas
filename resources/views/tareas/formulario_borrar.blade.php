@extends('layout.app')
@section('titulo', 'Confirmar borrado')
@section('contenido')

<h2>Confirmar borrado</h2>

<div class="confirmacion-caja">
    <h3>Seguro que quieres eliminar esta tarea?</h3>
    <p style="margin-bottom:14px;font-size:14px;color:#aaa;">Esta accion no se puede deshacer.</p>
    <table class="detalle-tabla" style="margin-bottom:16px;">
        <tr><th>ID</th><td>#{{ $tarea->id }}</td></tr>
        <tr><th>Descripcion</th><td>{{ $tarea->descripcion }}</td></tr>
        <tr><th>Persona contacto</th><td>{{ $tarea->persona_contacto }}</td></tr>
        <tr><th>Estado</th><td>{{ $tarea->estado }}</td></tr>
    </table>
    <form method="post" action="{{ url('tareas/borrar_confirmar') }}">
        @csrf
        <input type="hidden" name="id" value="{{ $tarea->id }}">
        <div class="fila-botones">
            <button type="submit" class="btn btn-peligro">Si, eliminar definitivamente</button>
            <a href="{{ url('tareas') }}" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</div>

@endsection
