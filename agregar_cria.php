<?php
require_once 'bd.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nueva Cría - Control de Ganado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <!-- Header similar al index -->
        <h1 class="mb-4">🐮 Agregar Nueva Cría</h1>

        <!-- Botones de navegación responsive -->
        <div class="mb-4">
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
                        <form action="procesar_agregar.php" method="POST">
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
                                    <input type="date" name="fecha_compra" id="fecha_compra" class="form-control" required value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Total de compra - destacado -->
                            <div class="mt-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">💰 Total de Compra</h6>
                                        <p class="card-text display-6 fw-bold text-success mb-0" id="total_compra">$0.00</p>
                                        <small class="text-muted">Se calcula automáticamente: Peso × Precio por Kg</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones responsive -->
                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
                                <button type="submit" class="btn btn-success">✅ Agregar Cría</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            

                <!-- Consejos - colapsible en móvil -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <button class="btn btn-link p-0 text-decoration-none fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#consejos" aria-expanded="true">
                                💡 Consejos y Recomendaciones
                            </button>
                        </h6>
                    </div>
                    <div class="collapse show" id="consejos">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="me-2">🏷️</div>
                                <div>
                                    <strong>Sistema de Arete:</strong><br>
                                    <small class="text-muted">Usa códigos consistentes (A001, B025, etc.)</small>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-3">
                                <div class="me-2">⚖️</div>
                                <div>
                                    <strong>Peso Exacto:</strong><br>
                                    <small class="text-muted">Registra el peso real al momento de compra</small>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-3">
                                <div class="me-2">💵</div>
                                <div>
                                    <strong>Precio Total:</strong><br>
                                    <small class="text-muted">Incluye todos los costos de adquisición</small>
                                </div>
                            </div>
                            
                            <div class="d-flex">
                                <div class="me-2">📅</div>
                                <div>
                                    <strong>Fecha Precisa:</strong><br>
                                    <small class="text-muted">Importante para el control temporal</small>
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
        // Calcular total automáticamente
        function calcularTotal() {
            const kg = parseFloat(document.getElementById('kg_compra').value) || 0;
            const precio = parseFloat(document.getElementById('precio_compra').value) || 0;
            const total = kg * precio;
            
            document.getElementById('total_compra').textContent = '$' + total.toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Eventos para cálculo automático
        document.getElementById('kg_compra').addEventListener('input', calcularTotal);
        document.getElementById('precio_compra').addEventListener('input', calcularTotal);

        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const arete = document.getElementById('arete').value.trim();
            const kg = parseFloat(document.getElementById('kg_compra').value);
            const precio = parseFloat(document.getElementById('precio_compra').value);

            if (!arete) {
                alert('❌ El número de arete es obligatorio');
                e.preventDefault();
                return;
            }

            if (kg <= 0) {
                alert('❌ El peso debe ser mayor a 0');
                e.preventDefault();
                return;
            }

            if (precio <= 0) {
                alert('❌ El precio debe ser mayor a 0');
                e.preventDefault();
                return;
            }

            // Confirmación final
            const total = kg * precio;
            const mensaje = `¿Confirmar el registro de la cría?

📋 Resumen:
• Arete: ${arete}
• Peso: ${kg} kg
• Precio: $${precio.toFixed(2)}
• Total: $${total.toFixed(2)}`;

            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });

        // Auto-foco en el primer campo
        document.getElementById('arete').focus();
    </script>
</body>
</html>