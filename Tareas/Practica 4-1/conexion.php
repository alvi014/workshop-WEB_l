<?php
//COnexion base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "workshop03";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
