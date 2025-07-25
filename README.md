# Sistema de Inventario Farmacéutico Avanzado - FarmaSys

## 🚀 **Descripción**

Sistema completo y profesional de gestión de inventario farmacéutico con control avanzado de ventas, usuarios, reportes, alertas y todas las funcionalidades necesarias para una farmacia moderna. Desarrollado en PHP con MySQL, siguiendo las mejores prácticas de seguridad y organización de código.

## ✨ **Nuevas Funcionalidades Implementadas**

### 🔄 **Sistema de Devoluciones y Reembolsos**
- ✅ Gestión completa de devoluciones de productos
- ✅ Procesamiento de reembolsos con validación
- ✅ Historial detallado de devoluciones
- ✅ Estados: Pendiente, Aprobada, Rechazada
- ✅ Restauración automática de stock al aprobar
- ✅ Alertas para administradores

### 💳 **Ventas a Crédito y Cobranza**
- ✅ Sistema completo de ventas a crédito
- ✅ Control de cuentas por cobrar
- ✅ Registro de pagos parciales
- ✅ Historial de pagos por cliente
- ✅ Alertas de pagos pendientes
- ✅ Estados: Pendiente, Completada, Cancelada

### 🎁 **Sistema de Promociones y Cupones**
- ✅ Gestión de promociones por temporada
- ✅ Cupones de descuento personalizables
- ✅ Descuentos por porcentaje o monto fijo
- ✅ Validación de fechas y usos máximos
- ✅ Aplicación por categorías de productos
- ✅ Control de compras mínimas

### 💰 **Control de Gastos Operativos**
- ✅ Registro detallado de gastos diarios
- ✅ Categorización: Servicios, Suministros, Equipos, Marketing, Otros
- ✅ Reportes de gastos vs ingresos
- ✅ Balance general automático
- ✅ Control de caja chica
- ✅ Historial completo de gastos

### 🏢 **Sistema de Proveedores**
- ✅ Gestión completa de proveedores
- ✅ Información de contacto y RUC
- ✅ Historial de compras por proveedor
- ✅ Evaluación de proveedores
- ✅ Control de estados activo/inactivo

### 📦 **Órdenes de Compra**
- ✅ Creación de órdenes de compra
- ✅ Estados: Pendiente, Confirmada, Recibida, Cancelada
- ✅ Actualización automática de stock al recibir
- ✅ Seguimiento de fechas de entrega
- ✅ Cálculo automático de totales
- ✅ Historial completo de órdenes

### 🔔 **Sistema de Alertas y Notificaciones**
- ✅ Alertas automáticas de stock bajo
- ✅ Notificaciones de productos por vencer
- ✅ Alertas de pagos pendientes
- ✅ Notificaciones de devoluciones pendientes
- ✅ Prioridades: Alta, Media, Baja
- ✅ Marcado como leído/no leído
- ✅ Contador de alertas no leídas

### 📊 **Reportes Avanzados**
- ✅ Análisis de tendencias de ventas
- ✅ Reportes de rentabilidad por producto
- ✅ Análisis de clientes más frecuentes
- ✅ Reportes de rendimiento por empleado
- ✅ Balance general (ingresos - gastos)
- ✅ Exportación de datos

### 🔐 **Seguridad y Auditoría**
- ✅ Logs completos del sistema
- ✅ Registro de todas las acciones
- ✅ Control de acceso por roles
- ✅ Validación de datos robusta
- ✅ Protección contra SQL Injection
- ✅ Escape de datos automático

## 🏗️ **Arquitectura del Sistema**

### Estructura de Archivos Actualizada
```
inventario-app/
├── assets/                 # Recursos estáticos
│   ├── css/
│   ├── js/
│   └── img/
├── includes/              # Archivos de inclusión
│   ├── conexion.php      # Conexión a base de datos
│   ├── funciones.php     # Funciones centralizadas avanzadas
│   ├── header.php        # Cabecera común actualizada
│   └── footer.php        # Pie de página común
├── ajax/                  # Archivos AJAX
│   └── get_productos_venta.php
├── db/                   # Base de datos
│   └── inventario.sql    # Estructura completa actualizada
├── dashboard.php         # Panel principal mejorado
├── inventario.php        # Gestión de productos
├── ventas.php           # Sistema de ventas
├── clientes.php         # Gestión de clientes
├── creditos.php         # Sistema de créditos
├── devoluciones.php     # Sistema de devoluciones
├── promociones.php      # Promociones y cupones
├── proveedores.php      # Gestión de proveedores
├── ordenes_compra.php   # Órdenes de compra
├── gastos.php           # Control de gastos
├── alertas.php          # Sistema de alertas
├── reportes.php         # Reportes avanzados
├── usuarios.php         # Gestión de usuarios
├── registro.php         # Registro de usuarios
├── index.php            # Login
└── logout.php           # Cerrar sesión
```

### Base de Datos Completa

#### Nuevas Tablas Implementadas:
- **devoluciones** - Gestión de devoluciones
- **detalle_devolucion** - Detalles de productos devueltos
- **promociones** - Sistema de promociones
- **cupones** - Cupones de descuento
- **gastos** - Control de gastos operativos
- **proveedores** - Gestión de proveedores
- **ordenes_compra** - Órdenes de compra
- **detalle_orden_compra** - Detalles de órdenes
- **pagos_credito** - Pagos a crédito
- **alertas** - Sistema de notificaciones
- **logs_sistema** - Auditoría del sistema

