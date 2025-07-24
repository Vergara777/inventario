            </div> <!-- Content Wrapper -->
        </main> <!-- Main Content -->
    </div> <!-- Layout -->

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-info">
                <span class="material-icons">local_pharmacy</span>
                <div>
                    <h4>FarmaSys</h4>
                    <p>Sistema de Inventario Farmacéutico</p>
                </div>
            </div>
            
            <div class="footer-links">
                <a href="dashboard.php" class="footer-link">
                    <span class="material-icons">dashboard</span>
                    Dashboard
                </a>
                <a href="reportes.php" class="footer-link">
                    <span class="material-icons">analytics</span>
                    Reportes
                </a>
                <a href="configuracion.php" class="footer-link">
                    <span class="material-icons">settings</span>
                    Configuración
                </a>
            </div>
            
            <div class="footer-copyright">
                <p>&copy; <?php echo date('Y'); ?> FarmaSys. Todos los derechos reservados.</p>
                <p>Desarrollado con ❤️ para farmacias</p>
            </div>
        </div>
</footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" title="Volver arriba">
        <span class="material-icons">keyboard_arrow_up</span>
    </button>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Cargando...</p>
        </div>
    </div>

    <!-- JavaScript -->
<script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target) && 
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });

        // Back to top button
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Loading overlay
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Show loading on form submissions
        document.addEventListener('submit', function(e) {
            if (e.target.tagName === 'FORM') {
                loadingOverlay.style.display = 'flex';
            }
        });
        
        // Show loading on link clicks (except for specific links)
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' && 
                !e.target.href.includes('#') && 
                !e.target.href.includes('javascript:') &&
                !e.target.classList.contains('btn-outline')) {
                loadingOverlay.style.display = 'flex';
            }
        });

        // Hide loading when page is fully loaded
        window.addEventListener('load', function() {
            loadingOverlay.style.display = 'none';
        });

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);

        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || 
                e.target.closest('.btn-delete')) {
                if (!confirm('¿Estás seguro de que quieres realizar esta acción?')) {
                    e.preventDefault();
                }
            }
        });

        // Tooltip functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tooltips = document.querySelectorAll('[data-tooltip]');
            
            tooltips.forEach(function(element) {
                element.addEventListener('mouseenter', function() {
                    const tooltip = this.getAttribute('data-tooltip');
                    const tooltipElement = document.createElement('div');
                    tooltipElement.className = 'tooltip-popup';
                    tooltipElement.textContent = tooltip;
                    document.body.appendChild(tooltipElement);
                    
                    const rect = this.getBoundingClientRect();
                    tooltipElement.style.left = rect.left + (rect.width / 2) - (tooltipElement.offsetWidth / 2) + 'px';
                    tooltipElement.style.top = rect.top - tooltipElement.offsetHeight - 10 + 'px';
                });
                
                element.addEventListener('mouseleave', function() {
                    const tooltipPopup = document.querySelector('.tooltip-popup');
                    if (tooltipPopup) {
                        tooltipPopup.remove();
                    }
                });
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Form validation enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(function(field) {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('error');
                            
                            // Show error message
                            let errorMsg = field.parentNode.querySelector('.error-message');
                            if (!errorMsg) {
                                errorMsg = document.createElement('div');
                                errorMsg.className = 'error-message';
                                field.parentNode.appendChild(errorMsg);
                            }
                            errorMsg.textContent = 'Este campo es requerido';
                        } else {
                            field.classList.remove('error');
                            const errorMsg = field.parentNode.querySelector('.error-message');
                            if (errorMsg) {
                                errorMsg.remove();
                            }
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        // Show general error message
                        const generalError = document.createElement('div');
                        generalError.className = 'message error';
                        generalError.innerHTML = '<span class="material-icons">error</span>Por favor, completa todos los campos requeridos';
                        form.insertBefore(generalError, form.firstChild);
                    }
                });
            });
        });

        // Auto-save form data
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[data-autosave]');
            
            forms.forEach(function(form) {
                const formId = form.getAttribute('data-autosave');
                
                // Load saved data
                const savedData = localStorage.getItem('form_' + formId);
                if (savedData) {
                    const data = JSON.parse(savedData);
                    Object.keys(data).forEach(function(key) {
                        const field = form.querySelector('[name="' + key + '"]');
                        if (field) {
                            field.value = data[key];
                        }
                    });
                }
                
                // Save data on input
                form.addEventListener('input', function(e) {
                    const formData = new FormData(form);
                    const data = {};
                    for (let [key, value] of formData.entries()) {
                        data[key] = value;
                    }
                    localStorage.setItem('form_' + formId, JSON.stringify(data));
                });
                
                // Clear saved data on successful submission
                form.addEventListener('submit', function() {
                    localStorage.removeItem('form_' + formId);
                });
    });
});
</script>

    <style>
        .main-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 30px;
            align-items: center;
        }

        .footer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .footer-info .material-icons {
            font-size: 2rem;
            color: #00eaff;
        }

        .footer-info h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .footer-info p {
            margin: 5px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .footer-links {
            display: flex;
            gap: 20px;
        }

        .footer-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .footer-link .material-icons {
            font-size: 1.1rem;
        }

        .footer-copyright {
            text-align: right;
        }

        .footer-copyright p {
            margin: 5px 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6c63ff, #7b8cff);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 20px rgba(108,99,255,0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108,99,255,0.6);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            text-align: center;
            color: white;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid #6c63ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .tooltip-popup {
            position: fixed;
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            z-index: 10000;
            pointer-events: none;
            animation: fadeIn 0.3s ease;
        }

        .form-group input.error,
        .form-group select.error,
        .form-group textarea.error {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231,76,60,0.1);
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 20px;
            }

            .footer-links {
                justify-content: center;
            }

            .footer-copyright {
                text-align: center;
            }

            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }
    </style>
</body>
</html>

