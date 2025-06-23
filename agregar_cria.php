<?php
require_once 'bd.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Nueva Cría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h2>🐮 Agregar Nueva Cría</h2>
        <a href="index.php" class="btn btn-secondary mb-3">⬅️ Volver al inicio</a>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📋 Datos de la Cría</h5>
                    </div>
                    <div class="card-body">
                        <form action="procesar_agregar.php" method="POST">
                            <div class="mb-3">
                                <label for="arete" class="form-label">Número de Arete <span class="text-danger">*</span></label>
                                <input type="text" name="arete" id="arete" class="form-control" required maxlength="20" placeholder="Ej: A001, B025, etc.">
                                <div class="form-text">El arete debe ser único para cada animal</div>
                            </div>

                            <div class="mb-3">
                                <label for="sexo" class="form-label">Sexo <span class="text-danger">*</span></label>
                                <select name="sexo" id="sexo" class="form-select" required>
                                    <option value="">Seleccionar sexo</option>
                                    <option value="M">🐂 Macho</option>
                                    <option value="H">🐄 Hembra</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="kg_compra" class="form-label">Peso de Compra (kg) <span class="text-danger">*</span></label>
                                <input type="number" name="kg_compra" id="kg_compra" class="form-control" step="0.01" min="0" required placeholder="Ej: 85.50">
                            </div>

                            <div class="mb-3">
                                <label for="precio_compra" class="form-label">Precio por Kg ($) <span class="text-danger">*</span></label>
                                <input type="number" name="precio_compra" id="precio_compra" class="form-control" step="0.01" min="0" required placeholder="Ej: 45.00">
                            </div>

                            <div class="mb-3">
                                <label for="fecha_compra" class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_compra" id="fecha_compra" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">💰 Total de Compra</h6>
                                        <p class="card-text fs-4 fw-bold text-success" id="total_compra">$0.00</p>
                                        <small class="text-muted">Se calcula automáticamente: Peso × Precio por Kg</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
                                <button type="submit" class="btn btn-success">✅ Agregar Cría</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">💡 Consejos</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">🏷️ <strong>Arete:</strong> Usa un sistema consistente de numeración</li>
                            <li class="mb-2">⚖️ <strong>Peso:</strong> Registra el peso exacto al momento de la compra</li>
                            <li class="mb-2">💵 <strong>Precio:</strong> Incluye todos los costos de adquisición</li>
                            <li class="mb-2">📅 <strong>Fecha:</strong> Registra la fecha real de compra</li>
                        </ul>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">📊 Estadísticas Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        // Obtener estadísticas básicas
                        $stats = $conn->query("
                            SELECT 
                                COUNT(*) as total_crias,
                                AVG(kg_compra) as peso_promedio,
                                AVG(precio_compra) as precio_promedio
                            FROM crias_activas
                        ")->fetch_assoc();
                        ?>
                        <p><strong>Total crías:</strong> <?= $stats['total_crias'] ?? 0 ?></p>
                        <p><strong>Peso promedio:</strong> <?= number_format($stats['peso_promedio'] ?? 0, 2) ?> kg</p>
                        <p><strong>Precio promedio:</strong> $<?= number_format($stats['precio_promedio'] ?? 0, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        // Agregar eventos para cálculo automático
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
            if (!confirm(`¿Confirmar el registro de la cría?\n\nArete: ${arete}\nPeso: ${kg} kg\nPrecio: $${precio}\nTotal: $${total.toFixed(2)}`)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>