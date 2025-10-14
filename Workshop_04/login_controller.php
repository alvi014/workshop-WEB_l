<?php
// Archivo: login_controller.php

session_start();

// Redirigir si el usuario ya está autenticado
if (isset($_SESSION['user_id'])) {
    header('Location: panel_admin.php');
    exit();
}

// Inicializar la variable del mensaje de error (para usarla en login.php)
$error_message = '';

// Leer el error de la URL (si existe)
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}