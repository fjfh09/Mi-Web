<?php
session_name('SESSION_ADMIN');
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_POST['seleccionados']) || !is_array($_POST['seleccionados'])) {
    header("Location: index.php");
    exit;
}

$ids = $_POST['seleccionados'];
$accion = $_POST['accion'];

try {
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($accion === 'leido') {
        $sql = "UPDATE mensajes SET visto = 1 WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
    } elseif ($accion === 'noleido') {
        $sql = "UPDATE mensajes SET visto = 0 WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
    } elseif ($accion === 'borrar') {
        $sql = "DELETE FROM mensajes WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
    } else {
        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($ids);

} catch (PDOException $e) {
    die("Error BD: " . $e->getMessage());
}

header("Location: index.php");
exit;