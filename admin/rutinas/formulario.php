<?php
// Rutinas — asignar una rutina nueva a un cliente (con sus ejercicios)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$clientes = $pdo->query("SELECT c.id, c.cedula, u.nombre FROM clientes c JOIN usuarios u ON u.id = c.usuario_id ORDER BY u.nombre")->fetchAll();
$ejercicios = $pdo->query("SELECT id, nombre, grupo_muscular FROM ejercicios ORDER BY grupo_muscular, nombre")->fetchAll();

$filas = 8;   // cantidad de ejercicios que se pueden cargar en el formulario
$errores = [];

$clienteId = (int) valorGet("cliente_id");
$nombre = "";
$objetivo = "";
// Una fila por ejercicio: id del ejercicio, día, series, repeticiones y notas
$lineas = [];
for ($i = 0; $i < $filas; $i++) {
    $lineas[$i] = ["ejercicio" => "", "dia" => "Lunes", "series" => "3", "reps" => "10", "notas" => ""];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clienteId = (int) valorPost("cliente_id");
    $nombre    = valorPost("nombre");
    $objetivo  = valorPost("objetivo");

    $clienteValido = false;
    foreach ($clientes as $c) {
        if ($c["id"] == $clienteId) {
            $clienteValido = true;
        }
    }
    $idsEjercicios = [];
    foreach ($ejercicios as $e) {
        $idsEjercicios[] = $e["id"];
    }

    // Se leen las filas del formulario (campos tipo ejercicio[0], dia[0], ...)
    $validas = [];
    for ($i = 0; $i < $filas; $i++) {
        $lineas[$i]["ejercicio"] = isset($_POST["ejercicio"][$i]) ? trim($_POST["ejercicio"][$i]) : "";
        $lineas[$i]["dia"]       = isset($_POST["dia"][$i]) ? trim($_POST["dia"][$i]) : "Lunes";
        $lineas[$i]["series"]    = isset($_POST["series"][$i]) ? trim($_POST["series"][$i]) : "";
        $lineas[$i]["reps"]      = isset($_POST["reps"][$i]) ? trim($_POST["reps"][$i]) : "";
        $lineas[$i]["notas"]     = isset($_POST["notas"][$i]) ? trim($_POST["notas"][$i]) : "";

        // Una fila sin ejercicio seleccionado se ignora
        if ($lineas[$i]["ejercicio"] == "") {
            continue;
        }
        $n = $i + 1;
        if (!in_array($lineas[$i]["ejercicio"], $idsEjercicios)) {
            $errores[] = "Fila $n: el ejercicio no es válido.";
        } elseif (!in_array($lineas[$i]["dia"], $diasSemana)) {
            $errores[] = "Fila $n: el día no es válido.";
        } elseif (!filter_var($lineas[$i]["series"], FILTER_VALIDATE_INT) || $lineas[$i]["series"] < 1) {
            $errores[] = "Fila $n: las series deben ser un número entero mayor a 0.";
        } elseif ($lineas[$i]["reps"] == "" || strlen($lineas[$i]["reps"]) > 20) {
            $errores[] = "Fila $n: indique las repeticiones (máximo 20 caracteres).";
        } else {
            $validas[] = $lineas[$i];
        }
    }

    if (!$clienteValido) {
        $errores[] = "Seleccione un cliente.";
    }
    if ($nombre == "" || strlen($nombre) > 80) {
        $errores[] = "El nombre de la rutina es obligatorio (máximo 80 caracteres).";
    }
    if (count($validas) == 0 && count($errores) == 0) {
        $errores[] = "Agregue al menos un ejercicio a la rutina.";
    }

    if (count($errores) == 0) {
        $stmt = $pdo->prepare("INSERT INTO rutinas (cliente_id, nombre, objetivo, fecha_asignacion, creado_por) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$clienteId, $nombre, $objetivo, date("Y-m-d"), $_SESSION["usuario_id"]]);
        $rutinaId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO rutina_ejercicios (rutina_id, ejercicio_id, dia, series, repeticiones, notas) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($validas as $linea) {
            $stmt->execute([$rutinaId, $linea["ejercicio"], $linea["dia"], $linea["series"], $linea["reps"], $linea["notas"] != "" ? $linea["notas"] : null]);
        }

        $_SESSION["mensaje"] = "Rutina asignada con " . count($validas) . " ejercicio(s).";
        header("Location: ver.php?id=" . $rutinaId);
        exit;
    }
}

$tituloPagina = "Asignar rutina";
$menuActivo = "rutinas";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina">Asignar rutina</h1>
    <p class="text-muted mb-0">Elija el cliente y arme su plan de entrenamiento por día.</p>
</div>

<?php if (count($errores) > 0) { ?>
    <div class="alert alert-danger">
        <strong>Corrija lo siguiente:</strong>
        <ul class="mb-0">
            <?php foreach ($errores as $error) { ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<form method="POST" action="formulario.php">
    <div class="tarjeta mb-3">
        <div class="tarjeta-cuerpo">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="cliente_id" class="form-label">Cliente *</label>
                    <select class="form-select" id="cliente_id" name="cliente_id" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($clientes as $c) { ?>
                            <option value="<?php echo (int) $c["id"]; ?>" <?php echo $clienteId == $c["id"] ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($c["nombre"]); ?> · <?php echo htmlspecialchars($c["cedula"]); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre de la rutina *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="80"
                        placeholder="Ej.: Hipertrofia Full Body" value="<?php echo htmlspecialchars($nombre); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="objetivo" class="form-label">Objetivo</label>
                    <input type="text" class="form-control" id="objetivo" name="objetivo" maxlength="150"
                        value="<?php echo htmlspecialchars($objetivo); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="tarjeta mb-3">
        <div class="tarjeta-encabezado">Ejercicios <small class="text-muted fw-normal">(las filas sin ejercicio se ignoran)</small></div>
        <div class="tarjeta-cuerpo">
            <?php for ($i = 0; $i < $filas; $i++) { ?>
                <div class="fila-ejercicio">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label small mb-1">Ejercicio</label>
                            <select name="ejercicio[<?php echo $i; ?>]" class="form-select form-select-sm">
                                <option value="">— Ninguno —</option>
                                <?php
                                $grupoActual = "";
                                foreach ($ejercicios as $e) {
                                    if ($e["grupo_muscular"] != $grupoActual) {
                                        if ($grupoActual != "") {
                                            echo "</optgroup>";
                                        }
                                        $grupoActual = $e["grupo_muscular"];
                                        echo '<optgroup label="' . htmlspecialchars($grupoActual) . '">';
                                    }
                                ?>
                                    <option value="<?php echo (int) $e["id"]; ?>" <?php echo $lineas[$i]["ejercicio"] == $e["id"] ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($e["nombre"]); ?>
                                    </option>
                                <?php }
                                if ($grupoActual != "") {
                                    echo "</optgroup>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label class="form-label small mb-1">Día</label>
                            <select name="dia[<?php echo $i; ?>]" class="form-select form-select-sm">
                                <?php foreach ($diasSemana as $d) { ?>
                                    <option value="<?php echo $d; ?>" <?php echo $lineas[$i]["dia"] == $d ? "selected" : ""; ?>><?php echo $d; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-3 col-lg-1">
                            <label class="form-label small mb-1">Series</label>
                            <input type="number" min="1" name="series[<?php echo $i; ?>]" class="form-control form-control-sm"
                                value="<?php echo htmlspecialchars($lineas[$i]["series"]); ?>">
                        </div>
                        <div class="col-3 col-lg-2">
                            <label class="form-label small mb-1">Repeticiones</label>
                            <input type="text" maxlength="20" name="reps[<?php echo $i; ?>]" class="form-control form-control-sm"
                                value="<?php echo htmlspecialchars($lineas[$i]["reps"]); ?>">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small mb-1">Notas</label>
                            <input type="text" maxlength="200" name="notas[<?php echo $i; ?>]" class="form-control form-control-sm"
                                value="<?php echo htmlspecialchars($lineas[$i]["notas"]); ?>">
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar rutina</button>
        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<?php include $ruta . "includes/footer.php"; ?>
