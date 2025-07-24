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

// Procesar venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar_venta'])) {
    $resultado = procesarVenta($_POST);
    if ($resultado) {
        $mensaje = 'Venta registrada exitosamente. ID de venta: ' . $resultado;
        $tipo_mensaje = 'success';
        // Limpiar el carrito después de la venta
        unset($_SESSION['carrito']);
    } else {
        $mensaje = 'Error al procesar la venta';
        $tipo_mensaje = 'error';
    }
}

// Agregar producto al carrito
if (isset($_POST['agregar_producto'])) {
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
    
    $producto = obtenerProducto($producto_id);
    if ($producto && $producto['cantidad_actual'] >= $cantidad) {
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$producto_id] = [
                'id' => $producto['id'],
                'codigo' => $producto['codigo'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio_venta'],
                'cantidad' => $cantidad,
                'stock' => $producto['cantidad_actual']
            ];
        }
        $mensaje = 'Producto agregado al carrito';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'Stock insuficiente o producto no encontrado';
        $tipo_mensaje = 'error';
    }
}

// Remover producto del carrito
if (isset($_GET['remover'])) {
    $producto_id = $_GET['remover'];
    if (isset($_SESSION['carrito'][$producto_id])) {
        unset($_SESSION['carrito'][$producto_id]);
        $mensaje = 'Producto removido del carrito';
        $tipo_mensaje = 'success';
    }
}

// Limpiar carrito
if (isset($_GET['limpiar'])) {
    unset($_SESSION['carrito']);
    $mensaje = 'Carrito limpiado';
    $tipo_mensaje = 'success';
}

// Obtener productos para el formulario
$productos = obtenerProductos();
$clientes = obtenerClientes();

$page_title = "Nueva Venta";
include 'includes/header.php';
?>

<!-- Header Section -->
<div class="card">
    <div class="card-header">
        <h2>Nueva Venta</h2>
        <p>Registra una nueva venta en tu farmacia</p>
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

