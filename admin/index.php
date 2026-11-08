<?php
// Dashboard del administrador
$ruta = "../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

// Todos los clientes con el último día de acceso que tienen pagado
$clientes = $pdo->query("SELECT c.id, u.nombre, MAX(m.fecha_fin) AS ultima_fin
                         FROM clientes c
                         JOIN usuarios u ON u.id = c.usuario_id
                         LEFT JOIN membresias m ON m.cliente_id = c.id
                         GROUP BY c.id, u.nombre
                         ORDER BY u.nombre")->fetchAll();

$totalClientes = count($clientes);
$vigentes = 0;
$porVencer = [];
$vencidas = [];
foreach ($clientes as $c) {
    $estado = estadoCliente($c["ultima_fin"]);
    if ($estado == "activa") {
        $vigentes++;
    } elseif ($estado == "por vencer") {
        $vigentes++;
        $porVencer[] = $c;
    } elseif ($estado == "vencida") {
        $vencidas[] = $c;
    }
}

// Ingresos del mes actual
$ingresoMes = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM pagos
                           WHERE fecha_pago >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn();

// Ingresos de los últimos 6 meses
$meses = $pdo->query("SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS mes, SUM(monto) AS total
                      FROM pagos
                      WHERE fecha_pago >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
                      GROUP BY mes
                      ORDER BY mes")->fetchAll();
$maximo = 1;
foreach ($meses as $m) {
    if ($m["total"] > $maximo) {
        $maximo = $m["total"];
    }
}

// Últimos 5 pagos
$ultimosPagos = $pdo->query("SELECT p.monto, p.metodo, p.fecha_pago, u.nombre, t.nombre AS plan
                             FROM pagos p
                             JOIN membresias m ON m.id = p.membresia_id
                             JOIN clientes c ON c.id = m.cliente_id
                             JOIN usuarios u ON u.id = c.usuario_id
                             JOIN tipos_membresia t ON t.id = m.tipo_membresia_id
                             ORDER BY p.fecha_pago DESC, p.id DESC
                             LIMIT 5")->fetchAll();

$tituloPagina = "Dashboard";
$menuActivo = "dashboard";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina">Dashboard</h1>
    <p class="text-muted mb-0">Resumen del gimnasio al <?php echo date("d/m/Y"); ?></p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="tarjeta indicador borde-naranja">
            <div><div class="numero"><?php echo $totalClientes; ?></div><div class="text-muted small">Clientes registrados</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="tarjeta indicador borde-verde">
            <div><div class="numero"><?php echo $vigentes; ?></div><div class="text-muted small">Con membresía vigente</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="tarjeta indicador borde-amarillo">
            <div><div class="numero"><?php echo count($porVencer); ?></div><div class="text-muted small">Por vencer (7 días)</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="tarjeta indicador borde-rojo">
            <div><div class="numero"><?php echo count($vencidas); ?></div><div class="text-muted small">Con membresía vencida</div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="tarjeta h-100">
            <div class="tarjeta-encabezado">
                <span>Ingresos de los últimos 6 meses</span>
                <span class="badge text-bg-light">Este mes: <?php echo colones($ingresoMes); ?></span>
            </div>
            <div class="tarjeta-cuerpo">
                <?php if (count($meses) == 0) { ?>
                    <p class="text-muted mb-0">Todavía no hay pagos registrados.</p>
                <?php } ?>
                <?php foreach ($meses as $m) { ?>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width: 80px;" class="small text-muted"><?php echo date("m/Y", strtotime($m["mes"] . "-01")); ?></div>
                        <div class="progress flex-grow-1" style="height: 22px;">
                            <div class="progress-bar" style="width: <?php echo round($m["total"] / $maximo * 100); ?>%; background-color: var(--naranja);"></div>
                        </div>
                        <div style="width: 100px;" class="text-end fw-bold small"><?php echo colones($m["total"]); ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="tarjeta h-100">
            <div class="tarjeta-encabezado">
                <span>Últimos pagos</span>
                <a href="pagos/index.php" class="btn btn-outline-primary btn-sm">Ver todos</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($ultimosPagos as $p) { ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($p["nombre"]); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($p["plan"]); ?> · <?php echo fecha($p["fecha_pago"]); ?></small>
                        </div>
                        <strong><?php echo colones($p["monto"]); ?></strong>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="tarjeta">
            <div class="tarjeta-encabezado"><span>Membresías por vencer</span></div>
            <table class="table align-middle">
                <thead><tr><th>Cliente</th><th>Vence</th><th></th></tr></thead>
                <tbody>
                    <?php if (count($porVencer) == 0) { ?>
                        <tr><td colspan="3" class="text-muted">Ningún cliente por vencer.</td></tr>
                    <?php } ?>
                    <?php foreach ($porVencer as $c) { ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($c["nombre"]); ?></td>
                            <td><?php echo fecha($c["ultima_fin"]); ?>
                                <small class="text-muted d-block">en <?php echo diasRestantes($c["ultima_fin"]); ?> día(s)</small></td>
                            <td class="text-end"><a class="btn btn-primary btn-sm" href="membresias/formulario.php?cliente_id=<?php echo (int) $c["id"]; ?>">Renovar</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="tarjeta">
            <div class="tarjeta-encabezado"><span>Clientes con membresía vencida</span></div>
            <table class="table align-middle">
                <thead><tr><th>Cliente</th><th>Venció</th><th></th></tr></thead>
                <tbody>
                    <?php if (count($vencidas) == 0) { ?>
                        <tr><td colspan="3" class="text-muted">Ningún cliente con membresía vencida.</td></tr>
                    <?php } ?>
                    <?php foreach ($vencidas as $c) { ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($c["nombre"]); ?></td>
                            <td><?php echo fecha($c["ultima_fin"]); ?></td>
                            <td class="text-end"><a class="btn btn-outline-primary btn-sm" href="membresias/formulario.php?cliente_id=<?php echo (int) $c["id"]; ?>">Renovar</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
