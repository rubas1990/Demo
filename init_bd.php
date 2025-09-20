<?php
try {
    $db = new PDO('sqlite:demo_mes.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar tablas antiguas si existen
    $db->exec("DROP TABLE IF EXISTS pasos");
    $db->exec("DROP TABLE IF EXISTS operador");
    $db->exec("DROP TABLE IF EXISTS maquina");
    $db->exec("DROP TABLE IF EXISTS plc_master");
    
$db->exec("CREATE TABLE plc_master (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha DATE DEFAULT CURRENT_DATE,
    hora TIME DEFAULT CURRENT_TIME,
    paso_actual INTEGER NOT NULL,
    estado_plc TEXT CHECK(estado_plc IN ('conectado','desconectado')) DEFAULT 'conectado',
    ciclo TEXT CHECK(ciclo IN ('con_fallas','sin_fallas')) DEFAULT 'sin_fallas',
    mes TEXT CHECK(mes IN ('pendiente','leido')) DEFAULT 'pendiente'
)");


    // Crear tabla pasos
    $db->exec("CREATE TABLE pasos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        descripcion TEXT,
        estado TEXT CHECK(estado IN ('pendiente','ok','fail')) DEFAULT 'pendiente',
        imagen TEXT
    )");

    // Insertar pasos de ejemplo
    $pasos = [
        ["Escribir receta en PLC", "Receta enviada correctamente", "imagenes/paso1.png"],
        ["Ponga la pieza en el nido", "Esperando pieza en sensor", "imagenes/paso2.png"],
        ["Escanear substrato", "Escaneo correcto", "imagenes/paso3.png"],
        ["Enviar escaneo completo", "Esperando confirmación PLC", "imagenes/paso4.png"],
        ["Resultado cámara", "Procesando imagen", "imagenes/paso5.png"]
    ];

    $stmt = $db->prepare("INSERT INTO pasos (nombre, descripcion, imagen) VALUES (?, ?, ?)");
    foreach ($pasos as $p) {
        $stmt->execute([$p[0], $p[1], $p[2]]);
    }

    // Crear tabla operador
    $db->exec("CREATE TABLE operador (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT,
        turno TEXT,
        piezas_ok INTEGER DEFAULT 0,
        piezas_ng INTEGER DEFAULT 0
    )");

    // Crear tabla maquina
    $db->exec("CREATE TABLE maquina (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ciclo_actual REAL DEFAULT 0,
        ciclo_promedio REAL DEFAULT 0,
        piezas_turno INTEGER DEFAULT 0,
        estado_plc TEXT CHECK(estado_plc IN ('conectado','desconectado')) DEFAULT 'desconectado',
        piezas_ok INTEGER DEFAULT 0,
        piezas_ng INTEGER DEFAULT 0
    )");

    echo "Base de datos inicializada correctamente ✅";

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>