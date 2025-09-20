<?php
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:demo_mes.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Leer POST JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $ciclo_actual = intval($data['ciclo_actual']);

    // Actualizar última fila de maquina
    $stmt = $db->prepare("UPDATE maquina SET ciclo_actual = ? WHERE id = (SELECT MAX(id) FROM maquina)");
    $stmt->execute([$ciclo_actual]);

    echo json_encode(["ok" => true, "ciclo_actual_guardado" => $ciclo_actual]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>