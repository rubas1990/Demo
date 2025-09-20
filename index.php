<?php
try {
    $db = new PDO('sqlite:demo_mes.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Paso actual (primer paso pendiente o con fail)
    $stmt = $db->query("SELECT * FROM pasos WHERE estado != 'ok' LIMIT 1");
    $paso_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lista completa de pasos
    $stmt = $db->query("SELECT * FROM pasos");
    $pasos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Último operador registrado
    $stmt = $db->query("SELECT * FROM operador ORDER BY id DESC LIMIT 1");
    $operador = $stmt->fetch(PDO::FETCH_ASSOC);

    // Última máquina registrada
    $stmt = $db->query("SELECT * FROM maquina ORDER BY id DESC LIMIT 1");
    $maquina = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Demo MES PHP</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

    <!-- ===================================
         ENCABEZADO SUPERIOR
    ==================================== -->


    <header>
        <div class="titulo-box">
            <div class="titulo"><?= htmlspecialchars($paso_actual['nombre']) ?></div>
            <div class="subtitulo"><?= htmlspecialchars($paso_actual['descripcion']) ?></div>
        </div>
    </header>


    <!-- ===================================
         CONTENEDOR PRINCIPAL (3 COLUMNAS)
    ==================================== -->
    <div class="container">

        <!-- ===============================
             COLUMNA IZQUIERDA: LISTA DE PASOS
        ================================ -->
        <div class="sidebar">
            <ul class="pasos">
                <?php foreach ($pasos as $row): ?>
                <li class="<?= $row['estado'] ?> <?= $row['id']==$paso_actual['id']?'actual':'' ?>">
                    <?= htmlspecialchars($row['nombre']) ?>
                    <?= $row['estado']=='ok'?'✔':'' ?>
                    <?= $row['estado']=='fail'?'✖':'' ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- ===============================
             COLUMNA CENTRAL: IMAGEN DEL PASO
        ================================ -->
        <div class="main">
            <img src="<?= htmlspecialchars($paso_actual['imagen']) ?>" alt="Paso actual">
        </div>

        <!-- ===============================
             COLUMNA DERECHA: INFO OPERADOR Y MÁQUINA
        ================================ -->
        <div class="rightbar">
            <div class="card">👷 Operador: <?= htmlspecialchars($operador['nombre']) ?>
                (<?= htmlspecialchars($operador['turno']) ?>)</div>
            <div class="card">⚡ Ciclo actual (paso): <?= htmlspecialchars($maquina['ciclo_actual']) ?></div>
            <div class="card">📊 Ciclo promedio: <?= htmlspecialchars($maquina['ciclo_promedio']) ?></div>
            <div class="card">✅ Piezas buenas: <?= htmlspecialchars($maquina['piezas_ok']) ?></div>
            <div class="card">❌ Piezas con fallas: <?= htmlspecialchars($maquina['piezas_ng']) ?></div>
            <div class="card">📦 Piezas turno: <?= htmlspecialchars($maquina['piezas_turno']) ?></div>
            <div class="card">🔌 PLC: <?= htmlspecialchars($maquina['estado_plc']) ?></div>
        </div>

    </div>

    <script src="js/app.js"></script>
</body>

</html>