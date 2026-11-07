<?php
// Funciones de apoyo: formularios, fechas, estados de membresía y formatos

// ---- Listas fijas usadas en varios formularios --------------------------------
$metodosPago = [
    "efectivo"      => "Efectivo",
    "tarjeta"       => "Tarjeta (datáfono)",
    "sinpe"         => "SINPE Móvil",
    "transferencia" => "Transferencia"
];
$diasSemana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
$gruposMusculares = ["Pecho", "Espalda", "Pierna", "Hombro", "Brazo", "Core", "Cardio"];

// ---- Leer datos de formularios -------------------------------------------------
function valorPost($campo) {
    return isset($_POST[$campo]) ? trim($_POST[$campo]) : "";
}

function valorGet($campo) {
    return isset($_GET[$campo]) ? trim($_GET[$campo]) : "";
}

// ---- Navegación -----------------------------------------------------------------
// Página de inicio según el rol (ruta desde la raíz del proyecto)
function paginaInicio($rol) {
    if ($rol == "administrador") {
        return "admin/index.php";
    }
    return "cliente/index.php";
}

// ---- Reglas de vencimiento de las membresías -----------------------------------
// Último día con acceso = fecha de inicio + (duración - 1) días
function calcularFechaFin($fechaInicio, $duracionDias) {
    return date("Y-m-d", strtotime($fechaInicio . " +" . ($duracionDias - 1) . " days"));
}

// Días que faltan para el último día de acceso (negativo si ya venció)
function diasRestantes($fechaFin) {
    $hoy = strtotime(date("Y-m-d"));
    return (int) round((strtotime($fechaFin) - $hoy) / 86400);
}

// Estado de UNA membresía: programada, activa o vencida
function estadoMembresia($fechaInicio, $fechaFin) {
    $hoy = date("Y-m-d");
    if ($hoy < $fechaInicio) {
        return "programada";
    }
    if ($hoy > $fechaFin) {
        return "vencida";
    }
    return "activa";
}

// Estado de un CLIENTE según el último día de acceso que tiene pagado
// (null = nunca ha tenido membresía). "Por vencer" = quedan 7 días o menos.
function estadoCliente($ultimaFin) {
    if ($ultimaFin == null) {
        return "sin membresía";
    }
    $dias = diasRestantes($ultimaFin);
    if ($dias < 0) {
        return "vencida";
    }
    if ($dias <= 7) {
        return "por vencer";
    }
    return "activa";
}

// Color de la insignia (badge de Bootstrap) para cada estado
function claseEstado($estado) {
    if ($estado == "activa") {
        return "text-bg-success";
    }
    if ($estado == "por vencer") {
        return "text-bg-warning";
    }
    if ($estado == "vencida") {
        return "text-bg-danger";
    }
    if ($estado == "programada") {
        return "text-bg-info";
    }
    return "text-bg-secondary";
}

// ---- Formatos --------------------------------------------------------------------
function fecha($fecha) {
    if ($fecha == null || $fecha == "") {
        return "—";
    }
    return date("d/m/Y", strtotime($fecha));
}

function colones($monto) {
    return "₡" . number_format($monto, 0, ",", ".");
}

// ---- Validaciones ----------------------------------------------------------------
function fechaValida($texto) {
    $f = DateTime::createFromFormat("Y-m-d", $texto);
    return $f && $f->format("Y-m-d") == $texto;
}
