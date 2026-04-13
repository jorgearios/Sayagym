-- ============================================================
-- SAYAGYM — Módulo Punto de Venta e Inventario
-- Ejecutar UNA VEZ en phpMyAdmin
-- ============================================================

-- ── Productos (inventario) ────────────────────────────────
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto`   int(11)         NOT NULL AUTO_INCREMENT,
  `nombre`        varchar(150)    NOT NULL,
  `categoria`     varchar(80)     DEFAULT 'General'
                                  COMMENT 'Suplementos | Bebidas | Accesorios | Ropa | General',
  `descripcion`   text            DEFAULT NULL,
  `precio_costo`  decimal(10,2)   NOT NULL DEFAULT 0.00,
  `precio_venta`  decimal(10,2)   NOT NULL DEFAULT 0.00,
  `stock`         int(11)         NOT NULL DEFAULT 0,
  `stock_minimo`  int(11)         NOT NULL DEFAULT 5
                                  COMMENT 'Alerta cuando stock <= este valor',
  `estado`        enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_producto`),
  KEY `idx_prod_categoria` (`categoria`),
  KEY `idx_prod_estado`    (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de productos del gimnasio';

-- ── Ventas (cabecera) ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ventas` (
  `id_venta`      int(11)         NOT NULL AUTO_INCREMENT,
  `id_usuario`    int(11)         DEFAULT NULL  COMMENT 'Cajero que realizó la venta',
  `id_socio`      int(11)         DEFAULT NULL  COMMENT 'Socio comprador (opcional)',
  `subtotal`      decimal(10,2)   NOT NULL DEFAULT 0.00,
  `descuento`     decimal(10,2)   NOT NULL DEFAULT 0.00,
  `total`         decimal(10,2)   NOT NULL DEFAULT 0.00,
  `metodo_pago`   varchar(50)     NOT NULL DEFAULT 'Efectivo'
                                  COMMENT 'Efectivo | Tarjeta | Transferencia',
  `nota`          varchar(255)    DEFAULT NULL,
  `fecha`         datetime        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_venta`),
  KEY `idx_venta_usuario` (`id_usuario`),
  KEY `idx_venta_socio`   (`id_socio`),
  KEY `idx_venta_fecha`   (`fecha`),
  CONSTRAINT `fk_venta_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_venta_socio`
    FOREIGN KEY (`id_socio`) REFERENCES `socios`(`id_socio`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cabecera de cada venta realizada';

-- ── Detalle de ventas (ítems) ─────────────────────────────
CREATE TABLE IF NOT EXISTS `venta_detalle` (
  `id_detalle`    int(11)         NOT NULL AUTO_INCREMENT,
  `id_venta`      int(11)         NOT NULL,
  `id_producto`   int(11)         NOT NULL,
  `cantidad`      int(11)         NOT NULL DEFAULT 1,
  `precio_unit`   decimal(10,2)   NOT NULL COMMENT 'Precio al momento de la venta',
  `subtotal`      decimal(10,2)   NOT NULL COMMENT 'cantidad * precio_unit',
  PRIMARY KEY (`id_detalle`),
  KEY `idx_vd_venta`    (`id_venta`),
  KEY `idx_vd_producto` (`id_producto`),
  CONSTRAINT `fk_vd_venta`
    FOREIGN KEY (`id_venta`)    REFERENCES `ventas`   (`id_venta`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_vd_producto`
    FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id_producto`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Productos incluidos en cada venta';

-- ── Promociones ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `promociones` (
  `id_promo`      int(11)         NOT NULL AUTO_INCREMENT,
  `nombre`        varchar(100)    NOT NULL,
  `tipo`          enum('porcentaje','monto_fijo') NOT NULL DEFAULT 'porcentaje',
  `valor`         decimal(10,2)   NOT NULL DEFAULT 0.00
                                  COMMENT '% o monto fijo de descuento',
  `fecha_inicio`  date            NOT NULL,
  `fecha_fin`     date            NOT NULL,
  `estado`        enum('activa','inactiva') NOT NULL DEFAULT 'activa',
  PRIMARY KEY (`id_promo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Promociones y descuentos del punto de venta';

-- ── Datos de ejemplo ──────────────────────────────────────
INSERT IGNORE INTO `productos` (`nombre`, `categoria`, `precio_costo`, `precio_venta`, `stock`, `stock_minimo`, `estado`) VALUES
  ('Proteína Whey 1kg',      'Suplementos', 350.00, 550.00, 20, 5, 'activo'),
  ('Creatina 300g',          'Suplementos', 180.00, 280.00, 15, 3, 'activo'),
  ('Agua 600ml',             'Bebidas',      8.00,  15.00,  50, 10, 'activo'),
  ('Bebida isotónica 500ml', 'Bebidas',     14.00,  25.00,  40, 10, 'activo'),
  ('Guantes de entrenamiento','Accesorios', 80.00, 150.00,  10, 3, 'activo'),
  ('Cuerda para saltar',     'Accesorios',  45.00,  90.00,  12, 3, 'activo'),
  ('Playera Sayagym',        'Ropa',        90.00, 180.00,   8, 2, 'activo'),
  ('Shorts deportivo',       'Ropa',       100.00, 200.00,   6, 2, 'activo');
