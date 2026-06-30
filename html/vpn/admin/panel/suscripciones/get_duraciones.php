<?php
header('Content-Type: application/json');

if (!isset($_GET['plan_id']) || !ctype_digit($_GET['plan_id'])) {
    echo json_encode([]);
    exit;
}

$plan_id = intval($_GET['plan_id']);

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    $stmt = $conn->prepare("SELECT DISTINCT duracion_meses FROM planes_precios WHERE plan_id = ? ORDER BY duracion_meses");
    $stmt->execute([$plan_id]);

    $duraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($duraciones);
} catch (PDOException $e) {
    echo json_encode([]);
}
