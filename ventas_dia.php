<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();

// Filtros para ventas del día
$fecha = $_GET['fecha'] ?? date('Y-m-d');

// Consulta de ventas del día
$sql = "SELECT v.*, u.usuario as usuario_nombre, c.nombre as cliente_nombre
        FROM ventas v
        LEFT JOIN usuarios u ON v.usuario = u.usuario
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE DATE(v.fecha) = '$fecha'
        ORDER BY v.fecha DESC";
$ventas = mysqli_query($conexion, $sql);

include 'includes/header.php';
?>

<div class="ventas-filtros">
    <label>Fecha:</label>
    <form method="get" style="display:inline;">
        <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
        <button class="btn btn-outline" type="submit"><span class="material-icons">search</span>Ver</button>
    </form>
</div>
<div class="ventas-filtros-bar">
    <span class="material-icons">search</span> Ventas del Día
</div>

<table class="ventas-table">
    <thead class="ventas-header">
        <tr>
            <th class="ventas-th">N° Venta</th>
            <th class="ventas-th">Fecha y Hora</th>
            <th class="ventas-th">Total</th>
            <th class="ventas-th">Método Pago</th>
            <th class="ventas-th">Cliente</th>
            <th class="ventas-th">Usuario</th>
            <th class="ventas-th">Detalle</th>
        </tr>
    </thead>
    <tbody>
        <?php while($venta = mysqli_fetch_assoc($ventas)): ?>
        <tr class="ventas-row">
            <td class="ventas-td"><a class="ventas-link" href="#">V<?= str_pad($venta['id'], 5, '0', STR_PAD_LEFT) ?></a></td>
            <td class="ventas-td">
                <?= date('d/m/Y', strtotime($venta['fecha'])) ?><br>
                <span style="color:#888;"><?= date('h:i a', strtotime($venta['fecha'])) ?></span>
            </td>
            <td class="ventas-td">
                <span class="ventas-total-cop">COP<br><?= formatearMoneda($venta['total_venta']) ?></span>
            </td>
            <td class="ventas-td">
                <?php
                $metodo = strtolower($venta['metodo_pago'] ?? '');
                if ($metodo == 'efectivo') {
                    echo '<span class="ventas-metodo-efectivo"><span class="material-icons">payments</span>Efectivo</span>';
                } elseif (strpos($metodo, 'tarjeta') !== false) {
                    echo '<span class="ventas-metodo-tarjeta"><span class="material-icons">credit_card</span>' . htmlspecialchars($venta['metodo_pago']) . '</span>';
                } elseif ($metodo == 'crédito' || $metodo == 'credito') {
                    echo '<span class="ventas-metodo-credito"><span class="material-icons">help</span>Crédito</span>';
                } else {
                    echo '<span class="ventas-metodo-otro"><span class="material-icons">payment</span>' . htmlspecialchars($venta['metodo_pago'] ?? 'Otro') . '</span>';
                }
                ?>
            </td>
            <td class="ventas-td">
                <?php
                if (!empty($venta['cliente_nombre'])) {
                    echo htmlspecialchars($venta['cliente_nombre']);
                } else {
                    echo 'Sin cliente';
                }
                ?>
            </td>
            <td class="ventas-td"><?= htmlspecialchars($venta['usuario_nombre'] ?? $venta['usuario']) ?></td>
            <td class="ventas-td">
                <div class="ventas-btns">
                    <a class="btn btn-icon btn-outline" href="#"><span class="material-icons">visibility</span>Ver detalle</a>
                    <a class="btn btn-primary" href="#"><span class="material-icons">print</span>Imprimir factura</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?> 