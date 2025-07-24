<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();
include 'includes/header.php';

$mensaje = '';
$error = '';

// Marcar alerta como leída
if (isset($_GET['marcar_leida']) && is_numeric($_GET['marcar_leida'])) {
    $alerta_id = intval($_GET['marcar_leida']);
    marcarAlertaLeida($conexion, $alerta_id, $_SESSION['usuario']);
    header('Location: alertas.php');
    exit();
}

// Marcar todas como leídas
if (isset($_GET['marcar_todas'])) {
    $sql = "UPDATE alertas SET leida = TRUE, fecha_vista = NOW() WHERE leida = FALSE";
    mysqli_query($conexion, $sql);
    $mensaje = '✅ Todas las alertas marcadas como leídas';
}

// Eliminar alerta
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $alerta_id = intval($_GET['eliminar']);
    $sql = "DELETE FROM alertas WHERE id = $alerta_id";
    if (mysqli_query($conexion, $sql)) {
        $mensaje = '✅ Alerta eliminada con éxito';
    } else {
        $error = '❌ Error al eliminar la alerta';
    }
}

// Generar alertas automáticas
function generarAlertasAutomaticas($conexion) {
    // Alertas de stock bajo
    $sql = "SELECT COUNT(*) as total FROM productos WHERE cantidad_actual <= stock_minimo AND cantidad_actual > 0";
    $resultado = mysqli_query($conexion, $sql);
    $stock_bajo = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
    
    if ($stock_bajo > 0) {
        $sql = "SELECT COUNT(*) as total FROM alertas WHERE tipo = 'stock_bajo' AND leida = FALSE";
        $resultado = mysqli_query($conexion, $sql);
        $existe_alerta = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
        
        if ($existe_alerta == 0) {
            crearAlerta($conexion, 'stock_bajo', 'Stock Bajo', 
                       "Tienes $stock_bajo productos con stock bajo. Revisa el inventario.", 'alta');
        }
    }
    
    // Alertas de productos por vencer
    $fecha_limite = date('Y-m-d', strtotime('+30 days'));
    $sql = "SELECT COUNT(*) as total FROM productos WHERE fecha_vencimiento <= '$fecha_limite' AND fecha_vencimiento >= CURDATE() AND activo = TRUE";
    $resultado = mysqli_query($conexion, $sql);
    $por_vencer = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
    
    if ($por_vencer > 0) {
        $sql = "SELECT COUNT(*) as total FROM alertas WHERE tipo = 'vencimiento' AND leida = FALSE";
        $resultado = mysqli_query($conexion, $sql);
        $existe_alerta = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
        
        if ($existe_alerta == 0) {
            crearAlerta($conexion, 'vencimiento', 'Productos por Vencer', 
                       "Tienes $por_vencer productos que vencen en los próximos 30 días.", 'media');
        }
    }
    
    // Alertas de pagos pendientes
    $sql = "SELECT COUNT(*) as total FROM ventas WHERE tipo_venta = 'credito' AND estado = 'pendiente'";
    $resultado = mysqli_query($conexion, $sql);
    $pagos_pendientes = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
    
    if ($pagos_pendientes > 0) {
        $sql = "SELECT COUNT(*) as total FROM alertas WHERE tipo = 'pago_pendiente' AND leida = FALSE";
        $resultado = mysqli_query($conexion, $sql);
        $existe_alerta = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
        
        if ($existe_alerta == 0) {
            crearAlerta($conexion, 'pago_pendiente', 'Pagos Pendientes', 
                       "Tienes $pagos_pendientes ventas a crédito con pagos pendientes.", 'alta');
        }
    }
}

// Generar alertas automáticas
generarAlertasAutomaticas($conexion);

// Filtros
$tipo_filtro = $_GET['tipo'] ?? '';
$prioridad_filtro = $_GET['prioridad'] ?? '';
$leida_filtro = $_GET['leida'] ?? '';

