<?php

require_once __DIR__ . '/../Core/Database.php';

class PostulacionRepository
{
    private PDO $db;
    private string $table = 'postulacion';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findExistingApplicationByDocument(string $numDocumento): ?array
    {
        $sql = "SELECT 
                    p.id_postulante,
                    p.nombres,
                    p.apellidos,
                    p.fecha_nacimiento,
                    p.num_documento,
                    po.id_postulacion,
                    po.etapa_id
                FROM postulacion po
                INNER JOIN postulante p ON p.id_postulante = po.postulante_id
                WHERE p.num_documento = :num_documento
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'num_documento' => $numDocumento
        ]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function create(array $data): array
    {
        $sql = "INSERT INTO {$this->table} (
                postulante_id,
                puesto_id,
                etapa_id
            ) VALUES (
                :postulante_id,
                :puesto_id,
                :etapa_id
            )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'postulante_id' => $data['postulante_id'],
            'puesto_id' => $data['puesto_id'],
            'etapa_id' => $data['etapa_id']
        ]);

        $id = (int) $this->db->lastInsertId();

        return [
            'id_postulacion' => $id,
            'postulante_id' => $data['postulante_id'],
            'puesto_id' => $data['puesto_id'],
            'etapa_id' => $data['etapa_id']
        ];
    }

    public function findApplicationViewByDocument(string $numDocumento): ?array
    {
        $sql = "SELECT 
            p.id_postulante,
            p.nombres,
            p.apellidos,
            p.genero_id,
            p.fecha_nacimiento,
            p.email,
            p.telefono,
            p.situacion_vivienda_id,
            p.num_documento,
            p.cv_url,
            po.id_postulacion,
            po.fecha_postulacion,
            po.puesto_id,
            po.etapa_id,
            e.descripcion AS etapa_descripcion,
            es.institucion_id,
            es.tipo_id AS tipo_estudio_id,
            es.estado_id,
            es.fecha_inicio,
            es.fecha_fin,
            pref.turno_id
        FROM postulacion po
        INNER JOIN postulante p ON p.id_postulante = po.postulante_id
        LEFT JOIN etapa e ON e.id_etapa = po.etapa_id
        LEFT JOIN estudio es ON es.postulante_id = p.id_postulante
        LEFT JOIN preferencias pref ON pref.postulante_id = p.id_postulante
        WHERE p.num_documento = :num_documento
        LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'num_documento' => $numDocumento
        ]);

        $result = $stmt->fetch();

        return $result ?: null;
    }


    public function createEstudio(array $data): bool
    {
        $sql = "INSERT INTO estudio (
                postulante_id,
                tipo_id,
                institucion_id,
                estado_id,
                fecha_inicio,
                fecha_fin
            ) VALUES (
                :postulante_id,
                :tipo_id,
                :institucion_id,
                :estado_id,
                :fecha_inicio,
                :fecha_fin
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'postulante_id' => $data['postulante_id'],
            'tipo_id' => $data['tipo_id'],
            'institucion_id' => $data['institucion_id'],
            'estado_id' => $data['estado_id'],
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
        ]);
    }

    public function createPreferencia(array $data): bool
    {
        $sql = "INSERT INTO preferencias (
                turno_id,
                postulante_id
            ) VALUES (
                :turno_id,
                :postulante_id
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'turno_id' => $data['turno_id'],
            'postulante_id' => $data['postulante_id'],
        ]);
    }

    public function createExperiencia(array $data): bool
    {
        $sql = "INSERT INTO experiencia (
            postulante_id,
            empresa,
            cargo,
            fecha_inicio,
            fecha_fin
        ) VALUES (
            :postulante_id,
            :empresa,
            :cargo,
            :fecha_inicio,
            :fecha_fin
        )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'postulante_id' => $data['postulante_id'],
            'empresa' => $data['empresa'],
            'cargo' => $data['cargo'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'] ?? null,
        ]);
    }
    public function deleteEstudioByPostulante(int $postulanteId): bool
    {
        $sql = "DELETE FROM estudio WHERE postulante_id = :postulante_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'postulante_id' => $postulanteId
        ]);
    }

    public function deletePreferenciaByPostulante(int $postulanteId): bool
    {
        $sql = "DELETE FROM preferencias WHERE postulante_id = :postulante_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'postulante_id' => $postulanteId
        ]);
    }
    public function deleteExperienciasByPostulante(int $postulanteId): bool
    {
        $sql = "DELETE FROM experiencia WHERE postulante_id = :postulante_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'postulante_id' => $postulanteId
        ]);
    }
    public function findExperienciasByPostulante(int $postulanteId): array
    {
        $sql = "SELECT
                id_experiencia,
                empresa,
                cargo,
                fecha_inicio,
                fecha_fin
            FROM experiencia
            WHERE postulante_id = :postulante_id
            ORDER BY fecha_inicio DESC, id_experiencia DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'postulante_id' => $postulanteId
        ]);

        return $stmt->fetchAll();
    }

    
    public function createSkill(array $data): bool
    {
        $sql = "INSERT INTO postulante_skill (
                postulante_id,
                skill_id,
                nivel_id
            ) VALUES (
                :postulante_id,
                :skill_id,
                :nivel_id
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'postulante_id' => $data['postulante_id'],
            'skill_id' => $data['skill_id'],
            'nivel_id' => $data['nivel_id'] ?? null,
        ]);
    }

    public function deleteSkillsByPostulante(int $postulanteId): bool
    {
        $sql = "DELETE FROM postulante_skill WHERE postulante_id = :postulante_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'postulante_id' => $postulanteId
        ]);
    }

    public function findSkillsByPostulante(int $postulanteId): array
    {
        $sql = "SELECT
                ps.skill_id,
                ps.nivel_id,
                s.descripcion AS skill_descripcion,
                n.descripcion AS nivel_descripcion
            FROM postulante_skill ps
            INNER JOIN skill s ON s.id_skill = ps.skill_id
            LEFT JOIN nivel n ON n.id_nivel = ps.nivel_id
            WHERE ps.postulante_id = :postulante_id
            ORDER BY s.descripcion ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'postulante_id' => $postulanteId
        ]);

        return $stmt->fetchAll();
    }
}
