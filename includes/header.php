<?php
// includes/header.php - Sidebar y header modernos estilo FarmaSys
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - FarmaSys' : 'FarmaSys - Sistema de Inventario'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script src="assets/js/funciones.js"></script>
</head>
<body>
<div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
                <span class="material-icons sidebar-logo-icon">local_pharmacy</span>
                <div class="sidebar-logo-text">FarmaSys</div>
        </div>
            
        <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">dashboard</span>
                    Dashboard
                </a>
                
                <a href="inventario.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'inventario.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">inventory</span>
                    Inventario
                </a>
                
                <a href="ventas.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'ventas.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">point_of_sale</span>
                    Ventas
                </a>
                
                <a href="devoluciones.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'devoluciones.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">assignment_return</span>
                    Devoluciones
                </a>
                
                <a href="creditos.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'creditos.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">credit_card</span>
                    Créditos
                </a>
                
                <a href="promociones.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'promociones.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">local_offer</span>
                    Promociones
                </a>
                
                <a href="clientes.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'clientes.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">people</span>
                    Clientes
                </a>
                
                <a href="proveedores.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'proveedores.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">business</span>
                    Proveedores
                </a>
                
                <a href="ordenes_compra.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'ordenes_compra.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">shopping_cart</span>
                    Órdenes de Compra
                </a>
                
                <a href="gastos.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'gastos.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">account_balance_wallet</span>
                    Gastos
                </a>
                
                <a href="estantes.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'estantes.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">grid_view</span>
                    Estantes
                </a>
                
                <a href="agotados.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'agotados.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">warning</span>
                    Productos Agotados
                </a>
                
                <a href="alertas.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'alertas.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">notifications</span>
                    Alertas
                </a>
                
                <a href="reportes.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'reportes.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">analytics</span>
                    Reportes
                </a>
                
                <a href="usuarios.php" class="sidebar-link <?php echo strpos($_SERVER['PHP_SELF'], 'usuarios.php') !== false ? 'active' : ''; ?>">
                    <span class="material-icons">admin_panel_settings</span>
                    Usuarios
                </a>
        </nav>
            
            <div class="sidebar-footer">
                
            </div>
    </aside>

        <!-- Main Content -->
    <main class="main-content">
            <!-- Header -->
        <header class="main-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <span class="material-icons">menu</span>
                    </button>
                    <h1 class="page-title"><?php echo isset($page_title) ? $page_title : 'FarmaSys'; ?></h1>
                </div>
                
                <div class="header-right">
                    <div class="header-actions">
                        <a href="alertas.php" class="notification-btn" title="Ver alertas">
                            <span class="material-icons">notifications</span>
                            <?php if (isset($alertas_count) && $alertas_count > 0): ?>
                                <span class="notification-badge"><?php echo $alertas_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="user-menu">
                        <span class="material-icons">account_circle</span>
                        <span><?php 
echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 
     (isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : 
     (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario'));
?></span>
                        <span class="material-icons">keyboard_arrow_down</span>
                        
                        <div class="user-dropdown">
                            <a href="perfil.php">
                                <span class="material-icons">person</span>
                                Mi Perfil
                            </a>
                            <a href="configuracion.php">
                                <span class="material-icons">settings</span>
                                Configuración
                            </a>
                            <a href="logout.php">
                                <span class="material-icons">logout</span>
                                Cerrar Sesión
                            </a>
                        </div>
            </div>
            </div>
        </header>

            <!-- Content Wrapper -->
            <div class="content-wrapper">

