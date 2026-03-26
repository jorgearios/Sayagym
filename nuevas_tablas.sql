-- ============================================================
-- SAYAGYM — Nuevas tablas y ajustes para módulos 2-7
-- Ejecutar una sola vez en phpMyAdmin o desde terminal
-- ============================================================

-- Módulo 1: Columna password en socios (si aún no existe)
ALTER TABLE `socios`
  ADD COLUMN IF NOT EXISTS `password` VARCHAR(255) DEFAULT NULL AFTER `correo`;

-- Módulo 1: Columna qr_codigo en socios (si aún no existe)
ALTER TABLE `socios`
  ADD COLUMN IF NOT EXISTS `qr_codigo` VARCHAR(255) DEFAULT NULL;

-- ──────────────────────────────────────────────────────────
-- Módulo 7: Evaluaciones Físicas
-- ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `evaluaciones_fisicas` (
  `id_evaluacion`     int(11)         NOT NULL AUTO_INCREMENT,
  `id_socio`          int(11)         NOT NULL,
  `id_entrenador`     int(11)         DEFAULT NULL,
  `fecha`             date            NOT NULL,
  `peso`              decimal(5,2)    DEFAULT NULL COMMENT 'kg',
  `altura`            decimal(5,2)    DEFAULT NULL COMMENT 'cm',
  `imc`               decimal(5,2)    DEFAULT NULL COMMENT 'calculado',
  `porcentaje_grasa`  decimal(5,2)    DEFAULT NULL,
  `masa_muscular`     decimal(5,2)    DEFAULT NULL COMMENT 'kg',
  `pecho`             decimal(5,2)    DEFAULT NULL COMMENT 'cm',
  `cintura`           decimal(5,2)    DEFAULT NULL COMMENT 'cm',
  `cadera`            decimal(5,2)    DEFAULT NULL COMMENT 'cm',
  `bicep`             decimal(5,2)    DEFAULT NULL COMMENT 'cm',
  `muslo`             decimal(5,2)    DEFAULT NULL COMMENT 'cm',
  `objetivo`          varchar(100)    DEFAULT NULL,
  `notas`             text            DEFAULT NULL,
  PRIMARY KEY (`id_evaluacion`),
  KEY `idx_eval_socio`       (`id_socio`),
  KEY `idx_eval_entrenador`  (`id_entrenador`),
  CONSTRAINT `fk_eval_socio`
    FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_eval_entrenador`
    FOREIGN KEY (`id_entrenador`) REFERENCES `entrenadores` (`id_entrenador`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