#### Tablas Mejoradas:
- **ventas** - Agregado tipo_venta y estado
- **clientes** - Agregado límite_credito y saldo_pendiente
- **productos** - Agregado stock_minimo y fechas
- **usuarios** - Agregado información adicional

## 🛠️ **Instalación**

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensión mysqli habilitada

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone [url-del-repositorio]
   cd inventario-app
   ```

2. **Configurar la base de datos**
   - Crear base de datos `inventario_db`
   - Importar `db/inventario.sql` (estructura completa)

3. **Configurar conexión**
   - Editar `includes/conexion.php`
   - Actualizar credenciales de base de datos

4. **Configurar servidor web**
   - Apuntar al directorio del proyecto
   - Asegurar permisos de escritura

5. **Crear usuario administrador**
   - Acceder a `registro.php`
   - Registrar usuario con rol "admin"
   - Usuario por defecto: admin / password

## 📱 **Uso del Sistema**

### Para Administradores
1. **Dashboard**: Estadísticas completas y alertas
2. **Inventario**: Gestión avanzada de productos
3. **Ventas**: Sistema completo de ventas
4. **Clientes**: Gestión de clientes y créditos
5. **Devoluciones**: Procesamiento de devoluciones
6. **Promociones**: Gestión de promociones y cupones
7. **Proveedores**: Gestión de proveedores
8. **Órdenes**: Órdenes de compra
9. **Gastos**: Control de gastos operativos
10. **Alertas**: Sistema de notificaciones
11. **Reportes**: Reportes avanzados
12. **Usuarios**: Gestión de personal

### Para Empleados
1. **Dashboard**: Estadísticas básicas
2. **Inventario**: Consulta de productos
3. **Ventas**: Procesamiento de ventas
4. **Clientes**: Consulta de clientes
5. **Devoluciones**: Registro de devoluciones
6. **Gastos**: Registro de gastos
7. **Alertas**: Ver notificaciones
8. **Reportes**: Reportes básicos

## 🔧 **Funciones Principales**

### Funciones de Estadísticas Avanzadas
- `obtenerEstadisticasDashboard()`: Estadísticas completas
- `generarReporteVentas()`: Reportes de ventas
- `generarReporteGastos()`: Reportes de gastos
- `calcularBalance()`: Balance general
- `obtenerProductosPorVencer()`: Productos por vencer

### Funciones de Negocio
- `registrarVenta()`: Venta completa con crédito
- `registrarDevolucion()`: Devolución con validación
- `registrarPagoCredito()`: Pago a crédito
- `registrarGasto()`: Registro de gastos
- `validarCupon()`: Validación de cupones
- `aplicarCupon()`: Aplicación de descuentos

### Funciones de Alertas
- `crearAlerta()`: Crear alertas automáticas
- `obtenerAlertasNoLeidas()`: Alertas pendientes
- `marcarAlertaLeida()`: Marcar como leída
- `generarAlertasAutomaticas()`: Alertas automáticas

### Funciones de Seguridad
- `registrarLog()`: Auditoría completa
- `verificarSesion()`: Control de sesión
- `esAdmin()`: Verificación de roles
- `escaparDato()`: Protección SQL Injection

## 🎯 **Características Destacadas**

### 🔒 **Seguridad Robusta**
- ✅ Protección contra SQL Injection
- ✅ Escape automático de datos
- ✅ Validación de sesiones
- ✅ Control de acceso por roles
- ✅ Hash seguro de contraseñas
- ✅ Auditoría completa de acciones

### 📊 **Reportes Profesionales**
- ✅ Dashboard interactivo
- ✅ Estadísticas en tiempo real
- ✅ Reportes filtrables
- ✅ Análisis de tendencias
- ✅ Balance general automático
- ✅ Exportación de datos

### 🔔 **Alertas Inteligentes**
- ✅ Alertas automáticas
- ✅ Notificaciones en tiempo real
- ✅ Priorización de alertas
- ✅ Contador de alertas no leídas
- ✅ Diferentes tipos de alertas
- ✅ Historial de alertas

### 💼 **Gestión Completa**
- ✅ Control de inventario avanzado
- ✅ Sistema de ventas completo
- ✅ Gestión de clientes y créditos
- ✅ Devoluciones y reembolsos
- ✅ Promociones y cupones
- ✅ Proveedores y órdenes
- ✅ Control de gastos
- ✅ Usuarios y roles

## 🔄 **Mantenimiento**

### Tareas Regulares
- Revisar alertas diariamente
- Verificar productos agotados
- Analizar reportes de ventas
- Revisar pagos pendientes
- Verificar devoluciones pendientes
- Controlar gastos operativos
- Respaldar base de datos

### Actualizaciones
- Mantener PHP y MySQL actualizados
- Revisar logs de errores
- Optimizar consultas según uso
- Actualizar promociones
- Revisar alertas automáticas

## 📞 **Soporte**

Para soporte técnico o consultas sobre el sistema, contactar al equipo de desarrollo.

---

**🎉 Sistema completamente funcional y listo para producción**

**Desarrollado con ❤️ para la gestión eficiente de farmacias modernas** 
**blablabla**