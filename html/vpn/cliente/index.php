<?php
session_name('SESSION_CLIENTE');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Si ya esta autenticado, redirige al panel
if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) {
    header("Location: panel");
    exit;
}

// Regenerar ID sesion una vez
if (!isset($_SESSION['regenerado'])) {
    session_regenerate_id(true);
    $_SESSION['regenerado'] = true;
}

// Control tiempo de sesion (900s = 15 minutos)
if (isset($_SESSION['ultima_actividad']) && time() - $_SESSION['ultima_actividad'] > 900) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
$_SESSION['ultima_actividad'] = time();

$error = '';

// Control de intentos max 5
if (!isset($_SESSION['intentos'])) $_SESSION['intentos'] = 0;
if ($_SESSION['intentos'] >= 3) {
    die('Demasiados intentos fallidos. Intenta mas tarde.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim(strip_tags($_POST['usuario'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($identificador === '' || $password === '') {
        $error = "Completa todos los campos";
    } else {
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

            $stmt = $conn->prepare("SELECT id, usuario, password, activo FROM cliente WHERE usuario = :id OR correo = :id LIMIT 1");
            $stmt->execute(['id' => $identificador]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
    if ($user['activo'] == 1) {
        $_SESSION['autenticado'] = true;
        $_SESSION['id_usuario'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['intentos'] = 0;
        header("Location: panel");
        exit;
    } else {
        $error = "Usuario desactivado";
    }
} else {
    $_SESSION['intentos']++;
    $error = "Usuario o contraseña incorrectos";
}


        } catch (PDOException $e) {
            die("Error en la conexion o consulta: " . $e->getMessage());
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
    <title>Cliente | AlmagaraVPN</title>
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
            .admin-link {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .admin-link a {
            color: #4caf50;
            font-weight: bold;
            text-decoration: none;
        }
        .admin-link a:hover {
            color: #45a049;
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
    <div class="admin-link">
        ¿Eres admin? <a href="https://vpn.almagara.es/admin">Inicia sesión aquí</a>
    </div>
    <?php if ($error !== ''): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <input type="text" name="usuario" placeholder="Usuario o correo" required autofocus />
        <input type="password" name="password" placeholder="Contraseña" required />
        <input type="submit" value="Entrar" />
    </form>
</div>
</body>
</html>
