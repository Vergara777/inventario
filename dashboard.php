<?php
session_start();
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Obtener estadísticas
$total_productos = obtenerTotalProductos();
$total_ventas_hoy = obtenerVentasHoy();
$total_clientes = obtenerTotalClientes();
$productos_agotados = obtenerProductosAgotados();
$ventas_mes = obtenerVentasMes();
$gastos_mes = obtenerGastosMes();
$creditos_pendientes = obtenerCreditosPendientes();
$devoluciones_pendientes = obtenerDevolucionesPendientes();

$page_title = "Dashboard";
include 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="card">
    <div class="card-header">
        <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?>!</h2>
        <p>Aquí tienes un resumen completo de tu farmacia</p>
</div>
</div>

<!-- Stats Cards -->
<div class="stats-row">
    <div class="stats-card">
        <span class="material-icons">inventory</span>
        <div class="stats-card-value"><?php echo number_format($total_productos); ?></div>
        <div class="stats-card-title">Total Productos</div>
    </div>
    
    <div class="stats-card">
        <span class="material-icons">point_of_sale</span>
        <div class="stats-card-value">$<?php echo number_format($total_ventas_hoy, 0, ',', '.'); ?></div>
        <div class="stats-card-title">Ventas Hoy</div>
    </div>
    
    <div class="stats-card">
        <span class="material-icons">people</span>
        <div class="stats-card-value"><?php echo number_format($total_clientes); ?></div>
        <div class="stats-card-title">Clientes Registrados</div>
    </div>
    
    <div class="stats-card">
        <span class="material-icons">warning</span>
        <div class="stats-card-value"><?php echo number_format($productos_agotados); ?></div>
        <div class="stats-card-title">Productos Agotados</div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="stats-row">
    <div class="stats-card">
        <span class="material-icons">trending_up</span>
        <div class="stats-card-value">$<?php echo number_format($ventas_mes, 0, ',', '.'); ?></div>
        <div class="stats-card-title">Ventas del Mes</div>
    </div>
    
    <div class="stats-card">
        <span class="material-icons">account_balance_wallet</span>
        <div class="stats-card-value">$<?php echo number_format($gastos_mes, 0, ',', '.'); ?></div>
        <div class="stats-card-title">Gastos del Mes</div>
    </div>
    
    <div class="stats-card">
        <span class="material-icons">credit_card</span>
        <div class="stats-card-value">$<?php echo number_format($creditos_pendientes, 0, ',', '.'); ?></div>
        <div class="stats-card-title">Créditos Pendientes</div>
    </div>
    
    <div class="stats-card">
        <span class="material-icons">assignment_return</span>
        <div class="stats-card-value"><?php echo number_format($devoluciones_pendientes); ?></div>
        <div class="stats-card-title">Devoluciones Pendientes</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <h3>Acciones Rápidas</h3>
    <div class="quick-actions">
        <a href="ventas.php" class="quick-btn">
            <span class="material-icons">add_shopping_cart</span>
            Nueva Venta
        </a>
        
        <a href="inventario.php" class="quick-btn">
            <span class="material-icons">add_box</span>
            Agregar Producto
        </a>
        
        <a href="clientes.php" class="quick-btn">
            <span class="material-icons">person_add</span>
            Nuevo Cliente
        </a>
        
        <a href="devoluciones.php" class="quick-btn">
            <span class="material-icons">assignment_return</span>
            Registrar Devolución
        </a>
        
        <a href="creditos.php" class="quick-btn">
            <span class="material-icons">credit_card</span>
            Venta a Crédito
        </a>
        
        <a href="gastos.php" class="quick-btn">
            <span class="material-icons">account_balance_wallet</span>
            Registrar Gasto
        </a>
        
        <a href="proveedores.php" class="quick-btn">
            <span class="material-icons">business</span>
            Nuevo Proveedor
        </a>
        
        <a href="reportes.php" class="quick-btn">
            <span class="material-icons">analytics</span>
            Ver Reportes
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <h3>Actividad Reciente</h3>
    <div class="recent-activity">
        <?php
        $actividad_reciente = obtenerActividadReciente();
        if ($actividad_reciente && mysqli_num_rows($actividad_reciente) > 0):
            while ($actividad = mysqli_fetch_assoc($actividad_reciente)):
        ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <span class="material-icons"><?php echo $actividad['icono']; ?></span>
                </div>
                <div class="activity-content">
                    <div class="activity-title"><?php echo $actividad['descripcion']; ?></div>
                    <div class="activity-time"><?php echo $actividad['fecha']; ?></div>
                </div>
            </div>
        <?php 
            endwhile;
        else:
        ?>
            <p class="no-activity">No hay actividad reciente</p>
        <?php endif; ?>
    </div>
