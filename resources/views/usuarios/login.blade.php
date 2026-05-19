<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion — Bunglebuild S.L.</title>
    <link rel="stylesheet" href="{{ url('/css/estilos.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-caja">
        <div class="login-titulo">Gestor de Tareas</div>
        <div class="login-empresa">Bunglebuild S.L. &mdash; Iniciar sesion</div>

        @if(session('error'))
            <div class="error-box">{{ session('error') }}</div>
        @endif

        <div class="contenedor-formulario" style="width:100%;padding:0;background:none;">
        <form action="{{ url('/login') }}" method="post">
            @csrf
            <label>Usuario
                <input type="text" name="usuario" value="{{ $usuarioRecordado }}" autocomplete="username">
            </label>
            <label>Contrasena
                <input type="password" name="password" autocomplete="current-password">
            </label>
            <label style="flex-direction:row;display:flex;align-items:center;gap:8px;margin-top:14px;">
                <input type="checkbox" name="recordar" value="1" id="recordar" style="width:auto;margin:0">
                <span style="font-size:13px;">Recordarme 3 dias</span>
            </label>
            <div style="margin-top:16px;">
                <input type="submit" value="Entrar" style="width:100%;padding:10px;">
            </div>
        </form>
        </div>
    </div>
</div>
</body>
</html>
