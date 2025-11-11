<?php
session_start();

// Definir el título de la página
$titulo_pagina = "Búsqueda de Rides"; 

// Incluir la conexión a la base de datos
// RUTA RELATIVA: Asume que 'includes' es una subcarpeta directa de la carpeta de index.php
require 'includes/db_conexion.php';


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


// Comprobación de rol de Pasajero
$es_pasajero_logueado = isset($_SESSION['id_usuario']) && 
                        isset($_SESSION['tipo_usuario']) && 
                        $_SESSION['tipo_usuario'] === 'pasajero';

// LÓGICA: Comprobación de rol de Chofer o rol 'dual'
$es_chofer_logueado = isset($_SESSION['tipo_usuario']) && 
                      ($_SESSION['tipo_usuario'] === 'chofer' || $_SESSION['tipo_usuario'] === 'dual');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        
    <link rel="icon" href="img/favicon.ico" type="image/x-icon"> 
    <style>
        /* Estilos básicos para el header */
        .main-header {
            background-color: #343a40; 
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-header .logo a {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .main-nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .main-nav ul li {
            margin-left: 15px;
        }
    </style>
</head>
<body>

<header class="main-header">
    <div class="logo">
        <!-- RUTA RELATIVA: Vuelve a index.php en la misma carpeta -->
        <a href="index.php"> 
            <span class="logo-text">Aventones</span>
        </a>
    </div>
    <nav class="main-nav">
        <ul>
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <li class="nav-welcome">
                    <span class="text-white-50 me-2">¡Hola, <strong><?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?></strong>!</span>
                </li>
                
                <!-- RUTA RELATIVA: public/views/pasajero/pasajero_panel.php -->
                <?php if ($es_pasajero_logueado): ?>
                <li><a href="public/views/pasajero/pasajero_panel.php" class="btn btn-warning">Panel Pasajero 👤</a></li>
                <?php endif; ?>

                <!-- RUTA RELATIVA: public/views/chofer/chofer_panel.php -->
                <?php if ($es_chofer_logueado): ?>
                <li><a href="public/views/chofer/chofer_panel.php" class="btn btn-info">Panel Chofer 🚗</a></li>
                <?php endif; ?>

                <!-- RUTA RELATIVA: public/logout.php -->
                <li><a href="public/logout.php" class="btn btn-danger">Cerrar Sesión</a></li>
                
            <?php else: ?>
                <!-- RUTA RELATIVA: public/login.php -->
                <li><a href="public/login.php" class="btn btn-success">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="main-content-container container mt-4 mb-5">

    <h1 class="page-title">Rides Disponibles</h1>
        
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger alert-error"><?= htmlspecialchars($errores[0]) ?></div>
        <?php endif; ?>

        <section class="filter-section card p-4">
            <h2>Filtrar y Ordenar</h2>
            <!-- RUTA RELATIVA PARA EL FORMULARIO -->
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
                        <!-- RUTA RELATIVA PARA LIMPIAR BÚSQUEDA -->
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
                        <div class="ride-card h-100 p-3 shadow-sm border rounded d-flex flex-column">
                            <div class="ride-header border-bottom pb-2 mb-2">
                                <span class="ride-route d-block fw-bold">
                                    <strong class="route-start text-primary"><?= htmlspecialchars($r['lugar_salida']) ?></strong>
                                    <span class="arrow text-muted mx-1">→</span> 
                                    <strong class="route-end text-success"><?= htmlspecialchars($r['lugar_llegada']) ?></strong>
                                </span>
                            </div>

                            <div class="ride-details mb-4">
                                <p class="mb-1"><small class="text-muted">Día y Hora:</small> <strong><?= htmlspecialchars($r['dia_semana']) ?></strong> a las <strong><?= htmlspecialchars(substr($r['hora'], 0, 5)) ?></strong></p>
                                <p class="mb-1"><small class="text-muted">Vehículo:</small> <span class="car-info"><?= htmlspecialchars("{$r['marca']} {$r['modelo']} ({$r['anio']})") ?></span></p>
                                <p class="mb-1"><small class="text-muted">Chofer:</small> <strong><?= htmlspecialchars($r['nombre_chofer']) ?></strong></p>
                                <p class="mb-1"><small class="text-muted">Disponibles:</small> <strong class="spaces-left text-danger"><?= htmlspecialchars($r['espacios_disponibles']) ?></strong></p>
                                <p class="price mt-2 h5">Costo: <strong>₡<?= htmlspecialchars(number_format($r['costo_por_espacio'], 2)) ?></strong></p>
                            </div>

                            <div class="ride-actions mt-auto">
                                <?php if (isset($_SESSION['id_usuario'])): // Si está logueado, ve el botón de Reservar ?>
                                    <!-- RUTA RESERVAR (ELIMINADO htmlspecialchars para la URL) -->
                                    <a href="public/reserva_ride.php?id=<?= $r['id_ride'] ?>" class="btn btn-success w-100">Reservar</a>
                                <?php else: ?>
                                    <!-- RUTA LOGIN (RUTA RELATIVA) -->
                                    <a href="public/login.php" class="btn btn-warning w-100">Reservar (Requiere Login)</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

</main>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">© <?= date('Y') ?> Aventones.</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>