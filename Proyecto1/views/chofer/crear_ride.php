<?php
session_start();

require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 

check_role_access('chofer');

$id_chofer = $_SESSION['id_usuario'];
$mensaje = '';
$accion = $_POST['accion'] ?? '';
$errores = [];

// Variables para el formulario (usando nombres consistentes con la tabla)
$nombre_ride = $lugar_salida = $lugar_llegada = $dia_semana = $hora = '';
$costo_por_espacio = $espacios_totales = 0;
$id_vehiculo = 0;
$id_ride_editar = null; 
$modo_edicion = false; 

$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];


// --- 1. CONSULTA: Obtener lista de Vehículos del Chofer ---
try {
    $stmt = $pdo->prepare("SELECT id_vehiculo, placa, marca, modelo, capacidad_asientos FROM Vehiculos WHERE id_chofer = ? ORDER BY placa");
    $stmt->execute([$id_chofer]);
    $vehiculos_chofer = $stmt->fetchAll();
    
    if (empty($vehiculos_chofer) && $_SERVER["REQUEST_METHOD"] != "POST") {
        $errores[] = "No puedes crear un Ride sin tener al menos un vehículo registrado. Por favor, ve a <a href='gestionar_vehiculos.php'>Gestionar Vehículos</a>.";
    }
} catch (\PDOException $e) {
    $errores[] = "Error al cargar la lista de vehículos: " . $e->getMessage();
    $vehiculos_chofer = [];
}


// --- 2. Lógica de Procesamiento (CRUD) ---
if ($accion === 'crear' || $accion === 'editar_guardar') {
    // Recolección y validación de datos (usando nombres de formulario y tabla)
    $nombre_ride = trim($_POST['nombre_ride'] ?? '');
    $lugar_salida = trim($_POST['lugar_salida'] ?? '');
    $lugar_llegada = trim($_POST['lugar_llegada'] ?? '');
    $dia_semana = trim($_POST['dia_semana'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $costo_por_espacio = floatval($_POST['costo_por_espacio'] ?? 0);
    $espacios_totales = intval($_POST['espacios_totales'] ?? 0);
    $id_vehiculo = intval($_POST['id_vehiculo'] ?? 0);
    $id_ride_editar = $_POST['id_ride'] ?? null;
    
    
    if (empty($nombre_ride) || empty($lugar_salida) || empty($lugar_llegada) || !in_array($dia_semana, $dias_semana) || empty($hora) || $costo_por_espacio <= 0 || $espacios_totales <= 0 || $id_vehiculo == 0) {
        $errores[] = "Asegúrate de llenar todos los campos y seleccionar un vehículo.";
    }
    
    // Validación de vehículo pertenencia y capacidad
    $vehiculo_valido = false;
    foreach($vehiculos_chofer as $v) {
        if ($v['id_vehiculo'] == $id_vehiculo) {
            if ($espacios_totales > $v['capacidad_asientos']) {
                 $errores[] = "Los espacios solicitados ($espacios_totales) exceden la capacidad del vehículo ({$v['capacidad_asientos']}).";
            }
            $vehiculo_valido = true;
            break;
        }
    }
    if (!$vehiculo_valido && $id_vehiculo != 0) {
        $errores[] = "El vehículo seleccionado no es válido o no existe.";
    }

    if (empty($errores)) {
        if ($accion === 'crear') {
            
            // INSERT: Uso de nombre_ride, dia_semana, costo_por_espacio, espacios_totales
            $sql = "INSERT INTO Rides (id_chofer, id_vehiculo, nombre_ride, lugar_salida, lugar_llegada, dia_semana, hora, costo_por_espacio, espacios_totales, espacios_disponibles) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            try {
                $stmt = $pdo->prepare($sql);
                // NOTA: Se usan los espacios_totales como espacios_disponibles al crear.
                $stmt->execute([$id_chofer, $id_vehiculo, $nombre_ride, $lugar_salida, $lugar_llegada, $dia_semana, $hora, $costo_por_espacio, $espacios_totales, $espacios_totales]);
                $mensaje = "¡Ride **" . htmlspecialchars($nombre_ride) . "** registrado exitosamente!";
            } catch (\PDOException $e) {
                $errores[] = "Error al crear el Ride: " . $e->getMessage();
            }
        } elseif ($accion === 'editar_guardar' && $id_ride_editar) {
            
      
            $sql = "UPDATE Rides SET 
                        id_vehiculo = ?, nombre_ride = ?, lugar_salida = ?, lugar_llegada = ?, 
                        dia_semana = ?, hora = ?, costo_por_espacio = ?, espacios_totales = ?, espacios_disponibles = ?
                    WHERE id_ride = ? AND id_chofer = ?";
            
            try {
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute([$id_vehiculo, $nombre_ride, $lugar_salida, $lugar_llegada, $dia_semana, $hora, $costo_por_espacio, $espacios_totales, $espacios_totales, $id_ride_editar, $id_chofer]);
                
                $mensaje = "¡Ride **" . htmlspecialchars($nombre_ride) . "** actualizado exitosamente!";
            } catch (\PDOException $e) {
                $errores[] = "Error al actualizar el Ride: " . $e->getMessage();
            }
        }
        header("Location: crear_ride.php");
        exit();
    }
}

// --- 3. Lógica para Cargar Datos al Formulario de EDICIÓN (GET) ---
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $id_ride_editar = intval($_GET['id'] ?? 0);
    if ($id_ride_editar > 0) {
        // CORRECCIÓN SELECT: Usando nombres correctos de columna
        $sql = "SELECT id_ride, nombre_ride, lugar_salida, lugar_llegada, dia_semana, hora, costo_por_espacio, espacios_totales, id_vehiculo FROM Rides WHERE id_ride = ? AND id_chofer = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_ride_editar, $id_chofer]);
        $ride_a_editar = $stmt->fetch();
        
        if ($ride_a_editar) {
            $nombre_ride = $ride_a_editar['nombre_ride'];
            $lugar_salida = $ride_a_editar['lugar_salida'];
            $lugar_llegada = $ride_a_editar['lugar_llegada'];
            $dia_semana = $ride_a_editar['dia_semana'];
            $hora = $ride_a_editar['hora'];
            $costo_por_espacio = $ride_a_editar['costo_por_espacio'];
            $espacios_totales = $ride_a_editar['espacios_totales'];
            $id_vehiculo = $ride_a_editar['id_vehiculo'];
            $modo_edicion = true;
        } else {
            $errores[] = "Ride no encontrado o no autorizado.";
            $id_ride_editar = null; 
        }
    }
}

