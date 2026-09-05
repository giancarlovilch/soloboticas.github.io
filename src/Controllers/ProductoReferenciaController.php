<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Helpers/Response.php';
require_once __DIR__ . '/../../config/env.php';

class ProductoReferenciaController extends Controller
{
    private PDO $db;

    // IdEstablecimiento (Softpharma) => id_local (web)
    private const LOCALES_VALIDOS = [2, 3, 4];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ---------------------------------------------------------------
    // Validación de API Key — header X-Sync-Key
    // ---------------------------------------------------------------
    private function autenticar(): void
    {
        $keyEsperada = env('PRODUCTOS_SYNC_KEY', '');

        if (empty($keyEsperada)) {
            Response::error('API Key no configurada en el servidor', 500);
        }

        $headers     = function_exists('getallheaders') ? getallheaders() : [];
        $keyRecibida = $headers['X-Sync-Key'] ?? $headers['x-sync-key'] ?? '';

        if (!hash_equals($keyEsperada, $keyRecibida)) {
            Response::unauthorized('API Key inválida');
        }
    }

    // ---------------------------------------------------------------
    // POST /api/productos-referencia/sincronizar
    // Reemplaza el snapshot completo de productos de UN local.
    // Body JSON: { id_local, productos: [ { cod_producto, nombre_producto,
    //              precio_costo, precio_venta, stock }, ... ] }
    // ---------------------------------------------------------------
    public function sincronizar(): void
    {
        $this->autenticar();

        $d         = $this->getJsonInput();
        $idLocal   = (int) ($d['id_local'] ?? 0);
        $productos = $d['productos'] ?? [];

        if (!in_array($idLocal, self::LOCALES_VALIDOS, true)) {
            $this->validationError('id_local inválido');
        }

        if (empty($productos) || !is_array($productos)) {
            $this->validationError('Se esperaba { id_local, productos: [...] } con al menos un producto');
        }

        $ahora = date('Y-m-d H:i:s');
        $inicio = microtime(true);

        // Códigos que ya existían para este local ANTES de esta corrida, para poder
        // contar cuántos son realmente nuevos (se usa en el registro/log del dashboard).
        $existentes = [];
        $stmtExist = $this->db->prepare('SELECT cod_producto FROM producto_referencia WHERE id_local = :id_local');
        $stmtExist->execute([':id_local' => $idLocal]);
        foreach ($stmtExist->fetchAll(PDO::FETCH_COLUMN) as $cod) {
            $existentes[$cod] = true;
        }

        $this->db->beginTransaction();

        try {
            $del = $this->db->prepare('DELETE FROM producto_referencia WHERE id_local = :id_local');
            $del->execute([':id_local' => $idLocal]);

            $stmt = $this->db->prepare('
                INSERT INTO producto_referencia
                    (cod_producto, id_local, nombre_producto, precio_costo, precio_venta, stock, actualizado_en)
                VALUES
                    (:cod_producto, :id_local, :nombre_producto, :precio_costo, :precio_venta, :stock, :actualizado_en)
                ON DUPLICATE KEY UPDATE
                    nombre_producto = VALUES(nombre_producto),
                    precio_costo    = VALUES(precio_costo),
                    precio_venta    = VALUES(precio_venta),
                    stock           = VALUES(stock),
                    actualizado_en  = VALUES(actualizado_en)
            ');

            $insertados = 0;
            $nuevos     = 0;

            foreach ($productos as $p) {
                $codProducto = trim((string) ($p['cod_producto'] ?? ''));

                if ($codProducto === '') {
                    continue;
                }

                $stmt->execute([
                    ':cod_producto'    => $codProducto,
                    ':id_local'        => $idLocal,
                    ':nombre_producto' => (string) ($p['nombre_producto'] ?? ''),
                    ':precio_costo'    => (float) ($p['precio_costo'] ?? 0),
                    ':precio_venta'    => (float) ($p['precio_venta'] ?? 0),
                    ':stock'           => (float) ($p['stock'] ?? 0),
                    ':actualizado_en'  => $ahora,
                ]);

                $insertados++;
                if (!isset($existentes[$codProducto])) {
                    $nuevos++;
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            $duracionMs = (int) round((microtime(true) - $inicio) * 1000);
            $this->registrarSyncLog($idLocal, count($productos), 0, $duracionMs, 'ERROR', $e->getMessage());
            Response::error('Error al sincronizar productos: ' . $e->getMessage(), 500);
        }

        $duracionMs = (int) round((microtime(true) - $inicio) * 1000);
        $this->registrarSyncLog($idLocal, $insertados, $nuevos, $duracionMs, 'OK', null);

        $this->success('Sincronización completada', [
            'id_local'         => $idLocal,
            'productos'        => $insertados,
            'productos_nuevos' => $nuevos,
            'duracion_ms'      => $duracionMs,
            'actualizado_en'   => $ahora,
        ]);
    }

    // ---------------------------------------------------------------
    // Deja registro de cada corrida para el widget del dashboard admin.
    // No debe interrumpir la respuesta principal si falla.
    // ---------------------------------------------------------------
    private function registrarSyncLog(int $idLocal, int $total, int $nuevos, int $duracionMs, string $estado, ?string $mensaje): void
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO producto_referencia_sync_log
                    (id_local, total_productos, productos_nuevos, duracion_ms, estado, mensaje)
                VALUES
                    (:id_local, :total, :nuevos, :duracion, :estado, :mensaje)
            ');
            $stmt->execute([
                ':id_local' => $idLocal,
                ':total'    => $total,
                ':nuevos'   => $nuevos,
                ':duracion' => $duracionMs,
                ':estado'   => $estado,
                ':mensaje'  => $mensaje,
            ]);
        } catch (Exception $e) {
            // Silencioso a propósito: un fallo del log no debe tumbar la sincronización real.
        }
    }
}
