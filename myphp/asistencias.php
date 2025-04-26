<?php
session_start();
include('db_connection.php');
include('session_manager.php');

// Obtener el nickname del usuario logeado
$nickname = $_SESSION['nickname'];

// Obtener información del usuario actual
$usuario = obtenerInformacionUsuario($nickname);

// Configuración de fechas
$fechaActual = new DateTime();
$mesActual = $fechaActual->format('m');
$anioActual = $fechaActual->format('Y');

// Obtener el mes y año seleccionados (si los hay)
$mesSeleccionado = isset($_GET['mes']) ? intval($_GET['mes']) : $mesActual;
$anioSeleccionado = isset($_GET['anio']) ? intval($_GET['anio']) : $anioActual;

// Validar mes y año
if ($mesSeleccionado < 1 || $mesSeleccionado > 12) {
    $mesSeleccionado = $mesActual;
}
if ($anioSeleccionado < 2020 || $anioSeleccionado > ($anioActual + 1)) {
    $anioSeleccionado = $anioActual;
}

// Verificar si el mes está bloqueado (más de 2 meses atrás o declarado)
$mesBloqueado = esMesBloqueado($nickname, $mesSeleccionado, $anioSeleccionado);

// Procesar formularios (registro/edición) solo si el mes no está bloqueado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$mesBloqueado) {
    if (isset($_POST['registrar_asistencia'])) {
        procesarRegistroAsistencia($nickname);
    } elseif (isset($_POST['editar_asistencia'])) {
        procesarEdicionAsistencia($nickname);
    } elseif (isset($_POST['declarar_mes'])) {
        declararMes($nickname, $mesSeleccionado, $anioSeleccionado);
        $mesBloqueado = true; // Actualizar estado después de declarar
    }
}

// Obtener asistencias del mes seleccionado
$asistencias = obtenerAsistenciasMes($nickname, $mesSeleccionado, $anioSeleccionado);



// Calcular resumen del mes
$resumenMes = calcularResumenMes($asistencias, $nickname, $mesSeleccionado, $anioSeleccionado);

// Función para verificar si un mes está bloqueado
function esMesBloqueado($nickname, $mes, $anio)
{
    global $pdo;

    // Verificar si es más de 2 meses atrás
    $fechaConsulta = new DateTime("$anio-$mes-01");
    $fechaActual = new DateTime();
    $diferencia = $fechaActual->diff($fechaConsulta);
    $mesesDiferencia = $diferencia->y * 12 + $diferencia->m;

    if ($mesesDiferencia > 1) {
        return true;
    }

    // Verificar si el mes fue declarado
    $stmt = $pdo->prepare("SELECT id FROM meses_bloqueados WHERE nickname = ? AND mes = ? AND anio = ?");
    $stmt->execute([$nickname, $mes, $anio]);
    return (bool)$stmt->fetch();
}

// Función para declarar un mes (marcar como no editable)
function declararMes($nickname, $mes, $anio)
{
    global $pdo;

    // Verificar si ya está declarado
    $stmt = $pdo->prepare("SELECT id FROM meses_bloqueados WHERE nickname = ? AND mes = ? AND anio = ?");
    $stmt->execute([$nickname, $mes, $anio]);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO meses_bloqueados (nickname, mes, anio) VALUES (?, ?, ?)");
        $stmt->execute([$nickname, $mes, $anio]);
        $_SESSION['exito'] = "Mes declarado correctamente. Ya no podrás realizar cambios.";
    }
}

