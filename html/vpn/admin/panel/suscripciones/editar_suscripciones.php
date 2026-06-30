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
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    $clientes = $conn->query("SELECT id, nombre FROM cliente ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $planes = $conn->query("SELECT id, nombre FROM planes ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM suscripciones WHERE id = ?");
    $stmt->execute([$id]);
    $suscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$suscripcion) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error conexion BD: " . $e->getMessage());
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $plan_id = $_POST['plan_id'] ?? '';
    $duracion_meses = $_POST['duracion_meses'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    $meses_pagados = isset($_POST['meses_pagados']) ? (int)$_POST['meses_pagados'] : 0; // ✅ añadido

    if (!$cliente_id) $errores[] = "Debe seleccionar un cliente.";
    if (!$plan_id) $errores[] = "Debe seleccionar un plan.";
    if (!$duracion_meses) $errores[] = "Debe seleccionar una duración.";
    if (!$fecha_inicio) $errores[] = "Debe indicar la fecha de inicio.";

    $fecha_fin = '';
    if (!$errores) {
        $fecha = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
        if (!$fecha) {
            $errores[] = "Fecha de inicio inválida.";
        } else {
            $fecha->modify("+{$duracion_meses} months");
            $fecha->modify("-1 day");
            $fecha_fin = $fecha->format('Y-m-d');
        }
    }

    if (empty($errores)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM planes_precios WHERE plan_id = ? AND duracion_meses = ?");
            $stmt->execute([$plan_id, $duracion_meses]);
            $planes_precios_id = $stmt->fetchColumn();

            if (!$planes_precios_id) {
                $errores[] = "La duración seleccionada no es válida para el plan.";
            } else {
                $ruta_perfiles = $_POST['ruta_perfiles'] ?? '';

if ($ruta_perfiles !== '') {
    // Quita posibles / al inicio para no duplicar
    $ruta_perfiles = '/perfiles/' . ltrim($ruta_perfiles, '/');
} else {
    $ruta_perfiles = null; // o '' si prefieres
}


$sql = "UPDATE suscripciones 
        SET cliente_id = ?, plan_id = ?, planes_precios_id = ?, 
            fecha_inicio = ?, fecha_fin = ?, meses_pagados = ?, 
            activo = ?, ruta_perfiles = ? 
        WHERE id = ?";
$stmt2 = $conn->prepare($sql);
$stmt2->execute([
    $cliente_id, $plan_id, $planes_precios_id, 
    $fecha_inicio . " 00:00:00", $fecha_fin . " 23:59:59", 
    $meses_pagados, $activo, $ruta_perfiles, $id
]);

                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar la suscripción: " . $e->getMessage();
        }
    }
} else {
    $cliente_id = $suscripcion['cliente_id'];
    $plan_id = $suscripcion['plan_id'];

    $stmtDur = $conn->prepare("SELECT duracion_meses FROM planes_precios WHERE id = ?");
    $stmtDur->execute([$suscripcion['planes_precios_id']]);
    $duracion_meses = $stmtDur->fetchColumn() ?: '';

    $fecha_inicio = substr($suscripcion['fecha_inicio'], 0, 10);
    $fecha_fin = date('d-m-Y', strtotime($suscripcion['fecha_fin']));
    $activo = $suscripcion['activo'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="/ico/Logo.ico" />
    <title>Editar Suscripción | AlmagaraVPN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    min-height: 100vh;
    padding: 20px;
}

/* Header con boton a la derecha */
header.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: nowrap;
}

h1 {
    font-size: 24px;
    margin-right: 15px; /* separacion entre h1 y boton */
    white-space: nowrap; /* evitar salto de linea */
}

/* Boton de volver */
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

/* Formulario principal */
form {
    max-width: 500px;
    margin: 20px auto;
    background-color: #1e1e1e;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
}

label {
    display: block;
    margin-top: 15px;
    margin-bottom: 6px;
    font-weight: bold;
}

input[type="text"],
input[type="date"],
select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #444;
    background-color: #2c2c2c;
    color: #fff;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="date"]:focus,
select:focus {
    border-color: #4caf50;
    outline: none;
}

input[type="checkbox"] {
    margin-right: 8px;
    vertical-align: middle;
}

/* Boton Crear suscripcion con estilo verde */
input[type="submit"].submit-btn {
    background-color: #4caf50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
    display: inline-block;
}

input[type="submit"].submit-btn:hover {
    background-color: #43a047;
}

/* Mensajes de errores */
.errores {
    max-width: 500px;
    margin: 10px auto 20px;
    background-color: #b00020;
    color: #fff;
    padding: 10px 15px;
    border-radius: 6px;
}

.errores ul {
    list-style: disc inside;
}

