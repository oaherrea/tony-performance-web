<?php
// Ejercicios — eliminar (solo POST)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = (int) $_POST["id"];

    // Un ejercicio que ya está en alguna rutina no se puede borrar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rutina_ejercicios WHERE ejercicio_id = ?");
    $stmt->execute([$id]);
    $usados = $stmt->fetchColumn();

    if ($usados > 0) {
        $_SESSION["error"] = "No se puede eliminar: el ejercicio se usa en $usados línea(s) de rutinas.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM ejercicios WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION["mensaje"] = "Ejercicio eliminado.";
    }
}

header("Location: index.php");
exit;
