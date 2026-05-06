<?php

require_once __DIR__ . '/../Helpers/Response.php';


// Esta es la "Clase Maestra" (Abstracta). No se usa sola, 
// sirve como molde estructural para todos los demás controladores.
abstract class Controller
{
    // SUBTÍTULO: El escáner de buzón digital (Lee datos JSON)
    protected function getJsonInput(): array
    {
        // Revisa la entrada "cruda" del servidor (el flujo de datos)
        $rawInput = file_get_contents('php://input');

        if (empty($rawInput)) {
            return [];
        }
        // Intenta traducir el código JSON a una lista (Array) de PHP
        $data = json_decode($rawInput, true);

        // Control de calidad: Si el JSON está mal escrito, manda error 400
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido', 400);
        }

        return is_array($data) ? $data : [];
    }
    // SUBTÍTULO: El receptor del muelle de carga (Formularios tradicionales)
    protected function getPostInput(): array
    {
        // Retorna lo que viene por el método POST estándar
        return $_POST ?? [];
    }

    // SUBTÍTULO: El lector de etiquetas de envío (Parámetros en la URL)
    protected function getQueryParams(): array
    {
        // Retorna lo que viene después del "?" en la URL
        return $_GET ?? [];
    }
    // SUBTÍTULO: El Clasificador General (Decide qué datos usar)
    protected function getAllInput(): array
    {
        // Primero intenta leer JSON (moderno)
        $json = $this->getJsonInput();
        if (!empty($json)) {
            return $json;
        }
        // Si no hay JSON, recurre al formulario clásico (POST)
        return $this->getPostInput();
    }

    // --- SUBTÍTULOS: El Sistema de Megafonía (Respuestas Estandarizadas) ---
    // Cada función avisa un estado diferente al mundo exterior.

    // Megáfono: "¡Todo salió bien!" (200 OK)
    protected function success(
        string $message = 'Operación exitosa',
        $data = null,
        int $statusCode = 200
    ): void {
        Response::success($message, $data, $statusCode);
    }

    // Megáfono: "¡Obra terminada / Recurso creado!" (201 Created)
    protected function created(
        string $message = 'Recurso creado correctamente',
        $data = null
    ): void {
        Response::created($message, $data);
    }

    // Megáfono: "¡Algo se rompió!" (500 Server Error)
    protected function error(
        string $message = 'Ocurrió un error',
        int $statusCode = 500,
        ?array $errors = null
    ): void {
        Response::error($message, $statusCode, $errors);
    }

    // Megáfono: "¡Aquí no hay nada!" (404 Not Found)
    protected function notFound(
        string $message = 'Recurso no encontrado'
    ): void {
        Response::notFound($message);
    }

    // Megáfono: "¡Los planos están mal!" (422 Unprocessable Entity)
    protected function validationError(
        string $message = 'Error de validación',
        array $errors = []
    ): void {
        Response::validationError($message, $errors);
    }

    // Megáfono: "¡Usted no trabaja aquí!" (401 Unauthorized)
    protected function unauthorized(
        string $message = 'No autorizado'
    ): void {
        Response::unauthorized($message);
    }

    // Megáfono: "¡Prohibido el paso!" (403 Forbidden)
    protected function forbidden(
        string $message = 'Acceso prohibido'
    ): void {
        Response::forbidden($message);
    }
}
