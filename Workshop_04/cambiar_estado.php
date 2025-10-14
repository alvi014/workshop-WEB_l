<?php
// Archivo: cambiar_estado.php (Backend)
session_start();
require 'conexion.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: panel_admin.php?status=error_auth');
    exit();
}

$id = $_GET['id'] ?? null;
$nuevo_estado = $_GET['estado'] ?? '';


if (!is_numeric($id) || $id <= 0 || !in_array($nuevo_estado, ['activo', 'inactivo'])) {
    header('Location: panel_admin.php?status=error_invalid_state');
    exit();
}


if ($id == $_SESSION['user_id'] && $nuevo_estado == 'inactivo') {
    header('Location: panel_admin.php?status=error_self_disable');
    exit();
}

try {
   
    $sql = "UPDATE usuarios SET estado = :estado WHERE id_usuario = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);

    header('Location: panel_admin.php?status=success');
    exit();
} catch (PDOException $e) {
    header('Location: panel_admin.php?status=error_bd');
    exit();
}