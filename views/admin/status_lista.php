<?php
/**
 * Vista: Actualización de Estados de Usuario
 * Archivo: views/admin/status_lista.php
 */
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
?>

<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Seguridad y Acceso</p>
            <h2>Actualización de Estados</h2>
        </div>
        <div class="search-box">
            <!-- Buscador resaltante idéntico al de postulantes -->
            <input type="text" id="statusSearch" placeholder="🔍 Buscar por DNI o nombre..." onkeyup="filtrarStatus()">
        </div>
    </div>

    <div class="table-wrapper">
        <table class="fl-table">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Colaborador</th>
                    <th>Nombre de Usuario</th>
                    <th class="text-center">Estado de Acceso</th>
                    <th class="text-center">Acción de Seguridad</th>
                </tr>
            </thead>
            <tbody id="tbodyStatus">
                <tr>
                    <td colspan="5" class="text-center">Cargando cuentas de usuario...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>