-- ============================================================
--  SoloBoticas 2026 — Grupo KGyR S.A.C
--  Archivo  : sb_schema.sql
--  Versión  : 2.0
--  Charset  : utf8mb4 (soporte completo Unicode / emojis)
-- ============================================================
-- CAMBIOS vs v1.0
--   postulante        → + foto_url (WebP 300×300px, ~25 KB en disco)
--   postulacion       → - postulacioncol (columna basura),
--                        + visto / fecha_vista / observacion (alertas de admin)
--   sesion_caja       → + estado PENDIENTE_VENTA en el flujo de cuadre
--   detalle_cuadre    → + monto_agente_bcp,
--                        saldo_proximo_dia dividido en efectivo + agente_bcp
--   asistencia        → + local_id (en qué local marcó)
--   NUEVO: semana          — períodos semanales para horarios
--   NUEVO: horario_solicitud — propuesta de horario del trabajador
-- ============================================================
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS,         UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE,
    SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `sb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sb`;

-- ============================================================
-- SECCIÓN 1: CATÁLOGOS (sin dependencias externas)
-- ============================================================

CREATE TABLE IF NOT EXISTS `etapa` (
  `id_etapa`          INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etapa`),
  UNIQUE INDEX `uq_etapa_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Flujo: Pendiente → Entrevista → Contratado | Rechazado
-- Post-contratación: Suspendido | Despedido


CREATE TABLE IF NOT EXISTS `genero` (
  `id_genero`         INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(20)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_genero`),
  UNIQUE INDEX `uq_genero_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `situacion_vivienda` (
  `id_situacion`      INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_situacion`),
  UNIQUE INDEX `uq_vivienda_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `nivel` (
  `id_nivel`          INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nivel`),
  UNIQUE INDEX `uq_nivel_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `skill` (
  `id_skill`          INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_skill`),
  UNIQUE INDEX `uq_skill_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `puesto` (
  `id_puesto`         INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_puesto`),
  UNIQUE INDEX `uq_puesto_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `tipo_estudio` (
  `id_tipo`           INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo`),
  UNIQUE INDEX `uq_tipo_estudio_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `turno` (
  `id_turno`          INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(20)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_turno`),
  UNIQUE INDEX `uq_turno_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `estado` (
  `id_estado`         INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estado`),
  UNIQUE INDEX `uq_estado_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol`            INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT     NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rol`),
  UNIQUE INDEX `uq_rol_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `institucion` (
  `id_institucion`    INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(150) NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_institucion`),
  UNIQUE INDEX `uq_institucion_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `especialidad` (
  `id_especialidad`   INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_especialidad`),
  UNIQUE INDEX `uq_especialidad_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `modo` (
  `id_modo`           INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_modo`),
  UNIQUE INDEX `uq_modo_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `tipo_movimiento` (
  `id_tipo_movimiento` INT         NOT NULL AUTO_INCREMENT,
  `descripcion`        VARCHAR(50) NOT NULL,
  `activo`             TINYINT(1)  NOT NULL DEFAULT 1,
  `fecha_registro`     TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_movimiento`),
  UNIQUE INDEX `uq_tipo_mov_desc` (`descripcion` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `concepto_gastos_local` (
  `id_concepto`       INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_concepto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `checklist` (
  `id_checklist`      INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(150) NOT NULL,
  `tipo`              ENUM('APERTURA','CIERRE') NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_checklist`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 2: ENTIDAD PRINCIPAL — POSTULANTE
-- ============================================================
-- foto_url: ruta relativa al archivo WebP almacenado en el servidor.
-- Recomendación: redimensionar a 300×300px, calidad 80%, formato WebP.
-- Tamaño esperado: 15-30 KB por foto. Ruta: /uploads/fotos/{id}.webp
-- ============================================================

CREATE TABLE IF NOT EXISTS `postulante` (
  `id_postulante`       INT          NOT NULL AUTO_INCREMENT,
  `nombres`             VARCHAR(100) NULL,
  `apellidos`           VARCHAR(100) NULL,
  `genero_id`           INT          NULL DEFAULT NULL,
  `fecha_nacimiento`    DATE         NULL DEFAULT NULL,
  `email`               VARCHAR(100) NULL DEFAULT NULL,
  `telefono`            VARCHAR(15)  NULL DEFAULT NULL,
  `situacion_vivienda_id` INT        NULL DEFAULT NULL,
  `num_documento`       VARCHAR(8)   NOT NULL,
  `direccion`           VARCHAR(255) NULL DEFAULT NULL,
  `distrito`            VARCHAR(100) NULL,
  `calificacion`        DECIMAL(5,2) NULL,
  `foto_url`            VARCHAR(255) NULL DEFAULT NULL,   -- ← NUEVO v2.0
  `fecha_registro`      TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion`  TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cv_url`              VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id_postulante`),
  UNIQUE INDEX `uq_postulante_dni`    (`num_documento` ASC),
  INDEX `idx_postulante_genero`       (`genero_id` ASC),
  INDEX `idx_postulante_vivienda`     (`situacion_vivienda_id` ASC),
  CONSTRAINT `fk_postulante_genero`
    FOREIGN KEY (`genero_id`) REFERENCES `genero` (`id_genero`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_postulante_vivienda`
    FOREIGN KEY (`situacion_vivienda_id`) REFERENCES `situacion_vivienda` (`id_situacion`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 3: RELACIONES DEL POSTULANTE
-- ============================================================

CREATE TABLE IF NOT EXISTS `experiencia` (
  `id_experiencia`    INT          NOT NULL AUTO_INCREMENT,
  `postulante_id`     INT          NULL DEFAULT NULL,
  `empresa`           VARCHAR(150) NULL DEFAULT NULL,
  `cargo`             VARCHAR(100) NULL DEFAULT NULL,
  `funciones`         VARCHAR(150) NULL,
  `fecha_inicio`      DATE         NULL DEFAULT NULL,
  `fecha_fin`         DATE         NULL DEFAULT NULL,
  PRIMARY KEY (`id_experiencia`),
  INDEX `idx_exp_postulante` (`postulante_id` ASC),
  CONSTRAINT `fk_experiencia_postulante`
    FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `postulante_skill` (
  `postulante_id`     INT          NOT NULL,
  `skill_id`          INT          NOT NULL,
  `nivel_id`          INT          NULL DEFAULT NULL,
  PRIMARY KEY (`postulante_id`, `skill_id`),
  INDEX `idx_ps_skill`  (`skill_id` ASC),
  INDEX `idx_ps_nivel`  (`nivel_id` ASC),
  CONSTRAINT `fk_ps_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_skill`      FOREIGN KEY (`skill_id`)      REFERENCES `skill` (`id_skill`),
  CONSTRAINT `fk_ps_nivel`      FOREIGN KEY (`nivel_id`)      REFERENCES `nivel` (`id_nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `postulante_especialidad` (
  `postulante_id`     INT          NOT NULL,
  `especialidad_id`   INT          NOT NULL,
  PRIMARY KEY (`postulante_id`, `especialidad_id`),
  INDEX `idx_pe_especialidad` (`especialidad_id` ASC),
  CONSTRAINT `fk_pe_postulante`   FOREIGN KEY (`postulante_id`)   REFERENCES `postulante` (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_pe_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidad` (`id_especialidad`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `preferencias` (
  `turno_id`          INT          NOT NULL,
  `postulante_id`     INT          NOT NULL,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`turno_id`, `postulante_id`),
  INDEX `idx_pref_postulante` (`postulante_id` ASC),
  CONSTRAINT `fk_pref_turno`      FOREIGN KEY (`turno_id`)      REFERENCES `turno` (`id_turno`)              ON DELETE NO ACTION,
  CONSTRAINT `fk_pref_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `estudio` (
  `id_estudio`        INT          NOT NULL AUTO_INCREMENT,
  `postulante_id`     INT          NOT NULL,
  `tipo_id`           INT          NOT NULL,
  `institucion_id`    INT          NOT NULL,
  `estado_id`         INT          NOT NULL,
  `fecha_inicio`      DATE         NULL,
  `fecha_fin`         DATE         NULL,
  PRIMARY KEY (`id_estudio`),
  UNIQUE INDEX `uq_estudio` (`postulante_id` ASC, `tipo_id` ASC, `institucion_id` ASC, `fecha_inicio` ASC),
  INDEX `idx_estudio_tipo`       (`tipo_id` ASC),
  INDEX `idx_estudio_estado`     (`estado_id` ASC),
  INDEX `idx_estudio_institucion` (`institucion_id` ASC),
  CONSTRAINT `fk_estudio_postulante`  FOREIGN KEY (`postulante_id`)  REFERENCES `postulante`   (`id_postulante`)  ON DELETE CASCADE,
  CONSTRAINT `fk_estudio_tipo`        FOREIGN KEY (`tipo_id`)        REFERENCES `tipo_estudio` (`id_tipo`)        ON DELETE NO ACTION,
  CONSTRAINT `fk_estudio_estado`      FOREIGN KEY (`estado_id`)      REFERENCES `estado`       (`id_estado`)      ON DELETE NO ACTION,
  CONSTRAINT `fk_estudio_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `institucion`  (`id_institucion`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `contacto_emergencia` (
  `id_contacto_emergencia` INT       NOT NULL AUTO_INCREMENT,
  `postulante_id`     INT          NOT NULL,
  `nombre_completo`   VARCHAR(150) NOT NULL,
  `parentesco`        VARCHAR(50)  NOT NULL,
  `telefono`          VARCHAR(15)  NOT NULL,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contacto_emergencia`),
  INDEX `idx_ce_postulante` (`postulante_id` ASC),
  CONSTRAINT `fk_ce_postulante`
    FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 4: POSTULACIÓN
-- ============================================================
-- v2.0: eliminada postulacioncol (artefacto de Workbench).
-- visto / fecha_vista → el admin sabe cuáles son nuevas (alertas).
-- observacion        → notas del admin al aprobar o rechazar.
-- INDEX en visto     → consulta rápida: SELECT COUNT(*) WHERE visto=0
-- ============================================================

CREATE TABLE IF NOT EXISTS `postulacion` (
  `id_postulacion`    INT          NOT NULL AUTO_INCREMENT,
  `postulante_id`     INT          NOT NULL,
  `puesto_id`         INT          NOT NULL,
  `etapa_id`          INT          NULL DEFAULT 1,
  `visto`             TINYINT(1)   NOT NULL DEFAULT 0,          -- ← NUEVO v2.0
  `fecha_vista`       TIMESTAMP    NULL DEFAULT NULL,            -- ← NUEVO v2.0
  `observacion`       TEXT         NULL DEFAULT NULL,            -- ← NUEVO v2.0
  `fecha_postulacion` TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_postulacion`),
  UNIQUE INDEX `uq_postulante_puesto` (`postulante_id` ASC, `puesto_id` ASC),
  INDEX `idx_postulacion_etapa`       (`etapa_id` ASC),
  INDEX `idx_postulacion_visto`       (`visto` ASC),             -- ← para alertas rápidas
  INDEX `idx_postulacion_fecha`       (`fecha_postulacion` ASC),
  CONSTRAINT `fk_postulacion_postulante`
    FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_postulacion_puesto`
    FOREIGN KEY (`puesto_id`) REFERENCES `puesto` (`id_puesto`) ON DELETE NO ACTION,
  CONSTRAINT `fk_postulacion_etapa`
    FOREIGN KEY (`etapa_id`) REFERENCES `etapa` (`id_etapa`)    ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 5: USUARIOS Y AUTENTICACIÓN
-- ============================================================

CREATE TABLE IF NOT EXISTS `usuario` (
  `postulante_id`     INT          NOT NULL,
  `rol_id`            INT          NOT NULL,
  `username`          VARCHAR(50)  NOT NULL,
  `password`          VARCHAR(255) NOT NULL,   -- bcrypt hash
  `activo`            TINYINT(1)   NULL DEFAULT 1,
  `fecha_registro`    TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`postulante_id`),
  UNIQUE INDEX `uq_usuario_username` (`username` ASC),
  INDEX `idx_usuario_rol` (`rol_id` ASC),
  CONSTRAINT `fk_usuario_postulante`
    FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuario_rol`
    FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id_rol`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 6: INFRAESTRUCTURA — LOCALES Y CAJAS
-- ============================================================

CREATE TABLE IF NOT EXISTS `local` (
  `id_local`          INT          NOT NULL AUTO_INCREMENT,
  `descripcion`       VARCHAR(50)  NULL,
  `direccion`         VARCHAR(150) NULL,
  `id_encargado`      INT          NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_local`),
  INDEX `idx_local_encargado` (`id_encargado` ASC),
  CONSTRAINT `fk_local_encargado`
    FOREIGN KEY (`id_encargado`) REFERENCES `postulante` (`id_postulante`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `caja` (
  `id_caja`           INT          NOT NULL AUTO_INCREMENT,
  `local_id`          INT          NOT NULL,
  `descripcion`       VARCHAR(50)  NOT NULL,
  `activo`            TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_caja`),
  UNIQUE INDEX `uq_caja_local_desc` (`local_id` ASC, `descripcion` ASC),
  CONSTRAINT `fk_caja_local`
    FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 7: MÓDULO DE CAJA — SESIONES
-- ============================================================
-- FLUJO DE ESTADOS:
--   ABIERTA          → Cajero registrando movimientos durante el turno
--   PENDIENTE_VENTA  → Cajero cerró su conteo; esperando que el vendedor
--                      ingrese las ventas del ERP (cajero NO puede editar)
--   CERRADA          → Todos los datos ingresados; listo para revisión
--   EN_REVISION      → Supervisor revisando el cuadre
--   APROBADA         → Cuadre aprobado (registro inmutable)
--   OBSERVADA        → Discrepancia encontrada; cajero puede rectificar
--   RECHAZADA        → Rechazado por el supervisor
-- ============================================================

CREATE TABLE IF NOT EXISTS `sesion_caja` (
  `id_sesion`                 INT           NOT NULL AUTO_INCREMENT,
  `caja_id`                   INT           NOT NULL,
  `turno_id`                  INT           NOT NULL,
  `postulante_apertura_id`    INT           NOT NULL,
  `postulante_cierre_id`      INT           NULL,
  `postulante_revisor_id`     INT           NULL,
  `estado`                    ENUM(
                                'ABIERTA',
                                'PENDIENTE_VENTA',   -- ← NUEVO v2.0
                                'CERRADA',
                                'EN_REVISION',
                                'APROBADA',
                                'OBSERVADA',
                                'RECHAZADA'
                              ) NOT NULL DEFAULT 'ABIERTA',
  `saldo_inicial`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  -- saldo_inicial = efectivo_caja + agente_bcp del cierre del día anterior
  `saldo_final_sistema`       DECIMAL(10,2) NULL,
  `saldo_final_contado`       DECIMAL(10,2) NULL,
  `diferencia_final`          DECIMAL(10,2) NULL,
  `margen_permitido`          DECIMAL(10,2) NOT NULL DEFAULT 10.00,
  `fecha_apertura`            TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre`              TIMESTAMP     NULL,
  `fecha_operacion`           DATE          NOT NULL,
  `fecha_envio_revision`      TIMESTAMP     NULL,
  `fecha_revision`            TIMESTAMP     NULL,
  `observacion_cierre`        TEXT          NULL,
  `observacion_revisor`       TEXT          NULL,
  `motivo_rechazo`            TEXT          NULL,
  `bloqueado`                 TINYINT(1)    NULL DEFAULT 0,
  -- bloqueado = 1 cuando el cajero cierra (estado PENDIENTE_VENTA en adelante)
  `requiere_revision`         TINYINT(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_sesion`),
  UNIQUE INDEX `uq_sesion_caja_turno_fecha` (`caja_id` ASC, `turno_id` ASC, `fecha_operacion` ASC),
  INDEX `idx_sesion_caja`                   (`caja_id` ASC),
  INDEX `idx_sesion_apertura`               (`postulante_apertura_id` ASC),
  INDEX `idx_sesion_cierre`                 (`postulante_cierre_id` ASC),
  INDEX `idx_sesion_revisor`                (`postulante_revisor_id` ASC),
  INDEX `idx_sesion_turno`                  (`turno_id` ASC),
  INDEX `idx_sesion_fecha_operacion`        (`fecha_operacion` ASC),
  INDEX `idx_sesion_estado`                 (`estado` ASC),
  CONSTRAINT `fk_sesion_caja`     FOREIGN KEY (`caja_id`)                REFERENCES `caja`       (`id_caja`)           ON DELETE NO ACTION,
  CONSTRAINT `fk_sesion_turno`    FOREIGN KEY (`turno_id`)               REFERENCES `turno`      (`id_turno`)          ON DELETE NO ACTION,
  CONSTRAINT `fk_sesion_apertura` FOREIGN KEY (`postulante_apertura_id`) REFERENCES `postulante` (`id_postulante`)     ON DELETE NO ACTION,
  CONSTRAINT `fk_sesion_cierre`   FOREIGN KEY (`postulante_cierre_id`)   REFERENCES `postulante` (`id_postulante`)     ON DELETE NO ACTION,
  CONSTRAINT `fk_sesion_revisor`  FOREIGN KEY (`postulante_revisor_id`)  REFERENCES `postulante` (`id_postulante`)     ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `sesion_participante` (
  `id_sesion_participante` INT   NOT NULL AUTO_INCREMENT,
  `sesion_id`              INT   NOT NULL,
  `postulante_id`          INT   NOT NULL,
  `rol_participacion`      ENUM('CAJERA','VENDEDORA','SUPERVISORA') NOT NULL,
  `responsable_faltante`   TINYINT(1) NOT NULL DEFAULT 0,
  `observacion`            TEXT       NULL,
  PRIMARY KEY (`id_sesion_participante`),
  UNIQUE INDEX `uq_sesion_participante` (`sesion_id` ASC, `postulante_id` ASC),
  INDEX `idx_sp_postulante` (`postulante_id` ASC),
  CONSTRAINT `fk_sp_sesion`     FOREIGN KEY (`sesion_id`)     REFERENCES `sesion_caja` (`id_sesion`)     ON DELETE NO ACTION,
  CONSTRAINT `fk_sp_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `movimiento_sesion` (
  `id_movimiento`             INT           NOT NULL AUTO_INCREMENT,
  `sesion_id`                 INT           NOT NULL,
  `tipo_movimiento_id`        INT           NOT NULL,
  `modo_id`                   INT           NULL,
  `postulante_registro_id`    INT           NOT NULL,
  `postulante_revision_id`    INT           NULL,
  `descripcion`               VARCHAR(250)  NULL,
  `monto`                     DECIMAL(10,2) NOT NULL,
  `numero_operacion`          VARCHAR(100)  NULL,
  `comprobante_url`           VARCHAR(255)  NULL,
  `fecha_movimiento`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision`            TIMESTAMP     NULL,
  `fecha_anulacion`           TIMESTAMP     NULL,
  `estado`                    ENUM('PENDIENTE','APROBADO','OBSERVADO','RECHAZADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  `motivo_anulacion`          TEXT          NULL,
  `observacion_revision`      TEXT          NULL,
  PRIMARY KEY (`id_movimiento`),
  INDEX `idx_mov_sesion`       (`sesion_id` ASC),
  INDEX `idx_mov_tipo`         (`tipo_movimiento_id` ASC),
  INDEX `idx_mov_registro`     (`postulante_registro_id` ASC),
  INDEX `idx_mov_revision`     (`postulante_revision_id` ASC),
  INDEX `idx_mov_modo`         (`modo_id` ASC),
  INDEX `idx_mov_fecha`        (`fecha_movimiento` ASC),
  INDEX `idx_mov_estado`       (`estado` ASC),
  CONSTRAINT `fk_mov_sesion`    FOREIGN KEY (`sesion_id`)              REFERENCES `sesion_caja`      (`id_sesion`)          ON DELETE NO ACTION,
  CONSTRAINT `fk_mov_tipo`      FOREIGN KEY (`tipo_movimiento_id`)     REFERENCES `tipo_movimiento`  (`id_tipo_movimiento`) ON DELETE NO ACTION,
  CONSTRAINT `fk_mov_registro`  FOREIGN KEY (`postulante_registro_id`) REFERENCES `postulante`       (`id_postulante`)      ON DELETE NO ACTION,
  CONSTRAINT `fk_mov_revision`  FOREIGN KEY (`postulante_revision_id`) REFERENCES `postulante`       (`id_postulante`)      ON DELETE NO ACTION,
  CONSTRAINT `fk_mov_modo`      FOREIGN KEY (`modo_id`)                REFERENCES `modo`             (`id_modo`)            ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- DETALLE_CUADRE: registro del conteo físico al cierre del turno
-- ============================================================
-- FÓRMULA BASE:
--   total_efectivo_contado = monedas + billetes_caja + billetes_caja_fuerte
--   total_contado_general  = total_efectivo_contado + yape_plin + visas + bcp_transferencias
--   total_esperado_sistema = saldo_inicial + total_ventas_sistema - total_gastos_sistema
--   diferencia             = total_contado_general - total_esperado_sistema
--   saldo_proxima_efectivo = total_efectivo_contado   (cash que queda en caja)
--   saldo_proxima_agente   = monto_agente_bcp          (saldo en agente BCP)
--   saldo_proximo_dia      = saldo_proxima_efectivo + saldo_proxima_agente
-- ============================================================

CREATE TABLE IF NOT EXISTS `detalle_cuadre` (
  `sesion_id`                  INT           NOT NULL,
  -- Conteo físico del cajero
  `monto_monedas`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_billetes_caja`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_billetes_caja_fuerte` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  -- Pagos digitales del día
  `monto_yape_plin`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_visas`                DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_bcp`                  DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- transferencias BCP recibidas
  `monto_agente_bcp`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- ← NUEVO v2.0: saldo agente BCP
  -- Totales calculados
  `total_efectivo_contado`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_contado_general`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_ventas_sistema`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_gastos_sistema`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_esperado_sistema`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `diferencia`                 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `resultado_cuadre`           ENUM('CONSISTENTE','SOBRANTE','FALTANTE') NULL,
  `observacion_cierre`         TEXT          NULL,
  -- Saldo para el día siguiente (se copia a sesion_caja.saldo_inicial de la próxima sesión)
  `saldo_proxima_efectivo`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- ← NUEVO v2.0
  `saldo_proxima_agente_bcp`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- ← NUEVO v2.0
  `saldo_proximo_dia`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- = efectivo + agente_bcp
  PRIMARY KEY (`sesion_id`),
  CONSTRAINT `fk_dc_sesion`
    FOREIGN KEY (`sesion_id`) REFERENCES `sesion_caja` (`id_sesion`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `rectificacion_cuadre` (
  `id_rectificacion`           INT           NOT NULL AUTO_INCREMENT,
  `sesion_id`                  INT           NOT NULL,
  `postulante_registra_id`     INT           NOT NULL,
  `postulante_responsable_id`  INT           NULL,
  `tipo_rectificacion`         ENUM('DEVOLUCION_DINERO','DINERO_ENCONTRADO','AJUSTE_CONTEO','COMPENSACION','OTRO') NOT NULL,
  `modo_id`                    INT           NULL,
  `monto`                      DECIMAL(10,2) NOT NULL,
  `descripcion_contexto`       TEXT          NOT NULL,
  `justificacion`              TEXT          NULL,
  `comprobante_url`            VARCHAR(255)  NULL,
  `fecha_rectificacion`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado`                     ENUM('PENDIENTE','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  `postulante_revisa_id`       INT           NULL,
  `fecha_revision`             TIMESTAMP     NULL,
  `observacion_revision`       TEXT          NULL,
  PRIMARY KEY (`id_rectificacion`),
  INDEX `idx_rect_sesion`       (`sesion_id` ASC),
  INDEX `idx_rect_registra`     (`postulante_registra_id` ASC),
  INDEX `idx_rect_responsable`  (`postulante_responsable_id` ASC),
  INDEX `idx_rect_revisa`       (`postulante_revisa_id` ASC),
  INDEX `idx_rect_modo`         (`modo_id` ASC),
  INDEX `idx_rect_estado`       (`estado` ASC),
  CONSTRAINT `fk_rect_sesion`       FOREIGN KEY (`sesion_id`)                 REFERENCES `sesion_caja` (`id_sesion`)     ON DELETE NO ACTION,
  CONSTRAINT `fk_rect_registra`     FOREIGN KEY (`postulante_registra_id`)    REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_rect_responsable`  FOREIGN KEY (`postulante_responsable_id`) REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_rect_revisa`       FOREIGN KEY (`postulante_revisa_id`)      REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_rect_modo`         FOREIGN KEY (`modo_id`)                   REFERENCES `modo`        (`id_modo`)       ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `auditoria_cuadre` (
  `id_auditoria`      INT          NOT NULL AUTO_INCREMENT,
  `sesion_id`         INT          NOT NULL,
  `postulante_id`     INT          NOT NULL,
  `accion`            VARCHAR(30)  NOT NULL,
  `campo_modificado`  VARCHAR(100) NULL,
  `valor_anterior`    TEXT         NULL,
  `valor_nuevo`       TEXT         NULL,
  `fecha`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  INDEX `idx_aud_cuadre_sesion`     (`sesion_id` ASC),
  INDEX `idx_aud_cuadre_postulante` (`postulante_id` ASC),
  CONSTRAINT `fk_aud_cuadre_sesion`     FOREIGN KEY (`sesion_id`)     REFERENCES `sesion_caja` (`id_sesion`)     ON DELETE NO ACTION,
  CONSTRAINT `fk_aud_cuadre_postulante` FOREIGN KEY (`postulante_id`) REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `transferencia_caja` (
  `id_transferencia`            INT           NOT NULL AUTO_INCREMENT,
  `sesion_origen_id`            INT           NOT NULL,
  `sesion_destino_id`           INT           NULL,
  `caja_origen_id`              INT           NOT NULL,
  `caja_destino_id`             INT           NOT NULL,
  `postulante_envia_id`         INT           NOT NULL,
  `postulante_recibe_id`        INT           NULL,
  `postulante_revisa_id`        INT           NULL,
  `monto`                       DECIMAL(10,2) NOT NULL,
  `numero_operacion`            VARCHAR(100)  NULL,
  `comprobante_url`             VARCHAR(255)  NULL,
  `fecha_envio`                 TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_confirmacion_recepcion` TIMESTAMP    NULL,
  `fecha_revision`              TIMESTAMP     NULL,
  `estado`                      ENUM('PENDIENTE_ENVIO','ENVIADO','RECIBIDO','OBSERVADO','RECHAZADO','APROBADO') NULL DEFAULT 'PENDIENTE_ENVIO',
  `observacion_recepcion`       TEXT          NULL,
  `observacion_revision`        TEXT          NULL,
  PRIMARY KEY (`id_transferencia`),
  INDEX `idx_tf_sesion_origen`   (`sesion_origen_id` ASC),
  INDEX `idx_tf_sesion_destino`  (`sesion_destino_id` ASC),
  INDEX `idx_tf_caja_origen`     (`caja_origen_id` ASC),
  INDEX `idx_tf_caja_destino`    (`caja_destino_id` ASC),
  INDEX `idx_tf_envia`           (`postulante_envia_id` ASC),
  INDEX `idx_tf_recibe`          (`postulante_recibe_id` ASC),
  INDEX `idx_tf_revisa`          (`postulante_revisa_id` ASC),
  INDEX `idx_tf_estado`          (`estado` ASC),
  CONSTRAINT `fk_tf_sesion_origen`  FOREIGN KEY (`sesion_origen_id`)   REFERENCES `sesion_caja` (`id_sesion`) ON DELETE NO ACTION,
  CONSTRAINT `fk_tf_sesion_destino` FOREIGN KEY (`sesion_destino_id`)  REFERENCES `sesion_caja` (`id_sesion`) ON DELETE NO ACTION,
  CONSTRAINT `fk_tf_caja_origen`    FOREIGN KEY (`caja_origen_id`)     REFERENCES `caja`        (`id_caja`)   ON DELETE NO ACTION,
  CONSTRAINT `fk_tf_caja_destino`   FOREIGN KEY (`caja_destino_id`)    REFERENCES `caja`        (`id_caja`)   ON DELETE NO ACTION,
  CONSTRAINT `fk_tf_envia`          FOREIGN KEY (`postulante_envia_id`)  REFERENCES `postulante` (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_tf_recibe`         FOREIGN KEY (`postulante_recibe_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_tf_revisa`         FOREIGN KEY (`postulante_revisa_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `pago_personal` (
  `id_pago_personal`             INT           NOT NULL AUTO_INCREMENT,
  `sesion_id`                    INT           NOT NULL,
  `postulante_emisor_id`         INT           NOT NULL,
  `postulante_beneficiario_id`   INT           NOT NULL,
  `postulante_revisor_id`        INT           NULL,
  `monto`                        DECIMAL(10,2) NOT NULL,
  `numero_operacion`             VARCHAR(100)  NULL,
  `comprobante_url`              VARCHAR(255)  NULL,
  `fecha_pago`                   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_confirmacion_beneficiario` TIMESTAMP  NULL,
  `fecha_revision`               TIMESTAMP     NULL,
  `estado`                       ENUM('PENDIENTE','PAGADO','CONFIRMADO_BENEFICIARIO','OBSERVADO','RECHAZADO','APROBADO') NOT NULL DEFAULT 'PENDIENTE',
  `observacion_beneficiario`     TEXT          NULL,
  `observacion_revision`         TEXT          NULL,
  PRIMARY KEY (`id_pago_personal`),
  INDEX `idx_pp_sesion`       (`sesion_id` ASC),
  INDEX `idx_pp_emisor`       (`postulante_emisor_id` ASC),
  INDEX `idx_pp_beneficiario` (`postulante_beneficiario_id` ASC),
  INDEX `idx_pp_revisor`      (`postulante_revisor_id` ASC),
  INDEX `idx_pp_estado`       (`estado` ASC),
  CONSTRAINT `fk_pp_sesion`       FOREIGN KEY (`sesion_id`)                  REFERENCES `sesion_caja` (`id_sesion`)     ON DELETE NO ACTION,
  CONSTRAINT `fk_pp_emisor`       FOREIGN KEY (`postulante_emisor_id`)       REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_pp_beneficiario` FOREIGN KEY (`postulante_beneficiario_id`) REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION,
  CONSTRAINT `fk_pp_revisor`      FOREIGN KEY (`postulante_revisor_id`)      REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `pago_local` (
  `id_pago_local`         INT           NOT NULL AUTO_INCREMENT,
  `sesion_id`             INT           NOT NULL,
  `local_id`              INT           NOT NULL,
  `postulante_emisor_id`  INT           NOT NULL,
  `concepto_id`           INT           NOT NULL,
  `monto`                 DECIMAL(10,2) NOT NULL,
  `numero_operacion`      VARCHAR(100)  NULL,
  `comprobante_url`       VARCHAR(255)  NULL,
  `fecha_pago`            TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision`        TIMESTAMP     NULL,
  `estado`                ENUM('PENDIENTE','OBSERVADO','RECHAZADO','APROBADO') NOT NULL DEFAULT 'PENDIENTE',
  `observacion_revision`  TEXT          NULL,
  PRIMARY KEY (`id_pago_local`),
  INDEX `idx_pl_sesion`  (`sesion_id` ASC),
  INDEX `idx_pl_local`   (`local_id` ASC),
  INDEX `idx_pl_emisor`  (`postulante_emisor_id` ASC),
  INDEX `idx_pl_concepto` (`concepto_id` ASC),
  CONSTRAINT `fk_pl_sesion`  FOREIGN KEY (`sesion_id`)            REFERENCES `sesion_caja`          (`id_sesion`)      ON DELETE NO ACTION,
  CONSTRAINT `fk_pl_local`   FOREIGN KEY (`local_id`)             REFERENCES `local`                (`id_local`)       ON DELETE NO ACTION,
  CONSTRAINT `fk_pl_emisor`  FOREIGN KEY (`postulante_emisor_id`) REFERENCES `postulante`           (`id_postulante`)  ON DELETE NO ACTION,
  CONSTRAINT `fk_pl_concepto` FOREIGN KEY (`concepto_id`)         REFERENCES `concepto_gastos_local` (`id_concepto`)   ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `reporte_venta` (
  `id_reporte_venta`      INT           NOT NULL AUTO_INCREMENT,
  `sesion_id`             INT           NOT NULL,
  `postulante_vendedor_id` INT          NOT NULL,
  `monto`                 DECIMAL(10,2) NOT NULL,
  `fecha_registro`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reporte_venta`),
  INDEX `idx_rv_sesion`   (`sesion_id` ASC),
  INDEX `idx_rv_vendedor` (`postulante_vendedor_id` ASC),
  CONSTRAINT `fk_rv_sesion`   FOREIGN KEY (`sesion_id`)              REFERENCES `sesion_caja` (`id_sesion`)     ON DELETE NO ACTION,
  CONSTRAINT `fk_rv_vendedor` FOREIGN KEY (`postulante_vendedor_id`) REFERENCES `postulante`  (`id_postulante`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 8: ASISTENCIA
-- ============================================================
-- v2.0: agrega local_id para saber en qué local marcó el trabajador.
-- ============================================================

CREATE TABLE IF NOT EXISTS `asistencia` (
  `id_asistencia`     INT          NOT NULL AUTO_INCREMENT,
  `postulante_id`     INT          NOT NULL,
  `local_id`          INT          NULL DEFAULT NULL,   -- ← NUEVO v2.0
  `fecha`             DATE         NOT NULL,
  `hora_ingreso`      DATETIME     NULL,
  `hora_salida`       DATETIME     NULL,
  `estado`            ENUM('A TIEMPO','TARDE','FALTA') NOT NULL DEFAULT 'A TIEMPO',
  `justificacion`     TEXT         NULL,
  `observacion`       ENUM('PROCEDE','NO PROCEDE','PENDIENTE') NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_asistencia`),
  UNIQUE INDEX `uq_asistencia_postulante_fecha` (`postulante_id` ASC, `fecha` ASC),
  INDEX `idx_asistencia_fecha`   (`fecha` ASC),
  INDEX `idx_asistencia_local`   (`local_id` ASC),
  CONSTRAINT `fk_asistencia_usuario`
    FOREIGN KEY (`postulante_id`) REFERENCES `usuario` (`postulante_id`) ON DELETE NO ACTION,
  CONSTRAINT `fk_asistencia_local`
    FOREIGN KEY (`local_id`) REFERENCES `local` (`id_local`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `asistencia_checklist` (
  `id_asistencia_checklist` INT      NOT NULL AUTO_INCREMENT,
  `asistencia_id`           INT      NOT NULL,
  `checklist_id`            INT      NOT NULL,
  `cumplido`                TINYINT(1) NOT NULL DEFAULT 0,
  `observacion`             TEXT     NULL,
  PRIMARY KEY (`id_asistencia_checklist`),
  INDEX `idx_ac_checklist`  (`checklist_id` ASC),
  INDEX `idx_ac_asistencia` (`asistencia_id` ASC),
  CONSTRAINT `fk_ac_checklist`  FOREIGN KEY (`checklist_id`)  REFERENCES `checklist`  (`id_checklist`)  ON DELETE NO ACTION,
  CONSTRAINT `fk_ac_asistencia` FOREIGN KEY (`asistencia_id`) REFERENCES `asistencia` (`id_asistencia`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 9: HORARIOS SEMANALES (NUEVO v2.0)
-- ============================================================
-- FLUJO:
--   Admin crea la semana (fecha_inicio lunes, fecha_fin domingo).
--   Cada trabajador envía su solicitud_horario indicando local, turno y días.
--   Admin aprueba o rechaza. Una vez APROBADO queda como la asignación oficial.
-- ============================================================

CREATE TABLE IF NOT EXISTS `semana` (
  `id_semana`         INT          NOT NULL AUTO_INCREMENT,
  `fecha_inicio`      DATE         NOT NULL,   -- lunes de la semana
  `fecha_fin`         DATE         NOT NULL,   -- domingo de la semana
  `estado`            ENUM('ABIERTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
  -- ABIERTA: trabajadores pueden enviar/editar su solicitud
  -- CERRADA: semana publicada, ya no se aceptan cambios
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_semana`),
  UNIQUE INDEX `uq_semana_inicio` (`fecha_inicio` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `horario_solicitud` (
  `id_solicitud`      INT          NOT NULL AUTO_INCREMENT,
  `semana_id`         INT          NOT NULL,
  `postulante_id`     INT          NOT NULL,
  `local_id`          INT          NOT NULL,
  `turno_id`          INT          NOT NULL,
  -- Días de la semana que el trabajador propone trabajar (1 = sí)
  `lunes`             TINYINT(1)   NOT NULL DEFAULT 0,
  `martes`            TINYINT(1)   NOT NULL DEFAULT 0,
  `miercoles`         TINYINT(1)   NOT NULL DEFAULT 0,
  `jueves`            TINYINT(1)   NOT NULL DEFAULT 0,
  `viernes`           TINYINT(1)   NOT NULL DEFAULT 0,
  `sabado`            TINYINT(1)   NOT NULL DEFAULT 0,
  `domingo`           TINYINT(1)   NOT NULL DEFAULT 0,
  -- Estado del workflow
  `estado`            ENUM('BORRADOR','ENVIADO','APROBADO','RECHAZADO') NOT NULL DEFAULT 'BORRADOR',
  `observacion`       TEXT         NULL,          -- nota del trabajador al proponer
  `observacion_admin` TEXT         NULL,          -- nota del admin al aprobar/rechazar
  `revisado_por_id`   INT          NULL,          -- postulante_id del admin que revisó
  `fecha_envio`       TIMESTAMP    NULL,
  `fecha_revision`    TIMESTAMP    NULL,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_solicitud`),
  UNIQUE INDEX `uq_horario_semana_trabajador` (`semana_id` ASC, `postulante_id` ASC),
  INDEX `idx_hs_postulante`    (`postulante_id` ASC),
  INDEX `idx_hs_local`         (`local_id` ASC),
  INDEX `idx_hs_turno`         (`turno_id` ASC),
  INDEX `idx_hs_estado`        (`estado` ASC),
  CONSTRAINT `fk_hs_semana`       FOREIGN KEY (`semana_id`)      REFERENCES `semana`     (`id_semana`)      ON DELETE CASCADE,
  CONSTRAINT `fk_hs_postulante`   FOREIGN KEY (`postulante_id`)  REFERENCES `postulante` (`id_postulante`)  ON DELETE CASCADE,
  CONSTRAINT `fk_hs_local`        FOREIGN KEY (`local_id`)       REFERENCES `local`      (`id_local`)       ON DELETE NO ACTION,
  CONSTRAINT `fk_hs_turno`        FOREIGN KEY (`turno_id`)       REFERENCES `turno`      (`id_turno`)       ON DELETE NO ACTION,
  CONSTRAINT `fk_hs_revisado_por` FOREIGN KEY (`revisado_por_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SECCIÓN 10: INCIDENCIAS Y AUDITORÍA
-- ============================================================

CREATE TABLE IF NOT EXISTS `incidencia` (
  `id_incidencia`     INT          NOT NULL AUTO_INCREMENT,
  `postulante_id`     INT          NOT NULL,
  `sesion_id`         INT          NULL,
  `tipo`              ENUM('ERROR_CAJA','FALTA_DISCIPLINARIA','SISTEMA','OTRO') NOT NULL,
  `descripcion`       TEXT         NOT NULL,
  `estado`            ENUM('REGISTRADO','EN_REVISION','RESUELTO') NOT NULL DEFAULT 'REGISTRADO',
  `fecha`             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_incidencia`),
  INDEX `idx_inc_postulante` (`postulante_id` ASC),
  INDEX `idx_inc_sesion`     (`sesion_id` ASC),
  CONSTRAINT `fk_inc_usuario` FOREIGN KEY (`postulante_id`) REFERENCES `usuario`     (`postulante_id`) ON DELETE NO ACTION,
  CONSTRAINT `fk_inc_sesion`  FOREIGN KEY (`sesion_id`)     REFERENCES `sesion_caja` (`id_sesion`)     ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `auditoria_sistema` (
  `id_auditoria`      INT          NOT NULL AUTO_INCREMENT,
  `postulante_id`     INT          NOT NULL,
  `tabla_afectada`    VARCHAR(100) NOT NULL,
  `id_registro`       INT          NULL,
  `accion`            VARCHAR(30)  NOT NULL,
  `descripcion`       TEXT         NULL,
  `fecha_registro`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  INDEX `idx_as_postulante` (`postulante_id` ASC),
  CONSTRAINT `fk_as_postulante`
    FOREIGN KEY (`postulante_id`) REFERENCES `postulante` (`id_postulante`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- FIN DEL ESQUEMA
-- ============================================================

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
