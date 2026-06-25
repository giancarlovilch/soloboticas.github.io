<?php

// Captcha matemático simple basado en sesión, sin dependencias externas.
class Captcha
{
    private const SESSION_KEY = 'captcha';
    private const TTL_SECONDS = 300;

    public static function generate(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $_SESSION[self::SESSION_KEY] = [
            'answer'  => $a + $b,
            'expires' => time() + self::TTL_SECONDS,
        ];

        return ['question' => "¿Cuánto es {$a} + {$b}?"];
    }

    // La respuesta se invalida tras un solo intento (éxito o fallo) para evitar fuerza bruta.
    public static function verify(?string $answer): bool
    {
        $data = $_SESSION[self::SESSION_KEY] ?? null;
        unset($_SESSION[self::SESSION_KEY]);

        if (!$data || time() > $data['expires'] || $answer === null || $answer === '') {
            return false;
        }

        return is_numeric($answer) && (int) $answer === (int) $data['answer'];
    }
}
