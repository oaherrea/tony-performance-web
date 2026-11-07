-- ============================================================================
--  Tony Performance Center Web — Base de datos (estructura + datos de ejemplo)
--  Proyecto Final Integrador · Programación V · UACA
--
--  Importar desde phpMyAdmin (pestaña "Importar") o con:
--      mysql -u root < database.sql
--  El archivo tiene 2 partes: (1) estructura de las tablas y (2) datos de ejemplo.
-- ============================================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS tony_performance_web;
CREATE DATABASE tony_performance_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tony_performance_web;

-- ---- usuarios: cuentas para iniciar sesión (administrador o cliente) --------
CREATE TABLE usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,          -- resultado de password_hash(), nunca la clave
    rol           VARCHAR(20)  NOT NULL,          -- 'administrador' o 'cliente'
    creado_en     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- clientes: datos del cliente (1 usuario <-> 1 cliente) ------------------
CREATE TABLE clientes (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id       INT NOT NULL UNIQUE,
    cedula           VARCHAR(20) NOT NULL UNIQUE,
    telefono         VARCHAR(20),
    fecha_nacimiento DATE,
    objetivo         VARCHAR(150),
    creado_en        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---- tipos_membresia: catálogo de planes (nombre, duración, precio) ---------
CREATE TABLE tipos_membresia (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(60)   NOT NULL UNIQUE,
    descripcion   VARCHAR(200),
    duracion_dias INT           NOT NULL,
    precio        DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- ---- membresias: período que contrata un cliente ----------------------------
--  REGLAS DE VENCIMIENTO
--    fecha_fin = fecha_inicio + (duracion_dias - 1)   -> último día con acceso
--    activa    : fecha_inicio <= hoy <= fecha_fin
--    vencida   : hoy > fecha_fin
--    programada: hoy < fecha_inicio
--  RENOVACIÓN: se inserta una fila NUEVA; renovacion_de guarda el id de la anterior.
CREATE TABLE membresias (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id        INT  NOT NULL,
    tipo_membresia_id INT  NOT NULL,
    fecha_inicio      DATE NOT NULL,
    fecha_fin         DATE NOT NULL,
    renovacion_de     INT  NULL,
    creado_en         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id)        REFERENCES clientes(id)        ON DELETE CASCADE,
    FOREIGN KEY (tipo_membresia_id) REFERENCES tipos_membresia(id),
    FOREIGN KEY (renovacion_de)     REFERENCES membresias(id)      ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---- pagos: cobro de cada membresía -----------------------------------------
CREATE TABLE pagos (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    membresia_id   INT           NOT NULL,
    monto          DECIMAL(10,2) NOT NULL,
    metodo         VARCHAR(20)   NOT NULL,        -- efectivo, tarjeta, sinpe, transferencia
    fecha_pago     DATE          NOT NULL,
    referencia     VARCHAR(60),
    registrado_por INT NULL,                      -- usuario (administrador) que cobró
    creado_en      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membresia_id)   REFERENCES membresias(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---- ejercicios: catálogo ---------------------------------------------------
CREATE TABLE ejercicios (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(80) NOT NULL UNIQUE,
    grupo_muscular VARCHAR(40) NOT NULL,
    descripcion    VARCHAR(300)
) ENGINE=InnoDB;

-- ---- rutinas: plan de entrenamiento asignado a un cliente -------------------
CREATE TABLE rutinas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id       INT         NOT NULL,
    nombre           VARCHAR(80) NOT NULL,
    objetivo         VARCHAR(150),
    fecha_asignacion DATE        NOT NULL,
    creado_por       INT NULL,
    creado_en        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---- rutina_ejercicios: relación muchos a muchos rutinas <-> ejercicios -----
CREATE TABLE rutina_ejercicios (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    rutina_id    INT         NOT NULL,
    ejercicio_id INT         NOT NULL,
    dia          VARCHAR(10) NOT NULL,            -- Lunes ... Domingo
    series       INT         NOT NULL,
    repeticiones VARCHAR(20) NOT NULL,            -- texto: "8-10", "45 s"
    notas        VARCHAR(200),
    FOREIGN KEY (rutina_id)    REFERENCES rutinas(id)    ON DELETE CASCADE,
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id)
) ENGINE=InnoDB;

-- ============================================================================
--  PARTE 2: DATOS DE EJEMPLO
--
--  Usuarios de prueba:
--    administrador:  admin@tonyperformance.com    /  Admin123!
--    cliente:        cliente@tonyperformance.com  /  Cliente123!
--    (maria@, jose@, laura@, andres@tonyperformance.com  /  Cliente123!)
--
--  Las fechas de las membresías son RELATIVAS a hoy (CURDATE()), así siempre
--  hay ejemplos de membresías activas, por vencer y vencidas.
-- ============================================================================

-- ---- Usuarios ---------------------------------------------------------------
INSERT INTO usuarios (id, nombre, email, password_hash, rol) VALUES
(1, 'Tony Administrador', 'admin@tonyperformance.com',   '$2y$10$ayXpoVlmuU0qrOzZu5/xEO4qnn/MrLILF4GLB6iqIE56SQiXY3dlS', 'administrador'),
(2, 'Carlos Mora Vargas',  'cliente@tonyperformance.com', '$2y$10$mEgYSZ/Cnn82FEBR8sG3heNtP7kfyBbVI81upgsASqbE0lv2ute2G', 'cliente'),
(3, 'María Fernández Solano', 'maria@tonyperformance.com',  '$2y$10$mEgYSZ/Cnn82FEBR8sG3heNtP7kfyBbVI81upgsASqbE0lv2ute2G', 'cliente'),
(4, 'José Pérez Castro',   'jose@tonyperformance.com',    '$2y$10$mEgYSZ/Cnn82FEBR8sG3heNtP7kfyBbVI81upgsASqbE0lv2ute2G', 'cliente'),
(5, 'Laura Jiménez Araya', 'laura@tonyperformance.com',   '$2y$10$mEgYSZ/Cnn82FEBR8sG3heNtP7kfyBbVI81upgsASqbE0lv2ute2G', 'cliente'),
(6, 'Andrés Rojas Quesada','andres@tonyperformance.com',  '$2y$10$mEgYSZ/Cnn82FEBR8sG3heNtP7kfyBbVI81upgsASqbE0lv2ute2G', 'cliente');

-- ---- Clientes ---------------------------------------------------------------
INSERT INTO clientes (id, usuario_id, cedula, telefono, fecha_nacimiento, objetivo) VALUES
(1, 2, '1-1234-0567', '8888-1111', '1998-03-14', 'Ganar masa muscular'),
(2, 3, '7-0222-0333', '8777-2222', '2000-07-02', 'Perder grasa y tonificar'),
(3, 4, '7-0155-0912', '8666-3333', '1995-11-23', 'Mejorar condición física'),
(4, 5, '1-1456-0788', '8555-4444', '2001-01-30', 'Fuerza y resistencia'),
(5, 6, '7-0301-0644', '8444-5555', '1999-09-09', 'Preparación para competencia');

-- ---- Tipos de membresía -----------------------------------------------------
INSERT INTO tipos_membresia (id, nombre, descripcion, duracion_dias, precio) VALUES
(1, 'Mensual',      'Acceso completo por 30 días',   30,  25000.00),
(2, 'Trimestral',   'Acceso completo por 90 días',   90,  65000.00),
(3, 'Semestral',    'Acceso completo por 180 días',  180, 120000.00),
(4, 'Anual',        'Acceso completo por 365 días',  365, 220000.00),
(5, 'Pase semanal', 'Acceso por 7 días',             7,   8000.00);

-- ---- Membresías (fecha_fin = fecha_inicio + duración - 1) -------------------
INSERT INTO membresias (id, cliente_id, tipo_membresia_id, fecha_inicio, fecha_fin, renovacion_de) VALUES
(1, 1, 1, CURDATE() - INTERVAL 39 DAY, CURDATE() - INTERVAL 10 DAY, NULL),  -- Carlos: anterior (vencida)
(2, 1, 1, CURDATE() - INTERVAL 9 DAY,  CURDATE() + INTERVAL 20 DAY, 1),     -- Carlos: renovación (activa)
(3, 2, 1, CURDATE() - INTERVAL 27 DAY, CURDATE() + INTERVAL 2 DAY,  NULL),  -- María: por vencer
(4, 3, 1, CURDATE() - INTERVAL 41 DAY, CURDATE() - INTERVAL 12 DAY, NULL),  -- José: vencida
(5, 4, 1, CURDATE() - INTERVAL 75 DAY, CURDATE() - INTERVAL 46 DAY, NULL),  -- Laura: anterior (vencida)
(6, 4, 2, CURDATE() - INTERVAL 45 DAY, CURDATE() + INTERVAL 44 DAY, 5);     -- Laura: renovación trimestral

-- ---- Pagos ------------------------------------------------------------------
INSERT INTO pagos (membresia_id, monto, metodo, fecha_pago, referencia, registrado_por) VALUES
(1, 25000.00, 'efectivo',      CURDATE() - INTERVAL 39 DAY, NULL,          1),
(2, 25000.00, 'sinpe',         CURDATE() - INTERVAL 9 DAY,  'SINPE-48213', 1),
(3, 25000.00, 'tarjeta',       CURDATE() - INTERVAL 27 DAY, 'POS-001275',  1),
(4, 25000.00, 'efectivo',      CURDATE() - INTERVAL 41 DAY, NULL,          1),
(5, 25000.00, 'efectivo',      CURDATE() - INTERVAL 75 DAY, NULL,          1),
(6, 65000.00, 'transferencia', CURDATE() - INTERVAL 45 DAY, 'TRF-99120',   1);

-- ---- Ejercicios -------------------------------------------------------------
INSERT INTO ejercicios (id, nombre, grupo_muscular, descripcion) VALUES
(1,  'Press de banca',               'Pecho',   'Press con barra en banco plano'),
(2,  'Press inclinado con mancuernas','Pecho',  'Énfasis en la parte superior del pecho'),
(3,  'Aperturas con mancuernas',     'Pecho',   'Movimiento de apertura en banco plano'),
(4,  'Dominadas',                    'Espalda', 'Agarre prono, rango completo'),
(5,  'Remo con barra',               'Espalda', 'Torso inclinado, barra hacia el abdomen'),
(6,  'Jalón al pecho',               'Espalda', 'Polea alta con agarre ancho'),
(7,  'Sentadilla libre',             'Pierna',  'Barra en espalda alta, profundidad paralela'),
(8,  'Prensa de piernas',            'Pierna',  'Máquina de prensa a 45°'),
(9,  'Peso muerto rumano',           'Pierna',  'Énfasis en femorales y glúteo'),
(10, 'Zancadas',                     'Pierna',  'Con mancuernas, alternando piernas'),
(11, 'Press militar',                'Hombro',  'De pie con barra o mancuernas'),
(12, 'Elevaciones laterales',        'Hombro',  'Mancuernas, control en la bajada'),
(13, 'Curl de bíceps con barra',     'Brazo',   'Barra Z, sin balanceo'),
(14, 'Extensión de tríceps en polea','Brazo',   'Cuerda o barra recta'),
(15, 'Plancha abdominal',            'Core',    'Mantener la posición con el core activado'),
(16, 'Crunch en polea alta',         'Core',    'Flexión de tronco con resistencia'),
(17, 'Cinta caminadora',             'Cardio',  'Caminata inclinada o trote suave');

-- ---- Rutinas ----------------------------------------------------------------
INSERT INTO rutinas (id, cliente_id, nombre, objetivo, fecha_asignacion, creado_por) VALUES
(1, 1, 'Hipertrofia Full Body', 'Ganar masa muscular',        CURDATE() - INTERVAL 9 DAY, 1),
(2, 2, 'Tonificación y cardio', 'Perder grasa y tonificar',   CURDATE() - INTERVAL 20 DAY, 1);

INSERT INTO rutina_ejercicios (rutina_id, ejercicio_id, dia, series, repeticiones, notas) VALUES
(1, 1,  'Lunes',     4, '8-10', 'Descanso 90 s entre series'),
(1, 5,  'Lunes',     4, '10',   NULL),
(1, 7,  'Miércoles', 4, '8',    'Profundidad paralela'),
(1, 9,  'Miércoles', 3, '10',   NULL),
(1, 15, 'Miércoles', 3, '45 s', 'Mantener abdomen firme'),
(1, 11, 'Viernes',   3, '10',   NULL),
(1, 6,  'Viernes',   3, '12',   NULL),
(1, 13, 'Viernes',   3, '12',   'Sin balanceo'),
(2, 10, 'Martes',    3, '12',   'Con mancuernas ligeras'),
(2, 12, 'Martes',    3, '15',   NULL),
(2, 17, 'Martes',    1, '20 min','Inclinación 6%'),
(2, 8,  'Jueves',    4, '12',   NULL),
(2, 16, 'Jueves',    3, '15',   NULL),
(2, 17, 'Sábado',    1, '30 min','Ritmo constante');
