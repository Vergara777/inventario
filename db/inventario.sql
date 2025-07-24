-- Sistema de Inventario Farmacéutico - Base de Datos Completa
-- Incluye todas las funcionalidades avanzadas

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS inventario_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventario_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado') DEFAULT 'empleado',
    nombre_completo VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    color VARCHAR(7) DEFAULT '#6c63ff',
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    cantidad_inicial INT DEFAULT 0,
    cantidad_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 20,
    categoria_id INT,
    fecha_compra DATE,
    fecha_vencimiento DATE,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabla de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    puntos_acumulados INT DEFAULT 0,
    nivel_cliente ENUM('bronce', 'plata', 'oro') DEFAULT 'bronce',
    limite_credito DECIMAL(10,2) DEFAULT 0,
    saldo_pendiente DECIMAL(10,2) DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de proveedores
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    contacto VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    ruc VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de ventas
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_venta VARCHAR(20) UNIQUE NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_venta DECIMAL(10,2) NOT NULL,
    utilidad_total DECIMAL(10,2) DEFAULT 0,
    descuento DECIMAL(10,2) DEFAULT 0,
    usuario VARCHAR(50),
    cliente_id INT,
    tipo_venta ENUM('contado', 'credito') DEFAULT 'contado',
    estado ENUM('completada', 'pendiente', 'cancelada') DEFAULT 'completada',
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);

-- Tabla de detalle de ventas
CREATE TABLE detalle_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    nombre_producto VARCHAR(100),
    cantidad_vendida INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    utilidad DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de devoluciones
CREATE TABLE devoluciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_devolucion VARCHAR(20) UNIQUE NOT NULL,
    venta_id INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT,
    total_devolucion DECIMAL(10,2) NOT NULL,
    usuario VARCHAR(50),
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
);

-- Tabla de detalle de devoluciones
CREATE TABLE detalle_devolucion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    devolucion_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad_devuelta INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (devolucion_id) REFERENCES devoluciones(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de promociones
CREATE TABLE promociones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo ENUM('descuento_porcentaje', 'descuento_fijo', 'compra_minima') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    aplicable_categorias TEXT,
    minimo_compra DECIMAL(10,2) DEFAULT 0
);

-- Tabla de cupones
CREATE TABLE cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    descripcion TEXT,
    tipo ENUM('porcentaje', 'fijo') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    uso_maximo INT DEFAULT 1,
    usos_actuales INT DEFAULT 0,
    minimo_compra DECIMAL(10,2) DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de gastos
CREATE TABLE gastos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(200) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    categoria ENUM('servicios', 'suministros', 'equipos', 'marketing', 'otros') NOT NULL,
    fecha DATE NOT NULL,
    usuario VARCHAR(50),
    comprobante VARCHAR(255),
    notas TEXT
);

-- Tabla de órdenes de compra
CREATE TABLE ordenes_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_orden VARCHAR(20) UNIQUE NOT NULL,
    proveedor_id INT NOT NULL,
    fecha_orden DATE NOT NULL,
    fecha_entrega_esperada DATE,
    total_orden DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'recibida', 'cancelada') DEFAULT 'pendiente',
    usuario VARCHAR(50),
    notas TEXT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE
);

-- Tabla de detalle de órdenes de compra
CREATE TABLE detalle_orden_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad_solicitada INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (orden_id) REFERENCES ordenes_compra(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de pagos a crédito
CREATE TABLE pagos_credito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    cliente_id INT NOT NULL,
    monto_pago DECIMAL(10,2) NOT NULL,
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    usuario VARCHAR(50),
    notas TEXT,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Tabla de alertas y notificaciones
CREATE TABLE alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('stock_bajo', 'vencimiento', 'pago_pendiente', 'devolucion_pendiente') NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_vista DATETIME,
    usuario_destino VARCHAR(50),
    leida BOOLEAN DEFAULT FALSE,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media'
);

-- Tabla de logs del sistema
CREATE TABLE logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50),
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INT,
    datos_anteriores TEXT,
    datos_nuevos TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);

-- Insertar categorías por defecto
INSERT INTO categorias (nombre, descripcion, color) VALUES
('Analgésicos', 'Medicamentos para el dolor', '#ff6b6b'),
('Antibióticos', 'Medicamentos antibacterianos', '#4ecdc4'),
('Vitaminas', 'Suplementos vitamínicos', '#45b7d1'),
('Cuidado Personal', 'Productos de higiene', '#96ceb4'),
('Primeros Auxilios', 'Material de primeros auxilios', '#feca57'),
('Cosméticos', 'Productos cosméticos', '#ff9ff3');

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (usuario, contrasena, rol, nombre_completo, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrador del Sistema', 'admin@sistema.com');

-- Crear índices para optimizar consultas
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_stock ON productos(cantidad_actual);
CREATE INDEX idx_ventas_fecha ON ventas(fecha);
CREATE INDEX idx_ventas_cliente ON ventas(cliente_id);
CREATE INDEX idx_ventas_usuario ON ventas(usuario);
CREATE INDEX idx_devoluciones_estado ON devoluciones(estado);
CREATE INDEX idx_gastos_fecha ON gastos(fecha);
CREATE INDEX idx_alertas_leida ON alertas(leida);
CREATE INDEX idx_logs_fecha ON logs_sistema(fecha);
