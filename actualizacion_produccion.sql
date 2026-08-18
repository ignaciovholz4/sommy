-- ============================================================
-- SOMMY — Actualizacion de base de datos para PRODUCCION
-- Generado: 2026-07-27 20:45
-- Importar en phpMyAdmin (pestana SQL o Importar).
--
-- NOTA: si alguna linea da error "Duplicate column name" o
-- "Table already exists", significa que ese cambio ya estaba
-- aplicado: es inofensivo, segui con las lineas siguientes.
-- ============================================================

SET NAMES utf8mb4;

-- 1) Orden de las categorias en el menu y la home
--    (SIN ESTA COLUMNA LA TIENDA DA ERROR 500)
ALTER TABLE `categorias` ADD COLUMN `orden` INT NOT NULL DEFAULT 99;
UPDATE `categorias` SET `orden` = 1 WHERE `slug` = "colchones";
UPDATE `categorias` SET `orden` = 2 WHERE `slug` = "sommiers";
UPDATE `categorias` SET `orden` = 3 WHERE `slug` = "almohadas";
UPDATE `categorias` SET `orden` = 4 WHERE `slug` = "sabanas";

-- 2) Canal de origen y fecha de entrega de los pedidos
ALTER TABLE `order_ecommerce` ADD COLUMN `origen` VARCHAR(20) NOT NULL DEFAULT "tienda";
ALTER TABLE `order_ecommerce` ADD COLUMN `fecha_entrega` DATE NULL;

-- 3) Cuenta corriente de clientes (cargos y pagos)
CREATE TABLE IF NOT EXISTS `cliente_cc_movimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `tipo` enum('cargo','pago') COLLATE utf8mb4_unicode_ci NOT NULL,
  `concepto` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `medio_pago` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cliente` (`cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Solicitudes del boton de arrepentimiento (ley 24.240)
CREATE TABLE IF NOT EXISTS `arrepentimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pedido` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Historial del Estudio de Publicaciones
CREATE TABLE IF NOT EXISTS `publicaciones_registro` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `canal` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `formato` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_prod` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

