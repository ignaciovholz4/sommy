-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-09-2025 a las 15:15:19
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
-- Base de datos: `landlord_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `add_detalle_venta_temp`
--

CREATE TABLE `add_detalle_venta_temp` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_user_tmp` varchar(255) NOT NULL,
  `idarticulo_tmp` varchar(255) NOT NULL,
  `nombre_tmp` varchar(255) NOT NULL,
  `cantidad_tmp` decimal(11,3) NOT NULL,
  `precio_tmp` decimal(11,2) NOT NULL,
  `descuento_tmp` decimal(11,2) NOT NULL,
  `iva_tmp` decimal(11,2) NOT NULL,
  `tipoProductoId_tmp` varchar(255) NOT NULL DEFAULT '0',
  `producto_variacion_variante_id_tmp` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aperturacajas`
--

CREATE TABLE `aperturacajas` (
  `idapertura` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_inicial` decimal(11,2) NOT NULL,
  `cantidad_final` decimal(11,2) NOT NULL,
  `estatus` varchar(100) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `fecha_hora_cierre` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aperturacajavirtual`
--

CREATE TABLE `aperturacajavirtual` (
  `caja_virtual_id` bigint(20) UNSIGNED NOT NULL,
  `initial_amount` decimal(15,2) DEFAULT NULL,
  `final_amount` decimal(15,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `status_box` enum('virtual_box_open','virtual_box_closed') NOT NULL DEFAULT 'virtual_box_open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `banner_ecommerce`
--

CREATE TABLE `banner_ecommerce` (
  `banner_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `name_image` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `banner_date` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `name_image_movil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capturarinventario`
--

CREATE TABLE `capturarinventario` (
  `idcaptura` bigint(20) UNSIGNED NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_cotizacion_temp`
--

CREATE TABLE `carrito_cotizacion_temp` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cod` varchar(30) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `descipcion` varchar(250) DEFAULT NULL,
  `precio` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `idcategoria` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `name_imagen` varchar(500) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`idcategoria`, `nombre`, `descripcion`, `name_imagen`, `estatus`) VALUES
