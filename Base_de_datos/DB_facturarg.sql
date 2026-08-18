
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*Table structure for table `aperturacajas` */
USE `facturarg2`;

DROP TABLE IF EXISTS `aperturacajas`;

CREATE TABLE `aperturacajas` (
  `idapertura` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `cantidad_inicial` decimal(11,2) NOT NULL,
  `cantidad_final` decimal(11,2) NOT NULL,
  `estatus` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `fecha_hora_cierre` datetime DEFAULT NULL,
  PRIMARY KEY (`idapertura`),
  KEY `aperturacajas_user_id_foreign` (`user_id`),
  CONSTRAINT `aperturacajas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `aperturacajas` */

/*Table structure for table `aperturacajavirtual` */

DROP TABLE IF EXISTS `aperturacajavirtual`;

CREATE TABLE `aperturacajavirtual` (
  `caja_virtual_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `initial_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `final_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `total` decimal(11,2) NOT NULL DEFAULT '0.00',
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `status_box` enum('virtual_box_open','virtual_box_closed') DEFAULT 'virtual_box_open',
  PRIMARY KEY (`caja_virtual_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `aperturacajavirtual` */

insert  into `aperturacajavirtual`(`caja_virtual_id`,`initial_amount`,`final_amount`,`total`,`start_date_time`,`end_date_time`,`status`,`status_box`) values 
(1,0.00,0.00,0.00,'2025-06-06 00:00:00',NULL,1,'virtual_box_open');

/*Table structure for table `banner_ecommerce` */

DROP TABLE IF EXISTS `banner_ecommerce`;

CREATE TABLE `banner_ecommerce` (
  `banner_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_image` varchar(255) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `banner_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT '1',
  `name_image_movil` varchar(255) NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `banner_ecommerce` */

/*Table structure for table `capturarinventario` */

DROP TABLE IF EXISTS `capturarinventario`;

CREATE TABLE `capturarinventario` (
  `idcaptura` bigint unsigned NOT NULL AUTO_INCREMENT,
  `articulo_id` bigint unsigned NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idcaptura`),
  KEY `capturarinventario_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `capturarinventario_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `capturarinventario` */

/*Table structure for table `carrito_cotizacion_temp` */

DROP TABLE IF EXISTS `carrito_cotizacion_temp`;

CREATE TABLE `carrito_cotizacion_temp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `producto_id` int NOT NULL,
  `cod` varchar(30) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `descipcion` varchar(250) DEFAULT NULL,
  `precio` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

/*Data for the table `carrito_cotizacion_temp` */

/*Table structure for table `categorias` */

DROP TABLE IF EXISTS `categorias`;

CREATE TABLE `categorias` (
  `idcategoria` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET utf8mb4  NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4  NOT NULL,
  `estatus` int NOT NULL DEFAULT '1',
  `name_imagen` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`idcategoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `categorias` */

/*Table structure for table `clientes` */

DROP TABLE IF EXISTS `clientes`;

CREATE TABLE `clientes` (
  `idcliente` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(300) CHARACTER SET utf8mb4  NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8mb4  NOT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4  NOT NULL,
  `estatus` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  `number_exterior` int DEFAULT NULL,
  `number_interior` varchar(100) DEFAULT NULL,
  `materno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `paterno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`idcliente`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `clientes` */

insert  into `clientes`(`idcliente`,`nombre`,`direccion`,`telefono`,`email`,`estatus`,`number_exterior`,`number_interior`,`materno`,`paterno`) values 
(1,'Publico general','Argentina','2222332231','argentina@gmail.com','Activo',103,'122','','');

/*Table structure for table `color` */

DROP TABLE IF EXISTS `color`;

CREATE TABLE `color` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `hexadecimal` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `color` */

/*Table structure for table `configuracion` */

DROP TABLE IF EXISTS `configuracion`;

CREATE TABLE `configuracion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(500) CHARACTER SET utf8mb4  NOT NULL,
  `image` varchar(500) CHARACTER SET utf8mb4  NOT NULL,
  `adress` varchar(500) CHARACTER SET utf8mb4  NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4  NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `configuracion` */

insert  into `configuracion`(`id`,`name`,`image`,`adress`,`email`,`phone`,`created_at`,`updated_at`) values 
(1,'Bodega nacho','descarga (1).png','Argentina','argentina@gmail.com','7937937957','2023-02-18 15:19:32','2024-01-02 03:11:06');

/*Table structure for table `corte_cajero_dia` */

DROP TABLE IF EXISTS `corte_cajero_dia`;

CREATE TABLE `corte_cajero_dia` (
  `idcortecaja` bigint unsigned NOT NULL AUTO_INCREMENT,
  `apertura_id` bigint unsigned NOT NULL,
  `total_acomulado` decimal(11,2) NOT NULL,
  `seriefolio` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `numfolio` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idcortecaja`),
  KEY `corte_cajero_dia_apertura_id_foreign` (`apertura_id`),
  CONSTRAINT `corte_cajero_dia_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `corte_cajero_dia` */

/*Table structure for table `cotizaciones` */

DROP TABLE IF EXISTS `cotizaciones`;

CREATE TABLE `cotizaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `id_cliente` bigint unsigned NOT NULL,
  `serie` varchar(255) NOT NULL,
  `factura` varchar(20) DEFAULT NULL,
  `tipo_pago` varchar(100) DEFAULT NULL,
  `validez` varchar(20) DEFAULT NULL,
  `total` decimal(11,2) NOT NULL,
  `abono` decimal(11,2) DEFAULT NULL,
  `servicio` text,
  `numero_cotizacion_manual` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

/*Data for the table `cotizaciones` */

/*Table structure for table `detalle_cotizacion` */

DROP TABLE IF EXISTS `detalle_cotizacion`;

CREATE TABLE `detalle_cotizacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_cotizacion` bigint unsigned NOT NULL,
  `id_producto` bigint unsigned NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `item` decimal(11,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cotizacion` (`id_cotizacion`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalles_cotizacion_ibfk_1` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalles_cotizacion_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

/*Data for the table `detalle_cotizacion` */

/*Table structure for table `detalle_devolucion_ventas` */

DROP TABLE IF EXISTS `detalle_devolucion_ventas`;

CREATE TABLE `detalle_devolucion_ventas` (
  `iddetalledevolucion` bigint unsigned NOT NULL AUTO_INCREMENT,
  `devolucion_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `nombre` varchar(500) CHARACTER SET utf8mb4  NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL,
  `motivo` varchar(500) CHARACTER SET utf8mb4  NOT NULL,
  PRIMARY KEY (`iddetalledevolucion`),
  KEY `detalle_devolucion_ventas_devolucion_id_foreign` (`devolucion_id`),
  KEY `detalle_devolucion_ventas_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `detalle_devolucion_ventas_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  CONSTRAINT `detalle_devolucion_ventas_devolucion_id_foreign` FOREIGN KEY (`devolucion_id`) REFERENCES `devolucion_ventas` (`iddevolucion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `detalle_devolucion_ventas` */

/*Table structure for table `detalle_entrada_temp` */

DROP TABLE IF EXISTS `detalle_entrada_temp`;

CREATE TABLE `detalle_entrada_temp` (
  `identradatemp` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `idarticulo` int NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  `nombre` varchar(1000) CHARACTER SET utf8mb4  NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `tipo_producto_id` int NOT NULL,
  `producto_variacion_variante_id` int DEFAULT '0',
  PRIMARY KEY (`identradatemp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `detalle_entrada_temp` */

/*Table structure for table `detalle_ingresos` */

DROP TABLE IF EXISTS `detalle_ingresos`;

CREATE TABLE `detalle_ingresos` (
  `iddetalle_ingreso` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ingreso_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio_compra` decimal(11,2) NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL,
  `tipo_producto_id` int NOT NULL,
  `producto_variacion_variante_id` int DEFAULT NULL,
  PRIMARY KEY (`iddetalle_ingreso`),
  KEY `detalle_ingresos_ingreso_id_foreign` (`ingreso_id`),
  KEY `detalle_ingresos_articulo_id_foreign` (`articulo_id`),
  KEY `fk_tipo_producto` (`tipo_producto_id`),
  KEY `fk_producto_variacion_variante` (`producto_variacion_variante_id`),
  CONSTRAINT `detalle_ingresos_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ingresos_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `ingresos` (`idingreso`) ON DELETE CASCADE,
  CONSTRAINT `fk_producto_variacion_variante` FOREIGN KEY (`producto_variacion_variante_id`) REFERENCES `producto_variacion_variante` (`id`),
  CONSTRAINT `fk_tipo_producto` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `detalle_ingresos` */

/*Table structure for table `detalle_venta_temp` */

DROP TABLE IF EXISTS `detalle_venta_temp`;

CREATE TABLE `detalle_venta_temp` (
  `iddetalletemp` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `idarticulo` int NOT NULL,
  `codproducto` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  `nombre` varchar(1000) CHARACTER SET utf8mb4  NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `iva` decimal(11,2) NOT NULL,
  `tipo_producto_id` int NOT NULL,
  `producto_variacion_variante_id` int DEFAULT '0',
  PRIMARY KEY (`iddetalletemp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `detalle_venta_temp` */

/*Table structure for table `detalle_ventas` */

DROP TABLE IF EXISTS `detalle_ventas`;

CREATE TABLE `detalle_ventas` (
  `iddetalle_venta` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `apertura_id` bigint unsigned NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL,
  `tipo_producto_id` int NOT NULL,
  `producto_variacion_variante_id` int DEFAULT NULL,
  PRIMARY KEY (`iddetalle_venta`),
  KEY `detalle_ventas_venta_id_foreign` (`venta_id`),
  KEY `detalle_ventas_articulo_id_foreign` (`articulo_id`),
  KEY `detalle_ventas_apertura_id_foreign` (`apertura_id`),
  KEY `fk_tipo_producto_detalle_ventas` (`tipo_producto_id`),
  KEY `fk_producto_variacion_variante_detalle_ventas` (`producto_variacion_variante_id`),
  CONSTRAINT `detalle_ventas_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`idventa`) ON DELETE CASCADE,
  CONSTRAINT `fk_producto_variacion_variante_detalle_ventas` FOREIGN KEY (`producto_variacion_variante_id`) REFERENCES `producto_variacion_variante` (`id`),
  CONSTRAINT `fk_tipo_producto_detalle_ventas` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `detalle_ventas` */

/*Table structure for table `devolucion_ventas` */

DROP TABLE IF EXISTS `devolucion_ventas`;

CREATE TABLE `devolucion_ventas` (
  `iddevolucion` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint unsigned NOT NULL,
  `observacion` varchar(1000) CHARACTER SET utf8mb4  NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`iddevolucion`),
  KEY `devolucion_ventas_venta_id_foreign` (`venta_id`),
  CONSTRAINT `devolucion_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`idventa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `devolucion_ventas` */

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `connection` text CHARACTER SET utf8mb4  NOT NULL,
  `queue` text CHARACTER SET utf8mb4  NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4  NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4  NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `failed_jobs` */

/*Table structure for table `ingresos` */

DROP TABLE IF EXISTS `ingresos`;

CREATE TABLE `ingresos` (
  `idingreso` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `proveedor_id` bigint unsigned NOT NULL,
  `folio_comprobante` varchar(200) CHARACTER SET utf8mb4  NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `total_ingreso` decimal(11,2) NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4  NOT NULL,
  PRIMARY KEY (`idingreso`),
  KEY `ingresos_user_id_foreign` (`user_id`),
  KEY `ingresos_proveedor_id_foreign` (`proveedor_id`),
  CONSTRAINT `ingresos_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`idproveedor`) ON DELETE CASCADE,
  CONSTRAINT `ingresos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `ingresos` */

/*Table structure for table `inventario` */

DROP TABLE IF EXISTS `inventario`;

CREATE TABLE `inventario` (
  `idinventario` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estatus` varchar(20) CHARACTER SET utf8mb4  NOT NULL,
  PRIMARY KEY (`idinventario`),
  KEY `inventario_user_id_foreign` (`user_id`),
  CONSTRAINT `inventario_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `inventario` */

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4  NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `migrations` */

/*Table structure for table `numero_corte_por_cajero` */

DROP TABLE IF EXISTS `numero_corte_por_cajero`;

CREATE TABLE `numero_corte_por_cajero` (
  `idnumerocorte` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cortecaja_id` bigint unsigned NOT NULL,
  `cantidad` decimal(11,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idnumerocorte`),
  KEY `numero_corte_por_cajero_cortecaja_id_foreign` (`cortecaja_id`),
  CONSTRAINT `numero_corte_por_cajero_cortecaja_id_foreign` FOREIGN KEY (`cortecaja_id`) REFERENCES `corte_cajero_dia` (`idcortecaja`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `numero_corte_por_cajero` */

/*Table structure for table `order_detail_ecommerce` */

DROP TABLE IF EXISTS `order_detail_ecommerce`;

CREATE TABLE `order_detail_ecommerce` (
  `order_detail_id` int NOT NULL AUTO_INCREMENT,
  `order_ecommerce_id` int NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `producto_variacion_variante_id` int DEFAULT NULL,
  PRIMARY KEY (`order_detail_id`),
  KEY `order_ecommerce_id` (`order_ecommerce_id`),
  KEY `product_id` (`product_id`),
  KEY `fk_producto_variacion_variante_id` (`producto_variacion_variante_id`),
  CONSTRAINT `fk_producto_variacion_variante_id` FOREIGN KEY (`producto_variacion_variante_id`) REFERENCES `producto_variacion_variante` (`id`),
  CONSTRAINT `order_detail_ecommerce_ibfk_1` FOREIGN KEY (`order_ecommerce_id`) REFERENCES `order_ecommerce` (`order_id`),
  CONSTRAINT `order_detail_ecommerce_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `productos` (`idarticulo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `order_detail_ecommerce` */

/*Table structure for table `order_ecommerce` */

DROP TABLE IF EXISTS `order_ecommerce`;

CREATE TABLE `order_ecommerce` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `status_order_id` int NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal_amount` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `additional_info` text,
  PRIMARY KEY (`order_id`),
  KEY `status_order_id` (`status_order_id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `order_ecommerce_ibfk_1` FOREIGN KEY (`status_order_id`) REFERENCES `status_orders_ecommerce` (`status_id`),
  CONSTRAINT `order_ecommerce_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`idcliente`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `order_ecommerce` */

/*Table structure for table `password_resets` */

DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
  `email` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `password_resets` */

/*Table structure for table `payment_ecommerce` */

DROP TABLE IF EXISTS `payment_ecommerce`;

CREATE TABLE `payment_ecommerce` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method_id` int DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(11,2) NOT NULL,
  `status_payment` enum('Pendiente','Completado','Fallido','Reembolsado','Cancelado') DEFAULT 'Pendiente',
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `payment_method_id` (`payment_method_id`),
  CONSTRAINT `payment_ecommerce_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_ecommerce` (`order_id`),
  CONSTRAINT `payment_ecommerce_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `payment_ecommerce` */

/*Table structure for table `payment_methods` */

DROP TABLE IF EXISTS `payment_methods`;

CREATE TABLE `payment_methods` (
  `payment_method_id` int NOT NULL AUTO_INCREMENT,
  `method_name` varchar(50) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`payment_method_id`),
  UNIQUE KEY `method_name` (`method_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `payment_methods` */

insert  into `payment_methods`(`payment_method_id`,`method_name`,`status`) values 
(1,'Tarjeta de crédito',1),
(2,'PayPal',1),
(3,'Transferencia bancaria',1),
(4,'Efectivo',1),
(5,'Tarjeta de debito',1);

/*Table structure for table `permission_role` */

DROP TABLE IF EXISTS `permission_role`;

CREATE TABLE `permission_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `permission_role` */

/*Table structure for table `permissions` */

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `description` text CHARACTER SET utf8mb4 ,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

/*Data for the table `permissions` */

insert  into `permissions`(`id`,`name`,`slug`,`description`,`created_at`,`updated_at`) values 
(1,'listar el menu principal','admin.index','un administrador puede ver menu','2020-10-17 21:47:42','2020-10-17 21:47:42'),
(2,'listar el menu de almacen','almacen.index','Un usuario puede ver menu de alamcen','2020-10-17 21:47:42','2020-10-17 21:47:42'),
(3,'listar el menu de compras','compras.index','Un usuario puede ver menu de compras','2020-10-17 21:47:42','2020-10-17 21:47:42'),
(4,'listar el menu de ventas','ventas.index','Un usuario puede ver menu de ventas','2020-10-17 21:47:42','2020-10-17 21:47:42'),
(5,'listar el menu de caja','caja.index','Un usuario puede ver el menu de caja',NULL,NULL),
(6,'listar el menu de devoluciones','devolucion.index','Un usuario puede ver el menu de devoluciones',NULL,NULL),
(7,'listar el menu de inventario','inventario.index','Un usuario puede ver el menu de inventario',NULL,NULL),
(8,'listar la seccion de roles','admin_role.index','Un usuario puede ver la seccion de roles',NULL,NULL),
(9,'listarla seccion de usuarios','admin_user.index','Un usuario puede ver la seccion de usuarios',NULL,NULL),
(10,'listar la seccion de apertura de caja','caja_apertura.index','Un usuario puede aperturar una caja para vender',NULL,NULL),
(11,'listar la seccion de corte de caja','caja_corte.index','Un usuario puede realizar el corte de caja',NULL,NULL),
(12,'listar la seccion de corte parcial de caja','caja_parcial.index','Un usuario puede realizar el corte de caja parcial',NULL,NULL),
(13,'listar la seccion de articulos','almacen_articulo.index','Un usuario puede realizar la alta de productos',NULL,NULL),
(14,'listar la seccion de categorias','almacen_categoria.index','Un usuario puede realizar la alta de categorias',NULL,NULL),
(15,'listar la seccion de entrada de mercancia','compras_entrada.index','Un usuario puede realizar la entrada de productos',NULL,NULL),
(16,'listar la seccion de proveedores','compras_proveedor.index','Un usuario puede realizar el registro de un proveedor',NULL,NULL),
(17,'listar la seccion de ventas','ventas_venta.index','Un usuario puede realizar las ventas',NULL,NULL),
(18,'listar la seccion de clientes','ventas_cliente.index','Un usuario puede realizar el registro de los clientes',NULL,NULL),
(19,'listar la seccion de devoluciones','devolucion_producto.index','Un usuario puede realizar la devolucion de productos',NULL,NULL),
(20,'listar el menu de reportes','reporte.index','Un usuario puede ver el menu de reportes',NULL,NULL),
(21,'listar el menu de configuracion','configuracion.index','Un usuario puede ver el modulo de configuracion',NULL,NULL),
(22,'Listar la seccion de inventario','almacen_inventario.index','Un usuario puede ver el módulo de inventario',NULL,NULL),
(23,'listar la seccion de historico de cajas','caja_historicolist.index','Un usuario puede vel la seccion de historico de cajas','2023-02-26 23:42:50','2023-02-26 23:42:50'),
(24,'Listar la seccion de cotizacion','cotizaciones.index','Un usuario puede ver el menu de cotizaciones',NULL,NULL),
(25,'Crear una cotizacion','cotizaciones_cliente.index','Un usuario puede crear una cotizacion',NULL,NULL),
(26,'Listar las cotizaciones','cotizaciones_cotizacion.index','Un usuario puede ver las cotizaciones',NULL,NULL);

/*Table structure for table `producto_integracion_variante` */

DROP TABLE IF EXISTS `producto_integracion_variante`;
CREATE TABLE `producto_integracion_variante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `variacion_id` int NOT NULL,
  `variante_id` int NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `status` varchar(5) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `variacion_id` (`variacion_id`),
  KEY `variante_id` (`variante_id`),
  CONSTRAINT `producto_integracion_variante_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`idarticulo`),
  CONSTRAINT `producto_integracion_variante_ibfk_2` FOREIGN KEY (`variacion_id`) REFERENCES `variaciones` (`id`),
  CONSTRAINT `producto_integracion_variante_ibfk_3` FOREIGN KEY (`variante_id`) REFERENCES `variantes_para_variaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `producto_integracion_variante` */

/*Table structure for table `producto_variacion_variante` */

DROP TABLE IF EXISTS `producto_variacion_variante`;

CREATE TABLE `producto_variacion_variante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `color_id` int NOT NULL,
  `product_integration_id` int NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `name_image` varchar(500) NOT NULL,
  `path_image` varchar(1000) NOT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) DEFAULT '1',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `show_ecommerce` tinyint(1) DEFAULT '0',
  `pcompra` decimal(11,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `color_id` (`color_id`),
  KEY `product_integration_id` (`product_integration_id`),
  CONSTRAINT `producto_variacion_variante_ibfk_1` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`),
  CONSTRAINT `producto_variacion_variante_ibfk_2` FOREIGN KEY (`product_integration_id`) REFERENCES `producto_integracion_variante` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `producto_variacion_variante` */

/*Table structure for table `productos` */

DROP TABLE IF EXISTS `productos`;

CREATE TABLE `productos` (
  `idarticulo` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4  NOT NULL,
  `stock` double(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4  NOT NULL,
  `imagen` varchar(200) CHARACTER SET utf8mb4  NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4  NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `iva` decimal(11,2) NOT NULL,
  `tipo_producto_id` int DEFAULT NULL,
  PRIMARY KEY (`idarticulo`),
  KEY `productos_categoria_id_foreign` (`categoria_id`),
  KEY `fk_tipo_producto_id` (`tipo_producto_id`),
  CONSTRAINT `fk_tipo_producto_id` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`),
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`idcategoria`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `productos` */

/*Table structure for table `proveedores` */

DROP TABLE IF EXISTS `proveedores`;

CREATE TABLE `proveedores` (
  `idproveedor` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(300) CHARACTER SET utf8mb4  NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8mb4  NOT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4  NOT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  PRIMARY KEY (`idproveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `proveedores` */

/*Table structure for table `role_user` */

DROP TABLE IF EXISTS `role_user`;

CREATE TABLE `role_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `role_user` */

insert  into `role_user`(`id`,`role_id`,`user_id`,`created_at`,`updated_at`) values 
(1,1,1,'2023-02-18 15:26:57','2023-02-18 15:26:57');

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `description` text CHARACTER SET utf8mb4 ,
  `full-access` enum('yes','no') CHARACTER SET utf8mb4  DEFAULT NULL,
  `estatus` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `roles` */

insert  into `roles`(`id`,`name`,`slug`,`description`,`full-access`,`estatus`,`created_at`,`updated_at`) values 
(1,'Admin','Admin','Administrador','yes',1,'2023-02-18 15:25:32','2023-02-18 15:25:32');

/*Table structure for table `status_orders_ecommerce` */

DROP TABLE IF EXISTS `status_orders_ecommerce`;

CREATE TABLE `status_orders_ecommerce` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `status_orders_ecommerce` */

insert  into `status_orders_ecommerce`(`status_id`,`status_name`,`active`,`registration_date`) values 
(1,'Pendiente',1,'2024-12-23 12:37:24'),
(2,'Pagado',1,'2024-12-23 12:37:24'),
(3,'Enviado',1,'2024-12-23 12:37:24'),
(4,'Entregado',1,'2024-12-23 12:37:24'),
(5,'Cancelado',1,'2024-12-23 12:37:24');

/*Table structure for table `tipo_producto` */

DROP TABLE IF EXISTS `tipo_producto`;

CREATE TABLE `tipo_producto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  Schema::create('tipo_producto', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('descripcion', 255);
            $table->boolean('status')->default(1);
            $table->timestamp('registration_date')->useCurrent()->nullable();
        });NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `tipo_producto` */

insert  into `tipo_producto`(`id`,`name`,`descripcion`,`status`,`registration_date`) values 
(1,'Producto simple','',1,'2024-12-23 12:34:31'),
(2,'Producto personalizado','',1,'2024-12-23 12:34:45');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `estatus` int NOT NULL DEFAULT '1',
  `remember_token` varchar(100) CHARACTER SET utf8mb4  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

insert  into `users`(`id`,`name`,`email`,`email_verified_at`,`password`,`estatus`,`remember_token`,`created_at`,`updated_at`) values 
(1,'Admin','admin@gmail.com',NULL,'$2y$10$XF4As8Nz9lr3UCBjo3YxKOpe4dHSqK4DrwPPqjA5JJwrfZXRe.p0.',1,'QRFkQj8jCYWw6RDF9QaCNK0sD7ue8zTPmIOB8tKrF4Hmzya9dAt8ZYn0o028','2023-02-18 15:00:57','2023-09-15 04:23:04');

/*Table structure for table `variaciones` */

DROP TABLE IF EXISTS `variaciones`;

CREATE TABLE `variaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `variaciones` */

/*Table structure for table `variantes_para_variaciones` */

DROP TABLE IF EXISTS `variantes_para_variaciones`;

CREATE TABLE `variantes_para_variaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `variacion_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `variacion_id` (`variacion_id`),
  CONSTRAINT `variantes_para_variaciones_ibfk_1` FOREIGN KEY (`variacion_id`) REFERENCES `variaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `variantes_para_variaciones` */

/*Table structure for table `ventas` */

DROP TABLE IF EXISTS `ventas`;

CREATE TABLE `ventas` (
  `idventa` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `tipo_comprobante` varchar(100) CHARACTER SET utf8mb4  NOT NULL,
  `num_folio` varchar(50) CHARACTER SET utf8mb4  NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `efectivo` decimal(11,2) NOT NULL,
  `total_venta` decimal(11,2) NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4  NOT NULL,
  PRIMARY KEY (`idventa`),
  KEY `ventas_user_id_foreign` (`user_id`),
  KEY `ventas_cliente_id_foreign` (`cliente_id`),
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE,
  CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `ventas` */

/* Trigger structure for table `detalle_devolucion_ventas` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trigger_deleteProductoOnDetalleVentas` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `trigger_deleteProductoOnDetalleVentas` AFTER INSERT ON `detalle_devolucion_ventas` FOR EACH ROW BEGIN
IF NEW.motivo = "Devolucion a stock" THEN
SET @idventa = (select dv.venta_id from devolucion_ventas as dv inner join detalle_devolucion_ventas as ddv
where dv.iddevolucion=NEW.devolucion_id AND ddv.devolucion_id=NEW.devolucion_id group by dv.venta_id);
DELETE FROM detalle_ventas WHERE detalle_ventas.venta_id=@idventa AND detalle_ventas.articulo_id=NEW.articulo_id;
UPDATE ventas AS vent SET vent.total_venta=vent.total_venta-NEW.subtotal WHERE vent.idventa=@idventa;
UPDATE productos AS p SET p.stock=p.stock+NEW.cantidad
WHERE p.idarticulo=NEW.articulo_id; 
ELSE
SET @idventa = (select dv.venta_id from devolucion_ventas as dv inner join detalle_devolucion_ventas as ddv
where dv.iddevolucion=NEW.devolucion_id AND ddv.devolucion_id=NEW.devolucion_id group by dv.venta_id);
DELETE FROM detalle_ventas WHERE detalle_ventas.venta_id=@idventa AND detalle_ventas.articulo_id=NEW.articulo_id;
UPDATE ventas AS vent SET vent.total_venta=vent.total_venta-NEW.subtotal WHERE vent.idventa=@idventa;
END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `detalle_ingresos` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trigger_updateStockProducto` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `trigger_updateStockProducto` AFTER INSERT ON `detalle_ingresos` FOR EACH ROW BEGIN
	if NEW.tipo_producto_id = 2 AND new.tipo_producto_id IS NOT NULL then
		UPDATE producto_variacion_variante SET stock=stock + NEW.cantidad, pcompra=NEW.precio_compra, price=NEW.precio_venta 
		WHERE id = NEW.producto_variacion_variante_id;
	ELSEIF NEW.tipo_producto_id = 1 THEN
		UPDATE productos SET stock=stock + NEW.cantidad, pcompra=NEW.precio_compra, pventa=NEW.precio_venta 
		WHERE productos.idarticulo = NEW.articulo_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `detalle_ventas` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trigger_updateStockVenta` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `trigger_updateStockVenta` AFTER INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
	IF NEW.tipo_producto_id = 2 AND new.tipo_producto_id IS NOT NULL THEN
		UPDATE producto_variacion_variante SET stock=stock - NEW.cantidad 
		WHERE id = NEW.producto_variacion_variante_id;
		
		UPDATE productos SET stock=stock-NEW.cantidad
		WHERE productos.idarticulo=NEW.articulo_id;
		
	ELSEIF NEW.tipo_producto_id = 1 THEN
		UPDATE productos SET stock=stock-NEW.cantidad
		WHERE productos.idarticulo=NEW.articulo_id;
	END IF;
	UPDATE corte_cajero_dia SET total_acomulado=total_acomulado+NEW.subtotal WHERE corte_cajero_dia.apertura_id=NEW.apertura_id;
END */$$


DELIMITER ;

/* Trigger structure for table `numero_corte_por_cajero` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trigger_updatecantidadacomulado` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `trigger_updatecantidadacomulado` AFTER INSERT ON `numero_corte_por_cajero` FOR EACH ROW BEGIN
	UPDATE corte_cajero_dia SET total_acomulado=total_acomulado-NEW.cantidad
	WHERE corte_cajero_dia.idcortecaja=NEW.cortecaja_id;
    END */$$


DELIMITER ;

/* Trigger structure for table `order_ecommerce` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trigger_cancelOrderEcommerce` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `trigger_cancelOrderEcommerce` AFTER UPDATE ON `order_ecommerce` FOR EACH ROW BEGIN
DECLARE caja_open_id INT;
		
IF NEW.status_order_id = 5 AND OLD.status_order_id <> 5 THEN  -- Check if the status was updated to Cancel
   SELECT caja_virtual_id INTO caja_open_id FROM aperturacajavirtual WHERE STATUS = 1 AND end_date_time IS NULL AND status_box = 'virtual_box_open';
    IF caja_open_id IS NOT NULL THEN
	/***********CODIGO POR SI SE REALIZA UNA CANCELACION DE PEDIDO QUE YA SE AHIGA PAGADO*********/
	/*select payment_id into paymentId from payment_ecommerce where status = 1 and payment_method_id IS NOT NULL and status_payment = 'Completado';
	IF paymentId IS NOT NULL THEN
		UPDATE aperturacajavirtual SET total = total - NEW.total_amount WHERE caja_virtual_id = caja_open_id; -- restar el total caja
		UPDATE payment_ecommerce SET status_payment = 'Cancelado' WHERE order_id = NEW.order_id;
    	else
		UPDATE payment_ecommerce SET status_payment = 'Cancelado' WHERE order_id = NEW.order_id;
    	end if;*/
    	UPDATE payment_ecommerce SET status_payment = 'Cancelado' WHERE order_id = NEW.order_id;
    ELSE
    	UPDATE payment_ecommerce SET status_payment = 'Fallido' WHERE order_id = NEW.order_id;
    END IF;  
END IF;
END */$$


DELIMITER ;

/* Trigger structure for table `order_ecommerce` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `trigger_afterOrderPaid` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `trigger_afterOrderPaid` AFTER UPDATE ON `order_ecommerce` FOR EACH ROW begin
		DECLARE caja_open_id INT;
		
    	IF NEW.status_order_id = 2 AND OLD.status_order_id <> 2 THEN 	-- Check if the status was updated to 'Paid'
    		select caja_virtual_id into caja_open_id from aperturacajavirtual where status = 1 and end_date_time IS null and status_box = 'virtual_box_open';
    		IF caja_open_id IS NOT NULL THEN
    		   UPDATE aperturacajavirtual SET total = total + NEW.total_amount WHERE caja_virtual_id = caja_open_id; -- Update the total
    		   CALL processStockAfterOrderPaid(NEW.order_id); -- llama al procedimiento para restar el stock al producto simple y variante
		   UPDATE payment_ecommerce SET status_payment = 'Completado' WHERE order_id = NEW.order_id;
    	   ELSE
    	      UPDATE payment_ecommerce SET status_payment = 'Fallido' WHERE order_id = NEW.order_id;
			END IF;  
    	END IF;
end */$$


DELIMITER ;

/*!50106 set global event_scheduler = 1*/;

/* Event structure for event `insert_at_start_of_day_on_table_aperturacajavirtual` */

/*!50106 DROP EVENT IF EXISTS `insert_at_start_of_day_on_table_aperturacajavirtual`*/;

DELIMITER $$

/*!50106 CREATE EVENT `insert_at_start_of_day_on_table_aperturacajavirtual` ON SCHEDULE EVERY 1 DAY STARTS '2024-12-18 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO INSERT INTO aperturacajavirtual (initial_amount, final_amount, total, start_date_time, status, status_box)
VALUES (0.00, 0.00, 0.00, CURRENT_DATE + INTERVAL 0 SECOND, TRUE, 'virtual_box_open') */$$
DELIMITER ;

/* Event structure for event `update_end_of_day_on_table_aperturacajavirtual` */

/*!50106 DROP EVENT IF EXISTS `update_end_of_day_on_table_aperturacajavirtual`*/;

DELIMITER $$

/*!50106 CREATE EVENT `update_end_of_day_on_table_aperturacajavirtual` ON SCHEDULE EVERY 1 DAY STARTS '2024-12-17 23:59:59' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE aperturacajavirtual SET status_box = 'virtual_box_closed', end_date_time = NOW()
    WHERE status_box = 'virtual_box_open';
END */$$
DELIMITER ;

/* Procedure structure for procedure `add_detalle_entrada_tmp` */

/*!50003 DROP PROCEDURE IF EXISTS  `add_detalle_entrada_tmp` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `add_detalle_entrada_tmp`(id_user_tmp INT, idarticulo_tmp INT,
 codigo_tmp VARCHAR(50), nombre_tmp VARCHAR(500), cantidad_tmp DECIMAL(11,3),
 pcompra_tmp DECIMAL(11,2), pventa_tmp DECIMAL(11,2), `tipoProductoId_tmp` INT, `producto_variacion_variante_id_tmp` INT)
BEGIN
DECLARE v_exists INT DEFAULT 0;
DECLARE tipoProductoId INT DEFAULT 0;
DECLARE v_exists_variacion_variante INT DEFAULT 0;
 -- Save into variable
SELECT COUNT(*) INTO v_exists FROM detalle_entrada_temp WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp;
IF v_exists > 0 THEN
    -- Update if exists
    SELECT tipo_producto_id INTO tipoProductoId FROM detalle_entrada_temp WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp LIMIT 1;
    IF tipoProductoId = 1 THEN
	UPDATE detalle_entrada_temp SET cantidad = cantidad + cantidad_tmp, pcompra = pcompra_tmp, pventa = pventa_tmp WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp;
    ELSEIF tipoProductoId = 2 THEN
	SELECT COUNT(*) INTO v_exists_variacion_variante FROM detalle_entrada_temp WHERE 
	id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp AND producto_variacion_variante_id = producto_variacion_variante_id_tmp;
	IF v_exists_variacion_variante > 0 THEN
		UPDATE detalle_entrada_temp SET cantidad = cantidad + cantidad_tmp, pcompra = pcompra_tmp, pventa = pventa_tmp 
		WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp 
		AND producto_variacion_variante_id = producto_variacion_variante_id_tmp;
	ELSE
		INSERT INTO detalle_entrada_temp(id_user,idarticulo,codigo,nombre, cantidad,pcompra,pventa,tipo_producto_id,producto_variacion_variante_id)
		VALUES(id_user_tmp,idarticulo_tmp,codigo_tmp,nombre_tmp,cantidad_tmp,pcompra_tmp,pventa_tmp,tipoProductoId_tmp,producto_variacion_variante_id_tmp);
	END IF;
	
    END IF;
    
ELSE
    INSERT INTO detalle_entrada_temp(id_user,idarticulo,codigo,nombre, cantidad,pcompra,pventa,tipo_producto_id,producto_variacion_variante_id)
    VALUES(id_user_tmp,idarticulo_tmp,codigo_tmp,nombre_tmp,cantidad_tmp,pcompra_tmp,pventa_tmp,tipoProductoId_tmp,producto_variacion_variante_id_tmp);
END IF;
SELECT *, SUM(tmp.cantidad) AS total_articulos FROM detalle_entrada_temp tmp WHERE tmp.id_user=id_user_tmp GROUP BY tmp.idarticulo, tmp.identradatemp ORDER BY tmp.identradatemp DESC;
END */$$
DELIMITER ;

/* Procedure structure for procedure `add_detalle_venta_temp` */

/*!50003 DROP PROCEDURE IF EXISTS  `add_detalle_venta_temp` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `add_detalle_venta_temp`(`id_user_tmp` INT, `idarticulo_tmp` INT,
 `codigo_tmp` VARCHAR(50), `nombre_tmp` VARCHAR(500), `cantidad_tmp` DECIMAL(11,3), `precio_tmp` DECIMAL(11,2),
 `descuento_tmp` DECIMAL(11,2), `iva_tmp` DECIMAL(11,2), `tipoProductoId_tmp` INT, `producto_variacion_variante_id_tmp` INT)
BEGIN
DECLARE v_exists INT DEFAULT 0;
DECLARE v_exists_variacion_variante INT DEFAULT 0;
DECLARE tipoProductoId INT DEFAULT 0;
 -- Save into variable
SELECT COUNT(*) INTO v_exists FROM detalle_venta_temp WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp;
IF v_exists > 0 THEN
 -- Update if exists
    SELECT tipo_producto_id INTO tipoProductoId FROM detalle_venta_temp WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp limit 1; -- AND producto_variacion_variante_id = producto_variacion_variante_id_tmp;
    IF tipoProductoId = 1 THEN
	UPDATE detalle_venta_temp SET cantidad = cantidad + cantidad_tmp, precio = precio_tmp, descuento = descuento_tmp 
	WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp;
   ELSEIF tipoProductoId = 2 THEN
	SELECT COUNT(*) INTO v_exists_variacion_variante FROM detalle_venta_temp WHERE 
	id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp AND producto_variacion_variante_id = producto_variacion_variante_id_tmp;
	IF v_exists_variacion_variante > 0 THEN
		UPDATE detalle_venta_temp SET cantidad = cantidad + cantidad_tmp, precio = precio_tmp, descuento = descuento_tmp 
		WHERE id_user = id_user_tmp AND idarticulo = idarticulo_tmp AND tipo_producto_id = tipoProductoId_tmp 
		AND producto_variacion_variante_id = producto_variacion_variante_id_tmp;
	else
		INSERT INTO detalle_venta_temp(id_user,idarticulo,codproducto,nombre,cantidad,precio,descuento,iva,tipo_producto_id, producto_variacion_variante_id)
		VALUES(id_user_tmp,idarticulo_tmp,codigo_tmp,nombre_tmp,cantidad_tmp,precio_tmp,descuento_tmp,iva_tmp,tipoProductoId_tmp,producto_variacion_variante_id_tmp);
	END IF;
	
	
    END IF;
    
ELSE
	INSERT INTO detalle_venta_temp(id_user,idarticulo,codproducto,nombre,cantidad,precio,descuento,iva,tipo_producto_id, producto_variacion_variante_id)
	VALUES(id_user_tmp,idarticulo_tmp,codigo_tmp,nombre_tmp,cantidad_tmp,precio_tmp,descuento_tmp,iva_tmp,tipoProductoId_tmp,producto_variacion_variante_id_tmp);
END IF;
SELECT *, SUM(tmp.cantidad) AS total_articulos FROM detalle_venta_temp tmp WHERE tmp.id_user=id_user_tmp GROUP BY tmp.idarticulo, tmp.iddetalletemp ORDER BY tmp.iddetalletemp DESC;
END */$$
DELIMITER ;

/* Procedure structure for procedure `delete_detalle_venta_temp` */

/*!50003 DROP PROCEDURE IF EXISTS  `delete_detalle_venta_temp` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `delete_detalle_venta_temp`(`id_detalle_articulo_temp` INT,`id_user_temp` INT, `id_articulo` INT, `tipo_producto_id_temp` INT, `producto_variacion_variante_id_temp` INT)
BEGIN
     	DELETE FROM detalle_venta_temp WHERE id_user=id_user_temp AND idarticulo=id_articulo and tipo_producto_id = tipo_producto_id_temp and producto_variacion_variante_id = producto_variacion_variante_id_temp;
        SELECT *, SUM(tmp.cantidad) AS total_articulos FROM detalle_venta_temp tmp WHERE tmp.id_user=id_user_temp GROUP BY tmp.idarticulo, tmp.iddetalletemp ORDER BY tmp.iddetalletemp DESC;
        
     END */$$
DELIMITER ;

/* Procedure structure for procedure `delete_prod_entrada_tmp` */

/*!50003 DROP PROCEDURE IF EXISTS  `delete_prod_entrada_tmp` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `delete_prod_entrada_tmp`(idart_tmp INT,id_user_tmp INT,id_articulo INT, `tipo_producto_id_temp` INT, `producto_variacion_variante_id_temp` INT)
BEGIN
DELETE FROM detalle_entrada_temp WHERE id_user=id_user_tmp AND idarticulo=id_articulo AND tipo_producto_id = tipo_producto_id_temp AND producto_variacion_variante_id = producto_variacion_variante_id_temp;
SELECT *, SUM(tmp.cantidad) AS total_articulos FROM detalle_entrada_temp tmp WHERE tmp.id_user=id_user_tmp GROUP BY tmp.idarticulo, tmp.identradatemp ORDER BY tmp.identradatemp DESC;
END */$$
DELIMITER ;

/* Procedure structure for procedure `processStockAfterOrderPaid` */

/*!50003 DROP PROCEDURE IF EXISTS  `processStockAfterOrderPaid` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `processStockAfterOrderPaid`(order_id INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_producto_variacion_variante_id INT DEFAULT null;
    DECLARE v_product_id INT;
    DECLARE v_quantity INT;
    DECLARE tipoProductoId INT;
    
    -- Declaramos el cursor
    DECLARE cur CURSOR FOR 
        SELECT product_id, quantity, producto_variacion_variante_id FROM order_detail_ecommerce WHERE order_ecommerce_id = order_id;
    
    -- Handler para controlar el final del cursor
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Abrimos el cursor
    OPEN cur;

    read_loop: LOOP
        -- Obtenemos la siguiente fila
        FETCH cur INTO v_product_id, v_quantity, v_producto_variacion_variante_id;
        
        -- Verificamos si terminamos
        IF done THEN
            LEAVE read_loop;
        END IF;
        -- SELECT CONCAT('El valor de product_id es: ', v_quantity) AS mensaje;
        -- Aquí puedes realizar la lógica con cada fila obtenida
        SELECT tipo_producto_id INTO tipoProductoId FROM productos WHERE idarticulo = v_product_id;
        IF tipoProductoId = 1 THEN
		UPDATE productos SET stock=stock - v_quantity
		WHERE productos.idarticulo=v_product_id;
        ELSEIF tipoProductoId = 2 THEN
		UPDATE producto_variacion_variante SET stock=stock - v_quantity 
		WHERE id = v_producto_variacion_variante_id;
		
		UPDATE productos SET stock=stock - v_quantity
		WHERE productos.idarticulo=v_product_id;
        END IF;
        
    END LOOP;

    -- Cerramos el cursor
    CLOSE cur;
END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
