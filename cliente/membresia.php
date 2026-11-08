<?php
// Portal del cliente — historial de sus membresías
$ruta = "../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloCliente.php";
require $ruta . "includes/funciones.php";

$stmt = $pdo->prepare("SELECT m.fecha_inicio, m.fecha_fin, m.renovacion_de, t.nombre AS plan, t.duracion_dias
                       FROM membresias m JOIN tipos_membresia t ON t.id = m.tipo_membresia_id
                       WHERE m.cliente_id = ?
                       ORDER BY m.fecha_inicio DESC, m.id DESC");
$stmt->execute([$clienteId]);
$membresias = $stmt->fetchAll();

$tituloPagina = "Mi membresía";
$menuActivo = "membresia";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina">Mi membresía</h1>
    <p class="text-muted mb-0">Historial de sus membresías y renovaciones.</p>
</div>

<div class="tarjeta mb-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Plan</th><th>Inicio</th><th>Último día</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php if (count($membresias) == 0) { ?>
                    <tr><td colspan="5" class="text-muted">Todavía no tiene membresías registradas.</td></tr>
                <?php } ?>
                <?php foreach ($membresias as $m) {
                    $estado = estadoMembresia($m["fecha_inicio"], $m["fecha_fin"]);
                    if ($estado == "activa" && diasRestantes($m["fecha_fin"]) <= 7) {
                        $estado = "por vencer";
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m["plan"]); ?> <small class="text-muted">(<?php echo (int) $m["duracion_dias"]; ?> días)</small></td>
                        <td><?php echo fecha($m["fecha_inicio"]); ?></td>
                        <td><?php echo fecha($m["fecha_fin"]); ?></td>
                        <td><span class="badge <?php echo claseEstado($estado); ?>"><?php echo $estado; ?></span></td>
                        <td class="text-muted small"><?php echo $m["renovacion_de"] != null ? "Renovación" : ""; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-secondary small">
    <strong>¿Cómo se calcula?</strong> El último día de acceso es el día de inicio más la duración del plan
    (contando el día de inicio). Si renueva antes de vencer, la nueva membresía empieza al día siguiente de que
    termine la actual, así no pierde días. Se considera «por vencer» cuando quedan 7 días o menos.
</div>

<?php include $ruta . "includes/footer.php"; ?>