$where = "WHERE 1=1";
if ($tipo_filtro) {
    $tipo_escaped = escaparDato($conexion, $tipo_filtro);
    $where .= " AND tipo = '$tipo_escaped'";
}
if ($prioridad_filtro) {
    $prioridad_escaped = escaparDato($conexion, $prioridad_filtro);
    $where .= " AND prioridad = '$prioridad_escaped'";
}
if ($leida_filtro !== '') {
    $leida = $leida_filtro == 'true' ? 'TRUE' : 'FALSE';
    $where .= " AND leida = $leida";
}

// Obtener alertas
$sql = "SELECT * FROM alertas $where ORDER BY prioridad DESC, fecha_creacion DESC";
$alertas = mysqli_query($conexion, $sql);

// Estadísticas
$sql_alertas_no_leidas = "SELECT COUNT(*) as total FROM alertas WHERE leida = FALSE";
$resultado = mysqli_query($conexion, $sql_alertas_no_leidas);
$alertas_no_leidas = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_alertas_alta = "SELECT COUNT(*) as total FROM alertas WHERE prioridad = 'alta' AND leida = FALSE";
$resultado = mysqli_query($conexion, $sql_alertas_alta);
$alertas_alta = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_total_alertas = "SELECT COUNT(*) as total FROM alertas";
$resultado = mysqli_query($conexion, $sql_total_alertas);
$total_alertas = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
?>

