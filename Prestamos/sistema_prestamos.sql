-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-12-2025 a las 21:47:15
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_prestamos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asesores_departamentos`
--

CREATE TABLE `asesores_departamentos` (
  `id` int(11) NOT NULL,
  `asesor_id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asesores_departamentos`
--

INSERT INTO `asesores_departamentos` (`id`, `asesor_id`, `departamento_id`, `disponible`) VALUES
(1, 2, 1, 1),
(2, 2, 2, 1),
(3, 2, 3, 1),
(4, 2, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_api_logs`
--

CREATE TABLE `chatbot_api_logs` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_data` text DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `status_code` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_flujo`
--

CREATE TABLE `chatbot_flujo` (
  `id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `tipo` enum('texto','opcion') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chatbot_flujo`
--

INSERT INTO `chatbot_flujo` (`id`, `pregunta`, `tipo`) VALUES
(1, '✨ Bienvenido a Préstamo Líder ✨\n\n¿En qué podemos ayudarte hoy?', 'opcion'),
(2, 'Por favor, ingresa tu DNI (sin puntos ni espacios):', 'texto'),
(3, '¿Con cuál de estas opciones te identificás?', 'opcion'),
(4, 'Ingresa tu número de teléfono con código de área (ejemplo: 1127390105):', 'texto'),
(5, 'Ingresa tu email:', 'texto'),
(6, 'Selecciona tu banco:', 'opcion');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_opciones`
--

CREATE TABLE `chatbot_opciones` (
  `id` int(11) NOT NULL,
  `flujo_id` int(11) NOT NULL,
  `texto` varchar(255) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chatbot_opciones`
--

INSERT INTO `chatbot_opciones` (`id`, `flujo_id`, `texto`, `departamento_id`) VALUES
(1, 1, '💰 Solicitar un préstamo', 1),
(2, 1, '💬 Hablar con un asesor', 4),
(3, 1, '📊 Consultar mi cuenta', 2),
(4, 1, '❓ Información general', 3),
(5, 3, '👨‍💼 Tengo Recibo de Sueldo (Empleado registrado)', 1),
(6, 3, '👴 Soy jubilado, pensionado o retirado', 1),
(7, 3, '👶 Cobro Asignación Universal por Hijo (AUH)', 1),
(8, 3, '👨‍👩‍👧 Cobro Asignaciones Familiares (SUAF)', 1),
(9, 3, '📋 Soy Monotributista o Responsable Inscripto', 1),
(10, 3, '⚠️ Trabajo sin estar registrado (en negro)', 1),
(11, 6, 'Banco Nación', 1),
(12, 6, 'Banco Provincia', 1),
(13, 6, 'Banco Galicia', 1),
(14, 6, 'Banco Santander', 1),
(15, 6, 'Banco Macro', 1),
(16, 6, 'Banco BBVA', 1),
(17, 6, 'Banco Credicoop', 1),
(18, 6, 'Banco Supervielle', 1),
(19, 6, 'Banco Patagonia', 1),
(20, 6, 'Banco Hipotecario', 1),
(21, 6, 'Otro banco', 1),
(22, 6, 'Banco Ciudad', 1),
(23, 6, 'Banco Comafi', 1),
(24, 6, 'Banco ICBC', 1),
(25, 6, 'Brubank', 1),
(26, 6, 'Naranja X', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_respuestas`
--

CREATE TABLE `chatbot_respuestas` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta` text NOT NULL,
  `opcion_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chatbot_respuestas`
--

INSERT INTO `chatbot_respuestas` (`id`, `chat_id`, `pregunta_id`, `respuesta`, `opcion_id`, `fecha`) VALUES
(1, 44, 1, '💰 Solicitar un préstamo', 1, '2025-12-10 20:36:31'),
(2, 45, 1, '💰 Solicitar un préstamo', 1, '2025-12-10 20:39:58'),
(3, 46, 1, '💰 Solicitar un préstamo', 1, '2025-12-10 20:42:01'),
(4, 47, 1, '💰 Solicitar un préstamo', 1, '2025-12-10 20:44:07'),
(5, 48, 1, '💰 Solicitar un préstamo', 1, '2025-12-10 20:45:10'),
(6, 49, 1, '💰 Solicitar un préstamo', 1, '2025-12-10 20:46:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cuil_validado` varchar(13) DEFAULT NULL,
  `nombre_validado` varchar(255) DEFAULT NULL,
  `situacion_laboral` varchar(100) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `fecha_solicitud_prestamo` datetime DEFAULT NULL,
  `api_enviado` tinyint(1) DEFAULT 0,
  `api_respuesta` text DEFAULT NULL,
  `asesor_id` int(11) DEFAULT NULL,
  `departamento_id` int(11) NOT NULL,
  `origen` enum('chatbot','manual') DEFAULT 'chatbot',
  `estado` enum('abierto','esperando_asesor','en_conversacion','cerrado') NOT NULL DEFAULT 'esperando_asesor',
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `ultima_lectura_asesor` timestamp NULL DEFAULT NULL,
  `ip_cliente` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `mac_dispositivo` varchar(64) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chats`
--

INSERT INTO `chats` (`id`, `cliente_id`, `cuil_validado`, `nombre_validado`, `situacion_laboral`, `banco`, `fecha_solicitud_prestamo`, `api_enviado`, `api_respuesta`, `asesor_id`, `departamento_id`, `origen`, `estado`, `fecha_inicio`, `fecha_cierre`, `ultima_lectura_asesor`, `ip_cliente`, `user_agent`, `mac_dispositivo`, `ciudad`, `pais`, `latitud`, `longitud`) VALUES
(39, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 18:21:27', NULL, '2025-12-10 20:37:07', '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(40, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:31:46', NULL, '2025-12-10 20:37:07', '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(41, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:33:59', NULL, '2025-12-10 20:37:07', '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(42, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:34:40', NULL, '2025-12-10 20:37:07', '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(43, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:35:07', NULL, '2025-12-10 20:37:07', '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(44, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:36:14', NULL, '2025-12-10 20:37:07', '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(45, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:39:55', NULL, NULL, '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(46, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:41:59', NULL, NULL, '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(47, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:44:06', NULL, NULL, '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(48, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:45:08', NULL, NULL, '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000),
(49, 18, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-10 20:46:30', NULL, NULL, '186.128.106.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Don Torcuato', 'Argentina', -34.5041000, -58.6374000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_archivos`
--

CREATE TABLE `chat_archivos` (
  `id` int(11) NOT NULL,
  `mensaje_id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `tamano` int(11) NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_detalles`
--

CREATE TABLE `clientes_detalles` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dni` varchar(50) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(120) DEFAULT NULL,
  `provincia` varchar(120) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes_detalles`
--

INSERT INTO `clientes_detalles` (`id`, `usuario_id`, `dni`, `direccion`, `ciudad`, `provincia`, `fecha_nacimiento`) VALUES
(16, 18, '41727387', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`) VALUES
(1, 'Finanzas'),
(2, 'Cobranza'),
(3, 'Soporte Técnico'),
(4, 'Atención General');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `emisor` enum('cliente','asesor','bot') NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `tiene_archivo` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `chat_id`, `emisor`, `usuario_id`, `mensaje`, `tiene_archivo`, `fecha`) VALUES
(182, 44, 'cliente', 18, '💰 Solicitar un préstamo', 0, '2025-12-10 20:36:32'),
(183, 44, 'asesor', NULL, 'hola', 0, '2025-12-10 20:37:11'),
(184, 45, 'cliente', 18, '💰 Solicitar un préstamo', 0, '2025-12-10 20:39:58'),
(185, 46, 'cliente', 18, '💰 Solicitar un préstamo', 0, '2025-12-10 20:42:01'),
(186, 47, 'cliente', 18, '💰 Solicitar un préstamo', 0, '2025-12-10 20:44:08'),
(187, 48, 'cliente', 18, '💰 Solicitar un préstamo', 0, '2025-12-10 20:45:10'),
(188, 49, 'cliente', 18, '💰 Solicitar un préstamo', 0, '2025-12-10 20:46:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `cuotas` int(11) DEFAULT NULL,
  `tasa_interes` decimal(5,2) DEFAULT NULL,
  `monto_total` decimal(10,2) DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado','cancelado','finalizado') DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_aprobacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos_pagos`
--

CREATE TABLE `prestamos_pagos` (
  `id` int(11) NOT NULL,
  `prestamo_id` int(11) NOT NULL,
  `cuota_num` int(11) NOT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha_pago` timestamp NULL DEFAULT NULL,
  `estado` enum('pendiente','pagado','atrasado') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','asesor','cliente') NOT NULL DEFAULT 'cliente',
  `estado` enum('activo','inactivo','suspendido') DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `telefono`, `password`, `rol`, `estado`, `fecha_registro`) VALUES
(2, 'Juan', 'Pérez', 'asesor@prestamolider.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asesor', 'activo', '2025-12-09 02:33:06'),
(14, 'Thomas Cano', NULL, 'temp_1765319659_6594@cliente.com', '1127390105', '$2y$10$G.A.YId4Va//kxS4/gzCZ.EfVTBHokQuvIFAGmAAHmu2VKkXS8TDa', 'cliente', 'activo', '2025-12-09 22:34:19'),
(15, 'Admin', 'Principal', 'admin@prestamolider.com', '01127390105', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo', '2025-12-09 23:22:51'),
(16, 'Thomas Cano', NULL, 'temp_1765376639_1234@cliente.com', '1127390105', '$2y$10$EtXSBkiKKuDgtJ3MLb/XfeMgS875iWSlBDztfZlt1KA6QinhT5K/S', 'cliente', 'activo', '2025-12-10 14:23:59'),
(17, 'juan', NULL, 'temp_1765380689_7688@cliente.com', '2132324', '$2y$10$AnFsg2mqBK0/9lKH/OkLguMc3UpvWyUMssOL4nhIMwArqZZGF2rCy', 'cliente', 'activo', '2025-12-10 15:31:29'),
(18, 'Thomas Cano', NULL, 'temp_1765390887_7175@cliente.com', '1127390105', '$2y$10$MbIgHC34ByTVZAwbesurZOwPixMUR5v8aEj7ZqQXySrDjtW2MSyuC', 'cliente', 'activo', '2025-12-10 18:21:27');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asesores_departamentos`
--
ALTER TABLE `asesores_departamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asesor_id` (`asesor_id`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `chatbot_api_logs`
--
ALTER TABLE `chatbot_api_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`);

--
-- Indices de la tabla `chatbot_flujo`
--
ALTER TABLE `chatbot_flujo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chatbot_opciones`
--
ALTER TABLE `chatbot_opciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flujo_id` (`flujo_id`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `chatbot_respuestas`
--
ALTER TABLE `chatbot_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pregunta_id` (`pregunta_id`),
  ADD KEY `opcion_id` (`opcion_id`),
  ADD KEY `idx_chatbot_respuestas_chat` (`chat_id`,`pregunta_id`);

--
-- Indices de la tabla `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `asesor_id` (`asesor_id`),
  ADD KEY `departamento_id` (`departamento_id`),
  ADD KEY `idx_chats_cuil` (`cuil_validado`),
  ADD KEY `idx_chats_estado` (`estado`);

--
-- Indices de la tabla `chat_archivos`
--
ALTER TABLE `chat_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mensaje_id` (`mensaje_id`),
  ADD KEY `chat_id` (`chat_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_clientes_usuario` (`usuario_id`);

--
-- Indices de la tabla `clientes_detalles`
--
ALTER TABLE `clientes_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `prestamos_pagos`
--
ALTER TABLE `prestamos_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prestamo_id` (`prestamo_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asesores_departamentos`
--
ALTER TABLE `asesores_departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `chatbot_api_logs`
--
ALTER TABLE `chatbot_api_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chatbot_flujo`
--
ALTER TABLE `chatbot_flujo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `chatbot_opciones`
--
ALTER TABLE `chatbot_opciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `chatbot_respuestas`
--
ALTER TABLE `chatbot_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `chat_archivos`
--
ALTER TABLE `chat_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes_detalles`
--
ALTER TABLE `clientes_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prestamos_pagos`
--
ALTER TABLE `prestamos_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asesores_departamentos`
--
ALTER TABLE `asesores_departamentos`
  ADD CONSTRAINT `asesores_departamentos_ibfk_1` FOREIGN KEY (`asesor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `asesores_departamentos_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`);

--
-- Filtros para la tabla `chatbot_api_logs`
--
ALTER TABLE `chatbot_api_logs`
  ADD CONSTRAINT `chatbot_api_logs_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chatbot_opciones`
--
ALTER TABLE `chatbot_opciones`
  ADD CONSTRAINT `chatbot_opciones_ibfk_1` FOREIGN KEY (`flujo_id`) REFERENCES `chatbot_flujo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chatbot_opciones_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`);

--
-- Filtros para la tabla `chatbot_respuestas`
--
ALTER TABLE `chatbot_respuestas`
  ADD CONSTRAINT `chatbot_respuestas_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chatbot_respuestas_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `chatbot_flujo` (`id`),
  ADD CONSTRAINT `chatbot_respuestas_ibfk_3` FOREIGN KEY (`opcion_id`) REFERENCES `chatbot_opciones` (`id`);

--
-- Filtros para la tabla `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`asesor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`);

--
-- Filtros para la tabla `chat_archivos`
--
ALTER TABLE `chat_archivos`
  ADD CONSTRAINT `chat_archivos_ibfk_1` FOREIGN KEY (`mensaje_id`) REFERENCES `mensajes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_archivos_ibfk_2` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes_detalles`
--
ALTER TABLE `clientes_detalles`
  ADD CONSTRAINT `clientes_detalles_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `prestamos_pagos`
--
ALTER TABLE `prestamos_pagos`
  ADD CONSTRAINT `prestamos_pagos_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
