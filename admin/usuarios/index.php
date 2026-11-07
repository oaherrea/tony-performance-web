<?php
// Usuarios — listado de todas las cuentas (administradores y clientes) con búsqueda
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$buscar = valorGet("q");

// LEFT JOIN: los administradores no tienen fila en clientes (c.id queda NULL)
$sql = "SELECT u.id, u.nombre, u.email, u.rol, u.creado_en, c.id AS cliente_id
        FROM usuarios u
        LEFT JOIN clientes c ON c.usuario_id = u.id";
$parametros = [];
if ($buscar != "") {
    $sql .= " WHERE u.nombre LIKE ? OR u.email LIKE ?";
    $parametros = ["%$buscar%", "%$buscar%"];
}
$sql .= " ORDER BY u.rol, u.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$usuarios = $stmt->fetchAll();

$tituloPagina = "Usuarios";
$menuActivo = "usuarios";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina">Usuarios y roles</h1>
        <p class="text-muted mb-0"><?php echo count($usuarios); ?> usuario(s) · Los clientes se crean desde el módulo Clientes</p>
    </div>
    <a href="formulario.php" class="btn btn-primary">Nuevo administrador</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-sm-8 col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o correo"
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
                <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Creado</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (count($usuarios) == 0) { ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron usuarios.</td></tr>
                <?php } ?>
                <?php foreach ($usuarios as $u) { ?>
                    <tr>
                        <td class="fw-semibold">
                            <?php echo htmlspecialchars($u["nombre"]); ?>
                            <?php if ($u["id"] == $_SESSION["usuario_id"]) { ?>
                                <small class="text-muted">(usted)</small>
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($u["email"]); ?></td>
                        <td>
                            <span class="badge <?php echo $u["rol"] == "administrador" ? 'text-bg-dark' : 'text-bg-secondary'; ?>">
                                <?php echo htmlspecialchars($u["rol"]); ?>
                            </span>
                        </td>
                        <td><?php echo fecha(substr($u["creado_en"], 0, 10)); ?></td>
                        <td class="text-end text-nowrap">
                            <?php if ($u["rol"] == "administrador") { ?>
                                <a href="formulario.php?id=<?php echo (int) $u["id"]; ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <?php } else { ?>
                                <a href="../clientes/formulario.php?id=<?php echo (int) $u["cliente_id"]; ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <?php } ?>

                            <?php if ($u["id"] != $_SESSION["usuario_id"]) { ?>
                                <form action="eliminar.php" method="POST" class="d-inline form-eliminar"
                                    data-mensaje="¿Eliminar este usuario? Si es un cliente también se borrarán sus membresías, pagos y rutinas.">
                                    <input type="hidden" name="id" value="<?php echo (int) $u["id"]; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
