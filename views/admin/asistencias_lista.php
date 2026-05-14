<?php if (!isset($_SESSION['user_rol'])) exit('Acceso denegado'); ?>
<style>
/* Radio buttons encuesta */
.mh-rg  { display:flex;gap:.3rem;flex-wrap:wrap;margin-top:.3rem; }
.mh-rb  { padding:.32rem .65rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.75rem;font-weight:600;
           cursor:pointer;background:#fff;color:#475569;transition:all .13s;line-height:1.2;text-align:center; }
.mh-rb.active { border-color:#0097A7;background:#f0fdfe;color:#0e7490; }
.mh-rb.rb-danger { border-color:#fca5a5;color:#991b1b; }
.mh-rb.rb-danger.active { border-color:#dc2626;background:#fee2e2;color:#991b1b; }
.mh-sep-enc { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;
              border-top:1px solid #f1f5f9;padding-top:.5rem;margin:.6rem 0 .3rem; }
.mh-field-enc { margin-bottom:.6rem; }
.mh-field-enc > label { font-size:.68rem;font-weight:700;color:#64748b;display:block;margin-bottom:.2rem; }
</style>

<!-- Modal editar ficha -->
<div id="editModal" class="asist-modal-overlay" hidden>
    <div class="asist-modal-box" style="max-width:560px;">
        <div class="asist-modal-header">
            <h3 id="editModalTitle">Editar ficha</h3>
            <button class="asist-modal-close" onclick="cerrarModalEdit()">✕</button>
        </div>
        <div class="asist-modal-body" style="max-height:72vh;overflow-y:auto;">
            <input type="hidden" id="editId">
            <input type="hidden" id="editSlotPid">
            <input type="hidden" id="editSlotFecha">
            <input type="hidden" id="editSlotTurno">
            <div class="asist-form-grid" style="margin-bottom:.75rem;">
                <div class="asist-field">
                    <label>Trabajador</label>
                    <input type="text" id="editNombre" disabled class="asist-input asist-input--readonly">
                </div>
                <div class="asist-field">
                    <label>Fecha · Turno</label>
                    <input type="text" id="editFechaLabel" disabled class="asist-input asist-input--readonly">
                </div>
                <div class="asist-field">
                    <label>Estado</label>
                    <select id="editEstado" class="asist-input">
                        <option value="A TIEMPO">A tiempo</option>
                        <option value="TEMPRANO">Temprano</option>
                        <option value="EXTRA">Extra (muy temprano)</option>
                        <option value="TARDE">Tarde</option>
                        <option value="FALTA">Falta</option>
                    </select>
                </div>
                <div class="asist-field">
                    <label>Local</label>
                    <select id="editLocal" class="asist-input">
                        <option value="">Sin local</option>
                    </select>
                </div>
            </div>

            <!-- ENTRADA -->
            <div class="mh-sep-enc">Entrada</div>

            <div class="mh-field-enc">
                <label>Puntualidad de llegada</label>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-field="llegada_puntualidad" data-val="MUY_TEMPRANO" onclick="pickRb(this)">+10 min antes</button>
                    <button type="button" class="mh-rb" data-field="llegada_puntualidad" data-val="TEMPRANO"     onclick="pickRb(this)">Temprano</button>
                    <button type="button" class="mh-rb rb-danger" data-field="llegada_puntualidad" data-val="TARDE"        onclick="pickRb(this)">Tarde</button>
                    <button type="button" class="mh-rb rb-danger" data-field="llegada_puntualidad" data-val="MUY_TARDE"    onclick="pickRb(this)">+10 min tarde</button>
                </div>
            </div>
            <div class="mh-field-enc">
                <label>¿Ayudó a abrir la puerta?</label>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-field="abrio_puerta" data-val="1" onclick="pickRb(this)">Sí</button>
                    <button type="button" class="mh-rb" data-field="abrio_puerta" data-val="0" onclick="pickRb(this)">No</button>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;">
                <div class="mh-field-enc">
                    <label>Aseo personal</label>
                    <div class="mh-rg">
                        <button type="button" class="mh-rb rb-danger" data-field="aseo_personal" data-val="SUCIO"   onclick="pickRb(this)">Sucio</button>
                        <button type="button" class="mh-rb" data-field="aseo_personal" data-val="REGULAR" onclick="pickRb(this)">Regular</button>
                        <button type="button" class="mh-rb" data-field="aseo_personal" data-val="LIMPIO"  onclick="pickRb(this)">Limpio</button>
                    </div>
                </div>
                <div class="mh-field-enc">
                    <label>Vestimenta</label>
                    <div class="mh-rg">
                        <button type="button" class="mh-rb rb-danger" data-field="vestimenta" data-val="SUCIO"   onclick="pickRb(this)">Sucio</button>
                        <button type="button" class="mh-rb" data-field="vestimenta" data-val="REGULAR" onclick="pickRb(this)">Regular</button>
                        <button type="button" class="mh-rb" data-field="vestimenta" data-val="LIMPIO"  onclick="pickRb(this)">Limpio</button>
                    </div>
                </div>
                <div class="mh-field-enc">
                    <label>Uñas</label>
                    <div class="mh-rg">
                        <button type="button" class="mh-rb rb-danger" data-field="unas" data-val="SUCIAS"  onclick="pickRb(this)">Sucias</button>
                        <button type="button" class="mh-rb" data-field="unas" data-val="REGULAR" onclick="pickRb(this)">Regular</button>
                        <button type="button" class="mh-rb" data-field="unas" data-val="LIMPIO"  onclick="pickRb(this)">Limpio</button>
                    </div>
                </div>
            </div>
            <div class="mh-field-enc">
                <label>Cabello</label>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-field="cabello" data-val="SUELTO"   onclick="pickRb(this)">Suelto</button>
                    <button type="button" class="mh-rb" data-field="cabello" data-val="RECOGIDO" onclick="pickRb(this)">Recogido</button>
                    <button type="button" class="mh-rb" data-field="cabello" data-val="MONO"     onclick="pickRb(this)">Con moño</button>
                </div>
            </div>

            <!-- SALIDA -->
            <div class="mh-sep-enc">Salida</div>

            <div class="mh-field-enc">
                <label>Puntualidad de salida</label>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-field="salida_puntualidad" data-val="MUY_TEMPRANO" onclick="pickRb(this)">+10 min antes</button>
                    <button type="button" class="mh-rb" data-field="salida_puntualidad" data-val="TEMPRANO"     onclick="pickRb(this)">Temprano</button>
                    <button type="button" class="mh-rb rb-danger" data-field="salida_puntualidad" data-val="TARDE"        onclick="pickRb(this)">Tarde</button>
                    <button type="button" class="mh-rb rb-danger" data-field="salida_puntualidad" data-val="MUY_TARDE"    onclick="pickRb(this)">+10 min tarde</button>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                <div class="mh-field-enc">
                    <label>Limpieza espacio personal</label>
                    <div class="mh-rg">
                        <button type="button" class="mh-rb rb-danger" data-field="limpieza_espacio" data-val="SUCIO"   onclick="pickRb(this)">Sucio</button>
                        <button type="button" class="mh-rb" data-field="limpieza_espacio" data-val="REGULAR" onclick="pickRb(this)">Regular</button>
                        <button type="button" class="mh-rb" data-field="limpieza_espacio" data-val="LIMPIO"  onclick="pickRb(this)">Limpio</button>
                    </div>
                </div>
                <div class="mh-field-enc">
                    <label>Medicamentos</label>
                    <div class="mh-rg">
                        <button type="button" class="mh-rb rb-danger" data-field="ordeno_medicamentos" data-val="DESORDENADO" onclick="pickRb(this)">Desordenado</button>
                        <button type="button" class="mh-rb" data-field="ordeno_medicamentos" data-val="REGULAR"     onclick="pickRb(this)">Regular</button>
                        <button type="button" class="mh-rb" data-field="ordeno_medicamentos" data-val="ORDENADO"    onclick="pickRb(this)">Ordenado</button>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                <div class="mh-field-enc">
                    <label>¿Limpieza general del local?</label>
                    <div class="mh-rg">
                        <button type="button" class="mh-rb" data-field="limpieza_local" data-val="1" onclick="pickRb(this)">Sí</button>
                        <button type="button" class="mh-rb" data-field="limpieza_local" data-val="0" onclick="pickRb(this)">No</button>
                    </div>
                </div>
                <div class="mh-field-enc">
                    <label>¿Ayudó a cerrar el local?</label>
                    <div class="mh-rg">
                        <button type="button" class="mh-rb" data-field="ayudo_cerrar" data-val="1" onclick="pickRb(this)">Sí</button>
                        <button type="button" class="mh-rb" data-field="ayudo_cerrar" data-val="0" onclick="pickRb(this)">No</button>
                    </div>
                </div>
            </div>

            <!-- Comentarios + admin fields -->
            <div class="mh-sep-enc">Notas</div>
            <div class="asist-form-grid">
                <div class="asist-field asist-field--full">
                    <label>Comentarios del turno</label>
                    <input type="text" id="editComentarios" class="asist-input" placeholder="Observaciones (máx. 200 caracteres)" maxlength="200">
                </div>
                <div class="asist-field asist-field--full">
                    <label>Justificación (admin)</label>
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
        <div class="asist-modal-footer">
            <button class="asist-btn asist-btn--secondary" onclick="cerrarModalEdit()">Cancelar</button>
            <button class="asist-btn asist-btn--primary" onclick="guardarEdit()">Guardar</button>
        </div>
        <div id="editMsg" class="asist-msg" hidden></div>
    </div>
</div>

<!-- Modal agregar manual -->
<div id="addModal" class="asist-modal-overlay" hidden>
    <div class="asist-modal-box" style="max-width:420px;">
        <div class="asist-modal-header">
            <h3>Agregar registro manual</h3>
            <button class="asist-modal-close" onclick="cerrarModalAdd()">✕</button>
        </div>
        <div class="asist-modal-body" style="display:flex;flex-direction:column;gap:.75rem;">
            <div class="asist-field">
                <label>Trabajador <span class="asist-req">*</span></label>
                <select id="addPostulante" class="asist-input"><option value="">— Seleccione trabajador —</option></select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div class="asist-field">
                    <label>Fecha <span class="asist-req">*</span></label>
                    <input type="date" id="addFecha" class="asist-input" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="asist-field">
                    <label>Turno</label>
                    <select id="addTurno" class="asist-input">
                        <option value="1">☀️ Mañana</option>
                        <option value="2">🌙 Tarde</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div class="asist-field">
                    <label>Estado</label>
                    <select id="addEstado" class="asist-input">
                        <option value="FALTA">Falta</option>
                        <option value="A TIEMPO">A tiempo</option>
                        <option value="TARDE">Tarde</option>
                        <option value="TEMPRANO">Temprano</option>
                        <option value="EXTRA">Extra</option>
                    </select>
                </div>
                <div class="asist-field">
                    <label>Local</label>
                    <select id="addLocal" class="asist-input"><option value="">Sin local</option></select>
                </div>
            </div>
            <div class="asist-field">
                <label>Justificación</label>
                <input type="text" id="addJustif" class="asist-input" placeholder="Motivo (opcional)">
            </div>
            <div id="addMsg" class="asist-msg" hidden></div>
        </div>
        <div class="asist-modal-footer">
            <button class="asist-btn asist-btn--secondary" onclick="cerrarModalAdd()">Cancelar</button>
            <button class="asist-btn asist-btn--primary" onclick="guardarAdd()">Registrar</button>
        </div>
    </div>
</div>

<!-- Modal eliminar -->
<div id="delModal" class="asist-modal-overlay" hidden>
    <div class="asist-modal-box" style="max-width:360px;">
        <div class="asist-modal-header">
            <h3>Eliminar registro</h3>
            <button class="asist-modal-close" onclick="cerrarModalDel()">✕</button>
        </div>
        <div class="asist-modal-body">
            <p id="delDesc" style="font-size:.82rem;color:#475569;margin-bottom:1rem;"></p>
            <label style="font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.35rem;">
                Confirma con tu contraseña de administrador
            </label>
            <input type="password" id="delPassword" class="asist-input" placeholder="Contraseña">
        </div>
        <div class="asist-modal-footer">
            <button class="asist-btn asist-btn--secondary" onclick="cerrarModalDel()">Cancelar</button>
            <button class="asist-btn" onclick="confirmarEliminar()" style="background:#dc2626;color:#fff;border:none;">Eliminar</button>
        </div>
        <div id="delMsg" class="asist-msg" hidden></div>
    </div>
</div>

<!-- Vista principal -->
<div class="postulantes-container">
    <div class="section-header" style="flex-wrap:wrap;gap:.75rem;">
        <div class="header-info">
            <p class="section-kicker">Recursos Humanos</p>
            <h2>Fichas de Asistencia</h2>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            <input type="date" id="filtroDesde" class="asist-filter-input"
                   value="<?= date('Y-m-01') ?>" onchange="cargarAsistencias()">
            <span style="color:#94a3b8;font-size:.8rem;">hasta</span>
            <input type="date" id="filtroHasta" class="asist-filter-input"
                   value="<?= date('Y-m-d') ?>" onchange="cargarAsistencias()">
            <select id="filtroPostulante" class="asist-filter-input" onchange="cargarAsistencias()" style="min-width:180px;">
                <option value="">Todos los trabajadores</option>
            </select>
            <button id="btnSinCalif" class="btn-refresh" onclick="toggleSinCalif()"
                    title="Mostrar solo turnos sin ficha completada">
                📋 Sin calificar
            </button>
            <button class="btn-refresh" onclick="cargarAsistencias()">Actualizar</button>
        </div>
    </div>

    <div id="contadorInfo" style="font-size:.75rem;color:#64748b;padding:.25rem 0 .5rem;"></div>

    <div class="table-wrapper">
        <table class="fl-table" id="tablaAsistencias">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Trabajador</th>
                    <th>Turno · Local · Rol</th>
                    <th class="text-center">Ficha</th>
                    <th class="text-center">Llegada</th>
                    <th class="text-center">Salida</th>
                    <th>Reg. por</th>
                    <th class="text-center">Acción</th>
                    <th class="text-center">Eliminar</th>
                </tr>
            </thead>
            <tbody id="tbodyAsistencias">
                <tr><td colspan="9" class="text-center">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>
