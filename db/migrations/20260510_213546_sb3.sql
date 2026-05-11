-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 10, 2026 at 09:31 PM
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
-- Table structure for table `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `postulante_id` int(11) NOT NULL,
  `local_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_ingreso` datetime DEFAULT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `estado` enum('A TIEMPO','TARDE','FALTA','EXTRA','TEMPRANO') NOT NULL DEFAULT 'A TIEMPO',
  `justificacion` text DEFAULT NULL,
  `observacion` enum('PROCEDE','NO PROCEDE','PENDIENTE') NOT NULL DEFAULT 'PENDIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asistencia`
--

INSERT INTO `asistencia` (`id_asistencia`, `postulante_id`, `local_id`, `fecha`, `hora_ingreso`, `hora_salida`, `estado`, `justificacion`, `observacion`) VALUES
(5, 1, 2, '2026-05-04', '2026-05-04 15:00:00', '2026-05-04 21:07:00', 'A TIEMPO', NULL, 'PENDIENTE'),
(6, 1, 3, '2026-05-04', '2026-05-04 21:08:21', '2026-05-04 21:08:31', 'EXTRA', NULL, 'PENDIENTE'),
(9, 11, 4, '2026-05-06', '2026-05-06 09:32:54', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(10, 4, NULL, '2026-05-06', '2026-05-06 09:45:34', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(11, 5, NULL, '2026-05-06', '2026-05-06 10:40:11', '2026-05-06 23:06:29', 'EXTRA', NULL, 'PENDIENTE'),
(12, 56, 2, '2026-05-06', '2026-05-06 10:40:18', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(13, 55, 4, '2026-05-06', '2026-05-06 11:40:10', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(14, 53, 3, '2026-05-06', '2026-05-06 13:36:35', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(15, 71, NULL, '2026-05-06', '2026-05-06 16:05:12', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(16, 22, 3, '2026-05-07', '2026-05-07 10:20:10', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(17, 22, NULL, '2026-05-08', '2026-05-08 07:19:10', NULL, 'TARDE', NULL, 'PENDIENTE'),
(18, 70, 3, '2026-05-08', '2026-05-08 07:20:31', NULL, 'TARDE', NULL, 'PENDIENTE'),
(19, 53, 3, '2026-05-08', '2026-05-08 16:12:31', NULL, 'EXTRA', NULL, 'PENDIENTE'),
(20, 70, 3, '2026-05-09', '2026-05-09 07:05:24', NULL, 'A TIEMPO', NULL, 'PENDIENTE'),
(21, 53, 3, '2026-05-09', '2026-05-09 15:31:02', NULL, 'TARDE', NULL, 'PENDIENTE'),
(22, 60, 3, '2026-05-09', '2026-05-09 15:34:31', NULL, 'TARDE', NULL, 'PENDIENTE'),
(23, 53, 3, '2026-05-10', '2026-05-10 07:26:17', NULL, 'TARDE', NULL, 'PENDIENTE'),
(24, 60, 3, '2026-05-10', '2026-05-10 14:59:30', NULL, 'TEMPRANO', NULL, 'PENDIENTE');

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
(110, 24, 5, 1, NULL);

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
(71, 183.80, 620.00, 1010.00, 11700.00, 0.00, 0.00, 0.00, 26556.48, 40070.28, 40070.28, 756.30, 0.00, 40050.84, 19.44, 'SOBRANTE', NULL, 40070.28, 0.00, 40070.28, 185);

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
(18, 68, 3, 5, 3, '2026-05-06', NULL),
(21, 69, 2, 4, 1, '2022-11-06', '2025-11-06'),
(25, 70, 2, 4, 2, '2023-05-06', '2026-05-06'),
(27, 71, 2, 5, 3, '2019-05-06', '2022-05-06'),
(29, 72, 3, 5, 4, '1985-01-01', NULL),
(34, 73, 2, 5, 2, '2000-01-01', NULL);

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
(56, 1, 2, 2, '2026-05-13', 1, 1, 29, '2026-05-06 16:30:25'),
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
(134, 1, 2, 2, '2026-05-16', 1, 1, 57, '2026-05-06 15:32:47'),
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
(146, 1, 3, 2, '2026-05-16', 1, 3, NULL, NULL),
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
(182, 1, 4, 2, '2026-05-17', 3, 1, NULL, NULL);

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
(2, 'YAPE', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(3, 'PLIN', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(4, 'VISAS', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(5, 'BCP', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12'),
(6, 'TRANSFERENCIA_BANCARIA', 1, '2026-05-04 20:58:12', '2026-05-04 20:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `movimiento_sesion`
--

CREATE TABLE `movimiento_sesion` (
  `id_movimiento` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `tipo_movimiento_id` int(11) NOT NULL,
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

INSERT INTO `movimiento_sesion` (`id_movimiento`, `sesion_id`, `tipo_movimiento_id`, `modo_id`, `postulante_registro_id`, `postulante_revision_id`, `descripcion`, `monto`, `numero_operacion`, `comprobante_url`, `fecha_movimiento`, `fecha_revision`, `fecha_anulacion`, `estado`, `motivo_anulacion`, `observacion_revision`) VALUES
(17, 27, 1, 2, 29, NULL, NULL, 445.70, NULL, NULL, '2026-05-07 14:41:58', NULL, NULL, 'PENDIENTE', NULL, NULL),
(18, 30, 1, 2, 17, NULL, NULL, 1000.00, NULL, NULL, '2026-05-07 14:56:19', NULL, NULL, 'PENDIENTE', NULL, NULL),
(19, 30, 1, 4, 17, NULL, NULL, 1000.00, NULL, NULL, '2026-05-07 14:56:24', NULL, NULL, 'PENDIENTE', NULL, NULL),
(20, 30, 1, 3, 17, NULL, NULL, 938.90, NULL, NULL, '2026-05-07 14:56:37', NULL, NULL, 'PENDIENTE', NULL, NULL),
(21, 33, 1, 2, 22, NULL, NULL, 10.00, NULL, NULL, '2026-05-07 15:13:59', NULL, NULL, 'PENDIENTE', NULL, NULL),
(25, 31, 1, 2, 17, NULL, NULL, 388.30, NULL, NULL, '2026-05-07 19:58:44', NULL, NULL, 'PENDIENTE', NULL, NULL),
(26, 31, 1, 4, 17, NULL, NULL, 232.80, NULL, NULL, '2026-05-07 19:59:03', NULL, NULL, 'PENDIENTE', NULL, NULL),
(27, 31, 2, 1, 17, NULL, 'desayuno sra. Marina', 3.01, NULL, NULL, '2026-05-07 20:00:17', NULL, NULL, 'APROBADO', NULL, NULL),
(28, 28, 1, 2, 29, NULL, NULL, 142.70, NULL, NULL, '2026-05-07 20:07:03', NULL, NULL, 'PENDIENTE', NULL, NULL),
(29, 35, 2, 1, 22, NULL, 'se compro lancetas', 40.00, NULL, NULL, '2026-05-07 20:16:16', NULL, NULL, 'APROBADO', NULL, NULL),
(30, 35, 2, 1, 22, NULL, 'deposito grupo kgyr', 11000.01, NULL, NULL, '2026-05-07 20:16:16', NULL, NULL, 'APROBADO', NULL, NULL),
(31, 38, 1, 2, 71, NULL, NULL, 612.80, NULL, NULL, '2026-05-07 20:22:38', NULL, NULL, 'PENDIENTE', NULL, NULL),
(33, 40, 1, 2, 54, NULL, NULL, 151.60, NULL, NULL, '2026-05-07 20:27:30', NULL, NULL, 'PENDIENTE', NULL, NULL),
(37, 42, 1, 2, 17, NULL, NULL, 871.10, NULL, NULL, '2026-05-08 03:59:23', NULL, NULL, 'PENDIENTE', NULL, NULL),
(38, 42, 1, 4, 17, NULL, NULL, 100.70, NULL, NULL, '2026-05-08 03:59:38', NULL, NULL, 'PENDIENTE', NULL, NULL),
(40, 42, 2, 1, 17, NULL, 'compra de Ciro', 80.50, NULL, NULL, '2026-05-08 04:00:18', NULL, NULL, 'APROBADO', NULL, NULL),
(42, 44, 2, 1, 57, NULL, 'compra de ciro', 37.00, NULL, NULL, '2026-05-08 04:15:55', NULL, NULL, 'APROBADO', NULL, NULL),
(43, 48, 1, 2, 69, NULL, NULL, 282.10, NULL, NULL, '2026-05-08 13:42:00', NULL, NULL, 'PENDIENTE', NULL, NULL),
(45, 50, 1, 2, 73, NULL, NULL, 298.10, NULL, NULL, '2026-05-08 16:07:40', NULL, NULL, 'PENDIENTE', NULL, NULL),
(46, 52, 2, 1, 22, NULL, 'deposito a grupo kgyr', 10000.00, NULL, NULL, '2026-05-08 19:51:46', NULL, NULL, 'APROBADO', NULL, NULL),
(47, 51, 1, 2, 11, NULL, NULL, 220.10, NULL, NULL, '2026-05-08 20:01:02', NULL, NULL, 'PENDIENTE', NULL, NULL),
(49, 47, 1, 4, 70, NULL, NULL, 22.90, NULL, NULL, '2026-05-08 20:08:34', NULL, NULL, 'PENDIENTE', NULL, NULL),
(50, 47, 1, 2, 70, NULL, NULL, 543.20, NULL, NULL, '2026-05-08 20:08:47', NULL, NULL, 'PENDIENTE', NULL, NULL),
(51, 47, 2, 1, 70, NULL, 'COMPRA', 42.00, NULL, NULL, '2026-05-08 20:09:36', NULL, NULL, 'APROBADO', NULL, NULL),
(52, 53, 1, 2, 29, NULL, NULL, 47.10, NULL, NULL, '2026-05-08 20:09:43', NULL, NULL, 'PENDIENTE', NULL, NULL),
(59, 55, 1, 2, 69, NULL, NULL, 707.70, NULL, NULL, '2026-05-09 04:07:20', NULL, NULL, 'PENDIENTE', NULL, NULL),
(60, 55, 1, 4, 69, NULL, NULL, 273.30, NULL, NULL, '2026-05-09 04:07:46', NULL, NULL, 'PENDIENTE', NULL, NULL),
(61, 57, 1, 2, 73, NULL, NULL, 206.20, NULL, NULL, '2026-05-09 04:09:33', NULL, NULL, 'PENDIENTE', NULL, NULL),
(62, 57, 1, 4, 73, NULL, NULL, 67.60, NULL, NULL, '2026-05-09 04:09:41', NULL, NULL, 'PENDIENTE', NULL, NULL),
(63, 55, 2, 1, 69, NULL, 'Gasto', 410.00, NULL, NULL, '2026-05-09 04:10:44', NULL, NULL, 'APROBADO', NULL, NULL),
(64, 55, 2, 1, 69, NULL, 'compra ciro', 37.00, NULL, NULL, '2026-05-09 04:10:44', NULL, NULL, 'APROBADO', NULL, NULL),
(65, 58, 1, 2, 53, NULL, NULL, 317.50, NULL, NULL, '2026-05-09 04:12:04', NULL, NULL, 'PENDIENTE', NULL, NULL),
(66, 58, 1, 4, 53, NULL, NULL, 188.90, NULL, NULL, '2026-05-09 04:12:14', NULL, NULL, 'PENDIENTE', NULL, NULL),
(67, 58, 2, 1, 53, NULL, 'compra de ciro', 74.00, NULL, NULL, '2026-05-09 04:12:42', NULL, NULL, 'APROBADO', NULL, NULL),
(68, 59, 1, 2, 29, NULL, NULL, 186.10, NULL, NULL, '2026-05-09 04:13:36', NULL, NULL, 'PENDIENTE', NULL, NULL),
(69, 61, 1, 2, 73, NULL, NULL, 885.65, NULL, NULL, '2026-05-09 20:03:08', NULL, NULL, 'PENDIENTE', NULL, NULL),
(70, 61, 1, 4, 73, NULL, NULL, 39.00, NULL, NULL, '2026-05-09 20:03:19', NULL, NULL, 'PENDIENTE', NULL, NULL),
(71, 62, 2, 1, 57, NULL, 'visa y yape', 90.20, NULL, NULL, '2026-05-09 20:23:34', NULL, NULL, 'APROBADO', NULL, NULL),
(72, 63, 1, 2, 71, NULL, NULL, 142.80, NULL, NULL, '2026-05-09 21:16:47', NULL, NULL, 'PENDIENTE', NULL, NULL),
(73, 63, 1, 4, 71, NULL, NULL, 45.30, NULL, NULL, '2026-05-09 21:16:59', NULL, NULL, 'PENDIENTE', NULL, NULL),
(74, 64, 1, 2, 57, NULL, NULL, 103.10, NULL, NULL, '2026-05-10 03:59:03', NULL, NULL, 'PENDIENTE', NULL, NULL),
(75, 64, 1, 4, 57, NULL, NULL, 3.00, NULL, NULL, '2026-05-10 03:59:12', NULL, NULL, 'PENDIENTE', NULL, NULL),
(76, 64, 2, 1, 57, NULL, 'descargo algodon', 2.60, NULL, NULL, '2026-05-10 03:59:46', NULL, NULL, 'APROBADO', NULL, NULL),
(77, 65, 2, 1, 53, NULL, 'compra de glucophage xr 100 x que faltaba completar para venta', 76.60, NULL, NULL, '2026-05-10 04:05:06', NULL, NULL, 'APROBADO', NULL, NULL),
(78, 66, 1, 2, 17, NULL, NULL, 583.80, NULL, NULL, '2026-05-10 04:05:54', NULL, NULL, 'PENDIENTE', NULL, NULL),
(79, 66, 1, 4, 17, NULL, NULL, 277.00, NULL, NULL, '2026-05-10 04:06:10', NULL, NULL, 'PENDIENTE', NULL, NULL),
(80, 66, 2, 1, 17, NULL, 'compra paliglobo', 32.00, NULL, NULL, '2026-05-10 04:07:21', NULL, NULL, 'APROBADO', NULL, NULL),
(81, 66, 2, 1, 17, NULL, 'deposito sra. Marina', 500.00, NULL, NULL, '2026-05-10 04:07:21', NULL, NULL, 'APROBADO', NULL, NULL),
(82, 67, 1, 2, 71, NULL, NULL, 255.60, NULL, NULL, '2026-05-10 04:14:52', NULL, NULL, 'PENDIENTE', NULL, NULL),
(83, 67, 1, 4, 71, NULL, NULL, 156.40, NULL, NULL, '2026-05-10 04:15:02', NULL, NULL, 'PENDIENTE', NULL, NULL),
(84, 67, 2, 1, 71, NULL, '1 docena de paliglobos', 2.50, NULL, NULL, '2026-05-10 04:16:04', NULL, NULL, 'APROBADO', NULL, NULL),
(97, 68, 1, 4, 17, NULL, NULL, 111.10, NULL, NULL, '2026-05-10 19:57:54', NULL, NULL, 'PENDIENTE', NULL, NULL),
(98, 68, 1, 2, 17, NULL, NULL, 306.00, NULL, NULL, '2026-05-10 19:58:57', NULL, NULL, 'PENDIENTE', NULL, NULL),
(102, 69, 1, 2, 69, NULL, NULL, 166.50, NULL, NULL, '2026-05-10 20:02:17', NULL, NULL, 'PENDIENTE', NULL, NULL),
(103, 68, 2, 1, 17, NULL, 'compra de jabon', 4.50, NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'APROBADO', NULL, NULL),
(104, 68, 2, 1, 17, NULL, 'compra de bolsas', 67.00, NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'APROBADO', NULL, NULL),
(105, 68, 2, 1, 17, NULL, 'pago de terreno Ayacucho', 2499.99, NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'APROBADO', NULL, NULL),
(106, 70, 1, 2, 53, NULL, NULL, 112.40, NULL, NULL, '2026-05-10 20:06:04', NULL, NULL, 'PENDIENTE', NULL, NULL),
(107, 70, 2, 1, 53, NULL, 'tiras reactivas de glucosa', 79.80, NULL, NULL, '2026-05-10 20:07:09', NULL, NULL, 'APROBADO', NULL, NULL),
(108, 71, 1, 2, 11, NULL, NULL, 356.30, NULL, NULL, '2026-05-10 20:17:35', NULL, NULL, 'PENDIENTE', NULL, NULL),
(109, 71, 1, 4, 11, NULL, NULL, 6.60, NULL, NULL, '2026-05-10 20:17:43', NULL, NULL, 'PENDIENTE', NULL, NULL);

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
-- Table structure for table `pago_local`
--

CREATE TABLE `pago_local` (
  `id_pago_local` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  `postulante_emisor_id` int(11) NOT NULL,
  `concepto_id` int(11) NOT NULL,
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
  `postulante_emisor_id` int(11) NOT NULL,
  `postulante_beneficiario_id` int(11) NOT NULL,
  `postulante_revisor_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
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

INSERT INTO `pago_personal` (`id_pago_personal`, `sesion_id`, `postulante_emisor_id`, `postulante_beneficiario_id`, `postulante_revisor_id`, `monto`, `numero_operacion`, `comprobante_url`, `fecha_pago`, `fecha_confirmacion_beneficiario`, `fecha_revision`, `estado`, `observacion_beneficiario`, `observacion_revision`) VALUES
(4, 55, 69, 69, NULL, 640.00, NULL, NULL, '2026-05-09 04:10:44', NULL, NULL, 'PAGADO', NULL, NULL),
(5, 61, 73, 22, NULL, 649.99, NULL, NULL, '2026-05-09 20:03:56', NULL, NULL, 'PAGADO', NULL, NULL),
(11, 68, 17, 73, NULL, 150.00, NULL, NULL, '2026-05-10 20:03:36', NULL, NULL, 'PAGADO', NULL, NULL);

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
(18, 4, 2, 3, 1);

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
(58, 68, 7, 4, 0, NULL, NULL, '2026-05-06 17:07:28', '2026-05-06 17:07:28'),
(61, 69, 3, 4, 0, NULL, NULL, '2026-05-06 18:09:59', '2026-05-06 18:09:59'),
(65, 70, 7, 4, 0, NULL, NULL, '2026-05-06 21:02:12', '2026-05-06 21:02:12'),
(67, 71, 7, 4, 0, NULL, NULL, '2026-05-06 21:04:09', '2026-05-06 21:04:09'),
(69, 72, 1, 4, 0, NULL, NULL, '2026-05-06 23:34:30', '2026-05-06 23:34:30'),
(74, 73, 7, 4, 0, NULL, NULL, '2026-05-08 15:11:35', '2026-05-08 15:11:35');

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
(68, 'BLOQUEADO', '', NULL, '2026-05-06', '', '', NULL, '00000000', '', '', NULL, NULL, '2026-05-06 17:06:24', '2026-05-06 17:07:03', NULL, 4, 'A1'),
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
(1, 68, '2026-05-06 17:07:28'),
(1, 69, '2026-05-06 18:09:59'),
(1, 70, '2026-05-06 21:02:12'),
(1, 72, '2026-05-06 23:34:30'),
(1, 73, '2026-05-08 15:11:35'),
(2, 1, '2026-05-04 21:08:25'),
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
(2, 68, '2026-05-06 17:07:28'),
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
  `tipo_rectificacion` enum('DEVOLUCION_DINERO','DINERO_ENCONTRADO','AJUSTE_CONTEO','COMPENSACION','OTRO') NOT NULL,
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

INSERT INTO `rectificacion_cuadre` (`id_rectificacion`, `sesion_id`, `postulante_registra_id`, `postulante_responsable_id`, `tipo_rectificacion`, `modo_id`, `monto`, `descripcion_contexto`, `justificacion`, `comprobante_url`, `fecha_rectificacion`, `estado`, `postulante_revisa_id`, `fecha_revision`, `observacion_revision`) VALUES
(9, 30, 17, NULL, 'DINERO_ENCONTRADO', NULL, 10.00, 'encontrado en el piso', NULL, NULL, '2026-05-07 15:04:39', 'APROBADA', NULL, NULL, NULL),
(10, 30, 17, NULL, 'DEVOLUCION_DINERO', NULL, -10.00, 'falta descargar', NULL, NULL, '2026-05-07 15:05:36', 'APROBADA', NULL, NULL, NULL),
(11, 33, 22, NULL, 'DEVOLUCION_DINERO', NULL, -10.00, 'me equivoque con un yape', NULL, NULL, '2026-05-07 15:15:38', 'APROBADA', NULL, NULL, NULL),
(12, 33, 22, NULL, 'DINERO_ENCONTRADO', NULL, 10.00, 'correcion', NULL, NULL, '2026-05-07 15:18:39', 'APROBADA', NULL, NULL, NULL),
(13, 31, 17, NULL, 'DINERO_ENCONTRADO', NULL, 90.00, 'falto contar', NULL, NULL, '2026-05-07 20:17:11', 'APROBADA', NULL, NULL, NULL),
(14, 48, 69, NULL, 'DINERO_ENCONTRADO', NULL, 0.41, 'falto contar en caja  0.4  y en la venta 0.01', NULL, NULL, '2026-05-08 14:16:36', 'APROBADA', NULL, NULL, NULL),
(15, 52, 22, NULL, 'DINERO_ENCONTRADO', NULL, 120.00, 'compra de ciro', NULL, NULL, '2026-05-08 19:59:56', 'APROBADA', NULL, NULL, NULL),
(16, 51, 11, NULL, 'OTRO', NULL, 6.60, 'falta descargar', NULL, NULL, '2026-05-08 20:06:19', 'APROBADA', NULL, NULL, NULL),
(17, 51, 11, NULL, 'OTRO', NULL, -13.20, 'correcion', NULL, NULL, '2026-05-08 20:25:55', 'APROBADA', NULL, NULL, NULL),
(18, 67, 71, NULL, 'OTRO', NULL, -2.50, 'correcion', NULL, NULL, '2026-05-10 04:17:35', 'APROBADA', NULL, NULL, NULL);

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
(69, 71, 11, 756.30, '2026-05-10 20:18:25');

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
(3, 'ALMACENERA', 'Almacenera', 1, 3, '#f59e0b');

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
(1, '2026-05-11', '2026-05-17', 'ABIERTA', '2026-05-05 14:14:03');

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
  `observacion_revisor` text DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT 0,
  `requiere_revision` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sesion_caja`
--

INSERT INTO `sesion_caja` (`id_sesion`, `caja_id`, `turno_id`, `postulante_apertura_id`, `postulante_cierre_id`, `postulante_revisor_id`, `estado`, `saldo_inicial`, `saldo_final_sistema`, `saldo_final_contado`, `diferencia_final`, `margen_permitido`, `fecha_apertura`, `fecha_cierre`, `fecha_operacion`, `fecha_envio_revision`, `fecha_revision`, `observacion_cierre`, `observacion_revisor`, `motivo_rechazo`, `bloqueado`, `requiere_revision`) VALUES
(25, 2, 1, 29, 29, NULL, 'CERRADA', 0.00, 0.00, 42817.46, 42817.46, 10.00, '2026-05-07 14:36:46', '2026-05-07 14:37:15', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(27, 2, 1, 29, 29, NULL, 'CERRADA', 42817.46, 43360.86, 43362.03, 1.17, 10.00, '2026-05-07 14:41:07', '2026-05-07 14:42:09', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(28, 2, 1, 29, 29, NULL, 'CERRADA', 43362.03, 43560.73, 43561.33, 0.60, 10.00, '2026-05-07 14:45:33', '2026-05-07 20:07:39', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(29, 3, 1, 17, 17, NULL, 'CERRADA', 0.00, 0.00, 27818.14, 27818.14, 10.00, '2026-05-07 14:52:33', '2026-05-07 14:53:04', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(30, 3, 1, 17, 17, NULL, 'CERRADA', 27818.14, 26358.79, 26378.98, 20.19, 10.00, '2026-05-07 14:53:37', '2026-05-07 14:58:03', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(31, 3, 1, 17, 17, NULL, 'CERRADA', 26378.98, 26539.47, 26448.38, -91.09, 10.00, '2026-05-07 14:59:33', '2026-05-07 20:00:17', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(32, 5, 1, 22, 22, NULL, 'CERRADA', 0.00, 0.00, 48905.89, 48905.89, 10.00, '2026-05-07 15:10:33', '2026-05-07 15:11:02', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(33, 5, 1, 22, 22, NULL, 'CERRADA', 48905.89, 51230.88, 51246.73, 15.85, 10.00, '2026-05-07 15:11:55', '2026-05-07 15:14:38', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(35, 5, 1, 22, 22, NULL, 'CERRADA', 51246.73, 41324.07, 41326.95, 2.88, 10.00, '2026-05-07 20:10:22', '2026-05-07 20:16:16', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(37, 4, 1, 1, 1, NULL, 'CERRADA', 0.00, 0.00, 35751.00, 35751.00, 10.00, '2026-05-07 20:20:03', '2026-05-07 20:20:24', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(38, 4, 1, 71, 71, NULL, 'CERRADA', 35751.00, 36605.48, 36625.28, 19.80, 10.00, '2026-05-07 20:21:21', '2026-05-07 20:22:44', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(40, 4, 1, 54, 54, NULL, 'CERRADA', 36625.28, 37201.98, 37183.03, -18.95, 10.00, '2026-05-07 20:25:27', '2026-05-07 20:29:26', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(42, 3, 2, 17, 17, NULL, 'CERRADA', 26538.38, 26750.07, 26712.88, -37.19, 10.00, '2026-05-08 03:56:35', '2026-05-08 04:00:18', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(44, 5, 2, 57, 57, NULL, 'CERRADA', 41326.95, 42750.25, 42742.75, -7.50, 10.00, '2026-05-08 04:13:43', '2026-05-08 04:15:55', '2026-05-07', NULL, NULL, NULL, NULL, NULL, 1, 0),
(47, 3, 1, 70, 70, NULL, 'CERRADA', 26712.88, 26906.28, 26905.46, -0.82, 10.00, '2026-05-08 12:26:18', '2026-05-08 20:09:36', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(48, 2, 2, 69, 69, NULL, 'CERRADA', 43561.33, 43800.47, 43812.06, 11.59, 10.00, '2026-05-08 13:37:34', '2026-05-08 13:42:11', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(50, 4, 2, 73, 73, NULL, 'CERRADA', 37183.03, 37668.33, 37669.50, 1.17, 10.00, '2026-05-08 16:06:15', '2026-05-08 16:07:48', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(51, 4, 1, 11, 11, NULL, 'CERRADA', 37669.50, 37996.10, 38002.70, 6.60, 10.00, '2026-05-08 16:10:15', '2026-05-08 20:01:55', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(52, 5, 1, 22, 22, NULL, 'CERRADA', 42742.75, 34023.25, 33900.22, -123.03, 10.00, '2026-05-08 19:30:12', '2026-05-08 19:51:46', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(53, 2, 1, 29, 29, NULL, 'CERRADA', 43812.47, 44056.77, 44052.82, -3.95, 10.00, '2026-05-08 20:07:20', '2026-05-08 20:10:04', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(55, 3, 2, 69, 69, NULL, 'CERRADA', 26905.46, 26106.95, 26118.48, 11.53, 10.00, '2026-05-08 21:21:12', '2026-05-09 04:10:44', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(57, 4, 2, 73, 73, NULL, 'CERRADA', 37996.10, 38667.70, 38687.90, 20.20, 10.00, '2026-05-09 04:08:16', '2026-05-09 04:09:48', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(58, 5, 2, 53, 53, NULL, 'CERRADA', 34020.22, 34852.11, 35356.52, 504.41, 10.00, '2026-05-09 04:10:36', '2026-05-09 04:12:42', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(59, 2, 2, 29, 29, NULL, 'CERRADA', 44052.82, 44438.62, 44419.91, -18.71, 10.00, '2026-05-09 04:12:00', '2026-05-09 04:13:40', '2026-05-08', NULL, NULL, NULL, NULL, NULL, 1, 0),
(60, 5, 1, 22, 22, NULL, 'CERRADA', 35356.52, 36939.97, 36961.38, 21.41, 10.00, '2026-05-09 19:51:33', '2026-05-09 19:52:40', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(61, 3, 1, 73, 73, NULL, 'CERRADA', 26118.48, 25867.04, 25846.72, -20.32, 10.00, '2026-05-09 20:01:25', '2026-05-09 20:03:56', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(62, 2, 1, 57, 57, NULL, 'CERRADA', 44419.91, 44669.11, 44761.63, 92.52, 10.00, '2026-05-09 20:19:23', '2026-05-09 20:23:34', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(63, 4, 1, 71, 71, NULL, 'CERRADA', 38687.90, 39003.80, 39000.08, -3.72, 10.00, '2026-05-09 21:13:42', '2026-05-09 21:17:29', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(64, 2, 2, 57, 57, NULL, 'CERRADA', 44761.63, 45241.53, 45154.34, -87.19, 10.00, '2026-05-10 03:56:22', '2026-05-10 03:59:46', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(65, 5, 2, 53, 53, NULL, 'CERRADA', 36961.38, 38127.46, 38131.69, 4.23, 10.00, '2026-05-10 04:01:32', '2026-05-10 04:05:06', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(66, 3, 2, 17, 17, NULL, 'CERRADA', 25846.72, 25558.51, 25568.80, 10.29, 10.00, '2026-05-10 04:04:08', '2026-05-10 04:07:21', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(67, 4, 2, 71, 71, NULL, 'CERRADA', 39000.08, 39658.98, 39659.94, 0.96, 10.00, '2026-05-10 04:12:47', '2026-05-10 04:16:04', '2026-05-09', NULL, NULL, NULL, NULL, NULL, 1, 0),
(68, 3, 1, 17, 17, NULL, 'CERRADA', 25568.80, 23442.41, 23447.08, 4.67, 10.00, '2026-05-10 19:16:52', '2026-05-10 20:03:36', '2026-05-10', NULL, NULL, NULL, NULL, NULL, 1, 0),
(69, 2, 1, 69, 69, NULL, 'CERRADA', 45154.34, 45533.34, 45536.24, 2.90, 10.00, '2026-05-10 19:46:12', '2026-05-10 20:02:26', '2026-05-10', NULL, NULL, NULL, NULL, NULL, 1, 0),
(70, 5, 1, 53, 53, NULL, 'CERRADA', 38131.69, 39067.92, 39069.44, 1.52, 10.00, '2026-05-10 20:03:00', '2026-05-10 20:07:09', '2026-05-10', NULL, NULL, NULL, NULL, NULL, 1, 0),
(71, 4, 1, 11, 11, NULL, 'CERRADA', 39657.44, 40050.84, 40070.28, 19.44, 10.00, '2026-05-10 20:14:11', '2026-05-10 20:18:08', '2026-05-10', NULL, NULL, NULL, NULL, NULL, 1, 0);

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
(133, 71, 55, 'VENDEDORA', 0, NULL);

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
  `notas` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- AUTO_INCREMENT for table `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `asistencia_checklist`
--
ALTER TABLE `asistencia_checklist`
  MODIFY `id_asistencia_checklist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

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
  MODIFY `id_estudio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  MODIFY `id_slot` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

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
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id_nivel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pago_local`
--
ALTER TABLE `pago_local`
  MODIFY `id_pago_local` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pago_personal`
--
ALTER TABLE `pago_personal`
  MODIFY `id_pago_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `plantilla_horario`
--
ALTER TABLE `plantilla_horario`
  MODIFY `id_plantilla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `postulacion`
--
ALTER TABLE `postulacion`
  MODIFY `id_postulacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

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
  MODIFY `id_rectificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `reporte_venta`
--
ALTER TABLE `reporte_venta`
  MODIFY `id_reporte_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rol_horario`
--
ALTER TABLE `rol_horario`
  MODIFY `id_rol_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `semana`
--
ALTER TABLE `semana`
  MODIFY `id_semana` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sesion_caja`
--
ALTER TABLE `sesion_caja`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `sesion_participante`
--
ALTER TABLE `sesion_participante`
  MODIFY `id_sesion_participante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

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
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `transferencia_caja`
--
ALTER TABLE `transferencia_caja`
  MODIFY `id_transferencia` int(11) NOT NULL AUTO_INCREMENT;

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
