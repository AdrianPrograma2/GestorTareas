@extends('layout.app')
@section('titulo', 'Listado de tareas')
@section('contenido')

<h2>Listado de tareas</h2>

<div style="margin-bottom:14px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
    @if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin')
        <a href="{{ url('tareas/crear') }}" class="btn">Nueva tarea</a>
    @endif
    <a href="{{ url('tareas') }}" class="btn btn-secundario">Todas</a>
    <a href="{{ url('tareas/pendientes') }}" class="btn btn-secundario">Pendientes</a>
    <span style="margin-left:auto;font-size:13px;color:#888;">Total: {{ $total }} tarea(s)</span>
</div>

@if(count($tareas) === 0)
    <p style="color:#888;">No hay tareas que mostrar.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Descripcion</th>
            <th>Persona contacto</th>
            <th>Fecha realizacion</th>
            <th>Estado</th>
            <th>Operario</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tareas as $t)
        <tr>
            <td>{{ $t->id }}</td>
            <td>{{ $t->descripcion }}</td>
            <td>{{ $t->persona_contacto }}</td>
            <td>{{ $t->fecha_realizacion ? date('d/m/Y', strtotime($t->fecha_realizacion)) : '-' }}</td>
            <td>
                <span class="estado estado-{{ $t->estado }}">
                    @if($t->estado == 'P') Pendiente
                    @elseif($t->estado == 'R') Realizada
                    @elseif($t->estado == 'C') Cancelada
                    @elseif($t->estado == 'B') Bloqueada
                    @else {{ $t->estado }}
                    @endif
                </span>
            </td>
            <td>{{ $t->operario ?? '-' }}</td>
            <td>
                <div class="acciones">
                    <a href="{{ url('tareas/ver/'.$t->id) }}" class="btn btn-info">Ver</a>
                    @if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin')
                        <a href="{{ url('tareas/editar/'.$t->id) }}" class="btn btn-secundario">Editar</a>
                        <a href="{{ url('tareas/borrar/'.$t->id) }}" class="btn btn-peligro">Borrar</a>
                    @endif
                    <a href="{{ url('tareas/completar/'.$t->id) }}" class="btn btn-verde">Completar</a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($totalPags > 1)
<nav class="paginacion">
    <a href="{{ url('tareas?pagina=1') }}">Primera</a>

    @if($pagina > 1)
        <a href="{{ url('tareas?pagina='.($pagina - 1)) }}">Anterior</a>
    @endif

    <span class="pag-activa">{{ $pagina }}</span>

    @if($pagina < $totalPags)
        <a href="{{ url('tareas?pagina='.($pagina + 1)) }}">Siguiente</a>
    @endif

    <a href="{{ url('tareas?pagina='.$totalPags) }}">Ultima</a>

    <span class="pag-info">Pagina {{ $pagina }} de {{ $totalPags }}</span>
</nav>
@endif
@endif

@endsection
