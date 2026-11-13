<?php
require_once __DIR__ . '/../../src/Services/SoftpharmaApiClient.php';

$db   = Database::getConnection();
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';

function normalizarNombre(string $n): string
{
    $n = mb_strtoupper(trim($n), 'UTF-8');
    $n = strtr($n, [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N','Ü'=>'U',
    ]);
    $n = preg_replace('/[^A-Z0-9 ]/', ' ', $n);
    $n = preg_replace('/\s+/', ' ', $n);
    return trim($n);
}

// ── Trabajadoras activas de soloboticas.com ────────────────
$postulantes = $db->query(
    "SELECT p.id_postulante, p.nombres, p.apellidos, p.num_documento, p.softpharma_usuario
     FROM postulante p
     INNER JOIN usuario u ON u.postulante_id = p.id_postulante
     WHERE u.activo = 1
     ORDER BY p.nombres, p.apellidos"
)->fetchAll();

// ── Usuarios de SOFTPHARMA ──────────────────────────────────
$apiError = null;
$usuariosErp = [];
try {
    $client = new SoftpharmaApiClient();
    $usuariosErp = $client->getUsuarios();
} catch (\Throwable $e) {
    $apiError = $e->getMessage();
}

// Indices para matching
$porDni = [];
foreach ($usuariosErp as $u) {
    if (!empty($u['numero_documento'])) $porDni[$u['numero_documento']] = $u;
}
$porNombreNorm = [];
foreach ($usuariosErp as $u) {
    $porNombreNorm[normalizarNombre($u['nombre'])] = $u;
}

// ── Sugerencias (no se guarda nada todavía, solo se calcula) ──
$filas = [];
foreach ($postulantes as $p) {
    $yaAsignado = $p['softpharma_usuario'];
    $sugerido = null;
    $confianza = null;

    if (!$yaAsignado) {
        if (!empty($p['num_documento']) && isset($porDni[$p['num_documento']])) {
            $sugerido = $porDni[$p['num_documento']];
            $confianza = 'dni';
        } else {
            $nombreCompleto = normalizarNombre($p['nombres'] . ' ' . $p['apellidos']);
            if (isset($porNombreNorm[$nombreCompleto])) {
                $sugerido = $porNombreNorm[$nombreCompleto];
                $confianza = 'nombre';
            }
        }
    }

    $filas[] = [
        'postulante'  => $p,
        'sugerido'    => $sugerido,
        'confianza'   => $confianza,
    ];
}

usort($filas, function($a, $b) {
    // Sin asignar y sin sugerencia primero (lo que necesita atención manual)
    $pa = $a['postulante']['softpharma_usuario'] ? 2 : ($a['sugerido'] ? 1 : 0);
    $pb = $b['postulante']['softpharma_usuario'] ? 2 : ($b['sugerido'] ? 1 : 0);
    if ($pa !== $pb) return $pa <=> $pb;
    return strcmp($a['postulante']['nombres'], $b['postulante']['nombres']);
});

$totalAsignados = count(array_filter($postulantes, fn($p) => $p['softpharma_usuario']));
$totalSugeridos = count(array_filter($filas, fn($f) => $f['sugerido']));
$totalSinNada   = count($filas) - $totalAsignados - $totalSugeridos;
?>

