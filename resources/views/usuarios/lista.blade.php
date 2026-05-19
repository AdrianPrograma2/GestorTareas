@extends('layout.app')
@section('titulo', 'Gestion de usuarios')
@section('contenido')

<h2>Gestion de usuarios</h2>

<div style="margin-bottom:14px;">
    <a href="{{ url('usuarios/crear') }}" class="btn">Nuevo usuario</a>
</div>

@if(count($usuarios) === 0)
    <p style="color:#888;">No hay usuarios registrados.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $u)
        <tr>
            <td>{{ $u->id }}</td>
            <td>{{ $u->nombre }}</td>
            <td>{{ $u->usuario }}</td>
            <td><span class="badge-rol badge-rol--{{ $u->rol }}">{{ $u->rol }}</span></td>
            <td>
                <div class="acciones">
                    <a href="{{ url('usuarios/editar/'.$u->id) }}" class="btn btn-secundario">Editar</a>
                    <a href="{{ url('usuarios/borrar/'.$u->id) }}" class="btn btn-peligro">Borrar</a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@endsection
