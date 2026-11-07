<?php
// Inicio de sesión (login)
$ruta = "";
require "config.php";
require "conexion.php";
require "includes/funciones.php";

// Si ya inició sesión, lo mandamos a su página de inicio
if (isset($_SESSION["usuario_id"])) {
    header("Location: " . paginaInicio($_SESSION["rol"]));
    exit;
}

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = valorPost("email");
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if ($email == "" || $password == "") {
        $error = "Ingrese su correo y su contraseña.";
    } else {
        // Consulta preparada: el correo nunca se pega directo en el SQL
        $stmt = $pdo->prepare("SELECT id, nombre, password_hash, rol FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // password_verify compara la clave escrita con el hash guardado en la BD
        if ($usuario && password_verify($password, $usuario["password_hash"])) {
            session_regenerate_id(true);   // nuevo id de sesión al iniciar
            $_SESSION["usuario_id"] = (int) $usuario["id"];
            $_SESSION["nombre"]     = $usuario["nombre"];
            $_SESSION["rol"]        = $usuario["rol"];

            if ($usuario["rol"] == "cliente") {
                $stmt = $pdo->prepare("SELECT id FROM clientes WHERE usuario_id = ?");
                $stmt->execute([$usuario["id"]]);
                $cliente = $stmt->fetch();
                $_SESSION["cliente_id"] = $cliente ? (int) $cliente["id"] : 0;
            }

            header("Location: " . paginaInicio($usuario["rol"]));
            exit;
        }
        // El mensaje es el mismo si el correo no existe o la clave es incorrecta
        $error = "Correo o contraseña incorrectos.";
    }
}

$tituloPagina = "Iniciar sesión";
include "includes/header.php";
?>

<div class="login-caja row g-0">
    <div class="col-md-5">
        <div class="login-hero">
            <div class="logo">T</div>
            <h1>Tony Performance Center</h1>
            <p class="text-secondary">Controla tus membresías, pagos y rutinas de entrenamiento en un solo lugar.</p>
            <ul class="list-unstyled small text-secondary mt-4 mb-0">
                <li class="mb-2">Administración de clientes</li>
                <li class="mb-2">Membresías y pagos</li>
                <li>Rutinas para cada cliente</li>
            </ul>
        </div>
    </div>

    <div class="col-md-7">
        <div class="login-form">
            <h2 class="h4 fw-bold mb-1">Iniciar sesión</h2>
            <p class="text-muted">Ingrese con su cuenta de administrador o de cliente.</p>

            <?php if ($error != "") { ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>

            <div class="cuentas-prueba mt-4">
                <strong>Cuentas de prueba</strong><br>
                Administrador: admin@tonyperformance.com / Admin123!<br>
                Cliente: cliente@tonyperformance.com / Cliente123!
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
