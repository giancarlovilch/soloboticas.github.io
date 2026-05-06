<?php

require_once __DIR__ . '/../Core/Database.php';

class AuthRepository
{
    private PDO $db;
    private string $table = 'usuario';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT
                    u.postulante_id,
                    u.password,
                    u.activo,
                    r.descripcion AS rol_nombre,
                    p.nombres,
                    p.apellidos
                FROM {$this->table} u
                INNER JOIN rol r       ON u.rol_id       = r.id_rol
                INNER JOIN postulante p ON u.postulante_id = p.id_postulante
                WHERE u.username = :username
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Actualiza la contraseña ya hasheada de un usuario.
     * Devuelve false si el usuario no existe.
     */
    public function findByPostulanteId(int $id): ?array
    {
        $sql  = "SELECT * FROM {$this->table} WHERE postulante_id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updatePassword(int $postulante_id, string $hashedPassword): bool
    {
        $sql = "UPDATE {$this->table} SET password = :pw WHERE postulante_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pw' => $hashedPassword, 'id' => $postulante_id]);

        return $stmt->rowCount() > 0;
    }
}
