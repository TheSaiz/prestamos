-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-12-2025 a las 14:01:17
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
(1, 2, 1, 0),
(2, 2, 2, 0),
(3, 2, 3, 0),
(4, 2, 4, 0);

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
(4, 'Ingresá tu código de área:', 'texto'),
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
(944, 382, 1, '💬 Hablar con un asesor', 2, '2025-12-17 17:45:49'),
(945, 382, 2, 'CANO ESPINDOLA ALVARO THOMAS', NULL, '2025-12-17 17:45:53'),
(946, 382, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-17 17:45:54'),
(947, 382, 4, '11', NULL, '2025-12-17 17:45:57'),
(948, 382, 5, '27390608', NULL, '2025-12-17 17:46:01'),
(949, 382, 7, 'Banco BBVA', 16, '2025-12-17 17:46:04'),
(950, 382, 8, 'thomec86@gmail.com', NULL, '2025-12-17 17:46:10'),
(951, 383, 1, '💬 Hablar con un asesor', 2, '2025-12-17 17:51:42'),
(952, 383, 2, 'CANO RENATA CATALINA', NULL, '2025-12-17 17:51:48'),
(953, 383, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-17 17:51:52'),
(954, 383, 4, '11', NULL, '2025-12-17 17:51:54'),
(955, 383, 5, '23678903', NULL, '2025-12-17 17:51:59'),
(956, 383, 7, 'Banco Ciudad', 21, '2025-12-17 17:52:01'),
(957, 383, 8, 'renataa2@gmail.com', NULL, '2025-12-17 17:52:08'),
(958, 384, 1, '💬 Hablar con un asesor', 2, '2025-12-17 17:56:16'),
(959, 384, 2, 'PEPE PEPE', NULL, '2025-12-17 17:56:24'),
(960, 384, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 17:56:27'),
(961, 384, 4, '11', NULL, '2025-12-17 17:56:34'),
(962, 384, 5, '12345678', NULL, '2025-12-17 17:56:39'),
(963, 384, 7, 'Banco Comafi', 22, '2025-12-17 17:56:40'),
(964, 384, 8, 'sadsada@gmail.com', NULL, '2025-12-17 17:56:45'),
(965, 385, 1, '💬 Hablar con un asesor', 2, '2025-12-17 18:06:35'),
(966, 385, 2, 'CANO ESPINDOLA ALVARO THOMAS', NULL, '2025-12-17 18:06:40'),
(967, 385, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-17 18:06:42'),
(968, 385, 4, '11', NULL, '2025-12-17 18:06:45'),
(969, 385, 5, '27390105', NULL, '2025-12-17 18:06:49'),
(970, 385, 7, 'Banco ICBC', 23, '2025-12-17 18:06:51'),
(971, 385, 8, 'thomec86@gmail.com', NULL, '2025-12-17 18:06:55'),
(972, 386, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 18:26:55'),
(973, 386, 2, 'GONZALEZ PATRICIA LUCIA', NULL, '2025-12-17 18:27:01'),
(974, 386, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 18:27:03'),
(975, 386, 4, '376', NULL, '2025-12-17 18:27:08'),
(976, 386, 5, '4565656', NULL, '2025-12-17 18:27:14'),
(977, 386, 7, 'Banco Hipotecario', 20, '2025-12-17 18:27:17'),
(978, 386, 8, 'prueba@gmail.com', NULL, '2025-12-17 18:27:31'),
(979, 387, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 18:31:10'),
(980, 387, 2, 'CARRIZO ADRIANA MONICA', NULL, '2025-12-17 18:31:18'),
(981, 387, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 18:31:20'),
(982, 387, 4, '376', NULL, '2025-12-17 18:31:39'),
(983, 387, 5, '4533222', NULL, '2025-12-17 18:31:46'),
(984, 387, 7, 'Banco Patagonia', 19, '2025-12-17 18:31:49'),
(985, 387, 8, 'prueba@gmail.com', NULL, '2025-12-17 18:32:06'),
(986, 388, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 18:33:40'),
(987, 388, 2, 'SERVIN VANESA ROSALIA', NULL, '2025-12-17 18:34:28'),
(988, 388, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 18:34:29'),
(989, 388, 4, '376', NULL, '2025-12-17 18:34:35'),
(990, 388, 5, '4323232', NULL, '2025-12-17 18:34:40'),
(991, 388, 7, 'Banco Patagonia', 19, '2025-12-17 18:34:42'),
(992, 388, 8, 'prueba@gmail.com', NULL, '2025-12-17 18:34:58'),
(993, 389, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 18:37:43'),
(994, 389, 2, 'MOREIRA PAULA BEATRIZ', NULL, '2025-12-17 18:37:47'),
(995, 389, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 18:37:49'),
(996, 389, 4, '376', NULL, '2025-12-17 18:37:53'),
(997, 389, 5, '4353535', NULL, '2025-12-17 18:38:01'),
(998, 389, 7, 'Banco Credicoop', 17, '2025-12-17 18:38:05'),
(999, 389, 8, 'prueba@gmail.com', NULL, '2025-12-17 18:38:24'),
(1000, 390, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 18:39:16'),
(1001, 390, 2, 'VOSILAITIS ANTONIO AGUSTIN', NULL, '2025-12-17 18:39:25'),
(1002, 390, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 18:39:26'),
(1003, 390, 4, '376', NULL, '2025-12-17 18:39:39'),
(1004, 390, 5, '4112183', NULL, '2025-12-17 18:39:59'),
(1005, 390, 7, 'Banco Macro', 15, '2025-12-17 18:40:06'),
(1006, 390, 8, 'xd123@gmail.com', NULL, '2025-12-17 18:40:20'),
(1007, 391, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 20:10:38'),
(1008, 391, 2, 'BRITEZ JULIAN ERNESTO', NULL, '2025-12-17 20:10:44'),
(1009, 391, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 20:10:45'),
(1010, 391, 4, '376', NULL, '2025-12-17 20:10:51'),
(1011, 391, 5, '4373737', NULL, '2025-12-17 20:11:01'),
(1012, 391, 7, 'Banco Patagonia', 19, '2025-12-17 20:11:03'),
(1013, 391, 8, 'prueba@hotmail.com', NULL, '2025-12-17 20:11:21'),
(1014, 392, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 20:34:50'),
(1015, 392, 2, 'RAMOS YESICA ADRIANA', NULL, '2025-12-17 20:35:18'),
(1016, 392, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-17 20:35:26'),
(1017, 392, 4, '376', NULL, '2025-12-17 20:39:10'),
(1018, 392, 5, '4805039', NULL, '2025-12-17 20:39:26'),
(1019, 392, 7, 'Banco Macro', 15, '2025-12-17 20:39:31'),
(1020, 392, 8, 'yessiicaramos22@gmail.com', NULL, '2025-12-17 20:39:39'),
(1021, 393, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 22:45:22'),
(1022, 393, 2, 'RIVAS MARIA LUCIANA', NULL, '2025-12-17 22:45:40'),
(1023, 393, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-17 22:45:56'),
(1024, 394, 1, '💬 Hablar con un asesor', 2, '2025-12-17 23:20:17'),
(1025, 394, 2, 'MANATINI CARINA GUADALUPE', NULL, '2025-12-17 23:20:42'),
(1026, 394, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-17 23:20:55'),
(1027, 394, 4, '11', NULL, '2025-12-17 23:21:06'),
(1028, 394, 5, '54746110', NULL, '2025-12-17 23:21:22'),
(1029, 394, 7, 'Banco Provincia', 12, '2025-12-17 23:21:27'),
(1030, 394, 8, 'manatinicarina86@gmail.com', NULL, '2025-12-17 23:21:37'),
(1031, 395, 1, '💬 Hablar con un asesor', 2, '2025-12-17 23:42:56'),
(1032, 395, 2, 'FUENTES LUIS ALBERTO', NULL, '2025-12-17 23:43:15'),
(1033, 395, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 23:43:23'),
(1034, 395, 4, '299', NULL, '2025-12-17 23:43:38'),
(1035, 395, 5, '4598939', NULL, '2025-12-17 23:44:12'),
(1036, 395, 7, 'Banco Santander', 14, '2025-12-17 23:44:21'),
(1037, 395, 8, 'luisalberto251268@gmail.com', NULL, '2025-12-17 23:44:38'),
(1038, 396, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 23:54:44'),
(1039, 396, 2, 'BALMACEDA PAOLA JULIETA', NULL, '2025-12-17 23:54:55'),
(1040, 396, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-17 23:54:58'),
(1041, 396, 4, '11', NULL, '2025-12-17 23:55:29'),
(1042, 396, 5, '36708885', NULL, '2025-12-17 23:55:48'),
(1043, 396, 7, 'Banco Provincia', 12, '2025-12-17 23:55:52'),
(1044, 396, 8, 'Tere_miisniietos@outlook.com', NULL, '2025-12-17 23:56:22'),
(1045, 397, 1, '💰 Solicitar un préstamo', 1, '2025-12-17 23:58:12'),
(1046, 397, 2, 'ESCURDIA DAVID EDUARDO', NULL, '2025-12-17 23:58:24'),
(1047, 397, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-17 23:58:32'),
(1048, 397, 4, '376', NULL, '2025-12-17 23:58:51'),
(1049, 397, 5, '4125555', NULL, '2025-12-17 23:59:14'),
(1050, 397, 7, 'Banco Macro', 15, '2025-12-17 23:59:19'),
(1051, 397, 8, 'david@hotmail.com', NULL, '2025-12-17 23:59:49'),
(1052, 398, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:18:03'),
(1053, 398, 2, 'ALGANARAZ PLAZA VIVIANA IRENE', NULL, '2025-12-18 00:19:00'),
(1054, 398, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 00:19:14'),
(1055, 399, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:29:27'),
(1056, 399, 2, 'FERNANDEZ HECTOR NICOLAS', NULL, '2025-12-18 00:29:35'),
(1057, 399, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 00:29:37'),
(1058, 399, 4, '11', NULL, '2025-12-18 00:30:31'),
(1059, 399, 5, '73635893', NULL, '2025-12-18 00:30:38'),
(1060, 399, 7, 'Banco Provincia', 12, '2025-12-18 00:30:41'),
(1061, 399, 8, 'fthaiel24@gmail.com', NULL, '2025-12-18 00:30:47'),
(1062, 400, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:35:58'),
(1063, 401, 1, '💬 Hablar con un asesor', 2, '2025-12-18 00:36:00'),
(1064, 401, 2, 'GONZALEZ MILAGROS AGUSTINA', NULL, '2025-12-18 00:36:10'),
(1065, 401, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 00:36:14'),
(1066, 400, 2, 'FERREYRA JOANA ELISABETH', NULL, '2025-12-18 00:36:20'),
(1067, 400, 3, 'Cobro Asignaciones Familiares (SUAF)', 8, '2025-12-18 00:36:27'),
(1068, 401, 4, '266', NULL, '2025-12-18 00:36:29'),
(1069, 400, 4, '341', NULL, '2025-12-18 00:36:40'),
(1070, 401, 5, '4152841', NULL, '2025-12-18 00:37:16'),
(1071, 400, 5, '6960075', NULL, '2025-12-18 00:37:27'),
(1072, 402, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:37:34'),
(1073, 400, 7, 'Brubank', 24, '2025-12-18 00:37:38'),
(1074, 401, 7, 'Otro banco', 26, '2025-12-18 00:37:44'),
(1075, 401, 8, 'Miligonza2011@gmail.com', NULL, '2025-12-18 00:37:54'),
(1076, 400, 8, 'yoanaferreyra05@gmail.com', NULL, '2025-12-18 00:37:58'),
(1077, 402, 2, 'SILVA ANGELA', NULL, '2025-12-18 00:38:20'),
(1078, 402, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 00:38:27'),
(1079, 403, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:39:07'),
(1080, 405, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:39:34'),
(1081, 403, 2, 'undefined', NULL, '2025-12-18 00:39:39'),
(1082, 404, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:39:39'),
(1083, 405, 2, 'VENIALGO ARNALDO DANIEL', NULL, '2025-12-18 00:39:45'),
(1084, 403, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 00:39:46'),
(1085, 404, 2, 'MAYDANA FLORENCIA MERLINDA', NULL, '2025-12-18 00:39:46'),
(1086, 405, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 00:39:49'),
(1087, 404, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 00:39:53'),
(1088, 404, 4, '362', NULL, '2025-12-18 00:40:01'),
(1089, 405, 4, '11', NULL, '2025-12-18 00:40:15'),
(1090, 405, 5, '32345069', NULL, '2025-12-18 00:40:26'),
(1091, 404, 5, '4013958', NULL, '2025-12-18 00:40:28'),
(1092, 405, 7, 'Naranja X', 25, '2025-12-18 00:40:31'),
(1093, 404, 7, 'Otro banco', 26, '2025-12-18 00:40:36'),
(1094, 404, 8, 'florenciamerlinda@gmail.com', NULL, '2025-12-18 00:40:46'),
(1095, 405, 8, 'Venialgodaniel1990@gmail.com', NULL, '2025-12-18 00:40:53'),
(1096, 406, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:41:20'),
(1097, 406, 2, 'BLAZQUEZ FABIAN EZEQUIEL', NULL, '2025-12-18 00:41:57'),
(1098, 406, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 00:42:06'),
(1099, 409, 1, '💬 Hablar con un asesor', 2, '2025-12-18 00:47:55'),
(1100, 409, 2, 'BLAZQUEZ FABIAN EZEQUIEL', NULL, '2025-12-18 00:48:24'),
(1101, 409, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 00:48:28'),
(1102, 410, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:49:49'),
(1103, 410, 2, 'AVELLANEDA MAURICIO', NULL, '2025-12-18 00:50:10'),
(1104, 410, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 00:50:18'),
(1105, 410, 4, '2901', NULL, '2025-12-18 00:51:31'),
(1106, 410, 5, '457645', NULL, '2025-12-18 00:53:30'),
(1107, 410, 7, 'Otro banco', 26, '2025-12-18 00:53:35'),
(1108, 410, 8, 'mauricioavellaneda830@gmail.com', NULL, '2025-12-18 00:53:45'),
(1109, 411, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:55:22'),
(1110, 411, 2, 'VILLALVA CORTEZ IVAN DAVID', NULL, '2025-12-18 00:55:36'),
(1111, 411, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 00:55:47'),
(1112, 411, 4, '264', NULL, '2025-12-18 00:56:00'),
(1113, 412, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 00:58:33'),
(1114, 412, 2, 'CORREA MARISA ISABEL', NULL, '2025-12-18 00:58:44'),
(1115, 412, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 00:58:53'),
(1116, 412, 4, '376', NULL, '2025-12-18 01:00:25'),
(1117, 412, 5, '5368899', NULL, '2025-12-18 01:00:34'),
(1118, 412, 7, 'Otro banco', 26, '2025-12-18 01:00:40'),
(1119, 412, 8, 'jarmitjara49@gmail.com', NULL, '2025-12-18 01:00:48'),
(1120, 411, 5, '3187682', NULL, '2025-12-18 01:02:42'),
(1121, 411, 7, 'Otro banco', 26, '2025-12-18 01:02:51'),
(1122, 411, 8, 'ivandavidvillalvacortez1982@gmail.com', NULL, '2025-12-18 01:03:11'),
(1123, 413, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:11:17'),
(1124, 414, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:11:29'),
(1125, 413, 2, 'BLAZQUEZ FABIAN EZEQUIEL', NULL, '2025-12-18 01:11:38'),
(1126, 413, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 01:11:41'),
(1127, 415, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:17:14'),
(1128, 415, 2, 'VAZQUEZ JOSEFINA BEATRIZ', NULL, '2025-12-18 01:17:28'),
(1129, 415, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 01:17:36'),
(1130, 415, 4, '387', NULL, '2025-12-18 01:18:21'),
(1131, 415, 5, '5788649', NULL, '2025-12-18 01:18:41'),
(1132, 415, 7, 'Banco Macro', 15, '2025-12-18 01:18:47'),
(1133, 415, 8, 'vazquezjosefinabeatriz@gmail.com', NULL, '2025-12-18 01:18:57'),
(1134, 416, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:21:33'),
(1135, 416, 2, 'REARTES MANUEL BENITO', NULL, '2025-12-18 01:21:48'),
(1136, 416, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 01:21:51'),
(1137, 416, 4, '383', NULL, '2025-12-18 01:22:16'),
(1138, 417, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:22:36'),
(1139, 416, 5, '4062651', NULL, '2025-12-18 01:22:49'),
(1140, 417, 2, 'PONCE HERRERA JUAN MANUEL', NULL, '2025-12-18 01:22:51'),
(1141, 417, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 01:22:53'),
(1142, 416, 7, 'Banco Nación', 11, '2025-12-18 01:22:56'),
(1143, 416, 8, 'reartesmanuel02@gmail.com', NULL, '2025-12-18 01:23:08'),
(1144, 418, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:35:01'),
(1145, 418, 2, 'LORENZO MARIA BELEN', NULL, '2025-12-18 01:35:10'),
(1146, 418, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 01:35:12'),
(1147, 418, 4, '11', NULL, '2025-12-18 01:35:28'),
(1148, 418, 5, '28937613', NULL, '2025-12-18 01:35:40'),
(1149, 418, 7, 'Banco Ciudad', 21, '2025-12-18 01:35:46'),
(1150, 418, 8, 'Mbelen.lorenzo1@gmail.com', NULL, '2025-12-18 01:36:06'),
(1151, 419, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:39:05'),
(1152, 419, 2, 'IBARRA MERCEDES YASMILA', NULL, '2025-12-18 01:39:15'),
(1153, 419, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 01:39:21'),
(1154, 419, 4, '387', NULL, '2025-12-18 01:39:32'),
(1155, 419, 5, '5670149', NULL, '2025-12-18 01:39:43'),
(1156, 419, 7, 'Otro banco', 26, '2025-12-18 01:39:52'),
(1157, 419, 8, 'Mercedes.ibarra0716@gmail.com', NULL, '2025-12-18 01:39:58'),
(1158, 420, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:54:10'),
(1159, 421, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:54:26'),
(1160, 420, 2, 'NAVARRO LEDEZMA MARIA LOURDES', NULL, '2025-12-18 01:54:29'),
(1161, 421, 2, 'BRIATORE MARIA AGOSTINA', NULL, '2025-12-18 01:54:40'),
(1162, 420, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 01:54:41'),
(1163, 421, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 01:54:50'),
(1164, 420, 4, '379', NULL, '2025-12-18 01:55:40'),
(1165, 420, 5, '5567648', NULL, '2025-12-18 01:56:25'),
(1166, 420, 7, 'Banco Nación', 11, '2025-12-18 01:56:34'),
(1167, 420, 8, 'nmarialourdes0@gmail.com', NULL, '2025-12-18 01:56:52'),
(1168, 422, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:56:54'),
(1169, 422, 2, 'DETTLER GIULIANA ABIGAIL', NULL, '2025-12-18 01:57:04'),
(1170, 422, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 01:57:06'),
(1171, 423, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 01:57:37'),
(1172, 423, 2, 'LUCERO MONICA SUSANA', NULL, '2025-12-18 01:58:07'),
(1173, 423, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 01:58:12'),
(1174, 424, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 02:01:43'),
(1175, 424, 2, 'MARTINEZ TOMAS DAVID', NULL, '2025-12-18 02:01:59'),
(1176, 424, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 02:02:10'),
(1177, 424, 4, '351', NULL, '2025-12-18 02:02:58'),
(1178, 425, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 02:05:29'),
(1179, 425, 2, 'SAAVEDRA JORGE ARIEL', NULL, '2025-12-18 02:06:21'),
(1180, 425, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 02:06:28'),
(1181, 426, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 02:09:11'),
(1182, 426, 2, 'MARTINEZ TOMAS DAVID', NULL, '2025-12-18 02:09:28'),
(1183, 426, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 02:09:33'),
(1184, 426, 4, '351', NULL, '2025-12-18 02:09:50'),
(1185, 426, 5, '2580677', NULL, '2025-12-18 02:10:13'),
(1186, 426, 7, 'Banco Galicia', 13, '2025-12-18 02:10:21'),
(1187, 426, 8, 'Tomasdm87@gmail.com', NULL, '2025-12-18 02:10:46'),
(1188, 427, 1, '💬 Hablar con un asesor', 2, '2025-12-18 02:15:47'),
(1189, 427, 2, 'RODRIGUEZ PAOLA ELIZABETH', NULL, '2025-12-18 02:17:59'),
(1190, 427, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 02:18:11'),
(1191, 427, 4, '387', NULL, '2025-12-18 02:18:46'),
(1192, 427, 5, '5767010', NULL, '2025-12-18 02:19:07'),
(1193, 428, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 02:19:13'),
(1194, 427, 7, 'Banco Nación', 11, '2025-12-18 02:19:18'),
(1195, 428, 2, 'ROBLEDO EZEQUIEL FERNANDO', NULL, '2025-12-18 02:19:27'),
(1196, 428, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 02:19:38'),
(1197, 427, 8, 'paolarodriguez909011@gmail.com', NULL, '2025-12-18 02:19:39'),
(1198, 428, 4, '341', NULL, '2025-12-18 02:19:50'),
(1199, 428, 5, '6396138', NULL, '2025-12-18 02:20:29'),
(1200, 428, 7, 'Banco Credicoop', 17, '2025-12-18 02:20:36'),
(1201, 428, 8, 'Ezequielfernando1907@gmail.com', NULL, '2025-12-18 02:20:49'),
(1202, 429, 1, '💬 Hablar con un asesor', 2, '2025-12-18 02:23:22'),
(1203, 429, 2, 'undefined', NULL, '2025-12-18 02:24:16'),
(1204, 429, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 02:24:21'),
(1205, 429, 4, '280', NULL, '2025-12-18 02:24:45'),
(1206, 429, 5, '4517365', NULL, '2025-12-18 02:25:08'),
(1207, 429, 7, 'Otro banco', 26, '2025-12-18 02:25:20'),
(1208, 429, 8, 'arruayohana5@gmail.com', NULL, '2025-12-18 02:25:33'),
(1209, 430, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 03:06:20'),
(1210, 430, 2, 'DETTLER GIULIANA ABIGAIL', NULL, '2025-12-18 03:06:32'),
(1211, 430, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 03:06:37'),
(1212, 431, 1, '💬 Hablar con un asesor', 2, '2025-12-18 03:08:34'),
(1213, 431, 2, 'DETTLER GIULIANA ABIGAIL', NULL, '2025-12-18 03:08:44'),
(1214, 431, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 03:08:46'),
(1215, 431, 4, '221', NULL, '2025-12-18 03:13:58'),
(1216, 432, 1, '💬 Hablar con un asesor', 2, '2025-12-18 03:15:31'),
(1217, 432, 2, 'DETTLER GIULIANA ABIGAIL', NULL, '2025-12-18 03:15:44'),
(1218, 432, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 03:15:48'),
(1219, 432, 4, '221', NULL, '2025-12-18 03:16:13'),
(1220, 433, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 03:24:19'),
(1221, 433, 2, 'GONZALEZ NADIA BELEN', NULL, '2025-12-18 03:25:08'),
(1222, 433, 3, 'Cobro Asignaciones Familiares (SUAF)', 8, '2025-12-18 03:25:11'),
(1223, 433, 4, '11', NULL, '2025-12-18 03:25:33'),
(1224, 433, 5, '60508956', NULL, '2025-12-18 03:26:00'),
(1225, 433, 7, 'Banco Supervielle', 18, '2025-12-18 03:26:05'),
(1226, 433, 8, 'ng7792374@gmail.com', NULL, '2025-12-18 03:26:18'),
(1227, 434, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 03:29:52'),
(1228, 434, 2, 'GONZALEZ NADIA BELEN', NULL, '2025-12-18 03:30:04'),
(1229, 434, 3, 'Cobro Asignaciones Familiares (SUAF)', 8, '2025-12-18 03:30:06'),
(1230, 434, 4, '11', NULL, '2025-12-18 03:30:18'),
(1231, 434, 5, '60508956', NULL, '2025-12-18 03:30:30'),
(1232, 434, 7, 'Banco Supervielle', 18, '2025-12-18 03:30:34'),
(1233, 434, 8, 'ng7792374@gmail.com', NULL, '2025-12-18 03:30:46'),
(1234, 435, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 03:41:16'),
(1235, 436, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 03:41:42'),
(1236, 435, 2, 'ARROYO JULIO RAMON', NULL, '2025-12-18 03:41:55'),
(1237, 436, 2, 'ERAZO ESTEFANIA BRENDA', NULL, '2025-12-18 03:42:00'),
(1238, 436, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 03:42:10'),
(1239, 435, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 03:42:17'),
(1240, 436, 4, '345', NULL, '2025-12-18 03:43:09'),
(1241, 436, 5, '7386232', NULL, '2025-12-18 03:45:47'),
(1242, 436, 7, 'Banco Macro', 15, '2025-12-18 03:45:51'),
(1243, 436, 8, 'Estefierazo2019@gmail.com', NULL, '2025-12-18 03:46:17'),
(1244, 437, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 04:20:05'),
(1245, 437, 2, 'EISENBART EZIO FRANCISCO', NULL, '2025-12-18 04:20:28'),
(1246, 438, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 04:46:55'),
(1247, 438, 2, 'PONCE NATALIA VERONICA', NULL, '2025-12-18 04:47:15'),
(1248, 438, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 04:47:26'),
(1249, 439, 1, '💬 Hablar con un asesor', 2, '2025-12-18 05:15:11'),
(1250, 439, 2, 'SALTO ROXANA BELEN', NULL, '2025-12-18 05:15:27'),
(1251, 439, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 05:15:42'),
(1252, 440, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 05:17:07'),
(1253, 440, 2, 'SANABRIA DAIANA PAMELA', NULL, '2025-12-18 05:17:16'),
(1254, 440, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 05:17:23'),
(1255, 441, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 05:17:41'),
(1256, 441, 2, 'SANABRIA DAIANA PAMELA', NULL, '2025-12-18 05:17:48'),
(1257, 441, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 05:17:53'),
(1258, 441, 4, '342', NULL, '2025-12-18 05:19:29'),
(1259, 442, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 05:54:25'),
(1260, 442, 2, 'GALVAN CESAR MAXIMILIANO', NULL, '2025-12-18 05:54:54'),
(1261, 442, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 05:55:21'),
(1262, 442, 4, '2903', NULL, '2025-12-18 05:57:17'),
(1263, 442, 5, '570728', NULL, '2025-12-18 05:59:05'),
(1264, 442, 7, 'Banco Credicoop', 17, '2025-12-18 05:59:15'),
(1265, 442, 8, 'galvancesar140@gmail.com', NULL, '2025-12-18 05:59:42'),
(1266, 443, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 06:04:47'),
(1267, 443, 2, 'PARADA SERGIO ALEJO', NULL, '2025-12-18 06:05:03'),
(1268, 443, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 06:05:06'),
(1269, 443, 4, '387', NULL, '2025-12-18 06:06:01'),
(1270, 443, 5, '8314086', NULL, '2025-12-18 06:06:19'),
(1271, 443, 7, 'Banco Macro', 15, '2025-12-18 06:06:22'),
(1272, 443, 8, 'alejosergioparada@gmail.com', NULL, '2025-12-18 06:06:30'),
(1273, 444, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 07:31:59'),
(1274, 444, 2, 'YADO AXEL EMANUEL', NULL, '2025-12-18 07:32:08'),
(1275, 444, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 07:32:11'),
(1276, 444, 4, '376', NULL, '2025-12-18 07:32:17'),
(1277, 444, 5, '5397072', NULL, '2025-12-18 07:32:25'),
(1278, 444, 7, 'Banco Santander', 14, '2025-12-18 07:32:32'),
(1279, 444, 8, 'axelyado24@gmail.com', NULL, '2025-12-18 07:32:41'),
(1280, 445, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 08:34:32'),
(1281, 445, 2, 'RUIZ DIAZ CONSTANZA DANIELA', NULL, '2025-12-18 08:34:41'),
(1282, 445, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 08:34:46'),
(1283, 445, 4, '11', NULL, '2025-12-18 08:35:02'),
(1284, 445, 5, '72878582', NULL, '2025-12-18 08:35:14'),
(1285, 445, 7, 'Otro banco', 26, '2025-12-18 08:35:20'),
(1286, 445, 8, 'cruizdiaz0553@gmail.com', NULL, '2025-12-18 08:35:28'),
(1287, 446, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 09:10:39'),
(1288, 446, 2, 'ROJAS ROXANA CATALINA', NULL, '2025-12-18 09:10:58'),
(1289, 446, 3, 'Soy Monotributista o Responsable Inscripto', 9, '2025-12-18 09:11:04'),
(1290, 446, 4, '11', NULL, '2025-12-18 09:11:36'),
(1291, 446, 5, '26330400', NULL, '2025-12-18 09:11:52'),
(1292, 446, 7, 'Banco Santander', 14, '2025-12-18 09:11:56'),
(1293, 446, 8, 'Roxyrojas960@gmail.comr', NULL, '2025-12-18 09:12:09'),
(1294, 447, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 09:17:51'),
(1295, 447, 2, 'SOTO VIVIANA ITATI', NULL, '2025-12-18 09:18:11'),
(1296, 447, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 09:18:15'),
(1297, 448, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 09:20:53'),
(1298, 448, 2, 'SOTO VIVIANA ITATI', NULL, '2025-12-18 09:21:10'),
(1299, 448, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 09:21:19'),
(1300, 449, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 09:26:49'),
(1301, 449, 2, 'SOTO VIVIANA ITATI', NULL, '2025-12-18 09:27:04'),
(1302, 449, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 09:27:07'),
(1303, 449, 4, '376', NULL, '2025-12-18 09:27:21'),
(1304, 449, 5, '4687069', NULL, '2025-12-18 09:29:52'),
(1305, 449, 7, 'Banco Nación', 11, '2025-12-18 09:30:13'),
(1306, 449, 8, 'sotovivi01@gmail.com', NULL, '2025-12-18 09:30:23'),
(1307, 450, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 09:49:29'),
(1308, 450, 2, 'ALMADA MARIA SOLEDAD', NULL, '2025-12-18 09:49:43'),
(1309, 450, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 09:49:53'),
(1310, 451, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 09:50:41'),
(1311, 451, 2, 'SOLOAGA ERICA ANALIA', NULL, '2025-12-18 09:50:58'),
(1312, 451, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 09:51:03'),
(1313, 450, 4, '376', NULL, '2025-12-18 09:51:09'),
(1314, 451, 4, '362', NULL, '2025-12-18 09:51:34'),
(1315, 450, 5, '4966233', NULL, '2025-12-18 09:51:51'),
(1316, 450, 7, 'Banco Macro', 15, '2025-12-18 09:51:56'),
(1317, 450, 8, 'almadamary163@gmail.com', NULL, '2025-12-18 09:52:07'),
(1318, 451, 5, '5178043', NULL, '2025-12-18 09:52:48'),
(1319, 451, 7, 'Banco Nación', 11, '2025-12-18 09:52:57'),
(1320, 451, 8, 'annaaliitaa31@gmail.com', NULL, '2025-12-18 09:53:09'),
(1321, 452, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 09:56:13'),
(1322, 452, 2, 'CORBINO ALAN', NULL, '2025-12-18 09:56:21'),
(1323, 452, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 09:56:23'),
(1324, 452, 4, '11', NULL, '2025-12-18 09:57:00'),
(1325, 452, 5, '33953658', NULL, '2025-12-18 09:57:23'),
(1326, 452, 7, 'Banco Galicia', 13, '2025-12-18 09:57:28'),
(1327, 452, 8, 'Corbinoalan81@gmail.com', NULL, '2025-12-18 09:57:36'),
(1328, 453, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 10:33:55'),
(1329, 453, 2, 'LOPEZ MATORRAS MARTA ALICIA', NULL, '2025-12-18 10:37:20'),
(1330, 453, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 10:37:25'),
(1331, 454, 1, '💬 Hablar con un asesor', 2, '2025-12-18 11:20:38'),
(1332, 454, 2, 'ALMIRON CARLOS ROBERTO', NULL, '2025-12-18 11:20:51'),
(1333, 454, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 11:20:54'),
(1334, 454, 4, '362', NULL, '2025-12-18 11:21:29'),
(1335, 455, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 11:25:53'),
(1336, 455, 2, 'DIAZ DANIELA JOHANA', NULL, '2025-12-18 11:26:07'),
(1337, 455, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 11:26:12'),
(1338, 455, 4, '266', NULL, '2025-12-18 11:27:10'),
(1339, 455, 5, '5069382', NULL, '2025-12-18 11:27:24'),
(1340, 455, 7, 'Banco Nación', 11, '2025-12-18 11:27:28'),
(1341, 455, 8, 'danielajohanadiaz@gmail.com', NULL, '2025-12-18 11:27:40'),
(1342, 454, 5, '4835363', NULL, '2025-12-18 11:28:03'),
(1343, 454, 7, 'Otro banco', 26, '2025-12-18 11:28:19'),
(1344, 454, 8, 'Ezequiel13almiron@gmail.com', NULL, '2025-12-18 11:32:58'),
(1345, 456, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 12:13:15'),
(1346, 456, 2, 'SEGOVIA LIDIA LUISA', NULL, '2025-12-18 12:13:34'),
(1347, 456, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 12:13:54'),
(1348, 457, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 12:19:57'),
(1349, 457, 2, 'CORBINO ALAN', NULL, '2025-12-18 12:20:06'),
(1350, 457, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 12:20:07'),
(1351, 457, 4, '11', NULL, '2025-12-18 12:20:14'),
(1352, 457, 5, '33953658', NULL, '2025-12-18 12:20:22'),
(1353, 457, 7, 'Banco Galicia', 13, '2025-12-18 12:20:26'),
(1354, 457, 8, 'Corbinoalan81@gmail.com', NULL, '2025-12-18 12:20:33'),
(1355, 458, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 12:25:10'),
(1356, 458, 2, 'ESCURDIA DAVID EDUARDO', NULL, '2025-12-18 12:25:18'),
(1357, 458, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 12:25:20'),
(1358, 459, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 12:40:23'),
(1359, 459, 2, 'PAZ NATALI ELISABETH', NULL, '2025-12-18 12:40:38'),
(1360, 459, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 12:40:49'),
(1361, 459, 4, '2901', NULL, '2025-12-18 12:41:48'),
(1362, 460, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 12:42:38'),
(1363, 460, 2, 'CADENA YOLANDA BEATRIZ', NULL, '2025-12-18 12:43:13'),
(1364, 460, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 12:43:17'),
(1365, 461, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 12:53:28'),
(1366, 461, 2, 'RUIZ DIAZ CABANAS LILIAN DAIANA', NULL, '2025-12-18 12:53:46'),
(1367, 461, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 12:53:51'),
(1368, 461, 4, '221', NULL, '2025-12-18 12:54:01'),
(1369, 461, 5, '5715153', NULL, '2025-12-18 12:54:27'),
(1370, 461, 7, 'Banco Provincia', 12, '2025-12-18 12:54:35'),
(1371, 461, 8, 'Lilianrdc86@gmail.com', NULL, '2025-12-18 12:54:48'),
(1372, 462, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 13:15:19'),
(1373, 462, 2, 'DETTLER GIULIANA ABIGAIL', NULL, '2025-12-18 13:15:25'),
(1374, 462, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 13:15:27'),
(1375, 463, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 13:25:47'),
(1376, 463, 2, 'GONZALEZ FABIAN DAVID', NULL, '2025-12-18 13:26:08'),
(1377, 463, 3, 'Trabajo sin estar registrado (Negro)', 10, '2025-12-18 13:26:22'),
(1378, 463, 4, '11', NULL, '2025-12-18 13:26:38'),
(1379, 463, 5, '57246267', NULL, '2025-12-18 13:26:56'),
(1380, 463, 7, 'Banco Provincia', 12, '2025-12-18 13:27:12'),
(1381, 463, 8, 'Fabii1an26.gonzalez@gmail.com', NULL, '2025-12-18 13:27:43'),
(1382, 464, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 13:46:50'),
(1383, 464, 2, 'CACERES YAMILA ABIGAIL', NULL, '2025-12-18 13:47:03'),
(1384, 464, 3, 'Cobro Asignación Universal por Hijo (AUH)', 7, '2025-12-18 13:47:06'),
(1385, 465, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 13:47:24'),
(1386, 465, 2, 'SALVAGIOT LEANDRA FABIANA', NULL, '2025-12-18 13:48:05'),
(1387, 465, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 13:48:16'),
(1388, 466, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 13:52:52'),
(1389, 466, 2, 'VEGA CARLOS EUGENIO NICOLAS', NULL, '2025-12-18 13:53:02'),
(1390, 466, 3, 'Tengo Recibo de Sueldo', 5, '2025-12-18 13:53:06'),
(1391, 466, 4, '380', NULL, '2025-12-18 13:53:20'),
(1392, 466, 5, '4616323', NULL, '2025-12-18 13:53:29'),
(1393, 466, 7, 'Otro banco', 26, '2025-12-18 13:53:41'),
(1394, 467, 1, '💰 Solicitar un préstamo', 1, '2025-12-18 13:53:48'),
(1395, 466, 8, 'vegaah1893@gmail.com', NULL, '2025-12-18 13:53:59'),
(1396, 467, 2, 'SALVAGIOT LEANDRA FABIANA', NULL, '2025-12-18 13:54:19'),
(1397, 467, 3, 'Soy jubilado, pensionado o retirado', 6, '2025-12-18 13:54:24');

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
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `ultima_lectura_asesor` timestamp NULL DEFAULT NULL,
  `ip_cliente` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `mac_dispositivo` varchar(64) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `prioridad` varchar(20) DEFAULT 'media',
  `score` int(11) DEFAULT 0,
  `estado_solicitud` varchar(50) DEFAULT 'nuevo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `chats`
--

INSERT INTO `chats` (`id`, `cliente_id`, `cuil_validado`, `nombre_validado`, `situacion_laboral`, `banco`, `fecha_solicitud_prestamo`, `api_enviado`, `api_respuesta`, `asesor_id`, `departamento_id`, `origen`, `estado`, `fecha_asignacion`, `fecha_inicio`, `fecha_cierre`, `ultima_lectura_asesor`, `ip_cliente`, `user_agent`, `mac_dispositivo`, `ciudad`, `pais`, `latitud`, `longitud`, `prioridad`, `score`, `estado_solicitud`) VALUES
(382, 199, '20417273876', 'CANO ESPINDOLA ALVARO THOMAS', 'MONO_RESPONSABLE', 'Banco BBVA', NULL, 1, '{\"Respuesta\":{\"RiePedID\":149885,\"P_OK\":\"S\",\"P_Msj\":\"CARGA REPETIDA\",\"P_Ren\":0,\"P_Disp\":99999,\"P_Rpta\":\"[S][12]\",\"P_CliSexo\":\"M\",\"P_CliCUIT\":\"20-41727387-6\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"CANO\",\"P_Nom\":\"ESPINDOLA ALVARO THOMAS\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{},\"I\":{},\"V\":{}}}', 27, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:28:15', '2025-12-17 17:45:48', NULL, '2025-12-17 18:28:30', '2802:8011:3024:e101:21f2:e17c:ae1b:f318', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000, 'media', 0, 'nuevo'),
(383, 200, '27588537493', 'CANO RENATA CATALINA', 'COBRA_AUH', '21', NULL, 1, '{\"Respuesta\":{\"RiePedID\":149888,\"P_OK\":\"S\",\"P_Msj\":\"CARGA REPETIDA\",\"P_Ren\":0,\"P_Disp\":99999,\"P_Rpta\":\"[S][12]\",\"P_CliSexo\":\"F\",\"P_CliCUIT\":\"27-58853749-3\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"CANO\",\"P_Nom\":\"RENATA CATALINA\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{},\"I\":{},\"V\":{}}}', 27, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:28:22', '2025-12-17 17:51:41', NULL, '2025-12-17 18:28:22', '2802:8011:3024:e101:21f2:e17c:ae1b:f318', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000, 'media', 0, 'nuevo'),
(384, 201, '20987654326', 'PEPE PEPE', NULL, '22', NULL, 0, NULL, 27, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:28:26', '2025-12-17 17:56:14', NULL, '2025-12-17 18:28:26', '2802:8011:3024:e101:21f2:e17c:ae1b:f318', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000, 'media', 0, 'nuevo'),
(385, 202, '20417273876', 'CANO ESPINDOLA ALVARO THOMAS', 'COBRA_AUH', '23', NULL, 0, NULL, 27, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:28:30', '2025-12-17 18:06:34', NULL, '2025-12-17 18:28:30', '2802:8011:3024:e101:21f2:e17c:ae1b:f318', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'San Fernando', 'Argentina', -34.4459000, -58.5835000, 'media', 0, 'nuevo'),
(386, 203, '27229126313', 'GONZALEZ PATRICIA LUCIA', NULL, '20', NULL, 0, NULL, 27, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:28:34', '2025-12-17 18:26:53', NULL, '2025-12-17 18:28:34', '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(387, 204, '27217764292', 'CARRIZO ADRIANA MONICA', NULL, '19', NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:35:20', '2025-12-17 18:31:08', NULL, NULL, '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(388, 205, '27348216592', 'SERVIN VANESA ROSALIA', NULL, '19', NULL, 0, NULL, 27, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:38:55', '2025-12-17 18:33:38', NULL, '2025-12-17 18:38:56', '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(389, 206, '23272002134', 'MOREIRA PAULA BEATRIZ', NULL, '17', NULL, 0, NULL, 27, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:39:01', '2025-12-17 18:37:37', NULL, '2025-12-17 18:39:01', '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(390, 207, '20425156161', 'VOSILAITIS ANTONIO AGUSTIN', NULL, '15', NULL, 0, NULL, 2, 1, 'chatbot', 'en_conversacion', '2025-12-17 18:40:48', '2025-12-17 18:39:12', NULL, '2025-12-17 18:40:48', '190.220.47.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 55, 'nuevo'),
(391, 208, '20437569933', 'BRITEZ JULIAN ERNESTO', NULL, '19', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-17 20:38:00', '2025-12-17 20:10:36', NULL, '2025-12-17 20:38:00', '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 55, 'nuevo'),
(392, 209, '27392229979', 'RAMOS YESICA ADRIANA', 'MONO_RESPONSABLE', '15', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-17 20:40:45', '2025-12-17 20:34:44', NULL, '2025-12-18 00:29:40', '186.157.102.248', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 0, 'nuevo'),
(393, 214, '27243251201', 'RIVAS MARIA LUCIANA', 'COBRA_NEGRO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-17 22:45:18', NULL, NULL, '131.196.36.138', 'Mozilla/5.0 (Linux; Android 15; 23106RN0DA Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024204040 AppName/musical_ly ByteLocale/es-419', NULL, 'Salto Grande', 'Argentina', -32.6614000, -61.0799000, 'media', 0, 'nuevo'),
(394, 215, '27323303474', 'MANATINI CARINA GUADALUPE', 'JUBILADO', '12', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:34:13', '2025-12-17 23:20:07', NULL, '2025-12-18 00:36:07', '181.85.128.211', 'Mozilla/5.0 (Linux; Android 15; 2409BRN2CL Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Channel/release AppName/ultralite app_version/41.8.3 Region/AR ByteLocale/es ByteFullLocale/es Spark/1.8.4-alpha.30 AppVersion/41.8.3  PIA/2.8.4', NULL, 'Morón', 'Argentina', -34.6512000, -58.6219000, 'media', 0, 'nuevo'),
(395, 216, '20204366315', 'FUENTES LUIS ALBERTO', NULL, '14', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:36:11', '2025-12-17 23:42:46', NULL, '2025-12-18 00:36:11', '186.141.225.33', 'Mozilla/5.0 (Linux; Android 13; SM-N985F Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024206030 AppName/musical_ly ByteLocale/es-419', NULL, 'Cordoba', 'Argentina', -31.4000000, -64.1833000, 'media', 0, 'nuevo'),
(396, 217, '27378416480', 'BALMACEDA PAOLA JULIETA', 'COBRA_AUH', '12', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:36:19', '2025-12-17 23:54:39', NULL, '2025-12-18 00:36:19', '186.143.204.98', 'Mozilla/5.0 (Linux; Android 15; moto g15 Build/VVTA35.51-137; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024301010 AppName/musical_ly ByteLocale/es-419', NULL, 'Buenos Aires', 'Argentina', -34.6142000, -58.3811000, 'media', 0, 'nuevo'),
(397, 218, '20353274083', 'ESCURDIA DAVID EDUARDO', NULL, '15', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:36:26', '2025-12-17 23:58:08', NULL, '2025-12-18 00:44:24', '181.192.101.73', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.108 Mobile/15E148 Safari/604.1', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(398, 219, '27292763064', 'ALGANARAZ PLAZA VIVIANA IRENE', 'COBRA_NEGRO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 00:17:59', NULL, NULL, '45.178.1.240', 'Mozilla/5.0 (Linux; Android 9; moto e6s Build/POBS29.288-60-6-1-29; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.179 Mobile Safari/537.36 Channel/release AppName/ultralite app_version/41.8.3 Region/AR ByteLocale/es ByteFullLocale/es Spark/1.8.4-alpha.30 AppVersion/41.8.3  PIA/2.8.4', NULL, 'San Luis', 'Argentina', -33.2950100, -66.3356300, 'media', 0, 'nuevo'),
(399, 220, '20405419972', 'FERNANDEZ HECTOR NICOLAS', NULL, '12', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:36:31', '2025-12-18 00:29:24', NULL, '2025-12-18 00:36:31', '2803:9800:9011:4fbb:500:2dc7:28c9:8c27', 'Mozilla/5.0 (Linux; Android 15; moto g05 Build/VVTA35.51-137; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.103 Mobile Safari/537.36 Instagram 409.1.0.49.170 Android (35/15; 280dpi; 720x1604; motorola; moto g05; lamul; mt6768; es_US; 843192238; IABMV/1)', NULL, 'Berazategui', 'Argentina', -34.7653100, -58.2127800, 'media', 0, 'nuevo'),
(400, 221, '23366789324', 'FERREYRA JOANA ELISABETH', 'COBRA_SUAF', '24', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:38:10', '2025-12-18 00:35:49', NULL, '2025-12-18 00:46:34', '2a09:bac5:cc:1b9::2c:b7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'San Miguel de Tucuman', 'Argentina', -26.8241400, -65.2226000, 'media', 0, 'nuevo'),
(401, 222, '27434910302', 'GONZALEZ MILAGROS AGUSTINA', 'COBRA_AUH', '26', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:38:26', '2025-12-18 00:35:53', NULL, '2025-12-18 00:40:27', '204.199.12.189', 'Mozilla/5.0 (Linux; Android 14; SM-A135M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (34/14; 450dpi; 1080x2208; samsung; SM-A135M; a13; exynos850; es_ES; 844485251; IABMV/1)', NULL, 'San Luis', 'Argentina', -33.2991000, -66.3547000, 'media', 0, 'nuevo'),
(402, 223, '27035991056', 'SILVA ANGELA', 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 00:37:29', NULL, NULL, '186.158.228.111', 'Mozilla/5.0 (Linux; Android 15; 2312FPCA6G Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.114 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (35/15; 440dpi; 1080x2400; Xiaomi/POCO; 2312FPCA6G; emerald; mt6789; es_US; 844485262; IABMV/1)', NULL, 'Posadas', 'Argentina', -27.3670800, -55.8960800, 'media', 0, 'nuevo'),
(403, 224, NULL, NULL, 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 00:39:01', NULL, NULL, '187.102.217.160', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 [FBAN/FBIOS;FBAV/542.0.0.37.140;FBBV/840410395;FBDV/iPhone14,5;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5;FBRV/846141037;IABMV/1]', NULL, 'Eldorado', 'Argentina', -26.4045300, -54.6184700, 'media', 0, 'nuevo'),
(404, 225, '27389144938', 'MAYDANA FLORENCIA MERLINDA', 'COBRA_AUH', '26', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:44:28', '2025-12-18 00:39:22', NULL, '2025-12-18 00:47:40', '45.237.223.15', 'Mozilla/5.0 (Linux; Android 15; moto g15 Build/VVTA35.51-137; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.103 Mobile Safari/537.36 Instagram 409.0.0.48.170 Android (35/15; 400dpi; 1080x2400; motorola; moto g15; lamu; mt6768; es_US; 839812236; IABMV/1)', NULL, 'Resistencia', 'Argentina', -27.4605600, -58.9838900, 'media', 0, 'nuevo'),
(405, 226, '20350080180', 'VENIALGO ARNALDO DANIEL', 'COBRA_NEGRO', '25', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:44:36', '2025-12-18 00:39:29', NULL, '2025-12-18 00:52:28', '2802:8010:8223:fe00:616a:a16a:cff0:4d70', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/23B85 Instagram 409.1.0.27.161 (iPhone13,3; iOS 26_1; es_LA; es; scale=3.00; 1170x2532; IABMV/1; 841016381) NW/3 Safari/604.1', NULL, 'San Justo', 'Argentina', -34.6856000, -58.5604000, 'media', 0, 'nuevo'),
(406, 227, '20357251827', 'BLAZQUEZ FABIAN EZEQUIEL', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 00:41:04', NULL, NULL, '181.90.192.224', 'Mozilla/5.0 (Linux; Android 14; SM-A042M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 Instagram 409.1.0.49.170 Android (34/14; 300dpi; 720x1465; samsung; SM-A042M; a04e; mt6765; es_US; 843192238; IABMV/1)', NULL, 'Goya', 'Argentina', -29.1325000, -59.2666000, 'media', 50, 'nuevo'),
(407, 228, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 00:47:33', NULL, NULL, '181.90.192.224', 'Mozilla/5.0 (Linux; Android 14; SM-A042M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 Instagram 409.1.0.49.170 Android (34/14; 300dpi; 720x1465; samsung; SM-A042M; a04e; mt6765; es_US; 843192238; IABMV/1)', NULL, 'Goya', 'Argentina', -29.1325000, -59.2666000, 'media', 0, 'nuevo'),
(408, 229, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 00:47:41', NULL, NULL, '181.90.192.224', 'Mozilla/5.0 (Linux; Android 14; SM-A042M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 Instagram 409.1.0.49.170 Android (34/14; 300dpi; 720x1465; samsung; SM-A042M; a04e; mt6765; es_US; 843192238; IABMV/1)', NULL, 'Goya', 'Argentina', -29.1325000, -59.2666000, 'media', 0, 'nuevo'),
(409, 230, '20357251827', 'BLAZQUEZ FABIAN EZEQUIEL', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 00:47:47', NULL, NULL, '181.90.192.224', 'Mozilla/5.0 (Linux; Android 14; SM-A042M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 Instagram 409.1.0.49.170 Android (34/14; 300dpi; 720x1465; samsung; SM-A042M; a04e; mt6765; es_US; 843192238; IABMV/1)', NULL, 'Goya', 'Argentina', -29.1325000, -59.2666000, 'media', 0, 'nuevo'),
(410, 231, '20457886153', 'AVELLANEDA MAURICIO', 'COBRA_NEGRO', '26', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 00:53:51', '2025-12-18 00:49:42', NULL, '2025-12-18 01:10:58', '186.5.250.90', 'Mozilla/5.0 (Linux; Android 14; moto e14 Build/ULBS34.66-128-1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.115 Mobile Safari/537.36 Channel/release AppName/ultralite app_version/41.8.3 Region/AR ByteLocale/es ByteFullLocale/es Spark/1.8.4-alpha.30 AppVersion/41.8.3  PIA/2.8.4', NULL, 'Puerto Rico', 'Argentina', -26.8005000, -55.0378000, 'media', 0, 'nuevo'),
(411, 232, '20295577291', 'VILLALVA CORTEZ IVAN DAVID', 'COBRA_NEGRO', '26', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 01:10:32', '2025-12-18 00:55:14', NULL, '2025-12-18 01:11:00', '45.226.227.227', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'San Juan', 'Argentina', -31.5353000, -68.5310000, 'media', 0, 'nuevo'),
(412, 233, '27171707493', 'CORREA MARISA ISABEL', 'JUBILADO', '26', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 01:00:56', '2025-12-18 00:58:28', NULL, '2025-12-18 01:27:41', '2803:9800:9480:8718:30bd:ef63:53ff:eed', 'Mozilla/5.0 (Linux; Android 15; 23129RA5FL Build/AQ3A.240829.003; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.114 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (35/15; 440dpi; 1080x2400; Xiaomi/Redmi; 23129RA5FL; sapphire; qcom; es_US; 844485262; IABMV/1)', NULL, 'Catamarca', 'Argentina', -28.4694000, -65.7871000, 'media', 0, 'nuevo'),
(413, 234, '20357251827', 'BLAZQUEZ FABIAN EZEQUIEL', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 01:11:12', NULL, NULL, '181.90.192.224', 'Mozilla/5.0 (Linux; Android 14; SM-A042M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 Instagram 409.1.0.49.170 Android (34/14; 300dpi; 720x1465; samsung; SM-A042M; a04e; mt6765; es_US; 843192238; IABMV/1)', NULL, 'Goya', 'Argentina', -29.1325000, -59.2666000, 'media', 0, 'nuevo'),
(414, 235, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 01:11:26', NULL, NULL, '201.241.216.206', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Safari/604.1 musical_ly_42.9.0 BytedanceWebview/d8a21c6', NULL, 'Santiago', 'Chile', -33.4521000, -70.6536000, 'media', 0, 'nuevo'),
(415, 236, '27436876810', 'VAZQUEZ JOSEFINA BEATRIZ', 'COBRA_AUH', '15', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 01:27:21', '2025-12-18 01:17:05', NULL, '2025-12-18 01:27:21', '181.165.158.84', 'Mozilla/5.0 (Linux; Android 10; moto e(7) plus Build/QPZS30.30-Q3-38-69-12; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Channel/release AppName/ultralite app_version/41.8.3 Region/AR ByteLocale/es ByteFullLocale/es Spark/1.8.4-alpha.30 AppVersion/41.8.3  PIA/2.8.4', NULL, 'Salta', 'Argentina', -24.7831000, -65.4111000, 'media', 0, 'nuevo'),
(416, 237, '20314505612', 'REARTES MANUEL BENITO', NULL, '11', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 01:27:32', '2025-12-18 01:21:26', NULL, '2025-12-18 01:27:32', '2803:9800:9482:9200:15b7:2769:77f0:2e5f', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36', NULL, 'Catamarca', 'Argentina', -28.4694000, -65.7871000, 'media', 0, 'nuevo'),
(417, 238, '20378255512', 'PONCE HERRERA JUAN MANUEL', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 01:22:34', NULL, NULL, '186.157.162.249', 'Mozilla/5.0 (Linux; Android 15; SM-A155M Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 musical_ly_2024209030 AppName/musical_ly ByteLocale/es-419', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 0, 'nuevo'),
(418, 239, '27372773184', 'LORENZO MARIA BELEN', NULL, '21', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 01:40:06', '2025-12-18 01:34:47', NULL, '2025-12-18 01:45:38', '2802:8010:8d31:e601:49f1:8ba3:72ec:d6da', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22G100 Instagram 409.1.0.27.161 (iPhone12,1; iOS 18_6_2; es_LA; es; scale=2.00; 828x1792; IABMV/1; 841016381) Safari/604.1', NULL, 'Avellaneda', 'Argentina', -34.6318000, -58.3675000, 'media', 0, 'nuevo'),
(419, 240, '23465321784', 'IBARRA MERCEDES YASMILA', 'COBRA_AUH', '26', NULL, 0, NULL, 177, 1, 'chatbot', 'en_conversacion', '2025-12-18 01:40:22', '2025-12-18 01:39:02', NULL, '2025-12-18 01:40:22', '181.199.157.145', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 0, 'nuevo'),
(420, 241, '27402607330', 'NAVARRO LEDEZMA MARIA LOURDES', 'COBRA_AUH', '11', NULL, 1, '{\"Respuesta\":{\"RiePedID\":150058,\"P_OK\":\"S\",\"P_Msj\":\"Empleado publico\",\"P_Ren\":2,\"P_Disp\":99999,\"P_Rpta\":\"[N][GES]\",\"P_CliSexo\":\"F\",\"P_CliCUIT\":\"27-40260733-0\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"NAVARRO\",\"P_Nom\":\"LEDEZMA MARIA LOURDES\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{\"RR\":[{\"Ren\":1,\"CapF\":0,\"Cap\":0,\"Cts\":0,\"Cta\":0,\"TNA\":0,\"TEA\":0,\"TEM\":0,\"CFT\":0,\"TIR\":0,\"Linea\":1,\"Cupo\":\"Sin renovación\",\"CapR\":0,\"LineaDes\":\"\",\"TipoOferta\":0,\"PrimerVto\":null,\"CFT2\":0,\"TEM2\":0,\"TEA2\":0},{\"Ren\":2,\"CapF\":0,\"Cap\":0,\"Cts\":0,\"Cta\":0,\"TNA\":0,\"TEA\":0,\"TEM\":0,\"CFT\":0,\"TIR\":0,\"Linea\":1,\"Cupo\":\"Sin renovación\",\"CapR\":0,\"LineaDes\":\"\",\"TipoOferta\":0,\"PrimerVto\":null,\"CFT2\":0,\"TEM2\":0,\"TEA2\":0}]},\"I\":{},\"V\":{}}}', 184, 1, 'chatbot', 'en_conversacion', '2025-12-18 13:48:00', '2025-12-18 01:54:07', NULL, '2025-12-18 13:48:00', '181.233.41.158', 'Mozilla/5.0 (Linux; Android 12; moto e22 Build/SOVS32.121-56-47; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024209030 AppName/musical_ly ByteLocale/es-419', NULL, 'Corrientes', 'Argentina', -27.4685000, -58.8313000, 'media', 70, 'nuevo'),
(421, 242, '27385350908', 'BRIATORE MARIA AGOSTINA', 'COBRA_NEGRO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 01:54:22', NULL, NULL, '186.148.136.190', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Safari/604.1 musical_ly_42.9.0 BytedanceWebview/d8a21c6', NULL, 'Tres Arroyos', 'Argentina', -38.2000000, -60.2833000, 'media', 0, 'nuevo'),
(422, 243, '27389351003', 'DETTLER GIULIANA ABIGAIL', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 01:56:51', NULL, NULL, '170.155.12.4', 'Mozilla/5.0 (Linux; Android 15; 25078RA3EL Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'La Plata', 'Argentina', -34.9309000, -57.9417000, 'media', 0, 'nuevo'),
(423, 244, '27345903017', 'LUCERO MONICA SUSANA', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 01:57:30', NULL, NULL, '186.143.138.53', 'Mozilla/5.0 (Linux; Android 14; SM-A065M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.174 Mobile Safari/537.36 musical_ly_2024209030 AppName/musical_ly ByteLocale/es-419', NULL, 'Buenos Aires', 'Argentina', -34.6142000, -58.3811000, 'media', 0, 'nuevo'),
(424, 245, '20339568619', 'MARTINEZ TOMAS DAVID', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 02:01:33', NULL, '2025-12-18 13:54:19', '186.139.196.205', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Marcos Juárez', 'Argentina', -32.7048000, -62.0925000, 'media', 0, 'nuevo'),
(425, 246, '20375866103', 'SAAVEDRA JORGE ARIEL', 'COBRA_NEGRO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 02:05:26', NULL, NULL, '181.9.207.234', 'Mozilla/5.0 (Linux; Android 10; Quantum Q20 Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 Channel/release AppName/ultralite app_version/41.4.5 Region/AR ByteLocale/es ByteFullLocale/es Spark/1.8.4-alpha.30 AppVersion/41.4.5  PIA/2.8.4', NULL, 'Buenos Aires', 'Argentina', -34.6142000, -58.3811000, 'media', 0, 'nuevo'),
(426, 247, '20339568619', 'MARTINEZ TOMAS DAVID', NULL, '13', NULL, 0, NULL, 184, 1, 'chatbot', 'en_conversacion', '2025-12-18 13:52:50', '2025-12-18 02:09:07', NULL, '2025-12-18 13:54:19', '186.139.196.205', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Marcos Juárez', 'Argentina', -32.7048000, -62.0925000, 'media', 55, 'nuevo'),
(427, 248, '27412963011', 'RODRIGUEZ PAOLA ELIZABETH', 'COBRA_AUH', '11', NULL, 1, '{\"Respuesta\":{\"RiePedID\":150059,\"P_OK\":\"S\",\"P_Msj\":\"CARGA REPETIDA\",\"P_Ren\":0,\"P_Disp\":99999,\"P_Rpta\":\"[S][12]\",\"P_CliSexo\":\"F\",\"P_CliCUIT\":\"27-41296301-1\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"RODRIGUEZ\",\"P_Nom\":\"PAOLA ELIZABETH\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{},\"I\":{},\"V\":{}}}', NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 02:15:37', NULL, NULL, '190.2.127.35', 'Mozilla/5.0 (Linux; Android 11; moto g(20) Build/RTAS31.68-66-3; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.174 Mobile Safari/537.36 musical_ly_2024208030 AppName/musical_ly ByteLocale/es-419', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 0, 'nuevo'),
(428, 249, '20404510429', 'ROBLEDO EZEQUIEL FERNANDO', NULL, '17', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 02:19:04', NULL, NULL, '190.112.216.109', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, 'Rosario', 'Argentina', -32.9468200, -60.6393200, 'media', 0, 'nuevo'),
(429, 250, NULL, NULL, NULL, '26', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 02:23:12', NULL, NULL, '2802:8012:d05c:2800:1c7d:fff5:236e:4d2f', 'Mozilla/5.0 (Linux; Android 14; moto g23 Build/UHAS34.29-29; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Channel/release AppName/ultralite app_version/41.7.4 Region/AR ByteLocale/es ByteFullLocale/es Spark/1.8.4-alpha.30 AppVersion/41.7.4  PIA/2.8.4', NULL, 'Boca Toma', 'Argentina', -43.4441000, -65.9420000, 'media', 0, 'nuevo'),
(430, 251, '27389351003', 'DETTLER GIULIANA ABIGAIL', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 03:06:17', NULL, NULL, '170.155.12.4', 'Mozilla/5.0 (Linux; Android 15; 25078RA3EL Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'La Plata', 'Argentina', -34.9309000, -57.9417000, 'media', 0, 'nuevo'),
(431, 252, '27389351003', 'DETTLER GIULIANA ABIGAIL', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 03:08:30', NULL, NULL, '170.155.12.4', 'Mozilla/5.0 (Linux; Android 15; 25078RA3EL Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'La Plata', 'Argentina', -34.9309000, -57.9417000, 'media', 0, 'nuevo'),
(432, 253, '27389351003', 'DETTLER GIULIANA ABIGAIL', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 03:15:27', NULL, NULL, '170.155.12.4', 'Mozilla/5.0 (Linux; Android 15; 25078RA3EL Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'La Plata', 'Argentina', -34.9309000, -57.9417000, 'media', 0, 'nuevo'),
(433, 254, '27424956606', 'GONZALEZ NADIA BELEN', 'COBRA_SUAF', '18', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 03:24:15', NULL, NULL, '2800:810:58b:8642:5b70:8872:1605:189f', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Laferrere', 'Argentina', -34.7503000, -58.5827000, 'media', 0, 'nuevo'),
(434, 255, '27424956606', 'GONZALEZ NADIA BELEN', 'COBRA_SUAF', '18', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 03:29:43', NULL, NULL, '2800:810:58b:8642:5b70:8872:1605:189f', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Laferrere', 'Argentina', -34.7503000, -58.5827000, 'media', 0, 'nuevo'),
(435, 256, '20308079423', 'ARROYO JULIO RAMON', 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 03:41:08', NULL, NULL, '186.14.155.2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Santa Teresa', 'Venezuela', 9.5651000, -68.8715000, 'media', 0, 'nuevo'),
(436, 257, '27444001017', 'ERAZO ESTEFANIA BRENDA', 'COBRA_AUH', '15', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 03:41:35', NULL, NULL, '186.124.123.118', 'Mozilla/5.0 (Linux; Android 15; SM-A155M Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024209030 AppName/musical_ly ByteLocale/es-419', NULL, 'General Mosconi', 'Argentina', -22.6000000, -63.8167000, 'media', 0, 'nuevo'),
(437, 258, '20470134519', 'EISENBART EZIO FRANCISCO', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 04:20:01', NULL, NULL, '38.255.105.32', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'San Isidro', 'Peru', -12.0965500, -77.0425800, 'media', 0, 'nuevo'),
(438, 259, '27269848052', 'PONCE NATALIA VERONICA', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 04:46:43', NULL, NULL, '2803:9800:94c0:9723:44ab:9281:1c5d:c529', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, 'Resistencia', 'Argentina', -27.4512000, -58.9865000, 'media', 0, 'nuevo'),
(439, 260, '27438364329', 'SALTO ROXANA BELEN', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 05:14:56', NULL, NULL, '148.227.73.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Safari/604.1 musical_ly_42.9.0 BytedanceWebview/d8a21c6', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 0, 'nuevo'),
(440, 261, '27456419505', 'SANABRIA DAIANA PAMELA', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 05:17:03', NULL, NULL, '181.169.187.232', 'Mozilla/5.0 (Linux; Android 12; moto g(60)s Build/S3RLS32.114-25-13; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/135.0.7049.78 Mobile Safari/537.36 musical_ly_2024209030 AppName/musical_ly ByteLocale/es-419', NULL, 'Rafaela', 'Argentina', -31.2655000, -61.4816000, 'media', 0, 'nuevo'),
(441, 262, '27456419505', 'SANABRIA DAIANA PAMELA', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 05:17:37', NULL, NULL, '181.169.187.232', 'Mozilla/5.0 (Linux; Android 12; moto g(60)s Build/S3RLS32.114-25-13; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/135.0.7049.78 Mobile Safari/537.36 musical_ly_2024209030 AppName/musical_ly ByteLocale/es-419', NULL, 'Rafaela', 'Argentina', -31.2655000, -61.4816000, 'media', 0, 'nuevo'),
(442, 263, '20390497025', 'GALVAN CESAR MAXIMILIANO', 'COBRA_NEGRO', '17', NULL, 1, '{\"Respuesta\":{\"RiePedID\":150080,\"P_OK\":\"S\",\"P_Msj\":\"Empleado privado\",\"P_Ren\":0,\"P_Disp\":99999,\"P_Rpta\":\"[S][NEP]\",\"P_CliSexo\":\"M\",\"P_CliCUIT\":\"20-39049702-5\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"GALVAN\",\"P_Nom\":\"CESAR MAXIMILIANO\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{},\"I\":{},\"V\":{}}}', NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 05:54:18', NULL, NULL, '2800:2505:71:1b7b:cd81:5f5d:d90:2614', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', NULL, 'Córdoba', 'Argentina', -31.4057000, -64.1849000, 'media', 0, 'nuevo'),
(443, 264, '20118640889', 'PARADA SERGIO ALEJO', 'JUBILADO', '15', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 06:04:43', NULL, NULL, '181.97.225.114', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Orán', 'Argentina', -23.1361000, -64.3231000, 'media', 0, 'nuevo'),
(444, 265, '20408742723', 'YADO AXEL EMANUEL', NULL, '14', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 07:31:56', NULL, NULL, '190.183.4.196', 'Mozilla/5.0 (Linux; Android 15; moto g54 5G Build/V1TDS35H.83-20-5-6; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.114 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (35/15; 400dpi; 1080x2400; motorola; moto g54 5G; cancunf; mt6855; es_US; 844485225; IABMV/1)', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(445, 266, '23447492504', 'RUIZ DIAZ CONSTANZA DANIELA', NULL, '26', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 08:34:29', NULL, NULL, '2800:2330:2700:f7:e53c:a63f:9492:795b', 'Mozilla/5.0 (Linux; Android 15; 23129RA5FL Build/AQ3A.240829.003; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'Buenos Aires', 'Argentina', -34.6142000, -58.3811000, 'media', 0, 'nuevo'),
(446, 267, '27928605782', 'ROJAS ROXANA CATALINA', 'MONO_RESPONSABLE', '14', NULL, 1, '{\"Respuesta\":{\"RiePedID\":150084,\"P_OK\":\"S\",\"P_Msj\":\"RECHAZO SISTEMA\",\"P_Ren\":0,\"P_Disp\":99999,\"P_Rpta\":\"[S][023]\",\"P_CliSexo\":\"F\",\"P_CliCUIT\":\"27-92860578-2\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"ROJAS\",\"P_Nom\":\"ROXANA CATALINA\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{},\"I\":{},\"V\":{}}}', NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 09:10:33', NULL, NULL, '45.180.63.143', 'Mozilla/5.0 (Linux; Android 14; SM-A055M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'Moreno', 'Argentina', -34.6340100, -58.7913800, 'media', 0, 'nuevo'),
(447, 268, '27467295832', 'SOTO VIVIANA ITATI', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 09:17:47', NULL, NULL, '190.183.247.184', 'Mozilla/5.0 (Linux; Android 15; SM-A145M Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (35/15; 420dpi; 1080x2408; samsung; SM-A145M; a14; s5e3830; es_US; 844485262; IABMV/1)', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(448, 269, '27467295832', 'SOTO VIVIANA ITATI', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 09:20:47', NULL, NULL, '190.183.247.184', 'Mozilla/5.0 (Linux; Android 15; SM-A145M Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (35/15; 420dpi; 1080x2408; samsung; SM-A145M; a14; s5e3830; es_US; 844485262; IABMV/1)', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(449, 270, '27467295832', 'SOTO VIVIANA ITATI', 'COBRA_AUH', '11', NULL, 1, '{\"Respuesta\":{\"RiePedID\":150087,\"P_OK\":\"S\",\"P_Msj\":\"Empleado publico\",\"P_Ren\":2,\"P_Disp\":99999,\"P_Rpta\":\"[N][GES]\",\"P_CliSexo\":\"F\",\"P_CliCUIT\":\"27-46729583-2\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"SOTO\",\"P_Nom\":\"VIVIANA ITATI\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{\"RR\":[{\"Ren\":1,\"CapF\":0,\"Cap\":0,\"Cts\":0,\"Cta\":0,\"TNA\":0,\"TEA\":0,\"TEM\":0,\"CFT\":0,\"TIR\":0,\"Linea\":1,\"Cupo\":\"Sin renovación\",\"CapR\":0,\"LineaDes\":\"\",\"TipoOferta\":0,\"PrimerVto\":null,\"CFT2\":0,\"TEM2\":0,\"TEA2\":0},{\"Ren\":2,\"CapF\":0,\"Cap\":0,\"Cts\":0,\"Cta\":0,\"TNA\":0,\"TEA\":0,\"TEM\":0,\"CFT\":0,\"TIR\":0,\"Linea\":1,\"Cupo\":\"Sin renovación\",\"CapR\":0,\"LineaDes\":\"\",\"TipoOferta\":0,\"PrimerVto\":null,\"CFT2\":0,\"TEM2\":0,\"TEA2\":0}]},\"I\":{},\"V\":{}}}', NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 09:26:46', NULL, NULL, '190.183.247.184', 'Mozilla/5.0 (Linux; Android 15; SM-A145M Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (35/15; 420dpi; 1080x2408; samsung; SM-A145M; a14; s5e3830; es_US; 844485262; IABMV/1)', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(450, 271, '27334867930', 'ALMADA MARIA SOLEDAD', 'COBRA_AUH', '15', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 09:49:21', NULL, NULL, '2803:580:82a:5d00:f05f:3533:bbff:7ed9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Garupa', 'Argentina', -27.4831500, -55.8292400, 'media', 0, 'nuevo'),
(451, 272, '27327814872', 'SOLOAGA ERICA ANALIA', 'COBRA_AUH', '11', NULL, 1, '{\"Respuesta\":{\"RiePedID\":150090,\"P_OK\":\"S\",\"P_Msj\":\"CARGA REPETIDA\",\"P_Ren\":0,\"P_Disp\":99999,\"P_Rpta\":\"[S][12]\",\"P_CliSexo\":\"F\",\"P_CliCUIT\":\"27-32781487-2\",\"P_CliCliFecNac\":null,\"P_BloqHasta\":null,\"P_EsCliente\":\"N\",\"P_PrestamosVigentes\":0,\"P_Ape\":\"SOLOAGA\",\"P_Nom\":\"ERICA ANALIA\",\"P_ECUIT\":\"\",\"P_ERZ\":\"\",\"P_Parm\":\"\",\"P_DomFiscal\":\"\",\"P_DomAlter\":\"\",\"P_HaberBruto\":0,\"P_HaberNeto\":0,\"P_Actividad\":0,\"P_EstCiv\":\"\",\"P_RelTrCod\":0,\"P_OfertaMaxima\":0,\"P_HaberOfertar\":0,\"R\":{},\"I\":{},\"V\":{}}}', NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 09:50:36', NULL, NULL, '201.213.76.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Resistencia', 'Argentina', -27.4512000, -58.9865000, 'media', 0, 'nuevo'),
(452, 273, '20403538850', 'CORBINO ALAN', NULL, '13', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 09:56:09', NULL, NULL, '186.141.140.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Buenos Aires', 'Argentina', -34.6142000, -58.3811000, 'media', 0, 'nuevo'),
(453, 274, '27138450703', 'LOPEZ MATORRAS MARTA ALICIA', 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 10:33:47', NULL, NULL, '2803:9800:b405:80ea:d977:881d:159f:504a', 'Mozilla/5.0 (Linux; Android 13; SM-A035M Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Channel/release AppName/ultralite app_version/41.8.3 Region/AR ByteLocale/es ByteFullLocale/es Spark/1.8.4-alpha.30 AppVersion/41.8.3  PIA/2.8.4', NULL, 'Salta', 'Argentina', -24.7831000, -65.4111000, 'media', 0, 'nuevo'),
(454, 275, '20170163959', 'ALMIRON CARLOS ROBERTO', NULL, '26', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 11:20:33', NULL, NULL, '190.220.147.190', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 0, 'nuevo'),
(455, 276, '27357673939', 'DIAZ DANIELA JOHANA', NULL, '11', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 11:25:43', NULL, NULL, '181.25.218.157', 'Mozilla/5.0 (Linux; Android 15; moto g75 5G Build/V1UQS35H.103-18-4; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.105 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/542.0.0.46.151;IABMV/1;]', NULL, 'San Luis', 'Argentina', -33.2991000, -66.3547000, 'media', 0, 'nuevo'),
(456, 277, '27301638073', 'SEGOVIA LIDIA LUISA', 'COBRA_NEGRO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 12:13:04', NULL, NULL, '190.196.234.5', 'Mozilla/5.0 (Linux; Android 15; 23053RN02A Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 musical_ly_2024209030 AppName/musical_ly ByteLocale/es-419', NULL, 'Roque Pérez', 'Argentina', -35.4199000, -59.3362000, 'media', 0, 'nuevo'),
(457, 278, '20403538850', 'CORBINO ALAN', NULL, '13', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 12:19:52', NULL, NULL, '186.141.140.8', 'Mozilla/5.0 (Linux; Android 15; SM-A146M Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 Instagram 410.0.0.53.71 Android (35/15; 450dpi; 1080x2408; samsung; SM-A146M; a14x; s5e8535; es_US; 844485252; IABMV/1)', NULL, 'Buenos Aires', 'Argentina', -34.6142000, -58.3811000, 'media', 0, 'nuevo'),
(458, 279, '20353274083', 'ESCURDIA DAVID EDUARDO', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 12:25:05', NULL, NULL, '181.192.101.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, 'Posadas', 'Argentina', -27.3833000, -55.8833000, 'media', 0, 'nuevo'),
(459, 280, '27317779408', 'PAZ NATALI ELISABETH', 'COBRA_NEGRO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 12:40:20', NULL, NULL, '45.173.213.218', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Catriel', 'Argentina', -37.8628000, -67.8331000, 'media', 0, 'nuevo'),
(460, 281, '27265284480', 'CADENA YOLANDA BEATRIZ', 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 12:42:30', NULL, NULL, '148.227.73.52', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Buenos Aires', 'Argentina', -34.6131500, -58.3772300, 'media', 0, 'nuevo'),
(461, 282, '27322907082', 'RUIZ DIAZ CABANAS LILIAN DAIANA', 'COBRA_AUH', '12', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 12:53:20', NULL, NULL, '201.220.18.170', 'Mozilla/5.0 (Linux; Android 15; SM-A145M Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es', NULL, 'La Plata', 'Argentina', -34.9309000, -57.9417000, 'media', 70, 'nuevo'),
(462, 283, '27389351003', 'DETTLER GIULIANA ABIGAIL', NULL, NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 13:15:16', NULL, NULL, '186.134.95.174', 'Mozilla/5.0 (Linux; Android 15; 25078RA3EL Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'Dolores', 'Argentina', -36.3354000, -57.6653000, 'media', 0, 'nuevo'),
(463, 284, '20345218484', 'GONZALEZ FABIAN DAVID', 'COBRA_NEGRO', '12', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 13:25:44', NULL, NULL, '45.181.130.165', 'Mozilla/5.0 (Linux; Android 14; SM-A135M Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.35 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'El Talar', 'Argentina', -34.4461000, -58.6458000, 'media', 0, 'nuevo'),
(464, 285, '27411668326', 'CACERES YAMILA ABIGAIL', 'COBRA_AUH', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 13:46:46', NULL, NULL, '2800:af0:10cd:8986:3606:ad50:613e:3687', 'Mozilla/5.0 (Linux; Android 15; 2409BRN2CL Build/AP3A.240905.015.A2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 musical_ly_2024300030 AppName/musical_ly ByteLocale/es-419', NULL, 'José C. Paz', 'Argentina', -34.5029000, -58.7512000, 'media', 0, 'nuevo'),
(465, 286, '23377672844', 'SALVAGIOT LEANDRA FABIANA', 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 13:47:16', NULL, NULL, '181.9.226.174', 'Mozilla/5.0 (Linux; Android 14; Infinix X6531B Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 musical_ly_2024206040 AppName/musical_ly ByteLocale/es', NULL, 'Córdoba', 'Argentina', -31.4057000, -64.1849000, 'media', 0, 'nuevo'),
(466, 287, '20374925149', 'VEGA CARLOS EUGENIO NICOLAS', NULL, '26', NULL, 0, NULL, NULL, 1, 'chatbot', 'esperando_asesor', NULL, '2025-12-18 13:52:49', NULL, NULL, '2800:2505:54:1379:1882:525f:81f0:c4e5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, 'Rosario', 'Argentina', -32.9540000, -60.6634000, 'media', 0, 'nuevo'),
(467, 288, '23377672844', 'SALVAGIOT LEANDRA FABIANA', 'JUBILADO', NULL, NULL, 0, NULL, NULL, 1, 'chatbot', 'pendiente', NULL, '2025-12-18 13:53:45', NULL, NULL, '181.9.226.174', 'Mozilla/5.0 (Linux; Android 14; Infinix X6531B Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/142.0.7444.171 Mobile Safari/537.36 musical_ly_2024206040 AppName/musical_ly ByteLocale/es', NULL, 'Córdoba', 'Argentina', -31.4057000, -64.1849000, 'media', 0, 'nuevo');

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
(77, 1360, 392, 'audio_1766004061807.webm', '6943155e535ec_1766004062.webm', 'audio/webm', 55358, 'uploads/6943155e535ec_1766004062.webm', '2025-12-17 20:41:02'),
(78, 1382, 400, 'audio_1766018350736.webm', '69434d301df3a_1766018352.webm', 'audio/webm', 294926, 'uploads/69434d301df3a_1766018352.webm', '2025-12-18 00:39:12'),
(79, 1452, 439, 'audio_1766035170298.webm', '69438ee40d0d5_1766035172.webm', 'audio/webm', 158800, 'uploads/69438ee40d0d5_1766035172.webm', '2025-12-18 05:19:32');

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
(6, 254, 27, 2, 'aceptada', '2025-12-15 15:13:49', '2025-12-15 15:13:58'),
(7, 299, 2, 27, 'aceptada', '2025-12-16 22:06:56', '2025-12-16 22:07:07'),
(8, 299, 27, 2, 'aceptada', '2025-12-16 22:13:21', '2025-12-16 22:13:30'),
(9, 306, 2, 27, 'aceptada', '2025-12-17 01:17:27', '2025-12-17 01:17:31'),
(10, 307, 2, 27, 'aceptada', '2025-12-17 01:48:13', '2025-12-17 01:48:17'),
(11, 326, 2, 27, 'aceptada', '2025-12-17 03:41:41', '2025-12-17 03:41:44'),
(12, 334, 27, 2, 'aceptada', '2025-12-17 14:29:26', '2025-12-17 14:29:35');

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
(119, 199, 'thomec86@gmail.com', NULL, '1127390608', '2025-12-17 17:45:49', '2025-12-17 17:46:10'),
(120, 200, 'renataa2@gmail.com', NULL, '1123678903', '2025-12-17 17:51:42', '2025-12-17 17:52:08'),
(121, 201, 'sadsada@gmail.com', NULL, '1112345678', '2025-12-17 17:56:16', '2025-12-17 17:56:45'),
(122, 202, 'temp_1765994795_2571@cliente.com', NULL, '1127390105', '2025-12-17 18:06:35', '2025-12-17 18:06:49'),
(123, 203, 'prueba@gmail.com', NULL, '3764565656', '2025-12-17 18:26:55', '2025-12-17 18:27:31'),
(124, 204, 'temp_1765996270_7876@cliente.com', NULL, '3764533222', '2025-12-17 18:31:10', '2025-12-17 18:31:46'),
(125, 205, 'temp_1765996420_4161@cliente.com', NULL, '3764323232', '2025-12-17 18:33:40', '2025-12-17 18:34:40'),
(126, 206, 'temp_1765996663_4627@cliente.com', NULL, '3764353535', '2025-12-17 18:37:43', '2025-12-17 18:38:01'),
(127, 207, 'xd123@gmail.com', NULL, '3764112183', '2025-12-17 18:39:16', '2025-12-17 18:40:20'),
(128, 208, 'prueba@hotmail.com', NULL, '3764373737', '2025-12-17 20:10:38', '2025-12-17 20:11:21'),
(129, 209, 'yessiicaramos22@gmail.com', NULL, '3764805039', '2025-12-17 20:34:50', '2025-12-17 20:39:39'),
(130, 214, 'temp_1766011522_4939@cliente.com', NULL, '', '2025-12-17 22:45:22', '2025-12-17 22:45:22'),
(131, 215, 'manatinicarina86@gmail.com', NULL, '1154746110', '2025-12-17 23:20:17', '2025-12-17 23:21:37'),
(132, 216, 'luisalberto251268@gmail.com', NULL, '2994598939', '2025-12-17 23:42:56', '2025-12-17 23:44:38'),
(133, 217, 'Tere_miisniietos@outlook.com', NULL, '1136708885', '2025-12-17 23:54:44', '2025-12-17 23:56:22'),
(134, 218, 'david@hotmail.com', NULL, '3764125555', '2025-12-17 23:58:12', '2025-12-17 23:59:49'),
(135, 219, 'temp_1766017083_7550@cliente.com', NULL, '', '2025-12-18 00:18:03', '2025-12-18 00:18:03'),
(136, 220, 'fthaiel24@gmail.com', NULL, '1173635893', '2025-12-18 00:29:27', '2025-12-18 00:30:47'),
(137, 221, 'yoanaferreyra05@gmail.com', NULL, '3416960075', '2025-12-18 00:35:58', '2025-12-18 00:37:58'),
(138, 222, 'Miligonza2011@gmail.com', NULL, '2664152841', '2025-12-18 00:36:00', '2025-12-18 00:37:54'),
(139, 223, 'temp_1766018254_2504@cliente.com', NULL, '', '2025-12-18 00:37:34', '2025-12-18 00:37:34'),
(140, 224, 'temp_1766018347_6330@cliente.com', NULL, '', '2025-12-18 00:39:07', '2025-12-18 00:39:07'),
(141, 226, 'Venialgodaniel1990@gmail.com', NULL, '1132345069', '2025-12-18 00:39:34', '2025-12-18 00:40:53'),
(142, 225, 'florenciamerlinda@gmail.com', NULL, '3624013958', '2025-12-18 00:39:39', '2025-12-18 00:40:46'),
(143, 227, 'temp_1766018480_3365@cliente.com', NULL, '', '2025-12-18 00:41:20', '2025-12-18 00:41:20'),
(144, 230, 'temp_1766018875_9534@cliente.com', NULL, '', '2025-12-18 00:47:55', '2025-12-18 00:47:55'),
(145, 231, 'mauricioavellaneda830@gmail.com', NULL, '2901457645', '2025-12-18 00:49:49', '2025-12-18 00:53:45'),
(146, 232, 'ivandavidvillalvacortez1982@gmail.com', NULL, '2643187682', '2025-12-18 00:55:22', '2025-12-18 01:03:11'),
(147, 233, 'jarmitjara49@gmail.com', NULL, '3765368899', '2025-12-18 00:58:33', '2025-12-18 01:00:48'),
(148, 234, 'temp_1766020277_4722@cliente.com', NULL, '', '2025-12-18 01:11:17', '2025-12-18 01:11:17'),
(149, 235, 'temp_1766020289_5097@cliente.com', NULL, '', '2025-12-18 01:11:29', '2025-12-18 01:11:29'),
(150, 236, 'vazquezjosefinabeatriz@gmail.com', NULL, '3875788649', '2025-12-18 01:17:14', '2025-12-18 01:18:57'),
(151, 237, 'reartesmanuel02@gmail.com', NULL, '3834062651', '2025-12-18 01:21:33', '2025-12-18 01:23:08'),
(152, 238, 'temp_1766020956_4058@cliente.com', NULL, '', '2025-12-18 01:22:36', '2025-12-18 01:22:36'),
(153, 239, 'Mbelen.lorenzo1@gmail.com', NULL, '1128937613', '2025-12-18 01:35:01', '2025-12-18 01:36:06'),
(154, 240, 'Mercedes.ibarra0716@gmail.com', NULL, '3875670149', '2025-12-18 01:39:05', '2025-12-18 01:39:58'),
(155, 241, 'nmarialourdes0@gmail.com', NULL, '3795567648', '2025-12-18 01:54:10', '2025-12-18 01:56:52'),
(156, 242, 'temp_1766022866_3611@cliente.com', NULL, '', '2025-12-18 01:54:26', '2025-12-18 01:54:26'),
(157, 243, 'temp_1766023014_9690@cliente.com', NULL, '', '2025-12-18 01:56:54', '2025-12-18 01:56:54'),
(158, 244, 'temp_1766023057_8010@cliente.com', NULL, '', '2025-12-18 01:57:37', '2025-12-18 01:57:37'),
(159, 245, 'temp_1766023303_9588@cliente.com', NULL, '', '2025-12-18 02:01:43', '2025-12-18 02:01:43'),
(160, 246, 'temp_1766023529_6105@cliente.com', NULL, '', '2025-12-18 02:05:29', '2025-12-18 02:05:29'),
(161, 247, 'Tomasdm87@gmail.com', NULL, '3512580677', '2025-12-18 02:09:11', '2025-12-18 02:10:46'),
(162, 248, 'paolarodriguez909011@gmail.com', NULL, '3875767010', '2025-12-18 02:15:47', '2025-12-18 02:19:39'),
(163, 249, 'Ezequielfernando1907@gmail.com', NULL, '3416396138', '2025-12-18 02:19:13', '2025-12-18 02:20:49'),
(164, 250, 'arruayohana5@gmail.com', NULL, '2804517365', '2025-12-18 02:23:22', '2025-12-18 02:25:33'),
(165, 251, 'temp_1766027180_5033@cliente.com', NULL, '', '2025-12-18 03:06:20', '2025-12-18 03:06:20'),
(166, 252, 'temp_1766027314_6227@cliente.com', NULL, '', '2025-12-18 03:08:34', '2025-12-18 03:08:34'),
(167, 253, 'temp_1766027731_9510@cliente.com', NULL, '', '2025-12-18 03:15:31', '2025-12-18 03:15:31'),
(168, 254, 'ng7792374@gmail.com', NULL, '1160508956', '2025-12-18 03:24:19', '2025-12-18 03:26:18'),
(169, 255, 'temp_1766028592_5923@cliente.com', NULL, '1160508956', '2025-12-18 03:29:52', '2025-12-18 03:30:30'),
(170, 256, 'temp_1766029276_9530@cliente.com', NULL, '', '2025-12-18 03:41:16', '2025-12-18 03:41:16'),
(171, 257, 'Estefierazo2019@gmail.com', NULL, '3457386232', '2025-12-18 03:41:42', '2025-12-18 03:46:17'),
(172, 258, 'temp_1766031605_9151@cliente.com', NULL, '', '2025-12-18 04:20:05', '2025-12-18 04:20:05'),
(173, 259, 'temp_1766033215_8074@cliente.com', NULL, '', '2025-12-18 04:46:55', '2025-12-18 04:46:55'),
(174, 260, 'temp_1766034911_8960@cliente.com', NULL, '', '2025-12-18 05:15:11', '2025-12-18 05:15:11'),
(175, 261, 'temp_1766035027_1297@cliente.com', NULL, '', '2025-12-18 05:17:07', '2025-12-18 05:17:07'),
(176, 262, 'temp_1766035061_1567@cliente.com', NULL, '', '2025-12-18 05:17:41', '2025-12-18 05:17:41'),
(177, 263, 'galvancesar140@gmail.com', NULL, '2903570728', '2025-12-18 05:54:25', '2025-12-18 05:59:42'),
(178, 264, 'alejosergioparada@gmail.com', NULL, '3878314086', '2025-12-18 06:04:47', '2025-12-18 06:06:30'),
(179, 265, 'axelyado24@gmail.com', NULL, '3765397072', '2025-12-18 07:31:59', '2025-12-18 07:32:41'),
(180, 266, 'cruizdiaz0553@gmail.com', NULL, '1172878582', '2025-12-18 08:34:32', '2025-12-18 08:35:28'),
(181, 267, 'Roxyrojas960@gmail.comr', NULL, '1126330400', '2025-12-18 09:10:39', '2025-12-18 09:12:09'),
(182, 268, 'temp_1766049471_5146@cliente.com', NULL, '', '2025-12-18 09:17:51', '2025-12-18 09:17:51'),
(183, 269, 'temp_1766049653_8034@cliente.com', NULL, '', '2025-12-18 09:20:53', '2025-12-18 09:20:53'),
(184, 270, 'sotovivi01@gmail.com', NULL, '3764687069', '2025-12-18 09:26:49', '2025-12-18 09:30:23'),
(185, 271, 'almadamary163@gmail.com', NULL, '3764966233', '2025-12-18 09:49:29', '2025-12-18 09:52:07'),
(186, 272, 'annaaliitaa31@gmail.com', NULL, '3625178043', '2025-12-18 09:50:41', '2025-12-18 09:53:09'),
(187, 273, 'Corbinoalan81@gmail.com', NULL, '1133953658', '2025-12-18 09:56:13', '2025-12-18 09:57:36'),
(188, 274, 'temp_1766054035_5643@cliente.com', NULL, '', '2025-12-18 10:33:55', '2025-12-18 10:33:55'),
(189, 275, 'Ezequiel13almiron@gmail.com', NULL, '3624835363', '2025-12-18 11:20:38', '2025-12-18 11:32:58'),
(190, 276, 'danielajohanadiaz@gmail.com', NULL, '2665069382', '2025-12-18 11:25:53', '2025-12-18 11:27:40'),
(191, 277, 'temp_1766059995_1005@cliente.com', NULL, '', '2025-12-18 12:13:15', '2025-12-18 12:13:15'),
(192, 278, 'temp_1766060397_4717@cliente.com', NULL, '1133953658', '2025-12-18 12:19:57', '2025-12-18 12:20:22'),
(193, 279, 'temp_1766060710_1825@cliente.com', NULL, '', '2025-12-18 12:25:10', '2025-12-18 12:25:10'),
(194, 280, 'temp_1766061623_3358@cliente.com', NULL, '', '2025-12-18 12:40:23', '2025-12-18 12:40:23'),
(195, 281, 'temp_1766061758_4133@cliente.com', NULL, '', '2025-12-18 12:42:38', '2025-12-18 12:42:38'),
(196, 282, 'Lilianrdc86@gmail.com', NULL, '2215715153', '2025-12-18 12:53:28', '2025-12-18 12:54:48'),
(197, 283, 'temp_1766063719_4321@cliente.com', NULL, '', '2025-12-18 13:15:19', '2025-12-18 13:15:19'),
(198, 284, 'Fabii1an26.gonzalez@gmail.com', NULL, '1157246267', '2025-12-18 13:25:47', '2025-12-18 13:27:43'),
(199, 285, 'temp_1766065610_1745@cliente.com', NULL, '', '2025-12-18 13:46:50', '2025-12-18 13:46:50'),
(200, 286, 'temp_1766065644_4334@cliente.com', NULL, '', '2025-12-18 13:47:24', '2025-12-18 13:47:24'),
(201, 287, 'vegaah1893@gmail.com', NULL, '3804616323', '2025-12-18 13:52:52', '2025-12-18 13:53:59'),
(202, 288, 'temp_1766066028_7110@cliente.com', NULL, '', '2025-12-18 13:53:48', '2025-12-18 13:53:48');

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
(150, 199, '41727387', NULL, NULL, NULL, NULL),
(151, 200, '58853749', NULL, NULL, NULL, NULL),
(152, 201, '98765432', NULL, NULL, NULL, NULL),
(153, 202, '41727387', NULL, NULL, NULL, NULL),
(154, 203, '22912631', NULL, NULL, NULL, NULL),
(155, 204, '21776429', NULL, NULL, NULL, NULL),
(156, 205, '34821659', NULL, NULL, NULL, NULL),
(157, 206, '27200213', NULL, NULL, NULL, NULL),
(158, 207, '42515616', NULL, NULL, NULL, NULL),
(159, 208, '43756993', NULL, NULL, NULL, NULL),
(160, 209, '39222997', NULL, NULL, NULL, NULL),
(161, 214, '24325120', NULL, NULL, NULL, NULL),
(162, 215, '32330347', NULL, NULL, NULL, NULL),
(163, 216, '20436631', NULL, NULL, NULL, NULL),
(164, 217, '37841648', NULL, NULL, NULL, NULL),
(165, 218, '35327408', NULL, NULL, NULL, NULL),
(166, 219, 'L29276306', NULL, NULL, NULL, NULL),
(167, 220, '40541997', NULL, NULL, NULL, NULL),
(168, 222, '43491030', NULL, NULL, NULL, NULL),
(169, 221, '36678932', NULL, NULL, NULL, NULL),
(170, 223, '3.599105', NULL, NULL, NULL, NULL),
(171, 224, '37219762', NULL, NULL, NULL, NULL),
(172, 226, '35008018', NULL, NULL, NULL, NULL),
(173, 225, '38914493', NULL, NULL, NULL, NULL),
(174, 227, '35725182', NULL, NULL, NULL, NULL),
(175, 230, '35725182', NULL, NULL, NULL, NULL),
(176, 231, '45788615', NULL, NULL, NULL, NULL),
(177, 232, '29557729', NULL, NULL, NULL, NULL),
(178, 233, '17170749', NULL, NULL, NULL, NULL),
(179, 234, '35725182', NULL, NULL, NULL, NULL),
(180, 236, '43687681', NULL, NULL, NULL, NULL),
(181, 237, '31450561', NULL, NULL, NULL, NULL),
(182, 238, '37825551', NULL, NULL, NULL, NULL),
(183, 239, '37277318', NULL, NULL, NULL, NULL),
(184, 240, '46532178', NULL, NULL, NULL, NULL),
(185, 241, '40260733', NULL, NULL, NULL, NULL),
(186, 242, '38535090', NULL, NULL, NULL, NULL),
(187, 243, '38935100', NULL, NULL, NULL, NULL),
(188, 244, '34590301', NULL, NULL, NULL, NULL),
(189, 245, '33956861', NULL, NULL, NULL, NULL),
(190, 246, '37586610', NULL, NULL, NULL, NULL),
(191, 247, '33956861', NULL, NULL, NULL, NULL),
(192, 248, '41296301', NULL, NULL, NULL, NULL),
(193, 249, '40451042', NULL, NULL, NULL, NULL),
(194, 250, '28390367', NULL, NULL, NULL, NULL),
(195, 251, '38935100', NULL, NULL, NULL, NULL),
(196, 252, '38935100', NULL, NULL, NULL, NULL),
(197, 253, '38935100', NULL, NULL, NULL, NULL),
(198, 254, '42495660', NULL, NULL, NULL, NULL),
(199, 255, '42495660', NULL, NULL, NULL, NULL),
(200, 256, '30807942', NULL, NULL, NULL, NULL),
(201, 257, '44400101', NULL, NULL, NULL, NULL),
(202, 258, '47013451', NULL, NULL, NULL, NULL),
(203, 259, '26984805', NULL, NULL, NULL, NULL),
(204, 260, '43836432', NULL, NULL, NULL, NULL),
(205, 261, '45641950', NULL, NULL, NULL, NULL),
(206, 262, '45641950', NULL, NULL, NULL, NULL),
(207, 263, '39049702', NULL, NULL, NULL, NULL),
(208, 264, '11864088', NULL, NULL, NULL, NULL),
(209, 265, '40874272', NULL, NULL, NULL, NULL),
(210, 266, '44749250', NULL, NULL, NULL, NULL),
(211, 267, '92860578', NULL, NULL, NULL, NULL),
(212, 268, '46729583', NULL, NULL, NULL, NULL),
(213, 269, '46729583', NULL, NULL, NULL, NULL),
(214, 270, '46729583', NULL, NULL, NULL, NULL),
(215, 271, '33486793', NULL, NULL, NULL, NULL),
(216, 272, '32781487', NULL, NULL, NULL, NULL),
(217, 273, '40353885', NULL, NULL, NULL, NULL),
(218, 274, '13845070', NULL, NULL, NULL, NULL),
(219, 275, '17016395', NULL, NULL, NULL, NULL),
(220, 276, '35767393', NULL, NULL, NULL, NULL),
(221, 277, '30163807', NULL, NULL, NULL, NULL),
(222, 278, '40353885', NULL, NULL, NULL, NULL),
(223, 279, '35327408', NULL, NULL, NULL, NULL),
(224, 280, '31777940', NULL, NULL, NULL, NULL),
(225, 281, '26528448', NULL, NULL, NULL, NULL),
(226, 282, '32290708', NULL, NULL, NULL, NULL),
(227, 283, '38935100', NULL, NULL, NULL, NULL),
(228, 284, '34521848', NULL, NULL, NULL, NULL),
(229, 285, '41166832', NULL, NULL, NULL, NULL),
(230, 286, '37767284', NULL, NULL, NULL, NULL),
(231, 287, '37492514', NULL, NULL, NULL, NULL),
(232, 288, '37767284', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_eventos`
--

CREATE TABLE `cliente_eventos` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_notas`
--

CREATE TABLE `cliente_notas` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nota` text NOT NULL,
  `tipo` varchar(50) DEFAULT 'general',
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_tags`
--

CREATE TABLE `cliente_tags` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `color` varchar(20) DEFAULT '#3b82f6'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_uid` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`id`, `chat_id`, `emisor`, `usuario_id`, `mensaje`, `tiene_archivo`, `fecha`, `request_uid`) VALUES
(1344, 382, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 17:46:11', NULL),
(1345, 383, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 17:52:10', NULL),
(1346, 384, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 17:56:45', NULL),
(1347, 385, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 18:06:55', NULL),
(1348, 386, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 18:27:31', NULL),
(1349, 386, 'cliente', NULL, 'hola', 0, '2025-12-17 18:27:34', NULL),
(1350, 387, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 18:32:06', NULL),
(1351, 387, 'cliente', NULL, 'ok', 0, '2025-12-17 18:32:10', NULL),
(1352, 388, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 18:34:59', NULL),
(1353, 389, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 18:38:24', NULL),
(1354, 389, 'asesor', NULL, 'hola', 0, '2025-12-17 18:39:08', NULL),
(1355, 390, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 18:40:20', NULL),
(1356, 391, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 20:11:21', NULL),
(1357, 392, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 20:39:39', NULL),
(1358, 392, 'asesor', NULL, 'Hola yesi', 0, '2025-12-17 20:40:49', NULL),
(1359, 392, 'asesor', NULL, 'soy David', 0, '2025-12-17 20:40:51', NULL),
(1360, 392, 'asesor', NULL, '', 1, '2025-12-17 20:41:02', NULL),
(1361, 394, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 23:21:37', NULL),
(1362, 394, 'cliente', NULL, 'Gracias', 0, '2025-12-17 23:21:54', NULL),
(1363, 395, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 23:44:38', NULL),
(1364, 395, 'cliente', NULL, 'Ok', 0, '2025-12-17 23:45:34', NULL),
(1365, 396, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 23:56:22', NULL),
(1366, 397, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-17 23:59:49', NULL),
(1367, 399, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 00:30:47', NULL),
(1368, 394, 'asesor', NULL, 'Hola', 0, '2025-12-18 00:34:17', NULL),
(1369, 394, 'asesor', NULL, 'estas?', 0, '2025-12-18 00:34:19', NULL),
(1370, 395, 'asesor', NULL, 'Hola Luis', 0, '2025-12-18 00:36:17', NULL),
(1371, 396, 'asesor', NULL, 'Hola Paola', 0, '2025-12-18 00:36:24', NULL),
(1372, 397, 'asesor', NULL, 'Hola David', 0, '2025-12-18 00:36:30', NULL),
(1373, 399, 'asesor', NULL, 'Hola Hector', 0, '2025-12-18 00:36:35', NULL),
(1374, 401, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 00:37:55', NULL),
(1375, 400, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 00:37:58', NULL),
(1376, 401, 'cliente', NULL, 'Gracias', 0, '2025-12-18 00:38:00', NULL),
(1377, 400, 'cliente', NULL, 'Ok', 0, '2025-12-18 00:38:14', NULL),
(1378, 400, 'asesor', NULL, 'Hola joana', 0, '2025-12-18 00:38:23', NULL),
(1379, 400, 'cliente', NULL, 'Hola', 0, '2025-12-18 00:38:30', NULL),
(1380, 401, 'asesor', NULL, 'Hola agustina', 0, '2025-12-18 00:38:34', NULL),
(1381, 400, 'cliente', NULL, '???', 0, '2025-12-18 00:39:11', NULL),
(1382, 400, 'asesor', NULL, '', 1, '2025-12-18 00:39:12', NULL),
(1383, 400, 'asesor', NULL, 'vos cobras suaf?', 0, '2025-12-18 00:39:40', NULL),
(1384, 404, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 00:40:46', NULL),
(1385, 405, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 00:40:53', NULL),
(1386, 400, 'cliente', NULL, 'Que tiene q ver con un préstamo', 0, '2025-12-18 00:40:56', NULL),
(1387, 405, 'cliente', NULL, 'Cuando!?', 0, '2025-12-18 00:41:02', NULL),
(1388, 400, 'asesor', NULL, 'disculpa no te entiendo', 0, '2025-12-18 00:41:29', NULL),
(1389, 400, 'cliente', NULL, 'Claro kiero un préstamo', 0, '2025-12-18 00:41:42', NULL),
(1390, 400, 'asesor', NULL, 'que cosa tiene que ver?', 0, '2025-12-18 00:41:43', NULL),
(1391, 400, 'cliente', NULL, 'Claro para q me otorguen un credito', 0, '2025-12-18 00:42:09', NULL),
(1392, 400, 'asesor', NULL, 'y yo te pregunte si cobras suaf? o que cobras?', 0, '2025-12-18 00:42:15', NULL),
(1393, 400, 'asesor', NULL, 'como que que tiene que ver?', 0, '2025-12-18 00:42:21', NULL),
(1394, 400, 'cliente', NULL, 'Trabajo en negro', 0, '2025-12-18 00:42:28', NULL),
(1395, 400, 'cliente', NULL, 'Y dudo q en negro te otorguen crédito', 0, '2025-12-18 00:42:45', NULL),
(1396, 400, 'asesor', NULL, 'claro por el momento no podriamos ayudarte', 0, '2025-12-18 00:43:03', NULL),
(1397, 400, 'asesor', NULL, 'trabajamos con recibo de sueldo', 0, '2025-12-18 00:43:10', NULL),
(1398, 400, 'asesor', NULL, 'jubilados y pensionados de anses', 0, '2025-12-18 00:43:19', NULL),
(1399, 400, 'cliente', NULL, 'Bueno', 0, '2025-12-18 00:43:21', NULL),
(1400, 400, 'asesor', NULL, 'empleados publicos tambien', 0, '2025-12-18 00:43:29', NULL),
(1401, 404, 'asesor', NULL, 'Hola Florencia', 0, '2025-12-18 00:44:35', NULL),
(1402, 405, 'asesor', NULL, 'Hola arnaldo', 0, '2025-12-18 00:44:41', NULL),
(1403, 405, 'asesor', NULL, 'tenes recibo de sueldo?', 0, '2025-12-18 00:45:32', NULL),
(1404, 410, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 00:53:46', NULL),
(1405, 410, 'cliente', NULL, 'Bueno gracias', 0, '2025-12-18 00:53:57', NULL),
(1406, 410, 'asesor', NULL, 'Hola Mauricio', 0, '2025-12-18 00:54:01', NULL),
(1407, 412, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 01:00:48', NULL),
(1408, 412, 'asesor', NULL, 'Hola Marisa', 0, '2025-12-18 01:01:02', NULL),
(1409, 412, 'cliente', NULL, 'Buenas noches', 0, '2025-12-18 01:01:14', NULL),
(1410, 412, 'asesor', NULL, 'Buenas noches como estas?', 0, '2025-12-18 01:01:35', NULL),
(1411, 412, 'cliente', NULL, 'Muy bien gracias', 0, '2025-12-18 01:01:48', NULL),
(1412, 411, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 01:03:11', NULL),
(1413, 411, 'cliente', NULL, 'Gracias', 0, '2025-12-18 01:03:44', NULL),
(1414, 412, 'cliente', NULL, 'Quería consultar sobre un préstamo', 0, '2025-12-18 01:03:47', NULL),
(1415, 412, 'cliente', NULL, 'Sisi', 0, '2025-12-18 01:08:40', NULL),
(1416, 412, 'asesor', NULL, 'tenes recibo de sueldo?', 0, '2025-12-18 01:10:25', NULL),
(1417, 412, 'cliente', NULL, 'Cobro jubilación', 0, '2025-12-18 01:10:45', NULL),
(1418, 411, 'asesor', NULL, 'Hola David enviame por aca tu ultimo recibo de sueldo', 0, '2025-12-18 01:10:53', NULL),
(1419, 412, 'asesor', NULL, 'por anses?', 0, '2025-12-18 01:11:08', NULL),
(1420, 412, 'cliente', NULL, 'Si', 0, '2025-12-18 01:11:27', NULL),
(1421, 412, 'asesor', NULL, 'enviame tu ultimo recibo por aca por favor', 0, '2025-12-18 01:11:39', NULL),
(1422, 412, 'cliente', NULL, 'Mañana puedo sacar del cajero y le paso xq ahora no tengo o puede ser de la aplicación del macro', 0, '2025-12-18 01:12:25', NULL),
(1423, 412, 'asesor', NULL, 'bueno envienos mañana', 0, '2025-12-18 01:14:29', NULL),
(1424, 412, 'cliente', NULL, 'Dale perfecto', 0, '2025-12-18 01:14:37', NULL),
(1425, 415, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 01:18:58', NULL),
(1426, 415, 'cliente', NULL, 'Ok', 0, '2025-12-18 01:19:08', NULL),
(1427, 416, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 01:23:08', NULL),
(1428, 416, 'cliente', NULL, 'Gracias', 0, '2025-12-18 01:23:15', NULL),
(1429, 415, 'asesor', NULL, 'Hola Josefina', 0, '2025-12-18 01:27:29', NULL),
(1430, 416, 'asesor', NULL, 'Hola Manuel', 0, '2025-12-18 01:27:40', NULL),
(1431, 418, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 01:36:06', NULL),
(1432, 418, 'cliente', NULL, 'Ok', 0, '2025-12-18 01:36:13', NULL),
(1433, 419, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 01:39:58', NULL),
(1434, 419, 'cliente', NULL, 'En que horario', 0, '2025-12-18 01:40:11', NULL),
(1435, 418, 'asesor', NULL, 'Hola Maria', 0, '2025-12-18 01:40:13', NULL),
(1436, 418, 'asesor', NULL, 'enviame tu ultimo recibo de sueldo', 0, '2025-12-18 01:40:21', NULL),
(1437, 419, 'asesor', NULL, 'Hola enviame tu ultimo recibo de sueldo', 0, '2025-12-18 01:40:35', NULL),
(1438, 420, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 01:56:56', NULL),
(1439, 420, 'cliente', NULL, 'Ok', 0, '2025-12-18 01:57:05', NULL),
(1440, 426, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 02:10:47', NULL),
(1441, 426, 'cliente', NULL, 'Mi celular es 3472 580677', 0, '2025-12-18 02:11:20', NULL),
(1442, 427, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 02:19:43', NULL),
(1443, 428, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 02:20:49', NULL),
(1444, 429, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 02:25:34', NULL),
(1445, 429, 'cliente', NULL, 'Gracias', 0, '2025-12-18 02:25:42', NULL),
(1446, 433, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 03:26:18', NULL),
(1447, 433, 'cliente', NULL, 'En cuanto', 0, '2025-12-18 03:26:42', NULL),
(1448, 433, 'cliente', NULL, '?', 0, '2025-12-18 03:27:34', NULL),
(1449, 434, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 03:30:46', NULL),
(1450, 436, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 03:46:17', NULL),
(1451, 436, 'cliente', NULL, 'Ok', 0, '2025-12-18 03:46:44', NULL),
(1452, 439, 'cliente', NULL, 'Archivo adjunto: audio_1766035170298.webm', 1, '2025-12-18 05:19:32', NULL),
(1453, 442, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 05:59:46', NULL),
(1454, 442, 'cliente', NULL, 'No', 0, '2025-12-18 06:00:06', NULL),
(1455, 443, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 06:06:31', NULL),
(1456, 444, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 07:32:41', NULL),
(1457, 445, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 08:35:28', NULL),
(1458, 446, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 09:12:11', NULL),
(1459, 446, 'cliente', NULL, 'Gracias', 0, '2025-12-18 09:12:23', NULL),
(1460, 446, 'cliente', NULL, 'Roxyrojas960@gmail.com', 0, '2025-12-18 09:12:58', NULL),
(1461, 449, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 09:30:26', NULL),
(1462, 449, 'cliente', NULL, 'bueno', 0, '2025-12-18 09:30:33', NULL),
(1463, 450, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 09:52:08', NULL),
(1464, 450, 'cliente', NULL, 'Gracias', 0, '2025-12-18 09:52:16', NULL),
(1465, 451, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 09:53:12', NULL),
(1466, 451, 'cliente', NULL, 'Gracias', 0, '2025-12-18 09:53:20', NULL),
(1467, 452, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 09:57:36', NULL),
(1468, 452, 'cliente', NULL, 'Gracias', 0, '2025-12-18 09:57:43', NULL),
(1469, 455, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 11:27:40', NULL),
(1470, 455, 'cliente', NULL, 'Gracias', 0, '2025-12-18 11:27:59', NULL),
(1471, 454, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 11:32:58', NULL),
(1472, 454, 'cliente', NULL, 'Ok', 0, '2025-12-18 11:33:04', NULL),
(1473, 457, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 12:20:33', NULL),
(1474, 457, 'cliente', NULL, 'Dale gracias aguardo', 0, '2025-12-18 12:20:50', NULL),
(1475, 461, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 12:54:48', NULL),
(1476, 463, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 13:27:43', NULL),
(1477, 420, 'asesor', NULL, 'Hola NAVARRO LEDEZMA MARIA LOURDES', 0, '2025-12-18 13:52:03', NULL),
(1478, 420, 'asesor', NULL, '¿Me dirías si trabajas o cobras algún beneficio?', 0, '2025-12-18 13:52:44', NULL),
(1479, 466, 'bot', NULL, '👨‍💼 Un asesor se comunicará contigo en breve', 0, '2025-12-18 13:53:59', NULL),
(1480, 426, 'asesor', NULL, 'Hola Tomas ¿como estas?', 0, '2025-12-18 13:55:50', NULL);

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
(177, 'DAVID', 'ESCURDIA', 'davidescurdia@hotmail.com', NULL, NULL, '$2y$10$YWZCsiyboNjbk1eRETArgOhncwvbaMaLzZO2mAajoxfWr1M/rHSFi', 'asesor', 'activo', '2025-12-17 17:09:50'),
(184, 'CANDELARIA', 'MARTINEZ', 'martinezcandelariabelen@gmail.com', NULL, NULL, '$2y$10$QHrOQtP5LXrDuNEqYl8dAeEMmtPoLiToOHK4laNjPO2FIan73f/lG', 'asesor', 'activo', '2025-12-17 17:21:06'),
(187, 'ALEJO', 'ESCOBAR', 'ALEESCOBAR430@GMAIL.COM', NULL, NULL, '$2y$10$eLFRrBanxpx9eKiCQBu4.OUuoCbj15Jm.vg2nFENUnWjkrbPZj1h.', 'asesor', 'activo', '2025-12-17 17:25:40'),
(188, 'TAMARA', 'ACOSTA', 'TAMIACO441@GMAIL.COM', NULL, NULL, '$2y$10$wgOoyyHs2eB/u2ZOf12VD.Roid5sceff6iIhQSgIrzYcGMyb0LTc6', 'asesor', 'activo', '2025-12-17 17:26:37'),
(199, 'CANO ESPINDOLA ALVARO THOMAS', NULL, 'thomec86@gmail.com', NULL, '1127390608', '$2y$10$hTmITtJb4flMFwaNPOtO3eyMT76QMu9XV5a4cn9MT94UrjGniVpt2', 'cliente', 'activo', '2025-12-17 17:45:48'),
(200, 'CANO RENATA CATALINA', NULL, 'renataa2@gmail.com', NULL, '1123678903', '$2y$10$cK5enYq9tUNgRfjS0bYHDO3Yp/uDPmxXfgBEng2rDCOVEEwzTIacy', 'cliente', 'activo', '2025-12-17 17:51:41'),
(201, 'PEPE PEPE', NULL, 'sadsada@gmail.com', NULL, '1112345678', '$2y$10$qqx.micRyC61NymmCE.mWOMwUd8GpFWMBOCHIhtd/dduNQ/kOQsZW', 'cliente', 'activo', '2025-12-17 17:56:14'),
(202, 'CANO ESPINDOLA ALVARO THOMAS', NULL, 'temp_1765994793_5276@cliente.com', NULL, '1127390105', '$2y$10$ShT/srJ2vtofHYDszff.Lea.yJQV2FOxVne8eoWEE0CzzdSKqeq3C', 'cliente', 'activo', '2025-12-17 18:06:34'),
(203, 'GONZALEZ PATRICIA LUCIA', NULL, 'prueba@gmail.com', NULL, '3764565656', '$2y$10$M.Qi4mLslEkSS1cxUU84U.UHbt4shlYqk5paBFpB5ulGskYmRZCZW', 'cliente', 'activo', '2025-12-17 18:26:53'),
(204, 'CARRIZO ADRIANA MONICA', NULL, 'temp_1765996268_4493@cliente.com', NULL, '3764533222', '$2y$10$AKbOF3EcRnb0tR7v14Z4s.D21lW4NXXzEOe6Fy669EF7B8zJx1KQ2', 'cliente', 'activo', '2025-12-17 18:31:08'),
(205, 'SERVIN VANESA ROSALIA', NULL, 'temp_1765996418_2812@cliente.com', NULL, '3764323232', '$2y$10$SqrPqlU.jvDxstBqhFHg2.xyXvsMzLK8sXns.CKwByF.F8E1qv3Pe', 'cliente', 'activo', '2025-12-17 18:33:38'),
(206, 'MOREIRA PAULA BEATRIZ', NULL, 'temp_1765996657_9102@cliente.com', NULL, '3764353535', '$2y$10$8YnrqUm46M.Uqsqt15DOPOFw97Q/8SXP7MYmKaxuNlqusWQ8wUxWm', 'cliente', 'activo', '2025-12-17 18:37:37'),
(207, 'VOSILAITIS ANTONIO AGUSTIN', NULL, 'xd123@gmail.com', NULL, '3764112183', '$2y$10$XtEnbNn89czIhSJfKT80Sek18ncTNoxMDK4p7xUYBiOlGkMCSDGEK', 'cliente', 'activo', '2025-12-17 18:39:12'),
(208, 'BRITEZ JULIAN ERNESTO', NULL, 'prueba@hotmail.com', NULL, '3764373737', '$2y$10$M5PtnPY9mKRqOaQT9XhkLuMZZkQk4Clf8TJ18LaaaBRe93TiqgICq', 'cliente', 'activo', '2025-12-17 20:10:36'),
(209, 'RAMOS YESICA ADRIANA', NULL, 'yessiicaramos22@gmail.com', NULL, '3764805039', '$2y$10$eQaaMScFsVyJwHQIUgqe9OOLkGJfiEdU7mue1hA.koMukWA8RWSwW', 'cliente', 'activo', '2025-12-17 20:34:44'),
(210, 'Sandra', 'Escurdia', 'sescurdia@prestamolider.com', NULL, NULL, '$2y$10$3ADVFtkRAo4Al5D6zMWy1e4PiRtjKpNjPyM3iBKtJgocVLcfNFRAS', 'asesor', 'activo', '2025-12-17 20:44:09'),
(211, 'Aylen', 'Escurdia', 'aescurdia@prestamolider.com', NULL, NULL, '$2y$10$.TpeLVgr.JfEdVBBt3BgueUs8iUlWKgj9xDqbCyw4.tRyhnzOxW12', 'asesor', 'activo', '2025-12-17 20:44:40'),
(212, 'Yesica', 'Ramos', 'yramos@prestamolider.com', NULL, NULL, '$2y$10$/XMMqa1KieG.ddk6sjq.AeNHgfBzMytvxndzj0MHf24mDaYyI69Qi', 'asesor', 'activo', '2025-12-17 20:44:58'),
(213, 'Brisa', 'Bonko', 'brisabonko@gmail.com', NULL, NULL, '$2y$10$Gzqx7T8GO9C63ABnmNH3oepbN6z.0SzNu4hMh0fcRkqaXz8ekZxoq', 'asesor', 'activo', '2025-12-17 20:46:09'),
(214, 'RIVAS MARIA LUCIANA', NULL, 'temp_1766011518_1212@cliente.com', NULL, NULL, '$2y$10$ROWXeToOjlj8w3USWulCn.Ck4XIYFjCPpTw1BbQ/9PefRkA34FrHq', 'cliente', 'activo', '2025-12-17 22:45:18'),
(215, 'MANATINI CARINA GUADALUPE', NULL, 'manatinicarina86@gmail.com', NULL, '1154746110', '$2y$10$R4I.APfLp1Hr8gip14mUBeTS..tPJSCNexODF/tE7rZBfrSRdVRuK', 'cliente', 'activo', '2025-12-17 23:20:07'),
(216, 'FUENTES LUIS ALBERTO', NULL, 'luisalberto251268@gmail.com', NULL, '2994598939', '$2y$10$LkZdB2KGBAmrJo3hXlMtA.NwONZjVRRbNDiFkmRwSRtUUwK3N0Hu.', 'cliente', 'activo', '2025-12-17 23:42:46'),
(217, 'BALMACEDA PAOLA JULIETA', NULL, 'Tere_miisniietos@outlook.com', NULL, '1136708885', '$2y$10$fPPr68LB1fIik4ssMnR.G.oquFp6mvzu9eMEdGVbtikBjfIeWrvWC', 'cliente', 'activo', '2025-12-17 23:54:39'),
(218, 'ESCURDIA DAVID EDUARDO', NULL, 'david@hotmail.com', NULL, '3764125555', '$2y$10$Ox2.0pebaYagQeqTXXR1g.kMGQvsUaOQUn/4lNKxNSZsfvYB2/lYq', 'cliente', 'activo', '2025-12-17 23:58:08'),
(219, 'ALGANARAZ PLAZA VIVIANA IRENE', NULL, 'temp_1766017079_6050@cliente.com', NULL, NULL, '$2y$10$tZaKAB2w9LessTSSPGl3HuhrgQ.KJEjpqoB56AIU1RevfKZCcSKVu', 'cliente', 'activo', '2025-12-18 00:17:59'),
(220, 'FERNANDEZ HECTOR NICOLAS', NULL, 'fthaiel24@gmail.com', NULL, '1173635893', '$2y$10$r2CwD7lcojjnB4zFFq52I.L6RCKNm1LG7OgL0Q81PZIllrBe.ca8C', 'cliente', 'activo', '2025-12-18 00:29:24'),
(221, 'FERREYRA JOANA ELISABETH', NULL, 'yoanaferreyra05@gmail.com', NULL, '3416960075', '$2y$10$SNg.KzHKW23t6pdl5Sfpu.aCcHRIpJXGTchwPaG1idMpBOieBpe92', 'cliente', 'activo', '2025-12-18 00:35:49'),
(222, 'GONZALEZ MILAGROS AGUSTINA', NULL, 'Miligonza2011@gmail.com', NULL, '2664152841', '$2y$10$5O9lkJt0lK2WpeA8ba7I1Os3v6gp4YXNVeYVV.bf/CwHiLPie3KwS', 'cliente', 'activo', '2025-12-18 00:35:53'),
(223, 'SILVA ANGELA', NULL, 'temp_1766018248_2849@cliente.com', NULL, NULL, '$2y$10$pGvfDzMw6LfS7olJAiYnTu61j6Ef4/jQPWjs1VKatEDqMzSCq7tsO', 'cliente', 'activo', '2025-12-18 00:37:29'),
(224, 'Miguel Ángel medeiro da rocha', NULL, 'temp_1766018341_7783@cliente.com', NULL, NULL, '$2y$10$.B647oxPwD6zpW6XO0uFa.4HI59EoSYg2RWFK2KMIQ/NayLLO/4ES', 'cliente', 'activo', '2025-12-18 00:39:01'),
(225, 'MAYDANA FLORENCIA MERLINDA', NULL, 'florenciamerlinda@gmail.com', NULL, '3624013958', '$2y$10$YxKte7Q4RjKXjjqZfU759.8mRTMYoHDEyL3MPzpevvhpiTQEPzWwG', 'cliente', 'activo', '2025-12-18 00:39:22'),
(226, 'VENIALGO ARNALDO DANIEL', NULL, 'Venialgodaniel1990@gmail.com', NULL, '1132345069', '$2y$10$afoq0mURNlmNiiPQAp3yKOQ8xm1eU41SYQU4o9/.VjVCiBFhs5LaC', 'cliente', 'activo', '2025-12-18 00:39:29'),
(227, 'BLAZQUEZ FABIAN EZEQUIEL', NULL, 'temp_1766018464_7780@cliente.com', NULL, NULL, '$2y$10$T4J2icxQ8fmPsilL6m4Yju4HLbg/6TUCCW3slAbGEcp1xIoifz2ZK', 'cliente', 'activo', '2025-12-18 00:41:04'),
(228, 'Fabian ezequiel blazquez', NULL, 'temp_1766018853_5727@cliente.com', NULL, NULL, '$2y$10$R1gx1778PafJPrdc3MWjsuUdxfgyeqDnQGtt.9cvy8rGjzVKfRdLK', 'cliente', 'activo', '2025-12-18 00:47:33'),
(229, 'Fabian ezequiel blazquez', NULL, 'temp_1766018861_7117@cliente.com', NULL, NULL, '$2y$10$N94mj004QGNNZifydU7uE.G3br83O7IQDWwzx69B0m2wHr4/R4Kna', 'cliente', 'activo', '2025-12-18 00:47:41'),
(230, 'BLAZQUEZ FABIAN EZEQUIEL', NULL, 'temp_1766018867_7593@cliente.com', NULL, NULL, '$2y$10$wXKgIogWrkDjY3bm1fv9huCIPpuwHxqW0JVpKJEAwtvu8O.BTvvt.', 'cliente', 'activo', '2025-12-18 00:47:47'),
(231, 'AVELLANEDA MAURICIO', NULL, 'mauricioavellaneda830@gmail.com', NULL, '2901457645', '$2y$10$KHXl.XjO7p7dHJpwBGKOJ.PS8hWL.WeTb0Ys.2N6gi4xMUQ0A/mjq', 'cliente', 'activo', '2025-12-18 00:49:42'),
(232, 'VILLALVA CORTEZ IVAN DAVID', NULL, 'ivandavidvillalvacortez1982@gmail.com', NULL, '2643187682', '$2y$10$tFlwwAadJn.ZlP1AIHScxevXgi1ftlFGhJ6f/C6h1LX6yEVPQU/Uq', 'cliente', 'activo', '2025-12-18 00:55:14'),
(233, 'CORREA MARISA ISABEL', NULL, 'jarmitjara49@gmail.com', NULL, '3765368899', '$2y$10$kCJ58f/gGp3UxMtg6oOzluKuBJ4zHkO67d.4Txx5a6dkUyrEvqzum', 'cliente', 'activo', '2025-12-18 00:58:28'),
(234, 'BLAZQUEZ FABIAN EZEQUIEL', NULL, 'temp_1766020272_5457@cliente.com', NULL, NULL, '$2y$10$/5GI59YbiefRVq9WypxgbOMZwUorfKIbO5ifoREK3tzZERavH5PXi', 'cliente', 'activo', '2025-12-18 01:11:12'),
(235, 'DARLING Monserrat Escalona toro', NULL, 'temp_1766020286_5422@cliente.com', NULL, NULL, '$2y$10$SZV.lZmwNnDGwHc1/lkqEubmxxkbWmhbP3ByKi9aYnYyOGU56t3dS', 'cliente', 'activo', '2025-12-18 01:11:26'),
(236, 'VAZQUEZ JOSEFINA BEATRIZ', NULL, 'vazquezjosefinabeatriz@gmail.com', NULL, '3875788649', '$2y$10$GaNb7BgxVJHqcZwfNt5ESun2WmKRwmvyC.reBiBiRHMvcE3kmAD.y', 'cliente', 'activo', '2025-12-18 01:17:05'),
(237, 'REARTES MANUEL BENITO', NULL, 'reartesmanuel02@gmail.com', NULL, '3834062651', '$2y$10$5MPKrII4Dh8iDQmoPKKCf.4.yMDLMbj2FGjQERtcNVqWibXYCwXfu', 'cliente', 'activo', '2025-12-18 01:21:26'),
(238, 'PONCE HERRERA JUAN MANUEL', NULL, 'temp_1766020954_7603@cliente.com', NULL, NULL, '$2y$10$G0.ql/GlRgwchbokY/umduISB3653I4giiBcySH9rjQ55zgf1uTi6', 'cliente', 'activo', '2025-12-18 01:22:34'),
(239, 'LORENZO MARIA BELEN', NULL, 'Mbelen.lorenzo1@gmail.com', NULL, '1128937613', '$2y$10$P4MhGD4CsgE0cYsrd6WQwOKtsRALBpeG50LSRrHF2W7T8JMLTfveq', 'cliente', 'activo', '2025-12-18 01:34:47'),
(240, 'IBARRA MERCEDES YASMILA', NULL, 'Mercedes.ibarra0716@gmail.com', NULL, '3875670149', '$2y$10$ENjdMF.vKLSTOvlRVYC2QuapLCGNBWx8RFMVKsIbFTFRtTkVVpHau', 'cliente', 'activo', '2025-12-18 01:39:02'),
(241, 'NAVARRO LEDEZMA MARIA LOURDES', NULL, 'nmarialourdes0@gmail.com', NULL, '3795567648', '$2y$10$J28cTAHQeIP1Zbp0SBhQS.KsESkScFF5Xk128UmCzkIaGEVBUvmrq', 'cliente', 'activo', '2025-12-18 01:54:07'),
(242, 'BRIATORE MARIA AGOSTINA', NULL, 'temp_1766022862_8410@cliente.com', NULL, NULL, '$2y$10$JYUt5Dl.vXLGxOvC9k.cK.2.x3NXpfatgBn4Xx66FW1q.3HYph9ce', 'cliente', 'activo', '2025-12-18 01:54:22'),
(243, 'DETTLER GIULIANA ABIGAIL', NULL, 'temp_1766023011_4383@cliente.com', NULL, NULL, '$2y$10$re7lZ/4NGzQRGOSDyJCfAubtcrUXS03Rsdq6OmQ4HmhA.j6OZk/gu', 'cliente', 'activo', '2025-12-18 01:56:51'),
(244, 'LUCERO MONICA SUSANA', NULL, 'temp_1766023050_1573@cliente.com', NULL, NULL, '$2y$10$.Yz5WAb.r.FPyaym2MftVOEChevOEYl/XVhZ03EC9ZLUo9e6f2XBK', 'cliente', 'activo', '2025-12-18 01:57:30'),
(245, 'MARTINEZ TOMAS DAVID', NULL, 'temp_1766023293_7337@cliente.com', NULL, NULL, '$2y$10$l/cSr1qkmjPVnSRKR9YEe.z2/f/i9jTAl7AEXVZHpvRnFGFw7xJL2', 'cliente', 'activo', '2025-12-18 02:01:33'),
(246, 'SAAVEDRA JORGE ARIEL', NULL, 'temp_1766023526_2963@cliente.com', NULL, NULL, '$2y$10$YhmWG0J7le5tdD2kDTFg6uroYkqvwnH2zKEeRokxUV2q4Kd5uyOzC', 'cliente', 'activo', '2025-12-18 02:05:26'),
(247, 'MARTINEZ TOMAS DAVID', NULL, 'Tomasdm87@gmail.com', NULL, '3512580677', '$2y$10$BkRDK0gRN6fEm3kvvH5ZB.ISs.ctPUMAS/ozgdGF9rWGq6FTEF4eC', 'cliente', 'activo', '2025-12-18 02:09:07'),
(248, 'RODRIGUEZ PAOLA ELIZABETH', NULL, 'paolarodriguez909011@gmail.com', NULL, '3875767010', '$2y$10$q2SK/Y5S8kPDZTCKrm4bZuedgeC5/RFBwq.RfV1LcXLeM88oaFWXG', 'cliente', 'activo', '2025-12-18 02:15:37'),
(249, 'ROBLEDO EZEQUIEL FERNANDO', NULL, 'Ezequielfernando1907@gmail.com', NULL, '3416396138', '$2y$10$fgr5YPcZJmUzSUcli4OQvuyDsrbRyBf9uh5eLb/XgB0w46sIZeLKG', 'cliente', 'activo', '2025-12-18 02:19:04'),
(250, 'Arrua ioana', NULL, 'arruayohana5@gmail.com', NULL, '2804517365', '$2y$10$zJhZ6I2J4PFU4Y6UZd5bOe1MY9gCFhWw43xH2aXmt9D2J/Oq2imBO', 'cliente', 'activo', '2025-12-18 02:23:12'),
(251, 'DETTLER GIULIANA ABIGAIL', NULL, 'temp_1766027177_6895@cliente.com', NULL, NULL, '$2y$10$iZHKgvFC3TClmryof.qr4.XdstYrYNbRLnWdQrQv75fk/9ryf53D6', 'cliente', 'activo', '2025-12-18 03:06:17'),
(252, 'DETTLER GIULIANA ABIGAIL', NULL, 'temp_1766027310_3243@cliente.com', NULL, NULL, '$2y$10$lRuurzaNSt9s1V0NwY9EPOj3nsjQznl6jgwGq9ge/Qgk6D3tp5f72', 'cliente', 'activo', '2025-12-18 03:08:30'),
(253, 'DETTLER GIULIANA ABIGAIL', NULL, 'temp_1766027727_3931@cliente.com', NULL, NULL, '$2y$10$BBAW6./26nSHjomgZV0WR.1lZIyZwDTOp9EMsClAVPKoi1c0TelD2', 'cliente', 'activo', '2025-12-18 03:15:27'),
(254, 'GONZALEZ NADIA BELEN', NULL, 'ng7792374@gmail.com', NULL, '1160508956', '$2y$10$2vo9.tKt4RDERTG/d9qnGesMjImDnv2ckxKMn.ACcEGS0HlNtyC0W', 'cliente', 'activo', '2025-12-18 03:24:15'),
(255, 'GONZALEZ NADIA BELEN', NULL, 'temp_1766028583_7409@cliente.com', NULL, '1160508956', '$2y$10$M7UNaQZ4IHScVq7TfqTazOUTPuSi0fsWQ7gc5pyrW/Bpqq2jQytAm', 'cliente', 'activo', '2025-12-18 03:29:43'),
(256, 'ARROYO JULIO RAMON', NULL, 'temp_1766029268_5685@cliente.com', NULL, NULL, '$2y$10$Hj8FiZMS56j/3bNCqGX0geR8zyV5U1OkHoEGBwnRZ9.yIPEuMFRBW', 'cliente', 'activo', '2025-12-18 03:41:08'),
(257, 'ERAZO ESTEFANIA BRENDA', NULL, 'Estefierazo2019@gmail.com', NULL, '3457386232', '$2y$10$S2c.IeXAHRBjeEkwcB0LWOuKw.F/7pod7raZnDyma6th6gjJyQtzK', 'cliente', 'activo', '2025-12-18 03:41:35'),
(258, 'EISENBART EZIO FRANCISCO', NULL, 'temp_1766031601_7288@cliente.com', NULL, NULL, '$2y$10$tUQW.Hk0QFhrkcgvDQiSf.sFnAXPSh8EjvYUJZTXou9ml/NJozcIq', 'cliente', 'activo', '2025-12-18 04:20:01'),
(259, 'PONCE NATALIA VERONICA', NULL, 'temp_1766033203_8325@cliente.com', NULL, NULL, '$2y$10$CWgyWtlC8N4Nft.fj84OuOT5CG7/WLOwiuDSbeZDptSCF5XiZxuem', 'cliente', 'activo', '2025-12-18 04:46:43'),
(260, 'SALTO ROXANA BELEN', NULL, 'temp_1766034896_8070@cliente.com', NULL, NULL, '$2y$10$/AB54stF3i9qRP9SuuRsbut2NNrGQOotpIGp8ZCz2raqDhKa1jzoO', 'cliente', 'activo', '2025-12-18 05:14:56'),
(261, 'SANABRIA DAIANA PAMELA', NULL, 'temp_1766035023_3211@cliente.com', NULL, NULL, '$2y$10$JDA0HsnP7IHdcm2ji4DUx./caETe4/JgsgcF8A23cqptIwpJ4U.YK', 'cliente', 'activo', '2025-12-18 05:17:03'),
(262, 'SANABRIA DAIANA PAMELA', NULL, 'temp_1766035057_5572@cliente.com', NULL, NULL, '$2y$10$feofPgb9.IcW8OQ6vsc2neewIXG5n7bWrJNBIwbnTMUrHUt3gXjSe', 'cliente', 'activo', '2025-12-18 05:17:37'),
(263, 'GALVAN CESAR MAXIMILIANO', NULL, 'galvancesar140@gmail.com', NULL, '2903570728', '$2y$10$3ZWdKKyLrWlyniXBGC./.OUFMn16EWRyCJ5VQWANxCLLSOOrrfRcC', 'cliente', 'activo', '2025-12-18 05:54:18'),
(264, 'PARADA SERGIO ALEJO', NULL, 'alejosergioparada@gmail.com', NULL, '3878314086', '$2y$10$dqzb9qGH72Vh9fZJ6YSbPeY9w7lFfQBdRd1rOyWgWBhkwRvNqT0kO', 'cliente', 'activo', '2025-12-18 06:04:43'),
(265, 'YADO AXEL EMANUEL', NULL, 'axelyado24@gmail.com', NULL, '3765397072', '$2y$10$.VxcvwLouC1Wo89n53.00usf5kWu8o.C4Xm4Y4hq8ib0CedriMbja', 'cliente', 'activo', '2025-12-18 07:31:56'),
(266, 'RUIZ DIAZ CONSTANZA DANIELA', NULL, 'cruizdiaz0553@gmail.com', NULL, '1172878582', '$2y$10$6sDmX7S9xPz17VCBUPyhc.zrxGQde5w.MRpv8qh6z7VkLHS1jQcZK', 'cliente', 'activo', '2025-12-18 08:34:29'),
(267, 'ROJAS ROXANA CATALINA', NULL, 'Roxyrojas960@gmail.comr', NULL, '1126330400', '$2y$10$lrZLGDOcntxCcfxdQ5juTOHXja8RJ.bh3Wv67F2jxqwlENBOazFAW', 'cliente', 'activo', '2025-12-18 09:10:33'),
(268, 'SOTO VIVIANA ITATI', NULL, 'temp_1766049467_5322@cliente.com', NULL, NULL, '$2y$10$oG0ffRnsuX.voXxkhus4leW4Qb1HF.u3bks/GTXNZ1jgv6SmJO4Yi', 'cliente', 'activo', '2025-12-18 09:17:47'),
(269, 'SOTO VIVIANA ITATI', NULL, 'temp_1766049647_4376@cliente.com', NULL, NULL, '$2y$10$3QAyQ4IbMG8DQmDou7Ra2.MudUF87XtNAiuhI0iKB01YVvBjqpQeK', 'cliente', 'activo', '2025-12-18 09:20:47'),
(270, 'SOTO VIVIANA ITATI', NULL, 'sotovivi01@gmail.com', NULL, '3764687069', '$2y$10$Bx8YZ2ci6AyYQq.C2otO6O4gI43FUy2aRf5CHdkVQXgvZ4aLOdG6G', 'cliente', 'activo', '2025-12-18 09:26:46'),
(271, 'ALMADA MARIA SOLEDAD', NULL, 'almadamary163@gmail.com', NULL, '3764966233', '$2y$10$I0y.TPiU.66ZH22uErpO4OngT7V.broyRVLIZ1ZxtqIThRPehoESq', 'cliente', 'activo', '2025-12-18 09:49:21'),
(272, 'SOLOAGA ERICA ANALIA', NULL, 'annaaliitaa31@gmail.com', NULL, '3625178043', '$2y$10$NpSvdzv.8TM89LhcehYHLuaAtiQK2fRduENn5Dc748w3gcf2ZuSE6', 'cliente', 'activo', '2025-12-18 09:50:36'),
(273, 'CORBINO ALAN', NULL, 'Corbinoalan81@gmail.com', NULL, '1133953658', '$2y$10$dwqFhp8eKZf.c3gNA6oVqu2RoMJJh05cQJqKpowU4EoA4WZUT9kvu', 'cliente', 'activo', '2025-12-18 09:56:09'),
(274, 'LOPEZ MATORRAS MARTA ALICIA', NULL, 'temp_1766054026_2565@cliente.com', NULL, NULL, '$2y$10$8A15kXT6y9AfVLnl/4tb3.ZAiQoilPHoGrFu/9I1k54BgYJ3uX44u', 'cliente', 'activo', '2025-12-18 10:33:47'),
(275, 'ALMIRON CARLOS ROBERTO', NULL, 'Ezequiel13almiron@gmail.com', NULL, '3624835363', '$2y$10$gFTB5XeNV4ciX46NAynTGeKL2NLPrHHIKbRoaV.TVHeSKOGb0MZGu', 'cliente', 'activo', '2025-12-18 11:20:33'),
(276, 'DIAZ DANIELA JOHANA', NULL, 'danielajohanadiaz@gmail.com', NULL, '2665069382', '$2y$10$iXxSvdU1XHoQ2NG3W3j5tOrVKHbyJUmVsxp6kIu4IktbJUEty12i.', 'cliente', 'activo', '2025-12-18 11:25:43'),
(277, 'SEGOVIA LIDIA LUISA', NULL, 'temp_1766059984_6244@cliente.com', NULL, NULL, '$2y$10$JRICR/2PRPRybcLY5TU48ONP.cNWc2x8M2uESAVy2fOLQon6/lHJe', 'cliente', 'activo', '2025-12-18 12:13:04'),
(278, 'CORBINO ALAN', NULL, 'temp_1766060392_7027@cliente.com', NULL, '1133953658', '$2y$10$KsFTp3/od0JlXy8zLYieM.oXHxaUn5CGW13pQSTc3H/xdBTv5s3by', 'cliente', 'activo', '2025-12-18 12:19:52'),
(279, 'ESCURDIA DAVID EDUARDO', NULL, 'temp_1766060705_7996@cliente.com', NULL, NULL, '$2y$10$JQb0DhcyKDy9yP1ieZdG8eWfoEBVJXZzmM1jQPSc58O/ZHybRs1l6', 'cliente', 'activo', '2025-12-18 12:25:05'),
(280, 'PAZ NATALI ELISABETH', NULL, 'temp_1766061620_9677@cliente.com', NULL, NULL, '$2y$10$jLw0Y7JntDdvz8QV7l3TF.eFHj0A/bFWNC7Ca.DEZ2D0vq9pBCdSO', 'cliente', 'activo', '2025-12-18 12:40:20'),
(281, 'CADENA YOLANDA BEATRIZ', NULL, 'temp_1766061750_6119@cliente.com', NULL, NULL, '$2y$10$fxHSc6Vz/ghNSNRD4ICOje7EqdPu.6RQ00MR4M72oLSuO1hJsax4C', 'cliente', 'activo', '2025-12-18 12:42:30'),
(282, 'RUIZ DIAZ CABANAS LILIAN DAIANA', NULL, 'Lilianrdc86@gmail.com', NULL, '2215715153', '$2y$10$pvsfoGsNxOc6MiU9zNL9X.nyN3f8wYw16nW3wHbD800C0vgUQBM0i', 'cliente', 'activo', '2025-12-18 12:53:20'),
(283, 'DETTLER GIULIANA ABIGAIL', NULL, 'temp_1766063716_3406@cliente.com', NULL, NULL, '$2y$10$0Lk/9HVSmcTHa8gKvt./e.ZDfq.CWp.pc0iA9JGMjtYgqClLTHGPG', 'cliente', 'activo', '2025-12-18 13:15:16'),
(284, 'GONZALEZ FABIAN DAVID', NULL, 'Fabii1an26.gonzalez@gmail.com', NULL, '1157246267', '$2y$10$pVDlDJ7NZ7cnYVahlPa7U.j0FogyNTTMWrXszKpt04OLJ6ceAAMu.', 'cliente', 'activo', '2025-12-18 13:25:44'),
(285, 'CACERES YAMILA ABIGAIL', NULL, 'temp_1766065606_7971@cliente.com', NULL, NULL, '$2y$10$I.PL4dNglGE3rTodZqRwnuz.wqWJucaofkRl/pGr19VVlW3eoP5li', 'cliente', 'activo', '2025-12-18 13:46:46'),
(286, 'SALVAGIOT LEANDRA FABIANA', NULL, 'temp_1766065636_2950@cliente.com', NULL, NULL, '$2y$10$glX9sHtOYPj2tcZ96W3DeuN4wkM1Tu1u85BokFssPdqhyXd3ddJS2', 'cliente', 'activo', '2025-12-18 13:47:16'),
(287, 'VEGA CARLOS EUGENIO NICOLAS', NULL, 'vegaah1893@gmail.com', NULL, '3804616323', '$2y$10$n301RDuStPhNXqpDyajqpe.b56ktwamhxTumK29QMkAF3vV4T.eqG', 'cliente', 'activo', '2025-12-18 13:52:49'),
(288, 'SALVAGIOT LEANDRA FABIANA', NULL, 'temp_1766066024_2534@cliente.com', NULL, NULL, '$2y$10$D8apU/VabE6kiOnhHw7SKeAXDcActOKw0c3Jxo58fp4hR47ngffwq', 'cliente', 'activo', '2025-12-18 13:53:45');

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
-- Indices de la tabla `cliente_eventos`
--
ALTER TABLE `cliente_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `cliente_notas`
--
ALTER TABLE `cliente_notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `cliente_tags`
--
ALTER TABLE `cliente_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_chat_tag` (`chat_id`,`tag`);

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
  ADD UNIQUE KEY `uniq_msg_request` (`request_uid`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1398;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=468;

--
-- AUTO_INCREMENT de la tabla `chat_archivos`
--
ALTER TABLE `chat_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT de la tabla `chat_transferencias`
--
ALTER TABLE `chat_transferencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=203;

--
-- AUTO_INCREMENT de la tabla `clientes_detalles`
--
ALTER TABLE `clientes_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT de la tabla `cliente_eventos`
--
ALTER TABLE `cliente_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_notas`
--
ALTER TABLE `cliente_notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_tags`
--
ALTER TABLE `cliente_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1481;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

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
-- Filtros para la tabla `cliente_eventos`
--
ALTER TABLE `cliente_eventos`
  ADD CONSTRAINT `cliente_eventos_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_eventos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cliente_notas`
--
ALTER TABLE `cliente_notas`
  ADD CONSTRAINT `cliente_notas_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_notas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cliente_tags`
--
ALTER TABLE `cliente_tags`
  ADD CONSTRAINT `cliente_tags_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;

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
