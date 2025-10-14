<?php
// Archivo: user_form.php
include 'user_controller.php'; 
// Ahora tenemos acceso a $form_title, $provincias, $nombre_value, etc.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $form_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
        <h2 class="text-center mb-4"><?= $form_title ?></h2>
        
        <form action="<?= $action_url ?>" method="POST">
            
            <?php if ($action == 'editar'): ?>
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($nombre_value) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="apellidos" class="form-label">Apellidos</label>
                <input type="text" name="apellidos" id="apellidos" class="form-control" value="<?= htmlspecialchars($apellidos_value) ?>" required>
            </div>

            <div class="mb-3">
                <label for="id_provincia" class="form-label">Provincia</label>
                <select id="id_provincia" name="id_provincia" class="form-select" required>
                    <option value="">Seleccione una provincia</option>
                    <?php foreach ($provincias as $provincia): ?>
                        <option value="<?= htmlspecialchars($provincia['id_provincia']) ?>" 
                            <?= ($provincia['id_provincia'] == $id_provincia_selected) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($provincia['nombreProvincia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" value="<?= htmlspecialchars($usuario_value) ?>" required>
            </div>

            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" name="contrasena" id="contrasena" class="form-control" 
                       placeholder="<?= ($action == 'editar') ? 'Dejar vacío para no cambiar' : 'Requerido' ?>"
                       <?= ($action == 'agregar') ? 'required' : '' ?>>
            </div>
            
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select id="rol" name="rol" class="form-select" required>
                    <option value="user" <?= ($rol_selected == 'user') ? 'selected' : '' ?>>Usuario Estándar</option>
                    <option value="admin" <?= ($rol_selected == 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <?= ($action == 'editar') ? 'Actualizar Usuario' : 'Registrar Usuario' ?>
            </button>
            <a href="panel_admin.php" class="btn btn-secondary w-100">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>