-- ============================================================
-- CREAR BASE DE DATOS
-- ============================================================
CREATE DATABASE IF NOT EXISTS sistema_prestamos CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sistema_prestamos;

-- ============================================================
-- TABLA: usuarios
-- admins | asesores | clientes
-- ============================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    email VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','asesor','cliente') NOT NULL DEFAULT 'cliente',
    estado ENUM('activo','inactivo','suspendido') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLA: clientes_detalles
-- datos extendidos del cliente
-- ============================================================
CREATE TABLE clientes_detalles (
    id INT AUTO_INCREMENT PRIMARY PRIMARY KEY,
    usuario_id INT NOT NULL,
    dni VARCHAR(50),
    direccion TEXT,
    ciudad VARCHAR(120),
    provincia VARCHAR(120),
    fecha_nacimiento DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLA: prestamos
-- solicitudes de préstamos
-- ============================================================
CREATE TABLE prestamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    monto DECIMAL(10,2),
    cuotas INT,
    tasa_interes DECIMAL(5,2),
    monto_total DECIMAL(10,2),
    estado ENUM('pendiente','aprobado','rechazado','cancelado','finalizado') DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
);

-- ============================================================
-- TABLA: prestamos_pagos
-- cuotas individuales del préstamo
-- ============================================================
CREATE TABLE prestamos_pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prestamo_id INT NOT NULL,
    cuota_num INT NOT NULL,
    monto DECIMAL(10,2),
    fecha_pago TIMESTAMP NULL,
    estado ENUM('pendiente','pagado','atrasado') DEFAULT 'pendiente',
    FOREIGN KEY (prestamo_id) REFERENCES prestamos(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLA: departamentos
-- áreas donde trabajan asesores
-- ============================================================
CREATE TABLE departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Insertamos los departamentos base
INSERT INTO departamentos (nombre) VALUES
('Finanzas'),
('Cobranza'),
('Soporte Técnico'),
('Atención General');

-- ============================================================
-- TABLA: asesores_departamentos
-- disponibilidad de los asesores
-- ============================================================
CREATE TABLE asesores_departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asesor_id INT NOT NULL,
    departamento_id INT NOT NULL,
    disponible TINYINT(1) DEFAULT 1,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id),
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
);

-- ============================================================
-- TABLA: chats
-- conversaciones entre clientes y asesores
-- ============================================================
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NULL,
    departamento_id INT NOT NULL,
    origen ENUM('chatbot','manual') DEFAULT 'chatbot',
    estado ENUM('abierto','esperando_asesor','en_conversacion','cerrado') DEFAULT 'esperando_asesor',
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id),
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
);

-- ============================================================
-- TABLA: mensajes
-- mensajes dentro de cada chat
-- ============================================================
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    emisor ENUM('cliente','asesor','bot') NOT NULL,
    usuario_id INT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ============================================================
-- TABLA: chatbot_flujo
-- preguntas del chatbot
-- ============================================================
CREATE TABLE chatbot_flujo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL,
    tipo ENUM('opcion','texto') DEFAULT 'opcion'
);

-- ============================================================
-- TABLA: chatbot_opciones
-- opciones de respuestas que elige el cliente
-- ============================================================
CREATE TABLE chatbot_opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flujo_id INT NOT NULL,
    texto VARCHAR(255) NOT NULL,
    departamento_id INT NOT NULL,
    FOREIGN KEY (flujo_id) REFERENCES chatbot_flujo(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
);

-- ============================================================
-- TABLA: logs
-- historial del sistema
-- ============================================================
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(255),
    detalle TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
