<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
extract($supervisoresDatos ?? []);
$supTrabajadores = $supTrabajadores ?? [];
$supPeriodos     = $supPeriodos     ?? [];
$hoy = date('Y-m-d');
?>
<style>
.bn-section  { margin-bottom:2rem; }
.bn-title    { font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#0097A7;margin-bottom:.75rem; }
.bn-card     { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:1rem 1.25rem;margin-bottom:.75rem; }
.bn-table    { width:100%;border-collapse:collapse;font-size:.8rem; }
.bn-table th { background:#f8fafc;padding:.35rem .6rem;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1.5px solid #e2e8f0; }
.bn-table td { padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
.bn-table tr:last-child td { border-bottom:none; }
.bn-add-form { display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;margin-top:.75rem;padding-top:.75rem;border-top:1px dashed #e2e8f0; }
.bn-add-form label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;display:block;margin-bottom:2px; }
.bn-add-form input, .bn-add-form select { padding:.38rem .65rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.8rem;outline:none;width:100%; }
.bn-add-form input:focus, .bn-add-form select:focus { border-color:#0097A7; }
.bn-msg { font-size:.78rem;padding:.4rem .75rem;border-radius:7px;margin-top:.5rem;display:none; }
.bn-msg--ok  { background:#d1fae5;color:#065f46; }
.bn-msg--err { background:#fee2e2;color:#991b1b; }
.bn-vig-tag  { font-size:.7rem;font-weight:700;padding:2px 9px;border-radius:20px; }
.bn-vig-act  { background:#d1fae5;color:#065f46; }
.bn-vig-past { background:#f1f5f9;color:#64748b; }
.sup-oblig   { font-size:.82rem;color:#475569;line-height:1.7;padding-left:1.2rem; }
.sup-oblig li { margin-bottom:.4rem; }
</style>

<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Economía</p>
            <h2>Supervisores</h2>
        </div>
    </div>

    <!-- ── Periodos de supervisión ─────────────────────── -->
    <div class="bn-section">
        <p class="bn-title">Periodos de supervisión</p>
        <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem;">
            Por cada turno trabajado dentro del periodo asignado, el monto indicado se suma al "Bono servicio" del trabajador en Economía.
        </p>

        <div class="bn-card">
            <table class="bn-table">
                <thead>
                    <tr>
                        <th>Trabajador</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Pago por turno</th>
                        <th class="text-center">Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbSupervisores">
                <?php foreach ($supPeriodos as $sp):
                    $vigente = $sp['fecha_desde'] <= $hoy && ($sp['fecha_hasta'] === null || $sp['fecha_hasta'] >= $hoy);
                ?>
                <tr id="sup-<?= $sp['id'] ?>">
                    <td style="font-weight:600;"><?= htmlspecialchars($sp['trabajador_nombre']) ?></td>
                    <td><?= date('d/m/Y', strtotime($sp['fecha_desde'])) ?></td>
                    <td><?= $sp['fecha_hasta'] !== null ? date('d/m/Y', strtotime($sp['fecha_hasta'])) : '<span style="color:#94a3b8;">Indefinido</span>' ?></td>
                    <td style="font-weight:700;color:#059669;">S/ <?= number_format((float)$sp['monto_dia'], 2, '.', '') ?></td>
                    <td class="text-center">
                        <span class="bn-vig-tag <?= $vigente ? 'bn-vig-act' : 'bn-vig-past' ?>">
                            <?= $vigente ? 'Activo' : ($sp['fecha_desde'] > $hoy ? 'Futuro' : 'Finalizado') ?>
                        </span>
                    </td>
                    <td><button onclick="delSupervisor(<?= $sp['id'] ?>)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.75rem;">✕</button></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($supPeriodos)): ?>
                <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:1rem;">Sin periodos registrados aún.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="bn-add-form">
                <div style="min-width:180px;flex:2;">
                    <label>Trabajador</label>
                    <select id="supTrabajador">
                        <?php foreach ($supTrabajadores as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:140px;flex:1;">
                    <label>Desde</label>
                    <input type="date" id="supDesde" value="<?= $hoy ?>">
                </div>
                <div style="min-width:140px;flex:1;">
                    <label>Hasta (vacío=indefinido)</label>
                    <input type="date" id="supHasta">
                </div>
                <div style="min-width:110px;flex:1;">
                    <label>Pago por turno (S/)</label>
                    <input type="number" id="supMonto" min="0" step="0.50" value="5.00">
                </div>
                <div style="display:flex;align-items:flex-end;">
                    <button class="asist-btn asist-btn--primary" onclick="addSupervisor()" style="white-space:nowrap;">+ Agregar</button>
                </div>
            </div>
            <div id="msgSupervisor" class="bn-msg"></div>
        </div>
    </div>

    <!-- ── Obligaciones del supervisor ─────────────────── -->
    <div class="bn-section">
        <p class="bn-title">Obligaciones del supervisor</p>
        <div class="bn-card">
            <ul class="sup-oblig">
                <li>Verificar la apertura y cierre puntual de caja en el local asignado durante su periodo.</li>
                <li>Supervisar la asistencia y puntualidad del personal de cada turno.</li>
                <li>Revisar que los cuadres de caja se realicen correctamente y reportar diferencias al administrador.</li>
                <li>Velar por el orden, limpieza y presentación del local durante su turno de supervisión.</li>
                <li>Atender y dar seguimiento a incidencias operativas (faltas, reclamos, problemas con el sistema, etc.).</li>
                <li>Servir como punto de contacto con la administración ante cualquier eventualidad fuera del horario normal.</li>
            </ul>
            <p style="font-size:.72rem;color:#94a3b8;margin-top:.75rem;">
                Texto referencial — ajustar según las funciones reales asignadas a cada supervisor.
            </p>
        </div>
    </div>
</div>

<script>
const BASE_SUP = window.location.pathname.split('/admin/')[0];
const apiUrlSup = (p) => `${window.location.origin}${BASE_SUP}${p}`;

function showMsgSup(msg, ok) {
    const el = document.getElementById('msgSupervisor');
    el.textContent = msg;
    el.className = 'bn-msg ' + (ok ? 'bn-msg--ok' : 'bn-msg--err');
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 3500);
}

async function addSupervisor() {
    const data = {
        postulante_id: document.getElementById('supTrabajador').value,
        fecha_desde:   document.getElementById('supDesde').value,
        fecha_hasta:   document.getElementById('supHasta').value,
        monto_dia:     document.getElementById('supMonto').value,
    };
    const r   = await fetch(apiUrlSup('/admin/api/supervisor/agregar'), {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)
    });
    const res = await r.json();
    if (res.success) { showMsgSup(res.message, true); setTimeout(() => location.reload(), 1000); }
    else showMsgSup(res.message || 'Error.', false);
}

async function delSupervisor(id) {
    if (!confirm('¿Eliminar este periodo de supervisión?')) return;
    const r   = await fetch(apiUrlSup(`/admin/api/supervisor/${id}/eliminar`), {
        method:'POST', headers:{'Content-Type':'application/json'}
    });
    const res = await r.json();
    if (res.success) { document.getElementById(`sup-${id}`)?.remove(); }
    else alert(res.message || 'Error.');
}
</script>
