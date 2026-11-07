<?php
// Protege las páginas del administrador (se incluye al inicio de cada una).
// Requiere que la página haya definido antes la variable $ruta.

if (!isset($_SESSION["usuario_id"])) {
    // No ha iniciado sesión
    header("Location: " . $ruta . "login.php");
    exit;
}

if ($_SESSION["rol"] != "administrador") {
    // Es un cliente: lo regresamos a su propio portal
    $_SESSION["error"] = "No tiene permiso para entrar a esa sección.";
    header("Location: " . $ruta . "cliente/index.php");
    exit;
}
