<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();
include 'includes/header.php';

$mensaje = '';
$error = '';

// Procesar nuevo pago
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'registrar_pago') {
    $venta_id = intval($_POST['venta_id']);
    $cliente_id = intval($_POST['cliente_id']);
    $monto_pago = floatval($_POST['monto_pago']);
    $metodo_pago = escaparDato($conexion, $_POST['metodo_pago']);
    $notas = escaparDato($conexion, $_POST['notas']);
    
    if ($monto_pago <= 0) {
        $error = '❌ El monto del pago debe ser mayor a 0';
    } else {
        if (registrarPagoCredito($conexion, $venta_id, $cliente_id, $monto_pago, $metodo_pago, $_SESSION['usuario'])) {
            $mensaje = '✅ Pago registrado con éxito';
        } else {
            $error = '❌ Error al registrar el pago';
        }
    }
}

// Obtener ventas a crédito
$estado_filtro = $_GET['estado'] ?? '';
$cliente_filtro = $_GET['cliente'] ?? '';

$where = "WHERE v.tipo_venta = 'credito'";
if ($estado_filtro) {
    $estado_escaped = escaparDato($conexion, $estado_filtro);
    $where .= " AND v.estado = '$estado_escaped'";
}
if ($cliente_filtro) {
    $cliente_escaped = escaparDato($conexion, $cliente_filtro);
    $where .= " AND c.nombre LIKE '%$cliente_escaped%'";
}

$sql = "SELECT v.*, c.nombre as cliente_nombre, c.saldo_pendiente,
        COALESCE(SUM(pc.monto_pago), 0) as total_pagado,
        (v.total_venta - COALESCE(SUM(pc.monto_pago), 0)) as saldo_restante
        FROM ventas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        LEFT JOIN pagos_credito pc ON v.id = pc.venta_id 
        $where 
        GROUP BY v.id 
        ORDER BY v.fecha DESC";
$ventas_credito = mysqli_query($conexion, $sql);

// Obtener clientes para filtro
$clientes = mysqli_query($conexion, "SELECT id, nombre FROM clientes WHERE activo = 1 ORDER BY nombre");

// Estadísticas de créditos
$sql_total_creditos = "SELECT COUNT(*) as total FROM ventas WHERE tipo_venta = 'credito'";
$resultado = mysqli_query($conexion, $sql_total_creditos);
$total_creditos = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_creditos_pendientes = "SELECT COUNT(*) as total FROM ventas WHERE tipo_venta = 'credito' AND estado = 'pendiente'";
$resultado = mysqli_query($conexion, $sql_creditos_pendientes);
$creditos_pendientes = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_total_pendiente = "SELECT COALESCE(SUM(v.total_venta - COALESCE(SUM(pc.monto_pago), 0)), 0) as total 
                        FROM ventas v 
                        LEFT JOIN pagos_credito pc ON v.id = pc.venta_id 
                        WHERE v.tipo_venta = 'credito' AND v.estado = 'pendiente' 
                        GROUP BY v.id";
$resultado = mysqli_query($conexion, $sql_total_pendiente);
$total_pendiente = 0;
while ($fila = mysqli_fetch_assoc($resultado)) {
    $total_pendiente += floatval($fila['total']);
}
?>

