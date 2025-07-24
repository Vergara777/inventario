<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();
include 'includes/header.php';

$mensaje = '';
$error = '';

// Agregar promoción
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'agregar_promocion') {
    $nombre = escaparDato($conexion, $_POST['nombre']);
    $descripcion = escaparDato($conexion, $_POST['descripcion']);
    $tipo = escaparDato($conexion, $_POST['tipo']);
    $valor = floatval($_POST['valor']);
    $fecha_inicio = escaparDato($conexion, $_POST['fecha_inicio']);
    $fecha_fin = escaparDato($conexion, $_POST['fecha_fin']);
    $minimo_compra = floatval($_POST['minimo_compra']);
    $aplicable_categorias = isset($_POST['categorias']) ? implode(',', $_POST['categorias']) : '';
    
    if (empty($nombre) || $valor <= 0 || empty($fecha_inicio) || empty($fecha_fin)) {
        $error = '❌ Por favor, completa todos los campos obligatorios';
    } elseif ($fecha_fin <= $fecha_inicio) {
        $error = '❌ La fecha de fin debe ser posterior a la fecha de inicio';
    } else {
        $sql = "INSERT INTO promociones (nombre, descripcion, tipo, valor, fecha_inicio, fecha_fin, minimo_compra, aplicable_categorias) 
                VALUES ('$nombre', '$descripcion', '$tipo', $valor, '$fecha_inicio', '$fecha_fin', $minimo_compra, '$aplicable_categorias')";
        
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Promoción agregada con éxito';
        } else {
            $error = '❌ Error al agregar promoción: ' . mysqli_error($conexion);
        }
    }
}

// Agregar cupón
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'agregar_cupon') {
    $codigo = escaparDato($conexion, $_POST['codigo']);
    $descripcion = escaparDato($conexion, $_POST['descripcion']);
    $tipo = escaparDato($conexion, $_POST['tipo']);
    $valor = floatval($_POST['valor']);
    $fecha_inicio = escaparDato($conexion, $_POST['fecha_inicio']);
    $fecha_fin = escaparDato($conexion, $_POST['fecha_fin']);
    $uso_maximo = intval($_POST['uso_maximo']);
    $minimo_compra = floatval($_POST['minimo_compra']);
    
    if (empty($codigo) || $valor <= 0 || empty($fecha_inicio) || empty($fecha_fin)) {
        $error = '❌ Por favor, completa todos los campos obligatorios';
    } elseif ($fecha_fin <= $fecha_inicio) {
        $error = '❌ La fecha de fin debe ser posterior a la fecha de inicio';
    } else {
        $sql = "INSERT INTO cupones (codigo, descripcion, tipo, valor, fecha_inicio, fecha_fin, uso_maximo, minimo_compra) 
                VALUES ('$codigo', '$descripcion', '$tipo', $valor, '$fecha_inicio', '$fecha_fin', $uso_maximo, $minimo_compra)";
        
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Cupón agregado con éxito';
        } else {
            $error = '❌ Error al agregar cupón: ' . mysqli_error($conexion);
        }
    }
}

// Activar/Desactivar promoción
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];
    
    if ($accion == 'activar' || $accion == 'desactivar') {
        $estado = $accion == 'activar' ? 1 : 0;
        $tabla = isset($_GET['tipo']) && $_GET['tipo'] == 'cupon' ? 'cupones' : 'promociones';
        
        $sql = "UPDATE $tabla SET activo = $estado WHERE id = $id";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Estado actualizado con éxito';
        } else {
            $error = '❌ Error al actualizar estado';
        }
    }
}

// Obtener promociones
$sql = "SELECT * FROM promociones ORDER BY fecha_inicio DESC";
$promociones = mysqli_query($conexion, $sql);

// Obtener cupones
$sql = "SELECT * FROM cupones ORDER BY fecha_inicio DESC";
$cupones = mysqli_query($conexion, $sql);

