<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Gestor de Tareas') — Bunglebuild S.L.</title>
    <link rel="stylesheet" href="{{ url('/css/estilos.css') }}?v={{ filemtime(public_path('css/estilos.css')) }}">
</head>
<body>

<header class="cabecera">
    <div class="cabecera-marca">
        <div>
            <div class="cabecera-titulo">Bunglebuild S.L.</div>
            <div class="cabecera-subtitulo">Gestor de Incidencias y Tareas</div>
        </div>
    </div>
    <div class="cabecera-usuario">
        @if(isset($_SESSION['usuario']))
            <span class="badge-rol badge-rol--{{ $_SESSION['rol'] }}">
                {{ $_SESSION['rol'] === 'admin' ? 'Admin' : 'Operario' }}
            </span>
            <span>{{ $_SESSION['nombre'] }}</span>
            <span class="hora-login">Sesion: {{ $_SESSION['hora_login'] }}</span>
            <a href="{{ url('logout') }}" class="btn-logout">Cerrar sesion</a>
        @endif
    </div>
</header>

<div class="layout">

    @if(isset($_SESSION['usuario']))
    <nav class="menu-lateral">
        <ul>
            <li class="menu-seccion">Tareas</li>
            <li><a href="{{ url('tareas') }}" class="{{ request()->is('tareas') ? 'activo' : '' }}">
                Todas las tareas
            </a></li>
            <li><a href="{{ url('tareas/pendientes') }}" class="{{ request()->is('tareas/pendientes') ? 'activo' : '' }}">
                Tareas pendientes
            </a></li>
            @if($_SESSION['rol'] === 'admin')
            <li><a href="{{ url('tareas/crear') }}" class="{{ request()->is('tareas/crear') ? 'activo' : '' }}">
                Nueva tarea
            </a></li>
            <li class="menu-seccion">Administracion</li>
            <li><a href="{{ url('usuarios') }}" class="{{ request()->is('usuarios*') ? 'activo' : '' }}">
                Gestion de usuarios
            </a></li>
            <li><a href="{{ url('configuracion') }}" class="{{ request()->is('configuracion*') ? 'activo' : '' }}">
                Configuracion
            </a></li>
            @endif
        </ul>
    </nav>
    @endif

    <main class="contenido">
        @if(session('exito'))
            <div class="alerta alerta--ok">{{ session('exito') }}</div>
        @endif
        @if(session('error'))
            <div class="alerta alerta--error">{{ session('error') }}</div>
        @endif

        @yield('contenido')
    </main>
</div>

<footer class="pie">
    Gestor de Tareas &mdash; Bunglebuild S.L. &mdash; DWES {{ date('Y') }}
</footer>

</body>
</html>
