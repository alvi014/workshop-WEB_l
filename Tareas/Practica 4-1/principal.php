<?php
// Archivo: principal.php

include("conexion.php");
// Incluimos la lógica de la función de búsqueda
include("busqueda.php"); 

// 1. Obtener los parámetros GET para la búsqueda
$search_column = $_GET['column'] ?? 'placa'; 
$search_term = $_GET['search'] ?? '';

// 2. Llamar a la función para obtener los resultados
$result = buscarVehiculos($conn, $search_column, $search_term);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Principal - Vehículos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <h2 class="text-center mb-4">Panel Principal - Vehículos</h2>

    <div class="row mb-4 justify-content-center">
        <div class="col-lg-8">
            <form action="principal.php" method="GET" class="d-flex shadow-sm p-3 bg-white rounded">
                
                <select name="column" class="form-select me-2" style="max-width: 150px;">
                    <option value="placa" <?php if ($search_column == 'placa') echo 'selected'; ?>>Placa</option>
                    <option value="marca" <?php if ($search_column == 'marca') echo 'selected'; ?>>Marca</option>
                    <option value="modelo" <?php if ($search_column == 'modelo') echo 'selected'; ?>>Modelo</option>
                    <option value="año" <?php if ($search_column == 'año') echo 'selected'; ?>>Año</option>
                    <option value="tipo" <?php if ($search_column == 'tipo') echo 'selected'; ?>>Tipo</option>
                </select>
                
                <input type="text" name="search" class="form-control me-2" 
                       placeholder="Buscar vehículo..." 
                       value="<?php echo htmlspecialchars($search_term); ?>">
                
                <button type="submit" class="btn btn-info text-white">🔎 Buscar</button>
                <?php if (!empty($search_term)): ?>
                    <a href="principal.php" class="btn btn-secondary ms-2">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="d-flex justify-content-center mb-4">
        <a href="formulario.php?action=insertar" class="btn btn-success">🚗 Agregar Vehículo</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Tipo</th>
                    <th>Placa</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // El resultado ya fue generado por la función buscarVehiculos()
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_vehiculo'] . "</td>";
                    echo "<td>" . $row['marca'] . "</td>";
                    echo "<td>" . $row['modelo'] . "</td>";
                    echo "<td>" . $row['año'] . "</td>";
                    echo "<td>" . $row['tipo'] . "</td>";
                    echo "<td>" . $row['placa'] . "</td>";
                    echo "<td>
                            <a href='formulario.php?action=ver&id=" . $row['id_vehiculo'] . "' class='btn btn-info btn-sm me-1'>👀 Ver</a>
                            <a href='formulario.php?action=editar&id=" . $row['id_vehiculo'] . "' class='btn btn-warning btn-sm me-1'>✍🏻 Editar</a>
                            <a href='formulario.php?action=eliminar&id=" . $row['id_vehiculo'] . "' class='btn btn-danger btn-sm'>❌ Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No se encontraron vehículos que coincidan con la búsqueda.</td></tr>";
            }
            $conn->close();
            ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>