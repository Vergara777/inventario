<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';

// Protección de sesión
redirigirSiNoSesion();

// Obtener estadísticas del dashboard
$estadisticas = obtenerEstadisticasDashboard($conexion);

// Filtros para reportes
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Día actual
$usuario_filtro = $_GET['usuario'] ?? '';
$familia_filtro = $_GET['familia'] ?? '';
// Cambiar a filtro por categoría
$categoria_filtro = $_GET['categoria'] ?? '';

// Construir consultas con filtros
$where_ventas = "WHERE v.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'";
if (!empty($usuario_filtro)) {
    $where_ventas .= " AND v.usuario = '" . escaparDato($conexion, $usuario_filtro) . "'";
}

$where_productos = "WHERE p.cantidad_actual BETWEEN 0 AND 20";
if (!empty($categoria_filtro)) {
    $where_productos .= " AND p.categoria_id = '" . intval($categoria_filtro) . "'";
}

// Consultas para reportes
$sql_ventas_periodo = "SELECT COUNT(*) as total FROM ventas v $where_ventas";
$resultado = mysqli_query($conexion, $sql_ventas_periodo);
$ventas_periodo = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_ganancias_periodo = "SELECT COALESCE(SUM(utilidad_total), 0) as total FROM ventas v $where_ventas";
$resultado = mysqli_query($conexion, $sql_ganancias_periodo);
$ganancias_periodo = floatval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_productos_agotados_filtro = "SELECT COUNT(*) as total FROM productos p $where_productos";
$resultado = mysqli_query($conexion, $sql_productos_agotados_filtro);
$productos_agotados_filtro = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

// Obtener datos para tablas
$sql_ventas_detalle = "SELECT v.*, COUNT(dv.id) as total_productos 
                       FROM ventas v 
                       LEFT JOIN detalle_venta dv ON v.id = dv.venta_id 
                       $where_ventas
                       GROUP BY v.id 
                       ORDER BY v.fecha DESC 
                       LIMIT 20";
$ventas_detalle = mysqli_query($conexion, $sql_ventas_detalle);

$sql_productos_agotados = "SELECT p.*, c.nombre AS categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id $where_productos ORDER BY p.cantidad_actual ASC, p.nombre ASC";
$productos_agotados = mysqli_query($conexion, $sql_productos_agotados);

$sql_usuarios_activos = "SELECT u.usuario, u.rol, COUNT(v.id) as total_ventas, COALESCE(SUM(v.utilidad_total), 0) as ganancias_totales
                         FROM usuarios u 
                         LEFT JOIN ventas v ON u.usuario = v.usuario AND v.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'
                         GROUP BY u.id, u.usuario, u.rol
                         ORDER BY total_ventas DESC";
$usuarios_activos = mysqli_query($conexion, $sql_usuarios_activos);

// Obtener listas para filtros
$sql_usuarios = "SELECT DISTINCT usuario FROM usuarios ORDER BY usuario";
$usuarios_lista = mysqli_query($conexion, $sql_usuarios);

$sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre";
$categorias_lista = mysqli_query($conexion, $sql_categorias);

include 'includes/header.php';
?>

<h2 style="text-align:center; font-size:2rem; text-shadow:0 0 10px #0ff;">📊 Reportes y Estadísticas</h2>

