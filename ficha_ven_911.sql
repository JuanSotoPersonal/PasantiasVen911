-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-04-2026 a las 03:22:47
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
-- Base de datos: `ficha_ven_911`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `casos`
--

CREATE TABLE `casos` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_emergencia_id` int(10) UNSIGNED NOT NULL,
  `nombre_caso` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `casos`
--

INSERT INTO `casos` (`id`, `tipo_emergencia_id`, `nombre_caso`, `descripcion`, `estado`) VALUES
(1, 1, 'Caida De Arbol', 'a', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL,
  `llave_activacion` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `llave_activacion`) VALUES
(1, 'V9K2L4M7N1P5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `despachos_organismos`
--

CREATE TABLE `despachos_organismos` (
  `id` int(10) UNSIGNED NOT NULL,
  `ficha_id` int(10) UNSIGNED NOT NULL,
  `organismo_id` int(10) UNSIGNED NOT NULL,
  `unidad_designada` varchar(100) DEFAULT NULL,
  `mando_acargo` varchar(100) DEFAULT NULL,
  `persona_atiende` varchar(100) DEFAULT NULL,
  `hora_despacho` timestamp NOT NULL DEFAULT current_timestamp(),
  `estatus_despacho` enum('Asignado','En Camino','En Sitio','Liberado','Cancelado') NOT NULL DEFAULT 'Asignado',
  `despachador_id` int(10) UNSIGNED DEFAULT NULL,
  `motivo_cancelacion` varchar(500) DEFAULT NULL,
  `tipo_motivo_cancelacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `despachos_organismos`
--

