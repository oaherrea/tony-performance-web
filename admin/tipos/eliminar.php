<?php
// Planes (tipos de membresía) — eliminar (solo POST)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = (int) $_POST["id"];

    // Un plan que ya se vendió no se puede borrar (se perdería el historial)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM membresias WHERE tipo_membresia_id = ?");
    $stmt->execute([$id]);
    $usadas = $stmt->fetchColumn();

    if ($usadas > 0) {
        $_SESSION["error"] = "No se puede eliminar: el plan tiene $usadas membresía(s) registrada(s).";
    } else {
        $stmt = $pdo->prepare("DELETE FROM tipos_membresia WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION["mensaje"] = "Plan eliminado.";
    }
}

header("Location: index.php");
exit;