<!-- Sales Form -->
<div class="sales-container">
    <!-- Product Selection -->
    <div class="card">
        <h3>
            <span class="material-icons">add_shopping_cart</span>
            Agregar Productos
        </h3>
        
        <form method="POST" class="product-selection-form">
        <div class="form-row">
                <div class="form-group">
                    <label for="producto_id">Producto *</label>
                    <select id="producto_id" name="producto_id" required>
                <option value="">Seleccionar producto</option>
                        <?php if ($productos): ?>
                            <?php while ($producto = mysqli_fetch_assoc($productos)): ?>
                                <option value="<?php echo $producto['id']; ?>" 
                                        data-precio="<?php echo $producto['precio_venta']; ?>"
                                        data-stock="<?php echo $producto['cantidad_actual']; ?>">
                                    <?php echo $producto['codigo'] . ' - ' . $producto['nombre']; ?>
                                    (Stock: <?php echo $producto['cantidad_actual']; ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
            </select>
                </div>
                
                <div class="form-group">
                    <label for="cantidad">Cantidad *</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" value="1" required>
                </div>
                
                <div class="form-group">
                    <label for="precio_unitario">Precio Unitario</label>
                    <input type="number" id="precio_unitario" name="precio_unitario" step="0.01" readonly>
        </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" name="agregar_producto" class="btn btn-edit">
                        <span class="material-icons">add</span>
                        Agregar al Carrito
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Shopping Cart -->
    <div class="card">
        <div class="cart-header">
            <h3>
                <span class="material-icons">shopping_cart</span>
                Carrito de Compras
            </h3>
            <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
                <a href="ventas.php?limpiar=1" class="btn btn-outline" onclick="return confirm('¿Limpiar carrito?')">
                    <span class="material-icons">clear_all</span>
                    Limpiar Carrito
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                            <th>Acciones</th>
            </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_venta = 0;
                        foreach ($_SESSION['carrito'] as $producto_id => $item): 
                            $subtotal = $item['precio'] * $item['cantidad'];
                            $total_venta += $subtotal;
                        ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-code"><?php echo $item['codigo']; ?></div>
                                        <div class="product-name"><?php echo $item['nombre']; ?></div>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($item['precio'], 0, ',', '.'); ?></td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td><strong>$<?php echo number_format($subtotal, 0, ',', '.'); ?></strong></td>
                                <td>
                                    <a href="ventas.php?remover=<?php echo $producto_id; ?>" 
                                       class="btn btn-delete" title="Remover">
                                        <span class="material-icons">remove_shopping_cart</span>
                                    </a>
                                </td>
            </tr>
            <?php endforeach; ?>
                    </tbody>
</table>
            </div>
            
            <div class="cart-summary">
                <div class="total-amount">
                    <span class="total-label">Total de la Venta:</span>
                    <span class="total-value">$<?php echo number_format($total_venta, 0, ',', '.'); ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <span class="material-icons">shopping_cart</span>
                <p>El carrito está vacío</p>
                <p>Agrega productos para continuar</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sale Details -->
    <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
        <div class="card">
            <h3>
                <span class="material-icons">receipt</span>
                Detalles de la Venta
            </h3>
            
            <form method="POST" class="sale-details-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="cliente_id">Cliente</label>
                        <select id="cliente_id" name="cliente_id">
                            <option value="">Cliente General</option>
                            <?php if ($clientes): ?>
                                <?php while ($cliente = mysqli_fetch_assoc($clientes)): ?>
                                    <option value="<?php echo $cliente['id']; ?>">
                                        <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodo_pago">Método de Pago *</label>
                        <select id="metodo_pago" name="metodo_pago" required>
                            <option value="">Seleccionar método</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="descuento">Descuento (%)</label>
                        <input type="number" id="descuento" name="descuento" min="0" max="100" value="0" step="0.01">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="3" 
                                  placeholder="Observaciones adicionales de la venta"></textarea>
                    </div>
                </div>
                
                <div class="sale-summary">
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total_venta, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-item" id="descuentoItem" style="display: none;">
                        <span>Descuento:</span>
                        <span id="descuentoValor">$0</span>
                    </div>
                    <div class="summary-item total">
                        <span>Total Final:</span>
                        <span id="totalFinal">$<?php echo number_format($total_venta, 0, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="finalizar_venta" class="btn btn-edit">
                        <span class="material-icons">check_circle</span>
                        Finalizar Venta
                    </button>
                    
                    <a href="ventas.php" class="btn btn-outline">
                        <span class="material-icons">cancel</span>
                        Cancelar
                    </a>
                </div>
    </form>
        </div>
    <?php endif; ?>
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

.sales-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.product-selection-form {
    margin-top: 20px;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
}

.cart-table th {
    background: linear-gradient(90deg, #6c63ff, #7b8cff);
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
}

.cart-table td {
    padding: 15px;
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.cart-table tr:hover {
    background: rgba(108,99,255,0.05);
}

.cart-table tr:last-child td {
    border-bottom: none;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.product-code {
    font-weight: 600;
    color: #6c63ff;
    font-size: 0.9rem;
}

.product-name {
    color: #2c3e50;
    font-size: 0.95rem;
}

.cart-summary {
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
}

.total-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.2rem;
    font-weight: 700;
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.empty-cart .material-icons {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-cart p {
    margin: 10px 0;
    font-size: 1.1rem;
}

.sale-details-form {
    margin-top: 20px;
}

.sale-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    margin: 20px 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item.total {
    font-weight: 700;
    font-size: 1.2rem;
    color: #2c3e50;
    border-top: 2px solid #6c63ff;
    padding-top: 15px;
    margin-top: 10px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

@media (max-width: 768px) {
    .cart-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .cart-table {
        font-size: 0.9rem;
    }
    
    .cart-table th,
    .cart-table td {
        padding: 10px 8px;
    }
}
</style>

<script>
// Update price when product is selected
document.getElementById('producto_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const precio = selectedOption.dataset.precio || '';
    document.getElementById('precio_unitario').value = precio;
});

// Calculate discount and total
document.getElementById('descuento').addEventListener('input', function() {
    const descuento = parseFloat(this.value) || 0;
    const subtotal = <?php echo isset($_SESSION['carrito']) ? array_sum(array_map(function($item) { return $item['precio'] * $item['cantidad']; }, $_SESSION['carrito'])) : 0; ?>;
    
    const descuentoMonto = (subtotal * descuento) / 100;
    const totalFinal = subtotal - descuentoMonto;
    
    if (descuento > 0) {
        document.getElementById('descuentoItem').style.display = 'flex';
        document.getElementById('descuentoValor').textContent = '$' + descuentoMonto.toLocaleString();
    } else {
        document.getElementById('descuentoItem').style.display = 'none';
    }
    
    document.getElementById('totalFinal').textContent = '$' + totalFinal.toLocaleString();
});

// Auto-focus on product selection
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('producto_id').focus();
});
</script>

<?php include 'includes/footer.php'; ?>

