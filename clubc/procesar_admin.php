<?php
$databaseFile = 'club_constitucion.db';

try {
    $db = new PDO("sqlite:" . $databaseFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$accion = $_GET['accion'] ?? '';

// ACCIÓN: LISTAR SOCIOS
if ($accion === 'listar') {
    // Seleccionamos los campos según tu archivo .db 
    $stmt = $db->query("SELECT id, nombre, dni, categoria, nro_socio, fecha_registro FROM socios ORDER BY id DESC");
    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Añadimos un estado de cuota aleatorio para demostración
    foreach ($socios as &$s) {
        $s['estado_cuota'] = (rand(0, 1) == 1) ? 'AL DÍA' : 'DEUDA';
    }

    header('Content-Type: application/json');
    echo json_encode($socios);
    exit;
}

// ACCIÓN: REGISTRAR SOCIO
if ($accion === 'registrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = strtoupper($_POST['nombre']);
    $dni = $_POST['dni'];
    $categoria = $_POST['categoria'];
    $nro_socio = $_POST['nro_socio'];

    try {
        // Insertamos respetando las columnas de tu tabla 
        $sql = "INSERT INTO socios (nombre, dni, categoria, nro_socio) VALUES (:nom, :dni, :cat, :nro)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $nombre,
            ':dni' => $dni,
            ':cat' => $categoria,
            ':nro' => $nro_socio
        ]);
        echo "Socio registrado con éxito.";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error en la base de datos: " . $e->getMessage();
    }
    exit;
}
?>