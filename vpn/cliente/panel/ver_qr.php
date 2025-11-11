<?php
session_name('SESSION_CLIENTE');
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    http_response_code(403);
    die("No autorizado");
}

$baseDir = '/perfiles/';
$archivoRel = $_GET['archivo'] ?? '';
$archivoRel = str_replace(['..\\', '../'], '', $archivoRel);

$rutaArchivo = realpath($baseDir . $archivoRel);
if (!$rutaArchivo || strpos($rutaArchivo, $baseDir) !== 0 || !is_file($rutaArchivo) || !is_readable($rutaArchivo)) {
    http_response_code(404);
    die("Archivo no encontrado");
}

$contenido = trim(file_get_contents($rutaArchivo));
if ($contenido === false) {
    http_response_code(500);
    die("Error leyendo el archivo");
}

// Codificar contenido para URL
$contenidoUrl = urlencode($contenido);

// Redirigir a qrserver.com
header("Location: https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=$contenidoUrl");
exit;
