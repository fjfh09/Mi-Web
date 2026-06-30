<?php
session_name('SESSION_CLIENTE');
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    http_response_code(403);
    die("No autorizado");
}

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    http_response_code(403);
    die("No autorizado");
}

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    $stmt = $conn->prepare("SELECT ruta_perfiles FROM suscripciones WHERE cliente_id = :id_usuario AND activo = 1 LIMIT 1");
    $stmt->execute(['id_usuario' => $id_usuario]);
    $ruta_perfiles = $stmt->fetchColumn();
} catch (PDOException $e) {
    http_response_code(500);
    die("Error de base de datos");
}

if (empty($ruta_perfiles)) {
    http_response_code(403);
    die("Acceso denegado. No tiene perfiles asignados.");
}

$baseDir = '/perfiles/';
$userDir = $ruta_perfiles;
if (strpos($userDir, $baseDir) !== 0) {
    $userDir = $baseDir . ltrim($userDir, '/');
}

$allowedDir = realpath($userDir);
if (!$allowedDir) {
    http_response_code(403);
    die("Acceso denegado. Directorio de perfiles no existe.");
}

$archivoRel = $_GET['archivo'] ?? '';
$archivoRel = str_replace(['..\\', '../'], '', $archivoRel);

$rutaArchivo = realpath($baseDir . $archivoRel);
$allowedDirWithSep = rtrim($allowedDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (!$rutaArchivo || strpos($rutaArchivo, $allowedDirWithSep) !== 0 || !is_file($rutaArchivo) || !is_readable($rutaArchivo)) {
    http_response_code(404);
    die("Archivo no encontrado");
}

$contenido = file_get_contents($rutaArchivo);
if ($contenido === false) {
    http_response_code(500);
    die("Error leyendo el archivo");
}

header('Content-Type: text/plain; charset=UTF-8');
echo $contenido;
exit;
