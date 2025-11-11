<?php
session_start();

// Rutas a includes: Corregidas
require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 


check_role_access('pasajero');

$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Pasajero'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Pasajero - Aventones</title>
</head>
<body>
    <h1>Bienvenido, Pasajero <?= htmlspecialchars($nombre_usuario) ?></h1>
    <p>Este es tu centro de control. Desde aquí puedes buscar rides y gestionar tus reservas.</p>

    <h2>Acciones Rápidas</h2>
    <ul>
        <li><a href="../../index.php">🔍 Buscar Rides Disponibles</a></li>
        
        <li><a href="mis_reservas.php">📋 Mis Solicitudes y Reservas</a></li>
        
    </ul>
    
    <hr>
    
    <h2>Información de la Cuenta</h2>
    <p><a href="../../public/logout.php">Cerrar Sesión</a></p>
    
    </body>
</html>