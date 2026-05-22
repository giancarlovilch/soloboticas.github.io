<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Helpers/Response.php';
require_once __DIR__ . '/../../config/env.php';

class PagoBBVAController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ---------------------------------------------------------------
    // Validación de API Key — header X-BBVA-Key o query ?api_key=
    // ---------------------------------------------------------------
    private function autenticar(): void
    {
        $keyEsperada = env('BBVA_API_KEY', '');

        if (empty($keyEsperada)) {
            Response::error('API Key no configurada en el servidor', 500);
        }

        $headers    = function_exists('getallheaders') ? getallheaders() : [];
        $keyRecibida = $headers['X-BBVA-Key']
                    ?? $headers['x-bbva-key']
                    ?? $_GET['api_key']
                    ?? '';

        if ($keyRecibida !== $keyEsperada) {
            Response::unauthorized('API Key inválida');
        }
    }

    // ---------------------------------------------------------------
    // POST /api/bbva/pago
    // Recibe UN pago desde la app móvil y lo guarda.
    // Body JSON: { id, cliente, monto, timestamp, titulo, texto,
    //              tituloBig, textoBig, subtexto, textoExtra, textoResumen,
    //              app, raw }
    // ---------------------------------------------------------------
    public function registrar(): void
    {
        $this->autenticar();

        $d = $this->getJsonInput();

        // Campos obligatorios
        if (empty($d['cliente']) || empty($d['monto']) || empty($d['timestamp'])) {
            $this->validationError('Faltan campos requeridos: cliente, monto, timestamp');
        }

        $timestampMs = (int) $d['timestamp'];
        $fechaNotif  = date('Y-m-d H:i:s', (int) ($timestampMs / 1000));

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO pagos_bbva
                (cliente, monto, timestamp_notif, fecha_notif,
                 titulo, titulo_big, texto, texto_big,
                 subtexto, texto_extra, texto_resumen,
                 app_origen, raw)
            VALUES
                (:cliente, :monto, :timestamp_notif, :fecha_notif,
                 :titulo, :titulo_big, :texto, :texto_big,
                 :subtexto, :texto_extra, :texto_resumen,
                 :app_origen, :raw)
        ");

        $stmt->execute([
            ':cliente'        => trim($d['cliente']),
            ':monto'          => (float) $d['monto'],
            ':timestamp_notif'=> $timestampMs,
            ':fecha_notif'    => $fechaNotif,
            ':titulo'         => $d['titulo']       ?? '',
            ':titulo_big'     => $d['tituloBig']    ?? '',
            ':texto'          => $d['texto']        ?? '',
            ':texto_big'      => $d['textoBig']     ?? null,
            ':subtexto'       => $d['subtexto']     ?? '',
            ':texto_extra'    => $d['textoExtra']   ?? '',
            ':texto_resumen'  => $d['textoResumen'] ?? '',
            ':app_origen'     => $d['app']          ?? '',
            ':raw'            => $d['raw']          ?? '',
        ]);

        // INSERT IGNORE: si el timestamp ya existe, rowCount() es 0
        if ($stmt->rowCount() === 0) {
            $this->success('Pago ya registrado (duplicado ignorado)', ['duplicado' => true]);
            return;
        }

        $this->created('Pago registrado', ['id' => $this->db->lastInsertId()]);
    }

    // ---------------------------------------------------------------
    // POST /api/bbva/pagos/lote
    // Recibe un array de pagos (máx. 50) para sincronización masiva.
    // Body JSON: { pagos: [ ...array de pagos... ] }
    // ---------------------------------------------------------------
    public function registrarLote(): void
    {
        $this->autenticar();

        $d     = $this->getJsonInput();
        $pagos = $d['pagos'] ?? [];

        if (empty($pagos) || !is_array($pagos)) {
            $this->validationError('Se esperaba { pagos: [...] }');
        }

        $pagos = array_slice($pagos, 0, 50);

        $guardados  = 0;
        $duplicados = 0;

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO pagos_bbva
                (cliente, monto, timestamp_notif, fecha_notif,
                 titulo, titulo_big, texto, texto_big,
                 subtexto, texto_extra, texto_resumen,
                 app_origen, raw)
            VALUES
                (:cliente, :monto, :timestamp_notif, :fecha_notif,
                 :titulo, :titulo_big, :texto, :texto_big,
                 :subtexto, :texto_extra, :texto_resumen,
                 :app_origen, :raw)
        ");

        foreach ($pagos as $d) {
            if (empty($d['cliente']) || empty($d['monto']) || empty($d['timestamp'])) {
                continue;
            }

            $timestampMs = (int) $d['timestamp'];
            $fechaNotif  = date('Y-m-d H:i:s', (int) ($timestampMs / 1000));

            $stmt->execute([
                ':cliente'        => trim($d['cliente']),
                ':monto'          => (float) $d['monto'],
                ':timestamp_notif'=> $timestampMs,
                ':fecha_notif'    => $fechaNotif,
                ':titulo'         => $d['titulo']       ?? '',
                ':titulo_big'     => $d['tituloBig']    ?? '',
                ':texto'          => $d['texto']        ?? '',
                ':texto_big'      => $d['textoBig']     ?? null,
                ':subtexto'       => $d['subtexto']     ?? '',
                ':texto_extra'    => $d['textoExtra']   ?? '',
                ':texto_resumen'  => $d['textoResumen'] ?? '',
                ':app_origen'     => $d['app']          ?? '',
                ':raw'            => $d['raw']          ?? '',
            ]);

            $stmt->rowCount() > 0 ? $guardados++ : $duplicados++;
        }

        $this->success('Lote procesado', [
            'guardados'  => $guardados,
            'duplicados' => $duplicados,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /admin/bbva-pagos
    // Vista HTML con tabla de pagos y total acumulado.
    // ---------------------------------------------------------------
    public function vista(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }

        $pagos = $this->db->query("
            SELECT id, cliente, monto, fecha_notif, titulo,
                   texto, texto_big, subtexto, texto_extra, app_origen, raw
            FROM pagos_bbva
            ORDER BY timestamp_notif DESC
            LIMIT 50
        ")->fetchAll();

        $row   = $this->db->query("SELECT COALESCE(SUM(monto),0) as total FROM pagos_bbva")->fetch();
        $total = (float) $row['total'];

        require_once __DIR__ . '/../../views/admin/bbva-pagos.php';
    }

    // ---------------------------------------------------------------
    // GET /api/bbva/pagos
    // Devuelve los últimos 50 pagos + total acumulado (JSON).
    // ---------------------------------------------------------------
    public function listar(): void
    {
        $this->autenticar();

        $pagos = $this->db->query("
            SELECT id, cliente, monto, fecha_notif, titulo, texto,
                   texto_big, subtexto, texto_extra, app_origen, recibido_en
            FROM pagos_bbva
            ORDER BY timestamp_notif DESC
            LIMIT 50
        ")->fetchAll();

        $total = $this->db->query("SELECT SUM(monto) as total FROM pagos_bbva")->fetch();

        $this->success('OK', [
            'total_acumulado' => (float) ($total['total'] ?? 0),
            'cantidad'        => count($pagos),
            'pagos'           => $pagos,
        ]);
    }
}