<style>
.su-wrap { max-width:1100px; margin:0 auto; padding:1.25rem 1rem 3rem; font-family:'Inter',sans-serif; }
.su-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
@media(max-width:700px){ .su-cards{ grid-template-columns:1fr; } }
.su-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:1rem 1.2rem; box-shadow:0 1px 3px rgba(0,0,0,.05); }
.su-card .num { font-size:1.7rem; font-weight:800; line-height:1; }
.su-card .lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-top:.3rem; }
.su-card.ok .num { color:#16a34a; }
.su-card.sug .num { color:#2563eb; }
.su-card.sin .num { color:#d97706; }

table.su-table { width:100%; border-collapse:collapse; font-size:.82rem; background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }
.su-table th { text-align:left; padding:.55rem .75rem; font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
               color:#94a3b8; border-bottom:2px solid #f1f5f9; white-space:nowrap; }
.su-table td { padding:.5rem .75rem; border-bottom:1px solid #f8fafc; vertical-align:middle; }
.su-table tr:last-child td { border-bottom:none; }
.su-table tr:hover td { background:#f8fafc; }

.badge { display:inline-block; padding:.2rem .55rem; border-radius:5px; font-size:.68rem; font-weight:700; }
.badge-dni { background:#d1fae5; color:#065f46; }
.badge-nombre { background:#dbeafe; color:#1e40af; }
.badge-ok { background:#e5e7eb; color:#374151; }
.badge-sin { background:#fef3c7; color:#92400e; }

select.su-sel { padding:.35rem .5rem; border:1.5px solid #e2e8f0; border-radius:6px; font-size:.78rem; font-family:inherit; min-width:220px; }
.btn-sm { padding:.3rem .7rem; border-radius:6px; font-size:.76rem; font-weight:700; cursor:pointer; border:1.5px solid transparent;
          font-family:inherit; background:#1e293b; color:#fff; }
.btn-sm:hover { background:#334155; }
.btn-sm:disabled { background:#cbd5e1; cursor:default; }
</style>

<div class="su-wrap">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; flex-wrap:wrap; gap:.5rem;">
        <div>
            <h1 style="font-size:1.15rem; font-weight:800; color:#1e293b; margin:0;">Usuarios SOFTPHARMA</h1>
            <p style="font-size:.78rem; color:#94a3b8; margin:.15rem 0 0;">Emparejar cada trabajadora con su login del ERP legacy (necesario para el panel de conciliación)</p>
        </div>
    </div>

    <?php if ($apiError): ?>
    <div style="padding:1rem 1.25rem; background:#fee2e2; color:#991b1b; border-radius:10px; margin-bottom:1.25rem; font-size:.85rem;">
        No se pudo contactar al microservicio de conciliación: <?= htmlspecialchars($apiError) ?>
    </div>
    <?php else: ?>

    <div class="su-cards">
        <div class="su-card ok"><div class="num"><?= $totalAsignados ?></div><div class="lbl">Ya asignados</div></div>
        <div class="su-card sug"><div class="num"><?= $totalSugeridos ?></div><div class="lbl">Con sugerencia</div></div>
        <div class="su-card sin"><div class="num"><?= $totalSinNada ?></div><div class="lbl">Sin sugerencia</div></div>
    </div>

    <?php if ($totalSugeridos > 0): ?>
    <div style="margin-bottom:1.1rem;">
        <button class="btn-sm" id="btnGuardarTodo" onclick="guardarTodasLasSugerencias()">
            Guardar las <?= $totalSugeridos ?> sugerencias de una vez
        </button>
        <span id="masivoMsg" style="margin-left:.6rem; font-size:.82rem;"></span>
    </div>
    <?php endif; ?>

    <table class="su-table">
        <thead>
            <tr>
                <th>Trabajadora</th>
                <th>DNI</th>
                <th>Usuario SOFTPHARMA</th>
                <th>Origen</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filas as $f): $p = $f['postulante']; ?>
            <tr id="su-row-<?= $p['id_postulante'] ?>">
                <td style="font-weight:700;"><?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?></td>
                <td class="mono" style="color:#64748b;"><?= htmlspecialchars($p['num_documento']) ?></td>
                <td>
                    <?php if ($p['softpharma_usuario']): ?>
                        <?= htmlspecialchars($p['softpharma_usuario']) ?>
                    <?php else: ?>
                        <select class="su-sel" id="su-sel-<?= $p['id_postulante'] ?>">
                            <option value="">— sin asignar —</option>
                            <?php foreach ($usuariosErp as $u): ?>
                            <option value="<?= htmlspecialchars($u['usuario']) ?>"
                                <?= ($f['sugerido'] && $f['sugerido']['usuario'] === $u['usuario']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['usuario']) ?> — <?= htmlspecialchars($u['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['softpharma_usuario']): ?>
                        <span class="badge badge-ok">Guardado</span>
                    <?php elseif ($f['confianza'] === 'dni'): ?>
                        <span class="badge badge-dni">DNI</span>
                    <?php elseif ($f['confianza'] === 'nombre'): ?>
                        <span class="badge badge-nombre">Nombre</span>
                    <?php else: ?>
                        <span class="badge badge-sin">Manual</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$p['softpharma_usuario']): ?>
                    <button class="btn-sm" onclick="guardarUsuario(<?= $p['id_postulante'] ?>, this)">Guardar</button>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
const SUGERENCIAS = <?= json_encode(array_values(array_filter(array_map(
    fn($f) => $f['sugerido'] && !$f['postulante']['softpharma_usuario']
        ? ['postulante_id' => $f['postulante']['id_postulante'], 'softpharma_usuario' => $f['sugerido']['usuario']]
        : null,
    $filas
)))) ?>;

async function guardarTodasLasSugerencias() {
    const btn = document.getElementById('btnGuardarTodo');
    const msg = document.getElementById('masivoMsg');
    btn.disabled = true;
    msg.textContent = 'Guardando...';
    try {
        const r = await fetch(`${BASE}/admin/api/softpharma-usuario/guardar-masivo`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pares: SUGERENCIAS })
        });
        const json = await r.json();
        if (!r.ok) throw new Error(json.message || 'Error');
        location.reload();
    } catch (e) {
        msg.textContent = e.message;
        btn.disabled = false;
    }
}

async function guardarUsuario(id, btn) {
    const sel = document.getElementById('su-sel-' + id);
    const usuario = sel.value;
    if (!usuario) { alert('Elige un usuario de SOFTPHARMA'); return; }
    btn.disabled = true;
    try {
        const r = await fetch(`${BASE}/admin/api/softpharma-usuario/guardar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ postulante_id: id, softpharma_usuario: usuario })
        });
        const json = await r.json();
        if (!r.ok) throw new Error(json.message || 'Error');
        location.reload();
    } catch (e) {
        alert(e.message);
        btn.disabled = false;
    }
}
</script>
