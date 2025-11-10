<?php
//Formulario para CRUD
include("conexion.php");

$action = $_GET['action'];
$id = isset($_GET['id']) ? $_GET['id'] : '';

$marca = $modelo = $año = $tipo = $placa = '';

if (($action == 'editar' || $action == 'ver' || $action == 'eliminar') && $id != '') {
    $sql = "SELECT * FROM vehiculos WHERE id_vehiculo=$id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $marca = $row['marca'];
        $modelo = $row['modelo'];
        $año = $row['año'];
        $tipo = $row['tipo'];
        $placa = $row['placa'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Formulario - Vehículos</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
        <h2 class="text-center mb-4">
            <?php 
            if ($action == 'insertar') echo "Agregar Vehículo";
            elseif ($action == 'editar') echo "Editar Vehículo";
            elseif ($action == 'ver') echo "Detalles del Vehículo";
            elseif ($action == 'eliminar') echo "Eliminar Vehículo";
            ?>
        </h2>

        <?php if($action == 'ver' || $action == 'eliminar'): ?>
            <div class="mb-3">
                <p><b>ID:</b> <?php echo $id; ?></p>
                <p><b>Marca:</b> <?php echo $marca; ?></p>
                <p><b>Modelo:</b> <?php echo $modelo; ?></p>
                <p><b>Año:</b> <?php echo $año; ?></p>
                <p><b>Tipo:</b> <?php echo $tipo; ?></p>
                <p><b>Placa:</b> <?php echo $placa; ?></p>
            </div>

            <?php if($action == 'eliminar'): ?>
                <form action="eliminar.php" method="GET">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button class="btn btn-danger w-100">Confirmar Eliminación</button>
                </form>
            <?php endif; ?>

        <?php else: ?>
            <form action="<?php echo ($action == 'editar') ? 'editar.php' : 'insertar.php'; ?>" method="POST">
                <?php if ($action == 'editar'): ?>
                    <input type="hidden" name="id_vehiculo" value="<?php echo $id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Marca</label>
                    <input type="text" name="marca" class="form-control" value="<?php echo $marca; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-control" value="<?php echo $modelo; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Año</label>
                    <input type="number" name="año" class="form-control" value="<?php echo $año; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <input type="text" name="tipo" class="form-control" value="<?php echo $tipo; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Placa</label>
                    <input type="text" name="placa" class="form-control" value="<?php echo $placa; ?>" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <?php echo ($action == 'editar') ? 'Actualizar' : 'Guardar'; ?>
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="principal.php" class="text-decoration-none">Volver al panel principal</a>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
