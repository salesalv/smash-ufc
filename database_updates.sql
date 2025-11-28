-- Actualizaciones de base de datos para ABM
-- SMASH UFC

USE smash_ufc;

-- Agregar campo de rol a usuarios
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS rol ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario' AFTER password;

-- Crear tabla de noticias
CREATE TABLE IF NOT EXISTS noticias (
    id INT(11) NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    imagen_url VARCHAR(500) NULL,
    autor_id INT(11) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_autor (autor_id),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de comentarios
CREATE TABLE IF NOT EXISTS comentarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    noticia_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (noticia_id) REFERENCES noticias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_noticia (noticia_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de peleadores favoritos (ELIMINADA - ya no se usa)
-- CREATE TABLE IF NOT EXISTS peleadores_favoritos (
--     id INT(11) NOT NULL AUTO_INCREMENT,
--     usuario_id INT(11) NOT NULL,
--     nombre_peleador VARCHAR(255) NOT NULL,
--     categoria VARCHAR(100) NULL,
--     notas TEXT NULL,
--     fecha_agregado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     PRIMARY KEY (id),
--     FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
--     INDEX idx_usuario (usuario_id),
--     UNIQUE KEY unique_usuario_peleador (usuario_id, nombre_peleador)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear usuario admin por defecto (opcional)
-- Contraseña: "admin123" (cambiar después del primer login)
-- INSERT INTO usuarios (nombre, email, password, rol) VALUES 
-- ('Administrador', 'admin@ufc.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

