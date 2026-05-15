-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 15, 2026 at 12:40 AM
-- Server version: 10.11.16-MariaDB-log
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sb`
--

-- --------------------------------------------------------

--
-- Table structure for table `ajuste_esperado`
--

CREATE TABLE `ajuste_esperado` (
  `id_ajuste` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `tipo` enum('COBRO','PERSONAL','LOCAL','COMPRA','DEPOSITO','OTRO') NOT NULL DEFAULT 'COBRO',
  `modo_id` int(11) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `ref2_id` int(11) DEFAULT NULL,
  `tipo_documento` enum('BOLETA','FACTURA','NOTA_DE_VENTA') DEFAULT NULL,
  `tipo_pago` enum('ADELANTO','PAGO_TOTAL') DEFAULT NULL,
  `accion` enum('AGREGAR','QUITAR') NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ajuste_esperado`
--

INSERT INTO `ajuste_esperado` (`id_ajuste`, `sesion_id`, `tipo`, `modo_id`, `ref_id`, `ref2_id`, `tipo_documento`, `tipo_pago`, `accion`, `descripcion`, `monto`, `postulante_id`, `fecha`) VALUES
(20, 97, 'COBRO', 4, NULL, NULL, NULL, NULL, 'QUITAR', 'se anoto mal el visa', 303.80, 73, '2026-05-12 20:19:43'),
(23, 103, 'OTRO', NULL, NULL, NULL, NULL, NULL, 'AGREGAR', 'la venta estaba mal sumada', 26.90, 1, '2026-05-13 15:22:06'),
(24, 118, 'COBRO', 2, NULL, NULL, NULL, NULL, 'AGREGAR', 'se olvidó mandar', 4.90, 29, '2026-05-14 12:32:30'),
(26, 116, 'COBRO', 4, NULL, NULL, NULL, NULL, 'AGREGAR', 'falto agregar', 447.50, 17, '2026-05-14 12:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `registrado_por_id` int(11) DEFAULT NULL,
  `local_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` enum('A TIEMPO','TARDE','FALTA','EXTRA','TEMPRANO') NOT NULL DEFAULT 'A TIEMPO',
  `justificacion` text DEFAULT NULL,
  `observacion` enum('PROCEDE','NO PROCEDE','PENDIENTE') NOT NULL DEFAULT 'PENDIENTE',
  `llegada_puntualidad` enum('MUY_TEMPRANO','TEMPRANO','TARDE','MUY_TARDE') DEFAULT NULL,
  `abrio_puerta` tinyint(1) DEFAULT NULL,
  `aseo_personal` enum('SUCIO','REGULAR','LIMPIO') DEFAULT NULL,
  `vestimenta` enum('SUCIO','REGULAR','LIMPIO') DEFAULT NULL,
  `unas` enum('SUCIAS','REGULAR','LIMPIO') DEFAULT NULL,
  `cabello` enum('SUELTO','RECOGIDO','MONO') DEFAULT NULL,
  `salida_puntualidad` enum('MUY_TEMPRANO','TEMPRANO','TARDE','MUY_TARDE') DEFAULT NULL,
  `limpieza_espacio` enum('SUCIO','REGULAR','LIMPIO') DEFAULT NULL,
  `limpieza_local` tinyint(1) DEFAULT NULL,
  `ayudo_cerrar` tinyint(1) DEFAULT NULL,
  `ordeno_medicamentos` enum('DESORDENADO','REGULAR','ORDENADO') DEFAULT NULL,
  `comentarios_ficha` varchar(200) DEFAULT NULL,
  `turno_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asistencia`
--

INSERT INTO `asistencia` (`id_asistencia`, `postulante_id`, `registrado_por_id`, `local_id`, `fecha`, `estado`, `justificacion`, `observacion`, `llegada_puntualidad`, `abrio_puerta`, `aseo_personal`, `vestimenta`, `unas`, `cabello`, `salida_puntualidad`, `limpieza_espacio`, `limpieza_local`, `ayudo_cerrar`, `ordeno_medicamentos`, `comentarios_ficha`, `turno_id`) VALUES
(5, 1, NULL, 2, '2026-05-04', 'A TIEMPO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 1, NULL, 3, '2026-05-04', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 11, NULL, 4, '2026-05-06', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 4, NULL, NULL, '2026-05-06', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 5, NULL, NULL, '2026-05-06', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 56, NULL, 2, '2026-05-06', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 55, NULL, 4, '2026-05-06', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 53, NULL, 3, '2026-05-06', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 71, NULL, NULL, '2026-05-06', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 22, NULL, 3, '2026-05-07', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 22, NULL, NULL, '2026-05-08', 'TARDE', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 70, NULL, 3, '2026-05-08', 'TARDE', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 53, NULL, 3, '2026-05-08', 'EXTRA', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 70, NULL, 3, '2026-05-09', 'A TIEMPO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 53, NULL, 3, '2026-05-09', 'TARDE', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 60, NULL, 3, '2026-05-09', 'TARDE', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 53, NULL, 3, '2026-05-10', 'TARDE', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 60, NULL, 3, '2026-05-10', 'TEMPRANO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 22, 53, NULL, '2026-05-11', 'A TIEMPO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 53, 60, NULL, '2026-05-11', 'TEMPRANO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 4, 11, NULL, '2026-05-11', 'A TIEMPO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 60, 53, NULL, '2026-05-11', 'TEMPRANO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 29, 13, NULL, '2026-05-13', 'A TIEMPO', NULL, 'PENDIENTE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 4, 17, NULL, '2026-05-14', 'TEMPRANO', NULL, 'PENDIENTE', 'TEMPRANO', 0, 'LIMPIO', 'LIMPIO', 'LIMPIO', 'MONO', NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `asistencia_checklist`
--

CREATE TABLE `asistencia_checklist` (
  `id_asistencia_checklist` int(11) NOT NULL,
  `asistencia_id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `cumplido` tinyint(1) NOT NULL DEFAULT 0,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asistencia_checklist`
--

INSERT INTO `asistencia_checklist` (`id_asistencia_checklist`, `asistencia_id`, `checklist_id`, `cumplido`, `observacion`) VALUES
(11, 5, 1, 1, NULL),
(12, 5, 2, 1, NULL),
(13, 5, 3, 1, NULL),
(14, 5, 4, 1, NULL),
(15, 5, 5, 1, NULL),
(16, 6, 1, 1, NULL),
(17, 6, 2, 1, NULL),
(18, 6, 3, 1, NULL),
(19, 6, 4, 1, NULL),
(20, 6, 5, 1, NULL),
(31, 9, 1, 1, NULL),
(32, 9, 2, 1, NULL),
(33, 9, 3, 1, NULL),
(34, 9, 4, 1, NULL),
(35, 9, 5, 1, NULL),
(36, 10, 1, 1, NULL),
(37, 10, 2, 1, NULL),
(38, 10, 3, 1, NULL),
(39, 10, 4, 1, NULL),
(40, 10, 5, 1, NULL),
(41, 11, 1, 1, NULL),
(42, 11, 2, 1, NULL),
(43, 11, 3, 1, NULL),
(44, 11, 4, 1, NULL),
(45, 11, 5, 1, NULL),
(46, 12, 1, 1, NULL),
(47, 12, 2, 1, NULL),
(48, 12, 3, 1, NULL),
(49, 12, 4, 1, NULL),
(50, 12, 5, 1, NULL),
(51, 13, 1, 1, NULL),
(52, 13, 2, 1, NULL),
(53, 13, 3, 1, NULL),
(54, 13, 4, 1, NULL),
(55, 13, 5, 1, NULL),
(56, 14, 1, 1, NULL),
(57, 14, 2, 1, NULL),
(58, 14, 3, 1, NULL),
(59, 14, 4, 1, NULL),
(60, 14, 5, 1, NULL),
(61, 15, 1, 1, NULL),
(62, 15, 2, 1, NULL),
(63, 15, 3, 1, NULL),
(64, 15, 4, 1, NULL),
(65, 15, 5, 1, NULL),
(66, 16, 1, 1, NULL),
(67, 16, 2, 1, NULL),
(68, 16, 3, 1, NULL),
(69, 16, 4, 1, NULL),
(70, 16, 5, 1, NULL),
(71, 17, 1, 1, NULL),
(72, 17, 2, 1, NULL),
(73, 17, 3, 1, NULL),
(74, 17, 4, 1, NULL),
(75, 17, 5, 1, NULL),
(76, 18, 1, 1, NULL),
(77, 18, 2, 1, NULL),
(78, 18, 3, 1, NULL),
(79, 18, 4, 1, NULL),
(80, 18, 5, 1, NULL),
(81, 19, 1, 1, NULL),
(82, 19, 2, 1, NULL),
(83, 19, 3, 1, NULL),
(84, 19, 4, 1, NULL),
(85, 19, 5, 1, NULL),
(86, 20, 1, 1, NULL),
(87, 20, 2, 1, NULL),
(88, 20, 3, 1, NULL),
(89, 20, 4, 1, NULL),
(90, 20, 5, 1, NULL),
(91, 21, 1, 1, NULL),
(92, 21, 2, 1, NULL),
(93, 21, 3, 1, NULL),
(94, 21, 4, 1, NULL),
(95, 21, 5, 1, NULL),
(96, 22, 1, 1, NULL),
(97, 22, 2, 1, NULL),
(98, 22, 3, 1, NULL),
(99, 22, 4, 1, NULL),
(100, 22, 5, 1, NULL),
(101, 23, 1, 1, NULL),
(102, 23, 2, 1, NULL),
(103, 23, 3, 1, NULL),
(104, 23, 4, 1, NULL),
(105, 23, 5, 1, NULL),
(106, 24, 1, 1, NULL),
(107, 24, 2, 1, NULL),
(108, 24, 3, 1, NULL),
(109, 24, 4, 1, NULL),
(110, 24, 5, 1, NULL),
(141, 26, 1, 1, NULL),
(142, 26, 2, 1, NULL),
(143, 26, 3, 1, NULL),
(144, 26, 4, 1, NULL),
(145, 26, 5, 1, NULL),
(146, 26, 6, 1, NULL),
(201, 29, 1, 1, NULL),
(202, 29, 2, 1, NULL),
(203, 29, 3, 1, NULL),
(204, 29, 4, 1, NULL),
(205, 29, 5, 1, NULL),
(206, 29, 6, 1, NULL),
(207, 27, 1, 1, NULL),
(208, 27, 2, 1, NULL),
(209, 27, 3, 1, NULL),
(210, 27, 4, 1, NULL),
(211, 27, 5, 1, NULL),
(212, 27, 6, 1, NULL),
(213, 28, 1, 1, NULL),
(214, 28, 2, 1, NULL),
(215, 28, 3, 1, NULL),
(216, 28, 4, 1, NULL),
(217, 28, 5, 1, NULL),
(218, 28, 6, 1, NULL),
(243, 31, 1, 1, NULL),
(244, 31, 2, 1, NULL),
(245, 31, 3, 1, NULL),
(246, 31, 4, 1, NULL),
(247, 31, 5, 1, NULL),
(248, 31, 6, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `auditoria_cuadre`
--

CREATE TABLE `auditoria_cuadre` (
  `id_auditoria` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `accion` varchar(30) NOT NULL,
  `campo_modificado` varchar(100) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auditoria_sistema`
--

CREATE TABLE `auditoria_sistema` (
  `id_auditoria` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `tabla_afectada` varchar(100) NOT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `accion` varchar(30) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caja`
--

CREATE TABLE `caja` (
  `id_caja` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `caja`
--

INSERT INTO `caja` (`id_caja`, `local_id`, `descripcion`, `activo`) VALUES
(2, 2, 'SB2', 1),
(3, 3, 'SB3', 1),
(4, 4, 'SB4', 1),
(5, 3, 'SB5', 1),
(6, 2, 'SB6', 1),
(7, 3, 'SB7', 1);

-- --------------------------------------------------------

--
-- Table structure for table `checklist`
--

CREATE TABLE `checklist` (
  `id_checklist` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `tipo` enum('APERTURA','CIERRE') NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checklist`
--

INSERT INTO `checklist` (`id_checklist`, `descripcion`, `tipo`, `activo`) VALUES
(1, 'Llegó a tiempo', 'APERTURA', 1),
(2, 'Aseo personal conforme (bañado)', 'APERTURA', 1),
(3, 'Chaqueta limpia y planchada', 'APERTURA', 1),
(4, 'Uñas cortas y limpias', 'APERTURA', 1),
(5, 'Cabello recogido', 'APERTURA', 1),
(6, 'Hizo limpieza', 'CIERRE', 1);

-- --------------------------------------------------------

--
-- Table structure for table `concepto_gastos_local`
--

CREATE TABLE `concepto_gastos_local` (
  `id_concepto` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `concepto_gastos_local`
--

INSERT INTO `concepto_gastos_local` (`id_concepto`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Alquiler', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(2, 'Agua', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(3, 'Luz', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(4, 'Internet', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(5, 'Mantenimiento / Limpieza', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(6, 'Arbitrios / Municipalidad', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `concepto_penalidad`
--

CREATE TABLE `concepto_penalidad` (
  `id_concepto` int(11) NOT NULL,
  `tipo` enum('PENALIDAD','BENEFICIO','TARIFA') NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `monto` decimal(8,2) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `notas` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `concepto_penalidad`
--

INSERT INTO `concepto_penalidad` (`id_concepto`, `tipo`, `descripcion`, `monto`, `activo`, `notas`) VALUES
(1, 'PENALIDAD', 'Ausencia injustificada al turno', -30.00, 1, 'Descuento al trabajador que no se presentó a su turno asignado'),
(2, 'BENEFICIO', 'Bono por cobertura de turno ausente', 20.00, 1, 'Reconocimiento económico al trabajador que cubre el turno'),
(3, 'TARIFA', 'Comisión empresa por cobertura', 10.00, 1, 'Diferencia que retiene la empresa del descuento aplicado'),
(4, 'TARIFA', 'Costo de cambio de horario voluntario', -10.00, 1, 'Descuento al trabajador que solicita cambio de posición');

-- --------------------------------------------------------

--
-- Table structure for table `contacto_emergencia`
--

CREATE TABLE `contacto_emergencia` (
  `id_contacto_emergencia` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `parentesco` varchar(50) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `correccion_venta`
--

CREATE TABLE `correccion_venta` (
  `id_correccion` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `monto_anterior` decimal(10,2) NOT NULL,
  `monto_nuevo` decimal(10,2) NOT NULL,
  `motivo` varchar(300) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `correccion_venta`
--

INSERT INTO `correccion_venta` (`id_correccion`, `sesion_id`, `monto_anterior`, `monto_nuevo`, `motivo`, `usuario_id`, `fecha_registro`) VALUES
(1, 119, 1051.98, 1052.98, NULL, 1, '2026-05-14 00:38:34'),
(2, 119, 1052.98, 1053.98, NULL, 1, '2026-05-14 00:39:08'),
(3, 119, 1053.98, 1051.98, NULL, 1, '2026-05-14 00:43:50');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_cuadre`
--

CREATE TABLE `detalle_cuadre` (
  `sesion_id` int(11) NOT NULL,
  `monto_caja_exterior` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_monedas` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_billetes_caja` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_billetes_caja_fuerte` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_yape_plin` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_visas` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_bcp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_agente_bcp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_efectivo_contado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_contado_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_ventas_sistema` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_gastos_sistema` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_esperado_sistema` decimal(10,2) NOT NULL DEFAULT 0.00,
  `diferencia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `resultado_cuadre` enum('CONSISTENTE','SOBRANTE','FALTANTE') DEFAULT NULL,
  `observacion_cierre` text DEFAULT NULL,
  `saldo_proxima_efectivo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_proxima_agente_bcp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_proximo_dia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `num_operaciones_bcp` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detalle_cuadre`
--

INSERT INTO `detalle_cuadre` (`sesion_id`, `monto_caja_exterior`, `monto_monedas`, `monto_billetes_caja`, `monto_billetes_caja_fuerte`, `monto_yape_plin`, `monto_visas`, `monto_bcp`, `monto_agente_bcp`, `total_efectivo_contado`, `total_contado_general`, `total_ventas_sistema`, `total_gastos_sistema`, `total_esperado_sistema`, `diferencia`, `resultado_cuadre`, `observacion_cierre`, `saldo_proxima_efectivo`, `saldo_proxima_agente_bcp`, `saldo_proximo_dia`, `num_operaciones_bcp`) VALUES
(25, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 42817.46, 42817.46, 42817.46, 0.00, 0.00, 0.00, 42817.46, 'SOBRANTE', NULL, 42817.46, 0.00, 42817.46, 0),
(27, 1938.60, 1135.00, 27100.00, 810.00, 0.00, 0.00, 0.00, 12378.43, 43362.03, 43362.03, 989.10, 0.00, 43360.86, 1.17, 'SOBRANTE', NULL, 43362.03, 0.00, 43362.03, 0),
(28, 948.00, 1035.00, 500.00, 28560.00, 0.00, 0.00, 0.00, 12518.33, 43561.33, 43561.33, 341.40, 0.00, 43560.73, 0.60, 'SOBRANTE', NULL, 43561.33, 0.00, 43561.33, 219),
(29, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 27818.14, 27818.14, 27818.14, 0.00, 0.00, 0.00, 27818.14, 'SOBRANTE', NULL, 27818.14, 0.00, 27818.14, 0),
(30, 167.70, 290.00, 13200.00, 0.00, 0.00, 0.00, 0.00, 12721.28, 26378.98, 26378.98, 1479.55, 0.00, 26358.79, 20.19, 'SOBRANTE', NULL, 26378.98, 0.00, 26378.98, 0),
(31, 1513.30, 190.00, 7480.00, 0.00, 0.00, 0.00, 0.00, 17265.08, 26448.38, 26448.38, 784.60, 3.01, 26539.47, -91.09, 'FALTANTE', NULL, 26538.38, 0.00, 26538.38, 228),
(32, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 48905.89, 48905.89, 48905.89, 0.00, 0.00, 0.00, 48905.89, 'SOBRANTE', NULL, 48905.89, 0.00, 48905.89, 0),
(33, 464.90, 1360.00, 40910.00, 0.00, 0.00, 0.00, 0.00, 8511.83, 51246.73, 51246.73, 2334.99, 0.00, 51230.88, 15.85, 'SOBRANTE', NULL, 51246.73, 0.00, 51246.73, 0),
(35, 615.50, 1360.00, 17510.00, 0.00, 0.00, 0.00, 0.00, 21841.45, 41326.95, 41326.95, 1117.35, 11040.01, 41324.07, 2.88, 'SOBRANTE', NULL, 41326.95, 0.00, 41326.95, 0),
(37, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 35751.00, 35751.00, 35751.00, 0.00, 0.00, 0.00, 35751.00, 'SOBRANTE', NULL, 35751.00, 0.00, 35751.00, 0),
(38, 151.00, 1110.00, 50.00, 11750.00, 0.00, 0.00, 0.00, 23564.28, 36625.28, 36625.28, 1467.28, 0.00, 36605.48, 19.80, 'SOBRANTE', NULL, 36625.28, 0.00, 36625.28, 0),
(40, 89.40, 1110.00, 540.00, 13300.00, 0.00, 0.00, 0.00, 22143.63, 37183.03, 37183.03, 728.30, 0.00, 37201.98, -18.95, 'FALTANTE', NULL, 37183.03, 0.00, 37183.03, 121),
(42, 308.00, 180.00, 14380.00, 0.00, 0.00, 0.00, 0.00, 11844.88, 26712.88, 26712.88, 1263.99, 80.50, 26750.07, -37.19, 'FALTANTE', NULL, 26712.88, 0.00, 26712.88, 211),
(44, 455.10, 1460.00, 33310.00, 0.00, 0.00, 0.00, 0.00, 7517.65, 42742.75, 42742.75, 1460.30, 37.00, 42750.25, -7.50, 'FALTANTE', NULL, 42742.75, 0.00, 42742.75, 189),
(47, 353.60, 450.00, 12700.00, 0.00, 0.00, 0.00, 0.00, 13401.86, 26905.46, 26905.46, 801.50, 42.00, 26906.28, -0.82, 'FALTANTE', NULL, 26905.46, 0.00, 26905.46, 290),
(48, 290.30, 1035.00, 330.00, 36860.00, 0.00, 0.00, 0.00, 5296.76, 43812.06, 43812.06, 521.24, 0.00, 43800.47, 11.59, 'SOBRANTE', NULL, 43812.47, 0.00, 43812.47, 220),
(50, 1023.90, 1010.00, 0.00, 15600.00, 0.00, 0.00, 0.00, 20035.60, 37669.50, 37669.50, 783.40, 0.00, 37668.33, 1.17, 'SOBRANTE', NULL, 37669.50, 0.00, 37669.50, 156),
(51, 102.80, 910.00, 650.00, 11100.00, 0.00, 0.00, 0.00, 25239.90, 38002.70, 38002.70, 546.70, 0.00, 37996.10, 6.60, 'SOBRANTE', NULL, 37996.10, 0.00, 37996.10, 117),
(52, 612.00, 1260.00, 12000.00, 0.00, 0.00, 0.00, 0.00, 20028.22, 33900.22, 33900.22, 1280.50, 10000.00, 34023.25, -123.03, 'FALTANTE', NULL, 34020.22, 0.00, 34020.22, 0),
(53, 370.70, 1135.00, 1770.00, 8400.00, 0.00, 0.00, 0.00, 32377.12, 44052.82, 44052.82, 291.40, 0.00, 44056.77, -3.95, 'FALTANTE', NULL, 44052.82, 0.00, 44052.82, 206),
(55, 86.30, 470.00, 160.00, 17600.00, 0.00, 0.00, 0.00, 7802.18, 26118.48, 26118.48, 1269.49, 1087.00, 26106.95, 11.53, 'SOBRANTE', NULL, 26118.48, 0.00, 26118.48, 181),
(57, 152.60, 720.00, 950.00, 18100.00, 0.00, 0.00, 0.00, 18765.30, 38687.90, 38687.90, 945.40, 0.00, 38667.70, 20.20, 'SOBRANTE', NULL, 38687.90, 0.00, 38687.90, 171),
(58, 583.00, 1160.00, 27470.00, 0.00, 0.00, 0.00, 0.00, 6143.52, 35356.52, 35356.52, 1412.29, 74.00, 34852.11, 504.41, 'SOBRANTE', NULL, 35356.52, 0.00, 35356.52, 198),
(59, 216.40, 1145.00, 840.00, 19920.00, 0.00, 0.00, 0.00, 22298.51, 44419.91, 44419.91, 571.90, 0.00, 44438.62, -18.71, 'FALTANTE', NULL, 44419.91, 0.00, 44419.91, 189),
(60, 1617.20, 1060.00, 21370.00, 0.00, 0.00, 0.00, 0.00, 12914.18, 36961.38, 36961.38, 1583.45, 0.00, 36939.97, 21.41, 'SOBRANTE', NULL, 36961.38, 0.00, 36961.38, 0),
(61, 709.20, 200.00, 15620.00, 0.00, 0.00, 0.00, 0.00, 9317.52, 25846.72, 25846.72, 1323.20, 649.99, 25867.04, -20.32, 'FALTANTE', NULL, 25846.72, 0.00, 25846.72, 203),
(62, 504.30, 1145.00, 15220.00, 0.00, 0.00, 0.00, 0.00, 27892.33, 44761.63, 44761.63, 339.40, 90.20, 44669.11, 92.52, 'SOBRANTE', NULL, 44761.63, 0.00, 44761.63, 276),
(63, 76.60, 720.00, 1180.00, 18500.00, 0.00, 0.00, 0.00, 18523.48, 39000.08, 39000.08, 504.00, 0.00, 39003.80, -3.72, 'FALTANTE', NULL, 39000.08, 0.00, 39000.08, 162),
(64, 679.00, 1145.00, 23120.00, 0.00, 0.00, 0.00, 0.00, 20210.34, 45154.34, 45154.34, 588.60, 2.60, 45241.53, -87.19, 'FALTANTE', NULL, 45154.34, 0.00, 45154.34, 258),
(65, 546.90, 950.00, 17400.00, 0.00, 0.00, 0.00, 0.00, 19234.79, 38131.69, 38131.69, 1242.68, 76.60, 38127.46, 4.23, 'SOBRANTE', NULL, 38131.69, 0.00, 38131.69, 253),
(66, 315.60, 200.00, 22630.00, 0.00, 0.00, 0.00, 0.00, 2423.20, 25568.80, 25568.80, 1104.59, 532.00, 25558.51, 10.29, 'SOBRANTE', NULL, 25568.80, 0.00, 25568.80, 260),
(67, 286.40, 620.00, 4970.00, 15200.00, 0.00, 0.00, 0.00, 18583.54, 39659.94, 39659.94, 1073.40, 2.50, 39658.98, 0.96, 'SOBRANTE', NULL, 39657.44, 0.00, 39657.44, 175),
(68, 1799.80, 200.00, 11540.00, 0.00, 0.00, 0.00, 0.00, 9907.28, 23447.08, 23447.08, 1012.20, 2721.49, 23442.41, 4.67, 'SOBRANTE', NULL, 23447.08, 0.00, 23447.08, 319),
(69, 229.10, 1245.00, 1220.00, 16130.00, 0.00, 0.00, 0.00, 26712.14, 45536.24, 45536.24, 545.50, 0.00, 45533.34, 2.90, 'SOBRANTE', NULL, 45536.24, 0.00, 45536.24, 339),
(70, 538.50, 840.00, 14900.00, 0.00, 0.00, 0.00, 0.00, 22790.94, 39069.44, 39069.44, 1128.43, 79.80, 39067.92, 1.52, 'SOBRANTE', NULL, 39069.44, 0.00, 39069.44, 332),
(71, 183.80, 620.00, 1010.00, 11700.00, 0.00, 0.00, 0.00, 26556.48, 40070.28, 40070.28, 756.30, 0.00, 40050.84, 19.44, 'SOBRANTE', NULL, 40070.28, 0.00, 40070.28, 185),
(72, 825.00, 620.00, 0.00, 10200.00, 0.00, 0.00, 0.00, 29145.40, 40790.40, 40790.40, 919.27, 0.00, 40815.85, -25.45, 'FALTANTE', NULL, 40790.40, 0.00, 40790.40, 0),
(73, 153.10, 740.00, 14200.00, 0.00, 0.00, 0.00, 0.00, 24875.69, 39968.79, 39968.79, 902.45, 0.00, 39971.89, -3.10, 'FALTANTE', NULL, 39968.79, 0.00, 39968.79, 127),
(74, 395.60, 200.00, 8450.00, 0.00, 0.00, 0.00, 0.00, 14593.71, 23639.31, 23639.31, 799.76, 80.50, 23633.74, 5.57, 'SOBRANTE', NULL, 23639.31, 0.00, 23639.31, 149),
(75, 1386.50, 1245.00, 1530.00, 8500.00, 0.00, 0.00, 0.00, 33170.39, 45831.89, 45831.89, 366.00, 0.00, 45829.34, 2.55, 'SOBRANTE', NULL, 45831.89, 0.00, 45831.89, 167),
(86, 1766.10, 640.00, 11000.00, 0.00, 0.00, 0.00, 0.00, 41717.24, 55123.34, 55123.34, 1720.90, 0.00, 41689.69, 13433.65, 'SOBRANTE', NULL, 41717.24, 0.00, 41717.24, 0),
(87, 462.70, 200.00, 14450.00, 0.00, 0.00, 0.00, 0.00, 7500.58, 22613.28, 22613.28, 848.90, 943.00, 22594.51, 18.77, 'SOBRANTE', NULL, 22613.28, 0.00, 22613.28, 264),
(88, 222.10, 1245.00, 2110.00, 10030.00, 0.00, 0.00, 0.00, 32609.71, 46216.81, 46216.81, 534.00, 0.00, 46214.59, 2.22, 'SOBRANTE', NULL, 46216.81, 0.00, 46216.81, 222),
(89, 824.90, 710.00, 16020.00, 0.00, 0.00, 0.00, 0.00, 23455.55, 41010.45, 41010.45, 447.90, 0.00, 41006.80, 3.65, 'SOBRANTE', NULL, 41010.45, 0.00, 41010.45, 171),
(90, 227.50, 1245.00, 690.00, 21130.00, 0.00, 0.00, 0.00, 23553.06, 46845.56, 46845.56, 879.10, 0.00, 46846.01, -0.45, 'FALTANTE', NULL, 46845.56, 0.00, 46845.56, 186),
(91, 390.60, 640.00, 28600.00, 0.00, 0.00, 0.00, 0.00, 13655.60, 43286.20, 43286.20, 1569.63, 0.00, 43286.87, -0.67, 'FALTANTE', NULL, 43286.20, 0.00, 43286.20, 282),
(92, 274.50, 710.00, 2320.00, 11320.00, 0.00, 0.00, 0.00, 27105.95, 41730.45, 41730.45, 1189.68, 0.00, 41821.63, -91.18, 'FALTANTE', NULL, 41730.45, 0.00, 41730.45, 175),
(93, 335.50, 200.00, 21800.00, 0.00, 0.00, 0.00, 0.00, 247.23, 22582.73, 22582.73, 1821.09, 427.00, 22563.57, 19.16, 'SOBRANTE', NULL, 22582.73, 0.00, 22582.73, 187),
(94, 631.40, 1245.00, 0.00, 9730.00, 0.00, 0.00, 0.00, 35702.31, 47308.71, 47308.71, 498.15, 0.00, 47291.71, 17.00, 'SOBRANTE', NULL, 47308.71, 0.00, 37308.71, 241),
(95, 464.20, 200.00, 19500.00, 0.00, 0.00, 0.00, 0.00, 2570.40, 22734.60, 22734.60, 1186.80, 53.00, 22696.03, 38.57, 'SOBRANTE', NULL, 22734.60, 0.00, 32734.60, 292),
(96, 1277.60, 640.00, 22300.00, 0.00, 0.00, 0.00, 0.00, 20357.14, 44574.74, 44574.74, 1286.90, 0.00, 44573.10, 1.64, 'SOBRANTE', NULL, 44574.74, 0.00, 44574.74, 351),
(97, 656.10, 710.00, 9900.00, 0.00, 0.00, 0.00, 0.00, 30853.17, 42119.27, 42119.27, 769.90, 0.00, 41813.25, 306.02, 'SOBRANTE', NULL, 42119.27, 0.00, 42119.27, 119),
(100, 580.30, 430.00, 40880.00, 0.00, 0.00, 0.00, 0.00, 4227.44, 46117.74, 46117.74, 1555.45, 37.00, 46093.19, 24.55, 'SOBRANTE', NULL, 46117.74, 0.00, 46117.74, 206),
(101, 131.90, 710.00, 80.00, 19600.00, 0.00, 0.00, 0.00, 22204.72, 42726.62, 42726.62, 776.40, 0.00, 42663.37, 63.25, 'SOBRANTE', NULL, 42726.62, 0.00, 42726.62, 149),
(103, 86.90, 1145.00, 390.00, 29150.00, 0.00, 0.00, 0.00, 6633.36, 37405.26, 37405.26, 363.20, 0.00, 37500.81, -95.55, 'FALTANTE', NULL, 37405.26, 0.00, 37405.26, 150),
(105, 585.80, 300.00, 23000.00, 0.00, 0.00, 0.00, 0.00, 8870.19, 32755.99, 32755.99, 1497.29, 102.50, 32762.69, -6.70, 'FALTANTE', NULL, 32755.99, 0.00, 32755.99, 237),
(112, 1992.40, 197.50, 7840.00, 0.00, 0.00, 0.00, 0.00, 23018.95, 33048.85, 33048.85, 1094.60, 0.00, 33038.19, 10.66, 'SOBRANTE', NULL, 33048.85, 0.00, 33048.85, 220),
(113, 886.90, 420.00, 16100.00, 0.00, 0.00, 0.00, 0.00, 19410.28, 36817.18, 36817.18, 1710.70, 11000.00, 36828.44, -11.26, 'FALTANTE', NULL, 36817.18, 0.00, 36817.18, 0),
(114, 419.50, 910.00, 2210.00, 16300.00, 0.00, 0.00, 0.00, 11376.57, 31216.07, 31216.07, 833.40, 12000.00, 31252.22, -36.15, 'FALTANTE', NULL, 31236.07, 0.00, 31236.07, 158),
(115, 181.60, 1045.00, 1470.00, 1400.00, 0.00, 0.00, 0.00, 20975.50, 25072.10, 25072.10, 385.30, 0.00, 37671.36, -12599.26, 'FALTANTE', NULL, 37672.10, 0.00, 37672.10, 204),
(116, 1655.50, 170.00, 21050.00, 0.00, 0.00, 0.00, 0.00, 9421.05, 32296.55, 32296.55, 1927.68, 1702.50, 32692.23, -395.68, 'FALTANTE', NULL, 32246.55, 0.00, 32246.55, 239),
(117, 302.90, 430.00, 140.00, 34900.00, 0.00, 0.00, 0.00, 726.72, 36499.62, 36499.62, 656.95, 0.00, 37474.13, -974.51, 'FALTANTE', NULL, 37549.62, 0.00, 37549.62, 179),
(118, 273.10, 945.00, 2700.00, 24000.00, 0.00, 0.00, 0.00, 10179.62, 38097.72, 38097.72, 586.50, 0.00, 38099.70, -1.98, 'FALTANTE', NULL, 38097.72, 0.00, 38097.72, 194),
(119, 173.20, 1410.00, 3580.00, 16600.00, 0.00, 0.00, 0.00, 10096.79, 31859.99, 31859.99, 1051.98, 0.00, 31855.55, 4.44, 'SOBRANTE', NULL, 31859.99, 0.00, 31859.99, 150),
(120, 260.10, 945.00, 1510.00, 27770.00, 0.00, 0.00, 0.00, 8041.20, 38526.30, 38526.30, 577.50, 0.00, 38525.92, 0.38, 'SOBRANTE', NULL, 38526.30, 0.00, 38526.30, 186),
(121, 237.00, 1310.00, 1770.00, 19000.00, 0.00, 0.00, 0.00, 9845.44, 32162.44, 32162.44, 609.00, 11.00, 32152.69, 9.75, 'SOBRANTE', NULL, 32162.44, 0.00, 32162.44, 146),
(122, 1043.90, 170.00, 18190.00, 0.00, 0.00, 0.00, 0.00, 11199.32, 30603.22, 30603.22, 580.20, 1908.40, 30570.05, 33.17, 'SOBRANTE', NULL, 30603.22, 0.00, 30603.22, 245),
(123, 515.40, 420.00, 34600.00, 0.00, 0.00, 0.00, 0.00, 3118.14, 38653.54, 38653.54, 1090.50, 0.00, 38640.12, 13.42, 'SOBRANTE', NULL, 38653.54, 0.00, 38653.54, 169),
(124, 118.50, 520.00, 27500.00, 0.00, 0.00, 0.00, 0.00, 12058.04, 40196.54, 40196.54, 1544.52, 0.00, 40198.06, -1.52, 'FALTANTE', NULL, 40196.54, 0.00, 40196.54, 193),
(125, 252.60, 945.00, 330.00, 14600.00, 0.00, 0.00, 0.00, 22765.62, 38893.22, 38893.22, 533.10, 0.00, 38893.20, 0.02, 'SOBRANTE', NULL, 38893.22, 0.00, 38893.22, 190),
(126, 114.50, 170.00, 23100.00, 0.00, 0.00, 0.00, 0.00, 5861.65, 29246.15, 29246.15, 1214.50, 1542.10, 29248.22, -2.07, 'FALTANTE', NULL, 29246.15, 0.00, 29246.15, 166),
(127, 232.70, 1520.00, 1870.00, 17100.00, 0.00, 0.00, 0.00, 11777.87, 32500.57, 32500.57, 513.25, 0.00, 32491.89, 8.68, 'SOBRANTE', NULL, 32500.57, 0.00, 32500.57, 166);

-- --------------------------------------------------------

--
-- Table structure for table `especialidad`
--

CREATE TABLE `especialidad` (
  `id_especialidad` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `estado`
--

INSERT INTO `estado` (`id_estado`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Egreso', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'En curso', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Titulado', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(4, 'Trunco', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `estudio`
--

CREATE TABLE `estudio` (
  `id_estudio` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `institucion_id` int(11) NOT NULL,
  `estado_id` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `estudio`
--

INSERT INTO `estudio` (`id_estudio`, `postulante_id`, `tipo_id`, `institucion_id`, `estado_id`, `fecha_inicio`, `fecha_fin`) VALUES
(8, 11, 2, 4, 3, '2020-01-01', NULL),
(12, 57, 3, 5, 4, '2006-01-16', '2018-10-31'),
(15, 55, 2, 4, 3, '2022-04-15', '2025-01-17'),
(21, 69, 2, 4, 1, '2022-11-06', '2025-11-06'),
(25, 70, 2, 4, 2, '2023-05-06', '2026-05-06'),
(27, 71, 2, 5, 3, '2019-05-06', '2022-05-06'),
(29, 72, 3, 5, 4, '1985-01-01', NULL),
(34, 73, 2, 5, 2, '2000-01-01', NULL),
(35, 68, 3, 5, 3, '2026-05-06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `etapa`
--

CREATE TABLE `etapa` (
  `id_etapa` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `etapa`
--

INSERT INTO `etapa` (`id_etapa`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Pendiente', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'Entrevista', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Rechazado', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(4, 'Contratado', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(5, 'Suspendido', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(6, 'Despedido', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `experiencia`
--

CREATE TABLE `experiencia` (
  `id_experiencia` int(11) NOT NULL,
  `postulante_id` int(11) DEFAULT NULL,
  `empresa` varchar(150) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `funciones` varchar(150) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `experiencia`
--

INSERT INTO `experiencia` (`id_experiencia`, `postulante_id`, `empresa`, `cargo`, `funciones`, `fecha_inicio`, `fecha_fin`) VALUES
(4, 57, 'Supermercado wong', 'Supervisora de cajas', NULL, '2012-06-01', '2014-01-31'),
(7, 55, 'Mifarma', 'Técnico', NULL, '2023-02-27', '2026-01-30'),
(9, 72, 'Boticas', 'Administradora', NULL, '2000-01-01', '2011-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `genero`
--

CREATE TABLE `genero` (
  `id_genero` int(11) NOT NULL,
  `descripcion` varchar(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genero`
--

INSERT INTO `genero` (`id_genero`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Masculino', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'Femenino', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Otro', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `horario_slot`
--

CREATE TABLE `horario_slot` (
  `id_slot` int(11) NOT NULL,
  `semana_id` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `fecha_dia` date NOT NULL,
  `rol_horario_id` int(11) NOT NULL,
  `slot_num` tinyint(4) NOT NULL DEFAULT 1,
  `postulante_id` int(11) DEFAULT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `horario_slot`
--

INSERT INTO `horario_slot` (`id_slot`, `semana_id`, `local_id`, `turno_id`, `fecha_dia`, `rol_horario_id`, `slot_num`, `postulante_id`, `fecha_asignacion`) VALUES
(1, 1, 2, 1, '2026-05-11', 1, 1, 29, '2026-05-06 16:31:13'),
(2, 1, 2, 1, '2026-05-11', 2, 1, 13, '2026-05-07 01:40:34'),
(3, 1, 2, 1, '2026-05-11', 3, 1, NULL, NULL),
(4, 1, 2, 2, '2026-05-11', 1, 1, 29, '2026-05-06 16:31:06'),
(5, 1, 2, 2, '2026-05-11', 2, 1, 56, '2026-05-07 03:29:21'),
(6, 1, 2, 2, '2026-05-11', 3, 1, NULL, NULL),
(7, 1, 3, 1, '2026-05-11', 1, 1, 22, '2026-05-06 13:49:10'),
(8, 1, 3, 1, '2026-05-11', 1, 2, 69, '2026-05-06 18:13:20'),
(9, 1, 3, 1, '2026-05-11', 1, 3, 57, '2026-05-06 17:07:47'),
(10, 1, 3, 1, '2026-05-11', 2, 1, 4, '2026-05-06 15:46:40'),
(11, 1, 3, 1, '2026-05-11', 2, 2, 45, '2026-05-06 15:51:42'),
(12, 1, 3, 1, '2026-05-11', 3, 1, 5, '2026-05-06 15:41:03'),
(13, 1, 3, 1, '2026-05-11', 3, 2, NULL, NULL),
(14, 1, 3, 2, '2026-05-11', 1, 1, 53, '2026-05-06 16:22:39'),
(15, 1, 3, 2, '2026-05-11', 1, 2, 61, '2026-05-06 19:38:10'),
(16, 1, 3, 2, '2026-05-11', 1, 3, 57, '2026-05-06 17:20:52'),
(17, 1, 3, 2, '2026-05-11', 2, 1, 10, '2026-05-06 19:35:18'),
(18, 1, 3, 2, '2026-05-11', 2, 2, 54, '2026-05-06 16:24:22'),
(19, 1, 3, 2, '2026-05-11', 3, 1, 5, '2026-05-06 15:41:15'),
(20, 1, 3, 2, '2026-05-11', 3, 2, 60, '2026-05-09 20:35:04'),
(21, 1, 4, 1, '2026-05-11', 1, 1, 73, '2026-05-07 02:07:21'),
(22, 1, 4, 1, '2026-05-11', 2, 1, 55, '2026-05-06 16:46:08'),
(23, 1, 4, 1, '2026-05-11', 3, 1, NULL, NULL),
(24, 1, 4, 2, '2026-05-11', 1, 1, 71, '2026-05-06 21:06:40'),
(25, 1, 4, 2, '2026-05-11', 2, 1, 11, '2026-05-06 13:36:45'),
(26, 1, 4, 2, '2026-05-11', 3, 1, NULL, NULL),
(27, 1, 2, 1, '2026-05-12', 1, 1, 13, '2026-05-07 02:02:44'),
(28, 1, 2, 1, '2026-05-12', 2, 1, 45, '2026-05-06 15:50:04'),
(29, 1, 2, 1, '2026-05-12', 3, 1, 54, '2026-05-08 20:44:45'),
(30, 1, 2, 2, '2026-05-12', 1, 1, 69, '2026-05-06 18:13:09'),
(31, 1, 2, 2, '2026-05-12', 2, 1, 56, '2026-05-06 15:49:15'),
(32, 1, 2, 2, '2026-05-12', 3, 1, NULL, NULL),
(33, 1, 3, 1, '2026-05-12', 1, 1, 22, '2026-05-06 13:49:12'),
(34, 1, 3, 1, '2026-05-12', 1, 2, 70, '2026-05-06 21:15:32'),
(35, 1, 3, 1, '2026-05-12', 1, 3, 57, '2026-05-06 17:07:48'),
(36, 1, 3, 1, '2026-05-12', 2, 1, 4, '2026-05-06 14:47:27'),
(37, 1, 3, 1, '2026-05-12', 2, 2, 10, '2026-05-06 16:20:36'),
(38, 1, 3, 1, '2026-05-12', 3, 1, 5, '2026-05-06 15:41:05'),
(39, 1, 3, 1, '2026-05-12', 3, 2, NULL, NULL),
(40, 1, 3, 2, '2026-05-12', 1, 1, 53, '2026-05-06 16:22:40'),
(41, 1, 3, 2, '2026-05-12', 1, 2, 61, '2026-05-06 19:38:10'),
(42, 1, 3, 2, '2026-05-12', 1, 3, 17, '2026-05-06 16:37:32'),
(43, 1, 3, 2, '2026-05-12', 2, 1, 4, '2026-05-06 14:58:35'),
(44, 1, 3, 2, '2026-05-12', 2, 2, 10, '2026-05-06 16:20:37'),
(45, 1, 3, 2, '2026-05-12', 3, 1, 5, '2026-05-06 15:41:15'),
(46, 1, 3, 2, '2026-05-12', 3, 2, 60, '2026-05-09 20:35:24'),
(47, 1, 4, 1, '2026-05-12', 1, 1, 73, '2026-05-07 02:07:21'),
(48, 1, 4, 1, '2026-05-12', 2, 1, 55, '2026-05-06 16:48:36'),
(49, 1, 4, 1, '2026-05-12', 3, 1, NULL, NULL),
(50, 1, 4, 2, '2026-05-12', 1, 1, 71, '2026-05-06 21:06:45'),
(51, 1, 4, 2, '2026-05-12', 2, 1, 11, '2026-05-06 13:40:25'),
(52, 1, 4, 2, '2026-05-12', 3, 1, NULL, NULL),
(53, 1, 2, 1, '2026-05-13', 1, 1, 29, '2026-05-08 16:55:11'),
(54, 1, 2, 1, '2026-05-13', 2, 1, 13, '2026-05-07 01:40:34'),
(55, 1, 2, 1, '2026-05-13', 3, 1, NULL, NULL),
(56, 1, 2, 2, '2026-05-13', 1, 1, 29, '2026-05-13 17:51:32'),
(57, 1, 2, 2, '2026-05-13', 2, 1, 56, '2026-05-06 15:48:33'),
(58, 1, 2, 2, '2026-05-13', 3, 1, NULL, NULL),
(59, 1, 3, 1, '2026-05-13', 1, 1, 22, '2026-05-06 13:49:13'),
(60, 1, 3, 1, '2026-05-13', 1, 2, 70, '2026-05-06 21:21:09'),
(61, 1, 3, 1, '2026-05-13', 1, 3, 53, '2026-05-06 16:36:06'),
(62, 1, 3, 1, '2026-05-13', 2, 1, 10, '2026-05-06 16:20:45'),
(63, 1, 3, 1, '2026-05-13', 2, 2, 4, '2026-05-06 16:37:55'),
(64, 1, 3, 1, '2026-05-13', 3, 1, 5, '2026-05-06 15:41:06'),
(65, 1, 3, 1, '2026-05-13', 3, 2, NULL, NULL),
(66, 1, 3, 2, '2026-05-13', 1, 1, 69, '2026-05-06 18:19:23'),
(67, 1, 3, 2, '2026-05-13', 1, 2, 61, '2026-05-06 18:30:05'),
(68, 1, 3, 2, '2026-05-13', 1, 3, 17, '2026-05-06 16:37:33'),
(69, 1, 3, 2, '2026-05-13', 2, 1, 70, '2026-05-06 21:09:35'),
(70, 1, 3, 2, '2026-05-13', 2, 2, 10, '2026-05-06 16:25:23'),
(71, 1, 3, 2, '2026-05-13', 3, 1, 5, '2026-05-06 15:41:16'),
(72, 1, 3, 2, '2026-05-13', 3, 2, 60, '2026-05-09 20:35:27'),
(73, 1, 4, 1, '2026-05-13', 1, 1, 11, '2026-05-06 13:41:02'),
(74, 1, 4, 1, '2026-05-13', 2, 1, 55, '2026-05-06 16:46:12'),
(75, 1, 4, 1, '2026-05-13', 3, 1, NULL, NULL),
(76, 1, 4, 2, '2026-05-13', 1, 1, 71, '2026-05-06 21:07:12'),
(77, 1, 4, 2, '2026-05-13', 2, 1, 11, '2026-05-06 13:40:43'),
(78, 1, 4, 2, '2026-05-13', 3, 1, NULL, NULL),
(79, 1, 2, 1, '2026-05-14', 1, 1, 29, '2026-05-06 16:29:00'),
(80, 1, 2, 1, '2026-05-14', 2, 1, 45, '2026-05-06 15:51:53'),
(81, 1, 2, 1, '2026-05-14', 3, 1, NULL, NULL),
(82, 1, 2, 2, '2026-05-14', 1, 1, 69, '2026-05-06 18:13:11'),
(83, 1, 2, 2, '2026-05-14', 2, 1, 56, '2026-05-06 15:49:21'),
(84, 1, 2, 2, '2026-05-14', 3, 1, NULL, NULL),
(85, 1, 3, 1, '2026-05-14', 1, 1, 22, '2026-05-06 13:49:15'),
(86, 1, 3, 1, '2026-05-14', 1, 2, 57, '2026-05-06 21:03:34'),
(87, 1, 3, 1, '2026-05-14', 1, 3, 17, '2026-05-06 16:41:15'),
(88, 1, 3, 1, '2026-05-14', 2, 1, 4, '2026-05-06 06:35:50'),
(89, 1, 3, 1, '2026-05-14', 2, 2, 54, '2026-05-06 16:23:46'),
(90, 1, 3, 1, '2026-05-14', 3, 1, 5, '2026-05-06 15:41:07'),
(91, 1, 3, 1, '2026-05-14', 3, 2, NULL, NULL),
(92, 1, 3, 2, '2026-05-14', 1, 1, 70, '2026-05-06 21:14:03'),
(93, 1, 3, 2, '2026-05-14', 1, 2, 61, '2026-05-06 19:41:11'),
(94, 1, 3, 2, '2026-05-14', 1, 3, 57, '2026-05-06 17:08:15'),
(95, 1, 3, 2, '2026-05-14', 2, 1, 4, '2026-05-06 14:59:53'),
(96, 1, 3, 2, '2026-05-14', 2, 2, 54, '2026-05-06 16:23:52'),
(97, 1, 3, 2, '2026-05-14', 3, 1, 5, '2026-05-06 15:41:17'),
(98, 1, 3, 2, '2026-05-14', 3, 2, 60, '2026-05-09 20:35:27'),
(99, 1, 4, 1, '2026-05-14', 1, 1, 13, '2026-05-07 03:27:49'),
(100, 1, 4, 1, '2026-05-14', 2, 1, 11, '2026-05-06 13:40:49'),
(101, 1, 4, 1, '2026-05-14', 3, 1, NULL, NULL),
(102, 1, 4, 2, '2026-05-14', 1, 1, 71, '2026-05-06 21:07:13'),
(103, 1, 4, 2, '2026-05-14', 2, 1, 55, '2026-05-06 16:46:52'),
(104, 1, 4, 2, '2026-05-14', 3, 1, NULL, NULL),
(105, 1, 2, 1, '2026-05-15', 1, 1, 29, '2026-05-06 16:30:56'),
(106, 1, 2, 1, '2026-05-15', 2, 1, 13, '2026-05-07 01:41:00'),
(107, 1, 2, 1, '2026-05-15', 3, 1, NULL, NULL),
(108, 1, 2, 2, '2026-05-15', 1, 1, 29, '2026-05-06 16:30:59'),
(109, 1, 2, 2, '2026-05-15', 2, 1, 13, '2026-05-07 03:07:47'),
(110, 1, 2, 2, '2026-05-15', 3, 1, NULL, NULL),
(111, 1, 3, 1, '2026-05-15', 1, 1, 22, '2026-05-06 13:49:17'),
(112, 1, 3, 1, '2026-05-15', 1, 2, 57, '2026-05-07 00:39:35'),
(113, 1, 3, 1, '2026-05-15', 1, 3, 17, '2026-05-06 16:41:50'),
(114, 1, 3, 1, '2026-05-15', 2, 1, 54, '2026-05-08 18:09:31'),
(115, 1, 3, 1, '2026-05-15', 2, 2, 45, '2026-05-06 15:59:58'),
(116, 1, 3, 1, '2026-05-15', 3, 1, 5, '2026-05-06 15:41:08'),
(117, 1, 3, 1, '2026-05-15', 3, 2, NULL, NULL),
(118, 1, 3, 2, '2026-05-15', 1, 1, 53, '2026-05-06 16:22:46'),
(119, 1, 3, 2, '2026-05-15', 1, 2, 70, '2026-05-06 21:13:24'),
(120, 1, 3, 2, '2026-05-15', 1, 3, 69, '2026-05-06 18:14:12'),
(121, 1, 3, 2, '2026-05-15', 2, 1, 4, '2026-05-07 20:32:12'),
(122, 1, 3, 2, '2026-05-15', 2, 2, 54, '2026-05-06 16:27:26'),
(123, 1, 3, 2, '2026-05-15', 3, 1, 5, '2026-05-06 15:41:18'),
(124, 1, 3, 2, '2026-05-15', 3, 2, 60, '2026-05-09 20:35:09'),
(125, 1, 4, 1, '2026-05-15', 1, 1, 73, '2026-05-07 03:15:29'),
(126, 1, 4, 1, '2026-05-15', 2, 1, 11, '2026-05-06 13:40:30'),
(127, 1, 4, 1, '2026-05-15', 3, 1, NULL, NULL),
(128, 1, 4, 2, '2026-05-15', 1, 1, 73, '2026-05-07 04:42:31'),
(129, 1, 4, 2, '2026-05-15', 2, 1, 11, '2026-05-06 13:40:31'),
(130, 1, 4, 2, '2026-05-15', 3, 1, NULL, NULL),
(131, 1, 2, 1, '2026-05-16', 1, 1, 13, '2026-05-07 02:18:21'),
(132, 1, 2, 1, '2026-05-16', 2, 1, 56, '2026-05-07 03:28:43'),
(133, 1, 2, 1, '2026-05-16', 3, 1, NULL, NULL),
(134, 1, 2, 2, '2026-05-16', 1, 1, 29, '2026-05-11 03:08:41'),
(135, 1, 2, 2, '2026-05-16', 2, 1, 56, '2026-05-07 03:09:43'),
(136, 1, 2, 2, '2026-05-16', 3, 1, NULL, NULL),
(137, 1, 3, 1, '2026-05-16', 1, 1, 53, '2026-05-06 16:31:00'),
(138, 1, 3, 1, '2026-05-16', 1, 2, 45, '2026-05-07 03:12:16'),
(139, 1, 3, 1, '2026-05-16', 1, 3, 17, '2026-05-06 16:36:40'),
(140, 1, 3, 1, '2026-05-16', 2, 1, 10, '2026-05-06 16:20:55'),
(141, 1, 3, 1, '2026-05-16', 2, 2, 54, '2026-05-06 16:25:23'),
(142, 1, 3, 1, '2026-05-16', 3, 1, 5, '2026-05-06 15:41:08'),
(143, 1, 3, 1, '2026-05-16', 3, 2, NULL, NULL),
(144, 1, 3, 2, '2026-05-16', 1, 1, 70, '2026-05-07 01:47:42'),
(145, 1, 3, 2, '2026-05-16', 1, 2, 61, '2026-05-06 18:30:11'),
(146, 1, 3, 2, '2026-05-16', 1, 3, 57, '2026-05-11 03:09:00'),
(147, 1, 3, 2, '2026-05-16', 2, 1, 45, '2026-05-07 03:38:37'),
(148, 1, 3, 2, '2026-05-16', 2, 2, 54, '2026-05-06 16:25:35'),
(149, 1, 3, 2, '2026-05-16', 3, 1, 5, '2026-05-06 15:41:19'),
(150, 1, 3, 2, '2026-05-16', 3, 2, NULL, NULL),
(151, 1, 4, 1, '2026-05-16', 1, 1, 73, '2026-05-07 02:19:09'),
(152, 1, 4, 1, '2026-05-16', 2, 1, 55, '2026-05-06 16:46:22'),
(153, 1, 4, 1, '2026-05-16', 3, 1, NULL, NULL),
(154, 1, 4, 2, '2026-05-16', 1, 1, 22, '2026-05-07 03:25:39'),
(155, 1, 4, 2, '2026-05-16', 2, 1, 71, '2026-05-06 21:06:31'),
(156, 1, 4, 2, '2026-05-16', 3, 1, NULL, NULL),
(157, 1, 2, 1, '2026-05-17', 1, 1, 29, '2026-05-06 16:49:56'),
(158, 1, 2, 1, '2026-05-17', 2, 1, 13, '2026-05-07 01:54:30'),
(159, 1, 2, 1, '2026-05-17', 3, 1, NULL, NULL),
(160, 1, 2, 2, '2026-05-17', 1, 1, 69, '2026-05-06 18:13:12'),
(161, 1, 2, 2, '2026-05-17', 2, 1, 56, '2026-05-06 15:49:27'),
(162, 1, 2, 2, '2026-05-17', 3, 1, NULL, NULL),
(163, 1, 3, 1, '2026-05-17', 1, 1, 22, '2026-05-06 13:50:25'),
(164, 1, 3, 1, '2026-05-17', 1, 2, 57, '2026-05-06 17:14:46'),
(165, 1, 3, 1, '2026-05-17', 1, 3, 17, '2026-05-06 16:36:42'),
(166, 1, 3, 1, '2026-05-17', 2, 1, 45, '2026-05-06 15:48:24'),
(167, 1, 3, 1, '2026-05-17', 2, 2, 10, '2026-05-06 16:21:04'),
(168, 1, 3, 1, '2026-05-17', 3, 1, NULL, NULL),
(169, 1, 3, 1, '2026-05-17', 3, 2, NULL, NULL),
(170, 1, 3, 2, '2026-05-17', 1, 1, 53, '2026-05-06 16:22:51'),
(171, 1, 3, 2, '2026-05-17', 1, 2, 61, '2026-05-06 18:30:12'),
(172, 1, 3, 2, '2026-05-17', 1, 3, 70, '2026-05-06 21:10:28'),
(173, 1, 3, 2, '2026-05-17', 2, 1, 4, '2026-05-07 18:54:18'),
(174, 1, 3, 2, '2026-05-17', 2, 2, 54, '2026-05-06 16:27:43'),
(175, 1, 3, 2, '2026-05-17', 3, 1, NULL, NULL),
(176, 1, 3, 2, '2026-05-17', 3, 2, 60, '2026-05-09 20:35:11'),
(177, 1, 4, 1, '2026-05-17', 1, 1, 73, '2026-05-07 02:07:23'),
(178, 1, 4, 1, '2026-05-17', 2, 1, 11, '2026-05-06 13:40:11'),
(179, 1, 4, 1, '2026-05-17', 3, 1, NULL, NULL),
(180, 1, 4, 2, '2026-05-17', 1, 1, 71, '2026-05-06 21:07:17'),
(181, 1, 4, 2, '2026-05-17', 2, 1, 55, '2026-05-06 16:46:31'),
(182, 1, 4, 2, '2026-05-17', 3, 1, NULL, NULL),
(183, 2, 2, 1, '2026-05-18', 1, 1, 29, '2026-05-11 16:04:00'),
(184, 2, 2, 1, '2026-05-18', 2, 1, 13, '2026-05-11 08:21:26'),
(185, 2, 2, 1, '2026-05-18', 3, 1, NULL, NULL),
(186, 2, 2, 2, '2026-05-18', 1, 1, 29, '2026-05-11 08:21:26'),
(187, 2, 2, 2, '2026-05-18', 2, 1, 56, '2026-05-11 08:21:26'),
(188, 2, 2, 2, '2026-05-18', 3, 1, NULL, NULL),
(189, 2, 3, 1, '2026-05-18', 1, 1, 22, '2026-05-11 08:21:26'),
(190, 2, 3, 1, '2026-05-18', 1, 2, 69, '2026-05-11 08:21:26'),
(191, 2, 3, 1, '2026-05-18', 1, 3, 57, '2026-05-11 08:21:26'),
(192, 2, 3, 1, '2026-05-18', 2, 1, 54, '2026-05-12 14:55:29'),
(193, 2, 3, 1, '2026-05-18', 2, 2, 45, '2026-05-11 08:21:26'),
(194, 2, 3, 1, '2026-05-18', 3, 1, 5, '2026-05-11 08:21:26'),
(195, 2, 3, 1, '2026-05-18', 3, 2, NULL, NULL),
(196, 2, 3, 2, '2026-05-18', 1, 1, 53, '2026-05-11 16:16:35'),
(197, 2, 3, 2, '2026-05-18', 1, 2, 61, '2026-05-11 08:21:26'),
(198, 2, 3, 2, '2026-05-18', 1, 3, 57, '2026-05-11 08:21:26'),
(199, 2, 3, 2, '2026-05-18', 2, 1, 10, '2026-05-11 08:21:26'),
(200, 2, 3, 2, '2026-05-18', 2, 2, 54, '2026-05-11 08:21:26'),
(201, 2, 3, 2, '2026-05-18', 3, 1, 5, '2026-05-11 08:21:26'),
(202, 2, 3, 2, '2026-05-18', 3, 2, 60, '2026-05-11 08:21:26'),
(203, 2, 4, 1, '2026-05-18', 1, 1, 73, '2026-05-11 08:21:26'),
(204, 2, 4, 1, '2026-05-18', 2, 1, 55, '2026-05-11 08:21:26'),
(205, 2, 4, 1, '2026-05-18', 3, 1, NULL, NULL),
(206, 2, 4, 2, '2026-05-18', 1, 1, 71, '2026-05-11 08:21:26'),
(207, 2, 4, 2, '2026-05-18', 2, 1, 11, '2026-05-11 16:13:16'),
(208, 2, 4, 2, '2026-05-18', 3, 1, NULL, NULL),
(209, 2, 2, 1, '2026-05-19', 1, 1, 29, '2026-05-11 16:40:51'),
(210, 2, 2, 1, '2026-05-19', 2, 1, 45, '2026-05-11 08:21:26'),
(211, 2, 2, 1, '2026-05-19', 3, 1, 13, '2026-05-12 15:29:46'),
(212, 2, 2, 2, '2026-05-19', 1, 1, 69, '2026-05-11 08:21:26'),
(213, 2, 2, 2, '2026-05-19', 2, 1, 56, '2026-05-11 08:21:26'),
(214, 2, 2, 2, '2026-05-19', 3, 1, NULL, NULL),
(215, 2, 3, 1, '2026-05-19', 1, 1, 22, '2026-05-11 08:21:26'),
(216, 2, 3, 1, '2026-05-19', 1, 2, 70, '2026-05-11 08:21:26'),
(217, 2, 3, 1, '2026-05-19', 1, 3, 57, '2026-05-11 08:21:26'),
(218, 2, 3, 1, '2026-05-19', 2, 1, 54, '2026-05-12 14:55:44'),
(219, 2, 3, 1, '2026-05-19', 2, 2, 10, '2026-05-11 18:38:21'),
(220, 2, 3, 1, '2026-05-19', 3, 1, 5, '2026-05-11 08:21:26'),
(221, 2, 3, 1, '2026-05-19', 3, 2, NULL, NULL),
(222, 2, 3, 2, '2026-05-19', 1, 1, 53, '2026-05-11 16:17:09'),
(223, 2, 3, 2, '2026-05-19', 1, 2, 61, '2026-05-11 08:21:26'),
(224, 2, 3, 2, '2026-05-19', 1, 3, 17, '2026-05-11 08:21:26'),
(225, 2, 3, 2, '2026-05-19', 2, 1, 4, '2026-05-11 08:21:26'),
(226, 2, 3, 2, '2026-05-19', 2, 2, 10, '2026-05-11 08:21:26'),
(227, 2, 3, 2, '2026-05-19', 3, 1, 5, '2026-05-11 08:21:26'),
(228, 2, 3, 2, '2026-05-19', 3, 2, 60, '2026-05-11 08:21:26'),
(229, 2, 4, 1, '2026-05-19', 1, 1, 73, '2026-05-11 08:21:26'),
(230, 2, 4, 1, '2026-05-19', 2, 1, 55, '2026-05-11 08:21:26'),
(231, 2, 4, 1, '2026-05-19', 3, 1, NULL, NULL),
(232, 2, 4, 2, '2026-05-19', 1, 1, 73, '2026-05-11 20:29:59'),
(233, 2, 4, 2, '2026-05-19', 2, 1, 11, '2026-05-11 16:25:04'),
(234, 2, 4, 2, '2026-05-19', 3, 1, NULL, NULL),
(235, 2, 2, 1, '2026-05-20', 1, 1, 29, '2026-05-11 16:04:07'),
(236, 2, 2, 1, '2026-05-20', 2, 1, 45, '2026-05-11 16:42:27'),
(237, 2, 2, 1, '2026-05-20', 3, 1, NULL, NULL),
(238, 2, 2, 2, '2026-05-20', 1, 1, 29, '2026-05-11 08:21:26'),
(239, 2, 2, 2, '2026-05-20', 2, 1, 56, '2026-05-11 08:21:26'),
(240, 2, 2, 2, '2026-05-20', 3, 1, NULL, NULL),
(241, 2, 3, 1, '2026-05-20', 1, 1, 22, '2026-05-11 08:21:26'),
(242, 2, 3, 1, '2026-05-20', 1, 2, 70, '2026-05-11 08:21:26'),
(243, 2, 3, 1, '2026-05-20', 1, 3, 53, '2026-05-11 16:17:15'),
(244, 2, 3, 1, '2026-05-20', 2, 1, 10, '2026-05-11 08:21:26'),
(245, 2, 3, 1, '2026-05-20', 2, 2, 54, '2026-05-12 15:00:22'),
(246, 2, 3, 1, '2026-05-20', 3, 1, 5, '2026-05-11 08:21:26'),
(247, 2, 3, 1, '2026-05-20', 3, 2, NULL, NULL),
(248, 2, 3, 2, '2026-05-20', 1, 1, 69, '2026-05-11 08:21:26'),
(249, 2, 3, 2, '2026-05-20', 1, 2, 61, '2026-05-11 08:21:26'),
(250, 2, 3, 2, '2026-05-20', 1, 3, 17, '2026-05-11 08:21:26'),
(251, 2, 3, 2, '2026-05-20', 2, 1, NULL, NULL),
(252, 2, 3, 2, '2026-05-20', 2, 2, 10, '2026-05-11 08:21:26'),
(253, 2, 3, 2, '2026-05-20', 3, 1, 5, '2026-05-11 08:21:26'),
(254, 2, 3, 2, '2026-05-20', 3, 2, 60, '2026-05-11 08:21:26'),
(255, 2, 4, 1, '2026-05-20', 1, 1, 71, '2026-05-12 20:25:15'),
(256, 2, 4, 1, '2026-05-20', 2, 1, 55, '2026-05-11 08:21:26'),
(257, 2, 4, 1, '2026-05-20', 3, 1, NULL, NULL),
(258, 2, 4, 2, '2026-05-20', 1, 1, 73, '2026-05-12 20:25:52'),
(259, 2, 4, 2, '2026-05-20', 2, 1, 71, '2026-05-12 20:25:32'),
(260, 2, 4, 2, '2026-05-20', 3, 1, NULL, NULL),
(261, 2, 2, 1, '2026-05-21', 1, 1, 29, '2026-05-11 08:21:26'),
(262, 2, 2, 1, '2026-05-21', 2, 1, 13, '2026-05-11 16:43:06'),
(263, 2, 2, 1, '2026-05-21', 3, 1, NULL, NULL),
(264, 2, 2, 2, '2026-05-21', 1, 1, 69, '2026-05-11 08:21:26'),
(265, 2, 2, 2, '2026-05-21', 2, 1, 56, '2026-05-11 08:21:26'),
(266, 2, 2, 2, '2026-05-21', 3, 1, NULL, NULL),
(267, 2, 3, 1, '2026-05-21', 1, 1, 22, '2026-05-11 08:21:26'),
(268, 2, 3, 1, '2026-05-21', 1, 2, 57, '2026-05-11 08:21:26'),
(269, 2, 3, 1, '2026-05-21', 1, 3, 17, '2026-05-14 16:58:48'),
(270, 2, 3, 1, '2026-05-21', 2, 1, 4, '2026-05-12 14:44:12'),
(271, 2, 3, 1, '2026-05-21', 2, 2, 54, '2026-05-11 08:21:26'),
(272, 2, 3, 1, '2026-05-21', 3, 1, 5, '2026-05-11 08:21:26'),
(273, 2, 3, 1, '2026-05-21', 3, 2, NULL, NULL),
(274, 2, 3, 2, '2026-05-21', 1, 1, 70, '2026-05-11 08:21:26'),
(275, 2, 3, 2, '2026-05-21', 1, 2, 61, '2026-05-12 01:50:08'),
(276, 2, 3, 2, '2026-05-21', 1, 3, 57, '2026-05-11 08:21:26'),
(277, 2, 3, 2, '2026-05-21', 2, 1, 4, '2026-05-11 08:21:26'),
(278, 2, 3, 2, '2026-05-21', 2, 2, 54, '2026-05-11 08:21:26'),
(279, 2, 3, 2, '2026-05-21', 3, 1, 5, '2026-05-11 08:21:26'),
(280, 2, 3, 2, '2026-05-21', 3, 2, 60, '2026-05-11 08:21:26'),
(281, 2, 4, 1, '2026-05-21', 1, 1, 73, '2026-05-12 20:25:59'),
(282, 2, 4, 1, '2026-05-21', 2, 1, 71, '2026-05-14 20:25:38'),
(283, 2, 4, 1, '2026-05-21', 3, 1, NULL, NULL),
(284, 2, 4, 2, '2026-05-21', 1, 1, NULL, NULL),
(285, 2, 4, 2, '2026-05-21', 2, 1, 55, '2026-05-11 08:21:26'),
(286, 2, 4, 2, '2026-05-21', 3, 1, NULL, NULL),
(287, 2, 2, 1, '2026-05-22', 1, 1, 29, '2026-05-11 08:21:26'),
(288, 2, 2, 1, '2026-05-22', 2, 1, 13, '2026-05-11 08:21:26'),
(289, 2, 2, 1, '2026-05-22', 3, 1, NULL, NULL),
(290, 2, 2, 2, '2026-05-22', 1, 1, 29, '2026-05-11 08:21:26'),
(291, 2, 2, 2, '2026-05-22', 2, 1, 13, '2026-05-11 08:21:26'),
(292, 2, 2, 2, '2026-05-22', 3, 1, NULL, NULL),
(293, 2, 3, 1, '2026-05-22', 1, 1, 22, '2026-05-11 08:21:26'),
(294, 2, 3, 1, '2026-05-22', 1, 2, 57, '2026-05-11 08:21:26'),
(295, 2, 3, 1, '2026-05-22', 1, 3, 17, '2026-05-11 08:21:26'),
(296, 2, 3, 1, '2026-05-22', 2, 1, 4, '2026-05-12 15:48:14'),
(297, 2, 3, 1, '2026-05-22', 2, 2, 45, '2026-05-11 08:21:26'),
(298, 2, 3, 1, '2026-05-22', 3, 1, 5, '2026-05-11 08:21:26'),
(299, 2, 3, 1, '2026-05-22', 3, 2, NULL, NULL),
(300, 2, 3, 2, '2026-05-22', 1, 1, 53, '2026-05-11 08:21:26'),
(301, 2, 3, 2, '2026-05-22', 1, 2, 70, '2026-05-11 08:21:26'),
(302, 2, 3, 2, '2026-05-22', 1, 3, 69, '2026-05-11 08:21:26'),
(303, 2, 3, 2, '2026-05-22', 2, 1, 4, '2026-05-11 08:21:26'),
(304, 2, 3, 2, '2026-05-22', 2, 2, 10, '2026-05-13 21:02:01'),
(305, 2, 3, 2, '2026-05-22', 3, 1, 5, '2026-05-11 08:21:26'),
(306, 2, 3, 2, '2026-05-22', 3, 2, 60, '2026-05-11 08:21:26'),
(307, 2, 4, 1, '2026-05-22', 1, 1, 73, '2026-05-11 08:21:26'),
(308, 2, 4, 1, '2026-05-22', 2, 1, 11, '2026-05-11 08:21:26'),
(309, 2, 4, 1, '2026-05-22', 3, 1, NULL, NULL),
(310, 2, 4, 2, '2026-05-22', 1, 1, 71, '2026-05-11 20:29:59'),
(311, 2, 4, 2, '2026-05-22', 2, 1, 11, '2026-05-11 08:21:26'),
(312, 2, 4, 2, '2026-05-22', 3, 1, NULL, NULL),
(313, 2, 2, 1, '2026-05-23', 1, 1, 13, '2026-05-11 08:21:26'),
(314, 2, 2, 1, '2026-05-23', 2, 1, 56, '2026-05-11 08:21:26'),
(315, 2, 2, 1, '2026-05-23', 3, 1, NULL, NULL),
(316, 2, 2, 2, '2026-05-23', 1, 1, 69, '2026-05-13 01:18:32'),
(317, 2, 2, 2, '2026-05-23', 2, 1, 56, '2026-05-11 08:21:26'),
(318, 2, 2, 2, '2026-05-23', 3, 1, NULL, NULL),
(319, 2, 3, 1, '2026-05-23', 1, 1, 53, '2026-05-11 08:21:26'),
(320, 2, 3, 1, '2026-05-23', 1, 2, 45, '2026-05-11 15:09:21'),
(321, 2, 3, 1, '2026-05-23', 1, 3, 17, '2026-05-11 08:21:26'),
(322, 2, 3, 1, '2026-05-23', 2, 1, 10, '2026-05-13 21:03:01'),
(323, 2, 3, 1, '2026-05-23', 2, 2, 54, '2026-05-11 08:21:26'),
(324, 2, 3, 1, '2026-05-23', 3, 1, 5, '2026-05-11 08:21:26'),
(325, 2, 3, 1, '2026-05-23', 3, 2, NULL, NULL),
(326, 2, 3, 2, '2026-05-23', 1, 1, 70, '2026-05-11 08:21:26'),
(327, 2, 3, 2, '2026-05-23', 1, 2, 61, '2026-05-11 08:21:26'),
(328, 2, 3, 2, '2026-05-23', 1, 3, 57, '2026-05-11 08:21:26'),
(329, 2, 3, 2, '2026-05-23', 2, 1, 45, '2026-05-11 08:21:26'),
(330, 2, 3, 2, '2026-05-23', 2, 2, 54, '2026-05-11 08:21:26'),
(331, 2, 3, 2, '2026-05-23', 3, 1, 5, '2026-05-11 08:21:26'),
(332, 2, 3, 2, '2026-05-23', 3, 2, NULL, NULL),
(333, 2, 4, 1, '2026-05-23', 1, 1, 11, '2026-05-12 20:28:05'),
(334, 2, 4, 1, '2026-05-23', 2, 1, 55, '2026-05-11 08:21:26'),
(335, 2, 4, 1, '2026-05-23', 3, 1, NULL, NULL),
(336, 2, 4, 2, '2026-05-23', 1, 1, 22, '2026-05-11 08:21:26'),
(337, 2, 4, 2, '2026-05-23', 2, 1, 71, '2026-05-11 08:21:26'),
(338, 2, 4, 2, '2026-05-23', 3, 1, NULL, NULL),
(339, 2, 2, 1, '2026-05-24', 1, 1, 29, '2026-05-11 08:21:26'),
(340, 2, 2, 1, '2026-05-24', 2, 1, 13, '2026-05-11 08:21:26'),
(341, 2, 2, 1, '2026-05-24', 3, 1, NULL, NULL),
(342, 2, 2, 2, '2026-05-24', 1, 1, NULL, NULL),
(343, 2, 2, 2, '2026-05-24', 2, 1, 56, '2026-05-11 08:21:26'),
(344, 2, 2, 2, '2026-05-24', 3, 1, NULL, NULL),
(345, 2, 3, 1, '2026-05-24', 1, 1, 22, '2026-05-11 08:21:26'),
(346, 2, 3, 1, '2026-05-24', 1, 2, 57, '2026-05-11 08:21:26'),
(347, 2, 3, 1, '2026-05-24', 1, 3, 17, '2026-05-11 08:21:26'),
(348, 2, 3, 1, '2026-05-24', 2, 1, 45, '2026-05-11 08:21:26'),
(349, 2, 3, 1, '2026-05-24', 2, 2, 10, '2026-05-11 08:21:26'),
(350, 2, 3, 1, '2026-05-24', 3, 1, NULL, NULL),
(351, 2, 3, 1, '2026-05-24', 3, 2, NULL, NULL),
(352, 2, 3, 2, '2026-05-24', 1, 1, 53, '2026-05-11 16:17:35'),
(353, 2, 3, 2, '2026-05-24', 1, 2, 61, '2026-05-11 08:21:26'),
(354, 2, 3, 2, '2026-05-24', 1, 3, 70, '2026-05-11 08:21:26'),
(355, 2, 3, 2, '2026-05-24', 2, 1, NULL, NULL),
(356, 2, 3, 2, '2026-05-24', 2, 2, 54, '2026-05-11 08:21:26'),
(357, 2, 3, 2, '2026-05-24', 3, 1, NULL, NULL),
(358, 2, 3, 2, '2026-05-24', 3, 2, 60, '2026-05-11 08:21:26'),
(359, 2, 4, 1, '2026-05-24', 1, 1, 73, '2026-05-11 08:21:26'),
(360, 2, 4, 1, '2026-05-24', 2, 1, 11, '2026-05-11 08:21:26'),
(361, 2, 4, 1, '2026-05-24', 3, 1, NULL, NULL),
(362, 2, 4, 2, '2026-05-24', 1, 1, 71, '2026-05-11 08:21:26'),
(363, 2, 4, 2, '2026-05-24', 2, 1, 55, '2026-05-11 08:21:26'),
(364, 2, 4, 2, '2026-05-24', 3, 1, NULL, NULL),
(365, 3, 2, 1, '2026-05-25', 1, 1, 29, '2026-05-11 08:21:26'),
(366, 3, 2, 1, '2026-05-25', 2, 1, 13, '2026-05-11 08:21:26'),
(367, 3, 2, 1, '2026-05-25', 3, 1, NULL, NULL),
(368, 3, 2, 2, '2026-05-25', 1, 1, 29, '2026-05-11 08:21:26'),
(369, 3, 2, 2, '2026-05-25', 2, 1, 56, '2026-05-11 08:21:26'),
(370, 3, 2, 2, '2026-05-25', 3, 1, NULL, NULL),
(371, 3, 3, 1, '2026-05-25', 1, 1, 22, '2026-05-11 08:21:26'),
(372, 3, 3, 1, '2026-05-25', 1, 2, 69, '2026-05-11 08:21:26'),
(373, 3, 3, 1, '2026-05-25', 1, 3, 57, '2026-05-11 08:21:26'),
(374, 3, 3, 1, '2026-05-25', 2, 1, 4, '2026-05-11 08:21:26'),
(375, 3, 3, 1, '2026-05-25', 2, 2, 45, '2026-05-11 08:21:26'),
(376, 3, 3, 1, '2026-05-25', 3, 1, 5, '2026-05-11 08:21:26'),
(377, 3, 3, 1, '2026-05-25', 3, 2, NULL, NULL),
(378, 3, 3, 2, '2026-05-25', 1, 1, 53, '2026-05-11 08:21:26'),
(379, 3, 3, 2, '2026-05-25', 1, 2, 61, '2026-05-11 08:21:26'),
(380, 3, 3, 2, '2026-05-25', 1, 3, 57, '2026-05-11 08:21:26'),
(381, 3, 3, 2, '2026-05-25', 2, 1, 10, '2026-05-11 08:21:26'),
(382, 3, 3, 2, '2026-05-25', 2, 2, 54, '2026-05-11 08:21:26'),
(383, 3, 3, 2, '2026-05-25', 3, 1, 5, '2026-05-11 08:21:26'),
(384, 3, 3, 2, '2026-05-25', 3, 2, 60, '2026-05-11 08:21:26'),
(385, 3, 4, 1, '2026-05-25', 1, 1, 73, '2026-05-11 08:21:26'),
(386, 3, 4, 1, '2026-05-25', 2, 1, 55, '2026-05-11 08:21:26'),
(387, 3, 4, 1, '2026-05-25', 3, 1, NULL, NULL),
(388, 3, 4, 2, '2026-05-25', 1, 1, 71, '2026-05-11 08:21:26'),
(389, 3, 4, 2, '2026-05-25', 2, 1, 11, '2026-05-11 08:21:26'),
(390, 3, 4, 2, '2026-05-25', 3, 1, NULL, NULL),
(391, 3, 2, 1, '2026-05-26', 1, 1, 13, '2026-05-11 08:21:26'),
(392, 3, 2, 1, '2026-05-26', 2, 1, 45, '2026-05-11 08:21:26'),
(393, 3, 2, 1, '2026-05-26', 3, 1, 54, '2026-05-11 08:21:26'),
(394, 3, 2, 2, '2026-05-26', 1, 1, 69, '2026-05-11 08:21:26'),
(395, 3, 2, 2, '2026-05-26', 2, 1, 56, '2026-05-11 08:21:26'),
(396, 3, 2, 2, '2026-05-26', 3, 1, NULL, NULL),
(397, 3, 3, 1, '2026-05-26', 1, 1, 22, '2026-05-11 08:21:26'),
(398, 3, 3, 1, '2026-05-26', 1, 2, 70, '2026-05-11 08:21:26'),
(399, 3, 3, 1, '2026-05-26', 1, 3, 57, '2026-05-11 08:21:26'),
(400, 3, 3, 1, '2026-05-26', 2, 1, 4, '2026-05-11 08:21:26'),
(401, 3, 3, 1, '2026-05-26', 2, 2, 10, '2026-05-11 08:21:26'),
(402, 3, 3, 1, '2026-05-26', 3, 1, 5, '2026-05-11 08:21:26'),
(403, 3, 3, 1, '2026-05-26', 3, 2, NULL, NULL),
(404, 3, 3, 2, '2026-05-26', 1, 1, 53, '2026-05-11 08:21:26'),
(405, 3, 3, 2, '2026-05-26', 1, 2, 61, '2026-05-11 08:21:26'),
(406, 3, 3, 2, '2026-05-26', 1, 3, 17, '2026-05-11 08:21:26'),
(407, 3, 3, 2, '2026-05-26', 2, 1, 4, '2026-05-11 08:21:26'),
(408, 3, 3, 2, '2026-05-26', 2, 2, 10, '2026-05-11 08:21:26'),
(409, 3, 3, 2, '2026-05-26', 3, 1, 5, '2026-05-11 08:21:26'),
(410, 3, 3, 2, '2026-05-26', 3, 2, 60, '2026-05-11 08:21:26'),
(411, 3, 4, 1, '2026-05-26', 1, 1, 73, '2026-05-11 08:21:26'),
(412, 3, 4, 1, '2026-05-26', 2, 1, 55, '2026-05-11 08:21:26'),
(413, 3, 4, 1, '2026-05-26', 3, 1, NULL, NULL),
(414, 3, 4, 2, '2026-05-26', 1, 1, 71, '2026-05-11 08:21:26'),
(415, 3, 4, 2, '2026-05-26', 2, 1, 11, '2026-05-11 08:21:26'),
(416, 3, 4, 2, '2026-05-26', 3, 1, NULL, NULL),
(417, 3, 2, 1, '2026-05-27', 1, 1, 29, '2026-05-11 08:21:26'),
(418, 3, 2, 1, '2026-05-27', 2, 1, 13, '2026-05-11 08:21:26'),
(419, 3, 2, 1, '2026-05-27', 3, 1, NULL, NULL),
(420, 3, 2, 2, '2026-05-27', 1, 1, 29, '2026-05-11 08:21:26'),
(421, 3, 2, 2, '2026-05-27', 2, 1, 56, '2026-05-11 08:21:26'),
(422, 3, 2, 2, '2026-05-27', 3, 1, NULL, NULL),
(423, 3, 3, 1, '2026-05-27', 1, 1, 22, '2026-05-11 08:21:26'),
(424, 3, 3, 1, '2026-05-27', 1, 2, 70, '2026-05-11 08:21:26'),
(425, 3, 3, 1, '2026-05-27', 1, 3, 53, '2026-05-11 08:21:26'),
(426, 3, 3, 1, '2026-05-27', 2, 1, 10, '2026-05-11 08:21:26'),
(427, 3, 3, 1, '2026-05-27', 2, 2, 4, '2026-05-11 08:21:26'),
(428, 3, 3, 1, '2026-05-27', 3, 1, 5, '2026-05-11 08:21:26'),
(429, 3, 3, 1, '2026-05-27', 3, 2, NULL, NULL),
(430, 3, 3, 2, '2026-05-27', 1, 1, 69, '2026-05-11 08:21:26'),
(431, 3, 3, 2, '2026-05-27', 1, 2, 61, '2026-05-11 08:21:26'),
(432, 3, 3, 2, '2026-05-27', 1, 3, 17, '2026-05-11 08:21:26'),
(433, 3, 3, 2, '2026-05-27', 2, 1, NULL, NULL),
(434, 3, 3, 2, '2026-05-27', 2, 2, 10, '2026-05-11 08:21:26'),
(435, 3, 3, 2, '2026-05-27', 3, 1, 5, '2026-05-11 08:21:26'),
(436, 3, 3, 2, '2026-05-27', 3, 2, 60, '2026-05-11 08:21:26'),
(437, 3, 4, 1, '2026-05-27', 1, 1, 11, '2026-05-11 08:21:26'),
(438, 3, 4, 1, '2026-05-27', 2, 1, 55, '2026-05-11 08:21:26'),
(439, 3, 4, 1, '2026-05-27', 3, 1, NULL, NULL),
(440, 3, 4, 2, '2026-05-27', 1, 1, 71, '2026-05-11 08:21:26'),
(441, 3, 4, 2, '2026-05-27', 2, 1, 11, '2026-05-11 08:21:26'),
(442, 3, 4, 2, '2026-05-27', 3, 1, NULL, NULL),
(443, 3, 2, 1, '2026-05-28', 1, 1, 29, '2026-05-11 08:21:26'),
(444, 3, 2, 1, '2026-05-28', 2, 1, 45, '2026-05-11 08:21:26'),
(445, 3, 2, 1, '2026-05-28', 3, 1, NULL, NULL),
(446, 3, 2, 2, '2026-05-28', 1, 1, 69, '2026-05-11 08:21:26'),
(447, 3, 2, 2, '2026-05-28', 2, 1, 56, '2026-05-11 08:21:26'),
(448, 3, 2, 2, '2026-05-28', 3, 1, NULL, NULL),
(449, 3, 3, 1, '2026-05-28', 1, 1, 22, '2026-05-11 08:21:26'),
(450, 3, 3, 1, '2026-05-28', 1, 2, 57, '2026-05-11 08:21:26'),
(451, 3, 3, 1, '2026-05-28', 1, 3, 17, '2026-05-11 08:21:26'),
(452, 3, 3, 1, '2026-05-28', 2, 1, 4, '2026-05-11 08:21:26'),
(453, 3, 3, 1, '2026-05-28', 2, 2, 54, '2026-05-11 08:21:26'),
(454, 3, 3, 1, '2026-05-28', 3, 1, 5, '2026-05-11 08:21:26'),
(455, 3, 3, 1, '2026-05-28', 3, 2, NULL, NULL),
(456, 3, 3, 2, '2026-05-28', 1, 1, 70, '2026-05-11 08:21:26'),
(457, 3, 3, 2, '2026-05-28', 1, 2, 61, '2026-05-11 08:21:26'),
(458, 3, 3, 2, '2026-05-28', 1, 3, 57, '2026-05-11 08:21:26'),
(459, 3, 3, 2, '2026-05-28', 2, 1, 4, '2026-05-11 08:21:26'),
(460, 3, 3, 2, '2026-05-28', 2, 2, 54, '2026-05-11 08:21:26'),
(461, 3, 3, 2, '2026-05-28', 3, 1, 5, '2026-05-11 08:21:26'),
(462, 3, 3, 2, '2026-05-28', 3, 2, 60, '2026-05-11 08:21:26'),
(463, 3, 4, 1, '2026-05-28', 1, 1, 13, '2026-05-11 08:21:26'),
(464, 3, 4, 1, '2026-05-28', 2, 1, 11, '2026-05-11 08:21:26'),
(465, 3, 4, 1, '2026-05-28', 3, 1, NULL, NULL),
(466, 3, 4, 2, '2026-05-28', 1, 1, 71, '2026-05-11 08:21:26'),
(467, 3, 4, 2, '2026-05-28', 2, 1, 55, '2026-05-11 08:21:26'),
(468, 3, 4, 2, '2026-05-28', 3, 1, NULL, NULL),
(469, 3, 2, 1, '2026-05-29', 1, 1, 29, '2026-05-11 08:21:26'),
(470, 3, 2, 1, '2026-05-29', 2, 1, 13, '2026-05-11 08:21:26'),
(471, 3, 2, 1, '2026-05-29', 3, 1, NULL, NULL),
(472, 3, 2, 2, '2026-05-29', 1, 1, 29, '2026-05-11 08:21:26'),
(473, 3, 2, 2, '2026-05-29', 2, 1, 13, '2026-05-11 08:21:26'),
(474, 3, 2, 2, '2026-05-29', 3, 1, NULL, NULL),
(475, 3, 3, 1, '2026-05-29', 1, 1, 22, '2026-05-11 08:21:26'),
(476, 3, 3, 1, '2026-05-29', 1, 2, 57, '2026-05-11 08:21:26'),
(477, 3, 3, 1, '2026-05-29', 1, 3, 17, '2026-05-11 08:21:26'),
(478, 3, 3, 1, '2026-05-29', 2, 1, 54, '2026-05-11 08:21:26'),
(479, 3, 3, 1, '2026-05-29', 2, 2, 45, '2026-05-11 08:21:26'),
(480, 3, 3, 1, '2026-05-29', 3, 1, 5, '2026-05-11 08:21:26'),
(481, 3, 3, 1, '2026-05-29', 3, 2, NULL, NULL),
(482, 3, 3, 2, '2026-05-29', 1, 1, 53, '2026-05-11 08:21:26'),
(483, 3, 3, 2, '2026-05-29', 1, 2, 70, '2026-05-11 08:21:26'),
(484, 3, 3, 2, '2026-05-29', 1, 3, 69, '2026-05-11 08:21:26'),
(485, 3, 3, 2, '2026-05-29', 2, 1, 4, '2026-05-11 08:21:26'),
(486, 3, 3, 2, '2026-05-29', 2, 2, 54, '2026-05-11 08:21:26'),
(487, 3, 3, 2, '2026-05-29', 3, 1, 5, '2026-05-11 08:21:26'),
(488, 3, 3, 2, '2026-05-29', 3, 2, 60, '2026-05-11 08:21:26'),
(489, 3, 4, 1, '2026-05-29', 1, 1, 73, '2026-05-11 08:21:26'),
(490, 3, 4, 1, '2026-05-29', 2, 1, 11, '2026-05-11 08:21:26'),
(491, 3, 4, 1, '2026-05-29', 3, 1, NULL, NULL),
(492, 3, 4, 2, '2026-05-29', 1, 1, 73, '2026-05-11 08:21:26'),
(493, 3, 4, 2, '2026-05-29', 2, 1, 11, '2026-05-11 08:21:26'),
(494, 3, 4, 2, '2026-05-29', 3, 1, NULL, NULL),
(495, 3, 2, 1, '2026-05-30', 1, 1, 13, '2026-05-11 08:21:26'),
(496, 3, 2, 1, '2026-05-30', 2, 1, 56, '2026-05-11 08:21:26'),
(497, 3, 2, 1, '2026-05-30', 3, 1, NULL, NULL),
(498, 3, 2, 2, '2026-05-30', 1, 1, 29, '2026-05-11 08:21:26'),
(499, 3, 2, 2, '2026-05-30', 2, 1, 56, '2026-05-11 08:21:26'),
(500, 3, 2, 2, '2026-05-30', 3, 1, NULL, NULL),
(501, 3, 3, 1, '2026-05-30', 1, 1, 53, '2026-05-11 08:21:26'),
(502, 3, 3, 1, '2026-05-30', 1, 2, 45, '2026-05-11 08:21:26'),
(503, 3, 3, 1, '2026-05-30', 1, 3, 17, '2026-05-11 08:21:26'),
(504, 3, 3, 1, '2026-05-30', 2, 1, 10, '2026-05-11 08:21:26'),
(505, 3, 3, 1, '2026-05-30', 2, 2, 54, '2026-05-11 08:21:26'),
(506, 3, 3, 1, '2026-05-30', 3, 1, 5, '2026-05-11 08:21:26'),
(507, 3, 3, 1, '2026-05-30', 3, 2, NULL, NULL),
(508, 3, 3, 2, '2026-05-30', 1, 1, 70, '2026-05-11 08:21:26'),
(509, 3, 3, 2, '2026-05-30', 1, 2, 61, '2026-05-11 08:21:26'),
(510, 3, 3, 2, '2026-05-30', 1, 3, 57, '2026-05-11 08:21:26'),
(511, 3, 3, 2, '2026-05-30', 2, 1, 45, '2026-05-11 08:21:26'),
(512, 3, 3, 2, '2026-05-30', 2, 2, 54, '2026-05-11 08:21:26'),
(513, 3, 3, 2, '2026-05-30', 3, 1, 5, '2026-05-11 08:21:26'),
(514, 3, 3, 2, '2026-05-30', 3, 2, NULL, NULL),
(515, 3, 4, 1, '2026-05-30', 1, 1, 73, '2026-05-11 08:21:26'),
(516, 3, 4, 1, '2026-05-30', 2, 1, 55, '2026-05-11 08:21:26'),
(517, 3, 4, 1, '2026-05-30', 3, 1, NULL, NULL),
(518, 3, 4, 2, '2026-05-30', 1, 1, 22, '2026-05-11 08:21:26'),
(519, 3, 4, 2, '2026-05-30', 2, 1, 71, '2026-05-11 08:21:26'),
(520, 3, 4, 2, '2026-05-30', 3, 1, NULL, NULL),
(521, 3, 2, 1, '2026-05-31', 1, 1, 29, '2026-05-11 08:21:26'),
(522, 3, 2, 1, '2026-05-31', 2, 1, 13, '2026-05-11 08:21:26'),
(523, 3, 2, 1, '2026-05-31', 3, 1, NULL, NULL),
(524, 3, 2, 2, '2026-05-31', 1, 1, 69, '2026-05-11 08:21:26'),
(525, 3, 2, 2, '2026-05-31', 2, 1, 56, '2026-05-11 08:21:26'),
(526, 3, 2, 2, '2026-05-31', 3, 1, NULL, NULL),
(527, 3, 3, 1, '2026-05-31', 1, 1, 22, '2026-05-11 08:21:26'),
(528, 3, 3, 1, '2026-05-31', 1, 2, 57, '2026-05-11 08:21:26'),
(529, 3, 3, 1, '2026-05-31', 1, 3, 17, '2026-05-11 08:21:26'),
(530, 3, 3, 1, '2026-05-31', 2, 1, 45, '2026-05-11 08:21:26'),
(531, 3, 3, 1, '2026-05-31', 2, 2, 10, '2026-05-11 08:21:26'),
(532, 3, 3, 1, '2026-05-31', 3, 1, NULL, NULL),
(533, 3, 3, 1, '2026-05-31', 3, 2, NULL, NULL),
(534, 3, 3, 2, '2026-05-31', 1, 1, 53, '2026-05-11 08:21:26'),
(535, 3, 3, 2, '2026-05-31', 1, 2, 61, '2026-05-11 08:21:26'),
(536, 3, 3, 2, '2026-05-31', 1, 3, 70, '2026-05-11 08:21:26'),
(537, 3, 3, 2, '2026-05-31', 2, 1, 4, '2026-05-11 08:21:26'),
(538, 3, 3, 2, '2026-05-31', 2, 2, 54, '2026-05-11 08:21:26'),
(539, 3, 3, 2, '2026-05-31', 3, 1, NULL, NULL),
(540, 3, 3, 2, '2026-05-31', 3, 2, 60, '2026-05-11 08:21:26'),
(541, 3, 4, 1, '2026-05-31', 1, 1, 73, '2026-05-11 08:21:26'),
(542, 3, 4, 1, '2026-05-31', 2, 1, 11, '2026-05-11 08:21:26'),
(543, 3, 4, 1, '2026-05-31', 3, 1, NULL, NULL),
(544, 3, 4, 2, '2026-05-31', 1, 1, 71, '2026-05-12 21:01:53'),
(545, 3, 4, 2, '2026-05-31', 2, 1, 55, '2026-05-11 08:21:26'),
(546, 3, 4, 2, '2026-05-31', 3, 1, NULL, NULL),
(547, 3, 2, 1, '2026-05-25', 4, 1, NULL, NULL),
(548, 3, 3, 1, '2026-05-25', 4, 1, NULL, NULL),
(549, 3, 4, 1, '2026-05-25', 4, 1, NULL, NULL),
(550, 2, 2, 1, '2026-05-18', 4, 1, NULL, NULL),
(551, 2, 3, 1, '2026-05-18', 4, 1, 22, '2026-05-11 20:15:17'),
(552, 2, 4, 1, '2026-05-18', 4, 1, NULL, NULL),
(553, 1, 2, 1, '2026-05-11', 4, 1, NULL, NULL),
(554, 1, 3, 1, '2026-05-11', 4, 1, NULL, NULL),
(555, 1, 4, 1, '2026-05-11', 4, 1, NULL, NULL),
(556, 3, 2, 1, '2026-05-26', 4, 1, NULL, NULL),
(557, 3, 3, 1, '2026-05-26', 4, 1, NULL, NULL),
(558, 3, 4, 1, '2026-05-26', 4, 1, NULL, NULL),
(559, 2, 2, 1, '2026-05-19', 4, 1, NULL, NULL),
(560, 2, 3, 1, '2026-05-19', 4, 1, NULL, NULL),
(561, 2, 4, 1, '2026-05-19', 4, 1, NULL, NULL),
(562, 1, 2, 1, '2026-05-12', 4, 1, NULL, NULL),
(563, 1, 3, 1, '2026-05-12', 4, 1, NULL, NULL),
(564, 1, 4, 1, '2026-05-12', 4, 1, NULL, NULL),
(565, 3, 2, 1, '2026-05-27', 4, 1, NULL, NULL),
(566, 3, 3, 1, '2026-05-27', 4, 1, NULL, NULL),
(567, 3, 4, 1, '2026-05-27', 4, 1, NULL, NULL),
(568, 2, 2, 1, '2026-05-20', 4, 1, NULL, NULL),
(569, 2, 3, 1, '2026-05-20', 4, 1, 54, '2026-05-14 12:42:53'),
(570, 2, 4, 1, '2026-05-20', 4, 1, NULL, NULL),
(571, 1, 2, 1, '2026-05-13', 4, 1, NULL, NULL),
(572, 1, 3, 1, '2026-05-13', 4, 1, NULL, NULL),
(573, 1, 4, 1, '2026-05-13', 4, 1, NULL, NULL),
(574, 3, 2, 1, '2026-05-28', 4, 1, NULL, NULL),
(575, 3, 3, 1, '2026-05-28', 4, 1, NULL, NULL),
(576, 3, 4, 1, '2026-05-28', 4, 1, NULL, NULL),
(577, 2, 2, 1, '2026-05-21', 4, 1, NULL, NULL),
(578, 2, 3, 1, '2026-05-21', 4, 1, NULL, NULL),
(579, 2, 4, 1, '2026-05-21', 4, 1, NULL, NULL),
(580, 1, 2, 1, '2026-05-14', 4, 1, NULL, NULL),
(581, 1, 3, 1, '2026-05-14', 4, 1, NULL, NULL),
(582, 1, 4, 1, '2026-05-14', 4, 1, NULL, NULL),
(583, 3, 2, 1, '2026-05-29', 4, 1, NULL, NULL),
(584, 3, 3, 1, '2026-05-29', 4, 1, NULL, NULL),
(585, 3, 4, 1, '2026-05-29', 4, 1, NULL, NULL),
(586, 2, 2, 1, '2026-05-22', 4, 1, NULL, NULL),
(587, 2, 3, 1, '2026-05-22', 4, 1, NULL, NULL),
(588, 2, 4, 1, '2026-05-22', 4, 1, NULL, NULL),
(589, 1, 2, 1, '2026-05-15', 4, 1, NULL, NULL),
(590, 1, 3, 1, '2026-05-15', 4, 1, NULL, NULL),
(591, 1, 4, 1, '2026-05-15', 4, 1, NULL, NULL),
(592, 3, 2, 1, '2026-05-30', 4, 1, NULL, NULL),
(593, 3, 3, 1, '2026-05-30', 4, 1, NULL, NULL),
(594, 3, 4, 1, '2026-05-30', 4, 1, NULL, NULL),
(595, 2, 2, 1, '2026-05-23', 4, 1, NULL, NULL),
(596, 2, 3, 1, '2026-05-23', 4, 1, NULL, NULL),
(597, 2, 4, 1, '2026-05-23', 4, 1, NULL, NULL),
(598, 1, 2, 1, '2026-05-16', 4, 1, NULL, NULL),
(599, 1, 3, 1, '2026-05-16', 4, 1, NULL, NULL),
(600, 1, 4, 1, '2026-05-16', 4, 1, NULL, NULL),
(601, 3, 2, 1, '2026-05-31', 4, 1, NULL, NULL),
(602, 3, 3, 1, '2026-05-31', 4, 1, NULL, NULL),
(603, 3, 4, 1, '2026-05-31', 4, 1, NULL, NULL),
(604, 2, 2, 1, '2026-05-24', 4, 1, NULL, NULL),
(605, 2, 3, 1, '2026-05-24', 4, 1, 57, '2026-05-14 23:55:43'),
(606, 2, 4, 1, '2026-05-24', 4, 1, NULL, NULL),
(607, 1, 2, 1, '2026-05-17', 4, 1, NULL, NULL),
(608, 1, 3, 1, '2026-05-17', 4, 1, NULL, NULL),
(609, 1, 4, 1, '2026-05-17', 4, 1, NULL, NULL),
(610, 1, 4, 2, '2026-05-11', 4, 1, NULL, NULL),
(611, 2, 4, 2, '2026-05-18', 4, 1, NULL, NULL),
(612, 3, 4, 2, '2026-05-25', 4, 1, NULL, NULL),
(613, 1, 3, 2, '2026-05-11', 4, 1, NULL, NULL),
(614, 2, 3, 2, '2026-05-18', 4, 1, NULL, NULL),
(615, 3, 3, 2, '2026-05-25', 4, 1, NULL, NULL),
(616, 1, 2, 2, '2026-05-11', 4, 1, NULL, NULL),
(617, 2, 2, 2, '2026-05-18', 4, 1, NULL, NULL),
(618, 3, 2, 2, '2026-05-25', 4, 1, NULL, NULL),
(619, 1, 4, 2, '2026-05-12', 4, 1, NULL, NULL),
(620, 2, 4, 2, '2026-05-19', 4, 1, NULL, NULL),
(621, 3, 4, 2, '2026-05-26', 4, 1, NULL, NULL),
(622, 1, 3, 2, '2026-05-12', 4, 1, NULL, NULL),
(623, 2, 3, 2, '2026-05-19', 4, 1, NULL, NULL),
(624, 3, 3, 2, '2026-05-26', 4, 1, NULL, NULL),
(625, 1, 2, 2, '2026-05-12', 4, 1, NULL, NULL),
(626, 2, 2, 2, '2026-05-19', 4, 1, NULL, NULL),
(627, 3, 2, 2, '2026-05-26', 4, 1, NULL, NULL),
(628, 1, 4, 2, '2026-05-13', 4, 1, NULL, NULL),
(629, 2, 4, 2, '2026-05-20', 4, 1, NULL, NULL),
(630, 3, 4, 2, '2026-05-27', 4, 1, NULL, NULL),
(631, 1, 3, 2, '2026-05-13', 4, 1, NULL, NULL),
(632, 2, 3, 2, '2026-05-20', 4, 1, NULL, NULL),
(633, 3, 3, 2, '2026-05-27', 4, 1, NULL, NULL),
(634, 1, 2, 2, '2026-05-13', 4, 1, NULL, NULL),
(635, 2, 2, 2, '2026-05-20', 4, 1, NULL, NULL),
(636, 3, 2, 2, '2026-05-27', 4, 1, NULL, NULL),
(637, 1, 4, 2, '2026-05-14', 4, 1, NULL, NULL),
(638, 2, 4, 2, '2026-05-21', 4, 1, NULL, NULL),
(639, 3, 4, 2, '2026-05-28', 4, 1, NULL, NULL),
(640, 1, 3, 2, '2026-05-14', 4, 1, NULL, NULL),
(641, 2, 3, 2, '2026-05-21', 4, 1, NULL, NULL),
(642, 3, 3, 2, '2026-05-28', 4, 1, NULL, NULL),
(643, 1, 2, 2, '2026-05-14', 4, 1, NULL, NULL),
(644, 2, 2, 2, '2026-05-21', 4, 1, NULL, NULL),
(645, 3, 2, 2, '2026-05-28', 4, 1, NULL, NULL),
(646, 1, 4, 2, '2026-05-15', 4, 1, NULL, NULL),
(647, 2, 4, 2, '2026-05-22', 4, 1, NULL, NULL),
(648, 3, 4, 2, '2026-05-29', 4, 1, NULL, NULL),
(649, 1, 3, 2, '2026-05-15', 4, 1, NULL, NULL),
(650, 2, 3, 2, '2026-05-22', 4, 1, NULL, NULL),
(651, 3, 3, 2, '2026-05-29', 4, 1, NULL, NULL),
(652, 1, 2, 2, '2026-05-15', 4, 1, NULL, NULL),
(653, 2, 2, 2, '2026-05-22', 4, 1, NULL, NULL),
(654, 3, 2, 2, '2026-05-29', 4, 1, NULL, NULL),
(655, 1, 4, 2, '2026-05-16', 4, 1, NULL, NULL),
(656, 2, 4, 2, '2026-05-23', 4, 1, NULL, NULL),
(657, 3, 4, 2, '2026-05-30', 4, 1, NULL, NULL),
(658, 1, 3, 2, '2026-05-16', 4, 1, NULL, NULL),
(659, 2, 3, 2, '2026-05-23', 4, 1, NULL, NULL),
(660, 3, 3, 2, '2026-05-30', 4, 1, NULL, NULL),
(661, 1, 2, 2, '2026-05-16', 4, 1, NULL, NULL),
(662, 2, 2, 2, '2026-05-23', 4, 1, NULL, NULL),
(663, 3, 2, 2, '2026-05-30', 4, 1, NULL, NULL),
(664, 1, 4, 2, '2026-05-17', 4, 1, NULL, NULL),
(665, 2, 4, 2, '2026-05-24', 4, 1, NULL, NULL),
(666, 3, 4, 2, '2026-05-31', 4, 1, NULL, NULL),
(667, 1, 3, 2, '2026-05-17', 4, 1, NULL, NULL),
(668, 2, 3, 2, '2026-05-24', 4, 1, NULL, NULL),
(669, 3, 3, 2, '2026-05-31', 4, 1, NULL, NULL),
(670, 1, 2, 2, '2026-05-17', 4, 1, NULL, NULL),
(671, 2, 2, 2, '2026-05-24', 4, 1, NULL, NULL),
(672, 3, 2, 2, '2026-05-31', 4, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `horario_solicitud`
--

CREATE TABLE `horario_solicitud` (
  `id_solicitud` int(11) NOT NULL,
  `semana_id` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `lunes` tinyint(1) NOT NULL DEFAULT 0,
  `martes` tinyint(1) NOT NULL DEFAULT 0,
  `miercoles` tinyint(1) NOT NULL DEFAULT 0,
  `jueves` tinyint(1) NOT NULL DEFAULT 0,
  `viernes` tinyint(1) NOT NULL DEFAULT 0,
  `sabado` tinyint(1) NOT NULL DEFAULT 0,
  `domingo` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('BORRADOR','ENVIADO','APROBADO','RECHAZADO') NOT NULL DEFAULT 'BORRADOR',
  `observacion` text DEFAULT NULL,
  `observacion_admin` text DEFAULT NULL,
  `revisado_por_id` int(11) DEFAULT NULL,
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incidencia`
--

CREATE TABLE `incidencia` (
  `id_incidencia` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `sesion_id` int(11) DEFAULT NULL,
  `tipo` enum('ERROR_CAJA','FALTA_DISCIPLINARIA','SISTEMA','OTRO') NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('REGISTRADO','EN_REVISION','RESUELTO') NOT NULL DEFAULT 'REGISTRADO',
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `institucion`
--

CREATE TABLE `institucion` (
  `id_institucion` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institucion`
--

INSERT INTO `institucion` (`id_institucion`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Instituto Federico Villarreal', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'Instituto IDAT', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Instituto Superior Arzobispo Loayza', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(4, 'Instituto Superior Daniel Alcides Carrión', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(5, 'Otros', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(6, 'Universidad María Auxiliadora', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(7, 'Universidad Nacional Mayor de San Marcos', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(8, 'Universidad Norbert Wiener', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(9, 'Universidad Privada del Norte', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(10, 'Universidad Tecnológica del Perú', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `local`
--

CREATE TABLE `local` (
  `id_local` int(11) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `id_encargado` int(11) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `local`
--

INSERT INTO `local` (`id_local`, `descripcion`, `direccion`, `id_encargado`, `activo`) VALUES
(2, 'Local 2', NULL, NULL, 1),
(3, 'Local 3', NULL, NULL, 1),
(4, 'Local 4', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `modo`
--

CREATE TABLE `modo` (
  `id_modo` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modo`
--

INSERT INTO `modo` (`id_modo`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'EFECTIVO', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(2, 'Yape/Plin', 1, '2026-05-04 20:58:12', '2026-05-11 05:02:23'),
(3, 'PLIN', 0, '2026-05-04 20:58:12', '2026-05-11 05:02:23'),
(4, 'Visa/POS', 1, '2026-05-04 20:58:12', '2026-05-11 05:02:23'),
(5, 'BCP', 0, '2026-05-04 20:58:12', '2026-05-11 05:02:23'),
(6, 'TRANSFERENCIA_BANCARIA', 0, '2026-05-04 20:58:12', '2026-05-11 05:02:23');

-- --------------------------------------------------------

--
-- Table structure for table `movimiento_sesion`
--

CREATE TABLE `movimiento_sesion` (
  `id_movimiento` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `tipo_movimiento_id` int(11) NOT NULL,
  `origen` enum('CUADRE','CORRECCION') NOT NULL DEFAULT 'CUADRE',
  `modo_id` int(11) DEFAULT NULL,
  `postulante_registro_id` int(11) NOT NULL,
  `postulante_revision_id` int(11) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_operacion` varchar(100) DEFAULT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `fecha_anulacion` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','APROBADO','OBSERVADO','RECHAZADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  `motivo_anulacion` text DEFAULT NULL,
  `observacion_revision` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movimiento_sesion`
--

INSERT INTO `movimiento_sesion` (`id_movimiento`, `sesion_id`, `tipo_movimiento_id`, `origen`, `modo_id`, `postulante_registro_id`, `postulante_revision_id`, `descripcion`, `monto`, `numero_operacion`, `comprobante_url`, `fecha_movimiento`, `fecha_revision`, `fecha_anulacion`, `estado`, `motivo_anulacion`, `observacion_revision`) VALUES
(17, 27, 1, 'CUADRE', 2, 29, NULL, NULL, 445.70, NULL, NULL, '2026-05-07 14:41:58', NULL, NULL, 'PENDIENTE', NULL, NULL),
(18, 30, 1, 'CUADRE', 2, 17, NULL, NULL, 1000.00, NULL, NULL, '2026-05-07 14:56:19', NULL, NULL, 'PENDIENTE', NULL, NULL),
(19, 30, 1, 'CUADRE', 4, 17, NULL, NULL, 1000.00, NULL, NULL, '2026-05-07 14:56:24', NULL, NULL, 'PENDIENTE', NULL, NULL),
(20, 30, 1, 'CUADRE', 3, 17, NULL, NULL, 938.90, NULL, NULL, '2026-05-07 14:56:37', NULL, NULL, 'PENDIENTE', NULL, NULL),
(21, 33, 1, 'CUADRE', 2, 22, NULL, NULL, 10.00, NULL, NULL, '2026-05-07 15:13:59', NULL, NULL, 'PENDIENTE', NULL, NULL),
(25, 31, 1, 'CUADRE', 2, 17, NULL, NULL, 388.30, NULL, NULL, '2026-05-07 19:58:44', NULL, NULL, 'PENDIENTE', NULL, NULL),
(26, 31, 1, 'CUADRE', 4, 17, NULL, NULL, 232.80, NULL, NULL, '2026-05-07 19:59:03', NULL, NULL, 'PENDIENTE', NULL, NULL),
(27, 31, 2, 'CUADRE', 1, 17, NULL, 'desayuno sra. Marina', 3.01, NULL, NULL, '2026-05-07 20:00:17', NULL, NULL, 'APROBADO', NULL, NULL),
(28, 28, 1, 'CUADRE', 2, 29, NULL, NULL, 142.70, NULL, NULL, '2026-05-07 20:07:03', NULL, NULL, 'PENDIENTE', NULL, NULL),
(29, 35, 2, 'CUADRE', 1, 22, NULL, 'se compro lancetas', 40.00, NULL, NULL, '2026-05-07 20:16:16', NULL, NULL, 'APROBADO', NULL, NULL),
(30, 35, 2, 'CUADRE', 1, 22, NULL, 'deposito grupo kgyr', 11000.01, NULL, NULL, '2026-05-07 20:16:16', NULL, NULL, 'APROBADO', NULL, NULL),
(31, 38, 1, 'CUADRE', 2, 71, NULL, NULL, 612.80, NULL, NULL, '2026-05-07 20:22:38', NULL, NULL, 'PENDIENTE', NULL, NULL),
(33, 40, 1, 'CUADRE', 2, 54, NULL, NULL, 151.60, NULL, NULL, '2026-05-07 20:27:30', NULL, NULL, 'PENDIENTE', NULL, NULL),
(37, 42, 1, 'CUADRE', 2, 17, NULL, NULL, 871.10, NULL, NULL, '2026-05-08 03:59:23', NULL, NULL, 'PENDIENTE', NULL, NULL),
(38, 42, 1, 'CUADRE', 4, 17, NULL, NULL, 100.70, NULL, NULL, '2026-05-08 03:59:38', NULL, NULL, 'PENDIENTE', NULL, NULL),
(40, 42, 2, 'CUADRE', 1, 17, NULL, 'compra de Ciro', 80.50, NULL, NULL, '2026-05-08 04:00:18', NULL, NULL, 'APROBADO', NULL, NULL),
(42, 44, 2, 'CUADRE', 1, 57, NULL, 'compra de ciro', 37.00, NULL, NULL, '2026-05-08 04:15:55', NULL, NULL, 'APROBADO', NULL, NULL),
(43, 48, 1, 'CUADRE', 2, 69, NULL, NULL, 282.10, NULL, NULL, '2026-05-08 13:42:00', NULL, NULL, 'PENDIENTE', NULL, NULL),
(45, 50, 1, 'CUADRE', 2, 73, NULL, NULL, 298.10, NULL, NULL, '2026-05-08 16:07:40', NULL, NULL, 'PENDIENTE', NULL, NULL),
(46, 52, 2, 'CUADRE', 1, 22, NULL, 'deposito a grupo kgyr', 10000.00, NULL, NULL, '2026-05-08 19:51:46', NULL, NULL, 'APROBADO', NULL, NULL),
(47, 51, 1, 'CUADRE', 2, 11, NULL, NULL, 220.10, NULL, NULL, '2026-05-08 20:01:02', NULL, NULL, 'PENDIENTE', NULL, NULL),
(49, 47, 1, 'CUADRE', 4, 70, NULL, NULL, 22.90, NULL, NULL, '2026-05-08 20:08:34', NULL, NULL, 'PENDIENTE', NULL, NULL),
(50, 47, 1, 'CUADRE', 2, 70, NULL, NULL, 543.20, NULL, NULL, '2026-05-08 20:08:47', NULL, NULL, 'PENDIENTE', NULL, NULL),
(51, 47, 2, 'CUADRE', 1, 70, NULL, 'COMPRA', 42.00, NULL, NULL, '2026-05-08 20:09:36', NULL, NULL, 'APROBADO', NULL, NULL),
(52, 53, 1, 'CUADRE', 2, 29, NULL, NULL, 47.10, NULL, NULL, '2026-05-08 20:09:43', NULL, NULL, 'PENDIENTE', NULL, NULL),
(59, 55, 1, 'CUADRE', 2, 69, NULL, NULL, 707.70, NULL, NULL, '2026-05-09 04:07:20', NULL, NULL, 'PENDIENTE', NULL, NULL),
(60, 55, 1, 'CUADRE', 4, 69, NULL, NULL, 273.30, NULL, NULL, '2026-05-09 04:07:46', NULL, NULL, 'PENDIENTE', NULL, NULL),
(61, 57, 1, 'CUADRE', 2, 73, NULL, NULL, 206.20, NULL, NULL, '2026-05-09 04:09:33', NULL, NULL, 'PENDIENTE', NULL, NULL),
(62, 57, 1, 'CUADRE', 4, 73, NULL, NULL, 67.60, NULL, NULL, '2026-05-09 04:09:41', NULL, NULL, 'PENDIENTE', NULL, NULL),
(63, 55, 2, 'CUADRE', 1, 69, NULL, 'Gasto', 410.00, NULL, NULL, '2026-05-09 04:10:44', NULL, NULL, 'APROBADO', NULL, NULL),
(64, 55, 2, 'CUADRE', 1, 69, NULL, 'compra ciro', 37.00, NULL, NULL, '2026-05-09 04:10:44', NULL, NULL, 'APROBADO', NULL, NULL),
(65, 58, 1, 'CUADRE', 2, 53, NULL, NULL, 317.50, NULL, NULL, '2026-05-09 04:12:04', NULL, NULL, 'PENDIENTE', NULL, NULL),
(66, 58, 1, 'CUADRE', 4, 53, NULL, NULL, 188.90, NULL, NULL, '2026-05-09 04:12:14', NULL, NULL, 'PENDIENTE', NULL, NULL),
(67, 58, 2, 'CUADRE', 1, 53, NULL, 'compra de ciro', 74.00, NULL, NULL, '2026-05-09 04:12:42', NULL, NULL, 'APROBADO', NULL, NULL),
(68, 59, 1, 'CUADRE', 2, 29, NULL, NULL, 186.10, NULL, NULL, '2026-05-09 04:13:36', NULL, NULL, 'PENDIENTE', NULL, NULL),
(69, 61, 1, 'CUADRE', 2, 73, NULL, NULL, 885.65, NULL, NULL, '2026-05-09 20:03:08', NULL, NULL, 'PENDIENTE', NULL, NULL),
(70, 61, 1, 'CUADRE', 4, 73, NULL, NULL, 39.00, NULL, NULL, '2026-05-09 20:03:19', NULL, NULL, 'PENDIENTE', NULL, NULL),
(71, 62, 2, 'CUADRE', 1, 57, NULL, 'visa y yape', 90.20, NULL, NULL, '2026-05-09 20:23:34', NULL, NULL, 'APROBADO', NULL, NULL),
(72, 63, 1, 'CUADRE', 2, 71, NULL, NULL, 142.80, NULL, NULL, '2026-05-09 21:16:47', NULL, NULL, 'PENDIENTE', NULL, NULL),
(73, 63, 1, 'CUADRE', 4, 71, NULL, NULL, 45.30, NULL, NULL, '2026-05-09 21:16:59', NULL, NULL, 'PENDIENTE', NULL, NULL),
(74, 64, 1, 'CUADRE', 2, 57, NULL, NULL, 103.10, NULL, NULL, '2026-05-10 03:59:03', NULL, NULL, 'PENDIENTE', NULL, NULL),
(75, 64, 1, 'CUADRE', 4, 57, NULL, NULL, 3.00, NULL, NULL, '2026-05-10 03:59:12', NULL, NULL, 'PENDIENTE', NULL, NULL),
(76, 64, 2, 'CUADRE', 1, 57, NULL, 'descargo algodon', 2.60, NULL, NULL, '2026-05-10 03:59:46', NULL, NULL, 'APROBADO', NULL, NULL),
(77, 65, 2, 'CUADRE', 1, 53, NULL, 'compra de glucophage xr 100 x que faltaba completar para venta', 76.60, NULL, NULL, '2026-05-10 04:05:06', NULL, NULL, 'APROBADO', NULL, NULL),
(78, 66, 1, 'CUADRE', 2, 17, NULL, NULL, 583.80, NULL, NULL, '2026-05-10 04:05:54', NULL, NULL, 'PENDIENTE', NULL, NULL),
(79, 66, 1, 'CUADRE', 4, 17, NULL, NULL, 277.00, NULL, NULL, '2026-05-10 04:06:10', NULL, NULL, 'PENDIENTE', NULL, NULL),
(80, 66, 2, 'CUADRE', 1, 17, NULL, 'compra paliglobo', 32.00, NULL, NULL, '2026-05-10 04:07:21', NULL, NULL, 'APROBADO', NULL, NULL),
(81, 66, 2, 'CUADRE', 1, 17, NULL, 'deposito sra. Marina', 500.00, NULL, NULL, '2026-05-10 04:07:21', NULL, NULL, 'APROBADO', NULL, NULL),
(82, 67, 1, 'CUADRE', 2, 71, NULL, NULL, 255.60, NULL, NULL, '2026-05-10 04:14:52', NULL, NULL, 'PENDIENTE', NULL, NULL),
(83, 67, 1, 'CUADRE', 4, 71, NULL, NULL, 156.40, NULL, NULL, '2026-05-10 04:15:02', NULL, NULL, 'PENDIENTE', NULL, NULL),
(84, 67, 2, 'CUADRE', 1, 71, NULL, '1 docena de paliglobos', 2.50, NULL, NULL, '2026-05-10 04:16:04', NULL, NULL, 'APROBADO', NULL, NULL),
(97, 68, 1, 'CUADRE', 4, 17, NULL, NULL, 111.10, NULL, NULL, '2026-05-10 19:57:54', NULL, NULL, 'PENDIENTE', NULL, NULL),
(98, 68, 1, 'CUADRE', 2, 17, NULL, NULL, 306.00, NULL, NULL, '2026-05-10 19:58:57', NULL, NULL, 'PENDIENTE', NULL, NULL),
(102, 69, 1, 'CUADRE', 2, 69, NULL, NULL, 166.50, NULL, NULL, '2026-05-10 20:02:17', NULL, NULL, 'PENDIENTE', NULL, NULL),
(103, 68, 2, 'CUADRE', 1, 17, NULL, 'compra de jabon', 4.50, NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'APROBADO', NULL, NULL),
(104, 68, 2, 'CUADRE', 1, 17, NULL, 'compra de bolsas', 67.00, NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'APROBADO', NULL, NULL),
(105, 68, 2, 'CUADRE', 1, 17, NULL, 'pago de terreno Ayacucho', 2499.99, NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'APROBADO', NULL, NULL),
(106, 70, 1, 'CUADRE', 2, 53, NULL, NULL, 112.40, NULL, NULL, '2026-05-10 20:06:04', NULL, NULL, 'PENDIENTE', NULL, NULL),
(107, 70, 2, 'CUADRE', 1, 53, NULL, 'tiras reactivas de glucosa', 79.80, NULL, NULL, '2026-05-10 20:07:09', NULL, NULL, 'APROBADO', NULL, NULL),
(108, 71, 1, 'CUADRE', 2, 11, NULL, NULL, 356.30, NULL, NULL, '2026-05-10 20:17:35', NULL, NULL, 'PENDIENTE', NULL, NULL),
(109, 71, 1, 'CUADRE', 4, 11, NULL, NULL, 6.60, NULL, NULL, '2026-05-10 20:17:43', NULL, NULL, 'PENDIENTE', NULL, NULL),
(110, 72, 1, 'CUADRE', 2, 22, NULL, NULL, 173.70, NULL, NULL, '2026-05-11 03:01:27', NULL, NULL, 'PENDIENTE', NULL, NULL),
(111, 74, 1, 'CUADRE', 2, 73, NULL, NULL, 366.60, NULL, NULL, '2026-05-11 04:01:14', NULL, NULL, 'PENDIENTE', NULL, NULL),
(112, 74, 1, 'CUADRE', 4, 73, NULL, NULL, 166.00, NULL, NULL, '2026-05-11 04:01:23', NULL, NULL, 'PENDIENTE', NULL, NULL),
(113, 74, 2, 'CUADRE', 1, 73, NULL, 'pago de producto al señor ciro', 80.50, NULL, NULL, '2026-05-11 04:03:57', NULL, NULL, 'APROBADO', NULL, NULL),
(114, 75, 1, 'CUADRE', 2, 29, NULL, NULL, 72.90, NULL, NULL, '2026-05-11 04:09:17', NULL, NULL, 'PENDIENTE', NULL, NULL),
(117, 88, 1, 'CUADRE', 2, 29, NULL, NULL, 119.50, NULL, NULL, '2026-05-11 20:08:12', NULL, NULL, 'PENDIENTE', NULL, NULL),
(118, 88, 1, 'CUADRE', 4, 29, NULL, NULL, 31.80, NULL, NULL, '2026-05-11 20:08:38', NULL, NULL, 'PENDIENTE', NULL, NULL),
(119, 87, 1, 'CUADRE', 2, 57, NULL, NULL, 696.20, NULL, NULL, '2026-05-11 20:10:08', NULL, NULL, 'PENDIENTE', NULL, NULL),
(120, 87, 1, 'CUADRE', 4, 57, NULL, NULL, 254.50, NULL, NULL, '2026-05-11 20:10:24', NULL, NULL, 'PENDIENTE', NULL, NULL),
(121, 89, 1, 'CUADRE', 2, 73, NULL, NULL, 174.90, NULL, NULL, '2026-05-11 20:15:19', NULL, NULL, 'PENDIENTE', NULL, NULL),
(122, 89, 1, 'CUADRE', 4, 73, NULL, NULL, 56.60, NULL, NULL, '2026-05-11 20:15:52', NULL, NULL, 'PENDIENTE', NULL, NULL),
(123, 90, 1, 'CUADRE', 2, 29, NULL, NULL, 222.50, NULL, NULL, '2026-05-12 04:04:27', NULL, NULL, 'PENDIENTE', NULL, NULL),
(124, 90, 1, 'CUADRE', 4, 29, NULL, NULL, 27.40, NULL, NULL, '2026-05-12 04:04:39', NULL, NULL, 'PENDIENTE', NULL, NULL),
(126, 92, 1, 'CUADRE', 4, 71, NULL, NULL, 72.00, NULL, NULL, '2026-05-12 04:10:16', NULL, NULL, 'PENDIENTE', NULL, NULL),
(127, 93, 1, 'CUADRE', 2, 57, NULL, NULL, 996.40, NULL, NULL, '2026-05-12 04:10:44', NULL, NULL, 'PENDIENTE', NULL, NULL),
(128, 93, 1, 'CUADRE', 4, 57, NULL, NULL, 447.40, NULL, NULL, '2026-05-12 04:10:56', NULL, NULL, 'PENDIENTE', NULL, NULL),
(129, 93, 2, 'CUADRE', 1, 57, NULL, 'PASAJE SB4', 8.00, NULL, NULL, '2026-05-12 04:12:17', NULL, NULL, 'APROBADO', NULL, NULL),
(130, 92, 1, 'CUADRE', 2, 71, NULL, NULL, 306.50, NULL, NULL, '2026-05-12 04:31:35', NULL, NULL, 'PENDIENTE', NULL, NULL),
(131, 94, 1, 'CUADRE', 2, 54, NULL, NULL, 43.00, NULL, NULL, '2026-05-12 20:03:14', NULL, NULL, 'PENDIENTE', NULL, NULL),
(132, 94, 1, 'CUADRE', 4, 54, NULL, NULL, 9.00, NULL, NULL, '2026-05-12 20:03:30', NULL, NULL, 'PENDIENTE', NULL, NULL),
(133, 95, 1, 'CUADRE', 2, 57, NULL, NULL, 649.30, NULL, NULL, '2026-05-12 20:09:44', NULL, NULL, 'PENDIENTE', NULL, NULL),
(134, 95, 1, 'CUADRE', 4, 57, NULL, NULL, 371.20, NULL, NULL, '2026-05-12 20:09:58', NULL, NULL, 'PENDIENTE', NULL, NULL),
(135, 97, 1, 'CUADRE', 2, 73, NULL, NULL, 303.80, NULL, NULL, '2026-05-12 20:13:50', NULL, NULL, 'PENDIENTE', NULL, NULL),
(136, 97, 1, 'CUADRE', 4, 73, NULL, NULL, 383.30, NULL, NULL, '2026-05-12 20:14:11', NULL, NULL, 'PENDIENTE', NULL, NULL),
(137, 100, 2, 'CUADRE', 1, 53, NULL, 'compra de ciro', 37.00, NULL, NULL, '2026-05-13 03:58:51', NULL, NULL, 'APROBADO', NULL, NULL),
(143, 101, 1, 'CUADRE', 2, 71, NULL, NULL, 180.10, NULL, NULL, '2026-05-13 04:19:32', NULL, NULL, 'PENDIENTE', NULL, NULL),
(144, 101, 1, 'CUADRE', 4, 71, NULL, NULL, 52.20, NULL, NULL, '2026-05-13 04:20:02', NULL, NULL, 'PENDIENTE', NULL, NULL),
(145, 103, 1, 'CUADRE', 2, 69, NULL, NULL, 18.10, NULL, NULL, '2026-05-13 05:45:53', NULL, NULL, 'PENDIENTE', NULL, NULL),
(146, 103, 1, 'CUADRE', 2, 69, NULL, NULL, 153.00, NULL, NULL, '2026-05-13 05:46:00', NULL, NULL, 'PENDIENTE', NULL, NULL),
(150, 105, 1, 'CUADRE', 2, 17, NULL, NULL, 409.20, NULL, NULL, '2026-05-13 05:51:36', NULL, NULL, 'PENDIENTE', NULL, NULL),
(151, 105, 1, 'CUADRE', 2, 17, NULL, NULL, 957.50, NULL, NULL, '2026-05-13 05:51:46', NULL, NULL, 'PENDIENTE', NULL, NULL),
(152, 105, 2, 'CUADRE', 1, 17, NULL, 'pasaje sb4', 2.50, NULL, NULL, '2026-05-13 05:52:26', NULL, NULL, 'APROBADO', NULL, NULL),
(153, 112, 1, 'CUADRE', 2, 53, NULL, NULL, 419.20, NULL, NULL, '2026-05-13 19:03:26', NULL, NULL, 'PENDIENTE', NULL, NULL),
(154, 112, 1, 'CUADRE', 4, 53, NULL, NULL, 393.20, NULL, NULL, '2026-05-13 19:03:34', NULL, NULL, 'PENDIENTE', NULL, NULL),
(155, 115, 1, 'CUADRE', 2, 29, NULL, NULL, 99.30, NULL, NULL, '2026-05-13 20:04:41', NULL, NULL, 'PENDIENTE', NULL, NULL),
(156, 115, 1, 'CUADRE', 4, 29, NULL, NULL, 19.90, NULL, NULL, '2026-05-13 20:04:51', NULL, NULL, 'PENDIENTE', NULL, NULL),
(157, 114, 1, 'CUADRE', 2, 11, NULL, NULL, 205.10, NULL, NULL, '2026-05-13 23:43:10', NULL, NULL, 'PENDIENTE', NULL, NULL),
(158, 114, 1, 'CUADRE', 4, 11, NULL, NULL, 102.70, NULL, NULL, '2026-05-13 23:43:21', NULL, NULL, 'PENDIENTE', NULL, NULL),
(159, 116, 1, 'CUADRE', 2, 17, NULL, NULL, 581.80, NULL, NULL, '2026-05-14 03:57:11', NULL, NULL, 'PENDIENTE', NULL, NULL),
(160, 116, 2, 'CUADRE', 1, 17, NULL, 'pasaje sb4', 2.50, NULL, NULL, '2026-05-14 03:58:21', NULL, NULL, 'APROBADO', NULL, NULL),
(161, 118, 1, 'CUADRE', 2, 29, NULL, NULL, 131.90, NULL, NULL, '2026-05-14 04:01:56', NULL, NULL, 'PENDIENTE', NULL, NULL),
(162, 118, 1, 'CUADRE', 4, 29, NULL, NULL, 27.00, NULL, NULL, '2026-05-14 04:02:04', NULL, NULL, 'PENDIENTE', NULL, NULL),
(163, 119, 1, 'CUADRE', 2, 71, NULL, NULL, 361.40, NULL, NULL, '2026-05-14 04:11:38', NULL, NULL, 'PENDIENTE', NULL, NULL),
(164, 119, 1, 'CUADRE', 4, 71, NULL, NULL, 71.10, NULL, NULL, '2026-05-14 04:11:46', NULL, NULL, 'PENDIENTE', NULL, NULL),
(165, 120, 1, 'CUADRE', 2, 29, NULL, NULL, 119.30, NULL, NULL, '2026-05-14 20:00:44', NULL, NULL, 'PENDIENTE', NULL, NULL),
(166, 120, 1, 'CUADRE', 4, 29, NULL, NULL, 30.00, NULL, NULL, '2026-05-14 20:00:54', NULL, NULL, 'PENDIENTE', NULL, NULL),
(167, 121, 1, 'CUADRE', 2, 13, NULL, NULL, 305.30, NULL, NULL, '2026-05-14 20:08:42', NULL, NULL, 'PENDIENTE', NULL, NULL),
(168, 121, 2, 'CUADRE', 1, 13, NULL, 'uso botica', 3.00, NULL, NULL, '2026-05-14 20:10:06', NULL, NULL, 'APROBADO', NULL, NULL),
(169, 121, 2, 'CUADRE', 1, 13, NULL, 'jorge mantenimiento', 8.00, NULL, NULL, '2026-05-14 20:10:06', NULL, NULL, 'APROBADO', NULL, NULL),
(170, 122, 1, 'CUADRE', 2, 17, NULL, NULL, 267.60, NULL, NULL, '2026-05-14 20:14:08', NULL, NULL, 'PENDIENTE', NULL, NULL),
(171, 122, 1, 'CUADRE', 4, 17, NULL, NULL, 80.70, NULL, NULL, '2026-05-14 20:14:30', NULL, NULL, 'PENDIENTE', NULL, NULL),
(172, 125, 1, 'CUADRE', 2, 69, NULL, NULL, 140.30, NULL, NULL, '2026-05-15 03:49:27', NULL, NULL, 'PENDIENTE', NULL, NULL),
(173, 125, 1, 'CUADRE', 4, 69, NULL, NULL, 19.90, NULL, NULL, '2026-05-15 03:49:39', NULL, NULL, 'PENDIENTE', NULL, NULL),
(174, 125, 1, 'CUADRE', 2, 69, NULL, NULL, 6.00, NULL, NULL, '2026-05-15 03:57:59', NULL, NULL, 'PENDIENTE', NULL, NULL),
(175, 126, 1, 'CUADRE', 2, 57, NULL, NULL, 694.80, NULL, NULL, '2026-05-15 04:07:45', NULL, NULL, 'PENDIENTE', NULL, NULL),
(176, 126, 1, 'CUADRE', 4, 57, NULL, NULL, 332.60, NULL, NULL, '2026-05-15 04:07:56', NULL, NULL, 'PENDIENTE', NULL, NULL),
(177, 127, 1, 'CUADRE', 2, 71, NULL, NULL, 172.30, NULL, NULL, '2026-05-15 04:14:01', NULL, NULL, 'PENDIENTE', NULL, NULL),
(178, 127, 1, 'CUADRE', 4, 71, NULL, NULL, 11.50, NULL, NULL, '2026-05-15 04:14:09', NULL, NULL, 'PENDIENTE', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nivel`
--

CREATE TABLE `nivel` (
  `id_nivel` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nivel`
--

INSERT INTO `nivel` (`id_nivel`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Básico', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'Intermedio', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Avanzado', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `pago_deposito`
--

CREATE TABLE `pago_deposito` (
  `id_pago_deposito` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `origen` enum('CUADRE','CORRECCION') NOT NULL DEFAULT 'CUADRE',
  `postulante_emisor_id` int(11) NOT NULL,
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado` enum('APROBADO') NOT NULL DEFAULT 'APROBADO',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pago_deposito`
--

INSERT INTO `pago_deposito` (`id_pago_deposito`, `sesion_id`, `origen`, `postulante_emisor_id`, `numero_comprobante`, `monto`, `estado`, `fecha_registro`) VALUES
(6, 113, 'CUADRE', 22, 'deposito grupo kgyr', 11000.00, 'APROBADO', '2026-05-13 20:00:35'),
(7, 114, 'CUADRE', 11, NULL, 12000.00, 'APROBADO', '2026-05-13 23:51:05');

-- --------------------------------------------------------

--
-- Table structure for table `pago_factura`
--

CREATE TABLE `pago_factura` (
  `id_pago_factura` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `origen` enum('CUADRE','CORRECCION') NOT NULL DEFAULT 'CUADRE',
  `postulante_emisor_id` int(11) NOT NULL,
  `tipo_documento` enum('BOLETA','FACTURA','NOTA_DE_VENTA') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `estado` enum('APROBADO') NOT NULL DEFAULT 'APROBADO',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pago_factura`
--

INSERT INTO `pago_factura` (`id_pago_factura`, `sesion_id`, `origen`, `postulante_emisor_id`, `tipo_documento`, `monto`, `numero_comprobante`, `estado`, `fecha_registro`) VALUES
(1, 93, 'CUADRE', 57, 'BOLETA', 69.00, 'CIRO', 'APROBADO', '2026-05-12 04:12:17'),
(2, 122, 'CUADRE', 17, 'BOLETA', 543.40, NULL, 'APROBADO', '2026-05-14 20:16:48'),
(3, 126, 'CUADRE', 57, 'BOLETA', 767.98, 'alcohol rodrigo', 'APROBADO', '2026-05-15 04:09:34'),
(4, 126, 'CUADRE', 57, 'BOLETA', 774.12, 'calcibone', 'APROBADO', '2026-05-15 04:09:34');

-- --------------------------------------------------------

--
-- Table structure for table `pago_local`
--

CREATE TABLE `pago_local` (
  `id_pago_local` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `origen` enum('CUADRE','CORRECCION') NOT NULL DEFAULT 'CUADRE',
  `tipo_egreso_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `local_id` int(11) NOT NULL,
  `postulante_emisor_id` int(11) NOT NULL,
  `concepto_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_operacion` varchar(100) DEFAULT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','OBSERVADO','RECHAZADO','APROBADO') NOT NULL DEFAULT 'PENDIENTE',
  `observacion_revision` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pago_personal`
--

CREATE TABLE `pago_personal` (
  `id_pago_personal` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `origen` enum('CUADRE','CORRECCION') NOT NULL DEFAULT 'CUADRE',
  `postulante_emisor_id` int(11) NOT NULL,
  `postulante_beneficiario_id` int(11) NOT NULL,
  `postulante_revisor_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tipo_pago` enum('ADELANTO','PAGO_TOTAL','OTROS') NOT NULL DEFAULT 'PAGO_TOTAL',
  `numero_operacion` varchar(100) DEFAULT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_confirmacion_beneficiario` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','PAGADO','CONFIRMADO_BENEFICIARIO','OBSERVADO','RECHAZADO','APROBADO') NOT NULL DEFAULT 'PENDIENTE',
  `observacion_beneficiario` text DEFAULT NULL,
  `observacion_revision` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pago_personal`
--

INSERT INTO `pago_personal` (`id_pago_personal`, `sesion_id`, `origen`, `postulante_emisor_id`, `postulante_beneficiario_id`, `postulante_revisor_id`, `monto`, `tipo_pago`, `numero_operacion`, `comprobante_url`, `fecha_pago`, `fecha_confirmacion_beneficiario`, `fecha_revision`, `estado`, `observacion_beneficiario`, `observacion_revision`) VALUES
(4, 55, 'CUADRE', 69, 69, NULL, 640.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-09 04:10:44', NULL, NULL, 'PAGADO', NULL, NULL),
(5, 61, 'CUADRE', 73, 22, NULL, 649.99, 'PAGO_TOTAL', NULL, NULL, '2026-05-09 20:03:56', NULL, NULL, 'PAGADO', NULL, NULL),
(11, 68, 'CUADRE', 17, 73, NULL, 150.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'PAGADO', NULL, NULL),
(12, 87, 'CUADRE', 57, 70, NULL, 840.00, 'ADELANTO', NULL, NULL, '2026-05-11 20:11:59', NULL, NULL, 'PAGADO', NULL, NULL),
(13, 87, 'CUADRE', 57, 72, NULL, 103.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-11 20:11:59', NULL, NULL, 'PAGADO', NULL, NULL),
(14, 93, 'CUADRE', 57, 54, NULL, 350.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-12 04:12:17', NULL, NULL, 'PAGADO', NULL, NULL),
(15, 95, 'CUADRE', 57, 72, NULL, 53.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-12 20:10:31', NULL, NULL, 'PAGADO', NULL, NULL),
(18, 105, 'CUADRE', 17, 73, NULL, 100.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-13 05:52:26', NULL, NULL, 'PAGADO', NULL, NULL),
(19, 116, 'CUADRE', 17, 5, NULL, 1700.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-14 03:58:21', NULL, NULL, 'PAGADO', NULL, NULL),
(20, 122, 'CUADRE', 17, 17, NULL, 800.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-14 20:16:48', NULL, NULL, 'PAGADO', NULL, NULL),
(21, 122, 'CUADRE', 17, 57, NULL, 565.00, 'PAGO_TOTAL', NULL, NULL, '2026-05-14 20:16:48', NULL, NULL, 'PAGADO', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plantilla_horario`
--

CREATE TABLE `plantilla_horario` (
  `id_plantilla` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `rol_horario_id` int(11) NOT NULL,
  `cantidad` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plantilla_horario`
--

INSERT INTO `plantilla_horario` (`id_plantilla`, `local_id`, `turno_id`, `rol_horario_id`, `cantidad`) VALUES
(1, 2, 1, 1, 1),
(2, 2, 1, 2, 1),
(3, 2, 1, 3, 1),
(4, 2, 2, 1, 1),
(5, 2, 2, 2, 1),
(6, 2, 2, 3, 1),
(7, 3, 1, 1, 3),
(8, 3, 1, 2, 2),
(9, 3, 1, 3, 2),
(10, 3, 2, 1, 3),
(11, 3, 2, 2, 2),
(12, 3, 2, 3, 2),
(13, 4, 1, 1, 1),
(14, 4, 1, 2, 1),
(15, 4, 1, 3, 1),
(16, 4, 2, 1, 1),
(17, 4, 2, 2, 1),
(18, 4, 2, 3, 1),
(19, 2, 1, 4, 1),
(20, 3, 1, 4, 1),
(21, 4, 1, 4, 1),
(22, 2, 2, 4, 1),
(23, 3, 2, 4, 1),
(24, 4, 2, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `postulacion`
--

CREATE TABLE `postulacion` (
  `id_postulacion` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `puesto_id` int(11) NOT NULL,
  `etapa_id` int(11) DEFAULT 1,
  `visto` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_vista` timestamp NULL DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `fecha_postulacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `postulacion`
--

INSERT INTO `postulacion` (`id_postulacion`, `postulante_id`, `puesto_id`, `etapa_id`, `visto`, `fecha_vista`, `observacion`, `fecha_postulacion`, `fecha_modificacion`) VALUES
(2, 1, 1, 4, 0, NULL, NULL, '2026-05-04 21:08:25', '2026-05-04 21:08:25'),
(15, 4, 7, 4, 0, NULL, NULL, '2026-05-06 00:56:32', '2026-05-06 00:56:32'),
(18, 10, 8, 4, 0, NULL, NULL, '2026-05-06 00:58:41', '2026-05-06 00:58:41'),
(20, 13, 7, 4, 0, NULL, NULL, '2026-05-06 01:10:08', '2026-05-06 01:10:08'),
(22, 5, 2, 4, 0, NULL, NULL, '2026-05-06 01:28:02', '2026-05-06 01:28:02'),
(23, 11, 7, 4, 0, NULL, NULL, '2026-05-06 01:28:28', '2026-05-06 01:28:28'),
(24, 17, 3, 4, 0, NULL, NULL, '2026-05-06 01:29:19', '2026-05-06 01:29:19'),
(25, 19, 7, 4, 0, NULL, NULL, '2026-05-06 01:31:08', '2026-05-06 01:31:08'),
(28, 22, 7, 4, 0, NULL, NULL, '2026-05-06 01:34:36', '2026-05-06 01:34:36'),
(30, 29, 7, 4, 0, NULL, NULL, '2026-05-06 01:36:34', '2026-05-06 01:36:34'),
(32, 45, 7, 4, 0, NULL, NULL, '2026-05-06 01:39:39', '2026-05-06 01:39:39'),
(34, 51, 7, 4, 0, NULL, NULL, '2026-05-06 01:41:52', '2026-05-06 01:41:52'),
(38, 53, 3, 4, 0, NULL, NULL, '2026-05-06 01:46:15', '2026-05-06 01:46:15'),
(40, 54, 7, 4, 0, NULL, NULL, '2026-05-06 01:49:29', '2026-05-06 01:49:29'),
(42, 59, 7, 4, 0, NULL, NULL, '2026-05-06 01:50:30', '2026-05-06 01:50:30'),
(44, 60, 7, 4, 0, NULL, NULL, '2026-05-06 01:51:26', '2026-05-06 01:51:26'),
(46, 61, 3, 4, 0, NULL, NULL, '2026-05-06 01:52:30', '2026-05-06 01:52:30'),
(47, 12, 7, 4, 0, NULL, NULL, '2026-05-06 01:53:47', '2026-05-06 01:53:47'),
(48, 52, 3, 4, 0, NULL, NULL, '2026-05-06 01:54:27', '2026-05-06 01:54:27'),
(52, 57, 3, 4, 0, NULL, NULL, '2026-05-06 15:27:24', '2026-05-06 15:27:24'),
(55, 55, 7, 4, 0, NULL, NULL, '2026-05-06 16:32:24', '2026-05-06 16:32:24'),
(61, 69, 3, 4, 0, NULL, NULL, '2026-05-06 18:09:59', '2026-05-06 18:09:59'),
(65, 70, 7, 4, 0, NULL, NULL, '2026-05-06 21:02:12', '2026-05-06 21:02:12'),
(67, 71, 7, 4, 0, NULL, NULL, '2026-05-06 21:04:09', '2026-05-06 21:04:09'),
(69, 72, 1, 4, 0, NULL, NULL, '2026-05-06 23:34:30', '2026-05-06 23:34:30'),
(74, 73, 7, 4, 0, NULL, NULL, '2026-05-08 15:11:35', '2026-05-08 15:11:35'),
(75, 68, 7, 4, 0, NULL, NULL, '2026-05-13 18:11:20', '2026-05-13 18:11:20');

-- --------------------------------------------------------

--
-- Table structure for table `postulante`
--

CREATE TABLE `postulante` (
  `id_postulante` int(11) NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `genero_id` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `situacion_vivienda_id` int(11) DEFAULT NULL,
  `num_documento` varchar(8) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `distrito` varchar(100) DEFAULT NULL,
  `calificacion` decimal(5,2) DEFAULT NULL,
  `foto_url` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cv_url` varchar(255) DEFAULT NULL,
  `etapa_id` int(11) DEFAULT 1,
  `tipo_personal` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `postulante`
--

INSERT INTO `postulante` (`id_postulante`, `nombres`, `apellidos`, `genero_id`, `fecha_nacimiento`, `email`, `telefono`, `situacion_vivienda_id`, `num_documento`, `direccion`, `distrito`, `calificacion`, `foto_url`, `fecha_registro`, `fecha_modificacion`, `cv_url`, `etapa_id`, `tipo_personal`) VALUES
(1, 'Gian Carlo', 'Vilcamiche Chávez', 1, '1991-02-16', '', '935812267', NULL, '47238914', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 00:41:20', NULL, 4, NULL),
(2, 'Solange Moulin', 'Coronel Camacllanqui', 2, NULL, 'solange@test.com', '923402449', 2, '75818239', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 02:26:31', NULL, 1, ''),
(3, 'Milagros Del Pilar', 'Huamán Cruzado', 2, '1987-10-01', NULL, '986152754', NULL, '44850621', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(4, 'Dariana', 'Bautista Contreras', 2, '1999-08-19', '', '926491304', 2, '71694239', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:20:10', NULL, 4, 'A1'),
(5, 'Patricia del Pilar', 'Obregon Pozo', 2, '2001-08-10', '', '980815404', 2, '71637953', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:28:02', NULL, 4, 'B1'),
(6, 'Maryori', 'Flores Ubaldo', 2, '1999-10-16', NULL, '985951246', NULL, '75519567', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(7, 'Maribel Rosario', 'Salazar Baldeon', 2, '1992-11-10', NULL, '937863443', NULL, '47512524', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(8, 'María Doris', 'García Torres', 2, '1990-02-19', NULL, '932767767', NULL, '46254125', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(9, 'Flor de Maria', 'Mercedes Huayta', 2, '1990-06-29', NULL, '928134625', NULL, '47752886', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(10, 'Karen Lizbeth', 'Martinez Encina', 2, '2001-06-09', '', '953933814', 1, '72220359', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:20:10', NULL, 4, 'A1'),
(11, 'Fiorella del Rosario', 'Chambi Rafaile', 2, '1998-05-20', '', '991241518', 2, '48857877', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:28:28', NULL, 4, 'A1'),
(12, 'Sharik Sheylly', 'Rodriguez Pineda', 2, '2004-12-01', '', '927025545', 2, '76863236', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:20:10', NULL, 4, 'B1'),
(13, 'Monica', 'Quispe Ccallo', 2, '2002-03-17', '', '967697231', 2, '74399262', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:20:10', NULL, 4, 'B1'),
(14, 'Karin Gianina', 'Ramirez Calixto', 2, NULL, NULL, '971292140', NULL, '73389615', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(15, 'Leidi', 'Peralta Colunche', 2, NULL, NULL, '924666882', NULL, '71142925', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(16, 'Diana', 'Mendoza Huaman', 2, '1998-03-03', NULL, '955059406', NULL, '76221752', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(17, 'Rocío Geraldinne', 'Quispe Alberco', 2, '1994-02-24', '', '936839098', 2, '72667321', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:29:19', NULL, 4, 'X1'),
(18, 'Guillermina Yomnis', 'Santos Basilio', 2, NULL, NULL, '912557536', NULL, '48219564', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(19, 'Elizabeth', 'Flores Silva', 2, NULL, '', '', 3, '47943458', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:31:08', NULL, 4, 'C1'),
(20, 'Marina', 'Heredia Acuña', 2, '1987-08-15', NULL, '949451967', NULL, '44428885', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(21, 'Alexander Rafael', 'Suarez Chacón', 1, '1992-06-10', NULL, '974190345', NULL, '47823006', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(22, 'Yolvi Romelia', 'Patricio Flores', 2, '1995-09-07', '', '973486812', 2, '76794496', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:34:06', NULL, 4, 'C1'),
(23, 'Inoe', 'Ortiz Quispe', 2, NULL, NULL, '921014820', NULL, '70576163', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(24, 'Sheila', 'Marcos Chagua', 2, '1995-11-27', NULL, '972021267', NULL, '73634205', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(25, 'Elena Dayana', 'Peña Manrique', 2, '1999-11-24', NULL, '923831364', NULL, '76633896', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(26, 'Dilza Elizabeth', 'Alarcon Muñoz', 2, '1992-06-27', NULL, '970832706', NULL, '48213065', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(27, 'Sharon Candy', 'Marcos Alfaro', 2, NULL, NULL, '936751302', NULL, '76221750', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(28, 'Miriam Oriana', 'Aguirre Borja', 2, '1990-04-08', NULL, '917328713', NULL, '46303722', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(29, 'Yenifer Katia', 'Quispe Llacchua', 2, '2002-07-10', '', '987083660', 2, '70686877', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:36:12', NULL, 4, 'C1'),
(30, 'Lizbeth', 'Quispe de la Cruz', 2, '2001-03-30', NULL, '928349105', NULL, '72109429', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(31, 'Yoselin Margarita', 'Baldera Siesquén', 2, '1993-11-08', NULL, '927219177', NULL, '48288048', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(32, 'Gavi', 'Santos Ascencio', 2, NULL, NULL, '922880107', NULL, '71020821', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(33, 'Roy Anthony', 'Vilcamiche Chavez', 1, '1989-03-02', NULL, '999443808', NULL, '45627948', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(34, 'Loreli Elizabeth', 'Salas Zuñiga', 2, '1994-02-10', NULL, '984135857', NULL, '48409771', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(35, 'Nayeli', 'Benancio Espinoza', 2, '2003-10-21', NULL, '931421447', NULL, '75603108', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(36, 'Geraldine Rosario', 'Felices Escobar', 2, '2000-12-28', NULL, '902280060', NULL, '76279496', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(37, 'Analu', 'Fonseca Fernández', 2, '2001-12-16', NULL, '955596689', NULL, '74384465', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(38, 'Delina', 'Guillen Matos', 2, '1992-10-29', NULL, '935669323', NULL, '47496488', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(39, 'Lisset', 'Bonifacio Duran', 2, '2001-04-25', NULL, '927914498', NULL, '77807884', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(40, 'Ana Lucia', 'Coaquira Mamani', 2, '1997-12-04', NULL, '936034533', NULL, '76325704', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(41, 'Jhovani', 'Suarez Cueva', 2, NULL, NULL, '990815725', NULL, '62117689', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(42, 'Carola Liz', 'Carhuaricra Reyes', 2, NULL, NULL, '965829567', NULL, '70127392', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(43, 'Luis Daryl', 'Sanchez Garcia', 1, '2004-09-02', NULL, '948676116', NULL, '72552020', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(44, 'Carmen Esmeralda', 'Guadalupe Galarza', 2, '1997-06-20', NULL, '927467567', NULL, '71293391', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(45, 'Erika Yuliana', 'Guerrero Huerta', 2, '2000-12-31', '', '910296978', 2, '73529760', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:39:11', NULL, 4, 'B1'),
(46, 'Kristhel Valeria', 'Vilcamiche Chávez', 2, NULL, NULL, NULL, NULL, '73623849', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(47, 'Marta', 'Laurente Lopez', 2, NULL, NULL, NULL, NULL, '48141371', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(49, 'Lucelly Angelmira', 'Robles Jauregui', 2, NULL, NULL, NULL, NULL, '74206381', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(50, 'Yamilla Anelhy', 'Quispe Silva', 2, NULL, NULL, NULL, NULL, '74588769', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(51, 'Dayana Ross', 'Boy Arellano', 2, NULL, '', '', 2, '75824495', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:41:25', NULL, 4, 'B1'),
(52, 'Merlinda Yessica', 'Bautista Contreras', 2, NULL, '', '', 2, '71694214', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:43:04', NULL, 4, 'X1'),
(53, 'Lucia Belen', 'Arango Caico', 2, NULL, '', '', 2, '76507846', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:45:57', NULL, 4, 'X1'),
(54, 'Yovaly Tatiana', 'De la Cruz Roque', 2, NULL, '', '', 2, '73111770', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:49:07', NULL, 4, 'B1'),
(55, 'Orfelinda Anahi', 'Modesto Cespedes', 2, '1994-03-25', 'orfelindacespedes17@gmail.com', '902101459', 2, '48348864', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 16:31:29', NULL, 4, 'B1'),
(56, 'Maria Ermendia', 'Yahuana Calderon', 2, NULL, '', '', 2, '74252343', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 15:23:21', NULL, 4, 'B1'),
(57, 'Elizabeth Rosa', 'Taype Cordova', 2, '1990-02-07', 'elizabeth.taype7427@gmail.com', '907223849', 2, '46300302', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 15:26:19', NULL, 4, 'C1'),
(58, 'Dasha Carla', 'Quichca Ramos', 2, NULL, NULL, NULL, NULL, '71884519', NULL, NULL, NULL, NULL, '2026-05-04 20:58:12', '2026-05-04 20:58:12', NULL, 1, NULL),
(59, 'Sandra Marina', 'Revoredo Quiñones', 2, NULL, '', '', 2, '44958162', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:50:13', NULL, 4, 'B1'),
(60, 'Eswin Eli', 'Salazar Ramirez', 1, NULL, '', '', 2, '76084263', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:51:04', NULL, 4, 'B1'),
(61, 'Fany Yadira', 'Benites Niquin', 2, NULL, '', '', 2, '71810694', '', '', NULL, NULL, '2026-05-04 20:58:12', '2026-05-06 01:52:12', NULL, 4, 'Y1'),
(68, 'VACIO', '', NULL, '2026-05-06', '', '', NULL, '00000000', '', '', NULL, NULL, '2026-05-06 17:06:24', '2026-05-13 18:11:20', NULL, 4, 'A1'),
(69, 'Flor Milenia', 'Huamani Yalle', 2, '2000-05-01', 'huamaniyallef@gmail.com', '921521070', 2, '76775002', '', '', NULL, NULL, '2026-05-06 18:07:23', '2026-05-06 18:09:14', NULL, 4, 'B1'),
(70, 'Debora', 'Peralta', 2, '2003-10-11', 'deborap0711@outlook.com', '902943304', 2, '70967730', '', '', NULL, NULL, '2026-05-06 20:58:36', '2026-05-06 21:01:14', NULL, 4, 'B1'),
(71, 'Esther Beatriz', 'Fernandez Huillca', 2, '1993-07-07', 'estherfernandezhuillca@outlook.es', '980872844', 2, '47883640', '', '', NULL, NULL, '2026-05-06 21:00:48', '2026-05-06 21:03:37', NULL, 4, 'B1'),
(72, 'Marina', 'Chavez Villavicencio', 2, '1961-08-28', 'marina.chavezvillavicencio@gmail.com', '947996894', 3, '28260072', '', '', NULL, NULL, '2026-05-06 23:33:40', '2026-05-06 23:34:30', NULL, 4, 'B1'),
(73, 'Victoria Jazmin', 'Huaman Arango', 2, '2000-12-09', '', '', 2, '71392072', '', '', NULL, NULL, '2026-05-07 02:06:06', '2026-05-08 15:11:35', NULL, 4, 'B1');

-- --------------------------------------------------------

--
-- Table structure for table `postulante_especialidad`
--

CREATE TABLE `postulante_especialidad` (
  `postulante_id` int(11) NOT NULL,
  `especialidad_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `postulante_skill`
--

CREATE TABLE `postulante_skill` (
  `postulante_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `nivel_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `postulante_skill`
--

INSERT INTO `postulante_skill` (`postulante_id`, `skill_id`, `nivel_id`) VALUES
(71, 7, 1),
(57, 5, 2),
(69, 5, 2),
(71, 1, 2),
(72, 5, 2),
(72, 7, 2),
(1, 6, 3),
(11, 1, 3),
(11, 7, 3),
(70, 1, 3),
(72, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `preferencias`
--

CREATE TABLE `preferencias` (
  `turno_id` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `preferencias`
--

INSERT INTO `preferencias` (`turno_id`, `postulante_id`, `fecha_registro`) VALUES
(1, 1, '2026-05-04 21:08:25'),
(1, 2, '2026-05-11 02:41:29'),
(1, 4, '2026-05-06 00:56:32'),
(1, 5, '2026-05-06 01:28:02'),
(1, 10, '2026-05-06 00:58:41'),
(1, 11, '2026-05-06 01:28:28'),
(1, 12, '2026-05-06 01:53:47'),
(1, 13, '2026-05-06 01:10:08'),
(1, 17, '2026-05-06 01:29:19'),
(1, 19, '2026-05-06 01:31:08'),
(1, 22, '2026-05-06 01:34:36'),
(1, 29, '2026-05-06 01:36:34'),
(1, 45, '2026-05-06 01:39:39'),
(1, 51, '2026-05-06 01:41:52'),
(1, 52, '2026-05-06 01:54:27'),
(1, 53, '2026-05-06 01:46:15'),
(1, 54, '2026-05-06 01:49:29'),
(1, 55, '2026-05-06 16:32:24'),
(1, 56, '2026-05-06 15:23:21'),
(1, 57, '2026-05-06 15:27:24'),
(1, 59, '2026-05-06 01:50:30'),
(1, 60, '2026-05-06 01:51:26'),
(1, 61, '2026-05-06 01:52:30'),
(1, 68, '2026-05-13 18:11:20'),
(1, 69, '2026-05-06 18:09:59'),
(1, 70, '2026-05-06 21:02:12'),
(1, 72, '2026-05-06 23:34:30'),
(1, 73, '2026-05-08 15:11:35'),
(2, 1, '2026-05-04 21:08:25'),
(2, 2, '2026-05-11 02:41:29'),
(2, 4, '2026-05-06 00:56:32'),
(2, 5, '2026-05-06 01:28:02'),
(2, 10, '2026-05-06 00:58:41'),
(2, 11, '2026-05-06 01:28:28'),
(2, 12, '2026-05-06 01:53:47'),
(2, 13, '2026-05-06 01:10:08'),
(2, 17, '2026-05-06 01:29:19'),
(2, 19, '2026-05-06 01:31:08'),
(2, 22, '2026-05-06 01:34:36'),
(2, 29, '2026-05-06 01:36:34'),
(2, 45, '2026-05-06 01:39:39'),
(2, 51, '2026-05-06 01:41:52'),
(2, 52, '2026-05-06 01:54:27'),
(2, 53, '2026-05-06 01:46:15'),
(2, 54, '2026-05-06 01:49:29'),
(2, 56, '2026-05-06 15:23:21'),
(2, 57, '2026-05-06 15:27:24'),
(2, 59, '2026-05-06 01:50:30'),
(2, 60, '2026-05-06 01:51:26'),
(2, 61, '2026-05-06 01:52:30'),
(2, 68, '2026-05-13 18:11:20'),
(2, 69, '2026-05-06 18:09:59'),
(2, 71, '2026-05-06 21:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `puesto`
--

CREATE TABLE `puesto` (
  `id_puesto` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `puesto`
--

INSERT INTO `puesto` (`id_puesto`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Administración', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'Almacén', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Caja', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(4, 'Contabilidad', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(5, 'Limpieza', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(6, 'Practicante', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(7, 'Técnica en Farmacia', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(8, 'QF', 1, '2026-05-06 00:58:14', '2026-05-06 00:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `rectificacion_cuadre`
--

CREATE TABLE `rectificacion_cuadre` (
  `id_rectificacion` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `postulante_registra_id` int(11) NOT NULL,
  `postulante_responsable_id` int(11) DEFAULT NULL,
  `tipo_rectificacion` enum('DEVOLUCION_DINERO','DINERO_ENCONTRADO','AJUSTE_CONTEO','COMPENSACION','OTRO') DEFAULT NULL,
  `tipo_rect_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `modo_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `descripcion_contexto` text NOT NULL,
  `justificacion` text DEFAULT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_rectificacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  `postulante_revisa_id` int(11) DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `observacion_revision` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rectificacion_cuadre`
--

INSERT INTO `rectificacion_cuadre` (`id_rectificacion`, `sesion_id`, `postulante_registra_id`, `postulante_responsable_id`, `tipo_rectificacion`, `tipo_rect_id`, `modo_id`, `monto`, `descripcion_contexto`, `justificacion`, `comprobante_url`, `fecha_rectificacion`, `estado`, `postulante_revisa_id`, `fecha_revision`, `observacion_revision`) VALUES
(9, 30, 17, NULL, 'DINERO_ENCONTRADO', 1, NULL, 10.00, 'encontrado en el piso', NULL, NULL, '2026-05-07 15:04:39', 'APROBADA', NULL, NULL, NULL),
(10, 30, 17, NULL, 'DEVOLUCION_DINERO', 2, NULL, -10.00, 'falta descargar', NULL, NULL, '2026-05-07 15:05:36', 'APROBADA', NULL, NULL, NULL),
(11, 33, 22, NULL, 'DEVOLUCION_DINERO', 2, NULL, -10.00, 'me equivoque con un yape', NULL, NULL, '2026-05-07 15:15:38', 'APROBADA', NULL, NULL, NULL),
(12, 33, 22, NULL, 'DINERO_ENCONTRADO', 1, NULL, 10.00, 'correcion', NULL, NULL, '2026-05-07 15:18:39', 'APROBADA', NULL, NULL, NULL),
(13, 31, 17, NULL, 'DINERO_ENCONTRADO', 1, NULL, 90.00, 'falto contar', NULL, NULL, '2026-05-07 20:17:11', 'APROBADA', NULL, NULL, NULL),
(14, 48, 69, NULL, 'DINERO_ENCONTRADO', 1, NULL, 0.41, 'falto contar en caja  0.4  y en la venta 0.01', NULL, NULL, '2026-05-08 14:16:36', 'APROBADA', NULL, NULL, NULL),
(15, 52, 22, NULL, 'DINERO_ENCONTRADO', 1, NULL, 120.00, 'compra de ciro', NULL, NULL, '2026-05-08 19:59:56', 'APROBADA', NULL, NULL, NULL),
(16, 51, 11, NULL, 'OTRO', NULL, NULL, 6.60, 'falta descargar', NULL, NULL, '2026-05-08 20:06:19', 'APROBADA', NULL, NULL, NULL),
(17, 51, 11, NULL, 'OTRO', NULL, NULL, -13.20, 'correcion', NULL, NULL, '2026-05-08 20:25:55', 'APROBADA', NULL, NULL, NULL),
(18, 67, 71, NULL, 'OTRO', NULL, NULL, -2.50, 'correcion', NULL, NULL, '2026-05-10 04:17:35', 'APROBADA', NULL, NULL, NULL),
(28, 86, 22, NULL, 'DEVOLUCION_DINERO', 2, NULL, -13406.10, 'saldo', NULL, NULL, '2026-05-11 20:00:32', 'APROBADA', NULL, NULL, NULL),
(36, 115, 29, NULL, 'DINERO_ENCONTRADO', 1, NULL, 12600.00, 'corrección caja fuerte', NULL, NULL, '2026-05-13 20:09:36', 'APROBADA', NULL, NULL, NULL),
(37, 114, 11, NULL, 'DINERO_ENCONTRADO', 1, NULL, 20.00, 'encontrado en el piso', NULL, NULL, '2026-05-13 23:52:08', 'APROBADA', NULL, NULL, NULL),
(39, 117, 1, NULL, 'DINERO_ENCONTRADO', 1, NULL, 1000.00, 'Faltó contar', NULL, NULL, '2026-05-14 04:13:01', 'APROBADA', NULL, NULL, NULL),
(40, 117, 1, NULL, 'DINERO_ENCONTRADO', 1, NULL, 50.00, 'encontre una visa para usa', NULL, NULL, '2026-05-14 16:38:48', 'APROBADA', NULL, NULL, NULL),
(41, 116, 17, NULL, 'DEVOLUCION_DINERO', 2, NULL, -50.00, 'sb5', NULL, NULL, '2026-05-14 16:47:18', 'APROBADA', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reporte_venta`
--

CREATE TABLE `reporte_venta` (
  `id_reporte_venta` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `postulante_vendedor_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reporte_venta`
--

INSERT INTO `reporte_venta` (`id_reporte_venta`, `sesion_id`, `postulante_vendedor_id`, `monto`, `fecha_registro`) VALUES
(20, 25, 29, 0.00, '2026-05-07 14:37:23'),
(22, 27, 29, 989.10, '2026-05-07 14:42:23'),
(24, 29, 17, 0.00, '2026-05-07 14:53:08'),
(25, 30, 17, 1479.55, '2026-05-07 14:58:16'),
(27, 32, 22, 0.00, '2026-05-07 15:11:06'),
(28, 33, 22, 2334.99, '2026-05-07 15:14:50'),
(30, 31, 17, 784.60, '2026-05-07 20:00:40'),
(31, 28, 29, 341.40, '2026-05-07 20:08:05'),
(32, 35, 22, 1117.35, '2026-05-07 20:16:44'),
(33, 37, 1, 0.00, '2026-05-07 20:20:28'),
(34, 38, 71, 1467.28, '2026-05-07 20:22:53'),
(36, 40, 54, 728.30, '2026-05-07 20:29:43'),
(39, 42, 17, 1263.99, '2026-05-08 04:01:07'),
(42, 44, 57, 1460.30, '2026-05-08 04:16:10'),
(45, 48, 69, 521.24, '2026-05-08 13:42:28'),
(47, 50, 73, 783.40, '2026-05-08 16:08:03'),
(48, 52, 22, 1280.50, '2026-05-08 19:52:13'),
(49, 51, 11, 546.70, '2026-05-08 20:04:46'),
(50, 47, 70, 801.50, '2026-05-08 20:10:13'),
(51, 53, 29, 291.40, '2026-05-08 20:10:31'),
(54, 57, 73, 945.40, '2026-05-09 04:10:00'),
(55, 55, 69, 1269.49, '2026-05-09 04:11:22'),
(56, 58, 53, 1412.29, '2026-05-09 04:12:58'),
(57, 59, 29, 571.90, '2026-05-09 04:13:53'),
(58, 60, 22, 1583.45, '2026-05-09 19:53:43'),
(59, 61, 73, 1323.20, '2026-05-09 20:04:09'),
(60, 62, 57, 339.40, '2026-05-09 20:23:55'),
(61, 63, 71, 504.00, '2026-05-09 21:17:52'),
(62, 64, 57, 588.60, '2026-05-10 04:00:10'),
(63, 65, 53, 1242.68, '2026-05-10 04:05:22'),
(64, 66, 17, 1104.59, '2026-05-10 04:07:39'),
(65, 67, 71, 1073.40, '2026-05-10 04:16:31'),
(66, 69, 69, 545.50, '2026-05-10 20:02:40'),
(67, 68, 17, 1012.20, '2026-05-10 20:03:51'),
(68, 70, 53, 1128.43, '2026-05-10 20:07:24'),
(69, 71, 11, 756.30, '2026-05-10 20:18:25'),
(70, 72, 22, 919.27, '2026-05-11 03:02:32'),
(71, 73, 70, 902.45, '2026-05-11 03:59:11'),
(72, 74, 73, 799.76, '2026-05-11 04:04:14'),
(73, 75, 29, 366.00, '2026-05-11 04:09:34'),
(84, 86, 22, 1720.90, '2026-05-11 19:58:20'),
(85, 88, 29, 534.00, '2026-05-11 20:09:30'),
(86, 87, 57, 848.90, '2026-05-11 20:12:22'),
(87, 89, 73, 447.90, '2026-05-11 20:17:12'),
(88, 91, 53, 1569.63, '2026-05-12 04:03:16'),
(89, 90, 29, 879.10, '2026-05-12 04:05:22'),
(90, 93, 57, 1821.09, '2026-05-12 04:12:39'),
(91, 92, 71, 1189.68, '2026-05-12 04:38:20'),
(92, 94, 54, 498.15, '2026-05-12 20:04:03'),
(93, 95, 57, 1186.80, '2026-05-12 20:10:44'),
(94, 96, 22, 1286.90, '2026-05-12 20:12:10'),
(95, 97, 73, 769.90, '2026-05-12 20:15:40'),
(97, 100, 53, 1555.45, '2026-05-13 03:59:13'),
(100, 101, 71, 776.40, '2026-05-13 04:20:46'),
(101, 103, 69, 363.20, '2026-05-13 05:46:30'),
(103, 105, 17, 1497.29, '2026-05-13 05:52:35'),
(110, 112, 53, 1094.60, '2026-05-13 19:04:11'),
(111, 113, 22, 1710.70, '2026-05-13 20:01:26'),
(112, 115, 29, 385.30, '2026-05-13 20:05:14'),
(113, 114, 11, 833.40, '2026-05-13 23:51:23'),
(114, 116, 17, 1927.68, '2026-05-14 04:01:48'),
(115, 118, 29, 586.50, '2026-05-14 04:02:30'),
(116, 117, 69, 656.95, '2026-05-14 04:07:16'),
(117, 119, 71, 1051.98, '2026-05-14 04:13:28'),
(118, 120, 29, 577.50, '2026-05-14 20:02:26'),
(119, 121, 13, 609.00, '2026-05-14 20:10:20'),
(120, 122, 17, 580.20, '2026-05-14 20:18:39'),
(121, 123, 73, 1090.50, '2026-05-14 20:20:11'),
(122, 125, 69, 533.10, '2026-05-15 04:01:24'),
(123, 124, 70, 1544.52, '2026-05-15 04:05:30'),
(124, 126, 57, 1214.50, '2026-05-15 04:09:55'),
(125, 127, 71, 513.25, '2026-05-15 04:14:31');

-- --------------------------------------------------------

--
-- Table structure for table `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rol`
--

INSERT INTO `rol` (`id_rol`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'STAFF', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(2, 'ADMIN', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `rol_horario`
--

CREATE TABLE `rol_horario` (
  `id_rol_horario` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `es_opcional` tinyint(1) NOT NULL DEFAULT 0,
  `orden` tinyint(4) NOT NULL DEFAULT 0,
  `color` varchar(7) NOT NULL DEFAULT '#94a3b8'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rol_horario`
--

INSERT INTO `rol_horario` (`id_rol_horario`, `codigo`, `descripcion`, `es_opcional`, `orden`, `color`) VALUES
(1, 'CAJERA', 'Cajera', 0, 2, '#2563eb'),
(2, 'VENDEDORA', 'Vendedora', 0, 1, '#059669'),
(3, 'ALMACENERA', 'Almacenera', 1, 3, '#f59e0b'),
(4, 'LIMPIEZA', 'Limpieza', 1, 4, '#8b5cf6');

-- --------------------------------------------------------

--
-- Table structure for table `semana`
--

CREATE TABLE `semana` (
  `id_semana` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('ABIERTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semana`
--

INSERT INTO `semana` (`id_semana`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_registro`) VALUES
(1, '2026-05-11', '2026-05-17', 'ABIERTA', '2026-05-05 14:14:03'),
(2, '2026-05-18', '2026-05-24', 'ABIERTA', '2026-05-11 08:17:28'),
(3, '2026-05-25', '2026-05-31', 'ABIERTA', '2026-05-11 08:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `sesion_caja`
--

CREATE TABLE `sesion_caja` (
  `id_sesion` int(11) NOT NULL,
  `caja_id` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `postulante_apertura_id` int(11) NOT NULL,
  `postulante_cierre_id` int(11) DEFAULT NULL,
  `postulante_revisor_id` int(11) DEFAULT NULL,
  `estado` enum('ABIERTA','PENDIENTE_VENTA','CERRADA','EN_REVISION','APROBADA','OBSERVADA','RECHAZADA') NOT NULL DEFAULT 'ABIERTA',
  `saldo_inicial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_final_sistema` decimal(10,2) DEFAULT NULL,
  `saldo_final_contado` decimal(10,2) DEFAULT NULL,
  `diferencia_final` decimal(10,2) DEFAULT NULL,
  `margen_permitido` decimal(10,2) NOT NULL DEFAULT 10.00,
  `fecha_apertura` timestamp NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `fecha_operacion` date NOT NULL,
  `fecha_envio_revision` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `observacion_cierre` text DEFAULT NULL,
  `comentario_cajera` text DEFAULT NULL,
  `respuesta_admin` text DEFAULT NULL,
  `observacion_revisor` text DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT 0,
  `requiere_revision` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sesion_caja`
--

INSERT INTO `sesion_caja` (`id_sesion`, `caja_id`, `turno_id`, `postulante_apertura_id`, `postulante_cierre_id`, `postulante_revisor_id`, `estado`, `saldo_inicial`, `saldo_final_sistema`, `saldo_final_contado`, `diferencia_final`, `margen_permitido`, `fecha_apertura`, `fecha_cierre`, `fecha_operacion`, `fecha_envio_revision`, `fecha_revision`, `observacion_cierre`, `comentario_cajera`, `respuesta_admin`, `observacion_revisor`, `motivo_rechazo`, `bloqueado`, `requiere_revision`) VALUES
(25, 2, 1, 29, 29, NULL, 'CERRADA', 0.00, 0.00, 42817.46, 42817.46, 10.00, '2026-05-07 14:36:46', '2026-05-07 14:37:15', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(27, 2, 1, 29, 29, NULL, 'CERRADA', 42817.46, 43360.86, 43362.03, 1.17, 10.00, '2026-05-07 14:41:07', '2026-05-07 14:42:09', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(28, 2, 1, 29, 29, NULL, 'CERRADA', 43362.03, 43560.73, 43561.33, 0.60, 10.00, '2026-05-07 14:45:33', '2026-05-07 20:07:39', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(29, 3, 1, 17, 17, NULL, 'CERRADA', 0.00, 0.00, 27818.14, 27818.14, 10.00, '2026-05-07 14:52:33', '2026-05-07 14:53:04', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(30, 3, 1, 17, 17, NULL, 'CERRADA', 27818.14, 26358.79, 26378.98, 20.19, 10.00, '2026-05-07 14:53:37', '2026-05-07 14:58:03', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(31, 3, 1, 17, 17, NULL, 'CERRADA', 26378.98, 26539.47, 26448.38, -91.09, 10.00, '2026-05-07 14:59:33', '2026-05-07 20:00:17', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(32, 5, 1, 22, 22, NULL, 'CERRADA', 0.00, 0.00, 48905.89, 48905.89, 10.00, '2026-05-07 15:10:33', '2026-05-07 15:11:02', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(33, 5, 1, 22, 22, NULL, 'CERRADA', 48905.89, 51230.88, 51246.73, 15.85, 10.00, '2026-05-07 15:11:55', '2026-05-07 15:14:38', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(35, 5, 1, 22, 22, NULL, 'CERRADA', 51246.73, 41324.07, 41326.95, 2.88, 10.00, '2026-05-07 20:10:22', '2026-05-07 20:16:16', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(37, 4, 1, 1, 1, NULL, 'CERRADA', 0.00, 0.00, 35751.00, 35751.00, 10.00, '2026-05-07 20:20:03', '2026-05-07 20:20:24', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(38, 4, 1, 71, 71, NULL, 'CERRADA', 35751.00, 36605.48, 36625.28, 19.80, 10.00, '2026-05-07 20:21:21', '2026-05-07 20:22:44', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(40, 4, 1, 54, 54, NULL, 'CERRADA', 36625.28, 37201.98, 37183.03, -18.95, 10.00, '2026-05-07 20:25:27', '2026-05-07 20:29:26', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(42, 3, 2, 17, 17, NULL, 'CERRADA', 26538.38, 26750.07, 26712.88, -37.19, 10.00, '2026-05-08 03:56:35', '2026-05-08 04:00:18', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(44, 5, 2, 57, 57, NULL, 'CERRADA', 41326.95, 42750.25, 42742.75, -7.50, 10.00, '2026-05-08 04:13:43', '2026-05-08 04:15:55', '2026-05-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(47, 3, 1, 70, 70, NULL, 'CERRADA', 26712.88, 26906.28, 26905.46, -0.82, 10.00, '2026-05-08 12:26:18', '2026-05-08 20:09:36', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(48, 2, 2, 69, 69, NULL, 'CERRADA', 43561.33, 43800.47, 43812.06, 11.59, 10.00, '2026-05-08 13:37:34', '2026-05-08 13:42:11', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(50, 4, 2, 73, 73, NULL, 'CERRADA', 37183.03, 37668.33, 37669.50, 1.17, 10.00, '2026-05-08 16:06:15', '2026-05-08 16:07:48', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(51, 4, 1, 11, 11, NULL, 'CERRADA', 37669.50, 37996.10, 38002.70, 6.60, 10.00, '2026-05-08 16:10:15', '2026-05-08 20:01:55', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(52, 5, 1, 22, 22, NULL, 'CERRADA', 42742.75, 34023.25, 33900.22, -123.03, 10.00, '2026-05-08 19:30:12', '2026-05-08 19:51:46', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(53, 2, 1, 29, 29, NULL, 'CERRADA', 43812.47, 44056.77, 44052.82, -3.95, 10.00, '2026-05-08 20:07:20', '2026-05-08 20:10:04', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(55, 3, 2, 69, 69, NULL, 'CERRADA', 26905.46, 26106.95, 26118.48, 11.53, 10.00, '2026-05-08 21:21:12', '2026-05-09 04:10:44', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(57, 4, 2, 73, 73, NULL, 'CERRADA', 37996.10, 38667.70, 38687.90, 20.20, 10.00, '2026-05-09 04:08:16', '2026-05-09 04:09:48', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(58, 5, 2, 53, 53, NULL, 'CERRADA', 34020.22, 34852.11, 35356.52, 504.41, 10.00, '2026-05-09 04:10:36', '2026-05-09 04:12:42', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(59, 2, 2, 29, 29, NULL, 'CERRADA', 44052.82, 44438.62, 44419.91, -18.71, 10.00, '2026-05-09 04:12:00', '2026-05-09 04:13:40', '2026-05-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(60, 5, 1, 22, 22, NULL, 'CERRADA', 35356.52, 36939.97, 36961.38, 21.41, 10.00, '2026-05-09 19:51:33', '2026-05-09 19:52:40', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(61, 3, 1, 73, 73, NULL, 'CERRADA', 26118.48, 25867.04, 25846.72, -20.32, 10.00, '2026-05-09 20:01:25', '2026-05-09 20:03:56', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(62, 2, 1, 57, 57, NULL, 'CERRADA', 44419.91, 44669.11, 44761.63, 92.52, 10.00, '2026-05-09 20:19:23', '2026-05-09 20:23:34', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(63, 4, 1, 71, 71, NULL, 'CERRADA', 38687.90, 39003.80, 39000.08, -3.72, 10.00, '2026-05-09 21:13:42', '2026-05-09 21:17:29', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(64, 2, 2, 57, 57, NULL, 'CERRADA', 44761.63, 45241.53, 45154.34, -87.19, 10.00, '2026-05-10 03:56:22', '2026-05-10 03:59:46', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(65, 5, 2, 53, 53, NULL, 'CERRADA', 36961.38, 38127.46, 38131.69, 4.23, 10.00, '2026-05-10 04:01:32', '2026-05-10 04:05:06', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(66, 3, 2, 17, 17, NULL, 'CERRADA', 25846.72, 25558.51, 25568.80, 10.29, 10.00, '2026-05-10 04:04:08', '2026-05-10 04:07:21', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(67, 4, 2, 71, 71, NULL, 'CERRADA', 39000.08, 39658.98, 39659.94, 0.96, 10.00, '2026-05-10 04:12:47', '2026-05-10 04:16:04', '2026-05-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(68, 3, 1, 17, 17, NULL, 'CERRADA', 25568.80, 23442.41, 23447.08, 4.67, 10.00, '2026-05-10 19:16:52', '2026-05-10 20:03:36', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(69, 2, 1, 69, 69, NULL, 'CERRADA', 45154.34, 45533.34, 45536.24, 2.90, 10.00, '2026-05-10 19:46:12', '2026-05-10 20:02:26', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(70, 5, 1, 53, 53, NULL, 'CERRADA', 38131.69, 39067.92, 39069.44, 1.52, 10.00, '2026-05-10 20:03:00', '2026-05-10 20:07:09', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(71, 4, 1, 11, 11, NULL, 'CERRADA', 39657.44, 40050.84, 40070.28, 19.44, 10.00, '2026-05-10 20:14:11', '2026-05-10 20:18:08', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(72, 4, 2, 22, 22, NULL, 'CERRADA', 40070.28, 40815.85, 40790.40, -25.45, 10.00, '2026-05-11 02:59:47', '2026-05-11 03:02:14', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(73, 5, 2, 70, 70, NULL, 'CERRADA', 39069.44, 39971.89, 39968.79, -3.10, 10.00, '2026-05-11 03:14:20', '2026-05-11 03:58:58', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(74, 3, 2, 73, 73, NULL, 'CERRADA', 23447.08, 23633.74, 23639.31, 5.57, 10.00, '2026-05-11 03:57:04', '2026-05-11 04:03:57', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(75, 2, 2, 29, 29, NULL, 'CERRADA', 45536.24, 45829.34, 45831.89, 2.55, 10.00, '2026-05-11 04:07:44', '2026-05-11 04:09:22', '2026-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(86, 5, 1, 22, 22, NULL, 'CERRADA', 39968.79, 41689.69, 55123.34, 13433.65, 10.00, '2026-05-11 19:46:31', '2026-05-11 19:57:33', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(87, 3, 1, 57, 57, NULL, 'CERRADA', 23639.31, 22594.51, 22613.28, 18.77, 10.00, '2026-05-11 19:50:53', '2026-05-11 20:11:59', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(88, 2, 1, 29, 29, NULL, 'CERRADA', 45831.89, 46214.59, 46216.81, 2.22, 10.00, '2026-05-11 20:05:39', '2026-05-11 20:09:03', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(89, 4, 1, 73, 73, NULL, 'CERRADA', 40790.40, 41006.80, 41010.45, 3.65, 10.00, '2026-05-11 20:11:41', '2026-05-11 20:16:55', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(90, 2, 2, 29, 29, NULL, 'CERRADA', 46216.81, 46846.01, 46845.56, -0.45, 10.00, '2026-05-11 20:27:17', '2026-05-12 04:05:01', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(91, 5, 2, 53, 53, NULL, 'CERRADA', 41717.24, 43286.87, 43286.20, -0.67, 10.00, '2026-05-12 04:01:16', '2026-05-12 04:02:39', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(92, 4, 2, 71, 71, NULL, 'CERRADA', 41010.45, 41821.63, 41730.45, -91.18, 10.00, '2026-05-12 04:07:26', '2026-05-12 04:37:43', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(93, 3, 2, 57, 57, NULL, 'CERRADA', 22613.28, 22563.57, 22582.73, 19.16, 10.00, '2026-05-12 04:09:44', '2026-05-12 04:12:17', '2026-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(94, 2, 1, 54, 54, NULL, 'CERRADA', 46845.56, 47291.71, 47308.71, 17.00, 10.00, '2026-05-12 20:00:53', '2026-05-12 20:03:43', '2026-05-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(95, 3, 1, 57, 57, NULL, 'CERRADA', 22582.73, 22696.03, 22734.60, 38.57, 10.00, '2026-05-12 20:07:54', '2026-05-12 20:10:31', '2026-05-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(96, 5, 1, 22, 22, NULL, 'CERRADA', 43286.20, 44573.10, 44574.74, 1.64, 10.00, '2026-05-12 20:09:23', '2026-05-12 20:11:45', '2026-05-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(97, 4, 1, 73, 73, NULL, 'CERRADA', 41730.45, 41813.25, 42119.27, 306.02, 10.00, '2026-05-12 20:11:11', '2026-05-12 20:15:04', '2026-05-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(100, 5, 2, 53, 53, NULL, 'CERRADA', 44574.74, 46093.19, 46117.74, 24.55, 10.00, '2026-05-13 03:57:27', '2026-05-13 03:58:51', '2026-05-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(101, 4, 2, 71, 71, NULL, 'CERRADA', 42119.27, 42663.37, 42726.62, 63.25, 10.00, '2026-05-13 04:10:00', '2026-05-13 04:20:18', '2026-05-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(103, 2, 1, 69, 69, NULL, 'CERRADA', 37308.71, 37500.81, 37405.26, -95.55, 10.00, '2026-05-13 05:44:15', '2026-05-13 05:46:22', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(105, 3, 1, 17, 17, NULL, 'CERRADA', 32734.60, 32762.69, 32755.99, -6.70, 10.00, '2026-05-13 05:51:02', '2026-05-13 05:52:26', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(112, 3, 1, 53, 53, NULL, 'CERRADA', 32755.99, 33038.19, 33048.85, 10.66, 10.00, '2026-05-13 19:01:47', '2026-05-13 19:03:58', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(113, 5, 1, 22, 22, NULL, 'CERRADA', 46117.74, 36828.44, 36817.18, -11.26, 10.00, '2026-05-13 19:58:58', '2026-05-13 20:00:35', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(114, 4, 1, 11, 11, NULL, 'CERRADA', 42726.62, 31252.22, 31216.07, -36.15, 10.00, '2026-05-13 19:59:38', '2026-05-13 23:51:05', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(115, 2, 1, 29, 29, NULL, 'CERRADA', 37405.26, 37671.36, 25072.10, -12599.26, 10.00, '2026-05-13 20:02:43', '2026-05-13 20:04:59', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(116, 3, 2, 17, 17, NULL, 'CERRADA', 33048.85, 32692.23, 32296.55, -395.68, 10.00, '2026-05-14 03:30:22', '2026-05-14 03:58:21', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(117, 5, 2, 69, 69, NULL, 'CERRADA', 36817.18, 37474.13, 36499.62, -974.51, 10.00, '2026-05-14 03:45:33', '2026-05-14 04:06:58', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(118, 2, 2, 29, 29, NULL, 'CERRADA', 37672.10, 38099.70, 38097.72, -1.98, 10.00, '2026-05-14 04:00:03', '2026-05-14 04:02:11', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(119, 4, 2, 71, 71, NULL, 'CERRADA', 31236.07, 31855.55, 31859.99, 4.44, 10.00, '2026-05-14 04:07:44', '2026-05-14 04:13:16', '2026-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(120, 2, 1, 29, 29, NULL, 'CERRADA', 38097.72, 38525.92, 38526.30, 0.38, 10.00, '2026-05-14 19:58:47', '2026-05-14 20:01:04', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(121, 4, 1, 13, 13, NULL, 'CERRADA', 31859.99, 32152.69, 32162.44, 9.75, 10.00, '2026-05-14 20:06:19', '2026-05-14 20:10:06', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(122, 3, 1, 17, 17, NULL, 'CERRADA', 32246.55, 30570.05, 30603.22, 33.17, 10.00, '2026-05-14 20:10:57', '2026-05-14 20:16:48', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(123, 5, 1, 73, 73, NULL, 'CERRADA', 37549.62, 38640.12, 38653.54, 13.42, 10.00, '2026-05-14 20:17:49', '2026-05-14 20:19:44', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(124, 5, 2, 70, 70, NULL, 'CERRADA', 38653.54, 40198.06, 40196.54, -1.52, 10.00, '2026-05-15 03:20:29', '2026-05-15 04:00:51', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(125, 2, 2, 69, 69, NULL, 'CERRADA', 38526.30, 38893.20, 38893.22, 0.02, 10.00, '2026-05-15 03:47:34', '2026-05-15 04:00:53', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(126, 3, 2, 57, 57, NULL, 'CERRADA', 30603.22, 29248.22, 29246.15, -2.07, 10.00, '2026-05-15 04:06:09', '2026-05-15 04:09:34', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0),
(127, 4, 2, 71, 71, NULL, 'CERRADA', 32162.44, 32491.89, 32500.57, 8.68, 10.00, '2026-05-15 04:12:31', '2026-05-15 04:14:16', '2026-05-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sesion_participante`
--

CREATE TABLE `sesion_participante` (
  `id_sesion_participante` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `rol_participacion` enum('CAJERA','VENDEDORA','SUPERVISORA') NOT NULL,
  `responsable_faltante` tinyint(1) NOT NULL DEFAULT 0,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sesion_participante`
--

INSERT INTO `sesion_participante` (`id_sesion_participante`, `sesion_id`, `postulante_id`, `rol_participacion`, `responsable_faltante`, `observacion`) VALUES
(40, 25, 29, 'CAJERA', 1, NULL),
(41, 25, 56, 'VENDEDORA', 0, NULL),
(44, 27, 29, 'CAJERA', 1, NULL),
(45, 27, 56, 'VENDEDORA', 0, NULL),
(46, 28, 29, 'CAJERA', 1, NULL),
(47, 28, 56, 'VENDEDORA', 0, NULL),
(48, 29, 17, 'CAJERA', 1, NULL),
(49, 29, 10, 'VENDEDORA', 0, NULL),
(50, 30, 17, 'CAJERA', 1, NULL),
(51, 30, 10, 'VENDEDORA', 0, NULL),
(52, 31, 17, 'CAJERA', 1, NULL),
(53, 31, 13, 'VENDEDORA', 0, NULL),
(54, 32, 22, 'CAJERA', 1, NULL),
(55, 32, 4, 'VENDEDORA', 0, NULL),
(56, 33, 22, 'CAJERA', 1, NULL),
(57, 33, 4, 'VENDEDORA', 0, NULL),
(60, 35, 22, 'CAJERA', 1, NULL),
(61, 35, 4, 'VENDEDORA', 0, NULL),
(64, 37, 1, 'CAJERA', 1, NULL),
(65, 37, 11, 'VENDEDORA', 0, NULL),
(66, 38, 71, 'CAJERA', 1, NULL),
(67, 38, 11, 'VENDEDORA', 0, NULL),
(70, 40, 54, 'CAJERA', 1, NULL),
(71, 40, 11, 'VENDEDORA', 0, NULL),
(74, 42, 17, 'CAJERA', 1, NULL),
(75, 42, 10, 'VENDEDORA', 0, NULL),
(78, 44, 57, 'CAJERA', 1, NULL),
(79, 44, 4, 'VENDEDORA', 0, NULL),
(84, 47, 70, 'CAJERA', 1, NULL),
(85, 47, 54, 'VENDEDORA', 0, NULL),
(86, 48, 69, 'CAJERA', 1, NULL),
(87, 48, 56, 'VENDEDORA', 0, NULL),
(90, 50, 73, 'CAJERA', 1, NULL),
(91, 50, 55, 'VENDEDORA', 0, NULL),
(92, 51, 11, 'CAJERA', 1, NULL),
(93, 51, 13, 'VENDEDORA', 0, NULL),
(94, 52, 22, 'CAJERA', 1, NULL),
(95, 52, 4, 'VENDEDORA', 0, NULL),
(96, 53, 29, 'CAJERA', 1, NULL),
(97, 53, 45, 'VENDEDORA', 0, NULL),
(100, 55, 69, 'CAJERA', 1, NULL),
(101, 55, 10, 'VENDEDORA', 0, NULL),
(104, 57, 73, 'CAJERA', 1, NULL),
(105, 57, 11, 'VENDEDORA', 0, NULL),
(106, 58, 53, 'CAJERA', 1, NULL),
(107, 58, 54, 'VENDEDORA', 0, NULL),
(108, 59, 29, 'CAJERA', 1, NULL),
(109, 59, 71, 'VENDEDORA', 0, NULL),
(110, 60, 22, 'CAJERA', 1, NULL),
(111, 60, 45, 'VENDEDORA', 0, NULL),
(112, 61, 73, 'CAJERA', 1, NULL),
(113, 61, 10, 'VENDEDORA', 0, NULL),
(114, 62, 57, 'CAJERA', 1, NULL),
(115, 62, 13, 'VENDEDORA', 0, NULL),
(116, 63, 71, 'CAJERA', 1, NULL),
(117, 63, 55, 'VENDEDORA', 0, NULL),
(118, 64, 57, 'CAJERA', 1, NULL),
(119, 64, 56, 'VENDEDORA', 0, NULL),
(120, 65, 53, 'CAJERA', 1, NULL),
(121, 65, 4, 'VENDEDORA', 0, NULL),
(122, 66, 17, 'CAJERA', 1, NULL),
(123, 66, 54, 'VENDEDORA', 0, NULL),
(124, 67, 71, 'CAJERA', 1, NULL),
(125, 67, 11, 'VENDEDORA', 0, NULL),
(126, 68, 17, 'CAJERA', 1, NULL),
(127, 68, 13, 'VENDEDORA', 0, NULL),
(128, 69, 69, 'CAJERA', 1, NULL),
(129, 69, 56, 'VENDEDORA', 0, NULL),
(130, 70, 53, 'CAJERA', 1, NULL),
(131, 70, 54, 'VENDEDORA', 0, NULL),
(132, 71, 11, 'CAJERA', 1, NULL),
(133, 71, 55, 'VENDEDORA', 0, NULL),
(134, 72, 22, 'CAJERA', 1, NULL),
(135, 72, 71, 'VENDEDORA', 0, NULL),
(136, 73, 70, 'CAJERA', 1, NULL),
(137, 73, 54, 'VENDEDORA', 0, NULL),
(138, 74, 73, 'CAJERA', 1, NULL),
(139, 74, 45, 'VENDEDORA', 0, NULL),
(140, 75, 29, 'CAJERA', 1, NULL),
(141, 75, 56, 'VENDEDORA', 0, NULL),
(161, 86, 22, 'CAJERA', 1, NULL),
(162, 86, 4, 'VENDEDORA', 0, NULL),
(163, 87, 57, 'CAJERA', 1, NULL),
(164, 87, 45, 'VENDEDORA', 0, NULL),
(165, 88, 29, 'CAJERA', 1, NULL),
(166, 88, 13, 'VENDEDORA', 0, NULL),
(167, 89, 73, 'CAJERA', 1, NULL),
(168, 89, 55, 'VENDEDORA', 0, NULL),
(169, 90, 29, 'CAJERA', 1, NULL),
(170, 90, 56, 'VENDEDORA', 0, NULL),
(171, 91, 53, 'CAJERA', 1, NULL),
(172, 91, 54, 'VENDEDORA', 0, NULL),
(173, 92, 71, 'CAJERA', 1, NULL),
(174, 92, 11, 'VENDEDORA', 0, NULL),
(175, 93, 57, 'CAJERA', 1, NULL),
(176, 93, 10, 'VENDEDORA', 0, NULL),
(177, 94, 54, 'CAJERA', 1, NULL),
(178, 94, 45, 'VENDEDORA', 0, NULL),
(179, 95, 57, 'CAJERA', 1, NULL),
(180, 95, 10, 'VENDEDORA', 0, NULL),
(181, 96, 22, 'CAJERA', 1, NULL),
(182, 96, 4, 'VENDEDORA', 0, NULL),
(183, 97, 73, 'CAJERA', 1, NULL),
(184, 97, 55, 'VENDEDORA', 0, NULL),
(188, 100, 53, 'CAJERA', 1, NULL),
(189, 100, 4, 'VENDEDORA', 0, NULL),
(190, 101, 71, 'CAJERA', 1, NULL),
(191, 101, 11, 'VENDEDORA', 0, NULL),
(194, 103, 69, 'CAJERA', 1, NULL),
(195, 103, 56, 'VENDEDORA', 0, NULL),
(198, 105, 17, 'CAJERA', 1, NULL),
(199, 105, 10, 'VENDEDORA', 0, NULL),
(212, 112, 53, 'CAJERA', 1, NULL),
(213, 112, 10, 'VENDEDORA', 0, NULL),
(214, 113, 22, 'CAJERA', 1, NULL),
(215, 113, 4, 'VENDEDORA', 0, NULL),
(216, 114, 11, 'CAJERA', 1, NULL),
(217, 114, 55, 'VENDEDORA', 0, NULL),
(218, 115, 29, 'CAJERA', 1, NULL),
(219, 115, 13, 'VENDEDORA', 0, NULL),
(220, 116, 17, 'CAJERA', 1, NULL),
(221, 116, 10, 'VENDEDORA', 0, NULL),
(222, 117, 69, 'CAJERA', 1, NULL),
(223, 117, 70, 'VENDEDORA', 0, NULL),
(224, 118, 29, 'CAJERA', 1, NULL),
(225, 118, 56, 'VENDEDORA', 0, NULL),
(226, 119, 71, 'CAJERA', 1, NULL),
(227, 119, 11, 'VENDEDORA', 0, NULL),
(228, 120, 29, 'CAJERA', 1, NULL),
(229, 120, 56, 'VENDEDORA', 0, NULL),
(230, 121, 13, 'CAJERA', 1, NULL),
(231, 121, 11, 'VENDEDORA', 0, NULL),
(232, 122, 17, 'CAJERA', 1, NULL),
(233, 122, 54, 'VENDEDORA', 0, NULL),
(234, 123, 73, 'CAJERA', 1, NULL),
(235, 123, 4, 'VENDEDORA', 0, NULL),
(236, 124, 70, 'CAJERA', 1, NULL),
(237, 124, 4, 'VENDEDORA', 0, NULL),
(238, 125, 69, 'CAJERA', 1, NULL),
(239, 125, 56, 'VENDEDORA', 0, NULL),
(240, 126, 57, 'CAJERA', 1, NULL),
(241, 126, 54, 'VENDEDORA', 0, NULL),
(242, 127, 71, 'CAJERA', 1, NULL),
(243, 127, 55, 'VENDEDORA', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `situacion_vivienda`
--

CREATE TABLE `situacion_vivienda` (
  `id_situacion` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `situacion_vivienda`
--

INSERT INTO `situacion_vivienda` (`id_situacion`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Alquilada', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'Familiar', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Propia', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `id_skill` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skill`
--

INSERT INTO `skill` (`id_skill`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'AgenteBCP', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'BPA', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'BPD', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(4, 'BPOF', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(5, 'Caja', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(6, 'Excel', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(7, 'Inyectables', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `solicitud_cambio`
--

CREATE TABLE `solicitud_cambio` (
  `id_solicitud` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `semana_id` int(11) NOT NULL,
  `tipo` enum('COBERTURA','CAMBIO') NOT NULL,
  `postulante_solicitante_id` int(11) NOT NULL,
  `postulante_original_id` int(11) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `notas` varchar(300) DEFAULT NULL,
  `estado` enum('ACTIVA','REVERTIDA') NOT NULL DEFAULT 'ACTIVA',
  `revertida_por` int(11) DEFAULT NULL,
  `fecha_reversion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `solicitud_cambio`
--

INSERT INTO `solicitud_cambio` (`id_solicitud`, `slot_id`, `semana_id`, `tipo`, `postulante_solicitante_id`, `postulante_original_id`, `fecha_solicitud`, `notas`, `estado`, `revertida_por`, `fecha_reversion`) VALUES
(1, 3, 1, 'CAMBIO', 68, NULL, '2026-05-11 03:28:08', NULL, 'ACTIVA', NULL, NULL),
(2, 3, 1, 'COBERTURA', 1, 68, '2026-05-11 03:28:39', NULL, 'REVERTIDA', 1, '2026-05-11 03:49:09'),
(3, 3, 1, 'CAMBIO', 1, 68, '2026-05-11 03:52:56', 'Eliminado del horario por administrador', 'ACTIVA', NULL, NULL),
(4, 3, 1, 'CAMBIO', 68, NULL, '2026-05-11 04:39:12', NULL, 'ACTIVA', NULL, NULL),
(5, 3, 1, 'CAMBIO', 1, 68, '2026-05-11 04:51:27', 'Eliminado del horario por administrador', 'ACTIVA', NULL, NULL),
(6, 56, 1, 'COBERTURA', 13, 29, '2026-05-13 12:50:34', 'no vino es vaga', 'REVERTIDA', 1, '2026-05-13 12:51:32');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_egreso`
--

CREATE TABLE `tipo_egreso` (
  `id_tipo_egreso` tinyint(3) UNSIGNED NOT NULL,
  `etiqueta` varchar(60) NOT NULL,
  `modo_ref` enum('PERSONAL','CONCEPTO','LIBRE','LOCAL','FACTURA','DEPOSITO') NOT NULL DEFAULT 'LIBRE',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_egreso`
--

INSERT INTO `tipo_egreso` (`id_tipo_egreso`, `etiqueta`, `modo_ref`, `activo`, `orden`) VALUES
(1, 'Pago de Personal', 'PERSONAL', 1, 1),
(2, 'Pago de Local', 'LOCAL', 1, 2),
(3, 'Pago de Compras', 'FACTURA', 1, 3),
(4, 'Otros pagos', 'LIBRE', 1, 5),
(5, 'Depósito a KGyR', 'DEPOSITO', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_estudio`
--

CREATE TABLE `tipo_estudio` (
  `id_tipo` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_estudio`
--

INSERT INTO `tipo_estudio` (`id_tipo`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Secundaria Completa', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(2, 'Técnico', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11'),
(3, 'Universitario', 1, '2026-05-04 20:58:11', '2026-05-04 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_movimiento`
--

CREATE TABLE `tipo_movimiento` (
  `id_tipo_movimiento` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_movimiento`
--

INSERT INTO `tipo_movimiento` (`id_tipo_movimiento`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'INGRESO', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(2, 'EGRESO', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_personal`
--

CREATE TABLE `tipo_personal` (
  `codigo` varchar(4) NOT NULL,
  `descripcion` varchar(120) NOT NULL,
  `rango` varchar(80) NOT NULL,
  `orden` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_personal`
--

INSERT INTO `tipo_personal` (`codigo`, `descripcion`, `rango`, `orden`) VALUES
('A1', 'Ventas - A1', 'Mayor a 70 operaciones', 1),
('B1', 'Ventas - B1', 'Entre 50 y 70 operaciones', 2),
('C1', 'Ventas - C1', 'Entre 40 y 50 operaciones', 3),
('D1', 'Ventas - D1', 'Menor a 40 operaciones', 4),
('X1', 'Caja - A1', 'Mayor a 200 operaciones', 5),
('Y1', 'Caja - B1', 'Hasta 200 operaciones', 6),
('Z1', 'Caja - C1', 'Hasta 150 operaciones', 7);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_rectificacion`
--

CREATE TABLE `tipo_rectificacion` (
  `id_tipo_rect` tinyint(3) UNSIGNED NOT NULL,
  `etiqueta` varchar(60) NOT NULL,
  `signo` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = suma, -1 = resta',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_rectificacion`
--

INSERT INTO `tipo_rectificacion` (`id_tipo_rect`, `etiqueta`, `signo`, `activo`, `orden`) VALUES
(1, 'Efectivo encontrado', 1, 1, 1),
(2, 'Devolución de efectivo', -1, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `transferencia_caja`
--

CREATE TABLE `transferencia_caja` (
  `id_transferencia` int(11) NOT NULL,
  `sesion_origen_id` int(11) NOT NULL,
  `sesion_destino_id` int(11) DEFAULT NULL,
  `caja_origen_id` int(11) NOT NULL,
  `caja_destino_id` int(11) NOT NULL,
  `postulante_envia_id` int(11) NOT NULL,
  `postulante_recibe_id` int(11) DEFAULT NULL,
  `postulante_revisa_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_operacion` varchar(100) DEFAULT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_confirmacion_recepcion` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE_ENVIO','ENVIADO','RECIBIDO','OBSERVADO','RECHAZADO','APROBADO') DEFAULT 'PENDIENTE_ENVIO',
  `observacion_recepcion` text DEFAULT NULL,
  `observacion_revision` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transferencia_saldo`
--

CREATE TABLE `transferencia_saldo` (
  `id` int(11) NOT NULL,
  `caja_origen_id` int(11) NOT NULL,
  `caja_destino_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `solicitante_id` int(11) NOT NULL,
  `confirmador_id` int(11) DEFAULT NULL,
  `anulador_id` int(11) DEFAULT NULL,
  `estado` enum('PENDIENTE','CONFIRMADA','ANULADA') NOT NULL DEFAULT 'PENDIENTE',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` datetime DEFAULT NULL,
  `anulada_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transferencia_saldo`
--

INSERT INTO `transferencia_saldo` (`id`, `caja_origen_id`, `caja_destino_id`, `monto`, `numero_comprobante`, `notas`, `solicitante_id`, `confirmador_id`, `anulador_id`, `estado`, `created_at`, `confirmed_at`, `anulada_at`) VALUES
(1, 2, 3, 10000.00, 'nose-0001', 'falta saldo', 1, 1, NULL, 'CONFIRMADA', '2026-05-13 00:39:03', '2026-05-13 00:42:37', NULL),
(2, 2, 3, 5000.00, NULL, 'check', 1, NULL, 1, 'ANULADA', '2026-05-13 01:00:37', NULL, '2026-05-13 01:00:53');

-- --------------------------------------------------------

--
-- Table structure for table `turno`
--

CREATE TABLE `turno` (
  `id_turno` int(11) NOT NULL,
  `descripcion` varchar(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `turno`
--

INSERT INTO `turno` (`id_turno`, `descripcion`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Mañana', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(2, 'Tarde', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `postulante_id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`postulante_id`, `rol_id`, `username`, `password`, `activo`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 2, 'GIANCARLOVC', '$2y$10$WdDI/yNovOtkl6.1P0fFVeCn5JDc2bwm0wOmcRGg/SDNYZf.oTJC.', 1, '2026-05-04 20:58:12', '2026-05-06 08:38:31'),
(2, 1, 'SOLANGECC', '$2y$10$hJUz5FU.fpTe3fg0ofQypOeTx7jlG1CDQf06F0ZHIQIBzjCbeWQ6y', 0, '2026-05-04 21:06:58', '2026-05-06 02:26:31'),
(4, 1, 'DARIANABC', '$2y$10$O5oR0esntN3OzN9Sa7TkB.jrB3MAuj/pukrAw.CMvhAaCbYCKksXC', 1, '2026-05-06 00:30:16', '2026-05-06 00:43:17'),
(5, 1, 'PATRICIAOP', '$2y$10$MRol58reXroFEKA9KPB2p.rXS3J1zQbZe6f1EPWFQ.tksghQ2DgFW', 1, '2026-05-06 00:44:13', '2026-05-06 00:44:29'),
(10, 1, 'KARENME', '$2y$10$7SA6hAGU62Eql9SP/w4pC.IngAObLwBa89NToGx9J9jtEFX827JXC', 1, '2026-05-06 00:57:18', '2026-05-06 00:58:38'),
(11, 1, 'FIORELLACR', '$2y$10$bzD/L/E00OiLUyqTURSv2ObNZzULqUmT6FxSayFYw4ht1D/4neeom', 1, '2026-05-05 00:57:08', '2026-05-05 20:35:17'),
(12, 1, 'SHARIKRP', '$2y$10$G95U/AdGGjGwTbyqkxC/buxvWKBketkYIgNXXegDih2zkCWFpGb7a', 0, '2026-05-06 01:07:45', '2026-05-06 01:53:47'),
(13, 1, 'MONICAQC', '$2y$10$RQVUnjY3Ka2GxIb/SipEMuaYAxU57O9a3mz8G57kPTKWVvBNFnsx6', 1, '2026-05-06 01:10:08', '2026-05-06 01:10:27'),
(17, 1, 'GERALDINNEQA', '$2y$10$yfbQNjv.BniXcr8xfw3iW.3i2l70PsZw3llYaYfe2edychBAW4xMm', 1, '2026-05-06 01:12:37', '2026-05-06 01:29:17'),
(19, 1, 'ELIZABETHFS', '$2y$10$5QoLrFKDQOIDeh4sqfIhSO73pb/VQH7z3/1k.SzqgyZ7j.gRq.PTu', 1, '2026-05-06 01:31:08', '2026-05-06 01:31:31'),
(22, 1, 'YOLVIPF', '$2y$10$a2s8Uhv2axrDDpxcOOeiRucVAT9xbMGRRqMcpZcT2asHNvZQiSDbi', 1, '2026-05-06 01:33:59', '2026-05-06 01:34:35'),
(29, 1, 'YENIFERQL', '$2y$10$rQpl7cOqBT7upb8lLdMC6ebX24QjbYV.FOKt0dnUoUh.Gj7tmvMxC', 1, '2026-05-06 01:36:12', '2026-05-06 01:36:32'),
(45, 1, 'ERIKAGH', '$2y$10$dzmWBJxPulc4NgUmkzag4eTMrWcFfBooZP0yNhc6apw9NOrzBc3ku', 1, '2026-05-06 01:39:11', '2026-05-06 01:39:35'),
(51, 1, 'DAYANABA', '$2y$10$2Kmz.vEOxw76NpjU26RyZ.ghm72IM0LxOoRQlM2yQwPT2WoaHKA0O', 1, '2026-05-06 01:41:25', '2026-05-06 01:41:49'),
(52, 1, 'MERLINDABC', '$2y$10$vr8JtZQCE2S5N9/woHtnyejlX6rwMRHThOAXHghq5O6cMFAwds1Fm', 0, '2026-05-06 01:43:04', '2026-05-06 01:54:27'),
(53, 1, 'LUCIAAC', '$2y$10$bh8zNb.84KaUwFG8bU9BTeKzFyOT7XIdA2fojxHRQmt5veyjDhwH.', 1, '2026-05-06 01:45:57', '2026-05-06 01:46:14'),
(54, 1, 'YOVALYDR', '$2y$10$4YdzBAsfOywcKzMl0qWHgO/89kCuDyYOGLzf4Jtafa3Km6fft7MvO', 1, '2026-05-06 01:49:07', '2026-05-06 01:49:27'),
(55, 1, 'ANAHIMC', '$2y$10$C4F3jVpvZXCNvcU4ZeF6o.FHYiwJMAUVzZBHdwXqzamyEcZFQNZae', 1, '2026-05-06 16:31:29', '2026-05-06 16:32:21'),
(56, 1, 'MARIAYC', '$2y$10$.oebRo1ph130j2f9ZIlUX.YYPUkCblZSYglkAgxyApLhhgR4C2HrO', 1, '2026-05-06 15:23:21', '2026-05-06 15:24:05'),
(57, 1, 'ROSATC', '$2y$10$8YBlVmWIrF9dbJTGpb0xdOS01wS3Mig65HWNCU6mjB/QglmMnDt3y', 1, '2026-05-06 15:26:19', '2026-05-06 15:27:21'),
(59, 1, 'SANDRARQ', '$2y$10$GI0Lijzd8dDv91YrABcJH.ivLU/5RGfb6Kmrhf64dsridwZH0vTlm', 1, '2026-05-06 01:50:13', '2026-05-06 01:50:29'),
(60, 1, 'ESWINSR', '$2y$10$C1q500vh5PJieW7XoMlAq.xV6MC8tRSaW4WU.qoI3DQ717XY6JdK6', 1, '2026-05-06 01:51:04', '2026-05-06 01:51:24'),
(61, 1, 'YADIRABN', '$2y$10$BREj.ELgFAihrq3rrj/BQOelgegmoqihYPrtxkTZuWakdkF3GisDC', 1, '2026-05-06 01:52:12', '2026-05-06 01:52:28'),
(68, 1, 'BLOQUEADO', '$2y$10$fSEmYth8OikhKLOib6bEAeC1A8shRZOQRJ6inf6Mtjo9XU03KQWZe', 1, '2026-05-06 17:07:03', '2026-05-06 17:10:31'),
(69, 1, 'FLORHY', '$2y$10$ZwbpuSQZszRof2zf8t5qcO2U68utcFC6jxS5RTZuyB/iSbPcylkAa', 1, '2026-05-06 18:09:14', '2026-05-06 18:09:56'),
(70, 1, 'DEBORAPA', '$2y$10$xfu7TGxUqYXVwVehXnyuDOnnumjeg9BHZhxxDgHmHAfU7Kgt2xMs.', 1, '2026-05-06 21:01:14', '2026-05-06 21:02:08'),
(71, 1, 'ESTHERFH', '$2y$10$Fvwu0dlWNpdMcOSg4Q8B6uLuE9.4/rm8GixY3Drzy0wABhI5IYfre', 1, '2026-05-06 21:03:37', '2026-05-06 21:04:06'),
(72, 2, 'MARINA', '$2y$10$y6Mn3lCSTyeaDda2r0OOLuhnCW/AKpdmrsIs4Ad4Vyo6Q0eZ2cktm', 1, '2026-05-06 23:34:30', '2026-05-06 23:35:23'),
(73, 1, 'VICTORIAHA', '$2y$10$pRLXci0rYu3eL4KXYWFdyuX0mKcug1HjNfsSNCAFG4tWG7wuIvhg2', 1, '2026-05-07 02:06:31', '2026-05-07 02:06:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ajuste_esperado`
--
ALTER TABLE `ajuste_esperado`
  ADD PRIMARY KEY (`id_ajuste`),
  ADD KEY `sesion_id` (`sesion_id`);

--
-- Indexes for table `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `idx_asistencia_fecha` (`fecha`),
  ADD KEY `idx_asistencia_local` (`local_id`),
  ADD KEY `idx_asistencia_postulante` (`postulante_id`);

--
-- Indexes for table `asistencia_checklist`
--
ALTER TABLE `asistencia_checklist`
  ADD PRIMARY KEY (`id_asistencia_checklist`),
  ADD KEY `idx_ac_checklist` (`checklist_id`),
  ADD KEY `idx_ac_asistencia` (`asistencia_id`);

--
-- Indexes for table `auditoria_cuadre`
--
ALTER TABLE `auditoria_cuadre`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `idx_aud_cuadre_sesion` (`sesion_id`),
  ADD KEY `idx_aud_cuadre_postulante` (`postulante_id`);

--
-- Indexes for table `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `idx_as_postulante` (`postulante_id`);

--
-- Indexes for table `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`id_caja`),
  ADD UNIQUE KEY `uq_caja_local_desc` (`local_id`,`descripcion`);

--
-- Indexes for table `checklist`
--
ALTER TABLE `checklist`
  ADD PRIMARY KEY (`id_checklist`);

--
-- Indexes for table `concepto_gastos_local`
--
ALTER TABLE `concepto_gastos_local`
  ADD PRIMARY KEY (`id_concepto`);

--
-- Indexes for table `concepto_penalidad`
--
ALTER TABLE `concepto_penalidad`
  ADD PRIMARY KEY (`id_concepto`);

--
-- Indexes for table `contacto_emergencia`
--
ALTER TABLE `contacto_emergencia`
  ADD PRIMARY KEY (`id_contacto_emergencia`),
  ADD KEY `idx_ce_postulante` (`postulante_id`);

--
-- Indexes for table `correccion_venta`
--
ALTER TABLE `correccion_venta`
  ADD PRIMARY KEY (`id_correccion`),
  ADD KEY `sesion_id` (`sesion_id`);

--
-- Indexes for table `detalle_cuadre`
--
ALTER TABLE `detalle_cuadre`
  ADD PRIMARY KEY (`sesion_id`);

--
-- Indexes for table `especialidad`
--
ALTER TABLE `especialidad`
  ADD PRIMARY KEY (`id_especialidad`),
  ADD UNIQUE KEY `uq_especialidad_desc` (`descripcion`);

--
-- Indexes for table `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`),
  ADD UNIQUE KEY `uq_estado_desc` (`descripcion`);

--
-- Indexes for table `estudio`
--
ALTER TABLE `estudio`
  ADD PRIMARY KEY (`id_estudio`),
  ADD UNIQUE KEY `uq_estudio` (`postulante_id`,`tipo_id`,`institucion_id`,`fecha_inicio`),
  ADD KEY `idx_estudio_tipo` (`tipo_id`),
  ADD KEY `idx_estudio_estado` (`estado_id`),
  ADD KEY `idx_estudio_institucion` (`institucion_id`);

--
-- Indexes for table `etapa`
--
ALTER TABLE `etapa`
  ADD PRIMARY KEY (`id_etapa`),
  ADD UNIQUE KEY `uq_etapa_desc` (`descripcion`);

--
-- Indexes for table `experiencia`
--
ALTER TABLE `experiencia`
  ADD PRIMARY KEY (`id_experiencia`),
  ADD KEY `idx_exp_postulante` (`postulante_id`);

--
-- Indexes for table `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`id_genero`),
  ADD UNIQUE KEY `uq_genero_desc` (`descripcion`);

--
-- Indexes for table `horario_slot`
--
ALTER TABLE `horario_slot`
  ADD PRIMARY KEY (`id_slot`),
  ADD UNIQUE KEY `uq_hslot` (`semana_id`,`local_id`,`turno_id`,`fecha_dia`,`rol_horario_id`,`slot_num`),
  ADD KEY `fk_hslot_local` (`local_id`),
  ADD KEY `fk_hslot_turno` (`turno_id`),
  ADD KEY `fk_hslot_postulante` (`postulante_id`),
  ADD KEY `fk_hs_rol` (`rol_horario_id`);

--
-- Indexes for table `horario_solicitud`
--
ALTER TABLE `horario_solicitud`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD UNIQUE KEY `uq_horario_semana_trabajador` (`semana_id`,`postulante_id`),
  ADD KEY `idx_hs_postulante` (`postulante_id`),
  ADD KEY `idx_hs_local` (`local_id`),
  ADD KEY `idx_hs_turno` (`turno_id`),
  ADD KEY `idx_hs_estado` (`estado`),
  ADD KEY `fk_hs_revisado_por` (`revisado_por_id`);

--
-- Indexes for table `incidencia`
--
ALTER TABLE `incidencia`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `idx_inc_postulante` (`postulante_id`),
  ADD KEY `idx_inc_sesion` (`sesion_id`);

--
-- Indexes for table `institucion`
--
ALTER TABLE `institucion`
  ADD PRIMARY KEY (`id_institucion`),
  ADD UNIQUE KEY `uq_institucion_desc` (`descripcion`);

--
-- Indexes for table `local`
--
ALTER TABLE `local`
  ADD PRIMARY KEY (`id_local`),
  ADD KEY `idx_local_encargado` (`id_encargado`);

--
-- Indexes for table `modo`
--
ALTER TABLE `modo`
  ADD PRIMARY KEY (`id_modo`),
  ADD UNIQUE KEY `uq_modo_desc` (`descripcion`);

--
-- Indexes for table `movimiento_sesion`
--
ALTER TABLE `movimiento_sesion`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_mov_sesion` (`sesion_id`),
  ADD KEY `idx_mov_tipo` (`tipo_movimiento_id`),
  ADD KEY `idx_mov_registro` (`postulante_registro_id`),
  ADD KEY `idx_mov_revision` (`postulante_revision_id`),
  ADD KEY `idx_mov_modo` (`modo_id`),
  ADD KEY `idx_mov_fecha` (`fecha_movimiento`),
  ADD KEY `idx_mov_estado` (`estado`);

--
-- Indexes for table `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id_nivel`),
  ADD UNIQUE KEY `uq_nivel_desc` (`descripcion`);

--
-- Indexes for table `pago_deposito`
--
ALTER TABLE `pago_deposito`
  ADD PRIMARY KEY (`id_pago_deposito`),
  ADD KEY `sesion_id` (`sesion_id`);

--
-- Indexes for table `pago_factura`
--
ALTER TABLE `pago_factura`
  ADD PRIMARY KEY (`id_pago_factura`),
  ADD KEY `sesion_id` (`sesion_id`);

--
-- Indexes for table `pago_local`
--
ALTER TABLE `pago_local`
  ADD PRIMARY KEY (`id_pago_local`),
  ADD KEY `idx_pl_sesion` (`sesion_id`),
  ADD KEY `idx_pl_local` (`local_id`),
  ADD KEY `idx_pl_emisor` (`postulante_emisor_id`),
  ADD KEY `idx_pl_concepto` (`concepto_id`);

--
-- Indexes for table `pago_personal`
--
ALTER TABLE `pago_personal`
  ADD PRIMARY KEY (`id_pago_personal`),
  ADD KEY `idx_pp_sesion` (`sesion_id`),
  ADD KEY `idx_pp_emisor` (`postulante_emisor_id`),
  ADD KEY `idx_pp_beneficiario` (`postulante_beneficiario_id`),
  ADD KEY `idx_pp_revisor` (`postulante_revisor_id`),
  ADD KEY `idx_pp_estado` (`estado`);

--
-- Indexes for table `plantilla_horario`
--
ALTER TABLE `plantilla_horario`
  ADD PRIMARY KEY (`id_plantilla`),
  ADD UNIQUE KEY `uq_plantilla` (`local_id`,`turno_id`,`rol_horario_id`),
  ADD KEY `turno_id` (`turno_id`),
  ADD KEY `rol_horario_id` (`rol_horario_id`);

--
-- Indexes for table `postulacion`
--
ALTER TABLE `postulacion`
  ADD PRIMARY KEY (`id_postulacion`),
  ADD UNIQUE KEY `uq_postulante_puesto` (`postulante_id`,`puesto_id`),
  ADD KEY `idx_postulacion_etapa` (`etapa_id`),
  ADD KEY `idx_postulacion_visto` (`visto`),
  ADD KEY `idx_postulacion_fecha` (`fecha_postulacion`),
  ADD KEY `fk_postulacion_puesto` (`puesto_id`);

--
-- Indexes for table `postulante`
--
ALTER TABLE `postulante`
  ADD PRIMARY KEY (`id_postulante`),
  ADD UNIQUE KEY `uq_postulante_dni` (`num_documento`),
  ADD KEY `idx_postulante_genero` (`genero_id`),
  ADD KEY `idx_postulante_vivienda` (`situacion_vivienda_id`);

--
-- Indexes for table `postulante_especialidad`
--
ALTER TABLE `postulante_especialidad`
  ADD PRIMARY KEY (`postulante_id`,`especialidad_id`),
  ADD KEY `idx_pe_especialidad` (`especialidad_id`);

--
-- Indexes for table `postulante_skill`
--
ALTER TABLE `postulante_skill`
  ADD PRIMARY KEY (`postulante_id`,`skill_id`),
  ADD KEY `idx_ps_skill` (`skill_id`),
  ADD KEY `idx_ps_nivel` (`nivel_id`);

--
-- Indexes for table `preferencias`
--
ALTER TABLE `preferencias`
  ADD PRIMARY KEY (`turno_id`,`postulante_id`),
  ADD KEY `idx_pref_postulante` (`postulante_id`);

--
-- Indexes for table `puesto`
--
ALTER TABLE `puesto`
  ADD PRIMARY KEY (`id_puesto`),
  ADD UNIQUE KEY `uq_puesto_desc` (`descripcion`);

--
-- Indexes for table `rectificacion_cuadre`
--
ALTER TABLE `rectificacion_cuadre`
  ADD PRIMARY KEY (`id_rectificacion`),
  ADD KEY `idx_rect_sesion` (`sesion_id`),
  ADD KEY `idx_rect_registra` (`postulante_registra_id`),
  ADD KEY `idx_rect_responsable` (`postulante_responsable_id`),
  ADD KEY `idx_rect_revisa` (`postulante_revisa_id`),
  ADD KEY `idx_rect_modo` (`modo_id`),
  ADD KEY `idx_rect_estado` (`estado`);

--
-- Indexes for table `reporte_venta`
--
ALTER TABLE `reporte_venta`
  ADD PRIMARY KEY (`id_reporte_venta`),
  ADD KEY `idx_rv_sesion` (`sesion_id`),
  ADD KEY `idx_rv_vendedor` (`postulante_vendedor_id`);

--
-- Indexes for table `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uq_rol_desc` (`descripcion`);

--
-- Indexes for table `rol_horario`
--
ALTER TABLE `rol_horario`
  ADD PRIMARY KEY (`id_rol_horario`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `semana`
--
ALTER TABLE `semana`
  ADD PRIMARY KEY (`id_semana`),
  ADD UNIQUE KEY `uq_semana_inicio` (`fecha_inicio`);

--
-- Indexes for table `sesion_caja`
--
ALTER TABLE `sesion_caja`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `idx_sesion_caja` (`caja_id`),
  ADD KEY `idx_sesion_apertura` (`postulante_apertura_id`),
  ADD KEY `idx_sesion_cierre` (`postulante_cierre_id`),
  ADD KEY `idx_sesion_revisor` (`postulante_revisor_id`),
  ADD KEY `idx_sesion_turno` (`turno_id`),
  ADD KEY `idx_sesion_fecha_operacion` (`fecha_operacion`),
  ADD KEY `idx_sesion_estado` (`estado`);

--
-- Indexes for table `sesion_participante`
--
ALTER TABLE `sesion_participante`
  ADD PRIMARY KEY (`id_sesion_participante`),
  ADD UNIQUE KEY `uq_sesion_participante` (`sesion_id`,`postulante_id`),
  ADD KEY `idx_sp_postulante` (`postulante_id`);

--
-- Indexes for table `situacion_vivienda`
--
ALTER TABLE `situacion_vivienda`
  ADD PRIMARY KEY (`id_situacion`),
  ADD UNIQUE KEY `uq_vivienda_desc` (`descripcion`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`id_skill`),
  ADD UNIQUE KEY `uq_skill_desc` (`descripcion`);

--
-- Indexes for table `solicitud_cambio`
--
ALTER TABLE `solicitud_cambio`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `slot_id` (`slot_id`),
  ADD KEY `semana_id` (`semana_id`),
  ADD KEY `postulante_solicitante_id` (`postulante_solicitante_id`),
  ADD KEY `postulante_original_id` (`postulante_original_id`);

--
-- Indexes for table `tipo_egreso`
--
ALTER TABLE `tipo_egreso`
  ADD PRIMARY KEY (`id_tipo_egreso`);

--
-- Indexes for table `tipo_estudio`
--
ALTER TABLE `tipo_estudio`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `uq_tipo_estudio_desc` (`descripcion`);

--
-- Indexes for table `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  ADD PRIMARY KEY (`id_tipo_movimiento`),
  ADD UNIQUE KEY `uq_tipo_mov_desc` (`descripcion`);

--
-- Indexes for table `tipo_personal`
--
ALTER TABLE `tipo_personal`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `tipo_rectificacion`
--
ALTER TABLE `tipo_rectificacion`
  ADD PRIMARY KEY (`id_tipo_rect`);

--
-- Indexes for table `transferencia_caja`
--
ALTER TABLE `transferencia_caja`
  ADD PRIMARY KEY (`id_transferencia`),
  ADD KEY `idx_tf_sesion_origen` (`sesion_origen_id`),
  ADD KEY `idx_tf_sesion_destino` (`sesion_destino_id`),
  ADD KEY `idx_tf_caja_origen` (`caja_origen_id`),
  ADD KEY `idx_tf_caja_destino` (`caja_destino_id`),
  ADD KEY `idx_tf_envia` (`postulante_envia_id`),
  ADD KEY `idx_tf_recibe` (`postulante_recibe_id`),
  ADD KEY `idx_tf_revisa` (`postulante_revisa_id`),
  ADD KEY `idx_tf_estado` (`estado`);

--
-- Indexes for table `transferencia_saldo`
--
ALTER TABLE `transferencia_saldo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `turno`
--
ALTER TABLE `turno`
  ADD PRIMARY KEY (`id_turno`),
  ADD UNIQUE KEY `uq_turno_desc` (`descripcion`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`postulante_id`),
  ADD UNIQUE KEY `uq_usuario_username` (`username`),
  ADD KEY `idx_usuario_rol` (`rol_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ajuste_esperado`
--
ALTER TABLE `ajuste_esperado`
  MODIFY `id_ajuste` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `asistencia_checklist`
--
ALTER TABLE `asistencia_checklist`
  MODIFY `id_asistencia_checklist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

--
-- AUTO_INCREMENT for table `auditoria_cuadre`
--
ALTER TABLE `auditoria_cuadre`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `caja`
--
ALTER TABLE `caja`
  MODIFY `id_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `checklist`
--
ALTER TABLE `checklist`
  MODIFY `id_checklist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `concepto_gastos_local`
--
ALTER TABLE `concepto_gastos_local`
  MODIFY `id_concepto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `concepto_penalidad`
--
ALTER TABLE `concepto_penalidad`
  MODIFY `id_concepto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contacto_emergencia`
--
ALTER TABLE `contacto_emergencia`
  MODIFY `id_contacto_emergencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `correccion_venta`
--
ALTER TABLE `correccion_venta`
  MODIFY `id_correccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `especialidad`
--
ALTER TABLE `especialidad`
  MODIFY `id_especialidad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `estudio`
--
ALTER TABLE `estudio`
  MODIFY `id_estudio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `etapa`
--
ALTER TABLE `etapa`
  MODIFY `id_etapa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `experiencia`
--
ALTER TABLE `experiencia`
  MODIFY `id_experiencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `genero`
--
ALTER TABLE `genero`
  MODIFY `id_genero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `horario_slot`
--
ALTER TABLE `horario_slot`
  MODIFY `id_slot` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=673;

--
-- AUTO_INCREMENT for table `horario_solicitud`
--
ALTER TABLE `horario_solicitud`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incidencia`
--
ALTER TABLE `incidencia`
  MODIFY `id_incidencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `local`
--
ALTER TABLE `local`
  MODIFY `id_local` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `modo`
--
ALTER TABLE `modo`
  MODIFY `id_modo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `movimiento_sesion`
--
ALTER TABLE `movimiento_sesion`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id_nivel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pago_deposito`
--
ALTER TABLE `pago_deposito`
  MODIFY `id_pago_deposito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pago_factura`
--
ALTER TABLE `pago_factura`
  MODIFY `id_pago_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pago_local`
--
ALTER TABLE `pago_local`
  MODIFY `id_pago_local` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pago_personal`
--
ALTER TABLE `pago_personal`
  MODIFY `id_pago_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `plantilla_horario`
--
ALTER TABLE `plantilla_horario`
  MODIFY `id_plantilla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `postulacion`
--
ALTER TABLE `postulacion`
  MODIFY `id_postulacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `postulante`
--
ALTER TABLE `postulante`
  MODIFY `id_postulante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `puesto`
--
ALTER TABLE `puesto`
  MODIFY `id_puesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rectificacion_cuadre`
--
ALTER TABLE `rectificacion_cuadre`
  MODIFY `id_rectificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `reporte_venta`
--
ALTER TABLE `reporte_venta`
  MODIFY `id_reporte_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rol_horario`
--
ALTER TABLE `rol_horario`
  MODIFY `id_rol_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `semana`
--
ALTER TABLE `semana`
  MODIFY `id_semana` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sesion_caja`
--
ALTER TABLE `sesion_caja`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `sesion_participante`
--
ALTER TABLE `sesion_participante`
  MODIFY `id_sesion_participante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT for table `situacion_vivienda`
--
ALTER TABLE `situacion_vivienda`
  MODIFY `id_situacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `id_skill` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `solicitud_cambio`
--
ALTER TABLE `solicitud_cambio`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tipo_egreso`
--
ALTER TABLE `tipo_egreso`
  MODIFY `id_tipo_egreso` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tipo_estudio`
--
ALTER TABLE `tipo_estudio`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  MODIFY `id_tipo_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipo_rectificacion`
--
ALTER TABLE `tipo_rectificacion`
  MODIFY `id_tipo_rect` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transferencia_caja`
--
ALTER TABLE `transferencia_caja`
  MODIFY `id_transferencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transferencia_saldo`
--
ALTER TABLE `transferencia_saldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `turno`
--
ALTER TABLE `turno`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_asistencia_usuario` FOREIGN KEY (`postulante_id`) REFERENCES `usuario` (`postulante_id`);

--
-- Constraints for table `asistencia_checklist`
--
ALTER TABLE `asistencia_checklist`
  ADD CONSTRAINT `fk_ac_asistencia` FOREIGN KEY (`asistencia_id`) REFERENCES `asistencia` (`id_asistencia`),
  ADD CONSTRAINT `fk_ac_checklist` FOREIGN KEY (`checklist_id`) REFERENCES `checklist` (`id_checklist`);

--
-- Constraints for table `auditoria_cuadre`
--
ALTER TABLE `auditoria_cuadre`
  ADD CONSTRAINT `fk_aud_cuadre_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_aud_cuadre_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`);

--
-- Constraints for table `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
  ADD CONSTRAINT `fk_as_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`);

--
-- Constraints for table `caja`
--
ALTER TABLE `caja`
  ADD CONSTRAINT `fk_caja_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`);

--
-- Constraints for table `contacto_emergencia`
--
ALTER TABLE `contacto_emergencia`
  ADD CONSTRAINT `fk_ce_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE;

--
-- Constraints for table `correccion_venta`
--
ALTER TABLE `correccion_venta`
  ADD CONSTRAINT `correccion_venta_ibfk_1` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`) ON DELETE CASCADE;

--
-- Constraints for table `detalle_cuadre`
--
ALTER TABLE `detalle_cuadre`
  ADD CONSTRAINT `fk_dc_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`);

--
-- Constraints for table `estudio`
--
ALTER TABLE `estudio`
  ADD CONSTRAINT `fk_estudio_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id_estado`),
  ADD CONSTRAINT `fk_estudio_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `institucion` (`id_institucion`),
  ADD CONSTRAINT `fk_estudio_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_estudio_tipo` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_estudio` (`id_tipo`);

--
-- Constraints for table `experiencia`
--
ALTER TABLE `experiencia`
  ADD CONSTRAINT `fk_experiencia_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE;

--
-- Constraints for table `horario_slot`
--
ALTER TABLE `horario_slot`
  ADD CONSTRAINT `fk_hs_rol` FOREIGN KEY (`rol_horario_id`) REFERENCES `rol_horario` (`id_rol_horario`),
  ADD CONSTRAINT `fk_hslot_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  ADD CONSTRAINT `fk_hslot_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hslot_semana` FOREIGN KEY (`semana_id`) REFERENCES `semana` (`id_semana`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hslot_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`);

--
-- Constraints for table `horario_solicitud`
--
ALTER TABLE `horario_solicitud`
  ADD CONSTRAINT `fk_hs_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  ADD CONSTRAINT `fk_hs_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hs_revisado_por` FOREIGN KEY (`revisado_por_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hs_semana` FOREIGN KEY (`semana_id`) REFERENCES `semana` (`id_semana`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hs_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`);

--
-- Constraints for table `incidencia`
--
ALTER TABLE `incidencia`
  ADD CONSTRAINT `fk_inc_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`),
  ADD CONSTRAINT `fk_inc_usuario` FOREIGN KEY (`postulante_id`) REFERENCES `usuario` (`postulante_id`);

--
-- Constraints for table `local`
--
ALTER TABLE `local`
  ADD CONSTRAINT `fk_local_encargado` FOREIGN KEY (`id_encargado`) REFERENCES `postulante` (`id_postulante`) ON DELETE SET NULL;

--
-- Constraints for table `movimiento_sesion`
--
ALTER TABLE `movimiento_sesion`
  ADD CONSTRAINT `fk_mov_modo` FOREIGN KEY (`modo_id`) REFERENCES `modo` (`id_modo`),
  ADD CONSTRAINT `fk_mov_registro` FOREIGN KEY (`postulante_registro_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_mov_revision` FOREIGN KEY (`postulante_revision_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_mov_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`),
  ADD CONSTRAINT `fk_mov_tipo` FOREIGN KEY (`tipo_movimiento_id`) REFERENCES `tipo_movimiento` (`id_tipo_movimiento`);

--
-- Constraints for table `pago_local`
--
ALTER TABLE `pago_local`
  ADD CONSTRAINT `fk_pl_concepto` FOREIGN KEY (`concepto_id`) REFERENCES `concepto_gastos_local` (`id_concepto`),
  ADD CONSTRAINT `fk_pl_emisor` FOREIGN KEY (`postulante_emisor_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_pl_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  ADD CONSTRAINT `fk_pl_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`);

--
-- Constraints for table `pago_personal`
--
ALTER TABLE `pago_personal`
  ADD CONSTRAINT `fk_pp_beneficiario` FOREIGN KEY (`postulante_beneficiario_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_pp_emisor` FOREIGN KEY (`postulante_emisor_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_pp_revisor` FOREIGN KEY (`postulante_revisor_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_pp_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`);

--
-- Constraints for table `plantilla_horario`
--
ALTER TABLE `plantilla_horario`
  ADD CONSTRAINT `plantilla_horario_ibfk_1` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  ADD CONSTRAINT `plantilla_horario_ibfk_2` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`),
  ADD CONSTRAINT `plantilla_horario_ibfk_3` FOREIGN KEY (`rol_horario_id`) REFERENCES `rol_horario` (`id_rol_horario`);

--
-- Constraints for table `postulacion`
--
ALTER TABLE `postulacion`
  ADD CONSTRAINT `fk_postulacion_etapa` FOREIGN KEY (`etapa_id`) REFERENCES `etapa` (`id_etapa`),
  ADD CONSTRAINT `fk_postulacion_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_postulacion_puesto` FOREIGN KEY (`puesto_id`) REFERENCES `puesto` (`id_puesto`);

--
-- Constraints for table `postulante`
--
ALTER TABLE `postulante`
  ADD CONSTRAINT `fk_postulante_genero` FOREIGN KEY (`genero_id`) REFERENCES `genero` (`id_genero`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_postulante_vivienda` FOREIGN KEY (`situacion_vivienda_id`) REFERENCES `situacion_vivienda` (`id_situacion`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `postulante_especialidad`
--
ALTER TABLE `postulante_especialidad`
  ADD CONSTRAINT `fk_pe_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidad` (`id_especialidad`),
  ADD CONSTRAINT `fk_pe_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`);

--
-- Constraints for table `postulante_skill`
--
ALTER TABLE `postulante_skill`
  ADD CONSTRAINT `fk_ps_nivel` FOREIGN KEY (`nivel_id`) REFERENCES `nivel` (`id_nivel`),
  ADD CONSTRAINT `fk_ps_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ps_skill` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id_skill`);

--
-- Constraints for table `preferencias`
--
ALTER TABLE `preferencias`
  ADD CONSTRAINT `fk_pref_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pref_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`);

--
-- Constraints for table `rectificacion_cuadre`
--
ALTER TABLE `rectificacion_cuadre`
  ADD CONSTRAINT `fk_rect_modo` FOREIGN KEY (`modo_id`) REFERENCES `modo` (`id_modo`),
  ADD CONSTRAINT `fk_rect_registra` FOREIGN KEY (`postulante_registra_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_rect_responsable` FOREIGN KEY (`postulante_responsable_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_rect_revisa` FOREIGN KEY (`postulante_revisa_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_rect_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`);

--
-- Constraints for table `reporte_venta`
--
ALTER TABLE `reporte_venta`
  ADD CONSTRAINT `fk_rv_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`),
  ADD CONSTRAINT `fk_rv_vendedor` FOREIGN KEY (`postulante_vendedor_id`) REFERENCES `postulante` (`id_postulante`);

--
-- Constraints for table `sesion_caja`
--
ALTER TABLE `sesion_caja`
  ADD CONSTRAINT `fk_sesion_apertura` FOREIGN KEY (`postulante_apertura_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_sesion_caja` FOREIGN KEY (`caja_id`) REFERENCES `caja` (`id_caja`),
  ADD CONSTRAINT `fk_sesion_cierre` FOREIGN KEY (`postulante_cierre_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_sesion_revisor` FOREIGN KEY (`postulante_revisor_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_sesion_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`);

--
-- Constraints for table `sesion_participante`
--
ALTER TABLE `sesion_participante`
  ADD CONSTRAINT `fk_sp_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_sp_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`);

--
-- Constraints for table `solicitud_cambio`
--
ALTER TABLE `solicitud_cambio`
  ADD CONSTRAINT `solicitud_cambio_ibfk_1` FOREIGN KEY (`slot_id`) REFERENCES `horario_slot` (`id_slot`),
  ADD CONSTRAINT `solicitud_cambio_ibfk_2` FOREIGN KEY (`semana_id`) REFERENCES `semana` (`id_semana`),
  ADD CONSTRAINT `solicitud_cambio_ibfk_3` FOREIGN KEY (`postulante_solicitante_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `solicitud_cambio_ibfk_4` FOREIGN KEY (`postulante_original_id`) REFERENCES `postulante` (`id_postulante`);

--
-- Constraints for table `transferencia_caja`
--
ALTER TABLE `transferencia_caja`
  ADD CONSTRAINT `fk_tf_caja_destino` FOREIGN KEY (`caja_destino_id`) REFERENCES `caja` (`id_caja`),
  ADD CONSTRAINT `fk_tf_caja_origen` FOREIGN KEY (`caja_origen_id`) REFERENCES `caja` (`id_caja`),
  ADD CONSTRAINT `fk_tf_envia` FOREIGN KEY (`postulante_envia_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_tf_recibe` FOREIGN KEY (`postulante_recibe_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_tf_revisa` FOREIGN KEY (`postulante_revisa_id`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `fk_tf_sesion_destino` FOREIGN KEY (`sesion_destino_id`) REFERENCES `sesion_caja` (`id_sesion`),
  ADD CONSTRAINT `fk_tf_sesion_origen` FOREIGN KEY (`sesion_origen_id`) REFERENCES `sesion_caja` (`id_sesion`);

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
