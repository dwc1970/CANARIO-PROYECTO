<?php
// 1. Configuración de la base de datos
$databaseFile = 'club_constitucion.db';
$mensaje_registro = "";

// 2. Lógica para Registrar un nuevo socio (Solo si el admin envía el formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_registrar'])) {
    try {
        $db = new PDO("sqlite:" . $databaseFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO socios (nombre, dni, categoria, nro_socio, fecha_registro) 
                VALUES (:nom, :dni, :cat, :nro, :fec)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':nom', strtoupper($_POST['reg_nombre']));
        $stmt->bindValue(':dni', $_POST['reg_dni']);
        $stmt->bindValue(':cat', $_POST['reg_categoria']);
        $stmt->bindValue(':nro', $_POST['reg_nro']);
        $stmt->bindValue(':fec', date('Y-m-d H:i:s'));
        
        if ($stmt->execute()) {
            $mensaje_registro = "<p style='color:green;'>Socio registrado con éxito.</p>";
        }
    } catch (PDOException $e) {
        $mensaje_registro = "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}

// 3. Lógica para obtener la lista de socios para la tabla
$socios_lista = [];
try {
    $db = new PDO("sqlite:" . $databaseFile);
    $res = $db->query("SELECT * FROM socios ORDER BY id DESC");
    $socios_lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* Error silencioso */ }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - C.S. y D. Constitución</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        #login-screen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #000; display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .login-box { background: white; padding: 30px; border-radius: 10px; text-align: center; width: 300px; }
        .login-box input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-login { background: #ffd700; color: #000; border: none; padding: 10px; width: 100%; font-weight: bold; cursor: pointer; border-radius: 5px; }

        #admin-content { display: none; max-width: 1100px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #ffd700; padding-bottom: 10px; margin-bottom: 20px; }
        
        /* Estilos Formulario Registro */
        .reg-box { background: #eee; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
        .reg-box input, .reg-box select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #000; color: #ffd700; }
        tr:hover { background: #fffde7; }
        .btn-refresh { background: #000; color: #ffd700; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div id="login-screen">
    <div class="login-box">
        <img src="logo2.png" width="60" alt="Logo">
        <h3>Acceso Administrador</h3>
        <input type="text" id="user" placeholder="Usuario">
        <input type="password" id="pass" placeholder="Contraseña">
        <button class="btn-login" onclick="verificarAcceso()">Entrar</button>
    </div>
</div>

<div id="admin-content">
    <header>
        <h2><i class="fas fa-users-cog"></i> Gestión de Socios (ADMIN)</h2>
        <div>
            <button class="btn-refresh" onclick="location.reload()"><i class="fas fa-sync"></i> Actualizar</button>
            <button class="btn-refresh" onclick="location.href='../index.html'" style="background:#444;">Volver al Inicio</button>
        </div>
    </header>

    <h3><i class="fas fa-user-plus"></i> Registrar Nuevo Socio</h3>
    <?php echo $mensaje_registro; ?>
    <form method="POST" class="reg-box">
        <div><label>Nombre:</label><br><input type="text" name="reg_nombre" required></div>
        <div><label>DNI:</label><br><input type="text" name="reg_dni" required></div>
        <div><label>Categoría:</label><br>
            <select name="reg_categoria">
                <option value="ACTIVO">ACTIVO</option>
                <option value="CADETE">CADETE</option>
                <option value="MENOR">MENOR</option>
            </select>
        </div>
        <div><label>N° Socio:</label><br><input type="text" name="reg_nro" required></div>
        <button type="submit" name="btn_registrar" class="btn-save">GUARDAR SOCIO</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre y Apellido</th>
                <th>DNI</th>
                <th>Categoría</th>
                <th>N° Socio</th>
                <th>Fecha Registro</th>
            </tr>
        </thead>
        <tbody id="tabla-socios">
            <?php foreach ($socios_lista as $s): ?>
                <tr>
                    <td><?php echo $s['id']; ?></td>
                    <td><strong><?php echo $s['nombre']; ?></strong></td>
                    <td><?php echo $s['dni']; ?></td>
                    <td><?php echo $s['categoria']; ?></td>
                    <td>#<?php echo $s['nro_socio']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($s['fecha_registro'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // Se mantiene tu lógica de acceso frontal para ocultar/mostrar el panel
    function verificarAcceso() {
        const u = document.getElementById('user').value;
        const p = document.getElementById('pass').value;

        // Tus credenciales
        if(u === "canario" && p === "123") {
            document.getElementById('login-screen').style.display = 'none';
            document.getElementById('admin-content').style.display = 'block';
        } else {
            alert("Usuario o contraseña incorrectos");
        }
    }
</script>

</body>
</html>