<?php
// Clientes — listado con búsqueda
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$buscar = valorGet("q");

// Un cliente con su último día de acceso pagado (para mostrar el estado)
$sql = "SELECT c.id, u.nombre, u.email, c.cedula, c.telefono, c.objetivo, MAX(m.fecha_fin) AS ultima_fin
        FROM clientes c
        JOIN usuarios u ON u.id = c.usuario_id
        LEFT JOIN membresias m ON m.cliente_id = c.id";
$parametros = [];
if ($buscar != "") {
    $sql .= " WHERE u.nombre LIKE ? OR u.email LIKE ? OR c.cedula LIKE ?";
    $parametros = ["%$buscar%", "%$buscar%", "%$buscar%"];
}
$sql .= " GROUP BY c.id, u.nombre, u.email, c.cedula, c.telefono, c.objetivo ORDER BY u.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$clientes = $stmt->fetchAll();

$tituloPagina = "Clientes";
$menuActivo = "clientes";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina">Clientes</h1>
        <p class="text-muted mb-0"><?php echo count($clientes); ?> cliente(s)</p>
    </div>
    <a href="formulario.php" class="btn btn-primary">Nuevo cliente</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-sm-8 col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, correo o cédula"
            value="<?php echo htmlspecialchars($buscar); ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <?php if ($buscar != "") { ?>
            <a href="index.php" class="btn btn-link">Limpiar</a>
        <?php } ?>
    </div>
</form>

<div class="tarjeta">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Cliente</th><th>Cédula</th><th>Teléfono</th><th>Membresía</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (count($clientes) == 0) { ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron clientes.</td></tr>
                <?php } ?>
                <?php foreach ($clientes as $c) {
                    $estado = estadoCliente($c["ultima_fin"]);
                ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($c["nombre"]); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($c["email"]); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($c["cedula"]); ?></td>
                        <td><?php echo htmlspecialchars($c["telefono"]); ?></td>
                        <td>
                            <span class="badge <?php echo claseEstado($estado); ?>"><?php echo $estado; ?></span>
                            <?php if ($c["ultima_fin"] != null) { ?>
                                <small class="text-muted d-block">hasta <?php echo fecha($c["ultima_fin"]); ?></small>
                            <?php } ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="ver.php?id=<?php echo (int) $c["id"]; ?>" class="btn btn-outline-secondary btn-sm">Ver</a>
                            <a href="formulario.php?id=<?php echo (int) $c["id"]; ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="eliminar.php" method="POST" class="d-inline form-eliminar"
                                data-mensaje="¿Eliminar a este cliente? También se borrarán sus membresías, pagos y rutinas.">
                                <input type="hidden" name="id" value="<?php echo (int) $c["id"]; ?>">
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
