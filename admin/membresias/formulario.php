<?php
// Membresías — registrar una membresía nueva o renovar, junto con su pago.
// Las fechas NO las escribe el usuario: las calcula el sistema con estas reglas:
//   1) Si el cliente todavía tiene días pagados, la nueva membresía empieza
//      el día siguiente a su último día de acceso (no pierde días).
//   2) Si ya venció (o es su primera vez), empieza hoy.
//   3) Último día = inicio + (duración - 1) días.
//   4) La renovación es una fila NUEVA: la anterior no se modifica y se guarda
//      su id en renovacion_de.
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$clientes = $pdo->query("SELECT c.id, c.cedula, u.nombre FROM clientes c JOIN usuarios u ON u.id = c.usuario_id ORDER BY u.nombre")->fetchAll();
$tipos = $pdo->query("SELECT id, nombre, duracion_dias, precio FROM tipos_membresia ORDER BY duracion_dias")->fetchAll();

$errores = [];
$clienteId = $_SERVER["REQUEST_METHOD"] == "POST" ? (int) valorPost("cliente_id") : (int) valorGet("cliente_id");

// Plan sugerido por defecto: el de 30 días (mensual)
$tipoDefecto = count($tipos) > 0 ? $tipos[0] : null;
foreach ($tipos as $t) {
    if ($t["duracion_dias"] == 30) {
        $tipoDefecto = $t;
    }
}
$tipoId     = $tipoDefecto ? (int) $tipoDefecto["id"] : 0;
$monto      = $tipoDefecto ? $tipoDefecto["precio"] : "";
$metodo     = "efectivo";
$referencia = "";

// Última membresía del cliente (la de fecha_fin más lejana)
function ultimaMembresia($pdo, $clienteId) {
    $stmt = $pdo->prepare("SELECT id, fecha_fin FROM membresias WHERE cliente_id = ? ORDER BY fecha_fin DESC, id DESC LIMIT 1");
    $stmt->execute([$clienteId]);
    return $stmt->fetch();
}

