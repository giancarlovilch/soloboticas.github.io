## VERSION 1.0

```mysql
INSERT INTO estado (id_estado, descripcion) VALUES
(1, 'Egreso'),
(2, 'En curso'),
(3, 'Titulado'),
(4, 'Trunco');

INSERT INTO etapa (id_etapa, descripcion) VALUES
(1, 'Pendiente'),
(2, 'Entrevista'),
(3, 'Rechazado'),
(4, 'Contratado'),
(5, 'Suspendido'),
(6, 'Despedido');

INSERT INTO genero (id_genero, descripcion) VALUES
(1, 'Masculino'),
(2, 'Femenino'),
(3, 'Otro');

INSERT INTO institucion (id_institucion, descripcion) VALUES
(1, 'Instituto Federico Villarreal'),
(2, 'Instituto IDAT'),
(3, 'Instituto Superior Arzobispo Loayza'),
(4, 'Instituto Superior Daniel Alcides Carrión'),
(5, 'Otros'),
(6, 'Universidad María Auxiliadora'),
(7, 'Universidad Nacional Mayor de San Marcos'),
(8, 'Universidad Norbert Wiener'),
(9, 'Universidad Privada del Norte'),
(10, 'Universidad Tecnológica del Perú');

INSERT INTO nivel (id_nivel, descripcion) VALUES
(1, 'Básico'),
(2, 'Intermedio'),
(3, 'Avanzado');

INSERT INTO puesto (id_puesto, descripcion) VALUES
(1, 'Administración'),
(2, 'Almacén'),
(3, 'Caja'),
(4, 'Contabilidad'),
(5, 'Limpieza'),
(6, 'Practicante'),
(7, 'Técnica en Farmacia');

INSERT INTO situacion_vivienda (id_situacion, descripcion) VALUES
(1, 'Alquilada'),
(2, 'Familiar'),
(3, 'Propia');

INSERT INTO skill (id_skill, descripcion) VALUES
(1, 'AgenteBCP'),
(2, 'BPA'),
(3, 'BPD'),
(4, 'BPOF'),
(5, 'Caja'),
(6, 'Excel'),
(7, 'Inyectables');

INSERT INTO tipo_estudio (id_tipo, descripcion) VALUES
(1, 'Secundaria Completa'),
(2, 'Técnico'),
(3, 'Universitario');

INSERT INTO turno (id_turno, descripcion) VALUES
(1, 'Mañana'),
(2, 'Tarde');

INSERT INTO rol (id_rol, descripcion, activo) VALUES
(1, 'STAFF', 1),
(2, 'ADMIN', 1);

INSERT INTO local (id_local, descripcion, id_encargado, activo) VALUES
(2, 'Local 2', NULL, 1),
(3, 'Local 3', NULL, 1),
(4, 'Local 4', NULL, 1);

INSERT INTO caja (id_caja, local_id, descripcion, activo) VALUES
(2, 2, 'SB2', 1),
(6, 2, 'SB6', 1),

(3, 3, 'SB3', 1),
(5, 3, 'SB5', 1),
(7, 3, 'SB7', 1),

(4, 4, 'SB4', 1);

INSERT INTO modo (id_modo, descripcion) VALUES
(1, 'EFECTIVO'),
(2, 'YAPE'),
(3, 'PLIN'),
(4, 'VISAS'),
(5, 'BCP'),
(6, 'TRANSFERENCIA_BANCARIA');

INSERT INTO tipo_movimiento (id_tipo_movimiento, descripcion, activo) VALUES
(1, 'INGRESO', 1),
(2, 'EGRESO', 1);

INSERT INTO `checklist` (`id_checklist`, `descripcion`, `tipo`, `activo`) VALUES 
(1, 'Llegó a tiempo', 'APERTURA', 1),
(2, 'Marco su Asistencia Correctamente', 'APERTURA', 1),
(3, 'Chaqueta limpia y planchada', 'APERTURA', 1),
(4, 'Uñas cortas y limpias', 'APERTURA', 1),
(5, 'Cabello recogido / Aseo personal conforme', 'APERTURA', 1);

INSERT INTO `concepto_gastos_local` (`id_concepto`, `descripcion`, `activo`) VALUES
(1, 'Alquiler', 1),
(2, 'Agua', 1),
(3, 'Luz', 1),
(4, 'Internet', 1),
(5, 'Mantenimiento / Limpieza', 1),
(6, 'Arbitrios / Municipalidad', 1);

```





## INTEGRANTES

