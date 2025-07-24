<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();
include 'includes/header.php';

$mensaje = '';
$error = '';

// Agregar gasto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'agregar_gasto') {
    $descripcion = escaparDato($conexion, $_POST['descripcion']);
    $monto = floatval($_POST['monto']);
    $categoria = escaparDato($conexion, $_POST['categoria']);
    $fecha = escaparDato($conexion, $_POST['fecha']);
    $notas = escaparDato($conexion, $_POST['notas']);
    
    if (empty($descripcion) || $monto <= 0 || empty($fecha)) {
        $error = '❌ Por favor, completa todos los campos obligatorios';
    } else {
        if (registrarGasto($conexion, $descripcion, $monto, $categoria, $fecha, $_SESSION['usuario'])) {
            $mensaje = '✅ Gasto registrado con éxito';
        } else {
            $error = '❌ Error al registrar el gasto';
        }
    }
}

// Eliminar gasto
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $sql = "DELETE FROM gastos WHERE id = $id";
    if (mysqli_query($conexion, $sql)) {
        $mensaje = '✅ Gasto eliminado con éxito';
    } else {
        $error = '❌ Error al eliminar el gasto';
    }
}

// Filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$categoria_filtro = $_GET['categoria'] ?? '';

$where = "WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
if ($categoria_filtro) {
    $categoria_escaped = escaparDato($conexion, $categoria_filtro);
    $where .= " AND categoria = '$categoria_escaped'";
}

// Obtener gastos
$sql = "SELECT * FROM gastos $where ORDER BY fecha DESC";
$gastos = mysqli_query($conexion, $sql);

// Estadísticas de gastos
$sql_total_gastos = "SELECT COALESCE(SUM(monto), 0) as total FROM gastos $where";
$resultado = mysqli_query($conexion, $sql_total_gastos);
$total_gastos = floatval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_gastos_por_categoria = "SELECT categoria, COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as total 
                             FROM gastos $where 
                             GROUP BY categoria 
                             ORDER BY total DESC";
$gastos_por_categoria = mysqli_query($conexion, $sql_gastos_por_categoria);

// Obtener ingresos del período para calcular balance
$sql_ingresos = "SELECT COALESCE(SUM(total_venta), 0) as total FROM ventas WHERE fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
$resultado = mysqli_query($conexion, $sql_ingresos);
$total_ingresos = floatval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$balance = $total_ingresos - $total_gastos;
$porcentaje_gastos = $total_ingresos > 0 ? ($total_gastos / $total_ingresos) * 100 : 0;
?>

<div class="card" style="max-width:1200px; margin:0 auto 32px auto; background:#fff; border-radius:14px; box-shadow:0 2px 12px 0 rgba(108,99,255,0.08); padding:32px 24px 24px 24px;">
    <h3 style="font-size:1.2rem; font-weight:700; color:#222; margin-bottom:18px;">
        <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">receipt_long</span> Control de Gastos
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
            <div style="font-size: 2rem; font-weight: bold;"><?= formatearMoneda($total_ingresos) ?></div>
            <div>Ingresos del Período</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= formatearMoneda($total_gastos) ?></div>
            <div>Gastos del Período</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= formatearMoneda($balance) ?></div>
            <div>Balance</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= number_format($porcentaje_gastos, 1) ?>%</div>
            <div>% Gastos/Ingresos</div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div style="margin-bottom: 24px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
        <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div>
                <label>Fecha inicio:</label>
                <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label>Fecha fin:</label>
                <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label>Categoría:</label>
                <select name="categoria" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Todas las categorías</option>
                    <option value="servicios" <?= $categoria_filtro == 'servicios' ? 'selected' : '' ?>>Servicios</option>
                    <option value="suministros" <?= $categoria_filtro == 'suministros' ? 'selected' : '' ?>>Suministros</option>
                    <option value="equipos" <?= $categoria_filtro == 'equipos' ? 'selected' : '' ?>>Equipos</option>
                    <option value="marketing" <?= $categoria_filtro == 'marketing' ? 'selected' : '' ?>>Marketing</option>
                    <option value="otros" <?= $categoria_filtro == 'otros' ? 'selected' : '' ?>>Otros</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-edit" style="width: 100%;">
                    <span class="material-icons">search</span> Filtrar
                </button>
            </div>
        </form>
    </div>
    
    <!-- Nuevo Gasto -->
    <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
        <h4 style="margin-bottom: 15px;">💰 Registrar Nuevo Gasto</h4>
