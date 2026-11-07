<?php
// Cabecera común de todas las páginas (como en TechBooks).
// Cada página define antes:
//   $ruta         -> cómo llegar a la raíz ("", "../" o "../../")
//   $tituloPagina -> título de la pestaña
//   $menuActivo   -> opción del menú que se resalta
if (!isset($ruta)) {
    $ruta = "";
}
if (!isset($tituloPagina)) {
    $tituloPagina = "Tony Performance Center";
}
if (!isset($menuActivo)) {
    $menuActivo = "";
}

$rolActual = isset($_SESSION["rol"]) ? $_SESSION["rol"] : "";

// Opciones del menú según el rol: clave => [texto, página]
$menu = [];
if ($rolActual == "administrador") {
    $menu = [
        "dashboard"  => ["Dashboard",   "admin/index.php"],
        "clientes"   => ["Clientes",    "admin/clientes/index.php"],
        "membresias" => ["Membresías",  "admin/membresias/index.php"],
        "pagos"      => ["Pagos",       "admin/pagos/index.php"],
        "tipos"      => ["Planes",      "admin/tipos/index.php"],
        "ejercicios" => ["Ejercicios",  "admin/ejercicios/index.php"],
        "rutinas"    => ["Rutinas",     "admin/rutinas/index.php"],
        "usuarios"   => ["Usuarios",    "admin/usuarios/index.php"]
    ];
} elseif ($rolActual == "cliente") {
    $menu = [
        "inicio"     => ["Inicio",       "cliente/index.php"],
        "membresia"  => ["Mi membresía", "cliente/membresia.php"],
        "pagos"      => ["Mis pagos",    "cliente/pagos.php"],
        "rutinas"    => ["Mis rutinas",  "cliente/rutinas.php"]
    ];
} else {
    // Visitante (sin iniciar sesión)
    $menu = [
        "inicio" => ["Inicio", "index.php"],
        "planes" => ["Planes", "index.php#planes"]
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina); ?> | Tony Performance Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $ruta; ?>css/estilos.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark barra-principal">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $ruta . "index.php"; ?>">
                <span class="marca-icono">TONY</span> <span class="marca-sub">PERFORMANCE CENTER</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto ms-lg-4">
                    <?php foreach ($menu as $clave => $opcion) { ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $menuActivo == $clave ? 'active' : ''; ?>"
                                href="<?php echo $ruta . $opcion[1]; ?>"><?php echo $opcion[0]; ?></a>
                        </li>
                    <?php } ?>
                </ul>

                <?php if ($rolActual != "") { ?>
                    <span class="navbar-text ms-lg-3 me-3">
                        <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                        <small class="text-secondary">(<?php echo htmlspecialchars($rolActual); ?>)</small>
                    </span>
                    <a class="btn btn-outline-light btn-sm" href="<?php echo $ruta; ?>logout.php">Salir</a>
                <?php } else { ?>
                    <a class="btn btn-primary btn-sm" href="<?php echo $ruta; ?>login.php">Iniciar sesión</a>
                <?php } ?>
            </div>
        </div>
    </nav>

    <main class="container py-4 flex-grow-1">
        <?php
        // Avisos de una sola vez (se guardan en la sesión antes de redirigir)
        if (isset($_SESSION["mensaje"])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION["mensaje"]); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php
            unset($_SESSION["mensaje"]);
        }
        if (isset($_SESSION["error"])) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION["error"]); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php
            unset($_SESSION["error"]);
        }
        ?>
