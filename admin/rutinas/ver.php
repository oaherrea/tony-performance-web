<?php
// Rutinas — detalle de una rutina
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$stmt = $pdo->prepare("SELECT r.id, r.nombre, r.objetivo, r.fecha_asignacion, r.cliente_id, u.nombre AS cliente
                       FROM rutinas r
                       JOIN clientes c ON c.id = r.cliente_id
                       JOIN usuarios u ON u.id = c.usuario_id
                       WHERE r.id = ?");
$stmt->execute([$id]);
$rutina = $stmt->fetch();
if (!$rutina) {
    $_SESSION["error"] = "La rutina no existe.";
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT re.dia, re.series, re.repeticiones, re.notas, e.nombre AS ejercicio, e.grupo_muscular
                       FROM rutina_ejercicios re
                       JOIN ejercicios e ON e.id = re.ejercicio_id
                       WHERE re.rutina_id = ?
                       ORDER BY re.id");
$stmt->execute([$id]);
$detalle = $stmt->fetchAll();

$tituloPagina = "Rutina";
$menuActivo = "rutinas";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina"><?php echo htmlspecialchars($rutina["nombre"]); ?></h1>
        <p class="text-muted mb-0">
            Cliente: <a href="../clientes/ver.php?id=<?php echo (int) $rutina["cliente_id"]; ?>"><?php echo htmlspecialchars($rutina["cliente"]); ?></a>
            · Asignada el <?php echo fecha($rutina["fecha_asignacion"]); ?>
            <?php if ($rutina["objetivo"] != "") { ?> · Objetivo: <?php echo htmlspecialchars($rutina["objetivo"]); ?><?php } ?>
        </p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">Volver</a>
</div>

<?php include $ruta . "includes/detalleRutina.php"; ?>

<?php include $ruta . "includes/footer.php"; ?>
