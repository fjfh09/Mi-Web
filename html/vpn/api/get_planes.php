<?php
header('Content-Type: application/json');

try {
    // Conexion (igual que en tu ejemplo)
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vpn/db_config.php';

    // Cache en archivo (5 min)
    $cache_file = sys_get_temp_dir() . '/cache_planes.json';
    $cache_time = 300;

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        echo file_get_contents($cache_file);
        exit;
    }

    // Consulta
    $sql = "SELECT p.id, p.nombre, p.perfiles, p.descripcion,
                   pp.duracion_meses, pp.precio_mes, pp.precio_total
            FROM planes p
            JOIN planes_precios pp ON p.id = pp.plan_id
            ORDER BY p.id, pp.duracion_meses";
    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transformar datos
    $planes = [];
    foreach ($rows as $r) {
        $id = $r['id'];
        if (!isset($planes[$id])) {
            $planes[$id] = [
                "category" => $r['nombre'],
                "data" => []
            ];
        }

        // Duracion legible
        if ($r['duracion_meses'] == 12) {
    $duracion = "1 año";
} elseif ($r['duracion_meses'] == 1) {
    $duracion = "1 mes";
} else {
    $duracion = $r['duracion_meses'] . " meses";
}

        $planes[$id]["data"][] = [
            $duracion,
            number_format($r['precio_mes'], 2, ',', '') . "€",
            number_format($r['precio_total'], 2, ',', '') . "€"
        ];
    }

    $json = json_encode(array_values($planes), JSON_UNESCAPED_UNICODE);
    file_put_contents($cache_file, $json);

    echo $json;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener planes: " . $e->getMessage()]);
}
