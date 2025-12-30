<?php
// 1. Configuración de la base de datos
$databaseFile = 'club_constitucion.db';
$error_message = "";

// 2. Lógica de Verificación Dinámica (Busca a cualquier socio en el archivo .db)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni_ingresado = trim($_POST['dni']);
    $nombre_ingresado = trim(strtoupper($_POST['nombre'])); // Convertimos a mayúsculas para evitar errores

    try {
        // Conexión a la base de datos SQLite
        $db = new PDO("sqlite:" . $databaseFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Buscamos en la tabla 'socios' si existe el registro
        $stmt = $db->prepare("SELECT * FROM socios WHERE dni = :dni AND nombre = :nombre");
        $stmt->bindValue(':dni', $dni_ingresado);
        $stmt->bindValue(':nombre', $nombre_ingresado);
        $stmt->execute();
        
        $socio = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($socio) {
            // SI EXISTE: Iniciamos sesión y mandamos a perfil.html dentro de la carpeta clubc
            session_start();
            $_SESSION['socio_id'] = $socio['id'];
            $_SESSION['socio_nombre'] = $socio['nombre'];
            header("Location: perfil.html");
            exit();
        } else {
            // NO EXISTE
            $error_message = "Socio no encontrado o datos incorrectos.";
        }
    } catch (PDOException $e) {
        $error_message = "Error de conexión con la base de datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Socio - Club Constitución</title>
    <style>
        /* Diseño visual solicitado */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.7);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-bottom: 4px solid #e31e24; /* Rojo Constitución */
        }
        img { width: 100px; margin-bottom: 20px; }
        h2 { margin-bottom: 20px; font-weight: 300; }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #333;
            background-color: #2a2a2a;
            color: white;
            box-sizing: border-box;
            outline: none;
        }
        input:focus { border-color: #e31e24; }
        button {
            width: 100%;
            padding: 12px;
            background-color: #e31e24;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        button:hover { background-color: #b3171b; }
        
        /* Alerta de error en PHP */
        .error { 
            color: #ff6b6b; 
            background: rgba(227, 30, 36, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px; 
            font-size: 14px;
            border: 1px solid #e31e24;
        }
        
        .btn-volver {
            display: inline-block;
            margin-top: 20px;
            color: #888;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-volver:hover { color: #e31e24; }
    </style>
</head>
<body>

<div class="login-box">
    <img src="logo2.png" alt="Club Constitución">
    <h2>Panel del Socio</h2>
    
    <?php if ($error_message): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="nombre" placeholder="Nombre Completo (MAYÚSCULAS)" required>
        <input type="text" name="dni" placeholder="Número de DNI" required>
        <button type="submit">VERIFICAR Y ENTRAR</button>
    </form>

    <a href="../index.html" class="btn-volver">← Volver al Inicio</a>
</div>

</body>
</html>