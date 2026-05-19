@extends('layout.app')
@section('titulo', 'Detalle tarea #'.$tarea->id)
@section('contenido')

<h2>Detalle tarea #{{ $tarea->id }}</h2>

<table class="detalle-tabla" style="margin-bottom:20px;">
    <tr><th>Descripcion</th><td>{{ $tarea->descripcion }}</td></tr>
    <tr><th>NIF/CIF</th><td>{{ $tarea->nif }}</td></tr>
    <tr><th>Persona de contacto</th><td>{{ $tarea->persona_contacto }}</td></tr>
    <tr><th>Telefono</th><td>{{ $tarea->telefono }}</td></tr>
    <tr><th>Correo electronico</th><td>{{ $tarea->email }}</td></tr>
    <tr><th>Direccion</th><td>{{ $tarea->direccion }}</td></tr>
    <tr><th>Poblacion</th><td>{{ $tarea->poblacion }}</td></tr>
    <tr><th>Codigo postal</th><td>{{ $tarea->cp }}</td></tr>
    <tr><th>Provincia</th><td>{{ $tarea->provincia }}</td></tr>
    <tr><th>Estado</th><td>
        <span class="estado estado-{{ $tarea->estado }}">
            {{ ['P'=>'Pendiente','R'=>'Realizada','C'=>'Cancelada','B'=>'Bloqueada'][$tarea->estado] ?? $tarea->estado }}
        </span>
    </td></tr>
    <tr><th>Fecha de creacion</th><td>{{ $tarea->fecha_creacion ? date('d/m/Y H:i', strtotime($tarea->fecha_creacion)) : '-' }}</td></tr>
    <tr><th>Fecha de realizacion</th><td>{{ $tarea->fecha_realizacion ? date('d/m/Y', strtotime($tarea->fecha_realizacion)) : '-' }}</td></tr>
    <tr><th>Operario asignado</th><td>{{ $tarea->operario ?? '-' }}</td></tr>
    <tr><th>Anotaciones anteriores</th><td>{{ $tarea->anotaciones_anteriores ?? '-' }}</td></tr>
    <tr><th>Anotaciones posteriores</th><td>{{ $tarea->anotaciones_posteriores ?? '-' }}</td></tr>
    @if($tarea->fichero_resumen)
    <tr><th>Fichero adjunto</th><td>
        <a href="{{ url('tareas/adjunto/'.$tarea->id) }}" class="btn btn-info" style="font-size:13px;padding:4px 10px;">
            Descargar: {{ $tarea->fichero_resumen }}
        </a>
    </td></tr>
    @endif
</table>

<div class="fila-botones">
    <a href="{{ url('tareas') }}" class="btn btn-secundario">Volver al listado</a>
    @if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin')
        <a href="{{ url('tareas/editar/'.$tarea->id) }}" class="btn">Editar</a>
    @endif
    <a href="{{ url('tareas/completar/'.$tarea->id) }}" class="btn btn-verde">Completar</a>
</div>

@endsection
