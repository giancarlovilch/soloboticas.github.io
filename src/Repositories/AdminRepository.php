<?php

require_once __DIR__ . '/../Core/Database.php';

class AdminRepository
{
    private PDO $db;

    public function __construct()
    {
        // Obtiene la conexión centralizada de la base de datos sb
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene la lista completa de postulantes con su etapa y puesto real
     * Se utilizan alias (AS) para que coincidan exactamente con las llaves del JSON en JS
     */
    public function obtenerTodos(): array
    {
        $sql = "SELECT p.id_postulante AS id,
                   CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
                   p.num_documento,
                   e.descripcion AS etapa_nombre,
                   u.activo,
                   u.username,
                   GROUP_CONCAT(pu.descripcion SEPARATOR ', ') AS puesto_nombre,
                   DATE(p.fecha_registro) AS fecha_creacion,
                   p.fecha_registro
            FROM postulante p
            LEFT JOIN postulacion pos ON p.id_postulante = pos.postulante_id
            LEFT JOIN etapa e ON p.etapa_id = e.id_etapa
            LEFT JOIN usuario u ON p.id_postulante = u.postulante_id
            LEFT JOIN puesto pu ON pos.puesto_id = pu.id_puesto
            WHERE p.id_postulante IS NOT NULL
            GROUP BY p.id_postulante, e.descripcion, u.activo, u.username, p.fecha_registro
        ORDER BY p.fecha_registro DESC";

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetalleCompleto(int $id): array
    {
        try {
            // 1. Datos base, Género, Vivienda y Usuario
            $sql = "SELECT p.*, 
                       CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo, 
                       g.descripcion AS genero_desc,
                       sv.descripcion AS vivienda_desc,
                       u.username, u.activo, u.rol_id, r.descripcion AS rol_desc,
                       p.etapa_id, e.descripcion AS etapa_desc,
                       pu.descripcion AS puesto_desc
                FROM postulante p
                LEFT JOIN genero g ON p.genero_id = g.id_genero
                LEFT JOIN situacion_vivienda sv ON p.situacion_vivienda_id = sv.id_situacion
                LEFT JOIN usuario u ON p.id_postulante = u.postulante_id
                LEFT JOIN rol r ON u.rol_id = r.id_rol
                LEFT JOIN postulacion pos ON p.id_postulante = pos.postulante_id
                LEFT JOIN etapa e ON p.etapa_id = e.id_etapa
                LEFT JOIN puesto pu ON pos.puesto_id = pu.id_puesto
                WHERE p.id_postulante = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $postulante = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$postulante) return [];

            // 2. Experiencia Laboral (Tabla: experiencia)
            $sqlExp = "SELECT * FROM experiencia WHERE postulante_id = :id ORDER BY fecha_inicio DESC";
            $stmtExp = $this->db->prepare($sqlExp);
            $stmtExp->execute(['id' => $id]);
            $postulante['experiencias'] = $stmtExp->fetchAll(PDO::FETCH_ASSOC);

            // 3. Estudios (Tabla: estudio + institución + tipo + estado)
            $sqlEdu = "SELECT est.*, inst.descripcion AS nombre_institucion, 
                          te.descripcion AS tipo_estudio, es.descripcion AS estado_estudio
                   FROM estudio est
                   LEFT JOIN institucion inst ON est.institucion_id = inst.id_institucion
                   LEFT JOIN tipo_estudio te ON est.tipo_id = te.id_tipo
                   LEFT JOIN estado es ON est.estado_id = es.id_estado
                   WHERE est.postulante_id = :id";
            $stmtEdu = $this->db->prepare($sqlEdu);
            $stmtEdu->execute(['id' => $id]);
            $postulante['estudios'] = $stmtEdu->fetchAll(PDO::FETCH_ASSOC);

            // 4. Habilidades (Tabla: postulante_skill + skill + nivel) - CORREGIDO
            $sqlSkills = "SELECT ps.skill_id, ps.nivel_id, s.descripcion AS habilidad, n.descripcion AS nivel
              FROM postulante_skill ps
              JOIN skill s ON ps.skill_id = s.id_skill
              JOIN nivel n ON ps.nivel_id = n.id_nivel
              WHERE ps.postulante_id = :id";
            $stmtSkills = $this->db->prepare($sqlSkills);
            $stmtSkills->execute(['id' => $id]);
            $postulante['skills'] = $stmtSkills->fetchAll(PDO::FETCH_ASSOC);

            // 5. Preferencias de Turno (Tabla: preferencias + turno) - CORREGIDO
            $sqlTurnos = "SELECT pref.turno_id, t.descripcion 
          FROM preferencias pref
          JOIN turno t ON pref.turno_id = t.id_turno
          WHERE pref.postulante_id = :id";
            $stmtTurnos = $this->db->prepare($sqlTurnos);
            $stmtTurnos->execute(['id' => $id]);
            $postulante['turnos'] = $stmtTurnos->fetchAll(PDO::FETCH_ASSOC);

            // Dentro de obtenerDetalleCompleto(int $id), al final antes del return:
            $sqlPostulaciones = "SELECT puesto_id, etapa_id FROM postulacion WHERE postulante_id = :id";
            $stmtPos = $this->db->prepare($sqlPostulaciones);
            $stmtPos->execute(['id' => $id]);
            $postulante['postulaciones'] = $stmtPos->fetchAll(PDO::FETCH_ASSOC);

            return $postulante;
        } catch (PDOException $e) {
            error_log("DATABASE ERROR: " . $e->getMessage());
            return ["error" => $e->getMessage()];
        }
    }

    public function getTiposPersonal(): array
    {
        return $this->db->query(
            "SELECT codigo, descripcion, rango FROM tipo_personal ORDER BY orden ASC"
        )->fetchAll();
    }

    public function obtenerCatalogo(string $tabla): array
    {
        try {
            // Sanitizamos el nombre de la tabla para evitar inyecciones, 
            // aunque aquí solo lo llamamos internamente[cite: 15].
            $sql = "SELECT * FROM $tabla ORDER BY descripcion ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener catálogo $tabla: " . $e->getMessage());
            return [];
        }
    }

