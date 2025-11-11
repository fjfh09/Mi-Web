<?php

// Recoger datos JSON enviados por POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

header("Access-Control-Allow-Origin: https://fjfh06.ddns.net");

$ip = $_SERVER['REMOTE_ADDR'];
$archivo = "/tmp/contacto_" . md5($ip);
if (file_exists($archivo) && time() - filemtime($archivo) < 30) {
    echo json_encode(['error' => 'Espera unos segundos antes de enviar de nuevo.']);
    exit;
}
touch($archivo);


if ($data === null) {
    // JSON inválido o no recibido
    echo json_encode(['error' => 'No se recibieron datos JSON válidos.', 'input' => $input]);
    exit;
}

if (!isset($data['nombre'], $data['correo'], $data['asunto'], $data['mensaje'])) {
    echo json_encode(['error' => 'Faltan campos.']);
    exit;
}

if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Correo invalido.']);
    exit;
}

if (strlen($data['mensaje']) > 1000) {
    echo json_encode(['error' => 'Mensaje demasiado largo.']);
    exit;
}


// Debug: muestra lo que has recibido
// echo json_encode(['recibido' => $data]);
// exit;

try {
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO mensajes (nombre, correo, asunto, mensaje) VALUES (:nombre, :correo, :asunto, :mensaje)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':nombre', $data['nombre']);
    $stmt->bindParam(':correo', $data['correo']);
    $stmt->bindParam(':asunto', $data['asunto']);
    $stmt->bindParam(':mensaje', $data['mensaje']);

    $stmt->execute();

    echo json_encode(['mensaje' => 'Mensaje enviado correctamente.']);
} catch (PDOException $e) {
    error_log("Error PDO: " . $e->getMessage());
    echo json_encode(['error' => 'Error al guardar en la base de datos.', 'detalle' => $e->getMessage()]);
}
