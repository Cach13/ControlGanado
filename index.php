<?php
require_once 'bd.php';

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
    <title>Control de Ganado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">📋 Control de Crías</h1>

        <div class="mb-4">
            <h5>🐮 Crías disponibles: <strong><?= $conteo ?></strong></h5>
            <h5>📈 Ganancia mensual: <strong>$<?= number_format($gananciaMensual, 2) ?></strong></h5>
            <h5>💰 Ganancia anual: <strong>$<?= number_format($gananciaAnual, 2) ?></strong></h5>
        </div>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-2">
                <select name="sexo" class="form-select">
                    <option value="">Sexo</option>
                    <option value="M" <?= $filtroSexo == 'M' ? 'selected' : '' ?>>Macho</option>
                    <option value="H" <?= $filtroSexo == 'H' ? 'selected' : '' ?>>Hembra</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="fecha" class="form-control" value="<?= $filtroFecha ?>">
            </div>
            <div class="col-md-3">
                <input type="text" name="arete" class="form-control" placeholder="Buscar por arete" value="<?= $filtroArete ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Arete</th>
                    <th>Sexo</th>
                    <th>Kg Compra</th>
                    <th>Precio Compra</th>
                    <th>Total Compra</th>
                    <th>Fecha Compra</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td>
                        <a href="venta.php?arete=<?= $row['arete'] ?>" class="text-decoration-none">
                            <?= $row['arete'] ?>
                        </a>
                    </td>
                    <td><?= $row['sexo'] ?></td>
                    <td><?= $row['kg_compra'] ?> kg</td>
                    <td>$<?= number_format($row['precio_compra'], 2) ?></td>
                    <td>$<?= number_format($row['total_compra'], 2) ?></td>
                    <td><?= $row['fecha_compra'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
