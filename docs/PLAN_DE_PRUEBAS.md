# Plan de pruebas — Tony Performance Center Web

**Proyecto Final Integrador · Programación V · UACA**
Estudiante: Oscar Armando Herrera Azofeifa

## 1. Objetivo y alcance

Comprobar que el sistema cumple las reglas de negocio y de seguridad definidas en este
avance. Cubre las cinco áreas pedidas: **autenticación, registro de pagos, asignación de
rutinas, renovación de membresías y portal del cliente**, además de la gestión de usuarios,
la seguridad y la integridad de la base de datos.

## 2. Preparación

1. Importar `database.sql` en phpMyAdmin (deja los datos de ejemplo en su estado inicial).
2. Abrir `http://localhost/tony-performance-web/`.
3. Cuentas: `admin@tonyperformance.com` / `Admin123!` y `cliente@tonyperformance.com` / `Cliente123!`.

Las pruebas se hacen **manualmente en el navegador**. Marque la columna *Res.* con ✔ (pasó) o ✘ (falló).

## 3. Casos de prueba

### 3.1 Autenticación y página principal

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| AUT-01 | Abrir `index.php` sin iniciar sesión | Se ve la página principal con los planes; no pide sesión | |
| AUT-02 | En `login.php` entrar como administrador | Abre el dashboard (`admin/index.php`) | |
| AUT-03 | Entrar como cliente | Abre el portal (`cliente/index.php`) | |
| AUT-04 | Escribir una contraseña incorrecta | Mensaje «Correo o contraseña incorrectos»; no entra | |
| AUT-05 | Escribir un correo que no existe | **Mismo** mensaje que AUT-04 (no revela si el correo existe) | |
| AUT-06 | Dejar los campos vacíos | Mensaje pidiendo correo y contraseña | |
| AUT-07 | En phpMyAdmin abrir la tabla `usuarios` | `password_hash` empieza con `$2y$`; no hay contraseñas en texto plano | |
| AUT-08 | Sin sesión, escribir en la barra `admin/clientes/index.php` | Redirige a `login.php` | |
| AUT-09 | Pulsar **Salir** y luego usar la flecha «atrás» a una página del panel | Redirige al login (la sesión se cerró) | |

### 3.2 Usuarios y roles (administrador)

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| USU-01 | Menú *Usuarios* | Lista todas las cuentas con su rol (administrador / cliente) | |
| USU-02 | Buscar «valery» | Solo aparece Valery Machado Nuñez | |
| USU-03 | *Nuevo administrador* con nombre vacío, correo «malo» y clave «123» | Muestra los 3 errores y no guarda | |
| USU-04 | Crear administrador con el correo `admin@tonyperformance.com` | Error «Ya existe un usuario con ese correo» | |
| USU-05 | Crear administrador con datos válidos | Aparece en la lista con rol *administrador* y clave con hash en la BD | |
| USU-06 | Cerrar sesión y entrar con el administrador nuevo | Inicia sesión y ve el dashboard | |
| USU-07 | Editar ese administrador dejando la clave vacía | Se guarda el nombre; la clave anterior sigue funcionando | |
| USU-08 | En la lista, buscar su propia cuenta | No tiene botón *Eliminar* | |
| USU-09 | Eliminar el administrador de prueba | Desaparece de la lista | |
| USU-10 | Entrar como cliente y abrir `admin/usuarios/index.php` | Lo devuelve a su portal con un aviso | |

### 3.3 Clientes (CRUD)

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| CLI-01 | Menú *Clientes* | Lista con estado de membresía (vigente, por vencer, vencida, sin membresía) | |
| CLI-02 | Buscar por nombre, correo o cédula | Filtra los resultados | |
| CLI-03 | *Nuevo cliente* con todo vacío | Errores de validación; no guarda | |
| CLI-04 | Crear cliente con una cédula ya existente | Error de cédula duplicada | |
| CLI-05 | Crear cliente válido | Aparece en la lista; en `usuarios` se creó la cuenta con rol `cliente` | |
| CLI-06 | Iniciar sesión con el cliente recién creado | Entra a su portal (sin membresía ni rutinas aún) | |
| CLI-07 | Editar el teléfono del cliente | El cambio se ve en la ficha | |
| CLI-08 | Eliminar el cliente de prueba | Se borra con su usuario, membresías, pagos y rutinas (cascada) | |

