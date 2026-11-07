<?php
// Protege el portal del cliente (se incluye al inicio de cada página de /cliente).
// IMPORTANTE: el id del cliente SIEMPRE se toma de la sesión, nunca de la URL ni
// de un formulario, así un cliente no puede ver datos de otro.

if (!isset($_SESSION["usuario_id"])) {
    header("Location: " . $ruta . "login.php");
    exit;
}

if ($_SESSION["rol"] != "cliente" || !isset($_SESSION["cliente_id"])) {
    $_SESSION["error"] = "Esta sección es solo para clientes.";
    header("Location: " . $ruta . "admin/index.php");
    exit;
}

$clienteId = (int) $_SESSION["cliente_id"];
