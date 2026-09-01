<?php

require_once __DIR__ . '/../Core/Database.php';

/**
 * Descuentos/penalidades registrados manualmente por el admin (ej. por desaprobar
 * la encuesta BCP). Es un registro informativo — no descuenta ni calcula nada
 * automáticamente; el admin lo usa como referencia al pagar.
 *
 * Nota: se llama "descuento" (no "penalidad") para no chocar con el concepto
 * de penalidad de horario (tabla concepto_penalidad, ajena a esto). En pantalla
 * se sigue mostrando como "Penalidades".
 */
class DescuentoRepository
{
    private PDO $db;

    public const DESCRIPCION_MAX_PALABRAS = 50;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** @return true|string true si se creó, o el mensaje de error */
    public function crear(
        int $postulanteId, string $tipo, string $descripcion, float $monto,
        string $mes, int $registradoPorId
    ): bool|string {
        $tipo = strtoupper(trim($tipo));
        $descripcion = trim($descripcion);
        if ($tipo === '') return 'El tipo es requerido';
        if ($descripcion === '') return 'La descripción es requerida';
        if (str_word_count($descripcion) > self::DESCRIPCION_MAX_PALABRAS) {
            return 'La descripción no puede superar las ' . self::DESCRIPCION_MAX_PALABRAS . ' palabras';
        }
        if ($monto <= 0) return 'El monto debe ser mayor a 0';
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) return 'Mes inválido';

        $fecha = date('Y-m-t', strtotime("{$mes}-01"));

        $this->db->prepare(
            "INSERT INTO descuento (postulante_id, tipo, estado, descripcion, monto, mes, fecha, registrado_por_id)
             VALUES (:pid, :tipo, 'APLICADO', :desc, :monto, :mes, :fecha, :reg)"
        )->execute([
            'pid' => $postulanteId, 'tipo' => $tipo, 'desc' => $descripcion,
            'monto' => $monto, 'mes' => $mes, 'fecha' => $fecha, 'reg' => $registradoPorId,
        ]);
        return true;
    }

    /** Descuentos de un mes (todos, o filtrados a un trabajador) para el admin */
    public function listar(?string $mes = null, ?int $postulanteId = null): array
    {
        $where  = [];
        $params = [];
        if ($mes)           { $where[] = 'd.mes = :mes';            $params['mes'] = $mes; }
        if ($postulanteId)  { $where[] = 'd.postulante_id = :pid';  $params['pid'] = $postulanteId; }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT d.id_descuento, d.postulante_id, p.nombres AS trabajador_nombre,
                    d.tipo, d.estado, d.descripcion, d.monto, d.mes, d.fecha,
                    d.registrado_por_id, r.nombres AS registrado_por_nombre, d.fecha_registro
             FROM descuento d
             INNER JOIN postulante p ON p.id_postulante = d.postulante_id
             INNER JOIN postulante r ON r.id_postulante = d.registrado_por_id
             {$whereSql}
             ORDER BY d.fecha DESC, d.id_descuento DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return true|string */
    public function actualizar(int $id, string $descripcion, float $monto): bool|string
    {
        $descripcion = trim($descripcion);
        if ($descripcion === '') return 'La descripción es requerida';
        if (str_word_count($descripcion) > self::DESCRIPCION_MAX_PALABRAS) {
            return 'La descripción no puede superar las ' . self::DESCRIPCION_MAX_PALABRAS . ' palabras';
        }
        if ($monto <= 0) return 'El monto debe ser mayor a 0';

        $this->db->prepare(
            "UPDATE descuento SET descripcion = :desc, monto = :monto WHERE id_descuento = :id"
        )->execute(['desc' => $descripcion, 'monto' => $monto, 'id' => $id]);
        return true;
    }

    public function eliminar(int $id): bool
    {
        return $this->db->prepare("DELETE FROM descuento WHERE id_descuento = :id")->execute(['id' => $id]);
    }
}
