<?php
require_once 'bd.php';

$sql = "SELECT * FROM ventas ORDER BY fecha_venta DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Ventas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h2>📜 Historial de Crías Vendidas</h2>
    <a href="index.php" class="btn btn-secondary mb-3">⬅️ Volver al inicio</a>

    <table class="table table-bordered table-striped table-sm">
      <thead class="table-dark">
        <tr>
          <th>Arete</th>
          <th>Sexo</th>
          <th>Kg Compra</th>
          <th>Kg Venta</th>
          <th>Diferencia</th>
          <th>Total Compra</th>
          <th>Total Costo</th>
          <th>Venta</th>
          <th>Gasto Traslado</th>
          <th>Utilidad</th>
          <th>Total Ganancia</th>
          <th>Fecha Compra</th>
          <th>Fecha Venta</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $resultado->fetch_assoc()): ?>
        <tr>
          <td><?= $row['arete'] ?></td>
          <td><?= $row['sexo'] ?></td>
          <td><?= $row['kg_compra'] ?> kg</td>
          <td><?= $row['kg_venta'] ?> kg</td>
          <td><?= $row['diferencia_kg'] ?> kg</td>
          <td>$<?= number_format($row['total_compra'], 2) ?></td>
          <td>$<?= number_format($row['total_costo'], 2) ?></td>
          <td>$<?= number_format($row['venta'], 2) ?></td>
          <td>$<?= number_format($row['gasto_traslado'], 2) ?></td>
          <td>$<?= number_format($row['utilidad'], 2) ?></td>
          <td>$<?= number_format($row['total_ganancia'], 2) ?></td>
          <td><?= $row['fecha_compra'] ?></td>
          <td><?= $row['fecha_venta'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
