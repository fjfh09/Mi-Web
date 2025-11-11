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

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Borrar el usuario (y opcionalmente sus suscripciones si lo necesitas)
    $stmt = $conn->prepare("DELETE FROM cliente WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8" />
        <title>Error Borrar Usuario | AlmagaraVPN</title>
        <style>
            body {
                background-color: #121212;
                color: #eee;
                font-family: Arial, sans-serif;
                padding: 20px;
                text-align: center;
            }
            .error {
                background: #f44336;
                padding: 20px;
                border-radius: 8px;
                display: inline-block;
                margin-top: 50px;
            }
            a {
                color: #4caf50;
                text-decoration: none;
                margin-top: 20px;
                display: inline-block;
            }
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>Error al borrar el usuario</h1>
            <p><?= htmlspecialchars($e->getMessage()) ?></p>
            <a href="index.php">&laquo; Volver a usuarios</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}