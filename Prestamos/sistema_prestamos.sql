-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-12-2025 a las 03:40:18
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
-- Estructura de tabla para la tabla `chatbot_flujo`
--

CREATE TABLE `chatbot_flujo` (
  `id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `tipo` enum('opcion','texto') DEFAULT 'opcion'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chatbot_flujo`
--

INSERT INTO `chatbot_flujo` (`id`, `pregunta`, `tipo`) VALUES
(1, '¿En qué podemos ayudarte hoy?', 'opcion'),
(2, '¿Cuál es tu nombre completo?', 'texto'),
(3, '¿Cuál es tu email?', 'texto'),
(4, '¿En qué podemos ayudarte hoy?', 'opcion'),
(5, '¿Cuál es tu email?', 'texto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_opciones`
--

CREATE TABLE `chatbot_opciones` (
  `id` int(11) NOT NULL,
  `flujo_id` int(11) NOT NULL,
  `texto` varchar(255) NOT NULL,
  `departamento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chatbot_opciones`
--

INSERT INTO `chatbot_opciones` (`id`, `flujo_id`, `texto`, `departamento_id`) VALUES
(1, 1, 'Solicitar un préstamo', 1),
(2, 1, 'Consultar mi préstamo', 2),
(3, 1, 'Soporte técnico', 3),
(4, 1, 'Otra consulta', 4),
(5, 1, 'Solicitar un préstamo', 1),
(6, 1, 'Consultar mi préstamo actual', 2),
(7, 1, 'Problema técnico', 3),
(8, 1, 'Otra consulta', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `asesor_id` int(11) DEFAULT NULL,
  `departamento_id` int(11) NOT NULL,
  `origen` enum('chatbot','manual') DEFAULT 'chatbot',
  `estado` enum('abierto','esperando_asesor','en_conversacion','cerrado') DEFAULT 'esperando_asesor',
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `ultima_lectura_asesor` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chats`
--

INSERT INTO `chats` (`id`, `cliente_id`, `asesor_id`, `departamento_id`, `origen`, `estado`, `fecha_inicio`, `fecha_cierre`, `ultima_lectura_asesor`) VALUES
(1, 1, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-08 20:56:30', NULL, NULL),
(2, 3, NULL, 3, 'chatbot', 'esperando_asesor', '2025-12-09 02:37:02', NULL, NULL);

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
(1, 1, '41727387', NULL, NULL, NULL, NULL),
(2, 3, '41727387', NULL, NULL, NULL, NULL);

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
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `chat_id`, `emisor`, `usuario_id`, `mensaje`, `fecha`) VALUES
(1, 1, 'cliente', 1, 'Otra consulta', '2025-12-08 20:57:07'),
(2, 1, 'cliente', 1, 'Solicitar un préstamo', '2025-12-08 20:57:17'),
(3, 2, 'cliente', 3, 'Solicitar un préstamo', '2025-12-09 02:37:04'),
(4, 2, 'cliente', 3, 'alvaro thomas cano espindola', '2025-12-09 02:37:11'),
(5, 2, 'cliente', 3, 'thomec86@gmail.com', '2025-12-09 02:37:17'),
(6, 2, 'cliente', 3, 'prestamo', '2025-12-09 02:37:22'),
(7, 2, 'cliente', 3, 'Soporte técnico', '2025-12-09 02:37:30');

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
(1, 'Thomas Cano', NULL, 'temp_1765227390_6086@cliente.com', '1127390105', '$2y$10$HJ22Z7RAUdeVcMWylqh59ePEEgFnCJhZKCi4/CSNUo.FX2INvcaBS', 'cliente', 'activo', '2025-12-08 20:56:30'),
(2, 'Juan', 'Pérez', 'asesor@prestamolider.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asesor', 'activo', '2025-12-09 02:33:06'),
(3, 'Thomas Cano', NULL, 'temp_1765247822_8031@cliente.com', '1127390105', '$2y$10$0/NrUVhg5m5TnuN2pxePke1Z229DZB..iUFnOzb0VXdP6uN5hG0Ua', 'cliente', 'activo', '2025-12-09 02:37:02');

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
-- Indices de la tabla `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `asesor_id` (`asesor_id`),
  ADD KEY `departamento_id` (`departamento_id`);

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
-- AUTO_INCREMENT de la tabla `chatbot_flujo`
--
ALTER TABLE `chatbot_flujo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `chatbot_opciones`
--
ALTER TABLE `chatbot_opciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clientes_detalles`
--
ALTER TABLE `clientes_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Filtros para la tabla `chatbot_opciones`
--
ALTER TABLE `chatbot_opciones`
  ADD CONSTRAINT `chatbot_opciones_ibfk_1` FOREIGN KEY (`flujo_id`) REFERENCES `chatbot_flujo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chatbot_opciones_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`);

--
-- Filtros para la tabla `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`asesor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`);

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
