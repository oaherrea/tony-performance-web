<?php
// Muestra el detalle de una rutina agrupado por día de la semana.
// Lo usan la vista del administrador (admin/rutinas/ver.php) y la del cliente
// (cliente/rutina.php). Requiere que la página defina antes:
//   $detalle     -> filas de rutina_ejercicios con el nombre del ejercicio
//   $diasSemana  -> lista de días (viene de includes/funciones.php)
?>
<?php if (count($detalle) == 0) { ?>
    <div class="alert alert-info">Esta rutina todavía no tiene ejercicios.</div>
<?php } ?>

<div class="row g-3">
    <?php foreach ($diasSemana as $dia) {
        // Se reúnen los ejercicios de ese día
        $delDia = [];
        foreach ($detalle as $linea) {
            if ($linea["dia"] == $dia) {
                $delDia[] = $linea;
            }
        }
        if (count($delDia) == 0) {
            continue;
        }
    ?>
        <div class="col-md-6 col-xl-4">
            <div class="tarjeta h-100">
                <div class="tarjeta-cuerpo">
                    <div class="dia-titulo mb-3"><?php echo $dia; ?></div>
                    <?php foreach ($delDia as $linea) { ?>
                        <div class="fila-ejercicio">
                            <div class="fw-semibold"><?php echo htmlspecialchars($linea["ejercicio"]); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($linea["grupo_muscular"]); ?></div>
                            <div class="mt-1">
                                <span class="badge text-bg-dark"><?php echo (int) $linea["series"]; ?> series</span>
                                <span class="badge text-bg-secondary">
                                    <?php
                                    // "10" o "8-10" llevan la palabra reps; "45 s" o "20 min" se muestran tal cual
                                    echo htmlspecialchars($linea["repeticiones"]);
                                    if (preg_match("/^[0-9-]+$/", $linea["repeticiones"])) {
                                        echo " reps";
                                    }
                                    ?>
                                </span>
                            </div>
                            <?php if ($linea["notas"] != "") { ?>
                                <div class="small mt-1"><?php echo htmlspecialchars($linea["notas"]); ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
