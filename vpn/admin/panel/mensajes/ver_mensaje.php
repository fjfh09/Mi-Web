<?php
session_name('SESSION_ADMIN');
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID no valido.");
}

$id = (int) $_GET['id'];

try {
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // marcar como leido
    $sql = "UPDATE mensajes SET visto = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    $sql = "SELECT * FROM mensajes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $mensaje = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mensaje) {
        die("Mensaje no encontrado.");
    }
} catch (PDOException $e) {
    die("Error BD: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mensaje | AlmagaraVPN</title>
<style>
    /* Reset basico */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    background: #121212;
    color: #fff;
    font-family: Arial, sans-serif;
    padding: 16px;
    margin: 0;
}

/* --- Encabezado superior (logo + titulo) --- */
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
.panel-header img { width: 40px; height: auto; }
.panel-header p { font-size: clamp(18px, 4vw, 24px); font-weight: 700; }

/* --- Header de pagina (titulo + boton volver) --- */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    flex-wrap: wrap;
    gap: 10px;
}
h1 {
    font-size: 24px;
}
.back-container input.back {
    background: #4caf50;
    color: #fff;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}
.back-container input.back:hover { background: #43a047; }

/* --- Barra de acciones globales --- */
.acciones-globales {
    display: none;
    gap: 10px;
    align-items: center;
    margin-bottom: 12px;
    padding: 8px;
    border-radius: 8px;
    background: #1a1a1a;
    border: 1px solid #272727;
    flex-wrap: wrap;
}
.acciones-globales button {
    background: #333;
    border: 0;
    margin: 4px 0;
    color: #fff;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
}
.acciones-globales button:hover { background: #444; }
.acciones-globales .btn-danger { background: #f44336; }
.acciones-globales .btn-danger:hover { background: #d32f2f; }

/* --- Controles lista --- */
.lista-controls {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.lista-controls input[type="checkbox"] { transform: scale(1.1); cursor: pointer; }

/* --- Lista de mensajes --- */
.mensajes-lista {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.mensaje-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #333;
    background: #1a1a1a;
    cursor: pointer;
    position: relative;
}
.mensaje-item.no-leido::before {
    content: "";
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    background: #2196f3;
    border-radius: 4px 0 0 4px;
}
.mensaje-item .checkbox { flex: 0 0 auto; z-index: 1; }
.mensaje-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
    overflow: hidden;
}
.mensaje-info .nombre { font-weight: 600; }
.mensaje-info .asunto { color: #bbb; font-size: 14px; }
.mensaje-fecha {
    font-size: 13px;
    color: #aaa;
    white-space: nowrap;
    margin-left: auto;
}

/* Estados */
.mensaje-item.leido { background: #1a1a1a; font-weight: 400; }
.mensaje-item.no-leido { background: #252525; font-weight: 700; }
.mensaje-item:hover { background: #2a2a2a; }

/* --- Pagina detalle mensaje --- */
.header-mensaje {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 12px;
    border-bottom: 1px solid #333;
    gap: 10px;
}
.info-mensaje h2 {
    margin: 0 0 6px;
    font-size: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.remitente { font-size: 14px; color: #bbb; }
.fecha { font-size: 12px; color: #888; }
.acciones {
    display: flex;
    gap: 10px;
    padding: 12px;
    border-bottom: 1px solid #333;
    flex-wrap: wrap;
}
.btn {
    background: #333;
    border: none;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
}
.btn:hover { background: #444; }
.btn-borrar { background: #f44336; }
.btn-borrar:hover { background: #d32f2f; }
.contenido-mensaje {
    padding: 12px;
    line-height: 0.8;
    white-space: pre-wrap;
}

/* --- Responsive --- */
@media (max-width: 600px) {
    header h1 { font-size: 20px; }
    .mensaje-info .asunto { font-size: 13px; }
    .mensaje-fecha { font-size: 12px; }
    .acciones { flex-direction: column; }
    .btn { width: 100%; text-align: center; }
}
@media (min-width: 601px) and (max-width: 900px) {
    header { flex-wrap: wrap; }
    .mensaje-fecha { font-size: 13px; }
}
</style>
</head>
<body>
<div class="panel-header">
    <img src="../../../../archivos/vpn/wireguard_logo.png" alt="WireGuard Logo">
    <p>AlmagaraVPN</p>
</div>

<header>
    <h1>Mensaje</h1>
    <div class="back-container">
        <form action="index.php" method="get">
            <input type="submit" class="back" value="Volver a mensajes" />
        </form>
    </div>
</header>
    
<div class="header-mensaje">
    <div class="info-mensaje" style="flex:1; min-width:0;">
        <h2 style="margin:0; font-size:20px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <?= htmlspecialchars($mensaje['asunto']) ?>
        </h2>
        <div class="remitente" style="font-size:14px; color:#bbb;">
            <?= htmlspecialchars($mensaje['nombre']) ?> &lt;<?= htmlspecialchars($mensaje['correo']) ?>&gt;
        </div>
        <div class="fecha" style="font-size:12px; color:#888;">
            <?= htmlspecialchars($mensaje['fecha']) ?>
        </div>
    </div>
</div>

<div class="acciones">
    <form method="post" action="acciones_mensajes.php" style="display:inline;">
        <input type="hidden" name="seleccionados[]" value="<?= $mensaje['id'] ?>">
        <button type="submit" name="accion" value="noleido" class="btn">✉ Marcar como no leído</button>
    </form>
    <a href="borrar_mensaje.php?id=<?= $mensaje['id'] ?>" onclick="return confirm('¿Seguro que quieres borrar este mensaje?')">
        <button class="btn btn-borrar">🗑 Borrar</button>
    </a>
</div>

<div class="contenido-mensaje">
    <?= nl2br(htmlspecialchars($mensaje['mensaje'])) ?>
</div>
</body>
</html>