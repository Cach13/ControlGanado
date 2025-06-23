<?php
require_once 'bd.php';

$arete = $_GET['arete'] ?? '';
if ($arete == '') {
    die("Arete no especificado.");
}

$sql = "SELECT * FROM crias_activas WHERE arete = '$arete'";
$res = $conn->query($sql);
$cria = $res->fetch_assoc();

if (!$cria) {
    die("Cría no encontrada.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
            border-radius: 10px;
            text-align: center;
        }

        .dashboard-header h1, .dashboard-header h2 {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: 0.5rem;
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
            background: white;
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

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-1px);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .btn {
            border-radius: 25px;
            padding: 0.75rem 2rem;
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

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .btn-secondary {
            background: var(--secondary-gradient);
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .info-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--info-color);
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #1565c0;
        }

        .info-value {
            font-weight: bold;
            color: #0d47a1;
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
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
                width: 100%;
            }

            .btn-group-mobile {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .info-section {
                padding: 1rem;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }

        @media (max-width: 576px) {
            .card-header {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .form-control {
                font-size: 0.9rem;
                padding: 0.6rem 0.8rem;
            }

            .btn {
                font-size: 0.85rem;
                padding: 0.5rem 1rem;
            }
        }

        /* Animation for form submission */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading .btn {
            position: relative;
        }

        .loading .btn::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
    </style>
    <script>
        function confirmarEnvio() {
            const confirmed = confirm("¿Estás seguro de registrar esta venta? Esta acción eliminará la cría de las crías activas.");
            if (confirmed) {
                // Add loading state
                document.body.classList.add('loading');
                // Disable form elements
                const form = document.querySelector('form');
                const inputs = form.querySelectorAll('input, button');
                inputs.forEach(input => input.disabled = true);
            }
            return confirmed;
        }

        // Auto-calculate profit when values change
        function calculateProfit() {
            const kgVenta = parseFloat(document.querySelector('input[name="kg_venta"]').value) || 0;
            const totalVenta = parseFloat(document.querySelector('input[name="venta"]').value) || 0;
            const gastoTraslado = parseFloat(document.querySelector('input[name="gasto_traslado"]').value) || 0;
            const costoKg = parseFloat(document.querySelector('input[name="costo_kg"]').value) || 0;
            const seguro = parseFloat(document.querySelector('input[name="seguro"]').value) || 0;
            
            // Calculate kg gained (assuming initial weight from purchase)
            const kgGanados = kgVenta - <?= $cria['kg_compra'] ?>;
            const costoTotal = <?= $cria['total_compra'] ?> + (kgGanados * costoKg) + gastoTraslado + seguro;
            const ganancia = totalVenta - costoTotal;
            
            // Update profit display if it exists
            const profitDisplay = document.getElementById('profit-preview');
            if (profitDisplay) {
                profitDisplay.textContent = `$${ganancia.toFixed(2)}`;
                profitDisplay.className = ganancia >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for auto-calculation
            const inputs = document.querySelectorAll('input[type="number"]');
            inputs.forEach(input => {
                input.addEventListener('input', calculateProfit);
            });
            
            // Initial calculation
            calculateProfit();
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-header">
            <h2>📤 Registrar Venta de Cría</h2>
            <p>Arete: <strong><?= htmlspecialchars($cria['arete']) ?></strong></p>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📋 Información de la Cría</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-section">
                            <div class="info-item">
                                <span class="info-label">Arete:</span>
                                <span class="info-value"><?= htmlspecialchars($cria['arete']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Sexo:</span>
                                <span class="info-value"><?= htmlspecialchars($cria['sexo']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Kg Compra:</span>
                                <span class="info-value"><?= number_format($cria['kg_compra'], 2) ?> kg</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Precio Compra:</span>
                                <span class="info-value">$<?= number_format($cria['precio_compra'], 2) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Total Compra:</span>
                                <span class="info-value">$<?= number_format($cria['total_compra'], 2) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha Compra:</span>
                                <span class="info-value"><?= date('d/m/Y', strtotime($cria['fecha_compra'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">💰 Datos de Venta</h5>
                    </div>
                    <div class="card-body">
                        <form action="procesar_venta.php" method="POST" onsubmit="return confirmarEnvio();">
                            <input type="hidden" name="arete" value="<?= htmlspecialchars($cria['arete']) ?>">
                            <input type="hidden" name="sexo" value="<?= htmlspecialchars($cria['sexo']) ?>">
                            <input type="hidden" name="kg_compra" value="<?= $cria['kg_compra'] ?>">
                            <input type="hidden" name="precio_compra" value="<?= $cria['precio_compra'] ?>">
                            <input type="hidden" name="total_compra" value="<?= $cria['total_compra'] ?>">
                            <input type="hidden" name="fecha_compra" value="<?= $cria['fecha_compra'] ?>">

                            <div class="mb-3">
                                <label for="kg_venta" class="form-label">🏋️ Kg Venta:</label>
                                <input type="number" name="kg_venta" id="kg_venta" step="0.01" class="form-control" required 
                                       placeholder="Ingresa el peso de venta">
                            </div>

                            <div class="mb-3">
                                <label for="venta" class="form-label">💵 Total Venta ($):</label>
                                <input type="number" name="venta" id="venta" step="0.01" class="form-control" required 
                                       placeholder="Ingresa el total de venta">
                            </div>

                            <div class="mb-3">
                                <label for="gasto_traslado" class="form-label">🚛 Gasto de Traslado ($):</label>
                                <input type="number" name="gasto_traslado" id="gasto_traslado" step="0.01" class="form-control" required 
                                       placeholder="Costo de transporte">
                            </div>

                            <div class="mb-3">
                                <label for="costo_kg" class="form-label">📈 Costo por Kg ganado ($):</label>
                                <input type="number" name="costo_kg" id="costo_kg" step="0.01" class="form-control" value="40.00" required>
                            </div>

                            <div class="mb-3">
                                <label for="seguro" class="form-label">🛡️ Seguro ($):</label>
                                <input type="number" name="seguro" id="seguro" step="0.01" class="form-control" value="650.00" required>
                            </div>

                            <div class="alert alert-info">
                                <strong>💡 Ganancia Estimada:</strong> 
                                <span id="profit-preview">$0.00</span>
                            </div>

                            <div class="btn-group-mobile">
                                <button type="submit" class="btn btn-success">
                                    ✅ Confirmar Venta
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    ⬅️ Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>