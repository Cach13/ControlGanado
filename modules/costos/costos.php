<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/bd.php';

// Verificar si es una solicitud POST y si existe 'accion'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        case 'agregar':
            // Validar y procesar agregar
            $descripcion = trim($_POST['descripcion']);
            $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
            $categoria = in_array($_POST['categoria'], ['alimenticio', 'salud', 'general']) ? $_POST['categoria'] : 'general';
            $fecha = DateTime::createFromFormat('Y-m-d', $_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
            
            if (empty($descripcion)) {
                echo "<div class='mensaje error'>La descripción es requerida</div>";
            } elseif ($monto === false || $monto <= 0) {
                echo "<div class='mensaje error'>Monto inválido</div>";
            } else {
                $sql = "INSERT INTO costos_ganaderos (descripcion, monto, categoria, fecha) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdss", $descripcion, $monto, $categoria, $fecha);
                
                if ($stmt->execute()) {
                    echo "<div class='mensaje exito'>Costo agregado exitosamente</div>";
                } else {
                    echo "<div class='mensaje error'>Error al agregar el costo: " . $conn->error . "</div>";
                }
                $stmt->close();
            }
            break;
            
        case 'eliminar':
            // Procesar eliminación
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                echo "<div class='mensaje error'>ID inválido</div>";
            } else {
                $sql = "UPDATE costos_ganaderos SET activo = FALSE WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    echo "<div class='mensaje exito'>Costo eliminado exitosamente</div>";
                } else {
                    echo "<div class='mensaje error'>Error al eliminar el costo</div>";
                }
                $stmt->close();
            }
            break;
            
        case 'editar':
            // Procesar edición
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $descripcion = trim($_POST['descripcion']);
            $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
            $categoria = in_array($_POST['categoria'], ['alimenticio', 'salud', 'general']) ? $_POST['categoria'] : 'general';
            $fecha = DateTime::createFromFormat('Y-m-d', $_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
            
            if ($id === false || $id <= 0) {
                echo "<div class='mensaje error'>ID inválido</div>";
            } elseif (empty($descripcion)) {
                echo "<div class='mensaje error'>La descripción es requerida</div>";
            } elseif ($monto === false || $monto <= 0) {
                echo "<div class='mensaje error'>Monto inválido</div>";
            } else {
                $sql = "UPDATE costos_ganaderos SET descripcion = ?, monto = ?, categoria = ?, fecha = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdssi", $descripcion, $monto, $categoria, $fecha, $id);
                
                if ($stmt->execute()) {
                    echo "<div class='mensaje exito'>Costo actualizado exitosamente</div>";
                } else {
                    echo "<div class='mensaje error'>Error al actualizar el costo</div>";
                }
                $stmt->close();
            }
            break;
    }
}

// Obtener datos para el resumen
$sql_resumen = "SELECT 
    categoria,
    COUNT(*) as total_registros,
    SUM(monto) as total_monto
FROM costos_ganaderos 
WHERE activo = TRUE 
GROUP BY categoria";
$resultado_resumen = $conn->query($sql_resumen);

// Obtener total general
$sql_total = "SELECT SUM(monto) as total_general FROM costos_ganaderos WHERE activo = TRUE";
$resultado_total = $conn->query($sql_total);
$total_general = $resultado_total->fetch_assoc()['total_general'];

// Obtener costos por mes
$sql_mensual = "SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as mes,
    SUM(monto) as total_mes
FROM costos_ganaderos 
WHERE activo = TRUE 
GROUP BY DATE_FORMAT(fecha, '%Y-%m')
ORDER BY mes DESC
LIMIT 12";
$resultado_mensual = $conn->query($sql_mensual);

// Obtener todos los costos activos
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

$sql_costos = "SELECT * FROM costos_ganaderos WHERE activo = TRUE";
$params = [];
$tipos = "";

if ($filtro_categoria) {
    $sql_costos .= " AND categoria = ?";
    $params[] = $filtro_categoria;
    $tipos .= "s";
}

if ($filtro_fecha_inicio) {
    $sql_costos .= " AND fecha >= ?";
    $params[] = $filtro_fecha_inicio;
    $tipos .= "s";
}

if ($filtro_fecha_fin) {
    $sql_costos .= " AND fecha <= ?";
    $params[] = $filtro_fecha_fin;
    $tipos .= "s";
}

$sql_costos .= " ORDER BY fecha DESC";

