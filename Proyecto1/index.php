<?php
session_start();

// Definir el título de la página
$titulo_pagina = "Búsqueda de Rides"; 

// Incluir la conexión y el HEADER (Abre HTML, HEAD, BODY, NAV, y contenedor <main>)
require 'includes/db_conexion.php'; 
require 'includes/header.php'; 

$busqueda = [
    'salida' => $_GET['salida'] ?? '',
    'llegada' => $_GET['llegada'] ?? '',
    'ordenar_por' => $_GET['ordenar_por'] ?? 'fecha_hora', 
    'direccion' => $_GET['direccion'] ?? 'ASC' 
];

$rides = [];
$errores = [];

// Definición de campos para el ORDER BY
$campos_ordenamiento = [
    'fecha_hora' => "FIELD(R.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), R.hora",
    'origen' => 'R.lugar_salida',
    'destino' => 'R.lugar_llegada'
];

// Validación y construcción del ORDER BY
$direccion_sql = strtoupper($busqueda['direccion']) === 'DESC' ? 'DESC' : 'ASC';
$campo_sql = $campos_ordenamiento[$busqueda['ordenar_por']] ?? $campos_ordenamiento['fecha_hora'];


// Consulta SQL para buscar rides disponibles
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

$es_pasajero_logueado = isset($_SESSION['rol']) && $_SESSION['rol'] === 'pasajero';

?>

<h1 class="page-title">🛣️ Rides Disponibles</h1>
    
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-error"><?= htmlspecialchars($errores[0]) ?></div>
    <?php endif; ?>

    <section class="filter-section card p-4">
        <h2>Filtrar y Ordenar</h2>
        <form method="GET" action="index.php" class="search-form">
            <div class="row g-3">
                
                <div class="col-md-4 col-sm-6">
                    <label for="salida" class="form-label">Partida:</label>
                    <input type="text" name="salida" value="<?= htmlspecialchars($busqueda['salida']) ?>" placeholder="Lugar de Salida" class="form-control input-text">
                </div>

                <div class="col-md-4 col-sm-6">
                    <label for="llegada" class="form-label">Llegada:</label>
                    <input type="text" name="llegada" value="<?= htmlspecialchars($busqueda['llegada']) ?>" placeholder="Lugar de Llegada" class="form-control input-text">
                </div>
            </div>
            
            <div class="row g-3 mt-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label for="ordenar_por" class="form-label">Ordenar por:</label>
                    <select name="ordenar_por" class="form-select input-select">
                        <option value="fecha_hora" <?= $busqueda['ordenar_por'] == 'fecha_hora' ? 'selected' : '' ?>>Fecha y Hora</option>
                        <option value="origen" <?= $busqueda['ordenar_por'] == 'origen' ? 'selected' : '' ?>>Lugar de Origen</option>
                        <option value="destino" <?= $busqueda['ordenar_por'] == 'destino' ? 'selected' : '' ?>>Lugar de Destino</option>
                    </select>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <select name="direccion" class="form-select input-select">
                        <option value="ASC" <?= $busqueda['direccion'] == 'ASC' ? 'selected' : '' ?>>Ascendente</option>
                        <option value="DESC" <?= $busqueda['direccion'] == 'DESC' ? 'selected' : '' ?>>Descendente</option>
                    </select>
                </div>

                <div class="col-md-4 col-12 form-actions">
                    <button type="submit" class="btn btn-primary">Buscar y Ordenar</button>
                    <a href="index.php" class="btn btn-secondary">Limpiar Búsqueda</a>
                </div>
            </div>
        </form>
    </section>
    
    <hr class="my-5 separator">
    
    <section class="results-section">
        <h2>Resultados (<?= count($rides) ?> Rides)</h2>
        <?php if (empty($rides)): ?>
            <p class="no-results">No se encontraron Rides que coincidan con los criterios de búsqueda o no hay espacios disponibles.</p>
        <?php else: ?>
            <div class="row g-4 ride-list"> 
            <?php foreach ($rides as $r): ?>
                <div class="col-12 col-md-6 col-lg-4"> 
                    <div class="ride-card h-100">
                        <div class="ride-header">
                            <span class="ride-route">
                                <strong class="route-start"><?= htmlspecialchars($r['lugar_salida']) ?></strong>
                                <span class="arrow">→</span> 
                                <strong class="route-end"><?= htmlspecialchars($r['lugar_llegada']) ?></strong>
                            </span>
                        </div>

                        <div class="ride-details mb-4">
                            <p class="mb-1">Día y Hora: <strong><?= htmlspecialchars($r['dia_semana']) ?></strong> a las <strong><?= htmlspecialchars(substr($r['hora'], 0, 5)) ?></strong></p>
                            <p class="mb-1">Vehículo: <span class="car-info"><?= htmlspecialchars("{$r['marca']} {$r['modelo']} ({$r['anio']})") ?></span></p>
                            <p class="mb-1">Chofer: <strong><?= htmlspecialchars($r['nombre_chofer']) ?></strong></p>
                            <p class="mb-1">Espacios Disponibles: <strong class="spaces-left"><?= htmlspecialchars($r['espacios_disponibles']) ?></strong></p>
                            <p class="price mt-2">Costo por Espacio: <strong>₡<?= htmlspecialchars(number_format($r['costo_por_espacio'], 2)) ?></strong></p>
                        </div>

                        <div class="ride-actions mt-auto">
                            <?php if ($es_pasajero_logueado): ?>
                                <a href="public/reserva_ride.php?id=<?= htmlspecialchars($r['id_ride']) ?>" class="btn btn-success w-100">Reservar</a>
                            <?php else: ?>
                                <a href="public/login.php" class="btn btn-warning w-100">Reservar (Requiere Login)</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

<?php
require 'includes/footer.php';
?>