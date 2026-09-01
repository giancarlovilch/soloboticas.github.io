<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
extract($ebcpDatos ?? []);
$ebcpMeses  = $ebcpMeses  ?? [];
$ebcpMesSel = $ebcpMesSel ?? date('Y-m');
$ebcpVotos       = $ebcpVotos       ?? [];
$ebcpMesActivo   = $ebcpMesActivo   ?? null;
$ebcpYoVote      = $ebcpYoVote      ?? false;
$ebcpComentarios = $ebcpComentarios ?? [];
$ebcpRanking     = $ebcpRanking     ?? [];
$basePath        = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';

$ebcpMesesNom = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
if ($ebcpMesActivo) {
    [$ebcpAnioAct, $ebcpNmesAct] = explode('-', $ebcpMesActivo);
    $ebcpMesActivoLabel = $ebcpMesesNom[(int)$ebcpNmesAct - 1] . ' ' . $ebcpAnioAct;
}
?>
<style>
.eb-table    { width:100%;border-collapse:collapse;font-size:.8rem; }
.eb-table th { background:#f8fafc;padding:.4rem .6rem;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1.5px solid #e2e8f0; }
.eb-table td { padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
.eb-table tr:last-child td { border-bottom:none; }
.eb-input    { width:52px;padding:.3rem .4rem;border:1.5px solid #e2e8f0;border-radius:6px;font-size:.8rem;text-align:center; }
.eb-btn      { border:none;border-radius:6px;padding:.32rem .7rem;font-size:.72rem;font-weight:700;cursor:pointer;margin-right:.3rem; }
.eb-btn--save   { background:#0097A7;color:#fff; }
.eb-btn--delete { background:#fee2e2;color:#991b1b; }
.eb-empty    { text-align:center;padding:2.5rem;color:#94a3b8; }
</style>

<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Personal</p>
            <h2>Encuesta BCP de cajeras</h2>
        </div>
    </div>

    <p style="font-size:.8rem;color:#64748b;margin-bottom:1rem;">
        Votos individuales de la encuesta anónima (tarjeta propia, fraccionamiento de operaciones e irregularidad en síntesis).
        Aquí como admin puedes ver quién votó, editar o eliminar un voto puntual.
    </p>

    <?php if ($ebcpMesActivo): ?>
    <a href="<?= $basePath ?>/staff/encuesta-bcp<?= $ebcpYoVote ? '/resultados' : '' ?>" target="_blank"
       style="display:block;margin-bottom:1rem;padding:.7rem 1rem;border-radius:8px;text-decoration:none;font-weight:700;font-size:.85rem;
              background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fdba74;color:#9a3412;">
        <?= $ebcpYoVote
            ? '📊 Ya votaste este mes — ver resultados de ' . htmlspecialchars($ebcpMesActivoLabel)
            : '🔥 Tú también eres personal — vota la encuesta de ' . htmlspecialchars($ebcpMesActivoLabel) ?>
    </a>
    <?php endif; ?>

    <form method="GET" style="display:flex;gap:.6rem;align-items:flex-end;margin-bottom:1rem;">
        <input type="hidden" name="page" value="encuesta-bcp">
        <div style="display:flex;flex-direction:column;gap:.25rem;">
            <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Mes</label>
            <select name="mes" onchange="this.form.submit()" style="padding:.4rem .7rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.82rem;">
                <?php foreach ($ebcpMeses as $m): ?>
                <option value="<?= $m ?>" <?= $m === $ebcpMesSel ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
                <?php if (!in_array($ebcpMesSel, $ebcpMeses, true)): ?>
                <option value="<?= htmlspecialchars($ebcpMesSel) ?>" selected><?= htmlspecialchars($ebcpMesSel) ?></option>
                <?php endif; ?>
            </select>
        </div>
    </form>

    <div id="ebMsg" style="display:none;font-size:.8rem;padding:.5rem .8rem;border-radius:7px;margin-bottom:.75rem;"></div>

    <!-- ── Ranking de aprobación (promedio de las 4 preguntas) ── -->
    <?php if (!empty($ebcpRanking)): ?>
    <p style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.5rem;">
        📊 Ranking de <?= htmlspecialchars($ebcpMesSel) ?> — % de sospecha (desaprueba si supera 50%)
    </p>
    <p style="font-size:.7rem;color:#94a3b8;margin:-.25rem 0 .5rem;">
        Descuento sugerido = % de sospecha ÷ 10 (solo si desaprueba) — máximo 10% con 100% de sospecha.
    </p>
    <div style="overflow-x:auto;background:#fff;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:1.5rem;">
    <table class="eb-table">
        <thead>
            <tr>
                <th>Cajera</th>
                <th class="text-center">% Sospecha</th>
                <th class="text-center">Resultado</th>
                <th class="text-center">Descuento sugerido</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ebcpRanking as $r): ?>
        <tr>
            <td style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($r['nombres']) ?></td>
            <td class="text-center" style="font-weight:700;"><?= number_format((float)$r['pct_sospecha'], 1) ?>%</td>
            <td class="text-center">
                <?php if ($r['aprobado']): ?>
                <span style="background:#d1fae5;color:#065f46;font-size:.68rem;font-weight:700;padding:2px 10px;border-radius:20px;">✅ Aprobó</span>
                <?php else: ?>
                <span style="background:#fee2e2;color:#991b1b;font-size:.68rem;font-weight:700;padding:2px 10px;border-radius:20px;">❌ Desaprobó</span>
                <?php endif; ?>
            </td>
            <td class="text-center" style="font-weight:700;color:<?= $r['aprobado'] ? '#94a3b8' : '#991b1b' ?>;">
                <?= $r['aprobado'] ? '—' : number_format((float)$r['descuento_pct'], 2) . '%' ?>
            </td>
            <td>
                <?php if (!$r['aprobado']): ?>
                <a href="<?= $basePath ?>/admin/dashboard?page=penalidades" style="font-size:.72rem;color:#0097A7;font-weight:700;text-decoration:none;">
                    Registrar penalidad →
                </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

    <?php if (empty($ebcpVotos)): ?>
    <div class="eb-empty">No hay votos registrados en <?= htmlspecialchars($ebcpMesSel) ?>.</div>
    <?php else: ?>
    <div style="overflow-x:auto;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
    <table class="eb-table">
        <thead>
            <tr>
                <th>Cajera</th>
                <th>Votante</th>
                <th class="text-center">🔥 Tarjeta propia</th>
                <th class="text-center">🔥 Fraccionamiento</th>
                <th class="text-center">🔥 Irregularidad</th>
                <th class="text-center">🔥 Sobrantes</th>
                <th>Fecha</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ebcpVotos as $v): ?>
        <tr data-id="<?= $v['id_voto'] ?>">
            <td style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($v['cajera_nombre']) ?></td>
            <td style="color:#64748b;"><?= htmlspecialchars($v['votante_nombre']) ?></td>
            <td class="text-center"><input type="number" class="eb-input" min="1" max="10" value="<?= (int)$v['tarjeta_propia'] ?>" data-field="tarjeta_propia"></td>
            <td class="text-center"><input type="number" class="eb-input" min="1" max="10" value="<?= (int)$v['fraccionamiento'] ?>" data-field="fraccionamiento"></td>
            <td class="text-center"><input type="number" class="eb-input" min="1" max="10" value="<?= (int)$v['irregularidad'] ?>" data-field="irregularidad"></td>
            <td class="text-center"><input type="number" class="eb-input" min="1" max="10" value="<?= (int)$v['apropiacion_sobrante'] ?>" data-field="apropiacion_sobrante"></td>
            <td style="color:#94a3b8;font-size:.72rem;white-space:nowrap;"><?= date('d/m/Y', strtotime($v['fecha_registro'])) ?></td>
            <td style="white-space:nowrap;">
                <button class="eb-btn eb-btn--save" onclick="ebGuardar(<?= $v['id_voto'] ?>, this)">Guardar</button>
                <button class="eb-btn eb-btn--delete" onclick="ebEliminar(<?= $v['id_voto'] ?>, this)">Eliminar</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($ebcpComentarios)): ?>
    <p style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:1.5rem 0 .65rem;">
        💬 Comentarios de <?= htmlspecialchars($ebcpMesSel) ?> (visibles solo para admin — moderación)
    </p>
    <div style="display:flex;flex-direction:column;gap:.5rem;">
        <?php foreach ($ebcpComentarios as $com): ?>
        <div id="ebcom-<?= $com['id_comentario'] ?>" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.6rem .8rem;font-size:.8rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;">
                <span style="font-size:.68rem;font-weight:700;color:#64748b;">
                    <?= htmlspecialchars($com['votante_nombre']) ?> · <?= date('d/m/Y H:i', strtotime($com['fecha_registro'])) ?>
                </span>
                <button class="eb-btn eb-btn--delete" onclick="ebEliminarComentario(<?= $com['id_comentario'] ?>, this)">Eliminar</button>
            </div>
            <div style="color:#334155;white-space:pre-wrap;"><?= htmlspecialchars($com['comentario']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
const EB_BASE = '<?= $basePath ?>';

function ebMostrarMsg(txt, ok) {
    const el = document.getElementById('ebMsg');
    el.textContent = txt;
    el.style.background = ok ? '#d1fae5' : '#fee2e2';
    el.style.color      = ok ? '#065f46' : '#991b1b';
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

async function ebGuardar(id, btn) {
    const row = btn.closest('tr');
    const tp = row.querySelector('[data-field="tarjeta_propia"]').value;
    const fr = row.querySelector('[data-field="fraccionamiento"]').value;
    const ir = row.querySelector('[data-field="irregularidad"]').value;
    const so = row.querySelector('[data-field="apropiacion_sobrante"]').value;
    btn.disabled = true;
    try {
        const r = await fetch(`${EB_BASE}/admin/api/encuesta-bcp/${id}/editar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tarjeta_propia: tp, fraccionamiento: fr, irregularidad: ir, apropiacion_sobrante: so }),
        });
        const res = await r.json();
        ebMostrarMsg(res.message || (res.success ? 'Guardado' : 'Error'), res.success);
    } catch { ebMostrarMsg('Error de conexión', false); }
    btn.disabled = false;
}

async function ebEliminar(id, btn) {
    if (!confirm('¿Eliminar este voto? No se puede deshacer.')) return;
    btn.disabled = true;
    try {
        const r = await fetch(`${EB_BASE}/admin/api/encuesta-bcp/${id}/eliminar`, { method: 'POST' });
        const res = await r.json();
        if (res.success) btn.closest('tr').remove();
        ebMostrarMsg(res.message || (res.success ? 'Eliminado' : 'Error'), res.success);
    } catch { ebMostrarMsg('Error de conexión', false); }
    btn.disabled = false;
}

async function ebEliminarComentario(id, btn) {
    if (!confirm('¿Eliminar este comentario? No se puede deshacer.')) return;
    btn.disabled = true;
    try {
        const r = await fetch(`${EB_BASE}/admin/api/encuesta-bcp/comentario/${id}/eliminar`, { method: 'POST' });
        const res = await r.json();
        if (res.success) document.getElementById(`ebcom-${id}`).remove();
        ebMostrarMsg(res.message || (res.success ? 'Eliminado' : 'Error'), res.success);
    } catch { ebMostrarMsg('Error de conexión', false); }
    btn.disabled = false;
}
</script>
