<?php $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : ''; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información interna | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }

        /* ── Header ── */
        .ih-header {
            background: linear-gradient(135deg, #0097A7 0%, #0077a8 100%);
            padding: 1.25rem 1.5rem 1.5rem;
        }
        .ih-header__brand { display: flex; align-items: center; gap: .75rem; margin-bottom: .75rem; }
        .ih-logo {
            width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1rem; color: #fff;
            border: 2px solid rgba(255,255,255,.3);
        }
        .ih-company { font-size: .65rem; color: rgba(255,255,255,.7); text-transform: uppercase; letter-spacing: .1em; }
        .ih-title   { font-size: 1.1rem; font-weight: 800; color: #fff; }
        .ih-subtitle { font-size: .78rem; color: rgba(255,255,255,.8); line-height: 1.4; }

        /* ── Main ── */
        .ih-main { max-width: 780px; margin: 0 auto; padding: 1.25rem 1rem 3rem; display: flex; flex-direction: column; gap: 1rem; }

        /* ── Section ── */
        .ih-section { border-radius: 14px; overflow: hidden; }
        .ih-section__head {
            padding: .8rem 1.1rem;
            display: flex; align-items: center; gap: .6rem;
        }
        .ih-section__icon { font-size: 1.1rem; line-height: 1; }
        .ih-section__title { font-size: .9rem; font-weight: 700; }
        .ih-section__sub   { font-size: .68rem; opacity: .75; margin-top: 1px; }

        /* BCP — azul oscuro */
        .s-bcp .ih-section__head { background: #1e3a5f; }
        .s-bcp .ih-section__title { color: #93c5fd; }
        .s-bcp .ih-tbl-wrap { background: #172035; }

        /* RRHH — verde oscuro */
        .s-rrhh .ih-section__head { background: #14532d; }
        .s-rrhh .ih-section__title { color: #86efac; }
        .s-rrhh .ih-cards-wrap { background: #0d2d1a; }

        /* Químicos — morado */
        .s-quim .ih-section__head { background: #3b1054; }
        .s-quim .ih-section__title { color: #d8b4fe; }
        .s-quim .ih-tbl-wrap { background: #1f0a30; }

        /* Locales — naranja */
        .s-local .ih-section__head { background: #7c2d12; }
        .s-local .ih-section__title { color: #fdba74; }
        .s-local .ih-tbl-wrap { background: #3d1507; }

        /* ── Table ── */
        .ih-tbl-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .ih-table { width: 100%; border-collapse: collapse; font-size: .82rem; min-width: 280px; }
        .ih-table th {
            text-align: left; padding: .55rem .9rem;
            font-size: .63rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .07em; color: #64748b;
            background: rgba(255,255,255,.04);
            border-bottom: 1px solid rgba(255,255,255,.07);
            white-space: nowrap;
        }
        .ih-table td {
            padding: .65rem .9rem;
            border-bottom: 1px solid rgba(255,255,255,.05);
            vertical-align: middle; line-height: 1.4;
        }
        .ih-table tr:last-child td { border-bottom: none; }
        .ih-table tr:hover td { background: rgba(255,255,255,.04); }

        .tag-local {
            display: inline-block; background: rgba(255,255,255,.12);
            color: #fff; font-weight: 800; font-size: .78rem;
            padding: 2px 8px; border-radius: 6px; letter-spacing: .03em;
        }
        .val-mono { font-family: 'Courier New', monospace; font-size: .8rem; color: #e2e8f0; letter-spacing: .03em; }
        .val-muted { color: #475569; }
        .val-service { font-size: .76rem; color: #94a3b8; }

        /* ── Contact cards ── */
        .ih-cards-wrap { padding: .75rem; }
        .ih-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .6rem; }
        .ih-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px; padding: .8rem 1rem;
        }
        .ih-card__name { font-weight: 700; font-size: .88rem; color: #f1f5f9; margin-bottom: .5rem; }
        .ih-card__row  { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; font-size: .73rem; padding: 3px 0; }
        .ih-card__key  { color: #64748b; flex-shrink: 0; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; padding-top: 1px; }
        .ih-card__val  { font-family: 'Courier New', monospace; color: #cbd5e1; font-size: .73rem; text-align: right; }
        .ih-card__tel  { color: #34d399; font-weight: 600; font-size: .78rem; }

        .ih-footer { text-align: center; font-size: .63rem; color: #334155; padding: 1rem 0; }

        @media (max-width: 480px) {
            .ih-header { padding: 1rem 1rem 1.25rem; }
            .ih-title   { font-size: .95rem; }
            .ih-main    { padding: 1rem .75rem 2rem; }
            .ih-table td, .ih-table th { padding: .5rem .65rem; }
            .ih-cards-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 360px) {
            .ih-cards-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header class="ih-header">
    <div class="ih-header__brand">
        <div class="ih-logo">SB</div>
        <div>
            <p class="ih-company">Grupo KGyR S.A.C</p>
            <p class="ih-title">Información interna</p>
        </div>
    </div>
    <p class="ih-subtitle">Datos de referencia para el personal. Uso exclusivo interno.</p>
</header>

<main class="ih-main">

    <!-- BCP -->
    <div class="ih-section s-bcp">
        <div class="ih-section__head">
            <span class="ih-section__icon">🏦</span>
            <div>
                <p class="ih-section__title">BCP — Cuentas por local</p>
                <p class="ih-section__sub">Cuentas corrientes, internet, luz y agua</p>
            </div>
        </div>
        <div class="ih-tbl-wrap">
            <table class="ih-table">
                <thead>
                    <tr><th>Local</th><th>Cta. Corriente</th><th>Internet</th><th>Luz</th><th>Agua</th></tr>
                </thead>
                <tbody>
                <?php foreach ([
                    ['Casa', '—',             '0255635 (WIN)',       '1732831', '5197733'],
                    ['SB2',  '1919412206086', '47238914 (FiberPro)', '—',       '—'],
                    ['SB3',  '1919284055031', '10472389144 (WIN)',   '0966235', '—'],
                    ['SB4',  '1919392112016', '47238914 (FiberPro)', '3073910', '—'],
                    ['SB5',  '1919981454065', '—', '—', '—'],
                    ['SB6',  '1911476402050', '—', '—', '—'],
                    ['SB7',  '1917136746041', '—', '—', '—'],
                ] as [$l, $cta, $inet, $luz, $agua]): ?>
                <tr>
                    <td><span class="tag-local"><?= $l ?></span></td>
                    <td><?= $cta!=='—' ? '<span class="val-mono">'.$cta.'</span>' : '<span class="val-muted">—</span>' ?></td>
                    <td><span class="val-service"><?= $inet!=='—' ? $inet : '<span class="val-muted">—</span>' ?></span></td>
                    <td><?= $luz!=='—'  ? '<span class="val-mono">'.$luz.'</span>'  : '<span class="val-muted">—</span>' ?></td>
                    <td><?= $agua!=='—' ? '<span class="val-mono">'.$agua.'</span>' : '<span class="val-muted">—</span>' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RRHH -->
    <div class="ih-section s-rrhh">
        <div class="ih-section__head">
            <span class="ih-section__icon">👥</span>
            <div>
                <p class="ih-section__title">RRHH — Contactos y cuentas</p>
                <p class="ih-section__sub">Personal clave, datos bancarios y teléfonos</p>
            </div>
        </div>
        <div class="ih-cards-wrap">
            <div class="ih-cards-grid">
            <?php foreach ([
                ['Grupo KGyR',    null,              '1919078016029', null],
                ['Sra. Marina',   '19191095796053',  null,             '947 996 894 (Bitel)'],
                ['Sr. Roy',       '19140391537031',  '1912172027065',  '999 443 808 (Bitel)'],
                ['Jv. Gian',      '19305710880064',  null,             '935 812 267 (Bitel)'],
                ['Sta. Kristhel', '19138031414069',  null,             '964 211 004 (Claro)'],
            ] as [$nombre, $ctaP, $ctaC, $tel]): ?>
            <div class="ih-card">
                <p class="ih-card__name"><?= $nombre ?></p>
                <?php if ($ctaP): ?>
                <div class="ih-card__row">
                    <span class="ih-card__key">Personal</span>
                    <span class="ih-card__val"><?= $ctaP ?></span>
                </div>
                <?php endif; ?>
                <?php if ($ctaC): ?>
                <div class="ih-card__row">
                    <span class="ih-card__key">Corriente</span>
                    <span class="ih-card__val"><?= $ctaC ?></span>
                </div>
                <?php endif; ?>
                <?php if ($tel): ?>
                <div class="ih-card__row">
                    <span class="ih-card__key">📱</span>
                    <span class="ih-card__tel"><?= $tel ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Químicos -->
    <div class="ih-section s-quim">
        <div class="ih-section__head">
            <span class="ih-section__icon">⚗️</span>
            <div>
                <p class="ih-section__title">Químicos</p>
                <p class="ih-section__sub">Cuentas personales BCP</p>
            </div>
        </div>
        <div class="ih-tbl-wrap">
            <table class="ih-table">
                <thead><tr><th>Nombre</th><th>Cta. Personal BCP</th></tr></thead>
                <tbody>
                <?php foreach ([
                    ['Cerin Soto Yesmin Rosa',         '19196481864026'],
                    ['Oropeza Molina Stephanie Flor',  '19330034927009'],
                    ['Bocanegra Cachay Clara Ivonne',  '19123516317054'],
                ] as [$nombre, $cta]): ?>
                <tr>
                    <td style="color:#e9d5ff;"><?= $nombre ?></td>
                    <td><span class="val-mono"><?= $cta ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Locales -->
    <div class="ih-section s-local">
        <div class="ih-section__head">
            <span class="ih-section__icon">📍</span>
            <div>
                <p class="ih-section__title">Locales — Dirección y RUC</p>
                <p class="ih-section__sub">Datos fiscales de cada establecimiento</p>
            </div>
        </div>
        <div class="ih-tbl-wrap">
            <table class="ih-table">
                <thead><tr><th>Local</th><th>Dirección</th><th>RUC</th></tr></thead>
                <tbody>
                <?php foreach ([
                    ['SB1', 'Av. Canto Grande 3714 Int. A — A.H. Jesús Oropeza Chonta, SJL',             '10456279487'],
                    ['SB2', 'Av. San Martín de Porres Este 111 Int. B — A.H. Jesús Oropeza Chonta, SJL', '20607821004'],
                    ['SB3', 'Av. Canto Grande 3718 Int. A-B — A.H. Jesús Oropeza Chonta, SJL',           '20607821004'],
                    ['SB4', 'Av. Canto Grande 2796 Int. A — Urb. Ganimedes, SJL',                         '20607821004'],
                ] as [$l, $dir, $ruc]): ?>
                <tr>
                    <td><span class="tag-local"><?= $l ?></span></td>
                    <td style="font-size:.75rem;color:#cbd5e1;line-height:1.5;"><?= $dir ?></td>
                    <td><span class="val-mono" style="color:#fdba74;"><?= $ruc ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="ih-footer">🔒 Confidencial · Solo Boticas · Grupo KGyR S.A.C</p>
</main>
</body>
</html>
