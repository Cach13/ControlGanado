<?php
require_once 'bd.php';

// Verificar que los datos lleguen por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Recibir y limpiar datos
$arete = trim($_POST['arete'] ?? '');
$sexo = $_POST['sexo'] ?? '';
$kg_compra = floatval($_POST['kg_compra'] ?? 0);
$precio_compra = floatval($_POST['precio_compra'] ?? 0);
$fecha_compra = $_POST['fecha_compra'] ?? '';

// Validaciones básicas
$errores = [];

if (empty($arete)) {
    $errores[] = "El número de arete es obligatorio";
}

if (!in_array($sexo, ['M', 'H'])) {
    $errores[] = "Debe seleccionar un sexo válido";
}

if ($kg_compra <= 0) {
    $errores[] = "El peso debe ser mayor a 0";
}

if ($precio_compra <= 0) {
    $errores[] = "El precio debe ser mayor a 0";
}

if (empty($fecha_compra)) {
    $errores[] = "La fecha de compra es obligatoria";
}

// Verificar que el arete no exista
if (empty($errores)) {
    $stmt_check = $conn->prepare("SELECT arete FROM crias_activas WHERE arete = ?");
    $stmt_check->bind_param("s", $arete);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $errores[] = "Ya existe una cría con el arete '$arete'";
    }
    $stmt_check->close();
    
    // También verificar en ventas para evitar duplicados históricos
    $stmt_check_ventas = $conn->prepare("SELECT arete FROM ventas WHERE arete = ?");
    $stmt_check_ventas->bind_param("s", $arete);
    $stmt_check_ventas->execute();
    $result_check_ventas = $stmt_check_ventas->get_result();
    
    if ($result_check_ventas->num_rows > 0) {
        $errores[] = "El arete '$arete' ya fue utilizado en una venta anterior";
    }
    $stmt_check_ventas->close();
}

// Si hay errores, redirigir con mensaje
if (!empty($errores)) {
    $mensaje_error = implode("\\n", $errores);
    echo "<script>
        alert('❌ Error al agregar la cría:\\n\\n$mensaje_error');
        window.location.href = 'agregar_cria.php';
    </script>";
    exit;
}

// Insertar la nueva cría
try {
    $stmt = $conn->prepare("INSERT INTO crias_activas (arete, sexo, kg_compra, precio_compra, fecha_compra) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdds", $arete, $sexo, $kg_compra, $precio_compra, $fecha_compra);
    
    if ($stmt->execute()) {
        $total_compra = $kg_compra * $precio_compra;
        $sexo_texto = ($sexo == 'M') ? 'Macho' : 'Hembra';
        
        echo "<script>
            alert('✅ Cría agregada exitosamente\\n\\nArete: $arete\\nSexo: $sexo_texto\\nPeso: $kg_compra kg\\nPrecio: $" . number_format($precio_compra, 2) . "\\nTotal: $" . number_format($total_compra, 2) . "');
            window.location.href = 'index.php';
        </script>";
    } else {
        throw new Exception("Error al ejecutar la consulta");
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo "<script>
        alert('❌ Error al agregar la cría:\\n\\n" . $e->getMessage() . "');
        window.location.href = 'agregar_cria.php';
    </script>";
}

$conn->close();
?>