<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();
include 'includes/header.php';

$mensaje = '';
$error = '';

// Crear nueva orden de compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'nueva_orden') {
    $proveedor_id = intval($_POST['proveedor_id']);
    $fecha_orden = escaparDato($conexion, $_POST['fecha_orden']);
    $fecha_entrega = escaparDato($conexion, $_POST['fecha_entrega']);
    $notas = escaparDato($conexion, $_POST['notas']);
    $productos = $_POST['productos'] ?? [];
    
    if ($proveedor_id <= 0 || empty($fecha_orden) || empty($productos)) {
        $error = '❌ Por favor, completa todos los campos obligatorios';
    } else {
        $numero_orden = 'OC-' . rand(10000, 99999);
        $total_orden = 0;
        
        // Calcular total
        foreach ($productos as $producto) {
            $total_orden += floatval($producto['cantidad']) * floatval($producto['precio']);
        }
        
        $sql = "INSERT INTO ordenes_compra (numero_orden, proveedor_id, fecha_orden, fecha_entrega_esperada, total_orden, usuario, notas) 
                VALUES ('$numero_orden', $proveedor_id, '$fecha_orden', '$fecha_entrega', $total_orden, '{$_SESSION['usuario']}', '$notas')";
        
        if (mysqli_query($conexion, $sql)) {
            $orden_id = mysqli_insert_id($conexion);
            
            // Insertar detalles
            foreach ($productos as $producto) {
                $producto_id = intval($producto['id']);
                $cantidad = intval($producto['cantidad']);
                $precio = floatval($producto['precio']);
                $subtotal = $cantidad * $precio;
                
                $sql = "INSERT INTO detalle_orden_compra (orden_id, producto_id, cantidad_solicitada, precio_unitario, subtotal) 
                        VALUES ($orden_id, $producto_id, $cantidad, $precio, $subtotal)";
                mysqli_query($conexion, $sql);
            }
            
            $mensaje = '✅ Orden de compra creada con éxito: ' . $numero_orden;
        } else {
            $error = '❌ Error al crear orden de compra: ' . mysqli_error($conexion);
        }
    }
}

// Cambiar estado de orden
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $orden_id = intval($_GET['id']);
    $accion = $_GET['accion'];
    
    if (in_array($accion, ['confirmar', 'recibir', 'cancelar'])) {
        $estado = $accion == 'confirmar' ? 'confirmada' : ($accion == 'recibir' ? 'recibida' : 'cancelada');
        
        $sql = "UPDATE ordenes_compra SET estado = '$estado' WHERE id = $orden_id";
        if (mysqli_query($conexion, $sql)) {
            if ($accion == 'recibir') {
                // Actualizar stock de productos
                $detalles = mysqli_query($conexion, "SELECT * FROM detalle_orden_compra WHERE orden_id = $orden_id");
                while ($detalle = mysqli_fetch_assoc($detalles)) {
                    $sql = "UPDATE productos SET cantidad_actual = cantidad_actual + {$detalle['cantidad_solicitada']} WHERE id = {$detalle['producto_id']}";
                    mysqli_query($conexion, $sql);
                }
            }
            $mensaje = '✅ Estado de orden actualizado con éxito';
        } else {
            $error = '❌ Error al actualizar estado';
        }
    }
}

// Filtros
$estado_filtro = $_GET['estado'] ?? '';
$proveedor_filtro = $_GET['proveedor'] ?? '';

$where = "WHERE 1=1";
if ($estado_filtro) {
    $estado_escaped = escaparDato($conexion, $estado_filtro);
    $where .= " AND oc.estado = '$estado_escaped'";
}
if ($proveedor_filtro) {
    $proveedor_escaped = escaparDato($conexion, $proveedor_filtro);
    $where .= " AND p.nombre LIKE '%$proveedor_escaped%'";
}

// Obtener órdenes de compra
$sql = "SELECT oc.*, p.nombre as proveedor_nombre 
        FROM ordenes_compra oc 
        LEFT JOIN proveedores p ON oc.proveedor_id = p.id 
        $where 
        ORDER BY oc.fecha_orden DESC";
$ordenes = mysqli_query($conexion, $sql);

// Obtener proveedores para filtro y formulario
$proveedores = mysqli_query($conexion, "SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre");

// Obtener productos para formulario
$productos = mysqli_query($conexion, "SELECT id, nombre, precio_compra FROM productos WHERE activo = 1 ORDER BY nombre");

// Estadísticas
$sql_total_ordenes = "SELECT COUNT(*) as total FROM ordenes_compra";
$resultado = mysqli_query($conexion, $sql_total_ordenes);
$total_ordenes = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_ordenes_pendientes = "SELECT COUNT(*) as total FROM ordenes_compra WHERE estado = 'pendiente'";
$resultado = mysqli_query($conexion, $sql_ordenes_pendientes);
$ordenes_pendientes = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_total_valor = "SELECT COALESCE(SUM(total_orden), 0) as total FROM ordenes_compra WHERE estado IN ('pendiente', 'confirmada')";
$resultado = mysqli_query($conexion, $sql_total_valor);
$total_valor = floatval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
?>

