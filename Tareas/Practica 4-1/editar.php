<?php
//metodo de editar
include("conexion.php");

$id = $_POST['id_vehiculo'];
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$año = $_POST['año'];
$tipo = $_POST['tipo'];
$placa = $_POST['placa'];

$sql = "UPDATE vehiculos SET 
        marca='$marca', modelo='$modelo', año=$año, tipo='$tipo', placa='$placa'
        WHERE id_vehiculo=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: principal.php");
} else {
    echo "Error al actualizar: " . $conn->error;
}

$conn->close();
?>
