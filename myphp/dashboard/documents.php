<?php
include('db_connection.php');
include('session_manager.php');

// Verificar sesión
if (!isset($_SESSION['nickname'])) {
    header('Location: login.php');
    exit();
}

// Obtener información del usuario
$nickname = $_SESSION['nickname'];
$mensaje = '';

// Verificar rol (no restringimos por rol según tu requerimiento)
$stmt = $pdo->prepare("SELECT rol FROM informacion_personal WHERE nickname = :nickname");
$stmt->bindParam(':nickname', $nickname);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['asignar_tarea'])) {
        // Asignar tarea al usuario
        $id_tarea = $_POST['id_tarea'];

        try {
            $stmt = $pdo->prepare("UPDATE tareas SET estado = 'Asignada', porcentaje_completado = 10 WHERE id = :id AND estado = 'Pendiente'");
            $stmt->bindParam(':id', $id_tarea);
            $stmt->execute();

            // Registrar en el historial
            $stmt = $pdo->prepare("INSERT INTO historial_tareas (id_tarea, nickname, accion, descripcion) 
                                  VALUES (:id_tarea, :nickname, 'Asignación', 'Tarea asignada al usuario')");
            $stmt->bindParam(':id_tarea', $id_tarea);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->execute();

            $mensaje = "Tarea asignada correctamente";
        } catch (PDOException $e) {
            $mensaje = "Error al asignar tarea: " . $e->getMessage();
        }
    } elseif (isset($_POST['completar_tarea'])) {
        // Marcar tarea como completada
        $id_tarea = $_POST['id_tarea'];

        try {
            $stmt = $pdo->prepare("UPDATE tareas SET estado = 'Completada', porcentaje_completado = 100 WHERE id = :id AND (estado = 'Asignada' OR estado = 'En Progreso')");
            $stmt->bindParam(':id', $id_tarea);
            $stmt->execute();

            // Registrar en el historial
            $stmt = $pdo->prepare("INSERT INTO historial_tareas (id_tarea, nickname, accion, descripcion) 
                                  VALUES (:id_tarea, :nickname, 'Completación', 'Tarea completada')");
            $stmt->bindParam(':id_tarea', $id_tarea);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->execute();

            $mensaje = "Tarea marcada como completada";
        } catch (PDOException $e) {
            $mensaje = "Error al completar tarea: " . $e->getMessage();
        }
    } elseif (isset($_POST['crear_tarea'])) {
        // Crear nueva tarea
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $id_categoria = $_POST['id_categoria'];
        $id_local = $_POST['id_local'];
        $fecha_vencimiento = $_POST['fecha_vencimiento'];
        $prioridad = $_POST['prioridad'];
        $presupuesto = $_POST['presupuesto'];

        try {
            // Generar código único para la tarea (ejemplo: LOCAL-001)
            $stmt = $pdo->prepare("SELECT COUNT(*) + 1 as next_num FROM tareas WHERE id_local = :id_local");
            $stmt->bindParam(':id_local', $id_local);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_num = str_pad($result['next_num'], 3, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("SELECT nombre FROM locales WHERE id = :id_local");
            $stmt->bindParam(':id_local', $id_local);
            $stmt->execute();
            $local = $stmt->fetch(PDO::FETCH_ASSOC);
            $prefijo = substr(strtoupper($local['nombre']), 0, 2);

            // Insertar nueva tarea
            $stmt = $pdo->prepare("INSERT INTO tareas (
                titulo, descripcion, id_categoria, id_local, 
                fecha_inicio, fecha_vencimiento,  
                prioridad, presupuesto, estado
            ) VALUES (
                :titulo, :descripcion, :id_categoria, :id_local, 
                CURDATE(), :fecha_vencimiento,  
                :prioridad, :presupuesto, 'Pendiente'
            )");

            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':id_categoria', $id_categoria);
            $stmt->bindParam(':id_local', $id_local);
            $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $stmt->bindParam(':prioridad', $prioridad);
            $stmt->bindParam(':presupuesto', $presupuesto);
            $stmt->execute();

            $id_tarea = $pdo->lastInsertId();

            // Registrar en el historial
            $stmt = $pdo->prepare("INSERT INTO historial_tareas (id_tarea, nickname, accion, descripcion) 
                                  VALUES (:id_tarea, :nickname, 'Creación', 'Nueva tarea creada')");
            $stmt->bindParam(':id_tarea', $id_tarea);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->execute();

            $mensaje = "Tarea creada correctamente";
        } catch (PDOException $e) {
            $mensaje = "Error al crear tarea: " . $e->getMessage();
        }
    } elseif (isset($_POST['actualizar_progreso'])) {
        // Actualizar progreso de tarea
        $id_tarea = $_POST['id_tarea'];
        $porcentaje = $_POST['porcentaje'];

        try {
            // Determinar estado basado en porcentaje
            $estado = 'En Progreso';
            if ($porcentaje >= 100) $estado = 'Completada';

            $stmt = $pdo->prepare("UPDATE tareas 
                                 SET porcentaje_completado = :porcentaje, estado = :estado 
                                 WHERE id = :id AND (estado = 'Asignada' OR estado = 'En Progreso')");
            $stmt->bindParam(':porcentaje', $porcentaje);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id_tarea);
            $stmt->execute();

            // Registrar en el historial
            $stmt = $pdo->prepare("INSERT INTO historial_tareas (id_tarea, nickname, accion, descripcion) 
                                  VALUES (:id_tarea, :nickname, 'Progreso', 'Progreso actualizado a $porcentaje%')");
            $stmt->bindParam(':id_tarea', $id_tarea);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->execute();

            $mensaje = "Progreso de tarea actualizado";
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar progreso: " . $e->getMessage();
        }
    }
}

// Obtener listas para formularios
$categorias = $pdo->query("SELECT * FROM categorias_tareas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$locales = $pdo->query("SELECT * FROM locales WHERE estado = 'Activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener tareas ordenadas por fecha de vencimiento (más próximas primero)
$tareas = $pdo->query("
    SELECT t.*, c.nombre as categoria, l.nombre as local_nombre,
           (SELECT h.nickname FROM historial_tareas h 
            WHERE h.id_tarea = t.id AND h.accion = 'Asignación' 
            ORDER BY h.fecha_registro DESC LIMIT 1) as asignado_a
    FROM tareas t
    JOIN categorias_tareas c ON t.id_categoria = c.id
    JOIN locales l ON t.id_local = l.id
    WHERE t.estado NOT IN ('Completada', 'Vencida')
    ORDER BY t.fecha_vencimiento ASC, t.prioridad DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener tareas asignadas al usuario actual
$mis_tareas = $pdo->prepare("
    SELECT t.*, c.nombre as categoria, l.nombre as local_nombre
    FROM tareas t
    JOIN categorias_tareas c ON t.id_categoria = c.id
    JOIN locales l ON t.id_local = l.id
    WHERE t.estado IN ('Asignada', 'En Progreso')
    AND EXISTS (
        SELECT 1 FROM historial_tareas h 
        WHERE h.id_tarea = t.id 
        AND h.nickname = :nickname 
        AND h.accion = 'Asignación'
    )
    ORDER BY t.fecha_vencimiento ASC
");
$mis_tareas->bindParam(':nickname', $nickname);
$mis_tareas->execute();
$mis_tareas = $mis_tareas->fetchAll(PDO::FETCH_ASSOC);

// Obtener tareas completadas recientemente
$tareas_completadas = $pdo->query("
    SELECT t.*, c.nombre as categoria, l.nombre as local_nombre
    FROM tareas t
    JOIN categorias_tareas c ON t.id_categoria = c.id
    JOIN locales l ON t.id_local = l.id
    WHERE t.estado = 'Completada'
    ORDER BY t.fecha_actualizacion DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación SB - Gestión de Tareas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Krub', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .task-list {
            list-style: none;
            padding: 0;
        }

        .task-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff;
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .task-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .task-code {
            background-color: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: monospace;
        }

        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }

        .task-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .task-priority {
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }

        .priority-high {
            background-color: #ffdddd;
            color: #d32f2f;
        }

        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }

        .priority-low {
            background-color: #d4edda;
            color: #155724;
        }

        .task-description {
            margin-bottom: 15px;
            padding-top: 1rem;
        }

        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .progress-container {
            width: 100%;
            margin: 10px 0;
        }

        .progress-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: #28a745;
            width: 0%;
            transition: width 0.3s;
        }

        .progress-text {
            text-align: center;
            font-size: 12px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-col {
            flex: 1;
        }

        .hidden {
            display: none;
        }

        .toggle-form {
            margin-bottom: 20px;
            text-align: center;
        }

        .tab-container {
            display: flex;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }

        .tab.active {
            background-color: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }

        .config-title {
            color: #2c3e50;
            /* margin-bottom: 20px; */
            font-size: 1.5rem;
            text-align: center;
            margin: 0 0 20px;
        }

        .status-pendiente {
            background-color: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .status-asignada {
            background-color: #cce5ff;
            color: #004085;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .status-en-progreso {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .status-completada {
            background-color: #d4edda;
            color: #155724;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .status-vencida {
            background-color: #f8d7da;
            color: #721c24;
            padding: 3px 8px;
            border-radius: 4px;
        }
    </style>
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="config-title">Gestión de Tareas</h1>
        </div>

        <?php if ($mensaje): ?>
            <div class="message <?php echo strpos($mensaje, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div class="tab-container">
            <div class="tab active" onclick="showTab('tareas-disponibles')">Tareas Disponibles</div>
            <div class="tab" onclick="showTab('mis-tareas')">Nuestras Tareas</div>
            <div class="tab" onclick="showTab('crear-tarea')">Crear Nueva Tarea</div>
        </div>

        <!-- Tareas Disponibles -->
        <div id="tareas-disponibles" class="tab-content">
            <div class="section">
                <h2 class="section-title">Tareas Disponibles</h2>

                <?php if (empty($tareas)): ?>
                    <p>No hay tareas disponibles en este momento.</p>
                <?php else: ?>
                    <ul class="task-list">
                        <?php foreach ($tareas as $tarea): ?>
                            <li class="task-item <?php echo $tarea['estado']; ?>">
                                <div class="task-header">
                                    <h3 class="task-title"><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                                    <span class="task-status status-<?php echo strtolower(str_replace(' ', '-', $tarea['estado'])); ?>">
                                        <?php echo $tarea['estado']; ?>
                                    </span>
                                </div>

                                <div class="task-meta">
                                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($tarea['categoria']); ?></span>
                                    <span><i class="fas fa-store"></i> <?php echo htmlspecialchars($tarea['local_nombre']); ?></span>
                                    <span><i class="fas fa-calendar-alt"></i> Vence: <?php echo date('d/m/Y', strtotime($tarea['fecha_vencimiento'])); ?></span>
                                    <span class="task-priority <?php echo 'priority-' . strtolower($tarea['prioridad']); ?>">
                                        <i class="fas fa-exclamation-circle"></i> <?php echo $tarea['prioridad']; ?>
                                    </span>
                                    <span><i class="fas fa-money-bill-wave"></i> S/ <?php echo number_format($tarea['presupuesto'], 2); ?></span>
                                </div>

                                <?php if (!empty($tarea['descripcion'])): ?>
                                    <div class="task-description">
                                        <?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="task-footer">
                                    <form method="POST">
                                        <input type="hidden" name="id_tarea" value="<?php echo $tarea['id']; ?>">
                                        <?php if ($tarea['estado'] === 'Pendiente'): ?>
                                            <button type="submit" name="asignar_tarea" class="btn btn-primary">
                                                <i class="fas fa-user-plus"></i> Asignarme
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-secondary" disabled>
                                                <i class="fas fa-user-check"></i> Ya asignada
                                            </button>
                                            <?php if ($tarea['asignado_a'] === $nickname): ?>
                                                <span class="already-assigned">(Esta tarea te está asignada a ti)</span>
                                            <?php else: ?>
                                                <span class="already-assigned">(Asignada a <?php echo htmlspecialchars($tarea['asignado_a']); ?>)</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mis Tareas -->
        <div id="mis-tareas" class="tab-content hidden">
            <div class="section">
                <h2 class="section-title">Mis Tareas</h2>

                <?php if (empty($mis_tareas)): ?>
                    <p>No tienes tareas asignadas actualmente.</p>
                <?php else: ?>
                    <ul class="task-list">
                        <?php foreach ($mis_tareas as $tarea): ?>
                            <li class="task-item">
                                <div class="task-header">
                                    <h3 class="task-title"><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                                    <span class="task-code"><?php echo htmlspecialchars($tarea['estado']); ?></span>
                                </div>

                                <div class="task-meta">
                                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($tarea['categoria']); ?></span>
                                    <span><i class="fas fa-store"></i> <?php echo htmlspecialchars($tarea['local_nombre']); ?></span>
                                    <span><i class="fas fa-calendar-alt"></i> Vence: <?php echo date('d/m/Y', strtotime($tarea['fecha_vencimiento'])); ?></span>
                                    <span class="task-priority <?php echo 'priority-' . strtolower($tarea['prioridad']); ?>">
                                        <i class="fas fa-exclamation-circle"></i> <?php echo $tarea['prioridad']; ?>
                                    </span>
                                    <span><i class="fas fa-money-bill-wave"></i> S/ <?php echo number_format($tarea['presupuesto'], 2); ?></span>
                                </div>

                                <?php if (!empty($tarea['descripcion'])): ?>
                                    <div class="task-description">
                                        <?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $tarea['porcentaje_completado']; ?>%"></div>
                                    </div>
                                    <div class="progress-text">Progreso: <?php echo $tarea['porcentaje_completado']; ?>%</div>
                                </div>

                                <div class="task-footer">
                                    <form method="POST" class="task-actions">
                                        <input type="hidden" name="id_tarea" value="<?php echo $tarea['id']; ?>">

                                        <div class="form-group" style="margin: 0; flex-grow: 1;">
                                            <input type="range" name="porcentaje" min="0" max="100"
                                                value="<?php echo $tarea['porcentaje_completado']; ?>"
                                                class="form-control" style="width: 100%;">
                                        </div>

                                        <button type="submit" name="actualizar_progreso" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt"></i> Actualizar
                                        </button>

                                        <button type="submit" name="completar_tarea" class="btn btn-success">
                                            <i class="fas fa-check"></i> Completar
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2 class="section-title">Tareas Completadas Recientemente</h2>

                <?php if (empty($tareas_completadas)): ?>
                    <p>No hay tareas completadas recientemente.</p>
                <?php else: ?>
                    <ul class="task-list">
                        <?php foreach ($tareas_completadas as $tarea): ?>
                            <li class="task-item">
                                <div class="task-header">
                                    <h3 class="task-title"><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                                    <span class="task-code status-<?php echo strtolower(str_replace(' ', '-', $tarea['estado'])); ?>">
                                        <?php echo htmlspecialchars($tarea['estado']); ?>
                                    </span>
                                </div>

                                <div class="task-meta">
                                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($tarea['categoria']); ?></span>
                                    <span><i class="fas fa-store"></i> <?php echo htmlspecialchars($tarea['local_nombre']); ?></span>
                                    <span><i class="fas fa-calendar-alt"></i> Completada: <?php echo date('d/m/Y', strtotime($tarea['fecha_actualizacion'])); ?></span>
                                    <span><i class="fas fa-user-check"></i> Completada por: <?php
                                                                                            // Obtener el nombre de quien completó la tarea
                                                                                            $stmt = $pdo->prepare("SELECT h.nickname, i.nombre_completo 
                                      FROM historial_tareas h
                                      JOIN informacion_personal i ON h.nickname = i.nickname
                                      WHERE h.id_tarea = :id_tarea AND h.accion = 'Completación' 
                                      ORDER BY h.fecha_registro DESC LIMIT 1");
                                                                                            $stmt->bindParam(':id_tarea', $tarea['id']);
                                                                                            $stmt->execute();
                                                                                            $completado_por = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                                            echo $completado_por ? htmlspecialchars($completado_por['nombre_completo']) : 'Desconocido';
                                                                                            ?></span>
                                    <span><i class="fas fa-money-bill-wave"></i> S/ <?php echo number_format($tarea['presupuesto'], 2); ?></span>
                                </div>

                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 100%"></div>
                                    </div>
                                    <div class="progress-text">Tarea completada</div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Crear Nueva Tarea -->
        <div id="crear-tarea" class="tab-content hidden">
            <div class="section">
                <h2 class="section-title">Crear Nueva Tarea</h2>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="titulo">Título de la Tarea</label>
                                <input type="text" id="titulo" name="titulo" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="id_categoria">Categoría</label>
                                <select id="id_categoria" name="id_categoria" class="form-control" required>
                                    <option value="">Seleccionar categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_local">Local</label>
                                <select id="id_local" name="id_local" class="form-control" required>
                                    <option value="">Seleccionar local</option>
                                    <?php foreach ($locales as $local): ?>
                                        <option value="<?php echo $local['id']; ?>"><?php echo htmlspecialchars($local['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="prioridad">Prioridad</label>
                                <select id="prioridad" name="prioridad" class="form-control" required>
                                    <option value="Alto">Alto</option>
                                    <option value="Medio" selected>Medio</option>
                                    <option value="Bajo">Bajo</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="presupuesto">Presupuesto (S/)</label>
                                <input type="number" id="presupuesto" name="presupuesto" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="form-group" style="text-align: center;">
                        <button type="submit" name="crear_tarea" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Crear Tarea
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            // Ocultar todos los contenidos de pestañas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Mostrar el contenido de la pestaña seleccionada
            document.getElementById(tabId).classList.remove('hidden');

            // Actualizar estado activo de las pestañas
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Marcar la pestaña correspondiente como activa
            event.currentTarget.classList.add('active');
        }

        // Establecer fecha mínima para el campo de fecha de vencimiento (hoy)
        document.getElementById('fecha_vencimiento').min = new Date().toISOString().split('T')[0];
    </script>
</body>

</html>