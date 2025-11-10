<?php
session_start();

// 1. Elimina todas las variables de sesión
$_SESSION = array();

// 2. Destruye la cookie de sesión (si existe)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruye la sesión
session_destroy();


header("Location: ../index.php");
exit;
?>