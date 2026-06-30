<?php
session_name('SESSION_CLIENTE');
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: ../index.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    $mensaje = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['editar_datos'])) {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');

            if ($nombre === '' || $apellidos === '' || $correo === '' || $usuario === '') {
                $error = "Todos los campos son obligatorios.";
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = "Correo no válido.";
            } else {
                $stmtCheck = $conn->prepare("SELECT id FROM cliente WHERE (usuario = :usuario OR correo = :correo) AND id != :id");
                $stmtCheck->execute(['usuario' => $usuario, 'correo' => $correo, 'id' => $id_usuario]);
                if ($stmtCheck->fetch()) {
                    $error = "El usuario o correo ya están en uso por otro usuario.";
                } else {
                    $stmtUpdate = $conn->prepare("UPDATE cliente SET nombre = :nombre, apellidos = :apellidos, correo = :correo, usuario = :usuario WHERE id = :id");
                    $stmtUpdate->execute([
                        'nombre' => $nombre,
                        'apellidos' => $apellidos,
                        'correo' => $correo,
                        'usuario' => $usuario,
                        'id' => $id_usuario
                    ]);
                    $mensaje = "Datos personales actualizados correctamente.";
                    $_SESSION['usuario'] = $usuario;
                }
            }
        } elseif (isset($_POST['cambiar_password'])) {
            $pass_actual = $_POST['pass_actual'] ?? '';
            $pass_nueva = $_POST['pass_nueva'] ?? '';
            $pass_confirm = $_POST['pass_confirm'] ?? '';

            if ($pass_actual === '' || $pass_nueva === '' || $pass_confirm === '') {
                $error = "Todos los campos de contraseña son obligatorios.";
            } elseif ($pass_nueva !== $pass_confirm) {
                $error = "La nueva contraseña y su confirmación no coinciden.";
            } elseif (strlen($pass_nueva) < 6) {
                $error = "La nueva contraseña debe tener al menos 6 caracteres.";
            } else {
                $stmtPass = $conn->prepare("SELECT password FROM cliente WHERE id = :id");
                $stmtPass->execute(['id' => $id_usuario]);
                $row = $stmtPass->fetch(PDO::FETCH_ASSOC);

                if (!$row || !password_verify($pass_actual, $row['password'])) {
                    $error = "La contraseña actual es incorrecta.";
                } else {
                    $hashNueva = password_hash($pass_nueva, PASSWORD_DEFAULT);
                    $stmtUpdPass = $conn->prepare("UPDATE cliente SET password = :pass WHERE id = :id");
                    $stmtUpdPass->execute(['pass' => $hashNueva, 'id' => $id_usuario]);
                    $mensaje = "Contraseña actualizada correctamente.";
                }
            }
        }
    }

    // Cargar datos actuales para mostrar en formulario
    $stmt = $conn->prepare("SELECT nombre, apellidos, usuario, correo FROM cliente WHERE id = :id");
    $stmt->execute(['id' => $id_usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cargar suscripcion activa y datos del plan y precio (ahora uniendo con planes_precios por planes_precios_id)
    $sql = "SELECT 
    DATE_FORMAT(s.fecha_inicio, '%d/%m/%Y') AS fecha_inicio,
    DATE_FORMAT(s.fecha_fin, '%d/%m/%Y') AS fecha_fin,
    s.activo AS suscripcion_activa,
    s.meses_pagados,
    s.ruta_perfiles,
    p.nombre AS plan_nombre,
    p.perfiles,
    p.descripcion,
    pp.duracion_meses,
    pp.precio_mes,
    pp.precio_total
FROM suscripciones s
JOIN planes_precios pp ON pp.id = s.planes_precios_id
JOIN planes p ON p.id = pp.plan_id
WHERE s.cliente_id = :id_usuario 
  AND s.activo = 1
ORDER BY s.fecha_inicio DESC
LIMIT 1;";


    $stmt2 = $conn->prepare($sql);
    $stmt2->execute(['id_usuario' => $id_usuario]);
    $info = $stmt2->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la conexion o consulta: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Cliente | AlmagaraVPN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body {
    margin: 0;
    padding: 0;
    background-color: #121212;
    color: #ffffff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 100%;
    overflow-x: hidden;
}

body {
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
.container {
    width: 100%;
    max-width: 700px;
    margin: auto;
    background-color: #1f1f1f;
    padding: 35px;
    border-radius: 14px;
    box-shadow: 0 0 25px rgba(0,0,0,0.6);
    box-sizing: border-box;
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

h1, h2 {
    margin-top: 0;
    font-size: 26px;
    font-weight: 600;
    color: #ffffff;
    border-bottom: 1px solid #333;
    padding-bottom: 10px;
}

h3 {
    margin-top: 0;
    font-size: 26px;
    font-weight: 600;
    color: #ffffff;
    border-top: 1px solid #333;
    padding-top: 20px;
    border-bottom: 1px solid #333;
    padding-bottom: 10px;
}

.info {
    margin-bottom: 12px;
    font-size: 16px;
    line-height: 1.5;
    color: #dddddd;
}

label {
    display: block;
    margin: 18px 0 6px;
    font-size: 15px;
    color: #cccccc;
}

input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #444;
    background-color: #2a2a2a;
    color: #fff;
    font-size: 16px;
    box-sizing: border-box;
    transition: border 0.2s ease, box-shadow 0.2s ease;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    border-color: #4caf50;
    box-shadow: 0 0 4px #4caf50;
    outline: none;
}

input[type="submit"] {
    margin-top: 20px;
    width: 100%;
    padding: 14px;
    background-color: #4caf50;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.1s ease;
}

input[type="submit"]:hover {
    background-color: #43a047;
    transform: scale(1.02);
}

.mensaje, .error {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 15px;
    word-wrap: break-word;
}

.mensaje {
    background-color: #2e7d32;
    color: #ffffff;
}

.error {
    background-color: #c62828;
    color: #ffffff;
}

.logout-btn {
    display: block; /* cambio importante */
    margin: 30px auto 0; /* centrado horizontal gracias al 'auto' */
    padding: 14px 20px;
    background-color: #f44336;
    color: #fff;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.1s ease;
    width: 100%;
    max-width: 700px;
    box-sizing: border-box;
}


.logout-btn:hover {
    background-color: #d32f2f;
    transform: scale(1.02);
}

hr {
    border: 0;
    border-top: 1px solid #333;
    margin: 35px 0;
}

.accordion {
    background-color: #2e2e2e;
    color: #ffffff;
    cursor: pointer;
    padding: 14px;
    width: 100%;
    border: none;
    text-align: left;
    outline: none;
    font-size: 16px;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: background-color 0.3s ease;
}

.accordion:hover {
    background-color: #3a3a3a;
}

.panel {
    padding: 0 0 20px 0;
    display: none;
    overflow: hidden;
    animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.perfil-btn {
    display: inline-block;
    background-color: #2e7d32;
    color: #ffffff;
    padding: 10px 18px;
    margin: 5px 10px 5px 0;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.1s ease;
    text-decoration: none;
}

.perfil-btn:hover {
    background-color: #1b5e20;
    transform: scale(1.05);
}

.perfil-actions {
    margin-top: 5px;
}

.perfil-actions {
    display: flex;
    gap: 10px; /* separacion uniforme entre botones */
    margin-top: 10px;
    flex-wrap: wrap; /* si no caben en una fila, se bajan automaticamente */
}

.perfil-actions a {
    flex: 1; /* que ambos botones ocupen el mismo ancho */
    text-align: center;
    background-color: #444;
    color: #fff;
    padding: 10px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.1s ease;
    min-width: 100px; /* evita que se hagan demasiado pequeños */
}

.perfil-actions a:hover {
    background-color: #555;
    transform: scale(1.03);
}

.perfiles-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* escritorio */
    gap: 15px;
    margin-top: 15px;
}

.perfil-item {
    display: flex;
    align-items: center; /* alineación vertical */
    justify-content: space-between; /* nombre a la izquierda, botones a la derecha */
    background-color: #2a2a2a;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.perfil-nombre {
    font-size: 16px;
    color: #ffffff;
    flex: 1; /* ocupa todo el espacio disponible */
    margin-right: 12px; /* espacio entre texto y botones */
}

.perfil-actions {
    display: flex;
    gap: 8px; /* espacio entre botones */
}

.btn-cuadro {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: #444;
    color: #fff;
    border-radius: 6px;
    font-size: 16px;
    text-decoration: none;
    transition: background-color 0.3s, transform 0.1s;
}

.btn-cuadro:hover {
    background-color: #555;
    transform: scale(1.1);
}

/* tablets y pantallas medianas */
@media (max-width: 900px) {
    .perfiles-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* móviles */
@media (max-width: 480px) {
    .perfiles-grid {
        grid-template-columns: 1fr;
    }
}

/* version 2 columnas (por si quieres cambiar) */
.perfiles-grid.cols-2 {
    grid-template-columns: repeat(2, 1fr);
}


/* Responsive para móviles */
@media (max-width: 480px) {
    body {
        padding: 10px;
    }

    a.back {
        padding: 10px 18px; /* algo menos para caber mejor */
        font-size: 16px;
        white-space: nowrap;
    }

    .container {
        padding: 20px;
    }

    h1, h2, h3 {
        font-size: 22px;
    }

    label, .info {
        font-size: 14px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="submit"],
    .logout-btn {
        font-size: 15px;
    }

    .logout-btn {
        margin-top: 20px;
    }
}
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="../../index.html" class="back"><i class="fas fa-home"></i></a>
    </div>
<div class="container">
    <div class="panel-header">
    <img src="../../../archivos/vpn/wireguard_logo.png" alt="WireGuard Logo">
    <p>AlmagaraVPN</p>
    </div>
    <h1>Bienvenido, <?= htmlspecialchars($user['nombre'] ?? $_SESSION['usuario']) ?></h1>
    
    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h2>Tu suscripción actual</h2>
    <?php if ($info && $info['suscripcion_activa'] == 1): ?>
    <div class="info"><strong>Plan:</strong> <?= htmlspecialchars($info['plan_nombre']) ?></div>
    <div class="info"><strong>Perfiles:</strong> <?= (int)$info['perfiles'] ?></div>
    <div class="info"><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($info['descripcion'] ?: 'Sin descripción')) ?></div>
    <div class="info"><strong>Duración (meses):</strong> <?= (int)$info['duracion_meses'] ?></div>
    <div class="info"><strong>Precio mensual:</strong> <?= number_format($info['precio_mes'], 2, ',') ?>€</div>
    <div class="info"><strong>Precio total:</strong> <?= number_format($info['precio_total'], 2, ',') ?>€</div>
    <?php
$fecha_inicio_raw = $info['fecha_inicio'];
$meses_pagados = (int)($info['meses_pagados'] ?? 0);
$duracion_total = (int)($info['duracion_meses'] ?? 0);
$meses_es = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

if ($meses_pagados === 0) {
    $pagado_hasta = "<span style='color:red;'>Sin pagar</span>";
} elseif ($meses_pagados >= $duracion_total) {
    $pagado_hasta = "<span style='color:lightgreen;'>Pagado al completo</span>";
} else {
    // fecha_inicio viene como dd/mm/YYYY desde el DATE_FORMAT de la consulta
    $fecha_base = DateTime::createFromFormat('d/m/Y', $fecha_inicio_raw);
    if ($fecha_base) {
        $offset = max(0, $meses_pagados - 1); // <- aqui el ajuste clave
        $fecha_base->modify('+' . $offset . ' months');

        // aqui SI es -1 (mes 1..12 -> indice 0..11)
        $mes_texto = $meses_es[((int)$fecha_base->format('n')) - 1];
        $anio = $fecha_base->format('Y');
        $pagado_hasta = ucfirst($mes_texto) . " " . $anio;
    } else {
        $pagado_hasta = "<span style='color:orange;'>Error en fecha</span>";
    }
}


?>
<div class="info"><strong>Pagado hasta (incluido):</strong> <?= $pagado_hasta ?></div>

<div class="info"><strong>Meses pagados:</strong> <?= $meses_pagados ?>/<?= (int) $info['duracion_meses'] ?> meses</div>

    <div class="info"><strong>Fecha inicio:</strong> <?= htmlspecialchars($info['fecha_inicio']) ?></div>
    <div class="info"><strong>Fecha fin:</strong> <?= htmlspecialchars($info['fecha_fin']) ?></div>

    <?php

if (!empty($info['ruta_perfiles'])) {
    $ruta = rtrim($info['ruta_perfiles'], '/') . '/'; // asegurar que termina con /
    $archivos = glob($ruta . "*.conf"); // obtiene todos los .conf
    if ($archivos) {
        echo '<h3>Perfiles VPN</h3>';
        echo '<div class="perfiles-grid">'; // grid en lugar de "info"

foreach ($archivos as $archivo) {
    $nombreArchivo = pathinfo($archivo, PATHINFO_FILENAME);
    $nombreMostrar = $nombreArchivo;
    $subcarpeta = basename(rtrim($info['ruta_perfiles'], '/'));
    $rutaRelativa = $subcarpeta . '/' . basename($archivo);

    echo '<div class="perfil-btn">';
echo htmlspecialchars($nombreMostrar);
echo '<div class="perfil-actions">';
echo '<a href="descargar.php?archivo=' . urlencode($rutaRelativa) . '" title="Descargar"><i class="fa-solid fa-download"></i></a>';
echo '<a href="#" class="ver-qr" data-archivo="' . htmlspecialchars($rutaRelativa) . '" title="Ver QR"><i class="fa-solid fa-qrcode"></i></a>';
echo '</div>';
echo '</div>';
}

echo '</div>';
    } else {
        echo "<div class='info'>No hay perfiles disponibles.</div>";
    }
}

?>

    <?php else: ?>
    <p>No tienes una suscripción activa.</p>
    <?php endif; ?>

<hr />


<button type="button" class="accordion">Editar datos personales</button>
<div class="panel">
    <form method="post" action="">
        <input type="hidden" name="editar_datos" value="1" />
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($user['nombre'] ?? '') ?>" />
        <label for="apellidos">Apellidos:</label>
        <input type="text" id="apellidos" name="apellidos" required value="<?= htmlspecialchars($user['apellidos'] ?? '') ?>" />
        <label for="correo">Correo electrónico:</label>
        <input type="email" id="correo" name="correo" required value="<?= htmlspecialchars($user['correo'] ?? '') ?>" />
        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" required value="<?= htmlspecialchars($user['usuario'] ?? '') ?>" />
        <input type="submit" value="Guardar cambios" />
    </form>
</div>

<hr />

<button type="button" class="accordion">Cambiar contraseña</button>
<div class="panel">
    <form method="post" action="">
        <input type="hidden" name="cambiar_password" value="1" />
        <label for="pass_actual">Contraseña actual:</label>
        <input type="password" id="pass_actual" name="pass_actual" required />
        <label for="pass_nueva">Nueva contraseña:</label>
        <input type="password" id="pass_nueva" name="pass_nueva" required />
        <label for="pass_confirm">Confirmar nueva contraseña:</label>
        <input type="password" id="pass_confirm" name="pass_confirm" required />
        <input type="submit" value="Cambiar contraseña" />
    </form>
</div>


</div>
    <a href="../logout.php" class="logout-btn">Cerrar sesión</a>
</div>
<!-- Modal QR -->
<div id="qrModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background: rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:10000;">
    <div style="background:#1f1f1f; padding:20px; border-radius:12px; position:relative; max-width:90%; text-align:center;">
        <span id="closeModal" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:24px; color:white;">&times;</span>
        <h2 style="color:white; margin-bottom:15px;">QR del perfil</h2>
        <div id="qrcode" style="margin: 0 auto; padding: 15px; background: white; border-radius: 8px; display: inline-block;"></div>
    </div>
</div>

<script src="qrcode.min.js"></script>
<script>
document.querySelectorAll(".accordion").forEach(button => {
    button.addEventListener("click", () => {
        const panel = button.nextElementSibling;
        const isVisible = panel.style.display === "block";
        document.querySelectorAll(".panel").forEach(p => p.style.display = "none");
        if (!isVisible) {
            panel.style.display = "block";
        }
    });
});

document.querySelectorAll(".ver-qr").forEach(btn => {
    btn.addEventListener("click", e => {
        e.preventDefault();
        const archivo = btn.dataset.archivo;
        const qrContainer = document.getElementById("qrcode");

        // Limpiar el QR anterior
        qrContainer.innerHTML = "";

        // Obtener el contenido del perfil mediante AJAX
        fetch("ver_qr.php?archivo=" + encodeURIComponent(archivo))
            .then(response => {
                if (!response.ok) {
                    throw new Error("No se pudo obtener el contenido del perfil.");
                }
                return response.text();
            })
            .then(text => {
                // Generar el código QR de manera local en el navegador
                new QRCode(qrContainer, {
                    text: text.trim(),
                    width: 256,
                    height: 256,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.M
                });

                document.getElementById("qrModal").style.display = "flex";
            })
            .catch(err => {
                alert(err.message);
            });
    });
});

document.getElementById("closeModal").addEventListener("click", () => {
    document.getElementById("qrModal").style.display = "none";
});
</script>

</body>
</html>
