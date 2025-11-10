<?php
session_start();

require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 
require '../../includes/utils.php';      

check_role_access('chofer');

$id_chofer = $_SESSION['id_usuario'];
$mensaje = '';
$accion = $_POST['accion'] ?? '';
$errores = [];




if ($accion === 'eliminar' && isset($_POST['id_vehiculo'])) {
    $id_vehiculo = $_POST['id_vehiculo'];
    try {
       
        $stmt = $pdo->prepare("DELETE FROM Vehiculos WHERE id_vehiculo = ? AND id_chofer = ?");
        $stmt->execute([$id_vehiculo, $id_chofer]);
        $mensaje = "Vehículo eliminado exitosamente.";
    } catch (\PDOException $e) {
        $errores[] = "Error al eliminar el vehículo: " . $e->getMessage();
    }
}

if ($accion === 'crear' || $accion === 'editar_guardar') {
  
    $placa = trim($_POST['placa'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $anio = trim($_POST['anio'] ?? '');
    $capacidad = trim($_POST['capacidad_asientos'] ?? '');
    $id_vehiculo_editar = $_POST['id_vehiculo'] ?? null;
    

    $fotografia = 'no_photo.jpg';

 
    if (empty($placa) || empty($marca) || empty($capacidad) || !is_numeric($capacidad)) {
        $errores[] = "Faltan datos obligatorios o la capacidad no es un número.";
    }

    if (empty($errores)) {
        if ($accion === 'crear') {
            $sql = "INSERT INTO Vehiculos (id_chofer, placa, color, marca, modelo, anio, capacidad_asientos, fotografia_automotor) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_chofer, $placa, $color, $marca, $modelo, $anio, $capacidad, $fotografia]);
                $mensaje = "¡Vehículo **" . htmlspecialchars($placa) . "** registrado exitosamente!";
            } catch (\PDOException $e) {
              
                if ($e->getCode() == '23000' && strpos($e->getMessage(), 'placa') !== false) {
                    $errores[] = "La placa **" . htmlspecialchars($placa) . "** ya está registrada.";
                } else {
                    $errores[] = "Error al crear el vehículo: " . $e->getMessage();
                }
            }
        } elseif ($accion === 'editar_guardar' && $id_vehiculo_editar) {
            $sql = "UPDATE Vehiculos SET placa = ?, color = ?, marca = ?, modelo = ?, anio = ?, capacidad_asientos = ?, fotografia_automotor = ? 
                    WHERE id_vehiculo = ? AND id_chofer = ?";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$placa, $color, $marca, $modelo, $anio, $capacidad, $fotografia, $id_vehiculo_editar, $id_chofer]);
                $mensaje = "Vehículo **" . htmlspecialchars($placa) . "** actualizado exitosamente.";
            } catch (\PDOException $e) {
                 if ($e->getCode() == '23000' && strpos($e->getMessage(), 'placa') !== false) {
                    $errores[] = "La placa **" . htmlspecialchars($placa) . "** ya está registrada en otro vehículo.";
                } else {
                    $errores[] = "Error al actualizar el vehículo: " . $e->getMessage();
                }
            }
        }
    }
}


try {
    $stmt = $pdo->prepare("SELECT * FROM Vehiculos WHERE id_chofer = ? ORDER BY placa");
    $stmt->execute([$id_chofer]);
    $vehiculos = $stmt->fetchAll();
} catch (\PDOException $e) {
    $errores[] = "Error al cargar la lista de vehículos.";
    $vehiculos = [];
}



$vehiculo_a_editar = null;
if (isset($_GET['action']) && $_GET['action'] === 'editar' && isset($_GET['id'])) {
    $id_a_editar = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Vehiculos WHERE id_vehiculo = ? AND id_chofer = ?");
    $stmt->execute([$id_a_editar, $id_chofer]);
    $vehiculo_a_editar = $stmt->fetch();

    if (!$vehiculo_a_editar) {
        $errores[] = "Vehículo no encontrado o no pertenece a tu cuenta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Vehículos</title>
</head>
<body>
    <h1> Gestión de Vehículos</h1>
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

    <h2><?= ($vehiculo_a_editar ? 'Editar Vehículo ' . htmlspecialchars($vehiculo_a_editar['placa']) : 'Registrar Nuevo Vehículo') ?></h2>
    <form method="POST" action="gestionar_vehiculo.php">
        <input type="hidden" name="accion" value="<?= ($vehiculo_a_editar ? 'editar_guardar' : 'crear') ?>">
        <?php if ($vehiculo_a_editar): ?>
            <input type="hidden" name="id_vehiculo" value="<?= htmlspecialchars($vehiculo_a_editar['id_vehiculo']) ?>">
        <?php endif; ?>

        <label for="placa">Placa:</label>
        <input type="text" name="placa" value="<?= htmlspecialchars($vehiculo_a_editar['placa'] ?? '') ?>" required><br><br>

        <label for="marca">Marca:</label>
        <input type="text" name="marca" value="<?= htmlspecialchars($vehiculo_a_editar['marca'] ?? '') ?>" required><br><br>
        
        <label for="modelo">Modelo:</label>
        <input type="text" name="modelo" value="<?= htmlspecialchars($vehiculo_a_editar['modelo'] ?? '') ?>" required><br><br>

        <label for="color">Color:</label>
        <input type="text" name="color" value="<?= htmlspecialchars($vehiculo_a_editar['color'] ?? '') ?>" required><br><br>

        <label for="anio">Año:</label>
        <input type="number" name="anio" min="1950" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($vehiculo_a_editar['anio'] ?? date('Y')) ?>" required><br><br>

        <label for="capacidad_asientos">Capacidad de Asientos:</label>
        <input type="number" name="capacidad_asientos" min="1" max="10" value="<?= htmlspecialchars($vehiculo_a_editar['capacidad_asientos'] ?? '4') ?>" required><br><br>

        <button type="submit"><?= ($vehiculo_a_editar ? 'Guardar Cambios' : 'Registrar Vehículo') ?></button>
        <?php if ($vehiculo_a_editar): ?>
             <a href="gestionar_vehiculo.php">Cancelar Edición</a>
        <?php endif; ?>
    </form>
    
    <hr>
    
    <h2>Mis Vehículos Registrados (<?= count($vehiculos) ?>)</h2>
    <?php if (empty($vehiculos)): ?>
        <p>Aún no tienes vehículos registrados. Utiliza el formulario superior.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Marca/Modelo</th>
                    <th>Capacidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehiculos as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['placa']) ?></td>
                    <td><?= htmlspecialchars($v['marca']) ?> (<?= htmlspecialchars($v['modelo']) ?>)</td>
                    <td><?= htmlspecialchars($v['capacidad_asientos']) ?></td>
                    <td>
                        <a href="gestionar_vehiculo.php?action=editar&id=<?= htmlspecialchars($v['id_vehiculo']) ?>">Editar</a>
                        
                        <form method="POST" action="gestionar_vehiculo.php" style="display:inline; margin-left: 10px;" onsubmit="return confirm('¿Está seguro de eliminar el vehículo <?= htmlspecialchars($v['placa']) ?>?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_vehiculo" value="<?= htmlspecialchars($v['id_vehiculo']) ?>">
                            <button type="submit" style="color: red;">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>