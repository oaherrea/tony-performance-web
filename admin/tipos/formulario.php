<?php
// Planes (tipos de membresía) — crear y editar
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$errores = [];
$datos = ["nombre" => "", "descripcion" => "", "duracion_dias" => "", "precio" => ""];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT nombre, descripcion, duracion_dias, precio FROM tipos_membresia WHERE id = ?");
    $stmt->execute([$id]);
    $tipo = $stmt->fetch();
    if (!$tipo) {
        $_SESSION["error"] = "El plan no existe.";
        header("Location: index.php");
        exit;
    }
    $datos = $tipo;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datos["nombre"]        = valorPost("nombre");
    $datos["descripcion"]   = valorPost("descripcion");
    $datos["duracion_dias"] = valorPost("duracion_dias");
    $datos["precio"]        = valorPost("precio");

    if ($datos["nombre"] == "" || strlen($datos["nombre"]) > 60) {
        $errores[] = "El nombre es obligatorio (máximo 60 caracteres).";
    }
    if (!filter_var($datos["duracion_dias"], FILTER_VALIDATE_INT) || $datos["duracion_dias"] < 1 || $datos["duracion_dias"] > 3650) {
        $errores[] = "La duración debe ser un número entero de días (mayor a 0).";
    }
    if (!is_numeric($datos["precio"]) || $datos["precio"] < 0) {
        $errores[] = "El precio debe ser un número mayor o igual a 0.";
    }

    if (count($errores) == 0) {
        $stmt = $pdo->prepare("SELECT id FROM tipos_membresia WHERE nombre = ? AND id <> ?");
        $stmt->execute([$datos["nombre"], $id]);
        if ($stmt->fetch()) {
            $errores[] = "Ya existe un plan con ese nombre.";
        }
    }

    if (count($errores) == 0) {
        if ($id == 0) {
            $stmt = $pdo->prepare("INSERT INTO tipos_membresia (nombre, descripcion, duracion_dias, precio) VALUES (?, ?, ?, ?)");
            $stmt->execute([$datos["nombre"], $datos["descripcion"], $datos["duracion_dias"], $datos["precio"]]);
            $_SESSION["mensaje"] = "Plan creado correctamente.";
        } else {
            $stmt = $pdo->prepare("UPDATE tipos_membresia SET nombre = ?, descripcion = ?, duracion_dias = ?, precio = ? WHERE id = ?");
            $stmt->execute([$datos["nombre"], $datos["descripcion"], $datos["duracion_dias"], $datos["precio"], $id]);
            $_SESSION["mensaje"] = "Plan actualizado correctamente.";
        }
        header("Location: index.php");
        exit;
    }
}

$tituloPagina = $id > 0 ? "Editar plan" : "Nuevo plan";
$menuActivo = "tipos";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina"><?php echo $tituloPagina; ?></h1>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="tarjeta">
            <div class="tarjeta-cuerpo">
                <?php if (count($errores) > 0) { ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $error) { ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <form method="POST" action="formulario.php<?php echo $id > 0 ? '?id=' . $id : ''; ?>">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del plan *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="60"
                            value="<?php echo htmlspecialchars($datos["nombre"]); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="200"
                            value="<?php echo htmlspecialchars($datos["descripcion"]); ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="duracion_dias" class="form-label">Duración (días) *</label>
                            <input type="number" min="1" class="form-control" id="duracion_dias" name="duracion_dias"
                                value="<?php echo htmlspecialchars($datos["duracion_dias"]); ?>" required>
                            <div class="form-text">Con esta duración se calcula el último día de acceso.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="precio" class="form-label">Precio (₡) *</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="precio" name="precio"
                                value="<?php echo htmlspecialchars($datos["precio"]); ?>" required>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