```mysql
INSERT INTO `postulante` (`id_postulante`, `nombres`, `apellidos`, `num_documento`, `genero_id`, `telefono`, `fecha_nacimiento`) VALUES
(1, 'Gian Carlo', 'Vilcamiche Chávez', '47238914', 1,'935812267','1991-02-16'),
(2, 'Solange Moulin', 'Coronel Camacllanqui', '75818239', 2,'923402449','2002-03-20'),
(3, 'Milagros Del Pilar', 'Huamán Cruzado', '44850621', 2,'986152754','1987-10-01'),
(4, 'Dariana', 'Bautista Contreras', '71694239', 2,'926491304','1999-08-19'),
(5, 'Patricia del Pilar', 'Obregon Pozo', '71637953', 2, '980815404','2001-08-10'),
(6, 'Maryori', 'Flores Ubaldo', '75519567', 2,'985951246','1999-10-16'),
(7, 'Maribel Rosario', 'Salazar Baldeon', '47512524', 2, '937863443','1992-11-10'),
(8, 'María Doris', 'García Torres', '46254125', 2, '932767767','1990-02-19'),
(9, 'Flor de Maria', 'Mercedes Huayta', '47752886', 2, '928134625','1990-06-29'),
(10, 'Karen Lizbeth', 'Martinez Encina', '72220359', 2, '953933814','2001-06-09'),
(11, 'Fiorella del Rosario', 'Chambi Rafaile', '48857877', 2, '991241518', '1998-05-20'),
(12, 'Sharik Sheylly', 'Rodriguez Pineda', '76863236', 2, '927025545','2004-12-01'),
(13, 'Monica', 'Quispe Ccallo', '74399262', 2,'967697231','2002-03-17'),
(14, 'Karin Gianina', 'Ramirez Calixto', '73389615', 2, '971292140',NULL),
(15, 'Leidi', 'Peralta Colunche', '71142925', 2, '924666882',NULL),
(16, 'Diana', 'Mendoza Huaman', '76221752', 2, '955059406','1998-03-03'),
(17, 'Rocío Geraldinne', 'Quispe Alberco', '72667321', 2, '936839098','1994-02-24'),
(18, 'Guillermina Yomnis', 'Santos Basilio', '48219564', 2, '912557536',NULL),
(19, 'Elizabeth', 'Flores Silva', '47943458', 2, NULL,NULL),
(20, 'Marina', 'Heredia Acuña', '44428885', 2, '949451967','1987-08-15'),
(21, 'Alexander Rafael', 'Suarez Chacón', '47823006', 1, '974190345', '1992-06-10'),
(22, 'Yolvi Romelia', 'Patricio Flores', '76794496', 2, '973486812', '1995-09-07'),
(23, 'Inoe', 'Ortiz Quispe', '70576163', 2, '921014820',NULL),
(24, 'Sheila', 'Marcos Chagua', '73634205', 2, '972021267','1995-11-27'),
(25, 'Elena Dayana', 'Peña Manrique', '76633896', 2, '923831364','1999-11-24'),
(26, 'Dilza Elizabeth', 'Alarcon Muñoz', '48213065', 2, '970832706','1992-06-27'),
(27, 'Sharon Candy', 'Marcos Alfaro', '76221750', 2, '936751302',NULL),
(28, 'Miriam Oriana', 'Aguirre Borja', '46303722', 2, '917328713','1990-04-08'),
(29, 'Yenifer Katia', 'Quispe Llacchua', '70686877', 2, '987083660','2002-07-10'),
(30, 'Lizbeth', 'Quispe de la cruz', '72109429', 2, '928349105','2001-03-30'),
(31, 'Yoselin Margarita', 'Baldera Siesquén', '48288048', 2, '927219177','1993-11-08'),
(32, 'Gavi', 'Santos ascencio', '71020821', 2, '922880107','2024-04-22'),
(33, 'Roy Anthony', 'Vilcamiche Chavez', '45627948', 1, '999443808','1989-03-02'),
(34, 'Loreli Elizabeth', 'Salas Zuñiga', '48409771', 2, '984135857','1994-02-10'),
(35, 'Nayeli', 'Benancio Espinoza', '75603108', 2, '931421447','2003-10-21'),
(36, 'Geraldine Rosario', 'Felices Escobar', '76279496', 2, '902280060','2000-12-28'),
(37, 'Analu', 'Fonseca Fernández', '74384465', 2, '955596689','2001-12-16'),
(38, 'Delina', 'Guillen Matos', '47496488', 2, '935669323','1992-10-29'),
(39, 'Lisset', 'Bonifacio Duran', '77807884', 2, '927914498','2001-04-25'),
(40, 'Ana Lucia', 'Coaquira Mamani', '76325704', 2, '936034533','1997-12-04'),
(41, 'Jhovani', 'Suarez Cueva', '62117689', 2, '990815725',NULL),
(42, 'Carola Liz', 'Carhuaricra Reyes', '70127392', 2, '965829567',NULL),
(43, 'Luis Daryl', 'Sanchez Garcia', '72552020', 1, '948676116','2004-09-02'),
(44, 'Carmen Esmeralda', 'Guadalupe Galarza', '71293391', 2, '927467567','1997-06-20'),
(45, 'Erika Yuliana', 'Guerrero Huerta', '73529760', 2, '910296978','2000-12-31'),
(46, 'Kristhel Valeria', 'Vilcamiche Chávez', '73623849', 2, NULL, NULL),
(47, 'Marta', 'Laurente Lopez', '48141371', 2, NULL, NULL),
(49, 'Lucelly Angelmira', 'Robles Jauregui', '74206381', 2, NULL, NULL),
(50, 'Yamilla Anelhy', 'Quispe Silva', '74588769', 2, NULL, NULL),
(51, 'Dayana Ross', 'Boy Arellano', '75824495', 2, NULL, NULL),
(52, 'Merlinda Yessica', 'Bautista Contreras', '71694214', 2, NULL, NULL),
(53, 'Lucia Belen', 'Arango Caico', '76507846', 2, NULL, NULL),
(54, 'Yovaly Tatiana', 'De la Cruz Roque', '73111770', 2, NULL, NULL),
(55, 'Orfelinda Anahi', 'Modesto Cespedes', '48348864', 2, NULL, NULL),
(56, 'Maria Ermendia', 'Yahuana Calderon', '74252343', 2, NULL, NULL),
(57, 'Elizabeth Rosa', 'Taype Cordova', '46300302', 2, NULL, NULL),
(58, 'Dasha Carla', 'Quichca Ramos', '71884519', 2, NULL, NULL),
(59, 'Sandra Marina', 'Revoredo Quiñones', '44958162', 2, NULL, NULL),
(60, 'Eswin Eli', 'Salazar Ramirez', '76084263', 1, NULL, NULL),
(61, 'Fany Yadira', 'Benites Niquin', '71810694', 2, NULL, NULL);
```

