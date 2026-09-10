<?php
/**
 * Cabecera común del módulo Gestión de Locales.
 * Espera: $basePath, $userName, $userRol y $docTab ('locales' | 'presupuesto' | 'logs').
 */
$basePath = $basePath ?? (defined('APP_BASE_PATH') ? APP_BASE_PATH : '');
$userName = $userName ?? ($_SESSION['user_name'] ?? 'Usuario');
$userRol  = $userRol  ?? ($_SESSION['user_rol']  ?? 'STAFF');
$docTab   = $docTab   ?? 'locales';
$docTitle = $docTitle ?? 'Gestión de Locales';
$esAdmin  = $esAdmin  ?? ($userRol === 'ADMIN');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($docTitle) ?> | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/locales.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Gestión de Locales</strong> — Documentación y vigencias</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= htmlspecialchars($userRol) ?>)</span>
        <?php if ($esAdmin): ?>
            <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">Dashboard</a>
        <?php else: ?>
            <a href="<?= $basePath ?>/staff" class="caja-btn-back">← Portal</a>
        <?php endif; ?>
        <a href="<?= $basePath ?>/logout" class="caja-btn-back">Salir</a>
    </div>
</header>

<div class="doc-wrap">
    <nav class="doc-tabs">
        <a href="<?= $basePath ?>/locales"
           class="doc-tab <?= $docTab === 'locales' ? 'doc-tab--on' : '' ?>">🏪 Locales</a>
        <a href="<?= $basePath ?>/locales/presupuesto"
           class="doc-tab <?= $docTab === 'presupuesto' ? 'doc-tab--on' : '' ?>">💵 Presupuesto</a>
        <a href="<?= $basePath ?>/locales/logs"
           class="doc-tab <?= $docTab === 'logs' ? 'doc-tab--on' : '' ?>">🕘 Bitácora</a>
        <?php if ($esAdmin): ?>
            <a href="<?= $basePath ?>/admin/dashboard?page=documentos" class="doc-tab">⚙️ Catálogo de documentos</a>
        <?php endif; ?>
    </nav>
