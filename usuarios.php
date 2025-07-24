<?php
session_start();
include 'includes/conexion.php';
include 'includes/funciones.php';

// Protección de sesión
redirigirSiNoSesion();

// Verificar si es administrador para ciertas operaciones
$es_admin = esAdmin();

// Variables de estado
$mensaje = '';
$error = '';

// Lógica de manejo de usuarios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Registrar nuevo usuario
    if (isset($_POST['accion']) && $_POST['accion'] == 'registrar') {
        $usuario = escaparDato($conexion, $_POST['usuario']);
        $contrasena = $_POST['contrasena'];
        $contrasena_confirmar = $_POST['contrasena_confirmar'];
        $rol = escaparDato($conexion, $_POST['rol']);
        
        // Validaciones
        if (strlen($usuario) < 3) {
            $error = "❌ El nombre de usuario debe tener al menos 3 caracteres";
        } elseif (strlen($contrasena) < 6) {
            $error = "❌ La contraseña debe tener al menos 6 caracteres";
        } elseif ($contrasena !== $contrasena_confirmar) {
            $error = "❌ Las contraseñas no coinciden";
        } elseif (!in_array($rol, ['admin', 'empleado'])) {
            $error = "❌ Rol no válido";
        } else {
            // Verificar si el usuario ya existe
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usuario = '$usuario'";
            $resultado = mysqli_query($conexion, $sql);
            $fila = mysqli_fetch_assoc($resultado);
            
            if (intval($fila['total']) > 0) {
                $error = "❌ El nombre de usuario ya existe";
            } else {
                $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (usuario, contrasena, rol) VALUES ('$usuario', '$contrasena_hash', '$rol')";
                
                if (mysqli_query($conexion, $sql)) {
                    $mensaje = "✅ Usuario registrado con éxito";
                } else {
                    $error = "❌ Error al registrar usuario: " . mysqli_error($conexion);
                }
            }
        }
    }
    
    // Editar usuario
    if (isset($_POST['accion']) && $_POST['accion'] == 'editar') {
        $id = intval($_POST['id']);
        $usuario = escaparDato($conexion, $_POST['usuario']);
        $rol = escaparDato($conexion, $_POST['rol']);
        $contrasena = $_POST['contrasena'];
        
        if (strlen($usuario) < 3) {
            $error = "❌ El nombre de usuario debe tener al menos 3 caracteres";
        } elseif (!in_array($rol, ['admin', 'empleado'])) {
            $error = "❌ Rol no válido";
        } else {
            // Verificar si el usuario ya existe (excluyendo el actual)
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usuario = '$usuario' AND id != $id";
            $resultado = mysqli_query($conexion, $sql);
            $fila = mysqli_fetch_assoc($resultado);
            
            if (intval($fila['total']) > 0) {
                $error = "❌ El nombre de usuario ya existe";
            } else {
                if (!empty($contrasena)) {
                    // Actualizar con nueva contraseña
                    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $sql = "UPDATE usuarios SET usuario='$usuario', contrasena='$contrasena_hash', rol='$rol' WHERE id = $id";
                } else {
                    // Actualizar sin cambiar contraseña
                    $sql = "UPDATE usuarios SET usuario='$usuario', rol='$rol' WHERE id = $id";
                }
                
                if (mysqli_query($conexion, $sql)) {
                    $mensaje = "✅ Usuario actualizado con éxito";
                } else {
                    $error = "❌ Error al actualizar usuario: " . mysqli_error($conexion);
                }
            }
        }
    }
}

