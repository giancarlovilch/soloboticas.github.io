<?php

class Response
{
    public static function json(
        bool $success,
        string $message,
        $data = null,
        int $statusCode = 200,
        ?array $errors = null
    ): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    public static function success(
        string $message = 'Operación exitosa',
        $data = null,
        int $statusCode = 200
    ): void {
        self::json(true, $message, $data, $statusCode);
    }

    public static function error(
        string $message = 'Ocurrió un error',
        int $statusCode = 500,
        ?array $errors = null
    ): void {
        self::json(false, $message, null, $statusCode, $errors);
    }

    public static function created(
        string $message = 'Recurso creado correctamente',
        $data = null
    ): void {
        self::json(true, $message, $data, 201);
    }

    public static function notFound(
        string $message = 'Recurso no encontrado'
    ): void {
        self::json(false, $message, null, 404);
    }

    public static function unauthorized(
        string $message = 'No autorizado'
    ): void {
        self::json(false, $message, null, 401);
    }

    public static function forbidden(
        string $message = 'Acceso prohibido'
    ): void {
        self::json(false, $message, null, 403);
    }

    public static function validationError(
        string $message = 'Error de validación',
        array $errors = []
    ): void {
        self::json(false, $message, null, 422, $errors);
    }
}