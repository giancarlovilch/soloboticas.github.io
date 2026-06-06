<?php
if (!isset($_SESSION['user_rol'])) {
    exit('Acceso denegado');
}
// Capturamos el ID desde la URL para que el JS lo use
$id_postulante = $_GET['id'] ?? null;
?>

<div class="update-container" data-id="<?= htmlspecialchars($id_postulante) ?>">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Panel de Control</p>
            <h2>Expediente del Postulante</h2>
        </div>
        <div class="actions">
            <a href="?page=postulantes" class="btn-back">Volver al listado</a>
        </div>
    </div>

    <!-- Banner de Estado Actual -->
    <div id="etapaBanner" class="status-banner">Cargando información...</div>

    <form id="updatePostulanteForm" class="form-grid-layout">
        <!-- Bloque 1: Datos Personales -->
        <section class="form-section">
            <h3><i class="fas fa-user"></i> Información Personal</h3>
            <div class="form-grid">
                <div class="input-group">
                    <label>Nombres y Apellidos</label>
                    <input type="text" id="nombre_completo" readonly class="readonly-input">
                </div>
                <div class="input-group">
                    <label>DNI</label>
                    <input type="text" id="num_documento" readonly class="readonly-input">
                </div>
                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="input-group">
                    <label>Teléfono</label>
                    <input type="text" id="telefono" name="telefono">
                </div>
            </div>
        </section>

        <!-- Bloque 2: Gestión de Contratación -->
        <section class="form-section highlight-section">
            <h3><i class="fas fa-briefcase"></i> Decisión de Contratación</h3>
            <div class="form-grid">
                <div class="input-group">
                    <label>Cambiar Etapa</label>
                    <select id="etapa_id" name="etapa_id" class="select-highlight">
                        <!-- Se llena dinámicamente -->
                    </select>
                </div>
                <div class="input-group">
                    <label>Estado de Usuario</label>
                    <select id="activo" name="activo">
                        <option value="1">Activo (Acceso permitido)</option>
                        <option value="0">Inactivo (Baja de personal)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Fecha de ingreso a la empresa</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso">
                    <small id="antiguedadLabel" style="color:#be185d;font-size:.72rem;margin-top:3px;display:block;"></small>
                </div>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn-save">Guardar Cambios y Procesar</button>
            </div>
        </section>
    </form>
</div>