<?php
session_name('SESSION_CLIENTE');
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    die("Acceso denegado");
}

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    die("Acceso denegado");
}

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    $stmt = $conn->prepare("SELECT ruta_perfiles FROM suscripciones WHERE cliente_id = :id_usuario AND activo = 1 LIMIT 1");
    $stmt->execute(['id_usuario' => $id_usuario]);
    $ruta_perfiles = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Error de base de datos");
}

if (empty($ruta_perfiles)) {
    die("Acceso denegado. No tiene perfiles asignados.");
}

$baseDir = '/perfiles/';
$userDir = $ruta_perfiles;
if (strpos($userDir, $baseDir) !== 0) {
    $userDir = $baseDir . ltrim($userDir, '/');
}

$allowedDir = realpath($userDir);
if (!$allowedDir) {
    die("Acceso denegado. Directorio de perfiles no existe.");
}

$archivo = $_GET['archivo'] ?? '';

// prevenir rutas fuera de baseDir
$archivo = str_replace(['..\\', '../'], '', $archivo);

$rutaCompleta = realpath($baseDir . $archivo);
$allowedDirWithSep = rtrim($allowedDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (!$rutaCompleta || strpos($rutaCompleta, $allowedDirWithSep) !== 0 || !is_file($rutaCompleta) || !is_readable($rutaCompleta)) {
    die("Archivo no encontrado o acceso denegado");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($rutaCompleta) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($rutaCompleta));

readfile($rutaCompleta);
exit;
