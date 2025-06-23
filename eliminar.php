<?php
require_once 'bd.php';

// Verificar que se recibió el parámetro arete
if (!isset($_GET['arete']) || empty($_GET['arete'])) {
    header('Location: index.php?error=arete_no_especificado');
    exit();
}

$arete = $_GET['arete'];

// Verificar que la cría existe
$verificar = $conn->prepare("SELECT * FROM crias_activas WHERE arete = ?");
$verificar->bind_param("s", $arete);
$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows === 0) {
    header('Location: index.php?error=cria_no_encontrada');
    exit();
}

$cria = $resultado->fetch_assoc();

// Si se confirma la eliminación
if (isset($_POST['confirmar_eliminacion'])) {
    // Eliminar la cría de la base de datos (NO se agrega al historial)
    $eliminar = $conn->prepare("DELETE FROM crias_activas WHERE arete = ?");
    $eliminar->bind_param("s", $arete);
    
    if ($eliminar->execute()) {
        header('Location: index.php?success=cria_eliminada');
    } else {
        header('Location: index.php?error=error_eliminacion');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Cría - Control de Ganado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">🗑️ Confirmar Eliminación</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>⚠️ ¡Atención!</strong><br>
                            Esta acción eliminará permanentemente la cría y <strong>NO</strong> se agregará al historial de ventas.
                        </div>
                        
                        <h5>Datos de la cría a eliminar:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Arete:</strong></td>
                                    <td><?= htmlspecialchars($cria['arete']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Sexo:</strong></td>
                                    <td>
                                        <?php if ($cria['sexo'] == 'M'): ?>
                                            <span class="badge bg-primary">🐂 Macho</span>
                                        <?php else: ?>
                                            <span class="badge bg-pink" style="background-color: #e91e63;">🐄 Hembra</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Peso Compra:</strong></td>
                                    <td><?= $cria['kg_compra'] ?> kg</td>
                                </tr>
                                <tr>
                                    <td><strong>Precio Compra:</strong></td>
                                    <td>$<?= number_format($cria['precio_compra'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Compra:</strong></td>
                                    <td><strong>$<?= number_format($cria['total_compra'], 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha Compra:</strong></td>
                                    <td><?= date('d/m/Y', strtotime($cria['fecha_compra'])) ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="text-center mt-4">
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="confirmar_eliminacion" class="btn btn-danger btn-lg me-3">
                                    🗑️ Sí, Eliminar Permanentemente
                                </button>
                            </form>
                            <a href="index.php" class="btn btn-secondary btn-lg">
                                ↩️ Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>