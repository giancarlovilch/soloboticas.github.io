-- Tabla retiro_kgyr: retiros de caja para depósito a Grupo KGyR
-- Permite registrar cuando el admin saca efectivo de una caja (cerrada o abierta)
-- para depositarlo a BCP/BBVA. El monto se descuenta del total_esperado_sistema
-- en el próximo cuadre de esa caja (sesion_aplicada_id), o de inmediato
-- ajustando el saldo base del último cuadre (sesion_base_ajustada_id / APLICADO_DIRECTO).

CREATE TABLE `retiro_kgyr` (
  `id`                        INT(11)        NOT NULL AUTO_INCREMENT,
  `caja_origen_id`            INT(11)        NOT NULL,
  `monto`                     DECIMAL(10,2)  NOT NULL,
  `banco`                     ENUM('BCP','BBVA') NOT NULL DEFAULT 'BCP',
  `referencia`                VARCHAR(100)   NULL DEFAULT NULL,
  `notas`                     VARCHAR(255)   NULL DEFAULT NULL,
  `estado`                    ENUM('ACTIVO','ANULADO','APLICADO_DIRECTO') NOT NULL DEFAULT 'ACTIVO',
  `registrado_por_id`         INT(11)        NOT NULL,
  `registrado_en`             DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `anulador_id`               INT(11)        NULL DEFAULT NULL,
  `anulada_at`                DATETIME       NULL DEFAULT NULL,
  `sesion_aplicada_id`        INT(11)        NULL DEFAULT NULL,
  `confirmado_directo_por_id` INT(11)        NULL DEFAULT NULL,
  `confirmado_directo_en`     DATETIME       NULL DEFAULT NULL,
  `sesion_base_ajustada_id`   INT(11)        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_caja_estado` (`caja_origen_id`, `estado`),
  KEY `idx_sesion_aplicada` (`sesion_aplicada_id`),
  CONSTRAINT `fk_rk_caja`    FOREIGN KEY (`caja_origen_id`)            REFERENCES `caja`(`id_caja`),
  CONSTRAINT `fk_rk_reg`     FOREIGN KEY (`registrado_por_id`)         REFERENCES `postulante`(`id_postulante`),
  CONSTRAINT `fk_rk_anu`     FOREIGN KEY (`anulador_id`)               REFERENCES `postulante`(`id_postulante`),
  CONSTRAINT `fk_rk_conf`    FOREIGN KEY (`confirmado_directo_por_id`) REFERENCES `postulante`(`id_postulante`),
  CONSTRAINT `fk_rk_sesion`  FOREIGN KEY (`sesion_aplicada_id`)        REFERENCES `sesion_caja`(`id_sesion`),
  CONSTRAINT `fk_rk_base`    FOREIGN KEY (`sesion_base_ajustada_id`)   REFERENCES `sesion_caja`(`id_sesion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