// Fecha en que empezaría la nueva membresía
function calcularInicio($ultima) {
    $hoy = date("Y-m-d");
    if ($ultima && $ultima["fecha_fin"] >= $hoy) {
        return date("Y-m-d", strtotime($ultima["fecha_fin"] . " +1 day"));
    }
    return $hoy;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipoId     = (int) valorPost("tipo_id");
    $monto      = valorPost("monto");
    $metodo     = valorPost("metodo");
    $referencia = valorPost("referencia");

    // ---- Validaciones ----
    $clienteValido = false;
    foreach ($clientes as $c) {
        if ($c["id"] == $clienteId) {
            $clienteValido = true;
        }
    }
    $tipoElegido = null;
    foreach ($tipos as $t) {
        if ($t["id"] == $tipoId) {
            $tipoElegido = $t;
        }
    }

    if (!$clienteValido) {
        $errores[] = "Seleccione un cliente.";
    }
    if ($tipoElegido == null) {
        $errores[] = "Seleccione un plan de membresía.";
    }
    if (!is_numeric($monto) || $monto <= 0) {
        $errores[] = "El monto debe ser un número mayor a 0.";
    }
    if (!isset($metodosPago[$metodo])) {
        $errores[] = "Seleccione un método de pago.";
    }
    if (strlen($referencia) > 60) {
        $errores[] = "La referencia no puede tener más de 60 caracteres.";
    }
    if (($metodo == "sinpe" || $metodo == "transferencia") && $referencia == "") {
        $errores[] = "Indique el número de comprobante para pagos por SINPE o transferencia.";
    }

    if (count($errores) == 0) {
        $ultima = ultimaMembresia($pdo, $clienteId);
        $inicio = calcularInicio($ultima);
        $fin    = calcularFechaFin($inicio, $tipoElegido["duracion_dias"]);
        $renovacionDe = $ultima ? $ultima["id"] : null;

        // 1) se guarda la membresía
        $stmt = $pdo->prepare("INSERT INTO membresias (cliente_id, tipo_membresia_id, fecha_inicio, fecha_fin, renovacion_de) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$clienteId, $tipoId, $inicio, $fin, $renovacionDe]);
        $membresiaId = $pdo->lastInsertId();

        // 2) se guarda el pago de esa membresía
        $stmt = $pdo->prepare("INSERT INTO pagos (membresia_id, monto, metodo, fecha_pago, referencia, registrado_por) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$membresiaId, $monto, $metodo, date("Y-m-d"), $referencia != "" ? $referencia : null, $_SESSION["usuario_id"]]);

        if ($ultima) {
            $_SESSION["mensaje"] = "Membresía renovada y pago registrado. Vigente del " . fecha($inicio) . " al " . fecha($fin) . ".";
        } else {
            $_SESSION["mensaje"] = "Membresía creada y pago registrado. Vigente del " . fecha($inicio) . " al " . fecha($fin) . ".";
        }
        header("Location: ../clientes/ver.php?id=" . $clienteId);
        exit;
    }
}

// Datos del cliente seleccionado para mostrar su situación actual
$ultima = null;
$inicioNuevo = date("Y-m-d");
if ($clienteId > 0) {
    $ultima = ultimaMembresia($pdo, $clienteId);
    $inicioNuevo = calcularInicio($ultima);
}

$tituloPagina = "Registrar membresía";
$menuActivo = "membresias";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina">Registrar membresía y pago</h1>
    <p class="text-muted mb-0">Sirve para la primera membresía de un cliente y para sus renovaciones.</p>
</div>

<div class="row g-3">
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

                <form method="POST" action="formulario.php">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select class="form-select" id="cliente_id" name="cliente_id" data-recargar="formulario.php" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($clientes as $c) { ?>
                                <option value="<?php echo (int) $c["id"]; ?>" <?php echo $clienteId == $c["id"] ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($c["nombre"]); ?> · <?php echo htmlspecialchars($c["cedula"]); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_id" class="form-label">Plan de membresía *</label>
                        <select class="form-select" id="tipo_id" name="tipo_id" data-inicio="<?php echo $inicioNuevo; ?>" required>
                            <?php foreach ($tipos as $t) { ?>
                                <option value="<?php echo (int) $t["id"]; ?>"
                                    data-precio="<?php echo $t["precio"]; ?>" data-dias="<?php echo (int) $t["duracion_dias"]; ?>"
                                    <?php echo $tipoId == $t["id"] ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($t["nombre"]); ?> · <?php echo (int) $t["duracion_dias"]; ?> días · <?php echo colones($t["precio"]); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label for="monto" class="form-label">Monto cobrado (₡) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="monto" name="monto"
                                value="<?php echo htmlspecialchars($monto); ?>" required>
                        </div>
                        <div class="col-sm-4">
                            <label for="metodo" class="form-label">Método de pago</label>
                            <select class="form-select" id="metodo" name="metodo">
                                <?php foreach ($metodosPago as $clave => $texto) { ?>
                                    <option value="<?php echo $clave; ?>" <?php echo $metodo == $clave ? "selected" : ""; ?>><?php echo $texto; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label for="referencia" class="form-label">Comprobante</label>
                            <input type="text" class="form-control" id="referencia" name="referencia" maxlength="60"
                                placeholder="Nº de comprobante" value="<?php echo htmlspecialchars($referencia); ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Registrar membresía y pago</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="tarjeta">
            <div class="tarjeta-encabezado">Período que se creará</div>
            <div class="tarjeta-cuerpo">
                <?php if ($clienteId == 0) { ?>
                    <p class="text-muted mb-0">Seleccione un cliente para ver cuándo empezaría su membresía.</p>
                <?php } else {
                    $estadoActual = $ultima ? estadoCliente($ultima["fecha_fin"]) : "sin membresía";
                ?>
                    <div class="mb-3">
                        <div class="etiqueta-dato">Situación actual</div>
                        <span class="badge <?php echo claseEstado($estadoActual); ?>"><?php echo $estadoActual; ?></span>
                        <?php if ($ultima) { ?> <small>último día: <?php echo fecha($ultima["fecha_fin"]); ?></small><?php } ?>
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-6">
                            <div class="p-3 rounded-3 border bg-light">
                                <div class="etiqueta-dato">Inicia</div>
                                <div class="fw-bold fs-5" id="fechaInicio"><?php echo fecha($inicioNuevo); ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border bg-light">
                                <div class="etiqueta-dato">Último día</div>
                                <div class="fw-bold fs-5" id="fechaFin">—</div>
                            </div>
                        </div>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Si el cliente aún tiene días pagados, la nueva membresía inicia al día siguiente de que
                        termine la actual. Si ya venció, inicia hoy.
                    </p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php include $ruta . "includes/footer.php"; ?>
