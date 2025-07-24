<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();
include 'includes/header.php';

$mensaje = '';
$error = '';

// Procesar nueva devolución
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'nueva_devolucion') {
    $venta_id = intval($_POST['venta_id']);
    $motivo = escaparDato($conexion, $_POST['motivo']);
    $productos_devueltos = [];
    
    // Procesar productos devueltos
    if (isset($_POST['productos']) && is_array($_POST['productos'])) {
        foreach ($_POST['productos'] as $producto_id => $cantidad) {
            if ($cantidad > 0) {
                $producto = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM productos WHERE id = $producto_id"));
                if ($producto) {
                    $productos_devueltos[] = [
                        'id' => $producto_id,
                        'nombre' => $producto['nombre'],
                        'cantidad' => intval($cantidad),
                        'precio' => $producto['precio_venta'],
                        'subtotal' => intval($cantidad) * $producto['precio_venta']
                    ];
                }
            }
        }
    }
    
    if (empty($productos_devueltos)) {
        $error = '❌ Debes seleccionar al menos un producto para devolver';
    } elseif (empty($motivo)) {
        $error = '❌ Debes especificar el motivo de la devolución';
    } else {
        if (registrarDevolucion($conexion, $venta_id, $productos_devueltos, $motivo, $_SESSION['usuario'])) {
            $mensaje = '✅ Devolución registrada con éxito. Pendiente de aprobación.';
        } else {
            $error = '❌ Error al registrar la devolución';
        }
    }
}

// Aprobar/Rechazar devolución
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $devolucion_id = intval($_GET['id']);
    $accion = $_GET['accion'];
    
    if ($accion == 'aprobar') {
        $sql = "UPDATE devoluciones SET estado = 'aprobada' WHERE id = $devolucion_id";
        if (mysqli_query($conexion, $sql)) {
            // Restaurar stock de productos
            $detalles = mysqli_query($conexion, "SELECT * FROM detalle_devolucion WHERE devolucion_id = $devolucion_id");
            while ($detalle = mysqli_fetch_assoc($detalles)) {
                $sql = "UPDATE productos SET cantidad_actual = cantidad_actual + {$detalle['cantidad_devuelta']} WHERE id = {$detalle['producto_id']}";
                mysqli_query($conexion, $sql);
            }
            
            // Marcar alerta como leída
            $sql = "UPDATE alertas SET leida = TRUE WHERE tipo = 'devolucion_pendiente' AND mensaje LIKE '%$devolucion_id%'";
            mysqli_query($conexion, $sql);
            
            $mensaje = '✅ Devolución aprobada y stock restaurado';
        } else {
            $error = '❌ Error al aprobar la devolución';
        }
    } elseif ($accion == 'rechazar') {
        $sql = "UPDATE devoluciones SET estado = 'rechazada' WHERE id = $devolucion_id";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Devolución rechazada';
        } else {
            $error = '❌ Error al rechazar la devolución';
        }
    }
}

// Obtener devoluciones
$estado_filtro = $_GET['estado'] ?? '';
$where = '';
if ($estado_filtro) {
    $estado_escaped = escaparDato($conexion, $estado_filtro);
    $where = "WHERE d.estado = '$estado_escaped'";
}

$sql = "SELECT d.*, v.numero_venta, v.total_venta, c.nombre as cliente_nombre 
        FROM devoluciones d 
        LEFT JOIN ventas v ON d.venta_id = v.id 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        $where 
        ORDER BY d.fecha DESC";
$devoluciones = mysqli_query($conexion, $sql);

// Obtener ventas para nueva devolución
$ventas_disponibles = mysqli_query($conexion, "SELECT v.*, c.nombre as cliente_nombre FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id WHERE v.estado = 'completada' ORDER BY v.fecha DESC LIMIT 50");
?>

