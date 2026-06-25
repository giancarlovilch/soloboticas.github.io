<link rel="stylesheet" href="/public/assets/css/expediente.css">
<?php
// Mapeo de datos maestros y preventivos
$nombre = $p['nombre_completo'] ?? 'Sin Nombre';
$idPostulante = $p['id_postulante'] ?? 0;
$activo = $p['activo'] ?? 0;
$etapaId = $p['etapa_id'] ?? 1;
$generoId = $p['genero_id'] ?? null;
$viviendaId = $p['situacion_vivienda_id'] ?? null;
$puestoId = $p['puesto_id'] ?? null;
$catalogos = $catalogos ?? [];
?>

<div class="expediente-container">
    <form id="formUpdateCompleto">
        <input type="hidden" name="id_postulante" value="<?= $idPostulante ?>">

        <!-- CABECERA -->
        <div class="exp-header">
            <div style="display:flex; align-items:center; gap:1rem;">
                <?php if (!empty($p['foto_url'])): ?>
                    <img src="<?= htmlspecialchars($p['foto_url']) ?>" alt="Foto de <?= htmlspecialchars($nombre) ?>"
                         style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--exp-border, #e2e8f0);">
                <?php else: ?>
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:#e5e7eb;color:#9ca3af;font-size:0.7rem;">Sin foto</span>
                <?php endif; ?>
                <div>
                    <p class="exp-header__kicker">Expediente del Colaborador</p>
                    <h2 class="exp-header__title"><?= htmlspecialchars($nombre) ?></h2>
                </div>
            </div>
            <div style="display:flex; gap:0.75rem;">
                <button type="button" class="btn btn--secondary" onclick="window.location.href='?page=postulantes'">Cancelar</button>
                <button type="submit" class="btn btn--primary">💾 Actualizar Expediente</button>
            </div>
        </div>

        <div class="exp-layout">

            <!-- COLUMNA PRINCIPAL -->
            <div>

                <!-- 1. PERFIL Y DATOS PERSONALES -->
                <div class="exp-section">
                    <h3 class="exp-section__title">
                        <span>👤</span> Perfil y Datos Personales
                    </h3>
                    <div class="exp-form-grid">
                        <div>
                            <label class="exp-field__label">Nombres</label>
                            <input type="text" name="nombres" class="exp-field__input" value="<?= $p['nombres'] ?? '' ?>">
                        </div>
                        <div>
                            <label class="exp-field__label">Apellidos</label>
                            <input type="text" name="apellidos" class="exp-field__input" value="<?= $p['apellidos'] ?? '' ?>">
                        </div>
                        <div class="exp-field--readonly">
                            <label class="exp-field__label">DNI</label>
                            <input type="text" class="exp-field__input" value="<?= $p['num_documento'] ?? '' ?>" readonly>
                        </div>
                        <div>
                            <label class="exp-field__label">Género</label>
                            <select name="genero_id" class="exp-field__select">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($catalogos['generos'] as $gen): ?>
                                    <option value="<?= $gen['id_genero'] ?>" <?= $generoId == $gen['id_genero'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($gen['descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="exp-field__label">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="exp-field__input" value="<?= $p['fecha_nacimiento'] ?? '' ?>">
                        </div>
                        <div>
                            <label class="exp-field__label">Situación de Vivienda</label>
                            <select name="situacion_vivienda_id" class="exp-field__select">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($catalogos['viviendas'] as $viv): ?>
                                    <option value="<?= $viv['id_situacion'] ?>" <?= $viviendaId == $viv['id_situacion'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($viv['descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="exp-field__label">Correo Electrónico</label>
                            <input type="email" name="email" class="exp-field__input" value="<?= $p['email'] ?? '' ?>">
                        </div>
                        <div>
                            <label class="exp-field__label">Teléfono</label>
                            <input type="text" name="telefono" class="exp-field__input" value="<?= $p['telefono'] ?? '' ?>">
                        </div>
                        <div>
                            <label class="exp-field__label">Distrito</label>
                            <input type="text" name="distrito" class="exp-field__input" value="<?= $p['distrito'] ?? '' ?>">
                        </div>
                        <div class="exp-field--full">
                            <label class="exp-field__label">Dirección Exacta</label>
                            <input type="text" name="direccion" class="exp-field__input" value="<?= $p['direccion'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <!-- 2. PUESTOS ASIGNADOS -->
                <div class="exp-section">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                        <h3 class="exp-section__title" style="margin-bottom:0;">
                            <span>🏷️</span> Puestos Asignados (Multifuncional)
                        </h3>
                        <button type="button" onclick="agregarFilaPuesto()" class="exp-btn--add">+ Asignar Puesto</button>
                    </div>
                    <div id="contenedor-puestos" class="exp-list--grid">
                        <?php
                        if (!empty($p['postulaciones'])):
                            foreach ($p['postulaciones'] as $idx => $pos): ?>
                                <div class="exp-row fila-dinamica animate-in">
                                    <select name="puestos[<?= $idx ?>][id_puesto]" class="exp-field__select">
                                        <?php foreach ($catalogos['puestos'] as $cat): ?>
                                            <option value="<?= $cat['id_puesto'] ?>" <?= ($pos['puesto_id'] == $cat['id_puesto']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove">✕</button>
                                </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- 3. HABILIDADES Y COMPETENCIAS -->
                <div class="exp-section">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                        <h3 class="exp-section__title" style="margin-bottom:0;">
                            <span>⚡</span> Habilidades (Skills)
                        </h3>
                        <button type="button" onclick="agregarFilaSkill()" class="exp-btn--add">+ Vincular Habilidad</button>
                    </div>
                    <div id="contenedor-skills" class="exp-list--grid">
                        <?php if (!empty($p['skills'])): foreach ($p['skills'] as $index => $sk): ?>
                            <div class="exp-row fila-dinamica animate-in">
                                <select name="skills[<?= $index ?>][skill_id]" class="exp-field__select" style="flex:2;">
                                    <option value="">-- Seleccionar Habilidad --</option>
                                    <?php foreach ($catalogos['skills'] as $cat): ?>
                                        <option value="<?= $cat['id_skill'] ?>" <?= (isset($sk['skill_id']) && $sk['skill_id'] == $cat['id_skill']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['descripcion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="skills[<?= $index ?>][nivel_id]" class="exp-field__select" style="flex:1;">
                                    <option value="1" <?= (isset($sk['nivel_id']) && $sk['nivel_id'] == 1) ? 'selected' : '' ?>>Básico</option>
                                    <option value="2" <?= (isset($sk['nivel_id']) && $sk['nivel_id'] == 2) ? 'selected' : '' ?>>Intermedio</option>
                                    <option value="3" <?= (isset($sk['nivel_id']) && $sk['nivel_id'] == 3) ? 'selected' : '' ?>>Avanzado</option>
                                </select>
                                <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove">✕</button>
                            </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- 4. EXPERIENCIA LABORAL -->
                <div class="exp-section">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                        <h3 class="exp-section__title" style="margin-bottom:0;">
                            <span>💼</span> Experiencia Laboral
                        </h3>
                        <button type="button" onclick="agregarFilaExperiencia()" class="exp-btn--add">+ Agregar</button>
                    </div>
                    <div id="contenedor-experiencias">
                        <?php if (!empty($p['experiencias'])): foreach ($p['experiencias'] as $index => $exp): ?>
                            <div class="exp-stack-row fila-dinamica animate-in">
                                <div class="exp-stack-grid">
                                    <div>
                                        <label class="exp-field__label">Empresa</label>
                                        <input type="text" name="experiencias[<?= $index ?>][empresa]" placeholder="Empresa" value="<?= $exp['empresa'] ?>" class="exp-field__input">
                                    </div>
                                    <div>
                                        <label class="exp-field__label">Cargo</label>
                                        <input type="text" name="experiencias[<?= $index ?>][cargo]" placeholder="Cargo" value="<?= $exp['cargo'] ?>" class="exp-field__input">
                                    </div>
                                    <div>
                                        <label class="exp-field__label">Periodo (Inicio — Fin)</label>
                                        <div class="exp-dual-inputs">
                                            <input type="date" name="experiencias[<?= $index ?>][fecha_inicio]" value="<?= $exp['fecha_inicio'] ?>" class="exp-field__input">
                                            <input type="date" name="experiencias[<?= $index ?>][fecha_fin]" value="<?= $exp['fecha_fin'] ?>" class="exp-field__input">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove-circle">✕</button>
                            </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- 5. FORMACIÓN ACADÉMICA -->
                <div class="exp-section">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                        <h3 class="exp-section__title" style="margin-bottom:0;">
                            <span>🎓</span> Formación Académica
                        </h3>
                        <button type="button" onclick="agregarFilaEstudio()" class="exp-btn--add">+ Agregar</button>
                    </div>
                    <div id="contenedor-estudios">
                        <?php if (!empty($p['estudios'])): foreach ($p['estudios'] as $index => $est): ?>
                            <div class="exp-stack-row fila-dinamica animate-in">
                                <div class="exp-stack-grid">
                                    <div>
                                        <label class="exp-field__label">Institución</label>
                                        <select name="estudios[<?= $index ?>][institucion_id]" class="exp-field__select">
                                            <option value="">-- Seleccionar --</option>
                                            <?php foreach ($catalogos['instituciones'] as $inst): ?>
                                                <option value="<?= $inst['id_institucion'] ?>" <?= (isset($est['institucion_id']) && $est['institucion_id'] == $inst['id_institucion']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($inst['descripcion']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="exp-field__label">Grado / Tipo</label>
                                        <select name="estudios[<?= $index ?>][tipo_id]" class="exp-field__select">
                                            <?php foreach ($catalogos['tipos_est'] as $tipo): ?>
                                                <option value="<?= $tipo['id_tipo'] ?>" <?= $est['tipo_id'] == $tipo['id_tipo'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tipo['descripcion']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="exp-field__label">Estado</label>
                                        <select name="estudios[<?= $index ?>][estado_id]" class="exp-field__select">
                                            <?php foreach ($catalogos['estados_est'] as $estado): ?>
                                                <option value="<?= $estado['id_estado'] ?>" <?= $est['estado_id'] == $estado['id_estado'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($estado['descripcion']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div style="margin-top:0.75rem; padding-right:2.5rem;">
                                    <label class="exp-field__label">Periodo (Inicio — Fin)</label>
                                    <div class="exp-dual-inputs">
                                        <input type="date" name="estudios[<?= $index ?>][fecha_inicio]" value="<?= $est['fecha_inicio'] ?>" class="exp-field__input">
                                        <input type="date" name="estudios[<?= $index ?>][fecha_fin]" value="<?= $est['fecha_fin'] ?>" class="exp-field__input">
                                    </div>
                                </div>
                                <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove-circle">✕</button>
                            </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>

            </div><!-- /col-main -->

            <!-- COLUMNA DERECHA: PANEL DE CONTROL -->
            <div>
                <div class="exp-sidebar__card--sticky exp-section">
                    <p class="exp-sidebar__title">Logística de Botica</p>

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">TURNO PREFERIDO</label>
                        <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; margin-bottom:0.4rem;">
                            <input type="checkbox" name="turnos[]" value="1" <?= in_array(1, array_column($p['turnos'] ?? [], 'turno_id')) ? 'checked' : '' ?>> Mañana
                        </label>
                        <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem;">
                            <input type="checkbox" name="turnos[]" value="2" <?= in_array(2, array_column($p['turnos'] ?? [], 'turno_id')) ? 'checked' : '' ?>> Tarde
                        </label>
                    </div>

                    <hr style="border:none; border-top:1px solid var(--exp-border); margin:1rem 0;">

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">ETAPA DE PROCESO</label>
                        <select name="etapa_id" class="exp-sidebar__select">
                            <?php foreach ($catalogos['etapas'] as $et): ?>
                                <option value="<?= $et['id_etapa'] ?>" <?= $etapaId == $et['id_etapa'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($et['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">ESTADO DE CUENTA</label>
                        <select name="activo" class="exp-sidebar__select <?= $activo == 1 ? 'exp-sidebar__select--status-active' : 'exp-sidebar__select--status-suspended' ?>">
                            <option value="1" <?= $activo == 1 ? 'selected' : '' ?>>Habilitado</option>
                            <option value="0" <?= $activo == 0 ? 'selected' : '' ?>>Suspendido</option>
                        </select>
                    </div>

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">TIPO DE PERSONAL</label>
                        <select name="tipo_personal" class="exp-sidebar__select">
                            <option value="">— Sin clasificar —</option>
                            <?php foreach ($catalogos['tipos_personal'] ?? [] as $t): ?>
                                <option value="<?= htmlspecialchars($t['codigo']) ?>"
                                    <?= ($p['tipo_personal'] ?? '') === $t['codigo'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['descripcion']) ?> — <?= htmlspecialchars($t['rango']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size:0.65rem;color:#94a3b8;margin-top:0.3rem;">
                            Clasificación según rendimiento operativo
                        </p>
                    </div>

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">FECHA DE INGRESO</label>
                        <input type="date" name="fecha_ingreso" class="exp-field__input"
                               value="<?= htmlspecialchars($p['fecha_ingreso'] ?? '') ?>">
                        <p style="font-size:0.65rem;color:#94a3b8;margin-top:0.3rem;" id="antiguedadSidebar">
                            <?php
                            if (!empty($p['fecha_ingreso'])) {
                                $dt = new DateTime($p['fecha_ingreso']);
                                $diff = $dt->diff(new DateTime());
                                $meses = $diff->y * 12 + $diff->m;
                                echo "Antigüedad: {$meses} meses · Bono S/ " . number_format($meses * 0.20, 2);
                            } else {
                                echo 'Sin fecha registrada';
                            }
                            ?>
                        </p>
                    </div>

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">ROL DE ACCESO</label>
                        <select name="rol_id" class="exp-sidebar__select">
                            <?php foreach ($catalogos['roles'] ?? [] as $rol): ?>
                                <option value="<?= $rol['id_rol'] ?>"
                                    <?= ($p['rol_id'] ?? 2) == $rol['id_rol'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rol['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size:0.65rem;color:#94a3b8;margin-top:0.3rem;">
                            Admin: acceso total · Staff: portal de colaboradores
                        </p>
                    </div>

                    <hr style="border:none; border-top:1px solid var(--exp-border); margin:1rem 0;">

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">NOMBRE DE USUARIO</label>
                        <input
                            type="text"
                            id="nuevoUsername"
                            class="exp-field__input"
                            value="<?= htmlspecialchars($p['username'] ?? '') ?>"
                            placeholder="Ej: MARIAFR"
                            autocomplete="off"
                            style="text-transform:uppercase; margin-bottom:0.5rem;"
                        >
                        <button
                            type="button"
                            class="btn btn--secondary"
                            onclick="cambiarUsername(<?= $idPostulante ?>)"
                            style="width:100%; justify-content:center;"
                        >
                            Actualizar usuario
                        </button>
                        <div id="usernameFeedback" style="font-size:0.65rem; margin-top:0.4rem;"></div>
                    </div>

                    <hr style="border:none; border-top:1px solid var(--exp-border); margin:1rem 0;">

                    <div class="exp-sidebar__group">
                        <label class="exp-sidebar__label">CAMBIAR CONTRASEÑA</label>
                        <input
                            type="password"
                            id="nuevaPassword"
                            class="exp-field__input"
                            placeholder="Nueva contraseña (mín. 6)"
                            autocomplete="new-password"
                            style="margin-bottom:0.5rem;"
                        >
                        <button
                            type="button"
                            class="btn btn--secondary"
                            onclick="cambiarPassword(<?= $idPostulante ?>)"
                            style="width:100%; justify-content:center;"
                        >
                            Actualizar contraseña
                        </button>
                        <p style="font-size:0.65rem; color:var(--exp-text-muted); margin-top:0.4rem;">
                            Si lo dejas vacío, no se modifica.
                        </p>
                        <div id="pwFeedback" style="font-size:0.72rem; margin-top:0.4rem;"></div>
                    </div>
                </div>
            </div>

        </div><!-- /exp-layout -->
    </form>
</div>

<script>
    // 1. Inicialización ÚNICA de contadores (Carga los valores actuales de PHP)
    let puestoCount = <?= count($p['postulaciones'] ?? []) ?>;
    let estCount = <?= count($p['estudios'] ?? []) ?>;
    let skCount = <?= count($p['skills'] ?? []) ?>;
    let expCount = <?= count($p['experiencias'] ?? []) ?>;

    // 2. Función para Puestos (Multifuncional)
    function agregarFilaPuesto() {
        const html = `
        <div class="exp-row fila-dinamica animate-in">
            <select name="puestos[${puestoCount}][id_puesto]" class="exp-field__select">
                <option value="">-- Seleccionar Puesto --</option>
                <?php foreach ($catalogos['puestos'] as $cat): ?>
                    <option value="<?= $cat['id_puesto'] ?>"><?= htmlspecialchars($cat['descripcion']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove">✕</button>
        </div>`;
        document.getElementById('contenedor-puestos').insertAdjacentHTML('beforeend', html);
        puestoCount++;
    }

    // 3. Función para Habilidades
    function agregarFilaSkill() {
        const html = `
        <div class="exp-row fila-dinamica animate-in">
            <select name="skills[${skCount}][skill_id]" class="exp-field__select" style="flex:2;">
                <option value="">-- Seleccionar --</option>
                <?php foreach ($catalogos['skills'] as $cat): ?>
                    <option value="<?= $cat['id_skill'] ?>"><?= htmlspecialchars($cat['descripcion']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="skills[${skCount}][nivel_id]" class="exp-field__select" style="flex:1;">
                <option value="1">Básico</option>
                <option value="2">Intermedio</option>
                <option value="3">Avanzado</option>
            </select>
            <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove">✕</button>
        </div>`;
        document.getElementById('contenedor-skills').insertAdjacentHTML('beforeend', html);
        skCount++;
    }

    // 4. Función para Estudios
    function agregarFilaEstudio() {
        const html = `
        <div class="exp-stack-row fila-dinamica animate-in">
            <div class="exp-stack-grid">
                <div>
                    <label class="exp-field__label">Institución</label>
                    <select name="estudios[${estCount}][institucion_id]" class="exp-field__select">
                        <option value="">-- Institución --</option>
                        <?php foreach ($catalogos['instituciones'] as $inst): ?>
                            <option value="<?= $inst['id_institucion'] ?>"><?= htmlspecialchars($inst['descripcion']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="exp-field__label">Grado / Tipo</label>
                    <select name="estudios[${estCount}][tipo_id]" class="exp-field__select">
                        <?php foreach ($catalogos['tipos_est'] as $tipo): ?>
                            <option value="<?= $tipo['id_tipo'] ?>"><?= htmlspecialchars($tipo['descripcion']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="exp-field__label">Estado</label>
                    <select name="estudios[${estCount}][estado_id]" class="exp-field__select">
                        <?php foreach ($catalogos['estados_est'] as $estado): ?>
                            <option value="<?= $estado['id_estado'] ?>"><?= htmlspecialchars($estado['descripcion']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-top:0.75rem; padding-right:2.5rem;">
                <label class="exp-field__label">Periodo (Inicio — Fin)</label>
                <div class="exp-dual-inputs">
                    <input type="date" name="estudios[${estCount}][fecha_inicio]" class="exp-field__input">
                    <input type="date" name="estudios[${estCount}][fecha_fin]" class="exp-field__input">
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove-circle">✕</button>
        </div>`;
        document.getElementById('contenedor-estudios').insertAdjacentHTML('beforeend', html);
        estCount++;
    }

    // 5. Función para Experiencia
    function agregarFilaExperiencia() {
        const html = `
        <div class="exp-stack-row fila-dinamica animate-in">
            <div class="exp-stack-grid">
                <div>
                    <label class="exp-field__label">Empresa</label>
                    <input type="text" name="experiencias[${expCount}][empresa]" placeholder="Empresa" class="exp-field__input">
                </div>
                <div>
                    <label class="exp-field__label">Cargo</label>
                    <input type="text" name="experiencias[${expCount}][cargo]" placeholder="Cargo" class="exp-field__input">
                </div>
                <div>
                    <label class="exp-field__label">Periodo (Inicio — Fin)</label>
                    <div class="exp-dual-inputs">
                        <input type="date" name="experiencias[${expCount}][fecha_inicio]" class="exp-field__input">
                        <input type="date" name="experiencias[${expCount}][fecha_fin]" class="exp-field__input">
                    </div>
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="exp-btn--remove-circle">✕</button>
        </div>`;
        document.getElementById('contenedor-experiencias').insertAdjacentHTML('beforeend', html);
        expCount++;
    }

    // Feedback visual dinámico del estado de cuenta
    document.querySelector('[name="activo"]')?.addEventListener('change', function() {
        this.className = 'exp-sidebar__select ' +
            (this.value == 1 ? 'exp-sidebar__select--status-active' : 'exp-sidebar__select--status-suspended');
    });

    // Cambio de nombre de usuario por el admin
    async function cambiarUsername(postulante_id) {
        const input    = document.getElementById('nuevoUsername');
        const feedback = document.getElementById('usernameFeedback');
        const val      = input.value.trim().toUpperCase();

        if (!val || val.length < 4) {
            feedback.textContent = 'Mínimo 4 caracteres.';
            feedback.style.color = '#dc3545';
            return;
        }

        feedback.textContent = 'Guardando...';
        feedback.style.color = '#6b7280';

        try {
            const res  = await fetch('/admin/usuario/username', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body:    JSON.stringify({ postulante_id, nuevo_username: val }),
            });
            const data = await res.json();

            if (data.success) {
                feedback.textContent = '✓ Usuario actualizado';
                feedback.style.color = '#0ea472';
                input.value = val;
            } else {
                feedback.textContent = '✗ ' + (data.message || 'Error');
                feedback.style.color = '#dc3545';
            }
        } catch {
            feedback.textContent = '✗ Error de conexión';
            feedback.style.color = '#dc3545';
        }
    }

    // Cambio de contraseña por el admin
    async function cambiarPassword(postulante_id) {
        const input    = document.getElementById('nuevaPassword');
        const feedback = document.getElementById('pwFeedback');
        const pw       = input.value.trim();

        if (!pw) { feedback.textContent = 'Ingresa una contraseña.'; feedback.style.color = '#dc3545'; return; }
        if (pw.length < 6) { feedback.textContent = 'Mínimo 6 caracteres.'; feedback.style.color = '#dc3545'; return; }

        feedback.textContent = 'Guardando...';
        feedback.style.color = '#6b7280';

        try {
            const res  = await fetch('/admin/usuario/password', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body:    JSON.stringify({ postulante_id, nueva_password: pw }),
            });
            const data = await res.json();

            if (data.success) {
                feedback.textContent = '✓ Contraseña actualizada';
                feedback.style.color = '#0ea472';
                input.value = '';
            } else {
                feedback.textContent = '✗ ' + (data.message || 'Error al actualizar');
                feedback.style.color = '#dc3545';
            }
        } catch {
            feedback.textContent = '✗ Error de conexión';
            feedback.style.color = '#dc3545';
        }
    }
</script>