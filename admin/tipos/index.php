<?php
// Planes (tipos de membresía) — listado
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

// Cuántas membresías se han vendido de cada plan
$tipos = $pdo->query("SELECT t.id, t.nombre, t.descripcion, t.duracion_dias, t.precio, COUNT(m.id) AS vendidas
                      FROM tipos_membresia t
                      LEFT JOIN membresias m ON m.tipo_membresia_id = t.id
                      GROUP BY t.id, t.nombre, t.descripcion, t.duracion_dias, t.precio
                      ORDER BY t.duracion_dias")->fetchAll();

$tituloPagina = "Planes de membresía";
$menuActivo = "tipos";
include $ruta . "includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="titulo-pagina">Planes de membresía</h1>
        <p class="text-muted mb-0">Cada plan define cuántos días dura y cuánto cuesta.</p>
    </div>
    <a href="formulario.php" class="btn btn-primary">Nuevo plan</a>
</div>

<div class="tarjeta">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Plan</th><th>Descripción</th><th>Duración</th><th>Precio</th><th>Vendidas</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($tipos as $t) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo htmlspecialchars($t["nombre"]); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($t["descripcion"]); ?></td>
                        <td><?php echo (int) $t["duracion_dias"]; ?> días</td>
                        <td><?php echo colones($t["precio"]); ?></td>
                        <td><?php echo (int) $t["vendidas"]; ?></td>
                        <td class="text-end text-nowrap">
                            <a href="formulario.php?id=<?php echo (int) $t["id"]; ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="eliminar.php" method="POST" class="d-inline form-eliminar" data-mensaje="¿Eliminar este plan?">
                                <input type="hidden" name="id" value="<?php echo (int) $t["id"]; ?>">
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
