<?php
session_start();

require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 


check_role_access('chofer');

$id_chofer = $_SESSION['id_usuario'];
$mensaje = '';
$errores = [];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $id_reserva = intval($_POST['id_reserva'] ?? 0);
    $id_ride = intval($_POST['id_ride'] ?? 0);
    $espacios_reservados = intval($_POST['espacios_reservados'] ?? 0);

   
    $pdo->beginTransaction();
    
    try {

        $stmt = $pdo->prepare("SELECT R.espacios_disponibles, RES.estado 
                               FROM Reservas RES 
                               JOIN Rides R ON RES.id_ride = R.id_ride
                               WHERE RES.id_reserva = ? AND R.id_chofer = ? AND RES.estado = 'Pendiente'");
        $stmt->execute([$id_reserva, $id_chofer]);
        $data = $stmt->fetch();

        if (!$data) {
            throw new Exception("La reserva no existe, no está Pendiente, o no te pertenece.");
        }
        
        $espacios_disponibles = $data['espacios_disponibles'];

        if ($accion === 'aceptar') {
            
     
            if ($espacios_reservados > $espacios_disponibles) {
                throw new Exception("No hay suficientes espacios disponibles ($espacios_disponibles) para aceptar esta reserva ($espacios_reservados).");
            }

        
            $stmt = $pdo->prepare("UPDATE Reservas SET estado = 'Aceptada' WHERE id_reserva = ?");
            $stmt->execute([$id_reserva]);

        
            $nuevo_disponible = $espacios_disponibles - $espacios_reservados;
            $stmt = $pdo->prepare("UPDATE Rides SET espacios_disponibles = ? WHERE id_ride = ?");
            $stmt->execute([$nuevo_disponible, $id_ride]);

            $mensaje = "Reserva Aceptada exitosamente. Espacios actualizados.";

        } elseif ($accion === 'rechazar') {
     
            $stmt = $pdo->prepare("UPDATE Reservas SET estado = 'Rechazada' WHERE id_reserva = ?");
            $stmt->execute([$id_reserva]);


            $mensaje = "Reserva Rechazada.";
        }
        

        $pdo->commit();

    } catch (Exception $e) {
       
        $pdo->rollBack();
        $errores[] = "Error en la gestión: " . $e->getMessage();
    }
}

// --- CONSULTA: Obtener Solicitudes de Reserva para los Rides del Chofer ---
try {
   
    $sql = "SELECT RES.*, R.nombre_ride, R.lugar_salida, R.lugar_llegada, P.nombre AS nombre_pasajero, P.apellido AS apellido_pasajero, P.correo_electronico
            FROM Reservas RES
            JOIN Rides R ON RES.id_ride = R.id_ride
            JOIN Usuarios P ON RES.id_pasajero = P.id_usuario
            WHERE R.id_chofer = ? 
            ORDER BY RES.fecha_solicitud DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_chofer]);
    $reservas = $stmt->fetchAll();

} catch (\PDOException $e) {
    $errores[] = "Error al cargar la lista de reservas: " . $e->getMessage();
    $reservas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Reservas</title>
</head>
<body>
    <h1>Solicitudes de Reserva</h1>
    <p><a href="chofer_panel.php">← Volver al Panel</a></p>

    <?php if (!empty($errores)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px;">
            <p><strong>Errores:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (!empty($mensaje)): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin-bottom: 20px;">
            <p><strong>Éxito:</strong> <?= htmlspecialchars($mensaje) ?></p>
        </div>
    <?php endif; ?>

    <h2>Reservas Asociadas a tus Rides (<?= count($reservas) ?>)</h2>
    
    <?php if (empty($reservas)): ?>
        <p>Aún no tienes solicitudes de reserva o reservas asociadas a tus Rides.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Ride Solicitado</th>
                    <th>Pasajero</th>
                    <th>Espacios</th>
                    <th>Fecha Solicitud</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $res): ?>
                <tr>
                    <td><?= htmlspecialchars($res['nombre_ride']) ?> (<?= htmlspecialchars($res['lugar_salida']) ?> → <?= htmlspecialchars($res['lugar_llegada']) ?>)</td>
                    <td><?= htmlspecialchars($res['nombre_pasajero'] . ' ' . $res['apellido_pasajero']) ?></td>
                    <td><?= htmlspecialchars($res['espacios_reservados']) ?></td>
                    <td><?= htmlspecialchars(date('d/M H:i', strtotime($res['fecha_solicitud']))) ?></td>
                    <td>
                        <strong style="color: 
                            <?php 
                                echo ($res['estado'] == 'Aceptada') ? 'green' : 
                                     (($res['estado'] == 'Pendiente') ? 'orange' : 'red');
                            ?>;">
                            <?= htmlspecialchars($res['estado']) ?>
                        </strong>
                    </td>
                    <td>
                        <?php if ($res['estado'] == 'Pendiente'): ?>
                            <form method="POST" action="gestionar_reserva.php" style="display:inline;" onsubmit="return confirm('¿Aceptar la reserva de <?= $res['nombre_pasajero'] ?>?');">
                                <input type="hidden" name="accion" value="aceptar">
                                <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($res['id_reserva']) ?>">
                                <input type="hidden" name="id_ride" value="<?= htmlspecialchars($res['id_ride']) ?>">
                                <input type="hidden" name="espacios_reservados" value="<?= htmlspecialchars($res['espacios_reservados']) ?>">
                                <button type="submit" style="background-color: green; color: white;">Aceptar</button>
                            </form>
                            
                            <form method="POST" action="gestionar_reserva.php" style="display:inline; margin-left: 5px;" onsubmit="return confirm('¿Rechazar la reserva de <?= $res['nombre_pasajero'] ?>?');">
                                <input type="hidden" name="accion" value="rechazar">
                                <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($res['id_reserva']) ?>">
                                <button type="submit" style="background-color: red; color: white;">Rechazar</button>
                            </form>
                        <?php else: ?>
                            ---
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>