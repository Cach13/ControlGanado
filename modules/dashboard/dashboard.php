<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/bd.php';

// Obtener datos para el dashboard
$anioActual = date('Y');
$mesActual = date('m');

// 1. Estadísticas generales
$statsGenerales = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM crias_activas) as crias_activas,
        (SELECT COUNT(*) FROM ventas WHERE YEAR(fecha_venta) = $anioActual) as ventas_año,
        (SELECT SUM(total_ganancia) FROM ventas WHERE YEAR(fecha_venta) = $anioActual) as ganancia_año,
        (SELECT AVG(total_ganancia) FROM ventas WHERE YEAR(fecha_venta) = $anioActual) as ganancia_promedio
")->fetch_assoc();

// 2. Ganancias por mes (últimos 12 meses)
$gananciasQuery = "
    SELECT 
        DATE_FORMAT(fecha_venta, '%Y-%m') as mes,
        MONTHNAME(fecha_venta) as nombre_mes,
        SUM(total_ganancia) as ganancia,
        COUNT(*) as ventas
    FROM ventas 
    WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(fecha_venta, '%Y-%m')
    ORDER BY fecha_venta ASC
";
$gananciasResult = $conn->query($gananciasQuery);
$gananciasData = [];
while($row = $gananciasResult->fetch_assoc()) {
    $gananciasData[] = $row;
}

// 3. Distribución por sexo
$sexoQuery = "
    SELECT 
        'Activas' as tipo,
        sexo,
        COUNT(*) as cantidad
    FROM crias_activas 
    GROUP BY sexo
    UNION ALL
    SELECT 
        'Vendidas' as tipo,
        sexo,
        COUNT(*) as cantidad
    FROM ventas 
    WHERE YEAR(fecha_venta) = $anioActual
    GROUP BY sexo
";
$sexoResult = $conn->query($sexoQuery);
$sexoData = [];
while($row = $sexoResult->fetch_assoc()) {
    $sexoData[] = $row;
}

// 4. Top 10 mejores ventas
$topVentasQuery = "
    SELECT arete, total_ganancia, fecha_venta, kg_venta, venta
    FROM ventas 
    ORDER BY total_ganancia DESC 
    LIMIT 10
";
$topVentasResult = $conn->query($topVentasQuery);
$topVentas = [];
while($row = $topVentasResult->fetch_assoc()) {
    $topVentas[] = $row;
}

// 5. Análisis de peso (compra vs venta)
$pesoQuery = "
    SELECT 
        arete,
        kg_compra,
        kg_venta,
        diferencia_kg,
        DATE_FORMAT(fecha_venta, '%Y-%m') as mes_venta
    FROM ventas 
    WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    ORDER BY fecha_venta DESC
";
$pesoResult = $conn->query($pesoQuery);
$pesoData = [];
while($row = $pesoResult->fetch_assoc()) {
    $pesoData[] = $row;
}

// 6. Crías por rango de edad (días desde compra)
$edadQuery = "
    SELECT 
        arete,
        fecha_compra,
        DATEDIFF(CURDATE(), fecha_compra) as dias_en_inventario,
        kg_compra,
        total_compra,
        CASE 
            WHEN DATEDIFF(CURDATE(), fecha_compra) < 30 THEN 'Menos de 1 mes'
            WHEN DATEDIFF(CURDATE(), fecha_compra) < 90 THEN '1-3 meses'
            WHEN DATEDIFF(CURDATE(), fecha_compra) < 180 THEN '3-6 meses'
            WHEN DATEDIFF(CURDATE(), fecha_compra) < 365 THEN '6-12 meses'
            ELSE 'Más de 1 año'
        END as rango_edad
    FROM crias_activas
    ORDER BY dias_en_inventario DESC
