<?php
/**
 * Script de Verificación de Sesión y Roles
 * Guardar en: includes/auth_check.php
 * * Este archivo debe ser incluido al inicio de CADA página restringida.
 * Requiere que session_start() se haya llamado previamente.
 */


if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['tipo_usuario'])) {
  
    header("Location: /public/login.php"); 
    exit();
}

/**
 * Función para verificar el tipo de usuario permitido en la página actual.
 * @param string $required_role El rol requerido ('administrador', 'chofer', 'pasajero').
 * @return bool Devuelve true si el rol de la sesión coincide con el requerido.
 */
function check_role_access(string $required_role): bool {
    // Si el rol en sesión no es el requerido, niega el acceso.
    if ($_SESSION['tipo_usuario'] !== $required_role) {
        // Podrías redirigir a una página de "acceso denegado"
        header("Location: /public/acceso_denegado.php"); 
        exit();
    }
    return true;
}


?>