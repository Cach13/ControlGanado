<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nueva Cría - Control de Ganado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .stat-card {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .stat-number {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: clamp(0.85rem, 2vw, 1rem);
            opacity: 0.9;
        }

        .dashboard-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 2rem 1rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .dashboard-header h1 {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .dashboard-header p {
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            opacity: 0.9;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: var(--secondary-gradient);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: var(--primary-gradient);
        }

        .btn-secondary {
            background: var(--secondary-gradient);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .total-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        .total-card:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
            border-radius: 15px;
        }

        .consejos-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            overflow: hidden;
        }

        .consejo-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .consejo-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .consejo-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            min-width: 2rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0.5rem;
            }

            .dashboard-header {
                padding: 1.5rem 1rem;
                margin-bottom: 1.5rem;
            }

            .card-body {
                padding: 1rem;
            }

            .btn {
                padding: 0.4rem 1rem;
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
                width: 100%;
            }

            .btn-group-mobile {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .consejo-item {
                padding: 0.75rem;
            }

            .consejo-icon {
                font-size: 1.2rem;
                margin-right: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .card-header {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        .card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .card:nth-child(3) {
            animation-delay: 0.2s;
        }

        /* Efectos de enfoque */
        .form-control:focus {
            transform: scale(1.02);
        }

        /* Gradientes personalizados para estados */
        .text-gradient {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header moderno -->
        <div class="dashboard-header">
            <h1>🐮 Agregar Nueva Cría</h1>
            <p>Sistema de Control de Ganado - Registro de Animales</p>
        </div>

        <!-- Botones de navegación responsive -->
        <div class="mb-4 d-flex flex-wrap gap-2">
            <a href="index.php" class="btn btn-secondary">⬅️ Volver al Inicio</a>
            <a href="dashboard.php" class="btn btn-primary">📊 Dashboard</a>
            <a href="historial.php" class="btn btn-info">📜 Historial</a>
        </div>

        <div class="row g-4">
            <!-- Formulario principal - responsive -->
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📋 Datos de la Cría</h5>
                    </div>
                    <div class="card-body">
                        <form id="criaForm">
                            <!-- Grid responsive para campos -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="arete" class="form-label">Número de Arete <span class="text-danger">*</span></label>
                                    <input type="text" name="arete" id="arete" class="form-control" required maxlength="20" placeholder="Ej: A001, B025">
                                    <div class="form-text">El arete debe ser único para cada animal</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="sexo" class="form-label">Sexo <span class="text-danger">*</span></label>
                                    <select name="sexo" id="sexo" class="form-select" required>
                                        <option value="">Seleccionar sexo</option>
                                        <option value="M">🐂 Macho</option>
                                        <option value="H">🐄 Hembra</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="kg_compra" class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                                    <input type="number" name="kg_compra" id="kg_compra" class="form-control" step="0.01" min="0" required placeholder="85.50">
                                </div>

                                <div class="col-md-4">
                                    <label for="precio_compra" class="form-label">Precio/Kg ($) <span class="text-danger">*</span></label>
                                    <input type="number" name="precio_compra" id="precio_compra" class="form-control" step="0.01" min="0" required placeholder="45.00">
                                </div>

                                <div class="col-md-4">
                                    <label for="fecha_compra" class="form-label">Fecha Compra <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_compra" id="fecha_compra" class="form-control" required>
                                </div>
                            </div>

                            <!-- Total de compra - destacado -->
                            <div class="mt-4">
                                <div class="total-card">
                                    <h6 class="mb-2">💰 Total de Compra</h6>
                                    <div class="display-6 fw-bold mb-2" id="total_compra">$0.00</div>
                                    <small class="opacity-75">Se calcula automáticamente: Peso × Precio por Kg</small>
                                </div>
                            </div>

                            <!-- Botones responsive -->
                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary" onclick="cancelar()">❌ Cancelar</button>
                                <button type="submit" class="btn btn-success">✅ Agregar Cría</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel lateral con consejos -->
            <div class="col-lg-4 col-md-12">
                <!-- Consejos mejorados -->
                <div class="card consejos-card">
                    <div class="card-header">
                        <h6 class="mb-0">💡 Consejos y Recomendaciones</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="consejo-item">
                            <div class="consejo-icon">🏷️</div>
                            <div>
                                <strong>Sistema de Arete</strong><br>
                                <small class="text-muted">Usa códigos consistentes (A001, B025, etc.)</small>
                            </div>
                        </div>
                        
                        <div class="consejo-item">
                            <div class="consejo-icon">⚖️</div>
                            <div>
                                <strong>Peso Exacto</strong><br>
                                <small class="text-muted">Registra el peso real al momento de compra</small>
                            </div>
                        </div>
                        
                        <div class="consejo-item">
                            <div class="consejo-icon">💵</div>
                            <div>
                                <strong>Precio Total</strong><br>
                                <small class="text-muted">Incluye todos los costos de adquisición</small>
                            </div>
                        </div>
                        
                        <div class="consejo-item">
                            <div class="consejo-icon">📅</div>
                            <div>
                                <strong>Fecha Precisa</strong><br>
                                <small class="text-muted">Importante para el control temporal</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen estadístico -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">📊 Resumen Rápido</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="h5 mb-1 text-gradient">125</div>
                                    <small class="text-muted">Total Animales</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="h5 mb-1 text-gradient">$45.50</div>
                                    <small class="text-muted">Precio Promedio</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Establecer fecha actual por defecto
        document.getElementById('fecha_compra').value = new Date().toISOString().split('T')[0];

        // Calcular total automáticamente
        function calcularTotal() {
            const kg = parseFloat(document.getElementById('kg_compra').value) || 0;
            const precio = parseFloat(document.getElementById('precio_compra').value) || 0;
            const total = kg * precio;
            
            document.getElementById('total_compra').textContent = '$' + total.toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Efecto visual cuando cambia el total
            const totalElement = document.getElementById('total_compra');
            totalElement.style.transform = 'scale(1.1)';
            setTimeout(() => {
                totalElement.style.transform = 'scale(1)';
            }, 200);
        }

        // Eventos para cálculo automático
        document.getElementById('kg_compra').addEventListener('input', calcularTotal);
        document.getElementById('precio_compra').addEventListener('input', calcularTotal);

        // Validación del formulario
        document.getElementById('criaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const arete = document.getElementById('arete').value.trim();
            const sexo = document.getElementById('sexo').value;
            const kg = parseFloat(document.getElementById('kg_compra').value);
            const precio = parseFloat(document.getElementById('precio_compra').value);
            const fecha = document.getElementById('fecha_compra').value;

            // Validaciones
            if (!arete) {
                mostrarAlerta('❌ El número de arete es obligatorio', 'danger');
                return;
            }

            if (!sexo) {
                mostrarAlerta('❌ Debes seleccionar el sexo del animal', 'danger');
                return;
            }

            if (kg <= 0) {
                mostrarAlerta('❌ El peso debe ser mayor a 0', 'danger');
                return;
            }

            if (precio <= 0) {
                mostrarAlerta('❌ El precio debe ser mayor a 0', 'danger');
                return;
            }

            if (!fecha) {
                mostrarAlerta('❌ La fecha de compra es obligatoria', 'danger');
                return;
            }

            // Confirmación final
            const total = kg * precio;
            const sexoTexto = sexo === 'M' ? '🐂 Macho' : '🐄 Hembra';
            
            const mensaje = `¿Confirmar el registro de la cría?

📋 Resumen:
• Arete: ${arete}
• Sexo: ${sexoTexto}
• Peso: ${kg} kg
• Precio: $${precio.toFixed(2)}
• Fecha: ${fecha}
• Total: $${total.toFixed(2)}`;

            if (confirm(mensaje)) {
                // Simular envío exitoso
                mostrarAlerta('✅ Cría registrada exitosamente', 'success');
                
                // Limpiar formulario después de 2 segundos
                setTimeout(() => {
                    document.getElementById('criaForm').reset();
                    document.getElementById('fecha_compra').value = new Date().toISOString().split('T')[0];
                    calcularTotal();
                }, 2000);
            }
        });

        // Función para mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            const alertaExistente = document.querySelector('.alert');
            if (alertaExistente) {
                alertaExistente.remove();
            }

            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
            alerta.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.querySelector('.container').insertBefore(alerta, document.querySelector('.row'));

            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                if (alerta.parentNode) {
                    alerta.remove();
                }
            }, 5000);
        }

        // Función cancelar
        function cancelar() {
            if (confirm('¿Estás seguro de que quieres cancelar? Se perderán los datos ingresados.')) {
                document.getElementById('criaForm').reset();
                document.getElementById('fecha_compra').value = new Date().toISOString().split('T')[0];
                calcularTotal();
                mostrarAlerta('ℹ️ Formulario cancelado', 'info');
            }
        }

        // Auto-foco en el primer campo
        document.getElementById('arete').focus();

        // Validación en tiempo real del arete
        document.getElementById('arete').addEventListener('input', function() {
            const valor = this.value;
            const regex = /^[A-Za-z0-9]+$/;
            
            if (valor && !regex.test(valor)) {
                this.style.borderColor = '#dc3545';
                this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
            } else {
                this.style.borderColor = '#28a745';
                this.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
            }
        });

        // Efectos visuales adicionales
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.2s ease';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>