";
$edadResult = $conn->query($edadQuery);
$edadData = [];
while($row = $edadResult->fetch_assoc()) {
    $edadData[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Ganado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg,rgb(40, 65, 177) 0%,rgb(50, 19, 80) 100%);
            --secondary-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        body {
            font-size: 14px;
            line-height: 1.5;
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

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1.5rem;
        }

        .dashboard-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 2rem 1rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            text-align: center;
        }

        .dashboard-header h1 {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            opacity: 0.9;
        }

        .nav-pills {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .nav-pills .nav-link {
            white-space: nowrap;
            margin-right: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background: var(--primary-gradient);
            transform: scale(1.05);
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
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
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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

        .list-group-item {
            border: none;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0.5rem;
            }

            .dashboard-header {
                padding: 1.5rem 1rem;
                margin-bottom: 1.5rem;
            }

            .stat-card {
                padding: 1rem;
                min-height: 100px;
            }

            .chart-container {
                height: 250px;
            }

            .nav-pills {
                margin-bottom: 1rem;
                padding: 0 0.5rem;
            }

            .nav-pills .nav-link {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table-responsive {
                font-size: 0.85rem;
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
        }

        @media (max-width: 576px) {
            .stat-number {
                font-size: 1.8rem;
            }

            .chart-container {
                height: 200px;
            }

            .table {
                font-size: 0.75rem;
            }

            .card-header {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Improve scrollbar appearance */
        .nav-pills::-webkit-scrollbar {
            height: 4px;
        }

        .nav-pills::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .nav-pills::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .nav-pills::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Loading animation for charts */
        .chart-loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* Custom mobile table styling */
        @media (max-width: 768px) {
            .mobile-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-table {
                min-width: 600px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>📊 Dashboard de Control de Ganado</h1>
            <p class="mb-0">Análisis completo de tu operación ganadera</p>
        </div>

        <!-- Navegación -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-group-mobile d-md-block">
                    <a href="/index.php" class="btn btn-secondary">⬅️ Volver al Inicio</a>
                    <a href="/modules/crias/agregar_cria.php" class="btn btn-success">➕ Agregar Cría</a>
                    <a href="modules/ventas/historial.php" class="btn btn-info">📜 Historial</a>
                    
                </div>
            </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="row mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= $statsGenerales['crias_activas'] ?? 0 ?></div>
                    <div class="stat-label">🐮 Crías Activas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= $statsGenerales['ventas_año'] ?? 0 ?></div>
                    <div class="stat-label">💰 Ventas <?= $anioActual ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number">$<?= number_format($statsGenerales['ganancia_año'] ?? 0, 0) ?></div>
                    <div class="stat-label">📈 Ganancia Anual</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number">$<?= number_format($statsGenerales['ganancia_promedio'] ?? 0, 0) ?></div>
                    <div class="stat-label">⭐ Ganancia Promedio</div>
                </div>
            </div>
        </div>

        <!-- Tabs para diferentes vistas -->
        <ul class="nav nav-pills mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ganancias-tab" data-bs-toggle="pill" data-bs-target="#ganancias" type="button">
                    📈 Ganancias
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventario-tab" data-bs-toggle="pill" data-bs-target="#inventario" type="button">
                    🐄 Inventario
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="analisis-tab" data-bs-toggle="pill" data-bs-target="#analisis" type="button">
                    📊 Análisis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ranking-tab" data-bs-toggle="pill" data-bs-target="#ranking" type="button">
                    🏆 Top Ventas
                </button>
            </li>
        </ul>

        <div class="tab-content" id="dashboardContent">
            <!-- Tab Ganancias -->
            <div class="tab-pane fade show active" id="ganancias" role="tabpanel">
                <div class="row">
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📈 Ganancias por Mes (Últimos 12 meses)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="gananciasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">🐂🐄 Distribución por Sexo</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="sexoChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Inventario -->
            <div class="tab-pane fade" id="inventario" role="tabpanel">
                <div class="row">
                    <div class="col-12 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">⏰ Crías por Tiempo en Inventario</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="edadChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">🚨 Alertas de Inventario</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $criasViejas = array_filter($edadData, function($cria) {
                                    return $cria['dias_en_inventario'] > 180; // Más de 6 meses
                                });
                                ?>
                                <?php if (count($criasViejas) > 0): ?>
                                    <div class="alert alert-warning">
                                        <strong>⚠️ <?= count($criasViejas) ?> crías llevan más de 6 meses</strong>
                                        <ul class="mt-2 mb-0">
                                            <?php foreach(array_slice($criasViejas, 0, 5) as $cria): ?>
                                                <li>Arete <?= $cria['arete'] ?> - <?= $cria['dias_en_inventario'] ?> días</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <strong>✅ Inventario saludable</strong><br>
                                        No hay crías con tiempo excesivo en inventario
                                    </div>
                                <?php endif; ?>

                                <h6 class="mt-3">📋 Resumen por Rango de Edad</h6>
                                <?php
                                $rangoStats = [];
                                foreach($edadData as $cria) {
                                    $rango = $cria['rango_edad'];
                                    if (!isset($rangoStats[$rango])) {
                                        $rangoStats[$rango] = 0;
                                    }
                                    $rangoStats[$rango]++;
                                }
                                ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach($rangoStats as $rango => $count): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><?= $rango ?></span>
                                            <span class="badge bg-primary"><?= $count ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Análisis -->
            <div class="tab-pane fade" id="analisis" role="tabpanel">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">⚖️ Análisis de Ganancia de Peso (Últimos 6 meses)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="pesoChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📊 Métricas de Rendimiento</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $pesoPromedio = array_sum(array_column($pesoData, 'diferencia_kg')) / max(count($pesoData), 1);
                                $mejorGanancia = max(array_column($pesoData, 'diferencia_kg'));
                                ?>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>📈 Ganancia promedio de peso:</span>
                                        <strong><?= number_format($pesoPromedio, 2) ?> kg</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>🏆 Mejor ganancia de peso:</span>
                                        <strong><?= number_format($mejorGanancia, 2) ?> kg</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>💰 Ganancia promedio por venta:</span>
                                        <strong>$<?= number_format($statsGenerales['ganancia_promedio'] ?? 0, 2) ?></strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">🎯 Recomendaciones</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong>💡 Consejos basados en tus datos:</strong>
                                    <ul class="mt-2 mb-0">
                                        <?php if ($pesoPromedio > 50): ?>
                                            <li>✅ Excelente ganancia de peso promedio</li>
                                        <?php elseif ($pesoPromedio > 30): ?>
                                            <li>⚠️ Ganancia de peso moderada, considera revisar alimentación</li>
                                        <?php else: ?>
                                            <li>🚨 Ganancia de peso baja, revisa estrategia nutricional</li>
                                        <?php endif; ?>
                                        
                                        <?php if (count($criasViejas) > 0): ?>
                                            <li>⏰ Considera vender crías con más tiempo en inventario</li>
                                        <?php endif; ?>
                                        
                                        <?php if (($statsGenerales['ganancia_año'] ?? 0) > 0): ?>
                                            <li>📈 Tendencia positiva en ganancias este año</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Ranking -->
            <div class="tab-pane fade" id="ranking" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">🏆 Top 10 Mejores Ventas</h5>
                    </div>
                    <div class="card-body">
                        <div class="mobile-table-wrapper">
                            <div class="table-responsive">
                                <table class="table table-hover mobile-table">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>🏅 Pos</th>
                                            <th>Arete</th>
                                            <th>Ganancia</th>
                                            <th>Peso</th>
                                            <th class="d-none d-md-table-cell">Total Venta</th>
                                            <th class="d-none d-md-table-cell">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($topVentas as $index => $venta): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($index == 0): ?>
                                                        <span class="badge bg-warning">🥇 1°</span>
                                                    <?php elseif ($index == 1): ?>
                                                        <span class="badge bg-secondary">🥈 2°</span>
                                                    <?php elseif ($index == 2): ?>
                                                        <span class="badge bg-warning" style="background-color: #cd7f32 !important;">🥉 3°</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary"><?= $index + 1 ?>°</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong><?= $venta['arete'] ?></strong></td>
                                                <td class="text-success"><strong>$<?= number_format($venta['total_ganancia'], 0) ?></strong></td>
                                                <td><?= $venta['kg_venta'] ?> kg</td>
                                                <td class="d-none d-md-table-cell">$<?= number_format($venta['venta'], 0) ?></td>
                                                <td class="d-none d-md-table-cell"><?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuración general de Chart.js
        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.color = '#333';
        
        // Configuración global de tooltips
        Chart.defaults.plugins.tooltip = {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 12
            },
            footerFont: {
                size: 10
            },
            padding: 12,
            cornerRadius: 8,
            displayColors: false,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== undefined) {
                        label += context.parsed.y.toLocaleString();
                    }
                    return label;
                }
            }
        };

        // Configuración responsive para gráficos
        const responsiveOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: window.innerWidth < 768 ? 'bottom' : 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        }
                    }
                }
            }
        };

        // 1. Gráfica de Ganancias por Mes (Mejorada con línea de tendencia)
        const gananciasCtx = document.getElementById('gananciasChart').getContext('2d');
        const gananciasChart = new Chart(gananciasCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($gananciasData, 'nombre_mes')) ?>,
                datasets: [{
                    label: 'Ganancias ($)',
                    data: <?= json_encode(array_column($gananciasData, 'ganancia')) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'Número de Ventas',
                    data: <?= json_encode(array_column($gananciasData, 'ventas')) ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }, {
                    label: 'Tendencia Ganancias',
                    data: <?= json_encode(array_column($gananciasData, 'ganancia')) ?>,
                    type: 'line',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    yAxisID: 'y',
                    tension: 0.1
                }]
            },
            options: {
                ...responsiveOptions,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Ganancias ($)',
                            font: {
                                size: window.innerWidth < 768 ? 10 : 12
                            }
                        },
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            },
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Número de Ventas',
                            font: {
                                size: window.innerWidth < 768 ? 10 : 12
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            },
                            maxRotation: window.innerWidth < 768 ? 45 : 0
                        }
                    }
                },
                plugins: {
                    ...responsiveOptions.plugins,
                    title: {
                        display: true,
                        text: 'Evolución Mensual de Ganancias y Ventas',
                        font: {
                            size: window.innerWidth < 768 ? 12 : 14
                        }
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function(context) {
                                const index = context[0].dataIndex;
                                const ganancia = context[0].raw;
                                const ventas = context[1].raw;
                                const promedio = ganancia / ventas;
                                return [
                                    `Promedio por venta: $${promedio.toLocaleString('es-MX', {maximumFractionDigits: 2})}`,
                                    `Total: $${ganancia.toLocaleString('es-MX')}`
                                ];
                            }
                        }
                    }
                }
            }
        });

        // 2. Gráfica de Distribución por Sexo (Mejorada a barras agrupadas)
        const sexoCtx = document.getElementById('sexoChart').getContext('2d');
        const sexoData = <?= json_encode($sexoData) ?>;
        
        // Procesar datos para el gráfico de barras agrupadas
        let activasM = 0, activasH = 0, vendidasM = 0, vendidasH = 0;
        sexoData.forEach(item => {
            if (item.tipo === 'Activas') {
                if (item.sexo === 'M') activasM = item.cantidad;
                if (item.sexo === 'H') activasH = item.cantidad;
            } else {
                if (item.sexo === 'M') vendidasM = item.cantidad;
                if (item.sexo === 'H') vendidasH = item.cantidad;
            }
        });

        const sexoChart = new Chart(sexoCtx, {
            type: 'bar',
            data: {
                labels: ['Machos', 'Hembras'],
                datasets: [
                    {
                        label: 'Activos',
                        data: [activasM, activasH],
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Vendidos',
                        data: [vendidasM, vendidasH],
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                ...responsiveOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad',
                            font: {
                                size: window.innerWidth < 768 ? 10 : 12
                            }
                        },
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            }
                        }
                    }
                },
                plugins: {
                    ...responsiveOptions.plugins,
                    title: {
                        display: true,
                        text: 'Distribución por Sexo (Activos vs Vendidos)',
                        font: {
                            size: window.innerWidth < 768 ? 11 : 13
                        }
                    },
                    tooltip: {
                        callbacks: {
                            footer: function(context) {
                                const dataset = context[0].dataset;
                                const total = dataset.data.reduce((a, b) => a + b, 0);
                                const porcentaje = ((context[0].raw / total) * 100).toFixed(1);
                                return `${porcentaje}% del total ${dataset.label.toLowerCase()}`;
                            }
                        }
                    }
                }
            }
        });

        // 3. Gráfica de Evolución de Peso (Mejorada)
        const evolucionPesoCtx = document.getElementById('pesoChart').getContext('2d');
        const evolucionPesoChart = new Chart(evolucionPesoCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_unique(array_column($pesoData, 'mes_venta'))) ?>,
                datasets: [{
                    label: 'Peso Promedio de Compra (kg)',
                    data: <?= json_encode(array_values(array_reduce($pesoData, function($acc, $item) {
                        $mes = $item['mes_venta'];
                        if (!isset($acc[$mes])) {
                            $acc[$mes] = ['sum' => 0, 'count' => 0];
                        }
                        $acc[$mes]['sum'] += $item['kg_compra'];
                        $acc[$mes]['count']++;
                        return $acc;
                    }, []))) ?>.map(m => m.sum / m.count),
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.4
                }, {
                    label: 'Peso Promedio de Venta (kg)',
                    data: <?= json_encode(array_values(array_reduce($pesoData, function($acc, $item) {
                        $mes = $item['mes_venta'];
                        if (!isset($acc[$mes])) {
                            $acc[$mes] = ['sum' => 0, 'count' => 0];
                        }
                        $acc[$mes]['sum'] += $item['kg_venta'];
                        $acc[$mes]['count']++;
                        return $acc;
                    }, []))) ?>.map(m => m.sum / m.count),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4
                }, {
                    label: 'Ganancia Promedio de Peso (kg)',
                    data: <?= json_encode(array_values(array_reduce($pesoData, function($acc, $item) {
                        $mes = $item['mes_venta'];
                        if (!isset($acc[$mes])) {
                            $acc[$mes] = ['sum' => 0, 'count' => 0];
                        }
                        $acc[$mes]['sum'] += ($item['kg_venta'] - $item['kg_compra']);
                        $acc[$mes]['count']++;
                        return $acc;
                    }, []))) ?>.map(m => m.sum / m.count),
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    tension: 0.4
                }]
            },
            options: {
                ...responsiveOptions,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Peso (kg)',
                            font: {
                                size: window.innerWidth < 768 ? 10 : 12
                            }
                        },
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            }
                        }
                    }
                },
                plugins: {
                    ...responsiveOptions.plugins,
                    title: {
                        display: true,
                        text: 'Evolución de Peso (Compra vs Venta)',
                        font: {
                            size: window.innerWidth < 768 ? 11 : 13
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw.toFixed(2)} kg`;
                            }
                        }
                    }
                }
            }
        });

        // 4. Gráfica de Edad del Inventario (Mejorada)
        const edadCtx = document.getElementById('edadChart').getContext('2d');
        const edadData = <?= json_encode($edadData) ?>;
        
        // Procesar datos para rangos de edad
        const rangos = {};
        edadData.forEach(item => {
            const rango = item.rango_edad;
            if (!rangos[rango]) rangos[rango] = 0;
            rangos[rango]++;
        });

        const edadChart = new Chart(edadCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(rangos),
                datasets: [{
                    label: 'Número de Crías',
                    data: Object.values(rangos),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ],
                    borderColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                ...responsiveOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Crías',
                            font: {
                                size: window.innerWidth < 768 ? 10 : 12
                            }
                        },
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            },
                            maxRotation: window.innerWidth < 768 ? 45 : 0
                        }
                    }
                },
                plugins: {
                    ...responsiveOptions.plugins,
                    title: {
                        display: true,
                        text: 'Distribución de Crías por Tiempo en Inventario',
                        font: {
                            size: window.innerWidth < 768 ? 11 : 13
                        }
                    }
                }
            }
        });

        // 5. Nuevo Gráfico: Rendimiento por Mes
        const rendimientoContainer = document.createElement('div');
        rendimientoContainer.className = 'chart-container';
        const rendimientoCanvas = document.createElement('canvas');
        rendimientoCanvas.id = 'rendimientoChart';
        rendimientoContainer.appendChild(rendimientoCanvas);
        document.querySelector('#analisis .card-body').prepend(rendimientoContainer);

        const rendimientoCtx = rendimientoCanvas.getContext('2d');
        const rendimientoChart = new Chart(rendimientoCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($gananciasData, 'nombre_mes')) ?>,
                datasets: [{
                    label: 'Ganancia por kg vendido',
                    data: <?= json_encode(array_map(function($item) {
                        return $item['ventas'] > 0 ? ($item['ganancia'] / $item['ventas']) : 0;
                    }, $gananciasData)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                ...responsiveOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Ganancia por kg ($)',
                            font: {
                                size: window.innerWidth < 768 ? 10 : 12
                            }
                        },
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            },
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            },
                            maxRotation: window.innerWidth < 768 ? 45 : 0
                        }
                    }
                },
                plugins: {
                    ...responsiveOptions.plugins,
                    title: {
                        display: true,
                        text: 'Rendimiento Mensual (Ganancia por kg vendido)',
                        font: {
                            size: window.innerWidth < 768 ? 11 : 13
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `$${context.raw.toFixed(2)} por kg`;
                            }
                        }
                    }
                }
            }
        });

        // 6. Nuevo Gráfico: Tasa de Rotación (Radar)
        const rotacionContainer = document.createElement('div');
        rotacionContainer.className = 'chart-container';
        const rotacionCanvas = document.createElement('canvas');
        rotacionCanvas.id = 'rotacionChart';
        rotacionContainer.appendChild(rotacionCanvas);
        document.querySelector('#inventario .card-body').prepend(rotacionContainer);

        const rotacionCtx = rotacionCanvas.getContext('2d');
        const rotacionData = <?= json_encode($rangoStats) ?>;
        const rotacionLabels = Object.keys(rotacionData);
        const rotacionValues = Object.values(rotacionData);

        const rotacionChart = new Chart(rotacionCtx, {
            type: 'radar',
            data: {
                labels: rotacionLabels,
                datasets: [{
                    label: 'Crías en Inventario',
                    data: rotacionValues,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    pointBackgroundColor: 'rgba(255, 159, 64, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 159, 64, 1)'
                }]
            },
            options: {
                ...responsiveOptions,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: Math.max(...rotacionValues) * 1.2,
                        ticks: {
                            font: {
                                size: window.innerWidth < 768 ? 8 : 10
                            }
                        }
                    }
                },
                plugins: {
                    ...responsiveOptions.plugins,
                    title: {
                        display: true,
                        text: 'Distribución de Crías por Tiempo en Inventario',
                        font: {
                            size: window.innerWidth < 768 ? 11 : 13
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw} crías`;
                            }
                        }
                    }
                }
            }
        });

        // Función para redimensionar gráficos cuando cambia el tamaño de ventana
        function resizeCharts() {
            setTimeout(() => {
                gananciasChart.resize();
                sexoChart.resize();
                evolucionPesoChart.resize();
                edadChart.resize();
                rendimientoChart.resize();
                rotacionChart.resize();
            }, 100);
        }

        // Event listener para redimensionamiento
        window.addEventListener('resize', resizeCharts);

        // Inicializar tooltips de Bootstrap si existen
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Scroll suave para navegación en tablets
        document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                // Pequeño delay para permitir que el tab se active
                setTimeout(() => {
                    const targetPane = document.querySelector(this.getAttribute('data-bs-target'));
                    if (targetPane && window.innerWidth >= 768 && window.innerWidth < 1200) {
                        targetPane.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 150);
            });
        });

        // Mejorar la experiencia en dispositivos táctiles
        if ('ontouchstart' in window) {
            document.body.classList.add('touch-device');
            
            // Añadir estilo adicional para dispositivos táctiles
            const touchStyles = `
                <style>
                    .touch-device .stat-card:hover {
                        transform: none;
                    }
                    
                    .touch-device .btn:hover {
                        transform: none;
                    }
                    
                    .touch-device .table-hover tbody tr:hover {
                        transform: none;
                    }
                    
                    .touch-device .stat-card:active,
                    .touch-device .btn:active {
                        transform: scale(0.98);
                        transition: transform 0.1s ease;
                    }
                </style>
            `;
            document.head.insertAdjacentHTML('beforeend', touchStyles);
        }

        // Función para optimizar rendimiento en dispositivos móviles
        function optimizeForMobile() {
            if (window.innerWidth < 768) {
                // Reducir la frecuencia de actualización de gráficos en móviles
                Chart.defaults.animation.duration = 800;
                
                // Simplificar las animaciones en móviles
                [gananciasChart, sexoChart, evolucionPesoChart, edadChart, rendimientoChart, rotacionChart].forEach(chart => {
                    if (chart) {
                        chart.options.animation = {
                            duration: 600,
                            easing: 'easeOutQuart'
                        };
                    }
                });
            }
        }

        // Ejecutar optimización al cargar
        optimizeForMobile();

        // Reoptimizar cuando cambie el tamaño de ventana
        window.addEventListener('resize', optimizeForMobile);

        // Lazy loading para gráficos (mejorar rendimiento inicial)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const chartCanvas = entry.target.querySelector('canvas');
                    if (chartCanvas && !chartCanvas.classList.contains('loaded')) {
                        chartCanvas.classList.add('loaded');
                        // Aquí podrías cargar datos adicionales si fuera necesario
                    }
                }
            });
        });

        // Observar todas las tarjetas de gráficos
        document.querySelectorAll('.chart-container').forEach(container => {
            observer.observe(container);
        });

        // Filtros interactivos
        document.getElementById('rangoFechas').addEventListener('change', function() {
            const meses = parseInt(this.value);
            const customContainer = document.getElementById('customRangeContainer');
            
            if (isNaN(meses)) {
                customContainer.classList.remove('d-none');
            } else {
                customContainer.classList.add('d-none');
                actualizarGraficos(meses);
            }
        });

        function actualizarGraficos(meses) {
            // Aquí iría la lógica para actualizar los gráficos con los nuevos datos
            // Podrías hacer una petición AJAX o filtrar los datos existentes
            
            console.log(`Actualizando gráficos para últimos ${meses} meses`);
            // Ejemplo de actualización simple (en realidad necesitarías nuevos datos)
            gananciasChart.data.labels = gananciasChart.data.labels.slice(-meses);
            gananciasChart.data.datasets.forEach(dataset => {
                dataset.data = dataset.data.slice(-meses);
            });
            gananciasChart.update();
            
            // Actualizar otros gráficos de manera similar...
        }
    </script>
</body>
</html>