<?php
require_once 'bd.php';

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
    <title>Dashboard - Control de Ganado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header text-center">
            <h1>📊 Dashboard de Control de Ganado</h1>
            <p class="mb-0">Análisis completo de tu operación ganadera</p>
        </div>

        <!-- Navegación -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="index.php" class="btn btn-secondary">⬅️ Volver al Sistema</a>
                <a href="historial.php" class="btn btn-info">📜 Historial</a>
                <a href="agregar_cria.php" class="btn btn-success">➕ Agregar Cría</a>
            </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= $statsGenerales['crias_activas'] ?? 0 ?></div>
                    <div>🐮 Crías Activas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number"><?= $statsGenerales['ventas_año'] ?? 0 ?></div>
                    <div>💰 Ventas <?= $anioActual ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number">$<?= number_format($statsGenerales['ganancia_año'] ?? 0, 0) ?></div>
                    <div>📈 Ganancia Anual</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number">$<?= number_format($statsGenerales['ganancia_promedio'] ?? 0, 0) ?></div>
                    <div>⭐ Ganancia Promedio</div>
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
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>📈 Ganancias por Mes (Últimos 12 meses)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="gananciasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>🐂🐄 Distribución por Sexo</h5>
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
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>⏰ Crías por Tiempo en Inventario</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="edadChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>🚨 Alertas de Inventario</h5>
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
                                <ul class="list-group">
                                    <?php foreach($rangoStats as $rango => $count): ?>
                                        <li class="list-group-item d-flex justify-content-between">
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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>⚖️ Análisis de Ganancia de Peso (Últimos 6 meses)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="pesoChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>📊 Métricas de Rendimiento</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $pesoPromedio = array_sum(array_column($pesoData, 'diferencia_kg')) / max(count($pesoData), 1);
                                $mejorGanancia = max(array_column($pesoData, 'diferencia_kg'));
                                $tiempoPromedio = 0; // Calcular después
                                ?>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>📈 Ganancia promedio de peso:</span>
                                        <strong><?= number_format($pesoPromedio, 2) ?> kg</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>🏆 Mejor ganancia de peso:</span>
                                        <strong><?= number_format($mejorGanancia, 2) ?> kg</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>💰 Ganancia promedio por venta:</span>
                                        <strong>$<?= number_format($statsGenerales['ganancia_promedio'] ?? 0, 2) ?></strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>🎯 Recomendaciones</h5>
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
                        <h5>🏆 Top 10 Mejores Ventas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>🏅 Posición</th>
                                        <th>Arete</th>
                                        <th>Ganancia</th>
                                        <th>Peso Venta</th>
                                        <th>Total Venta</th>
                                        <th>Fecha</th>
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
                                            <td class="text-success"><strong>$<?= number_format($venta['total_ganancia'], 2) ?></strong></td>
                                            <td><?= $venta['kg_venta'] ?> kg</td>
                                            <td>$<?= number_format($venta['venta'], 2) ?></td>
                                            <td><?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuración general de Chart.js
        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.color = '#333';

        // 1. Gráfica de Ganancias por Mes
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
                    fill: true
                }, {
                    label: 'Número de Ventas',
                    data: <?= json_encode(array_column($gananciasData, 'ventas')) ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Ganancias ($)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Número de Ventas'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Evolución Mensual de Ganancias y Ventas'
                    }
                }
            }
        });

        // 2. Gráfica de Distribución por Sexo
        const sexoCtx = document.getElementById('sexoChart').getContext('2d');
        const sexoData = <?= json_encode($sexoData) ?>;
        
        // Procesar datos para el gráfico de dona
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
            type: 'doughnut',
            data: {
                labels: ['🐂 Machos Activos', '🐄 Hembras Activas', '🐂 Machos Vendidos', '🐄 Hembras Vendidas'],
                datasets: [{
                    data: [activasM, activasH, vendidasM, vendidasH],
                    backgroundColor: [
                        '#36A2EB',
                        '#FF6384',
                        '#4BC0C0',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // 3. Gráfica de Ganancia de Peso
        const pesoCtx = document.getElementById('pesoChart').getContext('2d');
        const pesoChart = new Chart(pesoCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Peso Compra vs Ganancia',
                    data: <?= json_encode(array_map(function($item) {
                        return ['x' => $item['kg_compra'], 'y' => $item['diferencia_kg']];
                    }, $pesoData)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Peso de Compra (kg)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Ganancia de Peso (kg)'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Relación entre Peso de Compra y Ganancia de Peso'
                    }
                }
            }
        });

        // 4. Gráfica de Edad del Inventario
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
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Crías'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución de Crías por Tiempo en Inventario'
                    }
                }
            }
        });
    </script>
</body>
</html>