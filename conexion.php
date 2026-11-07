<?php
// Conexión a MySQL con PDO (cambie los datos si su MySQL usa otra clave)
$host       = "localhost";
$base       = "tony_performance_web";
$usuario    = "root";
$contrasena = "";

$dsn = "mysql:host=$host;dbname=$base;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $usuario, $contrasena, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $error) {
    http_response_code(500);
    exit("No se pudo conectar a la base de datos. Verifique que MySQL esté iniciado y que haya importado database.sql");
}
