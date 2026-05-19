-- ============================================================
-- Instalador de base de datos — Gestor de Tareas Bunglebuild S.L.
-- Ejecutar: mysql -u root -p gestor_tareas < database/install.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestor_tareas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestor_tareas;

-- Borrar tablas si existen (para reinstalación limpia)
DROP TABLE IF EXISTS tareas;
DROP TABLE IF EXISTS usuarios;

-- ── TABLA USUARIOS ──────────────────────────────────────────
CREATE TABLE usuarios (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(150)                    NOT NULL,
    usuario    VARCHAR(80)                     NOT NULL UNIQUE,
    password   VARCHAR(255)                    NOT NULL,
    rol        ENUM('admin', 'operario')       NOT NULL DEFAULT 'operario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── TABLA TAREAS ────────────────────────────────────────────
CREATE TABLE tareas (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nif                     VARCHAR(20)         NOT NULL,
    persona_contacto        VARCHAR(150)        NOT NULL,
    telefono                VARCHAR(30)         NULL,
    email                   VARCHAR(150)        NOT NULL,
    descripcion             TEXT                NOT NULL,
    anotaciones_anteriores  TEXT                NULL,
    anotaciones_posteriores TEXT                NULL,
    direccion               VARCHAR(255)        NULL,
    poblacion               VARCHAR(100)        NULL,
    cp                      CHAR(5)             NULL,
    provincia               CHAR(2)             NULL,
    estado                  ENUM('B','P','R','C') NOT NULL DEFAULT 'P'
                            COMMENT 'B=Bloqueada P=Pendiente R=Realizada C=Cancelada',
    fecha_creacion          TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    fecha_realizacion       DATE                NULL,
    operario                VARCHAR(80)         NULL,
    fichero_resumen         VARCHAR(255)        NULL
                            COMMENT 'Nombre del fichero adjunto guardado en storage/app/adjuntos/',
    CONSTRAINT fk_operario FOREIGN KEY (operario) REFERENCES usuarios(usuario)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── DATOS INICIALES ─────────────────────────────────────────
INSERT INTO usuarios (nombre, usuario, password, rol) VALUES
    ('Administrador',   'admin',    'admin123',    'admin'),
    ('Juan Operario',   'operario', 'oper123',     'operario'),
    ('María García',    'maria',    'maria123',    'operario'),
    ('Pedro Martínez',  'pedro',    'pedro123',    'admin');

INSERT INTO tareas (nif, persona_contacto, telefono, email, descripcion, anotaciones_anteriores, direccion, poblacion, cp, provincia, estado, fecha_realizacion, operario) VALUES
    ('12345678A', 'Ana López',    '600111222', 'ana@cliente.com',   'Reparación tejado nave industrial',   'Grietas en esquina norte',       'C/ Industrial 12', 'Sevilla',  '41001', '41', 'P', '2025-03-15', 'pedro'),
    ('87654321B', 'Carlos Ruiz',  '600333444', 'carlos@obra.com',  'Instalación ventanas planta baja',    'Medidas: 1.20x1.80 x4 ventanas', 'Av. Principal 45', 'Cádiz',    '11001', '11', 'P', '2025-03-20', 'operario'),
    ('11223344C', 'Lucía Pérez',  '600555666', 'lucia@hogar.com',  'Reforma baño completo',               'Quitar alicatado antiguo',        'C/ Mayor 3, 2ºB',  'Almería',  '04001', '04', 'R', '2025-02-10', 'maria'),
    ('99887766D', 'Roberto Mora', '600777888', 'roberto@piso.com', 'Pintura interior 3 habitaciones',    'Color a elegir por cliente',      'Av. Del Mar 7',    'Málaga',   '29001', '29', 'C', '2025-03-01', NULL),
    ('55443322E', 'Eva Fernández','600999000', 'eva@local.com',    'Reparación urgente tubería rota',     'Fuga detectada en cocina',        'C/ Olivos 22',     'Huelva',   '21001', '21', 'P', '2025-03-25', 'operario');
