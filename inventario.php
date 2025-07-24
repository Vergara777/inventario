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

// Procesar formulario de agregar/editar producto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] == 'agregar') {
            $resultado = agregarProducto($_POST);
            if ($resultado) {
                $mensaje = 'Producto agregado exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al agregar el producto';
                $tipo_mensaje = 'error';
            }
        } elseif ($_POST['accion'] == 'editar') {
            $resultado = editarProducto($_POST);
            if ($resultado) {
                $mensaje = 'Producto actualizado exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar el producto';
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Procesar eliminación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if (eliminarProducto($id)) {
        $mensaje = 'Producto eliminado exitosamente';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'Error al eliminar el producto';
        $tipo_mensaje = 'error';
    }
}

// Obtener producto para editar
$producto_editar = null;
if (isset($_GET['editar'])) {
    $producto_editar = obtenerProducto($_GET['editar']);
}

// Obtener productos
$productos = obtenerProductos();

$page_title = "Gestión de Inventario";
include 'includes/header.php';
?>

<!-- Header Section -->
<div class="card">
    <div class="card-header">
        <h2>Gestión de Inventario</h2>
        <p>Administra todos los productos de tu farmacia</p>
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

<!-- Add/Edit Product Form -->
<div class="card">
    <h3>
        <span class="material-icons"><?php echo $producto_editar ? 'edit' : 'add_box'; ?></span>
        <?php echo $producto_editar ? 'Editar Producto' : 'Agregar Nuevo Producto'; ?>
    </h3>
    
    <form method="POST" class="product-form">
        <input type="hidden" name="accion" value="<?php echo $producto_editar ? 'editar' : 'agregar'; ?>">
        <?php if ($producto_editar): ?>
            <input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="codigo">Código del Producto *</label>
                <input type="text" id="codigo" name="codigo" required 
                       value="<?php echo $producto_editar ? $producto_editar['codigo'] : ''; ?>"
                       placeholder="Ej: PAR001">
            </div>
            
            <div class="form-group">
                <label for="nombre">Nombre del Producto *</label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?php echo $producto_editar ? $producto_editar['nombre'] : ''; ?>"
                       placeholder="Ej: Paracetamol 500mg">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" 
                          placeholder="Descripción detallada del producto"><?php echo $producto_editar ? $producto_editar['descripcion'] : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoría *</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccionar categoría</option>
                    <option value="Analgésicos" <?php echo (isset($producto_editar['categoria']) && $producto_editar['categoria'] == 'Analgésicos') ? 'selected' : ''; ?>>Analgésicos</option>
                    <option value="Antibióticos" <?php echo (isset($producto_editar['categoria']) && $producto_editar['categoria'] == 'Antibióticos') ? 'selected' : ''; ?>>Antibióticos</option>
                    <option value="Antiinflamatorios" <?php echo (isset($producto_editar['categoria']) && $producto_editar['categoria'] == 'Antiinflamatorios') ? 'selected' : ''; ?>>Antiinflamatorios</option>
                    <option value="Vitaminas" <?php echo (isset($producto_editar['categoria']) && $producto_editar['categoria'] == 'Vitaminas') ? 'selected' : ''; ?>>Vitaminas</option>
                    <option value="Cuidado Personal" <?php echo (isset($producto_editar['categoria']) && $producto_editar['categoria'] == 'Cuidado Personal') ? 'selected' : ''; ?>>Cuidado Personal</option>
                    <option value="Otros" <?php echo (isset($producto_editar['categoria']) && $producto_editar['categoria'] == 'Otros') ? 'selected' : ''; ?>>Otros</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="precio_compra">Precio de Compra *</label>
                <input type="number" id="precio_compra" name="precio_compra" step="0.01" required 
                       value="<?php echo $producto_editar ? $producto_editar['precio_compra'] : ''; ?>"
                       placeholder="0.00">
            </div>
            
            <div class="form-group">
                <label for="precio_venta">Precio de Venta *</label>
                <input type="number" id="precio_venta" name="precio_venta" step="0.01" required 
                       value="<?php echo $producto_editar ? $producto_editar['precio_venta'] : ''; ?>"
                       placeholder="0.00">
            </div>
            
            <div class="form-group">
                <label for="cantidad_actual">Stock Actual *</label>
                <input type="number" id="cantidad_actual" name="cantidad_actual" required 
                       value="<?php echo $producto_editar['cantidad_actual'] ?? ''; ?>"
                       placeholder="0">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="stock_minimo">Stock Mínimo *</label>
                <input type="number" id="stock_minimo" name="stock_minimo" required 
                       value="<?php echo $producto_editar['stock_minimo'] ?? ''; ?>"
                       placeholder="0">
            </div>
            
            <div class="form-group">
                <label for="categoria_id">Categoría *</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Seleccionar categoría</option>
                    <option value="1" <?php echo (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == 1) ? 'selected' : ''; ?>>Analgésicos</option>
                    <option value="2" <?php echo (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == 2) ? 'selected' : ''; ?>>Antibióticos</option>
                    <option value="3" <?php echo (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == 3) ? 'selected' : ''; ?>>Antiinflamatorios</option>
                    <option value="4" <?php echo (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == 4) ? 'selected' : ''; ?>>Vitaminas</option>
                    <option value="5" <?php echo (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == 5) ? 'selected' : ''; ?>>Cuidado Personal</option>
                    <option value="6" <?php echo (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == 6) ? 'selected' : ''; ?>>Otros</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" 
                       value="<?php echo $producto_editar['fecha_vencimiento'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="lote">Número de Lote</label>
                <input type="text" id="lote" name="lote" 
                       value="<?php echo $producto_editar['lote'] ?? ''; ?>"
                       placeholder="Ej: L2024001">
            </div>
            
            <div class="form-group">
                <label for="requiere_receta">Requiere Receta</label>
                <select id="requiere_receta" name="requiere_receta">
                    <option value="0" <?php echo (isset($producto_editar['requiere_receta']) && $producto_editar['requiere_receta'] == 0) ? 'selected' : ''; ?>>No</option>
                    <option value="1" <?php echo (isset($producto_editar['requiere_receta']) && $producto_editar['requiere_receta'] == 1) ? 'selected' : ''; ?>>Sí</option>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-edit">
                <span class="material-icons"><?php echo $producto_editar ? 'update' : 'add'; ?></span>
                <?php echo $producto_editar ? 'Actualizar Producto' : 'Agregar Producto'; ?>
            </button>
            
            <?php if ($producto_editar): ?>
                <a href="inventario.php" class="btn btn-outline">
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
        <input type="text" id="searchInput" placeholder="Buscar productos...">
    </div>
    
    <div class="filters">
        <h3>Filtros</h3>
        <div class="filter-row">
            <div class="form-group">
                <label for="filterCategoria">Categoría</label>
                <select id="filterCategoria">
                    <option value="">Todas las categorías</option>
                    <option value="Analgésicos">Analgésicos</option>
                    <option value="Antibióticos">Antibióticos</option>
                    <option value="Antiinflamatorios">Antiinflamatorios</option>
                    <option value="Vitaminas">Vitaminas</option>
                    <option value="Cuidado Personal">Cuidado Personal</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="filterStock">Stock</label>
                <select id="filterStock">
                    <option value="">Todos</option>
                    <option value="bajo">Stock Bajo</option>
                    <option value="agotado">Agotados</option>
                    <option value="disponible">Disponibles</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="filterReceta">Receta</label>
                <select id="filterReceta">
                    <option value="">Todos</option>
                    <option value="1">Requiere Receta</option>
                    <option value="0">Sin Receta</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <h3>Lista de Productos</h3>
    
    <div class="table-container">
        <table class="productos-table" id="productosTable">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio Venta</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($productos && mysqli_num_rows($productos) > 0): ?>
                    <?php while ($producto = mysqli_fetch_assoc($productos)): ?>
                        <tr data-categoria="<?php echo isset($producto['categoria_nombre']) ? $producto['categoria_nombre'] : ''; ?>" 
                            data-stock="<?php echo isset($producto['cantidad_actual']) ? $producto['cantidad_actual'] : ''; ?>" 
                            data-receta="<?php echo isset($producto['requiere_receta']) ? $producto['requiere_receta'] : ''; ?>">
                            <td>
                                <strong><?php echo $producto['codigo']; ?></strong>
                            </td>
                            <td>
                                <div class="product-info">
                                    <div class="product-name"><?php echo $producto['nombre']; ?></div>
                                    <?php if (!empty($producto['descripcion'])): ?>
                                        <div class="product-description"><?php echo substr($producto['descripcion'], 0, 50) . '...'; ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="category-badge"><?php echo isset($producto['categoria_nombre']) ? $producto['categoria_nombre'] : ''; ?></span>
                            </td>
                            <td>
                                <strong>$<?php echo number_format($producto['precio_venta'], 0, ',', '.'); ?></strong>
                            </td>
                            <td>
                                <?php if (isset($producto['cantidad_actual']) && isset($producto['stock_minimo']) && $producto['cantidad_actual'] <= $producto['stock_minimo']): ?>
                                    <span class="stock-bajo"><?php echo $producto['cantidad_actual']; ?></span>
                                <?php elseif (isset($producto['cantidad_actual']) && $producto['cantidad_actual'] == 0): ?>
                                    <span class="stock-agotado">Agotado</span>
                                <?php else: ?>
                                    <span class="stock-disponible"><?php echo isset($producto['cantidad_actual']) ? $producto['cantidad_actual'] : ''; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($producto['requiere_receta'])): ?>
                                    <span class="status-badge status-warning">Receta</span>
                                <?php else: ?>
                                    <span class="status-badge status-approved">Libre</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="inventario.php?editar=<?php echo $producto['id']; ?>" 
                                       class="btn btn-edit" title="Editar">
                                        <span class="material-icons">edit</span>
                                    </a>
                                    <a href="inventario.php?eliminar=<?php echo $producto['id']; ?>" 
                                       class="btn btn-delete" 
                                       onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')"
                                       title="Eliminar">
                                        <span class="material-icons">delete</span>
                                    </a>
                                    <a href="producto_detalle.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-outline" title="Ver detalles">
                                        <span class="material-icons">visibility</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">
                            <div class="no-data-content">
                                <span class="material-icons">inventory_2</span>
                                <p>No hay productos registrados</p>
                                <p>Comienza agregando tu primer producto</p>
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

