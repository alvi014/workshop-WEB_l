<?php

session_start();


require '../../includes/auth_check.php';


check_role_access('chofer');


$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Chofer'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Chofer - Aventones</title>
</head>
<body>
    <?php ?>
    
    <h1>Bienvenido, Chofer <?= htmlspecialchars($nombre_usuario) ?></h1>
    <p>Este es tu panel de acceso restringido. Aquí puedes gestionar tus vehículos y tus rides.</p>

    <ul>
       <li><a href="gestionar_vehiculo.php">Gestionar Vehículos</a></li>
        <li><a href="crear_ride.php">Crear Nuevo Ride</a></li>
        <li><a href="gestionar_reserva.php">Ver Solicitudes de Reserva</a></li>
    </ul>
    
   <p><a href="../../public/logout.php">Cerrar Sesión</a></p>

    <?php  ?>
</body>
</html>