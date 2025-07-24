<?php
// includes/funciones.php
// Funciones centralizadas para el sistema de inventario avanzado

/**
 * Obtiene todas las estadísticas del dashboard
 * @param mysqli $conexion Conexión a la base de datos
 * @return array Array con todas las estadísticas
 */
function obtenerEstadisticasDashboard($conexion) {
    $estadisticas = [];
    
    // Total de productos
    $sql = "SELECT COUNT(*) as total FROM productos WHERE activo = 1";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['total_productos'] = intval($fila['total'] ?? 0);
    
    // Ventas del día actual
    $sql = "SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha) = CURDATE()";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['ventas_hoy'] = intval($fila['total'] ?? 0);
    
    // Productos agotados (stock entre 0 y 20)
    $sql = "SELECT COUNT(*) as total FROM productos WHERE cantidad_actual BETWEEN 0 AND 20";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['productos_agotados'] = intval($fila['total'] ?? 0);
    
    // Total de usuarios
    $sql = "SELECT COUNT(*) as total FROM usuarios";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['total_usuarios'] = intval($fila['total'] ?? 0);
    
    // Ganancias totales
    $sql = "SELECT COALESCE(SUM(utilidad_total), 0) as total FROM ventas";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['ganancias'] = floatval($fila['total'] ?? 0);
    
    // Ganancias del día
    $sql = "SELECT COALESCE(SUM(utilidad_total), 0) as total FROM ventas WHERE DATE(fecha) = CURDATE()";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['ganancias_dia'] = floatval($fila['total'] ?? 0);
    
    // Ganancias del mes
    $sql = "SELECT COALESCE(SUM(utilidad_total), 0) as total FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['ganancias_mes'] = floatval($fila['total'] ?? 0);
    
    // Ganancias del año
    $sql = "SELECT COALESCE(SUM(utilidad_total), 0) as total FROM ventas WHERE YEAR(fecha) = YEAR(CURDATE())";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['ganancias_ano'] = floatval($fila['total'] ?? 0);
    
    // Devoluciones pendientes
    $sql = "SELECT COUNT(*) as total FROM devoluciones WHERE estado = 'pendiente'";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['devoluciones_pendientes'] = intval($fila['total'] ?? 0);
    
    // Ventas a crédito pendientes
    $sql = "SELECT COUNT(*) as total FROM ventas WHERE tipo_venta = 'credito' AND estado = 'pendiente'";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['ventas_credito_pendientes'] = intval($fila['total'] ?? 0);
    
    // Total de clientes
    $sql = "SELECT COUNT(*) as total FROM clientes WHERE activo = 1";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['total_clientes'] = intval($fila['total'] ?? 0);
    
    // Total de proveedores
    $sql = "SELECT COUNT(*) as total FROM proveedores WHERE activo = 1";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    $estadisticas['total_proveedores'] = intval($fila['total'] ?? 0);
    
    return $estadisticas;
}

/**
 * Verifica si el usuario tiene sesión activa
 * @return bool True si tiene sesión, false en caso contrario
 */
function verificarSesion() {
    return isset($_SESSION['usuario']);
}

/**
 * Verifica si el usuario tiene rol de administrador
 * @return bool True si es admin, false en caso contrario
 */
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

/**
 * Redirige al login si no hay sesión activa
 */
