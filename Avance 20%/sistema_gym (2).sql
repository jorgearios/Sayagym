-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 10-03-2026 a las 04:21:40
-- Versión del servidor: 8.0.45-0ubuntu0.24.04.1
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_gym`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int NOT NULL,
  `id_socio` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `medio` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `accion` varchar(150) NOT NULL,
  `modulo` varchar(80) DEFAULT NULL,
  `descripcion` text,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases`
--

CREATE TABLE `clases` (
  `id_clase` int NOT NULL,
  `nombre_clase` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `capacidad` int DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicios`
--

CREATE TABLE `ejercicios` (
  `id_ejercicio` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `grupo_muscular` varchar(100) DEFAULT NULL,
  `descripcion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrenadores`
--

CREATE TABLE `entrenadores` (
  `id_entrenador` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `tarifa_comision` decimal(10,2) DEFAULT '0.00',
  `turno` enum('Matutino','Vespertino','Completo') DEFAULT 'Completo',
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `entrenadores`
--

INSERT INTO `entrenadores` (`id_entrenador`, `nombre`, `especialidad`, `telefono`, `correo`, `fecha_contratacion`, `tarifa_comision`, `turno`, `estado`) VALUES
(1, 'Paquito Cortez', 'Pesas', '8341234567', 'test@gym.com', '2026-03-07', 50.00, 'Matutino', 'activo'),
(2, 'Jorge Daniel Torres Ramos', 'Calistenia', '8341546152', 'jorge.torr@gmail.com', '2025-03-08', 45.00, 'Vespertino', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrenador_clase`
--

CREATE TABLE `entrenador_clase` (
  `id_relacion` int NOT NULL,
  `id_entrenador` int NOT NULL,
  `id_clase` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresias`
--

CREATE TABLE `membresias` (
  `id_membresia` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `duracion_meses` int NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descripcion` text,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `membresias`
--

INSERT INTO `membresias` (`id_membresia`, `nombre`, `duracion_meses`, `precio`, `descripcion`, `estado`) VALUES
(1, 'Mensual', 1, 500.00, 'Acceso por 1 mes completo', 'activo'),
(2, 'Trimestral', 3, 1350.00, 'Acceso por 3 meses (10% de ahorro)', 'activo'),
(3, 'Anual', 12, 4800.00, 'Acceso por 12 meses (20% de ahorro)', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresia_clase`
--

CREATE TABLE `membresia_clase` (
  `id_relacion` int NOT NULL,
  `id_membresia` int NOT NULL,
  `id_clase` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int NOT NULL,
  `id_socio` int DEFAULT NULL,
  `id_membresia` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_pago` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `estado` enum('pagado','pendiente','reembolsado') NOT NULL DEFAULT 'pagado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int NOT NULL,
  `tipo_reporte` varchar(100) NOT NULL,
  `fecha_generacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `generado_por` int DEFAULT NULL,
  `parametros` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutinas`
--

CREATE TABLE `rutinas` (
  `id_rutina` int NOT NULL,
  `nombre_rutina` varchar(150) NOT NULL,
  `descripcion` text,
  `nivel` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina_ejercicio`
--

CREATE TABLE `rutina_ejercicio` (
  `id_relacion` int NOT NULL,
  `id_rutina` int NOT NULL,
  `id_ejercicio` int NOT NULL,
  `orden` int DEFAULT '0',
  `series` int DEFAULT NULL,
  `repeticiones` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id_socio` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contacto_emergencia` varchar(255) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `id_membresia` int DEFAULT NULL,
  `estado` enum('activo','inactivo','vencido') DEFAULT 'activo',
  `id_entrenador` int DEFAULT NULL,
  `qr_codigo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`id_socio`, `nombre`, `apellido`, `foto`, `telefono`, `contacto_emergencia`, `correo`, `direccion`, `fecha_nacimiento`, `fecha_registro`, `fecha_vencimiento`, `id_membresia`, `estado`, `id_entrenador`, `qr_codigo`) VALUES
(1, 'Rubí María', 'Cobos Ramos', NULL, '8341543050', NULL, 'cobosrubi64@gmail.com', '0 y 1 Servando Canales', '2004-10-20', '2026-03-07', '2026-04-07', 1, 'activo', NULL, NULL),
(2, 'Jesús Emmanuel', 'López Zúñiga', NULL, '8342567889', NULL, 'chuy.lopez@live.com', 'Andromeda 534', '2005-08-15', '2026-03-08', '2026-06-08', 2, 'activo', 2, NULL),
(3, 'Esmeralda', 'Ramos Cortez', NULL, '8342688449', NULL, 'esmeramos23@hotmail.com', 'Julián de la Cerda 809', '1973-02-08', '2026-03-08', '2026-04-08', 3, 'activo', NULL, NULL),
(4, 'Maximino', 'Orozco Betancourt', NULL, '8342701650', NULL, 'maxorozco@gmail.com', 'Norias de los Angeles 431', '1961-06-11', '2026-03-08', '2026-06-08', 2, 'activo', 2, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios_membresias`
--

CREATE TABLE `socios_membresias` (
  `id_registro` int NOT NULL,
  `id_socio` int NOT NULL,
  `id_membresia` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('activa','vencida','cancelada') NOT NULL DEFAULT 'activa',
  `id_pago` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `socios_membresias`
--

INSERT INTO `socios_membresias` (`id_registro`, `id_socio`, `id_membresia`, `fecha_inicio`, `fecha_fin`, `estado`, `id_pago`) VALUES
(1, 1, 1, '2026-03-07', '2026-04-07', 'activa', NULL),
(2, 2, 2, '2026-03-08', '2026-06-08', 'activa', NULL),
(3, 4, 2, '2026-03-08', '2026-06-08', 'activa', NULL),
(4, 3, 3, '2026-03-08', '2026-04-08', 'activa', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socio_clase`
--

CREATE TABLE `socio_clase` (
  `id_registro` int NOT NULL,
  `id_socio` int NOT NULL,
  `id_clase` int NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `estado` enum('inscrito','cancelado','asistio') DEFAULT 'inscrito'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socio_rutina`
--

CREATE TABLE `socio_rutina` (
  `id_asignacion` int NOT NULL,
  `id_socio` int NOT NULL,
  `id_rutina` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Administrador','Recepcionista','Entrenador') DEFAULT 'Recepcionista',
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `idx_asistencia_socio` (`id_socio`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `idx_bit_usuario` (`id_usuario`);

--
-- Indices de la tabla `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id_clase`);

--
-- Indices de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD PRIMARY KEY (`id_ejercicio`);

--
-- Indices de la tabla `entrenadores`
--
ALTER TABLE `entrenadores`
  ADD PRIMARY KEY (`id_entrenador`);

--
-- Indices de la tabla `entrenador_clase`
--
ALTER TABLE `entrenador_clase`
  ADD PRIMARY KEY (`id_relacion`),
  ADD KEY `idx_ec_entrenador` (`id_entrenador`),
  ADD KEY `idx_ec_clase` (`id_clase`);

--
-- Indices de la tabla `membresias`
--
ALTER TABLE `membresias`
  ADD PRIMARY KEY (`id_membresia`);

--
-- Indices de la tabla `membresia_clase`
--
ALTER TABLE `membresia_clase`
  ADD PRIMARY KEY (`id_relacion`),
  ADD KEY `idx_mc_membresia` (`id_membresia`),
  ADD KEY `idx_mc_clase` (`id_clase`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `idx_pago_socio` (`id_socio`),
  ADD KEY `idx_pago_membresia` (`id_membresia`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_usuario` (`generado_por`);

--
-- Indices de la tabla `rutinas`
--
ALTER TABLE `rutinas`
  ADD PRIMARY KEY (`id_rutina`);

--
-- Indices de la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  ADD PRIMARY KEY (`id_relacion`),
  ADD KEY `idx_re_rutina` (`id_rutina`),
  ADD KEY `idx_re_ejercicio` (`id_ejercicio`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id_socio`),
  ADD UNIQUE KEY `qr_codigo` (`qr_codigo`),
  ADD KEY `fk_socio_membresia` (`id_membresia`),
  ADD KEY `fk_socio_entrenador` (`id_entrenador`);

--
-- Indices de la tabla `socios_membresias`
--
ALTER TABLE `socios_membresias`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `idx_sm_socio` (`id_socio`),
  ADD KEY `idx_sm_membresia` (`id_membresia`),
  ADD KEY `idx_sm_pago` (`id_pago`);

--
-- Indices de la tabla `socio_clase`
--
ALTER TABLE `socio_clase`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `idx_sc_socio` (`id_socio`),
  ADD KEY `idx_sc_clase` (`id_clase`);

--
-- Indices de la tabla `socio_rutina`
--
ALTER TABLE `socio_rutina`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `idx_sr_socio` (`id_socio`),
  ADD KEY `idx_sr_rutina` (`id_rutina`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clases`
--
ALTER TABLE `clases`
  MODIFY `id_clase` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  MODIFY `id_ejercicio` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrenadores`
--
ALTER TABLE `entrenadores`
  MODIFY `id_entrenador` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `entrenador_clase`
--
ALTER TABLE `entrenador_clase`
  MODIFY `id_relacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membresias`
--
ALTER TABLE `membresias`
  MODIFY `id_membresia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `membresia_clase`
--
ALTER TABLE `membresia_clase`
  MODIFY `id_relacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rutinas`
--
ALTER TABLE `rutinas`
  MODIFY `id_rutina` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  MODIFY `id_relacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id_socio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `socios_membresias`
--
ALTER TABLE `socios_membresias`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `socio_clase`
--
ALTER TABLE `socio_clase`
  MODIFY `id_registro` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socio_rutina`
--
ALTER TABLE `socio_rutina`
  MODIFY `id_asignacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `fk_bit_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `entrenador_clase`
--
ALTER TABLE `entrenador_clase`
  ADD CONSTRAINT `fk_ec_clase` FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ec_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenadores` (`id_entrenador`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `membresia_clase`
--
ALTER TABLE `membresia_clase`
  ADD CONSTRAINT `fk_mc_clase` FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mc_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pago_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reporte_usuario` FOREIGN KEY (`generado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  ADD CONSTRAINT `fk_re_ejercicio` FOREIGN KEY (`id_ejercicio`) REFERENCES `ejercicios` (`id_ejercicio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_re_rutina` FOREIGN KEY (`id_rutina`) REFERENCES `rutinas` (`id_rutina`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `socios`
--
ALTER TABLE `socios`
  ADD CONSTRAINT `fk_socio_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenadores` (`id_entrenador`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_socio_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`);

--
-- Filtros para la tabla `socios_membresias`
--
ALTER TABLE `socios_membresias`
  ADD CONSTRAINT `fk_sm_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sm_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id_pago`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sm_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `socio_clase`
--
ALTER TABLE `socio_clase`
  ADD CONSTRAINT `fk_sc_clase` FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sc_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `socio_rutina`
--
ALTER TABLE `socio_rutina`
  ADD CONSTRAINT `fk_sr_rutina` FOREIGN KEY (`id_rutina`) REFERENCES `rutinas` (`id_rutina`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sr_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
