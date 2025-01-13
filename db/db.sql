-- Configuraciones iniciales
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Crear tabla de información personal (tabla principal)
CREATE TABLE `informacion_personal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL,
  `nombre_completo` varchar(255) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `rol` enum('Administrador','Usuario') DEFAULT 'Usuario',
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nickname` (`nickname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear tabla de usuarios (relacionada con informacion_personal por nickname)
CREATE TABLE `usuarios` (
  `nickname` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,  
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_nickname` (`nickname`),
  CONSTRAINT `fk_nickname` FOREIGN KEY (`nickname`) REFERENCES `informacion_personal` (`nickname`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;


ALTER TABLE `informacion_personal` 
ADD COLUMN `correo` varchar(255) DEFAULT NULL AFTER `nombre_completo`,
ADD COLUMN `telefono` varchar(15) DEFAULT NULL AFTER `correo`,
ADD COLUMN `rol` enum('Administrador','Usuario') DEFAULT 'Usuario' AFTER `telefono`;