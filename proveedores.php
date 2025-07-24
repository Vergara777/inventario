<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';
redirigirSiNoSesion();
include 'includes/header.php';

$mensaje = '';
$error = '';

// Agregar proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $nombre = escaparDato($conexion, $_POST['nombre']);
    $contacto = escaparDato($conexion, $_POST['contacto']);
    $email = escaparDato($conexion, $_POST['email']);
    $telefono = escaparDato($conexion, $_POST['telefono']);
    $direccion = escaparDato($conexion, $_POST['direccion']);
    $ruc = escaparDato($conexion, $_POST['ruc']);
    
    if (empty($nombre)) {
        $error = '❌ El nombre del proveedor es obligatorio';
    } else {
        $sql = "INSERT INTO proveedores (nombre, contacto, email, telefono, direccion, ruc) 
                VALUES ('$nombre', '$contacto', '$email', '$telefono', '$direccion', '$ruc')";
        
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Proveedor agregado con éxito';
        } else {
            $error = '❌ Error al agregar proveedor: ' . mysqli_error($conexion);
        }
    }
}

// Editar proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id = intval($_POST['id']);
    $nombre = escaparDato($conexion, $_POST['nombre']);
    $contacto = escaparDato($conexion, $_POST['contacto']);
    $email = escaparDato($conexion, $_POST['email']);
    $telefono = escaparDato($conexion, $_POST['telefono']);
    $direccion = escaparDato($conexion, $_POST['direccion']);
    $ruc = escaparDato($conexion, $_POST['ruc']);
    
    $sql = "UPDATE proveedores SET nombre='$nombre', contacto='$contacto', email='$email', telefono='$telefono', direccion='$direccion', ruc='$ruc' WHERE id=$id";
    
    if (mysqli_query($conexion, $sql)) {
        $mensaje = '✅ Proveedor actualizado con éxito';
    } else {
        $error = '❌ Error al actualizar proveedor: ' . mysqli_error($conexion);
    }
}

// Eliminar proveedor
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    // Verificar si tiene órdenes de compra
    $sql = "SELECT COUNT(*) as total FROM ordenes_compra WHERE proveedor_id = $id";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    
    if (intval($fila['total']) > 0) {
        $error = '❌ No se puede eliminar el proveedor porque tiene órdenes de compra asociadas';
    } else {
        $sql = "DELETE FROM proveedores WHERE id = $id";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Proveedor eliminado con éxito';
        } else {
            $error = '❌ Error al eliminar proveedor: ' . mysqli_error($conexion);
        }
    }
}

// Obtener proveedores
$proveedores = mysqli_query($conexion, "SELECT * FROM proveedores ORDER BY nombre ASC");

// Obtener proveedor para editar
$proveedorEditar = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = mysqli_query($conexion, "SELECT * FROM proveedores WHERE id = $id");
    $proveedorEditar = mysqli_fetch_assoc($res);
}

// Estadísticas
$sql_total_proveedores = "SELECT COUNT(*) as total FROM proveedores WHERE activo = 1";
$resultado = mysqli_query($conexion, $sql_total_proveedores);
$total_proveedores = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);

$sql_ordenes_pendientes = "SELECT COUNT(*) as total FROM ordenes_compra WHERE estado = 'pendiente'";
$resultado = mysqli_query($conexion, $sql_ordenes_pendientes);
$ordenes_pendientes = intval(mysqli_fetch_assoc($resultado)['total'] ?? 0);
?>

<div class="card" style="max-width:1200px; margin:0 auto 32px auto; background:#fff; border-radius:14px; box-shadow:0 2px 12px 0 rgba(108,99,255,0.08); padding:32px 24px 24px 24px;">
    <h3 style="font-size:1.2rem; font-weight:700; color:#222; margin-bottom:18px;">
        <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">business</span> Gestión de Proveedores
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
            <div style="font-size: 2rem; font-weight: bold;"><?= $total_proveedores ?></div>
            <div>Proveedores Activos</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $ordenes_pendientes ?></div>
            <div>Órdenes Pendientes</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <a href="ordenes_compra.php" style="color: white; text-decoration: none;">
                <div style="font-size: 2rem; font-weight: bold;">📋</div>
                <div>Gestionar Órdenes</div>
            </a>
        </div>
    </div>
    
    <!-- Formulario para agregar/editar proveedor -->
    <div style="margin-bottom: 32px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
        <h4 style="margin-bottom: 15px;">
            <span class="material-icons" style="color:#6c63ff; vertical-align:middle;">add_business</span>
            <?= $proveedorEditar ? 'Editar Proveedor' : 'Agregar Nuevo Proveedor' ?>
        </h4>
        
        <form method="post">
            <input type="hidden" name="accion" value="<?= $proveedorEditar ? 'editar' : 'agregar' ?>">
            <?php if ($proveedorEditar): ?>
                <input type="hidden" name="id" value="<?= $proveedorEditar['id'] ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>Nombre del proveedor:</label>
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($proveedorEditar['nombre'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Persona de contacto:</label>
                    <input type="text" name="contacto" value="<?= htmlspecialchars($proveedorEditar['contacto'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($proveedorEditar['email'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($proveedorEditar['telefono'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>RUC:</label>
                    <input type="text" name="ruc" value="<?= htmlspecialchars($proveedorEditar['ruc'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Dirección:</label>
                    <input type="text" name="direccion" value="<?= htmlspecialchars($proveedorEditar['direccion'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-edit">
                <span class="material-icons"><?= $proveedorEditar ? 'edit' : 'add' ?></span>
                <?= $proveedorEditar ? 'Actualizar' : 'Registrar' ?> Proveedor
            </button>
            
            <?php if ($proveedorEditar): ?>
                <a href="proveedores.php" class="btn btn-delete" style="margin-left: 10px;">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Lista de proveedores -->
    <h4 style="margin-bottom: 15px;">📋 Lista de Proveedores</h4>
    <table class="productos-table">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Contacto</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>RUC</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
        </tr>
        <?php while($proveedor = mysqli_fetch_assoc($proveedores)): ?>
        <tr>
            <td><?= $proveedor['id'] ?></td>
            <td><strong><?= htmlspecialchars($proveedor['nombre']) ?></strong></td>
            <td><?= htmlspecialchars($proveedor['contacto']) ?></td>
            <td><?= htmlspecialchars($proveedor['email']) ?></td>
            <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
            <td><?= htmlspecialchars($proveedor['ruc']) ?></td>
            <td><?= date('d/m/Y', strtotime($proveedor['fecha_registro'])) ?></td>
            <td>
                <a href="?editar=<?= $proveedor['id'] ?>" class="btn btn-edit">
                    <span class="material-icons">edit</span>
                </a>
                <a href="ordenes_compra.php?proveedor=<?= $proveedor['id'] ?>" class="btn btn-outline">
                    <span class="material-icons">shopping_cart</span>
                </a>
                <?php if (esAdmin()): ?>
                    <a href="?eliminar=<?= $proveedor['id'] ?>" class="btn btn-delete" onclick="return confirm('¿Eliminar este proveedor?')">
                        <span class="material-icons">delete</span>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'includes/footer.php'; ?> 