<div class="card" style="max-width:1200px; margin:0 auto 32px auto; background:#fff; border-radius:14px; box-shadow:0 2px 12px 0 rgba(108,99,255,0.08); padding:32px 24px 24px 24px;">
    <h3 style="font-size:1.2rem; font-weight:700; color:#222; margin-bottom:18px;">
        <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">credit_score</span> Sistema de Créditos y Cobranza
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
            <div style="font-size: 2rem; font-weight: bold;"><?= $total_creditos ?></div>
            <div>Total Créditos</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $creditos_pendientes ?></div>
            <div>Créditos Pendientes</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= formatearMoneda($total_pendiente) ?></div>
            <div>Total Pendiente</div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div style="margin-bottom: 24px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="creditos.php" class="btn <?= !$estado_filtro && !$cliente_filtro ? 'btn-edit' : 'btn-outline' ?>">Todas</a>
        <a href="creditos.php?estado=pendiente" class="btn <?= $estado_filtro == 'pendiente' ? 'btn-edit' : 'btn-outline' ?>">Pendientes</a>
        <a href="creditos.php?estado=completada" class="btn <?= $estado_filtro == 'completada' ? 'btn-edit' : 'btn-outline' ?>">Pagadas</a>
        
        <form method="get" style="display: flex; gap: 10px; align-items: center;">
            <?php if ($estado_filtro): ?>
                <input type="hidden" name="estado" value="<?= htmlspecialchars($estado_filtro) ?>">
            <?php endif; ?>
            <select name="cliente" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">Todos los clientes</option>
                <?php while($cliente = mysqli_fetch_assoc($clientes)): ?>
                    <option value="<?= htmlspecialchars($cliente['nombre']) ?>" <?= $cliente_filtro == $cliente['nombre'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cliente['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-outline">Filtrar</button>
        </form>
    </div>
    
    <!-- Lista de Ventas a Crédito -->
    <h4 style="margin-bottom: 15px;">📋 Ventas a Crédito</h4>
    <table class="productos-table">
        <tr>
            <th>Venta</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Total Venta</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while($venta = mysqli_fetch_assoc($ventas_credito)): ?>
        <tr>
            <td><?= htmlspecialchars($venta['numero_venta']) ?></td>
            <td><?= htmlspecialchars($venta['cliente_nombre']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td>
            <td><?= formatearMoneda($venta['total_venta']) ?></td>
            <td><?= formatearMoneda($venta['total_pagado']) ?></td>
            <td style="font-weight: bold; color: <?= $venta['saldo_restante'] > 0 ? '#f00' : '#0f0' ?>;">
                <?= formatearMoneda($venta['saldo_restante']) ?>
            </td>
            <td>
                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                     background: <?= $venta['estado'] == 'pendiente' ? '#fff3cd' : '#d4edda' ?>; 
                     color: <?= $venta['estado'] == 'pendiente' ? '#856404' : '#155724' ?>;">
                    <?= ucfirst($venta['estado']) ?>
                </span>
            </td>
            <td>
                <?php if ($venta['saldo_restante'] > 0): ?>
                    <button onclick="mostrarModalPago(<?= $venta['id'] ?>, '<?= htmlspecialchars($venta['cliente_nombre']) ?>', <?= $venta['cliente_id'] ?>, <?= $venta['saldo_restante'] ?>)" class="btn btn-edit">
                        <span class="material-icons">payment</span>
                    </button>
                <?php endif; ?>
                <a href="ver_credito.php?id=<?= $venta['id'] ?>" class="btn btn-outline">
                    <span class="material-icons">visibility</span>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Modal para registrar pago -->
<div id="modalPago" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; min-width: 400px;">
        <h3 style="margin-bottom: 20px;">💳 Registrar Pago</h3>
        
        <form method="post" id="formPago">
            <input type="hidden" name="accion" value="registrar_pago">
            <input type="hidden" name="venta_id" id="venta_id_pago">
            <input type="hidden" name="cliente_id" id="cliente_id_pago">
            
            <div style="margin-bottom: 15px;">
                <label>Cliente:</label>
                <div id="cliente_nombre_pago" style="padding: 8px; background: #f8f9fa; border-radius: 5px;"></div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Saldo pendiente:</label>
                <div id="saldo_pendiente_pago" style="padding: 8px; background: #f8f9fa; border-radius: 5px; font-weight: bold;"></div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Monto del pago:</label>
                <input type="number" name="monto_pago" id="monto_pago" step="0.01" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Método de pago:</label>
                <select name="metodo_pago" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label>Notas (opcional):</label>
                <textarea name="notas" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; height: 60px;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="cerrarModalPago()" class="btn btn-delete">Cancelar</button>
                <button type="submit" class="btn btn-edit">Registrar Pago</button>
            </div>
        </form>
    </div>
</div>

<script>
function mostrarModalPago(ventaId, clienteNombre, clienteId, saldoRestante) {
    document.getElementById('venta_id_pago').value = ventaId;
    document.getElementById('cliente_id_pago').value = clienteId;
    document.getElementById('cliente_nombre_pago').textContent = clienteNombre;
    document.getElementById('saldo_pendiente_pago').textContent = '$' + saldoRestante.toFixed(2);
    document.getElementById('monto_pago').max = saldoRestante;
    document.getElementById('monto_pago').value = saldoRestante;
    document.getElementById('modalPago').style.display = 'block';
}

function cerrarModalPago() {
    document.getElementById('modalPago').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalPago').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalPago();
    }
});
</script>

<?php include 'includes/footer.php'; ?> 