// Función para obtener información del usuario
function obtenerInformacionUsuario($nickname)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM informacion_personal WHERE nickname = ?");
    $stmt->execute([$nickname]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para procesar registro de asistencia
function procesarRegistroAsistencia($nickname)
{
    global $pdo;

    $fecha = $_POST['fecha'];
    $hoy = new DateTime();
    $fechaRegistro = new DateTime($fecha);
    $horaEntrada = $_POST['hora_entrada'];
    $horaSalida = $_POST['hora_salida'];
    $comentarios = $_POST['comentarios'] ?? '';

    if ($fechaRegistro > $hoy) {
        $_SESSION['error'] = "No puedes registrar asistencias para fechas futuras.";
        return;
    }

    // Validar que no exista ya un registro para esa fecha
    $stmt = $pdo->prepare("SELECT id FROM registros_asistencia WHERE nickname = ? AND fecha = ?");
    $stmt->execute([$nickname, $fecha]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Ya existe un registro de asistencia para esta fecha.";
        return;
    }

    // Calcular tardanza (ejemplo: si llega después de las 8:00 AM)
    $horaReferencia = new DateTime($fecha . ' 08:00:00');
    $horaEntradaObj = new DateTime($fecha . ' ' . $horaEntrada);
    $tardanza = $_POST['tardanza'] ?? 0; // Tardanza en minutos (ingresada manualmente)

    $stmt = $pdo->prepare("INSERT INTO registros_asistencia 
                      (nickname, fecha, hora_entrada, hora_salida, tardanza_minutos, estado) 
                      VALUES (?, ?, ?, ?, ?, 'Pendiente')");
    $stmt->execute([$nickname, $fecha, $horaEntrada, $horaSalida, $tardanza]);

    $_SESSION['exito'] = "Asistencia registrada correctamente.";
}

// Función para procesar edición de asistencia
function procesarEdicionAsistencia($nickname)
{
    global $pdo;

    $id = $_POST['id'];
    $fecha = $_POST['fecha'];
    $horaEntrada = $_POST['hora_entrada'];
    $horaSalida = $_POST['hora_salida'];
    $comentarios = $_POST['comentarios'] ?? '';

    // Verificar que el registro pertenezca al usuario
    $stmt = $pdo->prepare("SELECT id FROM registros_asistencia WHERE id = ? AND nickname = ?");
    $stmt->execute([$id, $nickname]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "No tienes permiso para editar este registro.";
        return;
    }

    // Calcular nueva tardanza
    $horaReferencia = new DateTime($fecha . ' 08:00:00');
    $horaEntradaObj = new DateTime($fecha . ' ' . $horaEntrada);
    $tardanza = $_POST['tardanza'] ?? 0; // Tardanza en minutos (ingresada manualmente)

    $stmt = $pdo->prepare("UPDATE registros_asistencia 
                      SET hora_entrada = ?, hora_salida = ?, tardanza_minutos = ?, estado = 'Pendiente', fecha_modificacion = NOW() 
                      WHERE id = ?");
    $stmt->execute([$horaEntrada, $horaSalida, $tardanza, $id]);

    $_SESSION['exito'] = "Asistencia actualizada correctamente.";
}

// Función para obtener asistencias del mes
function obtenerAsistenciasMes($nickname, $mes, $anio)
{
    global $pdo;

    $fechaInicio = "$anio-$mes-01";
    $fechaFin = date("Y-m-t", strtotime($fechaInicio));

    $stmt = $pdo->prepare("SELECT * FROM registros_asistencia 
                          WHERE nickname = ? AND fecha BETWEEN ? AND ? 
                          ORDER BY fecha ASC");
    $stmt->execute([$nickname, $fechaInicio, $fechaFin]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// En la función calcularResumenMes, agregar el cálculo de turnos
function calcularResumenMes($asistencias, $nickname, $mes, $anio)
{
    $resumen = [
        'horas_trabajadas' => 0,
        'total_tardanza' => 0,
        'turnos_trabajados' => 0
    ];

    foreach ($asistencias as $asistencia) {
        if ($asistencia['estado'] != 'Rechazado') {
            $resumen['total_tardanza'] += $asistencia['tardanza_minutos'];

            // Calcular horas trabajadas
            $horaEntrada = new DateTime($asistencia['fecha'] . ' ' . $asistencia['hora_entrada']);
            $horaSalida = new DateTime($asistencia['fecha'] . ' ' . $asistencia['hora_salida']);

            if ($horaSalida < $horaEntrada) {
                $horaSalida->modify('+1 day');
            }

            $diferencia = $horaEntrada->diff($horaSalida);
            $horasTrabajadas = $diferencia->h + ($diferencia->i / 60);
            $resumen['horas_trabajadas'] += $horasTrabajadas;
        }
    }

    // Calcular turnos (8 horas = 1 turno)
    $resumen['turnos_trabajados'] = $resumen['horas_trabajadas'] / 8;

    return $resumen;
}

// Función para contar días laborables en un mes (lunes a viernes)
function contarDiasLaborables($mes, $anio)
{
    return cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
}

// Función para obtener nombre del mes en español
function obtenerNombreMes($mes)
{
    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    return $meses[$mes];
}

// Función para obtener nombre del día en español
function obtenerNombreDia($fecha)
{
    $dias = [
        'Mon' => 'L',
        'Tue' => 'M',
        'Wed' => 'M',
        'Thu' => 'J',
        'Fri' => 'V',
        'Sat' => 'S',
        'Sun' => 'D'
    ];
    $diaIngles = date('D', strtotime($fecha));
    return $dias[$diaIngles] ?? $diaIngles;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencias - <?= htmlspecialchars($usuario['nombre_completo']) ?></title>
    <!-- <link rel="stylesheet" href="../css/normalize.css"> -->
    <link rel="stylesheet" href="../css/asistencia.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos generales mejorados */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Estilo para el título principal */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin: 0;
            position: relative;
            padding-left: 20px;
        }

        .page-title:before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 80%;
            background-color: #3498db;
            border-radius: 3px;
        }

        .user-info {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        /* Selector de mes/año mejorado */
        .month-selector-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .month-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .month-selector select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 0.9rem;
            min-width: 120px;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 14px;
        }

        .month-selector button {
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }

        .month-selector button:hover {
            background-color: #2980b9;
        }

        .month-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .btn-declare {
            background-color: #2196F3;
        }

        .btn-unlock {
            background-color: #ff9800;
        }

        .declared-message {
            font-size: 0.85rem;
            color: #e74c3c;
            margin-left: auto;
            padding: 8px 12px;
            background-color: #fdecea;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-button {
            padding: 8px 26px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s;
            margin-right: 15px;
        }

        .back-button:hover {
            background-color: #5a6268;
            color: white;
        }

        .page-header {
            display: flex;
            justify-content: flex-start;
            /* Cambiado de space-between a flex-start */
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-title {
            margin: 0;
            position: relative;
            padding-left: 20px;
            flex-grow: 1;
            /* Para que el título ocupe el espacio restante */
        }

        /* Resto de tus estilos existentes (attendance-list, summary-card, etc.) */
        /* ... (mantener los estilos existentes pero puedes aplicar mejoras similares) ... */
    </style>
</head>

<body>
    <main class="container">
        <!-- Encabezado mejorado -->
        <div class="page-header">
            <a href="javascript:history.back()" class="back-button">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h1 class="page-title">
                <i class="fas fa-calendar-check" style="margin-right: 10px;"></i>
                Registro de Asistencias
            </h1>
            <div class="user-info">
                <i class="fas fa-user"></i> <?= htmlspecialchars($usuario['nombre_completo']) ?>
            </div>
        </div>

        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-success"><?= $_SESSION['exito'] ?></div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Selector de mes/año mejorado -->
        <div class="month-selector-container">
            <form method="get" class="month-selector">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-calendar-alt" style="color: #3498db;"></i>
                    <select name="mes">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $mesSeleccionado ? 'selected' : '' ?>>
                                <?= obtenerNombreMes($m) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="anio">
                        <?php for ($y = $anioActual - 1; $y <= $anioActual + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $anioSeleccionado ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit">
                        <i class="fas fa-search"></i> Mostrar
                    </button>
                </div>

                <div class="month-actions">
                    <?php if (!$mesBloqueado): ?>
                        <button type="button" onclick="confirmarDeclaracion()" class="btn-declare">
                            <i class="fas fa-lock"></i> Declarar Mes
                        </button>
                    <?php else: ?>
                        <button type="button" onclick="mostrarModalDesbloqueo()" class="btn-unlock">
                            <i class="fas fa-unlock"></i> Desbloquear Mes
                        </button>
                        <div class="declared-message">
                            <i class="fas fa-info-circle"></i> Bloqueado
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Modal para desbloquear mes -->
        <!-- Modal para desbloquear mes -->
        <div id="modal-desbloqueo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
            <div style="background-color: white; padding: 25px; border-radius: 8px; width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <i class="fas fa-unlock-alt"></i> Desbloquear Mes
                </h3>
                <p style="margin-bottom: 15px;">Ingrese la contraseña para permitir ediciones:</p>
                <input type="password" id="password-desbloqueo" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px;">
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button onclick="ocultarModalDesbloqueo()" style="padding: 8px 16px; background-color: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button onclick="desbloquearMes()" style="padding: 8px 16px; background-color: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-check"></i> Desbloquear
                    </button>
                </div>
            </div>
        </div>

        <!-- Resumen del mes -->
        <!-- Resumen del mes - diseño mejorado -->
        <!-- Modificar el resumen para mostrar los 3 elementos en la misma línea -->
        <div class="summary-card" style="background-color: #f8f9fa; border-radius: 8px; padding: 15px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;">Resumen del Mes</h2>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                <div style="background-color: white; padding: 10px; border-radius: 5px;">
                    <p style="margin: 0; font-size: 0.9em; color: #7f8c8d;">Horas trabajadas</p>
                    <p style="margin: 5px 0 0; font-size: 1.2em; font-weight: bold; color: #2980b9;"><?= number_format($resumenMes['horas_trabajadas'], 2) ?></p>
                </div>

                <div style="background-color: white; padding: 10px; border-radius: 5px;">
                    <p style="margin: 0; font-size: 0.9em; color: #7f8c8d;">Turnos trabajados</p>
                    <p style="margin: 5px 0 0; font-size: 1.2em; font-weight: bold; color: #27ae60;"><?= number_format($resumenMes['turnos_trabajados'], 2) ?></p>
                </div>

                <div style="background-color: white; padding: 10px; border-radius: 5px;">
                    <p style="margin: 0; font-size: 0.9em; color: #7f8c8d;">Tardanza total</p>
                    <p style="margin: 5px 0 0; font-size: 1.2em; font-weight: bold; color: #e74c3c;">
                        <?= floor($resumenMes['total_tardanza'] / 60) ?>h <?= $resumenMes['total_tardanza'] % 60 ?>m
                    </p>
                </div>
            </div>
        </div>

        <!-- Lista de asistencias -->
        <!-- Lista de asistencias -->
        <div style="overflow-x: auto;">

            <table class="attendance-list">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Día</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Tardanza (min)</th>
                        <th>Última Modificación</th> <!-- Nueva columna -->
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $diasMes = date('t', strtotime("$anioSeleccionado-$mesSeleccionado-01"));
                    $hoy = date('Y-m-d');

                    for ($dia = 1; $dia <= $diasMes; $dia++) {
                        $fecha = sprintf("%04d-%02d-%02d", $anioSeleccionado, $mesSeleccionado, $dia);
                        $nombreDia = obtenerNombreDia($fecha);
                        $esHoy = ($fecha == $hoy);
                        $esFuturo = ($fecha > $hoy);

                        // Buscar asistencia para este día
                        $asistenciaDia = null;
                        foreach ($asistencias as $asistencia) {
                            if ($asistencia['fecha'] == $fecha) {
                                $asistenciaDia = $asistencia;
                                break;
                            }
                        }

                        $claseFila = $mesBloqueado ? 'locked-row' : '';
                        if ($esHoy) {
                            $claseFila .= ' current-day';
                        }

                        echo '<tr class="' . trim($claseFila) . '">';
                        echo '<td>' . $fecha . '</td>';
                        echo '<td>' . $nombreDia . '</td>';

                        if ($asistenciaDia) {
                            if (!$mesBloqueado && !$esFuturo) {
                                echo '<td><input type="time" class="editable-time" value="' . substr($asistenciaDia['hora_entrada'], 0, 5) . '" data-id="' . $asistenciaDia['id'] . '" data-field="hora_entrada"></td>';
                                echo '<td><input type="time" class="editable-time" value="' . substr($asistenciaDia['hora_salida'], 0, 5) . '" data-id="' . $asistenciaDia['id'] . '" data-field="hora_salida"></td>';
                                echo '<td><input type="number" class="editable-number" value="' . $asistenciaDia['tardanza_minutos'] . '" data-id="' . $asistenciaDia['id'] . '" data-field="tardanza_minutos" min="0"></td>';
                                echo '<td class="modification-date">' . ($asistenciaDia['fecha_registro'] ? date('d/m/Y H:i', strtotime($asistenciaDia['fecha_registro'])) : '<span style="color: #999;">Nunca</span>') . '</td>';
                                echo '<td class="action-buttons">';
                                echo '<button class="delete-btn" onclick="confirmarEliminacion(' . $asistenciaDia['id'] . ')">Eliminar</button>';
                                echo '</td>';
                            } else {
                                echo '<td>' . substr($asistenciaDia['hora_entrada'], 0, 5) . '</td>';
                                echo '<td>' . substr($asistenciaDia['hora_salida'], 0, 5) . '</td>';
                                echo '<td>' . $asistenciaDia['tardanza_minutos'] . ' min</td>';
                                echo '<td>' . ($asistenciaDia['fecha_registro'] ? date('d/m/Y H:i', strtotime($asistenciaDia['fecha_registro'])) : 'Nunca') . '</td>';
                                echo '<td>-</td>';
                            }
                        } else {
                            // Para días sin registro
                            if (!$mesBloqueado && !$esFuturo) {
                                echo '<td colspan="5">'; // Aumentar el colspan a 5
                                echo '<button class="add-btn" onclick="registrarAsistencia(\'' . $fecha . '\')">Registrar</button>';
                                echo '</td>';
                                
                            } else {
                                echo '<td colspan="6">...' . ($esFuturo ? '...' : '') . '</td>'; // Aumentar el colspan a 6
                            }
                        }

                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>


        <!-- Formulario flotante para registro/edición -->
        <div id="attendance-form-container" class="form-container" style="display: none;">
            <h2 id="form-title">Registrar Asistencia</h2>
            <form id="attendance-form" method="post">
                <input type="hidden" id="asistencia-id" name="id">
                <input type="hidden" id="asistencia-fecha" name="fecha">

                <div class="form-group">
                    <label for="hora_entrada">Hora de Entrada:</label>
                    <input type="time" id="hora_entrada" name="hora_entrada" required>
                </div>

                <div class="form-group">
                    <label for="hora_salida">Hora de Salida:</label>
                    <input type="time" id="hora_salida" name="hora_salida" required>
                </div>

                <div class="form-group">
                    <label for="comentarios">Comentarios:</label>
                    <textarea id="comentarios" name="comentarios" rows="3"></textarea>
                </div>

                <button type="submit" name="registrar_asistencia" id="submit-button">Registrar</button>
                <button type="button" onclick="ocultarFormulario()">Cancelar</button>
            </form>
        </div>
        <?php if (!$mesBloqueado): ?>
            <div style="text-align: center; margin: 20px 0;">
                <button id="guardar-cambios" style="padding: 10px 20px; background-color: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; transition: background-color 0.2s;">
                    <i class="fas fa-save"></i> Guardar Todos los Cambios
                </button>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Registrar una nueva asistencia
        function registrarAsistencia(fecha) {
            const horaEntrada = '07:00';
            const horaSalida = '15:00';

            fetch('registrar_asistencia.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `fecha=${fecha}&hora_entrada=${horaEntrada}&hora_salida=${horaSalida}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Recargar para ver los cambios
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        // Manejar cambios en los campos editables
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.editable-time, .editable-text').forEach(input => {
                input.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const field = this.dataset.field;
                    const value = this.value;

                    fetch('actualizar_asistencia.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `id=${id}&field=${field}&value=${encodeURIComponent(value)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Actualizar visualmente si es necesario
                                if (field === 'hora_entrada') {
                                    // Recalcular tardanza si cambia la hora de entrada
                                    location.reload();
                                }
                            } else {
                                alert('Error: ' + data.message);
                                this.value = this.defaultValue;
                            }
                        });
                });
            });

            // Resaltar el día actual
            const hoy = new Date().toISOString().split('T')[0];
            document.querySelectorAll('tbody tr').forEach(row => {
                const fecha = row.querySelector('td:first-child').textContent;
                if (fecha === hoy) {
                    row.classList.add('current-day');
                }
            });
        });

        // Confirmar eliminación (se mantiene igual)
        function confirmarEliminacion(id) {
            window.location.href = 'eliminar_asistencia.php?id=' + id;
        }

        // Confirmar declaración del mes (se mantiene igual)
        function confirmarDeclaracion() {
            if (confirm('¿Estás seguro de declarar este mes?')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'declarar_mes';
                input.value = '1';

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        // Agregar este código para manejar el guardado general
        document.getElementById('guardar-cambios')?.addEventListener('click', function() {
            const cambios = [];

            document.querySelectorAll('.editable-time, .editable-number').forEach(input => {
                cambios.push({
                    id: input.dataset.id,
                    field: input.dataset.field,
                    value: input.value
                });
            });

            if (cambios.length > 0) {
                fetch('guardar_cambios_masivos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            cambios: cambios
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Error al guardar: ' + data.message);
                        }
                        location.reload(); // Recargar siempre, tanto en éxito como en error
                    });
            } else {
                alert('No hay cambios para guardar');
            }
        });

        // Eliminar el evento change individual si prefieres el guardado manual
        // document.querySelectorAll('.editable-time, .editable-number').forEach(input => {
        //     input.addEventListener('change', ...);
        // });
        // Funciones para manejar el desbloqueo
        function mostrarModalDesbloqueo() {
            document.getElementById('modal-desbloqueo').style.display = 'flex';
        }

        function ocultarModalDesbloqueo() {
            document.getElementById('modal-desbloqueo').style.display = 'none';
        }

        function desbloquearMes() {
            const password = document.getElementById('password-desbloqueo').value;
            const passwordCorrecta = "a20102552A";

            if (password === passwordCorrecta) {
                // Eliminar el bloqueo del mes
                fetch('desbloquear_mes.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `mes=<?= $mesSeleccionado ?>&anio=<?= $anioSeleccionado ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            } else {
                alert('Contraseña incorrecta');
            }
        }
    </script>
</body>

</html>