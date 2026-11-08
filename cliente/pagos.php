<?php
// Portal del cliente — sus pagos
$ruta = "../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloCliente.php";
require $ruta . "includes/funciones.php";

$stmt = $pdo->prepare("SELECT p.monto, p.metodo, p.fecha_pago, p.referencia, t.nombre AS plan
                       FROM pagos p
                       JOIN membresias m ON m.id = p.membresia_id
                       JOIN tipos_membresia t ON t.id = m.tipo_membresia_id
                       WHERE m.cliente_id = ?
                       ORDER BY p.fecha_pago DESC, p.id DESC");
$stmt->execute([$clienteId]);
$pagos = $stmt->fetchAll();

$total = 0;
foreach ($pagos as $p) {
    $total += $p["monto"];
}

$tituloPagina = "Mis pagos";
$menuActivo = "pagos";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina">Mis pagos</h1>
    <p class="text-muted mb-0">Total pagado: <strong><?php echo colones($total); ?></strong></p>
</div>

<div class="tarjeta">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Fecha</th><th>Plan</th><th>Método</th><th>Comprobante</th><th class="text-end">Monto</th></tr></thead>
            <tbody>
                <?php if (count($pagos) == 0) { ?>
                    <tr><td colspan="5" class="text-muted">Todavía no tiene pagos registrados.</td></tr>
                <?php } ?>
                <?php foreach ($pagos as $p) { ?>
                    <tr>
                        <td><?php echo fecha($p["fecha_pago"]); ?></td>
                        <td><?php echo htmlspecialchars($p["plan"]); ?></td>
                        <td><?php echo isset($metodosPago[$p["metodo"]]) ? $metodosPago[$p["metodo"]] : htmlspecialchars($p["metodo"]); ?></td>
                        <td><?php echo htmlspecialchars($p["referencia"]); ?></td>
                        <td class="text-end fw-bold"><?php echo colones($p["monto"]); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
