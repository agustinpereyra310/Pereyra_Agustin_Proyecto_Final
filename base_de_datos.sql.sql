-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-11-2026 a las 04:33:02
-- Versión del servidor: 10.11.16-MariaDB
-- Versión de PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `boca_socios`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas_socios`
--

CREATE TABLE `entradas_socios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `ubicacion` varchar(50) NOT NULL,
  `fecha_reserva` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `entradas_socios`
--

INSERT INTO `entradas_socios` (`id`, `usuario_id`, `partido_id`, `ubicacion`, `fecha_reserva`) VALUES
(1, 3, 1, 'Platea Baja Sector L', '2026-11-11 23:39:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `rival` varchar(100) NOT NULL,
  `competencia` varchar(100) NOT NULL,
  `fecha_partido` datetime NOT NULL,
  `estadio` varchar(100) DEFAULT 'La Bombonera',
  `imagen_url` varchar(255) DEFAULT '',
  `estado` enum('habilitado','finalizado','proximo') DEFAULT 'habilitado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `partidos`
--

INSERT INTO `partidos` (`id`, `rival`, `competencia`, `fecha_partido`, `estadio`, `imagen_url`, `estado`) VALUES
(1, 'River Plate', 'Torneo Local 2026', '2026-11-25 18:00:00', 'La Bombonera', '', 'habilitado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `archivado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `dni`, `email`, `password_hash`, `rol`, `creado_en`, `archivado_en`) VALUES
(1, 'juan', 'diaz', '12345678', 'juan@prueba.com', '$2y$10$QjJAKQmjIox65g8gnVkl6eenHR5cYD5Jc77KZPEJ9JDrphfYD8wKq', 'usuario', '2026-11-10 22:19:55', NULL),
(2, 'admin', 'socio', '99999999', 'admin@boca.com', '$2y$10$ks6aiGFkdy1OFquNeez1VOFhkXBHzIl6uGbIHE9ioOZJLTUWgtRe.', 'admin', '2026-11-10 22:44:57', NULL),
(3, 'Pedro', 'Perez', '22334455', 'pedro@prueba.com', '$2y$10$mg6Mt0i86KuUzc9Skx96pOhT/NxjWZEfe5XPVh5vMAllN8KUY2yNW', 'usuario', '2026-11-10 23:41:07', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `entradas_socios`
--
ALTER TABLE `entradas_socios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `entradas_socios`
--
ALTER TABLE `entradas_socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
