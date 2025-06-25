<?php
require_once 'config/bd.php';


// Filtros
$filtroSexo = $_GET['sexo'] ?? '';
$filtroFecha = $_GET['fecha'] ?? '';
$filtroArete = $_GET['arete'] ?? '';

// Consulta base
$sql = "SELECT * FROM crias_activas WHERE 1";

// Aplicar filtros
if ($filtroSexo != '') {
    $sql .= " AND sexo = '$filtroSexo'";
}
if ($filtroFecha != '') {
    $sql .= " AND fecha_compra = '$filtroFecha'";
}
if ($filtroArete != '') {
    $sql .= " AND arete LIKE '%$filtroArete%'";
}

$resultado = $conn->query($sql);

// Conteo de crías activas
$conteo = $conn->query("SELECT COUNT(*) AS total FROM crias_activas")->fetch_assoc()['total'];

// Ganancias mensuales y anuales
$mesActual = date('m');
$anioActual = date('Y');

$gananciaMensual = $conn->query("SELECT SUM(total_ganancia) AS total FROM ventas WHERE MONTH(fecha_venta) = $mesActual AND YEAR(fecha_venta) = $anioActual")->fetch_assoc()['total'] ?? 0;
$gananciaAnual = $conn->query("SELECT SUM(total_ganancia) AS total FROM ventas WHERE YEAR(fecha_venta) = $anioActual")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control Ganadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            background-color: #f8f9fa;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header-section {
            background: var(--secondary-gradient);
            color: white;
            padding: 2rem 1rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            text-align: center;
        }

        .header-title {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .header-subtitle {
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            opacity: 0.9;
            margin: 0;
        }

        .stats-section {
            margin-bottom: 2rem;
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

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: clamp(0.85rem, 2vw, 1rem);
            opacity: 0.9;
        }

        .actions-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .filters-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .filters-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2c3e50;
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

        .btn-custom {
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-dashboard {
            background: var(--info-color);
            color: white;
        }

        .btn-add {
            background: var(--success-color);
            color: white;
        }

        .btn-history {
            background: var(--warning-color);
            color: white;
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .table-section {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            background: white;
        }

        .table {
            margin: 0;
            font-size: 0.95rem;
        }

        .table-header {
            background: var(--secondary-gradient);
            color: white;
        }

        .table-header th {
            font-weight: 600;
            padding: 1rem;
            border: none;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .animal-id {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .gender-badge {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
            border-radius: 15px;
            font-weight: 600;
        }

        .badge-male {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .badge-female {
            background: linear-gradient(135deg, #e91e63, #c2185b);
            color: white;
        }

        .price-highlight {
            font-weight: 700;
            color: var(--success-color);
        }

        .btn-sell {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-sell:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .empty-icon {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: #6c757d;
            margin-bottom: 2rem;
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

        .footer-section {
            margin-top: 3rem;
            padding: 2rem 1rem;
            text-align: center;
            background: var(--secondary-gradient);
            color: white;
            border-radius: 10px;
        }

        .footer-info {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 0.5rem;
            }

            .header-section {
                padding: 1.5rem 1rem;
                margin-bottom: 1.5rem;
            }

            .stat-card {
                padding: 1rem;
                min-height: 100px;
            }

            .filters-section {
                padding: 1rem;
            }

            .btn {
                padding: 0.4rem 1rem;
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
                width: 100%;
            }

            .btn-custom {
                width: 100%;
                margin-bottom: 0.5rem;
                padding: 0.4rem 1rem;
                font-size: 0.85rem;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .btn-group-mobile {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .stat-value {
                font-size: 1.8rem;
            }

            .table {
                font-size: 0.75rem;
            }

            .filters-section {
                padding: 0.75rem;
            }
        }

        /* Mobile table styling */
        @media (max-width: 768px) {
            .mobile-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-table {
                min-width: 600px;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success message styling */
        .success-message {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            animation: slideIn 0.5s ease-out;
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
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="header-title">
                <i class="fas fa-clipboard-list"></i>
                Sistema de Control Ganadero
            </h1>
            <p class="header-subtitle">Gestión integral de ganado bovino</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?php 
            switch($_GET['success']) {
                case 'added':
                    echo 'Cría agregada exitosamente al sistema';
                    break;
                case 'sold':
                    echo 'Venta registrada exitosamente';
                    break;
                case 'updated':
                    echo 'Información actualizada correctamente';
                    break;
                default:
                    echo 'Operación completada exitosamente';
            }
            ?>
        </div>
        <?php endif; ?>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-cow"></i>
                        </div>
                        <div class="stat-value"><?= $conteo ?></div>
                        <div class="stat-label">Crías Disponibles</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value">$<?= number_format($gananciaMensual, 2) ?></div>
                        <div class="stat-label">Ganancia Mensual</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-value">$<?= number_format($gananciaAnual, 2) ?></div>
                        <div class="stat-label">Ganancia Anual</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Section -->
        <div class="actions-section">
            <div class="row">
                <div class="col-md-12">
                    <div class="btn-group-mobile">
                        <a href="/modules/dashboard/dashboard.php" class="btn btn-primary">📊 Dashboard</a>
                        <a href="/modules/crias/agregar_cria.php" class="btn btn-success">➕ Agregar Cría</a>
                        <a href="/modules/ventas/historial.php" class="btn btn-info">📜 Historial</a>
                        <a href="/modules/costos/costos.php" class="btn btn-warning">💰 Costos</a>
                            <i class="fas fa-file-alt"></i> Generar Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <h3 class="filters-title">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h3>
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Sexo</label>
                    <select name="sexo" class="form-select">
                        <option value="">Todos</option>
                        <option value="M" <?= $filtroSexo == 'M' ? 'selected' : '' ?>>Macho</option>
                        <option value="H" <?= $filtroSexo == 'H' ? 'selected' : '' ?>>Hembra</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de Compra</label>
                    <input type="date" name="fecha" class="form-control" value="<?= $filtroFecha ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Número de Arete</label>
                    <input type="text" name="arete" class="form-control" placeholder="Buscar por arete" value="<?= $filtroArete ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-custom btn-dashboard d-block w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="index.php" class="btn btn-outline-secondary d-block w-100">
                        <i class="fas fa-eraser"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <?php if ($conteo > 0): ?>
        <div class="table-section">
            <div class="table-responsive mobile-table-wrapper">
                <table class="table table-hover mobile-table">
                    <thead class="table-header">
                        <tr>
                            <th><i class="fas fa-tag"></i> Arete</th>
                            <th><i class="fas fa-venus-mars"></i> Sexo</th>
                            <th><i class="fas fa-weight"></i> Kg Compra</th>
                            <th><i class="fas fa-dollar-sign"></i> Precio Compra</th>
                            <th><i class="fas fa-calculator"></i> Total Compra</th>
                            <th><i class="fas fa-calendar"></i> Fecha Compra</th>
                            <th><i class="fas fa-cogs"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="animal-id"><?= htmlspecialchars($row['arete']) ?></span>
                            </td>
                            <td>
                                <?php if ($row['sexo'] == 'M'): ?>
                                    <span class="badge gender-badge badge-male">
                                        <i class="fas fa-mars"></i> Macho
                                    </span>
                                <?php else: ?>
                                    <span class="badge gender-badge badge-female">
                                        <i class="fas fa-venus"></i> Hembra
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($row['kg_compra'], 2) ?> kg</td>
                            <td>$<?= number_format($row['precio_compra'], 2) ?></td>
                            <td><span class="price-highlight">$<?= number_format($row['total_compra'], 2) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_compra'])) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/modules/ventas/venta.php urlencode($row['arete']) ?>" class="btn btn-sell btn-sm">
                                        <i class="fas fa-hand-holding-usd"></i> Vender
                                    </a>
                                    <a href="/modules/crias/eliminar.php urlencode($row['arete']) ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-cow"></i>
            </div>
            <h3 class="empty-title">No hay crías registradas</h3>
            <p class="empty-description">
                <?php if ($filtroSexo || $filtroFecha || $filtroArete): ?>
                    No se encontraron crías que coincidan con los filtros aplicados. Intente ajustar los criterios de búsqueda.
                <?php else: ?>
                    Comience agregando su primera cría al sistema para empezar a gestionar su ganado.
                <?php endif; ?>
            </p>
            <?php if ($filtroSexo || $filtroFecha || $filtroArete): ?>
                <a href="index.php" class="btn btn-custom btn-dashboard">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </a>
            <?php else: ?>
                <a href="agregar_cria.php" class="btn btn-custom btn-add">
                    <i class="fas fa-plus"></i> Agregar Primera Cría
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="footer-info">
                <i class="fas fa-info-circle"></i>
                Sistema de Control Ganadero - Versión 2.0
                <br>
                <small>Última actualización: <?= date('d/m/Y H:i') ?></small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmar antes de vender
        document.querySelectorAll('.btn-sell').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('¿Está seguro que desea proceder con la venta de esta cría?')) {
                    e.preventDefault();
                }
            });
        });

        // Auto-hide success messages
        setTimeout(function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                successMessage.style.opacity = '0';
                successMessage.style.transform = 'translateY(-20px)';
                setTimeout(() => successMessage.remove(), 500);
            }
        }, 5000);

        // Loading state for form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<span class="loading"></span> Procesando...';
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
</body>
</html>