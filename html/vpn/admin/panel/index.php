<?php
session_name('SESSION_ADMIN');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['regenerado'])) {
    session_regenerate_id(true);
    $_SESSION['regenerado'] = true;
}

if (isset($_SESSION['ultima_actividad']) && time() - $_SESSION['ultima_actividad'] > 900) {
    session_unset();
    session_destroy();
    header("Location: ../logout.php");
    exit;
}
$_SESSION['ultima_actividad'] = time();


if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin | AlmagaraVPN</title>
    <link rel="icon" href="/ico/Logo.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/vendor/fontawesome/css/all.css">

    <style>
        * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html, body {
    max-width: 100%;
    overflow-x: hidden;
}

body {
    background-color: #121212;
    color: #ffffff;
    font-family: Arial, sans-serif;
    min-height: 100vh;
    padding: 20px;
}

.top-bar {
    position: absolute;
    top: 25px;
    right: 20px;
    z-index: 1000;
}
        
    a.back {
    background-color: #4caf50;
    color: white;
    margin-top: 10px;
    padding: 10px 25px; /* mas padding para que el texto no quede pegado */
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-size: 16px;
    transition: background-color 0.3s ease;
    display: inline-block;
    white-space: nowrap; /* evitar que el texto del boton se corte */
    max-width: none;
    text-align: center;
    flex-shrink: 0; /* que no se reduzca el boton en flex */
}

a.back:hover {
    background-color: #43a047;
}

h1 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 26px;
}

.panel {
    width: 100%;
    max-width: 500px;
    margin: auto;
    background-color: #1e1e1e;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
    text-align: center;
}

.panel-header {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 0 auto 20px auto;
    padding-bottom: 5px;
    border-bottom: 1px solid #333;
}
.panel-header img {
    width: 40px;
    height: auto;
}
.panel-header p {
    font-size: clamp(20px, 5vw, 26px);
    font-weight: bold;
    margin: 0;
}

ul {
    list-style: none;
    padding: 0;
}

li {
    margin: 15px 0;
}

a,
input[type="submit"] {
    display: block;
    width: 100%;
    padding: 14px;
    margin: 30px auto 0; /* centrado horizontal gracias al 'auto' */
    text-align: center;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-size: 16px;
    transition: background-color 0.3s ease;
    box-sizing: border-box;
}

a {
    background-color: #4caf50;
    color: white;
}

a:hover {
    background-color: #45a049;
}

input[type="submit"] {
    background-color: #f44336;
    color: white;
    cursor: pointer;
    max-width: 500px;
}

input[type="submit"]:hover {
    background-color: #d32f2f;
    transform: scale(1.02);
}

.logout-form {
    margin-top: 20px;
}

/* ✅ RESPONSIVE PARA DISPOSITIVOS MOVILES */
@media (max-width: 480px) {
    body {
        padding: 10px;
    }

    a.back {
        margin: 0;
        padding: 10px 18px; /* algo menos para caber mejor */
        font-size: 16px;
        white-space: nowrap;
    }

    .panel {
        padding: 20px;
    }

    h1 {
        font-size: 22px;
    }

    a,
    input[type="submit"] {
        font-size: 15px;
        padding: 12px;
    }
}

    </style>
</head>
<body>
    <div class="top-bar">
            <a href="../../index.html" class="back"><i class="fas fa-home"></i></a>
        </div>
    <div class="panel">
        <div class="panel-header">
    <img src="../../../archivos/vpn/wireguard_logo.png" alt="WireGuard Logo">
    <p>AlmagaraVPN</p>
</div>
        <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?></h1>
        <ul>
    <?php if ($_SESSION['usuario'] === 'admin'): ?>
        <li><a href="admins">Ver admins</a></li>
    <?php endif; ?>
    <li><a href="usuarios">Ver usuarios</a></li>
    <li><a href="suscripciones">Gestionar suscripciones</a></li>
    <li><a href="mensajes">Ver mensajes</a></li>
</ul>
    </div>
    <form class="logout-form" action="../logout.php" method="post">
            <input type="submit" value="Cerrar sesion">
        </form>
</body>
</html>
