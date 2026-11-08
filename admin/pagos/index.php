<?php
// Pagos — historial con filtros (los pagos se registran junto con la membresía)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$buscar = valorGet("q");
$metodo = valorGet("metodo");
$mes    = valorGet("mes");      // formato AAAA-MM

$sql = "SELECT p.id, p.monto, p.metodo, p.fecha_pago, p.referencia, u.nombre, t.nombre AS plan, admin.nombre AS cobrado_por
        FROM pagos p
        JOIN membresias m ON m.id = p.membresia_id
        JOIN clientes c ON c.id = m.cliente_id
        JOIN usuarios u ON u.id = c.usuario_id
        JOIN tipos_membresia t ON t.id = m.tipo_membresia_id
        LEFT JOIN usuarios admin ON admin.id = p.registrado_por
        WHERE 1 = 1";
$parametros = [];
if ($buscar != "") {
    $sql .= " AND (u.nombre LIKE ? OR p.referencia LIKE ?)";
    $parametros[] = "%$buscar%";
    $parametros[] = "%$buscar%";
}
if ($metodo != "" && isset($metodosPago[$metodo])) {
    $sql .= " AND p.metodo = ?";
    $parametros[] = $metodo;
}
if ($mes != "" && preg_match("/^\d{4}-\d{2}$/", $mes)) {
    $sql .= " AND DATE_FORMAT(p.fecha_pago, '%Y-%m') = ?";
    $parametros[] = $mes;
}
$sql .= " ORDER BY p.fecha_pago DESC, p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$pagos = $stmt->fetchAll();

$total = 0;
foreach ($pagos as $p) {
    $total += $p["monto"];
}

$tituloPagina = "Pagos";
$menuActivo = "pagos";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina">Pagos</h1>
        <p class="text-muted mb-0"><?php echo count($pagos); ?> pago(s) · Total: <strong><?php echo colones($total); ?></strong></p>
    </div>
    <a href="../membresias/formulario.php" class="btn btn-primary">Registrar membresía y pago</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="q" class="form-control" placeholder="Buscar por cliente o comprobante" value="<?php echo htmlspecialchars($buscar); ?>">
    </div>
    <div class="col-sm-6 col-md-3">
        <select name="metodo" class="form-select">
            <option value="">Todos los métodos</option>
            <?php foreach ($metodosPago as $clave => $texto) { ?>
                <option value="<?php echo $clave; ?>" <?php echo $metodo == $clave ? "selected" : ""; ?>><?php echo $texto; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-sm-6 col-md-3">
        <input type="month" name="mes" class="form-control" value="<?php echo htmlspecialchars($mes); ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <?php if ($buscar != "" || $metodo != "" || $mes != "") { ?><a href="index.php" class="btn btn-link">Limpiar</a><?php } ?>
    </div>
</form>

<div class="tarjeta">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Fecha</th><th>Cliente</th><th>Plan</th><th>Método</th><th>Comprobante</th><th>Cobró</th><th class="text-end">Monto</th></tr>
            </thead>
            <tbody>
                <?php if (count($pagos) == 0) { ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay pagos con ese filtro.</td></tr>
                <?php } ?>
                <?php foreach ($pagos as $p) { ?>
                    <tr>
                        <td><?php echo fecha($p["fecha_pago"]); ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($p["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($p["plan"]); ?></td>
                        <td><?php echo isset($metodosPago[$p["metodo"]]) ? $metodosPago[$p["metodo"]] : htmlspecialchars($p["metodo"]); ?></td>
                        <td><?php echo htmlspecialchars($p["referencia"]); ?></td>
                        <td class="text-muted small"><?php echo htmlspecialchars($p["cobrado_por"]); ?></td>
                        <td class="text-end fw-bold"><?php echo colones($p["monto"]); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
