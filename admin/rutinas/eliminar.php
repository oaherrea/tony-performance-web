<?php
// Rutinas — eliminar (solo POST)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = (int) $_POST["id"];

    // Los ejercicios de la rutina se borran solos (ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM rutinas WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION["mensaje"] = "Rutina eliminada.";
}

header("Location: index.php");
exit;
