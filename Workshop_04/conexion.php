<?php
// Archivo: conexion.php

$host = 'localhost'; 
$db   = 'workshop04'; // VERIFICA ESTE NOMBRE
$user = 'root'; 
$pass = ''; // Tu contraseña de MySQL/XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    PDO::ATTR_EMULATE_PREPARES   => false, 
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si la conexión falla, muere aquí y muestra el error
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
// ¡No debe tener etiqueta de cierre ?>