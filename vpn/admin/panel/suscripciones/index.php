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

try {
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT s.id, c.nombre AS cliente, p.nombre AS plan, pp.duracion_meses AS duracion, 
               s.fecha_inicio, s.fecha_fin, s.activo, s.meses_pagados, IFNULL(NULLIF(s.ruta_perfiles, ''), 'Vacio') AS ruta_perfiles
        FROM suscripciones s
        JOIN cliente c ON s.cliente_id = c.id
        JOIN planes p ON s.plan_id = p.id
        JOIN planes_precios pp ON s.planes_precios_id = pp.id
        ORDER BY s.id DESC";

    $stmt = $conn->query($sql);
    $suscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Suscripciones | AlmagaraVPN</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #121212;
            color: #fff;
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
            color: #fff;
            min-width: 700px;
            border-radius: 6px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #2c2c2c;
        }
        tr:nth-child(even) {
            background-color: #2a2a2a;
        }
        tr:hover {
            background-color: #333;
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
        .activo {
            color: #4caf50;
            font-weight: bold;
        }
        .inactivo {
            color: #f44336;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            body {
                font-size: 14px;
            }
            th, td {
                padding: 6px;
            }
            table {
                min-width: 100%;
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
        <h1>Suscripciones</h1>
        <div class="back-container">
            <form action="../index.php" method="get">
                <input type="submit" class="back" value="Volver al panel" />
            </form>
        </div>
    </header>

    <a href="crear_suscripciones.php" class="btn-crear">Crear nueva suscripción</a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Plan</th>
                    <th>Duracion/Meses</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Pagado hasta (incluido)</th>
                    <th>Ruta perfiles</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($suscripciones): ?>
                
                    <?php 
                    setlocale(LC_TIME, 'es_ES.UTF-8'); // Asegurate de ponerlo antes del foreach
                    foreach ($suscripciones as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['id']) ?></td>
                            <td><?= htmlspecialchars($s['cliente']) ?></td>
                            <td><?= htmlspecialchars($s['plan']) ?></td>
                            <td><?= htmlspecialchars($s['duracion']) ?></td>
                            <td><?= htmlspecialchars($s['fecha_inicio']) ?></td>
                            <td><?= htmlspecialchars($s['fecha_fin']) ?></td>
                            <?php
    $fecha_inicio = new DateTime($s['fecha_inicio']);
$meses_pagados = isset($s['meses_pagados']) ? (int)$s['meses_pagados'] : 0;

$meses_es = [
    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
];

if ($meses_pagados == 0) {
    $pagado_hasta = "<span style='color:red;'>Sin pagar</span>";
} elseif ($meses_pagados >= $s['duracion']) {
    $pagado_hasta = "<span style='color:green;'>Pagado al completo</span>";
} else {
    $fecha_pagada = clone $fecha_inicio;
    $fecha_pagada->modify('+' . ($meses_pagados - 1) . ' months');
    $mes = (int)$fecha_pagada->format('n'); // 1-12
    $anio = $fecha_pagada->format('Y');
    $pagado_hasta = "<strong>" . ucfirst($meses_es[$mes - 1]) . " $anio</strong>";
}
?>
<td><?= $pagado_hasta ?></td>
<td><?= htmlspecialchars($s['ruta_perfiles']) ?></td>

                            <td class="<?= $s['activo'] ? 'activo' : 'inactivo' ?>">
                                <?= $s['activo'] ? 'Sí' : 'No' ?>
                            </td>
                            <td>
                                <a href="editar_suscripciones.php?id=<?= $s['id'] ?>" class="btn">Editar</a>
                                <a href="borrar_suscripciones.php?id=<?= $s['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Seguro que quieres borrar esta suscripción?');">Borrar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No hay suscripciones registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