(1, 'Sabanas', 'Sabanas', 'SABANA1.jpg', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `response` text DEFAULT NULL,
  `is_bot` tinyint(1) NOT NULL DEFAULT 0,
  `session_id` varchar(255) NOT NULL,
  `documentation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `idcliente` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `direccion` varchar(300) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `email` varchar(300) NOT NULL,
  `estatus` varchar(50) NOT NULL DEFAULT '',
  `number_exterior` int(11) DEFAULT NULL,
  `number_interior` varchar(100) DEFAULT NULL,
  `materno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `paterno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`idcliente`, `nombre`, `direccion`, `telefono`, `email`, `estatus`, `number_exterior`, `number_interior`, `materno`, `paterno`) VALUES
(1, 'Publico general', 'Argentina', '2222332231', 'argentina@gmail.com', 'Activo', 103, '122', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `client_activities`
--

CREATE TABLE `client_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `activity_type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `client_activities`
--

INSERT INTO `client_activities` (`id`, `tenant_id`, `activity_type`, `description`, `ip_address`, `user_agent`, `metadata`, `performed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', NULL, '2025-09-10 09:43:37', '2025-09-10 09:43:37', '2025-09-10 09:43:37'),
(2, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', NULL, '2025-09-10 16:15:37', '2025-09-10 16:15:37', '2025-09-10 16:15:37'),
(3, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', NULL, '2025-09-10 17:49:00', '2025-09-10 17:49:00', '2025-09-10 17:49:00'),
(4, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', NULL, '2025-09-12 02:09:55', '2025-09-12 02:09:55', '2025-09-12 02:09:55'),
(5, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, '2025-09-18 17:03:14', '2025-09-18 17:03:14', '2025-09-18 17:03:14'),
(6, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, '2025-09-24 20:59:38', '2025-09-24 20:59:38', '2025-09-24 20:59:38'),
(7, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', NULL, '2025-09-26 22:13:19', '2025-09-26 22:13:19', '2025-09-26 22:13:19'),
(8, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', NULL, '2025-09-27 02:11:52', '2025-09-27 02:11:52', '2025-09-27 02:11:52'),
(9, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', NULL, '2025-09-27 07:36:13', '2025-09-27 07:36:13', '2025-09-27 07:36:13'),
(10, 1, 'login', 'User ID 1 logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', NULL, '2025-09-27 09:12:20', '2025-09-27 09:12:20', '2025-09-27 09:12:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `color`
--

CREATE TABLE `color` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `hexadecimal` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(500) NOT NULL,
  `image` varchar(500) NOT NULL,
  `adress` varchar(500) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `name`, `image`, `adress`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Chemin', 'Diseño sin título (3).png', 'Argentina', 'argentina@gmail.com', '7937937957', '2023-02-18 21:19:32', '2025-06-13 03:41:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `corte_cajero_dia`
--

CREATE TABLE `corte_cajero_dia` (
  `idcortecaja` bigint(20) UNSIGNED NOT NULL,
  `apertura_id` bigint(20) UNSIGNED NOT NULL,
  `total_acomulado` decimal(11,2) NOT NULL,
  `seriefolio` varchar(100) NOT NULL,
  `numfolio` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cotizacion`
--

CREATE TABLE `detalle_cotizacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_devolucion_ventas`
--

CREATE TABLE `detalle_devolucion_ventas` (
  `iddetalledevolucion` bigint(20) UNSIGNED NOT NULL,
  `devolucion_id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL,
  `motivo` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_entrada_temp`
--

CREATE TABLE `detalle_entrada_temp` (
  `identradatemp` bigint(20) UNSIGNED NOT NULL,
  `id_user` int(11) NOT NULL,
  `idarticulo` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(1000) NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ingresos`
--

CREATE TABLE `detalle_ingresos` (
  `iddetalle_ingreso` bigint(20) UNSIGNED NOT NULL,
  `ingreso_id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio_compra` decimal(11,2) NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `iddetalle_venta` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `apertura_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL,
  `tipo_producto_id` int(11) NOT NULL,
  `producto_variacion_variante_id` int(11) DEFAULT NULL,
  `price_list_id` bigint(20) UNSIGNED DEFAULT NULL,
  `original_price` decimal(11,2) DEFAULT NULL,
  `effective_price` decimal(11,2) DEFAULT NULL,
  `price_list_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `detalle_ventas`
--
DELIMITER $$
CREATE TRIGGER `trigger_updateStockVenta` AFTER INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
                IF NEW.tipo_producto_id = 2 AND NEW.tipo_producto_id IS NOT NULL THEN
                    UPDATE producto_variacion_variante 
                    SET stock = stock - NEW.cantidad 
                    WHERE id = NEW.producto_variacion_variante_id;
                    
                    UPDATE productos 
                    SET stock = stock - NEW.cantidad
                    WHERE productos.idarticulo = NEW.articulo_id;
                ELSEIF NEW.tipo_producto_id = 1 THEN
                    UPDATE productos 
                    SET stock = stock - NEW.cantidad
                    WHERE productos.idarticulo = NEW.articulo_id;
                END IF;

                UPDATE corte_cajero_dia 
                SET total_acomulado = total_acomulado + NEW.subtotal 
                WHERE corte_cajero_dia.apertura_id = NEW.apertura_id;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta_temp`
--

CREATE TABLE `detalle_venta_temp` (
  `iddetalletemp` bigint(20) UNSIGNED NOT NULL,
  `id_user` int(11) NOT NULL,
  `idarticulo` int(11) NOT NULL,
  `codproducto` varchar(50) NOT NULL,
  `nombre` varchar(1000) NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `iva` decimal(11,2) NOT NULL,
  `price_list_id` bigint(20) UNSIGNED DEFAULT NULL,
  `original_price` decimal(11,2) DEFAULT NULL,
  `effective_price` decimal(11,2) DEFAULT NULL,
  `price_list_name` varchar(255) DEFAULT NULL,
  `sales_price_list_id` bigint(20) UNSIGNED DEFAULT NULL,
  `typo_producto_id` varchar(255) DEFAULT NULL,
  `producto_variacion_variante_id` varchar(255) DEFAULT NULL,
  `tipo_producto_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devolucion_ventas`
--

CREATE TABLE `devolucion_ventas` (
  `iddevolucion` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `observacion` varchar(1000) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentation`
--

CREATE TABLE `documentation` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(255) NOT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `order` int(11) NOT NULL DEFAULT 0,
  `meta_description` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documentation`
--

INSERT INTO `documentation` (`id`, `title`, `content`, `category`, `tags`, `is_active`, `order`, `meta_description`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Modulo de ventas', 'el paso 1 para crear una venta es solo tocar click en el boton de venta', 'Sales Management', NULL, 1, 0, 'el paso 1 para crear una venta es solo tocar click en el boton de venta', 'modulo-de-ventas', '2025-08-12 05:23:52', '2025-08-12 05:23:52'),
(2, 'Getting started', 'To log in you must go to the bottom of the ecommerce and click on log in. Once done, you need to enter your username and pa\r\nssword.\r\n\r\ngetting started\r\n¡Bienvenido a nuestro sistema de gestión empresarial! Esta guía le ayudará a familiarizarse con las funciones básicas y la navegación.\r\n\r\n## Primeros pasos\r\n\r\n1. **Inicie sesión en su cuenta** con sus credenciales\r\n2. **Complete su perfil** con información precisa\r\n3. **Explore el panel de control** para comprender las funciones principales\r\n4. **Configure sus preferencias** en la sección de configuración', 'Getting Started', '[\"Getting started\"]', 1, 0, 'Getting started', 'como-iniciar', '2025-09-12 02:13:23', '2025-09-12 03:48:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `idingreso` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `proveedor_id` bigint(20) UNSIGNED NOT NULL,
  `folio_comprobante` varchar(200) NOT NULL,
  `total_ingreso` decimal(11,2) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `estado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `idinventario` bigint(20) UNSIGNED NOT NULL,
  `estatus` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2020_06_09_192819_create_roles_table', 1),
(6, '2020_06_09_193225_create_role_user_table', 1),
(7, '2020_06_09_195649_create_permissions_table', 1),
(8, '2020_06_09_195931_create_permission_role_table', 1),
(9, '2020_06_17_215853_create_categories_table', 1),
(10, '2020_06_23_165625_create_productos_table', 1),
(11, '2020_06_25_020547_create_proveedores_table', 1),
(12, '2020_06_27_040026_create_ingresos_table', 1),
(13, '2020_06_27_042214_create_detalle_ingresos_table', 1),
(14, '2020_10_17_213834_create_clientes_table', 1),
(15, '2020_10_17_214026_create_aperturacajas_table', 1),
(16, '2020_10_17_214152_create_corte_cajero_dia_table', 1),
(17, '2020_10_17_214455_create_ventas_table', 1),
(18, '2020_10_17_214637_create_detalle_ventas_table', 1),
(19, '2020_10_17_215150_create_devolucion_ventas_table', 1),
(20, '2020_10_17_215305_create_detalle_devolucion_ventas_table', 1),
(21, '2020_10_17_220023_create_detalle_entrada_temp_table', 1),
(22, '2020_10_17_220249_create_detalle_venta_temp_table', 1),
(23, '2020_10_24_023819_add_corte_cajero_dia', 1),
(24, '2020_10_25_025021_add_serie_corte_cajero_dia', 1),
(25, '2020_11_08_045118_create_numero_corte_por_cajero_table', 1),
(26, '2020_11_16_234123_add_fecha_cierre', 1),
(27, '2020_12_25_032738_add_subtotal_detalle_ingresos', 1),
(28, '2021_08_25_052528_create_configuracion_table', 1),
(29, '2025_06_15_175545_create_landlord_tenants_table', 1),
(30, '2025_06_16_163440_add_user_fields_to_tenants_table', 1),
(31, '2025_06_21_000001_create_aperturacajavirtual_table', 1),
(32, '2025_06_21_000002_create_banner_ecommerce_table', 1),
(33, '2025_06_21_000003_create_capturarinventario_table', 1),
(34, '2025_06_21_000004_create_carrito_cotizacion_temp_table', 1),
(35, '2025_06_21_000005_create_color_table', 1),
(36, '2025_06_21_000006_create_cotizaciones_table', 1),
(37, '2025_06_21_000007_create_detalle_cotizacion_table', 1),
(38, '2025_06_21_000008_create_inventario_table', 1),
(39, '2025_06_21_000009_create_order_detail_ecommerce_table', 1),
(40, '2025_06_21_000010_create_order_ecommerce_table', 1),
(41, '2025_06_21_000011_create_payment_ecommerce_table', 1),
(42, '2025_06_21_000012_create_payment_methods_table', 1),
(43, '2025_06_21_000013_create_producto_integracion_variante_table', 1),
(44, '2025_06_21_000014_create_producto_variacion_variante_table', 1),
(45, '2025_06_21_183503_create_variantes_para_variaciones_table', 1),
(46, '2025_06_21_184311_add_missing_to_detale_ventas_table', 1),
(47, '2025_06_21_184850_add_missing_to_productos_table', 1),
(48, '2025_07_01_045213_create_variaciones_table', 1),
(49, '2025_07_01_045619_create_tipo_producto_table', 1),
(50, '2025_07_01_050602_add_fields_to_carrito_cotizacion_temp_table', 1),
(51, '2025_07_01_052249_add_missing_fields_to_clientes_table', 1),
(52, '2025_07_01_053650_add_missing_fields_to_categorias_table', 1),
(53, '2025_01_01_000001_create_super_admins_table', 2),
(54, '2025_01_01_000002_create_training_videos_table', 2),
(55, '2025_01_01_000003_create_client_activities_table', 2),
(56, '2025_01_01_000004_add_status_to_tenants_table', 2),
(57, '2025_07_30_184337_add_two_factor_confirmed_at_to_super_admins_table', 2),
(58, '2014_10_12_200000_add_two_factor_columns_to_users_table', 3),
(59, '2024_01_01_000001_create_documentation_table', 4),
(60, '2024_01_01_000002_create_chat_messages_table', 4),
(61, '2024_12_19_000000_add_bulk_upload_fields_to_productos_table', 4),
(62, '2025_08_02_000000_add_bulk_upload_fields_to_productos_table', 5),
(63, '2025_08_09_000000_create_price_lists_table', 6),
(64, '2025_08_09_000001_create_price_list_items_table', 6),
(65, '2025_08_10_000000_add_context_to_existing_price_lists', 6),
(66, '2025_08_10_000001_add_price_list_to_sales_temp', 6),
(67, '2025_08_10_000002_add_price_list_to_ventas_table', 6),
(68, '2025_08_10_000003_add_price_list_to_detalle_ventas_table', 6),
(69, '2025_08_18_113239_create_add_detalle_venta_temp_procedure', 6),
(70, '2025_08_23_070012_add_efectivo_to_table_ventas', 6),
(71, '2025_08_23_073636_create_trigger', 6),
(72, '2025_08_25_122352_add_sales_price_list_id_to_detalle_venta_temp_table', 6),
(73, '2025_08_25_122352_add_sales_price_list_id_to_producto_table', 6),
(74, '2025_08_25_122352_add_typo_producto_id_to_detalle_venta_temp_table', 7),
(75, '2025_08_25_122378_add_producto_variacion_variante_id_to_detalle_venta_temp_table', 8),
(76, '2025_08_25_122378_add_tipo_producto_id_to_detalle_venta_temp_table', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `numero_corte_por_cajero`
--

CREATE TABLE `numero_corte_por_cajero` (
  `idnumerocorte` bigint(20) UNSIGNED NOT NULL,
  `cortecaja_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_detail_ecommerce`
--

CREATE TABLE `order_detail_ecommerce` (
  `order_detail_id` int(10) UNSIGNED NOT NULL,
  `order_ecommerce_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `producto_variacion_variante_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_ecommerce`
--

CREATE TABLE `order_ecommerce` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `status_order_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `subtotal_amount` decimal(15,2) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `additional_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `order_ecommerce`
--
DELIMITER $$
CREATE TRIGGER `trigger_afterOrderPaid` AFTER UPDATE ON `order_ecommerce` FOR EACH ROW BEGIN
                DECLARE caja_open_id INT;
                
                IF NEW.status_order_id = 2 AND OLD.status_order_id <> 2 THEN
                    SELECT caja_virtual_id INTO caja_open_id 
                    FROM aperturacajavirtual 
                    WHERE STATUS = 1 AND end_date_time IS NULL AND status_box = 'virtual_box_open';

                    IF caja_open_id IS NOT NULL THEN
                        UPDATE aperturacajavirtual 
                        SET total = total + NEW.total_amount 
                        WHERE caja_virtual_id = caja_open_id;

                        CALL processStockAfterOrderPaid(NEW.order_id);

                        UPDATE payment_ecommerce 
                        SET status_payment = 'Completado' 
                        WHERE order_id = NEW.order_id;
                    ELSE
                        UPDATE payment_ecommerce 
                        SET status_payment = 'Fallido' 
                        WHERE order_id = NEW.order_id;
                    END IF;  
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_ecommerce`
--

CREATE TABLE `payment_ecommerce` (
  `payment_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `status_payment` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(10) UNSIGNED NOT NULL,
  `method_name` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'listar el menu principal', 'admin.index', 'un administrador puede ver el menu', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(2, 'listar el menu de almacen', 'almacen.index', 'Un usuario puede ver el menu de almacen', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(3, 'listar el menu de compras', 'compras.index', 'Un usuario puede ver el menu de compras', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(4, 'listar el menu de ventas', 'ventas.index', 'Un usuario puede ver el menu de ventas', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(5, 'listar el menu de caja', 'caja.index', 'Un usuario puede ver el menu de caja', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(6, 'listar el menu de devoluciones', 'devolucion.index', 'Un usuario puede ver el menu de devoluciones', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(7, 'listar la seccion de roles', 'admin_role.index', 'Un usuario puede ver la seccion de roles', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(8, 'listarla seccion de usuarios', 'admin_user.index', 'Un usuario puede ver la seccion de usuarios', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(9, 'listar la seccion de apertura de caja', 'caja_apertura.index', 'Un usuario puede aperturar una caja para vender', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(10, 'listar la seccion de corte de caja', 'caja_corte.index', 'Un usuario puede realizar el corte de caja', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(11, 'listar la seccion de corte parcial de caja', 'caja_parcial.index', 'Un usuario puede realizar el corte de caja parcial', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(12, 'listar la seccion de articulos', 'almacen_articulo.index', 'Un usuario puede realizar la alta de productos', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(13, 'listar la seccion de categorias', 'almacen_categoria.index', 'Un usuario puede realizar la alta de categorias', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(14, 'listar la seccion de entrada de mercancia', 'compras_entrada.index', 'Un usuario puede realizar la entrada de productos', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(15, 'listar la seccion de proveedores', 'compras_proveedor.index', 'Un usuario puede realizar el registro de un proveedor', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(16, 'listar la seccion de ventas', 'ventas_venta.index', 'Un usuario puede realizar las ventas', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(17, 'listar la seccion de clientes', 'ventas_cliente.index', 'Un usuario puede realizar el registro de los clientes', '2025-07-28 22:46:59', '2025-07-28 22:46:59'),
(18, 'listar la seccion de devoluciones', 'devolucion_producto.index', 'Un usuario puede realizar la devolucion de productos', '2025-07-28 22:46:59', '2025-07-28 22:46:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permission_role`
--

CREATE TABLE `permission_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `price_lists`
--

CREATE TABLE `price_lists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('amount','product_percentage','general_percentage') NOT NULL,
  `context` enum('sales','purchase') NOT NULL DEFAULT 'sales',
  `value_type` enum('discount','increase') DEFAULT NULL,
  `percentage` decimal(8,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `price_list_items`
--

CREATE TABLE `price_list_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `price_list_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `amount_price` decimal(11,2) DEFAULT NULL,
  `value_type` enum('discount','increase') DEFAULT NULL,
  `percentage` decimal(8,2) DEFAULT NULL,
  `purchase_price` decimal(11,2) DEFAULT NULL,
  `purchase_value_type` enum('discount','increase') DEFAULT NULL,
  `purchase_percentage` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `idarticulo` bigint(20) UNSIGNED NOT NULL,
  `categoria_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `stock` double(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `imagen` varchar(200) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `descuento` decimal(5,2) NOT NULL COMMENT 'Discount percentage (0-100)',
  `iva` decimal(11,2) NOT NULL,
  `tipo_producto_id` int(11) DEFAULT NULL,
  `ubicacion` varchar(200) DEFAULT NULL COMMENT 'Product location in warehouse',
  `marca` varchar(100) DEFAULT NULL COMMENT 'Product brand'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`idarticulo`, `categoria_id`, `codigo`, `nombre`, `stock`, `pcompra`, `pventa`, `descripcion`, `imagen`, `estado`, `descuento`, `iva`, `tipo_producto_id`, `ubicacion`, `marca`) VALUES
(1, 1, '1234567891234', 'Sabana Essencial Alcoyana', 3.000, 35000.00, 45000.00, 'La sábana Essencial de Alcoyana combina suavidad, frescura y durabilidad en una pieza ideal para el descanso diario. Confeccionada con materiales de alta calidad, ofrece una textura suave al tacto y una excelente resistencia al uso y los lavados. Su diseño simple y elegante se adapta a cualquier estilo de habitación, brindando confort y funcionalidad para un sueño placentero. Perfecta para quienes buscan calidad y confort en cada detalle.', 'SABANA1.jpg', 'Activo', 0.00, 0.00, 1, NULL, NULL),
(2, 1, '1234567891235', 'sabana esencial alcoyana', 1.000, 0.00, 0.00, 'sabana', 'SABANA1.jpg', 'Activo', 0.00, 0.00, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_integracion_variante`
--

CREATE TABLE `producto_integracion_variante` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `variacion_id` int(11) DEFAULT NULL,
  `variante_id` int(11) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_variacion_variante`
--

CREATE TABLE `producto_variacion_variante` (
  `id` int(10) UNSIGNED NOT NULL,
  `color_id` int(11) DEFAULT NULL,
  `product_integration_id` int(11) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `name_image` varchar(255) DEFAULT NULL,
  `path_image` varchar(255) DEFAULT NULL,
  `stock` decimal(15,2) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp(),
  `show_ecommerce` tinyint(4) NOT NULL DEFAULT 0,
  `pcompra` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `idproveedor` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `direccion` varchar(300) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `email` varchar(300) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `full-access` enum('yes','no') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `full-access`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Admin', 'Administrador', 'yes', '2025-07-28 22:46:59', '2025-07-28 22:46:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_user`
--

CREATE TABLE `role_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_user`
--

INSERT INTO `role_user` (`id`, `role_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-07-28 22:46:59', '2025-07-28 22:46:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `super_admins`
--

CREATE TABLE `super_admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `super_admins`
--

INSERT INTO `super_admins` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_active`, `last_login_at`, `last_login_ip`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`, `created_at`, `updated_at`, `two_factor_confirmed_at`) VALUES
(1, 'Super Administrator', 'admin@facturarg.com', NULL, '$2y$10$bMTapfOIgwcl4Cxw7KWnpO8LUjMc9pTdToGzHbWUqvUzg6AbaN.2C', 1, '2025-09-27 09:11:27', '127.0.0.1', NULL, NULL, NULL, '2025-07-31 06:34:48', '2025-09-27 09:11:27', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `database` varchar(255) NOT NULL,
  `status` enum('active','suspended','pending') NOT NULL DEFAULT 'active',
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `domain`, `database`, `status`, `suspended_at`, `suspension_reason`, `last_activity_at`, `created_at`, `updated_at`, `email`, `password`, `remember_token`, `email_verified_at`) VALUES
(1, 'sdfsdf', 'cdfsdfsd.localhost', 'tenant_cdfsdfsd', 'active', NULL, NULL, '2025-09-27 09:12:20', NULL, '2025-09-27 09:12:20', 'abcd@gmail.com', '$2y$10$jqLgqjr20VDv/uIjGeTd8u1V3pRhtfFjnkhOyOy2Zx4KRTb0cru0.', NULL, NULL),
(2, 'asdasd', 'asdasd.localhost', 'tenant_asdasd', 'active', NULL, NULL, NULL, NULL, NULL, 'asdasd@gmail.com', '$2y$10$jqLgqjr20VDv/uIjGeTd8u1V3pRhtfFjnkhOyOy2Zx4KRTb0cru0.', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto`
--

CREATE TABLE `tipo_producto` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_producto`
--

INSERT INTO `tipo_producto` (`id`, `name`, `descripcion`, `status`, `registration_date`) VALUES
(1, 'Producto simple', '', 1, '2024-12-23 15:34:31'),
(2, 'Producto personalizado', '', 1, '2024-12-23 15:34:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `training_videos`
--

CREATE TABLE `training_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `module_name` varchar(255) NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `training_videos`
--

INSERT INTO `training_videos` (`id`, `title`, `description`, `video_url`, `thumbnail_url`, `module_name`, `duration`, `is_active`, `sort_order`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(2, 'video', 'video', 'training-videos/JQy0iLGnrMdZ72EneSqUrlWWnvEO2OFHvO6WImvg.mp4', 'training-thumbnails/Ey3vXwz09M4NPuW6LqsHhjpdyca7EfWrx4UOe36z.png', 'settings', 2000, 1, 0, 1, '2025-09-08 16:51:27', '2025-09-08 16:51:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `estatus`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin sdfs', 'admin@gmail.com', NULL, '$2y$10$jqLgqjr20VDv/uIjGeTd8u1V3pRhtfFjnkhOyOy2Zx4KRTb0cru0', NULL, NULL, NULL, 1, NULL, '2025-07-28 22:46:59', '2025-07-28 22:46:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variaciones`
--

CREATE TABLE `variaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variantes_para_variaciones`
--

CREATE TABLE `variantes_para_variaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `variacion_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `idventa` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_comprobante` varchar(100) NOT NULL,
  `num_folio` varchar(50) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `total_venta` decimal(11,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `price_list_id` bigint(20) UNSIGNED DEFAULT NULL,
  `price_list_name` varchar(255) DEFAULT NULL,
  `efectivo` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `add_detalle_venta_temp`
--
ALTER TABLE `add_detalle_venta_temp`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `aperturacajas`
--
ALTER TABLE `aperturacajas`
  ADD PRIMARY KEY (`idapertura`),
  ADD KEY `aperturacajas_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `aperturacajavirtual`
--
ALTER TABLE `aperturacajavirtual`
  ADD PRIMARY KEY (`caja_virtual_id`);

--
-- Indices de la tabla `banner_ecommerce`
--
ALTER TABLE `banner_ecommerce`
  ADD PRIMARY KEY (`banner_id`);

--
-- Indices de la tabla `capturarinventario`
--
ALTER TABLE `capturarinventario`
  ADD PRIMARY KEY (`idcaptura`);

--
-- Indices de la tabla `carrito_cotizacion_temp`
--
ALTER TABLE `carrito_cotizacion_temp`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Indices de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_user_id_foreign` (`user_id`),
  ADD KEY `chat_messages_documentation_id_foreign` (`documentation_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcliente`);

--
-- Indices de la tabla `client_activities`
--
ALTER TABLE `client_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_activities_tenant_id_performed_at_index` (`tenant_id`,`performed_at`),
  ADD KEY `client_activities_activity_type_index` (`activity_type`);

--
-- Indices de la tabla `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  ADD PRIMARY KEY (`idcortecaja`),
  ADD KEY `corte_cajero_dia_apertura_id_foreign` (`apertura_id`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_devolucion_ventas`
--
ALTER TABLE `detalle_devolucion_ventas`
  ADD PRIMARY KEY (`iddetalledevolucion`),
  ADD KEY `detalle_devolucion_ventas_devolucion_id_foreign` (`devolucion_id`),
  ADD KEY `detalle_devolucion_ventas_articulo_id_foreign` (`articulo_id`);

--
-- Indices de la tabla `detalle_entrada_temp`
--
ALTER TABLE `detalle_entrada_temp`
  ADD PRIMARY KEY (`identradatemp`);

--
-- Indices de la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  ADD PRIMARY KEY (`iddetalle_ingreso`),
  ADD KEY `detalle_ingresos_ingreso_id_foreign` (`ingreso_id`),
  ADD KEY `detalle_ingresos_articulo_id_foreign` (`articulo_id`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`iddetalle_venta`),
  ADD KEY `detalle_ventas_venta_id_foreign` (`venta_id`),
  ADD KEY `detalle_ventas_articulo_id_foreign` (`articulo_id`),
  ADD KEY `detalle_ventas_apertura_id_foreign` (`apertura_id`),
  ADD KEY `detalle_ventas_price_list_id_foreign` (`price_list_id`);

--
-- Indices de la tabla `detalle_venta_temp`
--
ALTER TABLE `detalle_venta_temp`
  ADD PRIMARY KEY (`iddetalletemp`),
  ADD KEY `detalle_venta_temp_price_list_id_foreign` (`price_list_id`),
  ADD KEY `detalle_venta_temp_sales_price_list_id_foreign` (`sales_price_list_id`);

--
-- Indices de la tabla `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  ADD PRIMARY KEY (`iddevolucion`),
  ADD KEY `devolucion_ventas_venta_id_foreign` (`venta_id`);

--
-- Indices de la tabla `documentation`
--
ALTER TABLE `documentation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `documentation_slug_unique` (`slug`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`idingreso`),
  ADD KEY `ingresos_user_id_foreign` (`user_id`),
  ADD KEY `ingresos_proveedor_id_foreign` (`proveedor_id`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`idinventario`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  ADD PRIMARY KEY (`idnumerocorte`),
  ADD KEY `numero_corte_por_cajero_cortecaja_id_foreign` (`cortecaja_id`);

--
-- Indices de la tabla `order_detail_ecommerce`
--
ALTER TABLE `order_detail_ecommerce`
  ADD PRIMARY KEY (`order_detail_id`);

--
-- Indices de la tabla `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  ADD PRIMARY KEY (`order_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indices de la tabla `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indices de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indices de la tabla `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`),
  ADD KEY `permission_role_permission_id_foreign` (`permission_id`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `price_lists`
--
ALTER TABLE `price_lists`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `price_list_items`
--
ALTER TABLE `price_list_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `price_list_items_price_list_id_foreign` (`price_list_id`),
  ADD KEY `price_list_items_product_id_index` (`product_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`idarticulo`),
  ADD KEY `productos_categoria_id_foreign` (`categoria_id`);

--
-- Indices de la tabla `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`idproveedor`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indices de la tabla `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`),
  ADD KEY `role_user_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `super_admins_email_unique` (`email`);

--
-- Indices de la tabla `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_domain_unique` (`domain`),
  ADD UNIQUE KEY `tenants_database_unique` (`database`);

--
-- Indices de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `training_videos`
--
ALTER TABLE `training_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_videos_uploaded_by_foreign` (`uploaded_by`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `variaciones`
--
ALTER TABLE `variaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`idventa`),
  ADD KEY `ventas_user_id_foreign` (`user_id`),
  ADD KEY `ventas_cliente_id_foreign` (`cliente_id`),
  ADD KEY `ventas_price_list_id_foreign` (`price_list_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `add_detalle_venta_temp`
--
ALTER TABLE `add_detalle_venta_temp`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aperturacajas`
--
ALTER TABLE `aperturacajas`
  MODIFY `idapertura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aperturacajavirtual`
--
ALTER TABLE `aperturacajavirtual`
  MODIFY `caja_virtual_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `banner_ecommerce`
--
ALTER TABLE `banner_ecommerce`
  MODIFY `banner_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `capturarinventario`
--
ALTER TABLE `capturarinventario`
  MODIFY `idcaptura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrito_cotizacion_temp`
--
ALTER TABLE `carrito_cotizacion_temp`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `idcategoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `client_activities`
--
ALTER TABLE `client_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  MODIFY `idcortecaja` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_devolucion_ventas`
--
ALTER TABLE `detalle_devolucion_ventas`
  MODIFY `iddetalledevolucion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_entrada_temp`
--
ALTER TABLE `detalle_entrada_temp`
  MODIFY `identradatemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  MODIFY `iddetalle_ingreso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `iddetalle_venta` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_venta_temp`
--
ALTER TABLE `detalle_venta_temp`
  MODIFY `iddetalletemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  MODIFY `iddevolucion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentation`
--
ALTER TABLE `documentation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `idingreso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `idinventario` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  MODIFY `idnumerocorte` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `order_detail_ecommerce`
--
ALTER TABLE `order_detail_ecommerce`
  MODIFY `order_detail_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  MODIFY `payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `price_lists`
--
ALTER TABLE `price_lists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `price_list_items`
--
ALTER TABLE `price_list_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `idarticulo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idproveedor` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `training_videos`
--
ALTER TABLE `training_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `variaciones`
--
ALTER TABLE `variaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `idventa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aperturacajas`
--
ALTER TABLE `aperturacajas`
  ADD CONSTRAINT `aperturacajas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_documentation_id_foreign` FOREIGN KEY (`documentation_id`) REFERENCES `documentation` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `client_activities`
--
ALTER TABLE `client_activities`
  ADD CONSTRAINT `client_activities_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  ADD CONSTRAINT `corte_cajero_dia_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_devolucion_ventas`
--
ALTER TABLE `detalle_devolucion_ventas`
  ADD CONSTRAINT `detalle_devolucion_ventas_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_devolucion_ventas_devolucion_id_foreign` FOREIGN KEY (`devolucion_id`) REFERENCES `devolucion_ventas` (`iddevolucion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  ADD CONSTRAINT `detalle_ingresos_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ingresos_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `ingresos` (`idingreso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`idventa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_venta_temp`
--
ALTER TABLE `detalle_venta_temp`
  ADD CONSTRAINT `detalle_venta_temp_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_venta_temp_sales_price_list_id_foreign` FOREIGN KEY (`sales_price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  ADD CONSTRAINT `devolucion_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`idventa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD CONSTRAINT `ingresos_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`idproveedor`) ON DELETE CASCADE,
  ADD CONSTRAINT `ingresos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  ADD CONSTRAINT `numero_corte_por_cajero_cortecaja_id_foreign` FOREIGN KEY (`cortecaja_id`) REFERENCES `corte_cajero_dia` (`idcortecaja`) ON DELETE CASCADE;

--
-- Filtros para la tabla `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `price_list_items`
--
ALTER TABLE `price_list_items`
  ADD CONSTRAINT `price_list_items_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`idcategoria`) ON DELETE CASCADE;

--
-- Filtros para la tabla `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `training_videos`
--
ALTER TABLE `training_videos`
  ADD CONSTRAINT `training_videos_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `super_admins` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
