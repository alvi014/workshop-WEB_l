<?php
session_start();

require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 


check_role_access('administrador');

$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Administrador'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador - Aventones</title>
</head>
<body>
    <h1>🛠️ Bienvenido, Administrador <?= htmlspecialchars($nombre_usuario) ?></h1>
    <p>Este es tu centro de control para la gestión de usuarios y la seguridad del sistema.</p>

    <h2>Tareas de Administración</h2>
    <ul>
        <li><a href="gestionar_usuarios.php">👥 Gestionar Usuarios (Desactivar y Crear Administradores)</a></li>
        </ul>
    
    <hr>
    
    <h2>Seguridad y Cierre</h2>
    <p><a href="../../public/logout.php">Cerrar Sesión</a></p>
</body>
</html>