$stmt_costos = $conn->prepare($sql_costos);
if ($params) {
    $stmt_costos->bind_param($tipos, ...$params);
}
$stmt_costos->execute();
$resultado_costos = $stmt_costos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Costos Ganaderos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .seccion { margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; }
        .seccion h2 { margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .btn-eliminar { background: #dc3545; }
        .btn-eliminar:hover { background: #c82333; }
        .btn-editar { background: #28a745; }
        .btn-editar:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .mensaje { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .exito { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .resumen { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .tarjeta { border: 1px solid #ddd; padding: 15px; text-align: center; }
        .tarjeta h3 { margin: 0 0 10px 0; }
        .tarjeta .numero { font-size: 24px; font-weight: bold; color: #007bff; }
        .filtros { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Control de Costos Ganaderos</h1>
        
        <!-- Resumen de Costos -->
        <div class="seccion">
            <h2>Resumen de Costos</h2>
            <div class="resumen">
                <div class="tarjeta">
                    <h3>Total General</h3>
                    <div class="numero">$<?php echo number_format($total_general, 2); ?></div>
                </div>
                <?php while ($fila = $resultado_resumen->fetch_assoc()): ?>
                <div class="tarjeta">
                    <h3><?php echo ucfirst($fila['categoria']); ?></h3>
                    <div class="numero">$<?php echo number_format($fila['total_monto'], 2); ?></div>
                    <p><?php echo $fila['total_registros']; ?> registros</p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Costos por Mes -->
        <div class="seccion">
            <h2>Costos por Mes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado_mensual->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('F Y', strtotime($fila['mes'] . '-01')); ?></td>
                        <td>$<?php echo number_format($fila['total_mes'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario para Agregar Nuevo Costo -->
        <div class="seccion">
            <h2>Agregar Nuevo Costo</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar">
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="monto">Monto:</label>
                    <input type="number" name="monto" id="monto" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <select name="categoria" id="categoria" required>
                        <option value="">Seleccionar categoría</option>
                        <option value="alimenticio">Alimenticio</option>
                        <option value="salud">Salud</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <button type="submit">Agregar Costo</button>
            </form>
        </div>

        <!-- Filtros -->
        <div class="seccion">
            <h2>Filtrar Costos</h2>
            <form method="GET">
                <div class="filtros">
                    <div class="form-group">
                        <label for="categoria_filtro">Categoría:</label>
                        <select name="categoria" id="categoria_filtro">
                            <option value="">Todas las categorías</option>
                            <option value="alimenticio" <?php echo $filtro_categoria == 'alimenticio' ? 'selected' : ''; ?>>Alimenticio</option>
                            <option value="salud" <?php echo $filtro_categoria == 'salud' ? 'selected' : ''; ?>>Salud</option>
                            <option value="general" <?php echo $filtro_categoria == 'general' ? 'selected' : ''; ?>>General</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $filtro_fecha_inicio; ?>">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo $filtro_fecha_fin; ?>">
                    </div>
                </div>
                <button type="submit">Filtrar</button>
                <a href="?"><button type="button">Limpiar Filtros</button></a>
            </form>
        </div>

        <!-- Lista de Costos -->
        <div class="seccion">
            <h2>Lista de Costos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Monto</th>
                        <th>Categoría</th>
                        <th>Fecha</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($costo = $resultado_costos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $costo['id']; ?></td>
                        <td><?php echo htmlspecialchars($costo['descripcion']); ?></td>
                        <td>$<?php echo number_format($costo['monto'], 2); ?></td>
                        <td><?php echo ucfirst($costo['categoria']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($costo['fecha'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($costo['fecha_registro'])); ?></td>
                        <td>
                            <button class="btn-editar" onclick="editarCosto(<?php echo htmlspecialchars(json_encode($costo)); ?>">
                                Editar
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de eliminar este costo?')">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $costo['id']; ?>">
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Editar Costo -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Editar Costo</h2>
            <form method="POST" id="formEditar">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_descripcion">Descripción:</label>
                    <textarea name="descripcion" id="edit_descripcion" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_monto">Monto:</label>
                    <input type="number" name="monto" id="edit_monto" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit_categoria">Categoría:</label>
                    <select name="categoria" id="edit_categoria" required>
                        <option value="alimenticio">Alimenticio</option>
                        <option value="salud">Salud</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_fecha">Fecha:</label>
                    <input type="date" name="fecha" id="edit_fecha" required>
                </div>
                <button type="submit">Actualizar Costo</button>
                <button type="button" onclick="cerrarModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function editarCosto(costo) {
            document.getElementById('edit_id').value = costo.id;
            document.getElementById('edit_descripcion').value = costo.descripcion;
            document.getElementById('edit_monto').value = costo.monto;
            document.getElementById('edit_categoria').value = costo.categoria;
            document.getElementById('edit_fecha').value = costo.fecha;
            document.getElementById('modalEditar').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            var modal = document.getElementById('modalEditar');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>