<div class="card" style="max-width:1200px; margin:0 auto 32px auto; background:#fff; border-radius:14px; box-shadow:0 2px 12px 0 rgba(108,99,255,0.08); padding:32px 24px 24px 24px;">
    <h3 style="font-size:1.2rem; font-weight:700; color:#222; margin-bottom:18px;">
        <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">autorenew</span> Sistema de Devoluciones
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
    
    <!-- Filtros -->
    <div style="margin-bottom: 24px;">
        <a href="devoluciones.php" class="btn <?= !$estado_filtro ? 'btn-edit' : 'btn-outline' ?>">Todas</a>
        <a href="devoluciones.php?estado=pendiente" class="btn <?= $estado_filtro == 'pendiente' ? 'btn-edit' : 'btn-outline' ?>">Pendientes</a>
        <a href="devoluciones.php?estado=aprobada" class="btn <?= $estado_filtro == 'aprobada' ? 'btn-edit' : 'btn-outline' ?>">Aprobadas</a>
        <a href="devoluciones.php?estado=rechazada" class="btn <?= $estado_filtro == 'rechazada' ? 'btn-edit' : 'btn-outline' ?>">Rechazadas</a>
    </div>
    
    <!-- Nueva Devolución -->
    <div style="margin-bottom: 32px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
        <h4 style="margin-bottom: 15px;">📝 Nueva Devolución</h4>
        <form method="post" id="formDevolucion">
            <input type="hidden" name="accion" value="nueva_devolucion">
            
            <div style="margin-bottom: 15px;">
                <label>Venta a devolver:</label>
                <select name="venta_id" id="venta_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">-- Seleccionar venta --</option>
                    <?php while($venta = mysqli_fetch_assoc($ventas_disponibles)): ?>
                        <option value="<?= $venta['id'] ?>">
                            <?= $venta['numero_venta'] ?> - 
                            <?= htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente general') ?> - 
                            <?= formatearMoneda($venta['total_venta']) ?> - 
                            <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Motivo de la devolución:</label>
                <textarea name="motivo" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; height: 80px;" placeholder="Describe el motivo de la devolución..."></textarea>
            </div>
            
            <div id="productosVenta" style="display: none;">
                <label>Productos a devolver:</label>
                <div id="listaProductos"></div>
            </div>
            
            <button type="submit" class="btn btn-edit" style="margin-top: 10px;">
                <span class="material-icons">add</span> Registrar Devolución
            </button>
        </form>
    </div>
    
    <!-- Lista de Devoluciones -->
    <h4 style="margin-bottom: 15px;">📋 Historial de Devoluciones</h4>
    <table class="productos-table">
        <tr>
            <th>Número</th>
            <th>Venta</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Usuario</th>
            <th>Acciones</th>
        </tr>
        <?php while($devolucion = mysqli_fetch_assoc($devoluciones)): ?>
        <tr>
            <td><?= htmlspecialchars($devolucion['numero_devolucion']) ?></td>
            <td><?= htmlspecialchars($devolucion['numero_venta']) ?></td>
            <td><?= htmlspecialchars($devolucion['cliente_nombre'] ?? 'Cliente general') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($devolucion['fecha'])) ?></td>
            <td><?= formatearMoneda($devolucion['total_devolucion']) ?></td>
            <td>
                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                     background: <?= $devolucion['estado'] == 'pendiente' ? '#fff3cd' : 
                                   ($devolucion['estado'] == 'aprobada' ? '#d4edda' : '#f8d7da') ?>; 
                     color: <?= $devolucion['estado'] == 'pendiente' ? '#856404' : 
                               ($devolucion['estado'] == 'aprobada' ? '#155724' : '#721c24') ?>;">
                    <?= ucfirst($devolucion['estado']) ?>
                </span>
            </td>
            <td><?= htmlspecialchars($devolucion['usuario']) ?></td>
            <td>
                <?php if ($devolucion['estado'] == 'pendiente' && esAdmin()): ?>
                    <a href="?accion=aprobar&id=<?= $devolucion['id'] ?>" class="btn btn-edit" onclick="return confirm('¿Aprobar esta devolución?')">
                        <span class="material-icons">check</span>
                    </a>
                    <a href="?accion=rechazar&id=<?= $devolucion['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Rechazar esta devolución?')">
                        <span class="material-icons">close</span>
                    </a>
                <?php endif; ?>
                <a href="ver_devolucion.php?id=<?= $devolucion['id'] ?>" class="btn btn-outline">
                    <span class="material-icons">visibility</span>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
document.getElementById('venta_id').addEventListener('change', function() {
    const ventaId = this.value;
    const productosDiv = document.getElementById('productosVenta');
    const listaProductos = document.getElementById('listaProductos');
    
    if (ventaId) {
        // Cargar productos de la venta seleccionada
        fetch('ajax/get_productos_venta.php?venta_id=' + ventaId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    data.productos.forEach(producto => {
                        html += `
                            <div style="display: flex; align-items: center; margin-bottom: 10px; padding: 10px; background: white; border-radius: 5px;">
                                <div style="flex: 1;">
                                    <strong>${producto.nombre}</strong><br>
                                    <small>Cantidad vendida: ${producto.cantidad_vendida} | Precio: ${producto.precio_unitario}</small>
                                </div>
                                <div style="margin-left: 10px;">
                                    <input type="number" name="productos[${producto.producto_id}]" 
                                           min="0" max="${producto.cantidad_vendida}" value="0" 
                                           style="width: 60px; padding: 4px; border: 1px solid #ddd; border-radius: 3px;">
                                </div>
                            </div>
                        `;
                    });
                    listaProductos.innerHTML = html;
                    productosDiv.style.display = 'block';
                }
            });
    } else {
        productosDiv.style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?> 