-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-03-2026 a las 14:37:34
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
-- Base de datos: `ven911_fichas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `casos`
--

CREATE TABLE `casos` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_emergencia_id` int(10) UNSIGNED NOT NULL,
  `nombre_caso` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `estatus_despacho` enum('Asignado','En Camino','En Sitio','Liberado') DEFAULT 'Asignado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `operador_id` int(10) UNSIGNED NOT NULL,
  `despachador_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `hora_cierre` datetime DEFAULT NULL,
  `estado_ficha` enum('Pendiente','En Proceso','Atendido','Cerrado','Finalizado') DEFAULT 'Pendiente',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL COMMENT 'Quién hizo la acción',
  `accion` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','CAMBIO_ESTADO') NOT NULL,
  `tabla_afectada` varchar(50) NOT NULL COMMENT 'Ej: usuarios, fichas_emergencia, roles',
  `registro_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID del registro afectado en esa tabla',
  `ficha_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Solo si la acción está ligada a una emergencia',
  `valor_anterior` text DEFAULT NULL COMMENT 'Estado previo (JSON)',
  `valor_nuevo` text DEFAULT NULL COMMENT 'Estado nuevo (JSON)',
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_municipio` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `organismos`
--

CREATE TABLE `organismos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_organismo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parroquias`
--

CREATE TABLE `parroquias` (
  `id` int(10) UNSIGNED NOT NULL,
  `municipio_id` int(10) UNSIGNED NOT NULL,
  `nombre_parroquia` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 'Despachador'),
(4, 'Jefatura'),
(2, 'Operador'),
(1, 'Super Admin');

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
  `nombre` varchar(100) NOT NULL
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
  `codigo_operador` varchar(20) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
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
-- Indices de la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_despacho_ficha` (`ficha_id`),
  ADD KEY `fk_despacho_organismo` (`organismo_id`);

--
-- Indices de la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ficha_parroquia` (`parroquia_id`),
  ADD KEY `fk_ficha_caso` (`caso_id`),
  ADD KEY `fk_ficha_solicitante` (`solicitante_id`),
  ADD KEY `fk_ficha_operador` (`operador_id`),
  ADD KEY `fk_ficha_despachador` (`despachador_id`);

--
-- Indices de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_usuario` (`usuario_id`),
  ADD KEY `fk_log_ficha` (`ficha_id`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_municipio` (`nombre_municipio`);

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
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

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
  ADD UNIQUE KEY `codigo_operador` (`codigo_operador`),
  ADD KEY `fk_usuario_rol` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `casos`
--
ALTER TABLE `casos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `despachos_organismos`
--
ALTER TABLE `despachos_organismos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  ADD CONSTRAINT `fk_despacho_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `fichas_emergencia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_despacho_organismo` FOREIGN KEY (`organismo_id`) REFERENCES `organismos` (`id`);

--
-- Filtros para la tabla `fichas_emergencia`
--
ALTER TABLE `fichas_emergencia`
  ADD CONSTRAINT `fk_ficha_caso` FOREIGN KEY (`caso_id`) REFERENCES `casos` (`id`),
  ADD CONSTRAINT `fk_ficha_despachador` FOREIGN KEY (`despachador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_ficha_operador` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_ficha_parroquia` FOREIGN KEY (`parroquia_id`) REFERENCES `parroquias` (`id`),
  ADD CONSTRAINT `fk_ficha_solicitante` FOREIGN KEY (`solicitante_id`) REFERENCES `solicitantes` (`id`);

--
-- Filtros para la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `fk_log_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `fichas_emergencia` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_log_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `parroquias`
--
ALTER TABLE `parroquias`
  ADD CONSTRAINT `fk_parroquia_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
