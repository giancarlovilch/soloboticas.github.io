<?php
include('db_connection.php');
include('session_manager.php');

// Verificar sesión y permisos
if (!isset($_SESSION['nickname'])) {
    header('Location: login.php');
    exit();
}

// Obtener rol del usuario
$stmt = $pdo->prepare("SELECT rol FROM informacion_personal WHERE nickname = :nickname");
$stmt->bindParam(':nickname', $_SESSION['nickname']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['rol'] !== 'Administrador') {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit();
}

// Configuración inicial
$tabla_actual = $_GET['tabla'] ?? 'categorias_tareas';
$mensaje = '';
$edicion = false;
$registro_actual = null;

// Validar tabla permitida
$tablas_permitidas = ['categorias_tareas', 'locales'];
if (!in_array($tabla_actual, $tablas_permitidas)) {
    $tabla_actual = 'categorias_tareas';
}

// Construir URL base manteniendo el parámetro page
$url_base = "dashboard.php?page=predocuments";

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    
    try {
        if (isset($_POST['guardar'])) {
            if ($id) { // Edición
                if ($tabla_actual === 'categorias_tareas') {
                    $stmt = $pdo->prepare("UPDATE categorias_tareas SET nombre = ?, descripcion = ? WHERE id = ?");
                    $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE locales SET nombre = ?, direccion = ?, telefono = ?, responsable = ? WHERE id = ?");
                    $stmt->execute([$_POST['nombre'], $_POST['direccion'], $_POST['telefono'], $_POST['responsable'], $id]);
                }
                $mensaje = "Registro actualizado correctamente";
            } else { // Nuevo
                if ($tabla_actual === 'categorias_tareas') {
                    $stmt = $pdo->prepare("INSERT INTO categorias_tareas (nombre, descripcion) VALUES (?, ?)");
                    $stmt->execute([$_POST['nombre'], $_POST['descripcion']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO locales (nombre, direccion, telefono, responsable) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$_POST['nombre'], $_POST['direccion'], $_POST['telefono'], $_POST['responsable']]);
                }
                $mensaje = "Registro agregado correctamente";
            }
        } 
        elseif (isset($_POST['eliminar'])) {
            if ($tabla_actual === 'categorias_tareas') {
                $stmt = $pdo->prepare("DELETE FROM categorias_tareas WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("DELETE FROM locales WHERE id = ?");
            }
            $stmt->execute([$id]);
            $mensaje = "Registro eliminado correctamente";
        }
        elseif (isset($_POST['editar'])) {
            $edicion = true;
            if ($tabla_actual === 'categorias_tareas') {
                $stmt = $pdo->prepare("SELECT * FROM categorias_tareas WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM locales WHERE id = ?");
            }
            $stmt->execute([$id]);
            $registro_actual = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
    
    // Redirigir para evitar reenvío del formulario
    if (isset($_POST['guardar']) || isset($_POST['eliminar'])) {
        // header("Location: $url_base&tabla=$tabla_actual");
        echo "<script>window.location.href='dashboard.php?page=predocuments&tabla=categorias_tareas';</script>";
        exit();
    }
}

// Obtener registros
try {
    if ($tabla_actual === 'categorias_tareas') {
        $registros = $pdo->query("SELECT * FROM categorias_tareas ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $registros = $pdo->query("SELECT * FROM locales ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $mensaje = "Error al obtener registros: " . $e->getMessage();
    $registros = [];
}
?>
<div class="config-container">
    <h1 class="config-title">Configuración Inicial</h1>
    
    <?php if ($mensaje): ?>
        <div class="alert <?php echo strpos($mensaje, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
    
    <div class="config-tabs">
        <a href="<?php echo $url_base; ?>&tabla=categorias_tareas" 
           class="config-tab <?php echo $tabla_actual === 'categorias_tareas' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i> Categorías
        </a>
        <a href="<?php echo $url_base; ?>&tabla=locales" 
           class="config-tab <?php echo $tabla_actual === 'locales' ? 'active' : ''; ?>">
            <i class="fas fa-store"></i> Locales
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="config-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <?php if ($tabla_actual === 'categorias_tareas'): ?>
                        <th>Descripción</th>
                    <?php else: ?>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Responsable</th>
                    <?php endif; ?>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                <tr>
                    <td data-label="Nombre"><?php echo htmlspecialchars($registro['nombre']); ?></td>
                    <?php if ($tabla_actual === 'categorias_tareas'): ?>
                        <td data-label="Descripción"><?php echo htmlspecialchars($registro['descripcion']); ?></td>
                    <?php else: ?>
                        <td data-label="Dirección"><?php echo htmlspecialchars($registro['direccion']); ?></td>
                        <td data-label="Teléfono"><?php echo htmlspecialchars($registro['telefono']); ?></td>
                        <td data-label="Responsable"><?php echo htmlspecialchars($registro['responsable']); ?></td>
                    <?php endif; ?>
                    <td data-label="Acciones" class="actions-cell">
                        <form method="POST" class="form-inline">
                            <input type="hidden" name="id" value="<?php echo $registro['id']; ?>">
                            <input type="hidden" name="tabla" value="<?php echo $tabla_actual; ?>">
                            <button type="submit" name="editar" class="btn btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="submit" name="eliminar" class="btn btn-delete" onclick="return confirm('¿Eliminar este registro?')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="config-form">
        <h2 class="form-title"><?php echo $edicion ? 'Editar Registro' : 'Agregar Nuevo'; ?></h2>
        <form method="POST" class="form-grid">
            <input type="hidden" name="id" value="<?php echo $edicion ? $registro_actual['id'] : ''; ?>">
            <input type="hidden" name="tabla" value="<?php echo $tabla_actual; ?>">
            
            <div class="form-group">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-input" 
                       value="<?php echo $edicion ? htmlspecialchars($registro_actual['nombre']) : ''; ?>" required>
            </div>
            
            <?php if ($tabla_actual === 'categorias_tareas'): ?>
                <div class="form-group full-width">
                    <label class="form-label">Descripción:</label>
                    <textarea name="descripcion" class="form-textarea"><?php echo $edicion ? htmlspecialchars($registro_actual['descripcion']) : ''; ?></textarea>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label class="form-label">Dirección:</label>
                    <input type="text" name="direccion" class="form-input" 
                           value="<?php echo $edicion ? htmlspecialchars($registro_actual['direccion']) : ''; ?>" >
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono:</label>
                    <input type="text" name="telefono" class="form-input" 
                           value="<?php echo $edicion ? htmlspecialchars($registro_actual['telefono']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Responsable:</label>
                    <input type="text" name="responsable" class="form-input" 
                           value="<?php echo $edicion ? htmlspecialchars($registro_actual['responsable']) : ''; ?>">
                </div>
            <?php endif; ?>
            
            <div class="form-actions full-width">
                <button type="submit" name="guardar" class="btn btn-save">
                    <i class="fas fa-save"></i> <?php echo $edicion ? 'Guardar Cambios' : 'Agregar'; ?>
                </button>
                <?php if ($edicion): ?>
                    <a href="<?php echo $url_base; ?>&tabla=<?php echo $tabla_actual; ?>" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<style>
/* Estilos mejorados para móviles */
.config-container {
    padding: 15px;
    max-width: 100%;
    overflow-x: auto;
}

.config-title {
    color: #2c3e50;
    /* margin-bottom: 20px; */
    font-size: 1.5rem;
    text-align: center;
    margin: 0 0 20px;
}

.config-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.config-tab {
    padding: 10px 15px;
    background: #f8f9fa;
    margin-right: 5px;
    border-radius: 5px 5px 0 0;
    color: #495057;
    text-decoration: none;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.config-tab i {
    margin-right: 5px;
}

.config-tab.active {
    background: #3498db;
    color: white;
}

.table-responsive {
    overflow-x: auto;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}

.config-table {
    width: 100%;
    border-collapse: collapse;
}

.config-table th, .config-table td {
    padding: 12px 15px;
    border: 1px solid #dee2e6;
}

.config-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.config-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

.actions-cell {
    white-space: nowrap;
}

.btn {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85rem;
    margin: 0 3px;
    display: inline-flex;
    align-items: center;
}

.btn-edit {
    background-color: #f39c12;
    color: white;
}

.btn-delete {
    background-color: #e74c3c;
    color: white;
}

.btn-save {
    background-color: #2ecc71;
    color: white;
}

.btn-cancel {
    background-color: #95a5a6;
    color: white;
    text-decoration: none;
    padding: 8px 12px;
}

.config-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
}

.form-title {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #495057;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
}

.form-textarea {
    min-height: 80px;
    resize: vertical;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}

.alert {
    padding: 10px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-size: 0.9rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Estilos para móviles */
@media (max-width: 768px) {
    .config-tabs {
        flex-direction: column;
    }
    
    .config-tab {
        margin-bottom: 5px;
        border-radius: 5px;
    }
    
    .config-table thead {
        display: none;
    }
    
    .config-table tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    
    .config-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border: none;
        border-bottom: 1px solid #dee2e6;
    }
    
    .config-table td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #495057;
        margin-right: 10px;
    }
    
    .actions-cell {
        display: flex;
        justify-content: flex-end;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Incluir Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">