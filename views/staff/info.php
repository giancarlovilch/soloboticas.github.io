<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información interna | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        /* ── Header ── */
        .info-header {
            background: #1e293b;
            padding: .9rem 1.25rem;
            display: flex; align-items: center; gap: .75rem;
        }
        .info-header__logo {
            width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
            background: linear-gradient(135deg, #0097A7, #00BCD4);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.82rem; color: #fff;
        }
        .info-header__company { font-size: 0.58rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .07em; }
        .info-header__title   { font-size: 0.9rem; font-weight: 700; color: #fff; }

        /* ── Layout ── */
        .info-main {
            max-width: 720px;
            margin: 1.5rem auto;
            padding: 0 1rem 3rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }

        /* ── Secciones ── */
        .info-block { display: flex; flex-direction: column; gap: .5rem; }
        .info-block__label {
            font-size: 0.68rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: .09em; color: #0097A7;
            display: flex; align-items: center; gap: .4rem;
        }

        /* ── Tabla responsive ── */
        .tbl-wrap {
            border: 1px solid #e2e8f0; border-radius: 10px;
            overflow-x: auto; -webkit-overflow-scrolling: touch;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        table {
            width: 100%; border-collapse: collapse;
            font-size: 0.8rem; min-width: 320px;
        }
        th {
            text-align: left; padding: 8px 12px;
            background: #f8fafc;
            font-size: 0.68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em; color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155; vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
        .mono { font-family: 'Courier New', monospace; font-size: 0.78rem; color: #0f172a; }
        .bold { font-weight: 700; }
        .muted { color: #94a3b8; }

        /* Tarjetas de contacto (móvil-first para RRHH) */
        .contact-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: .6rem;
        }
        .contact-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: .85rem 1rem;
        }
        .contact-card__name { font-weight: 700; font-size: 0.85rem; margin-bottom: .4rem; color: #1e293b; }
        .contact-card__row  { display: flex; justify-content: space-between; gap: .5rem; font-size: 0.75rem; padding: 2px 0; }
        .contact-card__key  { color: #64748b; flex-shrink: 0; }
        .contact-card__val  { font-family: monospace; color: #0f172a; text-align: right; }

        .info-footer {
            text-align: center; font-size: 0.65rem; color: #cbd5e1; padding-top: .5rem;
        }

        @media (max-width: 480px) {
            .info-main { padding: 0 .75rem 2rem; }
            th, td { padding: 7px 9px; }
        }
    </style>
</head>
<body>

<header class="info-header">
    <div class="info-header__logo">SB</div>
    <div>
        <p class="info-header__company">Grupo KGyR S.A.C</p>
        <p class="info-header__title">Información interna</p>
    </div>
</header>

<main class="info-main">

    <!-- ── BCP Cuentas ── -->
    <div class="info-block">
        <p class="info-block__label">🏦 BCP — Cuentas por local</p>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Local</th>
                        <th>Cta. Corriente</th>
                        <th>Internet</th>
                        <th>Luz</th>
                        <th>Agua</th>
                    </tr>
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
                    ] as [$local, $cta, $inet, $luz, $agua]): ?>
                        <tr>
                            <td class="bold"><?= $local ?></td>
                            <td class="mono"><?= $cta ?></td>
                            <td><?= $inet ?></td>
                            <td><?= $luz ?></td>
                            <td><?= $agua ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── RRHH Contactos ── -->
    <div class="info-block">
        <p class="info-block__label">👥 RRHH — Contactos y cuentas</p>
        <div class="contact-cards">
            <?php foreach ([
                ['Grupo KGyR',    null,              '1919078016029', null],
                ['Sra. Marina',   '19191095796053',  null,             '947996894 (Bitel)'],
                ['Sr. Roy',       '19140391537031',  '1912172027065',  '999443808 (Bitel)'],
                ['Jv. Gian',      '19305710880064',  null,             '935812267 (Bitel)'],
                ['Sta. Kristhel', '19138031414069',  null,             '964211004 (Claro)'],
            ] as [$nombre, $ctaP, $ctaC, $tel]): ?>
            <div class="contact-card">
                <p class="contact-card__name"><?= $nombre ?></p>
                <?php if ($ctaP): ?>
                <div class="contact-card__row">
                    <span class="contact-card__key">Cta. personal</span>
                    <span class="contact-card__val"><?= $ctaP ?></span>
                </div>
                <?php endif; ?>
                <?php if ($ctaC): ?>
                <div class="contact-card__row">
                    <span class="contact-card__key">Cta. corriente</span>
                    <span class="contact-card__val"><?= $ctaC ?></span>
                </div>
                <?php endif; ?>
                <?php if ($tel): ?>
                <div class="contact-card__row">
                    <span class="contact-card__key">Contacto</span>
                    <span class="contact-card__val"><?= $tel ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Químicos ── -->
    <div class="info-block">
        <p class="info-block__label">⚗️ Químicos</p>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cta. Personal BCP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ([
                        ['Cerin Soto Yesmin Rosa',         '19196481864026'],
                        ['Oropeza Molina Stephanie Flor',  '19330034927009'],
                        ['Bocanegra Cachay Clara Ivonne',  '19123516317054'],
                    ] as [$nombre, $cta]): ?>
                        <tr>
                            <td><?= $nombre ?></td>
                            <td class="mono"><?= $cta ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Locales ── -->
    <div class="info-block">
        <p class="info-block__label">📍 Locales — Dirección y RUC</p>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Local</th>
                        <th>Dirección</th>
                        <th>RUC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ([
                        ['SB1', 'Av. Canto Grande 3714 Int. A — A.H. Jesús Oropeza Chonta, SJL',           '10456279487'],
                        ['SB2', 'Av. San Martín de Porres Este 111 Int. B — A.H. Jesús Oropeza Chonta, SJL', '20607821004'],
                        ['SB3', 'Av. Canto Grande 3718 Int. A-B — A.H. Jesús Oropeza Chonta, SJL',          '20607821004'],
                        ['SB4', 'Av. Canto Grande 2796 Int. A — Urb. Ganimedes, SJL',                        '20607821004'],
                    ] as [$local, $dir, $ruc]): ?>
                        <tr>
                            <td class="bold"><?= $local ?></td>
                            <td style="font-size:0.76rem;"><?= $dir ?></td>
                            <td class="mono"><?= $ruc ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="info-footer">Información confidencial · Solo Boticas · Grupo KGyR S.A.C</p>

</main>
</body>
</html>
