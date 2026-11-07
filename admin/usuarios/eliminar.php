<?php
// Usuarios — eliminar (solo se acepta por POST, desde el botón de la lista)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = (int) $_POST["id"];

    if ($id == $_SESSION["usuario_id"]) {
        // Evita que el administrador se borre a sí mismo y se quede sin acceso
        $_SESSION["error"] = "No puede eliminar su propio usuario.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION["mensaje"] = "Usuario eliminado.";
        } else {
            $_SESSION["error"] = "El usuario no existe.";
        }
    }
}

header("Location: index.php");
exit;
