<?php
// Archivo: conexion.php
$host = 'localhost'; 
$db   = 'workshop03';
$user = 'root'; 
$pass = ''; 
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
    // Si la conexión falla, detenemos el script con un error claro.
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
// ¡Sin etiqueta de cierre ?>!