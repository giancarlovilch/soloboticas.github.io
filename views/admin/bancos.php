<?php
$db   = Database::getConnection();
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$f2   = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

// ── Totales por banco ──────────────────────────────────
$totales = $db->query("
    SELECT banco,
           SUM(monto)                          AS total,
           SUM(IF(confirmado=1, monto, 0))     AS confirmado,
           SUM(IF(confirmado=0, monto, 0))     AS pendiente,
           COUNT(*)                            AS registros
    FROM deposito_kgyr
    GROUP BY banco
")->fetchAll();

$resumen = ['BCP' => ['total'=>0,'confirmado'=>0,'pendiente'=>0,'registros'=>0],
            'BBVA'=> ['total'=>0,'confirmado'=>0,'pendiente'=>0,'registros'=>0]];
foreach ($totales as $t) $resumen[$t['banco']] = $t;

// ── Lista de depósitos ─────────────────────────────────
$filtBanco = $_GET['banco'] ?? '';
$filtConf  = $_GET['conf']  ?? '';
$where = '1=1';
$params = [];
if ($filtBanco) { $where .= ' AND d.banco = :banco'; $params['banco'] = $filtBanco; }
if ($filtConf !== '')  { $where .= ' AND d.confirmado = :conf'; $params['conf'] = (int)$filtConf; }

$stmt = $db->prepare("
    SELECT d.*,
           p.nombres AS registrado_por,
           c.descripcion AS caja_desc,
           sc.fecha_operacion
    FROM deposito_kgyr d
    LEFT JOIN postulante p  ON p.id_postulante = d.registrado_por_id
    LEFT JOIN sesion_caja sc ON sc.id_sesion   = d.sesion_id
    LEFT JOIN caja c         ON c.id_caja      = sc.caja_id
    LEFT JOIN local l        ON l.id_local     = c.local_id
    WHERE {$where}
    ORDER BY d.registrado_en DESC
    LIMIT 200
");
$stmt->execute($params);
$depositos = $stmt->fetchAll();
?>

<style>
.bancos-wrap { max-width:1100px;margin:0 auto;padding:1.25rem 1rem 3rem;font-family:'Inter',sans-serif; }
.bancos-cards { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem; }
@media(max-width:600px){ .bancos-cards { grid-template-columns:1fr; } }
.banco-card { background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:1.1rem 1.25rem;
              box-shadow:0 1px 3px rgba(0,0,0,.05); }
.banco-card__name { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.25rem; }
.banco-card__total { font-size:2rem;font-weight:800;letter-spacing:-.03em;color:#1e293b;line-height:1; }
.banco-card__row  { display:flex;justify-content:space-between;font-size:.78rem;color:#64748b;margin-top:.5rem; }
.banco-card__row strong { color:#1e293b; }

.dep-table { width:100%;border-collapse:collapse;font-size:.82rem; }
.dep-table th { text-align:left;padding:.5rem .75rem;font-size:.68rem;font-weight:700;
                text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;
                border-bottom:2px solid #f1f5f9;white-space:nowrap; }
.dep-table td { padding:.55rem .75rem;border-bottom:1px solid #f8fafc;vertical-align:middle; }
.dep-table tr:last-child td { border-bottom:none; }
.dep-table tr:hover td { background:#f8fafc; }

.badge { display:inline-block;padding:.2rem .55rem;border-radius:5px;font-size:.68rem;font-weight:700; }
.badge-bcp  { background:#dbeafe;color:#1e40af; }
.badge-bbva { background:#fef3c7;color:#92400e; }
.badge-ok   { background:#d1fae5;color:#065f46; }
.badge-pend { background:#fef3c7;color:#92400e; }

.btn-sm { display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;border-radius:6px;
          font-size:.75rem;font-weight:600;cursor:pointer;border:1.5px solid transparent;
          font-family:inherit;transition:all .15s; }
.btn-confirm { background:#d1fae5;color:#065f46;border-color:#a7f3d0; }
.btn-confirm:hover { background:#a7f3d0; }
.btn-unconfirm { background:#f1f5f9;color:#64748b;border-color:#e2e8f0; }
.btn-unconfirm:hover { background:#e2e8f0; }

.filter-bar { display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem; }
.filter-bar select,.filter-bar input { padding:.4rem .7rem;border:1.5px solid #e2e8f0;border-radius:7px;
                                        font-size:.8rem;font-family:inherit;outline:none; }
.filter-bar select:focus,.filter-bar input:focus { border-color:#3b82f6; }

.card { background:#fff;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.05); }
.card-head { padding:.7rem 1.1rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between; }
.card-body { padding:.85rem 1.1rem; }
.card-title { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin:0; }
</style>

<div class="bancos-wrap">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.5rem;">
        <div>
            <h1 style="font-size:1.15rem;font-weight:800;color:#1e293b;margin:0;">Depósitos a Grupo KGyR</h1>
            <p style="font-size:.78rem;color:#94a3b8;margin:.15rem 0 0;">BCP · BBVA</p>
        </div>
        <button class="btn-sm" style="background:#1e293b;color:#fff;border-color:#1e293b;"
                onclick="document.getElementById('modalNuevo').style.display='flex'">
            + Registrar depósito
        </button>
    </div>

    <!-- Tarjetas resumen -->
    <div class="bancos-cards">
        <?php foreach (['BCP','BBVA'] as $banco):
            $r = $resumen[$banco];
            $badgeCls = $banco === 'BCP' ? 'badge-bcp' : 'badge-bbva';
        ?>
        <div class="banco-card">
            <p class="banco-card__name"><span class="badge <?= $badgeCls ?>"><?= $banco ?></span></p>
            <p class="banco-card__total"><?= $f2($r['total'] ?? 0) ?></p>
            <div class="banco-card__row">
                <span>Confirmado: <strong style="color:#16a34a"><?= $f2($r['confirmado'] ?? 0) ?></strong></span>
                <span>Pendiente: <strong style="color:#d97706"><?= $f2($r['pendiente'] ?? 0) ?></strong></span>
                <span><?= (int)($r['registros'] ?? 0) ?> depósito<?= ($r['registros'] ?? 0) != 1 ? 's' : '' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtros + tabla -->
    <div class="card">
        <div class="card-head">
            <p class="card-title">Historial</p>
            <form method="get" class="filter-bar" style="margin:0;">
                <input type="hidden" name="page" value="bancos">
                <select name="banco">
                    <option value="">Todos los bancos</option>
                    <option value="BCP"  <?= $filtBanco==='BCP'  ? 'selected':'' ?>>BCP</option>
                    <option value="BBVA" <?= $filtBanco==='BBVA' ? 'selected':'' ?>>BBVA</option>
                </select>
                <select name="conf">
                    <option value=""  <?= $filtConf==='' ? 'selected':'' ?>>Todos</option>
                    <option value="0" <?= $filtConf==='0' ? 'selected':'' ?>>Pendientes</option>
                    <option value="1" <?= $filtConf==='1' ? 'selected':'' ?>>Confirmados</option>
                </select>
                <button type="submit" class="btn-sm" style="background:#f1f5f9;color:#475569;border-color:#e2e8f0;">Filtrar</button>
            </form>
        </div>
        <div class="card-body" style="padding:0;overflow-x:auto;">
            <?php if (empty($depositos)): ?>
            <p style="padding:1.5rem;font-size:.85rem;color:#94a3b8;text-align:center;">Sin depósitos registrados.</p>
            <?php else: ?>
            <table class="dep-table">
                <thead>
                    <tr>
                        <th>Banco</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Referencia</th>
                        <th>Sesión</th>
                        <th>Caja</th>
                        <th>Origen</th>
                        <th>Registrado por</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($depositos as $d): ?>
                    <tr id="dep-row-<?= $d['id'] ?>">
                        <td>
                            <span class="badge <?= $d['banco']==='BCP'?'badge-bcp':'badge-bbva' ?>">
                                <?= $d['banco'] ?>
                            </span>
                        </td>
                        <td style="white-space:nowrap;color:#475569;"><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                        <td style="font-weight:700;white-space:nowrap;"><?= $f2($d['monto']) ?></td>
                        <td style="color:#475569;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($d['referencia'] ?? '—') ?>
                        </td>
                        <td>
                            <?php if ($d['sesion_id']): ?>
                            <a href="<?= $base ?>/caja/reporte/<?= $d['sesion_id'] ?>" target="_blank"
                               style="color:#3b82f6;font-weight:600;">#<?= $d['sesion_id'] ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($d['caja_desc'] ?? '—') ?></td>
                        <td>
                            <span style="font-size:.72rem;color:#94a3b8;">
                                <?= match($d['origen']) {
                                    'PAGO_DEPOSITO'   => 'Arqueo',
                                    'AJUSTE_ESPERADO' => 'Incidencia',
                                    default           => 'Manual',
                                } ?>
                            </span>
                        </td>
                        <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($d['registrado_por'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $d['confirmado'] ? 'badge-ok' : 'badge-pend' ?>">
                                <?= $d['confirmado'] ? 'Confirmado' : 'Pendiente' ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!$d['confirmado']): ?>
                            <button class="btn-sm btn-confirm" onclick="confirmarDeposito(<?= $d['id'] ?>, this)">✓</button>
                            <?php else: ?>
                            <button class="btn-sm btn-unconfirm" onclick="confirmarDeposito(<?= $d['id'] ?>, this)">↩</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal nuevo depósito manual -->
<div id="modalNuevo" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);
     z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:1.5rem;width:100%;max-width:420px;margin:1rem;">
        <p style="font-size:.95rem;font-weight:800;color:#1e293b;margin:0 0 1rem;">Registrar depósito manual</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.6rem;">
            <div>
                <label style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:.3rem;">Banco *</label>
                <select id="m_banco" style="width:100%;padding:.45rem .7rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.875rem;font-family:inherit;">
                    <option value="BCP">BCP</option>
                    <option value="BBVA">BBVA</option>
                </select>
            </div>
            <div>
                <label style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:.3rem;">Monto (S/) *</label>
                <input type="number" step="0.01" min="0.01" id="m_monto"
                       style="width:100%;padding:.45rem .7rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.875rem;font-family:inherit;"
                       placeholder="0.00">
            </div>
        </div>
        <div style="margin-bottom:.6rem;">
            <label style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:.3rem;">N° Operación</label>
            <input type="text" id="m_ref"
                   style="width:100%;padding:.45rem .7rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.875rem;font-family:inherit;"
                   placeholder="Opcional">
        </div>
        <div style="margin-bottom:.6rem;">
            <label style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:.3rem;">Fecha *</label>
            <input type="date" id="m_fecha"
                   value="<?= date('Y-m-d') ?>"
                   style="width:100%;padding:.45rem .7rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.875rem;font-family:inherit;">
        </div>
        <div style="margin-bottom:.85rem;">
            <label style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:.3rem;">Notas</label>
            <input type="text" id="m_notas"
                   style="width:100%;padding:.45rem .7rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.875rem;font-family:inherit;"
                   placeholder="Opcional">
        </div>
        <div style="display:flex;gap:.5rem;justify-content:flex-end;">
            <button onclick="document.getElementById('modalNuevo').style.display='none'"
                    style="padding:.5rem 1rem;border-radius:7px;border:1.5px solid #e2e8f0;background:#f1f5f9;
                           color:#475569;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;">
                Cancelar
            </button>
            <button onclick="guardarDepositoManual()"
                    style="padding:.5rem 1rem;border-radius:7px;border:none;background:#1e293b;
                           color:#fff;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;">
                Guardar
            </button>
        </div>
        <p id="modalAlert" style="display:none;margin-top:.5rem;font-size:.78rem;font-weight:600;color:#991b1b;"></p>
    </div>
</div>

<script>
async function confirmarDeposito(id, btn) {
    try {
        const r    = await fetch(`${BASE}/admin/api/deposito-kgyr/${id}/toggle`, { method:'POST' });
        const json = await r.json();
        if (!r.ok) throw new Error(json.message || 'Error');
        location.reload();
    } catch(e) { alert(e.message); }
}

async function guardarDepositoManual() {
    const banco = document.getElementById('m_banco').value;
    const monto = parseFloat(document.getElementById('m_monto').value) || 0;
    const ref   = document.getElementById('m_ref').value.trim();
    const fecha = document.getElementById('m_fecha').value;
    const notas = document.getElementById('m_notas').value.trim();
    const alert = document.getElementById('modalAlert');

    if (!monto || monto <= 0) { alert.textContent='Ingresa un monto válido'; alert.style.display='block'; return; }
    if (!fecha) { alert.textContent='Selecciona la fecha'; alert.style.display='block'; return; }

    try {
        const r    = await fetch(`${BASE}/admin/api/deposito-kgyr`, {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ banco, monto, referencia: ref||null, fecha, notas: notas||null })
        });
        const json = await r.json();
        if (!r.ok) throw new Error(json.message || 'Error');
        location.reload();
    } catch(e) {
        alert.textContent = e.message;
        alert.style.display = 'block';
    }
}
</script>