    public function actualizarDetalleCompleto(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction(); //[cite: 9]

            // 1. ACTUALIZAR POSTULANTE (Datos extendidos)[cite: 7, 8, 9]
            $sqlPost = "UPDATE postulante SET
                        nombres = :nom, apellidos = :ape, genero_id = :gen,
                        fecha_nacimiento = :fnac, email = :email, telefono = :tel,
                        situacion_vivienda_id = :viv, direccion = :dir, distrito = :dis,
                        tipo_personal = :tipo
                        WHERE id_postulante = :id";
            // Las columnas INT NULL y DATE NULL rechazan string vacío en strict mode → null
            $intOrNull  = fn($v) => ($v === '' || $v === null) ? null : (int)$v;
            $dateOrNull = fn($v) => ($v === '' || $v === null) ? null : $v;

            $this->db->prepare($sqlPost)->execute([
                'nom'   => $data['nombres']                ?? null,
                'ape'   => $data['apellidos']              ?? null,
                'gen'   => $intOrNull($data['genero_id']   ?? null),
                'fnac'  => $dateOrNull($data['fecha_nacimiento'] ?? null),
                'email' => $data['email']                  ?? null,
                'tel'   => $data['telefono']               ?? null,
                'viv'   => $intOrNull($data['situacion_vivienda_id'] ?? null),
                'dir'   => $data['direccion']              ?? null,
                'dis'   => $data['distrito']               ?? null,
                'tipo'  => $data['tipo_personal']          ?? null,
                'id'    => $id,
            ]);

            // 2a. GUARDAR ETAPA directamente en postulante (independiente de puestos)
            $etapaId = $intOrNull($data['etapa_id'] ?? null);
            if ($etapaId !== null) {
                $this->db->prepare(
                    "UPDATE postulante SET etapa_id = :etapa WHERE id_postulante = :id"
                )->execute(['etapa' => $etapaId, 'id' => $id]);
            }

            // 2b. SINCRONIZAR PUESTOS ASIGNADOS (MULTIFUNCIONAL)[cite: 18, 20]
            $this->db->prepare("DELETE FROM postulacion WHERE postulante_id = :id")->execute(['id' => $id]);

            if (!empty($data['puestos'])) {
                $sqlPos  = "INSERT INTO postulacion (postulante_id, puesto_id, etapa_id) VALUES (:id, :puesto, :etapa)";
                $stmtPos = $this->db->prepare($sqlPos);
                foreach ($data['puestos'] as $pue) {
                    $stmtPos->execute([
                        'id'     => $id,
                        'puesto' => $intOrNull($pue['puesto_id'] ?? null),
                        'etapa'  => $etapaId,
                    ]);
                }
            }

            // 3. SINCRONIZAR EXPERIENCIAS (Limpiar y Reinsertar)[cite: 7, 9]
            $this->db->prepare("DELETE FROM experiencia WHERE postulante_id = :id")->execute(['id' => $id]);
            if (!empty($data['experiencias'])) {
                $sqlExp = "INSERT INTO experiencia (postulante_id, empresa, cargo, funciones, fecha_inicio, fecha_fin) 
                           VALUES (:id, :emp, :car, :fun, :ini, :fin)";
                $stmtExp = $this->db->prepare($sqlExp);
                foreach ($data['experiencias'] as $exp) {
                    $stmtExp->execute([
                        'id'  => $id,
                        'emp' => $exp['empresa'],
                        'car' => $exp['cargo'],
                        'fun' => $exp['funciones'] ?? null,
                        'ini' => $exp['fecha_inicio'],
                        'fin' => $exp['fecha_fin'] ?: null
                    ]);
                }
            }

            // 4. SINCRONIZAR ESTUDIOS[cite: 7, 9]
            $this->db->prepare("DELETE FROM estudio WHERE postulante_id = :id")->execute(['id' => $id]);
            if (!empty($data['estudios'])) {
                $sqlEst = "INSERT INTO estudio (postulante_id, tipo_id, institucion_id, estado_id, fecha_inicio, fecha_fin) 
                           VALUES (:id, :tipo, :inst, :est, :ini, :fin)";
                $stmtEst = $this->db->prepare($sqlEst);
                foreach ($data['estudios'] as $est) {
                    $stmtEst->execute([
                        'id'   => $id,
                        'tipo' => $est['tipo_id'],
                        'inst' => $est['institucion_id'],
                        'est'  => $est['estado_id'],
                        'ini'  => $est['fecha_inicio'],
                        'fin'  => $est['fecha_fin'] ?: null
                    ]);
                }
            }

            // 5. SINCRONIZAR HABILIDADES (Skills) - CORREGIDO
            // Primero eliminamos los registros actuales para evitar errores de duplicado
            $this->db->prepare("DELETE FROM postulante_skill WHERE postulante_id = :id")->execute(['id' => $id]);

            if (!empty($data['skills'])) {
                $sqlSki = "INSERT INTO postulante_skill (postulante_id, skill_id, nivel_id) 
               VALUES (:id, :sid, :nid)";
                $stmtSki = $this->db->prepare($sqlSki);

                foreach ($data['skills'] as $skill) {
                    // Validamos que los datos existan antes de insertar[cite: 19]
                    if (!empty($skill['skill_id']) && !empty($skill['nivel_id'])) {
                        $stmtSki->execute([
                            'id'  => $id,
                            'sid' => $skill['skill_id'],
                            'nid' => $skill['nivel_id']
                        ]);
                    }
                }
            }

            // 6. SINCRONIZAR PREFERENCIAS DE TURNO
            $this->db->prepare("DELETE FROM preferencias WHERE postulante_id = :id")->execute(['id' => $id]);
            if (!empty($data['turnos'])) {
                $sqlTur = "INSERT INTO preferencias (postulante_id, turno_id) VALUES (:id, :tid)";
                $stmtTur = $this->db->prepare($sqlTur);
                foreach ($data['turnos'] as $tid) {
                    $stmtTur->execute(['id' => $id, 'tid' => (int)$tid]);
                }
            }

            // 7. ESTADO Y ROL DE USUARIO
            if (isset($data['activo'])) {
                $this->actualizarEstado($id, (int)$data['activo']);
            }
            if (!empty($data['rol_id'])) {
                $this->db->prepare(
                    "UPDATE usuario SET rol_id = :rol WHERE postulante_id = :pid"
                )->execute(['rol' => (int)$data['rol_id'], 'pid' => $id]);
            }

            $this->db->commit(); //[cite: 9]
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack(); //[cite: 9]
            error_log("Error crítico en actualización integral SB: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asegura que el estado 'activo' se guarde, incluso si el registro de usuario es nuevo[cite: 15]
     */
    public function actualizarEstado(int $id, int $activo): bool
    {
        // Esta consulta asegura que si el usuario no existe, se cree con un perfil básico
        $sql = "INSERT INTO usuario (postulante_id, activo, rol_id, username, password) 
            VALUES (:id, :activo, 2, CONCAT('user_', :id2), 'sin_password') 
            ON DUPLICATE KEY UPDATE activo = :activo_up";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'        => $id,
            'id2'       => $id,
            'activo'    => $activo,
            'activo_up' => $activo
        ]);
    }

