<?php

require_once __DIR__ . '/../Core/Database.php';

class PostulanteRepository
{
    private PDO $db;
    private string $table = 'postulante';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $sql = "SELECT 
                    id_postulante,
                    nombres,
                    apellidos,
                    genero_id,
                    fecha_nacimiento,
                    email,
                    telefono,
                    situacion_vivienda_id,
                    num_documento,
                    fecha_registro,
                    fecha_modificacion,
                    cv_url
                FROM {$this->table}
                ORDER BY id_postulante DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT 
                    id_postulante,
                    nombres,
                    apellidos,
                    genero_id,
                    fecha_nacimiento,
                    email,
                    telefono,
                    situacion_vivienda_id,
                    num_documento,
                    fecha_registro,
                    fecha_modificacion,
                    cv_url
                FROM {$this->table}
                WHERE id_postulante = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findByDocument(string $numDocumento): ?array
    {
        $sql = "SELECT 
                    id_postulante,
                    nombres,
                    apellidos,
                    genero_id,
                    fecha_nacimiento,
                    email,
                    telefono,
                    situacion_vivienda_id,
                    num_documento,
                    fecha_registro,
                    fecha_modificacion,
                    cv_url
                FROM {$this->table}
                WHERE num_documento = :num_documento
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['num_documento' => $numDocumento]);

        $result = $stmt->fetch();

        return $result ?: null;
    }
    

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT 
                    id_postulante,
                    nombres,
                    apellidos,
                    genero_id,
                    fecha_nacimiento,
                    email,
                    telefono,
                    situacion_vivienda_id,
                    num_documento,
                    fecha_registro,
                    fecha_modificacion,
                    cv_url
                FROM {$this->table}
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function create(array $data): array
    {
        $intOrNull = fn($v) => ($v === '' || $v === null) ? null : (int)$v;

        $sql = "INSERT INTO {$this->table} (
                    nombres, apellidos, genero_id, fecha_nacimiento,
                    email, telefono, situacion_vivienda_id, num_documento, cv_url
                ) VALUES (
                    :nombres, :apellidos, :genero_id, :fecha_nacimiento,
                    :email, :telefono, :situacion_vivienda_id, :num_documento, :cv_url
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombres'             => $data['nombres']             ?? null,
            'apellidos'           => $data['apellidos']           ?? null,
            'genero_id'           => $intOrNull($data['genero_id']           ?? null),
            'fecha_nacimiento'    => ($data['fecha_nacimiento'] ?? '') ?: null,
            'email'               => $data['email']               ?? null,
            'telefono'            => $data['telefono']            ?? null,
            'situacion_vivienda_id' => $intOrNull($data['situacion_vivienda_id'] ?? null),
            'num_documento'       => $data['num_documento'],
            'cv_url'              => $data['cv_url']              ?? null,
        ]);

        $id = (int) $this->db->lastInsertId();

        return $this->findById($id);
    }

    public function update(int $id, array $data): ?array
    {
        $intOrNull = fn($v) => ($v === '' || $v === null) ? null : (int)$v;

        $sql = "UPDATE {$this->table} SET
                    nombres = :nombres, apellidos = :apellidos,
                    genero_id = :genero_id, fecha_nacimiento = :fecha_nacimiento,
                    email = :email, telefono = :telefono,
                    situacion_vivienda_id = :situacion_vivienda_id,
                    num_documento = :num_documento, cv_url = :cv_url
                WHERE id_postulante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'                  => $id,
            'nombres'             => $data['nombres']             ?? null,
            'apellidos'           => $data['apellidos']           ?? null,
            'genero_id'           => $intOrNull($data['genero_id']           ?? null),
            'fecha_nacimiento'    => ($data['fecha_nacimiento'] ?? '') ?: null,
            'email'               => $data['email']               ?? null,
            'telefono'            => $data['telefono']            ?? null,
            'situacion_vivienda_id' => $intOrNull($data['situacion_vivienda_id'] ?? null),
            'num_documento'       => $data['num_documento'],
            'cv_url'              => $data['cv_url']              ?? null,
        ]);

        return $this->findById($id);
    }

    public function updateFoto(int $id, string $fotoUrl): bool
    {
        $sql = "UPDATE {$this->table} SET foto_url = :url WHERE id_postulante = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['url' => $fotoUrl, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_postulante = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
}
