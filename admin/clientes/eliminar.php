<?php
// Clientes — eliminar (solo se acepta por POST, desde el botón de la lista)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = (int) $_POST["id"];

    $stmt = $pdo->prepare("SELECT usuario_id FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        // Al borrar el usuario, la BD borra en cascada (ON DELETE CASCADE)
        // el cliente, sus membresías, pagos y rutinas.
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$cliente["usuario_id"]]);
        $_SESSION["mensaje"] = "Cliente eliminado.";
    } else {
        $_SESSION["error"] = "El cliente no existe.";
    }
}

header("Location: index.php");
exit;
