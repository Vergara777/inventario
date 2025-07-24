<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';

// Protección de sesión
redirigirSiNoSesion();

// Filtros
$familia_filtro = $_GET['familia'] ?? '';
// Cambiar a filtro por categoría
$categoria_filtro = $_GET['categoria'] ?? '';
$stock_minimo = $_GET['stock_minimo'] ?? 20;

// Construir consulta con filtros
$where = "WHERE p.cantidad_actual <= $stock_minimo";
if (!empty($familia_filtro)) {
    $where .= " AND p.familia = '" . escaparDato($conexion, $familia_filtro) . "'";
}
// Cambiar a:
if (!empty($categoria_filtro)) {
    $where .= " AND p.categoria_id = '" . intval($categoria_filtro) . "'";
}

// Estadísticas de productos agotados
$sql_total_agotados = "SELECT COUNT(*) as total FROM productos p $where";
$resultado = mysqli_query($conexion, $sql_total_agotados);
$total_agotados = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_valor_agotados = "SELECT COALESCE(SUM(cantidad_actual * precio_venta), 0) as valor FROM productos p $where";
$resultado = mysqli_query($conexion, $sql_valor_agotados);
$valor_agotados = floatval(mysqli_fetch_assoc($resultado)['valor'] ?? 0);

// Obtener productos agotados
$sql_productos = "SELECT p.*, c.nombre AS categoria_nombre, (p.cantidad_inicial - p.cantidad_actual) as vendidos, ROUND(((p.cantidad_inicial - p.cantidad_actual) / p.cantidad_inicial) * 100, 2) as porcentaje_vendido FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id $where ORDER BY p.cantidad_actual ASC, p.nombre ASC";
$productos_agotados = mysqli_query($conexion, $sql_productos);

// Obtener familias para filtro
$sql_familias = "SELECT DISTINCT familia FROM productos WHERE familia IS NOT NULL AND familia != '' ORDER BY familia";
$familias_lista = mysqli_query($conexion, $sql_familias);
// Cambiar a:
$sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre";
$categorias_lista = mysqli_query($conexion, $sql_categorias);

include 'includes/header.php';
?>

<h2 style="text-align:center; font-size:2rem; text-shadow:0 0 10px #0ff;">⚠️ Productos Agotados</h2>

<!-- Filtros -->
<div class="card">
    <h3 style="text-align:center;">🔍 Filtros</h3>
    <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div>
            <label>Stock Máximo:</label>
            <input type="number" name="stock_minimo" value="<?= htmlspecialchars($stock_minimo) ?>" min="0" max="100">
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
            <a href="agotados.php" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin-left: 10px;">🔄 Limpiar</a>
        </div>
    </form>
</div>

<!-- Resumen de estadísticas -->
<div class="card">
    <h3 style="text-align:center;">📊 Resumen de Productos Agotados</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <div style="background: rgba(255,165,0,0.1); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,165,0,0.3);">
            <h4 style="color: #ffa500; margin: 0 0 10px 0;">📦 Total Productos</h4>
            <p style="font-size: 1.5rem; font-weight: bold; color: #fff; margin: 0;"><?= number_format($total_agotados) ?></p>
        </div>
        
        <div style="background: rgba(255,0,0,0.1); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,0,0,0.3);">
            <h4 style="color: #ff0000; margin: 0 0 10px 0;">💰 Valor en Stock</h4>
            <p style="font-size: 1.5rem; font-weight: bold; color: #fff; margin: 0;"><?= formatearMoneda($valor_agotados) ?></p>
        </div>
    </div>
</div>

<!-- Lista de productos agotados -->
<div class="card">
    <h3 style="text-align:center;">📋 Lista de Productos</h3>
    <?php if (mysqli_num_rows($productos_agotados) > 0): ?>
        <table class="table">
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Stock Actual</th>
                <th>Stock Inicial</th>
                <th>Vendidos</th>
                <th>% Vendido</th>
                <th>Precio Venta</th>
                <th>Categoría</th>
                <th>Estado</th>
            </tr>
            <?php while($producto = mysqli_fetch_assoc($productos_agotados)): ?>
            <tr>
                <td><?= htmlspecialchars($producto['codigo']) ?></td>
                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                <td style="color: <?= $producto['cantidad_actual'] == 0 ? '#ff0000' : '#ffa500' ?>; font-weight: bold;">
                    <?= $producto['cantidad_actual'] ?>
                </td>
                <td><?= $producto['cantidad_inicial'] ?></td>
                <td><?= $producto['vendidos'] ?></td>
                <td>
                    <div style="background: #333; border-radius: 10px; height: 20px; position: relative;">
                        <div style="background: <?= $producto['porcentaje_vendido'] > 80 ? '#ff0000' : ($producto['porcentaje_vendido'] > 60 ? '#ffa500' : '#ffff00') ?>; 
                                    height: 100%; border-radius: 10px; width: <?= min($producto['porcentaje_vendido'], 100) ?>%;"></div>
                        <span style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); color: #fff; font-size: 12px;">
                            <?= $producto['porcentaje_vendido'] ?>%
                        </span>
                    </div>
                </td>
                <td><?= formatearMoneda($producto['precio_venta']) ?></td>
                <td><?= htmlspecialchars($producto['categoria_nombre'] ?? '') ?></td>
                <td>
                    <?php if ($producto['cantidad_actual'] == 0): ?>
                        <span style="color: #ff0000; font-weight: bold;">🔴 AGOTADO</span>
                    <?php elseif ($producto['cantidad_actual'] <= 5): ?>
                        <span style="color: #ffa500; font-weight: bold;">🟠 CRÍTICO</span>
                    <?php else: ?>
                        <span style="color: #ffff00; font-weight: bold;">🟡 BAJO</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #0f0; font-size: 1.1rem;">✅ No hay productos agotados con los filtros actuales.</p>
    <?php endif; ?>
</div>

<!-- Recomendaciones -->
<div class="card">
    <h3 style="text-align:center;">💡 Recomendaciones</h3>
    <div style="background: rgba(0,255,255,0.1); padding: 15px; border-radius: 10px; border: 1px solid rgba(0,255,255,0.3);">
        <ul style="color: #fff; margin: 0; padding-left: 20px;">
            <li>Revisa regularmente los productos con stock bajo</li>
            <li>Establece alertas automáticas para productos críticos</li>
            <li>Analiza las tendencias de venta para optimizar inventario</li>
            <li>Considera reabastecer productos con alta demanda</li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
