<?php
/** @var array $venta */ /** @var array $items */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota #<?= $venta['id'] ?> | Venta de Emergencia</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace; font-size: 12px; color: #000;
            width: 78mm; margin: 0 auto; padding: 4mm 2mm;
        }
        .np-center { text-align: center; }
        .np-empresa { font-size: 14px; font-weight: 700; }
        .np-local   { font-size: 12px; font-weight: 700; margin-top: 2px; }
        .np-titulo  { font-size: 12px; font-weight: 700; margin: 6px 0 2px; }
        hr { border: none; border-top: 1px dashed #000; margin: 5px 0; }
        .np-meta { font-size: 11px; margin: 1px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 4px; }
        th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; padding-bottom: 2px; }
        td { vertical-align: top; padding: 2px 0; }
        .np-num { text-align: right; white-space: nowrap; }
        .np-item-cod { font-size: 9.5px; color: #333; }
        .np-total-row td { border-top: 1px dashed #000; font-weight: 700; font-size: 13px; padding-top: 4px; }
        .np-aviso {
            margin-top: 8px; padding: 4px; border: 1px solid #000;
            font-size: 10px; font-weight: 700; text-align: center; line-height: 1.4;
        }
        .np-noprint { margin: 0 0 10px; text-align: center; }
        .np-noprint button, .np-noprint a {
            font-family: inherit; font-size: 13px; padding: 6px 14px; margin: 0 4px;
            border-radius: 6px; border: 1px solid #94a3b8; background: #f1f5f9; cursor: pointer;
            text-decoration: none; color: #000; display: inline-block;
        }
        @media print {
            .np-noprint { display: none !important; }
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body>

<div class="np-noprint">
    <button onclick="window.print()">🖨️ Imprimir</button>
    <a href="<?= $basePath ?>/ventas-emergencia/historial">← Volver al historial</a>
</div>

<div class="np-center">
    <div class="np-empresa">SOLO BOTICAS</div>
    <div class="np-local"><?= htmlspecialchars($venta['local_nombre']) ?></div>
    <div class="np-titulo">NOTA DE VENTA DE EMERGENCIA</div>
</div>

<hr>
<div class="np-meta">Ticket: #<?= $venta['id'] ?></div>
<div class="np-meta">Fecha: <?= date('d/m/Y H:i', strtotime($venta['creado_en'])) ?></div>
<div class="np-meta">Vendedor: <?= htmlspecialchars($venta['vendedor_nombre']) ?></div>
<hr>

<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th class="np-num">Cant</th>
            <th class="np-num">P.Unit</th>
            <th class="np-num">Subt.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
            <td>
                <?= htmlspecialchars($it['nombre_producto']) ?>
                <div class="np-item-cod"><?= htmlspecialchars($it['cod_producto']) ?></div>
            </td>
            <td class="np-num"><?= rtrim(rtrim(number_format($it['cantidad'], 2), '0'), '.') ?></td>
            <td class="np-num"><?= number_format($it['precio_venta'], 2) ?></td>
            <td class="np-num"><?= number_format($it['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="np-total-row">
            <td colspan="3">TOTAL</td>
            <td class="np-num">S/ <?= number_format($venta['total'], 2) ?></td>
        </tr>
    </tbody>
</table>

<div class="np-aviso">
    *** DOCUMENTO NO VÁLIDO COMO COMPROBANTE DE PAGO ***<br>
    Venta registrada durante una caída del sistema. Pendiente
    de ingresar manualmente al ERP.
</div>

<script>
window.addEventListener('load', () => setTimeout(() => window.print(), 300));
</script>
</body>
</html>
