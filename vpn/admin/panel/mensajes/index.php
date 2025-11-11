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

// Control de sesion y expiracion
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

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: ../index.php");
    exit;
}

// Conexion a la BD
try {
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM mensajes ORDER BY fecha DESC";
    $stmt = $conn->query($sql);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error conexion BD: " . $e->getMessage());
}

// Fallback para formatear fecha si no existe la funcion formatearFecha()
function safe_format_fecha($fecha) {
    if (!$fecha) return '';
    $ts = strtotime($fecha);
    if ($ts === false) return htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
    return date('d/m/Y H:i', $ts);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mensajes Recibidos | AlmagaraVPN</title>
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
.mensaje-info .asunto { color: #bbb; font-size: 14px; }
.mensaje-fecha {
    font-size: 13px;
    color: #aaa;
    white-space: nowrap;
    margin-left: auto;
}

/* Estados */
.mensaje-item.leido { background: #1a1a1a; }
.mensaje-item.no-leido { background: #252525; }

/* nombre y fecha cuando el mensaje esta no leido */
.mensaje-item.no-leido .mensaje-info .nombre,
.mensaje-item.no-leido .mensaje-fecha {
    font-weight: 600;
}

/* nombre y fecha cuando esta leido */
.mensaje-item.leido .mensaje-info .nombre,
.mensaje-item.leido .mensaje-fecha {
    font-weight: 400;
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
    padding: 7px;
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
        <h1>Mensajes recibidos</h1>
        <div class="back-container">
            <form action="../index.php" method="get">
                <input type="submit" class="back" value="Volver al panel" />
            </form>
        </div>
    </header>

<div class="acciones-globales" id="accionesGlobales">
    <form id="accionesForm" method="post" action="acciones_mensajes.php">
        <button type="submit" name="accion" id="btnToggleLeido" value="leido">✔ Marcar como leido</button>
        <button type="submit" name="accion" value="borrar" class="btn-danger" onclick="return confirm('Seguro que quieres borrar los seleccionados?')">🗑 Borrar</button>
    </form>
</div>

<div class="lista-controls">
    <label class="select-all"><input type="checkbox" id="masterCheckbox"> Seleccionar todos</label>
</div>

<div class="mensajes-lista">
<?php if(!empty($datos)): ?>
    <?php foreach($datos as $fila): ?>
    <div class="mensaje-item <?= $fila['visto'] ? 'leido' : 'no-leido' ?>" data-id="<?= htmlspecialchars($fila['id'], ENT_QUOTES, 'UTF-8') ?>">
    <div class="checkbox">
        <input type="checkbox" class="chk-item" name="seleccionados[]" form="accionesForm" value="<?= htmlspecialchars($fila['id'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="mensaje-info">
        <div class="nombre"><?= htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="asunto"><?= htmlspecialchars($fila['asunto'], ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="mensaje-fecha"><?= htmlspecialchars(function_exists('formatearFecha') ? formatearFecha($fila['fecha']) : safe_format_fecha($fila['fecha']), ENT_QUOTES, 'UTF-8') ?></div>
</div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No hay datos para mostrar.</p>
<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const acciones = document.getElementById('accionesGlobales');
    const btnToggle = document.getElementById('btnToggleLeido');
    const master = document.getElementById('masterCheckbox');

    const getChecks = () => Array.from(document.querySelectorAll('.chk-item'));

    function actualizarAcciones(){
        const seleccionadas = getChecks().filter(c => c.checked);
        if(seleccionadas.length > 0){
            acciones.style.display = 'flex';
            const hayNoLeidos = seleccionadas.some(c => {
                const fila = c.closest('.mensaje-item');
                return fila && fila.classList.contains('no-leido');
            });
            btnToggle.textContent = hayNoLeidos ? '✔ Marcar como leido' : '✉ Marcar como no leido';
            btnToggle.value = hayNoLeidos ? 'leido' : 'noleido';
        } else {
            acciones.style.display = 'none';
            btnToggle.value = '';
            btnToggle.textContent = '✔ Marcar como leido';
        }
    }

    // select all si existe master
    if(master){
        master.addEventListener('change', function(){
            getChecks().forEach(c => c.checked = master.checked);
            actualizarAcciones();
        });
    }

    // delegacion de clicks en checkboxes (funciona aunque se carguen dinamicamente)
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList && e.target.classList.contains('chk-item')){
            e.stopPropagation();
            actualizarAcciones();
        }
    });

    // filas clicables para ver detalle
    document.querySelectorAll('.mensaje-item[data-id]').forEach(r => {
        r.addEventListener('click', function(e){
            if(e.target && e.target.closest && e.target.closest('input')) return;
            window.location.href = 'ver_mensaje.php?id=' + encodeURIComponent(r.dataset.id);
        });
    });

    // inicializa estado
    actualizarAcciones();
});
</script>

</body>
</html>