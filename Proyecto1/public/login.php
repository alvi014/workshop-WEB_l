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
            // NOTA: Si check_password usa password_verify, es correcto.
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
                    
                    // --- LÓGICA DE REDIRECCIÓN CORREGIDA (Pasajero redirige a su Panel) ---
                    switch ($usuario['tipo_usuario']) {
                        case 'chofer':
                            // Chofer va a su panel
                            header("Location: ../views/chofer/chofer_panel.php");
                            exit();
                        case 'pasajero':
                            // CORRECCIÓN: Pasajero va a su panel de control
                            header("Location: ../views/pasajero/pasajero_panel.php");
                            exit();
                        case 'administrador':
                            // Administrador va a su panel
                            header("Location: ../views/admin/admin_panel.php");
                            exit();
                        default:
                            // En caso de un tipo no reconocido, vuelve a la página principal con error.
                            $errores[] = "Tipo de usuario no reconocido. Contacte al administrador.";
                            header("Location: ../index.php"); 
                            exit();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Aventones</title>
    <!-- Asumo que usarás Bootstrap o similar para el diseño -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow-sm" style="max-width: 400px; width: 100%;">
            <h1 class="card-title text-center mb-4">Iniciar Sesión</h1>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger" role="alert">
                    <p class="mb-0"><strong>Error:</strong></p>
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="correo_electronico" class="form-label">Correo Electrónico:</label>
                    <input type="email" id="correo_electronico" name="correo_electronico" value="<?= htmlspecialchars($correo) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">Ingresar</button>
            </form>

            <p class="text-center mt-3">
                ¿No tienes cuenta? <a href="registro_pasajero.php">Regístrate como Pasajero</a>
            </p>
            <p class="text-center">
                ¿Quieres ofrecer rides? <a href="registro_chofer.php">Regístrate como Chofer</a>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>