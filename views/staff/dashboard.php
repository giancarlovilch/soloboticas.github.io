<?php
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName  = $userName ?? $_SESSION['user_name'] ?? 'Colaborador';
$userRol   = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$diasSemana = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
$meses      = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$diaLabel   = $diasSemana[date('w')] . ', ' . date('d') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Asistencia | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
</head>
<body>

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">Solo Boticas <span>Colaboradores</span></p>
        </div>
    </div>
    <div class="staff-header__user">
        <div>
            <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
            <p class="staff-header__rol"><?= htmlspecialchars($userRol) ?></p>
        </div>
        <a href="<?= $basePath ?>/logout" class="staff-btn-logout">Salir</a>
        <a href="<?= $basePath ?>/staff/info" target="_blank"
           title="Información de la empresa"
           style="width:26px;height:26px;border-radius:50%;border:1.5px solid rgba(255,255,255,0.35);
                  background:rgba(255,255,255,0.12);color:#fff;font-size:0.78rem;font-weight:700;
                  cursor:pointer;line-height:26px;text-align:center;flex-shrink:0;
                  text-decoration:none;display:inline-flex;align-items:center;justify-content:center;
                  transition:background .15s;">?</a>
    </div>
</header>

<!-- ── Modal de confirmación ──────────────────────────── -->
<div id="marcarModal" class="staff-modal-overlay" hidden>
    <div class="staff-modal">
        <div class="staff-modal__header">
            <h3 id="modalTitulo">Marcar Entrada</h3>
            <button class="staff-modal__close" onclick="cerrarModal()">✕</button>
        </div>

        <div class="staff-modal__hora-box">
            <p class="staff-modal__hora-label">Tu hora registrada será:</p>
            <div class="staff-modal__hora" id="modalHoraActual">--:--:--</div>
        </div>

        <div class="staff-modal__checklist-wrap" id="checklistWrap">
            <p class="staff-modal__checklist-title">Declara lo siguiente:</p>
            <div id="modalChecklist" class="staff-checklist"></div>
        </div>

        <div class="staff-modal__pw-wrap">
            <label class="staff-modal__pw-label">Confirma con tu contraseña</label>
            <input type="password" id="modalPassword"
                   class="staff-modal__pw-input"
                   placeholder="Tu contraseña"
                   autocomplete="current-password">
        </div>

        <div id="modalError" class="staff-modal__error" hidden></div>

        <div class="staff-modal__footer">
            <button class="staff-modal__btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="staff-modal__btn-confirm" id="btnConfirmar" onclick="confirmarMarcaje()">
                Confirmar
            </button>
        </div>
    </div>
</div>

<main class="staff-main">

    <!-- ── Reloj ───────────────────────────────────────── -->
    <section class="staff-card staff-clock-card">
        <div class="staff-clock" id="reloj">00:00:00</div>
        <div class="staff-date"><?= $diaLabel ?></div>
    </section>

    <!-- ── Estado y botones ────────────────────────────── -->
    <section class="staff-card staff-today">
        <div id="todayStatus" class="staff-today__status">
            <span class="staff-spinner">Cargando...</span>
        </div>

        <div class="staff-today__times" id="todayTimes" hidden>
            <div class="time-pill time-pill--in">
                <span class="time-pill__label">Entrada</span>
                <span class="time-pill__value" id="horaIngreso">--:--</span>
            </div>
            <div class="time-pill time-pill--out">
                <span class="time-pill__label">Salida</span>
                <span class="time-pill__value" id="horaSalida">--:--</span>
            </div>
        </div>

        <!-- Selector de local (solo para ENTRADA) -->
        <div class="staff-local-wrap" id="localWrap" hidden>
            <label class="staff-local-label" for="localSelect">¿En qué local estás trabajando hoy?</label>
            <select id="localSelect" class="staff-local-select">
                <option value="">— Selecciona tu local —</option>
            </select>
        </div>

        <div class="staff-btns" id="staffBtns">
            <button id="btnEntrada"
                    class="staff-btn-marcar staff-btn-marcar--entrada"
                    disabled
                    onclick="abrirModal('ENTRADA')">
                Marcar Entrada
            </button>
            <button id="btnSalida"
                    class="staff-btn-marcar staff-btn-marcar--salida"
                    disabled
                    onclick="abrirModal('SALIDA')">
                Marcar Salida
            </button>
        </div>

        <div id="marcarMsg" class="staff-msg" hidden></div>
    </section>

    <!-- ── Horarios semanales ────────────────────────── -->
    <section class="staff-card" style="text-align:center;">
        <h2 class="staff-section-title">Mi horario semanal</h2>
        <p style="font-size:0.82rem;color:#64748b;margin-bottom:1rem;">
            Consulta y elige tus posiciones para la próxima semana.
        </p>
        <a href="<?= $basePath ?>/horario"
           class="staff-btn-marcar staff-btn-marcar--entrada"
           style="display:inline-block;text-decoration:none;padding:.75rem 2rem;">
            Ver horarios semanales →
        </a>
    </section>

    <!-- ── Caja ──────────────────────────────────────── -->
    <section class="staff-card" style="text-align:center;">
        <h2 class="staff-section-title">Módulo de caja</h2>
        <p style="font-size:0.82rem;color:#64748b;margin-bottom:1rem;">
            Registra y gestiona el arqueo de caja de tu turno.
        </p>
        <a href="<?= $basePath ?>/caja"
           class="staff-btn-marcar staff-btn-marcar--salida"
           style="display:inline-block;text-decoration:none;padding:.75rem 2rem;">
            Ir a caja →
        </a>
    </section>

    <!-- ── Historial ──────────────────────────────────── -->
    <section class="staff-card">
        <h2 class="staff-section-title">Mis últimas asistencias</h2>
        <div class="staff-table-wrap">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Local</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyHistorial">
                    <tr><td colspan="5" class="staff-table-empty">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

</main>

<script>
const BASE = (function() {
    const i = window.location.pathname.indexOf('/staff');
    return i === -1 ? '' : window.location.pathname.substring(0, i);
})();
const url = (p) => `${BASE}${p}`;
</script>
<script src="<?= $basePath ?>/assets/js/staff.js"></script>
</body>
</html>
