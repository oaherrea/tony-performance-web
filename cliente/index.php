<?php
// Portal del cliente — inicio. Todo se consulta con el cliente_id de la SESIÓN.
$ruta = "../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloCliente.php";   // define $clienteId (tomado de la sesión)
require $ruta . "includes/funciones.php";

// Su membresía con el último día de acceso más lejano
$stmt = $pdo->prepare("SELECT m.fecha_inicio, m.fecha_fin, t.nombre AS plan
                       FROM membresias m JOIN tipos_membresia t ON t.id = m.tipo_membresia_id
                       WHERE m.cliente_id = ?
                       ORDER BY m.fecha_fin DESC, m.id DESC LIMIT 1");
$stmt->execute([$clienteId]);
$membresia = $stmt->fetch();

$estado = $membresia ? estadoCliente($membresia["fecha_fin"]) : "sin membresía";

// Su rutina más reciente
$stmt = $pdo->prepare("SELECT id, nombre, objetivo, fecha_asignacion FROM rutinas WHERE cliente_id = ? ORDER BY fecha_asignacion DESC, id DESC LIMIT 1");
$stmt->execute([$clienteId]);
$rutina = $stmt->fetch();

// Sus últimos pagos
$stmt = $pdo->prepare("SELECT p.monto, p.metodo, p.fecha_pago, t.nombre AS plan
                       FROM pagos p
                       JOIN membresias m ON m.id = p.membresia_id
                       JOIN tipos_membresia t ON t.id = m.tipo_membresia_id
                       WHERE m.cliente_id = ?
                       ORDER BY p.fecha_pago DESC, p.id DESC LIMIT 3");
$stmt->execute([$clienteId]);
$pagos = $stmt->fetchAll();

$tituloPagina = "Mi portal";
$menuActivo = "inicio";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina">Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></h1>
    <p class="text-muted mb-0">Aquí puede consultar su membresía, sus pagos y sus rutinas.</p>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="tarjeta h-100">
            <div class="tarjeta-encabezado">Mi membresía</div>
            <div class="tarjeta-cuerpo">
                <?php if (!$membresia) { ?>
                    <p class="text-muted mb-0">Todavía no tiene una membresía registrada. Consulte en recepción.</p>
                <?php } else { ?>
                    <span class="badge fs-6 <?php echo claseEstado($estado); ?>"><?php echo ucfirst($estado); ?></span>
                    <div class="mt-3">
                        <div class="etiqueta-dato">Plan</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($membresia["plan"]); ?></div>
                    </div>
                    <div class="mt-2">
                        <div class="etiqueta-dato">Último día de acceso</div>
                        <div class="fw-semibold"><?php echo fecha($membresia["fecha_fin"]); ?></div>
                    </div>
                    <?php if ($estado == "activa" || $estado == "por vencer") { ?>
                        <div class="mt-2 text-muted small">Le quedan <?php echo diasRestantes($membresia["fecha_fin"]); ?> día(s).</div>
                    <?php } ?>
                    <?php if ($estado == "por vencer" || $estado == "vencida") { ?>
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">Para renovar su membresía acérquese a recepción.</div>
                    <?php } ?>
                    <a href="membresia.php" class="btn btn-outline-primary btn-sm mt-3">Ver historial</a>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="tarjeta mb-3">
            <div class="tarjeta-encabezado">Mi rutina actual</div>
            <div class="tarjeta-cuerpo">
                <?php if (!$rutina) { ?>
                    <p class="text-muted mb-0">Aún no tiene una rutina asignada. Su entrenador se la asignará pronto.</p>
                <?php } else { ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-bold fs-5"><?php echo htmlspecialchars($rutina["nombre"]); ?></div>
                            <div class="text-muted small">Asignada el <?php echo fecha($rutina["fecha_asignacion"]); ?>
                                <?php if ($rutina["objetivo"] != "") { ?> · <?php echo htmlspecialchars($rutina["objetivo"]); ?><?php } ?></div>
                        </div>
                        <a href="rutina.php?id=<?php echo (int) $rutina["id"]; ?>" class="btn btn-primary">Ver rutina</a>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="tarjeta">
            <div class="tarjeta-encabezado">
                <span>Mis últimos pagos</span>
                <a href="pagos.php" class="btn btn-outline-primary btn-sm">Ver todos</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (count($pagos) == 0) { ?>
                    <li class="list-group-item text-muted">Sin pagos registrados.</li>
                <?php } ?>
                <?php foreach ($pagos as $p) { ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?php echo htmlspecialchars($p["plan"]); ?> · <?php echo fecha($p["fecha_pago"]); ?></span>
                        <strong><?php echo colones($p["monto"]); ?></strong>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
