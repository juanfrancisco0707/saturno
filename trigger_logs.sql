-- 1. Crear tabla de logs para registrar las instalaciones duplicadas
CREATE TABLE IF NOT EXISTS logs_instalaciones_duplicadas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_unidad INT NOT NULL,
    id_servicio_nuevo INT NOT NULL,
    fecha_intento DATETIME DEFAULT CURRENT_TIMESTAMP,
    mensaje VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Eliminar el trigger si ya existe para poder recrearlo
DROP TRIGGER IF EXISTS trg_log_instalacion_duplicada;

-- 3. Crear el trigger que se dispara al insertar una instalación
DELIMITER //

CREATE TRIGGER trg_log_instalacion_duplicada
AFTER INSERT ON instalaciones
FOR EACH ROW
BEGIN
    DECLARE v_id_unidad INT;
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;
    DECLARE v_existe INT DEFAULT 0;

    -- Obtener datos del servicio asociado a la nueva instalación
    SELECT id_unidad, fecha_inicio, fecha_fin 
    INTO v_id_unidad, v_fecha_inicio, v_fecha_fin
    FROM servicios 
    WHERE id_servicio = NEW.id_servicio;

    -- Verificar si existe OTRA instalación para la misma unidad que caiga en el mismo periodo
    IF v_id_unidad IS NOT NULL AND v_fecha_inicio IS NOT NULL THEN
        SELECT COUNT(*) INTO v_existe
        FROM instalaciones i
        INNER JOIN servicios s ON i.id_servicio = s.id_servicio
        WHERE s.id_unidad = v_id_unidad
          AND i.id_instalacion != NEW.id_instalacion
          AND i.fecha_instalacion >= v_fecha_inicio
          AND (v_fecha_fin IS NULL OR i.fecha_instalacion <= v_fecha_fin);

        -- Si se encuentra una instalación previa en el mismo periodo, se genera el log
        IF v_existe > 0 THEN
            INSERT INTO logs_instalaciones_duplicadas (id_unidad, id_servicio_nuevo, mensaje)
            VALUES (v_id_unidad, NEW.id_servicio, 'ALERTA: Se registró una instalación solapada (ya existía otra en el mismo periodo de servicio).');
            
            -- NOTA: En MySQL/MariaDB, si se usa SIGNAL SQLSTATE para bloquear/cancelar el INSERT, 
            -- también se deshará (rollback) el INSERT en la tabla de logs porque comparten transacción.
            -- Por eso, este trigger solo audita/registra el evento permitiendo el flujo, 
            -- o se apoya en el backend de PHP para el bloqueo en la interfaz.
        END IF;
    END IF;
END;
//
DELIMITER ;
