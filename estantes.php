<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
include 'includes/header.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

// Obtener lista de estantes (categorías)
$estantes = mysqli_query($conexion, "SELECT DISTINCT c.id, c.nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE c.id IS NOT NULL");

// Filtrar por estante seleccionado
$familiaSeleccionada = isset($_GET['familia']) ? mysqli_real_escape_string($conexion, $_GET['familia']) : '';

$productos = [];
$totalProductos = 0;
$totalGanancias = 0;

if (!empty($familiaSeleccionada)) {
    $consulta = mysqli_query($conexion, "SELECT * FROM productos WHERE categoria_id = '$familiaSeleccionada'");
    while ($p = mysqli_fetch_assoc($consulta)) {
        $productos[] = $p;
        $totalProductos += $p['cantidad_inicial'];
        $vendidos = $p['cantidad_inicial'] - $p['cantidad_actual'];
        $ganancia = ($p['precio_venta'] - $p['precio_compra']) * $vendidos;
        $totalGanancias += $ganancia;
    }
}
?>

<h2>🗂 Reporte por Estantes (Categorías)</h2>

<form method="get">
    <label>Selecciona un Estante:</label>
    <select name="familia" required>
        <option value="">-- Elegir --</option>
        <?php while($fila = mysqli_fetch_assoc($estantes)): ?>
            <option value="<?= $fila['id'] ?>" <?= ($familiaSeleccionada == $fila['id']) ? 'selected' : '' ?>>
                <?= $fila['nombre'] ?>
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit">🔍 Ver Reporte</button>
</form>

<?php if (!empty($productos)): ?>
    <h3>📋 Productos en «<?= htmlspecialchars($familiaSeleccionada) ?>»</h3>
    <table class="table">
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Stock</th>
            <th>Precio Venta</th>
            <th>Precio Compra</th>
            <th>Vendidos</th>
            <th>Ganancia</th>
        </tr>
        <?php foreach($productos as $producto): ?>
        <?php $vendidos = $producto['cantidad_inicial'] - $producto['cantidad_actual'];
              $ganancia = ($producto['precio_venta'] - $producto['precio_compra']) * $vendidos; ?>
        <tr>
            <td><?= htmlspecialchars($producto['codigo']) ?></td>
            <td><?= htmlspecialchars($producto['nombre']) ?></td>
            <td><?= $producto['cantidad_actual'] ?></td>
            <td><?= formatearMoneda($producto['precio_venta']) ?></td>
            <td><?= formatearMoneda($producto['precio_compra']) ?></td>
            <td><?= $vendidos ?></td>
            <td><?= formatearMoneda($ganancia) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <div style="margin-top:16px; font-weight:bold; color:#222;">Ganancia total del estante: <?= formatearMoneda($totalGanancias) ?></div>
<?php elseif ($familiaSeleccionada): ?>
    <p style="color:red;">❌ No se encontraron productos en este estante.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
