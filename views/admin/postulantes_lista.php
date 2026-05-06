<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
?>

<!-- Modal: Confirmar eliminación con contraseña admin -->
<div id="deleteModal" class="admin-modal-overlay" hidden>
    <div class="admin-modal-box">
        <h3 class="admin-modal-title">Confirmar eliminación</h3>
        <p class="admin-modal-desc">
            Vas a eliminar a <strong id="deleteNombre">—</strong>. Esta acción
            es <strong>irreversible</strong> y borrará todos sus datos.<br>
            Ingresa tu contraseña de administrador para continuar.
        </p>
        <input type="password" id="deletePassword" class="admin-modal-input"
               placeholder="Tu contraseña de administrador" autocomplete="current-password">
        <div id="deleteError" class="admin-modal-error" hidden></div>
        <div class="admin-modal-actions">
            <button class="btn-refresh" onclick="cerrarModalEliminar()">Cancelar</button>
            <button class="btn-danger" id="deleteConfirmBtn" onclick="confirmarEliminar()">Eliminar</button>
        </div>
    </div>
</div>

<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Administración</p>
            <h2>Gestión de Postulantes</h2>
        </div>

        <div class="search-box">
            <!-- Input con el ID correcto para filtrarTabla() -->
            <input type="text"
                id="searchInput"
                placeholder="🔍 Buscar por DNI o nombre completo..."
                onkeyup="filtrarTabla()">

            <button class="btn-refresh" onclick="cargarPostulantes()">
                Actualizar Lista
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="fl-table" id="tablaPostulantes">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Postulante</th>
                    <th>Puesto</th>
                    <th>Etapa</th>
                    <th class="text-center">Estado</th>
                    <th>Registrado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>

            <!-- Contenedor dinámico poblado por postulantes.js -->
            <tbody id="tbodyPostulantes">
                <tr>
                    <td colspan="6" class="text-center">Cargando registros...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>