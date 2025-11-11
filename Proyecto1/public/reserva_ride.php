<?php
session_start();
require '../includes/db_conexion.php'; 
require '../includes/auth_check.php'; 

// Verifica que el usuario esté logueado como Pasajero
check_role_access('pasajero');

$id_pasajero = $_SESSION['id_usuario'];
$mensaje = '';
$error = '';


// Obtiene el ID del Ride de GET o POST
$id_ride = intval($_GET['id'] ?? $_POST['id_ride'] ?? 0); 

if ($id_ride <= 0) {
    die("ID de Ride inválido."); 
}

// 1. Obtener detalles del Ride y validar disponibilidad
try {

    $sql_ride = "SELECT id_chofer, nombre_ride, costo_por_espacio, espacios_disponibles 
                 FROM Rides WHERE id_ride = ?";
    $stmt_ride = $pdo->prepare($sql_ride);
    $stmt_ride->execute([$id_ride]);
    $ride = $stmt_ride->fetch();

    if (!$ride) {
        // Maneja si el ID no existe
        $error = "El Ride solicitado no existe.";
    } elseif ($ride['espacios_disponibles'] <= 0) {
        // Maneja si el Ride existe pero está lleno
        $error = "El Ride **" . htmlspecialchars($ride['nombre_ride']) . "** no tiene espacios disponibles.";
    }
} catch (\PDOException $e) {
    $error = "Error al obtener datos del Ride: " . $e->getMessage();
}

// 2. Procesamiento de la Reserva (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {

    $espacios_solicitados = 1; 
    
    // ------------------------------------------------------------------
    // Aseguramos que 'estado' se maneje correctamente y no haya duplicidad.
    // ------------------------------------------------------------------
    $sql_check = "SELECT id_reserva 
                  FROM Reservas 
                  WHERE id_pasajero = ? 
                  AND id_ride = ? 
                  AND estado IN ('Pendiente', 'Aceptada')"; // Usamos IN para claridad

    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_pasajero, $id_ride]);
    
    if ($stmt_check->rowCount() > 0) {
        $error = "Ya tienes una reserva **Pendiente** o **Aceptada** para este Ride. Revisa 'Mis Reservas'.";
    }

    // ------------------------------------------------------------------
    // CORRECCIÓN 2: Comprobar disponibilidad antes de la transacción (redundancia segura)
    // ------------------------------------------------------------------
    if (empty($error) && $espacios_solicitados > $ride['espacios_disponibles']) {
         $error = "Error: No se pudo completar la reserva. Solo quedan {$ride['espacios_disponibles']} espacios disponibles.";
    }
    
    if (empty($error)) {
        try {
            $pdo->beginTransaction();
            
            // A. Registrar la Reserva en la tabla Reservas
            // Nota: Aquí se usa $ride['id_chofer'] que obtuvimos de la consulta inicial.
            $sql_reserva = "INSERT INTO Reservas (id_ride, id_pasajero, id_chofer, espacios_reservados, costo_total, estado, fecha_reserva) 
                            VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW())";
            
            $costo_total = $ride['costo_por_espacio'] * $espacios_solicitados;
            
            $stmt_reserva = $pdo->prepare($sql_reserva);
            
            $stmt_reserva->execute([
                $id_ride, 
                $id_pasajero, 
                $ride['id_chofer'], // Chofer obtenido del $ride fetch
                $espacios_solicitados, 
                $costo_total
            ]);
            
            // B. Descontar los espacios disponibles del Ride
            $sql_update_ride = "UPDATE Rides 
                                SET espacios_disponibles = espacios_disponibles - ? 
                                WHERE id_ride = ? 
                                AND espacios_disponibles >= ?"; // Doble chequeo de concurrencia
            $stmt_update = $pdo->prepare($sql_update_ride);
            $stmt_update->execute([$espacios_solicitados, $id_ride, $espacios_solicitados]);
            
            // Si la actualización falla (por concurrencia), lanzamos una excepción.
            if ($stmt_update->rowCount() === 0) {
                 throw new \Exception("La disponibilidad ha cambiado, no se pudo asegurar el espacio.");
            }

            $pdo->commit();
            $mensaje = "¡Reserva solicitada exitosamente! El Chofer **" . htmlspecialchars($ride['nombre_ride']) . "** debe aprobarla. Serás redirigido a **Mis Reservas** en 5 segundos.";
            header("Refresh: 5; url=../views/pasajero/ver_mis_reservas.php");
            
        } catch (\Exception $e) {
            $pdo->rollBack();
            // Mostrar error más específico, incluyendo el de concurrencia
            $error = "Error al procesar la reserva. Razón: " . ($e->getMessage() ?? "Error desconocido en la transacción.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar Ride - <?= htmlspecialchars($ride['nombre_ride'] ?? 'Error') ?></title>
</head>
<body>
    <h1> Confirmar Reserva de Ride</h1>
    <p><a href="../index.php">← Volver a Búsqueda</a></p>
    
    <?php if ($mensaje): ?>
        <div style="color: green; border: 1px solid green; padding: 15px; margin-bottom: 20px;">
            <p><strong>¡Reserva Exitosa!</strong></p>
            <p><?= htmlspecialchars($mensaje) ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="color: red; border: 1px solid red; padding: 15px; margin-bottom: 20px;">
            <p><strong>Error de Reserva:</strong></p>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>
    
    <?php 
    // Muestra el formulario si no hay errores graves y la reserva no ha sido procesada
    if (!$mensaje && empty($error) && $ride): 
        // Calcular el costo total una vez más para mostrarlo
        $costo_total_mostrar = $ride['costo_por_espacio'] * 1; 
    ?>
        <h2>Resumen del Ride</h2>
        <table border="1" cellpadding="10">
            <tr><th>Nombre del Ride</th><td><?= htmlspecialchars($ride['nombre_ride']) ?></td></tr>
            <tr><th>Costo por Espacio</th><td>₡<?= number_format($ride['costo_por_espacio'], 2) ?></td></tr>
            <tr><th>Espacios Disponibles</th><td><?= htmlspecialchars($ride['espacios_disponibles']) ?></td></tr>
            <tr><th>Espacios a Reservar</th><td>1</td></tr>
            <tr><th>Costo Total (Aprox.)</th><td>₡<?= number_format($costo_total_mostrar, 2) ?></td></tr>
        </table>
        <p>Al confirmar, se descontará **1 espacio** de la disponibilidad del Ride, y la solicitud será enviada al Chofer.</p>
        <form method="POST" action="reserva_ride.php">
            <input type="hidden" name="id_ride" value="<?= htmlspecialchars($id_ride) ?>">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Confirmar Reserva</button>
        </form>
    <?php endif; ?>
</body>
</html>