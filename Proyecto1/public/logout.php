<?php
// Inicia la sesión
session_start();

// Destruye todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borra también la cookie de sesión.

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruye la sesión
session_destroy();

// Redirige al usuario a la página de inicio (index.php)
// Usando la ruta absoluta para mayor seguridad
header("Location: /Proyecto1/index.php");
exit;
?>