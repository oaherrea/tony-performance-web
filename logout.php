<?php
// Cierra la sesión y regresa al inicio de sesión
require "config.php";

$_SESSION = [];          // vacía los datos guardados
session_destroy();       // destruye la sesión

header("Location: index.php");
exit;
