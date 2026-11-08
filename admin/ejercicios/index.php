<?php
// Ejercicios — catálogo con filtros
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$buscar = valorGet("q");
$grupo  = valorGet("grupo");

$sql = "SELECT id, nombre, grupo_muscular, descripcion FROM ejercicios WHERE 1 = 1";
$parametros = [];
if ($buscar != "") {
    $sql .= " AND nombre LIKE ?";
    $parametros[] = "%$buscar%";
}
if ($grupo != "") {
    $sql .= " AND grupo_muscular = ?";
    $parametros[] = $grupo;
}
$sql .= " ORDER BY grupo_muscular, nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$ejercicios = $stmt->fetchAll();

$tituloPagina = "Ejercicios";
$menuActivo = "ejercicios";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina">Ejercicios</h1>
        <p class="text-muted mb-0">Catálogo que se usa para armar las rutinas.</p>
    </div>
    <a href="formulario.php" class="btn btn-primary">Nuevo ejercicio</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($buscar); ?>">
    </div>
    <div class="col-sm-6 col-md-3">
        <select name="grupo" class="form-select">
            <option value="">Todos los grupos</option>
            <?php foreach ($gruposMusculares as $g) { ?>
                <option value="<?php echo $g; ?>" <?php echo $grupo == $g ? "selected" : ""; ?>><?php echo $g; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
    </div>
</form>

<div class="tarjeta">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Ejercicio</th><th>Grupo muscular</th><th>Descripción</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                <?php if (count($ejercicios) == 0) { ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No hay ejercicios con ese filtro.</td></tr>
                <?php } ?>
                <?php foreach ($ejercicios as $e) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo htmlspecialchars($e["nombre"]); ?></td>
                        <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($e["grupo_muscular"]); ?></span></td>
                        <td class="text-muted"><?php echo htmlspecialchars($e["descripcion"]); ?></td>
                        <td class="text-end text-nowrap">
                            <a href="formulario.php?id=<?php echo (int) $e["id"]; ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="eliminar.php" method="POST" class="d-inline form-eliminar" data-mensaje="¿Eliminar este ejercicio?">
                                <input type="hidden" name="id" value="<?php echo (int) $e["id"]; ?>">
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