function redirigirSiNoSesion() {
    if (!verificarSesion()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Obtiene el precio de compra de un producto
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $id_producto ID del producto
 * @return float Precio de compra
 */
function obtenerPrecioCompra($conexion, $id_producto) {
    $sql = "SELECT precio_compra FROM productos WHERE id = " . intval($id_producto);
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return floatval($fila['precio_compra'] ?? 0);
}

/**
 * Formatea un número para mostrar como moneda
 * @param float $numero Número a formatear
 * @return string Número formateado
 */
function formatearMoneda($numero) {
    return '$' . number_format($numero, 2, '.', ',');
}

/**
 * Valida que un valor sea un número positivo
 * @param mixed $valor Valor a validar
 * @return bool True si es válido, false en caso contrario
 */
function validarNumeroPositivo($valor) {
    return is_numeric($valor) && $valor > 0;
}

/**
 * Escapa datos para prevenir SQL Injection
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $dato Dato a escapar
 * @return string Dato escapado
 */
function escaparDato($conexion, $dato) {
    return mysqli_real_escape_string($conexion, trim($dato));
}

/**
 * Registra una venta completa
 * @param mysqli $conexion Conexión a la base de datos
 * @param array $carrito Array con productos del carrito
 * @param float $descuento Descuento aplicado
 * @param string $usuario Usuario que realiza la venta
 * @param int|null $cliente_id ID del cliente (opcional)
 * @param string $tipo_venta Tipo de venta (contado/credito)
 * @return bool True si se registró correctamente, false en caso contrario
 */
function registrarVenta($conexion, $carrito, $descuento, $usuario, $cliente_id = null, $tipo_venta = 'contado') {
    if (empty($carrito)) {
        return false;
    }
    
    $total = 0;
    $utilidad_total = 0;
    
    // Calcular totales
    foreach ($carrito as $item) {
        $total += $item['subtotal'];
        $precio_compra = obtenerPrecioCompra($conexion, $item['id']);
        $utilidad_total += ($item['precio'] - $precio_compra) * $item['cantidad'];
    }
    
    $total -= floatval($descuento);
    $fecha = date('Y-m-d H:i:s');
    $numero_venta = 'V-' . rand(10000, 99999);
    
    // Insertar venta
    $cliente_id_sql = is_null($cliente_id) ? "NULL" : intval($cliente_id);
    $sql = "INSERT INTO ventas (numero_venta, fecha, total_venta, utilidad_total, descuento, usuario, cliente_id, tipo_venta) 
            VALUES ('$numero_venta', '$fecha', '$total', '$utilidad_total', '$descuento', '$usuario', $cliente_id_sql, '$tipo_venta')";
    
    if (!mysqli_query($conexion, $sql)) {
        return false;
    }
    
    $venta_id = mysqli_insert_id($conexion);
    
    // Insertar detalles de venta y actualizar stock
    foreach ($carrito as $item) {
        $precio_compra = obtenerPrecioCompra($conexion, $item['id']);
        $utilidad = ($item['precio'] - $precio_compra) * $item['cantidad'];
        
        // Insertar detalle
        $sql = "INSERT INTO detalle_venta (venta_id, producto_id, nombre_producto, cantidad_vendida, precio_unitario, subtotal, utilidad) 
                VALUES ($venta_id, {$item['id']}, '" . escaparDato($conexion, $item['nombre']) . "', {$item['cantidad']}, {$item['precio']}, {$item['subtotal']}, $utilidad)";
        mysqli_query($conexion, $sql);
        
        // Actualizar stock
        $sql = "UPDATE productos SET cantidad_actual = cantidad_actual - {$item['cantidad']} WHERE id = {$item['id']}";
        mysqli_query($conexion, $sql);
    }
    
    // Si es venta a crédito, actualizar saldo del cliente
    if ($tipo_venta == 'credito' && $cliente_id) {
        $sql = "UPDATE clientes SET saldo_pendiente = saldo_pendiente + $total WHERE id = $cliente_id";
        mysqli_query($conexion, $sql);
    }
    
    // Registrar log
    registrarLog($conexion, $usuario, 'registrar_venta', 'ventas', $venta_id, '', "Venta registrada: $numero_venta");
    
    return true;
}

/**
 * Registra una devolución
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $venta_id ID de la venta
 * @param array $productos_devueltos Array con productos devueltos
 * @param string $motivo Motivo de la devolución
 * @param string $usuario Usuario que registra la devolución
 * @return bool True si se registró correctamente
 */
function registrarDevolucion($conexion, $venta_id, $productos_devueltos, $motivo, $usuario) {
    if (empty($productos_devueltos)) {
        return false;
    }
    
    $total_devolucion = 0;
    foreach ($productos_devueltos as $item) {
        $total_devolucion += $item['subtotal'];
    }
    
    $numero_devolucion = 'D-' . rand(10000, 99999);
    $motivo_escaped = escaparDato($conexion, $motivo);
    
    $sql = "INSERT INTO devoluciones (numero_devolucion, venta_id, motivo, total_devolucion, usuario) 
            VALUES ('$numero_devolucion', $venta_id, '$motivo_escaped', $total_devolucion, '$usuario')";
    
    if (!mysqli_query($conexion, $sql)) {
        return false;
    }
    
    $devolucion_id = mysqli_insert_id($conexion);
    
    // Insertar detalles de devolución
    foreach ($productos_devueltos as $item) {
        $sql = "INSERT INTO detalle_devolucion (devolucion_id, producto_id, cantidad_devuelta, precio_unitario, subtotal) 
                VALUES ($devolucion_id, {$item['id']}, {$item['cantidad']}, {$item['precio']}, {$item['subtotal']})";
        mysqli_query($conexion, $sql);
    }
    
    // Crear alerta para administradores
    crearAlerta($conexion, 'devolucion_pendiente', 'Devolución Pendiente', 
                "Nueva devolución registrada: $numero_devolucion por $motivo", 'alta');
    
    registrarLog($conexion, $usuario, 'registrar_devolucion', 'devoluciones', $devolucion_id, '', "Devolución registrada: $numero_devolucion");
    
    return true;
}

/**
 * Registra un pago a crédito
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $venta_id ID de la venta
 * @param int $cliente_id ID del cliente
 * @param float $monto_pago Monto del pago
 * @param string $metodo_pago Método de pago
 * @param string $usuario Usuario que registra el pago
 * @return bool True si se registró correctamente
 */
function registrarPagoCredito($conexion, $venta_id, $cliente_id, $monto_pago, $metodo_pago, $usuario) {
    $sql = "INSERT INTO pagos_credito (venta_id, cliente_id, monto_pago, metodo_pago, usuario) 
            VALUES ($venta_id, $cliente_id, $monto_pago, '$metodo_pago', '$usuario')";
    
    if (!mysqli_query($conexion, $sql)) {
        return false;
    }
    
    // Actualizar saldo pendiente del cliente
    $sql = "UPDATE clientes SET saldo_pendiente = saldo_pendiente - $monto_pago WHERE id = $cliente_id";
    mysqli_query($conexion, $sql);
    
    // Verificar si la venta está completamente pagada
    $sql = "SELECT v.total_venta, COALESCE(SUM(pc.monto_pago), 0) as total_pagado 
            FROM ventas v 
            LEFT JOIN pagos_credito pc ON v.id = pc.venta_id 
            WHERE v.id = $venta_id 
            GROUP BY v.id";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    
    if ($fila && $fila['total_pagado'] >= $fila['total_venta']) {
        $sql = "UPDATE ventas SET estado = 'completada' WHERE id = $venta_id";
        mysqli_query($conexion, $sql);
    }
    
    registrarLog($conexion, $usuario, 'registrar_pago', 'pagos_credito', mysqli_insert_id($conexion), '', "Pago registrado: $monto_pago");
    
    return true;
}

/**
 * Registra un gasto
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $descripcion Descripción del gasto
 * @param float $monto Monto del gasto
 * @param string $categoria Categoría del gasto
 * @param string $fecha Fecha del gasto
 * @param string $usuario Usuario que registra el gasto
 * @return bool True si se registró correctamente
 */
function registrarGasto($conexion, $descripcion, $monto, $categoria, $fecha, $usuario) {
    $descripcion_escaped = escaparDato($conexion, $descripcion);
    $categoria_escaped = escaparDato($conexion, $categoria);
    
    $sql = "INSERT INTO gastos (descripcion, monto, categoria, fecha, usuario) 
            VALUES ('$descripcion_escaped', $monto, '$categoria_escaped', '$fecha', '$usuario')";
    
    if (!mysqli_query($conexion, $sql)) {
        return false;
    }
    
    registrarLog($conexion, $usuario, 'registrar_gasto', 'gastos', mysqli_insert_id($conexion), '', "Gasto registrado: $descripcion_escaped");
    
    return true;
}

/**
 * Crea una alerta en el sistema
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $tipo Tipo de alerta
 * @param string $titulo Título de la alerta
 * @param string $mensaje Mensaje de la alerta
 * @param string $prioridad Prioridad de la alerta
 * @param string $usuario_destino Usuario destinatario (opcional)
 */
function crearAlerta($conexion, $tipo, $titulo, $mensaje, $prioridad = 'media', $usuario_destino = null) {
    $titulo_escaped = escaparDato($conexion, $titulo);
    $mensaje_escaped = escaparDato($conexion, $mensaje);
    $usuario_destino_sql = $usuario_destino ? "'$usuario_destino'" : "NULL";
    
    $sql = "INSERT INTO alertas (tipo, titulo, mensaje, prioridad, usuario_destino) 
            VALUES ('$tipo', '$titulo_escaped', '$mensaje_escaped', '$prioridad', $usuario_destino_sql)";
    
    mysqli_query($conexion, $sql);
}

/**
 * Registra un log en el sistema
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $usuario Usuario que realiza la acción
 * @param string $accion Acción realizada
 * @param string $tabla_afectada Tabla afectada
 * @param int $registro_id ID del registro afectado
 * @param string $datos_anteriores Datos anteriores (JSON)
 * @param string $datos_nuevos Datos nuevos (JSON)
 */
function registrarLog($conexion, $usuario, $accion, $tabla_afectada, $registro_id, $datos_anteriores = '', $datos_nuevos = '') {
    $usuario_escaped = escaparDato($conexion, $usuario);
    $accion_escaped = escaparDato($conexion, $accion);
    $tabla_escaped = escaparDato($conexion, $tabla_afectada);
    $datos_anteriores_escaped = escaparDato($conexion, $datos_anteriores);
    $datos_nuevos_escaped = escaparDato($conexion, $datos_nuevos);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $sql = "INSERT INTO logs_sistema (usuario, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip_address) 
            VALUES ('$usuario_escaped', '$accion_escaped', '$tabla_escaped', $registro_id, '$datos_anteriores_escaped', '$datos_nuevos_escaped', '$ip_address')";
    
    mysqli_query($conexion, $sql);
}

/**
 * Obtiene alertas no leídas para un usuario
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $usuario Usuario (opcional)
 * @return array Array de alertas
 */
function obtenerAlertasNoLeidas($conexion, $usuario = null) {
    $where = "WHERE leida = FALSE";
    if ($usuario) {
        $usuario_escaped = escaparDato($conexion, $usuario);
        $where .= " AND (usuario_destino = '$usuario_escaped' OR usuario_destino IS NULL)";
    }
    
    $sql = "SELECT * FROM alertas $where ORDER BY prioridad DESC, fecha_creacion DESC LIMIT 10";
    return mysqli_query($conexion, $sql);
}

/**
 * Marca una alerta como leída
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $alerta_id ID de la alerta
 * @param string $usuario Usuario que marca como leída
 */
function marcarAlertaLeida($conexion, $alerta_id, $usuario) {
    $fecha_vista = date('Y-m-d H:i:s');
    $sql = "UPDATE alertas SET leida = TRUE, fecha_vista = '$fecha_vista' WHERE id = $alerta_id";
    mysqli_query($conexion, $sql);
}

/**
 * Valida un cupón de descuento
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $codigo Código del cupón
 * @param float $total_compra Total de la compra
 * @return array|false Array con datos del cupón o false si no es válido
 */
function validarCupon($conexion, $codigo, $total_compra) {
    $codigo_escaped = escaparDato($conexion, $codigo);
    $fecha_actual = date('Y-m-d');
    
    $sql = "SELECT * FROM cupones 
            WHERE codigo = '$codigo_escaped' 
            AND activo = TRUE 
            AND fecha_inicio <= '$fecha_actual' 
            AND fecha_fin >= '$fecha_actual' 
            AND usos_actuales < uso_maximo 
            AND minimo_compra <= $total_compra";
    
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

/**
 * Aplica un cupón y actualiza su uso
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $cupon_id ID del cupón
 * @param float $total_compra Total de la compra
 * @return float Monto del descuento aplicado
 */
function aplicarCupon($conexion, $cupon_id, $total_compra) {
    $sql = "SELECT * FROM cupones WHERE id = $cupon_id";
    $resultado = mysqli_query($conexion, $sql);
    $cupon = mysqli_fetch_assoc($resultado);
    
    if (!$cupon) {
        return 0;
    }
    
    $descuento = 0;
    if ($cupon['tipo'] == 'porcentaje') {
        $descuento = ($total_compra * $cupon['valor']) / 100;
    } else {
        $descuento = $cupon['valor'];
    }
    
    // Actualizar uso del cupón
    $sql = "UPDATE cupones SET usos_actuales = usos_actuales + 1 WHERE id = $cupon_id";
    mysqli_query($conexion, $sql);
    
    return $descuento;
}

/**
 * Obtiene productos por vencer
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $dias_antes Días antes del vencimiento para alertar
 * @return array Array de productos por vencer
 */
function obtenerProductosPorVencer($conexion, $dias_antes = 30) {
    $fecha_limite = date('Y-m-d', strtotime("+$dias_antes days"));
    
    $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.fecha_vencimiento <= '$fecha_limite' 
            AND p.fecha_vencimiento >= CURDATE() 
            AND p.activo = TRUE 
            ORDER BY p.fecha_vencimiento ASC";
    
    return mysqli_query($conexion, $sql);
}

/**
 * Genera un reporte de ventas por período
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $fecha_inicio Fecha de inicio
 * @param string $fecha_fin Fecha de fin
 * @param string $usuario Usuario filtro (opcional)
 * @return array Array con datos del reporte
 */
function generarReporteVentas($conexion, $fecha_inicio, $fecha_fin, $usuario = '') {
    $where = "WHERE v.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'";
    if ($usuario) {
        $usuario_escaped = escaparDato($conexion, $usuario);
        $where .= " AND v.usuario = '$usuario_escaped'";
    }
    
    $sql = "SELECT 
                COUNT(*) as total_ventas,
                COALESCE(SUM(v.total_venta), 0) as total_ingresos,
                COALESCE(SUM(v.utilidad_total), 0) as total_utilidades,
                COALESCE(SUM(v.descuento), 0) as total_descuentos,
                AVG(v.total_venta) as promedio_venta
            FROM ventas v 
            $where";
    
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

/**
 * Genera un reporte de gastos por período
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $fecha_inicio Fecha de inicio
 * @param string $fecha_fin Fecha de fin
 * @return array Array con datos del reporte
 */
function generarReporteGastos($conexion, $fecha_inicio, $fecha_fin) {
    $sql = "SELECT 
                COUNT(*) as total_gastos,
                COALESCE(SUM(monto), 0) as total_gastos_monto,
                categoria,
                COUNT(*) as cantidad_por_categoria
            FROM gastos 
            WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
            GROUP BY categoria
            ORDER BY total_gastos_monto DESC";
    
    return mysqli_query($conexion, $sql);
}

/**
 * Calcula el balance general (ingresos - gastos)
 * @param mysqli $conexion Conexión a la base de datos
 * @param string $fecha_inicio Fecha de inicio
 * @param string $fecha_fin Fecha de fin
 * @return array Array con balance
 */
function calcularBalance($conexion, $fecha_inicio, $fecha_fin) {
    // Total ingresos
    $sql = "SELECT COALESCE(SUM(total_venta), 0) as total_ingresos FROM ventas WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'";
    $resultado = mysqli_query($conexion, $sql);
    $ingresos = floatval(mysqli_fetch_assoc($resultado)['total_ingresos']);
    
    // Total gastos
    $sql = "SELECT COALESCE(SUM(monto), 0) as total_gastos FROM gastos WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $resultado = mysqli_query($conexion, $sql);
    $gastos = floatval(mysqli_fetch_assoc($resultado)['total_gastos']);
    
    return [
        'ingresos' => $ingresos,
        'gastos' => $gastos,
        'balance' => $ingresos - $gastos,
        'porcentaje_gastos' => $ingresos > 0 ? ($gastos / $ingresos) * 100 : 0
    ];
}

// ===== FUNCIONES PARA DASHBOARD =====

function obtenerTotalProductos() {
    global $conexion;
    $sql = "SELECT COUNT(*) as total FROM productos WHERE activo = 1";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return intval($fila['total'] ?? 0);
}

function obtenerVentasHoy() {
    global $conexion;
    $sql = "SELECT COALESCE(SUM(total_venta), 0) as total FROM ventas WHERE DATE(fecha) = CURDATE()";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return floatval($fila['total'] ?? 0);
}

function obtenerTotalClientes() {
    global $conexion;
    $sql = "SELECT COUNT(*) as total FROM clientes WHERE activo = 1";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return intval($fila['total'] ?? 0);
}

function obtenerProductosAgotados() {
    global $conexion;
    $sql = "SELECT COUNT(*) as total FROM productos WHERE cantidad_actual <= stock_minimo AND activo = 1";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return intval($fila['total'] ?? 0);
}

function obtenerVentasMes() {
    global $conexion;
    $sql = "SELECT COALESCE(SUM(total_venta), 0) as total FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return floatval($fila['total'] ?? 0);
}

function obtenerGastosMes() {
    global $conexion;
    $sql = "SELECT COALESCE(SUM(monto), 0) as total FROM gastos WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return floatval($fila['total'] ?? 0);
}

function obtenerCreditosPendientes() {
    global $conexion;
    $sql = "SELECT COALESCE(SUM(total_venta), 0) as total FROM ventas WHERE tipo_venta = 'credito' AND estado = 'pendiente'";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return floatval($fila['total'] ?? 0);
}

function obtenerDevolucionesPendientes() {
    global $conexion;
    $sql = "SELECT COUNT(*) as total FROM devoluciones WHERE estado = 'pendiente'";
    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);
    return intval($fila['total'] ?? 0);
}

function obtenerActividadReciente() {
    global $conexion;
    $sql = "SELECT * FROM logs_sistema ORDER BY fecha DESC LIMIT 10";
    return mysqli_query($conexion, $sql);
}

function obtenerAlertasUrgentes() {
    global $conexion;
    $sql = "SELECT * FROM alertas WHERE prioridad = 'alta' AND leida = 0 ORDER BY fecha_creacion DESC LIMIT 5";
    return mysqli_query($conexion, $sql);
}

function obtenerProductosStockBajo() {
    global $conexion;
    $sql = "SELECT * FROM productos WHERE cantidad_actual <= stock_minimo AND activo = 1 ORDER BY cantidad_actual ASC LIMIT 10";
    return mysqli_query($conexion, $sql);
}

// ===== FUNCIONES PARA INVENTARIO =====

function obtenerProductos() {
    global $conexion;
    $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.activo = 1 
            ORDER BY p.nombre ASC";
    return mysqli_query($conexion, $sql);
}

function obtenerProducto($id) {
    global $conexion;
    $sql = "SELECT * FROM productos WHERE id = " . intval($id);
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

function agregarProducto($datos) {
    global $conexion;
    
    $codigo = mysqli_real_escape_string($conexion, $datos['codigo']);
    $nombre = mysqli_real_escape_string($conexion, $datos['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $datos['descripcion'] ?? '');
    $categoria = mysqli_real_escape_string($conexion, $datos['categoria']);
    $precio_compra = floatval($datos['precio_compra']);
    $precio_venta = floatval($datos['precio_venta']);
    $stock = intval($datos['stock']);
    $stock_minimo = intval($datos['stock_minimo']);
    $fecha_vencimiento = !empty($datos['fecha_vencimiento']) ? "'" . $datos['fecha_vencimiento'] . "'" : 'NULL';
    
    // Buscar o crear categoría
    $sql_cat = "SELECT id FROM categorias WHERE nombre = '$categoria'";
    $result_cat = mysqli_query($conexion, $sql_cat);
    if (mysqli_num_rows($result_cat) > 0) {
        $categoria_id = mysqli_fetch_assoc($result_cat)['id'];
    } else {
        $sql_insert_cat = "INSERT INTO categorias (nombre) VALUES ('$categoria')";
        mysqli_query($conexion, $sql_insert_cat);
        $categoria_id = mysqli_insert_id($conexion);
    }
    
    $sql = "INSERT INTO productos (codigo, nombre, descripcion, precio_compra, precio_venta, cantidad_inicial, cantidad_actual, stock_minimo, categoria_id, fecha_vencimiento) 
            VALUES ('$codigo', '$nombre', '$descripcion', $precio_compra, $precio_venta, $stock, $stock, $stock_minimo, $categoria_id, $fecha_vencimiento)";
    
    return mysqli_query($conexion, $sql);
}

function editarProducto($datos) {
    global $conexion;
    
    $id = intval($datos['id']);
    $codigo = mysqli_real_escape_string($conexion, $datos['codigo']);
    $nombre = mysqli_real_escape_string($conexion, $datos['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $datos['descripcion'] ?? '');
    $categoria = mysqli_real_escape_string($conexion, $datos['categoria']);
    $precio_compra = floatval($datos['precio_compra']);
    $precio_venta = floatval($datos['precio_venta']);
    $stock = intval($datos['stock']);
    $stock_minimo = intval($datos['stock_minimo']);
    $fecha_vencimiento = !empty($datos['fecha_vencimiento']) ? "'" . $datos['fecha_vencimiento'] . "'" : 'NULL';
    
    // Buscar o crear categoría
    $sql_cat = "SELECT id FROM categorias WHERE nombre = '$categoria'";
    $result_cat = mysqli_query($conexion, $sql_cat);
    if (mysqli_num_rows($result_cat) > 0) {
        $categoria_id = mysqli_fetch_assoc($result_cat)['id'];
    } else {
        $sql_insert_cat = "INSERT INTO categorias (nombre) VALUES ('$categoria')";
        mysqli_query($conexion, $sql_insert_cat);
        $categoria_id = mysqli_insert_id($conexion);
    }
    
    $sql = "UPDATE productos SET 
            codigo = '$codigo', 
            nombre = '$nombre', 
            descripcion = '$descripcion', 
            precio_compra = $precio_compra, 
            precio_venta = $precio_venta, 
            cantidad_actual = $stock, 
            stock_minimo = $stock_minimo, 
            categoria_id = $categoria_id, 
            fecha_vencimiento = $fecha_vencimiento 
            WHERE id = $id";
    
    return mysqli_query($conexion, $sql);
}

function eliminarProducto($id) {
    global $conexion;
    $sql = "UPDATE productos SET activo = 0 WHERE id = " . intval($id);
    return mysqli_query($conexion, $sql);
}

// ===== FUNCIONES PARA CLIENTES =====

function obtenerClientes() {
    global $conexion;
    $sql = "SELECT * FROM clientes WHERE activo = 1 ORDER BY nombre ASC";
    return mysqli_query($conexion, $sql);
}

function obtenerCliente($id) {
    global $conexion;
    $sql = "SELECT * FROM clientes WHERE id = " . intval($id);
    $resultado = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($resultado);
}

function agregarCliente($datos) {
    global $conexion;
    $nombre = mysqli_real_escape_string($conexion, $datos['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $datos['apellido'] ?? '');
    $email = mysqli_real_escape_string($conexion, $datos['email'] ?? '');
    $telefono = mysqli_real_escape_string($conexion, $datos['telefono'] ?? '');
    $documento = mysqli_real_escape_string($conexion, $datos['documento'] ?? '');
    $tipo_documento = mysqli_real_escape_string($conexion, $datos['tipo_documento'] ?? '');
    $direccion = mysqli_real_escape_string($conexion, $datos['direccion'] ?? '');
    $ciudad = mysqli_real_escape_string($conexion, $datos['ciudad'] ?? '');
    $fecha_nacimiento = !empty($datos['fecha_nacimiento']) ? "'" . $datos['fecha_nacimiento'] . "'" : 'NULL';
    $genero = mysqli_real_escape_string($conexion, $datos['genero'] ?? '');
    $observaciones = mysqli_real_escape_string($conexion, $datos['observaciones'] ?? '');
    $activo = (isset($datos['estado']) && $datos['estado'] === 'activo') ? 1 : 0;
    $sql = "INSERT INTO clientes (nombre, apellido, email, telefono, documento, tipo_documento, direccion, ciudad, fecha_nacimiento, genero, observaciones, activo, fecha_registro) VALUES ('$nombre', '$apellido', '$email', '$telefono', '$documento', '$tipo_documento', '$direccion', '$ciudad', $fecha_nacimiento, '$genero', '$observaciones', $activo, NOW())";
    return mysqli_query($conexion, $sql);
}

function editarCliente($datos) {
    global $conexion;
    $id = intval($datos['id']);
    $nombre = mysqli_real_escape_string($conexion, $datos['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $datos['apellido'] ?? '');
    $email = mysqli_real_escape_string($conexion, $datos['email'] ?? '');
    $telefono = mysqli_real_escape_string($conexion, $datos['telefono'] ?? '');
    $documento = mysqli_real_escape_string($conexion, $datos['documento'] ?? '');
    $tipo_documento = mysqli_real_escape_string($conexion, $datos['tipo_documento'] ?? '');
    $direccion = mysqli_real_escape_string($conexion, $datos['direccion'] ?? '');
    $ciudad = mysqli_real_escape_string($conexion, $datos['ciudad'] ?? '');
    $fecha_nacimiento = !empty($datos['fecha_nacimiento']) ? "'" . $datos['fecha_nacimiento'] . "'" : 'NULL';
    $genero = mysqli_real_escape_string($conexion, $datos['genero'] ?? '');
    $observaciones = mysqli_real_escape_string($conexion, $datos['observaciones'] ?? '');
    $activo = (isset($datos['estado']) && $datos['estado'] === 'activo') ? 1 : 0;
    $sql = "UPDATE clientes SET nombre = '$nombre', apellido = '$apellido', email = '$email', telefono = '$telefono', documento = '$documento', tipo_documento = '$tipo_documento', direccion = '$direccion', ciudad = '$ciudad', fecha_nacimiento = $fecha_nacimiento, genero = '$genero', observaciones = '$observaciones', activo = $activo WHERE id = $id";
    return mysqli_query($conexion, $sql);
}

function eliminarCliente($id) {
    global $conexion;
    $sql = "UPDATE clientes SET activo = 0 WHERE id = " . intval($id);
    return mysqli_query($conexion, $sql);
}

// ===== FUNCIONES PARA VENTAS =====

function procesarVenta($datos) {
    global $conexion;
    
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        return false;
    }
    
    $cliente_id = !empty($datos['cliente_id']) ? intval($datos['cliente_id']) : 'NULL';
    $metodo_pago = mysqli_real_escape_string($conexion, $datos['metodo_pago']);
    $descuento = floatval($datos['descuento'] ?? 0);
    $observaciones = mysqli_real_escape_string($conexion, $datos['observaciones'] ?? '');
    
    // Calcular total
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    
    $total_final = $total - ($total * $descuento / 100);
    $fecha = date('Y-m-d H:i:s');
    $numero_venta = 'V-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Insertar venta
    $sql = "INSERT INTO ventas (numero_venta, fecha, total_venta, descuento, usuario, cliente_id) 
            VALUES ('$numero_venta', '$fecha', $total_final, $descuento, '" . $_SESSION['usuario'] . "', $cliente_id)";
    
    if (!mysqli_query($conexion, $sql)) {
        return false;
    }
    
    $venta_id = mysqli_insert_id($conexion);
    
    // Insertar detalles y actualizar stock
    foreach ($_SESSION['carrito'] as $producto_id => $item) {
        // Insertar detalle
        $sql = "INSERT INTO detalle_venta (venta_id, producto_id, nombre_producto, cantidad_vendida, precio_unitario, subtotal) 
                VALUES ($venta_id, $producto_id, '" . mysqli_real_escape_string($conexion, $item['nombre']) . "', " . $item['cantidad'] . ", " . $item['precio'] . ", " . ($item['precio'] * $item['cantidad']) . ")";
        mysqli_query($conexion, $sql);
        
        // Actualizar stock
        $sql = "UPDATE productos SET cantidad_actual = cantidad_actual - " . $item['cantidad'] . " WHERE id = $producto_id";
        mysqli_query($conexion, $sql);
    }
    
    return $numero_venta;
}

// ===== FUNCIÓN AUXILIAR =====

function calcularEdad($fecha_nacimiento) {
    if (empty($fecha_nacimiento)) return '';
    $fecha = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha);
    return $edad->y;
}
?>
