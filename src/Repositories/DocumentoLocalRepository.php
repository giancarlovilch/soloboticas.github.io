<?php

require_once __DIR__ . '/../Core/Database.php';

/**
 * Documentación de locales.
 *
 * Modelo mental:
 *  - `documento_tipo`   → el catálogo que arma el ADMIN (los "items"): licencia, extintores,
 *                         contrato de alquiler, etc. Ahí se define si CADUCA o es FIJO.
 *  - `documento_local`  → el papel real de cada local. Una fila por periodo de vigencia, así
 *                         que renovar no pisa nada: la anterior queda con es_actual = 0.
 *  - `documento_local_log` → bitácora de todo (quién cargó, quién revisó, quién firmó).
 *
 * El estado que se ve en pantalla NO es solo la columna `estado`: si el documento está
 * VIGENTE pero su fecha_fin ya pasó, se muestra VENCIDO sin que nadie tenga que tocarlo.
 * Eso se resuelve en calcularEstado() para no depender de un cron.
 */
class DocumentoLocalRepository
{
    private PDO $db;

    /** Estados guardados en BD (flujo) */
    public const ESTADOS = ['PENDIENTE', 'EN_REVISION', 'VIGENTE', 'OBSERVADO', 'NO_APLICA'];

    /** Categorías sugeridas para agrupar el catálogo */
    public const CATEGORIAS = [
        'MUNICIPAL'  => 'Municipal',
        'SANITARIO'  => 'Sanitario / DIGEMID',
        'SEGURIDAD'  => 'Seguridad (Defensa Civil)',
        'TRIBUTARIO' => 'Tributario / SUNAT',
        'CONTRATO'   => 'Contratos y alquiler',
        'SERVICIOS'  => 'Servicios y equipos',
        'PERSONAL'   => 'Personal',
        'GENERAL'    => 'General',
    ];

    /**
     * DOCUMENTO   → un solo papel por local (licencia, contrato). Es el modo de siempre.
     * INVENTARIO  → N unidades físicas por local (termohigrómetro, extintor, botiquín),
     *               cada una con su propia fecha/costo/vencimiento/firma. La cantidad
     *               requerida se define POR LOCAL en `documento_tipo_cantidad`.
     */
    public const TIPOS_MEDICION = [
        'DOCUMENTO'  => 'Documento único',
        'INVENTARIO' => 'Inventario (varias unidades)',
    ];

