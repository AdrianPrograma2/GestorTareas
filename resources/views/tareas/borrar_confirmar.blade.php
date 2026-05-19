@extends('layout.app')
@section('titulo', 'Tarea eliminada')
@section('contenido')
<h2>Tarea eliminada</h2>
<p style="margin-bottom:16px;color:#aaa;">La tarea ha sido eliminada correctamente.</p>
<a href="{{ url('tareas') }}" class="btn">Volver al listado</a>
@endsection