/* Responsive para moviles */
@media (max-width: 600px) {
    body {
        padding: 10px;
    }

    form {
        padding: 15px;
        margin: 10px;
    }

    h1 {
        font-size: 20px;
        margin-bottom: 0;
        margin-right: 10px;
    }

    header.header-flex {
        flex-direction: row; /* mantener h1 y boton al mismo nivel */
        justify-content: flex-start; /* que queden juntos a la izquierda */
        gap: 10px;
        margin-bottom: 20px;
    }

    a.back {
        padding: 10px 18px; /* algo menos para caber mejor */
        font-size: 16px;
        white-space: nowrap;
    }

    input[type="submit"].submit-btn {
        width: 100%;
        text-align: center;
        padding: 12px 0;
        font-size: 16px;
        display: inline-block;
    }
}
</style>
    <script>
    function cargarDuraciones() {
        const planId = document.getElementById('plan_id').value;
        const duracionSelect = document.getElementById('duracion_meses');

        duracionSelect.innerHTML = '<option value="">Cargando...</option>';

        if (!planId) {
            duracionSelect.innerHTML = '<option value="">-- Seleccione un plan primero --</option>';
            return;
        }

        fetch('get_duraciones.php?plan_id=' + planId)
            .then(response => response.json())
            .then(data => {
                duracionSelect.innerHTML = '<option value="">-- Seleccione duración --</option>';
                data.forEach(function(duracion) {
                    const option = document.createElement('option');
                    option.value = duracion.duracion_meses;
                    option.textContent = duracion.duracion_meses + ' meses';
                    duracionSelect.appendChild(option);
                });

                const duracionGuardada = '<?= $duracion_meses ?>';
                if (duracionGuardada) {
                    duracionSelect.value = duracionGuardada;
                }

                duracionSelect.dispatchEvent(new Event('change'));
            })
            .catch(() => {
                duracionSelect.innerHTML = '<option value="">Error cargando duraciones</option>';
            });
    }

    function actualizarFechaFin() {
        const fechaInicioStr = document.getElementById('fecha_inicio').value;
        const duracionSelect = document.getElementById('duracion_meses');
        const fechaFinInput = document.getElementById('fecha_fin');

        if (!fechaInicioStr || !duracionSelect.value) {
            fechaFinInput.value = '';
            return;
        }

        const fechaInicio = new Date(fechaInicioStr);
        const meses = parseInt(duracionSelect.value);

        if (isNaN(meses)) {
            fechaFinInput.value = '';
            return;
        }

        fechaInicio.setMonth(fechaInicio.getMonth() + meses);
        fechaInicio.setDate(fechaInicio.getDate() - 1);

        const yyyy = fechaInicio.getFullYear();
        let mm = fechaInicio.getMonth() + 1;
        let dd = fechaInicio.getDate();

        mm = mm < 10 ? '0' + mm : mm;
        dd = dd < 10 ? '0' + dd : dd;

        fechaFinInput.value = dd + '/' + mm + '/' + yyyy;
    }

    window.addEventListener('DOMContentLoaded', () => {
        document.getElementById('plan_id').addEventListener('change', cargarDuraciones);
        document.getElementById('duracion_meses').addEventListener('change', actualizarFechaFin);
        document.getElementById('fecha_inicio').addEventListener('change', actualizarFechaFin);
        cargarDuraciones();
    });
    </script>
</head>
<body>
    
    <header class="header-flex">
    <h1>Editar suscripción</h1>
    <a href="index.php" class="back">Volver a suscripciones</a>
</header>
    
    <?php if (!empty($errores)) : ?>
        <div class="errores">
            <ul>
                <?php foreach ($errores as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="editar_suscripciones.php?id=<?= urlencode($id) ?>">
        <label for="cliente_id">Cliente:</label>
        <select id="cliente_id" name="cliente_id" required>
            <option value="">-- Seleccione cliente --</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>" <?= ($cliente['id'] == $cliente_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cliente['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="plan_id">Plan:</label>
        <select id="plan_id" name="plan_id" required>
            <option value="">-- Seleccione plan --</option>
            <?php foreach ($planes as $plan): ?>
                <option value="<?= $plan['id'] ?>" <?= ($plan['id'] == $plan_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($plan['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="duracion_meses">Duración (meses):</label>
        <select id="duracion_meses" name="duracion_meses" required>
            <option value="">-- Seleccione duración --</option>
        </select>

        <label for="fecha_inicio">Fecha de inicio:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>" required />

        <label for="fecha_fin">Fecha fin (automática):</label>
        <input type="text" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>" readonly />
<label for="meses_pagados">Pagado hasta:</label>
<select name="meses_pagados" id="meses_pagados">
    <option value="0" <?= $suscripcion['meses_pagados'] == 0 ? 'selected' : '' ?>>Sin pagar</option>
    <?php
    $fecha_inicio_obj = new DateTime($fecha_inicio);
    $fecha_fin_obj = new DateTime($suscripcion['fecha_fin']);
    $intervalo = $fecha_inicio_obj->diff($fecha_fin_obj);
    $duracion = ($intervalo->y * 12) + $intervalo->m + 1;

    $meses_es = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    for ($i = 1; $i <= $duracion; $i++) {
        $mes_actual = clone $fecha_inicio_obj;
        $mes_actual->modify('+' . ($i - 1) . ' months');
        $mes_nombre = $meses_es[$mes_actual->format('n') - 1];
        $anio = $mes_actual->format('Y');
        $texto = ucfirst($mes_nombre) . ' ' . $anio;
        $selected = $suscripcion['meses_pagados'] == $i ? 'selected' : '';
        echo "<option value=\"$i\" $selected>$texto</option>";
    }
    ?>
</select>

<?php
// Mostrar solo la parte relativa (quitar /home/Web/perfiles/)
$ruta_mostrar = $suscripcion['ruta_perfiles'] ?? '';
if ($ruta_mostrar !== null && strpos($ruta_mostrar, '/perfiles/') === 0) {
    $ruta_mostrar = substr($ruta_mostrar, strlen('/perfiles/'));
}
?>

<label for="ruta_perfiles">Ruta de perfiles:</label>
<input type="text" id="ruta_perfiles" name="ruta_perfiles" 
       value="<?= htmlspecialchars($ruta_mostrar) ?>" 
       placeholder="Ej: confgrego/" />



        <label>
            <input type="checkbox" name="activo" <?= $activo ? 'checked' : '' ?> />
            Activo
        </label>

        <input type="submit" value="Guardar cambios" class="submit-btn" />
    </form>
</body>
</html>