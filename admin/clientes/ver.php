<?php
// Clientes — ficha con su historial de membresías, pagos y rutinas
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$stmt = $pdo->prepare("SELECT c.id, u.nombre, u.email, c.cedula, c.telefono, c.fecha_nacimiento, c.objetivo
                       FROM clientes c JOIN usuarios u ON u.id = c.usuario_id WHERE c.id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();
if (!$cliente) {
    $_SESSION["error"] = "El cliente no existe.";
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT m.id, m.fecha_inicio, m.fecha_fin, m.renovacion_de, t.nombre AS plan
                       FROM membresias m JOIN tipos_membresia t ON t.id = m.tipo_membresia_id
                       WHERE m.cliente_id = ? ORDER BY m.fecha_inicio DESC");
$stmt->execute([$id]);
$membresias = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT p.monto, p.metodo, p.fecha_pago, p.referencia
                       FROM pagos p JOIN membresias m ON m.id = p.membresia_id
                       WHERE m.cliente_id = ? ORDER BY p.fecha_pago DESC, p.id DESC");
$stmt->execute([$id]);
$pagos = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT id, nombre, objetivo, fecha_asignacion FROM rutinas WHERE cliente_id = ? ORDER BY fecha_asignacion DESC");
$stmt->execute([$id]);
$rutinas = $stmt->fetchAll();

// Estado del cliente según su última membresía
$ultimaFin = null;
foreach ($membresias as $m) {
    if ($ultimaFin == null || $m["fecha_fin"] > $ultimaFin) {
        $ultimaFin = $m["fecha_fin"];
    }
}
$estado = estadoCliente($ultimaFin);

$tituloPagina = "Ficha del cliente";
$menuActivo = "clientes";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina"><?php echo htmlspecialchars($cliente["nombre"]); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($cliente["email"]); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="../membresias/formulario.php?cliente_id=<?php echo $id; ?>" class="btn btn-primary">Registrar / renovar membresía</a>
        <a href="formulario.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">Editar</a>
        <a href="index.php" class="btn btn-outline-secondary">Volver</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="tarjeta h-100">
            <div class="tarjeta-encabezado">Datos personales</div>
            <div class="tarjeta-cuerpo">
                <div class="mb-2"><span class="etiqueta-dato">Cédula</span><div><?php echo htmlspecialchars($cliente["cedula"]); ?></div></div>
                <div class="mb-2"><span class="etiqueta-dato">Teléfono</span><div><?php echo htmlspecialchars($cliente["telefono"]); ?></div></div>
                <div class="mb-2"><span class="etiqueta-dato">Nacimiento</span><div><?php echo fecha($cliente["fecha_nacimiento"]); ?></div></div>
                <div class="mb-2"><span class="etiqueta-dato">Objetivo</span><div><?php echo htmlspecialchars($cliente["objetivo"]); ?></div></div>
                <div><span class="etiqueta-dato">Estado</span>
                    <div><span class="badge <?php echo claseEstado($estado); ?>"><?php echo $estado; ?></span>
                        <?php if ($ultimaFin != null) { ?> <small class="text-muted">hasta <?php echo fecha($ultimaFin); ?></small><?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="tarjeta h-100">
            <div class="tarjeta-encabezado">Historial de membresías</div>
            <table class="table align-middle">
                <thead><tr><th>Plan</th><th>Inicio</th><th>Último día</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    <?php if (count($membresias) == 0) { ?>
                        <tr><td colspan="5" class="text-muted">Sin membresías registradas.</td></tr>
                    <?php } ?>
                    <?php foreach ($membresias as $m) {
                        $e = estadoMembresia($m["fecha_inicio"], $m["fecha_fin"]);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m["plan"]); ?></td>
                            <td><?php echo fecha($m["fecha_inicio"]); ?></td>
                            <td><?php echo fecha($m["fecha_fin"]); ?></td>
                            <td><span class="badge <?php echo claseEstado($e); ?>"><?php echo $e; ?></span></td>
                            <td class="text-muted small"><?php echo $m["renovacion_de"] != null ? "Renovación" : ""; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="tarjeta">
            <div class="tarjeta-encabezado">Pagos</div>
            <table class="table align-middle">
                <thead><tr><th>Fecha</th><th>Método</th><th>Referencia</th><th class="text-end">Monto</th></tr></thead>
                <tbody>
                    <?php if (count($pagos) == 0) { ?>
                        <tr><td colspan="4" class="text-muted">Sin pagos.</td></tr>
                    <?php } ?>
                    <?php foreach ($pagos as $p) { ?>
                        <tr>
                            <td><?php echo fecha($p["fecha_pago"]); ?></td>
                            <td><?php echo isset($metodosPago[$p["metodo"]]) ? $metodosPago[$p["metodo"]] : htmlspecialchars($p["metodo"]); ?></td>
                            <td><?php echo htmlspecialchars($p["referencia"]); ?></td>
                            <td class="text-end fw-semibold"><?php echo colones($p["monto"]); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="tarjeta">
            <div class="tarjeta-encabezado">
                <span>Rutinas</span>
                <a href="../rutinas/formulario.php?cliente_id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">Asignar rutina</a>
            </div>
            <table class="table align-middle">
                <thead><tr><th>Rutina</th><th>Asignada</th><th></th></tr></thead>
                <tbody>
                    <?php if (count($rutinas) == 0) { ?>
                        <tr><td colspan="3" class="text-muted">Sin rutinas asignadas.</td></tr>
                    <?php } ?>
                    <?php foreach ($rutinas as $r) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r["nombre"]); ?></td>
                            <td><?php echo fecha($r["fecha_asignacion"]); ?></td>
                            <td class="text-end"><a href="../rutinas/ver.php?id=<?php echo (int) $r["id"]; ?>" class="btn btn-outline-secondary btn-sm">Ver</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
