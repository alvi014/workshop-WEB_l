<?php
// Inicializamos la variable que contendrá el valor del campo
$username_value = '';
$mensaje_exito = '';

// 1. Verificamos si la URL contiene el parámetro 'username'
if (isset($_GET['username'])) {
    // 2. Leemos el valor y lo limpiamos para evitar inyecciones HTML (XSS).
    $username_value = htmlspecialchars($_GET['username']);
    
    // Opcional: Mostramos un mensaje de éxito
    $mensaje_exito = '¡Registro exitoso! Ya puedes iniciar sesión con ' . $username_value . '.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
</head>
<body>
    <h1>Iniciar Sesión</h1>
    
    <?php if ($mensaje_exito): ?>
        <p style="color: green; font-weight: bold;"><?= $mensaje_exito ?></p>
    <?php endif; ?>

    <form method="POST" action="validar_login.php"> 
        <p>
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" 
                   value="<?= $username_value ?>" required>
        </p>
        <p>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </p>
        
        <p>
            <button type="submit">Entrar</button>
           
        </p>
    </form>
</body>
</html>