// --- 4. Lógica para ELIMINAR Ride (GET) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_ride = intval($_GET['id'] ?? 0);
    if ($id_ride > 0) {
        try {
            // Se verifica que el ride pertenezca al chofer logueado y se elimina
            $sql = "DELETE FROM Rides WHERE id_ride = ? AND id_chofer = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_ride, $id_chofer]);
            if ($stmt->rowCount() > 0) {
                $mensaje = "Ride eliminado exitosamente.";
            } else {
                $errores[] = "Error: No se encontró el Ride o no te pertenece.";
            }
        } catch (\PDOException $e) {
            $errores[] = "Error de BD al eliminar: " . $e->getMessage();
        }
    }
    header("Location: crear_ride.php");
    exit();
}


// --- 5. CONSULTA: Obtener lista de Rides del Chofer (para la tabla) ---
try {
 
    $sql_rides = "SELECT R.*, V.placa, V.modelo 
                  FROM Rides R 
                  JOIN Vehiculos V ON R.id_vehiculo = V.id_vehiculo
                  WHERE R.id_chofer = ?
                  ORDER BY R.dia_semana, R.hora"; 
    $stmt_rides = $pdo->prepare($sql_rides);
    $stmt_rides->execute([$id_chofer]);
    $rides = $stmt_rides->fetchAll();
} catch (\PDOException $e) {
    $errores[] = "Error al cargar rides: " . $e->getMessage();
    $rides = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $modo_edicion ? 'Editar Ride' : 'Crear Ride' ?> - Chofer</title>
</head>
<body>
    <h1>Gestión de Rides</h1>
    <p><a href="chofer_panel.php">← Volver al Panel</a></p>

    <?php if ($mensaje): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin-bottom: 20px;"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px;">
            <p><strong>Errores:</strong></p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2><?= $modo_edicion ? 'Editar Ride' : 'Crear Nuevo Ride' ?></h2>
    
    <form method="POST" action="crear_ride.php">
        <input type="hidden" name="accion" value="<?= $modo_edicion ? 'editar_guardar' : 'crear' ?>">
        <input type="hidden" name="id_ride" value="<?= htmlspecialchars($id_ride_editar) ?>">
        
        <label for="nombre_ride">Nombre del Ride:</label>
        <input type="text" id="nombre_ride" name="nombre_ride" value="<?= htmlspecialchars($nombre_ride) ?>" required><br><br>

        <label for="id_vehiculo">Vehículo Asignado:</label>
        <select id="id_vehiculo" name="id_vehiculo" required>
            <option value="">-- Seleccione un Vehículo --</option>
            <?php foreach ($vehiculos_chofer as $v): ?>
                <option value="<?= htmlspecialchars($v['id_vehiculo']) ?>" 
                        <?= ($id_vehiculo == $v['id_vehiculo']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['placa']) ?> (<?= htmlspecialchars($v['modelo']) ?> / Cap: <?= htmlspecialchars($v['capacidad_asientos']) ?>)
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="lugar_salida">Lugar de Salida:</label>
        <input type="text" id="lugar_salida" name="lugar_salida" value="<?= htmlspecialchars($lugar_salida) ?>" required><br><br>
        
        <label for="lugar_llegada">Lugar de Llegada:</label>
        <input type="text" id="lugar_llegada" name="lugar_llegada" value="<?= htmlspecialchars($lugar_llegada) ?>" required><br><br>

        <label for="dia_semana">Día de la Semana:</label>
        <select id="dia_semana" name="dia_semana" required>
            <option value="">-- Seleccione Día --</option>
            <?php foreach ($dias_semana as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>" <?= ($dia_semana == $d) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="hora">Hora (HH:MM):</label>
        <input type="time" id="hora" name="hora" value="<?= htmlspecialchars($hora) ?>" required><br><br>

        <label for="costo_por_espacio">Costo por Espacio (CRC):</label>
        <input type="number" step="0.01" id="costo_por_espacio" name="costo_por_espacio" value="<?= htmlspecialchars($costo_por_espacio) ?>" min="0.01" required><br><br>

        <label for="espacios_totales">Espacios Totales:</label>
        <input type="number" id="espacios_totales" name="espacios_totales" value="<?= htmlspecialchars($espacios_totales) ?>" min="1" max="8" required><br><br>

        <button type="submit"><?= $modo_edicion ? 'Guardar Cambios' : 'Registrar Ride' ?></button>
    </form>
    
    <hr>
    
    <h2>Mis Rides Registrados</h2>

    <?php if (empty($rides)): ?>
        <p>Aún no tienes Rides registrados. Crea uno para que los Pasajeros puedan reservarlo.</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Ruta</th>
                    <th>Día y Hora</th>
                    <th>Costo</th>
                    <th>Espacios Disp.</th>
                    <th>Vehículo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rides as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre_ride']) ?></td>
                    <td><?= htmlspecialchars($r['lugar_salida']) ?> → <?= htmlspecialchars($r['lugar_llegada']) ?></td>
                    <td><?= htmlspecialchars($r['dia_semana']) ?> a las <?= htmlspecialchars($r['hora']) ?></td>
                    <td>₡<?= number_format($r['costo_por_espacio'], 2) ?></td>
                    <td><?= htmlspecialchars($r['espacios_disponibles']) ?></td>
                    <td><?= htmlspecialchars($r['modelo']) ?> (<?= htmlspecialchars($r['placa']) ?>)</td>
                    <td>
                        <a href="crear_ride.php?action=edit&id=<?= htmlspecialchars($r['id_ride']) ?>">Editar</a> |
                        <a href="crear_ride.php?action=delete&id=<?= htmlspecialchars($r['id_ride']) ?>" onclick="return confirm('¿Estás seguro de eliminar este Ride? Esto eliminará las reservas asociadas.')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>