-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2025 at 04:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `soloboticas`
--

-- --------------------------------------------------------

--
-- Table structure for table `informacion_personal`
--

CREATE TABLE `informacion_personal` (
  `id` int(11) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `nombre_completo` varchar(255) DEFAULT NULL,
  `edad` int(3) DEFAULT NULL,
  `estudios` varchar(100) DEFAULT NULL,
  `estudios_estado` enum('Concluidos','En proceso') DEFAULT NULL,
  `titulo` tinyint(1) DEFAULT NULL,
  `lugar_origen` varchar(255) DEFAULT NULL,
  `numero_hijos` int(2) DEFAULT 0,
  `estado_civil` enum('Casado','Soltero') DEFAULT NULL,
  `tiempo_servicio` int(3) DEFAULT NULL,
  `lugar_nacimiento` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informacion_personal`
--

INSERT INTO `informacion_personal` (`id`, `nickname`, `nombre_completo`, `edad`, `estudios`, `estudios_estado`, `titulo`, `lugar_origen`, `numero_hijos`, `estado_civil`, `tiempo_servicio`, `lugar_nacimiento`, `fecha_nacimiento`) VALUES
(1, 'SOLANGECC', 'Solange Moulin, Coronel Camacllanqui', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(2, 'MILAGROSHC', 'Milagros Del Pilar, Huamán Cruzado', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(3, 'DARIANABC', 'Dariana, Bautista Contreras', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(4, 'PATRICIAOP', 'Patricia del Pilar, Obregon Pozo', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(5, 'MARYORIFU', 'Maryori, Flores Ubaldo', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(6, 'MARIBELSB', 'Maribel Rosario, Salazar Baldeon', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(7, 'MARIAGT', 'María Doris, García Torres', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(8, 'FLORMH', 'Flor de maria, Mercedes huayta', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(9, 'KARENME', 'Karen Lizbeth, Martinez Encina', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(10, 'FIORELLACR', 'Fiorella del Rosario, Chambi Rafaile', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(11, 'SHARIKRP', 'Sharik Sheylly, Rodriguez Pineda', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(12, 'MONICAQC', 'Monica, Quispe Ccallo', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(13, 'KARINRC', 'Karin Gianina, Ramirez Calixto', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(14, 'LEIDIPC', 'Leidi, Peralta Colunche', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(15, 'DIANAMH', 'Diana, Mendoza Huaman', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(16, 'GERALDINNEQA', 'Rocío Geraldinne, Quispe Alberco', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(17, 'GUILLERMINASB', 'GUILLERMINA YOMNIS, SANTOS BASILIO', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(18, 'ELIZABETHFS', 'Elizabeth, Flores Silva', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(19, 'MARINAHA', 'Marina, Heredia Acuña', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(20, 'ALEXANDERSC', 'Alexander Rafael, Suarez Chacón', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(21, 'YOLVIPF', 'Yolvi Romelia, Patricio Flores', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(22, 'INOEOQ', 'Inoe Ortiz Quispe, Inoe', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(23, 'SHEILAMC', 'Sheila, Marcos Chagua', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(24, 'ELENAPM', 'Elena Dayana, Peña Manrique', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(25, 'DILZAAM', 'DILZA ELIZABETH, ALARCON MUÑOZ', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(26, 'SHARONMA', 'Sharon Candy, Marcos Alfaro', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(27, 'MIRIAMAB', 'Miriam oriana, Aguirre borja', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(28, 'YENIFERQL', 'Yenifer Katia, Quispe Llacchua', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(29, 'LIZBETHQ', 'Lizbeth, Quispe de la cruz', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(30, 'YOSELINBS', 'Yoselin Margarita, Baldera Siesquén', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(31, 'GAVISA', 'Gavi, Santos ascencio', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(32, 'ROYVC', 'Roy Anthony, Vilcamiche Chavez', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(33, 'LORELISZ', 'Loreli Elizabeth, Salas Zuñiga', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(34, 'NAYELIBE', 'Nayeli, Benancio Espinoza', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(35, 'ROSARIOFE', 'Geraldine Rosario, Felices Escobar', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(36, 'ANALUFF', 'Analu, Fonseca Fernández', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(37, 'DELINAGM', 'DELINA, GUILLEN MATOS', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(38, 'LISSETBD', 'Lisset, Bonifacio Duran', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(39, 'ANACM', 'Ana Lucia, Coaquira Mamani', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(40, 'JHOVANISC', 'Jhovani, Suarez Cueva', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(41, 'CAROLACR', 'Carola liz, Carhuaricra reyes', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(42, 'LUISSG', 'Luis Daryl, Sanchez Garcia', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(43, 'CARMENGG', 'Carmen esmeralda, Guadalupe Galarza', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(44, 'ERIKAGH', 'Erika Yuliana, Guerrero Huerta', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(45, 'GIANCARLOVILCH', 'Gian Carlo, Vilcamiche Chávez', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nickname`, `password`) VALUES
(1, 'SOLANGECC', '$2y$10$ISMcEwHw3wNt0NY3WqbLL..41XzTslz1xXhMT6l556WoITFwOszdm'),
(2, 'GIANCARLOVILCH', '$2y$10$JAYlQVE356zEUNLSpppUYu8.Y7Io.A6HGvIIbhhOOBa.6Fhk31QqG'),
(3, 'DARIANABC', '$2y$10$bocr3kyqBnWX0htVQivjj.oeK6phGUbqEQHlXUvjBWLGaDnmgEfNa'),
(4, 'PATRICIAOP', '$2y$10$DG4/eG8Q9yRDiAbEEzPp2e5/RGoP1PnqDhgiwwf4BFLdVv10ZnSna'),
(5, 'MARYORIFU', '$2y$10$gG9qvj.PcvcQQaNuVoz1l.RF6XjgO1ukDbYy0r3rZQm8pjSZ4AWKG'),
(6, 'MARIBELSB', '$2y$10$TqsRvWj1q/NWErO4l3MXI.hhHoZxIcHz7NJnTFqg7FeM9Pz9tcW4i'),
(7, 'FLORMH', '$2y$10$sVBQTfsO0VzRBFvBpPUi2.UvSxt3T3qzxO8TePC7lbCySoVwch/ju'),
(8, 'KARENME', '$2y$10$cM0cLlwa8CQtnUC5s7jFDeXJfK3uV6oCq3w2JH4G3zBOf/6plFaWe'),
(9, 'FIORELLACR', '$2y$10$SbUsuHs5MP4J48VBN640PeBjAXKTvM3W7MDMuHkaTjn9t/NhBFK6W'),
(10, 'SHARIKRP', '$2y$10$B8elsJJL3nVs40uvs9vVdevjM6xdzf6VQj02Psm3jNnReT7r73ZhO'),
(11, 'DIANAMH', '$2y$10$XJg0foo8tZHqperB3D8Iqer2C3CswpNOrAyW1mEP90yVcyZn7Y5Na'),
(12, 'MONICAQC', '$2y$10$J71b9/Tc2FtG/jBhScq8QelUQKvPeslVL/bMfWalk2A5i54tkD1oi'),
(13, 'GERALDINNEQA', '$2y$10$QHO7s25y1ih4puTeuTFy6e6uMi/vVpq4X/82BMDZXhbXGEXndmGty'),
(14, 'YOLVIPF', '$2y$10$EBSi7R3KHdzsDgLNvnTroOb1xwCGp4ggSklAAxK19Jt5XE9EvLEPu'),
(15, 'SHARONMA', '$2y$10$hszjb1v9Pa3Rr30cGbUnyehQSaQgnhLQfeEw5k/.Ty3IpdPv9DCai'),
(16, 'YENIFERQL', '$2y$10$zA2R70H6bQhGyi3GGd4KXe0M4UwF62E22yP9cuDegdG/wE1v//a52'),
(17, 'ROYVC', '$2y$10$g/hs657mrcV0EgiNVWfFcevgOoLADSDb5KPpHtJ4fTSXzeF5aEubi'),
(18, 'NAYELIBE', '$2y$10$YvA2wQ.VjwCoVLN2HCEM.OL4LXE5MyoLrL7jngVnwWBCPsyY32nou'),
(19, 'JHOVANISC', '$2y$10$KPDUgU4.OODqxaT26UY6.u6vVK5i7oPv4IyRIEmB7LcvWAWRVV.BS'),
(20, 'CAROLACR', '$2y$10$JlcYOjxEWxswUsABU3iu5.cAzilfyQ42jTm2U8UKJGq7VKCBw901S'),
(21, 'ERIKAGH', '$2y$10$HxUEoIedk64J9Ed76DOJFuc.RIBeVV1nMHl3npOruHd0GILkTurEG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `informacion_personal`
--
ALTER TABLE `informacion_personal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nickname` (`nickname`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nickname` (`nickname`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `informacion_personal`
--
ALTER TABLE `informacion_personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_nickname` FOREIGN KEY (`nickname`) REFERENCES `informacion_personal` (`nickname`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
