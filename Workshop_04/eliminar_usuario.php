<?php
// Archivo: eliminar_usuario.php
session_start();
require 'conexion.php'; 

// 1. Verificación de Seguridad: Solo ADMIN puede eliminar.
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: panel_admin.php?status=error_auth');
    exit();
}

$id_a_eliminar = $_GET['id'] ?? null;

// 2. Validación
if (!is_numeric($id_a_eliminar) || $id_a_eliminar <= 0) {
    header('Location: panel_admin.php?status=error_invalid_id');
    exit();
}

// Opcional: Impedir que el administrador elimine su propia cuenta
if ($id_a_eliminar == $_SESSION['user_id']) {
    header('Location: panel_admin.php?status=error_self_delete');
    exit();
}

try {
    // 3. Sentencia preparada para DELETE (más segura)
    $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_a_eliminar]);

    // 4. Redirección exitosa
    header('Location: panel_admin.php?status=success');
    exit();
} catch (PDOException $e) {
    // Error de BD
    header('Location: panel_admin.php?status=error_bd_delete');
    exit();
}

// ¡No cierres la etiqueta PHP!