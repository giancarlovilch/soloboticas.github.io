<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PLIN · Visor</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f1f4;color:#1a1a2e;min-height:100vh}

/* header */
.header{background:#003da6;color:#fff;padding:8px 14px;display:flex;align-items:center;gap:10px}
.header a{color:rgba(255,255,255,.75);text-decoration:none;font-size:13px;font-weight:600;padding:3px 8px;border:1px solid rgba(255,255,255,.3);border-radius:5px;line-height:1;margin-right:2px}
.header a:hover{background:rgba(255,255,255,.15)}
.header h1{font-size:14px;font-weight:700;letter-spacing:.3px}
.dot{width:7px;height:7px;background:#4cd964;border-radius:50%;flex-shrink:0;animation:pulse 2s infinite;margin-left:auto}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

/* barra de fecha */
.fecha-bar{background:#fff;border-bottom:1px solid #dde0e8;padding:7px 14px;display:flex;align-items:center;gap:8px}
.fecha-bar button{background:#003da6;color:#fff;border:none;border-radius:5px;padding:4px 10px;font-size:12px;cursor:pointer;font-weight:600;line-height:1.4}
.fecha-bar button:hover{background:#0050cc}
.fecha-bar button:disabled{background:#b0b8c8;cursor:default}
.fecha-bar input[type=date]{border:1px solid #ccc;border-radius:5px;padding:4px 8px;font-size:12px;font-family:inherit}
#last-update{margin-left:auto;font-size:11px;color:#aaa}

/* resumen inline */
.resumen{background:#003da6;color:#fff;margin:10px 14px 0;border-radius:8px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between}
.res-total{font-size:24px;font-weight:800;font-variant-numeric:tabular-nums;line-height:1}
.res-total small{font-size:13px;font-weight:400;margin-right:2px}
.res-count{font-size:12px;opacity:.7;text-align:right;line-height:1.3}
.res-count strong{font-size:18px;font-weight:800;display:block;opacity:1}

/* tabla */
.tabla-wrap{margin:10px 14px 24px;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #dde0e8}
.tabla-head{display:grid;grid-template-columns:52px 1fr 80px;gap:6px;padding:6px 12px;background:#f2f3f6;border-bottom:1px solid #dde0e8;font-size:10px;font-weight:700;color:#777;text-transform:uppercase;letter-spacing:.5px}
.pago-row{display:grid;grid-template-columns:52px 1fr 80px;align-items:center;gap:6px;padding:6px 12px;border-bottom:1px solid #eeeef4}
.pago-row:last-child{border-bottom:none}
.pago-row:hover{background:#f6f7ff}
.pago-row.nuevo{animation:hi .5s ease}
@keyframes hi{from{background:#cfe3ff}to{background:#fff}}
.hora{font-size:11px;color:#aaa;font-variant-numeric:tabular-nums}
.cli{font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3}
.cli .sub{font-size:10px;color:#bbb;font-weight:400;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.monto{font-size:12px;font-weight:700;color:#003da6;white-space:nowrap;font-variant-numeric:tabular-nums;text-align:right}
.empty{padding:28px;text-align:center;color:#ccc;font-size:13px}
</style>
</head>
<body>

<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$pagos    = $pagos    ?? [];
$resumen  = $resumen  ?? ['total' => 0, 'cantidad' => 0];
$fecha    = $fecha    ?? date('Y-m-d');
$today    = date('Y-m-d');
$backUrl  = $backUrl  ?? $basePath . '/admin/dashboard';
?>

<div class="header">
  <a href="<?= htmlspecialchars($backUrl) ?>">← Volver</a>
  <h1>PLIN · Visor de Transacciones</h1>
  <div class="dot"></div>
</div>

<div class="fecha-bar">
  <button onclick="cambiarFecha(-1)">← Ant.</button>
  <input type="date" id="inp-fecha" value="<?= htmlspecialchars($fecha) ?>" onchange="irFecha(this.value)">
  <button onclick="cambiarFecha(1)" <?= ($fecha >= $today) ? 'disabled' : '' ?>>Sig. →</button>
  <span id="last-update"></span>
</div>

<div class="resumen" id="resumen">
  <div><div class="res-total"><small>S/</small><?= number_format((float)$resumen['total'], 2) ?></div></div>
  <div class="res-count"><strong><?= (int)$resumen['cantidad'] ?></strong>transacciones</div>
</div>

<div class="tabla-wrap">
  <div class="tabla-head">
    <span>Hora</span><span>Cliente</span><span style="text-align:right">Monto</span>
  </div>
  <div id="lista">
    <?php if (empty($pagos)): ?>
      <div class="empty" id="empty-msg">Sin transacciones para este día</div>
    <?php else: ?>
      <?php foreach ($pagos as $p): ?>
        <div class="pago-row" id="pr-<?= $p['id'] ?>">
          <div class="hora"><?= date('H:i', strtotime($p['fecha_notif'])) ?></div>
          <div class="cli">
            <?= htmlspecialchars($p['cliente']) ?>
            <?php if (!empty($p['subtexto'])): ?>
              <small class="sub"><?= htmlspecialchars($p['subtexto']) ?></small>
            <?php endif ?>
          </div>
          <div class="monto">S/ <?= number_format((float)$p['monto'], 2) ?></div>
        </div>
      <?php endforeach ?>
    <?php endif ?>
  </div>
</div>

<script>
const BASE = <?= json_encode($basePath) ?>;
let fecha  = <?= json_encode($fecha) ?>;
let maxId  = <?= empty($pagos) ? 0 : (int)$pagos[0]['id'] ?>;

function esc(s){ const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML }

function buildRow(p){
  const hora = p.fecha_notif ? p.fecha_notif.substring(11,16) : '--:--';
  const sub  = p.subtexto ? `<small class="sub">${esc(p.subtexto)}</small>` : '';
  return `<div class="pago-row nuevo" id="pr-${p.id}">
    <div class="hora">${hora}</div>
    <div class="cli">${esc(p.cliente)}${sub}</div>
    <div class="monto">S/ ${parseFloat(p.monto).toFixed(2)}</div>
  </div>`;
}

async function poll(){
  try {
    const r = await fetch(`${BASE}/plin/api/visor?fecha=${encodeURIComponent(fecha)}&since_id=${maxId}`);
    const j = await r.json();
    if (!j.data) return;
    const { pagos, total, cantidad } = j.data;

    if (pagos && pagos.length > 0){
      const lista = document.getElementById('lista');
      const empty = document.getElementById('empty-msg');
      if (empty) empty.remove();
      pagos.forEach(p => {
        if (document.getElementById('pr-' + p.id)) return;
        lista.insertAdjacentHTML('afterbegin', buildRow(p));
        if (+p.id > maxId) maxId = +p.id;
      });
    }

    document.getElementById('resumen').innerHTML =
      `<div><div class="res-total"><small>S/</small>${parseFloat(total).toFixed(2)}</div></div>
       <div class="res-count"><strong>${cantidad}</strong>transacciones</div>`;

    const t = new Date();
    document.getElementById('last-update').textContent =
      'Act. ' + t.toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit'});
  } catch(e){}
}

function cambiarFecha(d){
  const dt = new Date(fecha + 'T12:00:00');
  dt.setDate(dt.getDate() + d);
  irFecha(dt.toISOString().substring(0,10));
}

function irFecha(f){
  window.location.href = `${BASE}/plin?fecha=${f}`;
}

setInterval(poll, 30000);
</script>
</body>
</html>
