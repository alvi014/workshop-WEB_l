<?php
//metodo de eliminar
include("conexion.php");

$id = $_GET['id'];
$sql = "DELETE FROM vehiculos WHERE id_vehiculo=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: principal.php");
} else {
    echo "Error al eliminar: " . $conn->error;
}

$conn->close();
?>
