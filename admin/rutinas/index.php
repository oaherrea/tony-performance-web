<?php
// Rutinas — listado de todas las rutinas asignadas
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$buscar = valorGet("q");

$sql = "SELECT r.id, r.nombre, r.objetivo, r.fecha_asignacion, u.nombre AS cliente, COUNT(re.id) AS ejercicios
        FROM rutinas r
        JOIN clientes c ON c.id = r.cliente_id
        JOIN usuarios u ON u.id = c.usuario_id
        LEFT JOIN rutina_ejercicios re ON re.rutina_id = r.id";
$parametros = [];
if ($buscar != "") {
    $sql .= " WHERE u.nombre LIKE ? OR r.nombre LIKE ?";
    $parametros = ["%$buscar%", "%$buscar%"];
}
$sql .= " GROUP BY r.id, r.nombre, r.objetivo, r.fecha_asignacion, u.nombre ORDER BY r.fecha_asignacion DESC, r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$rutinas = $stmt->fetchAll();

$tituloPagina = "Rutinas";
$menuActivo = "rutinas";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina">Rutinas</h1>
        <p class="text-muted mb-0">Planes de entrenamiento asignados a los clientes.</p>
    </div>
    <a href="formulario.php" class="btn btn-primary">Asignar rutina</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-sm-8 col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Buscar por cliente o rutina" value="<?php echo htmlspecialchars($buscar); ?>">
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-outline-primary">Buscar</button></div>
</form>

<div class="tarjeta">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Rutina</th><th>Cliente</th><th>Objetivo</th><th>Asignada</th><th>Ejercicios</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                <?php if (count($rutinas) == 0) { ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay rutinas.</td></tr>
                <?php } ?>
                <?php foreach ($rutinas as $r) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo htmlspecialchars($r["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($r["cliente"]); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($r["objetivo"]); ?></td>
                        <td><?php echo fecha($r["fecha_asignacion"]); ?></td>
                        <td><?php echo (int) $r["ejercicios"]; ?></td>
                        <td class="text-end text-nowrap">
                            <a href="ver.php?id=<?php echo (int) $r["id"]; ?>" class="btn btn-outline-secondary btn-sm">Ver</a>
                            <form action="eliminar.php" method="POST" class="d-inline form-eliminar" data-mensaje="¿Eliminar esta rutina?">
                                <input type="hidden" name="id" value="<?php echo (int) $r["id"]; ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
