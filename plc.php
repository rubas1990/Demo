<?php
try {
    $db = new PDO('sqlite:demo_mes.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener el último paso insertado
    $ultimoPaso = $db->query("SELECT paso_actual FROM plc_master ORDER BY id DESC LIMIT 1")->fetchColumn();

    // Generar paso en orden 1-5
    if ($ultimoPaso === false || $ultimoPaso >= 5) {
        $paso_actual = 1; // Reinicia en 1 si no hay pasos o ya llegó a 5
    } else {
        $paso_actual = $ultimoPaso + 1;
    }

    // Estado PLC aleatorio
    $estado_plc = rand(0,1) ? 'conectado' : 'desconectado';

    // Ciclo aleatorio
    $ciclo = rand(0,1) ? 'sin_fallas' : 'con_fallas';

    // Insertar en PLC con columna mes como "pendiente"
    $stmt = $db->prepare("INSERT INTO plc_master (paso_actual, estado_plc, ciclo, mes) VALUES (?, ?, ?, ?)");
    $stmt->execute([$paso_actual, $estado_plc, $ciclo, 'pendiente']);

    echo "✅ Simulación PLC insertada correctamente: Paso $paso_actual, $ciclo, mes pendiente";

} catch (PDOException $e) {
    die("Error PLC: " . $e->getMessage());
}
?>