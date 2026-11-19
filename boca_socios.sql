-- Base de datos del sistema de socios
CREATE DATABASE IF NOT EXISTS boca_socios
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE boca_socios;

-- Tabla de usuarios (socios y administradores)
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(80) NOT NULL,
  apellido VARCHAR(80) NOT NULL,
  dni VARCHAR(20) NOT NULL,
  email VARCHAR(254) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  archivado_en TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY dni (dni),
  UNIQUE KEY email (email)
) ENGINE=InnoDB;

-- Partidos de La Bombonera (los usa partidos.php)
CREATE TABLE IF NOT EXISTS partidos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rival VARCHAR(80) NOT NULL,
  competencia VARCHAR(80) NOT NULL,
  fecha_partido DATETIME NOT NULL,
  estadio VARCHAR(100) NOT NULL DEFAULT 'La Bombonera',
  estado VARCHAR(20) NOT NULL DEFAULT 'habilitado',
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Entradas reservadas por los socios (una por socio y por partido)
CREATE TABLE IF NOT EXISTS entradas_socios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  partido_id INT UNSIGNED NOT NULL,
  ubicacion VARCHAR(60) NOT NULL,
  fecha_reserva TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_entrada_usuario_partido (usuario_id, partido_id),
  CONSTRAINT fk_entradas_socios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
  CONSTRAINT fk_entradas_socios_partido FOREIGN KEY (partido_id) REFERENCES partidos (id)
) ENGINE=InnoDB;

-- Partido de ejemplo para poder probar (solo se inserta si la tabla está vacía)
INSERT INTO partidos (rival, competencia, fecha_partido, estadio, estado)
SELECT 'River Plate', 'Liga Profesional', '2026-12-06 17:00:00', 'La Bombonera', 'habilitado'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM partidos);