## ENROLAMIENTO CAJA

```mysql
-- 1. Inmediatamente el sistema genera su postulación al puesto 1 (Administración) en etapa 1 (Pendiente)
INSERT INTO `postulacion` (`id_postulacion`, `postulante_id`, `puesto_id`) 
VALUES (1, 1, 1);

-- 2. Actualizamos tu postulación: pasas de 'Pendiente' (1) a 'Contratado' (4)
UPDATE `postulacion` 
SET `etapa_id` = 4 
WHERE `postulante_id` = 1;

-- 3. Te creamos tu usuario en el sistema
-- postulante_id = 1 (Tú)
-- rol_id = 2 (ADMIN)
INSERT INTO usuario (postulante_id, rol_id, username, password) 
VALUES (1, 2, 'GIANCARLOVC', '47238914');


-- 4. Registramos la entrada de Gian Carlo (postulante_id = 1)
-- Usamos CURDATE() para la fecha y NOW() para la hora exacta de ingreso
INSERT INTO `asistencia` (`id_asistencia`,`postulante_id`, `fecha`, `hora_ingreso`, `estado`) 
VALUES (1,1, CURDATE(), NOW(),'A TIEMPO');

-- 5. Registramos el checklist para tu asistencia de hoy (asistencia_id = 1)
INSERT INTO `asistencia_checklist` (`asistencia_id`, `checklist_id`, `cumplido`, `observacion`) VALUES 
(1, 1, 0, 'Llegue tarde pero coordine con mi companhera para que me cubra'), 
(1, 2, 1, NULL), 
(1, 3, 1, NULL), 
(1, 4, 1, NULL);


```





## TEST CUADRE

