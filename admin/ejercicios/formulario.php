<?php
// Ejercicios — crear y editar
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$errores = [];
$datos = ["nombre" => "", "grupo_muscular" => "", "descripcion" => ""];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT nombre, grupo_muscular, descripcion FROM ejercicios WHERE id = ?");
    $stmt->execute([$id]);
    $ejercicio = $stmt->fetch();
    if (!$ejercicio) {
        $_SESSION["error"] = "El ejercicio no existe.";
        header("Location: index.php");
        exit;
    }
    $datos = $ejercicio;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datos["nombre"]         = valorPost("nombre");
    $datos["grupo_muscular"] = valorPost("grupo_muscular");
    $datos["descripcion"]    = valorPost("descripcion");

    if ($datos["nombre"] == "" || strlen($datos["nombre"]) > 80) {
        $errores[] = "El nombre es obligatorio (máximo 80 caracteres).";
    }
    if (!in_array($datos["grupo_muscular"], $gruposMusculares)) {
        $errores[] = "Seleccione un grupo muscular de la lista.";
    }

    if (count($errores) == 0) {
        $stmt = $pdo->prepare("SELECT id FROM ejercicios WHERE nombre = ? AND id <> ?");
        $stmt->execute([$datos["nombre"], $id]);
        if ($stmt->fetch()) {
            $errores[] = "Ya existe un ejercicio con ese nombre.";
        }
    }

    if (count($errores) == 0) {
        if ($id == 0) {
            $stmt = $pdo->prepare("INSERT INTO ejercicios (nombre, grupo_muscular, descripcion) VALUES (?, ?, ?)");
            $stmt->execute([$datos["nombre"], $datos["grupo_muscular"], $datos["descripcion"]]);
            $_SESSION["mensaje"] = "Ejercicio creado correctamente.";
        } else {
            $stmt = $pdo->prepare("UPDATE ejercicios SET nombre = ?, grupo_muscular = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$datos["nombre"], $datos["grupo_muscular"], $datos["descripcion"], $id]);
            $_SESSION["mensaje"] = "Ejercicio actualizado correctamente.";
        }
        header("Location: index.php");
        exit;
    }
}

$tituloPagina = $id > 0 ? "Editar ejercicio" : "Nuevo ejercicio";
$menuActivo = "ejercicios";
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
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="80"
                                value="<?php echo htmlspecialchars($datos["nombre"]); ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label for="grupo_muscular" class="form-label">Grupo muscular *</label>
                            <select class="form-select" id="grupo_muscular" name="grupo_muscular" required>
                                <option value="">— Seleccione —</option>
                                <?php foreach ($gruposMusculares as $g) { ?>
                                    <option value="<?php echo $g; ?>" <?php echo $datos["grupo_muscular"] == $g ? "selected" : ""; ?>><?php echo $g; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="300"
                                value="<?php echo htmlspecialchars($datos["descripcion"]); ?>">
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