INSERT INTO `despachos_organismos` (`id`, `ficha_id`, `organismo_id`, `unidad_designada`, `mando_acargo`, `persona_atiende`, `hora_despacho`, `estatus_despacho`, `despachador_id`, `motivo_cancelacion`, `tipo_motivo_cancelacion`) VALUES
(1, 8, 3, '255', 'juan soto', NULL, '2026-04-28 16:13:52', 'Liberado', 3, NULL, NULL),
(2, 8, 4, '255', 'juan soto', 'asdasdas', '2026-04-28 16:56:46', 'Liberado', 3, NULL, NULL),
(3, 12, 3, 'patrulla a', 'jose gimenez', NULL, '2026-04-28 17:33:55', 'Liberado', 7, NULL, NULL),
(4, 10, 3, '3424', '32432', '3423', '2026-04-28 17:45:31', 'Liberado', 3, NULL, NULL),
(5, 10, 4, '3424', '32432', NULL, '2026-04-28 17:46:17', 'Liberado', 3, NULL, NULL),
(6, 6, 2, 'XXXXXX', 'AXAXAXA', 'XXXXX', '2026-04-29 01:07:00', 'Cancelado', 2, 'Error de Datos', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_fichas`
--

CREATE TABLE `eventos_fichas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ficha_id` int(10) UNSIGNED NOT NULL COMMENT 'Ficha que originó el evento',
  `usuario_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Operador que realizó la acción (NULL = sistema automático)',
  `tipo_evento` enum('CREACION','MODIFICACION','CAMBIO_ESTADO','PLAN_ACCION','DESPACHO','CIERRE') NOT NULL,
  `estado_anterior` varchar(50) DEFAULT NULL COMMENT 'Estado previo de la ficha (para CAMBIO_ESTADO)',
  `estado_nuevo` varchar(50) DEFAULT NULL COMMENT 'Estado nuevo de la ficha (para CAMBIO_ESTADO)',
  `valor_anterior` text DEFAULT NULL COMMENT 'Snapshot JSON del estado previo',
  `valor_nuevo` text DEFAULT NULL COMMENT 'Snapshot JSON del estado nuevo',
  `descripcion` text DEFAULT NULL COMMENT 'Nota legible del evento',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos_fichas`
--

INSERT INTO `eventos_fichas` (`id`, `ficha_id`, `usuario_id`, `tipo_evento`, `estado_anterior`, `estado_nuevo`, `valor_anterior`, `valor_nuevo`, `descripcion`, `fecha`) VALUES
(1, 8, 2, 'CREACION', NULL, 'Pendiente', NULL, '{\"id\":8,\"caso\":1,\"estado\":\"Pendiente\"}', 'Ficha de emergencia #8 creada.', '2026-04-23 19:39:03'),
(2, 8, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', '{\"estado\":\"Pendiente\"}', '{\"estado\":\"En Proceso\"}', 'Ficha #8 cambió de \'Pendiente\' a \'En Proceso\'.', '2026-04-24 14:41:13'),
(3, 7, 2, 'CAMBIO_ESTADO', 'Atendido', 'Finalizado', '{\"estado\":\"Atendido\"}', '{\"estado\":\"Finalizado\"}', 'Ficha #7 cambió de \'Atendido\' a \'Finalizado\'.', '2026-04-24 14:41:17'),
(4, 9, 2, 'CREACION', NULL, 'Pendiente', NULL, '{\"id\":9,\"caso\":1,\"estado\":\"Pendiente\"}', 'Ficha de emergencia #9 creada.', '2026-04-28 15:49:21'),
(5, 9, 3, 'MODIFICACION', 'Pendiente', 'Pendiente', '{\"id\":9,\"parroquia_id\":2,\"direccion_exacta\":\"asfasfasfasfasfas\",\"caso_id\":1,\"descripcion_caso\":\"fasfasfasfasfasfasfasfas\",\"solicitante_id\":11,\"id_user\":2,\"id_owner\":null,\"fecha_creacion\":\"2026-04-28 11:49:21\",\"hora_cierre\":null,\"estado_ficha\":\"Pendiente\",\"fecha_actualizacion\":\"2026-04-28 11:49:21\",\"nombre_solicitante\":\"asdasdasdasdf\",\"cedula_solicitante\":\"3103415\",\"telefono1\":\"04145779077\",\"telefono2\":\"\",\"nombre_caso\":\"Caida De Arbol\",\"tipo_emergencia_id\":1,\"tipo_emergencia\":\"Salud\",\"nombre_parroquia\":\"h\",\"municipio_id\":2,\"nombre_municipio\":\"naguanagua\"}', '{\"parroquia_id\":2,\"direccion_exacta\":\"asfasfasfasfasfas\",\"caso_id\":1,\"descripcion_caso\":\"fasfasfasfasfasfasfasfas\",\"nombre_solicitante\":\"asdasdasdasdf\",\"cedula_solicitante\":\"3103415\",\"telefono1\":\"04145779077\",\"telefono2\":\"\"}', 'Ficha #9 actualizada.', '2026-04-28 16:00:14'),
(6, 9, 3, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":3}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-28 16:05:04'),
(7, 8, 3, 'MODIFICACION', 'En Proceso', 'En Proceso', '{\"id\":8,\"parroquia_id\":1,\"direccion_exacta\":\"awfasfasfasfsafasf\",\"caso_id\":1,\"descripcion_caso\":\"fasfsafasfasfasfasfas\",\"solicitante_id\":10,\"id_user\":2,\"id_owner\":2,\"fecha_creacion\":\"2026-04-23 15:39:03\",\"hora_cierre\":null,\"estado_ficha\":\"En Proceso\",\"fecha_actualizacion\":\"2026-04-24 10:41:13\",\"nombre_solicitante\":\"asdasdasdasdf\",\"cedula_solicitante\":\"3103414\",\"telefono1\":\"04145779077\",\"telefono2\":\"\",\"nombre_caso\":\"Caida De Arbol\",\"tipo_emergencia_id\":1,\"tipo_emergencia\":\"Salud\",\"nombre_parroquia\":\"valencia\",\"municipio_id\":1,\"nombre_municipio\":\"Valencia\"}', '{\"parroquia_id\":2,\"direccion_exacta\":\"awfasfasfasfsafasf\",\"caso_id\":1,\"descripcion_caso\":\"fasfsafasfasfasfasfas\",\"nombre_solicitante\":\"asdasdasdasdf\",\"cedula_solicitante\":\"3103414\",\"telefono1\":\"04145779077\",\"telefono2\":\"\"}', 'Ficha #8 actualizada.', '2026-04-28 16:13:27'),
(8, 8, 3, 'MODIFICACION', 'En Proceso', 'En Proceso', '{\"id\":8,\"parroquia_id\":2,\"direccion_exacta\":\"awfasfasfasfsafasf\",\"caso_id\":1,\"descripcion_caso\":\"fasfsafasfasfasfasfas\",\"solicitante_id\":10,\"id_user\":2,\"id_owner\":3,\"fecha_creacion\":\"2026-04-23 15:39:03\",\"hora_cierre\":null,\"estado_ficha\":\"En Proceso\",\"fecha_actualizacion\":\"2026-04-28 12:13:27\",\"nombre_solicitante\":\"asdasdasdasdf\",\"cedula_solicitante\":\"3103414\",\"telefono1\":\"04145779077\",\"telefono2\":\"\",\"nombre_caso\":\"Caida De Arbol\",\"tipo_emergencia_id\":1,\"tipo_emergencia\":\"Salud\",\"nombre_parroquia\":\"h\",\"municipio_id\":2,\"nombre_municipio\":\"naguanagua\"}', '{\"parroquia_id\":2,\"direccion_exacta\":\"awfasfasfasfsafasf\",\"caso_id\":1,\"descripcion_caso\":\"fasfsafasfasfasfasfas\",\"nombre_solicitante\":\"asdasdasdasdf\",\"cedula_solicitante\":\"3103414\",\"telefono1\":\"04145779074\",\"telefono2\":\"\"}', 'Ficha #8 actualizada.', '2026-04-28 16:13:34'),
(9, 8, 3, 'DESPACHO', NULL, NULL, NULL, '{\"despacho_id\":1,\"organismo_id\":3,\"unidad\":\"255\"}', 'Despacho #1: Organismo ID 3 — Unidad \'255\'.', '2026-04-28 16:13:52'),
(10, 8, 3, 'DESPACHO', 'Asignado', 'En Camino', '{\"estatus\":\"Asignado\"}', '{\"estatus\":\"En Camino\"}', 'Despacho #1: \'Asignado\' → \'En Camino\'.', '2026-04-28 16:14:01'),
(11, 8, 3, 'DESPACHO', 'En Camino', 'En Sitio', '{\"estatus\":\"En Camino\"}', '{\"estatus\":\"En Sitio\"}', 'Despacho #1: \'En Camino\' → \'En Sitio\'.', '2026-04-28 16:14:03'),
(12, 8, 3, 'DESPACHO', 'En Sitio', 'Liberado', '{\"estatus\":\"En Sitio\"}', '{\"estatus\":\"Liberado\"}', 'Despacho #1: \'En Sitio\' → \'Liberado\'.', '2026-04-28 16:14:04'),
(13, 8, 3, 'DESPACHO', NULL, NULL, NULL, '{\"despacho_id\":2,\"organismo_id\":4,\"unidad\":\"255\"}', 'Despacho #2: Organismo ID 4 — Unidad \'255\'.', '2026-04-28 16:56:46'),
(14, 8, 3, 'DESPACHO', 'Asignado', 'En Camino', '{\"estatus\":\"Asignado\"}', '{\"estatus\":\"En Camino\"}', 'Despacho #2: \'Asignado\' → \'En Camino\'.', '2026-04-28 16:56:57'),
(15, 8, 3, 'DESPACHO', 'En Camino', 'En Sitio', '{\"estatus\":\"En Camino\"}', '{\"estatus\":\"En Sitio\"}', 'Despacho #2: \'En Camino\' → \'En Sitio\'.', '2026-04-28 16:57:00'),
(16, 8, 3, 'DESPACHO', 'En Sitio', 'Liberado', '{\"estatus\":\"En Sitio\"}', '{\"estatus\":\"Liberado\"}', 'Despacho #2: \'En Sitio\' → \'Liberado\'.', '2026-04-28 16:57:12'),
(17, 9, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-28 16:58:24'),
(18, 8, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-28 16:59:14'),
(19, 9, 2, 'CAMBIO_ESTADO', 'Atendido', 'Finalizado', '{\"estado\":\"Atendido\"}', '{\"estado\":\"Finalizado\"}', 'Ficha #9 cambió de \'Atendido\' a \'Finalizado\'.', '2026-04-28 17:03:38'),
(20, 8, 2, 'CAMBIO_ESTADO', 'Atendido', 'Finalizado', '{\"estado\":\"Atendido\"}', '{\"estado\":\"Finalizado\"}', 'Ficha #8 cambió de \'Atendido\' a \'Finalizado\'.', '2026-04-28 17:04:44'),
(21, 10, 2, 'CREACION', NULL, 'Pendiente', NULL, '{\"id\":10,\"caso\":1,\"estado\":\"Pendiente\"}', 'Ficha de emergencia #10 creada.', '2026-04-28 17:12:16'),
(22, 11, 4, 'CREACION', NULL, 'Pendiente', NULL, '{\"id\":11,\"caso\":1,\"estado\":\"Pendiente\"}', 'Ficha de emergencia #11 creada.', '2026-04-28 17:15:33'),
(23, 11, 3, 'MODIFICACION', 'Pendiente', 'Pendiente', '{\"id\":11,\"parroquia_id\":1,\"direccion_exacta\":\"asdasdasdfasfas\",\"caso_id\":1,\"descripcion_caso\":\"safasfasfasfasasdasdas\",\"solicitante_id\":12,\"id_user\":4,\"id_owner\":null,\"fecha_creacion\":\"2026-04-28 13:15:33\",\"hora_cierre\":null,\"motivo_cierre\":null,\"estado_ficha\":\"Pendiente\",\"fecha_actualizacion\":\"2026-04-28 13:15:33\",\"nombre_solicitante\":\"mele soto\",\"cedula_solicitante\":\"3102112\",\"telefono1\":\"04145773077\",\"telefono2\":\"\",\"nombre_caso\":\"Caida De Arbol\",\"tipo_emergencia_id\":1,\"tipo_emergencia\":\"Salud\",\"nombre_parroquia\":\"valencia\",\"municipio_id\":1,\"nombre_municipio\":\"Valencia\"}', '{\"parroquia_id\":2,\"direccion_exacta\":\"asdasdasdfasfas\",\"caso_id\":1,\"descripcion_caso\":\"safasfasfasfasasdasdas\",\"nombre_solicitante\":\"mele soto\",\"cedula_solicitante\":\"3102112\",\"telefono1\":\"04145773077\",\"telefono2\":\"\"}', 'Ficha #11 actualizada.', '2026-04-28 17:18:55'),
(24, 11, 3, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":3}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-28 17:19:34'),
(25, 10, 3, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":3}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-28 17:19:39'),
(26, 12, 4, 'CREACION', NULL, 'Pendiente', NULL, '{\"id\":12,\"caso\":1,\"estado\":\"Pendiente\"}', 'Ficha de emergencia #12 creada.', '2026-04-28 17:31:19'),
(27, 12, 7, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":7}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-28 17:33:21'),
(28, 12, 7, 'DESPACHO', NULL, NULL, NULL, '{\"despacho_id\":3,\"organismo_id\":3,\"unidad\":\"patrulla a\"}', 'Despacho #3: Organismo ID 3 — Unidad \'patrulla a\'.', '2026-04-28 17:33:55'),
(29, 12, 7, 'DESPACHO', 'Asignado', 'En Camino', '{\"estatus\":\"Asignado\"}', '{\"estatus\":\"En Camino\"}', 'Despacho #3: \'Asignado\' → \'En Camino\'.', '2026-04-28 17:34:27'),
(30, 12, 7, 'DESPACHO', 'En Camino', 'En Sitio', '{\"estatus\":\"En Camino\"}', '{\"estatus\":\"En Sitio\"}', 'Despacho #3: \'En Camino\' → \'En Sitio\'.', '2026-04-28 17:34:31'),
(31, 12, 7, 'DESPACHO', 'En Sitio', 'Liberado', '{\"estatus\":\"En Sitio\"}', '{\"estatus\":\"Liberado\"}', 'Despacho #3: \'En Sitio\' → \'Liberado\'.', '2026-04-28 17:34:38'),
(32, 13, 4, 'CREACION', NULL, 'Pendiente', NULL, '{\"id\":13,\"caso\":1,\"estado\":\"Pendiente\"}', 'Ficha de emergencia #13 creada.', '2026-04-28 17:43:34'),
(33, 13, 3, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":3}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-28 17:44:44'),
(34, 10, 3, 'DESPACHO', NULL, NULL, NULL, '{\"despacho_id\":4,\"organismo_id\":3,\"unidad\":\"3424\"}', 'Despacho #4: Organismo ID 3 — Unidad \'3424\'.', '2026-04-28 17:45:31'),
(35, 10, 3, 'DESPACHO', 'Asignado', 'En Camino', '{\"estatus\":\"Asignado\"}', '{\"estatus\":\"En Camino\"}', 'Despacho #4: \'Asignado\' → \'En Camino\'.', '2026-04-28 17:45:47'),
(36, 10, 3, 'DESPACHO', 'En Camino', 'En Sitio', '{\"estatus\":\"En Camino\"}', '{\"estatus\":\"En Sitio\"}', 'Despacho #4: \'En Camino\' → \'En Sitio\'.', '2026-04-28 17:45:57'),
(37, 10, 3, 'DESPACHO', 'En Sitio', 'Liberado', '{\"estatus\":\"En Sitio\"}', '{\"estatus\":\"Liberado\"}', 'Despacho #4: \'En Sitio\' → \'Liberado\'.', '2026-04-28 17:46:04'),
(38, 10, 3, 'DESPACHO', NULL, NULL, NULL, '{\"despacho_id\":5,\"organismo_id\":4,\"unidad\":\"3424\"}', 'Despacho #5: Organismo ID 4 — Unidad \'3424\'.', '2026-04-28 17:46:17'),
(39, 10, 3, 'DESPACHO', 'Asignado', 'En Camino', '{\"estatus\":\"Asignado\"}', '{\"estatus\":\"En Camino\"}', 'Despacho #5: \'Asignado\' → \'En Camino\'.', '2026-04-28 17:46:24'),
(40, 10, 3, 'DESPACHO', 'En Camino', 'En Sitio', '{\"estatus\":\"En Camino\"}', '{\"estatus\":\"En Sitio\"}', 'Despacho #5: \'En Camino\' → \'En Sitio\'.', '2026-04-28 17:46:27'),
(41, 10, 3, 'DESPACHO', 'En Sitio', 'Liberado', '{\"estatus\":\"En Sitio\"}', '{\"estatus\":\"Liberado\"}', 'Despacho #5: \'En Sitio\' → \'Liberado\'.', '2026-04-28 17:46:29'),
(42, 12, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-28 17:49:39'),
(43, 10, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-28 17:50:07'),
(44, 11, 3, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-28 22:47:49'),
(45, 13, 3, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-28 22:48:00'),
(46, 1, 3, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":3}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-28 22:58:47'),
(47, 1, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"Llamada Falsa\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: Llamada Falsa', '2026-04-29 00:07:29'),
(48, 2, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:09:15'),
(49, 2, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-29 00:09:30'),
(50, 4, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:19:27'),
(51, 4, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"registro duplicado\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: registro duplicado', '2026-04-29 00:20:03'),
(52, 11, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:21:38'),
(53, 9, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"Error de datos\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: Error de datos', '2026-04-29 00:22:19'),
(54, 11, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"86486886\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: 86486886', '2026-04-29 00:22:34'),
(55, 10, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:22:49'),
(56, 10, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"&^*^&asdas\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: &^*^&asdas', '2026-04-29 00:23:00'),
(57, 5, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:29:50'),
(58, 5, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"Cierre de prueba con el nuevo motivo.\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: Cierre de prueba con el nuevo motivo.', '2026-04-29 00:30:53'),
(59, 6, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:48:45'),
(60, 6, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-29 00:48:48'),
(61, 7, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:48:58'),
(62, 7, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-29 00:49:00'),
(63, 8, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 00:49:08'),
(64, 8, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Atendido', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Atendido\",\"motivo\":\"\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Atendido\'.', '2026-04-29 00:49:12'),
(65, 6, 2, 'CAMBIO_ESTADO', 'Pendiente', 'En Proceso', NULL, '{\"id_owner\":2}', 'Ficha tomada por despachador. Estado: \'Pendiente\' → \'En Proceso\'.', '2026-04-29 01:06:48'),
(66, 6, 2, 'DESPACHO', NULL, NULL, NULL, '{\"despacho_id\":6,\"organismo_id\":2,\"unidad\":\"XXXXXX\"}', 'Despacho #6: Organismo ID 2 — Unidad \'XXXXXX\'.', '2026-04-29 01:07:00'),
(67, 6, 2, 'DESPACHO', 'Asignado', 'Cancelado', '{\"estatus\":\"Asignado\"}', '{\"estatus\":\"Cancelado\",\"motivo\":\"Error de Datos: \"}', 'Despacho #6 (Policía Nacional Bolivariana) cancelado. Motivo: Error de Datos.', '2026-04-29 01:07:55'),
(68, 6, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"asdasda\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: asdasda', '2026-04-29 01:08:27'),
(69, 7, 2, 'CAMBIO_ESTADO', 'En Proceso', 'Cerrado', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Cerrado\",\"motivo\":\"aaaaa\"}', 'Estado cambiado desde Centro de Despacho: \'En Proceso\' → \'Cerrado\'. Motivo: aaaaa', '2026-04-29 01:09:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_sistema`
--

CREATE TABLE `eventos_sistema` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Quién hizo la acción (NULL = setup inicial del sistema)',
  `tipo_accion` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','CAMBIO_ESTADO') NOT NULL,
  `tabla_afectada` varchar(50) NOT NULL COMMENT 'Ej: usuarios, fichas_emergencia, roles',
  `registro_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID del registro afectado en esa tabla',
  `valor_anterior` text DEFAULT NULL COMMENT 'Estado previo (JSON)',
  `valor_nuevo` text DEFAULT NULL COMMENT 'Estado nuevo (JSON)',
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos_sistema`
--

INSERT INTO `eventos_sistema` (`id`, `usuario_id`, `tipo_accion`, `tabla_afectada`, `registro_id`, `valor_anterior`, `valor_nuevo`, `descripcion`, `fecha`) VALUES
(1, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-23 19:39:42'),
(2, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-23 19:39:52'),
(3, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-24 14:40:34'),
(4, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 14:21:58'),
(5, 2, 'UPDATE', 'usuarios', 3, '{\"usuario\":\"Admin2025\",\"nombre_completo\":\"despachador\",\"cedula\":\"00000041\",\"rol_id\":3}', '{\"nombre_completo\":\"despachador\",\"cedula\":\"00000041\",\"usuario\":\"despachador\",\"rol_id\":3}', 'Usuario ID 3 editado.', '2026-04-28 15:53:15'),
(6, 2, 'UPDATE', 'usuarios', 3, '{\"usuario\":\"despachador\",\"nombre_completo\":\"despachador\"}', NULL, 'Contraseña del usuario ID 3 actualizada.', '2026-04-28 15:53:24'),
(7, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 15:53:26'),
(8, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 15:53:30'),
(9, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 16:57:34'),
(10, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'admin2024\' inició sesión.', '2026-04-28 16:58:02'),
(11, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 17:03:59'),
(12, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 17:12:38'),
(13, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 17:12:49'),
(14, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 17:13:28'),
(15, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 17:13:37'),
(16, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 17:14:20'),
(17, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 17:14:29'),
(18, 2, 'UPDATE', 'usuarios', 4, '{\"usuario\":\"operador\",\"nombre_completo\":\"operador\"}', NULL, 'Contraseña del usuario ID 4 actualizada.', '2026-04-28 17:14:45'),
(19, 2, 'UPDATE', 'usuarios', 3, '{\"usuario\":\"despachador\",\"nombre_completo\":\"despachador\"}', NULL, 'Contraseña del usuario ID 3 actualizada.', '2026-04-28 17:14:49'),
(20, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 17:14:51'),
(21, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 17:14:56'),
(22, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 17:17:48'),
(23, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 17:17:51'),
(24, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 17:19:42'),
(25, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 17:19:51'),
(26, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 17:20:01'),
(27, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 17:20:04'),
(28, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 17:26:46'),
(29, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 17:26:51'),
(30, 2, 'UPDATE', 'usuarios', 7, '{\"usuario\":\"Admin20242\",\"nombre_completo\":\"juan soto 1s\",\"cedula\":\"01001010\",\"rol_id\":2}', '{\"nombre_completo\":\"Operador 2\",\"cedula\":\"01001010\",\"usuario\":\"Despachador2\",\"rol_id\":3}', 'Usuario ID 7 editado.', '2026-04-28 17:27:26'),
(31, 2, 'UPDATE', 'usuarios', 7, '{\"usuario\":\"Despachador2\",\"nombre_completo\":\"Operador 2\",\"cedula\":\"01001010\",\"rol_id\":3}', '{\"nombre_completo\":\"Operador 2\",\"cedula\":\"01001010\",\"usuario\":\"despachador2\",\"rol_id\":3}', 'Usuario ID 7 editado.', '2026-04-28 17:27:39'),
(32, 2, 'CAMBIO_ESTADO', 'usuarios', 6, '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', 'Usuario ID 6 cambiado a \'inactivo\'.', '2026-04-28 17:27:45'),
(33, 2, 'CAMBIO_ESTADO', 'usuarios', 6, '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', 'Usuario ID 6 cambiado a \'activo\'.', '2026-04-28 17:27:47'),
(34, 2, 'UPDATE', 'usuarios', 7, '{\"usuario\":\"despachador2\",\"nombre_completo\":\"Operador 2\",\"cedula\":\"01001010\",\"rol_id\":3}', '{\"nombre_completo\":\"despachador 2\",\"cedula\":\"01001010\",\"usuario\":\"despachador2\",\"rol_id\":3}', 'Usuario ID 7 editado.', '2026-04-28 17:29:37'),
(35, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'admin2024\' inició sesión.', '2026-04-28 17:29:59'),
(36, 2, 'CAMBIO_ESTADO', 'usuarios', 7, '{\"estado\":\"activo\"}', '{\"estado\":\"inactivo\"}', 'Usuario ID 7 cambiado a \'inactivo\'.', '2026-04-28 17:30:05'),
(37, 2, 'CAMBIO_ESTADO', 'usuarios', 7, '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', 'Usuario ID 7 cambiado a \'activo\'.', '2026-04-28 17:30:10'),
(38, 2, 'CAMBIO_ESTADO', 'usuarios', 5, '{\"estado\":\"inactivo\"}', '{\"estado\":\"activo\"}', 'Usuario ID 5 cambiado a \'activo\'.', '2026-04-28 17:30:11'),
(39, 2, 'UPDATE', 'usuarios', 7, '{\"usuario\":\"despachador2\",\"nombre_completo\":\"despachador 2\",\"cedula\":\"01001010\",\"rol_id\":3}', '{\"nombre_completo\":\"despachador2\",\"cedula\":\"01001010\",\"usuario\":\"despachador2\",\"rol_id\":3}', 'Usuario ID 7 editado.', '2026-04-28 17:30:27'),
(40, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 17:30:30'),
(41, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 17:30:36'),
(42, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 17:31:22'),
(43, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 17:31:46'),
(44, 2, 'UPDATE', 'usuarios', 7, '{\"usuario\":\"despachador2\",\"nombre_completo\":\"despachador2\"}', NULL, 'Contraseña del usuario ID 7 actualizada.', '2026-04-28 17:31:59'),
(45, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 17:32:02'),
(46, 7, 'LOGIN', 'usuarios', 7, NULL, NULL, 'Usuario \'despachador2\' inició sesión.', '2026-04-28 17:32:05'),
(47, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 17:38:18'),
(48, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 17:40:27'),
(49, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 17:40:59'),
(50, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 17:44:04'),
(51, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 17:44:15'),
(52, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 17:47:51'),
(53, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 17:48:11'),
(54, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 17:49:06'),
(55, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'admin2024\' inició sesión.', '2026-04-28 17:49:25'),
(56, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 17:52:03'),
(57, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 22:40:58'),
(58, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 22:41:44'),
(59, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 22:41:54'),
(60, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 23:45:58'),
(61, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 23:46:06'),
(62, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 23:57:09'),
(63, 3, 'LOGIN', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' inició sesión.', '2026-04-28 23:57:20'),
(64, 3, 'LOGOUT', 'usuarios', 3, NULL, NULL, 'Usuario \'despachador\' cerró sesión.', '2026-04-28 23:57:32'),
(65, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 23:57:41'),
(66, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Usuario \'juan\' cerró sesión.', '2026-04-28 23:58:48'),
(67, 4, 'LOGIN', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' inició sesión.', '2026-04-28 23:58:57'),
(68, 4, 'LOGOUT', 'usuarios', 4, NULL, NULL, 'Usuario \'operador\' cerró sesión.', '2026-04-28 23:59:26'),
(69, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-28 23:59:34'),
(70, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-29 00:03:30'),
(71, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-29 00:05:20'),
(72, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-29 00:06:43'),
(73, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-29 00:12:02'),
(74, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'admin2024\' inició sesión.', '2026-04-29 00:26:10'),
(75, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-29 00:38:45'),
(76, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-29 01:14:56'),
(77, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Usuario \'Admin2024\' inició sesión.', '2026-04-29 01:18:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichas_emergencia`
--

CREATE TABLE `fichas_emergencia` (
  `id` int(10) UNSIGNED NOT NULL,
  `parroquia_id` int(10) UNSIGNED NOT NULL,
  `direccion_exacta` text NOT NULL,
  `caso_id` int(10) UNSIGNED NOT NULL,
  `descripcion_caso` text NOT NULL,
  `solicitante_id` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED DEFAULT NULL COMMENT 'Usuario que CREÓ la ficha (no cambia)',
  `id_owner` int(10) UNSIGNED DEFAULT NULL COMMENT 'Último usuario que MODIFICÓ la ficha',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `hora_cierre` datetime DEFAULT NULL,
  `motivo_cierre` varchar(500) DEFAULT NULL,
  `tipo_motivo_cierre` varchar(150) DEFAULT NULL,
  `estado_ficha` enum('Pendiente','En Proceso','Atendido','Cerrado','Finalizado') DEFAULT 'Pendiente',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fichas_emergencia`
--

INSERT INTO `fichas_emergencia` (`id`, `parroquia_id`, `direccion_exacta`, `caso_id`, `descripcion_caso`, `solicitante_id`, `id_user`, `id_owner`, `fecha_creacion`, `hora_cierre`, `motivo_cierre`, `tipo_motivo_cierre`, `estado_ficha`, `fecha_actualizacion`) VALUES
(1, 1, 'calle ricaute diagonal x', 1, 'calle ricaute diagonal x', 1, 2, 2, '2026-04-22 18:11:16', '2026-04-28 20:07:29', 'Llamada Falsa', NULL, 'Cerrado', '2026-04-29 00:07:29'),
(2, 1, 'calle ricaute diagonal x', 1, 'calle ricaute diagonal x', 2, 2, 2, '2026-04-22 18:12:04', '2026-04-22 14:12:57', NULL, NULL, 'Atendido', '2026-04-29 00:09:30'),
(3, 1, 'asfdadsfdgzfdgasfgsfdgsfdgs', 1, 'sdfdsfadfadfadsfasdfadsfadsfdas', 3, 2, 2, '2026-04-22 18:13:50', '2026-04-23 11:20:48', NULL, NULL, 'Cerrado', '2026-04-23 15:20:48'),
(4, 1, 'asfasfasfasfas', 1, 'asdasdsadasdsada', 4, 2, 2, '2026-04-22 18:14:15', '2026-04-28 20:20:03', 'registro duplicado', 'Llamada Falsa / Sabotaje', 'Cerrado', '2026-04-29 00:20:03'),
(5, 1, 'PLAZA BOLIVAR', 1, 'la persona llamo y dijo que estaba secuestrada en la calle tal y el ultimo carro que vio era uno azul con la placa 25asd', 6, 2, 2, '2026-04-23 15:16:52', '2026-04-28 20:30:53', 'Cierre de prueba con el nuevo motivo.', 'Motivo de Prueba 1', 'Cerrado', '2026-04-29 00:30:53'),
(6, 1, 'asasadasdasdaasd', 1, 'asdasddfsdfsfsd', 10, 2, 2, '2026-04-23 15:35:27', '2026-04-28 21:08:27', 'asdasda', 'Error de Datos', 'Cerrado', '2026-04-29 01:08:27'),
(7, 1, 'kjhhfhfffggjhkhkk', 1, 'uygyugjygygukgukyug', 10, 2, 2, '2026-04-23 19:30:32', '2026-04-28 21:09:28', 'aaaaa', 'Error de Datos', 'Cerrado', '2026-04-29 01:09:28'),
(8, 2, 'awfasfasfasfsafasf', 1, 'fasfsafasfasfasfasfas', 10, 2, 2, '2026-04-23 19:39:03', '2026-04-28 13:04:44', NULL, NULL, 'Atendido', '2026-04-29 00:49:12'),
(9, 2, 'asfasfasfasfasfas', 1, 'fasfasfasfasfasfasfasfas', 11, 2, 2, '2026-04-28 15:49:21', '2026-04-28 20:22:19', 'Error de datos', 'Error de Datos / Prueba', 'Cerrado', '2026-04-29 00:22:19'),
(10, 2, 'dsfsdfdsfsdfsdfsd', 1, 'adsasdsadasdsadasd', 12, 2, 2, '2026-04-28 17:12:16', '2026-04-28 20:23:00', '&^*^&asdas', 'Error de Datos / Prueba', 'Cerrado', '2026-04-29 00:23:00'),
(11, 2, 'asdasdasdfasfas', 1, 'safasfasfasfasasdasdas', 12, 4, 2, '2026-04-28 17:15:33', '2026-04-28 20:22:34', '86486886', 'Falta de Recursos / Unidades', 'Cerrado', '2026-04-29 00:22:34'),
(12, 2, 'gdasasgsagasgag', 1, 'gsagasgasgsagsagasgas', 6, 4, 2, '2026-04-28 17:31:19', NULL, NULL, NULL, 'Atendido', '2026-04-28 17:49:39'),
(13, 1, 'secoytr tanque calle 21', 1, 'caida de arbol contra menor de edad', 13, 4, 3, '2026-04-28 17:43:34', NULL, NULL, NULL, 'Atendido', '2026-04-28 22:48:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` int(10) UNSIGNED NOT NULL,
  `clave` varchar(50) NOT NULL COMMENT 'Ej: usuarios, fichas, despachos',
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `clave`, `descripcion`) VALUES
(1, 'usuarios', 'Gestión de usuarios del sistema'),
(2, 'fichas', 'Creación y gestión de fichas de emergencia'),
(3, 'despachos', 'Despacho a organismos de respuesta'),
(4, 'historial', 'Visualización del historial de auditoría'),
(5, 'reportes', 'Generación de informes y estadísticas'),
(6, 'configuracion', 'Ajustes y configuración del sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motivos_cierre`
--

CREATE TABLE `motivos_cierre` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` int(11) DEFAULT 1,
  `contexto` enum('ficha','organismo') NOT NULL DEFAULT 'ficha' COMMENT 'Indica si el motivo aplica al cierre de una ficha o a la cancelacion de un organismo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `motivos_cierre`
--

INSERT INTO `motivos_cierre` (`id`, `nombre`, `descripcion`, `estado`, `contexto`) VALUES
(1, 'Llamada Falsa / Sabotaje', 'La llamada no corresponde a una emergencia real.', 1, 'ficha'),
(2, 'Registro Duplicado', 'Ya existe otra ficha activa para este incidente.', 1, 'ficha'),
(3, 'Error de Datos', 'Carga de prueba o equivocación de captura', 1, 'ficha'),
(4, 'Ficha Atendida / Exitosa', 'El incidente fue resuelto satisfactoriamente.', 1, 'ficha'),
(5, 'Falta de Recursos / Unidades', 'No se disponía de unidades para el despacho.', 1, 'ficha'),
(6, 'Otro', 'Cualquier otra causa no contemplada.', 1, 'ficha'),
(7, 'Motivo de Prueba 1', 'Descripci', 0, 'ficha'),
(8, 'Oficial no llego al lugar', 'no logro llegar al lugar', 1, 'organismo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_municipio` varchar(100) NOT NULL,
  `Descripcion` varchar(256) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `municipios`
--

INSERT INTO `municipios` (`id`, `nombre_municipio`, `Descripcion`, `estado`) VALUES
(1, 'Valencia', '1#', 1),
(2, 'naguanagua', 'a', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_recibe_id` int(10) UNSIGNED NOT NULL COMMENT 'Destinatario de la notificación',
  `ficha_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Ficha que originó la notificación',
  `tipo` varchar(50) NOT NULL DEFAULT 'info' COMMENT 'info | alerta | cambio_estado',
  `titulo` varchar(150) NOT NULL DEFAULT 'Notificación' COMMENT 'Título corto de la alerta',
  `mensaje` varchar(255) NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `organismos`
--

CREATE TABLE `organismos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_organismo` varchar(150) NOT NULL,
  `Descripcion` varchar(256) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `organismos`
--

INSERT INTO `organismos` (`id`, `nombre_organismo`, `Descripcion`, `estado`) VALUES
(2, 'Policía Nacional Bolivariana', 'El Cuerpo de Policía Nacional Bolivariana ​​ es la principal fuerza de seguridad civil a nivel federal o nacional de Venezuela, es una de las instituciones adscritas al Ministerio del Poder Popular para Relaciones Interiores, Justicia y Paz.', 1),
(3, 'Guardia Nacional Bolivariana', 'La Guardia Nacional Bolivariana es uno de los cinco componentes que integran la Fuerza Armada Nacional Bolivariana.', 1),
(4, 'A', '', 0),
(5, 'Test Desc', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parroquias`
--

CREATE TABLE `parroquias` (
  `id` int(10) UNSIGNED NOT NULL,
  `municipio_id` int(10) UNSIGNED NOT NULL,
  `nombre_parroquia` varchar(100) NOT NULL,
  `Descripcion` varchar(256) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `parroquias`
--

INSERT INTO `parroquias` (`id`, `municipio_id`, `nombre_parroquia`, `Descripcion`, `estado`) VALUES
(1, 1, 'valencia', 'a', 1),
(2, 2, 'h', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(10) UNSIGNED NOT NULL,
  `modulo_id` int(10) UNSIGNED NOT NULL,
  `clave` varchar(50) NOT NULL COMMENT 'ver, crear, editar, cambiar_estado, gestionar',
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `modulo_id`, `clave`, `descripcion`) VALUES
(1, 1, 'ver', 'Ver lista de usuarios'),
(2, 1, 'crear', 'Crear nuevos usuarios'),
(3, 1, 'editar', 'Editar información de usuarios'),
(4, 1, 'cambiar_estado', 'Activar o desactivar usuarios'),
(5, 1, 'gestionar', 'Gestión completa del módulo de usuarios'),
(6, 2, 'ver', 'Ver fichas de emergencia'),
(7, 2, 'crear', 'Crear nuevas fichas'),
(8, 2, 'editar', 'Editar fichas existentes'),
(9, 2, 'cambiar_estado', 'Cambiar estado de una ficha'),
(10, 2, 'gestionar', 'Gestión completa de fichas'),
(11, 3, 'ver', 'Ver despachos activos'),
(12, 3, 'crear', 'Crear un despacho a organismo'),
(13, 3, 'editar', 'Editar información de despacho'),
(14, 3, 'cambiar_estado', 'Cambiar estado de despacho'),
(15, 3, 'gestionar', 'Gestión completa de despachos'),
(16, 4, 'ver', 'Ver el historial de auditoría del sistema'),
(17, 5, 'ver', 'Acceder a reportes y estadísticas'),
(18, 6, 'gestionar', 'Acceso completo a la configuración del sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

CREATE TABLE `preguntas_seguridad` (
  `id` int(11) NOT NULL,
  `pregunta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_seguridad`
--

INSERT INTO `preguntas_seguridad` (`id`, `pregunta`) VALUES
(1, 'ejemplo 2?\r\n'),
(2, 'ejemplo 1?\r\n');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(3, 'Despachador'),
(4, 'Jefatura'),
(2, 'Operador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

CREATE TABLE `rol_permiso` (
  `rol_id` int(10) UNSIGNED NOT NULL,
  `permiso_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permiso`
--

INSERT INTO `rol_permiso` (`rol_id`, `permiso_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(2, 6),
(2, 7),
(3, 6),
(3, 8),
(3, 9),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(4, 6),
(4, 11),
(4, 16),
(4, 17);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitantes`
--

CREATE TABLE `solicitantes` (
  `id` int(10) UNSIGNED NOT NULL,
  `cedula` varchar(15) DEFAULT NULL,
  `nombre_solicitante` varchar(120) NOT NULL,
  `telefono1` varchar(20) NOT NULL,
  `telefono2` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitantes`
--

INSERT INTO `solicitantes` (`id`, `cedula`, `nombre_solicitante`, `telefono1`, `telefono2`) VALUES
(1, NULL, 'juan soto', '04149739335', ''),
(2, NULL, 'juan soto', '04149739335', ''),
(3, NULL, 'juan soto', '04149739335', ''),
(4, NULL, 'juan soto', '04149739335', ''),
(5, NULL, 'JUAN SOTO', '04145779077', ''),
(6, '31034138', 'Ignaciosoto', '04145773017', ''),
(7, NULL, 'asdasdasd', '04145779077', ''),
(8, NULL, 'asdasdasd', '04145779077', ''),
(9, '3103413', 'asdasdasd', '04145779077', ''),
(10, '3103414', 'asdasdasdasdf', '04145779074', ''),
(11, '3103415', 'asdasdasdasdf', '04145779077', ''),
(12, '3102112', 'mele soto', '04145773077', ''),
(13, '34789735', 'deicker', '04125007647', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_emergencia`
--

CREATE TABLE `tipos_emergencia` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `descripcion` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_emergencia`
--

INSERT INTO `tipos_emergencia` (`id`, `nombre`, `estado`, `descripcion`) VALUES
(1, 'Salud', 1, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `cedula` varchar(12) DEFAULT NULL,
  `rol_id` int(10) UNSIGNED NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `pregunta_1_id` int(11) DEFAULT NULL,
  `pregunta_2_id` int(11) DEFAULT NULL,
  `respuesta_1` varchar(255) DEFAULT NULL,
  `respuesta_2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre_completo`, `cedula`, `rol_id`, `estado`, `pregunta_1_id`, `pregunta_2_id`, `respuesta_1`, `respuesta_2`) VALUES
(2, 'Admin2024', '$2y$10$1Xo4RSWwvDpMNi7cmwmlTeC1p8F4WcA7Y.MOY1W7PtEU6j/d7x6aC', 'juan', '12312312', 1, 'activo', 1, 2, '$2y$10$2meKzzHoJh8.GPvBsDorYuBWHawuoFNFPgIhJGSFfH0Lyv5BlFNCO', '$2y$10$6JxoBtScbyKZBhjsJ3AUtOS41wbKyj1qBAYWtd9tDr4Ggkv58D85.'),
(3, 'despachador', '$2y$10$/LRpjuFnVtFFntj5VCKtbuqXIgOwWFxGYqWTotB.RZ9e4sjSIftxe', 'despachador', '00000041', 3, 'activo', NULL, NULL, NULL, NULL),
(4, 'operador', '$2y$10$muLCChnUH0KMveSG7juj4ON3D5/C5dEVZLiFk31uCBkK/nbL9fqr2', 'operador', '31034121', 2, 'activo', NULL, NULL, NULL, NULL),
(5, 'operador2', '$2y$10$/0.I.dU/KlqxAGIfdPhvBOBr8HotzTbJUBAwSsW3hfXIXSjFOGyti', 'operador2', '53123123', 4, 'activo', NULL, NULL, NULL, NULL),
(6, 'Jefatura', '$2y$10$w6i.yH38MPzAIiRYDnZc4ukG1Afmvx/gghB48S98sf.7vGRZLu9Yq', 'Jefatura', '01010101', 4, 'activo', NULL, NULL, NULL, NULL),
(7, 'despachador2', '$2y$10$o8q4haV07esxLv4pwIKZXeiw71jZhqgwErkpQRkgXAyM9y1Tm3Gcm', 'despachador2', '01001010', 3, 'activo', NULL, NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `casos`
--
ALTER TABLE `casos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_caso_tipo` (`tipo_emergencia_id`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_despacho_ficha` (`ficha_id`),
  ADD KEY `fk_despacho_organismo` (`organismo_id`),
  ADD KEY `fk_despacho_despachador` (`despachador_id`);

--
-- Indices de la tabla `eventos_fichas`
--
ALTER TABLE `eventos_fichas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_efich_ficha` (`ficha_id`),
  ADD KEY `idx_efich_usuario` (`usuario_id`),
  ADD KEY `idx_efich_tipo` (`tipo_evento`);

--
-- Indices de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_evsis_usuario` (`usuario_id`);

--
-- Indices de la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ficha_parroquia` (`parroquia_id`),
  ADD KEY `fk_ficha_caso` (`caso_id`),
  ADD KEY `fk_ficha_solicitante` (`solicitante_id`),
  ADD KEY `fk_ficha_id_user` (`id_user`),
  ADD KEY `fk_ficha_id_owner` (`id_owner`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `motivos_cierre`
--
ALTER TABLE `motivos_cierre`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_municipio` (`nombre_municipio`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leido` (`usuario_recibe_id`,`leido`),
  ADD KEY `fk_notif_ficha` (`ficha_id`);

--
-- Indices de la tabla `organismos`
--
ALTER TABLE `organismos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_organismo` (`nombre_organismo`);

--
-- Indices de la tabla `parroquias`
--
ALTER TABLE `parroquias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parroquia_municipio` (`municipio_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_modulo_clave` (`modulo_id`,`clave`);

--
-- Indices de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`rol_id`,`permiso_id`),
  ADD KEY `fk_rp_permiso` (`permiso_id`);

--
-- Indices de la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `tipos_emergencia`
--
ALTER TABLE `tipos_emergencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `fk_usuario_rol` (`rol_id`),
  ADD KEY `fk_usuario_p1` (`pregunta_1_id`),
  ADD KEY `fk_usuario_p2` (`pregunta_2_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `casos`
--
ALTER TABLE `casos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `eventos_fichas`
--
ALTER TABLE `eventos_fichas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `motivos_cierre`
--
ALTER TABLE `motivos_cierre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `organismos`
--
ALTER TABLE `organismos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `parroquias`
--
ALTER TABLE `parroquias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tipos_emergencia`
--
ALTER TABLE `tipos_emergencia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `casos`
--
ALTER TABLE `casos`
  ADD CONSTRAINT `fk_caso_tipo` FOREIGN KEY (`tipo_emergencia_id`) REFERENCES `tipos_emergencia` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  ADD CONSTRAINT `fk_despacho_despachador` FOREIGN KEY (`despachador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_despacho_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `fichas_emergencia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_despacho_organismo` FOREIGN KEY (`organismo_id`) REFERENCES `organismos` (`id`);

--
-- Filtros para la tabla `eventos_fichas`
--
ALTER TABLE `eventos_fichas`
  ADD CONSTRAINT `fk_efich_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `fichas_emergencia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_efich_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  ADD CONSTRAINT `fk_evsis_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  ADD CONSTRAINT `fk_ficha_caso` FOREIGN KEY (`caso_id`) REFERENCES `casos` (`id`),
  ADD CONSTRAINT `fk_ficha_id_owner` FOREIGN KEY (`id_owner`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ficha_id_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ficha_parroquia` FOREIGN KEY (`parroquia_id`) REFERENCES `parroquias` (`id`),
  ADD CONSTRAINT `fk_ficha_solicitante` FOREIGN KEY (`solicitante_id`) REFERENCES `solicitantes` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `fichas_emergencia` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_recibe_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `parroquias`
--
ALTER TABLE `parroquias`
  ADD CONSTRAINT `fk_parroquia_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `fk_permiso_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_pregunta1` FOREIGN KEY (`pregunta_1_id`) REFERENCES `preguntas_seguridad` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_pregunta2` FOREIGN KEY (`pregunta_2_id`) REFERENCES `preguntas_seguridad` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
