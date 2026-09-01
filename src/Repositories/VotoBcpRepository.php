<?php

require_once __DIR__ . '/../Core/Database.php';

/**
 * Encuesta mensual anónima sobre el uso del agente BCP de cada cajera
 * (posible fraccionamiento de operaciones u operaciones con tarjeta propia
 * para inflar bonificaciones). La vota todo el personal habilitado y
 * contratado sobre las cajeras que trabajaron en el mes. Independiente de
 * las fichas de desempeño (encuesta_desempeno) y de los cuadres de caja.
 */
class VotoBcpRepository
{
    private PDO $db;

    public const PREGUNTAS = [
        'tarjeta_propia'       => '¿Realizó operaciones con su tarjeta personal o con la de la empresa con el fin de incrementar sus bonificaciones?',
        'fraccionamiento'      => '¿Fraccionó operaciones a sus clientes con el fin de incrementar sus bonificaciones?',
        'irregularidad'        => 'En síntesis, ¿incurrió en alguna irregularidad con el fin de incrementar sus bonificaciones?',
        'apropiacion_sobrante' => '¿Percibe usted algún riesgo de apropiación indebida de sobrantes en su cuadre de caja?',
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Mes que está abierto para encuestar hoy, o null si no hay ventana activa.
     * Ventana: últimos 3 días del mes (encuesta ese mes) + primeros 3 días del
     * mes siguiente (sigue encuestando el mes que acaba de cerrar).
     */
    public function getMesEncuestado(?string $hoy = null): ?string
    {
        $hoy = $hoy ? new DateTime($hoy) : new DateTime('today');
        $dia = (int)$hoy->format('j');

        if ($dia <= 3) {
            return (clone $hoy)->modify('first day of last month')->format('Y-m');
        }
        if ($dia > (int)$hoy->format('t') - 3) {
            return $hoy->format('Y-m');
        }
        return null;
    }

    /** Cajeras que abrieron caja durante el mes, con el conteo de veces por caja (SB2, SB3, ...) */
    public function getCajerasDelMes(string $mes, int $excluirId = 0): array
    {
        [$anio, $nmes] = explode('-', $mes);
        $desde = "{$anio}-{$nmes}-01";
        $hasta = date('Y-m-t', strtotime($desde));

        $cajas = $this->db->query(
            "SELECT id_caja, descripcion FROM caja WHERE activo = 1 ORDER BY descripcion"
        )->fetchAll();

        $stmt = $this->db->prepare(
            "SELECT sc.postulante_apertura_id AS cajera_id, p.nombres, sc.caja_id, COUNT(*) AS veces
             FROM sesion_caja sc
             INNER JOIN postulante p ON p.id_postulante = sc.postulante_apertura_id
             WHERE sc.fecha_operacion BETWEEN :desde AND :hasta
               AND sc.postulante_apertura_id != :excluir
             GROUP BY sc.postulante_apertura_id, sc.caja_id"
        );
        $stmt->execute(['desde' => $desde, 'hasta' => $hasta, 'excluir' => $excluirId]);

        $cajeras = [];
        foreach ($stmt->fetchAll() as $r) {
            $cid = (int)$r['cajera_id'];
            if (!isset($cajeras[$cid])) {
                $cajeras[$cid] = ['id' => $cid, 'nombre' => $r['nombres'], 'porCaja' => []];
                foreach ($cajas as $c) $cajeras[$cid]['porCaja'][$c['id_caja']] = 0;
            }
            $cajeras[$cid]['porCaja'][$r['caja_id']] = (int)$r['veces'];
        }

        usort($cajeras, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));
        return ['cajas' => $cajas, 'cajeras' => array_values($cajeras)];
    }

