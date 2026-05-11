-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema sb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema sb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `sb` DEFAULT CHARACTER SET utf8mb3 ;
USE `sb` ;

-- -----------------------------------------------------
-- Table `sb`.`etapa`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`etapa` (
  `id_etapa` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etapa`),
  UNIQUE INDEX `estados_postulacion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`genero`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`genero` (
  `id_genero` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(20) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_genero`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`situacion_vivienda`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`situacion_vivienda` (
  `id_situacion` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_situacion`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`postulante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`postulante` (
  `id_postulante` INT NOT NULL AUTO_INCREMENT,
  `nombres` VARCHAR(100) NULL,
  `apellidos` VARCHAR(100) NULL,
  `genero_id` INT NULL DEFAULT NULL,
  `fecha_nacimiento` DATE NULL DEFAULT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `telefono` VARCHAR(15) NULL DEFAULT NULL,
  `situacion_vivienda_id` INT NULL DEFAULT NULL,
  `num_documento` VARCHAR(8) NOT NULL,
  `direccion` VARCHAR(255) NULL DEFAULT NULL,
  `distrito` VARCHAR(100) NULL,
  `calificacion` DECIMAL(5,2) NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cv_url` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id_postulante`),
  INDEX `genero_id_idx` (`genero_id` ASC) VISIBLE,
  INDEX `situacion_vivienda_id_idx` (`situacion_vivienda_id` ASC) VISIBLE,
  UNIQUE INDEX `num_documento_UNIQUE` (`num_documento` ASC) VISIBLE,
  CONSTRAINT `fk_postulante_genero`
    FOREIGN KEY (`genero_id`)
    REFERENCES `sb`.`genero` (`id_genero`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_postulante_situacion_vivienda`
    FOREIGN KEY (`situacion_vivienda_id`)
    REFERENCES `sb`.`situacion_vivienda` (`id_situacion`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`experiencia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`experiencia` (
  `id_experiencia` INT NOT NULL AUTO_INCREMENT,
  `postulante_id` INT NULL DEFAULT NULL,
  `empresa` VARCHAR(150) NULL DEFAULT NULL,
  `cargo` VARCHAR(100) NULL DEFAULT NULL,
  `funciones` VARCHAR(150) NULL,
  `fecha_inicio` DATE NULL DEFAULT NULL,
  `fecha_fin` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id_experiencia`),
  INDEX `postulante_id_idx` (`postulante_id` ASC) VISIBLE,
  CONSTRAINT `fk_experiencia_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`institucion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`institucion` (
  `id_institucion` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(150) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_institucion`),
  UNIQUE INDEX `nombre_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`nivel`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`nivel` (
  `id_nivel` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nivel`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`skill`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`skill` (
  `id_skill` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_skill`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`postulante_skill`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`postulante_skill` (
  `postulante_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  `nivel_id` INT NULL DEFAULT NULL,
  PRIMARY KEY (`postulante_id`, `skill_id`),
  INDEX `nivel_id_idx` (`nivel_id` ASC) VISIBLE,
  INDEX `id_postulante_idx` (`skill_id` ASC) VISIBLE,
  CONSTRAINT `fk_postulante_skill_skill`
    FOREIGN KEY (`skill_id`)
    REFERENCES `sb`.`skill` (`id_skill`),
  CONSTRAINT `fk_postulante_skill_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_postulante_skill_nivel`
    FOREIGN KEY (`nivel_id`)
    REFERENCES `sb`.`nivel` (`id_nivel`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`puesto`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`puesto` (
  `id_puesto` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_puesto`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`postulacion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`postulacion` (
  `id_postulacion` INT NOT NULL AUTO_INCREMENT,
  `postulante_id` INT NOT NULL,
  `puesto_id` INT NOT NULL,
  `fecha_postulacion` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `etapa_id` INT NULL,
  `postulacioncol` VARCHAR(45) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_postulacion`),
  INDEX `postulante_id_idx` (`postulante_id` ASC) VISIBLE,
  INDEX `puesto_id_idx` (`puesto_id` ASC) VISIBLE,
  INDEX `estado_id_idx` (`etapa_id` ASC) VISIBLE,
  UNIQUE INDEX `uq_postulante_puesto` (`postulante_id` ASC, `puesto_id` ASC) VISIBLE,
  CONSTRAINT `fk_postulacion_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_postulacion_puesto`
    FOREIGN KEY (`puesto_id`)
    REFERENCES `sb`.`puesto` (`id_puesto`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_postulacion_etapa`
    FOREIGN KEY (`etapa_id`)
    REFERENCES `sb`.`etapa` (`id_etapa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`tipo_estudio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`tipo_estudio` (
  `id_tipo` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`turno`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`turno` (
  `id_turno` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(20) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_turno`),
  UNIQUE INDEX `nombre_turno_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`preferencias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`preferencias` (
  `turno_id` INT NOT NULL,
  `postulante_id` INT NOT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`turno_id`, `postulante_id`),
  INDEX `fk_turno_has_postulante_postulante1_idx` (`postulante_id` ASC) VISIBLE,
  INDEX `fk_turno_has_postulante_turno1_idx` (`turno_id` ASC) VISIBLE,
  CONSTRAINT `fl_preferencia_turno`
    FOREIGN KEY (`turno_id`)
    REFERENCES `sb`.`turno` (`id_turno`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_preferencia_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `sb`.`estado`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`estado` (
  `id_estado` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estado`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`estudio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`estudio` (
  `id_estudio` INT NOT NULL AUTO_INCREMENT,
  `postulante_id` INT NOT NULL,
  `tipo_id` INT NOT NULL,
  `institucion_id` INT NOT NULL,
  `estado_id` INT NOT NULL,
  `fecha_inicio` DATE NULL,
  `fecha_fin` DATE NULL,
  PRIMARY KEY (`id_estudio`),
  INDEX `fk_estudios_postulante_idx` (`postulante_id` ASC) VISIBLE,
  INDEX `fk_estudios_tipo_idx` (`tipo_id` ASC) VISIBLE,
  INDEX `fk_estudios_estado_idx` (`estado_id` ASC) VISIBLE,
  INDEX `fk_estudios_institucion_idx` (`institucion_id` ASC) VISIBLE,
  UNIQUE INDEX `uq_estudio_postulante` (`postulante_id` ASC, `tipo_id` ASC, `institucion_id` ASC, `fecha_inicio` ASC) VISIBLE,
  CONSTRAINT `fk_estudios_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_estudios_tipo`
    FOREIGN KEY (`tipo_id`)
    REFERENCES `sb`.`tipo_estudio` (`id_tipo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_estudios_estado`
    FOREIGN KEY (`estado_id`)
    REFERENCES `sb`.`estado` (`id_estado`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_estudios_institucion`
    FOREIGN KEY (`institucion_id`)
    REFERENCES `sb`.`institucion` (`id_institucion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`rol`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`rol` (
  `id_rol` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rol`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`usuario` (
  `postulante_id` INT NOT NULL,
  `rol_id` INT NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `activo` TINYINT(1) NULL DEFAULT '1',
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`postulante_id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) VISIBLE,
  INDEX `fk_usuario_rol_idx` (`rol_id` ASC) VISIBLE,
  CONSTRAINT `fk_usuario_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_rol`
    FOREIGN KEY (`rol_id`)
    REFERENCES `sb`.`rol` (`id_rol`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`local`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`local` (
  `id_local` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NULL,
  `direccion` VARCHAR(150) NULL,
  `id_encargado` INT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_local`),
  INDEX `fk_local_postulante_idx` (`id_encargado` ASC) VISIBLE,
  CONSTRAINT `fk_local_postulante`
    FOREIGN KEY (`id_encargado`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`caja`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`caja` (
  `id_caja` INT NOT NULL AUTO_INCREMENT,
  `local_id` INT NOT NULL,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_caja`),
  INDEX `fk_caja_local_idx` (`local_id` ASC) VISIBLE,
  UNIQUE INDEX `uq_caja_local_descripcion` (`local_id` ASC, `descripcion` ASC) VISIBLE,
  CONSTRAINT `fk_caja_local`
    FOREIGN KEY (`local_id`)
    REFERENCES `sb`.`local` (`id_local`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`sesion_caja`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`sesion_caja` (
  `id_sesion` INT NOT NULL AUTO_INCREMENT,
  `caja_id` INT NOT NULL,
  `turno_id` INT NOT NULL,
  `postulante_apertura_id` INT NOT NULL,
  `postulante_cierre_id` INT NULL,
  `postulante_revisor_id` INT NULL,
  `estado` ENUM('ABIERTA', 'CERRADA', 'EN_REVISION', 'APROBADA', 'OBSERVADA', 'RECHAZADA') NOT NULL DEFAULT 'ABIERTA',
  `saldo_inicial` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `saldo_final_sistema` DECIMAL(10,2) NULL,
  `saldo_final_contado` DECIMAL(10,2) NULL,
  `diferencia_final` DECIMAL(10,2) NULL,
  `margen_permitido` DECIMAL(10,2) NOT NULL DEFAULT 10.00,
  `fecha_apertura` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` TIMESTAMP NULL,
  `fecha_operacion` DATE NOT NULL,
  `fecha_envio_revision` TIMESTAMP NULL,
  `fecha_revision` TIMESTAMP NULL,
  `observacion_cierre` TEXT NULL,
  `observacion_revisor` TEXT NULL,
  `motivo_rechazo` TEXT NULL,
  `bloqueado` TINYINT(1) NULL DEFAULT 0,
  `requiere_revision` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_sesion`),
  INDEX `fk_sesion_caja_caja_idx` (`caja_id` ASC) VISIBLE,
  INDEX `fk_sesion_caja_postulante_idx` (`postulante_apertura_id` ASC) VISIBLE,
  INDEX `fk_sesion_caja_cierre_idx` (`postulante_cierre_id` ASC) VISIBLE,
  INDEX `fk_sesion_caja_revisor_idx` (`postulante_revisor_id` ASC) VISIBLE,
  INDEX `fk_sesion_caja_turno_idx` (`turno_id` ASC) VISIBLE,
  UNIQUE INDEX `fk_sesion_caja_restriccion` (`caja_id` ASC, `turno_id` ASC, `fecha_operacion` ASC) VISIBLE,
  INDEX `idx_sesion_caja_fecha_operacion` (`fecha_operacion` ASC) VISIBLE,
  INDEX `idx_sesion_caja_estado` (`estado` ASC) VISIBLE,
  CONSTRAINT `fk_sesion_caja_caja`
    FOREIGN KEY (`caja_id`)
    REFERENCES `sb`.`caja` (`id_caja`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sesion_caja_apertura`
    FOREIGN KEY (`postulante_apertura_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sesion_caja_cierre`
    FOREIGN KEY (`postulante_cierre_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sesion_caja_revisor`
    FOREIGN KEY (`postulante_revisor_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sesion_caja_turno`
    FOREIGN KEY (`turno_id`)
    REFERENCES `sb`.`turno` (`id_turno`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`tipo_movimiento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`tipo_movimiento` (
  `id_tipo_movimiento` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_movimiento`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`modo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`modo` (
  `id_modo` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_modo`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`movimiento_sesion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`movimiento_sesion` (
  `id_movimiento` INT NOT NULL AUTO_INCREMENT,
  `sesion_id` INT NOT NULL,
  `tipo_movimiento_id` INT NOT NULL,
  `modo_id` INT NULL,
  `postulante_registro_id` INT NOT NULL,
  `postulante_revision_id` INT NULL,
  `descripcion` VARCHAR(250) NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `numero_operacion` VARCHAR(100) NULL,
  `comprobante_url` VARCHAR(255) NULL,
  `fecha_movimiento` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` TIMESTAMP NULL,
  `fecha_anulacion` TIMESTAMP NULL,
  `estado` ENUM('PENDIENTE', 'APROBADO', 'OBSERVADO', 'RECHAZADO', 'ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  `motivo_anulacion` TEXT NULL,
  `observacion_revision` TEXT NULL,
  PRIMARY KEY (`id_movimiento`),
  INDEX `fk_gasto_sesion_caja_idx` (`sesion_id` ASC) VISIBLE,
  INDEX `fk_movimiento_sesion_tipo_movimiento_idx` (`tipo_movimiento_id` ASC) VISIBLE,
  INDEX `fk_movimiento_sesion_postulante_idx` (`postulante_registro_id` ASC) VISIBLE,
  INDEX `fk_movimiento_sesion_revision_idx` (`postulante_revision_id` ASC) VISIBLE,
  INDEX `fk_movimiento_sesion_modo_idx` (`modo_id` ASC) VISIBLE,
  INDEX `idx_movimiento_sesion_fecha` (`fecha_movimiento` ASC) VISIBLE,
  INDEX `idx_movimiento_sesion_estado` (`estado` ASC) VISIBLE,
  CONSTRAINT `fk_gasto_sesion_caja`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movimiento_sesion_tipo_movimiento`
    FOREIGN KEY (`tipo_movimiento_id`)
    REFERENCES `sb`.`tipo_movimiento` (`id_tipo_movimiento`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movimiento_sesion_registro`
    FOREIGN KEY (`postulante_registro_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movimiento_sesion_revision`
    FOREIGN KEY (`postulante_revision_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_movimiento_sesion_modo`
    FOREIGN KEY (`modo_id`)
    REFERENCES `sb`.`modo` (`id_modo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`detalle_cuadre`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`detalle_cuadre` (
  `sesion_id` INT NOT NULL,
  `monto_monedas` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `monto_billetes_caja` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `monto_billetes_caja_fuerte` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `monto_yape_plin` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `monto_visas` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `monto_bcp` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `observacion_cierre` TEXT NULL,
  `total_efectivo_contado` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_contado_general` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_ventas_sistema` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_gastos_sistema` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_esperado_sistema` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `diferencia` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `resultado_cuadre` ENUM('CONSISTENTE', 'SOBRANTE', 'FALTANTE') NULL,
  `saldo_proximo_dia` DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sesion_id`),
  CONSTRAINT `fk_detalle_cuadre_sesion_caja`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`auditoria_cuadre`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`auditoria_cuadre` (
  `id_auditoria` INT NOT NULL AUTO_INCREMENT,
  `sesion_id` INT NOT NULL,
  `postulante_id` INT NOT NULL,
  `accion` VARCHAR(30) NOT NULL,
  `campo_modificado` VARCHAR(100) NULL,
  `valor_anterior` TEXT NULL,
  `valor_nuevo` TEXT NULL,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  INDEX `fk_auditoria_cuadre_sesion_idx` (`sesion_id` ASC) VISIBLE,
  INDEX `fk_auditoria_cuadre_sesion_idx1` (`postulante_id` ASC) VISIBLE,
  CONSTRAINT `fk_auditoria_cuadre_sesion`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_auditoria_cuadre_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`transferencia_caja`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`transferencia_caja` (
  `id_transferencia` INT NOT NULL AUTO_INCREMENT,
  `sesion_origen_id` INT NOT NULL,
  `sesion_destino_id` INT NULL,
  `caja_origen_id` INT NOT NULL,
  `caja_destino_id` INT NOT NULL,
  `postulante_envia_id` INT NOT NULL,
  `postulante_recibe_id` INT NULL,
  `postulante_revisa_id` INT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `numero_operacion` VARCHAR(100) NULL,
  `comprobante_url` VARCHAR(255) NULL,
  `fecha_envio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_confirmacion_recepcion` TIMESTAMP NULL,
  `fecha_revision` TIMESTAMP NULL,
  `estado` ENUM('PENDIENTE_ENVIO', 'ENVIADO', 'RECIBIDO', 'OBSERVADO', 'RECHAZADO', 'APROBADO') NULL DEFAULT 'PENDIENTE_ENVIO',
  `observacion_recepcion` TEXT NULL,
  `observacion_revision` TEXT NULL,
  PRIMARY KEY (`id_transferencia`),
  INDEX `fk_transferencia_sesion_origen_idx` (`sesion_origen_id` ASC) VISIBLE,
  INDEX `fk_transferencia_sesion_destino_idx` (`sesion_destino_id` ASC) VISIBLE,
  INDEX `fk_transferencia_caja_origen_idx` (`caja_origen_id` ASC) VISIBLE,
  INDEX `fk_transferencia_caja_destino_idx` (`caja_destino_id` ASC) VISIBLE,
  INDEX `fk_transferencia_envia_idx` (`postulante_envia_id` ASC) VISIBLE,
  INDEX `fk_transferencia_recibe_idx` (`postulante_recibe_id` ASC) VISIBLE,
  INDEX `fk_transferencia_revisa_idx` (`postulante_revisa_id` ASC) VISIBLE,
  INDEX `idx_transferencia_estado` (`estado` ASC) VISIBLE,
  INDEX `idx_transferencia_fecha_envio` (`fecha_envio` ASC) VISIBLE,
  CONSTRAINT `fk_transferencia_sesion_origen`
    FOREIGN KEY (`sesion_origen_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_transferencia_sesion_destino`
    FOREIGN KEY (`sesion_destino_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_transferencia_caja_origen`
    FOREIGN KEY (`caja_origen_id`)
    REFERENCES `sb`.`caja` (`id_caja`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_transferencia_caja_destino`
    FOREIGN KEY (`caja_destino_id`)
    REFERENCES `sb`.`caja` (`id_caja`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_transferencia_envia`
    FOREIGN KEY (`postulante_envia_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_transferencia_recibe`
    FOREIGN KEY (`postulante_recibe_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_transferencia_revisa`
    FOREIGN KEY (`postulante_revisa_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`pago_personal`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`pago_personal` (
  `id_pago_personal` INT NOT NULL AUTO_INCREMENT,
  `sesion_id` INT NOT NULL,
  `postulante_emisor_id` INT NOT NULL,
  `postulante_beneficiario_id` INT NOT NULL,
  `postulante_revisor_id` INT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `numero_operacion` VARCHAR(100) NULL,
  `comprobante_url` VARCHAR(255) NULL,
  `fecha_pago` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_confirmacion_beneficiario` TIMESTAMP NULL,
  `fecha_revision` TIMESTAMP NULL,
  `estado` ENUM('PENDIENTE', 'PAGADO', 'CONFIRMADO_BENEFICIARIO', 'OBSERVADO', 'RECHAZADO', 'APROBADO') NOT NULL DEFAULT 'PENDIENTE',
  `observacion_beneficiario` TEXT NULL,
  `observacion_revision` TEXT NULL,
  PRIMARY KEY (`id_pago_personal`),
  INDEX `fk_pago_personal_sesion_idx` (`sesion_id` ASC) VISIBLE,
  INDEX `fk_pago_personal_emisor_idx` (`postulante_emisor_id` ASC) VISIBLE,
  INDEX `fk_pago_personal_beneficiario_idx` (`postulante_beneficiario_id` ASC) VISIBLE,
  INDEX `fk_pago_personal_revisor_idx` (`postulante_revisor_id` ASC) VISIBLE,
  INDEX `idx_pago_personal_estado` (`estado` ASC) VISIBLE,
  INDEX `idx_pago_personal_fecha_pago` (`fecha_pago` ASC) VISIBLE,
  CONSTRAINT `fk_pago_personal_sesion`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pago_personal_emisor`
    FOREIGN KEY (`postulante_emisor_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pago_personal_beneficiario`
    FOREIGN KEY (`postulante_beneficiario_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pago_personal_revisor`
    FOREIGN KEY (`postulante_revisor_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`sesion_participante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`sesion_participante` (
  `id_sesion_participante` INT NOT NULL AUTO_INCREMENT,
  `sesion_id` INT NOT NULL,
  `postulante_id` INT NOT NULL,
  `rol_participacion` ENUM('CAJERA', 'VENDEDORA', 'SUPERVISORA') NOT NULL,
  `responsable_faltante` TINYINT(1) NOT NULL DEFAULT 0,
  `observacion` TEXT NULL,
  PRIMARY KEY (`id_sesion_participante`),
  INDEX `fk_sesion_participante_sesion_idx` (`sesion_id` ASC) VISIBLE,
  INDEX `fk_sesion_participante_postulante_idx` (`postulante_id` ASC) VISIBLE,
  UNIQUE INDEX `uq_sesion_participante` (`sesion_id` ASC, `postulante_id` ASC) VISIBLE,
  CONSTRAINT `fk_sesion_participante_sesion`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sesion_participante_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`rectificacion_cuadre`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`rectificacion_cuadre` (
  `id_rectificacion` INT NOT NULL AUTO_INCREMENT,
  `sesion_id` INT NOT NULL,
  `postulante_registra_id` INT NOT NULL,
  `postulante_responsable_id` INT NULL,
  `tipo_rectificacion` ENUM('DEVOLUCION_DINERO', 'DINERO_ENCONTRADO', 'AJUSTE_CONTEO', 'COMPENSACION', 'OTRO') NOT NULL,
  `modo_id` INT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `descripcion_contexto` TEXT NOT NULL,
  `justificacion` TEXT NULL,
  `comprobante_url` VARCHAR(255) NULL,
  `fecha_rectificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` ENUM('PENDIENTE', 'APROBADA', 'RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  `postulante_revisa_id` INT NULL,
  `fecha_revision` TIMESTAMP NULL,
  `observacion_revision` TEXT NULL,
  PRIMARY KEY (`id_rectificacion`),
  INDEX `fk_rectificacion_sesion_idx` (`sesion_id` ASC) VISIBLE,
  INDEX `fk_rectificacion_registra_idx` (`postulante_registra_id` ASC) VISIBLE,
  INDEX `fk_rectificacion_responsable_idx` (`postulante_responsable_id` ASC) VISIBLE,
  INDEX `fk_rectificacion_revisa_idx` (`postulante_revisa_id` ASC) VISIBLE,
  INDEX `fk_rectificacion_modo_idx` (`modo_id` ASC) VISIBLE,
  INDEX `idx_rectificacion_estado` (`estado` ASC) VISIBLE,
  INDEX `idx_rectificacion_fecha` (`fecha_rectificacion` ASC) VISIBLE,
  CONSTRAINT `fk_rectificacion_sesion`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rectificacion_registra`
    FOREIGN KEY (`postulante_registra_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rectificacion_responsable`
    FOREIGN KEY (`postulante_responsable_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rectificacion_revisa`
    FOREIGN KEY (`postulante_revisa_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rectificacion_modo`
    FOREIGN KEY (`modo_id`)
    REFERENCES `sb`.`modo` (`id_modo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`contacto_emergencia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`contacto_emergencia` (
  `id_contacto_emergencia` INT NOT NULL AUTO_INCREMENT,
  `postulante_id` INT NOT NULL,
  `nombre_completo` VARCHAR(150) NOT NULL,
  `parentesco` VARCHAR(50) NOT NULL,
  `telefono` VARCHAR(15) NOT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contacto_emergencia`),
  INDEX `fk_contacto_emergencia_postulante_idx` (`postulante_id` ASC) VISIBLE,
  CONSTRAINT `fk_contacto_emergencia_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`especialidad`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`especialidad` (
  `id_especialidad` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_especialidad`),
  UNIQUE INDEX `descripcion_UNIQUE` (`descripcion` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`postulante_especialidad`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`postulante_especialidad` (
  `postulante_id` INT NOT NULL,
  `especialidad_id` INT NOT NULL,
  PRIMARY KEY (`postulante_id`, `especialidad_id`),
  INDEX `fk_postulante_especialidad_especialidad_idx` (`especialidad_id` ASC) VISIBLE,
  CONSTRAINT `fk_postulante_especialidad_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_postulante_especialidad_especialidad`
    FOREIGN KEY (`especialidad_id`)
    REFERENCES `sb`.`especialidad` (`id_especialidad`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`auditoria_sistema`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`auditoria_sistema` (
  `id_auditoria` INT NOT NULL AUTO_INCREMENT,
  `postulante_id` INT NOT NULL,
  `tabla_afectada` VARCHAR(100) NOT NULL,
  `id_registro` INT NULL,
  `accion` VARCHAR(30) NOT NULL,
  `descripcion` TEXT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  INDEX `fk_auditoria_sistema_postulante_idx` (`postulante_id` ASC) VISIBLE,
  CONSTRAINT `fk_auditoria_sistema_postulante`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`asistencia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`asistencia` (
  `id_asistencia` INT NOT NULL AUTO_INCREMENT,
  `postulante_id` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `hora_ingreso` DATETIME NULL,
  `hora_salida` DATETIME NULL,
  `estado` ENUM('A TIEMPO', 'TARDE', 'FALTA') NOT NULL DEFAULT 'A TIEMPO',
  `justificacion` TEXT NULL,
  `observacion` ENUM('PROCEDE', 'NO PROCEDE', 'PENDIENTE') NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_asistencia`),
  INDEX `fk_asistencia_postulante_idx` (`postulante_id` ASC) VISIBLE,
  UNIQUE INDEX `uniq_usuario_fecha` (`postulante_id` ASC, `fecha` ASC) VISIBLE,
  CONSTRAINT `fk_asistencia_usuario`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`usuario` (`postulante_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`checklist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`checklist` (
  `id_checklist` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(150) NOT NULL,
  `tipo` ENUM('APERTURA', 'CIERRE') NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_checklist`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`asistencia_checklist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`asistencia_checklist` (
  `id_asistencia_checklist` INT NOT NULL AUTO_INCREMENT,
  `asistencia_id` INT NOT NULL,
  `checklist_id` INT NOT NULL,
  `cumplido` TINYINT(1) NOT NULL DEFAULT 0,
  `observacion` TEXT NULL,
  PRIMARY KEY (`id_asistencia_checklist`),
  INDEX `idx_sesion_checklist_checklist_idx` (`checklist_id` ASC) VISIBLE,
  INDEX `fk_asistencia_checklist_asistencia_idx` (`asistencia_id` ASC) VISIBLE,
  CONSTRAINT `fk_sesion_checklist_checklist`
    FOREIGN KEY (`checklist_id`)
    REFERENCES `sb`.`checklist` (`id_checklist`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_asistencia_checklist_asistencia`
    FOREIGN KEY (`asistencia_id`)
    REFERENCES `sb`.`asistencia` (`id_asistencia`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`incidencia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`incidencia` (
  `id_incidencia` INT NOT NULL AUTO_INCREMENT,
  `postulante_id` INT NOT NULL,
  `sesion_id` INT NULL,
  `tipo` ENUM('ERROR_CAJA', 'FALTA_DISCIPLINARIA', 'SISTEMA', 'OTRO') NOT NULL,
  `descripcion` TEXT NOT NULL,
  `estado` ENUM('REGISTRADO', 'EN_REVISION', 'RESUELTO') NOT NULL DEFAULT 'REGISTRADO',
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_incidencia`),
  INDEX `fk_incidencia_postulante_idx` (`postulante_id` ASC) VISIBLE,
  INDEX `fk_incidencia_sesion_caja_idx` (`sesion_id` ASC) VISIBLE,
  CONSTRAINT `fk_incidencia_usuario`
    FOREIGN KEY (`postulante_id`)
    REFERENCES `sb`.`usuario` (`postulante_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_incidencia_sesion_caja`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`concepto_gastos_local`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`concepto_gastos_local` (
  `id_concepto` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_concepto`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`pago_local`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`pago_local` (
  `id_pago_local` INT NOT NULL AUTO_INCREMENT,
  `sesion_id` INT NOT NULL,
  `local_id` INT NOT NULL,
  `postulante_emisor_id` INT NOT NULL,
  `concepto_id` INT NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `numero_operacion` VARCHAR(100) NULL,
  `comprobante_url` VARCHAR(255) NULL,
  `fecha_pago` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` TIMESTAMP NULL,
  `estado` ENUM('PENDIENTE', 'OBSERVADO', 'RECHAZADO', 'APROBADO') NOT NULL DEFAULT 'PENDIENTE',
  `observacion_revision` TEXT NULL,
  PRIMARY KEY (`id_pago_local`),
  INDEX `fk_pago_local_sesion_idx` (`sesion_id` ASC) VISIBLE,
  INDEX `fk_pago_local_local_idx` (`local_id` ASC) VISIBLE,
  INDEX `fk_pago_local_emisor_idx` (`postulante_emisor_id` ASC) VISIBLE,
  INDEX `fk_pago_local_concepto_idx` (`concepto_id` ASC) VISIBLE,
  CONSTRAINT `fk_pago_local_sesion`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pago_local_local`
    FOREIGN KEY (`local_id`)
    REFERENCES `sb`.`local` (`id_local`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pago_local_emisor`
    FOREIGN KEY (`postulante_emisor_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pago_local_concepto`
    FOREIGN KEY (`concepto_id`)
    REFERENCES `sb`.`concepto_gastos_local` (`id_concepto`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sb`.`reporte_venta`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sb`.`reporte_venta` (
  `id_reporte_venta` INT NOT NULL AUTO_INCREMENT,
  `sesion_id` INT NOT NULL,
  `postulante_vendedor_id` INT NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reporte_venta`),
  INDEX `fk_reporte_venta_sesion_idx` (`sesion_id` ASC) VISIBLE,
  INDEX `fk_reporte_venta_vendedor_idx` (`postulante_vendedor_id` ASC) VISIBLE,
  CONSTRAINT `fk_reporte_venta_sesion`
    FOREIGN KEY (`sesion_id`)
    REFERENCES `sb`.`sesion_caja` (`id_sesion`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reporte_venta_vendedor`
    FOREIGN KEY (`postulante_vendedor_id`)
    REFERENCES `sb`.`postulante` (`id_postulante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;