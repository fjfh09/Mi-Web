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
$usuario = null;

// Conexion BD
try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';
} catch (PDOException $e) {
    die("Error conexion BD: " . $e->getMessage());
}

// Obtener datos del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar edicion
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $usuario_nombre = trim($_POST['usuario']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $nueva_pass = $_POST['password'];

    if (strlen($nombre) < 3 || strlen($apellidos) < 3 || strlen($usuario_nombre) < 3 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Datos invalidos. Nombre/apellidos/usuario min. 3 caracteres. Correo valido.";
    } else {
        try {
            if (!empty($nueva_pass) && strlen($nueva_pass) < 6) {
                $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
            } else {
                if (!empty($nueva_pass)) {
                    $pass_hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE cliente SET nombre = ?, apellidos = ?, correo = ?, usuario = ?, password = ?, activo = ? WHERE id = ?");
                    $stmt->execute([$nombre, $apellidos, $correo, $usuario_nombre, $pass_hash, $activo, $id]);
                } else {
                    $stmt = $conn->prepare("UPDATE cliente SET nombre = ?, apellidos = ?, correo = ?, usuario = ?, activo = ? WHERE id = ?");
                    $stmt->execute([$nombre, $apellidos, $correo, $usuario_nombre, $activo, $id]);
                }
                header("Location: index.php?mensaje=actualizado");
exit;
            }
        } catch (PDOException $e) {
            $mensaje = "Error en la base de datos: " . htmlspecialchars($e->getMessage());
        }
    }
} elseif (isset($_GET['id'])) {
    // Obtener datos por ID
    $stmt = $conn->prepare("SELECT * FROM cliente WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado.");
    }
} else {
    die("ID de usuario no proporcionado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="/ico/Logo.ico" />
    <title>Editar Usuario | AlmagaraVPN</title>
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

input::placeholder {
    color: #aaaaaa;
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

label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    margin-top: 10px;
}

input[type="checkbox"] {
    width: auto;
    accent-color: #4caf50;
    transform: scale(1.2);
    cursor: pointer;
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
    <h1>Editar Usuario VPN</h1>
    <div class="back-container">
        <form action="index.php" method="get">
            <input type="submit" class="back" value="Volver a usuarios" />
        </form>
    </div>
</header>

<?php if ($usuario): ?>
<form id="form-editar" method="post" autocomplete="off" novalidate>
    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>" />
    <input type="text" name="nombre" placeholder="Nombre" required value="<?= htmlspecialchars($usuario['nombre']) ?>" />
    <input type="text" name="apellidos" placeholder="Apellidos" required value="<?= htmlspecialchars($usuario['apellidos']) ?>" />
    <input type="email" name="correo" placeholder="Correo" required value="<?= htmlspecialchars($usuario['correo']) ?>" />
    <input type="text" name="usuario" placeholder="Usuario" required value="<?= htmlspecialchars($usuario['usuario']) ?>" />
    <input type="password" name="password" placeholder="Nueva contraseña (dejar vacio para no cambiar)" />
    
    <label>
        <input type="checkbox" name="activo" <?= $usuario['activo'] ? 'checked' : '' ?> /> Usuario activo
    </label>

    <button type="submit">Actualizar</button>
    <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
</form>
<?php endif; ?>

</body>
</html>