<!-- Filtros -->
<div class="card">
    <h3 style="text-align:center;">🔍 Filtros de Reporte</h3>
    <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div>
            <label>Fecha Inicio:</label>
            <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
        </div>
        <div>
            <label>Fecha Fin:</label>
            <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
        </div>
        <div>
            <label>Usuario:</label>
            <select name="usuario">
                <option value="">Todos los usuarios</option>
                <?php while($u = mysqli_fetch_assoc($usuarios_lista)): ?>
                <option value="<?= htmlspecialchars($u['usuario']) ?>" <?= $usuario_filtro == $u['usuario'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['usuario']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Categoría:</label>
            <select name="categoria">
                <option value="">Todas las categorías</option>
                <?php while($c = mysqli_fetch_assoc($categorias_lista)): ?>
                <option value="<?= $c['id'] ?>" <?= $categoria_filtro == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div style="grid-column: 1 / -1; text-align: center;">
            <button type="submit" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">🔍 Aplicar Filtros</button>
            <a href="reportes.php" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-left: 10px;">🔄 Limpiar</a>
        </div>
    </form>
</div>

<!-- Resumen de estadísticas -->
<div class="card">
    <h3 style="text-align:center;">📈 Resumen del Período</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <div style="background: rgba(0,255,255,0.1); padding: 15px; border-radius: 10px; border: 1px solid rgba(0,255,255,0.3);">
            <h4 style="color: #0ff; margin: 0 0 10px 0;">🛒 Ventas del Período</h4>
            <p style="font-size: 1.5rem; font-weight: bold; color: #fff; margin: 0;"><?= number_format($ventas_periodo) ?></p>
        </div>
        
        <div style="background: rgba(0,255,0,0.1); padding: 15px; border-radius: 10px; border: 1px solid rgba(0,255,0,0.3);">
            <h4 style="color: #0f0; margin: 0 0 10px 0;">💰 Ganancias del Período</h4>
            <p style="font-size: 1.5rem; font-weight: bold; color: #fff; margin: 0;"><?= formatearMoneda($ganancias_periodo) ?></p>
        </div>
        
        <div style="background: rgba(255,165,0,0.1); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,165,0,0.3);">
            <h4 style="color: #ffa500; margin: 0 0 10px 0;">⚠️ Productos Agotados</h4>
            <p style="font-size: 1.5rem; font-weight: bold; color: #fff; margin: 0;"><?= number_format($productos_agotados_filtro) ?></p>
        </div>
    </div>
</div>

<!-- Ventas del período -->
<div class="card">
    <h3 style="text-align:center;">📋 Ventas del Período</h3>
    <table class="table">
        <tr>
            <th>Número</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Utilidad</th>
            <th>Productos</th>
            <th>Usuario</th>
        </tr>
        <?php while($venta = mysqli_fetch_assoc($ventas_detalle)): ?>
        <tr>
            <td><?= htmlspecialchars($venta['numero_venta']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td>
            <td><?= formatearMoneda($venta['total_venta']) ?></td>
            <td style="color: #0f0;"><?= formatearMoneda($venta['utilidad_total']) ?></td>
            <td><?= $venta['total_productos'] ?></td>
            <td><?= htmlspecialchars($venta['usuario']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Productos agotados -->
<div class="card">
    <h3 style="text-align:center;">⚠️ Productos Agotados</h3>
    <table class="table">
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Stock</th>
            <th>Precio Venta</th>
            <th>Categoría</th>
        </tr>
        <?php while($producto = mysqli_fetch_assoc($productos_agotados)): ?>
        <tr>
            <td><?= htmlspecialchars($producto['codigo']) ?></td>
            <td><?= htmlspecialchars($producto['nombre']) ?></td>
            <td style="color: <?= $producto['cantidad_actual'] == 0 ? '#ff0000' : '#ffa500' ?>; font-weight: bold;">
                <?= $producto['cantidad_actual'] ?>
            </td>
            <td><?= formatearMoneda($producto['precio_venta']) ?></td>
            <td><?= htmlspecialchars($producto['categoria_nombre'] ?? '') ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Rendimiento de usuarios -->
<div class="card">
    <h3 style="text-align:center;">👥 Rendimiento de Usuarios</h3>
    <table class="table">
        <tr>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Ventas</th>
            <th>Ganancias Generadas</th>
        </tr>
        <?php while($usuario = mysqli_fetch_assoc($usuarios_activos)): ?>
        <tr>
            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
            <td>
                <span style="color: <?= $usuario['rol'] == 'admin' ? '#ff6b6b' : '#4ecdc4' ?>; font-weight: bold;">
                    <?= ucfirst($usuario['rol']) ?>
                </span>
            </td>
            <td><?= number_format($usuario['total_ventas']) ?></td>
            <td style="color: #0f0;"><?= formatearMoneda($usuario['ganancias_totales']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
