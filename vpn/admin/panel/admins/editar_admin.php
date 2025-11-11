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

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true || $_SESSION['usuario'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$mensaje = "";
if (!isset($_GET['id'])) die("ID no especificado.");
$id = intval($_GET['id']);

try {
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = trim($_POST['usuario']);
        $password = trim($_POST['password']);

        if (strlen($usuario) >= 3) {
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $mensaje = "La contraseña debe tener al menos 6 caracteres.";
                } else {
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admins SET usuario = ?, password = ? WHERE id = ?");
                    $stmt->execute([$usuario, $pass_hash, $id]);
                    header("Location: index.php?creado=1");
                    exit;
                }
            } else {
                $stmt = $conn->prepare("UPDATE admins SET usuario = ? WHERE id = ?");
                $stmt->execute([$usuario, $id]);
                header("Location: index.php?creado=1");
                exit;
            }
        } else {
            $mensaje = "El nombre de usuario debe tener al menos 3 caracteres.";
        }
    }

    $stmt = $conn->prepare("SELECT usuario FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin) die("Administrador no encontrado.");
} catch (PDOException $e) {
    $mensaje = "Error BD: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" href="/ico/Logo.ico" />
<title>Editar Admin | AlmagaraVPN</title>
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

    form#form-editar {
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
    <h1>Editar Administrador</h1>
    <div class="back-container">
        <form action="index.php" method="get">
            <input type="submit" class="back" value="Volver a admins" />
        </form>
    </div>
</header>

<form id="form-editar" method="post" autocomplete="off" novalidate>
    <input type="text" name="usuario" placeholder="Usuario" value="<?= htmlspecialchars($admin['usuario']) ?>" required minlength="3" />
    <input type="password" name="password" placeholder="Nueva contraseña (opcional)" minlength="6" />
    <button type="submit">Guardar cambios</button>
    <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
</form>

</body>
</html>