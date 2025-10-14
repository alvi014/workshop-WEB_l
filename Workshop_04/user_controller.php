<?php
// Archivo: user_controller.php

session_start();
require 'conexion.php'; 

// Verificación de seguridad: Solo ADMIN puede acceder a este controlador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php?error=' . urlencode('Permiso denegado.'));
    exit();
}

// Inicializar variables del formulario
$action = $_GET['action'] ?? 'agregar'; // Determina si es agregar o editar
$id = $_GET['id'] ?? null;
$user_data = [];
$form_title = ($action == 'editar') ? 'Editar Usuario' : 'Agregar Nuevo Usuario';

// 1. Cargar Provincias
try {
    $stmt_provincia = $pdo->query("SELECT id_provincia, nombreProvincia FROM provincia ORDER BY nombreProvincia");
    $provincias = $stmt_provincia->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar provincias: " . $e->getMessage());
}

// 2. Cargar datos del usuario si la acción es EDITAR
if ($action == 'editar' && $id) {
    try {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
        $stmt_user = $pdo->prepare($sql);
        $stmt_user->execute([':id' => $id]);
        $user_data = $stmt_user->fetch();

        if (!$user_data) {
            header('Location: panel_admin.php?status=error_user_not_found');
            exit();
        }
    } catch (PDOException $e) {
        die("Error al cargar datos del usuario: " . $e->getMessage());
    }
}

// Variables que se usarán en la VISTA (user_form.php)
$nombre_value = $user_data['nombre'] ?? '';
$apellidos_value = $user_data['apellidos'] ?? '';
$usuario_value = $user_data['usuario'] ?? '';
$id_provincia_selected = $user_data['id_provincia'] ?? ''; // Para precargar el select
$rol_selected = $user_data['rol'] ?? 'user';
$action_url = 'procesar_usuario.php?action=' . $action;

// ¡No cerrar la etiqueta PHP!