    public function yaVoto(int $votanteId, string $mes): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM voto_bcp_cajera WHERE votante_id = :vid AND mes = :mes LIMIT 1"
        );
        $stmt->execute(['vid' => $votanteId, 'mes' => $mes]);
        return (bool)$stmt->fetchColumn();
    }

    public const COMENTARIO_MAX_PALABRAS = 200;

    /**
     * Registra todos los votos de un votante para el mes de una sola vez (anónimo hacia el resto),
     * más un comentario libre opcional (hasta COMENTARIO_MAX_PALABRAS palabras, también anónimo).
     */
    public function registrarVotos(int $votanteId, string $mes, array $votos, string $password, string $comentario = ''): bool|string
    {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $votanteId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        if ($this->yaVoto($votanteId, $mes)) return 'Ya enviaste tu encuesta de este mes';
        if (empty($votos)) return 'No hay cajeras para calificar';

        $comentario = trim($comentario);
        if (str_word_count($comentario) > self::COMENTARIO_MAX_PALABRAS) {
            return 'El comentario no puede superar las ' . self::COMENTARIO_MAX_PALABRAS . ' palabras';
        }

        $campos = array_keys(self::PREGUNTAS);
        $cols   = implode(', ', $campos);
        $vals   = implode(', ', array_map(fn($c) => ":{$c}", $campos));
        $ins    = $this->db->prepare(
            "INSERT INTO voto_bcp_cajera (cajera_id, votante_id, mes, {$cols})
             VALUES (:cid, :vid, :mes, {$vals})"
        );
        $insCom = $this->db->prepare(
            "INSERT INTO voto_bcp_comentario (votante_id, mes, comentario) VALUES (:vid, :mes, :com)"
        );

        $this->db->beginTransaction();
        try {
            foreach ($votos as $cajeraId => $respuestas) {
                $cajeraId = (int)$cajeraId;
                if ($cajeraId === $votanteId) continue; // nunca autocalificación

                $params = ['cid' => $cajeraId, 'vid' => $votanteId, 'mes' => $mes];
                foreach ($campos as $campo) {
                    $v = (int)($respuestas[$campo] ?? 0);
                    if ($v < 1 || $v > 10) {
                        throw new Exception('Todas las cajeras deben calificarse del 1 al 10 en las 3 preguntas');
                    }
                    $params[$campo] = $v;
                }
                $ins->execute($params);
            }
            if ($comentario !== '') {
                $insCom->execute(['vid' => $votanteId, 'mes' => $mes, 'com' => $comentario]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return $e->getMessage();
        }
    }

    /** Comentarios anónimos del mes (sin revelar quién los escribió) */
    public function getComentarios(string $mes): array
    {
        $stmt = $this->db->prepare(
            "SELECT comentario, fecha_registro FROM voto_bcp_comentario WHERE mes = :mes ORDER BY fecha_registro ASC"
        );
        $stmt->execute(['mes' => $mes]);
        return $stmt->fetchAll();
    }

    /** Resultados públicos agregados del mes (promedios por cajera, sin revelar quién votó) */
    public function getResultados(string $mes): array
    {
        $avgCols = implode(', ', array_map(
            fn($c) => "ROUND(AVG(v.{$c}), 2) AS prom_{$c}",
            array_keys(self::PREGUNTAS)
        ));
        $stmt = $this->db->prepare(
            "SELECT v.cajera_id, p.nombres, COUNT(*) AS total_votos, {$avgCols}
             FROM voto_bcp_cajera v
             INNER JOIN postulante p ON p.id_postulante = v.cajera_id
             WHERE v.mes = :mes
             GROUP BY v.cajera_id, p.nombres
             ORDER BY p.nombres ASC"
        );
        $stmt->execute(['mes' => $mes]);
        return $stmt->fetchAll();
    }

    /**
     * Ranking de aprobación/desaprobación del mes: promedio de las 4 preguntas (1-10,
     * donde 10 = más sospecha de fraude) convertido a % de sospecha. Desaprueba si ese
     * % supera 50 (promedio > 5). Solo incluye cajeras con al menos un voto — si nadie
     * la calificó, no aparece (no cuenta como aprobada ni desaprobada).
     *
     * Descuento sugerido (solo si desaprueba): % de sospecha / 10 — ej. 50.1% de
     * sospecha => 5.01% de descuento, hasta un máximo de 10% con 100% de sospecha.
     * Si aprueba (<=50%), el descuento sugerido es 0.
     */
    public function getRankingAprobacion(string $mes): array
    {
        $campos  = array_keys(self::PREGUNTAS);
        $sumExpr = implode(' + ', array_map(fn($c) => "v.{$c}", $campos));
        $n       = count($campos);
        $stmt = $this->db->prepare(
            "SELECT v.cajera_id, p.nombres, COUNT(*) AS total_votos,
                    ROUND(AVG(({$sumExpr}) / {$n}), 2) AS promedio,
                    ROUND(AVG(({$sumExpr}) / {$n}) * 10, 1) AS pct_sospecha
             FROM voto_bcp_cajera v
             INNER JOIN postulante p ON p.id_postulante = v.cajera_id
             WHERE v.mes = :mes
             GROUP BY v.cajera_id, p.nombres
             ORDER BY pct_sospecha DESC"
        );
        $stmt->execute(['mes' => $mes]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['aprobado']       = (float)$r['promedio'] <= 5;
            $r['descuento_pct']  = $r['aprobado'] ? 0.0 : round((float)$r['pct_sospecha'] / 10, 2);
        }
        return $rows;
    }

    // ── Admin: CRUD directo sobre los votos individuales ──────────

    public function getVotosAdmin(string $mes): array
    {
        $campos = implode(', ', array_map(fn($c) => "v.{$c}", array_keys(self::PREGUNTAS)));
        $stmt = $this->db->prepare(
            "SELECT v.id_voto, v.cajera_id, pc.nombres AS cajera_nombre,
                    v.votante_id, pv.nombres AS votante_nombre,
                    v.mes, {$campos}, v.fecha_registro
             FROM voto_bcp_cajera v
             INNER JOIN postulante pc ON pc.id_postulante = v.cajera_id
             INNER JOIN postulante pv ON pv.id_postulante = v.votante_id
             WHERE v.mes = :mes
             ORDER BY pc.nombres ASC, pv.nombres ASC"
        );
        $stmt->execute(['mes' => $mes]);
        return $stmt->fetchAll();
    }

    /** @param array<string,int> $respuestas clave = pregunta (ver PREGUNTAS), valor = 1-10 */
    public function actualizarVoto(int $idVoto, array $respuestas): bool
    {
        $campos = array_keys(self::PREGUNTAS);
        $params = ['id' => $idVoto];
        foreach ($campos as $campo) {
            $v = (int)($respuestas[$campo] ?? 0);
            if ($v < 1 || $v > 10) return false;
            $params[$campo] = $v;
        }
        $sets = implode(', ', array_map(fn($c) => "{$c} = :{$c}", $campos));
        return $this->db->prepare("UPDATE voto_bcp_cajera SET {$sets} WHERE id_voto = :id")->execute($params);
    }

    public function eliminarVoto(int $idVoto): bool
    {
        return $this->db->prepare("DELETE FROM voto_bcp_cajera WHERE id_voto = :id")->execute(['id' => $idVoto]);
    }

    /** Meses (más reciente primero) que tienen al menos un voto registrado */
    public function getMesesConVotos(): array
    {
        return array_column(
            $this->db->query("SELECT DISTINCT mes FROM voto_bcp_cajera ORDER BY mes DESC")->fetchAll(),
            'mes'
        );
    }

    /** Comentarios del mes con autor visible, solo para moderación de admin */
    public function getComentariosAdmin(string $mes): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id_comentario, c.votante_id, p.nombres AS votante_nombre, c.comentario, c.fecha_registro
             FROM voto_bcp_comentario c
             INNER JOIN postulante p ON p.id_postulante = c.votante_id
             WHERE c.mes = :mes
             ORDER BY c.fecha_registro ASC"
        );
        $stmt->execute(['mes' => $mes]);
        return $stmt->fetchAll();
    }

    public function eliminarComentario(int $idComentario): bool
    {
        return $this->db->prepare("DELETE FROM voto_bcp_comentario WHERE id_comentario = :id")
            ->execute(['id' => $idComentario]);
    }
}
