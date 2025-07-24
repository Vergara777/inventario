<?php
session_start();
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de agregar/editar cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] == 'agregar') {
            $resultado = agregarCliente($_POST);
            if ($resultado) {
                $mensaje = 'Cliente agregado exitosamente';
                $tipo_mensaje = 'success';
    } else {
                $mensaje = 'Error al agregar el cliente';
                $tipo_mensaje = 'error';
            }
        } elseif ($_POST['accion'] == 'editar') {
            $resultado = editarCliente($_POST);
            if ($resultado) {
                $mensaje = 'Cliente actualizado exitosamente';
                $tipo_mensaje = 'success';
        } else {
                $mensaje = 'Error al actualizar el cliente';
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Procesar eliminación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if (eliminarCliente($id)) {
        $mensaje = 'Cliente eliminado exitosamente';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'Error al eliminar el cliente';
        $tipo_mensaje = 'error';
    }
}

// Obtener cliente para editar
$cliente_editar = null;
if (isset($_GET['editar'])) {
    $cliente_editar = obtenerCliente($_GET['editar']);
}

// Obtener clientes
$clientes = obtenerClientes();

$page_title = "Gestión de Clientes";
include 'includes/header.php';
?>

<!-- Header Section -->
<div class="card">
    <div class="card-header">
        <h2>Gestión de Clientes</h2>
        <p>Administra la información de todos tus clientes</p>
    </div>
</div>

<!-- Messages -->
<?php if ($mensaje): ?>
    <div class="message <?php echo $tipo_mensaje; ?>">
        <span class="material-icons">
            <?php echo $tipo_mensaje == 'success' ? 'check_circle' : 'error'; ?>
        </span>
        <?php echo $mensaje; ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Client Form -->
<div class="card">
    <h3>
        <span class="material-icons"><?php echo $cliente_editar ? 'edit' : 'person_add'; ?></span>
        <?php echo $cliente_editar ? 'Editar Cliente' : 'Agregar Nuevo Cliente'; ?>
    </h3>
    
    <form method="POST" class="client-form" data-autosave="client-form">
        <input type="hidden" name="accion" value="<?php echo $cliente_editar ? 'editar' : 'agregar'; ?>">
        <?php if ($cliente_editar): ?>
            <input type="hidden" name="id" value="<?php echo $cliente_editar['id']; ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?php echo $cliente_editar['nombre'] ?? ''; ?>"
                       placeholder="Ej: Juan Carlos">
            </div>
            
            <div class="form-group">
                <label for="apellido">Apellido *</label>
                <input type="text" id="apellido" name="apellido" required 
                       value="<?php echo $cliente_editar['apellido'] ?? ''; ?>"
                       placeholder="Ej: Pérez García">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo $cliente_editar['email'] ?? ''; ?>"
                       placeholder="Ej: juan.perez@email.com">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono *</label>
                <input type="tel" id="telefono" name="telefono" required 
                       value="<?php echo $cliente_editar['telefono'] ?? ''; ?>"
                       placeholder="Ej: 3001234567">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="documento">Número de Documento *</label>
                <input type="text" id="documento" name="documento" required 
                       value="<?php echo $cliente_editar['documento'] ?? ''; ?>"
                       placeholder="Ej: 12345678">
            </div>
            
            <div class="form-group">
                <label for="tipo_documento">Tipo de Documento *</label>
                <select id="tipo_documento" name="tipo_documento" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="CC" <?php echo (isset($cliente_editar['tipo_documento']) && $cliente_editar['tipo_documento'] == 'CC') ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                    <option value="CE" <?php echo (isset($cliente_editar['tipo_documento']) && $cliente_editar['tipo_documento'] == 'CE') ? 'selected' : ''; ?>>Cédula de Extranjería</option>
                    <option value="TI" <?php echo (isset($cliente_editar['tipo_documento']) && $cliente_editar['tipo_documento'] == 'TI') ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                    <option value="PP" <?php echo (isset($cliente_editar['tipo_documento']) && $cliente_editar['tipo_documento'] == 'PP') ? 'selected' : ''; ?>>Pasaporte</option>
                    <option value="NIT" <?php echo (isset($cliente_editar['tipo_documento']) && $cliente_editar['tipo_documento'] == 'NIT') ? 'selected' : ''; ?>>NIT</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea id="direccion" name="direccion" rows="3" 
                          placeholder="Dirección completa del cliente"><?php echo $cliente_editar['direccion'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" 
                       value="<?php echo $cliente_editar['ciudad'] ?? ''; ?>"
                       placeholder="Ej: Bogotá">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                       value="<?php echo $cliente_editar['fecha_nacimiento'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="genero">Género</label>
                <select id="genero" name="genero">
                    <option value="">Seleccionar género</option>
                    <option value="M" <?php echo (isset($cliente_editar['genero']) && $cliente_editar['genero'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                    <option value="F" <?php echo (isset($cliente_editar['genero']) && $cliente_editar['genero'] == 'F') ? 'selected' : ''; ?>>Femenino</option>
                    <option value="O" <?php echo (isset($cliente_editar['genero']) && $cliente_editar['genero'] == 'O') ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="estado">Estado *</label>
                <select id="estado" name="estado" required>
                    <option value="">Seleccionar estado</option>
                    <option value="activo" <?php echo (isset($cliente_editar['estado']) && $cliente_editar['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo (isset($cliente_editar['estado']) && $cliente_editar['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3" 
                          placeholder="Observaciones adicionales sobre el cliente"><?php echo $cliente_editar['observaciones'] ?? ''; ?></textarea>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-edit">
                <span class="material-icons"><?php echo $cliente_editar ? 'update' : 'add'; ?></span>
                <?php echo $cliente_editar ? 'Actualizar Cliente' : 'Agregar Cliente'; ?>
            </button>
            
            <?php if ($cliente_editar): ?>
                <a href="clientes.php" class="btn btn-outline">
                    <span class="material-icons">cancel</span>
                    Cancelar
                </a>
        <?php endif; ?>
        </div>
    </form>
</div>

<!-- Search and Filters -->
<div class="card">
    <div class="search-box">
        <span class="material-icons">search</span>
        <input type="text" id="searchInput" placeholder="Buscar clientes...">
    </div>
    
    <div class="filters">
        <h3>Filtros</h3>
        <div class="filter-row">
            <div class="form-group">
                <label for="filterEstado">Estado</label>
                <select id="filterEstado">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="filterGenero">Género</label>
                <select id="filterGenero">
                    <option value="">Todos los géneros</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="O">Otro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="filterTipoDoc">Tipo de Documento</label>
                <select id="filterTipoDoc">
                    <option value="">Todos los tipos</option>
                    <option value="CC">Cédula de Ciudadanía</option>
                    <option value="CE">Cédula de Extranjería</option>
                    <option value="TI">Tarjeta de Identidad</option>
                    <option value="PP">Pasaporte</option>
                    <option value="NIT">NIT</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Clients Table -->
<div class="card">
    <h3>Lista de Clientes</h3>
    
    <div class="table-container">
        <table class="productos-table" id="clientesTable">
            <thead>
                <tr>
                    <th class="doc-col">Documento</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>Ubicación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
        </tr>
            </thead>
            <tbody>
                <?php if ($clientes && mysqli_num_rows($clientes) > 0): ?>
                    <?php while ($cliente = mysqli_fetch_assoc($clientes)): ?>
                        <tr data-estado="<?php echo $cliente['activo'] ? 'activo' : 'inactivo'; ?>" 
                            data-genero="<?php echo $cliente['genero'] ?? ''; ?>" 
                            data-tipo-doc="<?php echo $cliente['tipo_documento'] ?? ''; ?>">
                            <td class="doc-col">
                                <div class="document-info">
                                    <span class="document-full"><strong><?php echo ($cliente['tipo_documento'] ?? '') . ' ' . ($cliente['documento'] ?? ''); ?></strong></span>
                                </div>
                            </td>
                            <td>
                                <div class="client-info">
                                    <div class="client-name"><?php echo $cliente['nombre'] . ' ' . ($cliente['apellido'] ?? ''); ?></div>
                                    <?php if (!empty($cliente['fecha_nacimiento'])): ?>
                                        <div class="client-age"><?php echo calcularEdad($cliente['fecha_nacimiento']); ?> años</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <?php if ($cliente['telefono']): ?>
                                        <div class="contact-item">
                                            <span class="material-icons">phone</span>
                                            <?php echo $cliente['telefono']; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($cliente['email']): ?>
                                        <div class="contact-item">
                                            <span class="material-icons">email</span>
                                            <?php echo $cliente['email']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <?php if (!empty($cliente['ciudad'])): ?>
                                        <div class="location-item">
                                            <span class="material-icons">location_city</span>
                                            <?php echo $cliente['ciudad']; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($cliente['direccion']): ?>
                                        <div class="location-item">
                                            <span class="material-icons">home</span>
                                            <?php echo substr($cliente['direccion'], 0, 30) . '...'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($cliente['activo']): ?>
                                    <span class="status-badge status-approved">Activo</span>
                                <?php else: ?>
                                    <span class="status-badge status-rejected">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="clientes.php?editar=<?php echo $cliente['id']; ?>" 
                                       class="btn btn-edit" title="Editar">
                                        <span class="material-icons">edit</span>
                                    </a>
                                    <a href="cliente_detalle.php?id=<?php echo $cliente['id']; ?>" 
                                       class="btn btn-outline" title="Ver detalles">
                                        <span class="material-icons">visibility</span>
                                    </a>
                                    <a href="clientes.php?eliminar=<?php echo $cliente['id']; ?>" 
                                       class="btn btn-delete" 
                                       onclick="return confirm('¿Estás seguro de que quieres eliminar este cliente?')"
                                       title="Eliminar">
                                        <span class="material-icons">delete</span>
                                    </a>
                                </div>
            </td>
        </tr>
        <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <div class="no-data-content">
                                <span class="material-icons">people</span>
                                <p>No hay clientes registrados</p>
                                <p>Comienza agregando tu primer cliente</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
    </table>
    </div>
</div>

<style>
.card-header {
    text-align: center;
    margin-bottom: 20px;
}

.card-header h2 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 2rem;
}

.card-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.client-form {
    margin-top: 20px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.table-container {
    overflow-x: auto;
    margin-top: 20px;
}

.document-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.document-type {
    font-weight: 600;
    color: #6c63ff;
    font-size: 0.9rem;
}

.document-number {
    color: #2c3e50;
    font-size: 0.95rem;
}

.client-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.client-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1rem;
}

.client-age {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.contact-info,
.location-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-item,
.location-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #2c3e50;
}

.contact-item .material-icons,
.location-item .material-icons {
    font-size: 1rem;
    color: #6c63ff;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.action-buttons .btn {
    padding: 8px;
    min-width: auto;
}

.no-data {
    text-align: center;
    padding: 60px 20px;
}

.no-data-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.no-data-content .material-icons {
    font-size: 4rem;
    color: #bdc3c7;
}

.no-data-content p {
    color: #7f8c8d;
    margin: 0;
}

.no-data-content p:first-of-type {
    font-size: 1.2rem;
    font-weight: 600;
}

/* Mejorar visibilidad de la tabla de clientes */
.productos-table th.doc-col, .productos-table td.doc-col {
    background: #e3e8f0;
    color: #2c3e50;
    font-weight: bold;
    border-right: 2px solid #b2becd;
    font-size: 1.05rem;
}
.productos-table td.doc-col .document-full {
    font-size: 1.1rem;
    letter-spacing: 0.5px;
    color: #222b3a;
}
.productos-table tr {
    background: #f8fafd;
    border-bottom: 1.5px solid #d1d9e6;
}
.productos-table tr:hover {
    background: #e0e7ff;
}
.card h3 {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 18px;
    text-align: left;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
    
    .action-buttons .btn {
        width: 100%;
        justify-content: center;
    }
    
    .contact-info,
    .location-info {
        font-size: 0.8rem;
    }
}
</style>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#clientesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter functionality
function applyFilters() {
    const estado = document.getElementById('filterEstado').value;
    const genero = document.getElementById('filterGenero').value;
    const tipoDoc = document.getElementById('filterTipoDoc').value;
    
    const rows = document.querySelectorAll('#clientesTable tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (estado && row.dataset.estado !== estado) show = false;
        if (genero && row.dataset.genero !== genero) show = false;
        if (tipoDoc && row.dataset.tipoDoc !== tipoDoc) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

document.getElementById('filterEstado').addEventListener('change', applyFilters);
document.getElementById('filterGenero').addEventListener('change', applyFilters);
document.getElementById('filterTipoDoc').addEventListener('change', applyFilters);

// Auto-focus on first input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('nombre').focus();
});
</script>

<?php include 'includes/footer.php'; ?> 