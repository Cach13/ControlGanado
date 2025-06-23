<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "control_ganado";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establece codificación en UTF-8
$conn->set_charset("utf8");
?>
