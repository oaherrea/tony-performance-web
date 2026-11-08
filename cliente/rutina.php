<?php
// Portal del cliente — detalle de UNA de sus rutinas
$ruta = "../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloCliente.php";
require $ruta . "includes/funciones.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

// La condición "AND cliente_id = ?" evita que un cliente vea la rutina de otro
// aunque cambie el número en la dirección (?id=...)
$stmt = $pdo->prepare("SELECT id, nombre, objetivo, fecha_asignacion FROM rutinas WHERE id = ? AND cliente_id = ?");
$stmt->execute([$id, $clienteId]);
$rutina = $stmt->fetch();

if (!$rutina) {
    http_response_code(404);
    $tituloPagina = "Rutina no encontrada";
    $menuActivo = "rutinas";
    include $ruta . "includes/header.php";
    ?>
    <div class="alert alert-warning">La rutina no existe o no es suya.</div>
    <a href="rutinas.php" class="btn btn-primary">Volver a mis rutinas</a>
    <?php
    include $ruta . "includes/footer.php";
    exit;
}

$stmt = $pdo->prepare("SELECT re.dia, re.series, re.repeticiones, re.notas, e.nombre AS ejercicio, e.grupo_muscular
                       FROM rutina_ejercicios re
                       JOIN ejercicios e ON e.id = re.ejercicio_id
                       WHERE re.rutina_id = ?
                       ORDER BY re.id");
$stmt->execute([$rutina["id"]]);
$detalle = $stmt->fetchAll();

$tituloPagina = "Mi rutina";
$menuActivo = "rutinas";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina"><?php echo htmlspecialchars($rutina["nombre"]); ?></h1>
        <p class="text-muted mb-0">
            Asignada el <?php echo fecha($rutina["fecha_asignacion"]); ?>
            <?php if ($rutina["objetivo"] != "") { ?> · Objetivo: <?php echo htmlspecialchars($rutina["objetivo"]); ?><?php } ?>
        </p>
    </div>
    <a href="rutinas.php" class="btn btn-outline-secondary">Volver</a>
</div>

<?php include $ruta . "includes/detalleRutina.php"; ?>

<?php include $ruta . "includes/footer.php"; ?>
