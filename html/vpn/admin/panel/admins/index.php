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

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true || $_SESSION['usuario'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    $stmt = $conn->query("SELECT id, usuario FROM admins ORDER BY id DESC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error conexion BD: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="/ico/Logo.ico" />
    <title>Administradores | AlmagaraVPN</title>
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

        .panel-header {
    display: flex; /* flex normal, no inline-flex */
    align-items: center;
    justify-content: center; /* centra contenido */
    gap: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #333;
    width: fit-content; /* ancho ajustado al contenido */
    margin: 0 auto 20px auto; /* centra el bloque en el container */
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

        .btn-crear {
            background-color: #2196f3;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-bottom: 15px;
            display: inline-block;
        }

        .btn-crear:hover {
            background-color: #1976d2;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #1e1e1e;
            color: #ffffff;
            min-width: 200px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left;
        }

        th {
            background-color: #2c2c2c;
        }

        tr:nth-child(even) {
            background-color: #2a2a2a;
        }

        tr:hover {
            background-color: #333333;
        }

        a.btn {
            background-color: #2196f3;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 5px;
            font-size: 14px;
            display: inline-block;
        }

        a.btn:hover {
            background-color: #1976d2;
        }

        a.btn-danger {
            background-color: #f44336;
        }

        a.btn-danger:hover {
            background-color: #d32f2f;
        }

        @media (max-width: 600px) {
            body {
                font-size: 14px;
            }

            th, td {
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="panel-header">
    <img src="../../../../archivos/vpn/wireguard_logo.png" alt="WireGuard Logo">
    <p>AlmagaraVPN</p>
    </div>
    <header>
        <h1>Administradores</h1>
        <div class="back-container">
            <form action="../index.php" method="get">
                <input type="submit" class="back" value="Volver al panel" />
            </form>
        </div>
    </header>

    <a href="crear_admin.php" class="btn-crear">Crear nuevo admin</a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($admins): ?>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?= htmlspecialchars($admin['id']) ?></td>
                            <td><?= htmlspecialchars($admin['usuario']) ?></td>
                            <td>
                                <a href="editar_admin.php?id=<?= $admin['id'] ?>" class="btn">Editar</a>
                                <a href="borrar_admin.php?id=<?= $admin['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas borrar este administrador?');">Borrar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">No hay administradores.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>