<div class="card" style="max-width:1200px; margin:0 auto 32px auto; background:#fff; border-radius:14px; box-shadow:0 2px 12px 0 rgba(108,99,255,0.08); padding:32px 24px 24px 24px;">
    <h3 style="font-size:1.2rem; font-weight:700; color:#222; margin-bottom:18px;">
        <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">shopping_cart</span> Órdenes de Compra
    </h3>
    
    <?php if (!empty($mensaje)): ?>
        <div style="background: rgba(0,255,0,0.1); color: #0f0; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div style="background: rgba(255,0,0,0.1); color: #f00; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <!-- Estadísticas -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $total_ordenes ?></div>
            <div>Total Órdenes</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $ordenes_pendientes ?></div>
            <div>Órdenes Pendientes</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= formatearMoneda($total_valor) ?></div>
            <div>Valor Pendiente</div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div style="margin-bottom: 24px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="ordenes_compra.php" class="btn <?= !$estado_filtro && !$proveedor_filtro ? 'btn-edit' : 'btn-outline' ?>">Todas</a>
        <a href="ordenes_compra.php?estado=pendiente" class="btn <?= $estado_filtro == 'pendiente' ? 'btn-edit' : 'btn-outline' ?>">Pendientes</a>
        <a href="ordenes_compra.php?estado=confirmada" class="btn <?= $estado_filtro == 'confirmada' ? 'btn-edit' : 'btn-outline' ?>">Confirmadas</a>
        <a href="ordenes_compra.php?estado=recibida" class="btn <?= $estado_filtro == 'recibida' ? 'btn-edit' : 'btn-outline' ?>">Recibidas</a>
        
        <form method="get" style="display: flex; gap: 10px; align-items: center;">
            <?php if ($estado_filtro): ?>
                <input type="hidden" name="estado" value="<?= htmlspecialchars($estado_filtro) ?>">
            <?php endif; ?>
            <select name="proveedor" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">Todos los proveedores</option>
                <?php 
                mysqli_data_seek($proveedores, 0);
                while($proveedor = mysqli_fetch_assoc($proveedores)): 
                ?>
                    <option value="<?= htmlspecialchars($proveedor['nombre']) ?>" <?= $proveedor_filtro == $proveedor['nombre'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($proveedor['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-outline">Filtrar</button>
        </form>
    </div>
    
    <!-- Nueva Orden de Compra -->
    <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
        <h4 style="margin-bottom: 15px;">📝 Nueva Orden de Compra</h4>
        <form method="post" id="formOrden">
            <input type="hidden" name="accion" value="nueva_orden">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>Proveedor:</label>
                    <select name="proveedor_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">Seleccionar proveedor</option>
                        <?php 
                        mysqli_data_seek($proveedores, 0);
                        while($proveedor = mysqli_fetch_assoc($proveedores)): 
                        ?>
                            <option value="<?= $proveedor['id'] ?>"><?= htmlspecialchars($proveedor['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Fecha de orden:</label>
                    <input type="date" name="fecha_orden" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>Fecha de entrega esperada:</label>
                    <input type="date" name="fecha_entrega" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Notas:</label>
                    <input type="text" name="notas" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Productos:</label>
                <div id="productosOrden">
                    <div class="producto-orden" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;">
                        <select name="productos[0][id]" required style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">Seleccionar producto</option>
                            <?php 
                            mysqli_data_seek($productos, 0);
                            while($producto = mysqli_fetch_assoc($productos)): 
                            ?>
                                <option value="<?= $producto['id'] ?>" data-precio="<?= $producto['precio_compra'] ?>">
                                    <?= htmlspecialchars($producto['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="productos[0][cantidad]" placeholder="Cantidad" min="1" required style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <input type="number" name="productos[0][precio]" placeholder="Precio" step="0.01" required style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <input type="text" name="productos[0][subtotal]" placeholder="Subtotal" readonly style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; background: #f8f9fa;">
                        <button type="button" onclick="eliminarProducto(this)" class="btn btn-delete">🗑️</button>
                    </div>
                </div>
                <button type="button" onclick="agregarProducto()" class="btn btn-outline" style="margin-top: 10px;">
                    <span class="material-icons">add</span> Agregar Producto
                </button>
            </div>
            
            <button type="submit" class="btn btn-edit">
                <span class="material-icons">add</span> Crear Orden de Compra
            </button>
        </form>
    </div>
    
    <!-- Lista de Órdenes -->
    <h4 style="margin-bottom: 15px;">📋 Historial de Órdenes de Compra</h4>
    <table class="productos-table">
        <tr>
            <th>Número</th>
            <th>Proveedor</th>
            <th>Fecha Orden</th>
            <th>Entrega</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Usuario</th>
            <th>Acciones</th>
        </tr>
        <?php while($orden = mysqli_fetch_assoc($ordenes)): ?>
        <tr>
            <td><strong><?= htmlspecialchars($orden['numero_orden']) ?></strong></td>
            <td><?= htmlspecialchars($orden['proveedor_nombre']) ?></td>
            <td><?= date('d/m/Y', strtotime($orden['fecha_orden'])) ?></td>
            <td><?= $orden['fecha_entrega_esperada'] ? date('d/m/Y', strtotime($orden['fecha_entrega_esperada'])) : '-' ?></td>
            <td><?= formatearMoneda($orden['total_orden']) ?></td>
            <td>
                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                     background: <?= $orden['estado'] == 'pendiente' ? '#fff3cd' : 
                                   ($orden['estado'] == 'confirmada' ? '#d1ecf1' : 
                                   ($orden['estado'] == 'recibida' ? '#d4edda' : '#f8d7da')) ?>; 
                     color: <?= $orden['estado'] == 'pendiente' ? '#856404' : 
                               ($orden['estado'] == 'confirmada' ? '#0c5460' : 
                               ($orden['estado'] == 'recibida' ? '#155724' : '#721c24')) ?>;">
                    <?= ucfirst($orden['estado']) ?>
                </span>
            </td>
            <td><?= htmlspecialchars($orden['usuario']) ?></td>
            <td>
                <?php if ($orden['estado'] == 'pendiente'): ?>
                    <a href="?accion=confirmar&id=<?= $orden['id'] ?>" class="btn btn-edit" onclick="return confirm('¿Confirmar esta orden?')">
                        <span class="material-icons">check</span>
                    </a>
                    <a href="?accion=cancelar&id=<?= $orden['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Cancelar esta orden?')">
                        <span class="material-icons">close</span>
                    </a>
                <?php elseif ($orden['estado'] == 'confirmada'): ?>
                    <a href="?accion=recibir&id=<?= $orden['id'] ?>" class="btn btn-edit" onclick="return confirm('¿Marcar como recibida? Esto actualizará el stock.')">
                        <span class="material-icons">inventory</span>
                    </a>
                <?php endif; ?>
                <a href="ver_orden.php?id=<?= $orden['id'] ?>" class="btn btn-outline">
                    <span class="material-icons">visibility</span>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
let contadorProductos = 1;

function agregarProducto() {
    const container = document.getElementById('productosOrden');
    const nuevoProducto = document.createElement('div');
    nuevoProducto.className = 'producto-orden';
    nuevoProducto.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;';
    
    nuevoProducto.innerHTML = `
        <select name="productos[${contadorProductos}][id]" required style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
            <option value="">Seleccionar producto</option>
            <?php 
            mysqli_data_seek($productos, 0);
            while($producto = mysqli_fetch_assoc($productos)): 
            ?>
                <option value="<?= $producto['id'] ?>" data-precio="<?= $producto['precio_compra'] ?>">
                    <?= htmlspecialchars($producto['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="productos[${contadorProductos}][cantidad]" placeholder="Cantidad" min="1" required style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
        <input type="number" name="productos[${contadorProductos}][precio]" placeholder="Precio" step="0.01" required style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
        <input type="text" name="productos[${contadorProductos}][subtotal]" placeholder="Subtotal" readonly style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; background: #f8f9fa;">
        <button type="button" onclick="eliminarProducto(this)" class="btn btn-delete">🗑️</button>
    `;
    
    container.appendChild(nuevoProducto);
    contadorProductos++;
    
    // Agregar event listeners al nuevo producto
    const select = nuevoProducto.querySelector('select');
    const cantidad = nuevoProducto.querySelector('input[name*="[cantidad]"]');
    const precio = nuevoProducto.querySelector('input[name*="[precio]"]');
    const subtotal = nuevoProducto.querySelector('input[name*="[subtotal]"]');
    
    select.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.dataset.precio) {
            precio.value = option.dataset.precio;
            calcularSubtotal(cantidad, precio, subtotal);
        }
    });
    
    cantidad.addEventListener('input', () => calcularSubtotal(cantidad, precio, subtotal));
    precio.addEventListener('input', () => calcularSubtotal(cantidad, precio, subtotal));
}

function eliminarProducto(button) {
    const productos = document.querySelectorAll('.producto-orden');
    if (productos.length > 1) {
        button.closest('.producto-orden').remove();
    }
}

function calcularSubtotal(cantidad, precio, subtotal) {
    const cant = parseFloat(cantidad.value) || 0;
    const prec = parseFloat(precio.value) || 0;
    subtotal.value = (cant * prec).toFixed(2);
}

// Event listeners para el primer producto
document.addEventListener('DOMContentLoaded', function() {
    const select = document.querySelector('select[name="productos[0][id]"]');
    const cantidad = document.querySelector('input[name="productos[0][cantidad]"]');
    const precio = document.querySelector('input[name="productos[0][precio]"]');
    const subtotal = document.querySelector('input[name="productos[0][subtotal]"]');
    
    select.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.dataset.precio) {
            precio.value = option.dataset.precio;
            calcularSubtotal(cantidad, precio, subtotal);
        }
    });
    
    cantidad.addEventListener('input', () => calcularSubtotal(cantidad, precio, subtotal));
    precio.addEventListener('input', () => calcularSubtotal(cantidad, precio, subtotal));
});
</script>

<?php include 'includes/footer.php'; ?> 