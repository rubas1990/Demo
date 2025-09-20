<?php
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:demo_mes.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1️⃣ Leer todos los registros pendientes del PLC
    $stmt = $db->query("SELECT * FROM plc_master WHERE mes='pendiente' ORDER BY id ASC");
    $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$pendientes) {
        echo json_encode(["message" => "No hay registros pendientes"]);
        exit;
    }

    foreach ($pendientes as $master) {
        $paso_actual_id = intval($master['paso_actual']);

        // 2️⃣ Verificar si existe paso extra "Guardar Información"
        $guardar_info = $db->query("SELECT * FROM pasos WHERE nombre='Guardar Información'")->fetch(PDO::FETCH_ASSOC);

        // Si paso PLC es 5 y no existe paso extra, lo creamos
        if ($paso_actual_id == 5 && !$guardar_info) {
            $stmt_insert = $db->prepare("INSERT INTO pasos (nombre, descripcion, estado, imagen) VALUES (?, ?, ?, ?)");
            $stmt_insert->execute([
                "Guardar Información",
                "Contando piezas y registrando resultados finales",
                "pendiente",
                "imagenes/paso6.png"
            ]);
            $guardar_info = $db->query("SELECT * FROM pasos WHERE nombre='Guardar Información'")->fetch(PDO::FETCH_ASSOC);
        }

        // Si paso PLC es 5 y existe el paso extra, lo activamos como paso actual
        if ($paso_actual_id == 5 && $guardar_info) {
            $paso_actual_id = $guardar_info['id'];
        }

        // 3️⃣ Actualizar pasos (1-5 y extra)
        $db->exec("UPDATE pasos SET estado='ok' WHERE id < $paso_actual_id");
        $db->exec("UPDATE pasos SET estado='pendiente' WHERE id > $paso_actual_id");

        $estado_paso = ($paso_actual_id == $guardar_info['id']) 
            ? ($master['ciclo'] === 'sin_fallas' ? 'ok' : 'fail') 
            : 'pendiente';

        $stmt_update = $db->prepare("UPDATE pasos SET estado=? WHERE id=?");
        $stmt_update->execute([$estado_paso, $paso_actual_id]);

        // 4️⃣ Contado de piezas solo si estamos en paso "Guardar Información"
        $ultimo_maquina = $db->query("SELECT * FROM maquina ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $piezas_ok = $ultimo_maquina['piezas_ok'] ?? 0;
        $piezas_ng = $ultimo_maquina['piezas_ng'] ?? 0;

        if ($paso_actual_id == $guardar_info['id']) {
            if ($master['ciclo'] === 'sin_fallas') $piezas_ok++;
            else $piezas_ng++;
        }

        // 5️⃣ Insertar operador
        $stmt_op = $db->prepare("INSERT INTO operador (nombre, turno, piezas_ok, piezas_ng) VALUES (?, ?, ?, ?)");
        $stmt_op->execute(['Juan Pérez', 'Turno A', $piezas_ok, $piezas_ng]);

        // 6️⃣ Insertar máquina
        $piezas_turno = $piezas_ok + $piezas_ng;

        if ($paso_actual_id < $guardar_info['id']) {
            $ciclo_actual = $paso_actual_id;
        } else {
            $ciclo_actual = $ultimo_maquina['ciclo_actual'] ?? 0;
        }

        if ($ultimo_maquina) {
            $ciclo_promedio = ($ultimo_maquina['ciclo_promedio'] * ($ultimo_maquina['ciclo_actual'] - 1) + $ciclo_actual) / max(1, $ciclo_actual);
        } else {
            $ciclo_promedio = $ciclo_actual;
        }

        $stmt_maq = $db->prepare("INSERT INTO maquina (ciclo_actual, ciclo_promedio, piezas_turno, estado_plc, piezas_ok, piezas_ng) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_maq->execute([$ciclo_actual, $ciclo_promedio, $piezas_turno, $master['estado_plc'], $piezas_ok, $piezas_ng]);

        // 7️⃣ Marcar PLC como leído
        $stmt_plc = $db->prepare("UPDATE plc_master SET mes='leido' WHERE id=?");
        $stmt_plc->execute([$master['id']]);
    }

    // 8️⃣ Preparar datos para frontend
    $paso_actual = $db->query("SELECT * FROM pasos WHERE estado != 'ok' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    // ✅ Si todos los pasos están ok, devolver el último paso
    if (!$paso_actual) {
        $paso_actual = $db->query("SELECT * FROM pasos ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    $pasos    = $db->query("SELECT * FROM pasos")->fetchAll(PDO::FETCH_ASSOC);
    $operador = $db->query("SELECT * FROM operador ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $maquina  = $db->query("SELECT * FROM maquina ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "paso_actual" => $paso_actual,
        "pasos"       => $pasos,
        "operador"    => $operador,
        "maquina"     => $maquina
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>