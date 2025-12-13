-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 13-12-2025 a las 05:46:37
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
(1, 2, 'uploads/perfiles/perfil_2_1765597596.jpg', '1127390105', '', 'demo', 'demo 2', 'demo 3', 'demo4', '2025-12-13 03:45:03', '2025-12-13 04:30:40');

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
(174, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 27, 1, 'chatbot', 'en_conversacion', '2025-12-12 18:07:39', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(175, 33, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-12 18:13:08', NULL, '2025-12-13 04:07:36', '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000),
(176, 34, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-12 18:15:07', NULL, '2025-12-13 04:07:40', '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000),
(177, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:06:58', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(178, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:09:26', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(179, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:20:16', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(180, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:29:34', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(181, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:30:57', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(182, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:34:29', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(183, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:46:34', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(184, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:54:34', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(185, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:57:32', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(186, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 04:58:47', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(187, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:00:21', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(188, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:03:49', NULL, '2025-12-13 05:35:54', '186.128.95.160', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(189, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:11:13', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(190, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:13:28', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(191, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:13:45', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(192, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:14:48', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000),
(193, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:22:31', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(194, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:25:44', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(195, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:26:40', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(196, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:27:38', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(197, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:28:29', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(198, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:33:35', NULL, '2025-12-13 05:35:54', '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000),
(199, 32, NULL, NULL, NULL, NULL, NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-13 05:34:43', NULL, NULL, '2802:8011:3069:3f01:dd49:4678:8310:63fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Tigre', 'Argentina', -34.4231000, -58.5830000);

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
(4, 174, 2, 27, 'aceptada', '2025-12-12 18:19:53', '2025-12-12 18:20:02');

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
(29, 32, '41727387', NULL, NULL, NULL, NULL),
(30, 33, '42263089', NULL, NULL, NULL, NULL),
(31, 34, '31832621', NULL, NULL, NULL, NULL);

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
(395, 174, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-12 18:07:43'),
(396, 174, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-12 18:07:45'),
(397, 174, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-12 18:07:46'),
(398, 174, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-12 18:07:47'),
(399, 174, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-12 18:07:48'),
(400, 174, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-12 18:07:52'),
(401, 174, 'bot', NULL, 'Ingresaste  1127390105, ¿es correcto?', 0, '2025-12-12 18:07:56'),
(402, 174, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-12 18:07:58'),
(403, 174, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-12 18:07:59'),
(404, 174, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-12 18:08:05'),
(405, 174, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-12 18:08:12'),
(406, 177, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:07:03'),
(407, 177, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:07:06'),
(408, 177, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:07:07'),
(409, 177, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:07:08'),
(410, 177, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:07:09'),
(411, 177, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:07:12'),
(412, 177, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:07:16'),
(413, 177, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:07:19'),
(414, 177, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:07:20'),
(415, 177, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:07:25'),
(416, 177, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:07:28'),
(417, 177, 'asesor', NULL, 'Hola!', 0, '2025-12-13 04:07:46'),
(418, 177, 'cliente', NULL, 'Hola!', 0, '2025-12-13 04:07:54'),
(419, 178, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:09:29'),
(420, 178, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:09:32'),
(421, 178, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:09:32'),
(422, 178, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:09:34'),
(423, 178, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:09:35'),
(424, 178, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:09:36'),
(425, 178, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:09:40'),
(426, 178, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:09:42'),
(427, 178, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:09:43'),
(428, 178, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:09:48'),
(429, 178, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:09:49'),
(430, 178, 'asesor', NULL, 'Hola!', 0, '2025-12-13 04:09:56'),
(431, 178, 'cliente', NULL, 'hola!', 0, '2025-12-13 04:10:06'),
(432, 179, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:20:18'),
(433, 179, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:20:24'),
(434, 179, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:20:24'),
(435, 179, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:20:25'),
(436, 179, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:20:26'),
(437, 179, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:20:28'),
(438, 179, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:20:30'),
(439, 179, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:20:31'),
(440, 179, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:20:32'),
(441, 179, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:20:37'),
(442, 179, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:20:38'),
(443, 180, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:29:36'),
(444, 180, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:29:39'),
(445, 180, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:29:39'),
(446, 180, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:29:40'),
(447, 180, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:29:41'),
(448, 180, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:29:42'),
(449, 180, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:29:45'),
(450, 180, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:29:46'),
(451, 180, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:29:48'),
(452, 180, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:29:54'),
(453, 180, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:29:55'),
(454, 180, 'asesor', NULL, 'Hola', 0, '2025-12-13 04:30:06'),
(455, 180, 'cliente', NULL, 'Hola', 0, '2025-12-13 04:30:10'),
(456, 181, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:30:58'),
(457, 181, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:31:01'),
(458, 181, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:31:02'),
(459, 181, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:31:03'),
(460, 181, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:31:04'),
(461, 181, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:31:05'),
(462, 181, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:31:07'),
(463, 181, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:31:08'),
(464, 181, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:31:09'),
(465, 181, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:31:15'),
(466, 181, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:31:16'),
(467, 181, 'asesor', NULL, 'Hola', 0, '2025-12-13 04:31:26'),
(468, 181, 'cliente', NULL, 'Hola', 0, '2025-12-13 04:31:29'),
(469, 182, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:34:31'),
(470, 182, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:34:34'),
(471, 182, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:34:34'),
(472, 182, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:34:35'),
(473, 182, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:34:36'),
(474, 182, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:34:37'),
(475, 182, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:34:39'),
(476, 182, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:34:41'),
(477, 182, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:34:42'),
(478, 182, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:34:48'),
(479, 182, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:34:49'),
(480, 182, 'asesor', NULL, 'Hola', 0, '2025-12-13 04:34:55'),
(481, 182, 'cliente', NULL, 'Hola', 0, '2025-12-13 04:34:59'),
(482, 183, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:46:36'),
(483, 183, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:46:40'),
(484, 183, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:46:41'),
(485, 183, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:46:42'),
(486, 183, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:46:43'),
(487, 183, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:46:45'),
(488, 183, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:46:46'),
(489, 183, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:46:48'),
(490, 183, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:46:49'),
(491, 183, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:46:54'),
(492, 183, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:46:55'),
(493, 183, 'asesor', NULL, 'asdsadsadsa', 0, '2025-12-13 04:47:04'),
(494, 183, 'cliente', NULL, 'Como te encuentras hoy?', 0, '2025-12-13 04:47:18'),
(495, 183, 'asesor', NULL, 'Bien y vos?', 0, '2025-12-13 04:47:26'),
(496, 184, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:54:40'),
(497, 184, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:54:43'),
(498, 184, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:54:44'),
(499, 184, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:54:45'),
(500, 184, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:54:46'),
(501, 184, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:54:47'),
(502, 184, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:54:50'),
(503, 184, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:55:36'),
(504, 184, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:55:36'),
(505, 184, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:55:41'),
(506, 184, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:55:49'),
(507, 185, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:57:34'),
(508, 185, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:57:36'),
(509, 185, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:57:37'),
(510, 185, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:57:38'),
(511, 185, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:57:39'),
(512, 185, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:57:40'),
(513, 185, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:57:42'),
(514, 186, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 04:58:49'),
(515, 186, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 04:58:52'),
(516, 186, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 04:58:52'),
(517, 186, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 04:58:54'),
(518, 186, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 04:58:55'),
(519, 186, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 04:58:56'),
(520, 186, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 04:58:59'),
(521, 186, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 04:59:00'),
(522, 186, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:59:01'),
(523, 186, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 04:59:03'),
(524, 186, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 04:59:05'),
(525, 187, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 05:00:23'),
(526, 187, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 05:00:25'),
(527, 187, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 05:00:26'),
(528, 187, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 05:00:27'),
(529, 187, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 05:00:29'),
(530, 187, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 05:00:30'),
(531, 187, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 05:00:32'),
(532, 187, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 05:00:33'),
(533, 187, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 05:00:34'),
(534, 187, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 05:00:36'),
(535, 187, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 05:00:37'),
(536, 188, 'bot', NULL, 'Por favor, ingresá tu DNI (sin puntos ni espacios):', 0, '2025-12-13 05:03:51'),
(537, 188, 'bot', NULL, '⏳ Validando tu DNI...', 0, '2025-12-13 05:03:54'),
(538, 188, 'bot', NULL, '¿Sos CANO ESPINDOLA ALVARO THOMAS?', 0, '2025-12-13 05:03:54'),
(539, 188, 'bot', NULL, '¿Con cuál de estas opciones te identificás?', 0, '2025-12-13 05:03:55'),
(540, 188, 'bot', NULL, 'Ingresá tu código de área (ejemplo +549):', 0, '2025-12-13 05:03:56'),
(541, 188, 'bot', NULL, 'Ingresá tu número de teléfono (sin el 15):', 0, '2025-12-13 05:03:58'),
(542, 188, 'bot', NULL, 'Ingresaste  27390105, ¿es correcto?', 0, '2025-12-13 05:04:00'),
(543, 188, 'bot', NULL, 'Seleccioná tu banco:', 0, '2025-12-13 05:04:10'),
(544, 188, 'bot', NULL, 'Ingresá tu correo electrónico:', 0, '2025-12-13 05:04:13'),
(545, 188, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo.', 0, '2025-12-13 05:04:18'),
(546, 199, 'asesor', NULL, 'Hola', 0, '2025-12-13 05:35:58'),
(547, 199, 'cliente', NULL, 'Hola', 0, '2025-12-13 05:36:01');

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
(32, 'Thomas Cano', NULL, 'temp_1765562859_3431@cliente.com', NULL, '1127390105', '$2y$10$z1xLRP1KaSaoP9KJZMqLSepz2k/qmm3AgYbX.V1uyy1Km.G7cOsvq', 'cliente', 'activo', '2025-12-12 18:07:39'),
(33, 'TORRES JAVIER ARMANDO', NULL, 'temp_1765563188_4869@cliente.com', NULL, '3731550808', '$2y$10$A5NLMJgptXiXwM2XC8pyHeHk.dm7m0jRAc6ATN9ho1C.6Yjz0sbyq', 'cliente', 'activo', '2025-12-12 18:13:08'),
(34, 'SALVO SHEILA MAGALY', NULL, 'temp_1765563307_9434@cliente.com', NULL, '1161607736', '$2y$10$W8m0JJhCLEmqYL7cEYsuFO1Xmg.zgIYVSs0ZwNmIZndqIv0XxOOZW', 'cliente', 'activo', '2025-12-12 18:15:07');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT de la tabla `chat_archivos`
--
ALTER TABLE `chat_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `chat_transferencias`
--
ALTER TABLE `chat_transferencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes_detalles`
--
ALTER TABLE `clientes_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=548;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