.product-form {
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

.product-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.product-name {
    font-weight: 600;
    color: #2c3e50;
}

.product-description {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.category-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stock-bajo {
    color: #e74c3c;
    font-weight: 700;
}

.stock-agotado {
    color: #e74c3c;
    font-weight: 700;
    background: rgba(231,76,60,0.1);
    padding: 3px 8px;
    border-radius: 10px;
}

.stock-disponible {
    color: #27ae60;
    font-weight: 600;
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
}
</style>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#productosTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter functionality
function applyFilters() {
    const categoria = document.getElementById('filterCategoria').value;
    const stock = document.getElementById('filterStock').value;
    const receta = document.getElementById('filterReceta').value;
    
    const rows = document.querySelectorAll('#productosTable tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (categoria && row.dataset.categoria !== categoria) show = false;
        if (receta && row.dataset.receta !== receta) show = false;
        
        if (stock) {
            const stockValue = parseInt(row.dataset.stock);
            if (stock === 'bajo' && stockValue > 10) show = false;
            if (stock === 'agotado' && stockValue > 0) show = false;
            if (stock === 'disponible' && stockValue <= 0) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

document.getElementById('filterCategoria').addEventListener('change', applyFilters);
document.getElementById('filterStock').addEventListener('change', applyFilters);
document.getElementById('filterReceta').addEventListener('change', applyFilters);
</script>

<?php include 'includes/footer.php'; ?>


