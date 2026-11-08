<?php
// Membresías — listado con su estado (se calcula con las fechas)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$buscar = valorGet("q");
$filtro = valorGet("estado");   // activa, por vencer, vencida, programada o vacío (todas)

$sql = "SELECT m.id, m.cliente_id, m.fecha_inicio, m.fecha_fin, m.renovacion_de,
               u.nombre, c.cedula, t.nombre AS plan
        FROM membresias m
        JOIN clientes c ON c.id = m.cliente_id
        JOIN usuarios u ON u.id = c.usuario_id
        JOIN tipos_membresia t ON t.id = m.tipo_membresia_id";
$parametros = [];
if ($buscar != "") {
    $sql .= " WHERE u.nombre LIKE ? OR c.cedula LIKE ?";
    $parametros = ["%$buscar%", "%$buscar%"];
}
$sql .= " ORDER BY m.fecha_fin DESC, m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$todas = $stmt->fetchAll();

// El estado no se guarda en la BD: se calcula con las fechas cada vez que se muestra
$membresias = [];
$conteo = ["activa" => 0, "por vencer" => 0, "vencida" => 0, "programada" => 0];
foreach ($todas as $m) {
    $estado = estadoMembresia($m["fecha_inicio"], $m["fecha_fin"]);
    if ($estado == "activa" && diasRestantes($m["fecha_fin"]) <= 7) {
        $estado = "por vencer";
    }
    $m["estado"] = $estado;
    $conteo[$estado]++;
    if ($filtro == "" || $filtro == $estado) {
        $membresias[] = $m;
    }
}

$tituloPagina = "Membresías";
$menuActivo = "membresias";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina">Membresías</h1>
        <p class="text-muted mb-0">Cada renovación es una membresía nueva enlazada a la anterior.</p>
    </div>
    <a href="formulario.php" class="btn btn-primary">Registrar membresía y pago</a>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link <?php echo $filtro == "" ? "active" : ""; ?>" href="index.php">Todas</a></li>
    <?php foreach ($conteo as $nombreEstado => $cantidad) { ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $filtro == $nombreEstado ? "active" : ""; ?>"
                href="index.php?estado=<?php echo urlencode($nombreEstado); ?>"><?php echo ucfirst($nombreEstado); ?> (<?php echo $cantidad; ?>)</a>
        </li>
    <?php } ?>
</ul>

<form method="GET" class="row g-2 mb-3">
    <?php if ($filtro != "") { ?><input type="hidden" name="estado" value="<?php echo htmlspecialchars($filtro); ?>"><?php } ?>
    <div class="col-sm-8 col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Buscar por cliente o cédula" value="<?php echo htmlspecialchars($buscar); ?>">
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-outline-primary">Buscar</button></div>
</form>

<div class="tarjeta">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Cliente</th><th>Plan</th><th>Inicio</th><th>Último día</th><th>Estado</th><th class="text-end">Acción</th></tr>
            </thead>
            <tbody>
                <?php if (count($membresias) == 0) { ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay membresías con ese filtro.</td></tr>
                <?php } ?>
                <?php foreach ($membresias as $m) { ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($m["nombre"]); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($m["cedula"]); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($m["plan"]); ?>
                            <?php if ($m["renovacion_de"] != null) { ?><small class="text-muted d-block">Renovación</small><?php } ?></td>
                        <td><?php echo fecha($m["fecha_inicio"]); ?></td>
                        <td><?php echo fecha($m["fecha_fin"]); ?></td>
                        <td>
                            <span class="badge <?php echo claseEstado($m["estado"]); ?>"><?php echo $m["estado"]; ?></span>
                            <?php if ($m["estado"] == "activa" || $m["estado"] == "por vencer") { ?>
                                <small class="text-muted d-block"><?php echo diasRestantes($m["fecha_fin"]); ?> día(s) restantes</small>
                            <?php } ?>
                        </td>
                        <td class="text-end">
                            <a href="formulario.php?cliente_id=<?php echo (int) $m["cliente_id"]; ?>" class="btn btn-outline-primary btn-sm">Renovar</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
