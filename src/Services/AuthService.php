<?php

require_once __DIR__ . '/../Repositories/AuthRepository.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../Helpers/JWTHelper.php';

class AuthService
{
    private AuthRepository $repository;

    public function __construct()
    {
        $this->repository = new AuthRepository();
    }

    public function login(array $data): array
    {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Usuario y contraseña son requeridos', 'status' => 422];
        }

        $user = $this->repository->findByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales incorrectas', 'status' => 401];
        }

        if (!(bool)$user['activo']) {
            return ['success' => false, 'message' => 'Tu cuenta está desactivada. Contacta al administrador.', 'status' => 403];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Credenciales incorrectas', 'status' => 401];
        }

        $payload = [
            'sub'       => $user['postulante_id'],
            'username'  => $username,
            'rol'       => $user['rol_nombre'],
            'full_name' => trim($user['nombres'] . ' ' . $user['apellidos']),
        ];

        $token = JWTHelper::create($payload);

        return [
            'success' => true,
            'message' => '¡Bienvenido, ' . $user['nombres'] . '!',
            'data'    => [
                'postulante_id'  => $user['postulante_id'],
                'username'       => $username,
                'rol'            => $user['rol_nombre'],
                'nombre_completo' => $payload['full_name'],
                'token'          => $token,
            ],
            'status' => 200,
        ];
    }

    /**
     * Cambia la contraseña de un usuario. Solo accesible por ADMIN.
     */
    public function changePassword(int $postulante_id, string $newPassword): array
    {
        if (strlen($newPassword) < 4) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 4 caracteres'];
        }

        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $ok = $this->repository->updatePassword($postulante_id, $hashed);

        if (!$ok) {
            return ['success' => false, 'message' => 'No se encontró el usuario o ocurrió un error'];
        }

        return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
    }
}
