<?php if (!isset($_SESSION['user_rol'])) exit('Acceso denegado'); ?>

<!-- Modal editar asistencia -->
<div id="editModal" class="asist-modal-overlay" hidden>
    <div class="asist-modal-box">
        <div class="asist-modal-header">
            <h3 id="editModalTitle">Editar asistencia</h3>
            <button class="asist-modal-close" onclick="cerrarModalEdit()">✕</button>
        </div>
        <div class="asist-modal-body">
            <input type="hidden" id="editId">
            <div class="asist-form-grid">
                <div class="asist-field">
                    <label>Trabajador</label>
                    <input type="text" id="editNombre" disabled class="asist-input asist-input--readonly">
                </div>
                <div class="asist-field">
                    <label>Fecha</label>
                    <input type="text" id="editFechaLabel" disabled class="asist-input asist-input--readonly">
                </div>
                <div class="asist-field">
                    <label>Hora ingreso</label>
                    <input type="datetime-local" id="editIngreso" class="asist-input">
                </div>
                <div class="asist-field">
                    <label>Hora salida</label>
                    <input type="datetime-local" id="editSalida" class="asist-input">
                </div>
                <div class="asist-field">
                    <label>Estado</label>
                    <select id="editEstado" class="asist-input">
                        <option value="A TIEMPO">A tiempo</option>
                        <option value="TARDE">Tarde</option>
                        <option value="FALTA">Falta</option>
                        <option value="EXTRA">Extra</option>
                        <option value="TEMPRANO">Temprano</option>
                    </select>
                </div>
                <div class="asist-field">
                    <label>Local</label>
                    <select id="editLocal" class="asist-input">
                        <option value="">Sin local</option>
                    </select>
                </div>
                <div class="asist-field asist-field--full">
                    <label>Justificación</label>
                    <input type="text" id="editJustif" class="asist-input" placeholder="Motivo (opcional)">
                </div>
                <div class="asist-field asist-field--full">
                    <label>Observación del supervisor</label>
                    <select id="editObs" class="asist-input">
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="PROCEDE">Procede</option>
                        <option value="NO PROCEDE">No procede</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Checklist del trabajador -->
        <div id="editChecklistWrap" style="padding:0 0 .5rem;" hidden>
            <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:.75rem 0 .5rem;">
                Declaraciones del trabajador
            </p>
            <div id="editChecklist" style="display:flex;flex-direction:column;gap:.4rem;"></div>
        </div>

        <div class="asist-modal-footer">
            <button class="asist-btn asist-btn--secondary" onclick="cerrarModalEdit()">Cancelar</button>
            <button class="asist-btn asist-btn--primary" onclick="guardarEdit()">Guardar asistencia</button>
        </div>
        <div id="editMsg" class="asist-msg" hidden></div>
    </div>
</div>

<!-- Modal agregar asistencia manual -->
<div id="addModal" class="asist-modal-overlay" hidden>
    <div class="asist-modal-box">
        <div class="asist-modal-header">
            <h3>Agregar registro manual</h3>
            <button class="asist-modal-close" onclick="cerrarModalAdd()">✕</button>
        </div>
        <div class="asist-modal-body">
            <div class="asist-form-grid">
                <div class="asist-field">
                    <label>Trabajador <span class="asist-req">*</span></label>
                    <select id="addPostulante" class="asist-input">
                        <option value="">Seleccione</option>
                    </select>
                </div>
                <div class="asist-field">
                    <label>Fecha <span class="asist-req">*</span></label>
                    <input type="date" id="addFecha" class="asist-input" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="asist-field">
                    <label>Hora ingreso</label>
                    <input type="datetime-local" id="addIngreso" class="asist-input">
                </div>
                <div class="asist-field">
                    <label>Hora salida</label>
                    <input type="datetime-local" id="addSalida" class="asist-input">
                </div>
                <div class="asist-field">
                    <label>Estado</label>
                    <select id="addEstado" class="asist-input">
                        <option value="FALTA">Falta</option>
                        <option value="A TIEMPO">A tiempo</option>
                        <option value="TARDE">Tarde</option>
                        <option value="EXTRA">Extra</option>
                        <option value="TEMPRANO">Temprano</option>
                    </select>
                </div>
                <div class="asist-field">
                    <label>Local</label>
                    <select id="addLocal" class="asist-input">
                        <option value="">Sin local</option>
                    </select>
                </div>
                <div class="asist-field asist-field--full">
                    <label>Justificación</label>
                    <input type="text" id="addJustif" class="asist-input" placeholder="Motivo (opcional)">
                </div>
            </div>
        </div>
        <div class="asist-modal-footer">
            <button class="asist-btn asist-btn--secondary" onclick="cerrarModalAdd()">Cancelar</button>
            <button class="asist-btn asist-btn--primary" onclick="guardarAdd()">Registrar</button>
        </div>
        <div id="addMsg" class="asist-msg" hidden></div>
    </div>
</div>

<!-- Modal eliminar asistencia con clave -->
<div id="delModal" class="asist-modal-overlay" hidden>
    <div class="asist-modal-box" style="max-width:360px;">
        <div class="asist-modal-header">
            <h3>Eliminar asistencia</h3>
            <button class="asist-modal-close" onclick="cerrarModalDel()">✕</button>
        </div>
        <div class="asist-modal-body">
            <p id="delDesc" style="font-size:0.82rem;color:#475569;margin-bottom:1rem;"></p>
            <label style="font-size:0.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.35rem;">
                Confirma con tu contraseña de administrador
            </label>
            <input type="password" id="delPassword" class="asist-input"
                   placeholder="Contraseña" autocomplete="current-password">
        </div>
        <div class="asist-modal-footer">
            <button class="asist-btn asist-btn--secondary" onclick="cerrarModalDel()">Cancelar</button>
            <button class="asist-btn" onclick="confirmarEliminar()"
                    style="background:#dc2626;color:#fff;border:none;">
                Eliminar definitivamente
            </button>
        </div>
        <div id="delMsg" class="asist-msg" hidden></div>
    </div>
</div>

<!-- Vista principal -->
<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Recursos Humanos</p>
            <h2>Control de Asistencias</h2>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
            <input type="date" id="filtroFecha" class="asist-filter-input"
                   value="<?= date('Y-m-d') ?>"
                   onchange="cargarAsistencias()">
            <select id="filtroPostulante" class="asist-filter-input" onchange="cargarAsistencias()" style="min-width:180px;">
                <option value="">Todos los trabajadores</option>
            </select>
            <button class="btn-refresh" onclick="cargarAsistencias()">Actualizar</button>
            <button class="btn-edit" onclick="abrirModalAdd()">+ Agregar</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="fl-table" id="tablaAsistencias">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Trabajador</th>
                    <th>DNI</th>
                    <th>Local</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Observación</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>
            </thead>
            <tbody id="tbodyAsistencias">
                <tr><td colspan="10" class="text-center">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>
