-- =============================================================
--  GYM RR — Base de Datos del Sistema de Gestión
--  Base de datos : sistema_gym
--  Servidor      : MySQL 8.0 / MariaDB
--  Versión       : 2026
--  Descripción   : Estructura completa con datos de prueba.
-- =============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
START TRANSACTION;

-- -------------------------------------------------------------
-- Crear y seleccionar la base de datos
-- -------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `sistema_gym`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sistema_gym`;

-- =============================================================
--  TABLAS PRINCIPALES
-- =============================================================

-- -------------------------------------------------------------
--  USUARIOS  (acceso al sistema: Admin / Recepcionista / Entrenador)
-- -------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id_usuario`      INT          NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(150) NOT NULL,
  `usuario`         VARCHAR(50)  NOT NULL,
  `password`        VARCHAR(255) NOT NULL,
  `rol`             ENUM('Administrador','Recepcionista','Entrenador') DEFAULT 'Recepcionista',
  `estado`          ENUM('activo','inactivo') DEFAULT 'activo',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  MEMBRESIAS  (planes: Mensual / Trimestral / Anual)
-- -------------------------------------------------------------
CREATE TABLE `membresias` (
  `id_membresia`   INT           NOT NULL AUTO_INCREMENT,
  `nombre`         VARCHAR(50)   NOT NULL,
  `duracion_meses` INT           NOT NULL,
  `precio`         DECIMAL(10,2) NOT NULL,
  `descripcion`    TEXT,
  `estado`         ENUM('activo','inactivo') DEFAULT 'activo',
  PRIMARY KEY (`id_membresia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `membresias` (`nombre`, `duracion_meses`, `precio`, `descripcion`) VALUES
  ('Mensual',     1,  500.00, 'Acceso por 1 mes completo'),
  ('Trimestral',  3, 1350.00, 'Acceso por 3 meses (10% de ahorro)'),
  ('Anual',      12, 4800.00, 'Acceso por 12 meses (20% de ahorro)');

-- -------------------------------------------------------------
--  ENTRENADORES
-- -------------------------------------------------------------
CREATE TABLE `entrenadores` (
  `id_entrenador`     INT           NOT NULL AUTO_INCREMENT,
  `nombre`            VARCHAR(100)  NOT NULL,
  `especialidad`      VARCHAR(100)  DEFAULT NULL,
  `telefono`          VARCHAR(20)   DEFAULT NULL,
  `correo`            VARCHAR(100)  DEFAULT NULL,
  `fecha_contratacion`DATE          DEFAULT NULL,
  `tarifa_comision`   DECIMAL(10,2) DEFAULT '0.00',
  `turno`             ENUM('Matutino','Vespertino','Completo') DEFAULT 'Completo',
  `estado`            ENUM('activo','inactivo') DEFAULT 'activo',
  PRIMARY KEY (`id_entrenador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `entrenadores` (`nombre`, `especialidad`, `telefono`, `correo`, `fecha_contratacion`, `tarifa_comision`, `turno`, `estado`) VALUES
  ('Paquito Cortez',            'Pesas',      '8341234567', 'test@gym.com',           '2026-03-07', 50.00, 'Matutino',   'activo'),
  ('Jorge Daniel Torres Ramos', 'Calistenia', '8341546152', 'jorge.torr@gmail.com',   '2025-03-08', 45.00, 'Vespertino', 'activo');

-- -------------------------------------------------------------
--  SOCIOS
-- -------------------------------------------------------------
CREATE TABLE `socios` (
  `id_socio`            INT          NOT NULL AUTO_INCREMENT,
  `nombre`              VARCHAR(100) NOT NULL,
  `apellido`            VARCHAR(100) NOT NULL,
  `foto`                VARCHAR(255) DEFAULT NULL,
  `telefono`            VARCHAR(20)  DEFAULT NULL,
  `contacto_emergencia` VARCHAR(255) DEFAULT NULL,
  `correo`              VARCHAR(100) DEFAULT NULL,
  `direccion`           TEXT,
  `fecha_nacimiento`    DATE         DEFAULT NULL,
  `fecha_registro`      DATE         DEFAULT NULL,
  `fecha_vencimiento`   DATE         DEFAULT NULL,
  `id_membresia`        INT          DEFAULT NULL,
  `estado`              ENUM('activo','inactivo','vencido') DEFAULT 'activo',
  `id_entrenador`       INT          DEFAULT NULL,
  `qr_codigo`           VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_socio`),
  UNIQUE KEY `qr_codigo` (`qr_codigo`),
  KEY `fk_socio_membresia`  (`id_membresia`),
  KEY `fk_socio_entrenador` (`id_entrenador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `socios` (`nombre`, `apellido`, `telefono`, `contacto_emergencia`, `correo`, `direccion`, `fecha_nacimiento`, `fecha_registro`, `fecha_vencimiento`, `id_membresia`, `estado`, `id_entrenador`) VALUES
  ('Rubí María',    'Cobos Ramos',         '8341543050', NULL, 'cobosrubi64@gmail.com',  '0 y 1 Servando Canales',    '2004-10-20', '2026-03-07', '2026-04-07', 1, 'activo', NULL),
  ('Jesús Emmanuel','López Zúñiga',         '8342567889', NULL, 'chuy.lopez@live.com',   'Andromeda 534',              '2005-08-15', '2026-03-08', '2026-06-08', 2, 'activo', 2),
  ('Esmeralda',     'Ramos Cortez',         '8342688449', NULL, 'esmeramos23@hotmail.com','Julián de la Cerda 809',    '1973-02-08', '2026-03-08', '2026-04-08', 3, 'activo', NULL),
  ('Maximino',      'Orozco Betancourt',    '8342701650', NULL, 'maxorozco@gmail.com',   'Norias de los Angeles 431', '1961-06-11', '2026-03-08', '2026-06-08', 2, 'activo', 2);

-- =============================================================
--  TABLAS DE CLASES Y RUTINAS
-- =============================================================

CREATE TABLE `clases` (
  `id_clase`     INT          NOT NULL AUTO_INCREMENT,
  `nombre_clase` VARCHAR(100) DEFAULT NULL,
  `descripcion`  VARCHAR(255) DEFAULT NULL,
  `horario`      VARCHAR(100) DEFAULT NULL,
  `capacidad`    INT          DEFAULT NULL,
  `estado`       VARCHAR(20)  DEFAULT 'activo',
  PRIMARY KEY (`id_clase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ejercicios` (
  `id_ejercicio`  INT          NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(150) NOT NULL,
  `grupo_muscular`VARCHAR(100) DEFAULT NULL,
  `descripcion`   TEXT,
  PRIMARY KEY (`id_ejercicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rutinas` (
  `id_rutina`     INT          NOT NULL AUTO_INCREMENT,
  `nombre_rutina` VARCHAR(150) NOT NULL,
  `descripcion`   TEXT,
  `nivel`         VARCHAR(50)  DEFAULT NULL,
  PRIMARY KEY (`id_rutina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  TABLAS RELACIONALES (muchos a muchos)
-- =============================================================

CREATE TABLE `entrenador_clase` (
  `id_relacion`  INT NOT NULL AUTO_INCREMENT,
  `id_entrenador`INT NOT NULL,
  `id_clase`     INT NOT NULL,
  PRIMARY KEY (`id_relacion`),
  KEY `idx_ec_entrenador` (`id_entrenador`),
  KEY `idx_ec_clase`      (`id_clase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `membresia_clase` (
  `id_relacion` INT NOT NULL AUTO_INCREMENT,
  `id_membresia`INT NOT NULL,
  `id_clase`    INT NOT NULL,
  PRIMARY KEY (`id_relacion`),
  KEY `idx_mc_membresia` (`id_membresia`),
  KEY `idx_mc_clase`     (`id_clase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rutina_ejercicio` (
  `id_relacion` INT NOT NULL AUTO_INCREMENT,
  `id_rutina`   INT NOT NULL,
  `id_ejercicio`INT NOT NULL,
  `orden`       INT DEFAULT '0',
  `series`      INT DEFAULT NULL,
  `repeticiones`INT DEFAULT NULL,
  PRIMARY KEY (`id_relacion`),
  KEY `idx_re_rutina`    (`id_rutina`),
  KEY `idx_re_ejercicio` (`id_ejercicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `socio_clase` (
  `id_registro`       INT  NOT NULL AUTO_INCREMENT,
  `id_socio`          INT  NOT NULL,
  `id_clase`          INT  NOT NULL,
  `fecha_inscripcion` DATE NOT NULL,
  `estado`            ENUM('inscrito','cancelado','asistio') DEFAULT 'inscrito',
  PRIMARY KEY (`id_registro`),
  KEY `idx_sc_socio` (`id_socio`),
  KEY `idx_sc_clase` (`id_clase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `socio_rutina` (
  `id_asignacion`INT  NOT NULL AUTO_INCREMENT,
  `id_socio`     INT  NOT NULL,
  `id_rutina`    INT  NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin`    DATE DEFAULT NULL,
  PRIMARY KEY (`id_asignacion`),
  KEY `idx_sr_socio`   (`id_socio`),
  KEY `idx_sr_rutina`  (`id_rutina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  MODULO FINANCIERO Y SEGUIMIENTO
-- =============================================================

CREATE TABLE `pagos` (
  `id_pago`     INT           NOT NULL AUTO_INCREMENT,
  `id_socio`    INT           DEFAULT NULL,
  `id_membresia`INT           DEFAULT NULL,
  `monto`       DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `fecha_pago`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metodo_pago` VARCHAR(50)   DEFAULT NULL,
  `referencia`  VARCHAR(100)  DEFAULT NULL,
  `estado`      ENUM('pagado','pendiente','reembolsado') NOT NULL DEFAULT 'pagado',
  PRIMARY KEY (`id_pago`),
  KEY `idx_pago_socio`     (`id_socio`),
  KEY `idx_pago_membresia` (`id_membresia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `socios_membresias` (
  `id_registro` INT  NOT NULL AUTO_INCREMENT,
  `id_socio`    INT  NOT NULL,
  `id_membresia`INT  NOT NULL,
  `fecha_inicio`DATE NOT NULL,
  `fecha_fin`   DATE DEFAULT NULL,
  `estado`      ENUM('activa','vencida','cancelada') NOT NULL DEFAULT 'activa',
  `id_pago`     INT  DEFAULT NULL,
  PRIMARY KEY (`id_registro`),
  KEY `idx_sm_socio`     (`id_socio`),
  KEY `idx_sm_membresia` (`id_membresia`),
  KEY `idx_sm_pago`      (`id_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `socios_membresias` (`id_socio`, `id_membresia`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
  (1, 1, '2026-03-07', '2026-04-07', 'activa'),
  (2, 2, '2026-03-08', '2026-06-08', 'activa'),
  (4, 2, '2026-03-08', '2026-06-08', 'activa'),
  (3, 3, '2026-03-08', '2026-04-08', 'activa');

CREATE TABLE `asistencia` (
  `id_asistencia`INT  NOT NULL AUTO_INCREMENT,
  `id_socio`     INT  NOT NULL,
  `fecha`        DATE NOT NULL,
  `hora_entrada` TIME DEFAULT NULL,
  `hora_salida`  TIME DEFAULT NULL,
  `medio`        VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  KEY `idx_asistencia_socio` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reportes` (
  `id_reporte`       INT      NOT NULL AUTO_INCREMENT,
  `tipo_reporte`     VARCHAR(100) NOT NULL,
  `fecha_generacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `generado_por`     INT      DEFAULT NULL,
  `parametros`       JSON     DEFAULT NULL,
  PRIMARY KEY (`id_reporte`),
  KEY `fk_reporte_usuario` (`generado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bitacora` (
  `id_bitacora`INT          NOT NULL AUTO_INCREMENT,
  `id_usuario` INT          DEFAULT NULL,
  `accion`     VARCHAR(150) NOT NULL,
  `modulo`     VARCHAR(80)  DEFAULT NULL,
  `descripcion`TEXT,
  `fecha`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_bitacora`),
  KEY `idx_bit_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  CLAVES FORÁNEAS
-- =============================================================

ALTER TABLE `socios`
  ADD CONSTRAINT `fk_socio_membresia`  FOREIGN KEY (`id_membresia`)  REFERENCES `membresias`   (`id_membresia`),
  ADD CONSTRAINT `fk_socio_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenadores` (`id_entrenador`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `bitacora`
  ADD CONSTRAINT `fk_bit_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `entrenador_clase`
  ADD CONSTRAINT `fk_ec_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenadores` (`id_entrenador`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ec_clase`      FOREIGN KEY (`id_clase`)      REFERENCES `clases`       (`id_clase`)      ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `membresia_clase`
  ADD CONSTRAINT `fk_mc_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mc_clase`     FOREIGN KEY (`id_clase`)     REFERENCES `clases`     (`id_clase`)     ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `rutina_ejercicio`
  ADD CONSTRAINT `fk_re_rutina`    FOREIGN KEY (`id_rutina`)    REFERENCES `rutinas`    (`id_rutina`)    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_re_ejercicio` FOREIGN KEY (`id_ejercicio`) REFERENCES `ejercicios` (`id_ejercicio`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `socio_clase`
  ADD CONSTRAINT `fk_sc_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sc_clase` FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `socio_rutina`
  ADD CONSTRAINT `fk_sr_socio`   FOREIGN KEY (`id_socio`)  REFERENCES `socios`  (`id_socio`)  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sr_rutina`  FOREIGN KEY (`id_rutina`) REFERENCES `rutinas` (`id_rutina`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_socio`     FOREIGN KEY (`id_socio`)     REFERENCES `socios`     (`id_socio`)     ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pago_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `socios_membresias`
  ADD CONSTRAINT `fk_sm_socio`     FOREIGN KEY (`id_socio`)     REFERENCES `socios`     (`id_socio`)     ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sm_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sm_pago`      FOREIGN KEY (`id_pago`)      REFERENCES `pagos`      (`id_pago`)      ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reporte_usuario` FOREIGN KEY (`generado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

-- =============================================================
--  FIN DEL SCRIPT — GYM RR © 2026
-- =============================================================
