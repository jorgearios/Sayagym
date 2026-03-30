-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-03-2026 a las 14:23:43
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

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
-- Estructura de tabla para la tabla `alimentos_calorias`
--

CREATE TABLE `alimentos_calorias` (
  `id_alimento` int(11) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `calorias_100g` int(11) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de alimentos con calorías';

--
-- Volcado de datos para la tabla `alimentos_calorias`
--

INSERT INTO `alimentos_calorias` (`id_alimento`, `categoria`, `nombre`, `calorias_100g`, `estado`) VALUES
(1, 'FRUTAS Y HORTALIZAS', 'Aceitunas negras', 349, 'activo'),
(2, 'FRUTAS Y HORTALIZAS', 'Aceitunas verdes', 132, 'activo'),
(3, 'FRUTAS Y HORTALIZAS', 'Acelgas', 33, 'activo'),
(4, 'FRUTAS Y HORTALIZAS', 'Ajos', 169, 'activo'),
(5, 'FRUTAS Y HORTALIZAS', 'Alcachofas', 64, 'activo'),
(6, 'FRUTAS Y HORTALIZAS', 'Apio', 20, 'activo'),
(7, 'FRUTAS Y HORTALIZAS', 'Berenjena', 29, 'activo'),
(8, 'FRUTAS Y HORTALIZAS', 'Berros', 21, 'activo'),
(9, 'FRUTAS Y HORTALIZAS', 'Brócoli', 31, 'activo'),
(10, 'FRUTAS Y HORTALIZAS', 'Calabacín', 31, 'activo'),
(11, 'FRUTAS Y HORTALIZAS', 'Calabaza', 24, 'activo'),
(12, 'FRUTAS Y HORTALIZAS', 'Cebolla', 47, 'activo'),
(13, 'FRUTAS Y HORTALIZAS', 'Cebolla tierna', 39, 'activo'),
(14, 'FRUTAS Y HORTALIZAS', 'Champiñón y otras setas', 28, 'activo'),
(15, 'FRUTAS Y HORTALIZAS', 'Col', 28, 'activo'),
(16, 'FRUTAS Y HORTALIZAS', 'Col de Bruselas', 54, 'activo'),
(17, 'FRUTAS Y HORTALIZAS', 'Coliflor', 30, 'activo'),
(18, 'FRUTAS Y HORTALIZAS', 'Endibia', 22, 'activo'),
(19, 'FRUTAS Y HORTALIZAS', 'Escarola', 37, 'activo'),
(20, 'FRUTAS Y HORTALIZAS', 'Espárragos', 26, 'activo'),
(21, 'FRUTAS Y HORTALIZAS', 'Espárragos en lata', 24, 'activo'),
(22, 'FRUTAS Y HORTALIZAS', 'Espinaca', 32, 'activo'),
(23, 'FRUTAS Y HORTALIZAS', 'Espinacas congeladas', 25, 'activo'),
(24, 'FRUTAS Y HORTALIZAS', 'Habas tiernas', 64, 'activo'),
(25, 'FRUTAS Y HORTALIZAS', 'Hinojo', 16, 'activo'),
(26, 'FRUTAS Y HORTALIZAS', 'Lechuga', 18, 'activo'),
(27, 'FRUTAS Y HORTALIZAS', 'Nabos', 29, 'activo'),
(28, 'FRUTAS Y HORTALIZAS', 'Papas cocidas', 86, 'activo'),
(29, 'FRUTAS Y HORTALIZAS', 'Pepino', 12, 'activo'),
(30, 'FRUTAS Y HORTALIZAS', 'Perejil', 55, 'activo'),
(31, 'FRUTAS Y HORTALIZAS', 'Pimiento', 22, 'activo'),
(32, 'FRUTAS Y HORTALIZAS', 'Porotos verdes', 21, 'activo'),
(33, 'FRUTAS Y HORTALIZAS', 'Puerros', 42, 'activo'),
(34, 'FRUTAS Y HORTALIZAS', 'Rábanos', 20, 'activo'),
(35, 'FRUTAS Y HORTALIZAS', 'Remolacha', 40, 'activo'),
(36, 'FRUTAS Y HORTALIZAS', 'Repollo', 19, 'activo'),
(37, 'FRUTAS Y HORTALIZAS', 'Rúcula', 37, 'activo'),
(38, 'FRUTAS Y HORTALIZAS', 'Soja, Brotes de', 50, 'activo'),
(39, 'FRUTAS Y HORTALIZAS', 'Tomate triturado en conserva', 39, 'activo'),
(40, 'FRUTAS Y HORTALIZAS', 'Tomates', 22, 'activo'),
(41, 'FRUTAS Y HORTALIZAS', 'Trufa', 92, 'activo'),
(42, 'FRUTAS Y HORTALIZAS', 'Zanahoria', 42, 'activo'),
(43, 'FRUTAS Y HORTALIZAS', 'Zumo de tomate', 21, 'activo'),
(44, 'FRUTAS', 'Arándanos', 41, 'activo'),
(45, 'FRUTAS', 'Caqui', 64, 'activo'),
(46, 'FRUTAS', 'Cereza', 47, 'activo'),
(47, 'FRUTAS', 'Chirimoya', 78, 'activo'),
(48, 'FRUTAS', 'Ciruela', 44, 'activo'),
(49, 'FRUTAS', 'Ciruela seca', 290, 'activo'),
(50, 'FRUTAS', 'Coco', 646, 'activo'),
(51, 'FRUTAS', 'Dátil', 279, 'activo'),
(52, 'FRUTAS', 'Dátil seco', 306, 'activo'),
(53, 'FRUTAS', 'Frambuesa', 40, 'activo'),
(54, 'FRUTAS', 'Fresas', 36, 'activo'),
(55, 'FRUTAS', 'Granada', 65, 'activo'),
(56, 'FRUTAS', 'Grosella', 37, 'activo'),
(57, 'FRUTAS', 'Higos', 80, 'activo'),
(58, 'FRUTAS', 'Higos secos', 275, 'activo'),
(59, 'FRUTAS', 'Kiwi', 51, 'activo'),
(60, 'FRUTAS', 'Limón', 39, 'activo'),
(61, 'FRUTAS', 'Mandarina', 40, 'activo'),
(62, 'FRUTAS', 'Mango', 57, 'activo'),
(63, 'FRUTAS', 'Manzana', 52, 'activo'),
(64, 'FRUTAS', 'Melón', 31, 'activo'),
(65, 'FRUTAS', 'Mora', 37, 'activo'),
(66, 'FRUTAS', 'Naranja', 44, 'activo'),
(67, 'FRUTAS', 'Nectarina', 64, 'activo'),
(68, 'FRUTAS', 'Nísperos', 97, 'activo'),
(69, 'FRUTAS', 'Papaya', 45, 'activo'),
(70, 'FRUTAS', 'Pera', 61, 'activo'),
(71, 'FRUTAS', 'Piña', 51, 'activo'),
(72, 'FRUTAS', 'Piña en almíbar', 84, 'activo'),
(73, 'FRUTAS', 'Plátano', 90, 'activo'),
(74, 'FRUTAS', 'Pomelo', 30, 'activo'),
(75, 'FRUTAS', 'Sandía', 30, 'activo'),
(76, 'FRUTAS', 'Uva', 81, 'activo'),
(77, 'FRUTAS', 'Uva pasa', 324, 'activo'),
(78, 'FRUTAS', 'Zumo de fruta', 45, 'activo'),
(79, 'FRUTAS', 'Zumo de Naranja', 42, 'activo'),
(80, 'FRUTOS SECOS', 'Almendras', 620, 'activo'),
(81, 'FRUTOS SECOS', 'Avellanas', 675, 'activo'),
(82, 'FRUTOS SECOS', 'Castañas', 199, 'activo'),
(83, 'FRUTOS SECOS', 'Maní', 560, 'activo'),
(84, 'FRUTOS SECOS', 'Nueces', 660, 'activo'),
(85, 'FRUTOS SECOS', 'Piñones', 660, 'activo'),
(86, 'FRUTOS SECOS', 'Pistacho', 581, 'activo'),
(87, 'LÁCTEOS Y DERIVADOS', 'Cuajada', 92, 'activo'),
(88, 'LÁCTEOS Y DERIVADOS', 'Flan de huevo', 126, 'activo'),
(89, 'LÁCTEOS Y DERIVADOS', 'Flan de vainilla', 102, 'activo'),
(90, 'LÁCTEOS Y DERIVADOS', 'Helados lácteos', 167, 'activo'),
(91, 'LÁCTEOS Y DERIVADOS', 'Leche condensada con azúcar', 350, 'activo'),
(92, 'LÁCTEOS Y DERIVADOS', 'Leche condensada sin azúcar', 160, 'activo'),
(93, 'LÁCTEOS Y DERIVADOS', 'Leche de cabra', 72, 'activo'),
(94, 'LÁCTEOS Y DERIVADOS', 'Leche de oveja', 96, 'activo'),
(95, 'LÁCTEOS Y DERIVADOS', 'Leche descremada', 36, 'activo'),
(96, 'LÁCTEOS Y DERIVADOS', 'Leche en polvo descremada', 373, 'activo'),
(97, 'LÁCTEOS Y DERIVADOS', 'Leche en polvo entera', 500, 'activo'),
(98, 'LÁCTEOS Y DERIVADOS', 'Leche entera', 68, 'activo'),
(99, 'LÁCTEOS Y DERIVADOS', 'Leche semi descremada', 49, 'activo'),
(100, 'LÁCTEOS Y DERIVADOS', 'Mousse', 177, 'activo'),
(101, 'LÁCTEOS Y DERIVADOS', 'Nata o crema de leche', 298, 'activo'),
(102, 'LÁCTEOS Y DERIVADOS', 'Queso blanco desnatado', 70, 'activo'),
(103, 'LÁCTEOS Y DERIVADOS', 'Queso Brie', 263, 'activo'),
(104, 'LÁCTEOS Y DERIVADOS', 'Queso camembert', 312, 'activo'),
(105, 'LÁCTEOS Y DERIVADOS', 'Queso cheddar', 381, 'activo'),
(106, 'LÁCTEOS Y DERIVADOS', 'Queso crema', 245, 'activo'),
(107, 'LÁCTEOS Y DERIVADOS', 'Queso de bola', 349, 'activo'),
(108, 'LÁCTEOS Y DERIVADOS', 'Queso de Burgos', 174, 'activo'),
(109, 'LÁCTEOS Y DERIVADOS', 'Queso de oveja', 245, 'activo'),
(110, 'LÁCTEOS Y DERIVADOS', 'Queso edam', 306, 'activo'),
(111, 'LÁCTEOS Y DERIVADOS', 'Queso emmental', 415, 'activo'),
(112, 'LÁCTEOS Y DERIVADOS', 'Queso fundido untable', 285, 'activo'),
(113, 'LÁCTEOS Y DERIVADOS', 'Queso gruyere', 391, 'activo'),
(114, 'LÁCTEOS Y DERIVADOS', 'Queso manchego', 376, 'activo'),
(115, 'LÁCTEOS Y DERIVADOS', 'Queso mozzarella', 245, 'activo'),
(116, 'LÁCTEOS Y DERIVADOS', 'Queso parmesano', 393, 'activo'),
(117, 'LÁCTEOS Y DERIVADOS', 'Queso ricota', 400, 'activo'),
(118, 'LÁCTEOS Y DERIVADOS', 'Queso roquefort', 405, 'activo'),
(119, 'LÁCTEOS Y DERIVADOS', 'Requesón', 96, 'activo'),
(120, 'LÁCTEOS Y DERIVADOS', 'Yogur desnatado', 45, 'activo'),
(121, 'LÁCTEOS Y DERIVADOS', 'Yogur desnatado con frutas', 82, 'activo'),
(122, 'LÁCTEOS Y DERIVADOS', 'Yogur enriquecido con nata', 65, 'activo'),
(123, 'LÁCTEOS Y DERIVADOS', 'Yogur natural', 62, 'activo'),
(124, 'LÁCTEOS Y DERIVADOS', 'Yogur natural con fruta', 100, 'activo'),
(125, 'CARNES Y EMBUTIDOS', 'Bacon (Panceta ahumada)', 665, 'activo'),
(126, 'CARNES Y EMBUTIDOS', 'Butifarra cocida', 390, 'activo'),
(127, 'CARNES Y EMBUTIDOS', 'Butifarra / Salchicha fresca', 326, 'activo'),
(128, 'CARNES Y EMBUTIDOS', 'Cabrito', 127, 'activo'),
(129, 'CARNES Y EMBUTIDOS', 'Cerdo, chuleta', 330, 'activo'),
(130, 'CARNES Y EMBUTIDOS', 'Cerdo, hígado', 153, 'activo'),
(131, 'CARNES Y EMBUTIDOS', 'Cerdo, lomo', 208, 'activo'),
(132, 'CARNES Y EMBUTIDOS', 'Chicharrón', 601, 'activo'),
(133, 'CARNES Y EMBUTIDOS', 'Chorizo', 468, 'activo'),
(134, 'CARNES Y EMBUTIDOS', 'Ciervo', 120, 'activo'),
(135, 'CARNES Y EMBUTIDOS', 'Codorniz y perdiz', 114, 'activo'),
(136, 'CARNES Y EMBUTIDOS', 'Conejo / Liebre', 162, 'activo'),
(137, 'CARNES Y EMBUTIDOS', 'Cordero lechón', 105, 'activo'),
(138, 'CARNES Y EMBUTIDOS', 'Cordero pierna', 98, 'activo'),
(139, 'CARNES Y EMBUTIDOS', 'Cordero, costillas', 215, 'activo'),
(140, 'CARNES Y EMBUTIDOS', 'Cordero, hígado', 132, 'activo'),
(141, 'CARNES Y EMBUTIDOS', 'Faisán', 144, 'activo'),
(142, 'CARNES Y EMBUTIDOS', 'Foie Gras', 518, 'activo'),
(143, 'CARNES Y EMBUTIDOS', 'Gallina', 369, 'activo'),
(144, 'CARNES Y EMBUTIDOS', 'Hamburguesa', 230, 'activo'),
(145, 'CARNES Y EMBUTIDOS', 'Jabalí', 107, 'activo'),
(146, 'CARNES Y EMBUTIDOS', 'Jamón serrano', 380, 'activo'),
(147, 'CARNES Y EMBUTIDOS', 'Jamón cocido', 126, 'activo'),
(148, 'CARNES Y EMBUTIDOS', 'Jamón crudo', 296, 'activo'),
(149, 'CARNES Y EMBUTIDOS', 'Jamón York', 289, 'activo'),
(150, 'CARNES Y EMBUTIDOS', 'Lengua de vaca', 191, 'activo'),
(151, 'CARNES Y EMBUTIDOS', 'Lomo embuchado', 380, 'activo'),
(152, 'CARNES Y EMBUTIDOS', 'Mortadela', 265, 'activo'),
(153, 'CARNES Y EMBUTIDOS', 'Pato', 200, 'activo'),
(154, 'CARNES Y EMBUTIDOS', 'Pavo, muslo', 186, 'activo'),
(155, 'CARNES Y EMBUTIDOS', 'Pavo, pechuga', 134, 'activo'),
(156, 'CARNES Y EMBUTIDOS', 'Perdiz', 120, 'activo'),
(157, 'CARNES Y EMBUTIDOS', 'Pies de cerdo', 290, 'activo'),
(158, 'CARNES Y EMBUTIDOS', 'Pollo, hígado', 129, 'activo'),
(159, 'CARNES Y EMBUTIDOS', 'Pollo, muslo', 186, 'activo'),
(160, 'CARNES Y EMBUTIDOS', 'Pollo, pechuga', 134, 'activo'),
(161, 'CARNES Y EMBUTIDOS', 'Salami', 325, 'activo'),
(162, 'CARNES Y EMBUTIDOS', 'Salchicha Frankfurt', 315, 'activo'),
(163, 'CARNES Y EMBUTIDOS', 'Salchichón', 294, 'activo'),
(164, 'CARNES Y EMBUTIDOS', 'Ternera', 181, 'activo'),
(165, 'CARNES Y EMBUTIDOS', 'Ternera, chuleta', 168, 'activo'),
(166, 'CARNES Y EMBUTIDOS', 'Ternera, hígado', 140, 'activo'),
(167, 'CARNES Y EMBUTIDOS', 'Ternera, lengua', 207, 'activo'),
(168, 'CARNES Y EMBUTIDOS', 'Ternera, riñón', 86, 'activo'),
(169, 'CARNES Y EMBUTIDOS', 'Ternera, sesos', 125, 'activo'),
(170, 'CARNES Y EMBUTIDOS', 'Ternera, solomillo', 290, 'activo'),
(171, 'CARNES Y EMBUTIDOS', 'Tira de asado', 401, 'activo'),
(172, 'CARNES Y EMBUTIDOS', 'Tripas', 100, 'activo'),
(173, 'CARNES Y EMBUTIDOS', 'Vacuno, hígado', 129, 'activo'),
(174, 'PESCADOS Y MARISCOS', 'Almejas', 50, 'activo'),
(175, 'PESCADOS Y MARISCOS', 'Anchoas', 175, 'activo'),
(176, 'PESCADOS Y MARISCOS', 'Anguilas', 200, 'activo'),
(177, 'PESCADOS Y MARISCOS', 'Atún en lata con aceite', 280, 'activo'),
(178, 'PESCADOS Y MARISCOS', 'Atún en lata con agua', 127, 'activo'),
(179, 'PESCADOS Y MARISCOS', 'Atún fresco', 225, 'activo'),
(180, 'PESCADOS Y MARISCOS', 'Bacalao fresco', 74, 'activo'),
(181, 'PESCADOS Y MARISCOS', 'Bacalao seco', 322, 'activo'),
(182, 'PESCADOS Y MARISCOS', 'Besugo', 118, 'activo'),
(183, 'PESCADOS Y MARISCOS', 'Caballa', 153, 'activo'),
(184, 'PESCADOS Y MARISCOS', 'Calamar', 82, 'activo'),
(185, 'PESCADOS Y MARISCOS', 'Cangrejo', 85, 'activo'),
(186, 'PESCADOS Y MARISCOS', 'Caviar', 233, 'activo'),
(187, 'PESCADOS Y MARISCOS', 'Congrio', 112, 'activo'),
(188, 'PESCADOS Y MARISCOS', 'Dorada', 80, 'activo'),
(189, 'PESCADOS Y MARISCOS', 'Gallo', 73, 'activo'),
(190, 'PESCADOS Y MARISCOS', 'Gambas', 96, 'activo'),
(191, 'PESCADOS Y MARISCOS', 'Langosta', 67, 'activo'),
(192, 'PESCADOS Y MARISCOS', 'Langostino', 96, 'activo'),
(193, 'PESCADOS Y MARISCOS', 'Lenguado', 73, 'activo'),
(194, 'PESCADOS Y MARISCOS', 'Lubina', 118, 'activo'),
(195, 'PESCADOS Y MARISCOS', 'Lucio', 81, 'activo'),
(196, 'PESCADOS Y MARISCOS', 'Mejillón', 74, 'activo'),
(197, 'PESCADOS Y MARISCOS', 'Merluza', 86, 'activo'),
(198, 'PESCADOS Y MARISCOS', 'Mero', 118, 'activo'),
(199, 'PESCADOS Y MARISCOS', 'Ostras', 80, 'activo'),
(200, 'PESCADOS Y MARISCOS', 'Pejerrey', 87, 'activo'),
(201, 'PESCADOS Y MARISCOS', 'Pez espada', 109, 'activo'),
(202, 'PESCADOS Y MARISCOS', 'Pulpo', 57, 'activo'),
(203, 'PESCADOS Y MARISCOS', 'Rodaballo', 81, 'activo'),
(204, 'PESCADOS Y MARISCOS', 'Salmón', 172, 'activo'),
(205, 'PESCADOS Y MARISCOS', 'Salmón ahumado', 154, 'activo'),
(206, 'PESCADOS Y MARISCOS', 'Salmonete', 97, 'activo'),
(207, 'PESCADOS Y MARISCOS', 'Sardinas en lata con aceite', 192, 'activo'),
(208, 'PESCADOS Y MARISCOS', 'Sardinas frescas', 151, 'activo'),
(209, 'PESCADOS Y MARISCOS', 'Trucha', 94, 'activo'),
(210, 'AZÚCARES Y DULCES', 'Azúcar', 380, 'activo'),
(211, 'AZÚCARES Y DULCES', 'Cacao en polvo con azúcar', 366, 'activo'),
(212, 'AZÚCARES Y DULCES', 'Caramelos', 378, 'activo'),
(213, 'AZÚCARES Y DULCES', 'Chocolate con leche', 550, 'activo'),
(214, 'AZÚCARES Y DULCES', 'Chocolate negro', 530, 'activo'),
(215, 'AZÚCARES Y DULCES', 'Crema de chocolate con avellanas', 549, 'activo'),
(216, 'AZÚCARES Y DULCES', 'Dulce de membrillo', 215, 'activo'),
(217, 'AZÚCARES Y DULCES', 'Helados de agua', 139, 'activo'),
(218, 'AZÚCARES Y DULCES', 'Mermelada con azúcar', 280, 'activo'),
(219, 'AZÚCARES Y DULCES', 'Mermelada sin azúcar', 145, 'activo'),
(220, 'AZÚCARES Y DULCES', 'Miel', 300, 'activo'),
(221, 'CEREALES Y DERIVADOS', 'Arroz blanco', 354, 'activo'),
(222, 'CEREALES Y DERIVADOS', 'Arroz integral', 350, 'activo'),
(223, 'CEREALES Y DERIVADOS', 'Avena', 367, 'activo'),
(224, 'CEREALES Y DERIVADOS', 'Cebada', 373, 'activo'),
(225, 'CEREALES Y DERIVADOS', 'Centeno', 350, 'activo'),
(226, 'CEREALES Y DERIVADOS', 'Cereales con chocolate', 358, 'activo'),
(227, 'CEREALES Y DERIVADOS', 'Cereales de desayuno con miel', 386, 'activo'),
(228, 'CEREALES Y DERIVADOS', 'Copos de maíz', 350, 'activo'),
(229, 'CEREALES Y DERIVADOS', 'Harina de maíz', 349, 'activo'),
(230, 'CEREALES Y DERIVADOS', 'Harina de trigo integral', 340, 'activo'),
(231, 'CEREALES Y DERIVADOS', 'Harina de trigo refinada', 353, 'activo'),
(232, 'CEREALES Y DERIVADOS', 'Pan de centeno', 241, 'activo'),
(233, 'CEREALES Y DERIVADOS', 'Pan de trigo blanco', 255, 'activo'),
(234, 'CEREALES Y DERIVADOS', 'Pan de trigo integral', 239, 'activo'),
(235, 'CEREALES Y DERIVADOS', 'Pan de molde blanco', 233, 'activo'),
(236, 'CEREALES Y DERIVADOS', 'Pan de molde integral', 216, 'activo'),
(237, 'CEREALES Y DERIVADOS', 'Pasta al huevo', 368, 'activo'),
(238, 'CEREALES Y DERIVADOS', 'Pasta de sémola', 361, 'activo'),
(239, 'CEREALES Y DERIVADOS', 'Polenta', 358, 'activo'),
(240, 'CEREALES Y DERIVADOS', 'Sémola de trigo', 368, 'activo'),
(241, 'CEREALES Y DERIVADOS', 'Yuca', 338, 'activo'),
(242, 'LEGUMBRES', 'Garbanzos', 361, 'activo'),
(243, 'LEGUMBRES', 'Judías', 343, 'activo'),
(244, 'LEGUMBRES', 'Lentejas', 336, 'activo'),
(245, 'HUEVOS', 'Clara de huevo', 48, 'activo'),
(246, 'HUEVOS', 'Huevo duro', 147, 'activo'),
(247, 'HUEVOS', 'Huevo entero', 162, 'activo'),
(248, 'HUEVOS', 'Yema de huevo', 368, 'activo'),
(249, 'PASTELERÍA', 'Bizcocho', 456, 'activo'),
(250, 'PASTELERÍA', 'Croissant de chocolate', 469, 'activo'),
(251, 'PASTELERÍA', 'Croissant / Donut', 456, 'activo'),
(252, 'PASTELERÍA', 'Galletas de chocolate', 524, 'activo'),
(253, 'PASTELERÍA', 'Galletas de mantequilla', 397, 'activo'),
(254, 'PASTELERÍA', 'Galletas saladas', 464, 'activo'),
(255, 'PASTELERÍA', 'Magdalenas', 469, 'activo'),
(256, 'PASTELERÍA', 'Pasta de hojaldre cocida', 565, 'activo'),
(257, 'PASTELERÍA', 'Pastel de manzana', 311, 'activo'),
(258, 'PASTELERÍA', 'Pastel de manzana con hojaldre', 456, 'activo'),
(259, 'PASTELERÍA', 'Pastel de queso', 414, 'activo'),
(260, 'BEBIDAS', 'Aguardiente', 280, 'activo'),
(261, 'BEBIDAS', 'Agua tónica', 34, 'activo'),
(262, 'BEBIDAS', 'Anís', 312, 'activo'),
(263, 'BEBIDAS', 'Batido lácteo de cacao', 100, 'activo'),
(264, 'BEBIDAS', 'Cacao en polvo sin azúcar', 439, 'activo'),
(265, 'BEBIDAS', 'Café', 1, 'activo'),
(266, 'BEBIDAS', 'Cerveza negra', 37, 'activo'),
(267, 'BEBIDAS', 'Cerveza rubia', 45, 'activo'),
(268, 'BEBIDAS', 'Champaña demi-sec', 90, 'activo'),
(269, 'BEBIDAS', 'Champaña dulce', 118, 'activo'),
(270, 'BEBIDAS', 'Champaña seca', 85, 'activo'),
(271, 'BEBIDAS', 'Coñac / Brandy', 243, 'activo'),
(272, 'BEBIDAS', 'Crema de cacao', 260, 'activo'),
(273, 'BEBIDAS', 'Daiquiri', 122, 'activo'),
(274, 'BEBIDAS', 'Gin Tónica', 76, 'activo'),
(275, 'BEBIDAS', 'Ginebra', 244, 'activo'),
(276, 'BEBIDAS', 'Leche de almendras', 335, 'activo'),
(277, 'BEBIDAS', 'Licor de caña', 273, 'activo'),
(278, 'BEBIDAS', 'Piña colada', 194, 'activo'),
(279, 'BEBIDAS', 'Pisco', 210, 'activo'),
(280, 'BEBIDAS', 'Refresco carbonatado', 48, 'activo'),
(281, 'BEBIDAS', 'Ron', 244, 'activo'),
(282, 'BEBIDAS', 'Sidra dulce', 33, 'activo'),
(283, 'BEBIDAS', 'Sidra seca', 35, 'activo'),
(284, 'BEBIDAS', 'Té', 1, 'activo'),
(285, 'BEBIDAS', 'Vermouth amargo', 112, 'activo'),
(286, 'BEBIDAS', 'Vermouth dulce', 160, 'activo'),
(287, 'BEBIDAS', 'Vino de mesa', 70, 'activo'),
(288, 'BEBIDAS', 'Vino dulce / Jerez', 160, 'activo'),
(289, 'BEBIDAS', 'Vino oporto', 160, 'activo'),
(290, 'BEBIDAS', 'Vodka', 315, 'activo'),
(291, 'BEBIDAS', 'Whisky', 244, 'activo'),
(292, 'ACEITES Y GRASAS', 'Aceite de girasol', 900, 'activo'),
(293, 'ACEITES Y GRASAS', 'Aceite de oliva', 900, 'activo'),
(294, 'ACEITES Y GRASAS', 'Manteca', 670, 'activo'),
(295, 'ACEITES Y GRASAS', 'Mantequilla', 752, 'activo'),
(296, 'ACEITES Y GRASAS', 'Margarina vegetal', 752, 'activo'),
(297, 'SALSAS Y CONDIMENTOS', 'Bechamel', 115, 'activo'),
(298, 'SALSAS Y CONDIMENTOS', 'Caldo concentrado', 259, 'activo'),
(299, 'SALSAS Y CONDIMENTOS', 'Ketchup', 98, 'activo'),
(300, 'SALSAS Y CONDIMENTOS', 'Mayonesa', 718, 'activo'),
(301, 'SALSAS Y CONDIMENTOS', 'Mayonesa light', 374, 'activo'),
(302, 'SALSAS Y CONDIMENTOS', 'Mostaza', 15, 'activo'),
(303, 'SALSAS Y CONDIMENTOS', 'Salsa de soja', 61, 'activo'),
(304, 'SALSAS Y CONDIMENTOS', 'Salsa de tomate en conserva', 86, 'activo'),
(305, 'SALSAS Y CONDIMENTOS', 'Sofrito', 116, 'activo'),
(306, 'SALSAS Y CONDIMENTOS', 'Vinagre', 8, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `medio` varchar(50) DEFAULT 'Manual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de entradas y salidas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(150) NOT NULL,
  `modulo` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoría';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases`
--

CREATE TABLE `clases` (
  `id_clase` int(11) NOT NULL,
  `nombre_clase` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clases grupales';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consumo_detalle`
--

CREATE TABLE `consumo_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_consumo` int(11) NOT NULL,
  `id_alimento` int(11) NOT NULL,
  `gramos` int(11) NOT NULL DEFAULT 100,
  `calorias` int(11) NOT NULL DEFAULT 0,
  `momento` enum('Desayuno','Almuerzo','Cena','Snack') NOT NULL DEFAULT 'Almuerzo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de alimentos consumidos por día';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicios`
--

CREATE TABLE `ejercicios` (
  `id_ejercicio` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `grupo_muscular` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de ejercicios';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrenadores`
--

CREATE TABLE `entrenadores` (
  `id_entrenador` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `tarifa_comision` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Comisión por socio asignado',
  `turno` enum('Matutino','Vespertino','Completo') NOT NULL DEFAULT 'Completo',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrenador_clase`
--

CREATE TABLE `entrenador_clase` (
  `id_relacion` int(11) NOT NULL,
  `id_entrenador` int(11) NOT NULL,
  `id_clase` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones_fisicas`
--

CREATE TABLE `evaluaciones_fisicas` (
  `id_evaluacion` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `id_entrenador` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `peso` decimal(5,2) DEFAULT NULL COMMENT 'kg',
  `altura` decimal(5,2) DEFAULT NULL COMMENT 'cm',
  `imc` decimal(5,2) DEFAULT NULL,
  `porcentaje_grasa` decimal(5,2) DEFAULT NULL COMMENT '%',
  `masa_muscular` decimal(5,2) DEFAULT NULL COMMENT 'kg',
  `pecho` decimal(5,2) DEFAULT NULL COMMENT 'cm',
  `cintura` decimal(5,2) DEFAULT NULL COMMENT 'cm',
  `cadera` decimal(5,2) DEFAULT NULL COMMENT 'cm',
  `bicep` decimal(5,2) DEFAULT NULL COMMENT 'cm',
  `muslo` decimal(5,2) DEFAULT NULL COMMENT 'cm',
  `objetivo` varchar(100) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Evaluaciones físicas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresias`
--

CREATE TABLE `membresias` (
  `id_membresia` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `duracion_meses` int(11) NOT NULL DEFAULT 1,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Planes de membresía del gimnasio';

--
-- Volcado de datos para la tabla `membresias`
--

INSERT INTO `membresias` (`id_membresia`, `nombre`, `duracion_meses`, `precio`, `descripcion`, `estado`) VALUES
(1, 'Fase 1', 1, 500.00, 'Acceso por 1 mes completo', 'activo'),
(2, 'Fase 2', 3, 1350.00, 'Acceso por 3 meses (10% de ahorro)', 'activo'),
(3, 'Fase 3', 12, 4800.00, 'Acceso por 12 meses (20% de ahorro)', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresia_clase`
--

CREATE TABLE `membresia_clase` (
  `id_relacion` int(11) NOT NULL,
  `id_membresia` int(11) NOT NULL,
  `id_clase` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_socio` int(11) DEFAULT NULL,
  `id_membresia` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `metodo_pago` varchar(50) DEFAULT 'Efectivo',
  `referencia` varchar(100) DEFAULT NULL,
  `estado` enum('pagado','pendiente','reembolsado') NOT NULL DEFAULT 'pagado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_socio`, `id_membresia`, `monto`, `fecha_pago`, `metodo_pago`, `referencia`, `estado`) VALUES
(1, 1, 1, 500.00, '2026-03-30 12:23:39', 'Efectivo', NULL, 'pagado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `tipo_reporte` varchar(100) NOT NULL,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `generado_por` int(11) DEFAULT NULL,
  `parametros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parametros`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reportes generados';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutinas`
--

CREATE TABLE `rutinas` (
  `id_rutina` int(11) NOT NULL,
  `nombre_rutina` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel` enum('Principiante','Intermedio','Avanzado') DEFAULT 'Intermedio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rutinas de entrenamiento';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina_ejercicio`
--

CREATE TABLE `rutina_ejercicio` (
  `id_relacion` int(11) NOT NULL,
  `id_rutina` int(11) NOT NULL,
  `id_ejercicio` int(11) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `series` int(11) DEFAULT 3,
  `repeticiones` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id_socio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contacto_emergencia` varchar(255) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT 'bcrypt — null si no tiene cuenta propia',
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `id_membresia` int(11) DEFAULT NULL,
  `id_entrenador` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo','vencido') NOT NULL DEFAULT 'activo',
  `qr_codigo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`id_socio`, `nombre`, `apellido`, `foto`, `telefono`, `contacto_emergencia`, `correo`, `password`, `direccion`, `fecha_nacimiento`, `fecha_registro`, `fecha_vencimiento`, `id_membresia`, `id_entrenador`, `estado`, `qr_codigo`) VALUES
(1, 'Socio', 'Prueba', NULL, '555-1234567', NULL, 'socio@sayagym.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIU0GIV.5NdkNFO', NULL, NULL, '2026-03-30', '2026-04-30', 1, NULL, 'activo', 'SGY-1-A1B2C3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios_membresias`
--

CREATE TABLE `socios_membresias` (
  `id_registro` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `id_membresia` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('activa','vencida','cancelada') NOT NULL DEFAULT 'activa',
  `id_pago` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial completo de membresías por socio';

--
-- Volcado de datos para la tabla `socios_membresias`
--

INSERT INTO `socios_membresias` (`id_registro`, `id_socio`, `id_membresia`, `fecha_inicio`, `fecha_fin`, `estado`, `id_pago`) VALUES
(1, 1, 1, '2026-03-30', '2026-04-30', 'activa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socio_calorias_limite`
--

CREATE TABLE `socio_calorias_limite` (
  `id` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `limite_diario` int(11) NOT NULL DEFAULT 2000,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha_ajuste` datetime NOT NULL DEFAULT current_timestamp(),
  `ajustado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Límites calóricos personalizados por socio';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socio_clase`
--

CREATE TABLE `socio_clase` (
  `id_registro` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `id_clase` int(11) NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `estado` enum('inscrito','cancelado','asistio') NOT NULL DEFAULT 'inscrito'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socio_consumo_calorico`
--

CREATE TABLE `socio_consumo_calorico` (
  `id_consumo` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro diario de consumo calórico por socio';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socio_rutina`
--

CREATE TABLE `socio_rutina` (
  `id_asignacion` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `id_rutina` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Asignación de rutinas a socios';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Administrador','Recepcionista','Entrenador') NOT NULL DEFAULT 'Recepcionista',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Personal interno del gimnasio';

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_completo`, `usuario`, `password`, `rol`, `estado`) VALUES
(1, 'Administrador Principal', 'admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIU0GIV.5NdkNFO', 'Administrador', 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alimentos_calorias`
--
ALTER TABLE `alimentos_calorias`
  ADD PRIMARY KEY (`id_alimento`),
  ADD KEY `idx_alim_categoria` (`categoria`),
  ADD KEY `idx_alim_nombre` (`nombre`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `idx_asistencia_socio` (`id_socio`),
  ADD KEY `idx_asistencia_fecha` (`fecha`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `idx_bit_usuario` (`id_usuario`),
  ADD KEY `idx_bit_fecha` (`fecha`);

--
-- Indices de la tabla `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id_clase`);

--
-- Indices de la tabla `consumo_detalle`
--
ALTER TABLE `consumo_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_cd_consumo` (`id_consumo`),
  ADD KEY `idx_cd_alimento` (`id_alimento`);

--
-- Indices de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD PRIMARY KEY (`id_ejercicio`),
  ADD KEY `idx_ej_grupo` (`grupo_muscular`);

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
-- Indices de la tabla `evaluaciones_fisicas`
--
ALTER TABLE `evaluaciones_fisicas`
  ADD PRIMARY KEY (`id_evaluacion`),
  ADD KEY `idx_eval_socio` (`id_socio`),
  ADD KEY `idx_eval_entrenador` (`id_entrenador`),
  ADD KEY `idx_eval_fecha` (`fecha`);

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
  ADD KEY `idx_pago_membresia` (`id_membresia`),
  ADD KEY `idx_pago_fecha` (`fecha_pago`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `idx_rep_usuario` (`generado_por`);

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
  ADD UNIQUE KEY `uk_qr_codigo` (`qr_codigo`),
  ADD KEY `idx_socio_membresia` (`id_membresia`),
  ADD KEY `idx_socio_entrenador` (`id_entrenador`),
  ADD KEY `idx_socio_correo` (`correo`);

--
-- Indices de la tabla `socios_membresias`
--
ALTER TABLE `socios_membresias`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `idx_sm_socio` (`id_socio`),
  ADD KEY `idx_sm_membresia` (`id_membresia`),
  ADD KEY `idx_sm_pago` (`id_pago`);

--
-- Indices de la tabla `socio_calorias_limite`
--
ALTER TABLE `socio_calorias_limite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scl_socio` (`id_socio`),
  ADD KEY `idx_scl_admin` (`ajustado_por`);

--
-- Indices de la tabla `socio_clase`
--
ALTER TABLE `socio_clase`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `idx_sc_socio` (`id_socio`),
  ADD KEY `idx_sc_clase` (`id_clase`);

--
-- Indices de la tabla `socio_consumo_calorico`
--
ALTER TABLE `socio_consumo_calorico`
  ADD PRIMARY KEY (`id_consumo`),
  ADD UNIQUE KEY `uk_socio_fecha` (`id_socio`,`fecha`),
  ADD KEY `idx_scc_socio` (`id_socio`),
  ADD KEY `idx_scc_fecha` (`fecha`);

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
  ADD UNIQUE KEY `uk_usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alimentos_calorias`
--
ALTER TABLE `alimentos_calorias`
  MODIFY `id_alimento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clases`
--
ALTER TABLE `clases`
  MODIFY `id_clase` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consumo_detalle`
--
ALTER TABLE `consumo_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  MODIFY `id_ejercicio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrenadores`
--
ALTER TABLE `entrenadores`
  MODIFY `id_entrenador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrenador_clase`
--
ALTER TABLE `entrenador_clase`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `evaluaciones_fisicas`
--
ALTER TABLE `evaluaciones_fisicas`
  MODIFY `id_evaluacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membresias`
--
ALTER TABLE `membresias`
  MODIFY `id_membresia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `membresia_clase`
--
ALTER TABLE `membresia_clase`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rutinas`
--
ALTER TABLE `rutinas`
  MODIFY `id_rutina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id_socio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `socios_membresias`
--
ALTER TABLE `socios_membresias`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `socio_calorias_limite`
--
ALTER TABLE `socio_calorias_limite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socio_clase`
--
ALTER TABLE `socio_clase`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socio_consumo_calorico`
--
ALTER TABLE `socio_consumo_calorico`
  MODIFY `id_consumo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socio_rutina`
--
ALTER TABLE `socio_rutina`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Filtros para la tabla `consumo_detalle`
--
ALTER TABLE `consumo_detalle`
  ADD CONSTRAINT `fk_cd_alimento` FOREIGN KEY (`id_alimento`) REFERENCES `alimentos_calorias` (`id_alimento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cd_consumo` FOREIGN KEY (`id_consumo`) REFERENCES `socio_consumo_calorico` (`id_consumo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entrenador_clase`
--
ALTER TABLE `entrenador_clase`
  ADD CONSTRAINT `fk_ec_clase` FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ec_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenadores` (`id_entrenador`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evaluaciones_fisicas`
--
ALTER TABLE `evaluaciones_fisicas`
  ADD CONSTRAINT `fk_eval_entrenador` FOREIGN KEY (`id_entrenador`) REFERENCES `entrenadores` (`id_entrenador`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eval_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_socio_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `socios_membresias`
--
ALTER TABLE `socios_membresias`
  ADD CONSTRAINT `fk_sm_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sm_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id_pago`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sm_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `socio_calorias_limite`
--
ALTER TABLE `socio_calorias_limite`
  ADD CONSTRAINT `fk_scl_admin` FOREIGN KEY (`ajustado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_scl_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `socio_clase`
--
ALTER TABLE `socio_clase`
  ADD CONSTRAINT `fk_sc_clase` FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sc_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `socio_consumo_calorico`
--
ALTER TABLE `socio_consumo_calorico`
  ADD CONSTRAINT `fk_scc_socio` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE;

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