</div>

<!-- Alerts Section -->
<?php
$alertas_urgentes = obtenerAlertasUrgentes();
if ($alertas_urgentes && mysqli_num_rows($alertas_urgentes) > 0):
?>
<div class="card">
    <h3>Alertas Urgentes</h3>
    <div class="alerts-list">
        <?php while ($alerta = mysqli_fetch_assoc($alertas_urgentes)): ?>
            <div class="alert-item">
                <span class="material-icons alert-icon">warning</span>
                <div class="alert-content">
                    <div class="alert-title"><?php echo $alerta['titulo']; ?></div>
                    <div class="alert-description"><?php echo $alerta['descripcion']; ?></div>
                </div>
                <div class="alert-time"><?php echo $alerta['fecha']; ?></div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<!-- Sales Chart -->
<div class="card">
    <h3>Ventas de los Últimos 7 Días</h3>
    <div class="chart-container">
        <canvas id="salesChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Low Stock Products -->
<div class="card">
    <h3>Productos con Stock Bajo</h3>
    <div class="low-stock-list">
        <?php
        $productos_stock_bajo = obtenerProductosStockBajo();
        if ($productos_stock_bajo && mysqli_num_rows($productos_stock_bajo) > 0):
        ?>
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($producto = mysqli_fetch_assoc($productos_stock_bajo)): ?>
                        <tr>
                            <td><?php echo $producto['nombre']; ?></td>
                            <td class="stock-bajo"><?php echo $producto['stock']; ?></td>
                            <td><?php echo $producto['stock_minimo']; ?></td>
                            <td>
                                <a href="inventario.php?editar=<?php echo $producto['id']; ?>" class="btn btn-edit">
                                    <span class="material-icons">edit</span>
                                    Editar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No hay productos con stock bajo</p>
        <?php endif; ?>
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

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.recent-activity {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: rgba(108,99,255,0.05);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.activity-icon .material-icons {
    color: white;
    font-size: 1.2rem;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.activity-time {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.no-activity {
    text-align: center;
    color: #7f8c8d;
    padding: 40px;
    font-style: italic;
}

.alerts-list {
    max-height: 300px;
    overflow-y: auto;
}

.alert-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: rgba(255,107,107,0.1);
    border-radius: 10px;
    margin-bottom: 10px;
    border-left: 4px solid #e74c3c;
}

.alert-icon {
    color: #e74c3c;
    margin-right: 15px;
    font-size: 1.5rem;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.alert-description {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.alert-time {
    font-size: 0.8rem;
    color: #95a5a6;
}

.chart-container {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 10px;
    margin-top: 20px;
}

.low-stock-list {
    margin-top: 20px;
}

.stock-bajo {
    color: #e74c3c;
    font-weight: 700;
}

.no-data {
    text-align: center;
    color: #7f8c8d;
    padding: 40px;
    font-style: italic;
}

@media (max-width: 768px) {
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-icon {
        margin-bottom: 10px;
        margin-right: 0;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        datasets: [{
            label: 'Ventas ($)',
            data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
            borderColor: '#6c63ff',
            backgroundColor: 'rgba(108, 99, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Mobile menu toggle
document.getElementById('menuToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>

<?php include 'includes/footer.php'; ?>

