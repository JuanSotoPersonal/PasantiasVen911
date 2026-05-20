-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: ven911_db
-- Tiempo de generación: 20-05-2026 a las 15:06:22
-- Versión del servidor: 10.11.16-MariaDB-ubu2204
-- Versión de PHP: 8.3.31

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunas`
--

CREATE TABLE `comunas` (
  `id` int(10) UNSIGNED NOT NULL,
  `parroquia_id` int(10) UNSIGNED NOT NULL,
  `nombre_comuna` varchar(150) NOT NULL,
  `descripcion` varchar(256) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int(10) UNSIGNED NOT NULL,
  `llave_activacion` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `llave_activacion`) VALUES
(1, 'V9K2L4M7N1P5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuadrantes_paz`
--

CREATE TABLE `cuadrantes_paz` (
  `id` int(10) UNSIGNED NOT NULL,
  `sector_id` int(10) UNSIGNED NOT NULL,
  `organismo_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre_cuadrante` varchar(150) NOT NULL,
  `descripcion` varchar(256) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `despachos_organismos`
--

CREATE TABLE `despachos_organismos` (
  `id` int(10) UNSIGNED NOT NULL,
  `ficha_id` int(10) UNSIGNED NOT NULL,
  `organismo_id` int(10) UNSIGNED NOT NULL,
  `cuadrante_id` int(10) UNSIGNED DEFAULT NULL,
  `unidad_designada` varchar(100) DEFAULT NULL,
  `mando_acargo` varchar(100) DEFAULT NULL,
  `persona_atiende` varchar(100) DEFAULT NULL,
  `hora_despacho` timestamp NOT NULL DEFAULT current_timestamp(),
  `estatus_despacho` enum('Asignado','En Camino','En Sitio','Liberado','Cancelado') NOT NULL DEFAULT 'Asignado',
  `despachador_id` int(10) UNSIGNED DEFAULT NULL,
  `motivo_cancelacion` varchar(500) DEFAULT NULL,
  `tipo_motivo_cancelacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichas_emergencia`
--

CREATE TABLE `fichas_emergencia` (
  `id` int(10) UNSIGNED NOT NULL,
  `parroquia_id` int(10) UNSIGNED NOT NULL,
  `comuna_id` int(10) UNSIGNED DEFAULT NULL,
  `sector_id` int(10) UNSIGNED DEFAULT NULL,
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
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` int(11) DEFAULT 1,
  `contexto` enum('ficha','organismo') NOT NULL DEFAULT 'ficha' COMMENT 'Indica si el motivo aplica al cierre de una ficha o a la cancelacion de un organismo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id` int(10) UNSIGNED NOT NULL,
  `estado_id` int(10) UNSIGNED NOT NULL,
  `nombre_municipio` varchar(100) NOT NULL,
  `Descripcion` varchar(256) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_recibe_id` int(10) UNSIGNED NOT NULL COMMENT 'Destinatario de la notificación',
  `ficha_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Ficha que originó la notificación',
  `tipo` varchar(50) NOT NULL DEFAULT 'info' COMMENT 'info | alerta | cambio_estado',
  `titulo` varchar(150) NOT NULL DEFAULT 'Notificaci¾n',
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
  `id` int(10) UNSIGNED NOT NULL,
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
-- Estructura de tabla para la tabla `sectores`
--

CREATE TABLE `sectores` (
  `id` int(10) UNSIGNED NOT NULL,
  `comuna_id` int(10) UNSIGNED NOT NULL,
  `nombre_sector` varchar(150) NOT NULL,
  `descripcion` varchar(256) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `pregunta_1_id` int(10) UNSIGNED DEFAULT NULL,
  `pregunta_2_id` int(10) UNSIGNED DEFAULT NULL,
  `respuesta_1` varchar(255) DEFAULT NULL,
  `respuesta_2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indices de la tabla `comunas`
--
ALTER TABLE `comunas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parroquia_id` (`parroquia_id`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cuadrantes_paz`
--
ALTER TABLE `cuadrantes_paz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `organismo_id` (`organismo_id`);

--
-- Indices de la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_despacho_ficha` (`ficha_id`),
  ADD KEY `fk_despacho_organismo` (`organismo_id`),
  ADD KEY `fk_despacho_despachador` (`despachador_id`),
  ADD KEY `idx_estatus_despacho` (`estatus_despacho`),
  ADD KEY `idx_despacho_ficha_estado` (`ficha_id`,`estatus_despacho`),
  ADD KEY `fk_despacho_cuadrante` (`cuadrante_id`);

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
  ADD KEY `fk_evsis_usuario` (`usuario_id`),
  ADD KEY `idx_evsis_fecha` (`fecha`);

--
-- Indices de la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ficha_parroquia` (`parroquia_id`),
  ADD KEY `fk_ficha_caso` (`caso_id`),
  ADD KEY `fk_ficha_solicitante` (`solicitante_id`),
  ADD KEY `fk_ficha_id_user` (`id_user`),
  ADD KEY `fk_ficha_id_owner` (`id_owner`),
  ADD KEY `idx_estado_ficha` (`estado_ficha`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`),
  ADD KEY `idx_estado_fecha` (`estado_ficha`,`fecha_creacion`),
  ADD KEY `idx_owner_estado` (`id_owner`,`estado_ficha`),
  ADD KEY `fk_ficha_comuna` (`comuna_id`),
  ADD KEY `fk_ficha_sector` (`sector_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contexto_estado` (`contexto`,`estado`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_municipio` (`nombre_municipio`),
  ADD KEY `fk_municipio_estado` (`estado_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notif_ficha` (`ficha_id`),
  ADD KEY `idx_notif_fecha` (`usuario_recibe_id`,`leido`,`fecha_creacion`);

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
-- Indices de la tabla `sectores`
--
ALTER TABLE `sectores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comuna_id` (`comuna_id`);

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
  ADD KEY `fk_usuario_p2` (`pregunta_2_id`),
  ADD KEY `idx_usuario_estado_rol` (`estado`,`rol_id`),
  ADD KEY `idx_usuario_nombre` (`nombre_completo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `casos`
--
ALTER TABLE `casos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comunas`
--
ALTER TABLE `comunas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuadrantes_paz`
--
ALTER TABLE `cuadrantes_paz`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_fichas`
--
ALTER TABLE `eventos_fichas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos_sistema`
--
ALTER TABLE `eventos_sistema`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `motivos_cierre`
--
ALTER TABLE `motivos_cierre`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `organismos`
--
ALTER TABLE `organismos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `parroquias`
--
ALTER TABLE `parroquias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sectores`
--
ALTER TABLE `sectores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_emergencia`
--
ALTER TABLE `tipos_emergencia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `casos`
--
ALTER TABLE `casos`
  ADD CONSTRAINT `fk_caso_tipo` FOREIGN KEY (`tipo_emergencia_id`) REFERENCES `tipos_emergencia` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comunas`
--
ALTER TABLE `comunas`
  ADD CONSTRAINT `comunas_ibfk_1` FOREIGN KEY (`parroquia_id`) REFERENCES `parroquias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cuadrantes_paz`
--
ALTER TABLE `cuadrantes_paz`
  ADD CONSTRAINT `cuadrantes_paz_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cuadrantes_paz_ibfk_2` FOREIGN KEY (`organismo_id`) REFERENCES `organismos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  ADD CONSTRAINT `fk_despacho_cuadrante` FOREIGN KEY (`cuadrante_id`) REFERENCES `cuadrantes_paz` (`id`) ON DELETE SET NULL,
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
  ADD CONSTRAINT `fk_ficha_comuna` FOREIGN KEY (`comuna_id`) REFERENCES `comunas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ficha_id_owner` FOREIGN KEY (`id_owner`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ficha_id_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ficha_parroquia` FOREIGN KEY (`parroquia_id`) REFERENCES `parroquias` (`id`),
  ADD CONSTRAINT `fk_ficha_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ficha_solicitante` FOREIGN KEY (`solicitante_id`) REFERENCES `solicitantes` (`id`);

--
-- Filtros para la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD CONSTRAINT `fk_municipio_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `sectores`
--
ALTER TABLE `sectores`
  ADD CONSTRAINT `sectores_ibfk_1` FOREIGN KEY (`comuna_id`) REFERENCES `comunas` (`id`) ON DELETE CASCADE;

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
