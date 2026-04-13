-- ============================================================
-- SAYAGYM — Columnas nuevas adaptadas del proyecto de referencia
-- Ejecutar en phpMyAdmin una sola vez
-- ============================================================

-- Columna género en socios (para cálculo automático de calorías)
ALTER TABLE `socios`
  ADD COLUMN IF NOT EXISTS `genero`
    ENUM('Masculino','Femenino','Otro') NOT NULL DEFAULT 'Masculino'
    AFTER `apellido`;

-- Columna límite calórico diario (reemplaza la tabla socio_calorias_limite como columna directa)
ALTER TABLE `socios`
  ADD COLUMN IF NOT EXISTS `limite_calorias`
    INT NOT NULL DEFAULT 2000
    COMMENT 'kcal/día — calculado por edad y género al crear el socio';

-- Columna QR como URL de API (más simple que librería)
-- Ya existe qr_codigo en el script base, solo aseguramos que no tenga UNIQUE si ya hay datos vacíos
-- (ignorar si ya existe)
ALTER TABLE `socios`
  ADD COLUMN IF NOT EXISTS `qr_codigo` VARCHAR(255) DEFAULT NULL;

-- Tabla pagos: agregar columna concepto si no existe (usada por el proyecto de referencia)
ALTER TABLE `pagos`
  ADD COLUMN IF NOT EXISTS `concepto`
    VARCHAR(150) DEFAULT 'Pago de membresía'
    AFTER `referencia`;

-- ============================================================
-- VERIFICACIÓN — corre esto para confirmar que quedó bien:
-- SHOW COLUMNS FROM socios;
-- SHOW COLUMNS FROM pagos;
-- ============================================================
