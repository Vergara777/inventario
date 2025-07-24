<?php
session_start();
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: clientes.php');
    exit();
}

$cliente = obtenerCliente($_GET['id']);
if (!$cliente) {
    header('Location: clientes.php');
    exit();
}

$page_title = 'Detalle de Cliente';
include 'includes/header.php';
?>

<div class="card" style="max-width: 600px; margin: 40px auto;">
    <h2 style="margin-bottom: 20px; color: #2c3e50;">Detalle de Cliente</h2>
    <div style="padding: 20px;">
        <p><strong>Nombre:</strong> <?php echo $cliente['nombre'] . ' ' . ($cliente['apellido'] ?? ''); ?></p>
        <p><strong>Email:</strong> <?php echo $cliente['email'] ?? '-'; ?></p>
        <p><strong>Teléfono:</strong> <?php echo $cliente['telefono'] ?? '-'; ?></p>
        <p><strong>Dirección:</strong> <?php echo $cliente['direccion'] ?? '-'; ?></p>
        <p><strong>Ciudad:</strong> <?php echo $cliente['ciudad'] ?? '-'; ?></p>
        <p><strong>Fecha de registro:</strong> <?php echo $cliente['fecha_registro'] ?? '-'; ?></p>
        <p><strong>Puntos acumulados:</strong> <?php echo $cliente['puntos_acumulados'] ?? '0'; ?></p>
        <p><strong>Nivel cliente:</strong> <?php echo $cliente['nivel_cliente'] ?? '-'; ?></p>
        <p><strong>Límite de crédito:</strong> $<?php echo number_format($cliente['limite_credito'] ?? 0, 2, ',', '.'); ?></p>
        <p><strong>Saldo pendiente:</strong> $<?php echo number_format($cliente['saldo_pendiente'] ?? 0, 2, ',', '.'); ?></p>
        <p><strong>Estado:</strong> <?php echo ($cliente['activo'] ? '<span style="color:green;font-weight:bold;">Activo</span>' : '<span style="color:red;font-weight:bold;">Inactivo</span>'); ?></p>
    </div>
    <div style="text-align: right; padding: 0 20px 20px 20px;">
        <a href="clientes.php" class="btn btn-outline"><span class="material-icons">arrow_back</span> Volver a la lista</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 