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
    <title>Registrar Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmarEnvio() {
            return confirm("¿Estás seguro de registrar esta venta? Esta acción eliminará la cría.");
        }
    </script>
</head>
<body class="p-4">
    <div class="container">
        <h2>📤 Registrar Venta de Cría</h2>
        <form action="procesar_venta.php" method="POST" onsubmit="return confirmarEnvio();">
            <input type="hidden" name="arete" value="<?= $cria['arete'] ?>">
            <input type="hidden" name="sexo" value="<?= $cria['sexo'] ?>">
            <input type="hidden" name="kg_compra" value="<?= $cria['kg_compra'] ?>">
            <input type="hidden" name="precio_compra" value="<?= $cria['precio_compra'] ?>">
            <input type="hidden" name="total_compra" value="<?= $cria['total_compra'] ?>">
            <input type="hidden" name="fecha_compra" value="<?= $cria['fecha_compra'] ?>">

            <div class="mb-3">
                <label>Kg Venta:</label>
                <input type="number" name="kg_venta" step="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Total Venta ($):</label>
                <input type="number" name="venta" step="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Gasto de Traslado ($):</label>
                <input type="number" name="gasto_traslado" step="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Costo por Kg ganado ($):</label>
                <input type="number" name="costo_kg" step="0.01" class="form-control" value="40.00" required>
            </div>

            <div class="mb-3">
                <label>Seguro ($):</label>
                <input type="number" name="seguro" step="0.01" class="form-control" value="650.00" required>
            </div>

            <button type="submit" class="btn btn-success">✅ Confirmar venta</button>
            <a href="index.php" class="btn btn-secondary">⬅️ Cancelar</a>
        </form>
    </div>
</body>
</html>
