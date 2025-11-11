<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_name('SESSION_ADMIN');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) {
    header("Location: https://fjfh06.ddns.net/vpn/admin/panel");
    exit;
}

if (!isset($_SESSION['regenerado'])) {
    session_regenerate_id(true);
    $_SESSION['regenerado'] = true;
}

if (isset($_SESSION['ultima_actividad']) && time() - $_SESSION['ultima_actividad'] > 900) {
    session_unset();
    session_destroy();
    header("Location: logout.php");
    exit;
}
$_SESSION['ultima_actividad'] = time();

$error = '';

if (!isset($_SESSION['intentos'])) $_SESSION['intentos'] = 0;

if ($_SESSION['intentos'] >= 5) {
    die('Demasiados intentos fallidos. Intenta mas tarde.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $usuario = trim(strip_tags($usuario));

    try {
        $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT password FROM admins WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado && password_verify($password, $resultado['password'])) {
            $_SESSION['autenticado'] = true;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['intentos'] = 0;
            header("Location: panel");
            exit;
        } else {
            $_SESSION['intentos']++;
            $error = "Usuario o contraseña incorrectos.";
        }

    } catch (PDOException $e) {
        die("Error en la conexion o consulta: " . $e->getMessage());
    }
}

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true): ?>
<!-- HTML login -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/ico/Logo.ico">
    <title>Admin | AlmagaraVPN</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .top-bar {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 1000;
}
        
        a.back {
    background-color: #4caf50;
    color: white;
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

        .login-box {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 400px;
        }

        .login-box h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .login-box form {
            display: flex;
            flex-direction: column;
        }
        
        .login-header {
    display: flex; /* flex normal, no inline-flex */
    align-items: center;
    justify-content: center; /* centra contenido */
    gap: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #333;
    width: fit-content; /* ancho ajustado al contenido */
    margin: 0 auto 20px auto; /* centra el bloque en el container */
}
.login-header img {
    width: 40px;
    height: auto;
}
.login-header h1 {
    font-size: clamp(20px, 5vw, 26px);
    margin: 0;
}

        .login-box input[type="text"],
        .login-box input[type="password"],
        .login-box input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            background-color: #2a2a2a;
            color: #ffffff;
        }

        .login-box input[type="submit"] {
            background-color: #4caf50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-box input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: #ff4c4c;
            margin-bottom: 10px;
            text-align: center;
        }
        
                @media (max-width: 600px) {
    a.back {
        padding: 10px 18px; /* algo menos para caber mejor */
        font-size: 16px;
        white-space: nowrap;
    }
}
    </style>
</head>
<body>
        <div class="top-bar">
    <a href="../index.html" class="back">Volver a inicio</a>
</div>
<div class="login-box">
        <div class="login-header">
    <img src="../../archivos/vpn/wireguard_logo.png" alt="WireGuard Logo">
    <h1>AlmagaraVPN</h1>
</div>
    <h2>Iniciar sesión</h2>
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <input type="submit" value="Entrar">
    </form>
</div>
</body>
</html>
<?php exit; endif; ?>
