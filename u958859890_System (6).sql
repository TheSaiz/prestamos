-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 15-12-2025 a las 17:25:31
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u958859890_System`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Estructura de tabla para la tabla `asesores_perfil`
--

CREATE TABLE `asesores_perfil` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `celular` varchar(30) DEFAULT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `telegram` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `asesores_perfil`
--

INSERT INTO `asesores_perfil` (`id`, `usuario_id`, `foto_perfil`, `celular`, `whatsapp`, `telegram`, `instagram`, `facebook`, `tiktok`, `created_at`, `updated_at`) VALUES
(1, 2, 'uploads/perfiles/perfil_2_1765597596.jpg', '1127390105', '+5493764177398', 'demo', 'https://www.instagram.com/davidescurdia?igsh=bHZoa3kyb3NudDN5', '', 'https://www.tiktok.com/@davidescurdiaok?_r=1&_t=ZM-92FMM0hbNJD', '2025-12-13 03:45:03', '2025-12-15 16:28:27');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_flujo`
--

CREATE TABLE `chatbot_flujo` (
  `id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `tipo` enum('texto','opcion') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `chatbot_flujo`
--

INSERT INTO `chatbot_flujo` (`id`, `pregunta`, `tipo`) VALUES
(1, '✨ Préstamo Líder no es solo un préstamo, es tener con quién contar. ✨\n\n¿En qué podemos ayudarte hoy?', 'opcion'),
(2, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 'texto'),
(3, '¿Con cuál de estas opciones te identificás?', 'opcion'),
(4, 'Ingresá tu código de área (ejemplo +549):', 'texto'),
(5, 'Ingresá tu número de teléfono (sin el 15):', 'texto'),
(7, 'Seleccioná tu banco:', 'opcion'),
(8, 'Ingresá tu correo electrónico:', 'texto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_opciones`
--

CREATE TABLE `chatbot_opciones` (
  `id` int(11) NOT NULL,
  `flujo_id` int(11) NOT NULL,
  `texto` varchar(255) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `chatbot_opciones`
--

INSERT INTO `chatbot_opciones` (`id`, `flujo_id`, `texto`, `departamento_id`) VALUES
(1, 1, '💰 Solicitar un préstamo', 1),
(2, 1, '💬 Hablar con un asesor', 4),
(3, 1, '📊 Consultar mi cuenta', 2),
(4, 1, '❓ Información general', 3),
(5, 3, 'Tengo Recibo de Sueldo', 1),
(6, 3, 'Soy jubilado, pensionado o retirado', 1),
(7, 3, 'Cobro Asignación Universal por Hijo (AUH)', 1),
(8, 3, 'Cobro Asignaciones Familiares (SUAF)', 1),
(9, 3, 'Soy Monotributista o Responsable Inscripto', 1),
(10, 3, 'Trabajo sin estar registrado (Negro)', 1),
(11, 7, 'Banco Nación', 1),
(12, 7, 'Banco Provincia', 1),
(13, 7, 'Banco Galicia', 1),
(14, 7, 'Banco Santander', 1),
(15, 7, 'Banco Macro', 1),
(16, 7, 'Banco BBVA', 1),
(17, 7, 'Banco Credicoop', 1),
(18, 7, 'Banco Supervielle', 1),
(19, 7, 'Banco Patagonia', 1),
(20, 7, 'Banco Hipotecario', 1),
(21, 7, 'Banco Ciudad', 1),
(22, 7, 'Banco Comafi', 1),
(23, 7, 'Banco ICBC', 1),
(24, 7, 'Brubank', 1),
(25, 7, 'Naranja X', 1),
(26, 7, 'Otro banco', 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `chatbot_respuestas`
--

INSERT INTO `chatbot_respuestas` (`id`, `chat_id`, `pregunta_id`, `respuesta`, `opcion_id`, `fecha`) VALUES
(231, 253, 1, '💬 Hablar con un asesor', 2, '2025-12-15 00:25:20'),
(232, 253, 2, 'CANO ESPINDOLA ALVARO THOMAS', NULL, '2025-12-15 00:25:24'),
(233, 253, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-15 00:25:27'),
(234, 253, 4, '11', NULL, '2025-12-15 00:25:29'),
(235, 253, 5, '1127390105', NULL, '2025-12-15 00:25:34'),
(236, 253, 7, 'Brubank', 24, '2025-12-15 00:25:46'),
(237, 253, 8, 'thomec86@gmail.com', NULL, '2025-12-15 00:25:52'),
(238, 254, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 14:46:53'),
(239, 254, 2, 'FONCECA VILMA ELIZABETH', NULL, '2025-12-15 14:47:05'),
(240, 254, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-15 14:47:07'),
(241, 254, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 14:52:58'),
(242, 254, 2, 'FONCECA VILMA ELIZABETH', NULL, '2025-12-15 14:53:11'),
(243, 254, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-15 14:53:13'),
(244, 254, 4, '376', NULL, '2025-12-15 14:53:21'),
(245, 254, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 14:54:23'),
(246, 254, 2, 'FONCECA VILMA ELIZABETH', NULL, '2025-12-15 14:54:37'),
(247, 254, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-15 14:54:39'),
(248, 254, 4, '376', NULL, '2025-12-15 14:55:03'),
(249, 254, 5, '37641773988', NULL, '2025-12-15 14:55:15'),
(250, 254, 7, 'Banco Nación', 11, '2025-12-15 14:55:19'),
(251, 254, 1, 'vilma@hotmail.com', NULL, '2025-12-15 14:56:38'),
(252, 254, 2, '💰 Solicitar un préstamo', 1, '2025-12-15 14:57:38'),
(253, 254, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-15 14:57:50'),
(254, 254, 4, '376', NULL, '2025-12-15 14:57:59'),
(255, 254, 5, '3764177398', NULL, '2025-12-15 14:58:19'),
(256, 254, 7, 'Banco Patagonia', 19, '2025-12-15 14:58:21'),
(257, 254, 8, 'vilmaefonceca@hotmail.com', NULL, '2025-12-15 14:58:38'),
(258, 255, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 16:11:46'),
(259, 255, 2, 'FLORES YOHANA MACARENA', NULL, '2025-12-15 16:12:01'),
(260, 255, 3, 'Cobro Asignaciones Familiares (SUAF)', 8, '2025-12-15 16:13:00'),
(261, 255, 4, '376', NULL, '2025-12-15 16:13:20'),
(262, 255, 5, '3764565656', NULL, '2025-12-15 16:13:28'),
(263, 255, 7, 'Banco ICBC', 23, '2025-12-15 16:13:31'),
(264, 255, 8, 'floresyoha973@gmail.com', NULL, '2025-12-15 16:13:44'),
(265, 256, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 16:29:45'),
(266, 256, 2, 'ROLON NORMA VERONICA', NULL, '2025-12-15 16:29:56'),
(267, 256, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-15 16:29:59'),
(268, 256, 4, '376', NULL, '2025-12-15 16:30:05'),
(269, 256, 5, '3764565656', NULL, '2025-12-15 16:30:12'),
(270, 256, 7, 'Banco ICBC', 23, '2025-12-15 16:30:13'),
(271, 256, 8, 'prueba@gmail.com', NULL, '2025-12-15 16:30:29'),
(272, 257, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 16:39:24'),
(273, 257, 2, 'FLORES YOHANA MACARENA', NULL, '2025-12-15 16:39:29'),
(274, 257, 3, 'Cobro Asignaciones Familiares (SUAF)', 8, '2025-12-15 16:39:32'),
(275, 257, 4, '376', NULL, '2025-12-15 16:39:37'),
(276, 257, 5, '376456565656', NULL, '2025-12-15 16:39:46'),
(277, 257, 7, 'Banco Comafi', 22, '2025-12-15 16:39:47'),
(278, 257, 8, 'prueba@gmail.com', NULL, '2025-12-15 16:40:02'),
(279, 258, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 16:53:54'),
(280, 258, 2, 'DA LUZ HECTOR ADRIAN', NULL, '2025-12-15 16:53:58'),
(281, 258, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-15 16:53:59'),
(282, 258, 4, '376', NULL, '2025-12-15 16:54:10'),
(283, 258, 5, '376456565678', NULL, '2025-12-15 16:54:18'),
(284, 258, 7, 'Banco Ciudad', 21, '2025-12-15 16:54:20'),
(285, 258, 8, 'prueba@gmail.com', NULL, '2025-12-15 16:55:09'),
(286, 259, 1, '💰 Solicitar un préstamo', 1, '2025-12-15 16:56:11'),
(287, 259, 2, 'ESCALANTE LOURDES ESTHER', NULL, '2025-12-15 16:56:15'),
(288, 259, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-15 16:56:17');

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
  `estado` enum('pendiente','esperando_asesor','en_conversacion','cerrado') NOT NULL DEFAULT 'pendiente',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `chats`
--

INSERT INTO `chats` (`id`, `cliente_id`, `cuil_validado`, `nombre_validado`, `situacion_laboral`, `banco`, `fecha_solicitud_prestamo`, `api_enviado`, `api_respuesta`, `asesor_id`, `departamento_id`, `origen`, `estado`, `fecha_inicio`, `fecha_cierre`, `ultima_lectura_asesor`, `ip_cliente`, `user_agent`, `mac_dispositivo`, `ciudad`, `pais`, `latitud`, `longitud`) VALUES
(253, 64, '20417273876', 'CANO ESPINDOLA ALVARO THOMAS', 'MONOTRIBUTISTA', 'Brubank', NULL, 0, NULL, 2, 1, 'chatbot', 'cerrado', '2025-12-15 00:25:16', '2025-12-15 15:20:16', '2025-12-15 15:14:38', '2802:8011:3024:e101:3125:92b9:8122:728c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(254, 65, '27358401843', 'FONCECA VILMA ELIZABETH', 'MONOTRIBUTISTA', 'Banco Patagonia', NULL, 0, NULL, 2, 1, 'chatbot', 'cerrado', '2025-12-15 14:46:49', '2025-12-15 15:20:24', NULL, '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000),
(255, 66, '27414194805', 'FLORES YOHANA MACARENA', 'COBRA_SUAF', 'Banco ICBC', NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-15 16:11:43', NULL, '2025-12-15 17:20:09', '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000),
(256, 67, '27237312355', 'ROLON NORMA VERONICA', 'COBRA_AUH', 'Banco ICBC', NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-15 16:29:43', NULL, '2025-12-15 16:58:37', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000),
(257, 68, '27414194805', 'FLORES YOHANA MACARENA', 'COBRA_SUAF', 'Banco Comafi', NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-15 16:39:16', NULL, '2025-12-15 17:20:09', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000),
(258, 69, '20360930182', 'DA LUZ HECTOR ADRIAN', 'JUBILADO', 'Banco Ciudad', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', '2025-12-15 16:53:51', NULL, NULL, '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000),
(259, 70, '27390444600', 'ESCALANTE LOURDES ESTHER', 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', '2025-12-15 16:56:09', NULL, NULL, '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `chat_archivos`
--

INSERT INTO `chat_archivos` (`id`, `mensaje_id`, `chat_id`, `nombre_original`, `nombre_guardado`, `tipo_mime`, `tamano`, `ruta`, `fecha_subida`) VALUES
(45, 847, 254, 'micbu (2) (1) (1) (2).pdf', '694022a7441b3_1765810855.pdf', 'application/pdf', 31666, 'uploads/694022a7441b3_1765810855.pdf', '2025-12-15 15:00:55'),
(46, 848, 254, 'micbu (2) (1) (1) (2).pdf', '694022e3c2a8a_1765810915.pdf', 'application/pdf', 31666, 'uploads/694022e3c2a8a_1765810915.pdf', '2025-12-15 15:01:55'),
(47, 849, 254, 'WhatsApp Image 2025-12-05 at 07.53.16.jpeg', '694023001710d_1765810944.jpeg', 'image/jpeg', 83916, 'uploads/694023001710d_1765810944.jpeg', '2025-12-15 15:02:24'),
(48, 850, 254, 'prestamo-logo (1).png', '69402314689fe_1765810964.png', 'image/png', 55017, 'uploads/69402314689fe_1765810964.png', '2025-12-15 15:02:44'),
(49, 851, 254, 'pintura.xlsx', '69402325df2e3_1765810981.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 11768, 'uploads/69402325df2e3_1765810981.xlsx', '2025-12-15 15:03:01'),
(50, 852, 254, 'LISTADO DE BAJAS AGREGADAS POR BANCO (15).xlsx', '6940233a3416c_1765811002.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 440239, 'uploads/6940233a3416c_1765811002.xlsx', '2025-12-15 15:03:22'),
(51, 853, 254, 'audio_1765811021558.webm', '6940234e0b724_1765811022.webm', 'audio/webm', 81440, 'uploads/6940234e0b724_1765811022.webm', '2025-12-15 15:03:42'),
(52, 854, 254, 'audio_1765811049684.webm', '69402369dc193_1765811049.webm', 'audio/webm', 62120, 'uploads/69402369dc193_1765811049.webm', '2025-12-15 15:04:09'),
(53, 904, 256, 'audio_1765819358234.webm', '694043de812c6_1765819358.webm', 'audio/webm', 41834, 'uploads/694043de812c6_1765819358.webm', '2025-12-15 17:22:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_transferencias`
--

CREATE TABLE `chat_transferencias` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `asesor_origen` int(11) NOT NULL,
  `asesor_destino` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `fecha` datetime DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chat_transferencias`
--

INSERT INTO `chat_transferencias` (`id`, `chat_id`, `asesor_origen`, `asesor_destino`, `estado`, `fecha`, `fecha_respuesta`) VALUES
(2, 164, 2, 27, 'aceptada', '2025-12-12 17:18:38', '2025-12-12 17:18:50'),
(3, 164, 27, 2, 'aceptada', '2025-12-12 17:20:35', '2025-12-12 17:21:05'),
(4, 174, 2, 27, 'aceptada', '2025-12-12 18:19:53', '2025-12-12 18:20:02'),
(5, 254, 2, 27, 'aceptada', '2025-12-15 15:11:29', '2025-12-15 15:11:39'),
(6, 254, 27, 2, 'aceptada', '2025-12-15 15:13:49', '2025-12-15 15:13:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `codigo_area` varchar(10) DEFAULT NULL,
  `telefono` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `usuario_id`, `email`, `codigo_area`, `telefono`, `created_at`, `updated_at`) VALUES
(7, 64, 'thomec86@gmail.com', NULL, '1127390105', '2025-12-15 00:25:20', '2025-12-15 00:25:52'),
(8, 65, 'vilmaefonceca@hotmail.com', NULL, '3764177398', '2025-12-15 14:46:53', '2025-12-15 14:58:38'),
(9, 66, 'floresyoha973@gmail.com', NULL, '3764565656', '2025-12-15 16:11:46', '2025-12-15 16:13:44'),
(10, 67, 'prueba@gmail.com', NULL, '3764565656', '2025-12-15 16:29:45', '2025-12-15 16:30:29'),
(11, 68, 'temp_1765816764_5592@cliente.com', NULL, '376456565656', '2025-12-15 16:39:24', '2025-12-15 16:39:46'),
(12, 69, 'temp_1765817634_9168@cliente.com', NULL, '376456565678', '2025-12-15 16:53:54', '2025-12-15 16:54:18'),
(13, 70, 'temp_1765817771_6007@cliente.com', NULL, '', '2025-12-15 16:56:11', '2025-12-15 16:56:11');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `clientes_detalles`
--

INSERT INTO `clientes_detalles` (`id`, `usuario_id`, `dni`, `direccion`, `ciudad`, `provincia`, `fecha_nacimiento`) VALUES
(44, 64, '41727387', NULL, NULL, NULL, NULL),
(45, 65, '35.840.184', NULL, NULL, NULL, NULL),
(48, 66, '41419480', NULL, NULL, NULL, NULL),
(49, 67, '23731235', NULL, NULL, NULL, NULL),
(50, 68, '41419480', NULL, NULL, NULL, NULL),
(51, 69, '36093018', NULL, NULL, NULL, NULL),
(52, 70, '39044460', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `chat_id`, `emisor`, `usuario_id`, `mensaje`, `tiene_archivo`, `fecha`) VALUES
(816, 253, 'cliente', 64, '💬 Hablar con un asesor', 0, '2025-12-15 00:25:20'),
(817, 253, 'cliente', 64, 'CANO ESPINDOLA ALVARO THOMAS', 0, '2025-12-15 00:25:24'),
(818, 253, 'cliente', 64, 'Soy Monotributista o Responsable Inscripto', 0, '2025-12-15 00:25:27'),
(819, 253, 'cliente', 64, '11', 0, '2025-12-15 00:25:29'),
(820, 253, 'cliente', 64, '1127390105', 0, '2025-12-15 00:25:34'),
(821, 253, 'cliente', 64, 'Brubank', 0, '2025-12-15 00:25:46'),
(822, 253, 'cliente', 64, 'thomec86@gmail.com', 0, '2025-12-15 00:25:52'),
(823, 253, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-15 00:25:53'),
(824, 253, 'asesor', NULL, 'https://www.youtube.com', 0, '2025-12-15 00:45:17'),
(825, 254, 'cliente', 65, '💰 Solicitar un préstamo', 0, '2025-12-15 14:46:53'),
(826, 254, 'cliente', 65, 'FONCECA VILMA ELIZABETH', 0, '2025-12-15 14:47:05'),
(827, 254, 'cliente', 65, 'Soy Monotributista o Responsable Inscripto', 0, '2025-12-15 14:47:07'),
(828, 254, 'cliente', 65, '💰 Solicitar un préstamo', 0, '2025-12-15 14:52:58'),
(829, 254, 'cliente', 65, 'FONCECA VILMA ELIZABETH', 0, '2025-12-15 14:53:11'),
(830, 254, 'cliente', 65, 'Soy Monotributista o Responsable Inscripto', 0, '2025-12-15 14:53:13'),
(831, 254, 'cliente', 65, '376', 0, '2025-12-15 14:53:21'),
(832, 254, 'cliente', 65, '💰 Solicitar un préstamo', 0, '2025-12-15 14:54:23'),
(833, 254, 'cliente', 65, 'FONCECA VILMA ELIZABETH', 0, '2025-12-15 14:54:37'),
(834, 254, 'cliente', 65, 'Soy Monotributista o Responsable Inscripto', 0, '2025-12-15 14:54:39'),
(835, 254, 'cliente', 65, '376', 0, '2025-12-15 14:55:03'),
(836, 254, 'cliente', 65, '37641773988', 0, '2025-12-15 14:55:15'),
(837, 254, 'cliente', 65, 'Banco Nación', 0, '2025-12-15 14:55:19'),
(838, 254, 'cliente', 65, 'vilma@hotmail.com', 0, '2025-12-15 14:56:38'),
(839, 254, 'cliente', 65, '💰 Solicitar un préstamo', 0, '2025-12-15 14:57:38'),
(840, 254, 'cliente', 65, 'Soy Monotributista o Responsable Inscripto', 0, '2025-12-15 14:57:50'),
(841, 254, 'cliente', 65, '376', 0, '2025-12-15 14:57:59'),
(842, 254, 'cliente', 65, '3764177398', 0, '2025-12-15 14:58:19'),
(843, 254, 'cliente', 65, 'Banco Patagonia', 0, '2025-12-15 14:58:21'),
(844, 254, 'cliente', 65, 'vilmaefonceca@hotmail.com', 0, '2025-12-15 14:58:38'),
(845, 254, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-15 14:58:38'),
(846, 254, 'asesor', NULL, 'buenos dias', 0, '2025-12-15 15:00:29'),
(847, 254, 'cliente', NULL, 'Archivo adjunto: micbu (2) (1) (1) (2).pdf', 1, '2025-12-15 15:00:55'),
(848, 254, 'asesor', NULL, '', 1, '2025-12-15 15:01:55'),
(849, 254, 'asesor', NULL, '', 1, '2025-12-15 15:02:24'),
(850, 254, 'cliente', NULL, 'Archivo adjunto: prestamo-logo (1).png', 1, '2025-12-15 15:02:44'),
(851, 254, 'cliente', NULL, 'Archivo adjunto: pintura.xlsx', 1, '2025-12-15 15:03:01'),
(852, 254, 'asesor', NULL, '', 1, '2025-12-15 15:03:22'),
(853, 254, 'asesor', NULL, '', 1, '2025-12-15 15:03:42'),
(854, 254, 'cliente', NULL, 'Archivo adjunto: audio_1765811049684.webm', 1, '2025-12-15 15:04:09'),
(855, 254, 'asesor', NULL, 'buenos dias, me derivaron tu caso', 0, '2025-12-15 15:11:53'),
(856, 254, 'cliente', NULL, 'ok o', 0, '2025-12-15 15:12:23'),
(857, 254, 'asesor', NULL, 'buenas', 0, '2025-12-15 15:14:04'),
(858, 254, 'cliente', NULL, 'ok', 0, '2025-12-15 15:14:21'),
(859, 254, 'cliente', NULL, 'hola', 0, '2025-12-15 15:20:30'),
(860, 255, 'cliente', 66, '💰 Solicitar un préstamo', 0, '2025-12-15 16:11:46'),
(861, 255, 'cliente', 66, 'FLORES YOHANA MACARENA', 0, '2025-12-15 16:12:01'),
(862, 255, 'cliente', 66, 'Cobro Asignaciones Familiares (SUAF)', 0, '2025-12-15 16:13:00'),
(863, 255, 'cliente', 66, '376', 0, '2025-12-15 16:13:20'),
(864, 255, 'cliente', 66, '3764565656', 0, '2025-12-15 16:13:28'),
(865, 255, 'cliente', 66, 'Banco ICBC', 0, '2025-12-15 16:13:31'),
(866, 255, 'cliente', 66, 'floresyoha973@gmail.com', 0, '2025-12-15 16:13:44'),
(867, 255, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-15 16:13:44'),
(868, 255, 'asesor', NULL, 'hola', 0, '2025-12-15 16:15:56'),
(869, 255, 'cliente', NULL, 'quiero un credto', 0, '2025-12-15 16:16:10'),
(870, 255, 'asesor', NULL, 'si', 0, '2025-12-15 16:20:13'),
(871, 255, 'asesor', NULL, 'ok', 0, '2025-12-15 16:29:01'),
(872, 256, 'cliente', 67, '💰 Solicitar un préstamo', 0, '2025-12-15 16:29:45'),
(873, 256, 'cliente', 67, 'ROLON NORMA VERONICA', 0, '2025-12-15 16:29:56'),
(874, 256, 'cliente', 67, 'Cobro Asignación Universal por Hijo (AUH)', 0, '2025-12-15 16:29:59'),
(875, 256, 'cliente', 67, '376', 0, '2025-12-15 16:30:05'),
(876, 256, 'cliente', 67, '3764565656', 0, '2025-12-15 16:30:12'),
(877, 256, 'cliente', 67, 'Banco ICBC', 0, '2025-12-15 16:30:13'),
(878, 256, 'cliente', 67, 'prueba@gmail.com', 0, '2025-12-15 16:30:29'),
(879, 256, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-15 16:30:30'),
(880, 256, 'asesor', NULL, 'hola', 0, '2025-12-15 16:31:45'),
(881, 256, 'asesor', NULL, 'hace tu home baning siguiendo estos pasoshttps://www.youtube.com/watch?v=bymK-t4quCQ&embeds_referring_euri=https%3A%2F%2Fprestamolider.com%2F&embeds_referring_origin=https%3A%2F%2Fprestamolider.com&source_ve_path=Mjg2NjY', 0, '2025-12-15 16:38:28'),
(882, 257, 'cliente', 68, '💰 Solicitar un préstamo', 0, '2025-12-15 16:39:24'),
(883, 257, 'cliente', 68, 'FLORES YOHANA MACARENA', 0, '2025-12-15 16:39:29'),
(884, 257, 'cliente', 68, 'Cobro Asignaciones Familiares (SUAF)', 0, '2025-12-15 16:39:32'),
(885, 257, 'cliente', 68, '376', 0, '2025-12-15 16:39:37'),
(886, 257, 'cliente', 68, '376456565656', 0, '2025-12-15 16:39:46'),
(887, 257, 'cliente', 68, 'Banco Comafi', 0, '2025-12-15 16:39:47'),
(888, 257, 'cliente', 68, 'prueba@gmail.com', 0, '2025-12-15 16:40:02'),
(889, 257, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-15 16:40:02'),
(890, 257, 'asesor', NULL, 'hola', 0, '2025-12-15 16:40:15'),
(891, 257, 'cliente', NULL, 'quiero un credito', 0, '2025-12-15 16:41:36'),
(892, 257, 'asesor', NULL, 'o', 0, '2025-12-15 16:41:48'),
(893, 258, 'cliente', 69, '💰 Solicitar un préstamo', 0, '2025-12-15 16:53:54'),
(894, 258, 'cliente', 69, 'DA LUZ HECTOR ADRIAN', 0, '2025-12-15 16:53:58'),
(895, 258, 'cliente', 69, 'Soy jubilado, pensionado o retirado', 0, '2025-12-15 16:53:59'),
(896, 258, 'cliente', 69, '376', 0, '2025-12-15 16:54:10'),
(897, 258, 'cliente', 69, '376456565678', 0, '2025-12-15 16:54:18'),
(898, 258, 'cliente', 69, 'Banco Ciudad', 0, '2025-12-15 16:54:20'),
(899, 258, 'cliente', 69, 'prueba@gmail.com', 0, '2025-12-15 16:55:09'),
(900, 258, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-15 16:55:10'),
(901, 259, 'cliente', 70, '💰 Solicitar un préstamo', 0, '2025-12-15 16:56:11'),
(902, 259, 'cliente', 70, 'ESCALANTE LOURDES ESTHER', 0, '2025-12-15 16:56:15'),
(903, 259, 'cliente', 70, 'Soy jubilado, pensionado o retirado', 0, '2025-12-15 16:56:17'),
(904, 256, 'cliente', NULL, 'Archivo adjunto: audio_1765819358234.webm', 1, '2025-12-15 17:22:38');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `codigo_area` varchar(10) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','asesor','cliente') NOT NULL DEFAULT 'cliente',
  `estado` enum('activo','inactivo','suspendido') DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `codigo_area`, `telefono`, `password`, `rol`, `estado`, `fecha_registro`) VALUES
(2, 'Juan', 'Pérez', 'asesor@prestamolider.com', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asesor', 'activo', '2025-12-09 05:33:06'),
(15, 'Admin', 'Principal', 'admin@prestamolider.com', NULL, '01127390105', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo', '2025-12-10 02:22:51'),
(27, 'Ramon', 'Pérez', 'asesor2@prestamolider.com', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asesor', 'activo', '2025-12-09 05:33:06'),
(64, 'CANO ESPINDOLA ALVARO THOMAS', NULL, 'thomec86@gmail.com', NULL, '1127390105', '$2y$10$FdoZAcxANxG10LbmiBiB.udTU8cfGDFz7w1HsXUgWiDaPfp/z6pv2', 'cliente', 'activo', '2025-12-15 00:25:16'),
(65, 'FONCECA VILMA ELIZABETH', NULL, 'vilmaefonceca@hotmail.com', NULL, '3764177398', '$2y$10$fZGrqW9rXxQi9aqZwq3er.GMHsqYrSmmcHY8WNtXARcae75A1H1sO', 'cliente', 'activo', '2025-12-15 14:46:49'),
(66, 'FLORES YOHANA MACARENA', NULL, 'floresyoha973@gmail.com', NULL, '3764565656', '$2y$10$3a1hmNPIiVREww7FKHUtQu3fraSlcDNiHkdMynuIY/vLX1h1splGG', 'cliente', 'activo', '2025-12-15 16:11:43'),
(67, 'ROLON NORMA VERONICA', NULL, 'prueba@gmail.com', NULL, '3764565656', '$2y$10$XbaVXecwx6p3w3oFuH8vH.2/Wr/IcMK61qY3M0FaqyW.i66jWsOb2', 'cliente', 'activo', '2025-12-15 16:29:43'),
(68, 'FLORES YOHANA MACARENA', NULL, 'temp_1765816756_7364@cliente.com', NULL, '376456565656', '$2y$10$5waNeBwUuvHcR.CgGwnCTOpRjT/dH/hgRdF.yvDsw77PEx7.hUfq.', 'cliente', 'activo', '2025-12-15 16:39:16'),
(69, 'DA LUZ HECTOR ADRIAN', NULL, 'temp_1765817631_1439@cliente.com', NULL, '376456565678', '$2y$10$L1yVjwTWwoHIn/IY6u5Te.Lw7H6DqXah53jOuuvBXj93lnE1A9mYK', 'cliente', 'activo', '2025-12-15 16:53:51'),
(70, 'ESCALANTE LOURDES ESTHER', NULL, 'temp_1765817769_8450@cliente.com', NULL, NULL, '$2y$10$Z.kMjukwJM/iDzz1JDVvoOlM55XT59rn9mqX9Hzoz5lhrUlib3GPq', 'cliente', 'activo', '2025-12-15 16:56:09');

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
-- Indices de la tabla `asesores_perfil`
--
ALTER TABLE `asesores_perfil`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

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
  ADD KEY `chatbot_opciones_ibfk_1` (`flujo_id`),
  ADD KEY `chatbot_opciones_ibfk_2` (`departamento_id`);

--
-- Indices de la tabla `chatbot_respuestas`
--
ALTER TABLE `chatbot_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chatbot_respuestas_chat` (`chat_id`,`pregunta_id`),
  ADD KEY `chatbot_respuestas_ibfk_2` (`pregunta_id`),
  ADD KEY `chatbot_respuestas_ibfk_3` (`opcion_id`);

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
-- Indices de la tabla `chat_transferencias`
--
ALTER TABLE `chat_transferencias`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `uk_cliente_usuario` (`usuario_id`),
  ADD UNIQUE KEY `uniq_clientes_detalles_usuario` (`usuario_id`),
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
-- AUTO_INCREMENT de la tabla `asesores_perfil`
--
ALTER TABLE `asesores_perfil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `chatbot_api_logs`
--
ALTER TABLE `chatbot_api_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chatbot_flujo`
--
ALTER TABLE `chatbot_flujo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `chatbot_opciones`
--
ALTER TABLE `chatbot_opciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `chatbot_respuestas`
--
ALTER TABLE `chatbot_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT de la tabla `chat_archivos`
--
ALTER TABLE `chat_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `chat_transferencias`
--
ALTER TABLE `chat_transferencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `clientes_detalles`
--
ALTER TABLE `clientes_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=905;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

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
-- Filtros para la tabla `asesores_perfil`
--
ALTER TABLE `asesores_perfil`
  ADD CONSTRAINT `asesores_perfil_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

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
