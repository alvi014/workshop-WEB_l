<?php
// Archivo: validar_login.php

session_start();
require 'conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$usuario_ingresado = trim($_POST['username'] ?? '');
$contrasena_ingresada = $_POST['password'] ?? '';

if (empty($usuario_ingresado) || empty($contrasena_ingresada)) {
    header('Location: login.php?error=' . urlencode('Faltan usuario o contraseña.'));
    exit();
}

try {
    // Asegúrate de que las columnas coincidan con tu BD
    $sql = "SELECT id_usuario, usuario, contrasena, rol, estado FROM usuarios WHERE usuario = :usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario' => $usuario_ingresado]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Primero verificamos si la cuenta está activa
        if (strtolower($usuario['estado']) !== 'activo' && $usuario['estado'] != 1) {
            header('Location: login.php?error=' . urlencode('Tu cuenta ha sido deshabilitada.'));
            exit();
        }

        // Verificar contraseña cifrada o en texto plano
        $contrasena_valida = password_verify($contrasena_ingresada, $usuario['contrasena']) 
                             || $contrasena_ingresada === $usuario['contrasena'];

        if ($contrasena_valida) {
            // Guardar variables de sesión
            $_SESSION['user_id'] = $usuario['id_usuario'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['usuario'] = $usuario['usuario'];

            // Redirigir según rol
            header('Location: panel_admin.php');
            exit();
        }
    }

    // Si llegó hasta aquí, usuario o contraseña incorrectos
    header('Location: login.php?error=' . urlencode('Usuario o contraseña incorrectos.'));
    exit();

} catch (PDOException $e) {
    header('Location: login.php?error=' . urlencode('Error en el servidor: ' . $e->getMessage()));
    exit();
}
