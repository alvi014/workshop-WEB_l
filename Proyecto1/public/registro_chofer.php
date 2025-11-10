<?php

session_start();


require '../includes/db_conexion.php'; 
require '../includes/utils.php';       

$errores = [];
$exito = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $correo_electronico = strtolower(trim($_POST['correo_electronico'] ?? ''));
    $telefono = trim($_POST['telefono'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $repetir_contrasena = $_POST['repetir_contrasena'] ?? '';
    

    $fotografia_personal = 'default_chofer.jpg'; 

  
    if (empty($nombre) || empty($apellido) || empty($cedula) || empty($fecha_nacimiento) || empty($correo_electronico) || empty($telefono) || empty($contrasena) || empty($repetir_contrasena)) {
        $errores[] = "Todos los campos obligatorios deben ser llenados.";
    }

    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido.";
    }

    if ($contrasena !== $repetir_contrasena) {
        $errores[] = "Las contraseñas no coinciden.";
    }
    
   
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM Usuarios WHERE correo_electronico = ? OR cedula = ?");
            $stmt->execute([$correo_electronico, $cedula]);
            if ($stmt->fetch()) {
                $errores[] = "El correo electrónico o la cédula ya están registrados.";
            }
        } catch (\PDOException $e) {
            $errores[] = "Error al verificar la base de datos.";
        }
    }


    
    if (empty($errores)) {
        $contrasena_hash = hash_password($contrasena);
        
      
        $tipo_usuario = 'chofer';
        $estado = 'Pendiente';
        
        $sql = "INSERT INTO Usuarios 
                (nombre, apellido, cedula, fecha_nacimiento, correo_electronico, telefono, fotografia_personal, contrasena_hash, tipo_usuario, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nombre, 
                $apellido, 
                $cedula, 
                $fecha_nacimiento, 
                $correo_electronico, 
                $telefono, 
                $fotografia_personal, 
                $contrasena_hash, 
                $tipo_usuario, 
                $estado
            ]);
            
            $exito = "¡Registro exitoso como Chofer! Revise su correo electrónico para activar su cuenta. Será redirigido al login en 5 segundos...";
            header("Refresh: 5; url=login.php"); 
            
        } catch (\PDOException $e) {
            $errores[] = "Error al intentar registrar al Chofer: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Chofer</title>
</head>
<body>
    <h1>Registro de Chofer 🚘</h1>
    
    <?php if (!empty($errores)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px;">
            <p>Se encontraron los siguientes errores:</p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
        <div style="color: green; border: 1px solid green; padding: 10px;">
            <p><?= htmlspecialchars($exito) ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="registro_chofer.php" enctype="multipart/form-data">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre ?? '') ?>" required><br><br>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($apellido ?? '') ?>" required><br><br>
        
        <label for="cedula">Número de Cédula:</label>
        <input type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($cedula ?? '') ?>" required><br><br>

        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($fecha_nacimiento ?? '') ?>" required><br><br>

        <label for="correo_electronico">Correo Electrónico:</label>
        <input type="email" id="correo_electronico" name="correo_electronico" value="<?= htmlspecialchars($correo_electronico ?? '') ?>" required><br><br>

        <label for="telefono">Número de Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($telefono ?? '') ?>" required><br><br>

        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required><br><br>

        <label for="repetir_contrasena">Repetir Contraseña:</label>
        <input type="password" id="repetir_contrasena" name="repetir_contrasena" required><br><br>
        
        <label for="fotografia">Fotografía Personal:</label>
        <input type="file" id="fotografia" name="fotografia"><br><br>
        
        <button type="submit">Registrarse como Chofer</button>
    </form>
    
    <p>¿Ya tiene cuenta? <a href="login.php">Iniciar Sesión</a></p>
    <p>¿Quiere registrarse como pasajero? <a href="registro_pasajero.php">Regístrese aquí</a></p>
</body>
</html>