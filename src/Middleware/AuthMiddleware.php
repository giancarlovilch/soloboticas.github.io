<?php

require_once __DIR__ . '/../Helpers/JWTHelper.php';

class AuthMiddleware
{
    /**
     * Extrae el Bearer token del header Authorization, compatible con Apache y nginx.
     */
    private static function getBearerToken(): ?string
    {
        $auth = '';

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (empty($auth)) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }

        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }

        return null;
    }

    /**
     * Valida el token JWT de la petición.
     * Si es válido devuelve el payload; si no, termina con 401.
     */
    public static function requireAuth(): array
    {
        $token = self::getBearerToken();

        if (!$token) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Token requerido. Incluye Authorization: Bearer <token>']);
            exit;
        }

        $payload = JWTHelper::verify($token);

        if (!$payload) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Token inválido o expirado']);
            exit;
        }

        return $payload;
    }

    /**
     * Igual que requireAuth() pero además exige rol ADMIN.
     */
    public static function requireAdmin(): array
    {
        $payload = self::requireAuth();

        if (($payload['rol'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Se requieren privilegios de administrador']);
            exit;
        }

        return $payload;
    }
}
