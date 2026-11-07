<?php
// Clientes — crear y editar (el mismo formulario sirve para las dos cosas)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

// Si viene ?id=5 estamos editando; si no, es un cliente nuevo
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$errores = [];

$datos = ["nombre" => "", "email" => "", "cedula" => "", "telefono" => "", "fecha_nacimiento" => "", "objetivo" => ""];
$usuarioId = 0;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT c.id, c.usuario_id, u.nombre, u.email, c.cedula, c.telefono, c.fecha_nacimiento, c.objetivo
                           FROM clientes c JOIN usuarios u ON u.id = c.usuario_id WHERE c.id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();
    if (!$cliente) {
        $_SESSION["error"] = "El cliente no existe.";
        header("Location: index.php");
        exit;
    }
    $usuarioId = (int) $cliente["usuario_id"];
    $datos = $cliente;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datos["nombre"]           = valorPost("nombre");
    $datos["email"]            = valorPost("email");
    $datos["cedula"]           = valorPost("cedula");
    $datos["telefono"]         = valorPost("telefono");
    $datos["fecha_nacimiento"] = valorPost("fecha_nacimiento");
    $datos["objetivo"]         = valorPost("objetivo");
    $password                  = isset($_POST["password"]) ? $_POST["password"] : "";

    // ---- Validaciones ----
    if ($datos["nombre"] == "" || strlen($datos["nombre"]) > 100) {
        $errores[] = "El nombre es obligatorio (máximo 100 caracteres).";
    }
    if (!filter_var($datos["email"], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Ingrese un correo electrónico válido.";
    }
    if ($datos["cedula"] == "") {
        $errores[] = "La cédula es obligatoria.";
    }
    if ($datos["fecha_nacimiento"] != "" && !fechaValida($datos["fecha_nacimiento"])) {
        $errores[] = "La fecha de nacimiento no es válida.";
    }
    // La contraseña es obligatoria al crear; al editar solo si se quiere cambiar
    if ($id == 0 && strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    }
    if ($id > 0 && $password != "" && strlen($password) < 8) {
        $errores[] = "La nueva contraseña debe tener al menos 8 caracteres.";
    }

    // ---- Correo y cédula no se pueden repetir ----
    if (count($errores) == 0) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ?");
        $stmt->execute([$datos["email"], $usuarioId]);
        if ($stmt->fetch()) {
            $errores[] = "Ya existe un usuario con ese correo.";
        }
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE cedula = ? AND id <> ?");
        $stmt->execute([$datos["cedula"], $id]);
        if ($stmt->fetch()) {
            $errores[] = "Ya existe un cliente con esa cédula.";
        }
    }

    if (count($errores) == 0) {
        $nacimiento = $datos["fecha_nacimiento"] != "" ? $datos["fecha_nacimiento"] : null;

        if ($id == 0) {
            // La contraseña se guarda SOLO como hash (nunca en texto plano)
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, 'cliente')");
            $stmt->execute([$datos["nombre"], $datos["email"], $hash]);
            $nuevoUsuarioId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO clientes (usuario_id, cedula, telefono, fecha_nacimiento, objetivo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nuevoUsuarioId, $datos["cedula"], $datos["telefono"], $nacimiento, $datos["objetivo"]]);
            $nuevoClienteId = $pdo->lastInsertId();

            $_SESSION["mensaje"] = "Cliente creado. Ahora registre su primera membresía.";
            header("Location: ../membresias/formulario.php?cliente_id=" . $nuevoClienteId);
            exit;
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
            $stmt->execute([$datos["nombre"], $datos["email"], $usuarioId]);

            if ($password != "") {
                $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
                $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $usuarioId]);
            }

            $stmt = $pdo->prepare("UPDATE clientes SET cedula = ?, telefono = ?, fecha_nacimiento = ?, objetivo = ? WHERE id = ?");
            $stmt->execute([$datos["cedula"], $datos["telefono"], $nacimiento, $datos["objetivo"], $id]);

            $_SESSION["mensaje"] = "Cliente actualizado correctamente.";
            header("Location: index.php");
            exit;
        }
    }
}

$tituloPagina = $id > 0 ? "Editar cliente" : "Nuevo cliente";
$menuActivo = "clientes";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina"><?php echo $tituloPagina; ?></h1>
    <p class="text-muted mb-0">Se crea también la cuenta con la que el cliente entra a su portal.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="tarjeta">
            <div class="tarjeta-cuerpo">
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

                <form method="POST" action="formulario.php<?php echo $id > 0 ? '?id=' . $id : ''; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100"
                                value="<?php echo htmlspecialchars($datos["nombre"]); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo electrónico *</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="120"
                                value="<?php echo htmlspecialchars($datos["email"]); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" maxlength="20"
                                value="<?php echo htmlspecialchars($datos["cedula"]); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20"
                                value="<?php echo htmlspecialchars($datos["telefono"]); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                value="<?php echo htmlspecialchars($datos["fecha_nacimiento"]); ?>">
                        </div>
                        <div class="col-12">
                            <label for="objetivo" class="form-label">Objetivo</label>
                            <input type="text" class="form-control" id="objetivo" name="objetivo" maxlength="150"
                                placeholder="Ej.: ganar masa muscular"
                                value="<?php echo htmlspecialchars($datos["objetivo"]); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Contraseña <?php echo $id > 0 ? "(déjela vacía para no cambiarla)" : "*"; ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                            <div class="form-text">Mínimo 8 caracteres. Se guarda cifrada (hash).</div>
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
