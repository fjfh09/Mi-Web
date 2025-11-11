<?php
header('Content-Type: application/json');

try {
    // Conexion (igual que en tu ejemplo)
    $conn = new PDO("mysql:host=192.168.18.10;port=33306;dbname=vpn_db;charset=utf8", "vpn_user", "vpn_pass");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cache en archivo (5 min)
    $cache_file = __DIR__ . '/cache_planes.json';
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
        $duracion = $r['duracion_meses'] == 12 ? "1 año" : $r['duracion_meses'] . " meses";

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
