<?php
// Archivo: login.php
// Incluimos el controlador/lógica (Debe ser lo primero)
include 'login_controller.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <h2 class="text-center mb-4">Acceso Administrativo</h2>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="validar_login.php"> 
            <div class="mb-3">
                <label for="username" class="form-label">Usuario:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
    </div>
</div>

</body>
</html>