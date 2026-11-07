<?php
// Inicia la sesión antes de mostrar el HTML (igual que en TechBooks)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Zona horaria del gimnasio (las fechas de las membresías dependen de "hoy")
date_default_timezone_set("America/Costa_Rica");

// La sesión guarda:
//   $_SESSION["usuario_id"], $_SESSION["nombre"], $_SESSION["rol"]
//   $_SESSION["cliente_id"]  (solo si el rol es "cliente")
//   $_SESSION["mensaje"] / $_SESSION["error"]  (avisos que se muestran una vez)
