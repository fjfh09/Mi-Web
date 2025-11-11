<?php
session_name('SESSION_CLIENTE');
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    die("Acceso denegado");
}

$baseDir = '/perfiles/'; 
$archivo = $_GET['archivo'] ?? '';

// prevenir rutas fuera de baseDir
$archivo = str_replace(['..\\', '../'], '', $archivo);

$rutaCompleta = realpath($baseDir . $archivo);

if (!$rutaCompleta || strpos($rutaCompleta, $baseDir) !== 0 || !is_file($rutaCompleta) || !is_readable($rutaCompleta)) {
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
