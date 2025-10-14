<?php
// Archivo: logout.php (Backend)

session_start();

// 1. Eliminar todas las variables de sesión
$_SESSION = array();

// 2. Destruir la sesión
session_destroy();

// 3. Redirigir al login
header('Location: login.php?status=logout_success');
exit();