// Obtener categorías para promociones
$categorias = mysqli_query($conexion, "SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
?>

<div class="card" style="max-width:1200px; margin:0 auto 32px auto; background:#fff; border-radius:14px; box-shadow:0 2px 12px 0 rgba(108,99,255,0.08); padding:32px 24px 24px 24px;">
    <h3 style="font-size:1.2rem; font-weight:700; color:#222; margin-bottom:18px;">
        <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">card_giftcard</span> Sistema de Promociones y Cupones
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
    
    <!-- Pestañas -->
    <div style="margin-bottom: 30px;">
        <button class="btn btn-edit" onclick="mostrarSeccion('promociones')" id="tab-promociones">Promociones</button>
        <button class="btn btn-outline" onclick="mostrarSeccion('cupones')" id="tab-cupones">Cupones</button>
    </div>
    
    <!-- Sección Promociones -->
    <div id="seccion-promociones">
        <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h4 style="margin-bottom: 15px;">🎯 Nueva Promoción</h4>
            <form method="post">
                <input type="hidden" name="accion" value="agregar_promocion">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Nombre de la promoción:</label>
                        <input type="text" name="nombre" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label>Tipo de descuento:</label>
                        <select name="tipo" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="descuento_porcentaje">Descuento por porcentaje</option>
                            <option value="descuento_fijo">Descuento fijo</option>
                            <option value="compra_minima">Compra mínima</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Valor del descuento:</label>
                        <input type="number" name="valor" step="0.01" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label>Compra mínima:</label>
                        <input type="number" name="minimo_compra" step="0.01" value="0" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Fecha de inicio:</label>
                        <input type="date" name="fecha_inicio" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label>Fecha de fin:</label>
                        <input type="date" name="fecha_fin" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Categorías aplicables (opcional):</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-top: 5px;">
                        <?php while($categoria = mysqli_fetch_assoc($categorias)): ?>
                            <label style="display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" name="categorias[]" value="<?= $categoria['id'] ?>">
                                <?= htmlspecialchars($categoria['nombre']) ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Descripción:</label>
                    <textarea name="descripcion" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; height: 80px;"></textarea>
                </div>
                
                <button type="submit" class="btn btn-edit">
                    <span class="material-icons">add</span> Crear Promoción
                </button>
            </form>
        </div>
        
        <h4 style="margin-bottom: 15px;">📋 Promociones Activas</h4>
        <table class="productos-table">
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Vigencia</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <?php while($promocion = mysqli_fetch_assoc($promociones)): ?>
            <tr>
                <td><?= htmlspecialchars($promocion['nombre']) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $promocion['tipo'])) ?></td>
                <td>
                    <?php if ($promocion['tipo'] == 'descuento_porcentaje'): ?>
                        <?= $promocion['valor'] ?>%
                    <?php else: ?>
                        <?= formatearMoneda($promocion['valor']) ?>
                    <?php endif; ?>
                </td>
                <td><?= date('d/m/Y', strtotime($promocion['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($promocion['fecha_fin'])) ?></td>
                <td>
                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                         background: <?= $promocion['activo'] ? '#d4edda' : '#f8d7da' ?>; 
                         color: <?= $promocion['activo'] ? '#155724' : '#721c24' ?>;">
                        <?= $promocion['activo'] ? 'Activa' : 'Inactiva' ?>
                    </span>
                </td>
                <td>
                    <?php if ($promocion['activo']): ?>
                        <a href="?accion=desactivar&id=<?= $promocion['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Desactivar esta promoción?')">
                            <span class="material-icons">block</span>
                        </a>
                    <?php else: ?>
                        <a href="?accion=activar&id=<?= $promocion['id'] ?>" class="btn btn-edit">
                            <span class="material-icons">check_circle</span>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    
    <!-- Sección Cupones -->
    <div id="seccion-cupones" style="display: none;">
        <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h4 style="margin-bottom: 15px;">🎫 Nuevo Cupón</h4>
            <form method="post">
                <input type="hidden" name="accion" value="agregar_cupon">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Código del cupón:</label>
                        <input type="text" name="codigo" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label>Tipo de descuento:</label>
                        <select name="tipo" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="porcentaje">Porcentaje</option>
                            <option value="fijo">Monto fijo</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Valor del descuento:</label>
                        <input type="number" name="valor" step="0.01" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label>Uso máximo:</label>
                        <input type="number" name="uso_maximo" value="1" min="1" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Fecha de inicio:</label>
                        <input type="date" name="fecha_inicio" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label>Fecha de fin:</label>
                        <input type="date" name="fecha_fin" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Compra mínima:</label>
                    <input type="number" name="minimo_compra" step="0.01" value="0" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Descripción:</label>
                    <textarea name="descripcion" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; height: 80px;"></textarea>
                </div>
                
                <button type="submit" class="btn btn-edit">
                    <span class="material-icons">add</span> Crear Cupón
                </button>
            </form>
        </div>
        
        <h4 style="margin-bottom: 15px;">🎫 Cupones Disponibles</h4>
        <table class="productos-table">
            <tr>
                <th>Código</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Uso</th>
                <th>Vigencia</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <?php while($cupon = mysqli_fetch_assoc($cupones)): ?>
            <tr>
                <td><strong><?= htmlspecialchars($cupon['codigo']) ?></strong></td>
                <td><?= ucfirst($cupon['tipo']) ?></td>
                <td>
                    <?php if ($cupon['tipo'] == 'porcentaje'): ?>
                        <?= $cupon['valor'] ?>%
                    <?php else: ?>
                        <?= formatearMoneda($cupon['valor']) ?>
                    <?php endif; ?>
                </td>
                <td><?= $cupon['usos_actuales'] ?>/<?= $cupon['uso_maximo'] ?></td>
                <td><?= date('d/m/Y', strtotime($cupon['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($cupon['fecha_fin'])) ?></td>
                <td>
                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; 
                         background: <?= $cupon['activo'] ? '#d4edda' : '#f8d7da' ?>; 
                         color: <?= $cupon['activo'] ? '#155724' : '#721c24' ?>;">
                        <?= $cupon['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td>
                    <?php if ($cupon['activo']): ?>
                        <a href="?accion=desactivar&id=<?= $cupon['id'] ?>&tipo=cupon" class="btn btn-delete" onclick="return confirm('¿Desactivar este cupón?')">
                            <span class="material-icons">block</span>
                        </a>
                    <?php else: ?>
                        <a href="?accion=activar&id=<?= $cupon['id'] ?>&tipo=cupon" class="btn btn-edit">
                            <span class="material-icons">check_circle</span>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<script>
function mostrarSeccion(seccion) {
    // Ocultar todas las secciones
    document.getElementById('seccion-promociones').style.display = 'none';
    document.getElementById('seccion-cupones').style.display = 'none';
    
    // Mostrar la sección seleccionada
    document.getElementById('seccion-' + seccion).style.display = 'block';
    
    // Actualizar pestañas
    document.getElementById('tab-promociones').className = 'btn btn-outline';
    document.getElementById('tab-cupones').className = 'btn btn-outline';
    document.getElementById('tab-' + seccion).className = 'btn btn-edit';
}
</script>

<?php include 'includes/footer.php'; ?> 