<?php
//metodo de insertar
include("conexion.php");

$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$año = $_POST['año'];
$tipo = $_POST['tipo'];
$placa = $_POST['placa'];

$sql = "INSERT INTO vehiculos (marca, modelo, año, tipo, placa)
        VALUES ('$marca', '$modelo', $año, '$tipo', '$placa')";

if ($conn->query($sql) === TRUE) {
    header("Location: principal.php");
} else {
    echo "Error al insertar: " . $conn->error;
}

$conn->close();
?>
