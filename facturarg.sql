-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 15 juin 2025 à 18:44
-- Version du serveur : 10.4.22-MariaDB
-- Version de PHP : 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `facturarg`
--

-- --------------------------------------------------------

--
-- Structure de la table `aperturacajas`
--

CREATE TABLE `aperturacajas` (
  `idapertura` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_inicial` decimal(11,2) NOT NULL,
  `cantidad_final` decimal(11,2) NOT NULL,
  `estatus` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `fecha_hora_cierre` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `aperturacajavirtual`
--

CREATE TABLE `aperturacajavirtual` (
  `caja_virtual_id` bigint(20) UNSIGNED NOT NULL,
  `initial_amount` decimal(11,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(11,2) NOT NULL DEFAULT 0.00,
  `total` decimal(11,2) NOT NULL DEFAULT 0.00,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `status_box` enum('virtual_box_open','virtual_box_closed') DEFAULT 'virtual_box_open'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `aperturacajavirtual`
--

INSERT INTO `aperturacajavirtual` (`caja_virtual_id`, `initial_amount`, `final_amount`, `total`, `start_date_time`, `end_date_time`, `status`, `status_box`) VALUES
(6, 0.00, 0.00, 0.00, '2025-04-30 00:00:00', NULL, 1, 'virtual_box_open');

-- --------------------------------------------------------

--
-- Structure de la table `banner_ecommerce`
--

CREATE TABLE `banner_ecommerce` (
  `banner_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_image` varchar(255) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `banner_date` timestamp NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `capturarinventario`
--

CREATE TABLE `capturarinventario` (
  `idcaptura` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `carrito_cotizacion_temp`
--

CREATE TABLE `carrito_cotizacion_temp` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cod` varchar(30) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `descipcion` varchar(250) DEFAULT NULL,
  `precio` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `categorias`
--

CREATE TABLE `categorias` (
  `idcategoria` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `name_imagen` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `clientes`
--

CREATE TABLE `clientes` (
  `idcliente` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(300) CHARACTER SET utf8mb4 NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8mb4 NOT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4 NOT NULL,
  `estatus` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `number_exterior` int(11) DEFAULT NULL,
  `number_interior` int(11) DEFAULT NULL,
  `materno` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `paterno` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `clientes`
--

INSERT INTO `clientes` (`idcliente`, `nombre`, `direccion`, `telefono`, `email`, `estatus`, `number_exterior`, `number_interior`, `materno`, `paterno`) VALUES
(1, 'Publico general', 'Argentina', '2222332231', 'argentina@gmail.com', 'Activo', 103, 122, '', '');

-- --------------------------------------------------------

--
-- Structure de la table `color`
--

CREATE TABLE `color` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `hexadecimal` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `color`
--

INSERT INTO `color` (`id`, `name`, `status`, `hexadecimal`, `registration_date`) VALUES
(6, 'Azul', 1, '#0000FF', '2024-11-18 22:51:30'),
(7, 'Verde', 1, '#7CFC00', '2024-11-18 22:51:53'),
(8, 'Rojo', 1, '#FF0000', '2024-11-18 22:52:04'),
(9, 'Amarillo', 1, '#FFFF00', '2024-11-18 22:52:21');

-- --------------------------------------------------------

--
-- Structure de la table `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(500) CHARACTER SET utf8mb4 NOT NULL,
  `image` varchar(500) CHARACTER SET utf8mb4 NOT NULL,
  `adress` varchar(500) CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `configuracion`
--

INSERT INTO `configuracion` (`id`, `name`, `image`, `adress`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Bodega nacho', 'descarga (1).png', 'Argentina', 'argentina@gmail.com', '7937937957', '2023-02-18 15:19:32', '2024-01-02 03:11:06');

-- --------------------------------------------------------

--
-- Structure de la table `corte_cajero_dia`
--

CREATE TABLE `corte_cajero_dia` (
  `idcortecaja` bigint(20) UNSIGNED NOT NULL,
  `apertura_id` bigint(20) UNSIGNED NOT NULL,
  `total_acomulado` decimal(11,2) NOT NULL,
  `seriefolio` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `numfolio` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `id_cliente` bigint(20) UNSIGNED NOT NULL,
  `serie` varchar(255) NOT NULL,
  `factura` varchar(20) DEFAULT NULL,
  `tipo_pago` varchar(100) DEFAULT NULL,
  `validez` varchar(20) DEFAULT NULL,
  `total` decimal(11,2) NOT NULL,
  `abono` decimal(11,2) DEFAULT NULL,
  `servicio` text DEFAULT NULL,
  `numero_cotizacion_manual` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `detalle_cotizacion`
--

CREATE TABLE `detalle_cotizacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_cotizacion` bigint(20) UNSIGNED NOT NULL,
  `id_producto` bigint(20) UNSIGNED NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `item` decimal(11,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `detalle_devolucion_ventas`
--

CREATE TABLE `detalle_devolucion_ventas` (
  `iddetalledevolucion` bigint(20) UNSIGNED NOT NULL,
  `devolucion_id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(500) CHARACTER SET utf8mb4 NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL,
  `motivo` varchar(500) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `detalle_entrada_temp`
--

CREATE TABLE `detalle_entrada_temp` (
  `identradatemp` bigint(20) UNSIGNED NOT NULL,
  `id_user` int(11) NOT NULL,
  `idarticulo` int(11) NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `nombre` varchar(1000) CHARACTER SET utf8mb4 NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `detalle_ingresos`
--

CREATE TABLE `detalle_ingresos` (
  `iddetalle_ingreso` bigint(20) UNSIGNED NOT NULL,
  `ingreso_id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio_compra` decimal(11,2) NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `iddetalle_venta` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `apertura_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `detalle_venta_temp`
--

CREATE TABLE `detalle_venta_temp` (
  `iddetalletemp` bigint(20) UNSIGNED NOT NULL,
  `id_user` int(11) NOT NULL,
  `idarticulo` int(11) NOT NULL,
  `codproducto` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `nombre` varchar(1000) CHARACTER SET utf8mb4 NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `iva` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `devolucion_ventas`
--

CREATE TABLE `devolucion_ventas` (
  `iddevolucion` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `observacion` varchar(1000) CHARACTER SET utf8mb4 NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text CHARACTER SET utf8mb4 NOT NULL,
  `queue` text CHARACTER SET utf8mb4 NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ingresos`
--

CREATE TABLE `ingresos` (
  `idingreso` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `proveedor_id` bigint(20) UNSIGNED NOT NULL,
  `folio_comprobante` varchar(200) CHARACTER SET utf8mb4 NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `total_ingreso` decimal(11,2) NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `inventario`
--

CREATE TABLE `inventario` (
  `idinventario` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estatus` varchar(20) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `numero_corte_por_cajero`
--

CREATE TABLE `numero_corte_por_cajero` (
  `idnumerocorte` bigint(20) UNSIGNED NOT NULL,
  `cortecaja_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `order_detail_ecommerce`
--

CREATE TABLE `order_detail_ecommerce` (
  `order_detail_id` int(11) NOT NULL,
  `order_ecommerce_id` int(11) NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `producto_variacion_variante_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `order_ecommerce`
--

CREATE TABLE `order_ecommerce` (
  `order_id` int(11) NOT NULL,
  `status_order_id` int(11) NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  `subtotal_amount` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `additional_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déclencheurs `order_ecommerce`
--
DELIMITER $$
CREATE TRIGGER `trigger_afterOrderPaid` AFTER UPDATE ON `order_ecommerce` FOR EACH ROW begin
		DECLARE caja_open_id INT; -- declare variable for save caja virtual id
		
    	IF NEW.status_order_id = 2 AND OLD.status_order_id <> 2 THEN 	-- Check if the status was updated to 'Paid'
    		select caja_virtual_id into caja_open_id from aperturacajavirtual where status = 1 and end_date_time IS null and status_box = 'virtual_box_open';
    		IF caja_open_id IS NOT NULL THEN
    		   UPDATE aperturacajavirtual SET total = total + NEW.total_amount WHERE caja_virtual_id = caja_open_id; -- Update the total for virtual box
    	      UPDATE payment_ecommerce SET status_payment = 'Completado' WHERE order_id = NEW.order_id; -- update the status payment
    	   ELSE
    	      UPDATE payment_ecommerce SET status_payment = 'Fallido' WHERE order_id = NEW.order_id; -- if caja_open_id is null. not found virtualbox
			END IF;  
    	END IF;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `payment_ecommerce`
--

CREATE TABLE `payment_ecommerce` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `total` decimal(11,2) NOT NULL,
  `status_payment` enum('Pendiente','Completado','Fallido','Reembolsado','Cancelado') DEFAULT 'Pendiente',
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `method_name`, `status`) VALUES
(6, 'Tarjeta de crédito', 1),
(7, 'PayPal', 1),
(8, 'Transferencia bancaria', 1),
(9, 'Efectivo', 1),
(10, 'Tarjeta de debito', 1);

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'listar el menu principal', 'admin.index', 'un administrador puede ver menu', '2020-10-17 21:47:42', '2020-10-17 21:47:42'),
(2, 'listar el menu de almacen', 'almacen.index', 'Un usuario puede ver menu de alamcen', '2020-10-17 21:47:42', '2020-10-17 21:47:42'),
(3, 'listar el menu de compras', 'compras.index', 'Un usuario puede ver menu de compras', '2020-10-17 21:47:42', '2020-10-17 21:47:42'),
(4, 'listar el menu de ventas', 'ventas.index', 'Un usuario puede ver menu de ventas', '2020-10-17 21:47:42', '2020-10-17 21:47:42'),
(5, 'listar el menu de caja', 'caja.index', 'Un usuario puede ver el menu de caja', NULL, NULL),
(6, 'listar el menu de devoluciones', 'devolucion.index', 'Un usuario puede ver el menu de devoluciones', NULL, NULL),
(7, 'listar el menu de inventario', 'inventario.index', 'Un usuario puede ver el menu de inventario', NULL, NULL),
(8, 'listar la seccion de roles', 'admin_role.index', 'Un usuario puede ver la seccion de roles', NULL, NULL),
(9, 'listarla seccion de usuarios', 'admin_user.index', 'Un usuario puede ver la seccion de usuarios', NULL, NULL),
(10, 'listar la seccion de apertura de caja', 'caja_apertura.index', 'Un usuario puede aperturar una caja para vender', NULL, NULL),
(11, 'listar la seccion de corte de caja', 'caja_corte.index', 'Un usuario puede realizar el corte de caja', NULL, NULL),
(12, 'listar la seccion de corte parcial de caja', 'caja_parcial.index', 'Un usuario puede realizar el corte de caja parcial', NULL, NULL),
(13, 'listar la seccion de articulos', 'almacen_articulo.index', 'Un usuario puede realizar la alta de productos', NULL, NULL),
(14, 'listar la seccion de categorias', 'almacen_categoria.index', 'Un usuario puede realizar la alta de categorias', NULL, NULL),
(15, 'listar la seccion de entrada de mercancia', 'compras_entrada.index', 'Un usuario puede realizar la entrada de productos', NULL, NULL),
(16, 'listar la seccion de proveedores', 'compras_proveedor.index', 'Un usuario puede realizar el registro de un proveedor', NULL, NULL),
(17, 'listar la seccion de ventas', 'ventas_venta.index', 'Un usuario puede realizar las ventas', NULL, NULL),
(18, 'listar la seccion de clientes', 'ventas_cliente.index', 'Un usuario puede realizar el registro de los clientes', NULL, NULL),
(19, 'listar la seccion de devoluciones', 'devolucion_producto.index', 'Un usuario puede realizar la devolucion de productos', NULL, NULL),
(20, 'listar el menu de reportes', 'reporte.index', 'Un usuario puede ver el menu de reportes', NULL, NULL),
(21, 'listar el menu de configuracion', 'configuracion.index', 'Un usuario puede ver el modulo de configuracion', NULL, NULL),
(22, 'Listar la seccion de inventario', 'almacen_inventario.index', 'Un usuario puede ver el módulo de inventario', NULL, NULL),
(23, 'listar la seccion de historico de cajas', 'caja_historicolist.index', 'Un usuario puede vel la seccion de historico de cajas', '2023-02-26 23:42:50', '2023-02-26 23:42:50'),
(24, 'Listar la seccion de cotizacion', 'cotizaciones.index', 'Un usuario puede ver el menu de cotizaciones', NULL, NULL),
(25, 'Crear una cotizacion', 'cotizaciones_cliente.index', 'Un usuario puede crear una cotizacion', NULL, NULL),
(26, 'Listar las cotizaciones', 'cotizaciones_cotizacion.index', 'Un usuario puede ver las cotizaciones', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `permission_role`
--

CREATE TABLE `permission_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `productos`
--

CREATE TABLE `productos` (
  `idarticulo` bigint(20) UNSIGNED NOT NULL,
  `categoria_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 NOT NULL,
  `stock` double(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 NOT NULL,
  `imagen` varchar(200) CHARACTER SET utf8mb4 NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `iva` decimal(11,2) NOT NULL,
  `tipo_producto_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `producto_integracion_variante`
--

CREATE TABLE `producto_integracion_variante` (
  `id` int(11) NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `variacion_id` int(11) NOT NULL,
  `variante_id` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `status` varchar(5) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `producto_variacion_variante`
--

CREATE TABLE `producto_variacion_variante` (
  `id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `product_integration_id` int(11) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `name_image` varchar(500) NOT NULL,
  `path_image` varchar(1000) NOT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp(),
  `show_ecommerce` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `proveedores`
--

CREATE TABLE `proveedores` (
  `idproveedor` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(300) CHARACTER SET utf8mb4 NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8mb4 NOT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4 NOT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `full-access` enum('yes','no') CHARACTER SET utf8mb4 DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `full-access`, `estatus`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Admin', 'Administrador', 'yes', 1, '2023-02-18 15:25:32', '2023-02-18 15:25:32');

-- --------------------------------------------------------

--
-- Structure de la table `role_user`
--

CREATE TABLE `role_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `role_user`
--

INSERT INTO `role_user` (`id`, `role_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2023-02-18 15:26:57', '2023-02-18 15:26:57');

-- --------------------------------------------------------

--
-- Structure de la table `status_orders_ecommerce`
--

CREATE TABLE `status_orders_ecommerce` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `status_orders_ecommerce`
--

INSERT INTO `status_orders_ecommerce` (`status_id`, `status_name`, `active`, `registration_date`) VALUES
(6, 'Pendiente', 1, '2025-04-30 00:09:44'),
(7, 'Pagado', 1, '2025-04-30 00:09:44'),
(8, 'Enviado', 1, '2025-04-30 00:09:44'),
(9, 'Entregado', 1, '2025-04-30 00:09:44'),
(10, 'Cancelado', 1, '2025-04-30 00:09:44');

-- --------------------------------------------------------

--
-- Structure de la table `tipo_producto`
--

CREATE TABLE `tipo_producto` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `tipo_producto`
--

INSERT INTO `tipo_producto` (`id`, `name`, `descripcion`, `status`, `registration_date`) VALUES
(1, 'Producto simple', '', 1, '2024-12-23 12:34:31'),
(2, 'Producto personalizado', '', 1, '2024-12-23 12:34:45');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `estatus`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@gmail.com', NULL, '$2a$12$9UybrlHmgZnYenJQjDkuyeVYbW4L1dAwiCob5BKO88TnytgSPZJGu', 1, 'QRFkQj8jCYWw6RDF9QaCNK0sD7ue8zTPmIOB8tKrF4Hmzya9dAt8ZYn0o028', '2023-02-18 15:00:57', '2023-09-15 04:23:04');

-- --------------------------------------------------------

--
-- Structure de la table `variaciones`
--

CREATE TABLE `variaciones` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `variantes_para_variaciones`
--

CREATE TABLE `variantes_para_variaciones` (
  `id` int(11) NOT NULL,
  `variacion_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ventas`
--

CREATE TABLE `ventas` (
  `idventa` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_comprobante` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `num_folio` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `efectivo` decimal(11,2) NOT NULL,
  `total_venta` decimal(11,2) NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `aperturacajas`
--
ALTER TABLE `aperturacajas`
  ADD PRIMARY KEY (`idapertura`),
  ADD KEY `aperturacajas_user_id_foreign` (`user_id`);

--
-- Index pour la table `aperturacajavirtual`
--
ALTER TABLE `aperturacajavirtual`
  ADD PRIMARY KEY (`caja_virtual_id`);

--
-- Index pour la table `banner_ecommerce`
--
ALTER TABLE `banner_ecommerce`
  ADD PRIMARY KEY (`banner_id`);

--
-- Index pour la table `capturarinventario`
--
ALTER TABLE `capturarinventario`
  ADD PRIMARY KEY (`idcaptura`),
  ADD KEY `capturarinventario_articulo_id_foreign` (`articulo_id`);

--
-- Index pour la table `carrito_cotizacion_temp`
--
ALTER TABLE `carrito_cotizacion_temp`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Index pour la table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcliente`);

--
-- Index pour la table `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  ADD PRIMARY KEY (`idcortecaja`),
  ADD KEY `corte_cajero_dia_apertura_id_foreign` (`apertura_id`);

--
-- Index pour la table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Index pour la table `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cotizacion` (`id_cotizacion`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Index pour la table `detalle_devolucion_ventas`
--
ALTER TABLE `detalle_devolucion_ventas`
  ADD PRIMARY KEY (`iddetalledevolucion`),
  ADD KEY `detalle_devolucion_ventas_devolucion_id_foreign` (`devolucion_id`),
  ADD KEY `detalle_devolucion_ventas_articulo_id_foreign` (`articulo_id`);

--
-- Index pour la table `detalle_entrada_temp`
--
ALTER TABLE `detalle_entrada_temp`
  ADD PRIMARY KEY (`identradatemp`);

--
-- Index pour la table `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  ADD PRIMARY KEY (`iddetalle_ingreso`),
  ADD KEY `detalle_ingresos_ingreso_id_foreign` (`ingreso_id`),
  ADD KEY `detalle_ingresos_articulo_id_foreign` (`articulo_id`);

--
-- Index pour la table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`iddetalle_venta`),
  ADD KEY `detalle_ventas_venta_id_foreign` (`venta_id`),
  ADD KEY `detalle_ventas_articulo_id_foreign` (`articulo_id`),
  ADD KEY `detalle_ventas_apertura_id_foreign` (`apertura_id`);

--
-- Index pour la table `detalle_venta_temp`
--
ALTER TABLE `detalle_venta_temp`
  ADD PRIMARY KEY (`iddetalletemp`);

--
-- Index pour la table `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  ADD PRIMARY KEY (`iddevolucion`),
  ADD KEY `devolucion_ventas_venta_id_foreign` (`venta_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`idingreso`),
  ADD KEY `ingresos_user_id_foreign` (`user_id`),
  ADD KEY `ingresos_proveedor_id_foreign` (`proveedor_id`);

--
-- Index pour la table `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`idinventario`),
  ADD KEY `inventario_user_id_foreign` (`user_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  ADD PRIMARY KEY (`idnumerocorte`),
  ADD KEY `numero_corte_por_cajero_cortecaja_id_foreign` (`cortecaja_id`);

--
-- Index pour la table `order_detail_ecommerce`
--
ALTER TABLE `order_detail_ecommerce`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_ecommerce_id` (`order_ecommerce_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_producto_variacion_variante_id` (`producto_variacion_variante_id`);

--
-- Index pour la table `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `status_order_id` (`status_order_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Index pour la table `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Index pour la table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`),
  ADD UNIQUE KEY `method_name` (`method_name`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Index pour la table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`),
  ADD KEY `permission_role_permission_id_foreign` (`permission_id`);

--
-- Index pour la table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`idarticulo`),
  ADD KEY `productos_categoria_id_foreign` (`categoria_id`),
  ADD KEY `fk_tipo_producto_id` (`tipo_producto_id`);

--
-- Index pour la table `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `variacion_id` (`variacion_id`),
  ADD KEY `variante_id` (`variante_id`);

--
-- Index pour la table `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `color_id` (`color_id`),
  ADD KEY `product_integration_id` (`product_integration_id`);

--
-- Index pour la table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`idproveedor`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Index pour la table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`),
  ADD KEY `role_user_user_id_foreign` (`user_id`);

--
-- Index pour la table `status_orders_ecommerce`
--
ALTER TABLE `status_orders_ecommerce`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Index pour la table `tipo_producto`
--
ALTER TABLE `tipo_producto`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Index pour la table `variaciones`
--
ALTER TABLE `variaciones`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variacion_id` (`variacion_id`);

--
-- Index pour la table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`idventa`),
  ADD KEY `ventas_user_id_foreign` (`user_id`),
  ADD KEY `ventas_cliente_id_foreign` (`cliente_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `aperturacajas`
--
ALTER TABLE `aperturacajas`
  MODIFY `idapertura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `aperturacajavirtual`
--
ALTER TABLE `aperturacajavirtual`
  MODIFY `caja_virtual_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `banner_ecommerce`
--
ALTER TABLE `banner_ecommerce`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `capturarinventario`
--
ALTER TABLE `capturarinventario`
  MODIFY `idcaptura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `carrito_cotizacion_temp`
--
ALTER TABLE `carrito_cotizacion_temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `idcategoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `color`
--
ALTER TABLE `color`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  MODIFY `idcortecaja` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `detalle_devolucion_ventas`
--
ALTER TABLE `detalle_devolucion_ventas`
  MODIFY `iddetalledevolucion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `detalle_entrada_temp`
--
ALTER TABLE `detalle_entrada_temp`
  MODIFY `identradatemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  MODIFY `iddetalle_ingreso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `iddetalle_venta` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `detalle_venta_temp`
--
ALTER TABLE `detalle_venta_temp`
  MODIFY `iddetalletemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  MODIFY `iddevolucion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `idingreso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inventario`
--
ALTER TABLE `inventario`
  MODIFY `idinventario` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  MODIFY `idnumerocorte` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `order_detail_ecommerce`
--
ALTER TABLE `order_detail_ecommerce`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pour la table `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `productos`
--
ALTER TABLE `productos`
  MODIFY `idarticulo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idproveedor` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `status_orders_ecommerce`
--
ALTER TABLE `status_orders_ecommerce`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `variaciones`
--
ALTER TABLE `variaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `idventa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `aperturacajas`
--
ALTER TABLE `aperturacajas`
  ADD CONSTRAINT `aperturacajas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `capturarinventario`
--
ALTER TABLE `capturarinventario`
  ADD CONSTRAINT `capturarinventario_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE;

--
-- Contraintes pour la table `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  ADD CONSTRAINT `corte_cajero_dia_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE;

--
-- Contraintes pour la table `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD CONSTRAINT `detalles_cotizacion_ibfk_1` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_cotizacion_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE;

--
-- Contraintes pour la table `detalle_devolucion_ventas`
--
ALTER TABLE `detalle_devolucion_ventas`
  ADD CONSTRAINT `detalle_devolucion_ventas_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_devolucion_ventas_devolucion_id_foreign` FOREIGN KEY (`devolucion_id`) REFERENCES `devolucion_ventas` (`iddevolucion`) ON DELETE CASCADE;

--
-- Contraintes pour la table `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  ADD CONSTRAINT `detalle_ingresos_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ingresos_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `ingresos` (`idingreso`) ON DELETE CASCADE;

--
-- Contraintes pour la table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`idventa`) ON DELETE CASCADE;

--
-- Contraintes pour la table `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  ADD CONSTRAINT `devolucion_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`idventa`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ingresos`
--
ALTER TABLE `ingresos`
  ADD CONSTRAINT `ingresos_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`idproveedor`) ON DELETE CASCADE,
  ADD CONSTRAINT `ingresos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  ADD CONSTRAINT `numero_corte_por_cajero_cortecaja_id_foreign` FOREIGN KEY (`cortecaja_id`) REFERENCES `corte_cajero_dia` (`idcortecaja`) ON DELETE CASCADE;

--
-- Contraintes pour la table `order_detail_ecommerce`
--
ALTER TABLE `order_detail_ecommerce`
  ADD CONSTRAINT `fk_producto_variacion_variante_id` FOREIGN KEY (`producto_variacion_variante_id`) REFERENCES `producto_variacion_variante` (`id`),
  ADD CONSTRAINT `order_detail_ecommerce_ibfk_1` FOREIGN KEY (`order_ecommerce_id`) REFERENCES `order_ecommerce` (`order_id`),
  ADD CONSTRAINT `order_detail_ecommerce_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `productos` (`idarticulo`);

--
-- Contraintes pour la table `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  ADD CONSTRAINT `order_ecommerce_ibfk_1` FOREIGN KEY (`status_order_id`) REFERENCES `status_orders_ecommerce` (`status_id`),
  ADD CONSTRAINT `order_ecommerce_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`idcliente`);

--
-- Contraintes pour la table `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  ADD CONSTRAINT `payment_ecommerce_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_ecommerce` (`order_id`),
  ADD CONSTRAINT `payment_ecommerce_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

--
-- Contraintes pour la table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_tipo_producto_id` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`),
  ADD CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`idcategoria`) ON DELETE CASCADE;

--
-- Contraintes pour la table `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  ADD CONSTRAINT `producto_integracion_variante_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`idarticulo`),
  ADD CONSTRAINT `producto_integracion_variante_ibfk_2` FOREIGN KEY (`variacion_id`) REFERENCES `variaciones` (`id`),
  ADD CONSTRAINT `producto_integracion_variante_ibfk_3` FOREIGN KEY (`variante_id`) REFERENCES `variantes_para_variaciones` (`id`);

--
-- Contraintes pour la table `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  ADD CONSTRAINT `producto_variacion_variante_ibfk_1` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`),
  ADD CONSTRAINT `producto_variacion_variante_ibfk_2` FOREIGN KEY (`product_integration_id`) REFERENCES `producto_integracion_variante` (`id`);

--
-- Contraintes pour la table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  ADD CONSTRAINT `variantes_para_variaciones_ibfk_1` FOREIGN KEY (`variacion_id`) REFERENCES `variaciones` (`id`);

--
-- Contraintes pour la table `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
