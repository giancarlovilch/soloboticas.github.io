<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de Datos | Solo Boticas</title>
    <link rel="stylesheet" href="<?= APP_BASE_PATH ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= APP_BASE_PATH ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= APP_BASE_PATH ?>/assets/img/logo.ico">
    <style>
        .db-upload-form { display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-top:.85rem;
            background:#f8fafc;padding:.85rem 1rem;border-radius:8px;border:1px solid #e2e8f0; }
        .db-upload-form input[type="file"] { flex:1;min-width:0;padding:.45rem .65rem;
            border:1.5px solid #e2e8f0;border-radius:7px;font-size:.82rem;background:#fff;outline:none; }
        .db-migration-list { background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;
            max-height:280px;overflow-y:auto;margin-top:.75rem; }
        .db-migration-item { display:flex;align-items:center;justify-content:space-between;gap:.75rem;
            padding:.6rem .9rem;border-bottom:1px solid #f1f5f9;font-size:.8rem;color:#475569; }
        .db-migration-item:last-child { border-bottom:none; }
        .db-migration-item span { font-family:monospace;font-size:.76rem; }
        .db-warn { background:#fff5f5;border:1px solid #fecaca;border-radius:8px;
            padding:.75rem 1rem;color:#991b1b;font-size:.82rem;font-weight:600;margin-bottom:.75rem; }
        /* Loader */
        .db-loader { display:none;position:fixed;inset:0;background:rgba(255,255,255,.85);
            z-index:9999;flex-direction:column;align-items:center;justify-content:center; }
        .db-spinner { width:48px;height:48px;border:5px solid #e2e8f0;
            border-top-color:#0097A7;border-radius:50%;animation:dbspin 1s linear infinite; }
        @keyframes dbspin { to { transform:rotate(360deg); } }
        .db-loader-txt { margin-top:1rem;font-weight:700;color:#1e293b;font-size:.88rem; }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<!-- Loader -->
<div id="dbLoader" class="db-loader">
    <div class="db-spinner"></div>
    <div class="db-loader-txt">Procesando base de datos… por favor espere.</div>
</div>

<!-- Header -->
<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Gestión de <strong>Base de Datos</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <a href="<?= APP_BASE_PATH ?>/admin/dashboard" class="caja-btn-back">← Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:820px;">

    <?php if (isset($_GET['reset']) || isset($_GET['applied'])): ?>
        <div class="caja-alert caja-alert--ok" style="margin-bottom:.75rem;">
            ✅ Base de datos actualizada correctamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="caja-alert caja-alert--error" style="white-space:pre-wrap;font-family:monospace;font-size:.78rem;margin-bottom:.75rem;">
            ❌ Error: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <!-- 1. Subir y reemplazar -->
    <section class="caja-card">
        <h2 class="caja-card__title">1. Subir y Reemplazar Base de Datos</h2>
        <div class="db-warn">⚠️ Esta acción reemplazará completamente la base de datos actual con el archivo seleccionado.</div>
        <p class="caja-card__desc">Sube un archivo <code>.sql</code> para restaurar un backup o aplicar una versión nueva.</p>
        <form action="<?= APP_BASE_PATH ?>/admin/database/upload" method="POST" enctype="multipart/form-data" class="db-upload-form">
            <input type="file" name="sql_file" accept=".sql" required>
            <button type="submit" class="caja-btn caja-btn--primary" style="background:#dc2626;border-color:#dc2626;white-space:nowrap;">
                Subir y Reemplazar
            </button>
        </form>
    </section>

    <!-- 2. Backup completo -->
    <section class="caja-card">
        <h2 class="caja-card__title">2. Generar Backup Completo</h2>
        <p class="caja-card__desc">Descarga un volcado completo (estructura + datos) del estado actual de la base de datos.</p>
        <div style="margin-top:.85rem;">
            <a href="<?= APP_BASE_PATH ?>/admin/database/download-full" class="caja-btn caja-btn--primary" id="btnBackup">
                Generar y Descargar Backup
            </a>
        </div>
    </section>

    <!-- 3. Historial -->
    <section class="caja-card">
        <h2 class="caja-card__title">Historial de versiones subidas</h2>
        <p class="caja-card__desc">Archivos guardados en <code>db/migrations/</code>. Puedes restaurar cualquier versión anterior.</p>
        <div class="db-migration-list">
            <?php if (empty($migrations)): ?>
                <div style="padding:.75rem 1rem;color:#94a3b8;font-size:.82rem;">No hay archivos en el historial.</div>
            <?php else: ?>
                <?php foreach (array_reverse($migrations) as $m): ?>
                    <div class="db-migration-item">
                        <span>📄 <?= htmlspecialchars($m) ?></span>
                        <a href="<?= APP_BASE_PATH ?>/admin/database/apply?file=<?= urlencode($m) ?>"
                           class="caja-btn caja-btn--outline" style="padding:3px 10px;font-size:.72rem;">
                            Cargar
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

</main>

<script>
function showLoader() {
    document.getElementById('dbLoader').style.display = 'flex';
    document.querySelectorAll('button,a.caja-btn').forEach(b => b.style.pointerEvents = 'none');
}

document.querySelector('form').onsubmit = function() {
    if (confirm('¿Confirmas? La base de datos actual será reemplazada por completo.')) {
        showLoader(); return true;
    }
    return false;
};

document.getElementById('btnBackup').onclick = function() {
    const orig = this.textContent;
    this.textContent = 'Generando…';
    this.style.opacity = '.7';
    setTimeout(() => { this.textContent = orig; this.style.opacity = '1'; }, 6000);
};

document.querySelectorAll('.db-migration-item a').forEach(a => {
    a.onclick = function() {
        if (!confirm('¿Restaurar la base de datos a esta versión?')) return false;
        showLoader(); return true;
    };
});
</script>
</body>
</html>
