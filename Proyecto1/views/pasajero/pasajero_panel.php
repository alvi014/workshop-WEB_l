<?php
session_start();

// Rutas a includes: Corregidas
require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 


check_role_access('pasajero');

$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Pasajero'; 

// Comprobación para ver si el usuario también es Chofer (o tiene el rol dual)
$es_chofer = isset($_SESSION['tipo_usuario']) && 
             ($_SESSION['tipo_usuario'] === 'chofer' || $_SESSION['tipo_usuario'] === 'dual'); 
             // Asume que tienes un campo o lógica que define si es chofer.
             // Si el rol solo es 'chofer' o 'pasajero', usa la variable de sesión que lo defina.
             // Si el rol es 'chofer', usualmente no entraría a este panel, pero si puede ser ambos,
             // usaremos esta variable de sesión para el botón.
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
        
        <?php if ($es_chofer): ?>
            <li><a href="../chofer/panel_chofer.php" style="color: blue; font-weight: bold;">🚗 Ir al Panel de Chofer</a></li>
        <?php endif; ?>

</ul>

<hr>

<h2>Información de la Cuenta</h2>
<p><a href="../../public/logout.php">Cerrar Sesión</a></p>
</body>
</html>