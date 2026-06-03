-- =============================================
-- Crear tabla de modelos de equipos GPS
-- =============================================

CREATE TABLE IF NOT EXISTS modelos_equipos_gps (
    id_modelo_gps INT AUTO_INCREMENT PRIMARY KEY,
    nombre_modelo VARCHAR(100) NOT NULL,
    marca VARCHAR(100) DEFAULT NULL,
    descripcion TEXT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Agregar columna id_modelo_gps a la tabla unidades
-- =============================================

ALTER TABLE unidades 
ADD COLUMN id_modelo_gps INT DEFAULT NULL AFTER idcategoria,
ADD CONSTRAINT fk_unidades_modelo_gps 
    FOREIGN KEY (id_modelo_gps) REFERENCES modelos_equipos_gps(id_modelo_gps)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- =============================================
-- Datos de ejemplo (opcional)
-- =============================================

INSERT INTO modelos_equipos_gps (nombre_modelo, marca, descripcion) VALUES
('GT06N', 'Concox', 'Rastreador GPS compacto con corte de motor'),
('TK103B', 'Coban', 'Rastreador GPS vehicular con control remoto'),
('ST-901', 'SinoTrack', 'Mini rastreador GPS resistente al agua'),
('GT02A', 'Concox', 'Rastreador GPS económico con micrófono'),
('TK303F', 'Coban', 'Rastreador GPS con detección de combustible'),
('JM-VL01', 'JimiIoT', 'Rastreador 4G LTE con cámara'),
('FMB920', 'Teltonika', 'Rastreador GPS profesional con Bluetooth'),
('GV20', 'Queclink', 'Rastreador GPS para vehículos pesados');
