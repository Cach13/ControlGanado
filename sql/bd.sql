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

-- Nueva tabla para control de costos
CREATE TABLE costos_ganaderos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    categoria ENUM('alimenticio', 'salud', 'general') NOT NULL,
    fecha DATE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Insertar algunos datos de ejemplo
INSERT INTO costos_ganaderos (descripcion, monto, categoria, fecha) VALUES
('Compra de alimento balanceado', 2500.00, 'alimenticio', '2024-01-15'),
('Vacunación contra fiebre aftosa', 800.00, 'salud', '2024-01-18'),
('Reparación de cercas', 1200.00, 'general', '2024-01-20'),
('Suplementos vitamínicos', 450.00, 'alimenticio', '2024-01-22'),
('Consulta veterinaria', 300.00, 'salud', '2024-01-25'),
('Mantenimiento de bebederos', 600.00, 'general', '2024-01-28');