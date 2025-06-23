<?php
require_once 'bd.php';

// Recibir datos
$arete = $_POST['arete'];
$sexo = $_POST['sexo'];
$kg_compra = $_POST['kg_compra'];
$precio_compra = $_POST['precio_compra'];
$total_compra = $_POST['total_compra'];
$fecha_compra = $_POST['fecha_compra'];

$kg_venta = $_POST['kg_venta'];
$venta = $_POST['venta'];
$gasto_traslado = $_POST['gasto_traslado'];
$costo_kg = $_POST['costo_kg'];
$seguro = $_POST['seguro'];

// Cálculos
$diferencia_kg = $kg_venta - $kg_compra;
$total_costo = $diferencia_kg * $costo_kg;
$venta_menos_gasto = $venta - $gasto_traslado;
$utilidad = $venta_menos_gasto - $total_compra - $total_costo;
$parte_correspondiente = $utilidad / 2;
$total_ganancia = $parte_correspondiente - $seguro;

// Guardar venta
$stmt = $conn->prepare("INSERT INTO ventas (
    arete, sexo, kg_compra, precio_compra, total_compra, fecha_compra,
    kg_venta, diferencia_kg, costo_kg, total_costo, venta, gasto_traslado,
    venta_menos_gasto, utilidad, parte_correspondiente, seguro, total_ganancia
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ssddddddddddddddd",
    $arete, $sexo, $kg_compra, $precio_compra, $total_compra, $fecha_compra,
    $kg_venta, $diferencia_kg, $costo_kg, $total_costo, $venta, $gasto_traslado,
    $venta_menos_gasto, $utilidad, $parte_correspondiente, $seguro, $total_ganancia
);

$stmt->execute();
$stmt->close();

// Eliminar de crías activas
$conn->query("DELETE FROM crias_activas WHERE arete = '$arete'");

// Redirigir
header("Location: index.php");
exit;
?>
