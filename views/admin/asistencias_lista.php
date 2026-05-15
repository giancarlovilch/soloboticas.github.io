<?php if (!isset($_SESSION['user_rol'])) exit('Acceso denegado'); ?>
<style>
/* Radio buttons encuesta */
.mh-rg  { display:flex;gap:.3rem;flex-wrap:wrap;margin-top:.3rem; }
.mh-rb  { padding:.32rem .65rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.75rem;font-weight:600;
           cursor:pointer;background:#fff;color:#475569;transition:all .13s;line-height:1.2;text-align:center; }
.mh-rb.active { border-color:#0097A7;background:#f0fdfe;color:#0e7490; }
.mh-block-enc { background:#f8fafc;border-radius:9px;padding:.6rem .8rem;margin-bottom:.55rem;border:1px solid #e8edf2; }
.mh-block-enc__hd { font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-bottom:.45rem; }
.mh-row-2-enc { display:grid;grid-template-columns:1fr 1fr;gap:.45rem; }
.mh-field-enc { margin-bottom:.5rem; }
.mh-field-enc > label { font-size:.66rem;font-weight:700;color:#64748b;display:block;margin-bottom:.2rem; }
/* Variantes de color */
.mh-rb[data-color="blue"]            { border-color:#bfdbfe;color:#3b82f6; }
.mh-rb[data-color="green"]           { border-color:#a7f3d0;color:#10b981; }
.mh-rb[data-color="amber"]           { border-color:#fde68a;color:#d97706; }
.mh-rb[data-color="orange"]          { border-color:#fed7aa;color:#f97316; }
.mh-rb[data-color="red"]             { border-color:#fecaca;color:#ef4444; }
.mh-rb[data-color="purple"]          { border-color:#ddd6fe;color:#8b5cf6; }
.mh-rb[data-color="blue"].active     { border-color:#3b82f6;background:#dbeafe;color:#1e40af; }
.mh-rb[data-color="green"].active    { border-color:#10b981;background:#d1fae5;color:#065f46; }
.mh-rb[data-color="amber"].active    { border-color:#f59e0b;background:#fef3c7;color:#92400e; }
.mh-rb[data-color="orange"].active   { border-color:#f97316;background:#ffedd5;color:#9a3412; }
.mh-rb[data-color="red"].active      { border-color:#ef4444;background:#fee2e2;color:#991b1b; }
.mh-rb[data-color="purple"].active   { border-color:#8b5cf6;background:#ede9fe;color:#5b21b6; }
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
            <div class="mh-block-enc">
                <div class="mh-block-enc__hd">⏰ Puntualidad al ingreso</div>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="blue"   data-field="llegada_puntualidad" data-val="MUY_TEMPRANO" onclick="pickRb(this)">Muy anticipado</button>
                    <button type="button" class="mh-rb" data-color="green"  data-field="llegada_puntualidad" data-val="TEMPRANO"     onclick="pickRb(this)">Con anticipación</button>
                    <button type="button" class="mh-rb" data-color="orange" data-field="llegada_puntualidad" data-val="TARDE"        onclick="pickRb(this)">Retraso leve</button>
                    <button type="button" class="mh-rb" data-color="red"    data-field="llegada_puntualidad" data-val="MUY_TARDE"    onclick="pickRb(this)">Retraso considerable</button>
                </div>
            </div>
            <div class="mh-block-enc">
                <div class="mh-block-enc__hd">🏪 Estado del área al ingreso</div>
                <div class="mh-row-2-enc">
                    <div class="mh-field-enc"><label>¿El área estaba ordenada?</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="green" data-field="area_ordenada_ingreso" data-val="1" onclick="pickRb(this)">Sí</button>
                        <button type="button" class="mh-rb" data-color="red"   data-field="area_ordenada_ingreso" data-val="0" onclick="pickRb(this)">No</button>
                    </div></div>
                    <div class="mh-field-enc"><label>¿El área estaba limpia?</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="green" data-field="area_limpia_ingreso" data-val="1" onclick="pickRb(this)">Sí</button>
                        <button type="button" class="mh-rb" data-color="red"   data-field="area_limpia_ingreso" data-val="0" onclick="pickRb(this)">No</button>
                    </div></div>
                </div>
            </div>
            <div class="mh-block-enc">
                <div class="mh-block-enc__hd">👕 Presentación personal</div>
                <div class="mh-row-2-enc">
                    <div class="mh-field-enc"><label>Higiene personal</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="red"   data-field="aseo_personal" data-val="DEFICIENTE" onclick="pickRb(this)">Deficiente</button>
                        <button type="button" class="mh-rb" data-color="amber" data-field="aseo_personal" data-val="ACEPTABLE"  onclick="pickRb(this)">Aceptable</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="aseo_personal" data-val="OPTIMO"     onclick="pickRb(this)">Óptimo</button>
                    </div></div>
                    <div class="mh-field-enc"><label>Uniforme e indumentaria</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="red"   data-field="vestimenta" data-val="DESCUIDADO"  onclick="pickRb(this)">Descuidado</button>
                        <button type="button" class="mh-rb" data-color="amber" data-field="vestimenta" data-val="PRESENTABLE" onclick="pickRb(this)">Presentable</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="vestimenta" data-val="IMPECABLE"   onclick="pickRb(this)">Impecable</button>
                    </div></div>
                    <div class="mh-field-enc"><label>Estado de uñas</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="red"   data-field="unas" data-val="DESCUIDADAS" onclick="pickRb(this)">Descuidadas</button>
                        <button type="button" class="mh-rb" data-color="amber" data-field="unas" data-val="ACEPTABLES"  onclick="pickRb(this)">Aceptables</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="unas" data-val="CUIDADAS"    onclick="pickRb(this)">Cuidadas</button>
                    </div></div>
                    <div class="mh-field-enc"><label>Presentación del cabello</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="red"   data-field="cabello" data-val="SUELTO"   onclick="pickRb(this)">Suelto</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="cabello" data-val="RECOGIDO" onclick="pickRb(this)">Recogido</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="cabello" data-val="MONO"     onclick="pickRb(this)">Con moño</button>
                    </div></div>
                </div>
            </div>

            <!-- SALIDA -->
            <div class="mh-block-enc">
                <div class="mh-block-enc__hd">⏰ Puntualidad al retiro</div>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="blue"   data-field="salida_puntualidad" data-val="MUY_TEMPRANO" onclick="pickRb(this)">Muy anticipado</button>
                    <button type="button" class="mh-rb" data-color="green"  data-field="salida_puntualidad" data-val="TEMPRANO"     onclick="pickRb(this)">Con anticipación</button>
                    <button type="button" class="mh-rb" data-color="orange" data-field="salida_puntualidad" data-val="TARDE"        onclick="pickRb(this)">Retraso leve</button>
                    <button type="button" class="mh-rb" data-color="red"    data-field="salida_puntualidad" data-val="MUY_TARDE"    onclick="pickRb(this)">Retraso considerable</button>
                </div>
            </div>
            <div class="mh-block-enc">
                <div class="mh-block-enc__hd">🧹 Cierre del turno</div>
                <div class="mh-field-enc"><label>Estado del área de trabajo al cierre</label><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="red"   data-field="estado_area_cierre" data-val="DESCUIDADO"  onclick="pickRb(this)">Descuidado</button>
                    <button type="button" class="mh-rb" data-color="amber" data-field="estado_area_cierre" data-val="PRESENTABLE" onclick="pickRb(this)">Presentable</button>
                    <button type="button" class="mh-rb" data-color="green" data-field="estado_area_cierre" data-val="IMPECABLE"   onclick="pickRb(this)">Impecable</button>
                </div></div>
                <div class="mh-row-2-enc">
                    <div class="mh-field-enc"><label>¿Realizó la limpieza de su área?</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="green" data-field="limpieza_area_cierre" data-val="1" onclick="pickRb(this)">Sí</button>
                        <button type="button" class="mh-rb" data-color="red"   data-field="limpieza_area_cierre" data-val="0" onclick="pickRb(this)">No</button>
                    </div></div>
                    <div class="mh-field-enc"><label>¿Dejó su área ordenada?</label><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="green" data-field="area_ordenada_cierre" data-val="1" onclick="pickRb(this)">Sí</button>
                        <button type="button" class="mh-rb" data-color="red"   data-field="area_ordenada_cierre" data-val="0" onclick="pickRb(this)">No</button>
                    </div></div>
                </div>
                <div class="mh-field-enc"><label>¿Participó en la apertura y/o cierre del local?</label><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="green" data-field="participo_apertura_cierre" data-val="1" onclick="pickRb(this)">Sí</button>
                    <button type="button" class="mh-rb" data-color="red"   data-field="participo_apertura_cierre" data-val="0" onclick="pickRb(this)">No</button>
                </div></div>
            </div>
            <div class="mh-block-enc">
                <div class="mh-block-enc__hd">📊 Evaluación del turno</div>
                <div class="mh-field-enc"><label>Uso del celular personal durante el turno</label><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="green"  data-field="uso_celular" data-val="NO_USO"    onclick="pickRb(this)">No usó</button>
                    <button type="button" class="mh-rb" data-color="amber"  data-field="uso_celular" data-val="OCASIONAL" onclick="pickRb(this)">Uso ocasional</button>
                    <button type="button" class="mh-rb" data-color="red"    data-field="uso_celular" data-val="FRECUENTE" onclick="pickRb(this)">Uso frecuente</button>
                </div></div>
                <div class="mh-field-enc"><label>Calificación general del turno</label><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="red"    data-field="calificacion_turno" data-val="MALO"      onclick="pickRb(this)">Malo</button>
                    <button type="button" class="mh-rb" data-color="orange" data-field="calificacion_turno" data-val="REGULAR"   onclick="pickRb(this)">Regular</button>
                    <button type="button" class="mh-rb" data-color="green"  data-field="calificacion_turno" data-val="BUENO"     onclick="pickRb(this)">Bueno</button>
                    <button type="button" class="mh-rb" data-color="purple" data-field="calificacion_turno" data-val="EXCELENTE" onclick="pickRb(this)">Excelente</button>
                </div></div>
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
