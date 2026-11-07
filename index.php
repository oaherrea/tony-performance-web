<?php
// Página principal pública: se puede ver sin iniciar sesión.
$ruta = "";
require "config.php";
require "conexion.php";
require "includes/funciones.php";
require "datos/planes.php";

$tituloPagina = "Inicio";
$menuActivo = "inicio";
include "includes/header.php";
?>

<section class="portada-inicio mb-5">
    <h1>Entrena con plan.<br>Controla tu progreso.</h1>
    <p class="lead">Consulta tu membresía, tus pagos y tus rutinas de entrenamiento en Tony Performance Center.</p>
    <?php if (isset($_SESSION["usuario_id"])) { ?>
        <a href="<?php echo paginaInicio($_SESSION["rol"]); ?>" class="btn btn-primary btn-lg">Ir a mi panel</a>
    <?php } else { ?>
        <a href="login.php" class="btn btn-primary btn-lg">Iniciar sesión</a>
        <a href="#planes" class="btn btn-outline-light btn-lg">Ver planes</a>
    <?php } ?>
</section>

<section class="mb-5">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="tarjeta h-100">
                <div class="tarjeta-cuerpo">
                    <h2 class="h5 fw-bold">Membresías</h2>
                    <p class="text-muted mb-0">Cada cliente ve cuándo empieza y cuándo termina su membresía y si está vigente, por vencer o vencida.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tarjeta h-100">
                <div class="tarjeta-cuerpo">
                    <h2 class="h5 fw-bold">Pagos</h2>
                    <p class="text-muted mb-0">Cada pago queda registrado con su fecha, monto y método, y el cliente puede consultarlos.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tarjeta h-100">
                <div class="tarjeta-cuerpo">
                    <h2 class="h5 fw-bold">Rutinas</h2>
                    <p class="text-muted mb-0">El entrenador asigna rutinas por día de la semana y el cliente las consulta desde su portal.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="planes" class="mb-4">
    <h2 class="titulo-pagina mb-3">Nuestros planes</h2>
    <div class="row g-3">
        <?php foreach ($planes as $plan) { ?>
            <div class="col-sm-6 col-lg-4">
                <div class="tarjeta h-100">
                    <div class="tarjeta-cuerpo">
                        <h3 class="h5 fw-bold"><?php echo htmlspecialchars($plan["nombre"]); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($plan["descripcion"]); ?></p>
                        <div class="precio-plan"><?php echo colones($plan["precio"]); ?></div>
                        <div class="small text-muted"><?php echo (int) $plan["duracion_dias"]; ?> días de acceso</div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if (count($planes) == 0) { ?>
            <div class="col-12"><div class="alert alert-info">Aún no hay planes registrados.</div></div>
        <?php } ?>
    </div>
</section>

<?php include "includes/footer.php"; ?>
