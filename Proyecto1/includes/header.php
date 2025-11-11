<?php

// NOTA: Para que $_SESSION funcione, el archivo principal (index.php) debe llamar a session_start() primero.

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina ?? 'Aventones - Tu Viaje Compartido'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="/css/style.css"> 
    
    <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
</head>
<body>

    <header class="main-header">
        <div class="logo">
            <a href="/index.php">
                <span class="logo-text">Aventones</span>
                </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <li><a href="/index.php">Buscar Rides</a></li>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'chofer'): ?>
                        <li><a href="/views/chofer/chofer_panel.php">Panel de Chofer</a></li>
                    <?php elseif (isset($_SESSION['rol']) && $_SESSION['rol'] === 'pasajero'): ?>
                        <li><a href="/views/pasajero/pasajero_panel.php">Mi Cuenta</a></li>
                    <?php endif; ?>
                    <li><a href="/public/logout.php" class="btn btn-logout">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="/public/login.php">Iniciar Sesión</a></li>
                    
                    <li><a href="/public/registro_pasajero.php" class="btn btn-registro">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main-content-container container mt-4 mb-5">