    /**
     * Actualiza el username de un usuario.
     * Devuelve true si OK, string con el mensaje de error si falla.
     */
    public function actualizarUsername(int $postulanteId, string $username): bool|string
    {
        // Verificar que no esté en uso por otro usuario
        $check = $this->db->prepare(
            "SELECT postulante_id FROM usuario WHERE username = :u AND postulante_id != :id LIMIT 1"
        );
        $check->execute(['u' => strtoupper(trim($username)), 'id' => $postulanteId]);
        if ($check->fetch()) {
            return 'El nombre de usuario ya está en uso por otro colaborador.';
        }

        $stmt = $this->db->prepare(
            "UPDATE usuario SET username = :u WHERE postulante_id = :id"
        );
        $stmt->execute(['u' => strtoupper(trim($username)), 'id' => $postulanteId]);
        return true;
    }

    /**
     * Busca la información detallada de un postulante por ID, DNI o Nombre
     * Incluye datos adicionales para el formulario de edición
     */
    public function buscarPostulantes(string $termino): array
    {
        $sql = "SELECT p.id_postulante AS id, 
                       CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo, 
                       p.num_documento, 
                       p.email,
                       p.telefono,
                       p.direccion,
                       p.distrito,
                       p.etapa_id,
                       e.descripcion AS etapa_nombre,
                       u.activo,
                       u.username,
                       pu.descripcion AS puesto_nombre
                FROM postulante p
                LEFT JOIN postulacion pos ON p.id_postulante = pos.postulante_id
                LEFT JOIN etapa e ON p.etapa_id = e.id_etapa
                LEFT JOIN usuario u ON p.id_postulante = u.postulante_id
                LEFT JOIN puesto pu ON pos.puesto_id = pu.id_puesto
                WHERE p.num_documento LIKE :term 
                   OR p.nombres LIKE :term 
                   OR p.apellidos LIKE :term
                   OR p.id_postulante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'term' => "%$termino%",
            'id' => is_numeric($termino) ? (int)$termino : 0
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Procesa la contratación insertando/actualizando en postulacion y usuario
     * Transacción para asegurar la integridad de los datos en sb
     */
    public function contratarPostulante(int $id, int $rolId, string $username, string $password): bool
    {
        try {
            $this->db->beginTransaction();

            // Actualizamos a la etapa 4 (Contratado)
            $sqlEtapa = "INSERT INTO postulacion (postulante_id, puesto_id, etapa_id) 
                         VALUES (:id, 1, 4) 
                         ON DUPLICATE KEY UPDATE etapa_id = 4";
            $stmt1 = $this->db->prepare($sqlEtapa);
            $stmt1->execute(['id' => $id]);

            // Creamos o actualizamos el acceso del usuario
            $sqlUser = "INSERT INTO usuario (postulante_id, rol_id, username, password, activo) 
                        VALUES (:id, :rol, :user, :pass, 1)
                        ON DUPLICATE KEY UPDATE rol_id = :rol_up, username = :user_up, password = :pass_up, activo = 1";

            $stmt2 = $this->db->prepare($sqlUser);
            $stmt2->execute([
                'id'      => $id,
                'rol'     => $rolId,
                'user'    => $username,
                'pass'    => $password,
                'rol_up'  => $rolId,
                'user_up' => $username,
                'pass_up' => $password
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error en contratación Solo Boticas: " . $e->getMessage());
            return false;
        }
    }
}
