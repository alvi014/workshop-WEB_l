<?php
// Archivo: procesar_usuario.php
session_start();
require 'conexion.php'; 

// 1. Verificación de Seguridad y Método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['rol'] !== 'admin') {
    header('Location: panel_admin.php?status=error_auth');
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_POST['id_usuario'] ?? null;

// Captura y limpieza de datos (incluyendo el hash de contraseña)
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$id_provincia = (int)($_POST['id_provincia'] ?? 0);
$usuario = trim($_POST['usuario'] ?? '');
$rol = $_POST['rol'] ?? 'user';
$contrasena_plana = $_POST['contrasena'] ?? '';

// Array para los datos base de la sentencia preparada
$params = [
    ':nombre' => $nombre,
    ':apellidos' => $apellidos,
    ':id_provincia' => $id_provincia,
    ':usuario' => $usuario,
    ':rol' => $rol,
];

try {
    if ($action == 'agregar') {
        // --- Lógica de INSERCIÓN (Agregar) ---
        if (empty($contrasena_plana)) {
             header('Location: user_form.php?action=agregar&error=' . urlencode('La contraseña es obligatoria.'));
             exit();
        }
        
        $contrasena_hash = password_hash($contrasena_plana, PASSWORD_DEFAULT);
        $params[':contrasena'] = $contrasena_hash;

        $sql = "INSERT INTO usuarios (nombre, apellidos, id_provincia, usuario, contrasena, rol) 
                VALUES (:nombre, :apellidos, :id_provincia, :usuario, :contrasena, :rol)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

    } elseif ($action == 'editar' && $id) {
        // --- Lógica de EDICIÓN (Actualizar) ---
        
        $sql_update = "UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, id_provincia = :id_provincia, 
                       usuario = :usuario, rol = :rol";
        
        if (!empty($contrasena_plana)) {
            // Si el usuario ingresó una nueva contraseña, la hasheamos y la incluimos
            $contrasena_hash = password_hash($contrasena_plana, PASSWORD_DEFAULT);
            $sql_update .= ", contrasena = :contrasena";
            $params[':contrasena'] = $contrasena_hash;
        }

        $sql_update .= " WHERE id_usuario = :id";
        $params[':id'] = $id;

        $stmt = $pdo->prepare($sql_update);
        $stmt->execute($params);

    } else {
        header('Location: panel_admin.php?status=error_action');
        exit();
    }

    // Redirección exitosa a la lista de usuarios
    header('Location: panel_admin.php?status=success');
    exit();

} catch (PDOException $e) {
    // Error, generalmente por duplicidad de 'usuario' o 'placa' (UNIQUE)
    header('Location: panel_admin.php?status=error_bd&msg=' . urlencode($e->getMessage()));
    exit();
}

// ¡No cierres la etiqueta PHP!