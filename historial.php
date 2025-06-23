<?php
require_once 'bd.php';

$sql = "SELECT * FROM ventas ORDER BY fecha_venta DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas</title>
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

        .dashboard-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 2rem 1rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .dashboard-header h2 {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .dashboard-header p {
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            opacity: 0.9;
            margin: 0;
        }

        .btn {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
            margin-bottom: 1rem;
            text-decoration: none;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4c93 100%);
            color: white;
        }

        .table-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .table-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 1.5rem;
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .table-responsive {
            border-radius: 0;
            box-shadow: none;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1rem 0.75rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 0.875rem 0.75rem;
            vertical-align: middle;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:nth-child(even):hover {
            background: linear-gradient(90deg, #e9ecef 0%, #dee2e6 100%);
        }

        /* Badges para diferentes valores */
        .badge-profit {
            background: var(--success-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-loss {
            background: var(--danger-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-neutral {
            background: var(--info-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
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

            .btn {
                padding: 0.5rem 1.5rem;
                font-size: 0.9rem;
                width: 100%;
                margin-bottom: 1rem;
            }

            .table-container {
                border-radius: 15px;
                margin: 0 -0.5rem 2rem -0.5rem;
            }

            .table-header {
                padding: 1rem;
                font-size: 1rem;
            }

            .table {
                font-size: 0.75rem;
            }

            .table thead th {
                padding: 0.75rem 0.5rem;
                font-size: 0.7rem;
            }

            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.75rem;
            }

            /* Horizontal scroll for mobile */
            .mobile-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-table {
                min-width: 1000px;
            }
        }

        @media (max-width: 576px) {
            .table {
                font-size: 0.7rem;
            }

            .table thead th {
                padding: 0.5rem 0.3rem;
                font-size: 0.65rem;
            }

            .table tbody td {
                padding: 0.5rem 0.3rem;
                font-size: 0.7rem;
            }

            .mobile-table {
                min-width: 1200px;
            }
        }

        /* Custom scrollbar */
        .mobile-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .mobile-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .mobile-scroll::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 10px;
        }

        .mobile-scroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4c93 100%);
        }

        /* Animation for table load */
        .table-container {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header">
            <h2>📜 Historial de Crías Vendidas</h2>
            <p>Registro completo de todas las transacciones realizadas</p>
        </div>

        <!-- Back Button -->
        <a href="index.php" class="btn btn-secondary">
            ⬅️ Volver al inicio
        </a>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                📊 Registro de Ventas Completadas
            </div>
            
            <?php if($resultado->num_rows > 0): ?>
            <div class="table-responsive mobile-scroll">
                <table class="table table-hover mobile-table">
                    <thead>
                        <tr>
                            <th>🏷️ Arete</th>
                            <th>👥 Sexo</th>
                            <th>⚖️ Kg Compra</th>
                            <th>⚖️ Kg Venta</th>
                            <th>📈 Diferencia</th>
                            <th>💰 Total Compra</th>
                            <th>💸 Total Costo</th>
                            <th>💵 Venta</th>
                            <th>🚚 Gasto Traslado</th>
                            <th>📊 Utilidad</th>
                            <th>🎯 Total Ganancia</th>
                            <th>📅 Fecha Compra</th>
                            <th>📅 Fecha Venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['arete']) ?></strong></td>
                            <td>
                                <?= $row['sexo'] == 'M' ? '👨 Macho' : '👩 Hembra' ?>
                            </td>
                            <td><?= number_format($row['kg_compra'], 1) ?> kg</td>
                            <td><?= number_format($row['kg_venta'], 1) ?> kg</td>
                            <td>
                                <?php if($row['diferencia_kg'] > 0): ?>
                                    <span class="badge-profit">+<?= number_format($row['diferencia_kg'], 1) ?> kg</span>
                                <?php elseif($row['diferencia_kg'] < 0): ?>
                                    <span class="badge-loss"><?= number_format($row['diferencia_kg'], 1) ?> kg</span>
                                <?php else: ?>
                                    <span class="badge-neutral"><?= number_format($row['diferencia_kg'], 1) ?> kg</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($row['total_compra'], 2) ?></td>
                            <td>$<?= number_format($row['total_costo'], 2) ?></td>
                            <td><strong>$<?= number_format($row['venta'], 2) ?></strong></td>
                            <td>$<?= number_format($row['gasto_traslado'], 2) ?></td>
                            <td>
                                <?php if($row['utilidad'] > 0): ?>
                                    <span class="badge-profit">$<?= number_format($row['utilidad'], 2) ?></span>
                                <?php elseif($row['utilidad'] < 0): ?>
                                    <span class="badge-loss">$<?= number_format($row['utilidad'], 2) ?></span>
                                <?php else: ?>
                                    <span class="badge-neutral">$<?= number_format($row['utilidad'], 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['total_ganancia'] > 0): ?>
                                    <span class="badge-profit"><strong>$<?= number_format($row['total_ganancia'], 2) ?></strong></span>
                                <?php elseif($row['total_ganancia'] < 0): ?>
                                    <span class="badge-loss"><strong>$<?= number_format($row['total_ganancia'], 2) ?></strong></span>
                                <?php else: ?>
                                    <span class="badge-neutral"><strong>$<?= number_format($row['total_ganancia'], 2) ?></strong></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_compra'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_venta'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                <h4>No hay registros de ventas</h4>
                <p>Aún no se han registrado ventas en el sistema.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>