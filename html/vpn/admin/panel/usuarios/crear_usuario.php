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
    header("Location: ../../logout.php");
    exit;
}
$_SESSION['ultima_actividad'] = time();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: ../../index.php");
    exit;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['nombre']) || empty($_POST['apellidos']) || empty($_POST['correo']) || empty($_POST['usuario']) || empty($_POST['password'])) {
        $mensaje = "Por favor, rellena todos los campos.";
    } else {
        $nombre = trim($_POST['nombre']);
        $apellidos = trim($_POST['apellidos']);
        $correo = trim($_POST['correo']);
        $usuario = trim($_POST['usuario']);
        $password = $_POST['password'];

        if (strlen($nombre) < 3 || strlen($apellidos) < 3 || strlen($usuario) < 3 || strlen($password) < 6 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Datos inválidos. Nombre y usuario mínimo 3 caracteres, contraseña mínimo 6, correo válido.";
        } else {
            try {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

                // Comprobar si usuario ya existe
                $stmt = $conn->prepare("SELECT COUNT(*) FROM cliente WHERE usuario = ?");
                $stmt->execute([$usuario]);
                if ($stmt->fetchColumn() > 0) {
                    $mensaje = "El usuario VPN ya existe.";
                } else {
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO cliente (nombre, apellidos, correo, usuario, password) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nombre, $apellidos, $correo, $usuario, $pass_hash]);
                    $mensaje = "Usuario VPN creado con éxito.";
                }
            } catch (PDOException $e) {
                $mensaje = "Error en la base de datos: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" href="/ico/Logo.ico" />
<title>Crear Usuario | AlmagaraVPN</title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        background-color: #121212;
        color: #ffffff;
        font-family: Arial, sans-serif;
        padding: 20px;
    }

    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    h1 {
        font-size: 28px;
    }

    .back-container form {
        display: inline;
    }

    input[type="submit"].back {
        background-color: #4caf50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    input[type="submit"].back:hover {
        background-color: #43a047;
    }

    form#form-crear {
        max-width: 400px;
        margin: auto;
        background: #1e1e1e;
        padding: 20px;
        border-radius: 10px;
    }

    input, button {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        background-color: #2c2c2c;
        color: white;
    }

    button {
        background-color: #4caf50;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #43a047;
    }

    .mensaje {
        text-align: center;
        margin-top: 15px;
        font-weight: bold;
    }
</style>
</head>
<body>

<header>
    <h1>Crear Usuario VPN</h1>
    <div class="back-container">
        <form action="index.php" method="get">
            <input type="submit" class="back" value="Volver a usuarios" />
        </form>
    </div>
</header>

<form id="form-crear" method="post" autocomplete="off" novalidate>
    <input type="text" name="nombre" placeholder="Nombre" required minlength="3" />
    <input type="text" name="apellidos" placeholder="Apellidos" required minlength="3" />
    <input type="email" name="correo" placeholder="Correo" required />
    <input type="text" name="usuario" placeholder="Usuario" required minlength="3" />
    <input type="password" name="password" placeholder="Contraseña" required minlength="6" />
    <button type="submit">Crear</button>
    <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
</form>

</body>
</html>