```mysql
-- 1. El sistema registra que tú (id = 1) estás abriendo la caja SB2 (id = 2) en el turno Mañana (id = 1)
INSERT INTO `sesion_caja` (
    `caja_id`, 
    `turno_id`, 
    `postulante_apertura_id`, 
    `estado`, 
    `saldo_inicial`, 
    `fecha_operacion`
) VALUES (
    2, -- ID de la Caja SB2
    1, -- Turno Mañana
    1, -- Tu ID (Gian Carlo) como el que realiza la acción
    'ABIERTA', 
    200.00, -- Tu declaración de cuánto dinero base hay al iniciar
    CURDATE()
);

-- 2. El sistema automáticamente te registra como participante de esa sesión y responsable del dinero
INSERT INTO `sesion_participante` (
    `sesion_id`, 
    `postulante_id`, 
    `rol_participacion`, 
    `responsable_faltante`
) VALUES (
    LAST_INSERT_ID(), -- Toma dinámicamente el ID de la sesión que se acaba de crear arriba
    1,                -- Tu ID (Gian Carlo)
    'CAJERA',         -- El rol operativo para esta sesión
    1                 -- Eres el responsable directo del faltante (1 = Sí)
);

-- 3. Gastos de la farmacia (tipo_movimiento = 2: EGRESO, modo = 1: EFECTIVO)
INSERT INTO `movimiento_sesion` (`sesion_id`, `tipo_movimiento_id`, `modo_id`, `postulante_registro_id`, `descripcion`, `monto`)
VALUES (1, 2, 1, 1, 'Compra de lapicero', 2.00);

-- 4. sesion_id = 1, local_id = 2, emisor = 1 (Tú), concepto = 5 (Mantenimiento)
INSERT INTO `pago_local` (`sesion_id`, `local_id`, `postulante_emisor_id`, `concepto_id`, `monto`)
VALUES (1, 2, 1, 3, 2.00);

-- 5. Adelanto de sueldo a personal (Dariana, id_postulante = 4)
INSERT INTO `pago_personal` (`sesion_id`, `postulante_emisor_id`, `postulante_beneficiario_id`, `monto`)
VALUES (1, 1, 4, 5.00);

-- 6. Registramos las ventas del sistema externo (Ingresos digitales)
-- Ventas Yape (tipo = 1 INGRESO, modo = 2 Yape)
INSERT INTO `movimiento_sesion` (`sesion_id`, `tipo_movimiento_id`, `modo_id`, `postulante_registro_id`, `descripcion`, `monto`)
VALUES (1, 1, 2, 1, 'Yape', 10.00);

-- 7. Ventas Visa (tipo = 1 INGRESO, modo = 4 Visa)
INSERT INTO `movimiento_sesion` (`sesion_id`, `tipo_movimiento_id`, `modo_id`, `postulante_registro_id`, `descripcion`, `monto`)
VALUES (1, 1, 4, 1, 'Visa', 1.00);



```



## ENROLAMIENTO VENTAS

```mysql
-- 1. RRHH la contrata (Puesto 7: Técnica en Farmacia, Etapa 4: Contratado)
INSERT INTO `postulacion` (`postulante_id`, `puesto_id`, `etapa_id`) 
VALUES (11, 7, 4);

-- 2. Le creamos su usuario de acceso al sistema (Rol 1: STAFF)
-- Usamos su DNI como contraseña provisional
INSERT INTO `usuario` (`postulante_id`, `rol_id`, `username`, `password`) 
VALUES (11, 1, 'FIORELLACR', '48857877');

-- 3. Fiorella llega a trabajar y marca su asistencia
INSERT INTO `asistencia` (`id_asistencia`,`postulante_id`, `fecha`, `hora_ingreso`, `estado`) 
VALUES (2,11, CURDATE(), NOW(), 'A TIEMPO');

-- 4. Obligatoriamente llena su Checklist de Imagen
-- Como ella acaba de marcar asistencia, su ID de asistencia será el 2
INSERT INTO `asistencia_checklist` (`asistencia_id`, `checklist_id`, `cumplido`, `observacion`) VALUES 
(2, 1, 1, NULL), -- Llegó a tiempo: SÍ
(2, 2, 1, NULL), -- Marcó correctamente: SÍ
(2, 3, 1, NULL), -- Chaqueta limpia: SÍ
(2, 4, 1, NULL), -- Uñas cortas: SÍ
(2, 5, 1, NULL); -- Cabello recogido: SÍ

-- 5. El sistema la asigna automáticamente como la responsable de esa caja
INSERT INTO `sesion_participante` (`sesion_id`, `postulante_id`, `rol_participacion`, `responsable_faltante`) 
VALUES (1, 11, 'VENDEDORA', 1);

-- 6. Pasa el día... y al final de su turno, el sistema externo (ERP) 
-- dice que Fiorella vendió un total de 450.50 soles. Registramos eso en tu nueva tabla.
INSERT INTO `reporte_venta` (`sesion_id`, `postulante_vendedor_id`, `monto`) 
VALUES (1, 11, 450.50);

```
























