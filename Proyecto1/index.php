<?php
session_start();

require 'includes/db_conexion.php'; 

$busqueda = [
    'salida' => $_GET['salida'] ?? '',
    'llegada' => $_GET['llegada'] ?? '',
    'ordenar_por' => $_GET['ordenar_por'] ?? 'fecha_hora', 
    'direccion' => $_GET['direccion'] ?? 'ASC' 
];

$rides = [];
$errores = [];


$campos_ordenamiento = [
    'fecha_hora' => "FIELD(R.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), R.hora",
    'origen' => 'R.lugar_salida',
    'destino' => 'R.lugar_llegada'
];


$direccion_sql = strtoupper($busqueda['direccion']) === 'DESC' ? 'DESC' : 'ASC';
$campo_sql = $campos_ordenamiento[$busqueda['ordenar_por']] ?? $campos_ordenamiento['fecha_hora'];


$sql = "SELECT 
            R.*, 
            V.marca, 
            V.modelo, 
            V.anio,
            U.nombre AS nombre_chofer 
        FROM Rides R
        JOIN Vehiculos V ON R.id_vehiculo = V.id_vehiculo
        JOIN Usuarios U ON R.id_chofer = U.id_usuario
        WHERE R.espacios_disponibles > 0 "; 


if (!empty($busqueda['salida'])) {
    $sql .= " AND R.lugar_salida LIKE :salida ";
}
if (!empty($busqueda['llegada'])) {
    $sql .= " AND R.lugar_llegada LIKE :llegada ";
}

$sql .= " ORDER BY " . $campo_sql . " " . $direccion_sql;


try {
    $stmt = $pdo->prepare($sql);
    
    if (!empty($busqueda['salida'])) {
        $stmt->bindValue(':salida', '%' . $busqueda['salida'] . '%');
    }
    if (!empty($busqueda['llegada'])) {
        $stmt->bindValue(':llegada', '%' . $busqueda['llegada'] . '%');
    }
    
    $stmt->execute();
    $rides = $stmt->fetchAll();

} catch (\PDOException $e) {
    $errores[] = "Error al cargar los Rides: " . $e->getMessage();
}


$es_pasajero_logueado = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'pasajero';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda de Rides - Aventones</title>
</head>
<body>
    
    <div style="text-align: right; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
        <?php if (isset($_SESSION['tipo_usuario'])): ?>
            <span>¡Hola, <?= htmlspecialchars($_SESSION['nombre_usuario']) ?>!</span> 
            | <a href="views/<?= $_SESSION['tipo_usuario'] ?>/<?= $_SESSION['tipo_usuario'] ?>_panel.php">Ir a mi Panel</a>
            | <a href="public/logout.php">Cerrar Sesión</a>
        <?php else: ?>
            <a href="public/login.php" style="font-weight: bold;">Iniciar Sesión</a> | 
            <a href="public/registro_pasajero.php">Regístrate como Pasajero</a> |
            <a href="public/registro_chofer.php">Regístrate como Chofer</a>
        <?php endif; ?>
    </div>
    
    <h1>🌎 Rides Disponibles</h1>
    
    <?php if (!empty($errores)): ?>
        <div style="color: red; padding: 10px; border: 1px solid red;"><?= htmlspecialchars($errores[0]) ?></div>
    <?php endif; ?>

    <h2>Filtrar y Ordenar</h2>
    <form method="GET" action="index.php">
        <label for="salida">Partida:</label>
        <input type="text" name="salida" value="<?= htmlspecialchars($busqueda['salida']) ?>" placeholder="Lugar de Salida">

        <label for="llegada">Llegada:</label>
        <input type="text" name="llegada" value="<?= htmlspecialchars($busqueda['llegada']) ?>" placeholder="Lugar de Llegada">
        
        <br><br>

        <label for="ordenar_por">Ordenar por:</label>
        <select name="ordenar_por">
            <option value="fecha_hora" <?= $busqueda['ordenar_por'] == 'fecha_hora' ? 'selected' : '' ?>>Fecha y Hora</option>
            <option value="origen" <?= $busqueda['ordenar_por'] == 'origen' ? 'selected' : '' ?>>Lugar de Origen</option>
            <option value="destino" <?= $busqueda['ordenar_por'] == 'destino' ? 'selected' : '' ?>>Lugar de Destino</option>
        </select>
        
        <select name="direccion">
            <option value="ASC" <?= $busqueda['direccion'] == 'ASC' ? 'selected' : '' ?>>Ascendente (A-Z/Más Antiguo)</option>
            <option value="DESC" <?= $busqueda['direccion'] == 'DESC' ? 'selected' : '' ?>>Descendente (Z-A/Más Nuevo)</option>
        </select>

        <button type="submit">Buscar y Ordenar</button>
        <a href="index.php">Limpiar Búsqueda</a>
    </form>
    
    <hr>
    
    <h2>Resultados de la Búsqueda (<?= count($rides) ?> Rides)</h2>
    <?php if (empty($rides)): ?>
        <p>No se encontraron Rides que coincidan con los criterios de búsqueda o no hay espacios disponibles.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Ruta</th>
                    <th>Día y Hora</th>
                    <th>Vehículo (Marca, Modelo, Año)</th>
                    <th>Costo por Espacio</th>
                    <th>Espacios Disp.</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rides as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['lugar_salida']) ?></strong> → <strong><?= htmlspecialchars($r['lugar_llegada']) ?></strong></td>
                    <td><?= htmlspecialchars($r['dia_semana']) ?> a las <?= htmlspecialchars(substr($r['hora'], 0, 5)) ?></td>
                    <td><?= htmlspecialchars("{$r['marca']} {$r['modelo']} ({$r['anio']})") ?></td>
                  <td>₡<?= htmlspecialchars(number_format($r['costo_por_espacio'], 2)) ?></td>
                    <td><?= htmlspecialchars($r['espacios_disponibles']) ?></td>
                    <td>
                        <?php if ($es_pasajero_logueado): ?>
                            <a href="public/reserva_ride.php?id=<?= htmlspecialchars($r['id_ride']) ?>" style="background-color: #4CAF50; color: white; padding: 5px 10px; text-decoration: none;">Reservar</a>
                        <?php else: ?>
                            <a href="public/login.php" style="background-color: #f44336; color: white; padding: 5px 10px; text-decoration: none;">Reservar (Requiere Login)</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>