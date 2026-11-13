<?php

require_once __DIR__ . '/../../config/env.php';

/**
 * Cliente HTTP hacia el microservicio soloboticas-softpharma (Django), que
 * compara lo declarado por cada trabajadora contra SOFTPHARMA (el
 * facturador legacy). Server-a-servidor, autenticado con API key fija
 * (header X-API-Key) -- no hay usuario humano en este lado.
 */
class SoftpharmaApiClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('SOFTPHARMA_API_BASE_URL', ''), '/');
        $this->apiKey  = env('SOFTPHARMA_API_KEY', '');
    }

    public function configurado(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    /** GET /api/v1/usuarios → [{usuario, nombre, numero_documento}] */
    public function getUsuarios(): array
    {
        $data = $this->request('GET', '/api/v1/usuarios');
        return $data['usuarios'] ?? [];
    }

    /** GET /api/v1/ventas/dia → [{id_caja, usuario, nombre, local, turno, apertura, cierre, venta_neta, ...}] */
    public function getVentasDia(string $fecha, ?string $local = null): array
    {
        $qs = 'fecha=' . urlencode($fecha) . ($local ? '&local=' . urlencode($local) : '');
        $data = $this->request('GET', '/api/v1/ventas/dia?' . $qs);
        return $data['cierres'] ?? [];
    }

    /** POST /api/v1/cuadres/registrar */
    public function registrarCuadre(string $usuario, string $fecha, float $monto, ?string $local): array
    {
        return $this->request('POST', '/api/v1/cuadres/registrar', [
            'usuario'         => $usuario,
            'fecha'           => $fecha,
            'monto_declarado' => $monto,
            'local'           => $local,
        ]);
    }

    private function request(string $method, string $path, array $body = []): array
    {
        if (!$this->configurado()) {
            throw new RuntimeException('SOFTPHARMA_API_BASE_URL / SOFTPHARMA_API_KEY no configurados');
        }

        $ch = curl_init($this->baseUrl . $path);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'X-API-Key: ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($ch, $opts);
        $raw      = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            throw new RuntimeException("Error de conexión con SOFTPHARMA API: {$error}");
        }

        $data = json_decode($raw, true);
        if ($httpCode >= 400) {
            $detail = $data['detail'] ?? $raw;
            throw new RuntimeException("SOFTPHARMA API [{$httpCode}]: {$detail}");
        }

        return $data ?? [];
    }
}
