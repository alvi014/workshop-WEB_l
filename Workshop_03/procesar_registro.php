<?php

require 'conexion.php';

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro_form.php');
    exit();
}

// Captura de datos
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
// El campo del formulario probablemente se llama 'id_provincia' como en el HTML, no 'provincia'.
$id_provincia = (int)($_POST['id_provincia'] ?? 0); 
$usuario = trim($_POST['usuario'] ?? '');
$contrasena_plana = $_POST['contrasena'] ?? '';

// Validación básica
if (empty($nombre) || empty($apellidos) || $id_provincia === 0 || empty($usuario) || empty($contrasena_plana)) {
    $error_msg = "Todos los campos son obligatorios.";
    header('Location: registro_form.php?error=' . urlencode($error_msg));
    exit();
}

try {
    $sql = "INSERT INTO usuarios (nombre, apellidos, id_provincia, usuario, contrasena) 
            VALUES (:nombre, :apellidos, :id_provincia, :usuario, :contrasena)";
    $stmt = $pdo->prepare($sql); 
    $stmt->execute([
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        ':id_provincia' => $id_provincia,
        ':usuario' => $usuario,
        ':contrasena' => password_hash($contrasena_plana, PASSWORD_DEFAULT) 
    ]);
    $usuario_registrado = urlencode($usuario); 
    header("Location: login.php?username={$usuario_registrado}");
    exit();

} catch (PDOException $e) {
    $error_msg = "Error al registrar: " . $e->getMessage();
    header('Location: registro_form.php?error=' . urlencode($error_msg));
    exit();
}

