<?php
// Incluir la conexión a la BD para OBTENER las provincias
require 'conexion.php';

$mensaje = '';

// Lógica de LECTURA: Leer provincias de la BD 
$provincias = [];
try {
   $stmt = $pdo->query('SELECT id_provincia AS id, nombreProvincia FROM provincia ORDER BY nombreProvincia');
    $provincias = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = 'Error al cargar las provincias: ' . $e->getMessage();
}

// Lógica para mostrar mensajes de error/éxito
if (isset($_GET['error'])) {
    $mensaje = htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario (Formulario)</title>
</head>
<body>
    <h1>Formulario de Registro</h1>
    <?php if ($mensaje): ?>
        <p style="color: red;"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="POST" action="procesar_registro.php"> 
        <p>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </p>
        <p>
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required>
        </p>
        
        <p>
            <label for="id_provincia">Provincia:</label>
            <select id="id_provincia" name="id_provincia" required>
                <option value="">Seleccione una provincia</option>
                <?php foreach ($provincias as $provincia): ?>
                    <option value="<?= htmlspecialchars($provincia['id']) ?>">
                        <?= htmlspecialchars($provincia['nombreProvincia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>
        </p>
        <p>
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </p>
        
        <p>
            <button type="submit">Registrar</button>
        </p>
    </form>
</body>
</html>