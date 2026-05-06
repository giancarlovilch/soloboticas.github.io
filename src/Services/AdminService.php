<?php
require_once __DIR__ . '/../Repositories/AdminRepository.php';

class AdminService {
    private AdminRepository $repository;

    public function __construct() {
        $this->repository = new AdminRepository();
    }

    public function gestionarContratacion(array $data): array {
        $id = $data['postulante_id'] ?? null;
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $rolId = $data['rol_id'] ?? 1; // 1 = STAFF, 2 = ADMIN[cite: 17]

        if (!$id || empty($username) || empty($password)) {
            return [
                'success' => false, 
                'message' => 'Faltan datos: ID de postulante, usuario y contraseña son obligatorios'
            ];
        }

        $res = $this->repository->contratarPostulante((int)$id, (int)$rolId, $username, $password);

        return $res 
            ? ['success' => true, 'message' => 'Trabajador contratado y usuario creado correctamente']
            : ['success' => false, 'message' => 'Error técnico al procesar el alta en la base de datos'];
    }
}