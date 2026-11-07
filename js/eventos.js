// Eventos de la interfaz (JavaScript básico, sin librerías)

// 1) Confirmar antes de eliminar: los formularios con la clase "form-eliminar"
var formsEliminar = document.querySelectorAll(".form-eliminar");
for (var i = 0; i < formsEliminar.length; i++) {
    formsEliminar[i].addEventListener("submit", function (evento) {
        var mensaje = this.getAttribute("data-mensaje");
        if (!mensaje) {
            mensaje = "¿Está seguro de eliminar este registro?";
        }
        if (!confirm(mensaje)) {
            evento.preventDefault();
        }
    });
}

// 2) Formulario de membresía: al cambiar de cliente se recarga la página con
//    ?cliente_id=... para mostrar su situación (cuándo empezaría la nueva membresía)
var selectCliente = document.getElementById("cliente_id");
if (selectCliente && selectCliente.getAttribute("data-recargar")) {
    selectCliente.addEventListener("change", function () {
        if (this.value) {
            window.location.href = this.getAttribute("data-recargar") + "?cliente_id=" + this.value;
        }
    });
}

// 3) Formulario de membresía: al elegir el plan se llena el monto y se calcula
//    la fecha en que termina (inicio + duración - 1 día)
var selectPlan = document.getElementById("tipo_id");
if (selectPlan) {
    var campoMonto = document.getElementById("monto");
    var textoFin = document.getElementById("fechaFin");

    function formatear(fecha) {
        var d = fecha.getDate();
        var m = fecha.getMonth() + 1;
        if (d < 10) { d = "0" + d; }
        if (m < 10) { m = "0" + m; }
        return d + "/" + m + "/" + fecha.getFullYear();
    }

    function actualizarPlan(cambiarMonto) {
        var opcion = selectPlan.options[selectPlan.selectedIndex];
        if (!opcion || !opcion.getAttribute("data-dias")) {
            return;
        }
        if (cambiarMonto) {
            campoMonto.value = opcion.getAttribute("data-precio");
        }
        var inicio = selectPlan.getAttribute("data-inicio");   // formato AAAA-MM-DD
        if (inicio && textoFin) {
            var partes = inicio.split("-");
            var fecha = new Date(partes[0], partes[1] - 1, partes[2]);
            fecha.setDate(fecha.getDate() + parseInt(opcion.getAttribute("data-dias")) - 1);
            textoFin.textContent = formatear(fecha);
        }
    }

    selectPlan.addEventListener("change", function () {
        actualizarPlan(true);
    });
    actualizarPlan(false);
}
