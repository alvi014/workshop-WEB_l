<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../includes/db_conexion.php'; 
require '../includes/utils.php'; 

$errores = [];
$correo = $_POST['correo_electronico'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$mensaje_estado = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Validación de campos
    if (empty($correo) || empty($contrasena)) {
        $errores[] = "Debe ingresar su correo electrónico y contraseña.";
    }

    if (empty($errores)) {
        // 2. Prepara la consulta SQL para obtener todos los datos
        $stmt = $pdo->prepare("SELECT id_usuario, contrasena_hash, tipo_usuario, estado, nombre FROM Usuarios WHERE correo_electronico = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Asegúrate de que la función check_password esté definida en utils.php
            // Asumo que tienes una función para verificar contraseñas hasheadas (e.g., password_verify)
            $contrasena_correcta = check_password($contrasena, $usuario['contrasena_hash']);

            if ($contrasena_correcta) {
                
                // 3. Verificar estado de la cuenta
                if ($usuario['estado'] !== 'Activa') {
                    $errores[] = "No se le permite ingresar: su cuenta está en estado **{$usuario['estado']}**. Revise su correo para activarla.";
                } else {
                    // 4. Login exitoso: Almacena datos de sesión y redirige
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre'];
                    
                    // --- LÓGICA DE REDIRECCIÓN CORREGIDA ---
                    switch ($usuario['tipo_usuario']) {
                        case 'chofer':
                            // Chofer va a su panel
                            header("Location: ../views/chofer/chofer_panel.php");
                            exit();
                        case 'pasajero':
                            // Pasajero va a la página principal de búsqueda (index.php, un nivel arriba)
                            header("Location: ../index.php");
                            exit();
                        case 'administrador':
                            // Administrador va a su panel
                            header("Location: ../views/admin/admin_panel.php");
                            exit();
                        default:
                            $errores[] = "Tipo de usuario no reconocido. Contacte al administrador.";
                        
                    }
                    // ----------------------------------------
                }
            } else {
                $errores[] = "Correo o contraseña incorrectos.";
            }
        } else {
            $errores[] = "Correo o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Aventones</title>
</head>
<body>
    <h1> Iniciar Sesión</h1>

    <?php if (!empty($errores)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px;">
            <p><strong>Errores:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="correo_electronico">Correo Electrónico:</label>
        <input type="email" id="correo_electronico" name="correo_electronico" value="<?= htmlspecialchars($correo) ?>" required><br><br>

        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required><br><br>
        
        <button type="submit">Ingresar</button>
    </form>

    <p>¿No tienes cuenta? <a href="registro_pasajero.php">Regístrate como Pasajero</a></p>
    <p>¿Quieres ofrecer rides? <a href="registro_chofer.php">Regístrate como Chofer</a></p>

</body>
</html>