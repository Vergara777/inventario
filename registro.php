<?php
session_start();
require_once 'includes/conexion.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($nombre) || empty($usuario) || empty($email) || empty($password)) {
        $mensaje = 'Todos los campos son requeridos';
        $tipo_mensaje = 'error';
    } elseif ($password !== $confirm_password) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'error';
    } elseif (strlen($password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipo_mensaje = 'error';
    } else {
        // Verificar si el usuario ya existe
        $check_query = "SELECT id FROM usuarios WHERE usuario = ? OR email = ?";
        $check_stmt = mysqli_prepare($conexion, $check_query);
        mysqli_stmt_bind_param($check_stmt, "ss", $usuario, $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $mensaje = 'El usuario o email ya existe';
            $tipo_mensaje = 'error';
        } else {
            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $insert_query = "INSERT INTO usuarios (nombre, usuario, email, password, rol, estado, fecha_registro) VALUES (?, ?, ?, ?, 'usuario', 'activo', NOW())";
            $insert_stmt = mysqli_prepare($conexion, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "ssss", $nombre, $usuario, $email, $password_hash);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $mensaje = 'Usuario registrado exitosamente. Ya puedes iniciar sesión.';
                $tipo_mensaje = 'success';
                
                // Limpiar formulario
                $_POST = array();
            } else {
                $mensaje = 'Error al registrar el usuario';
                $tipo_mensaje = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmaSys - Registro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script src="assets/js/funciones.js"></script>
</head>
<body class="register-body">
    <div class="register-container">
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
        
        <!-- Register Form -->
        <div class="register-card">
            <div class="register-header">
                <div class="logo-container">
                    <span class="material-icons logo-icon">local_pharmacy</span>
                    <h1 class="logo-text">FarmaSys</h1>
                </div>
                <p class="register-subtitle">Crear Nueva Cuenta</p>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="register-message <?php echo $tipo_mensaje; ?>">
                    <span class="material-icons">
                        <?php echo $tipo_mensaje == 'success' ? 'check_circle' : 'error'; ?>
                    </span>
                    <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

            <form method="POST" class="register-form" data-validate="true">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <div class="input-container">
                            <span class="material-icons input-icon">person</span>
                            <input type="text" id="nombre" name="nombre" required 
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                                   placeholder="Ingresa tu nombre completo">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario">Nombre de Usuario *</label>
                        <div class="input-container">
                            <span class="material-icons input-icon">account_circle</span>
                            <input type="text" id="usuario" name="usuario" required 
                                   value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                                   placeholder="Elige un nombre de usuario">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <div class="input-container">
                            <span class="material-icons input-icon">email</span>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="tu@email.com">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <div class="input-container">
                            <span class="material-icons input-icon">lock</span>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Mínimo 6 caracteres">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <span class="material-icons">visibility</span>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña *</label>
                        <div class="input-container">
                            <span class="material-icons input-icon">lock_outline</span>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   placeholder="Repite tu contraseña">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <span class="material-icons">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" id="terms" name="terms" required>
                        <span class="checkmark"></span>
                        Acepto los <a href="#" onclick="showTerms()">términos y condiciones</a>
                    </label>
                    
                    <label class="checkbox-container">
                        <input type="checkbox" id="newsletter" name="newsletter">
                        <span class="checkmark"></span>
                        Recibir notificaciones por email
                    </label>
                </div>
                
                <button type="submit" class="register-btn">
                    <span class="material-icons">person_add</span>
                    Crear Cuenta
                </button>
            </form>
            
            <div class="register-footer">
                <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión aquí</a></p>
            </div>
        </div>
        
        <!-- Benefits Section -->
        <div class="benefits-section">
            <h2>¿Por qué elegir FarmaSys?</h2>
            <div class="benefits-grid">
                <div class="benefit-item">
                    <span class="material-icons">speed</span>
                    <h3>Fácil de Usar</h3>
                    <p>Interfaz intuitiva diseñada para farmacias</p>
                </div>
                
                <div class="benefit-item">
                    <span class="material-icons">cloud_done</span>
                    <h3>Acceso Seguro</h3>
                    <p>Datos protegidos con encriptación avanzada</p>
                </div>
                
                <div class="benefit-item">
                    <span class="material-icons">support_agent</span>
                    <h3>Soporte 24/7</h3>
                    <p>Asistencia técnica disponible siempre</p>
                </div>
                
                <div class="benefit-item">
                    <span class="material-icons">trending_up</span>
                    <h3>Mejora Continua</h3>
                    <p>Actualizaciones regulares con nuevas funciones</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .register-body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        .register-container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
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

        .register-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 2;
            animation: slideInUp 0.8s ease;
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

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .logo-icon {
            font-size: 3rem;
            color: #6c63ff;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .logo-text {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .register-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin: 0;
        }

        .register-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }

        .register-message.success {
            background: linear-gradient(90deg, #00b894, #00a085);
            color: white;
        }

        .register-message.error {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
            color: white;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-form {
            margin-bottom: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
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
            padding: 15px 15px 15px 50px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input-container input:focus {
            outline: none;
            border-color: #6c63ff;
            background: white;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
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

        .password-strength {
            margin-top: 8px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .password-strength.weak {
            color: #e74c3c;
        }

        .password-strength.medium {
            color: #f39c12;
        }

        .password-strength.strong {
            color: #27ae60;
        }

        .form-options {
            margin-bottom: 25px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .checkbox-container input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 18px;
            height: 18px;
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
            font-size: 12px;
            font-weight: bold;
        }

        .checkbox-container a {
            color: #6c63ff;
            text-decoration: none;
        }

        .checkbox-container a:hover {
            text-decoration: underline;
        }

        .register-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #6c63ff, #7b8cff);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(108, 99, 255, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .register-footer {
            text-align: center;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }

        .register-footer p {
            margin: 0;
            color: #7f8c8d;
        }

        .register-footer a {
            color: #6c63ff;
            text-decoration: none;
            font-weight: 600;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .benefits-section {
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

        .benefits-section h2 {
            color: white;
            text-align: center;
            margin-bottom: 40px;
            font-size: 2rem;
            font-weight: 700;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .benefit-item {
            text-align: center;
            color: white;
            padding: 20px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .benefit-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .benefit-item .material-icons {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #00eaff;
        }

        .benefit-item h3 {
            margin: 0 0 10px 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .benefit-item p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.4;
        }

        @media (max-width: 1200px) {
            .benefits-section {
                display: none;
            }
            
            .register-container {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .register-card {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .logo-text {
                font-size: 2rem;
            }
            
            .form-options {
                margin-bottom: 20px;
            }
        }
    </style>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.parentNode.querySelector('.password-toggle .material-icons');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'visibility';
            }
        }

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let message = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    message = 'Muy débil';
                    strengthDiv.className = 'password-strength weak';
                    break;
                case 2:
                case 3:
                    message = 'Débil';
                    strengthDiv.className = 'password-strength weak';
                    break;
                case 4:
                    message = 'Media';
                    strengthDiv.className = 'password-strength medium';
                    break;
                case 5:
                    message = 'Fuerte';
                    strengthDiv.className = 'password-strength strong';
                    break;
            }
            
            strengthDiv.innerHTML = `<span class="material-icons">${strength >= 4 ? 'check_circle' : 'warning'}</span> ${message}`;
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // Show terms modal
        function showTerms() {
            showToast('Términos y condiciones en desarrollo', 'info');
        }

        // Auto-focus on first field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nombre').focus();
        });
    </script>
</body>
</html>