### 3.4 Membresías y renovación

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| MEM-01 | Menú *Membresías* | Lista con estado calculado y filtro por estado | |
| MEM-02 | Dashboard → «Renovar» de un cliente **vencido** | La nueva membresía empieza **hoy** | |
| MEM-03 | Renovar a un cliente **vigente** | La nueva empieza el **día siguiente** a su último día de acceso | |
| MEM-04 | Plan de 30 días que inicia el 1/11 | Termina el 30/11 (`inicio + duración − 1`) | |
| MEM-05 | Cambiar el plan en el formulario | El monto y la fecha de fin se recalculan solos | |
| MEM-06 | Después de renovar, ver la ficha del cliente | La membresía anterior sigue en el historial; la nueva indica que renueva a la anterior | |
| MEM-07 | Dashboard | Por vencer = activas con 7 días o menos | |

### 3.5 Pagos

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| PAG-01 | Registrar una membresía con pago en efectivo | Se crea el pago con el monto del plan y la fecha de hoy | |
| PAG-02 | Elegir SINPE sin escribir comprobante | Error: el comprobante es obligatorio | |
| PAG-03 | Elegir SINPE con comprobante | Se guarda; el pago muestra qué administrador cobró | |
| PAG-04 | Menú *Pagos* → filtrar por método | Solo muestra los pagos de ese método | |
| PAG-05 | Buscar por número de comprobante | Encuentra el pago | |

### 3.6 Planes y ejercicios

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| PLA-01 | Crear un plan con duración 0 o precio negativo | Error de validación | |
| PLA-02 | Crear un plan válido | Aparece en la lista y en la página principal pública | |
| PLA-03 | Intentar eliminar un plan que ya se vendió | No se elimina y muestra aviso | |
| EJE-01 | Crear, editar y eliminar un ejercicio sin uso | Funciona | |
| EJE-02 | Eliminar un ejercicio usado en una rutina | No se elimina y muestra aviso | |

### 3.7 Asignación de rutinas

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| RUT-01 | *Rutinas* → nueva rutina para un cliente, con ejercicios en 2 días distintos | Se guarda; el detalle agrupa los ejercicios por día | |
| RUT-02 | Dejar filas de ejercicios vacías | Se ignoran | |
| RUT-03 | Guardar una rutina sin nombre | Error de validación | |
| RUT-04 | Eliminar una rutina | Se borra junto con sus ejercicios | |

### 3.8 Portal del cliente y seguridad

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| POR-01 | Entrar como cliente → *Mi membresía* | Muestra solo sus membresías con fechas y estado | |
| POR-02 | *Mis pagos* | Solo sus pagos | |
| POR-03 | *Mis rutinas* → abrir una | Ve el detalle de su rutina por día | |
| POR-04 | Cambiar en la URL `cliente/rutina.php?id=` por la rutina de otro cliente | Respuesta 404; no se muestran datos ajenos | |
| POR-05 | Como cliente, escribir `admin/index.php` | Lo devuelve a su portal | |
| POR-06 | Como administrador, escribir `cliente/index.php` | Lo devuelve al dashboard | |
| SEG-01 | En el buscador de clientes escribir `' OR '1'='1` | No devuelve todos los clientes ni da error SQL | |
| SEG-02 | Crear un cliente con nombre `<script>alert(1)</script>` | Se muestra como texto; no se ejecuta | |
| SEG-03 | Abrir `admin/clientes/eliminar.php?id=1` escribiéndolo en la barra (GET) | No elimina nada | |

### 3.9 Diseño

| ID | Pasos | Resultado esperado | Res. |
|---|---|---|---|
| DIS-01 | Reducir la ventana a ancho de celular | El menú se vuelve «hamburguesa» y las tablas se desplazan sin romper la página | |

## 4. Resultados

Al terminar el recorrido se anota la fecha de la prueba, las columnas *Res.* completas y las
fallas encontradas con su corrección.
