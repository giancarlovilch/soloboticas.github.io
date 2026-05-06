<?php

require_once __DIR__ . '/../Helpers/Response.php';

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->normalizePath($path),
            'handler' => $handler
        ];
    }

    public function dispatch(string $requestMethod, string $requestUri): void
    {
        // Normalizamos la ruta de entrada para que coincida con las registradas
        $requestPath = $this->normalizePath(parse_url($requestUri, PHP_URL_PATH) ?? '/');

        foreach ($this->routes as $route) {
            // Verificamos método HTTP
            if ($route['method'] !== strtoupper($requestMethod)) {
                continue;
            }

            // Buscamos coincidencia de patrón
            $params = $this->matchRoute($route['path'], $requestPath);

            if ($params !== false) {
                [$controllerName, $methodName] = $route['handler'];

                try {
                    if (!class_exists($controllerName)) {
                        Response::error("Controlador {$controllerName} no encontrado", 500);
                    }

                    $controller = new $controllerName();

                    if (!method_exists($controller, $methodName)) {
                        Response::error("Método {$methodName} no encontrado en {$controllerName}", 500);
                    }

                    // Ejecutar el método con los parámetros extraídos (si existen)
                    call_user_func_array([$controller, $methodName], $params);
                    return;
                } catch (Throwable $e) {
                    error_log("Error en Router: " . $e->getMessage());
                    Response::error('Error interno del servidor', 500);
                }
            }
        }

        // Si llega aquí, ninguna ruta coincidió
        Response::notFound('Ruta no encontrada');
    }

    private function matchRoute(string $routePath, string $requestPath)
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $index => $routePart) {
            $requestPart = $requestParts[$index] ?? null;

            // Detectar parámetros dinámicos {id} o {dni}
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $routePart)) {
                $params[] = $requestPart;
                continue;
            }

            if ($routePart !== $requestPart) {
                return false;
            }
        }

        return $params;
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? $path : rtrim($path, '/');
    }
}