<div class="card" style="max-width:1200px; margin:0 auto 32px auto; background:#fff; border-radius:14px; box-shadow:0 2px 12px 0 rgba(108,99,255,0.08); padding:32px 24px 24px 24px;">
    <h3 style="font-size:1.2rem; font-weight:700; color:#222; margin-bottom:18px;">
        <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">notifications</span> Sistema de Alertas y Notificaciones
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
            <div style="font-size: 2rem; font-weight: bold;"><?= $alertas_no_leidas ?></div>
            <div>Alertas No Leídas</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $alertas_alta ?></div>
            <div>Prioridad Alta</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $total_alertas ?></div>
            <div>Total Alertas</div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div style="margin-bottom: 24px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="alertas.php" class="btn <?= !$tipo_filtro && !$prioridad_filtro && $leida_filtro === '' ? 'btn-edit' : 'btn-outline' ?>">Todas</a>
        <a href="alertas.php?leida=false" class="btn <?= $leida_filtro === 'false' ? 'btn-edit' : 'btn-outline' ?>">No Leídas</a>
        <a href="alertas.php?leida=true" class="btn <?= $leida_filtro === 'true' ? 'btn-edit' : 'btn-outline' ?>">Leídas</a>
        <a href="alertas.php?prioridad=alta" class="btn <?= $prioridad_filtro == 'alta' ? 'btn-edit' : 'btn-outline' ?>">Alta Prioridad</a>
        
        <form method="get" style="display: flex; gap: 10px; align-items: center;">
            <?php if ($leida_filtro !== ''): ?>
                <input type="hidden" name="leida" value="<?= htmlspecialchars($leida_filtro) ?>">
            <?php endif; ?>
            <?php if ($prioridad_filtro): ?>
                <input type="hidden" name="prioridad" value="<?= htmlspecialchars($prioridad_filtro) ?>">
            <?php endif; ?>
            <select name="tipo" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">Todos los tipos</option>
                <option value="stock_bajo" <?= $tipo_filtro == 'stock_bajo' ? 'selected' : '' ?>>Stock Bajo</option>
                <option value="vencimiento" <?= $tipo_filtro == 'vencimiento' ? 'selected' : '' ?>>Vencimiento</option>
                <option value="pago_pendiente" <?= $tipo_filtro == 'pago_pendiente' ? 'selected' : '' ?>>Pago Pendiente</option>
                <option value="devolucion_pendiente" <?= $tipo_filtro == 'devolucion_pendiente' ? 'selected' : '' ?>>Devolución Pendiente</option>
            </select>
            <button type="submit" class="btn btn-outline">Filtrar</button>
        </form>
        
        <?php if ($alertas_no_leidas > 0): ?>
            <a href="alertas.php?marcar_todas=1" class="btn btn-edit" onclick="return confirm('¿Marcar todas las alertas como leídas?')">
                <span class="material-icons">mark_email_read</span> Marcar Todas Leídas
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Lista de Alertas -->
    <h4 style="margin-bottom: 15px;">📋 Alertas del Sistema</h4>
    
    <?php if (mysqli_num_rows($alertas) == 0): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <span class="material-icons" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;">notifications_off</span>
            <h3>No hay alertas</h3>
            <p>No se encontraron alertas con los filtros seleccionados.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 15px;">
            <?php while($alerta = mysqli_fetch_assoc($alertas)): ?>
                <div style="padding: 20px; border-radius: 10px; border-left: 4px solid 
                     <?= $alerta['prioridad'] == 'alta' ? '#dc3545' : 
                        ($alerta['prioridad'] == 'media' ? '#ffc107' : '#28a745') ?>; 
                     background: <?= $alerta['leida'] ? '#f8f9fa' : '#fff' ?>; 
                     border: 1px solid #e9ecef;">
                    
                    <div style="display: flex; justify-content: between; align-items: start; gap: 15px;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <span class="material-icons" style="color: 
                                     <?= $alerta['tipo'] == 'stock_bajo' ? '#dc3545' : 
                                        ($alerta['tipo'] == 'vencimiento' ? '#ffc107' : 
                                        ($alerta['tipo'] == 'pago_pendiente' ? '#dc3545' : '#17a2b8')) ?>;">
                                    <?= $alerta['tipo'] == 'stock_bajo' ? 'inventory' : 
                                       ($alerta['tipo'] == 'vencimiento' ? 'event' : 
                                       ($alerta['tipo'] == 'pago_pendiente' ? 'payment' : 'autorenew')) ?>
                                </span>
                                <h4 style="margin: 0; font-size: 1.1rem;"><?= htmlspecialchars($alerta['titulo']) ?></h4>
                                <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.7em; 
                                     background: <?= $alerta['prioridad'] == 'alta' ? '#dc3545' : 
                                                    ($alerta['prioridad'] == 'media' ? '#ffc107' : '#28a745') ?>; 
                                     color: white; text-transform: uppercase;">
                                    <?= $alerta['prioridad'] ?>
                                </span>
                                <?php if (!$alerta['leida']): ?>
                                    <span style="background: #dc3545; color: white; border-radius: 50%; width: 8px; height: 8px; display: inline-block;"></span>
                                <?php endif; ?>
                            </div>
                            
                            <p style="margin: 0 0 10px 0; color: #666;"><?= htmlspecialchars($alerta['mensaje']) ?></p>
                            
                            <div style="display: flex; gap: 10px; font-size: 0.9em; color: #999;">
                                <span>📅 <?= date('d/m/Y H:i', strtotime($alerta['fecha_creacion'])) ?></span>
                                <?php if ($alerta['leida']): ?>
                                    <span>👁️ Leída: <?= date('d/m/Y H:i', strtotime($alerta['fecha_vista'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 5px;">
                            <?php if (!$alerta['leida']): ?>
                                <a href="?marcar_leida=<?= $alerta['id'] ?>" class="btn btn-outline" title="Marcar como leída">
                                    <span class="material-icons">mark_email_read</span>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (esAdmin()): ?>
                                <a href="?eliminar=<?= $alerta['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Eliminar esta alerta?')" title="Eliminar">
                                    <span class="material-icons">delete</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-refresh cada 5 minutos para nuevas alertas
setTimeout(function() {
    location.reload();
}, 300000);

// Marcar como leída al hacer clic en la alerta
document.addEventListener('DOMContentLoaded', function() {
    const alertas = document.querySelectorAll('.alerta-no-leida');
    alertas.forEach(function(alerta) {
        alerta.addEventListener('click', function() {
            const id = this.dataset.id;
            window.location.href = '?marcar_leida=' + id;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 