// Eliminar usuario
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    // No permitir eliminar el usuario actual
    if ($id == $_SESSION['usuario_id'] ?? 0) {
        $error = "❌ No puedes eliminar tu propia cuenta";
    } else {
        // Verificar si el usuario tiene ventas asociadas
        $sql = "SELECT COUNT(*) as total FROM ventas WHERE usuario = (SELECT usuario FROM usuarios WHERE id = $id)";
        $resultado = mysqli_query($conexion, $sql);
        $fila = mysqli_fetch_assoc($resultado);
        
        if (intval($fila['total']) > 0) {
            $error = "❌ No se puede eliminar el usuario porque tiene ventas asociadas";
        } else {
            $sql = "DELETE FROM usuarios WHERE id = $id";
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "✅ Usuario eliminado con éxito";
            } else {
                $error = "❌ Error al eliminar usuario: " . mysqli_error($conexion);
            }
        }
    }
}

// Obtener usuarios para mostrar
$sql = "SELECT id, usuario, rol, fecha_registro FROM usuarios ORDER BY usuario ASC";
$usuarios = mysqli_query($conexion, $sql);

include 'includes/header.php';
?>

<h2 style="text-align:center; font-size:2rem; text-shadow:0 0 10px #0ff;">👥 Gestión de Usuarios</h2>

<!-- Mensajes de estado -->
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

<!-- Formulario para registrar usuario -->
<?php if ($es_admin): ?>
<div class="card">
    <h3 style="text-align:center;">➕ Registrar Nuevo Usuario</h3>
    <form method="post">
        <input type="hidden" name="accion" value="registrar">
        <input type="text" name="usuario" placeholder="Nombre de usuario" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <input type="password" name="contrasena_confirmar" placeholder="Confirmar contraseña" required>
        <select name="rol" required>
            <option value="">Seleccionar rol</option>
            <option value="admin">Administrador</option>
            <option value="empleado">Empleado</option>
        </select>
        <button type="submit">Registrar Usuario</button>
    </form>
</div>
<?php endif; ?>

<!-- Lista de usuarios -->
<div class="card">
    <h3 style="text-align:center;">📋 Lista de Usuarios</h3>
    <table class="table">
        <tr>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Fecha de Registro</th>
            <?php if ($es_admin): ?>
            <th>Acciones</th>
            <?php endif; ?>
        </tr>
        <?php while($usuario = mysqli_fetch_assoc($usuarios)): ?>
        <tr>
            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
            <td>
                <span style="color: <?= $usuario['rol'] == 'admin' ? '#ff6b6b' : '#4ecdc4' ?>; font-weight: bold;">
                    <?= ucfirst($usuario['rol']) ?>
                </span>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($usuario['fecha_registro'])) ?></td>
            <?php if ($es_admin): ?>
            <td>
                <button onclick="editarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['usuario']) ?>', '<?= $usuario['rol'] ?>')" 
                        style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">✏️</button>
                <?php if ($usuario['usuario'] != $_SESSION['usuario']): ?>
                <a href="?eliminar=<?= $usuario['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este usuario?')" 
                   style="background: #dc3545; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px;">🗑️</a>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Modal para editar usuario -->
<?php if ($es_admin): ?>
<div id="modalEditar" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000;">
    <div style="background:#2c2c2c; color:#fff; padding:20px; border-radius:10px; width:400px; margin:100px auto; position:relative;">
        <h3>✏️ Editar Usuario</h3>
        <form method="post">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="usuario" id="edit_usuario" placeholder="Nombre de usuario" required>
            <input type="password" name="contrasena" id="edit_contrasena" placeholder="Nueva contraseña (dejar vacío para no cambiar)">
            <select name="rol" id="edit_rol" required>
                <option value="admin">Administrador</option>
                <option value="empleado">Empleado</option>
            </select>
            <button type="submit">Actualizar Usuario</button>
        </form>
        <button onclick="document.getElementById('modalEditar').style.display='none'" style="background: #6c757d; color: white; border: none; padding: 10px; border-radius: 5px; margin-top: 10px; cursor: pointer;">❌ Cerrar</button>
    </div>
</div>

<script>
function editarUsuario(id, usuario, rol) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_usuario').value = usuario;
    document.getElementById('edit_contrasena').value = '';
    document.getElementById('edit_rol').value = rol;
    document.getElementById('modalEditar').style.display = 'block';
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
