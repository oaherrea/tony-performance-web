<?php
// Portal del cliente — lista de SUS rutinas
$ruta = "../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloCliente.php";
require $ruta . "includes/funciones.php";

$stmt = $pdo->prepare("SELECT r.id, r.nombre, r.objetivo, r.fecha_asignacion, COUNT(re.id) AS ejercicios
                       FROM rutinas r
                       LEFT JOIN rutina_ejercicios re ON re.rutina_id = r.id
                       WHERE r.cliente_id = ?
                       GROUP BY r.id, r.nombre, r.objetivo, r.fecha_asignacion
                       ORDER BY r.fecha_asignacion DESC, r.id DESC");
$stmt->execute([$clienteId]);
$rutinas = $stmt->fetchAll();

$tituloPagina = "Mis rutinas";
$menuActivo = "rutinas";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina">Mis rutinas</h1>
    <p class="text-muted mb-0">Rutinas de entrenamiento que le asignó su entrenador.</p>
</div>

<?php if (count($rutinas) == 0) { ?>
    <div class="alert alert-info">Todavía no tiene rutinas asignadas.</div>
<?php } ?>

<div class="row g-3">
    <?php foreach ($rutinas as $r) { ?>
        <div class="col-md-6 col-lg-4">
            <div class="tarjeta h-100">
                <div class="tarjeta-cuerpo d-flex flex-column h-100">
                    <h2 class="h5 fw-bold"><?php echo htmlspecialchars($r["nombre"]); ?></h2>
                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($r["objetivo"]); ?></p>
                    <p class="small mb-3">
                        <?php echo fecha($r["fecha_asignacion"]); ?> ·
                        <?php echo (int) $r["ejercicios"]; ?> ejercicios
                    </p>
                    <a href="rutina.php?id=<?php echo (int) $r["id"]; ?>" class="btn btn-primary mt-auto">Ver rutina</a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php include $ruta . "includes/footer.php"; ?>
