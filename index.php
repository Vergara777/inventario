<?php
session_start();
require_once 'includes/conexion.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM usuarios WHERE usuario = ? AND activo = 1";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($usuario_data = mysqli_fetch_assoc($resultado)) {
        if (password_verify($password, $usuario_data['contrasena'])) {
            $_SESSION['usuario_id'] = $usuario_data['id'];
            $_SESSION['usuario'] = $usuario_data['usuario'];
            $_SESSION['nombre'] = $usuario_data['nombre_completo'];
            $_SESSION['rol'] = $usuario_data['rol'];
            
            // Registrar inicio de sesión
            $ip = $_SERVER['REMOTE_ADDR'];
            $fecha = date('Y-m-d H:i:s');
            $log_query = "INSERT INTO logs_sistema (usuario_id, accion, detalles, ip, fecha) VALUES (?, 'login', 'Inicio de sesión exitoso', ?, ?)";
            $log_stmt = mysqli_prepare($conexion, $log_query);
            mysqli_stmt_bind_param($log_stmt, "iss", $usuario_data['id_address'], $ip, $fecha);
            mysqli_stmt_execute($log_stmt);
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Contraseña incorrecta';
        }
    } else {
        $error = 'Usuario no encontrado o inactivo';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmaSys - Inicio de Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script src="assets/js/funciones.js"></script>
</head>
<body class="login-body">
    <div class="login-container">
        <!-- Background Animation -->
        <div class="background-animation">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
                <div class="shape shape-5"></div>
            </div>
        </div>
        
        <!-- Login Form -->
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <span class="material-icons logo-icon">local_pharmacy</span>
                    <h1 class="logo-text">FarmaSys</h1>
                </div>
                <p class="login-subtitle">Sistema de Inventario Farmacéutico</p>
            </div>
            
            <?php if ($error): ?>
                <div class="login-error">
                    <span class="material-icons">error</span>
                    <?php echo $error; ?>
            </div>
        <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <div class="input-container">
                        <span class="material-icons input-icon">person</span>
                        <input type="text" id="usuario" name="usuario" required 
                               placeholder="Ingresa tu usuario" autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-container">
                        <span class="material-icons input-icon">lock</span>
                        <input type="password" id="password" name="password" required 
                               placeholder="Ingresa tu contraseña" autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <span class="material-icons">visibility</span>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkmark"></span>
                        Recordarme
                    </label>
                    <a href="recuperar_password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="login-btn">
                    <span class="material-icons">login</span>
                    Iniciar Sesión
                </button>
        </form>

            <div class="login-footer">
                <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
                <div class="social-login">
                    <p>O inicia sesión con:</p>
                    <div class="social-buttons">
                        <button class="social-btn google">
                            <span class="material-icons">google</span>
                            Google
                        </button>
                        <button class="social-btn facebook">
                            <span class="material-icons">facebook</span>
                            Facebook
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Features Section -->
        <div class="features-section">
            <h2>Características Principales</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <span class="material-icons">inventory</span>
                    <h3>Gestión de Inventario</h3>
                    <p>Control completo de productos, stock y alertas de agotamiento</p>
                </div>
                
                <div class="feature-item">
                    <span class="material-icons">point_of_sale</span>
                    <h3>Ventas y Facturación</h3>
                    <p>Sistema de ventas integrado con múltiples métodos de pago</p>
                </div>
                
                <div class="feature-item">
                    <span class="material-icons">people</span>
                    <h3>Gestión de Clientes</h3>
                    <p>Base de datos completa de clientes con historial de compras</p>
                </div>
                
                <div class="feature-item">
                    <span class="material-icons">analytics</span>
                    <h3>Reportes Avanzados</h3>
                    <p>Reportes detallados de ventas, inventario y rentabilidad</p>
                </div>
                
                <div class="feature-item">
                    <span class="material-icons">notifications</span>
                    <h3>Sistema de Alertas</h3>
                    <p>Notificaciones automáticas para stock bajo y vencimientos</p>
                </div>
                
                <div class="feature-item">
                    <span class="material-icons">security</span>
                    <h3>Seguridad Total</h3>
                    <p>Acceso seguro con roles y permisos personalizables</p>
                </div>
            </div>
        </div>
</div>

    <style>
        .login-body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        .login-container {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 0;
        }

        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .floating-shapes {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            top: 10%;
            right: 30%;
            animation-delay: 1s;
        }

        .shape-5 {
            width: 70px;
            height: 70px;
            top: 40%;
            left: 60%;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 32px 32px 24px 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 370px;
            min-width: 300px;
            position: relative;
            z-index: 2;
            animation: slideInUp 0.8s ease;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .logo-icon {
            font-size: 2.5rem;
            color: #6c63ff;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .logo-text {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .login-subtitle {
            color: #7f8c8d;
            font-size: 1rem;
            margin: 0;
        }

        .login-error {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
            color: white;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.5s ease;
            font-size: 1rem;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .login-form {
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: #6c63ff;
            z-index: 2;
        }

        .input-container input {
            width: 100%;
            padding: 12px 12px 12px 44px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input-container input:focus {
            outline: none;
            border-color: #6c63ff;
            background: white;
            box-shadow: 0 0 0 2px rgba(108, 99, 255, 0.08);
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #7f8c8d;
            cursor: pointer;
            padding: 0;
            z-index: 2;
        }

        .password-toggle:hover {
            color: #6c63ff;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .checkbox-container input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 16px;
            height: 16px;
            border: 2px solid #e9ecef;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
        }

        .checkbox-container input[type="checkbox"]:checked + .checkmark {
            background: #6c63ff;
            border-color: #6c63ff;
        }

        .checkbox-container input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 11px;
            font-weight: bold;
        }

        .forgot-password {
            color: #6c63ff;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #5a52d5;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6c63ff, #7b8cff);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(108, 99, 255, 0.18);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            border-top: 1px solid #e9ecef;
            padding-top: 14px;
        }

        .login-footer p {
            margin: 0 0 12px 0;
            color: #7f8c8d;
        }

        .login-footer a {
            color: #6c63ff;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .social-login {
            margin-top: 10px;
        }

        .social-login p {
            margin: 0 0 10px 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .social-buttons {
            display: flex;
            gap: 8px;
        }

        .social-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }

        .social-btn.google:hover {
            border-color: #db4437;
            color: #db4437;
        }

        .social-btn.facebook:hover {
            border-color: #4267B2;
            color: #4267B2;
        }

        .features-section {
            position: absolute;
            right: 0;
            top: 0;
            width: 50%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 1;
        }

        .features-section h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.7rem;
            font-weight: 700;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .feature-item {
            text-align: center;
            color: white;
            padding: 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.22);
            transform: translateY(-4px);
        }

        .feature-item .material-icons {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #00eaff;
        }

        .feature-item h3 {
            margin: 0 0 6px 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .feature-item p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.9;
            line-height: 1.3;
        }

        @media (max-width: 1200px) {
            .features-section {
                display: none;
            }
            .login-container {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 20px 10px;
                margin: 10px;
                min-width: unset;
                max-width: 98vw;
            }
            .logo-text {
                font-size: 1.3rem;
            }
            .form-options {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            .social-buttons {
                flex-direction: column;
            }
        }
    </style>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle .material-icons');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'visibility';
            }
        }

        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('usuario').focus();
        });

        // Form validation
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const usuario = document.getElementById('usuario').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!usuario || !password) {
                e.preventDefault();
                showToast('Por favor, completa todos los campos', 'warning');
            }
        });

        // Social login buttons (placeholder)
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                showToast('Función en desarrollo', 'info');
            });
        });
    </script>
</body>
</html>