<form method="post">
            <input type="hidden" name="accion" value="agregar_gasto">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>Descripción del gasto:</label>
                    <input type="text" name="descripcion" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Monto:</label>
                    <input type="number" name="monto" step="0.01" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>Categoría:</label>
                    <select name="categoria" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">Seleccionar categoría</option>
                        <option value="servicios">Servicios</option>
                        <option value="suministros">Suministros</option>
                        <option value="equipos">Equipos</option>
                        <option value="marketing">Marketing</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                <div>
                    <label>Fecha:</label>
                    <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Notas adicionales:</label>
                <textarea name="notas" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; height: 80px;"></textarea>
            </div>
            
            <button type="submit" class="btn btn-edit">
                <span class="material-icons">add</span> Registrar Gasto
            </button>
</form>
    </div>
    
    <!-- Gastos por Categoría -->
    <div style="margin-bottom: 30px;">
        <h4 style="margin-bottom: 15px;">📊 Gastos por Categoría</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <?php while($categoria = mysqli_fetch_assoc($gastos_por_categoria)): ?>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #6c63ff;">
                        <?= formatearMoneda($categoria['total']) ?>
                    </div>
                    <div style="color: #666; text-transform: capitalize;">
                        <?= ucfirst($categoria['categoria']) ?>
                    </div>
                    <div style="font-size: 0.9em; color: #999;">
                        <?= $categoria['cantidad'] ?> gastos
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Lista de Gastos -->
    <h4 style="margin-bottom: 15px;">📋 Historial de Gastos</h4>
    <table class="productos-table">
        <tr>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Categoría</th>
        <th>Monto</th>
            <th>Usuario</th>
            <th>Acciones</th>
    </tr>
        <?php while($gasto = mysqli_fetch_assoc($gastos)): ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
            <td><?= htmlspecialchars($gasto['descripcion']) ?></td>
            <td>
                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                     background: #e9ecef; color: #495057; text-transform: capitalize;">
                    <?= ucfirst($gasto['categoria']) ?>
                </span>
            </td>
            <td style="font-weight: bold; color: #f00;"><?= formatearMoneda($gasto['monto']) ?></td>
            <td><?= htmlspecialchars($gasto['usuario']) ?></td>
            <td>
                <?php if (esAdmin()): ?>
                    <a href="?eliminar=<?= $gasto['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Eliminar este gasto?')">
                        <span class="material-icons">delete</span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($gasto['notas'])): ?>
                    <button onclick="mostrarNotas('<?= htmlspecialchars($gasto['notas']) ?>')" class="btn btn-outline">
                        <span class="material-icons">info</span>
                    </button>
                <?php endif; ?>
            </td>
    </tr>
    <?php endwhile; ?>
</table>
</div>

<!-- Modal para mostrar notas -->
<div id="modalNotas" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; min-width: 400px;">
        <h3 style="margin-bottom: 20px;">📝 Notas del Gasto</h3>
        <div id="contenidoNotas" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;"></div>
        <button onclick="cerrarModalNotas()" class="btn btn-edit">Cerrar</button>
    </div>
</div>

<script>
function mostrarNotas(notas) {
    document.getElementById('contenidoNotas').textContent = notas;
    document.getElementById('modalNotas').style.display = 'block';
}

function cerrarModalNotas() {
    document.getElementById('modalNotas').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalNotas').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalNotas();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
