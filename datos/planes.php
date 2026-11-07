<?php
// Lee los planes de membresía de la base de datos (igual que datos/libros.php en TechBooks).
// Requiere que conexion.php ya haya creado $pdo.
$consulta = $pdo->query("SELECT id, nombre, descripcion, duracion_dias, precio FROM tipos_membresia ORDER BY precio");
$planes = $consulta->fetchAll();
