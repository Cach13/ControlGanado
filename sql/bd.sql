CREATE DATABASE IF NOT EXISTS control_ganado;
USE control_ganado;

-- Tabla principal para crías activas
CREATE TABLE crias_activas (
    arete VARCHAR(20) PRIMARY KEY,
    sexo ENUM('M', 'H') NOT NULL,
    kg_compra DECIMAL(6,2) NOT NULL,
    precio_compra DECIMAL(8,2) NOT NULL,
    total_compra DECIMAL(10,2) AS (kg_compra * precio_compra) STORED,
    fecha_compra DATE NOT NULL
);

-- Tabla para crías vendidas
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arete VARCHAR(20),
    sexo ENUM('M', 'H'),
    kg_compra DECIMAL(6,2),
    precio_compra DECIMAL(8,2),
    total_compra DECIMAL(10,2),
    fecha_compra DATE,
    kg_venta DECIMAL(6,2),
    diferencia_kg DECIMAL(6,2),
    costo_kg DECIMAL(6,2),
    total_costo DECIMAL(10,2),
    venta DECIMAL(10,2),
    gasto_traslado DECIMAL(10,2),
    venta_menos_gasto DECIMAL(10,2),
    utilidad DECIMAL(10,2),
    parte_correspondiente DECIMAL(10,2),
    seguro DECIMAL(10,2),
    total_ganancia DECIMAL(10,2),
    fecha_venta DATE DEFAULT CURRENT_DATE
);