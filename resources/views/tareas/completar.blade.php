@extends('layout.app')
@section('titulo', 'Completar tarea #'.$tarea->id)
@section('contenido')

<h2>Completar tarea #{{ $tarea->id }}</h2>

<p style="color:#888;margin-bottom:16px;font-size:14px;">{{ $tarea->descripcion }} &mdash; {{ $tarea->persona_contacto }}</p>

<div class="contenedor-formulario">
<form action="{{ url('tareas/completar_guardar') }}" method="post" enctype="multipart/form-data">
@csrf
<input type="hidden" name="id" value="{{ $tarea->id }}">
    <label>Estado final *</label>
    <label style="display:inline-flex;align-items:center;gap:6px;margin-top:6px;margin-right:16px;">
        <input type="radio" name="estado" value="R" checked style="width:auto"> Realizada
    </label>
    <label style="display:inline-flex;align-items:center;gap:6px;">
        <input type="radio" name="estado" value="C" style="width:auto"> Cancelada
    </label>
    <label>Fecha de realizacion<input type="date" name="fecha_realizacion" value="{{ $tarea->fecha_realizacion }}"></label>
    <label>Anotaciones posteriores<textarea name="anotaciones_posteriores">{{ $tarea->anotaciones_posteriores }}</textarea></label>
    <label>Fichero resumen (PDF, imagen...)
        <input type="file" name="fichero_resumen">
        @if($tarea->fichero_resumen)
            <small style="color:#2ecc71;font-size:12px;">Adjunto actual: {{ $tarea->fichero_resumen }}</small>
        @endif
    </label>
    <div class="fila-botones">
        <button type="submit" class="btn btn-verde">Completar tarea</button>
        <a href="{{ url('tareas') }}" class="btn btn-secundario">Cancelar</a>
    </div>
</form>
</div>

@endsection
