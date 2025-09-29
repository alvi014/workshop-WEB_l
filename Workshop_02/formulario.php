<?php
// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    // Mostrar datos que se van a registrar
    echo "<h3>Datos registrados:</h3>";
    echo "Nombre: $nombre<br>";
    echo "Apellido: $apellido<br>";
    echo "Correo: $correo<br>";
    echo "Teléfono: $telefono<br>";



     $conn = new mysqli("localhost", "root", "", "Workshop02");

    // conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Insertar datos en la tabla
    $sql = "INSERT INTO usuario (nombre, apellido, correo, telefono) 
            VALUES ('$nombre', '$apellido', '$correo', '$telefono')";

    if ($conn->query($sql) === TRUE) {
        echo "Registro insertado correctamente.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();

}
?>

<!-- Formulario HTML -->
<form method="post" action="">
    <label>Nombre:</label>
    <input type="text" name="nombre" required><br>
    <label>Apellido:</label>
    <input type="text" name="apellido" required><br>
    <label>Correo:</label>
    <input type="email" name="correo" required><br>
    <label>Teléfono:</label>
    <input type="text" name="telefono" required><br>
    <input type="submit" value="Registrar">
</form>