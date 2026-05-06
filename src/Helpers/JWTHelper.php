<?php

require_once __DIR__ . '/../../config/env.php';

class JWTHelper
{
    private static function secret(): string
    {
        $s = env('JWT_SECRET', '');
        if (empty($s)) {
            throw new RuntimeException('JWT_SECRET no está configurado en .env');
        }
        return $s;
    }

    public static function create(array $data): string
    {
        $header  = self::b64e(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = self::b64e(json_encode(array_merge($data, [
            'iat' => time(),
            'exp' => time() + (8 * 3600),
        ])));

        $sig = self::b64e(hash_hmac('sha256', "$header.$payload", self::secret(), true));
        return "$header.$payload.$sig";
    }

    /**
     * Verifica un JWT. Devuelve el payload si es válido, null si no.
     */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $sig] = $parts;

        $expected = self::b64e(hash_hmac('sha256', "$header.$payload", self::secret(), true));
        if (!hash_equals($expected, $sig)) return null;

        $data = json_decode(self::b64d($payload), true);
        if (!$data || !isset($data['exp'])) return null;
        if ($data['exp'] < time()) return null;

        return $data;
    }

    private static function b64e(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
