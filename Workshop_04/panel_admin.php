<?php
// Archivo: panel_admin.php (Contiene Lógica + Vista)

session_start();
require 'conexion.php'; 

// 1. Verificación de Seguridad y Rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    // Si no es admin o no ha iniciado sesión, lo redirigimos
    header('Location: login.php?error=' . urlencode('Acceso denegado: Se requiere rol de Administrador.'));
    exit();
}

// 2. Consulta de todos los usuarios (Necesita las columnas rol y estado)
try {
    $sql = "SELECT id_usuario, nombre, apellidos, usuario, rol, estado FROM usuarios ORDER BY id_usuario DESC";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    // Error si las columnas rol/estado no existen
    die("Error al cargar usuarios. Asegúrate de que las columnas 'rol' y 'estado' existen en la tabla 'usuarios': " . $e->getMessage());
}

// Mensajes de estado (si vienen de las acciones de CRUD)
$status_message = '';
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $status_message = '<div class="alert alert-success">Operación realizada con éxito.</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .estado-activo { color: green; font-weight: bold; }
        .estado-inactivo { color: red; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Usuarios</h2>
        <div>
            <span class="me-3">Bienvenido, <?= htmlspecialchars($_SESSION['rol']) ?></span>
            <a href="logout.php" class="btn btn-secondary btn-sm">🚪 Cerrar Sesión</a>
        </div>
    </div>
    
    <?= $status_message ?>

    <div class="mb-3">
        <a href="user_form.php?action=agregar" class="btn btn-primary">➕ Agregar Nuevo Usuario</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usuarios): ?>
                    <?php foreach ($usuarios as $user): ?>
                    <tr>
                        <td><?= $user['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']) ?></td>
                        <td><?= htmlspecialchars($user['usuario']) ?></td>
                        <td><?= htmlspecialchars($user['rol']) ?></td>
                        <td class="<?= ($user['estado'] == 'activo') ? 'estado-activo' : 'estado-inactivo' ?>">
                            <?= ucfirst($user['estado']) ?>
                        </td>
                        <td>
                            <a href="user_form.php?action=editar&id=<?= $user['id_usuario'] ?>" class="btn btn-warning btn-sm me-1">✍🏻 Editar</a>
                            
                            <?php 
                            // Lógica para Deshabilitar/Habilitar
                            $new_state = ($user['estado'] == 'activo') ? 'inactivo' : 'activo';
                            $btn_class = ($user['estado'] == 'activo') ? 'btn-secondary' : 'btn-success';
                            $btn_text  = ($user['estado'] == 'activo') ? '🚫 Deshabilitar' : '✅ Habilitar';
                            ?>
                            <a href="cambiar_estado.php?id=<?= $user['id_usuario'] ?>&estado=<?= $new_state ?>" class="btn <?= $btn_class ?> btn-sm me-1">
                                <?= $btn_text ?>
                            </a>

                            <a href="eliminar_usuario.php?id=<?= $user['id_usuario'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar a <?= $user['usuario'] ?>?');">❌ Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>