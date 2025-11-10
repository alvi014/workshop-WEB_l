<?php
session_start();
require '../../includes/db_conexion.php'; 
require '../../includes/auth_check.php'; 
require '../../includes/utils.php';


check_role_access('administrador');

$id_admin_actual = $_SESSION['id_usuario'];
$mensaje = '';
$errores = [];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === 'desactivar') {
    $id_usuario_target = intval($_POST['id_usuario'] ?? 0);
    
    if ($id_usuario_target === $id_admin_actual) {
        $errores[] = "Error: No puedes desactivar tu propia cuenta mientras estás activo.";
    } elseif ($id_usuario_target > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE Usuarios SET estado = 'Inactivo' WHERE id_usuario = ?");
            $stmt->execute([$id_usuario_target]);
            $mensaje = "Usuario con ID **{$id_usuario_target}** ha sido **DESACTIVADO** exitosamente.";
        } catch (\PDOException $e) {
            $errores[] = "Error al desactivar el usuario: " . $e->getMessage();
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === 'activar') {
    $id_usuario_target = intval($_POST['id_usuario'] ?? 0);
    if ($id_usuario_target > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE Usuarios SET estado = 'Activa' WHERE id_usuario = ?");
            $stmt->execute([$id_usuario_target]);
            $mensaje = "Usuario con ID **{$id_usuario_target}** ha sido **ACTIVADO** exitosamente.";
        } catch (\PDOException $e) {
            $errores[] = "Error al activar el usuario: " . $e->getMessage();
        }
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === 'crear_admin') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = strtolower(trim($_POST['correo_electronico'] ?? ''));
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        $errores[] = "Todos los campos para crear un Admin son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido.";
    } else {
        try {
     
            $stmt = $pdo->prepare("SELECT id_usuario FROM Usuarios WHERE correo_electronico = ?");
            $stmt->execute([$correo]);
            if ($stmt->fetch()) {
                $errores[] = "El correo electrónico ya está registrado.";
            } else {
                
                $contrasena_hash = hash_password($contrasena); 
                
                $sql = "INSERT INTO Usuarios 
                        (nombre, apellido, cedula, fecha_nacimiento, correo_electronico, telefono, contrasena_hash, tipo_usuario, estado) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
             
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nombre, 
                    'Admin', 
                    '0000', 
                    '2000-01-01', 
                    $correo, 
                    '0000', 
                    $contrasena_hash, 
                    'administrador', 
                    'Activa'
                ]);
                
                $mensaje = "¡Nuevo Administrador **{$nombre}** creado exitosamente y Activo!";
            }
        } catch (\PDOException $e) {
            $errores[] = "Error al crear el Administrador: " . $e->getMessage();
        }
    }
}


try {
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, apellido, tipo_usuario, correo_electronico, estado FROM Usuarios ORDER BY tipo_usuario, nombre");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (\PDOException $e) {
    $errores[] = "Error al cargar la lista de usuarios: " . $e->getMessage();
    $usuarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Admin</title>
</head>
<body>
    <h1>👥 Gestión de Usuarios</h1>
    <p><a href="admin_panel.php">← Volver al Panel</a></p>

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
    
    <hr>

    <h2>Crear Nuevo Usuario Administrador</h2>
    <form method="POST" action="gestionar_usuarios.php">
        <input type="hidden" name="accion" value="crear_admin">

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required><br><br>

        <label for="correo_electronico">Correo Electrónico:</label>
        <input type="email" name="correo_electronico" required><br><br>

        <label for="contrasena">Contraseña Temporal:</label>
        <input type="password" name="contrasena" required><br><br>

        <button type="submit">Crear Administrador</button>
    </form>
    
    <hr>
    
    <h2>Lista General de Usuarios (<?= count($usuarios) ?>)</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['id_usuario']) ?></td>
                <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></td>
                <td><?= htmlspecialchars($u['correo_electronico']) ?></td>
                <td><?= htmlspecialchars($u['tipo_usuario']) ?></td>
                <td>
                    <strong style="color: <?= ($u['estado'] == 'Activa' ? 'green' : ($u['estado'] == 'Pendiente' ? 'orange' : 'red')); ?>">
                        <?= htmlspecialchars($u['estado']) ?>
                    </strong>
                </td>
                <td>
                    <?php if ($u['id_usuario'] === $id_admin_actual): ?>
                        <span style="color: gray;">(Tu Cuenta)</span>
                    <?php elseif ($u['estado'] !== 'Inactivo'): ?>
                        <form method="POST" action="gestionar_usuarios.php" style="display:inline;" onsubmit="return confirm('ADVERTENCIA: ¿Está seguro de DESACTIVAR la cuenta de <?= $u['nombre'] ?>?');">
                            <input type="hidden" name="accion" value="desactivar">
                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($u['id_usuario']) ?>">
                            <button type="submit" style="color: red;">Desactivar</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="gestionar_usuarios.php" style="display:inline;" onsubmit="return confirm('¿Desea ACTIVAR la cuenta de <?= $u['nombre'] ?>?');">
                            <input type="hidden" name="accion" value="activar">
                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($u['id_usuario']) ?>">
                            <button type="submit" style="color: green;">Activar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>