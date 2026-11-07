<?php
// Usuarios — crear y editar administradores
// (los usuarios con rol cliente se gestionan en el módulo Clientes)
$ruta = "../../";
require $ruta . "config.php";
require $ruta . "conexion.php";
require $ruta . "includes/soloAdmin.php";
require $ruta . "includes/funciones.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$errores = [];
$datos = ["nombre" => "", "email" => ""];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT nombre, email, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
    if (!$usuario) {
        $_SESSION["error"] = "El usuario no existe.";
        header("Location: index.php");
        exit;
    }
    if ($usuario["rol"] != "administrador") {
        $_SESSION["error"] = "Los clientes se editan desde el módulo Clientes.";
        header("Location: index.php");
        exit;
    }
    $datos["nombre"] = $usuario["nombre"];
    $datos["email"]  = $usuario["email"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datos["nombre"] = valorPost("nombre");
    $datos["email"]  = valorPost("email");
    $password  = isset($_POST["password"]) ? $_POST["password"] : "";
    $password2 = isset($_POST["password2"]) ? $_POST["password2"] : "";

    // Validaciones
    if ($datos["nombre"] == "" || strlen($datos["nombre"]) > 100) {
        $errores[] = "El nombre es obligatorio (máximo 100 caracteres).";
    }
    if (!filter_var($datos["email"], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Ingrese un correo electrónico válido.";
    }
    // En un usuario nuevo la contraseña es obligatoria; al editar, vacía = no cambiarla
    if ($id == 0 || $password != "") {
        if (strlen($password) < 8) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres.";
        }
        if ($password != $password2) {
            $errores[] = "Las contraseñas no coinciden.";
        }
    }

    // El correo no puede estar repetido (se ignora el propio usuario al editar)
    if (count($errores) == 0) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ?");
        $stmt->execute([$datos["email"], $id]);
        if ($stmt->fetch()) {
            $errores[] = "Ya existe un usuario con ese correo.";
        }
    }

    if (count($errores) == 0) {
        if ($id == 0) {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, 'administrador')");
            $stmt->execute([$datos["nombre"], $datos["email"], password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION["mensaje"] = "Administrador creado correctamente.";
        } else {
            if ($password != "") {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$datos["nombre"], $datos["email"], password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
                $stmt->execute([$datos["nombre"], $datos["email"], $id]);
            }
            // Si se editó a sí mismo, se actualiza el nombre que muestra el menú
            if ($id == $_SESSION["usuario_id"]) {
                $_SESSION["nombre"] = $datos["nombre"];
            }
            $_SESSION["mensaje"] = "Usuario actualizado correctamente.";
        }
        header("Location: index.php");
        exit;
    }
}

$tituloPagina = $id > 0 ? "Editar administrador" : "Nuevo administrador";
$menuActivo = "usuarios";
include $ruta . "includes/header.php";
?>

<div class="mb-4">
    <h1 class="titulo-pagina"><?php echo $tituloPagina; ?></h1>
    <p class="text-muted mb-0">Rol asignado: administrador</p>
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
                        <label for="nombre" class="form-label">Nombre completo *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100"
                            value="<?php echo htmlspecialchars($datos["nombre"]); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico *</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="120"
                            value="<?php echo htmlspecialchars($datos["email"]); ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="password" class="form-label">Contraseña <?php echo $id == 0 ? '*' : ''; ?></label>
                            <input type="password" class="form-control" id="password" name="password" <?php echo $id == 0 ? 'required' : ''; ?>>
                            <div class="form-text">
                                <?php echo $id > 0 ? 'Déjela vacía para conservar la actual. ' : ''; ?>Mínimo 8 caracteres.
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="password2" class="form-label">Repetir contraseña</label>
                            <input type="password" class="form-control" id="password2" name="password2">
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
