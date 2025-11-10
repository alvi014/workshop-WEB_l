<?php
session_start();
require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 

check_role_access('pasajero');

$id_pasajero = $_SESSION['id_usuario'];
$mensaje = '';
$errores = [];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === 'cancelar') {
    $id_reserva = intval($_POST['id_reserva'] ?? 0);
    

    $pdo->beginTransaction();
    
    try {

        $stmt = $pdo->prepare("SELECT RES.id_ride, RES.espacios_reservados, RES.estado 
                               FROM Reservas RES 
                               WHERE RES.id_reserva = ? AND RES.id_pasajero = ? AND RES.estado IN ('Pendiente', 'Aceptada')");
        $stmt->execute([$id_reserva, $id_pasajero]);
        $reserva_data = $stmt->fetch();

        if (!$reserva_data) {
            throw new Exception("La reserva no se puede cancelar (no existe o ya fue gestionada).");
        }
        
        $estado_original = $reserva_data['estado'];
        $id_ride = $reserva_data['id_ride'];
        $espacios = $reserva_data['espacios_reservados'];

       
        $stmt = $pdo->prepare("UPDATE Reservas SET estado = 'Cancelada' WHERE id_reserva = ? AND id_pasajero = ?");
        $stmt->execute([$id_reserva, $id_pasajero]);
        
       
        if ($estado_original === 'Aceptada') {
            $stmt = $pdo->prepare("UPDATE Rides SET espacios_disponibles = espacios_disponibles + ? WHERE id_ride = ?");
            $stmt->execute([$espacios, $id_ride]);
            $mensaje = "Reserva cancelada exitosamente y **{$espacios} espacios liberados** en el Ride.";
        } else {
        
             $mensaje = "Solicitud de reserva cancelada exitosamente.";
        }
        
   
        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();
        $errores[] = "Error al intentar cancelar la reserva: " . $e->getMessage();
    }
}


try {
  
    $sql = "SELECT RES.*, R.nombre_ride, R.lugar_salida, R.lugar_llegada, R.dia_semana, R.hora
            FROM Reservas RES
            JOIN Rides R ON RES.id_ride = R.id_ride
            WHERE RES.id_pasajero = ? 
            ORDER BY RES.fecha_solicitud DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pasajero]);
    $reservas = $stmt->fetchAll();

} catch (\PDOException $e) {
    $errores[] = "Error al cargar la lista de mis reservas: " . $e->getMessage();
    $reservas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas</title>
</head>
<body>
    <h1>📋 Mis Solicitudes y Reservas</h1>
    <p><a href="pasajero_panel.php">← Volver al Panel</a></p>

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

    <h2>Historial de Reservas (<?= count($reservas) ?>)</h2>
    
    <?php if (empty($reservas)): ?>
        <p>Aún no has realizado ninguna solicitud de reserva.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Ride</th>
                    <th>Ruta</th>
                    <th>Día y Hora</th>
                    <th>Espacios</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $res): ?>
                <tr>
                    <td><?= htmlspecialchars($res['nombre_ride']) ?></td>
                    <td><?= htmlspecialchars($res['lugar_salida']) ?> → <?= htmlspecialchars($res['lugar_llegada']) ?></td>
                    <td><?= htmlspecialchars($res['dia_semana']) ?> a las <?= htmlspecialchars(substr($res['hora'], 0, 5)) ?></td>
                    <td><?= htmlspecialchars($res['espacios_reservados']) ?></td>
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
                        <?php if ($res['estado'] == 'Pendiente' || $res['estado'] == 'Aceptada'): ?>
                            <form method="POST" action="mis_reservas.php" onsubmit="return confirm('¿Está seguro de CANCELAR esta reserva/solicitud?');">
                                <input type="hidden" name="accion" value="cancelar">
                                <input type="hidden" name="id_reserva" value="<?= htmlspecialchars($res['id_reserva']) ?>">
                                <button type="submit" style="color: red;">Cancelar</button>
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