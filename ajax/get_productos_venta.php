<?php
session_start();
include '../includes/conexion.php';
include '../includes/funciones.php';

// Verificar sesión
if (!verificarSesion()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if (isset($_GET['venta_id'])) {
    $venta_id = intval($_GET['venta_id']);
    
    $sql = "SELECT dv.*, p.nombre as producto_nombre 
            FROM detalle_venta dv 
            LEFT JOIN productos p ON dv.producto_id = p.id 
            WHERE dv.venta_id = $venta_id";
    
    $resultado = mysqli_query($conexion, $sql);
    $productos = [];
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $productos[] = [
            'producto_id' => $fila['producto_id'],
            'nombre' => $fila['nombre_producto'],
            'cantidad_vendida' => $fila['cantidad_vendida'],
            'precio_unitario' => formatearMoneda($fila['precio_unitario']),
            'subtotal' => $fila['subtotal']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de venta requerido'
    ]);
}
?> 