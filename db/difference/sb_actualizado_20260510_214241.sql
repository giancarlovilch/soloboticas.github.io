-- MySQL dump 10.13  Distrib 8.0.39, for Win64 (x86_64)
--
-- Host: localhost    Database: sb
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencia` (
  `id_asistencia` int NOT NULL AUTO_INCREMENT,
  `postulante_id` int NOT NULL,
  `local_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_ingreso` datetime DEFAULT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `estado` enum('A TIEMPO','TARDE','FALTA','EXTRA','TEMPRANO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A TIEMPO',
  `justificacion` text COLLATE utf8mb4_unicode_ci,
  `observacion` enum('PROCEDE','NO PROCEDE','PENDIENTE') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_asistencia`),
  KEY `idx_asistencia_fecha` (`fecha`),
  KEY `idx_asistencia_local` (`local_id`),
  KEY `idx_asistencia_postulante` (`postulante_id`),
  CONSTRAINT `fk_asistencia_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`) ON DELETE SET NULL,
  CONSTRAINT `fk_asistencia_usuario` FOREIGN KEY (`postulante_id`) REFERENCES `usuario` (`postulante_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
INSERT INTO `asistencia` VALUES (5,1,2,'2026-05-04','2026-05-04 15:00:00','2026-05-04 21:07:00','A TIEMPO',NULL,'PENDIENTE'),(6,1,3,'2026-05-04','2026-05-04 21:08:21','2026-05-04 21:08:31','EXTRA',NULL,'PENDIENTE'),(9,11,4,'2026-05-06','2026-05-06 09:32:54',NULL,'EXTRA',NULL,'PENDIENTE'),(10,4,NULL,'2026-05-06','2026-05-06 09:45:34',NULL,'EXTRA',NULL,'PENDIENTE'),(11,5,NULL,'2026-05-06','2026-05-06 10:40:11','2026-05-06 23:06:29','EXTRA',NULL,'PENDIENTE'),(12,56,2,'2026-05-06','2026-05-06 10:40:18',NULL,'EXTRA',NULL,'PENDIENTE'),(13,55,4,'2026-05-06','2026-05-06 11:40:10',NULL,'EXTRA',NULL,'PENDIENTE'),(14,53,3,'2026-05-06','2026-05-06 13:36:35',NULL,'EXTRA',NULL,'PENDIENTE'),(15,71,NULL,'2026-05-06','2026-05-06 16:05:12',NULL,'EXTRA',NULL,'PENDIENTE'),(16,22,3,'2026-05-07','2026-05-07 10:20:10',NULL,'EXTRA',NULL,'PENDIENTE'),(17,22,NULL,'2026-05-08','2026-05-08 07:19:10',NULL,'TARDE',NULL,'PENDIENTE'),(18,70,3,'2026-05-08','2026-05-08 07:20:31',NULL,'TARDE',NULL,'PENDIENTE'),(19,53,3,'2026-05-08','2026-05-08 16:12:31',NULL,'EXTRA',NULL,'PENDIENTE'),(20,70,3,'2026-05-09','2026-05-09 07:05:24',NULL,'A TIEMPO',NULL,'PENDIENTE'),(21,53,3,'2026-05-09','2026-05-09 15:31:02',NULL,'TARDE',NULL,'PENDIENTE'),(22,60,3,'2026-05-09','2026-05-09 15:34:31',NULL,'TARDE',NULL,'PENDIENTE'),(23,53,3,'2026-05-10','2026-05-10 07:26:17',NULL,'TARDE',NULL,'PENDIENTE'),(24,60,3,'2026-05-10','2026-05-10 14:59:30',NULL,'TEMPRANO',NULL,'PENDIENTE');
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencia_checklist`
--

DROP TABLE IF EXISTS `asistencia_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencia_checklist` (
  `id_asistencia_checklist` int NOT NULL AUTO_INCREMENT,
  `asistencia_id` int NOT NULL,
  `checklist_id` int NOT NULL,
  `cumplido` tinyint(1) NOT NULL DEFAULT '0',
  `observacion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_asistencia_checklist`),
  KEY `idx_ac_checklist` (`checklist_id`),
  KEY `idx_ac_asistencia` (`asistencia_id`),
  CONSTRAINT `fk_ac_asistencia` FOREIGN KEY (`asistencia_id`) REFERENCES `asistencia` (`id_asistencia`),
  CONSTRAINT `fk_ac_checklist` FOREIGN KEY (`checklist_id`) REFERENCES `checklist` (`id_checklist`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia_checklist`
--

LOCK TABLES `asistencia_checklist` WRITE;
/*!40000 ALTER TABLE `asistencia_checklist` DISABLE KEYS */;
INSERT INTO `asistencia_checklist` VALUES (11,5,1,1,NULL),(12,5,2,1,NULL),(13,5,3,1,NULL),(14,5,4,1,NULL),(15,5,5,1,NULL),(16,6,1,1,NULL),(17,6,2,1,NULL),(18,6,3,1,NULL),(19,6,4,1,NULL),(20,6,5,1,NULL),(31,9,1,1,NULL),(32,9,2,1,NULL),(33,9,3,1,NULL),(34,9,4,1,NULL),(35,9,5,1,NULL),(36,10,1,1,NULL),(37,10,2,1,NULL),(38,10,3,1,NULL),(39,10,4,1,NULL),(40,10,5,1,NULL),(41,11,1,1,NULL),(42,11,2,1,NULL),(43,11,3,1,NULL),(44,11,4,1,NULL),(45,11,5,1,NULL),(46,12,1,1,NULL),(47,12,2,1,NULL),(48,12,3,1,NULL),(49,12,4,1,NULL),(50,12,5,1,NULL),(51,13,1,1,NULL),(52,13,2,1,NULL),(53,13,3,1,NULL),(54,13,4,1,NULL),(55,13,5,1,NULL),(56,14,1,1,NULL),(57,14,2,1,NULL),(58,14,3,1,NULL),(59,14,4,1,NULL),(60,14,5,1,NULL),(61,15,1,1,NULL),(62,15,2,1,NULL),(63,15,3,1,NULL),(64,15,4,1,NULL),(65,15,5,1,NULL),(66,16,1,1,NULL),(67,16,2,1,NULL),(68,16,3,1,NULL),(69,16,4,1,NULL),(70,16,5,1,NULL),(71,17,1,1,NULL),(72,17,2,1,NULL),(73,17,3,1,NULL),(74,17,4,1,NULL),(75,17,5,1,NULL),(76,18,1,1,NULL),(77,18,2,1,NULL),(78,18,3,1,NULL),(79,18,4,1,NULL),(80,18,5,1,NULL),(81,19,1,1,NULL),(82,19,2,1,NULL),(83,19,3,1,NULL),(84,19,4,1,NULL),(85,19,5,1,NULL),(86,20,1,1,NULL),(87,20,2,1,NULL),(88,20,3,1,NULL),(89,20,4,1,NULL),(90,20,5,1,NULL),(91,21,1,1,NULL),(92,21,2,1,NULL),(93,21,3,1,NULL),(94,21,4,1,NULL),(95,21,5,1,NULL),(96,22,1,1,NULL),(97,22,2,1,NULL),(98,22,3,1,NULL),(99,22,4,1,NULL),(100,22,5,1,NULL),(101,23,1,1,NULL),(102,23,2,1,NULL),(103,23,3,1,NULL),(104,23,4,1,NULL),(105,23,5,1,NULL),(106,24,1,1,NULL),(107,24,2,1,NULL),(108,24,3,1,NULL),(109,24,4,1,NULL),(110,24,5,1,NULL);
/*!40000 ALTER TABLE `asistencia_checklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_cuadre`
--

DROP TABLE IF EXISTS `auditoria_cuadre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_cuadre` (
  `id_auditoria` int NOT NULL AUTO_INCREMENT,
  `sesion_id` int NOT NULL,
  `postulante_id` int NOT NULL,
  `accion` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `campo_modificado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_anterior` text COLLATE utf8mb4_unicode_ci,
  `valor_nuevo` text COLLATE utf8mb4_unicode_ci,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_aud_cuadre_sesion` (`sesion_id`),
  KEY `idx_aud_cuadre_postulante` (`postulante_id`),
  CONSTRAINT `fk_aud_cuadre_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_aud_cuadre_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_cuadre`
--

LOCK TABLES `auditoria_cuadre` WRITE;
/*!40000 ALTER TABLE `auditoria_cuadre` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria_cuadre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_sistema`
--

DROP TABLE IF EXISTS `auditoria_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_sistema` (
  `id_auditoria` int NOT NULL AUTO_INCREMENT,
  `postulante_id` int NOT NULL,
  `tabla_afectada` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_registro` int DEFAULT NULL,
  `accion` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_as_postulante` (`postulante_id`),
  CONSTRAINT `fk_as_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_sistema`
--

LOCK TABLES `auditoria_sistema` WRITE;
/*!40000 ALTER TABLE `auditoria_sistema` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caja`
--

DROP TABLE IF EXISTS `caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caja` (
  `id_caja` int NOT NULL AUTO_INCREMENT,
  `local_id` int NOT NULL,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_caja`),
  UNIQUE KEY `uq_caja_local_desc` (`local_id`,`descripcion`),
  CONSTRAINT `fk_caja_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caja`
--

LOCK TABLES `caja` WRITE;
/*!40000 ALTER TABLE `caja` DISABLE KEYS */;
INSERT INTO `caja` VALUES (2,2,'SB2',1),(3,3,'SB3',1),(4,4,'SB4',1),(5,3,'SB5',1),(6,2,'SB6',1),(7,3,'SB7',1);
/*!40000 ALTER TABLE `caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklist`
--

DROP TABLE IF EXISTS `checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklist` (
  `id_checklist` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('APERTURA','CIERRE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_checklist`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklist`
--

LOCK TABLES `checklist` WRITE;
/*!40000 ALTER TABLE `checklist` DISABLE KEYS */;
INSERT INTO `checklist` VALUES (1,'Llegó a tiempo','APERTURA',1),(2,'Aseo personal conforme (bañado)','APERTURA',1),(3,'Chaqueta limpia y planchada','APERTURA',1),(4,'Uñas cortas y limpias','APERTURA',1),(5,'Cabello recogido','APERTURA',1),(6,'Hizo limpieza','CIERRE',1);
/*!40000 ALTER TABLE `checklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `concepto_gastos_local`
--

DROP TABLE IF EXISTS `concepto_gastos_local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `concepto_gastos_local` (
  `id_concepto` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_concepto`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `concepto_gastos_local`
--

LOCK TABLES `concepto_gastos_local` WRITE;
/*!40000 ALTER TABLE `concepto_gastos_local` DISABLE KEYS */;
INSERT INTO `concepto_gastos_local` VALUES (1,'Alquiler',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(2,'Agua',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(3,'Luz',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(4,'Internet',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(5,'Mantenimiento / Limpieza',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(6,'Arbitrios / Municipalidad',1,'2026-05-04 20:58:12','2026-05-04 20:58:12');
/*!40000 ALTER TABLE `concepto_gastos_local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `concepto_penalidad`
--

DROP TABLE IF EXISTS `concepto_penalidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `concepto_penalidad` (
  `id_concepto` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('PENALIDAD','BENEFICIO','TARIFA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(8,2) NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `notas` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_concepto`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `concepto_penalidad`
--

LOCK TABLES `concepto_penalidad` WRITE;
/*!40000 ALTER TABLE `concepto_penalidad` DISABLE KEYS */;
INSERT INTO `concepto_penalidad` VALUES (1,'PENALIDAD','Ausencia injustificada al turno',-30.00,1,'Descuento al trabajador que no se presentó a su turno asignado'),(2,'BENEFICIO','Bono por cobertura de turno ausente',20.00,1,'Reconocimiento económico al trabajador que cubre el turno'),(3,'TARIFA','Comisión empresa por cobertura',10.00,1,'Diferencia que retiene la empresa del descuento aplicado'),(4,'TARIFA','Costo de cambio de horario voluntario',-10.00,1,'Descuento al trabajador que solicita cambio de posición');
/*!40000 ALTER TABLE `concepto_penalidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacto_emergencia`
--

DROP TABLE IF EXISTS `contacto_emergencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacto_emergencia` (
  `id_contacto_emergencia` int NOT NULL AUTO_INCREMENT,
  `postulante_id` int NOT NULL,
  `nombre_completo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parentesco` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contacto_emergencia`),
  KEY `idx_ce_postulante` (`postulante_id`),
  CONSTRAINT `fk_ce_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacto_emergencia`
--

LOCK TABLES `contacto_emergencia` WRITE;
/*!40000 ALTER TABLE `contacto_emergencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacto_emergencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_cuadre`
--

DROP TABLE IF EXISTS `detalle_cuadre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_cuadre` (
  `sesion_id` int NOT NULL,
  `monto_caja_exterior` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_monedas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_billetes_caja` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_billetes_caja_fuerte` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_yape_plin` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_visas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_bcp` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_agente_bcp` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_efectivo_contado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_contado_general` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_ventas_sistema` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_gastos_sistema` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_esperado_sistema` decimal(10,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(10,2) NOT NULL DEFAULT '0.00',
  `resultado_cuadre` enum('CONSISTENTE','SOBRANTE','FALTANTE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion_cierre` text COLLATE utf8mb4_unicode_ci,
  `saldo_proxima_efectivo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `saldo_proxima_agente_bcp` decimal(10,2) NOT NULL DEFAULT '0.00',
  `saldo_proximo_dia` decimal(10,2) NOT NULL DEFAULT '0.00',
  `num_operaciones_bcp` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`sesion_id`),
  CONSTRAINT `fk_dc_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_cuadre`
--

LOCK TABLES `detalle_cuadre` WRITE;
/*!40000 ALTER TABLE `detalle_cuadre` DISABLE KEYS */;
INSERT INTO `detalle_cuadre` VALUES (25,0.00,0.00,0.00,0.00,0.00,0.00,0.00,42817.46,42817.46,42817.46,0.00,0.00,0.00,42817.46,'SOBRANTE',NULL,42817.46,0.00,42817.46,0),(27,1938.60,1135.00,27100.00,810.00,0.00,0.00,0.00,12378.43,43362.03,43362.03,989.10,0.00,43360.86,1.17,'SOBRANTE',NULL,43362.03,0.00,43362.03,0),(28,948.00,1035.00,500.00,28560.00,0.00,0.00,0.00,12518.33,43561.33,43561.33,341.40,0.00,43560.73,0.60,'SOBRANTE',NULL,43561.33,0.00,43561.33,219),(29,0.00,0.00,0.00,0.00,0.00,0.00,0.00,27818.14,27818.14,27818.14,0.00,0.00,0.00,27818.14,'SOBRANTE',NULL,27818.14,0.00,27818.14,0),(30,167.70,290.00,13200.00,0.00,0.00,0.00,0.00,12721.28,26378.98,26378.98,1479.55,0.00,26358.79,20.19,'SOBRANTE',NULL,26378.98,0.00,26378.98,0),(31,1513.30,190.00,7480.00,0.00,0.00,0.00,0.00,17265.08,26448.38,26448.38,784.60,3.01,26539.47,-91.09,'FALTANTE',NULL,26538.38,0.00,26538.38,228),(32,0.00,0.00,0.00,0.00,0.00,0.00,0.00,48905.89,48905.89,48905.89,0.00,0.00,0.00,48905.89,'SOBRANTE',NULL,48905.89,0.00,48905.89,0),(33,464.90,1360.00,40910.00,0.00,0.00,0.00,0.00,8511.83,51246.73,51246.73,2334.99,0.00,51230.88,15.85,'SOBRANTE',NULL,51246.73,0.00,51246.73,0),(35,615.50,1360.00,17510.00,0.00,0.00,0.00,0.00,21841.45,41326.95,41326.95,1117.35,11040.01,41324.07,2.88,'SOBRANTE',NULL,41326.95,0.00,41326.95,0),(37,0.00,0.00,0.00,0.00,0.00,0.00,0.00,35751.00,35751.00,35751.00,0.00,0.00,0.00,35751.00,'SOBRANTE',NULL,35751.00,0.00,35751.00,0),(38,151.00,1110.00,50.00,11750.00,0.00,0.00,0.00,23564.28,36625.28,36625.28,1467.28,0.00,36605.48,19.80,'SOBRANTE',NULL,36625.28,0.00,36625.28,0),(40,89.40,1110.00,540.00,13300.00,0.00,0.00,0.00,22143.63,37183.03,37183.03,728.30,0.00,37201.98,-18.95,'FALTANTE',NULL,37183.03,0.00,37183.03,121),(42,308.00,180.00,14380.00,0.00,0.00,0.00,0.00,11844.88,26712.88,26712.88,1263.99,80.50,26750.07,-37.19,'FALTANTE',NULL,26712.88,0.00,26712.88,211),(44,455.10,1460.00,33310.00,0.00,0.00,0.00,0.00,7517.65,42742.75,42742.75,1460.30,37.00,42750.25,-7.50,'FALTANTE',NULL,42742.75,0.00,42742.75,189),(47,353.60,450.00,12700.00,0.00,0.00,0.00,0.00,13401.86,26905.46,26905.46,801.50,42.00,26906.28,-0.82,'FALTANTE',NULL,26905.46,0.00,26905.46,290),(48,290.30,1035.00,330.00,36860.00,0.00,0.00,0.00,5296.76,43812.06,43812.06,521.24,0.00,43800.47,11.59,'SOBRANTE',NULL,43812.47,0.00,43812.47,220),(50,1023.90,1010.00,0.00,15600.00,0.00,0.00,0.00,20035.60,37669.50,37669.50,783.40,0.00,37668.33,1.17,'SOBRANTE',NULL,37669.50,0.00,37669.50,156),(51,102.80,910.00,650.00,11100.00,0.00,0.00,0.00,25239.90,38002.70,38002.70,546.70,0.00,37996.10,6.60,'SOBRANTE',NULL,37996.10,0.00,37996.10,117),(52,612.00,1260.00,12000.00,0.00,0.00,0.00,0.00,20028.22,33900.22,33900.22,1280.50,10000.00,34023.25,-123.03,'FALTANTE',NULL,34020.22,0.00,34020.22,0),(53,370.70,1135.00,1770.00,8400.00,0.00,0.00,0.00,32377.12,44052.82,44052.82,291.40,0.00,44056.77,-3.95,'FALTANTE',NULL,44052.82,0.00,44052.82,206),(55,86.30,470.00,160.00,17600.00,0.00,0.00,0.00,7802.18,26118.48,26118.48,1269.49,1087.00,26106.95,11.53,'SOBRANTE',NULL,26118.48,0.00,26118.48,181),(57,152.60,720.00,950.00,18100.00,0.00,0.00,0.00,18765.30,38687.90,38687.90,945.40,0.00,38667.70,20.20,'SOBRANTE',NULL,38687.90,0.00,38687.90,171),(58,583.00,1160.00,27470.00,0.00,0.00,0.00,0.00,6143.52,35356.52,35356.52,1412.29,74.00,34852.11,504.41,'SOBRANTE',NULL,35356.52,0.00,35356.52,198),(59,216.40,1145.00,840.00,19920.00,0.00,0.00,0.00,22298.51,44419.91,44419.91,571.90,0.00,44438.62,-18.71,'FALTANTE',NULL,44419.91,0.00,44419.91,189),(60,1617.20,1060.00,21370.00,0.00,0.00,0.00,0.00,12914.18,36961.38,36961.38,1583.45,0.00,36939.97,21.41,'SOBRANTE',NULL,36961.38,0.00,36961.38,0),(61,709.20,200.00,15620.00,0.00,0.00,0.00,0.00,9317.52,25846.72,25846.72,1323.20,649.99,25867.04,-20.32,'FALTANTE',NULL,25846.72,0.00,25846.72,203),(62,504.30,1145.00,15220.00,0.00,0.00,0.00,0.00,27892.33,44761.63,44761.63,339.40,90.20,44669.11,92.52,'SOBRANTE',NULL,44761.63,0.00,44761.63,276),(63,76.60,720.00,1180.00,18500.00,0.00,0.00,0.00,18523.48,39000.08,39000.08,504.00,0.00,39003.80,-3.72,'FALTANTE',NULL,39000.08,0.00,39000.08,162),(64,679.00,1145.00,23120.00,0.00,0.00,0.00,0.00,20210.34,45154.34,45154.34,588.60,2.60,45241.53,-87.19,'FALTANTE',NULL,45154.34,0.00,45154.34,258),(65,546.90,950.00,17400.00,0.00,0.00,0.00,0.00,19234.79,38131.69,38131.69,1242.68,76.60,38127.46,4.23,'SOBRANTE',NULL,38131.69,0.00,38131.69,253),(66,315.60,200.00,22630.00,0.00,0.00,0.00,0.00,2423.20,25568.80,25568.80,1104.59,532.00,25558.51,10.29,'SOBRANTE',NULL,25568.80,0.00,25568.80,260),(67,286.40,620.00,4970.00,15200.00,0.00,0.00,0.00,18583.54,39659.94,39659.94,1073.40,2.50,39658.98,0.96,'SOBRANTE',NULL,39657.44,0.00,39657.44,175),(68,1799.80,200.00,11540.00,0.00,0.00,0.00,0.00,9907.28,23447.08,23447.08,1012.20,2721.49,23442.41,4.67,'SOBRANTE',NULL,23447.08,0.00,23447.08,319),(69,229.10,1245.00,1220.00,16130.00,0.00,0.00,0.00,26712.14,45536.24,45536.24,545.50,0.00,45533.34,2.90,'SOBRANTE',NULL,45536.24,0.00,45536.24,339),(70,538.50,840.00,14900.00,0.00,0.00,0.00,0.00,22790.94,39069.44,39069.44,1128.43,79.80,39067.92,1.52,'SOBRANTE',NULL,39069.44,0.00,39069.44,332),(71,183.80,620.00,1010.00,11700.00,0.00,0.00,0.00,26556.48,40070.28,40070.28,756.30,0.00,40050.84,19.44,'SOBRANTE',NULL,40070.28,0.00,40070.28,185);
/*!40000 ALTER TABLE `detalle_cuadre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especialidad`
--

DROP TABLE IF EXISTS `especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especialidad` (
  `id_especialidad` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_especialidad`),
  UNIQUE KEY `uq_especialidad_desc` (`descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especialidad`
--

LOCK TABLES `especialidad` WRITE;
/*!40000 ALTER TABLE `especialidad` DISABLE KEYS */;
/*!40000 ALTER TABLE `especialidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado`
--

DROP TABLE IF EXISTS `estado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado` (
  `id_estado` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estado`),
  UNIQUE KEY `uq_estado_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado`
--

LOCK TABLES `estado` WRITE;
/*!40000 ALTER TABLE `estado` DISABLE KEYS */;
INSERT INTO `estado` VALUES (1,'Egreso',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'En curso',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Titulado',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(4,'Trunco',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `estado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudio`
--

DROP TABLE IF EXISTS `estudio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estudio` (
  `id_estudio` int NOT NULL AUTO_INCREMENT,
  `postulante_id` int NOT NULL,
  `tipo_id` int NOT NULL,
  `institucion_id` int NOT NULL,
  `estado_id` int NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_estudio`),
  UNIQUE KEY `uq_estudio` (`postulante_id`,`tipo_id`,`institucion_id`,`fecha_inicio`),
  KEY `idx_estudio_tipo` (`tipo_id`),
  KEY `idx_estudio_estado` (`estado_id`),
  KEY `idx_estudio_institucion` (`institucion_id`),
  CONSTRAINT `fk_estudio_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id_estado`),
  CONSTRAINT `fk_estudio_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `institucion` (`id_institucion`),
  CONSTRAINT `fk_estudio_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_estudio_tipo` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_estudio` (`id_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudio`
--

LOCK TABLES `estudio` WRITE;
/*!40000 ALTER TABLE `estudio` DISABLE KEYS */;
INSERT INTO `estudio` VALUES (8,11,2,4,3,'2020-01-01',NULL),(12,57,3,5,4,'2006-01-16','2018-10-31'),(15,55,2,4,3,'2022-04-15','2025-01-17'),(18,68,3,5,3,'2026-05-06',NULL),(21,69,2,4,1,'2022-11-06','2025-11-06'),(25,70,2,4,2,'2023-05-06','2026-05-06'),(27,71,2,5,3,'2019-05-06','2022-05-06'),(29,72,3,5,4,'1985-01-01',NULL),(34,73,2,5,2,'2000-01-01',NULL);
/*!40000 ALTER TABLE `estudio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `etapa`
--

DROP TABLE IF EXISTS `etapa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etapa` (
  `id_etapa` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etapa`),
  UNIQUE KEY `uq_etapa_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `etapa`
--

LOCK TABLES `etapa` WRITE;
/*!40000 ALTER TABLE `etapa` DISABLE KEYS */;
INSERT INTO `etapa` VALUES (1,'Pendiente',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'Entrevista',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Rechazado',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(4,'Contratado',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(5,'Suspendido',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(6,'Despedido',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `etapa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `experiencia`
--

DROP TABLE IF EXISTS `experiencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `experiencia` (
  `id_experiencia` int NOT NULL AUTO_INCREMENT,
  `postulante_id` int DEFAULT NULL,
  `empresa` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `funciones` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_experiencia`),
  KEY `idx_exp_postulante` (`postulante_id`),
  CONSTRAINT `fk_experiencia_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `experiencia`
--

LOCK TABLES `experiencia` WRITE;
/*!40000 ALTER TABLE `experiencia` DISABLE KEYS */;
INSERT INTO `experiencia` VALUES (4,57,'Supermercado wong','Supervisora de cajas',NULL,'2012-06-01','2014-01-31'),(7,55,'Mifarma','Técnico',NULL,'2023-02-27','2026-01-30'),(9,72,'Boticas','Administradora',NULL,'2000-01-01','2011-01-01');
/*!40000 ALTER TABLE `experiencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genero`
--

DROP TABLE IF EXISTS `genero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genero` (
  `id_genero` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_genero`),
  UNIQUE KEY `uq_genero_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genero`
--

LOCK TABLES `genero` WRITE;
/*!40000 ALTER TABLE `genero` DISABLE KEYS */;
INSERT INTO `genero` VALUES (1,'Masculino',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'Femenino',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Otro',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `genero` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario_slot`
--

DROP TABLE IF EXISTS `horario_slot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario_slot` (
  `id_slot` int NOT NULL AUTO_INCREMENT,
  `semana_id` int NOT NULL,
  `local_id` int NOT NULL,
  `turno_id` int NOT NULL,
  `fecha_dia` date NOT NULL,
  `rol_horario_id` int NOT NULL,
  `slot_num` tinyint NOT NULL DEFAULT '1',
  `postulante_id` int DEFAULT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_slot`),
  UNIQUE KEY `uq_hslot` (`semana_id`,`local_id`,`turno_id`,`fecha_dia`,`rol_horario_id`,`slot_num`),
  KEY `fk_hslot_local` (`local_id`),
  KEY `fk_hslot_turno` (`turno_id`),
  KEY `fk_hslot_postulante` (`postulante_id`),
  KEY `fk_hs_rol` (`rol_horario_id`),
  CONSTRAINT `fk_hs_rol` FOREIGN KEY (`rol_horario_id`) REFERENCES `rol_horario` (`id_rol_horario`),
  CONSTRAINT `fk_hslot_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  CONSTRAINT `fk_hslot_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE SET NULL,
  CONSTRAINT `fk_hslot_semana` FOREIGN KEY (`semana_id`) REFERENCES `semana` (`id_semana`) ON DELETE CASCADE,
  CONSTRAINT `fk_hslot_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario_slot`
--

LOCK TABLES `horario_slot` WRITE;
/*!40000 ALTER TABLE `horario_slot` DISABLE KEYS */;
INSERT INTO `horario_slot` VALUES (1,1,2,1,'2026-05-11',1,1,29,'2026-05-06 16:31:13'),(2,1,2,1,'2026-05-11',2,1,13,'2026-05-07 01:40:34'),(3,1,2,1,'2026-05-11',3,1,NULL,NULL),(4,1,2,2,'2026-05-11',1,1,29,'2026-05-06 16:31:06'),(5,1,2,2,'2026-05-11',2,1,56,'2026-05-07 03:29:21'),(6,1,2,2,'2026-05-11',3,1,NULL,NULL),(7,1,3,1,'2026-05-11',1,1,22,'2026-05-06 13:49:10'),(8,1,3,1,'2026-05-11',1,2,69,'2026-05-06 18:13:20'),(9,1,3,1,'2026-05-11',1,3,57,'2026-05-06 17:07:47'),(10,1,3,1,'2026-05-11',2,1,4,'2026-05-06 15:46:40'),(11,1,3,1,'2026-05-11',2,2,45,'2026-05-06 15:51:42'),(12,1,3,1,'2026-05-11',3,1,5,'2026-05-06 15:41:03'),(13,1,3,1,'2026-05-11',3,2,NULL,NULL),(14,1,3,2,'2026-05-11',1,1,53,'2026-05-06 16:22:39'),(15,1,3,2,'2026-05-11',1,2,61,'2026-05-06 19:38:10'),(16,1,3,2,'2026-05-11',1,3,57,'2026-05-06 17:20:52'),(17,1,3,2,'2026-05-11',2,1,10,'2026-05-06 19:35:18'),(18,1,3,2,'2026-05-11',2,2,54,'2026-05-06 16:24:22'),(19,1,3,2,'2026-05-11',3,1,5,'2026-05-06 15:41:15'),(20,1,3,2,'2026-05-11',3,2,60,'2026-05-09 20:35:04'),(21,1,4,1,'2026-05-11',1,1,73,'2026-05-07 02:07:21'),(22,1,4,1,'2026-05-11',2,1,55,'2026-05-06 16:46:08'),(23,1,4,1,'2026-05-11',3,1,NULL,NULL),(24,1,4,2,'2026-05-11',1,1,71,'2026-05-06 21:06:40'),(25,1,4,2,'2026-05-11',2,1,11,'2026-05-06 13:36:45'),(26,1,4,2,'2026-05-11',3,1,NULL,NULL),(27,1,2,1,'2026-05-12',1,1,13,'2026-05-07 02:02:44'),(28,1,2,1,'2026-05-12',2,1,45,'2026-05-06 15:50:04'),(29,1,2,1,'2026-05-12',3,1,54,'2026-05-08 20:44:45'),(30,1,2,2,'2026-05-12',1,1,69,'2026-05-06 18:13:09'),(31,1,2,2,'2026-05-12',2,1,56,'2026-05-06 15:49:15'),(32,1,2,2,'2026-05-12',3,1,NULL,NULL),(33,1,3,1,'2026-05-12',1,1,22,'2026-05-06 13:49:12'),(34,1,3,1,'2026-05-12',1,2,70,'2026-05-06 21:15:32'),(35,1,3,1,'2026-05-12',1,3,57,'2026-05-06 17:07:48'),(36,1,3,1,'2026-05-12',2,1,4,'2026-05-06 14:47:27'),(37,1,3,1,'2026-05-12',2,2,10,'2026-05-06 16:20:36'),(38,1,3,1,'2026-05-12',3,1,5,'2026-05-06 15:41:05'),(39,1,3,1,'2026-05-12',3,2,NULL,NULL),(40,1,3,2,'2026-05-12',1,1,53,'2026-05-06 16:22:40'),(41,1,3,2,'2026-05-12',1,2,61,'2026-05-06 19:38:10'),(42,1,3,2,'2026-05-12',1,3,17,'2026-05-06 16:37:32'),(43,1,3,2,'2026-05-12',2,1,4,'2026-05-06 14:58:35'),(44,1,3,2,'2026-05-12',2,2,10,'2026-05-06 16:20:37'),(45,1,3,2,'2026-05-12',3,1,5,'2026-05-06 15:41:15'),(46,1,3,2,'2026-05-12',3,2,60,'2026-05-09 20:35:24'),(47,1,4,1,'2026-05-12',1,1,73,'2026-05-07 02:07:21'),(48,1,4,1,'2026-05-12',2,1,55,'2026-05-06 16:48:36'),(49,1,4,1,'2026-05-12',3,1,NULL,NULL),(50,1,4,2,'2026-05-12',1,1,71,'2026-05-06 21:06:45'),(51,1,4,2,'2026-05-12',2,1,11,'2026-05-06 13:40:25'),(52,1,4,2,'2026-05-12',3,1,NULL,NULL),(53,1,2,1,'2026-05-13',1,1,29,'2026-05-08 16:55:11'),(54,1,2,1,'2026-05-13',2,1,13,'2026-05-07 01:40:34'),(55,1,2,1,'2026-05-13',3,1,NULL,NULL),(56,1,2,2,'2026-05-13',1,1,29,'2026-05-06 16:30:25'),(57,1,2,2,'2026-05-13',2,1,56,'2026-05-06 15:48:33'),(58,1,2,2,'2026-05-13',3,1,NULL,NULL),(59,1,3,1,'2026-05-13',1,1,22,'2026-05-06 13:49:13'),(60,1,3,1,'2026-05-13',1,2,70,'2026-05-06 21:21:09'),(61,1,3,1,'2026-05-13',1,3,53,'2026-05-06 16:36:06'),(62,1,3,1,'2026-05-13',2,1,10,'2026-05-06 16:20:45'),(63,1,3,1,'2026-05-13',2,2,4,'2026-05-06 16:37:55'),(64,1,3,1,'2026-05-13',3,1,5,'2026-05-06 15:41:06'),(65,1,3,1,'2026-05-13',3,2,NULL,NULL),(66,1,3,2,'2026-05-13',1,1,69,'2026-05-06 18:19:23'),(67,1,3,2,'2026-05-13',1,2,61,'2026-05-06 18:30:05'),(68,1,3,2,'2026-05-13',1,3,17,'2026-05-06 16:37:33'),(69,1,3,2,'2026-05-13',2,1,70,'2026-05-06 21:09:35'),(70,1,3,2,'2026-05-13',2,2,10,'2026-05-06 16:25:23'),(71,1,3,2,'2026-05-13',3,1,5,'2026-05-06 15:41:16'),(72,1,3,2,'2026-05-13',3,2,60,'2026-05-09 20:35:27'),(73,1,4,1,'2026-05-13',1,1,11,'2026-05-06 13:41:02'),(74,1,4,1,'2026-05-13',2,1,55,'2026-05-06 16:46:12'),(75,1,4,1,'2026-05-13',3,1,NULL,NULL),(76,1,4,2,'2026-05-13',1,1,71,'2026-05-06 21:07:12'),(77,1,4,2,'2026-05-13',2,1,11,'2026-05-06 13:40:43'),(78,1,4,2,'2026-05-13',3,1,NULL,NULL),(79,1,2,1,'2026-05-14',1,1,29,'2026-05-06 16:29:00'),(80,1,2,1,'2026-05-14',2,1,45,'2026-05-06 15:51:53'),(81,1,2,1,'2026-05-14',3,1,NULL,NULL),(82,1,2,2,'2026-05-14',1,1,69,'2026-05-06 18:13:11'),(83,1,2,2,'2026-05-14',2,1,56,'2026-05-06 15:49:21'),(84,1,2,2,'2026-05-14',3,1,NULL,NULL),(85,1,3,1,'2026-05-14',1,1,22,'2026-05-06 13:49:15'),(86,1,3,1,'2026-05-14',1,2,57,'2026-05-06 21:03:34'),(87,1,3,1,'2026-05-14',1,3,17,'2026-05-06 16:41:15'),(88,1,3,1,'2026-05-14',2,1,4,'2026-05-06 06:35:50'),(89,1,3,1,'2026-05-14',2,2,54,'2026-05-06 16:23:46'),(90,1,3,1,'2026-05-14',3,1,5,'2026-05-06 15:41:07'),(91,1,3,1,'2026-05-14',3,2,NULL,NULL),(92,1,3,2,'2026-05-14',1,1,70,'2026-05-06 21:14:03'),(93,1,3,2,'2026-05-14',1,2,61,'2026-05-06 19:41:11'),(94,1,3,2,'2026-05-14',1,3,57,'2026-05-06 17:08:15'),(95,1,3,2,'2026-05-14',2,1,4,'2026-05-06 14:59:53'),(96,1,3,2,'2026-05-14',2,2,54,'2026-05-06 16:23:52'),(97,1,3,2,'2026-05-14',3,1,5,'2026-05-06 15:41:17'),(98,1,3,2,'2026-05-14',3,2,60,'2026-05-09 20:35:27'),(99,1,4,1,'2026-05-14',1,1,13,'2026-05-07 03:27:49'),(100,1,4,1,'2026-05-14',2,1,11,'2026-05-06 13:40:49'),(101,1,4,1,'2026-05-14',3,1,NULL,NULL),(102,1,4,2,'2026-05-14',1,1,71,'2026-05-06 21:07:13'),(103,1,4,2,'2026-05-14',2,1,55,'2026-05-06 16:46:52'),(104,1,4,2,'2026-05-14',3,1,NULL,NULL),(105,1,2,1,'2026-05-15',1,1,29,'2026-05-06 16:30:56'),(106,1,2,1,'2026-05-15',2,1,13,'2026-05-07 01:41:00'),(107,1,2,1,'2026-05-15',3,1,NULL,NULL),(108,1,2,2,'2026-05-15',1,1,29,'2026-05-06 16:30:59'),(109,1,2,2,'2026-05-15',2,1,13,'2026-05-07 03:07:47'),(110,1,2,2,'2026-05-15',3,1,NULL,NULL),(111,1,3,1,'2026-05-15',1,1,22,'2026-05-06 13:49:17'),(112,1,3,1,'2026-05-15',1,2,57,'2026-05-07 00:39:35'),(113,1,3,1,'2026-05-15',1,3,17,'2026-05-06 16:41:50'),(114,1,3,1,'2026-05-15',2,1,54,'2026-05-08 18:09:31'),(115,1,3,1,'2026-05-15',2,2,45,'2026-05-06 15:59:58'),(116,1,3,1,'2026-05-15',3,1,5,'2026-05-06 15:41:08'),(117,1,3,1,'2026-05-15',3,2,NULL,NULL),(118,1,3,2,'2026-05-15',1,1,53,'2026-05-06 16:22:46'),(119,1,3,2,'2026-05-15',1,2,70,'2026-05-06 21:13:24'),(120,1,3,2,'2026-05-15',1,3,69,'2026-05-06 18:14:12'),(121,1,3,2,'2026-05-15',2,1,4,'2026-05-07 20:32:12'),(122,1,3,2,'2026-05-15',2,2,54,'2026-05-06 16:27:26'),(123,1,3,2,'2026-05-15',3,1,5,'2026-05-06 15:41:18'),(124,1,3,2,'2026-05-15',3,2,60,'2026-05-09 20:35:09'),(125,1,4,1,'2026-05-15',1,1,73,'2026-05-07 03:15:29'),(126,1,4,1,'2026-05-15',2,1,11,'2026-05-06 13:40:30'),(127,1,4,1,'2026-05-15',3,1,NULL,NULL),(128,1,4,2,'2026-05-15',1,1,73,'2026-05-07 04:42:31'),(129,1,4,2,'2026-05-15',2,1,11,'2026-05-06 13:40:31'),(130,1,4,2,'2026-05-15',3,1,NULL,NULL),(131,1,2,1,'2026-05-16',1,1,13,'2026-05-07 02:18:21'),(132,1,2,1,'2026-05-16',2,1,56,'2026-05-07 03:28:43'),(133,1,2,1,'2026-05-16',3,1,NULL,NULL),(134,1,2,2,'2026-05-16',1,1,57,'2026-05-06 15:32:47'),(135,1,2,2,'2026-05-16',2,1,56,'2026-05-07 03:09:43'),(136,1,2,2,'2026-05-16',3,1,NULL,NULL),(137,1,3,1,'2026-05-16',1,1,53,'2026-05-06 16:31:00'),(138,1,3,1,'2026-05-16',1,2,45,'2026-05-07 03:12:16'),(139,1,3,1,'2026-05-16',1,3,17,'2026-05-06 16:36:40'),(140,1,3,1,'2026-05-16',2,1,10,'2026-05-06 16:20:55'),(141,1,3,1,'2026-05-16',2,2,54,'2026-05-06 16:25:23'),(142,1,3,1,'2026-05-16',3,1,5,'2026-05-06 15:41:08'),(143,1,3,1,'2026-05-16',3,2,NULL,NULL),(144,1,3,2,'2026-05-16',1,1,70,'2026-05-07 01:47:42'),(145,1,3,2,'2026-05-16',1,2,61,'2026-05-06 18:30:11'),(146,1,3,2,'2026-05-16',1,3,NULL,NULL),(147,1,3,2,'2026-05-16',2,1,45,'2026-05-07 03:38:37'),(148,1,3,2,'2026-05-16',2,2,54,'2026-05-06 16:25:35'),(149,1,3,2,'2026-05-16',3,1,5,'2026-05-06 15:41:19'),(150,1,3,2,'2026-05-16',3,2,NULL,NULL),(151,1,4,1,'2026-05-16',1,1,73,'2026-05-07 02:19:09'),(152,1,4,1,'2026-05-16',2,1,55,'2026-05-06 16:46:22'),(153,1,4,1,'2026-05-16',3,1,NULL,NULL),(154,1,4,2,'2026-05-16',1,1,22,'2026-05-07 03:25:39'),(155,1,4,2,'2026-05-16',2,1,71,'2026-05-06 21:06:31'),(156,1,4,2,'2026-05-16',3,1,NULL,NULL),(157,1,2,1,'2026-05-17',1,1,29,'2026-05-06 16:49:56'),(158,1,2,1,'2026-05-17',2,1,13,'2026-05-07 01:54:30'),(159,1,2,1,'2026-05-17',3,1,NULL,NULL),(160,1,2,2,'2026-05-17',1,1,69,'2026-05-06 18:13:12'),(161,1,2,2,'2026-05-17',2,1,56,'2026-05-06 15:49:27'),(162,1,2,2,'2026-05-17',3,1,NULL,NULL),(163,1,3,1,'2026-05-17',1,1,22,'2026-05-06 13:50:25'),(164,1,3,1,'2026-05-17',1,2,57,'2026-05-06 17:14:46'),(165,1,3,1,'2026-05-17',1,3,17,'2026-05-06 16:36:42'),(166,1,3,1,'2026-05-17',2,1,45,'2026-05-06 15:48:24'),(167,1,3,1,'2026-05-17',2,2,10,'2026-05-06 16:21:04'),(168,1,3,1,'2026-05-17',3,1,NULL,NULL),(169,1,3,1,'2026-05-17',3,2,NULL,NULL),(170,1,3,2,'2026-05-17',1,1,53,'2026-05-06 16:22:51'),(171,1,3,2,'2026-05-17',1,2,61,'2026-05-06 18:30:12'),(172,1,3,2,'2026-05-17',1,3,70,'2026-05-06 21:10:28'),(173,1,3,2,'2026-05-17',2,1,4,'2026-05-07 18:54:18'),(174,1,3,2,'2026-05-17',2,2,54,'2026-05-06 16:27:43'),(175,1,3,2,'2026-05-17',3,1,NULL,NULL),(176,1,3,2,'2026-05-17',3,2,60,'2026-05-09 20:35:11'),(177,1,4,1,'2026-05-17',1,1,73,'2026-05-07 02:07:23'),(178,1,4,1,'2026-05-17',2,1,11,'2026-05-06 13:40:11'),(179,1,4,1,'2026-05-17',3,1,NULL,NULL),(180,1,4,2,'2026-05-17',1,1,71,'2026-05-06 21:07:17'),(181,1,4,2,'2026-05-17',2,1,55,'2026-05-06 16:46:31'),(182,1,4,2,'2026-05-17',3,1,NULL,NULL);
/*!40000 ALTER TABLE `horario_slot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario_solicitud`
--

DROP TABLE IF EXISTS `horario_solicitud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario_solicitud` (
  `id_solicitud` int NOT NULL AUTO_INCREMENT,
  `semana_id` int NOT NULL,
  `postulante_id` int NOT NULL,
  `local_id` int NOT NULL,
  `turno_id` int NOT NULL,
  `lunes` tinyint(1) NOT NULL DEFAULT '0',
  `martes` tinyint(1) NOT NULL DEFAULT '0',
  `miercoles` tinyint(1) NOT NULL DEFAULT '0',
  `jueves` tinyint(1) NOT NULL DEFAULT '0',
  `viernes` tinyint(1) NOT NULL DEFAULT '0',
  `sabado` tinyint(1) NOT NULL DEFAULT '0',
  `domingo` tinyint(1) NOT NULL DEFAULT '0',
  `estado` enum('BORRADOR','ENVIADO','APROBADO','RECHAZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BORRADOR',
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `observacion_admin` text COLLATE utf8mb4_unicode_ci,
  `revisado_por_id` int DEFAULT NULL,
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_solicitud`),
  UNIQUE KEY `uq_horario_semana_trabajador` (`semana_id`,`postulante_id`),
  KEY `idx_hs_postulante` (`postulante_id`),
  KEY `idx_hs_local` (`local_id`),
  KEY `idx_hs_turno` (`turno_id`),
  KEY `idx_hs_estado` (`estado`),
  KEY `fk_hs_revisado_por` (`revisado_por_id`),
  CONSTRAINT `fk_hs_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  CONSTRAINT `fk_hs_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_hs_revisado_por` FOREIGN KEY (`revisado_por_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE SET NULL,
  CONSTRAINT `fk_hs_semana` FOREIGN KEY (`semana_id`) REFERENCES `semana` (`id_semana`) ON DELETE CASCADE,
  CONSTRAINT `fk_hs_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario_solicitud`
--

LOCK TABLES `horario_solicitud` WRITE;
/*!40000 ALTER TABLE `horario_solicitud` DISABLE KEYS */;
/*!40000 ALTER TABLE `horario_solicitud` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencia`
--

DROP TABLE IF EXISTS `incidencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incidencia` (
  `id_incidencia` int NOT NULL AUTO_INCREMENT,
  `postulante_id` int NOT NULL,
  `sesion_id` int DEFAULT NULL,
  `tipo` enum('ERROR_CAJA','FALTA_DISCIPLINARIA','SISTEMA','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('REGISTRADO','EN_REVISION','RESUELTO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGISTRADO',
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_incidencia`),
  KEY `idx_inc_postulante` (`postulante_id`),
  KEY `idx_inc_sesion` (`sesion_id`),
  CONSTRAINT `fk_inc_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`),
  CONSTRAINT `fk_inc_usuario` FOREIGN KEY (`postulante_id`) REFERENCES `usuario` (`postulante_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia`
--

LOCK TABLES `incidencia` WRITE;
/*!40000 ALTER TABLE `incidencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `incidencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `institucion`
--

DROP TABLE IF EXISTS `institucion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institucion` (
  `id_institucion` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_institucion`),
  UNIQUE KEY `uq_institucion_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `institucion`
--

LOCK TABLES `institucion` WRITE;
/*!40000 ALTER TABLE `institucion` DISABLE KEYS */;
INSERT INTO `institucion` VALUES (1,'Instituto Federico Villarreal',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'Instituto IDAT',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Instituto Superior Arzobispo Loayza',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(4,'Instituto Superior Daniel Alcides Carrión',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(5,'Otros',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(6,'Universidad María Auxiliadora',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(7,'Universidad Nacional Mayor de San Marcos',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(8,'Universidad Norbert Wiener',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(9,'Universidad Privada del Norte',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(10,'Universidad Tecnológica del Perú',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `institucion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local`
--

DROP TABLE IF EXISTS `local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local` (
  `id_local` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_encargado` int DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_local`),
  KEY `idx_local_encargado` (`id_encargado`),
  CONSTRAINT `fk_local_encargado` FOREIGN KEY (`id_encargado`) REFERENCES `postulante` (`id_postulante`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local`
--

LOCK TABLES `local` WRITE;
/*!40000 ALTER TABLE `local` DISABLE KEYS */;
INSERT INTO `local` VALUES (2,'Local 2',NULL,NULL,1),(3,'Local 3',NULL,NULL,1),(4,'Local 4',NULL,NULL,1);
/*!40000 ALTER TABLE `local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modo`
--

DROP TABLE IF EXISTS `modo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modo` (
  `id_modo` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_modo`),
  UNIQUE KEY `uq_modo_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modo`
--

LOCK TABLES `modo` WRITE;
/*!40000 ALTER TABLE `modo` DISABLE KEYS */;
INSERT INTO `modo` VALUES (1,'EFECTIVO',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(2,'YAPE',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(3,'PLIN',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(4,'VISAS',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(5,'BCP',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(6,'TRANSFERENCIA_BANCARIA',1,'2026-05-04 20:58:12','2026-05-04 20:58:12');
/*!40000 ALTER TABLE `modo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimiento_sesion`
--

DROP TABLE IF EXISTS `movimiento_sesion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimiento_sesion` (
  `id_movimiento` int NOT NULL AUTO_INCREMENT,
  `sesion_id` int NOT NULL,
  `tipo_movimiento_id` int NOT NULL,
  `modo_id` int DEFAULT NULL,
  `postulante_registro_id` int NOT NULL,
  `postulante_revision_id` int DEFAULT NULL,
  `descripcion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_operacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comprobante_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `fecha_anulacion` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','APROBADO','OBSERVADO','RECHAZADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `motivo_anulacion` text COLLATE utf8mb4_unicode_ci,
  `observacion_revision` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_movimiento`),
  KEY `idx_mov_sesion` (`sesion_id`),
  KEY `idx_mov_tipo` (`tipo_movimiento_id`),
  KEY `idx_mov_registro` (`postulante_registro_id`),
  KEY `idx_mov_revision` (`postulante_revision_id`),
  KEY `idx_mov_modo` (`modo_id`),
  KEY `idx_mov_fecha` (`fecha_movimiento`),
  KEY `idx_mov_estado` (`estado`),
  CONSTRAINT `fk_mov_modo` FOREIGN KEY (`modo_id`) REFERENCES `modo` (`id_modo`),
  CONSTRAINT `fk_mov_registro` FOREIGN KEY (`postulante_registro_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_mov_revision` FOREIGN KEY (`postulante_revision_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_mov_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`),
  CONSTRAINT `fk_mov_tipo` FOREIGN KEY (`tipo_movimiento_id`) REFERENCES `tipo_movimiento` (`id_tipo_movimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimiento_sesion`
--

LOCK TABLES `movimiento_sesion` WRITE;
/*!40000 ALTER TABLE `movimiento_sesion` DISABLE KEYS */;
INSERT INTO `movimiento_sesion` VALUES (17,27,1,2,29,NULL,NULL,445.70,NULL,NULL,'2026-05-07 14:41:58',NULL,NULL,'PENDIENTE',NULL,NULL),(18,30,1,2,17,NULL,NULL,1000.00,NULL,NULL,'2026-05-07 14:56:19',NULL,NULL,'PENDIENTE',NULL,NULL),(19,30,1,4,17,NULL,NULL,1000.00,NULL,NULL,'2026-05-07 14:56:24',NULL,NULL,'PENDIENTE',NULL,NULL),(20,30,1,3,17,NULL,NULL,938.90,NULL,NULL,'2026-05-07 14:56:37',NULL,NULL,'PENDIENTE',NULL,NULL),(21,33,1,2,22,NULL,NULL,10.00,NULL,NULL,'2026-05-07 15:13:59',NULL,NULL,'PENDIENTE',NULL,NULL),(25,31,1,2,17,NULL,NULL,388.30,NULL,NULL,'2026-05-07 19:58:44',NULL,NULL,'PENDIENTE',NULL,NULL),(26,31,1,4,17,NULL,NULL,232.80,NULL,NULL,'2026-05-07 19:59:03',NULL,NULL,'PENDIENTE',NULL,NULL),(27,31,2,1,17,NULL,'desayuno sra. Marina',3.01,NULL,NULL,'2026-05-07 20:00:17',NULL,NULL,'APROBADO',NULL,NULL),(28,28,1,2,29,NULL,NULL,142.70,NULL,NULL,'2026-05-07 20:07:03',NULL,NULL,'PENDIENTE',NULL,NULL),(29,35,2,1,22,NULL,'se compro lancetas',40.00,NULL,NULL,'2026-05-07 20:16:16',NULL,NULL,'APROBADO',NULL,NULL),(30,35,2,1,22,NULL,'deposito grupo kgyr',11000.01,NULL,NULL,'2026-05-07 20:16:16',NULL,NULL,'APROBADO',NULL,NULL),(31,38,1,2,71,NULL,NULL,612.80,NULL,NULL,'2026-05-07 20:22:38',NULL,NULL,'PENDIENTE',NULL,NULL),(33,40,1,2,54,NULL,NULL,151.60,NULL,NULL,'2026-05-07 20:27:30',NULL,NULL,'PENDIENTE',NULL,NULL),(37,42,1,2,17,NULL,NULL,871.10,NULL,NULL,'2026-05-08 03:59:23',NULL,NULL,'PENDIENTE',NULL,NULL),(38,42,1,4,17,NULL,NULL,100.70,NULL,NULL,'2026-05-08 03:59:38',NULL,NULL,'PENDIENTE',NULL,NULL),(40,42,2,1,17,NULL,'compra de Ciro',80.50,NULL,NULL,'2026-05-08 04:00:18',NULL,NULL,'APROBADO',NULL,NULL),(42,44,2,1,57,NULL,'compra de ciro',37.00,NULL,NULL,'2026-05-08 04:15:55',NULL,NULL,'APROBADO',NULL,NULL),(43,48,1,2,69,NULL,NULL,282.10,NULL,NULL,'2026-05-08 13:42:00',NULL,NULL,'PENDIENTE',NULL,NULL),(45,50,1,2,73,NULL,NULL,298.10,NULL,NULL,'2026-05-08 16:07:40',NULL,NULL,'PENDIENTE',NULL,NULL),(46,52,2,1,22,NULL,'deposito a grupo kgyr',10000.00,NULL,NULL,'2026-05-08 19:51:46',NULL,NULL,'APROBADO',NULL,NULL),(47,51,1,2,11,NULL,NULL,220.10,NULL,NULL,'2026-05-08 20:01:02',NULL,NULL,'PENDIENTE',NULL,NULL),(49,47,1,4,70,NULL,NULL,22.90,NULL,NULL,'2026-05-08 20:08:34',NULL,NULL,'PENDIENTE',NULL,NULL),(50,47,1,2,70,NULL,NULL,543.20,NULL,NULL,'2026-05-08 20:08:47',NULL,NULL,'PENDIENTE',NULL,NULL),(51,47,2,1,70,NULL,'COMPRA',42.00,NULL,NULL,'2026-05-08 20:09:36',NULL,NULL,'APROBADO',NULL,NULL),(52,53,1,2,29,NULL,NULL,47.10,NULL,NULL,'2026-05-08 20:09:43',NULL,NULL,'PENDIENTE',NULL,NULL),(59,55,1,2,69,NULL,NULL,707.70,NULL,NULL,'2026-05-09 04:07:20',NULL,NULL,'PENDIENTE',NULL,NULL),(60,55,1,4,69,NULL,NULL,273.30,NULL,NULL,'2026-05-09 04:07:46',NULL,NULL,'PENDIENTE',NULL,NULL),(61,57,1,2,73,NULL,NULL,206.20,NULL,NULL,'2026-05-09 04:09:33',NULL,NULL,'PENDIENTE',NULL,NULL),(62,57,1,4,73,NULL,NULL,67.60,NULL,NULL,'2026-05-09 04:09:41',NULL,NULL,'PENDIENTE',NULL,NULL),(63,55,2,1,69,NULL,'Gasto',410.00,NULL,NULL,'2026-05-09 04:10:44',NULL,NULL,'APROBADO',NULL,NULL),(64,55,2,1,69,NULL,'compra ciro',37.00,NULL,NULL,'2026-05-09 04:10:44',NULL,NULL,'APROBADO',NULL,NULL),(65,58,1,2,53,NULL,NULL,317.50,NULL,NULL,'2026-05-09 04:12:04',NULL,NULL,'PENDIENTE',NULL,NULL),(66,58,1,4,53,NULL,NULL,188.90,NULL,NULL,'2026-05-09 04:12:14',NULL,NULL,'PENDIENTE',NULL,NULL),(67,58,2,1,53,NULL,'compra de ciro',74.00,NULL,NULL,'2026-05-09 04:12:42',NULL,NULL,'APROBADO',NULL,NULL),(68,59,1,2,29,NULL,NULL,186.10,NULL,NULL,'2026-05-09 04:13:36',NULL,NULL,'PENDIENTE',NULL,NULL),(69,61,1,2,73,NULL,NULL,885.65,NULL,NULL,'2026-05-09 20:03:08',NULL,NULL,'PENDIENTE',NULL,NULL),(70,61,1,4,73,NULL,NULL,39.00,NULL,NULL,'2026-05-09 20:03:19',NULL,NULL,'PENDIENTE',NULL,NULL),(71,62,2,1,57,NULL,'visa y yape',90.20,NULL,NULL,'2026-05-09 20:23:34',NULL,NULL,'APROBADO',NULL,NULL),(72,63,1,2,71,NULL,NULL,142.80,NULL,NULL,'2026-05-09 21:16:47',NULL,NULL,'PENDIENTE',NULL,NULL),(73,63,1,4,71,NULL,NULL,45.30,NULL,NULL,'2026-05-09 21:16:59',NULL,NULL,'PENDIENTE',NULL,NULL),(74,64,1,2,57,NULL,NULL,103.10,NULL,NULL,'2026-05-10 03:59:03',NULL,NULL,'PENDIENTE',NULL,NULL),(75,64,1,4,57,NULL,NULL,3.00,NULL,NULL,'2026-05-10 03:59:12',NULL,NULL,'PENDIENTE',NULL,NULL),(76,64,2,1,57,NULL,'descargo algodon',2.60,NULL,NULL,'2026-05-10 03:59:46',NULL,NULL,'APROBADO',NULL,NULL),(77,65,2,1,53,NULL,'compra de glucophage xr 100 x que faltaba completar para venta',76.60,NULL,NULL,'2026-05-10 04:05:06',NULL,NULL,'APROBADO',NULL,NULL),(78,66,1,2,17,NULL,NULL,583.80,NULL,NULL,'2026-05-10 04:05:54',NULL,NULL,'PENDIENTE',NULL,NULL),(79,66,1,4,17,NULL,NULL,277.00,NULL,NULL,'2026-05-10 04:06:10',NULL,NULL,'PENDIENTE',NULL,NULL),(80,66,2,1,17,NULL,'compra paliglobo',32.00,NULL,NULL,'2026-05-10 04:07:21',NULL,NULL,'APROBADO',NULL,NULL),(81,66,2,1,17,NULL,'deposito sra. Marina',500.00,NULL,NULL,'2026-05-10 04:07:21',NULL,NULL,'APROBADO',NULL,NULL),(82,67,1,2,71,NULL,NULL,255.60,NULL,NULL,'2026-05-10 04:14:52',NULL,NULL,'PENDIENTE',NULL,NULL),(83,67,1,4,71,NULL,NULL,156.40,NULL,NULL,'2026-05-10 04:15:02',NULL,NULL,'PENDIENTE',NULL,NULL),(84,67,2,1,71,NULL,'1 docena de paliglobos',2.50,NULL,NULL,'2026-05-10 04:16:04',NULL,NULL,'APROBADO',NULL,NULL),(97,68,1,4,17,NULL,NULL,111.10,NULL,NULL,'2026-05-10 19:57:54',NULL,NULL,'PENDIENTE',NULL,NULL),(98,68,1,2,17,NULL,NULL,306.00,NULL,NULL,'2026-05-10 19:58:57',NULL,NULL,'PENDIENTE',NULL,NULL),(102,69,1,2,69,NULL,NULL,166.50,NULL,NULL,'2026-05-10 20:02:17',NULL,NULL,'PENDIENTE',NULL,NULL),(103,68,2,1,17,NULL,'compra de jabon',4.50,NULL,NULL,'2026-05-10 20:03:36',NULL,NULL,'APROBADO',NULL,NULL),(104,68,2,1,17,NULL,'compra de bolsas',67.00,NULL,NULL,'2026-05-10 20:03:36',NULL,NULL,'APROBADO',NULL,NULL),(105,68,2,1,17,NULL,'pago de terreno Ayacucho',2499.99,NULL,NULL,'2026-05-10 20:03:36',NULL,NULL,'APROBADO',NULL,NULL),(106,70,1,2,53,NULL,NULL,112.40,NULL,NULL,'2026-05-10 20:06:04',NULL,NULL,'PENDIENTE',NULL,NULL),(107,70,2,1,53,NULL,'tiras reactivas de glucosa',79.80,NULL,NULL,'2026-05-10 20:07:09',NULL,NULL,'APROBADO',NULL,NULL),(108,71,1,2,11,NULL,NULL,356.30,NULL,NULL,'2026-05-10 20:17:35',NULL,NULL,'PENDIENTE',NULL,NULL),(109,71,1,4,11,NULL,NULL,6.60,NULL,NULL,'2026-05-10 20:17:43',NULL,NULL,'PENDIENTE',NULL,NULL);
/*!40000 ALTER TABLE `movimiento_sesion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nivel`
--

DROP TABLE IF EXISTS `nivel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nivel` (
  `id_nivel` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nivel`),
  UNIQUE KEY `uq_nivel_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nivel`
--

LOCK TABLES `nivel` WRITE;
/*!40000 ALTER TABLE `nivel` DISABLE KEYS */;
INSERT INTO `nivel` VALUES (1,'Básico',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'Intermedio',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Avanzado',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `nivel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pago_local`
--

DROP TABLE IF EXISTS `pago_local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pago_local` (
  `id_pago_local` int NOT NULL AUTO_INCREMENT,
  `sesion_id` int NOT NULL,
  `local_id` int NOT NULL,
  `postulante_emisor_id` int NOT NULL,
  `concepto_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_operacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comprobante_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','OBSERVADO','RECHAZADO','APROBADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `observacion_revision` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_pago_local`),
  KEY `idx_pl_sesion` (`sesion_id`),
  KEY `idx_pl_local` (`local_id`),
  KEY `idx_pl_emisor` (`postulante_emisor_id`),
  KEY `idx_pl_concepto` (`concepto_id`),
  CONSTRAINT `fk_pl_concepto` FOREIGN KEY (`concepto_id`) REFERENCES `concepto_gastos_local` (`id_concepto`),
  CONSTRAINT `fk_pl_emisor` FOREIGN KEY (`postulante_emisor_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_pl_local` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  CONSTRAINT `fk_pl_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago_local`
--

LOCK TABLES `pago_local` WRITE;
/*!40000 ALTER TABLE `pago_local` DISABLE KEYS */;
/*!40000 ALTER TABLE `pago_local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pago_personal`
--

DROP TABLE IF EXISTS `pago_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pago_personal` (
  `id_pago_personal` int NOT NULL AUTO_INCREMENT,
  `sesion_id` int NOT NULL,
  `postulante_emisor_id` int NOT NULL,
  `postulante_beneficiario_id` int NOT NULL,
  `postulante_revisor_id` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_operacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comprobante_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_confirmacion_beneficiario` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE','PAGADO','CONFIRMADO_BENEFICIARIO','OBSERVADO','RECHAZADO','APROBADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `observacion_beneficiario` text COLLATE utf8mb4_unicode_ci,
  `observacion_revision` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_pago_personal`),
  KEY `idx_pp_sesion` (`sesion_id`),
  KEY `idx_pp_emisor` (`postulante_emisor_id`),
  KEY `idx_pp_beneficiario` (`postulante_beneficiario_id`),
  KEY `idx_pp_revisor` (`postulante_revisor_id`),
  KEY `idx_pp_estado` (`estado`),
  CONSTRAINT `fk_pp_beneficiario` FOREIGN KEY (`postulante_beneficiario_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_pp_emisor` FOREIGN KEY (`postulante_emisor_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_pp_revisor` FOREIGN KEY (`postulante_revisor_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_pp_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago_personal`
--

LOCK TABLES `pago_personal` WRITE;
/*!40000 ALTER TABLE `pago_personal` DISABLE KEYS */;
INSERT INTO `pago_personal` VALUES (4,55,69,69,NULL,640.00,NULL,NULL,'2026-05-09 04:10:44',NULL,NULL,'PAGADO',NULL,NULL),(5,61,73,22,NULL,649.99,NULL,NULL,'2026-05-09 20:03:56',NULL,NULL,'PAGADO',NULL,NULL),(11,68,17,73,NULL,150.00,NULL,NULL,'2026-05-10 20:03:36',NULL,NULL,'PAGADO',NULL,NULL);
/*!40000 ALTER TABLE `pago_personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plantilla_horario`
--

DROP TABLE IF EXISTS `plantilla_horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plantilla_horario` (
  `id_plantilla` int NOT NULL AUTO_INCREMENT,
  `local_id` int NOT NULL,
  `turno_id` int NOT NULL,
  `rol_horario_id` int NOT NULL,
  `cantidad` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_plantilla`),
  UNIQUE KEY `uq_plantilla` (`local_id`,`turno_id`,`rol_horario_id`),
  KEY `turno_id` (`turno_id`),
  KEY `rol_horario_id` (`rol_horario_id`),
  CONSTRAINT `plantilla_horario_ibfk_1` FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`),
  CONSTRAINT `plantilla_horario_ibfk_2` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`),
  CONSTRAINT `plantilla_horario_ibfk_3` FOREIGN KEY (`rol_horario_id`) REFERENCES `rol_horario` (`id_rol_horario`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plantilla_horario`
--

LOCK TABLES `plantilla_horario` WRITE;
/*!40000 ALTER TABLE `plantilla_horario` DISABLE KEYS */;
INSERT INTO `plantilla_horario` VALUES (1,2,1,1,1),(2,2,1,2,1),(3,2,1,3,1),(4,2,2,1,1),(5,2,2,2,1),(6,2,2,3,1),(7,3,1,1,3),(8,3,1,2,2),(9,3,1,3,2),(10,3,2,1,3),(11,3,2,2,2),(12,3,2,3,2),(13,4,1,1,1),(14,4,1,2,1),(15,4,1,3,1),(16,4,2,1,1),(17,4,2,2,1),(18,4,2,3,1);
/*!40000 ALTER TABLE `plantilla_horario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postulacion`
--

DROP TABLE IF EXISTS `postulacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postulacion` (
  `id_postulacion` int NOT NULL AUTO_INCREMENT,
  `postulante_id` int NOT NULL,
  `puesto_id` int NOT NULL,
  `etapa_id` int DEFAULT '1',
  `visto` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_vista` timestamp NULL DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `fecha_postulacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_postulacion`),
  UNIQUE KEY `uq_postulante_puesto` (`postulante_id`,`puesto_id`),
  KEY `idx_postulacion_etapa` (`etapa_id`),
  KEY `idx_postulacion_visto` (`visto`),
  KEY `idx_postulacion_fecha` (`fecha_postulacion`),
  KEY `fk_postulacion_puesto` (`puesto_id`),
  CONSTRAINT `fk_postulacion_etapa` FOREIGN KEY (`etapa_id`) REFERENCES `etapa` (`id_etapa`),
  CONSTRAINT `fk_postulacion_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_postulacion_puesto` FOREIGN KEY (`puesto_id`) REFERENCES `puesto` (`id_puesto`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postulacion`
--

LOCK TABLES `postulacion` WRITE;
/*!40000 ALTER TABLE `postulacion` DISABLE KEYS */;
INSERT INTO `postulacion` VALUES (2,1,1,4,0,NULL,NULL,'2026-05-04 21:08:25','2026-05-04 21:08:25'),(15,4,7,4,0,NULL,NULL,'2026-05-06 00:56:32','2026-05-06 00:56:32'),(18,10,8,4,0,NULL,NULL,'2026-05-06 00:58:41','2026-05-06 00:58:41'),(20,13,7,4,0,NULL,NULL,'2026-05-06 01:10:08','2026-05-06 01:10:08'),(22,5,2,4,0,NULL,NULL,'2026-05-06 01:28:02','2026-05-06 01:28:02'),(23,11,7,4,0,NULL,NULL,'2026-05-06 01:28:28','2026-05-06 01:28:28'),(24,17,3,4,0,NULL,NULL,'2026-05-06 01:29:19','2026-05-06 01:29:19'),(25,19,7,4,0,NULL,NULL,'2026-05-06 01:31:08','2026-05-06 01:31:08'),(28,22,7,4,0,NULL,NULL,'2026-05-06 01:34:36','2026-05-06 01:34:36'),(30,29,7,4,0,NULL,NULL,'2026-05-06 01:36:34','2026-05-06 01:36:34'),(32,45,7,4,0,NULL,NULL,'2026-05-06 01:39:39','2026-05-06 01:39:39'),(34,51,7,4,0,NULL,NULL,'2026-05-06 01:41:52','2026-05-06 01:41:52'),(38,53,3,4,0,NULL,NULL,'2026-05-06 01:46:15','2026-05-06 01:46:15'),(40,54,7,4,0,NULL,NULL,'2026-05-06 01:49:29','2026-05-06 01:49:29'),(42,59,7,4,0,NULL,NULL,'2026-05-06 01:50:30','2026-05-06 01:50:30'),(44,60,7,4,0,NULL,NULL,'2026-05-06 01:51:26','2026-05-06 01:51:26'),(46,61,3,4,0,NULL,NULL,'2026-05-06 01:52:30','2026-05-06 01:52:30'),(47,12,7,4,0,NULL,NULL,'2026-05-06 01:53:47','2026-05-06 01:53:47'),(48,52,3,4,0,NULL,NULL,'2026-05-06 01:54:27','2026-05-06 01:54:27'),(52,57,3,4,0,NULL,NULL,'2026-05-06 15:27:24','2026-05-06 15:27:24'),(55,55,7,4,0,NULL,NULL,'2026-05-06 16:32:24','2026-05-06 16:32:24'),(58,68,7,4,0,NULL,NULL,'2026-05-06 17:07:28','2026-05-06 17:07:28'),(61,69,3,4,0,NULL,NULL,'2026-05-06 18:09:59','2026-05-06 18:09:59'),(65,70,7,4,0,NULL,NULL,'2026-05-06 21:02:12','2026-05-06 21:02:12'),(67,71,7,4,0,NULL,NULL,'2026-05-06 21:04:09','2026-05-06 21:04:09'),(69,72,1,4,0,NULL,NULL,'2026-05-06 23:34:30','2026-05-06 23:34:30'),(74,73,7,4,0,NULL,NULL,'2026-05-08 15:11:35','2026-05-08 15:11:35');
/*!40000 ALTER TABLE `postulacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postulante`
--

DROP TABLE IF EXISTS `postulante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postulante` (
  `id_postulante` int NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genero_id` int DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `situacion_vivienda_id` int DEFAULT NULL,
  `num_documento` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distrito` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calificacion` decimal(5,2) DEFAULT NULL,
  `foto_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cv_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `etapa_id` int DEFAULT '1',
  `tipo_personal` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_postulante`),
  UNIQUE KEY `uq_postulante_dni` (`num_documento`),
  KEY `idx_postulante_genero` (`genero_id`),
  KEY `idx_postulante_vivienda` (`situacion_vivienda_id`),
  CONSTRAINT `fk_postulante_genero` FOREIGN KEY (`genero_id`) REFERENCES `genero` (`id_genero`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_postulante_vivienda` FOREIGN KEY (`situacion_vivienda_id`) REFERENCES `situacion_vivienda` (`id_situacion`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postulante`
--

LOCK TABLES `postulante` WRITE;
/*!40000 ALTER TABLE `postulante` DISABLE KEYS */;
INSERT INTO `postulante` VALUES (1,'Gian Carlo','Vilcamiche Chávez',1,'1991-02-16','','935812267',NULL,'47238914','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 00:41:20',NULL,4,NULL),(2,'Solange Moulin','Coronel Camacllanqui',2,NULL,'solange@test.com','923402449',2,'75818239','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 02:26:31',NULL,1,''),(3,'Milagros Del Pilar','Huamán Cruzado',2,'1987-10-01',NULL,'986152754',NULL,'44850621',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(4,'Dariana','Bautista Contreras',2,'1999-08-19','','926491304',2,'71694239','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:20:10',NULL,4,'A1'),(5,'Patricia del Pilar','Obregon Pozo',2,'2001-08-10','','980815404',2,'71637953','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:28:02',NULL,4,'B1'),(6,'Maryori','Flores Ubaldo',2,'1999-10-16',NULL,'985951246',NULL,'75519567',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(7,'Maribel Rosario','Salazar Baldeon',2,'1992-11-10',NULL,'937863443',NULL,'47512524',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(8,'María Doris','García Torres',2,'1990-02-19',NULL,'932767767',NULL,'46254125',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(9,'Flor de Maria','Mercedes Huayta',2,'1990-06-29',NULL,'928134625',NULL,'47752886',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(10,'Karen Lizbeth','Martinez Encina',2,'2001-06-09','','953933814',1,'72220359','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:20:10',NULL,4,'A1'),(11,'Fiorella del Rosario','Chambi Rafaile',2,'1998-05-20','','991241518',2,'48857877','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:28:28',NULL,4,'A1'),(12,'Sharik Sheylly','Rodriguez Pineda',2,'2004-12-01','','927025545',2,'76863236','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:20:10',NULL,4,'B1'),(13,'Monica','Quispe Ccallo',2,'2002-03-17','','967697231',2,'74399262','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:20:10',NULL,4,'B1'),(14,'Karin Gianina','Ramirez Calixto',2,NULL,NULL,'971292140',NULL,'73389615',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(15,'Leidi','Peralta Colunche',2,NULL,NULL,'924666882',NULL,'71142925',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(16,'Diana','Mendoza Huaman',2,'1998-03-03',NULL,'955059406',NULL,'76221752',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(17,'Rocío Geraldinne','Quispe Alberco',2,'1994-02-24','','936839098',2,'72667321','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:29:19',NULL,4,'X1'),(18,'Guillermina Yomnis','Santos Basilio',2,NULL,NULL,'912557536',NULL,'48219564',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(19,'Elizabeth','Flores Silva',2,NULL,'','',3,'47943458','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:31:08',NULL,4,'C1'),(20,'Marina','Heredia Acuña',2,'1987-08-15',NULL,'949451967',NULL,'44428885',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(21,'Alexander Rafael','Suarez Chacón',1,'1992-06-10',NULL,'974190345',NULL,'47823006',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(22,'Yolvi Romelia','Patricio Flores',2,'1995-09-07','','973486812',2,'76794496','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:34:06',NULL,4,'C1'),(23,'Inoe','Ortiz Quispe',2,NULL,NULL,'921014820',NULL,'70576163',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(24,'Sheila','Marcos Chagua',2,'1995-11-27',NULL,'972021267',NULL,'73634205',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(25,'Elena Dayana','Peña Manrique',2,'1999-11-24',NULL,'923831364',NULL,'76633896',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(26,'Dilza Elizabeth','Alarcon Muñoz',2,'1992-06-27',NULL,'970832706',NULL,'48213065',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(27,'Sharon Candy','Marcos Alfaro',2,NULL,NULL,'936751302',NULL,'76221750',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(28,'Miriam Oriana','Aguirre Borja',2,'1990-04-08',NULL,'917328713',NULL,'46303722',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(29,'Yenifer Katia','Quispe Llacchua',2,'2002-07-10','','987083660',2,'70686877','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:36:12',NULL,4,'C1'),(30,'Lizbeth','Quispe de la Cruz',2,'2001-03-30',NULL,'928349105',NULL,'72109429',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(31,'Yoselin Margarita','Baldera Siesquén',2,'1993-11-08',NULL,'927219177',NULL,'48288048',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(32,'Gavi','Santos Ascencio',2,NULL,NULL,'922880107',NULL,'71020821',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(33,'Roy Anthony','Vilcamiche Chavez',1,'1989-03-02',NULL,'999443808',NULL,'45627948',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(34,'Loreli Elizabeth','Salas Zuñiga',2,'1994-02-10',NULL,'984135857',NULL,'48409771',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(35,'Nayeli','Benancio Espinoza',2,'2003-10-21',NULL,'931421447',NULL,'75603108',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(36,'Geraldine Rosario','Felices Escobar',2,'2000-12-28',NULL,'902280060',NULL,'76279496',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(37,'Analu','Fonseca Fernández',2,'2001-12-16',NULL,'955596689',NULL,'74384465',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(38,'Delina','Guillen Matos',2,'1992-10-29',NULL,'935669323',NULL,'47496488',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(39,'Lisset','Bonifacio Duran',2,'2001-04-25',NULL,'927914498',NULL,'77807884',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(40,'Ana Lucia','Coaquira Mamani',2,'1997-12-04',NULL,'936034533',NULL,'76325704',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(41,'Jhovani','Suarez Cueva',2,NULL,NULL,'990815725',NULL,'62117689',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(42,'Carola Liz','Carhuaricra Reyes',2,NULL,NULL,'965829567',NULL,'70127392',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(43,'Luis Daryl','Sanchez Garcia',1,'2004-09-02',NULL,'948676116',NULL,'72552020',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(44,'Carmen Esmeralda','Guadalupe Galarza',2,'1997-06-20',NULL,'927467567',NULL,'71293391',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(45,'Erika Yuliana','Guerrero Huerta',2,'2000-12-31','','910296978',2,'73529760','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:39:11',NULL,4,'B1'),(46,'Kristhel Valeria','Vilcamiche Chávez',2,NULL,NULL,NULL,NULL,'73623849',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(47,'Marta','Laurente Lopez',2,NULL,NULL,NULL,NULL,'48141371',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(49,'Lucelly Angelmira','Robles Jauregui',2,NULL,NULL,NULL,NULL,'74206381',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(50,'Yamilla Anelhy','Quispe Silva',2,NULL,NULL,NULL,NULL,'74588769',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(51,'Dayana Ross','Boy Arellano',2,NULL,'','',2,'75824495','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:41:25',NULL,4,'B1'),(52,'Merlinda Yessica','Bautista Contreras',2,NULL,'','',2,'71694214','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:43:04',NULL,4,'X1'),(53,'Lucia Belen','Arango Caico',2,NULL,'','',2,'76507846','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:45:57',NULL,4,'X1'),(54,'Yovaly Tatiana','De la Cruz Roque',2,NULL,'','',2,'73111770','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:49:07',NULL,4,'B1'),(55,'Orfelinda Anahi','Modesto Cespedes',2,'1994-03-25','orfelindacespedes17@gmail.com','902101459',2,'48348864','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 16:31:29',NULL,4,'B1'),(56,'Maria Ermendia','Yahuana Calderon',2,NULL,'','',2,'74252343','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 15:23:21',NULL,4,'B1'),(57,'Elizabeth Rosa','Taype Cordova',2,'1990-02-07','elizabeth.taype7427@gmail.com','907223849',2,'46300302','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 15:26:19',NULL,4,'C1'),(58,'Dasha Carla','Quichca Ramos',2,NULL,NULL,NULL,NULL,'71884519',NULL,NULL,NULL,NULL,'2026-05-04 20:58:12','2026-05-04 20:58:12',NULL,1,NULL),(59,'Sandra Marina','Revoredo Quiñones',2,NULL,'','',2,'44958162','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:50:13',NULL,4,'B1'),(60,'Eswin Eli','Salazar Ramirez',1,NULL,'','',2,'76084263','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:51:04',NULL,4,'B1'),(61,'Fany Yadira','Benites Niquin',2,NULL,'','',2,'71810694','','',NULL,NULL,'2026-05-04 20:58:12','2026-05-06 01:52:12',NULL,4,'Y1'),(68,'BLOQUEADO','',NULL,'2026-05-06','','',NULL,'00000000','','',NULL,NULL,'2026-05-06 17:06:24','2026-05-06 17:07:03',NULL,4,'A1'),(69,'Flor Milenia','Huamani Yalle',2,'2000-05-01','huamaniyallef@gmail.com','921521070',2,'76775002','','',NULL,NULL,'2026-05-06 18:07:23','2026-05-06 18:09:14',NULL,4,'B1'),(70,'Debora','Peralta',2,'2003-10-11','deborap0711@outlook.com','902943304',2,'70967730','','',NULL,NULL,'2026-05-06 20:58:36','2026-05-06 21:01:14',NULL,4,'B1'),(71,'Esther Beatriz','Fernandez Huillca',2,'1993-07-07','estherfernandezhuillca@outlook.es','980872844',2,'47883640','','',NULL,NULL,'2026-05-06 21:00:48','2026-05-06 21:03:37',NULL,4,'B1'),(72,'Marina','Chavez Villavicencio',2,'1961-08-28','marina.chavezvillavicencio@gmail.com','947996894',3,'28260072','','',NULL,NULL,'2026-05-06 23:33:40','2026-05-06 23:34:30',NULL,4,'B1'),(73,'Victoria Jazmin','Huaman Arango',2,'2000-12-09','','',2,'71392072','','',NULL,NULL,'2026-05-07 02:06:06','2026-05-08 15:11:35',NULL,4,'B1');
/*!40000 ALTER TABLE `postulante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postulante_especialidad`
--

DROP TABLE IF EXISTS `postulante_especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postulante_especialidad` (
  `postulante_id` int NOT NULL,
  `especialidad_id` int NOT NULL,
  PRIMARY KEY (`postulante_id`,`especialidad_id`),
  KEY `idx_pe_especialidad` (`especialidad_id`),
  CONSTRAINT `fk_pe_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidad` (`id_especialidad`),
  CONSTRAINT `fk_pe_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postulante_especialidad`
--

LOCK TABLES `postulante_especialidad` WRITE;
/*!40000 ALTER TABLE `postulante_especialidad` DISABLE KEYS */;
/*!40000 ALTER TABLE `postulante_especialidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postulante_skill`
--

DROP TABLE IF EXISTS `postulante_skill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postulante_skill` (
  `postulante_id` int NOT NULL,
  `skill_id` int NOT NULL,
  `nivel_id` int DEFAULT NULL,
  PRIMARY KEY (`postulante_id`,`skill_id`),
  KEY `idx_ps_skill` (`skill_id`),
  KEY `idx_ps_nivel` (`nivel_id`),
  CONSTRAINT `fk_ps_nivel` FOREIGN KEY (`nivel_id`) REFERENCES `nivel` (`id_nivel`),
  CONSTRAINT `fk_ps_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_skill` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id_skill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postulante_skill`
--

LOCK TABLES `postulante_skill` WRITE;
/*!40000 ALTER TABLE `postulante_skill` DISABLE KEYS */;
INSERT INTO `postulante_skill` VALUES (71,7,1),(57,5,2),(69,5,2),(71,1,2),(72,5,2),(72,7,2),(1,6,3),(11,1,3),(11,7,3),(70,1,3),(72,1,3);
/*!40000 ALTER TABLE `postulante_skill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preferencias`
--

DROP TABLE IF EXISTS `preferencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preferencias` (
  `turno_id` int NOT NULL,
  `postulante_id` int NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`turno_id`,`postulante_id`),
  KEY `idx_pref_postulante` (`postulante_id`),
  CONSTRAINT `fk_pref_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_pref_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preferencias`
--

LOCK TABLES `preferencias` WRITE;
/*!40000 ALTER TABLE `preferencias` DISABLE KEYS */;
INSERT INTO `preferencias` VALUES (1,1,'2026-05-04 21:08:25'),(1,2,'2026-05-11 02:41:29'),(1,4,'2026-05-06 00:56:32'),(1,5,'2026-05-06 01:28:02'),(1,10,'2026-05-06 00:58:41'),(1,11,'2026-05-06 01:28:28'),(1,12,'2026-05-06 01:53:47'),(1,13,'2026-05-06 01:10:08'),(1,17,'2026-05-06 01:29:19'),(1,19,'2026-05-06 01:31:08'),(1,22,'2026-05-06 01:34:36'),(1,29,'2026-05-06 01:36:34'),(1,45,'2026-05-06 01:39:39'),(1,51,'2026-05-06 01:41:52'),(1,52,'2026-05-06 01:54:27'),(1,53,'2026-05-06 01:46:15'),(1,54,'2026-05-06 01:49:29'),(1,55,'2026-05-06 16:32:24'),(1,56,'2026-05-06 15:23:21'),(1,57,'2026-05-06 15:27:24'),(1,59,'2026-05-06 01:50:30'),(1,60,'2026-05-06 01:51:26'),(1,61,'2026-05-06 01:52:30'),(1,68,'2026-05-06 17:07:28'),(1,69,'2026-05-06 18:09:59'),(1,70,'2026-05-06 21:02:12'),(1,72,'2026-05-06 23:34:30'),(1,73,'2026-05-08 15:11:35'),(2,1,'2026-05-04 21:08:25'),(2,2,'2026-05-11 02:41:29'),(2,4,'2026-05-06 00:56:32'),(2,5,'2026-05-06 01:28:02'),(2,10,'2026-05-06 00:58:41'),(2,11,'2026-05-06 01:28:28'),(2,12,'2026-05-06 01:53:47'),(2,13,'2026-05-06 01:10:08'),(2,17,'2026-05-06 01:29:19'),(2,19,'2026-05-06 01:31:08'),(2,22,'2026-05-06 01:34:36'),(2,29,'2026-05-06 01:36:34'),(2,45,'2026-05-06 01:39:39'),(2,51,'2026-05-06 01:41:52'),(2,52,'2026-05-06 01:54:27'),(2,53,'2026-05-06 01:46:15'),(2,54,'2026-05-06 01:49:29'),(2,56,'2026-05-06 15:23:21'),(2,57,'2026-05-06 15:27:24'),(2,59,'2026-05-06 01:50:30'),(2,60,'2026-05-06 01:51:26'),(2,61,'2026-05-06 01:52:30'),(2,68,'2026-05-06 17:07:28'),(2,69,'2026-05-06 18:09:59'),(2,71,'2026-05-06 21:04:09');
/*!40000 ALTER TABLE `preferencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `puesto`
--

DROP TABLE IF EXISTS `puesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `puesto` (
  `id_puesto` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_puesto`),
  UNIQUE KEY `uq_puesto_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `puesto`
--

LOCK TABLES `puesto` WRITE;
/*!40000 ALTER TABLE `puesto` DISABLE KEYS */;
INSERT INTO `puesto` VALUES (1,'Administración',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'Almacén',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Caja',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(4,'Contabilidad',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(5,'Limpieza',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(6,'Practicante',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(7,'Técnica en Farmacia',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(8,'QF',1,'2026-05-06 00:58:14','2026-05-06 00:58:14');
/*!40000 ALTER TABLE `puesto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rectificacion_cuadre`
--

DROP TABLE IF EXISTS `rectificacion_cuadre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rectificacion_cuadre` (
  `id_rectificacion` int NOT NULL AUTO_INCREMENT,
  `sesion_id` int NOT NULL,
  `postulante_registra_id` int NOT NULL,
  `postulante_responsable_id` int DEFAULT NULL,
  `tipo_rectificacion` enum('DEVOLUCION_DINERO','DINERO_ENCONTRADO','AJUSTE_CONTEO','COMPENSACION','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modo_id` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `descripcion_contexto` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `justificacion` text COLLATE utf8mb4_unicode_ci,
  `comprobante_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_rectificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `postulante_revisa_id` int DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `observacion_revision` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_rectificacion`),
  KEY `idx_rect_sesion` (`sesion_id`),
  KEY `idx_rect_registra` (`postulante_registra_id`),
  KEY `idx_rect_responsable` (`postulante_responsable_id`),
  KEY `idx_rect_revisa` (`postulante_revisa_id`),
  KEY `idx_rect_modo` (`modo_id`),
  KEY `idx_rect_estado` (`estado`),
  CONSTRAINT `fk_rect_modo` FOREIGN KEY (`modo_id`) REFERENCES `modo` (`id_modo`),
  CONSTRAINT `fk_rect_registra` FOREIGN KEY (`postulante_registra_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_rect_responsable` FOREIGN KEY (`postulante_responsable_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_rect_revisa` FOREIGN KEY (`postulante_revisa_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_rect_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rectificacion_cuadre`
--

LOCK TABLES `rectificacion_cuadre` WRITE;
/*!40000 ALTER TABLE `rectificacion_cuadre` DISABLE KEYS */;
INSERT INTO `rectificacion_cuadre` VALUES (9,30,17,NULL,'DINERO_ENCONTRADO',NULL,10.00,'encontrado en el piso',NULL,NULL,'2026-05-07 15:04:39','APROBADA',NULL,NULL,NULL),(10,30,17,NULL,'DEVOLUCION_DINERO',NULL,-10.00,'falta descargar',NULL,NULL,'2026-05-07 15:05:36','APROBADA',NULL,NULL,NULL),(11,33,22,NULL,'DEVOLUCION_DINERO',NULL,-10.00,'me equivoque con un yape',NULL,NULL,'2026-05-07 15:15:38','APROBADA',NULL,NULL,NULL),(12,33,22,NULL,'DINERO_ENCONTRADO',NULL,10.00,'correcion',NULL,NULL,'2026-05-07 15:18:39','APROBADA',NULL,NULL,NULL),(13,31,17,NULL,'DINERO_ENCONTRADO',NULL,90.00,'falto contar',NULL,NULL,'2026-05-07 20:17:11','APROBADA',NULL,NULL,NULL),(14,48,69,NULL,'DINERO_ENCONTRADO',NULL,0.41,'falto contar en caja  0.4  y en la venta 0.01',NULL,NULL,'2026-05-08 14:16:36','APROBADA',NULL,NULL,NULL),(15,52,22,NULL,'DINERO_ENCONTRADO',NULL,120.00,'compra de ciro',NULL,NULL,'2026-05-08 19:59:56','APROBADA',NULL,NULL,NULL),(16,51,11,NULL,'OTRO',NULL,6.60,'falta descargar',NULL,NULL,'2026-05-08 20:06:19','APROBADA',NULL,NULL,NULL),(17,51,11,NULL,'OTRO',NULL,-13.20,'correcion',NULL,NULL,'2026-05-08 20:25:55','APROBADA',NULL,NULL,NULL),(18,67,71,NULL,'OTRO',NULL,-2.50,'correcion',NULL,NULL,'2026-05-10 04:17:35','APROBADA',NULL,NULL,NULL);
/*!40000 ALTER TABLE `rectificacion_cuadre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reporte_venta`
--

DROP TABLE IF EXISTS `reporte_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reporte_venta` (
  `id_reporte_venta` int NOT NULL AUTO_INCREMENT,
  `sesion_id` int NOT NULL,
  `postulante_vendedor_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reporte_venta`),
  KEY `idx_rv_sesion` (`sesion_id`),
  KEY `idx_rv_vendedor` (`postulante_vendedor_id`),
  CONSTRAINT `fk_rv_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`),
  CONSTRAINT `fk_rv_vendedor` FOREIGN KEY (`postulante_vendedor_id`) REFERENCES `postulante` (`id_postulante`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reporte_venta`
--

LOCK TABLES `reporte_venta` WRITE;
/*!40000 ALTER TABLE `reporte_venta` DISABLE KEYS */;
INSERT INTO `reporte_venta` VALUES (20,25,29,0.00,'2026-05-07 14:37:23'),(22,27,29,989.10,'2026-05-07 14:42:23'),(24,29,17,0.00,'2026-05-07 14:53:08'),(25,30,17,1479.55,'2026-05-07 14:58:16'),(27,32,22,0.00,'2026-05-07 15:11:06'),(28,33,22,2334.99,'2026-05-07 15:14:50'),(30,31,17,784.60,'2026-05-07 20:00:40'),(31,28,29,341.40,'2026-05-07 20:08:05'),(32,35,22,1117.35,'2026-05-07 20:16:44'),(33,37,1,0.00,'2026-05-07 20:20:28'),(34,38,71,1467.28,'2026-05-07 20:22:53'),(36,40,54,728.30,'2026-05-07 20:29:43'),(39,42,17,1263.99,'2026-05-08 04:01:07'),(42,44,57,1460.30,'2026-05-08 04:16:10'),(45,48,69,521.24,'2026-05-08 13:42:28'),(47,50,73,783.40,'2026-05-08 16:08:03'),(48,52,22,1280.50,'2026-05-08 19:52:13'),(49,51,11,546.70,'2026-05-08 20:04:46'),(50,47,70,801.50,'2026-05-08 20:10:13'),(51,53,29,291.40,'2026-05-08 20:10:31'),(54,57,73,945.40,'2026-05-09 04:10:00'),(55,55,69,1269.49,'2026-05-09 04:11:22'),(56,58,53,1412.29,'2026-05-09 04:12:58'),(57,59,29,571.90,'2026-05-09 04:13:53'),(58,60,22,1583.45,'2026-05-09 19:53:43'),(59,61,73,1323.20,'2026-05-09 20:04:09'),(60,62,57,339.40,'2026-05-09 20:23:55'),(61,63,71,504.00,'2026-05-09 21:17:52'),(62,64,57,588.60,'2026-05-10 04:00:10'),(63,65,53,1242.68,'2026-05-10 04:05:22'),(64,66,17,1104.59,'2026-05-10 04:07:39'),(65,67,71,1073.40,'2026-05-10 04:16:31'),(66,69,69,545.50,'2026-05-10 20:02:40'),(67,68,17,1012.20,'2026-05-10 20:03:51'),(68,70,53,1128.43,'2026-05-10 20:07:24'),(69,71,11,756.30,'2026-05-10 20:18:25');
/*!40000 ALTER TABLE `reporte_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_rol_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'STAFF',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(2,'ADMIN',1,'2026-05-04 20:58:12','2026-05-04 20:58:12');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_horario`
--

DROP TABLE IF EXISTS `rol_horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_horario` (
  `id_rol_horario` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_opcional` tinyint(1) NOT NULL DEFAULT '0',
  `orden` tinyint NOT NULL DEFAULT '0',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#94a3b8',
  PRIMARY KEY (`id_rol_horario`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_horario`
--

LOCK TABLES `rol_horario` WRITE;
/*!40000 ALTER TABLE `rol_horario` DISABLE KEYS */;
INSERT INTO `rol_horario` VALUES (1,'CAJERA','Cajera',0,2,'#2563eb'),(2,'VENDEDORA','Vendedora',0,1,'#059669'),(3,'ALMACENERA','Almacenera',1,3,'#f59e0b');
/*!40000 ALTER TABLE `rol_horario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semana`
--

DROP TABLE IF EXISTS `semana`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semana` (
  `id_semana` int NOT NULL AUTO_INCREMENT,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('ABIERTA','CERRADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTA',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_semana`),
  UNIQUE KEY `uq_semana_inicio` (`fecha_inicio`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semana`
--

LOCK TABLES `semana` WRITE;
/*!40000 ALTER TABLE `semana` DISABLE KEYS */;
INSERT INTO `semana` VALUES (1,'2026-05-11','2026-05-17','ABIERTA','2026-05-05 14:14:03');
/*!40000 ALTER TABLE `semana` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesion_caja`
--

DROP TABLE IF EXISTS `sesion_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesion_caja` (
  `id_sesion` int NOT NULL AUTO_INCREMENT,
  `caja_id` int NOT NULL,
  `turno_id` int NOT NULL,
  `postulante_apertura_id` int NOT NULL,
  `postulante_cierre_id` int DEFAULT NULL,
  `postulante_revisor_id` int DEFAULT NULL,
  `estado` enum('ABIERTA','PENDIENTE_VENTA','CERRADA','EN_REVISION','APROBADA','OBSERVADA','RECHAZADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ABIERTA',
  `saldo_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `saldo_final_sistema` decimal(10,2) DEFAULT NULL,
  `saldo_final_contado` decimal(10,2) DEFAULT NULL,
  `diferencia_final` decimal(10,2) DEFAULT NULL,
  `margen_permitido` decimal(10,2) NOT NULL DEFAULT '10.00',
  `fecha_apertura` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `fecha_operacion` date NOT NULL,
  `fecha_envio_revision` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `observacion_cierre` text COLLATE utf8mb4_unicode_ci,
  `observacion_revisor` text COLLATE utf8mb4_unicode_ci,
  `motivo_rechazo` text COLLATE utf8mb4_unicode_ci,
  `bloqueado` tinyint(1) DEFAULT '0',
  `requiere_revision` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_sesion`),
  KEY `idx_sesion_caja` (`caja_id`),
  KEY `idx_sesion_apertura` (`postulante_apertura_id`),
  KEY `idx_sesion_cierre` (`postulante_cierre_id`),
  KEY `idx_sesion_revisor` (`postulante_revisor_id`),
  KEY `idx_sesion_turno` (`turno_id`),
  KEY `idx_sesion_fecha_operacion` (`fecha_operacion`),
  KEY `idx_sesion_estado` (`estado`),
  CONSTRAINT `fk_sesion_apertura` FOREIGN KEY (`postulante_apertura_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_sesion_caja` FOREIGN KEY (`caja_id`) REFERENCES `caja` (`id_caja`),
  CONSTRAINT `fk_sesion_cierre` FOREIGN KEY (`postulante_cierre_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_sesion_revisor` FOREIGN KEY (`postulante_revisor_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_sesion_turno` FOREIGN KEY (`turno_id`) REFERENCES `turno` (`id_turno`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesion_caja`
--

LOCK TABLES `sesion_caja` WRITE;
/*!40000 ALTER TABLE `sesion_caja` DISABLE KEYS */;
INSERT INTO `sesion_caja` VALUES (25,2,1,29,29,NULL,'CERRADA',0.00,0.00,42817.46,42817.46,10.00,'2026-05-07 14:36:46','2026-05-07 14:37:15','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(27,2,1,29,29,NULL,'CERRADA',42817.46,43360.86,43362.03,1.17,10.00,'2026-05-07 14:41:07','2026-05-07 14:42:09','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(28,2,1,29,29,NULL,'CERRADA',43362.03,43560.73,43561.33,0.60,10.00,'2026-05-07 14:45:33','2026-05-07 20:07:39','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(29,3,1,17,17,NULL,'CERRADA',0.00,0.00,27818.14,27818.14,10.00,'2026-05-07 14:52:33','2026-05-07 14:53:04','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(30,3,1,17,17,NULL,'CERRADA',27818.14,26358.79,26378.98,20.19,10.00,'2026-05-07 14:53:37','2026-05-07 14:58:03','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(31,3,1,17,17,NULL,'CERRADA',26378.98,26539.47,26448.38,-91.09,10.00,'2026-05-07 14:59:33','2026-05-07 20:00:17','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(32,5,1,22,22,NULL,'CERRADA',0.00,0.00,48905.89,48905.89,10.00,'2026-05-07 15:10:33','2026-05-07 15:11:02','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(33,5,1,22,22,NULL,'CERRADA',48905.89,51230.88,51246.73,15.85,10.00,'2026-05-07 15:11:55','2026-05-07 15:14:38','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(35,5,1,22,22,NULL,'CERRADA',51246.73,41324.07,41326.95,2.88,10.00,'2026-05-07 20:10:22','2026-05-07 20:16:16','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(37,4,1,1,1,NULL,'CERRADA',0.00,0.00,35751.00,35751.00,10.00,'2026-05-07 20:20:03','2026-05-07 20:20:24','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(38,4,1,71,71,NULL,'CERRADA',35751.00,36605.48,36625.28,19.80,10.00,'2026-05-07 20:21:21','2026-05-07 20:22:44','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(40,4,1,54,54,NULL,'CERRADA',36625.28,37201.98,37183.03,-18.95,10.00,'2026-05-07 20:25:27','2026-05-07 20:29:26','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(42,3,2,17,17,NULL,'CERRADA',26538.38,26750.07,26712.88,-37.19,10.00,'2026-05-08 03:56:35','2026-05-08 04:00:18','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(44,5,2,57,57,NULL,'CERRADA',41326.95,42750.25,42742.75,-7.50,10.00,'2026-05-08 04:13:43','2026-05-08 04:15:55','2026-05-07',NULL,NULL,NULL,NULL,NULL,1,0),(47,3,1,70,70,NULL,'CERRADA',26712.88,26906.28,26905.46,-0.82,10.00,'2026-05-08 12:26:18','2026-05-08 20:09:36','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(48,2,2,69,69,NULL,'CERRADA',43561.33,43800.47,43812.06,11.59,10.00,'2026-05-08 13:37:34','2026-05-08 13:42:11','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(50,4,2,73,73,NULL,'CERRADA',37183.03,37668.33,37669.50,1.17,10.00,'2026-05-08 16:06:15','2026-05-08 16:07:48','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(51,4,1,11,11,NULL,'CERRADA',37669.50,37996.10,38002.70,6.60,10.00,'2026-05-08 16:10:15','2026-05-08 20:01:55','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(52,5,1,22,22,NULL,'CERRADA',42742.75,34023.25,33900.22,-123.03,10.00,'2026-05-08 19:30:12','2026-05-08 19:51:46','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(53,2,1,29,29,NULL,'CERRADA',43812.47,44056.77,44052.82,-3.95,10.00,'2026-05-08 20:07:20','2026-05-08 20:10:04','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(55,3,2,69,69,NULL,'CERRADA',26905.46,26106.95,26118.48,11.53,10.00,'2026-05-08 21:21:12','2026-05-09 04:10:44','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(57,4,2,73,73,NULL,'CERRADA',37996.10,38667.70,38687.90,20.20,10.00,'2026-05-09 04:08:16','2026-05-09 04:09:48','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(58,5,2,53,53,NULL,'CERRADA',34020.22,34852.11,35356.52,504.41,10.00,'2026-05-09 04:10:36','2026-05-09 04:12:42','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(59,2,2,29,29,NULL,'CERRADA',44052.82,44438.62,44419.91,-18.71,10.00,'2026-05-09 04:12:00','2026-05-09 04:13:40','2026-05-08',NULL,NULL,NULL,NULL,NULL,1,0),(60,5,1,22,22,NULL,'CERRADA',35356.52,36939.97,36961.38,21.41,10.00,'2026-05-09 19:51:33','2026-05-09 19:52:40','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(61,3,1,73,73,NULL,'CERRADA',26118.48,25867.04,25846.72,-20.32,10.00,'2026-05-09 20:01:25','2026-05-09 20:03:56','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(62,2,1,57,57,NULL,'CERRADA',44419.91,44669.11,44761.63,92.52,10.00,'2026-05-09 20:19:23','2026-05-09 20:23:34','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(63,4,1,71,71,NULL,'CERRADA',38687.90,39003.80,39000.08,-3.72,10.00,'2026-05-09 21:13:42','2026-05-09 21:17:29','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(64,2,2,57,57,NULL,'CERRADA',44761.63,45241.53,45154.34,-87.19,10.00,'2026-05-10 03:56:22','2026-05-10 03:59:46','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(65,5,2,53,53,NULL,'CERRADA',36961.38,38127.46,38131.69,4.23,10.00,'2026-05-10 04:01:32','2026-05-10 04:05:06','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(66,3,2,17,17,NULL,'CERRADA',25846.72,25558.51,25568.80,10.29,10.00,'2026-05-10 04:04:08','2026-05-10 04:07:21','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(67,4,2,71,71,NULL,'CERRADA',39000.08,39658.98,39659.94,0.96,10.00,'2026-05-10 04:12:47','2026-05-10 04:16:04','2026-05-09',NULL,NULL,NULL,NULL,NULL,1,0),(68,3,1,17,17,NULL,'CERRADA',25568.80,23442.41,23447.08,4.67,10.00,'2026-05-10 19:16:52','2026-05-10 20:03:36','2026-05-10',NULL,NULL,NULL,NULL,NULL,1,0),(69,2,1,69,69,NULL,'CERRADA',45154.34,45533.34,45536.24,2.90,10.00,'2026-05-10 19:46:12','2026-05-10 20:02:26','2026-05-10',NULL,NULL,NULL,NULL,NULL,1,0),(70,5,1,53,53,NULL,'CERRADA',38131.69,39067.92,39069.44,1.52,10.00,'2026-05-10 20:03:00','2026-05-10 20:07:09','2026-05-10',NULL,NULL,NULL,NULL,NULL,1,0),(71,4,1,11,11,NULL,'CERRADA',39657.44,40050.84,40070.28,19.44,10.00,'2026-05-10 20:14:11','2026-05-10 20:18:08','2026-05-10',NULL,NULL,NULL,NULL,NULL,1,0);
/*!40000 ALTER TABLE `sesion_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesion_participante`
--

DROP TABLE IF EXISTS `sesion_participante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesion_participante` (
  `id_sesion_participante` int NOT NULL AUTO_INCREMENT,
  `sesion_id` int NOT NULL,
  `postulante_id` int NOT NULL,
  `rol_participacion` enum('CAJERA','VENDEDORA','SUPERVISORA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable_faltante` tinyint(1) NOT NULL DEFAULT '0',
  `observacion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_sesion_participante`),
  UNIQUE KEY `uq_sesion_participante` (`sesion_id`,`postulante_id`),
  KEY `idx_sp_postulante` (`postulante_id`),
  CONSTRAINT `fk_sp_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_sp_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesion_participante`
--

LOCK TABLES `sesion_participante` WRITE;
/*!40000 ALTER TABLE `sesion_participante` DISABLE KEYS */;
INSERT INTO `sesion_participante` VALUES (40,25,29,'CAJERA',1,NULL),(41,25,56,'VENDEDORA',0,NULL),(44,27,29,'CAJERA',1,NULL),(45,27,56,'VENDEDORA',0,NULL),(46,28,29,'CAJERA',1,NULL),(47,28,56,'VENDEDORA',0,NULL),(48,29,17,'CAJERA',1,NULL),(49,29,10,'VENDEDORA',0,NULL),(50,30,17,'CAJERA',1,NULL),(51,30,10,'VENDEDORA',0,NULL),(52,31,17,'CAJERA',1,NULL),(53,31,13,'VENDEDORA',0,NULL),(54,32,22,'CAJERA',1,NULL),(55,32,4,'VENDEDORA',0,NULL),(56,33,22,'CAJERA',1,NULL),(57,33,4,'VENDEDORA',0,NULL),(60,35,22,'CAJERA',1,NULL),(61,35,4,'VENDEDORA',0,NULL),(64,37,1,'CAJERA',1,NULL),(65,37,11,'VENDEDORA',0,NULL),(66,38,71,'CAJERA',1,NULL),(67,38,11,'VENDEDORA',0,NULL),(70,40,54,'CAJERA',1,NULL),(71,40,11,'VENDEDORA',0,NULL),(74,42,17,'CAJERA',1,NULL),(75,42,10,'VENDEDORA',0,NULL),(78,44,57,'CAJERA',1,NULL),(79,44,4,'VENDEDORA',0,NULL),(84,47,70,'CAJERA',1,NULL),(85,47,54,'VENDEDORA',0,NULL),(86,48,69,'CAJERA',1,NULL),(87,48,56,'VENDEDORA',0,NULL),(90,50,73,'CAJERA',1,NULL),(91,50,55,'VENDEDORA',0,NULL),(92,51,11,'CAJERA',1,NULL),(93,51,13,'VENDEDORA',0,NULL),(94,52,22,'CAJERA',1,NULL),(95,52,4,'VENDEDORA',0,NULL),(96,53,29,'CAJERA',1,NULL),(97,53,45,'VENDEDORA',0,NULL),(100,55,69,'CAJERA',1,NULL),(101,55,10,'VENDEDORA',0,NULL),(104,57,73,'CAJERA',1,NULL),(105,57,11,'VENDEDORA',0,NULL),(106,58,53,'CAJERA',1,NULL),(107,58,54,'VENDEDORA',0,NULL),(108,59,29,'CAJERA',1,NULL),(109,59,71,'VENDEDORA',0,NULL),(110,60,22,'CAJERA',1,NULL),(111,60,45,'VENDEDORA',0,NULL),(112,61,73,'CAJERA',1,NULL),(113,61,10,'VENDEDORA',0,NULL),(114,62,57,'CAJERA',1,NULL),(115,62,13,'VENDEDORA',0,NULL),(116,63,71,'CAJERA',1,NULL),(117,63,55,'VENDEDORA',0,NULL),(118,64,57,'CAJERA',1,NULL),(119,64,56,'VENDEDORA',0,NULL),(120,65,53,'CAJERA',1,NULL),(121,65,4,'VENDEDORA',0,NULL),(122,66,17,'CAJERA',1,NULL),(123,66,54,'VENDEDORA',0,NULL),(124,67,71,'CAJERA',1,NULL),(125,67,11,'VENDEDORA',0,NULL),(126,68,17,'CAJERA',1,NULL),(127,68,13,'VENDEDORA',0,NULL),(128,69,69,'CAJERA',1,NULL),(129,69,56,'VENDEDORA',0,NULL),(130,70,53,'CAJERA',1,NULL),(131,70,54,'VENDEDORA',0,NULL),(132,71,11,'CAJERA',1,NULL),(133,71,55,'VENDEDORA',0,NULL);
/*!40000 ALTER TABLE `sesion_participante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `situacion_vivienda`
--

DROP TABLE IF EXISTS `situacion_vivienda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `situacion_vivienda` (
  `id_situacion` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_situacion`),
  UNIQUE KEY `uq_vivienda_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `situacion_vivienda`
--

LOCK TABLES `situacion_vivienda` WRITE;
/*!40000 ALTER TABLE `situacion_vivienda` DISABLE KEYS */;
INSERT INTO `situacion_vivienda` VALUES (1,'Alquilada',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'Familiar',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Propia',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `situacion_vivienda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skill`
--

DROP TABLE IF EXISTS `skill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skill` (
  `id_skill` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_skill`),
  UNIQUE KEY `uq_skill_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skill`
--

LOCK TABLES `skill` WRITE;
/*!40000 ALTER TABLE `skill` DISABLE KEYS */;
INSERT INTO `skill` VALUES (1,'AgenteBCP',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'BPA',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'BPD',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(4,'BPOF',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(5,'Caja',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(6,'Excel',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(7,'Inyectables',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `skill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitud_cambio`
--

DROP TABLE IF EXISTS `solicitud_cambio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitud_cambio` (
  `id_solicitud` int NOT NULL AUTO_INCREMENT,
  `slot_id` int NOT NULL,
  `semana_id` int NOT NULL,
  `tipo` enum('COBERTURA','CAMBIO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `postulante_solicitante_id` int NOT NULL,
  `postulante_original_id` int DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP,
  `notas` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`),
  KEY `slot_id` (`slot_id`),
  KEY `semana_id` (`semana_id`),
  KEY `postulante_solicitante_id` (`postulante_solicitante_id`),
  KEY `postulante_original_id` (`postulante_original_id`),
  CONSTRAINT `solicitud_cambio_ibfk_1` FOREIGN KEY (`slot_id`) REFERENCES `horario_slot` (`id_slot`),
  CONSTRAINT `solicitud_cambio_ibfk_2` FOREIGN KEY (`semana_id`) REFERENCES `semana` (`id_semana`),
  CONSTRAINT `solicitud_cambio_ibfk_3` FOREIGN KEY (`postulante_solicitante_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `solicitud_cambio_ibfk_4` FOREIGN KEY (`postulante_original_id`) REFERENCES `postulante` (`id_postulante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_cambio`
--

LOCK TABLES `solicitud_cambio` WRITE;
/*!40000 ALTER TABLE `solicitud_cambio` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitud_cambio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_estudio`
--

DROP TABLE IF EXISTS `tipo_estudio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_estudio` (
  `id_tipo` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo`),
  UNIQUE KEY `uq_tipo_estudio_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_estudio`
--

LOCK TABLES `tipo_estudio` WRITE;
/*!40000 ALTER TABLE `tipo_estudio` DISABLE KEYS */;
INSERT INTO `tipo_estudio` VALUES (1,'Secundaria Completa',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(2,'Técnico',1,'2026-05-04 20:58:11','2026-05-04 20:58:11'),(3,'Universitario',1,'2026-05-04 20:58:11','2026-05-04 20:58:11');
/*!40000 ALTER TABLE `tipo_estudio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_movimiento`
--

DROP TABLE IF EXISTS `tipo_movimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_movimiento` (
  `id_tipo_movimiento` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_movimiento`),
  UNIQUE KEY `uq_tipo_mov_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_movimiento`
--

LOCK TABLES `tipo_movimiento` WRITE;
/*!40000 ALTER TABLE `tipo_movimiento` DISABLE KEYS */;
INSERT INTO `tipo_movimiento` VALUES (1,'INGRESO',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(2,'EGRESO',1,'2026-05-04 20:58:12','2026-05-04 20:58:12');
/*!40000 ALTER TABLE `tipo_movimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_personal`
--

DROP TABLE IF EXISTS `tipo_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_personal` (
  `codigo` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rango` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_personal`
--

LOCK TABLES `tipo_personal` WRITE;
/*!40000 ALTER TABLE `tipo_personal` DISABLE KEYS */;
INSERT INTO `tipo_personal` VALUES ('A1','Ventas - A1','Mayor a 70 operaciones',1),('B1','Ventas - B1','Entre 50 y 70 operaciones',2),('C1','Ventas - C1','Entre 40 y 50 operaciones',3),('D1','Ventas - D1','Menor a 40 operaciones',4),('X1','Caja - A1','Mayor a 200 operaciones',5),('Y1','Caja - B1','Hasta 200 operaciones',6),('Z1','Caja - C1','Hasta 150 operaciones',7);
/*!40000 ALTER TABLE `tipo_personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transferencia_caja`
--

DROP TABLE IF EXISTS `transferencia_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transferencia_caja` (
  `id_transferencia` int NOT NULL AUTO_INCREMENT,
  `sesion_origen_id` int NOT NULL,
  `sesion_destino_id` int DEFAULT NULL,
  `caja_origen_id` int NOT NULL,
  `caja_destino_id` int NOT NULL,
  `postulante_envia_id` int NOT NULL,
  `postulante_recibe_id` int DEFAULT NULL,
  `postulante_revisa_id` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_operacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comprobante_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_confirmacion_recepcion` timestamp NULL DEFAULT NULL,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `estado` enum('PENDIENTE_ENVIO','ENVIADO','RECIBIDO','OBSERVADO','RECHAZADO','APROBADO') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE_ENVIO',
  `observacion_recepcion` text COLLATE utf8mb4_unicode_ci,
  `observacion_revision` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_transferencia`),
  KEY `idx_tf_sesion_origen` (`sesion_origen_id`),
  KEY `idx_tf_sesion_destino` (`sesion_destino_id`),
  KEY `idx_tf_caja_origen` (`caja_origen_id`),
  KEY `idx_tf_caja_destino` (`caja_destino_id`),
  KEY `idx_tf_envia` (`postulante_envia_id`),
  KEY `idx_tf_recibe` (`postulante_recibe_id`),
  KEY `idx_tf_revisa` (`postulante_revisa_id`),
  KEY `idx_tf_estado` (`estado`),
  CONSTRAINT `fk_tf_caja_destino` FOREIGN KEY (`caja_destino_id`) REFERENCES `caja` (`id_caja`),
  CONSTRAINT `fk_tf_caja_origen` FOREIGN KEY (`caja_origen_id`) REFERENCES `caja` (`id_caja`),
  CONSTRAINT `fk_tf_envia` FOREIGN KEY (`postulante_envia_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_tf_recibe` FOREIGN KEY (`postulante_recibe_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_tf_revisa` FOREIGN KEY (`postulante_revisa_id`) REFERENCES `postulante` (`id_postulante`),
  CONSTRAINT `fk_tf_sesion_destino` FOREIGN KEY (`sesion_destino_id`) REFERENCES `sesion_caja` (`id_sesion`),
  CONSTRAINT `fk_tf_sesion_origen` FOREIGN KEY (`sesion_origen_id`) REFERENCES `sesion_caja` (`id_sesion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transferencia_caja`
--

LOCK TABLES `transferencia_caja` WRITE;
/*!40000 ALTER TABLE `transferencia_caja` DISABLE KEYS */;
/*!40000 ALTER TABLE `transferencia_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turno`
--

DROP TABLE IF EXISTS `turno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turno` (
  `id_turno` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_turno`),
  UNIQUE KEY `uq_turno_desc` (`descripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turno`
--

LOCK TABLES `turno` WRITE;
/*!40000 ALTER TABLE `turno` DISABLE KEYS */;
INSERT INTO `turno` VALUES (1,'Mañana',1,'2026-05-04 20:58:12','2026-05-04 20:58:12'),(2,'Tarde',1,'2026-05-04 20:58:12','2026-05-04 20:58:12');
/*!40000 ALTER TABLE `turno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `postulante_id` int NOT NULL,
  `rol_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`postulante_id`),
  UNIQUE KEY `uq_usuario_username` (`username`),
  KEY `idx_usuario_rol` (`rol_id`),
  CONSTRAINT `fk_usuario_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,2,'GIANCARLOVC','$2y$10$WdDI/yNovOtkl6.1P0fFVeCn5JDc2bwm0wOmcRGg/SDNYZf.oTJC.',1,'2026-05-04 20:58:12','2026-05-06 08:38:31'),(2,1,'SOLANGECC','$2y$10$hJUz5FU.fpTe3fg0ofQypOeTx7jlG1CDQf06F0ZHIQIBzjCbeWQ6y',0,'2026-05-04 21:06:58','2026-05-06 02:26:31'),(4,1,'DARIANABC','$2y$10$O5oR0esntN3OzN9Sa7TkB.jrB3MAuj/pukrAw.CMvhAaCbYCKksXC',1,'2026-05-06 00:30:16','2026-05-06 00:43:17'),(5,1,'PATRICIAOP','$2y$10$MRol58reXroFEKA9KPB2p.rXS3J1zQbZe6f1EPWFQ.tksghQ2DgFW',1,'2026-05-06 00:44:13','2026-05-06 00:44:29'),(10,1,'KARENME','$2y$10$7SA6hAGU62Eql9SP/w4pC.IngAObLwBa89NToGx9J9jtEFX827JXC',1,'2026-05-06 00:57:18','2026-05-06 00:58:38'),(11,1,'FIORELLACR','$2y$10$bzD/L/E00OiLUyqTURSv2ObNZzULqUmT6FxSayFYw4ht1D/4neeom',1,'2026-05-05 00:57:08','2026-05-05 20:35:17'),(12,1,'SHARIKRP','$2y$10$G95U/AdGGjGwTbyqkxC/buxvWKBketkYIgNXXegDih2zkCWFpGb7a',0,'2026-05-06 01:07:45','2026-05-06 01:53:47'),(13,1,'MONICAQC','$2y$10$RQVUnjY3Ka2GxIb/SipEMuaYAxU57O9a3mz8G57kPTKWVvBNFnsx6',1,'2026-05-06 01:10:08','2026-05-06 01:10:27'),(17,1,'GERALDINNEQA','$2y$10$yfbQNjv.BniXcr8xfw3iW.3i2l70PsZw3llYaYfe2edychBAW4xMm',1,'2026-05-06 01:12:37','2026-05-06 01:29:17'),(19,1,'ELIZABETHFS','$2y$10$5QoLrFKDQOIDeh4sqfIhSO73pb/VQH7z3/1k.SzqgyZ7j.gRq.PTu',1,'2026-05-06 01:31:08','2026-05-06 01:31:31'),(22,1,'YOLVIPF','$2y$10$a2s8Uhv2axrDDpxcOOeiRucVAT9xbMGRRqMcpZcT2asHNvZQiSDbi',1,'2026-05-06 01:33:59','2026-05-06 01:34:35'),(29,1,'YENIFERQL','$2y$10$rQpl7cOqBT7upb8lLdMC6ebX24QjbYV.FOKt0dnUoUh.Gj7tmvMxC',1,'2026-05-06 01:36:12','2026-05-06 01:36:32'),(45,1,'ERIKAGH','$2y$10$dzmWBJxPulc4NgUmkzag4eTMrWcFfBooZP0yNhc6apw9NOrzBc3ku',1,'2026-05-06 01:39:11','2026-05-06 01:39:35'),(51,1,'DAYANABA','$2y$10$2Kmz.vEOxw76NpjU26RyZ.ghm72IM0LxOoRQlM2yQwPT2WoaHKA0O',1,'2026-05-06 01:41:25','2026-05-06 01:41:49'),(52,1,'MERLINDABC','$2y$10$vr8JtZQCE2S5N9/woHtnyejlX6rwMRHThOAXHghq5O6cMFAwds1Fm',0,'2026-05-06 01:43:04','2026-05-06 01:54:27'),(53,1,'LUCIAAC','$2y$10$bh8zNb.84KaUwFG8bU9BTeKzFyOT7XIdA2fojxHRQmt5veyjDhwH.',1,'2026-05-06 01:45:57','2026-05-06 01:46:14'),(54,1,'YOVALYDR','$2y$10$4YdzBAsfOywcKzMl0qWHgO/89kCuDyYOGLzf4Jtafa3Km6fft7MvO',1,'2026-05-06 01:49:07','2026-05-06 01:49:27'),(55,1,'ANAHIMC','$2y$10$C4F3jVpvZXCNvcU4ZeF6o.FHYiwJMAUVzZBHdwXqzamyEcZFQNZae',1,'2026-05-06 16:31:29','2026-05-06 16:32:21'),(56,1,'MARIAYC','$2y$10$.oebRo1ph130j2f9ZIlUX.YYPUkCblZSYglkAgxyApLhhgR4C2HrO',1,'2026-05-06 15:23:21','2026-05-06 15:24:05'),(57,1,'ROSATC','$2y$10$8YBlVmWIrF9dbJTGpb0xdOS01wS3Mig65HWNCU6mjB/QglmMnDt3y',1,'2026-05-06 15:26:19','2026-05-06 15:27:21'),(59,1,'SANDRARQ','$2y$10$GI0Lijzd8dDv91YrABcJH.ivLU/5RGfb6Kmrhf64dsridwZH0vTlm',1,'2026-05-06 01:50:13','2026-05-06 01:50:29'),(60,1,'ESWINSR','$2y$10$C1q500vh5PJieW7XoMlAq.xV6MC8tRSaW4WU.qoI3DQ717XY6JdK6',1,'2026-05-06 01:51:04','2026-05-06 01:51:24'),(61,1,'YADIRABN','$2y$10$BREj.ELgFAihrq3rrj/BQOelgegmoqihYPrtxkTZuWakdkF3GisDC',1,'2026-05-06 01:52:12','2026-05-06 01:52:28'),(68,1,'BLOQUEADO','$2y$10$fSEmYth8OikhKLOib6bEAeC1A8shRZOQRJ6inf6Mtjo9XU03KQWZe',1,'2026-05-06 17:07:03','2026-05-06 17:10:31'),(69,1,'FLORHY','$2y$10$ZwbpuSQZszRof2zf8t5qcO2U68utcFC6jxS5RTZuyB/iSbPcylkAa',1,'2026-05-06 18:09:14','2026-05-06 18:09:56'),(70,1,'DEBORAPA','$2y$10$xfu7TGxUqYXVwVehXnyuDOnnumjeg9BHZhxxDgHmHAfU7Kgt2xMs.',1,'2026-05-06 21:01:14','2026-05-06 21:02:08'),(71,1,'ESTHERFH','$2y$10$Fvwu0dlWNpdMcOSg4Q8B6uLuE9.4/rm8GixY3Drzy0wABhI5IYfre',1,'2026-05-06 21:03:37','2026-05-06 21:04:06'),(72,2,'MARINA','$2y$10$y6Mn3lCSTyeaDda2r0OOLuhnCW/AKpdmrsIs4Ad4Vyo6Q0eZ2cktm',1,'2026-05-06 23:34:30','2026-05-06 23:35:23'),(73,1,'VICTORIAHA','$2y$10$pRLXci0rYu3eL4KXYWFdyuX0mKcug1HjNfsSNCAFG4tWG7wuIvhg2',1,'2026-05-07 02:06:31','2026-05-07 02:06:53');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-10 21:42:42
