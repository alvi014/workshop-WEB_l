<?php
session_start();
require '../includes/db_conexion.php'; 

$mensaje = '';
$errores = [];

$token_id = intval($_GET['token'] ?? 0); 
$tipo_usuario = $_GET['tipo'] ?? ''; // Para un mensaje más específico

if ($token_id <= 0) {
    $errores[] = "Enlace de activación inválido. Falta el token.";
} else {

    try {
        $stmt = $pdo->prepare("SELECT estado FROM Usuarios WHERE id_usuario = ?");
        $stmt->execute([$token_id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $errores[] = "Usuario no encontrado.";
        } elseif ($usuario['estado'] === 'Activa') {
            $mensaje = "Tu cuenta ya se encuentra **Activa**. Puedes iniciar sesión.";
            header("Refresh: 5; url=login.php");
        } elseif ($usuario['estado'] === 'Inactivo') {
             $errores[] = "Tu cuenta está **Inactiva** y requiere gestión de un administrador.";
        } elseif ($usuario['estado'] === 'Pendiente') {
    
            $stmt = $pdo->prepare("UPDATE Usuarios SET estado = 'Activa' WHERE id_usuario = ? AND estado = 'Pendiente'");
            $stmt->execute([$token_id]);
            
            if ($stmt->rowCount() > 0) {
                $mensaje = "¡Activación exitosa! Tu cuenta de **{$tipo_usuario}** ahora está activa. Serás redirigido al login.";
                header("Refresh: 5; url=login.php");
            } else {
                $errores[] = "Error al activar la cuenta. Vuelve a intentar o contacta al soporte.";
            }
        }
    } catch (\PDOException $e) {
        $errores[] = "Error de base de datos durante la activación: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activación de Cuenta</title>
</head>
<body>
    <h1>🎉 Activación de Cuenta</h1>

    <?php if (!empty($errores)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px;">
            <p><strong>Fallo en la Activación:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <p><a href="login.php">Ir a Iniciar Sesión</a></p>
        </div>
    <?php elseif (!empty($mensaje)): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin-bottom: 20px;">
            <p><?= htmlspecialchars($mensaje) ?></p>
        </div>
    <?php endif; ?>

    <p>Si la redirección no funciona, haga clic <a href="login.php">aquí</a>.</p>
</body>
</html>A