    /**
     * Vigencia PERIODICO: no es "vence una vez", es una obligación que se repite
     * (revisar el libro de recetas cada día, subir algo a DIGEMID el día 15 de cada
     * mes). El vencimiento del periodo ACTUAL se calcula solo, en vivo — nunca lo
     * escribe un humano. Ver periodoActual().
     */
    public const PERIODICIDADES = [
        'DIARIO'  => 'Diario',
        'SEMANAL' => 'Semanal',
        'MENSUAL' => 'Mensual',
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ─────────────────────────────────────────────────────────────
    //  Estado derivado (fecha + flujo)
    // ─────────────────────────────────────────────────────────────

    /**
     * Traduce (estado guardado + fechas) al estado que realmente ve el usuario.
     * Devuelve ['codigo', 'label', 'clase', 'dias'] donde `dias` son los días que
     * faltan para vencer (negativo si ya venció, null si no caduca).
     *
     * Para PERIODICO, `fecha_fin` no es un dato guardado por un humano: getChecklist()
     * ya la reemplaza en vivo por el vencimiento del periodo ACTUAL antes de llamar
     * aquí (hoy si es diario, el día-N de esta semana o de este mes). Si el `doc` que
     * llega es null/de un periodo pasado, se trata igual que "sin registrar".
     */
    public static function calcularEstado(?array $doc, int $diasAlerta = 30): array
    {
        $mk = fn(string $c, string $l, string $cl, ?int $d = null) =>
            ['codigo' => $c, 'label' => $l, 'clase' => $cl, 'dias' => $d];

        $esPeriodico = ($doc['vigencia'] ?? null) === 'PERIODICO';

        if (!$esPeriodico && ($doc === null || empty($doc['doc_id']))) {
            return $mk('PENDIENTE', 'Sin registrar', 'doc-badge--pend');
        }

        $estado = $doc['estado'] ?? null;

        if ($estado === 'NO_APLICA') return $mk('NO_APLICA', 'No aplica', 'doc-badge--na');
        if ($estado === 'OBSERVADO') return $mk('OBSERVADO', 'Observado', 'doc-badge--obs');
        if (!$esPeriodico && $estado === 'PENDIENTE') return $mk('PENDIENTE', 'Sin registrar', 'doc-badge--pend');

        $fin  = $doc['fecha_fin'] ?? null;
        $dias = null;
        if (!empty($fin)) {
            $hoy  = new DateTime('today');
            $dias = (int)$hoy->diff(new DateTime($fin))->format('%r%a');
        }

        if ($esPeriodico) {
            // Ya se confirmó el cumplimiento del periodo actual: no importa la fecha.
            if ($estado === 'VIGENTE') return $mk('VIGENTE', 'Cumplido', 'doc-badge--vig', $dias);
            if ($dias !== null && $dias < 0) return $mk('VENCIDO', 'Vencido', 'doc-badge--venc', $dias);
            if ($estado === 'EN_REVISION')   return $mk('EN_REVISION', 'En revisión', 'doc-badge--rev', $dias);
            if ($dias !== null && $dias <= $diasAlerta) return $mk('POR_VENCER', 'Por vencer', 'doc-badge--pv', $dias);
            return $mk('PENDIENTE', 'Sin registrar', 'doc-badge--pend', $dias);
        }

        // Un documento vencido es vencido aunque siga "en revisión": la fecha manda.
        if ($dias !== null && $dias < 0) {
            return $mk('VENCIDO', 'Vencido', 'doc-badge--venc', $dias);
        }

        if ($estado === 'EN_REVISION') {
            return $mk('EN_REVISION', 'En revisión', 'doc-badge--rev', $dias);
        }

        if ($dias !== null && $dias <= $diasAlerta) {
            return $mk('POR_VENCER', 'Por vencer', 'doc-badge--pv', $dias);
        }

        return $mk('VIGENTE', 'Vigente', 'doc-badge--vig', $dias);
    }

    /**
     * Vencimiento del periodo ACTUAL para un tipo PERIODICO, calculado en vivo (nunca
     * guardado): [inicio, vencimiento] del día/semana/mes en curso, en base a hoy.
     *  - DIARIO:  vence hoy mismo.
     *  - SEMANAL: `diaVenc` es 1=lunes..7=domingo.
     *  - MENSUAL: `diaVenc` es el día del mes (se recorta si el mes es más corto,
     *             ej. 31 en febrero cae al último día de febrero).
     */
    public static function periodoActual(string $periodicidad, ?int $diaVenc, ?DateTime $hoy = null): array
    {
        $hoy = $hoy ?: new DateTime('today');

        if ($periodicidad === 'SEMANAL') {
            $isoHoy = (int)$hoy->format('N'); // 1..7
            $inicio = (clone $hoy)->modify('-' . ($isoHoy - 1) . ' days');
            $dia    = max(1, min(7, $diaVenc ?: 5));
            $venc   = (clone $inicio)->modify('+' . ($dia - 1) . ' days');
            return [$inicio, $venc];
        }

        if ($periodicidad === 'MENSUAL') {
            $inicio = new DateTime($hoy->format('Y-m-01'));
            $dia    = max(1, min((int)$hoy->format('t'), $diaVenc ?: 30));
            $venc   = new DateTime($hoy->format('Y-m-') . str_pad((string)$dia, 2, '0', STR_PAD_LEFT));
            return [$inicio, $venc];
        }

        // DIARIO (o valor desconocido): el periodo es el propio día.
        return [clone $hoy, clone $hoy];
    }

    /** Peso para ordenar: primero lo que arde. */
    public static function prioridadEstado(string $codigo): int
    {
        return [
            'VENCIDO'     => 0,
            'POR_VENCER'  => 1,
            'OBSERVADO'   => 2,
            'PENDIENTE'   => 3,
            'EN_REVISION' => 4,
            'VIGENTE'     => 5,
            'NO_APLICA'   => 6,
        ][$codigo] ?? 9;
    }

    // ─────────────────────────────────────────────────────────────
    //  Catálogo de tipos (ADMIN)
    // ─────────────────────────────────────────────────────────────

    public function getTipos(bool $soloActivos = false): array
    {
        $where = $soloActivos ? 'WHERE t.activo = 1' : '';
        $rows = $this->db->query(
            "SELECT t.*,
                    CONCAT(p.nombres, ' ', COALESCE(p.apellidos, '')) AS creador_nombre,
                    (SELECT COUNT(*) FROM documento_local d WHERE d.tipo_id = t.id) AS total_docs
             FROM documento_tipo t
             LEFT JOIN postulante p ON p.id_postulante = t.creado_por
             {$where}
             ORDER BY t.categoria, t.orden, t.nombre"
        )->fetchAll();

        // Locales asignados cuando el alcance es ESPECIFICOS
        $mapa = [];
        foreach ($this->db->query("SELECT tipo_id, local_id FROM documento_tipo_local")->fetchAll() as $r) {
            $mapa[(int)$r['tipo_id']][] = (int)$r['local_id'];
        }

        // Cantidad requerida por local (solo aplica a tipo_medicion = INVENTARIO)
        $cant = [];
        foreach ($this->db->query("SELECT tipo_id, local_id, cantidad FROM documento_tipo_cantidad")->fetchAll() as $r) {
            $cant[(int)$r['tipo_id']][(int)$r['local_id']] = (int)$r['cantidad'];
        }

        foreach ($rows as &$t) {
            $t['locales']    = $mapa[(int)$t['id']] ?? [];
            $t['cantidades'] = $cant[(int)$t['id']] ?? [];
        }
        return $rows;
    }

    public function getTipo(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM documento_tipo WHERE id = :id");
        $st->execute(['id' => $id]);
        $t = $st->fetch();
        if (!$t) return null;

        $st2 = $this->db->prepare("SELECT local_id FROM documento_tipo_local WHERE tipo_id = :id");
        $st2->execute(['id' => $id]);
        $t['locales'] = array_map('intval', $st2->fetchAll(PDO::FETCH_COLUMN));

        $st3 = $this->db->prepare("SELECT local_id, cantidad FROM documento_tipo_cantidad WHERE tipo_id = :id");
        $st3->execute(['id' => $id]);
        $t['cantidades'] = [];
        foreach ($st3->fetchAll() as $r) $t['cantidades'][(int)$r['local_id']] = (int)$r['cantidad'];

        return $t;
    }

    /** Cuántas unidades se requieren de un tipo INVENTARIO en un local (default 1 si no se configuró). */
    private function cantidadRequerida(array $cantidades, int $localId): int
    {
        return isset($cantidades[$localId]) ? max(0, (int)$cantidades[$localId]) : 1;
    }

    /** Crea o actualiza un tipo. Devuelve el id. */
    public function guardarTipo(array $d, int $userId): int
    {
        $id       = (int)($d['id'] ?? 0);
        $vigencia = $d['vigencia'] ?? 'VENCE';
        $vigencia = in_array($vigencia, ['FIJO', 'VENCE', 'PERIODICO'], true) ? $vigencia : 'VENCE';
        $alcance  = ($d['alcance'] ?? 'TODOS') === 'ESPECIFICOS' ? 'ESPECIFICOS' : 'TODOS';

        // PERIODICO es siempre "un documento" (la unidad no tiene sentido ahí: el libro
        // de recetas no se registra "por 3 unidades", se cumple sí o sí cada periodo).
        $tipoMedicion   = ($vigencia !== 'PERIODICO' && ($d['tipo_medicion'] ?? 'DOCUMENTO') === 'INVENTARIO')
                          ? 'INVENTARIO' : 'DOCUMENTO';
        $unidadEtiqueta = trim($d['unidad_etiqueta'] ?? '') ?: 'unidad';

        $periodicidad = null;
        $diaVenc      = null;
        if ($vigencia === 'PERIODICO') {
            $periodicidad = in_array($d['periodicidad'] ?? '', array_keys(self::PERIODICIDADES), true)
                ? $d['periodicidad'] : 'MENSUAL';
            if ($periodicidad === 'SEMANAL') $diaVenc = max(1, min(7, (int)($d['dia_vencimiento'] ?? 5)));
            if ($periodicidad === 'MENSUAL') $diaVenc = max(1, min(31, (int)($d['dia_vencimiento'] ?? 30)));
        }

        $params = [
            'nombre'      => trim($d['nombre'] ?? ''),
            'categoria'   => $d['categoria'] ?? 'GENERAL',
            'descripcion' => trim($d['descripcion'] ?? '') ?: null,
            'vigencia'    => $vigencia,
            'duracion'    => ($vigencia === 'VENCE' && !empty($d['duracion_meses'])) ? (int)$d['duracion_meses'] : null,
            'periodicidad'=> $periodicidad,
            'dia_venc'    => $diaVenc,
            'alerta'      => max(0, (int)($d['dias_alerta'] ?? 30)),
            'costo'       => ($d['costo_referencial'] ?? '') !== '' ? (float)$d['costo_referencial'] : null,
            'entidad'     => trim($d['entidad'] ?? '') ?: null,
            'firma'       => !empty($d['requiere_firma']) ? 1 : 0,
            'obligatorio' => !empty($d['obligatorio']) ? 1 : 0,
            'alcance'     => $alcance,
            'orden'       => (int)($d['orden'] ?? 0),
            'medicion'    => $tipoMedicion,
            'unidad_etq'  => $unidadEtiqueta,
        ];

        if ($id > 0) {
            $params['id'] = $id;
            $this->db->prepare(
                "UPDATE documento_tipo SET
                    nombre = :nombre, categoria = :categoria, descripcion = :descripcion,
                    vigencia = :vigencia, duracion_meses = :duracion,
                    periodicidad = :periodicidad, dia_vencimiento = :dia_venc, dias_alerta = :alerta,
                    costo_referencial = :costo, entidad = :entidad, requiere_firma = :firma,
                    obligatorio = :obligatorio, alcance = :alcance, orden = :orden,
                    tipo_medicion = :medicion, unidad_etiqueta = :unidad_etq
                 WHERE id = :id"
            )->execute($params);
            $accion = 'TIPO_EDITAR';
        } else {
            $params['creador'] = $userId;
            $this->db->prepare(
                "INSERT INTO documento_tipo
                    (nombre, categoria, descripcion, vigencia, duracion_meses,
                     periodicidad, dia_vencimiento, dias_alerta,
                     costo_referencial, entidad, requiere_firma, obligatorio, alcance, orden,
                     tipo_medicion, unidad_etiqueta, creado_por)
                 VALUES
                    (:nombre, :categoria, :descripcion, :vigencia, :duracion,
                     :periodicidad, :dia_venc, :alerta,
                     :costo, :entidad, :firma, :obligatorio, :alcance, :orden,
                     :medicion, :unidad_etq, :creador)"
            )->execute($params);
            $id = (int)$this->db->lastInsertId();
            $accion = 'TIPO_CREAR';
        }

        // Locales específicos
        $this->db->prepare("DELETE FROM documento_tipo_local WHERE tipo_id = :id")->execute(['id' => $id]);
        if ($alcance === 'ESPECIFICOS' && !empty($d['locales']) && is_array($d['locales'])) {
            $ins = $this->db->prepare("INSERT IGNORE INTO documento_tipo_local (tipo_id, local_id) VALUES (:t, :l)");
            foreach ($d['locales'] as $lid) {
                $ins->execute(['t' => $id, 'l' => (int)$lid]);
            }
        }

        // Cantidad requerida por local: solo tiene sentido para INVENTARIO.
        // Se guarda explícita por local (si falta un local, getChecklist() asume 1 por defecto).
        $this->db->prepare("DELETE FROM documento_tipo_cantidad WHERE tipo_id = :id")->execute(['id' => $id]);
        if ($tipoMedicion === 'INVENTARIO' && !empty($d['cantidades']) && is_array($d['cantidades'])) {
            $insC = $this->db->prepare(
                "INSERT INTO documento_tipo_cantidad (tipo_id, local_id, cantidad) VALUES (:t, :l, :c)"
            );
            foreach ($d['cantidades'] as $lid => $cantidad) {
                $cantidad = (int)$cantidad;
                if ($cantidad < 0) $cantidad = 0;
                $insC->execute(['t' => $id, 'l' => (int)$lid, 'c' => $cantidad]);
            }
        }

        $this->log([
            'tipo_id' => $id, 'accion' => $accion, 'postulante_id' => $userId,
            'detalle' => 'Tipo de documento: ' . $params['nombre'],
        ]);

        return $id;
    }

    public function toggleTipo(int $id, int $userId): bool
    {
        $st = $this->db->prepare("SELECT nombre, activo FROM documento_tipo WHERE id = :id");
        $st->execute(['id' => $id]);
        $t = $st->fetch();
        if (!$t) return false;

        $nuevo = (int)$t['activo'] === 1 ? 0 : 1;
        $this->db->prepare("UPDATE documento_tipo SET activo = :a WHERE id = :id")
                 ->execute(['a' => $nuevo, 'id' => $id]);

        $this->log([
            'tipo_id' => $id, 'accion' => $nuevo ? 'TIPO_ACTIVAR' : 'TIPO_DESACTIVAR',
            'postulante_id' => $userId, 'detalle' => $t['nombre'],
        ]);
        return true;
    }

    /** Solo se puede borrar de verdad si nadie lo usó todavía. */
    public function eliminarTipo(int $id, int $userId)
    {
        $st = $this->db->prepare("SELECT COUNT(*) FROM documento_local WHERE tipo_id = :id");
        $st->execute(['id' => $id]);
        if ((int)$st->fetchColumn() > 0) {
            return 'Este tipo ya tiene documentos registrados. Desactívalo en lugar de eliminarlo.';
        }

        $nom = $this->db->prepare("SELECT nombre FROM documento_tipo WHERE id = :id");
        $nom->execute(['id' => $id]);
        $nombre = $nom->fetchColumn();
        if ($nombre === false) return 'Tipo no encontrado.';

        $this->db->prepare("DELETE FROM documento_tipo WHERE id = :id")->execute(['id' => $id]);
        $this->log(['accion' => 'TIPO_ELIMINAR', 'postulante_id' => $userId, 'detalle' => (string)$nombre]);
        return true;
    }

    // ─────────────────────────────────────────────────────────────
    //  Locales y checklist
    // ─────────────────────────────────────────────────────────────

    public function getLocales(bool $soloActivos = true): array
    {
        $where = $soloActivos ? 'WHERE activo = 1' : '';
        return $this->db->query(
            "SELECT id_local, descripcion, direccion, activo FROM local {$where} ORDER BY descripcion"
        )->fetchAll();
    }

    public function getLocal(int $id): ?array
    {
        $st = $this->db->prepare("SELECT id_local, descripcion, direccion, activo FROM local WHERE id_local = :id");
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }

    /**
     * Checklist completo: cada tipo activo cruzado con cada local al que aplica.
     *
     * Para un tipo DOCUMENTO sale una fila (su documento vigente, o PENDIENTE si no
     * hay ninguno). Para un tipo INVENTARIO sale una fila POR UNIDAD requerida
     * (`documento_tipo_cantidad`, default 1): si el local necesita 4 termohigrómetros
     * y solo hay 3 registrados, la 4.ª aparece como PENDIENTE. Si por algún motivo
     * hay más unidades registradas que las requeridas, también se muestran (no se
     * pierde información aunque el admin haya bajado la cantidad después).
     */
    public function getChecklist(?int $localId = null): array
    {
        $params = [];
        $filtro = '';
        if ($localId !== null) {
            $filtro = ' AND l.id_local = :lid';
            $params['lid'] = $localId;
        }

        // 1) Pares (tipo, local) aplicables — sin datos de documento todavía.
        $stPares = $this->db->prepare(
            "SELECT l.id_local, l.descripcion AS local_nombre,
                    t.id AS tipo_id, t.nombre AS tipo_nombre, t.categoria, t.descripcion AS tipo_desc,
                    t.vigencia, t.duracion_meses, t.dias_alerta, t.costo_referencial,
                    t.entidad AS tipo_entidad, t.requiere_firma, t.obligatorio, t.orden,
                    t.tipo_medicion, t.unidad_etiqueta, t.periodicidad, t.dia_vencimiento
             FROM local l
             CROSS JOIN documento_tipo t
             LEFT JOIN documento_tipo_local tl ON tl.tipo_id = t.id AND tl.local_id = l.id_local
             WHERE l.activo = 1 AND t.activo = 1
               AND (t.alcance = 'TODOS' OR tl.local_id IS NOT NULL)
               {$filtro}
             ORDER BY l.descripcion, t.categoria, t.orden, t.nombre"
        );
        $stPares->execute($params);
        $pares = $stPares->fetchAll();
        if (empty($pares)) return [];

        // 2) Cantidad requerida por (tipo, local) — solo relevante para INVENTARIO.
        $cantidades = [];
        foreach ($this->db->query("SELECT tipo_id, local_id, cantidad FROM documento_tipo_cantidad")->fetchAll() as $r) {
            $cantidades[(int)$r['tipo_id']][(int)$r['local_id']] = (int)$r['cantidad'];
        }

        // 3) Documentos vigentes (es_actual = 1) de esos mismos pares, con su unidad.
        $stDocs = $this->db->prepare(
            "SELECT d.id AS doc_id, d.tipo_id, d.local_id, d.unidad, d.estado, d.numero_documento,
                    d.entidad, d.fecha_emision, d.fecha_inicio, d.fecha_fin, d.costo, d.observacion,
                    d.archivo, d.archivo_nombre, d.revisado_en, d.firmado_en, d.actualizado_en,
                    CONCAT(pr.nombres, ' ', COALESCE(pr.apellidos, '')) AS registrado_nombre,
                    CONCAT(pv.nombres, ' ', COALESCE(pv.apellidos, '')) AS revisado_nombre,
                    CONCAT(pf.nombres, ' ', COALESCE(pf.apellidos, '')) AS firmado_nombre,
                    (SELECT COUNT(*) FROM documento_local h
                      WHERE h.tipo_id = d.tipo_id AND h.local_id = d.local_id AND h.unidad = d.unidad) AS versiones
             FROM documento_local d
             INNER JOIN documento_tipo t ON t.id = d.tipo_id AND t.activo = 1
             INNER JOIN local l          ON l.id_local = d.local_id AND l.activo = 1
             LEFT JOIN documento_tipo_local tl ON tl.tipo_id = t.id AND tl.local_id = l.id_local
             LEFT JOIN postulante pr ON pr.id_postulante = d.registrado_por
             LEFT JOIN postulante pv ON pv.id_postulante = d.revisado_por
             LEFT JOIN postulante pf ON pf.id_postulante = d.firmado_por
             WHERE d.es_actual = 1
               AND (t.alcance = 'TODOS' OR tl.local_id IS NOT NULL)
               {$filtro}"
        );
        $stDocs->execute($params);
        $docsPorSlot = [];
        foreach ($stDocs->fetchAll() as $doc) {
            $docsPorSlot[$doc['tipo_id'] . '-' . $doc['local_id'] . '-' . $doc['unidad']] = $doc;
        }

        // 4) Armar las filas finales: un slot por unidad requerida (o 1 slot si es DOCUMENTO),
        //    más cualquier unidad extra que exista aunque supere la cantidad requerida.
        $rows = [];
        foreach ($pares as $par) {
            $tipoId  = (int)$par['tipo_id'];
            $localId2 = (int)$par['id_local'];
            $esInventario = $par['tipo_medicion'] === 'INVENTARIO';

            $requerida = $esInventario ? $this->cantidadRequerida($cantidades[$tipoId] ?? [], $localId2) : 1;

            $maxUnidadExistente = 0;
            foreach ($docsPorSlot as $key => $doc) {
                if ((int)$doc['tipo_id'] === $tipoId && (int)$doc['local_id'] === $localId2) {
                    $maxUnidadExistente = max($maxUnidadExistente, (int)$doc['unidad']);
                }
            }
            $totalSlots = max($requerida, $maxUnidadExistente, $esInventario ? 0 : 1);

            $esPeriodico = $par['vigencia'] === 'PERIODICO';
            [$inicioPeriodo, $vencPeriodo] = $esPeriodico
                ? self::periodoActual($par['periodicidad'] ?? 'MENSUAL', $par['dia_vencimiento'] ?? null)
                : [null, null];

            for ($u = 1; $u <= $totalSlots; $u++) {
                $doc = $docsPorSlot[$tipoId . '-' . $localId2 . '-' . $u] ?? null;
                $versionesReales = $doc['versiones'] ?? 0;

                // Un doc de un periodo YA PASADO no cuenta como "cumplimiento vigente":
                // se guarda como referencia informativa y el slot vuelve a verse pendiente.
                $ultimoCumplido = null;
                if ($esPeriodico && $doc && (empty($doc['fecha_inicio']) || $doc['fecha_inicio'] < $inicioPeriodo->format('Y-m-d'))) {
                    $ultimoCumplido = ['fecha' => $doc['fecha_inicio'], 'quien' => $doc['firmado_nombre'] ?: $doc['registrado_nombre']];
                    $doc = null;
                }

                $row = $par;
                $row['id_local']          = $localId2;
                $row['unidad']            = $u;
                $row['cantidad_requerida'] = $requerida;
                $row['doc_id']            = $doc['doc_id']            ?? null;
                $row['estado']            = $doc['estado']            ?? null;
                $row['numero_documento']  = $doc['numero_documento']  ?? null;
                $row['entidad']           = $doc['entidad']           ?? null;
                $row['fecha_emision']     = $doc['fecha_emision']     ?? null;
                $row['fecha_inicio']      = $doc['fecha_inicio']      ?? null;
                $row['fecha_fin']         = $esPeriodico ? $vencPeriodo->format('Y-m-d') : ($doc['fecha_fin'] ?? null);
                $row['costo']             = $doc['costo']             ?? null;
                $row['observacion']       = $doc['observacion']       ?? null;
                $row['archivo']           = $doc['archivo']           ?? null;
                $row['archivo_nombre']    = $doc['archivo_nombre']    ?? null;
                $row['revisado_en']       = $doc['revisado_en']       ?? null;
                $row['firmado_en']        = $doc['firmado_en']        ?? null;
                $row['actualizado_en']    = $doc['actualizado_en']    ?? null;
                $row['registrado_nombre'] = $doc['registrado_nombre'] ?? null;
                $row['revisado_nombre']   = $doc['revisado_nombre']   ?? null;
                $row['firmado_nombre']    = $doc['firmado_nombre']    ?? null;
                $row['versiones']         = $versionesReales;
                $row['periodo_inicio']    = $esPeriodico ? $inicioPeriodo->format('Y-m-d') : null;
                $row['ultimo_cumplido']   = $ultimoCumplido;
                $row['estado_calc']       = self::calcularEstado($row, (int)$row['dias_alerta']);
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /** Resumen por local (contadores + gasto del año) para la portada del módulo. */
    public function getResumenLocales(?int $anio = null): array
    {
        $anio  = $anio ?: (int)date('Y');
        $rows  = $this->getChecklist();
        $gasto = $this->getGastoPorLocal($anio);

        $out = [];
        foreach ($this->getLocales() as $l) {
            $out[(int)$l['id_local']] = [
                'id_local'    => (int)$l['id_local'],
                'nombre'      => $l['descripcion'],
                'direccion'   => $l['direccion'],
                'total'       => 0,
                'VIGENTE'     => 0, 'POR_VENCER' => 0, 'VENCIDO' => 0,
                'EN_REVISION' => 0, 'PENDIENTE'  => 0, 'OBSERVADO' => 0, 'NO_APLICA' => 0,
                'gasto_anio'  => (float)($gasto[(int)$l['id_local']] ?? 0),
                'proximo'     => null,
            ];
        }

        foreach ($rows as $r) {
            $lid = (int)$r['id_local'];
            if (!isset($out[$lid])) continue;
            $cod = $r['estado_calc']['codigo'];
            $out[$lid]['total']++;
            $out[$lid][$cod] = ($out[$lid][$cod] ?? 0) + 1;

            // Vencimiento más cercano que todavía no vence
            $dias = $r['estado_calc']['dias'];
            if ($dias !== null && $dias >= 0 && in_array($cod, ['VIGENTE', 'POR_VENCER'], true)) {
                $act = $out[$lid]['proximo'];
                if ($act === null || $dias < $act['dias']) {
                    $out[$lid]['proximo'] = ['dias' => $dias, 'nombre' => $r['tipo_nombre'], 'fecha' => $r['fecha_fin']];
                }
            }
        }

        foreach ($out as &$o) {
            $base = max(1, $o['total'] - $o['NO_APLICA']);
            $o['cumplimiento'] = (int)round((($o['VIGENTE'] + $o['POR_VENCER']) / $base) * 100);
        }
        return array_values($out);
    }

    /**
     * Resumen liviano para la campanita 🔔 del header (admin y staff).
     * No filtra por ventana de días: cuenta TODO lo vencido/por vencer ahora mismo.
     */
    public function getAlertaResumen(): array
    {
        $vencidos = 0;
        $porVencer = 0;
        foreach ($this->getChecklist() as $r) {
            $cod = $r['estado_calc']['codigo'];
            if ($cod === 'VENCIDO') $vencidos++;
            elseif ($cod === 'POR_VENCER') $porVencer++;
        }
        return ['vencidos' => $vencidos, 'por_vencer' => $porVencer, 'total' => $vencidos + $porVencer];
    }

    /** Documentos que vencen dentro de N días (o ya vencidos), ordenados por urgencia. */
    public function getProximosVencimientos(int $dias = 60): array
    {
        $out = [];
        foreach ($this->getChecklist() as $r) {
            $cod = $r['estado_calc']['codigo'];
            if (!in_array($cod, ['VENCIDO', 'POR_VENCER'], true)) continue;
            if ($r['estado_calc']['dias'] !== null && $r['estado_calc']['dias'] > $dias) continue;
            $out[] = $r;
        }
        usort($out, fn($a, $b) => ($a['estado_calc']['dias'] ?? 9999) <=> ($b['estado_calc']['dias'] ?? 9999));
        return $out;
    }

    // ─────────────────────────────────────────────────────────────
    //  Documentos
    // ─────────────────────────────────────────────────────────────

    public function getDocumento(int $id): ?array
    {
        $st = $this->db->prepare(
            "SELECT d.*, t.nombre AS tipo_nombre, t.vigencia, t.dias_alerta, t.requiere_firma,
                    t.categoria, l.descripcion AS local_nombre,
                    CONCAT(pr.nombres, ' ', COALESCE(pr.apellidos, '')) AS registrado_nombre,
                    CONCAT(pv.nombres, ' ', COALESCE(pv.apellidos, '')) AS revisado_nombre,
                    CONCAT(pf.nombres, ' ', COALESCE(pf.apellidos, '')) AS firmado_nombre
             FROM documento_local d
             INNER JOIN documento_tipo t ON t.id = d.tipo_id
             INNER JOIN local l          ON l.id_local = d.local_id
             LEFT JOIN postulante pr ON pr.id_postulante = d.registrado_por
             LEFT JOIN postulante pv ON pv.id_postulante = d.revisado_por
             LEFT JOIN postulante pf ON pf.id_postulante = d.firmado_por
             WHERE d.id = :id"
        );
        $st->execute(['id' => $id]);
        $d = $st->fetch();
        if (!$d) return null;
        $d['doc_id'] = $d['id'];
        $d['estado_calc'] = self::calcularEstado($d, (int)$d['dias_alerta']);
        return $d;
    }

    /**
     * Crea o actualiza el documento de un local.
     * Con `renovar = true` archiva el vigente (es_actual = 0) y abre un periodo nuevo,
     * que es exactamente lo que pasa cuando se renueva un certificado.
     */
    public function guardarDocumento(array $d, int $userId): array
    {
        $docId   = (int)($d['doc_id'] ?? 0);
        $renovar = !empty($d['renovar']);
        $tipoId  = (int)($d['tipo_id'] ?? 0);
        $localId = (int)($d['local_id'] ?? 0);
        // Solo importa para altas/renovaciones: qué unidad del inventario estamos llenando.
        // En documentos únicos siempre es 1; el frontend ya lo manda así.
        $unidad  = max(1, (int)($d['unidad'] ?? 1));

        $tipo = $this->getTipo($tipoId);
        if (!$tipo)                     return ['ok' => false, 'msg' => 'Tipo de documento no válido.'];
        if (!$this->getLocal($localId)) return ['ok' => false, 'msg' => 'Local no válido.'];

        $esFijo      = $tipo['vigencia'] === 'FIJO';
        $esPeriodico = $tipo['vigencia'] === 'PERIODICO';
        $fechaIni = ($d['fecha_inicio']  ?? '') !== '' ? $d['fecha_inicio']  : null;
        $fechaFin = ($esFijo || $esPeriodico) ? null : ((($d['fecha_fin'] ?? '') !== '') ? $d['fecha_fin'] : null);
        $fechaEmi = ($d['fecha_emision'] ?? '') !== '' ? $d['fecha_emision'] : null;

        // Un periódico no se "vence" con una fecha tecleada: el cumplimiento se ancla
        // siempre al día de HOY, que es cuando efectivamente se está confirmando.
        if ($esPeriodico) $fechaIni = date('Y-m-d');

        $estado = in_array($d['estado'] ?? '', self::ESTADOS, true) ? $d['estado'] : 'EN_REVISION';

        if (!$esFijo && !$esPeriodico && $fechaFin === null && $estado !== 'NO_APLICA') {
            return ['ok' => false, 'msg' => 'Este documento caduca: indica la fecha de vencimiento.'];
        }
        if ($fechaIni && $fechaFin && $fechaFin < $fechaIni) {
            return ['ok' => false, 'msg' => 'La fecha de vencimiento no puede ser anterior a la de inicio.'];
        }

        $campos = [
            'numero'  => trim($d['numero_documento'] ?? '') ?: null,
            'entidad' => trim($d['entidad'] ?? '') ?: null,
            'emision' => $fechaEmi,
            'inicio'  => $fechaIni,
            'fin'     => $fechaFin,
            'costo'   => (($d['costo'] ?? '') !== '' && $d['costo'] !== null) ? (float)$d['costo'] : null,
            'obs'     => trim($d['observacion'] ?? '') ?: null,
        ];

        $this->db->beginTransaction();
        try {
            if ($docId > 0 && !$renovar) {
                $prev = $this->getDocumento($docId);
                if (!$prev) { $this->db->rollBack(); return ['ok' => false, 'msg' => 'Documento no encontrado.']; }

                // Editar un periódico no debe reubicarlo en otro periodo: se conserva la
                // fecha con la que se ancló la primera vez que se confirmó este periodo.
                if ($esPeriodico) { $fechaIni = $prev['fecha_inicio']; $campos['inicio'] = $fechaIni; }

                // Si cambian las fechas de vigencia, la firma anterior deja de ser válida.
                $tocaVigencia = ($prev['fecha_fin'] !== $fechaFin) || ($prev['fecha_inicio'] !== $fechaIni);
                $limpiarFirma = $tocaVigencia && $prev['estado'] === 'VIGENTE';
                $estadoFinal  = $limpiarFirma ? 'EN_REVISION' : $estado;

                $sql = "UPDATE documento_local SET
                            numero_documento = :numero, entidad = :entidad, fecha_emision = :emision,
                            fecha_inicio = :inicio, fecha_fin = :fin, costo = :costo,
                            observacion = :obs, estado = :estado";
                if ($limpiarFirma) $sql .= ", firmado_por = NULL, firmado_en = NULL";
                $extra = [];
                // Quien lo deja "en revisión" es quien queda como responsable de esa revisión
                if ($estadoFinal === 'EN_REVISION') {
                    $sql .= ", revisado_por = :revisor, revisado_en = NOW()";
                    $extra['revisor'] = $userId;
                }
                if ($estadoFinal === 'VIGENTE' && empty($prev['firmado_por'])) {
                    $sql .= ", firmado_por = :firmante, firmado_en = NOW()";
                    $extra['firmante'] = $userId;
                }
                $sql .= " WHERE id = :id";

                $this->db->prepare($sql)->execute($campos + $extra + ['estado' => $estadoFinal, 'id' => $docId]);

                $this->log([
                    'documento_id' => $docId, 'tipo_id' => $tipoId, 'local_id' => $localId,
                    'accion' => 'ACTUALIZAR', 'postulante_id' => $userId,
                    'estado_anterior' => $prev['estado'], 'estado_nuevo' => $estadoFinal,
                    'detalle' => $limpiarFirma
                        ? 'Se editaron las fechas de vigencia; la firma anterior quedó sin efecto.'
                        : 'Datos del documento actualizados.',
                ]);

                $this->db->commit();
                return ['ok' => true, 'id' => $docId, 'msg' => 'Documento actualizado.'];
            }

            // Alta o renovación: archivamos el vigente de ESA unidad puntual (tipo+local+unidad).
            // Para un documento único, unidad siempre es 1, así que se comporta igual que antes.
            $this->db->prepare(
                "UPDATE documento_local SET es_actual = 0
                 WHERE tipo_id = :t AND local_id = :l AND unidad = :u AND es_actual = 1"
            )->execute(['t' => $tipoId, 'l' => $localId, 'u' => $unidad]);

            // Quien registra queda como revisor; si además lo deja vigente, queda como firmante.
            // Cada placeholder va una sola vez: con EMULATE_PREPARES=false, PDO no acepta
            // repetir un parámetro con nombre.
            $cols  = 'revisado_por, revisado_en';
            $vals  = ':revisor, NOW()';
            $extra = ['revisor' => $userId];
            if ($estado === 'VIGENTE') {
                $cols .= ', firmado_por, firmado_en';
                $vals .= ', :firmante, NOW()';
                $extra['firmante'] = $userId;
            }

            $this->db->prepare(
                "INSERT INTO documento_local
                    (tipo_id, local_id, unidad, estado, numero_documento, entidad, fecha_emision,
                     fecha_inicio, fecha_fin, costo, observacion, es_actual, registrado_por, {$cols})
                 VALUES
                    (:tipo, :local, :unidad, :estado, :numero, :entidad, :emision,
                     :inicio, :fin, :costo, :obs, 1, :user, {$vals})"
            )->execute($campos + $extra + [
                'tipo' => $tipoId, 'local' => $localId, 'unidad' => $unidad, 'estado' => $estado, 'user' => $userId,
            ]);

            $nuevoId = (int)$this->db->lastInsertId();

            $this->log([
                'documento_id' => $nuevoId, 'tipo_id' => $tipoId, 'local_id' => $localId,
                'accion' => $renovar ? 'RENOVAR' : 'CREAR', 'postulante_id' => $userId,
                'estado_nuevo' => $estado,
                'detalle' => $renovar ? 'Renovación registrada (el periodo anterior pasó al historial).'
                                      : 'Documento registrado por primera vez.',
            ]);

            $this->db->commit();
            return ['ok' => true, 'id' => $nuevoId, 'msg' => $renovar ? 'Renovación registrada.' : 'Documento registrado.'];
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('DocumentoLocalRepository::guardarDocumento -> ' . $e->getMessage());
            return ['ok' => false, 'msg' => 'No se pudo guardar el documento.'];
        }
    }

    /** Cambia el estado del flujo: enviar a revisión, firmar, observar, no aplica. */
    public function cambiarEstado(int $docId, string $nuevo, int $userId, ?string $obs = null): array
    {
        if (!in_array($nuevo, self::ESTADOS, true)) {
            return ['ok' => false, 'msg' => 'Estado no válido.'];
        }

        $doc = $this->getDocumento($docId);
        if (!$doc) return ['ok' => false, 'msg' => 'Documento no encontrado.'];

        $anterior = $doc['estado'];
        $sets     = ['estado = :estado'];
        $params   = ['estado' => $nuevo, 'id' => $docId];
        $accion   = 'CAMBIO_ESTADO';

        if ($nuevo === 'VIGENTE') {
            if (empty($doc['fecha_fin']) && $doc['vigencia'] === 'VENCE') {
                return ['ok' => false, 'msg' => 'Falta la fecha de vencimiento: no se puede firmar.'];
            }
            $sets[] = 'firmado_por = :user';
            $sets[] = 'firmado_en = NOW()';
            $params['user'] = $userId;
            $accion = 'FIRMAR';
        } else {
            // Cualquier salida de VIGENTE anula la firma
            $sets[] = 'firmado_por = NULL';
            $sets[] = 'firmado_en = NULL';
            if ($nuevo === 'EN_REVISION') {
                $sets[] = 'revisado_por = :user';
                $sets[] = 'revisado_en = NOW()';
                $params['user'] = $userId;
                $accion = 'ENVIAR_REVISION';
            }
            if ($nuevo === 'OBSERVADO') $accion = 'OBSERVAR';
        }

        if ($obs !== null && trim($obs) !== '') {
            $sets[] = 'observacion = :obs';
            $params['obs'] = trim($obs);
        }

        $this->db->prepare('UPDATE documento_local SET ' . implode(', ', $sets) . ' WHERE id = :id')
                 ->execute($params);

        $this->log([
            'documento_id' => $docId, 'tipo_id' => (int)$doc['tipo_id'], 'local_id' => (int)$doc['local_id'],
            'accion' => $accion, 'postulante_id' => $userId,
            'estado_anterior' => $anterior, 'estado_nuevo' => $nuevo,
            'detalle' => ($obs !== null && trim($obs) !== '') ? trim($obs) : null,
        ]);

        return ['ok' => true, 'msg' => $accion === 'FIRMAR' ? 'Documento firmado.' : 'Estado actualizado.'];
    }

    public function guardarArchivo(int $docId, string $ruta, string $nombre, int $userId): void
    {
        $doc = $this->getDocumento($docId);
        $this->db->prepare(
            "UPDATE documento_local SET archivo = :a, archivo_nombre = :n WHERE id = :id"
        )->execute(['a' => $ruta ?: null, 'n' => $nombre ?: null, 'id' => $docId]);

        if ($ruta === '') return; // quitar adjunto: el log lo escribe el controlador

        $this->log([
            'documento_id' => $docId,
            'tipo_id'  => $doc ? (int)$doc['tipo_id']  : null,
            'local_id' => $doc ? (int)$doc['local_id'] : null,
            'accion' => 'ADJUNTAR', 'postulante_id' => $userId, 'detalle' => $nombre,
        ]);
    }

    public function eliminarDocumento(int $docId, int $userId): array
    {
        $doc = $this->getDocumento($docId);
        if (!$doc) return ['ok' => false, 'msg' => 'Documento no encontrado.'];

        $this->log([
            'tipo_id' => (int)$doc['tipo_id'], 'local_id' => (int)$doc['local_id'],
            'accion' => 'ELIMINAR', 'postulante_id' => $userId,
            'estado_anterior' => $doc['estado'],
            'detalle' => $doc['tipo_nombre'] . ' — ' . $doc['local_nombre'],
        ]);

        $this->db->prepare("DELETE FROM documento_local WHERE id = :id")->execute(['id' => $docId]);

        // Si borramos el vigente, el periodo anterior de ESA MISMA unidad vuelve a ser el actual
        if ((int)$doc['es_actual'] === 1) {
            $this->db->prepare(
                "UPDATE documento_local SET es_actual = 1
                 WHERE tipo_id = :t AND local_id = :l AND unidad = :u
                 ORDER BY COALESCE(fecha_inicio, creado_en) DESC LIMIT 1"
            )->execute(['t' => (int)$doc['tipo_id'], 'l' => (int)$doc['local_id'], 'u' => (int)$doc['unidad']]);
        }

        return ['ok' => true, 'msg' => 'Documento eliminado.', 'archivo' => $doc['archivo']];
    }

    /** Historial de periodos (renovaciones) de una unidad puntual (tipo + local + unidad). */
    public function getHistorialDocumento(int $tipoId, int $localId, int $unidad = 1): array
    {
        $st = $this->db->prepare(
            "SELECT d.*, t.dias_alerta,
                    CONCAT(pr.nombres, ' ', COALESCE(pr.apellidos, '')) AS registrado_nombre,
                    CONCAT(pf.nombres, ' ', COALESCE(pf.apellidos, '')) AS firmado_nombre
             FROM documento_local d
             INNER JOIN documento_tipo t ON t.id = d.tipo_id
             LEFT JOIN postulante pr ON pr.id_postulante = d.registrado_por
             LEFT JOIN postulante pf ON pf.id_postulante = d.firmado_por
             WHERE d.tipo_id = :t AND d.local_id = :l AND d.unidad = :u
             ORDER BY d.es_actual DESC, COALESCE(d.fecha_inicio, d.creado_en) DESC"
        );
        $st->execute(['t' => $tipoId, 'l' => $localId, 'u' => $unidad]);
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['doc_id'] = $r['id'];
            $r['estado_calc'] = self::calcularEstado($r, (int)$r['dias_alerta']);
        }
        return $rows;
    }

    // ─────────────────────────────────────────────────────────────
    //  Presupuesto
    // ─────────────────────────────────────────────────────────────

    /** Gasto total por local en un año (según fecha de inicio/emisión del documento). */
    public function getGastoPorLocal(int $anio): array
    {
        $st = $this->db->prepare(
            "SELECT local_id, SUM(costo) AS total
             FROM documento_local
             WHERE costo IS NOT NULL
               AND YEAR(COALESCE(fecha_inicio, fecha_emision, creado_en)) = :anio
             GROUP BY local_id"
        );
        $st->execute(['anio' => $anio]);
        $out = [];
        foreach ($st->fetchAll() as $r) $out[(int)$r['local_id']] = (float)$r['total'];
        return $out;
    }

    /** Detalle de gasto del año: filas + totales por categoría y por local. */
    public function getPresupuesto(int $anio, ?int $localId = null): array
    {
        $params = ['anio' => $anio];
        $filtro = '';
        if ($localId !== null) { $filtro = ' AND d.local_id = :lid'; $params['lid'] = $localId; }

        $st = $this->db->prepare(
            "SELECT d.id, d.costo, d.fecha_inicio, d.fecha_emision, d.creado_en, d.numero_documento,
                    t.nombre AS tipo_nombre, t.categoria, t.costo_referencial,
                    l.id_local, l.descripcion AS local_nombre
             FROM documento_local d
             INNER JOIN documento_tipo t ON t.id = d.tipo_id
             INNER JOIN local l          ON l.id_local = d.local_id
             WHERE d.costo IS NOT NULL
               AND YEAR(COALESCE(d.fecha_inicio, d.fecha_emision, d.creado_en)) = :anio
               {$filtro}
             ORDER BY l.descripcion, t.categoria, t.nombre"
        );
        $st->execute($params);
        $filas = $st->fetchAll();

        $porCategoria = [];
        $porLocal     = [];
        $total        = 0.0;
        foreach ($filas as $f) {
            $c = $f['categoria'];
            $porCategoria[$c] = ($porCategoria[$c] ?? 0) + (float)$f['costo'];
            $porLocal[(int)$f['id_local']] = ($porLocal[(int)$f['id_local']] ?? 0) + (float)$f['costo'];
            $total += (float)$f['costo'];
        }
        arsort($porCategoria);

        return ['filas' => $filas, 'por_categoria' => $porCategoria, 'por_local' => $porLocal, 'total' => $total];
    }

    /** Años con movimiento, para el selector del reporte. */
    public function getAniosConGasto(): array
    {
        $rows = $this->db->query(
            "SELECT DISTINCT YEAR(COALESCE(fecha_inicio, fecha_emision, creado_en)) AS anio
             FROM documento_local WHERE costo IS NOT NULL ORDER BY anio DESC"
        )->fetchAll(PDO::FETCH_COLUMN);
        $anios = array_map('intval', $rows);
        if (!in_array((int)date('Y'), $anios, true)) array_unshift($anios, (int)date('Y'));
        return $anios;
    }

    // ─────────────────────────────────────────────────────────────
    //  Bitácora
    // ─────────────────────────────────────────────────────────────

    public function log(array $d): void
    {
        try {
            $this->db->prepare(
                "INSERT INTO documento_local_log
                    (documento_id, tipo_id, local_id, accion, estado_anterior, estado_nuevo, detalle, postulante_id)
                 VALUES (:doc, :tipo, :local, :accion, :ant, :nue, :detalle, :user)"
            )->execute([
                'doc'     => $d['documento_id']    ?? null,
                'tipo'    => $d['tipo_id']         ?? null,
                'local'   => $d['local_id']        ?? null,
                'accion'  => $d['accion'],
                'ant'     => $d['estado_anterior'] ?? null,
                'nue'     => $d['estado_nuevo']    ?? null,
                'detalle' => $d['detalle']         ?? null,
                'user'    => $d['postulante_id']   ?? null,
            ]);
        } catch (Throwable $e) {
            // La bitácora nunca debe tumbar la operación principal
            error_log('DocumentoLocalRepository::log -> ' . $e->getMessage());
        }
    }

    public function getLogs(array $f = [], int $limit = 200): array
    {
        $where  = [];
        $params = [];

        if (!empty($f['local_id'])) { $where[] = 'g.local_id = :lid';     $params['lid'] = (int)$f['local_id']; }
        if (!empty($f['tipo_id']))  { $where[] = 'g.tipo_id = :tid';      $params['tid'] = (int)$f['tipo_id']; }
        if (!empty($f['doc_id']))   { $where[] = 'g.documento_id = :did'; $params['did'] = (int)$f['doc_id']; }
        if (!empty($f['accion']))   { $where[] = 'g.accion = :acc';       $params['acc'] = $f['accion']; }
        if (!empty($f['desde']))    { $where[] = 'DATE(g.creado_en) >= :desde'; $params['desde'] = $f['desde']; }
        if (!empty($f['hasta']))    { $where[] = 'DATE(g.creado_en) <= :hasta'; $params['hasta'] = $f['hasta']; }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $limit    = max(1, min(1000, $limit));

        $st = $this->db->prepare(
            "SELECT g.*, t.nombre AS tipo_nombre, l.descripcion AS local_nombre,
                    CONCAT(p.nombres, ' ', COALESCE(p.apellidos, '')) AS usuario_nombre,
                    r.descripcion AS usuario_rol
             FROM documento_local_log g
             LEFT JOIN documento_tipo t ON t.id = g.tipo_id
             LEFT JOIN local l          ON l.id_local = g.local_id
             LEFT JOIN postulante p     ON p.id_postulante = g.postulante_id
             LEFT JOIN usuario u        ON u.postulante_id = g.postulante_id
             LEFT JOIN rol r            ON r.id_rol = u.rol_id
             {$sqlWhere}
             ORDER BY g.creado_en DESC, g.id DESC
             LIMIT {$limit}"
        );
        $st->execute($params);
        return $st->fetchAll();
    }
}
