-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-09-2025 a las 15:16:27
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
-- Base de datos: `tenant_cdfsdfsd`
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

--
-- Volcado de datos para la tabla `aperturacajas`
--

INSERT INTO `aperturacajas` (`idapertura`, `user_id`, `cantidad_inicial`, `cantidad_final`, `estatus`, `fecha_hora`, `fecha_hora_cierre`) VALUES
(1, 1, 100.00, 0.00, 'Abierta', '2025-08-18 11:48:25', NULL),
(2, 1, 20000.00, 0.00, 'Abierta', '2025-08-29 13:24:47', NULL),
(3, 1, 10000.00, 0.00, 'Abierta', '2025-09-08 04:27:39', NULL),
(4, 1, 2332323.00, 0.00, 'Abierta', '2025-09-10 07:35:04', NULL),
(5, 1, 1000.00, 0.00, 'Abierta', '2025-09-24 12:05:03', NULL),
(6, 1, 1000.00, 0.00, 'Abierta', '2025-09-26 17:11:59', NULL);

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

--
-- Volcado de datos para la tabla `banner_ecommerce`
--

INSERT INTO `banner_ecommerce` (`banner_id`, `name`, `name_image`, `description`, `banner_date`, `status`, `name_image_movil`) VALUES
(1, 'banner_1', 'CINTIA HOLZ (8).png', NULL, NULL, 1, 'Bienvenido (6).png');

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
(1, 'Sabanas', 'Sabanas', 'SABANA1.jpg', 1),
(2, 'Electrónicos', 'Auto-created from bulk import', '', 1),
(3, 'Accesorios', 'Auto-created from bulk import', '', 1);

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
  `documentation_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `response`, `is_bot`, `session_id`, `documentation_id`, `rating`, `feedback`, `created_at`, `updated_at`) VALUES
(1, 1, 'hola', '¡Hola! ¿En qué puedo ayudarte hoy?', 0, 'chat_1754593423493_extr2ni6g', NULL, NULL, NULL, '2025-08-07 22:03:51', '2025-08-07 22:03:51'),
(2, 1, '', '¡Hola! ¿En qué puedo ayudarte hoy?', 1, 'chat_1754593423493_extr2ni6g', NULL, NULL, NULL, '2025-08-07 22:03:51', '2025-08-07 22:03:51'),
(3, 1, 'How do I create a new sale?', 'To create a new sale in Facturarg, please follow these steps:\n\n1. **Sign In**: Log in to your Facturarg account with your credentials.\n2. **Navigate to Sales**: Go to the \"Sales\" or \"Invoices\" section. This can usually be found in the main menu or dashboard.\n3. **Create a New Sale**: Look for an option like \"Create New Sale\" or \"New Invoice\" and click on it.\n4. **Add Customer Details**: Enter the customer\'s name, contact information, and any other required details.\n5. **Add Products/Services**: Click on \"Add Item\" or similar to select the products or services being sold. Enter quantity, price, and any other relevant information.\n6. **Review and Finalize**: Double-check all details, including prices and quantities. Make any necessary adjustments.\n7. **Save or Send**: Once everything looks correct, you can save the sale as a draft, print it, or send it directly to the customer.\n\nIf you encounter any issues or need further assistance, feel free to reach out.', 0, 'chat_1754774024938_ayjohc4bk', NULL, NULL, NULL, '2025-08-10 00:13:53', '2025-08-10 00:13:53'),
(4, 1, '', 'To create a new sale in Facturarg, please follow these steps:\n\n1. **Sign In**: Log in to your Facturarg account with your credentials.\n2. **Navigate to Sales**: Go to the \"Sales\" or \"Invoices\" section. This can usually be found in the main menu or dashboard.\n3. **Create a New Sale**: Look for an option like \"Create New Sale\" or \"New Invoice\" and click on it.\n4. **Add Customer Details**: Enter the customer\'s name, contact information, and any other required details.\n5. **Add Products/Services**: Click on \"Add Item\" or similar to select the products or services being sold. Enter quantity, price, and any other relevant information.\n6. **Review and Finalize**: Double-check all details, including prices and quantities. Make any necessary adjustments.\n7. **Save or Send**: Once everything looks correct, you can save the sale as a draft, print it, or send it directly to the customer.\n\nIf you encounter any issues or need further assistance, feel free to reach out.', 1, 'chat_1754774024938_ayjohc4bk', NULL, NULL, NULL, '2025-08-10 00:13:53', '2025-08-10 00:13:53'),
(5, 1, 'How do I create a new sale?', 'To create a new sale in Facturarg, you can follow these steps:\n\n1. **Navigate to the Sales Module**: Go to the main dashboard and look for the \"Sales\" module or tab.\n2. **Start a New Sale**: Look for an option like \"New Sale\" or \"Create Sale\" and click on it.\n3. **Add Customer Information**: Enter the customer details like name, contact information, and billing address.\n4. **Add Products/Services**: Click on an option to add products or services to the sale. You may need to search for the items or add them manually.\n5. **Set Quantity and Prices**: Enter the quantity of each item and adjust prices if needed.\n6. **Apply Discounts or Taxes**: If applicable, apply any discounts or taxes to the sale.\n7. **Review and Finalize**: Double-check all the details, make necessary adjustments, and then finalize the sale.\n8. **Save and Send**: Save the sale and choose to send an invoice to the customer via email or print it out.\n\nIf you encounter any issues during the process, please let me know so I can provide further assistance.', 0, '6b9a8f90-5a6a-41ee-93e1-a49dea6e72d0', NULL, NULL, NULL, '2025-08-10 00:13:57', '2025-08-10 00:13:57'),
(6, 1, '', 'To create a new sale in Facturarg, you can follow these steps:\n\n1. **Navigate to the Sales Module**: Go to the main dashboard and look for the \"Sales\" module or tab.\n2. **Start a New Sale**: Look for an option like \"New Sale\" or \"Create Sale\" and click on it.\n3. **Add Customer Information**: Enter the customer details like name, contact information, and billing address.\n4. **Add Products/Services**: Click on an option to add products or services to the sale. You may need to search for the items or add them manually.\n5. **Set Quantity and Prices**: Enter the quantity of each item and adjust prices if needed.\n6. **Apply Discounts or Taxes**: If applicable, apply any discounts or taxes to the sale.\n7. **Review and Finalize**: Double-check all the details, make necessary adjustments, and then finalize the sale.\n8. **Save and Send**: Save the sale and choose to send an invoice to the customer via email or print it out.\n\nIf you encounter any issues during the process, please let me know so I can provide further assistance.', 1, '6b9a8f90-5a6a-41ee-93e1-a49dea6e72d0', NULL, NULL, NULL, '2025-08-10 00:13:57', '2025-08-10 00:13:57'),
(7, 1, 'How do I create a new sale?', 'To create a new sale in Facturarg, please follow these steps:\n\n1. **Log in to your Facturarg account** using your credentials.\n2. Go to the **Sales** menu or **Invoicing** section.\n3. Look for an option like **Create New Sale** or **New Invoice**.\n4. Click on the **Create New Sale** option.\n5. Fill in the required details such as customer information, products/services sold, quantities, prices, etc.\n6. Review the sale details to ensure accuracy.\n7. Once everything looks correct, click on **Save** or **Generate Invoice** to finalize the sale.\n\nIf you encounter any issues during the process, double-check that you have the necessary permissions to create sales in your account. If you need further assistance, feel free to reach out to Facturarg\'s support team for more guidance.', 0, '6b9a8f90-5a6a-41ee-93e1-a49dea6e72d0', NULL, NULL, NULL, '2025-08-10 00:14:00', '2025-08-10 00:14:00'),
(8, 1, '', 'To create a new sale in Facturarg, please follow these steps:\n\n1. **Log in to your Facturarg account** using your credentials.\n2. Go to the **Sales** menu or **Invoicing** section.\n3. Look for an option like **Create New Sale** or **New Invoice**.\n4. Click on the **Create New Sale** option.\n5. Fill in the required details such as customer information, products/services sold, quantities, prices, etc.\n6. Review the sale details to ensure accuracy.\n7. Once everything looks correct, click on **Save** or **Generate Invoice** to finalize the sale.\n\nIf you encounter any issues during the process, double-check that you have the necessary permissions to create sales in your account. If you need further assistance, feel free to reach out to Facturarg\'s support team for more guidance.', 1, '6b9a8f90-5a6a-41ee-93e1-a49dea6e72d0', NULL, NULL, NULL, '2025-08-10 00:14:00', '2025-08-10 00:14:00'),
(9, 1, 'hola', '¡Hola! ¿En qué puedo ayudarte hoy?', 0, 'chat_1754774024938_ayjohc4bk', NULL, NULL, NULL, '2025-08-10 00:14:04', '2025-08-10 00:14:04'),
(10, 1, '', '¡Hola! ¿En qué puedo ayudarte hoy?', 1, 'chat_1754774024938_ayjohc4bk', NULL, NULL, NULL, '2025-08-10 00:14:04', '2025-08-10 00:14:04'),
(11, 1, '¿Cómo creo una nueva venta?', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón \"Nueva Venta\" o \"Crear Venta\".\n4. Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Una vez completados todos los campos obligatorios, confirma la venta haciendo clic en \"Guardar\" o \"Finalizar Venta\".\n\nSi encuentras algún problema durante este proceso, asegúrate de verificar que todos los campos necesarios estén completos y que la información ingresada sea correcta. Si necesitas más ayuda o tienes alguna pregunta específica, no dudes en consultar la documentación de Facturarg o contactar al equipo de soporte.', 0, 'chat_1754774351386_rafrahi2n', NULL, NULL, NULL, '2025-08-10 00:19:24', '2025-08-10 00:19:24'),
(12, 1, '', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón \"Nueva Venta\" o \"Crear Venta\".\n4. Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Una vez completados todos los campos obligatorios, confirma la venta haciendo clic en \"Guardar\" o \"Finalizar Venta\".\n\nSi encuentras algún problema durante este proceso, asegúrate de verificar que todos los campos necesarios estén completos y que la información ingresada sea correcta. Si necesitas más ayuda o tienes alguna pregunta específica, no dudes en consultar la documentación de Facturarg o contactar al equipo de soporte.', 1, 'chat_1754774351386_rafrahi2n', NULL, NULL, NULL, '2025-08-10 00:19:24', '2025-08-10 00:19:24'),
(13, 1, '¿Cómo creo una nueva venta?', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, ve a la sección de \"Ventas\" o \"Facturación\".\n3. Busca y selecciona la opción para \"Crear nueva venta\" o \"Agregar nueva venta\".\n4. Ingresa los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, precios, impuestos, etc.\n5. Revisa toda la información ingresada para asegurarte de que sea precisa.\n6. Guarda la venta una vez que hayas verificado todos los detalles.\n\nSi necesitas más ayuda o detalles específicos sobre algún paso en particular, no dudes en preguntar. ¡Estoy aquí para ayudarte!', 0, 'a6a839be-200f-4994-90a8-bbf42fc2fceb', NULL, NULL, NULL, '2025-08-10 00:19:26', '2025-08-10 00:19:26'),
(14, 1, '', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, ve a la sección de \"Ventas\" o \"Facturación\".\n3. Busca y selecciona la opción para \"Crear nueva venta\" o \"Agregar nueva venta\".\n4. Ingresa los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, precios, impuestos, etc.\n5. Revisa toda la información ingresada para asegurarte de que sea precisa.\n6. Guarda la venta una vez que hayas verificado todos los detalles.\n\nSi necesitas más ayuda o detalles específicos sobre algún paso en particular, no dudes en preguntar. ¡Estoy aquí para ayudarte!', 1, 'a6a839be-200f-4994-90a8-bbf42fc2fceb', NULL, NULL, NULL, '2025-08-10 00:19:26', '2025-08-10 00:19:26'),
(15, 1, '¿Cómo gestionar el inventario?', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar al Sistema**: Inicia sesión en tu cuenta de Facturarg.\n2. **Acceder al Módulo de Inventario**: Dirígete al menú principal y busca la opción relacionada con \"Inventario\" o \"Productos\".\n3. **Agregar Productos al Inventario**: Haz clic en \"Agregar Producto\" o una opción similar para introducir los detalles del artículo, como nombre, descripción, precio, cantidad inicial, etc.\n4. **Actualizar Inventario**: Para ajustar la cantidad de un producto, busca la opción de \"Editar\" o \"Actualizar\" dentro de la lista de productos. Allí podrás modificar la cantidad disponible.\n5. **Registrar Movimientos de Inventario**: Para registrar entradas o salidas de productos, busca la opción de \"Movimientos de Inventario\" o \"Ingresos/Salidas\". Registra los detalles de cada movimiento para mantener un seguimiento preciso.\n\nSi necesitas más detalles sobre funciones específicas o tener acceso a la documentación detallada, te recomendaría contactar con el equipo de soporte de Facturarg o consultar la documentación oficial del sistema para obtener información más detallada sobre la gestión de inventario.', 0, 'chat_1754774351386_rafrahi2n', NULL, NULL, NULL, '2025-08-10 00:19:43', '2025-08-10 00:19:43'),
(16, 1, '', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar al Sistema**: Inicia sesión en tu cuenta de Facturarg.\n2. **Acceder al Módulo de Inventario**: Dirígete al menú principal y busca la opción relacionada con \"Inventario\" o \"Productos\".\n3. **Agregar Productos al Inventario**: Haz clic en \"Agregar Producto\" o una opción similar para introducir los detalles del artículo, como nombre, descripción, precio, cantidad inicial, etc.\n4. **Actualizar Inventario**: Para ajustar la cantidad de un producto, busca la opción de \"Editar\" o \"Actualizar\" dentro de la lista de productos. Allí podrás modificar la cantidad disponible.\n5. **Registrar Movimientos de Inventario**: Para registrar entradas o salidas de productos, busca la opción de \"Movimientos de Inventario\" o \"Ingresos/Salidas\". Registra los detalles de cada movimiento para mantener un seguimiento preciso.\n\nSi necesitas más detalles sobre funciones específicas o tener acceso a la documentación detallada, te recomendaría contactar con el equipo de soporte de Facturarg o consultar la documentación oficial del sistema para obtener información más detallada sobre la gestión de inventario.', 1, 'chat_1754774351386_rafrahi2n', NULL, NULL, NULL, '2025-08-10 00:19:43', '2025-08-10 00:19:43'),
(17, 1, '¿Cómo gestionar el inventario?', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar a tu cuenta**: Inicia sesión en tu cuenta de Facturarg.\n2. **Ir al módulo de inventario**: En el menú principal, busca y selecciona el módulo de \"Inventario\" o \"Stock\".\n3. **Agregar productos al inventario**: Para añadir productos, busca la opción que te permita \"Agregar producto\" o \"Crear nuevo producto\". Completa la información requerida como nombre, descripción, precio, cantidad inicial, etc.\n4. **Actualizar cantidades**: Para modificar la cantidad de un producto en inventario, busca la opción de \"Editar producto\" y ajusta la cantidad disponible.\n5. **Realizar ajustes de inventario**: Si necesitas hacer ajustes por pérdidas, daños u otros motivos, busca la opción de \"Ajuste de inventario\" y sigue las instrucciones para registrar estos cambios.\n6. **Realizar seguimiento de ventas**: Puedes ver qué productos se están vendiendo más y cuáles necesitan ser reabastecidos revisando los informes de ventas y el estado del inventario.\n\nSi necesitas más detalles sobre funciones específicas o tienes alguna otra pregunta, ¡házmela saber!', 0, 'a6a839be-200f-4994-90a8-bbf42fc2fceb', NULL, NULL, NULL, '2025-08-10 00:19:47', '2025-08-10 00:19:47'),
(18, 1, '', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar a tu cuenta**: Inicia sesión en tu cuenta de Facturarg.\n2. **Ir al módulo de inventario**: En el menú principal, busca y selecciona el módulo de \"Inventario\" o \"Stock\".\n3. **Agregar productos al inventario**: Para añadir productos, busca la opción que te permita \"Agregar producto\" o \"Crear nuevo producto\". Completa la información requerida como nombre, descripción, precio, cantidad inicial, etc.\n4. **Actualizar cantidades**: Para modificar la cantidad de un producto en inventario, busca la opción de \"Editar producto\" y ajusta la cantidad disponible.\n5. **Realizar ajustes de inventario**: Si necesitas hacer ajustes por pérdidas, daños u otros motivos, busca la opción de \"Ajuste de inventario\" y sigue las instrucciones para registrar estos cambios.\n6. **Realizar seguimiento de ventas**: Puedes ver qué productos se están vendiendo más y cuáles necesitan ser reabastecidos revisando los informes de ventas y el estado del inventario.\n\nSi necesitas más detalles sobre funciones específicas o tienes alguna otra pregunta, ¡házmela saber!', 1, 'a6a839be-200f-4994-90a8-bbf42fc2fceb', NULL, NULL, NULL, '2025-08-10 00:19:47', '2025-08-10 00:19:47'),
(19, 1, 'Hola, como estas! necesito aprender a usar facturarg!', '¡Hola! ¡Estoy aquí para ayudarte a aprender a usar Facturarg! ¿Podrías decirme qué función específica te gustaría aprender a usar en Facturarg? Por ejemplo, ¿necesitas ayuda para crear facturas, gestionar inventario o generar reportes? ¡Estoy aquí para guiarte paso a paso!', 0, 'chat_1754774388853_mr3j89b16', NULL, NULL, NULL, '2025-08-10 00:20:14', '2025-08-10 00:20:14'),
(20, 1, '', '¡Hola! ¡Estoy aquí para ayudarte a aprender a usar Facturarg! ¿Podrías decirme qué función específica te gustaría aprender a usar en Facturarg? Por ejemplo, ¿necesitas ayuda para crear facturas, gestionar inventario o generar reportes? ¡Estoy aquí para guiarte paso a paso!', 1, 'chat_1754774388853_mr3j89b16', NULL, NULL, NULL, '2025-08-10 00:20:14', '2025-08-10 00:20:14'),
(21, 1, 'Hola, como estas! necesito aprender a usar facturarg!', '¡Hola! ¡Estoy aquí para ayudarte a aprender a usar Facturarg! Para comenzar, ¿podrías decirme qué función específica de Facturarg te gustaría aprender a usar? Por ejemplo, ¿te gustaría aprender a crear facturas, gestionar inventario o generar informes financieros? ¡Estoy aquí para ayudarte en lo que necesites!', 0, 'chat_1754774419834_41wxl4v1z', NULL, NULL, NULL, '2025-08-10 00:21:13', '2025-08-10 00:21:13'),
(22, 1, '', '¡Hola! ¡Estoy aquí para ayudarte a aprender a usar Facturarg! Para comenzar, ¿podrías decirme qué función específica de Facturarg te gustaría aprender a usar? Por ejemplo, ¿te gustaría aprender a crear facturas, gestionar inventario o generar informes financieros? ¡Estoy aquí para ayudarte en lo que necesites!', 1, 'chat_1754774419834_41wxl4v1z', NULL, NULL, NULL, '2025-08-10 00:21:13', '2025-08-10 00:21:13'),
(23, 1, 'Hola como estas! Necesito aprender a usar facturarg!', '¡Hola! ¡Bien, gracias! ¡Estoy aquí para ayudarte a aprender a usar Facturarg! ¿En qué aspecto específico necesitas ayuda? ¿Te gustaría saber cómo crear facturas, gestionar inventario o configurar tu cuenta? Por favor, proporciona más detalles para poder brindarte la información más útil. ¡Estoy aquí para ayudarte en lo que necesites!', 0, 'chat_1754774780844_0tkkb3gel', NULL, NULL, NULL, '2025-08-10 00:27:03', '2025-08-10 00:27:03'),
(24, 1, '', '¡Hola! ¡Bien, gracias! ¡Estoy aquí para ayudarte a aprender a usar Facturarg! ¿En qué aspecto específico necesitas ayuda? ¿Te gustaría saber cómo crear facturas, gestionar inventario o configurar tu cuenta? Por favor, proporciona más detalles para poder brindarte la información más útil. ¡Estoy aquí para ayudarte en lo que necesites!', 1, 'chat_1754774780844_0tkkb3gel', NULL, NULL, NULL, '2025-08-10 00:27:03', '2025-08-10 00:27:03'),
(25, 1, 'Hola como estas necesito aprender a usar facturarg', '¡Hola! ¡Bienvenido a Facturarg! Estoy aquí para ayudarte a aprender a usar nuestro sistema. Para empezar, ¿puedes decirme qué función específica te gustaría aprender a utilizar en Facturarg? Por ejemplo, ¿te gustaría aprender a crear facturas, gestionar inventario o generar reportes? ¡Estoy aquí para ayudarte en lo que necesites!', 0, 'chat_1754774859894_mj9dg5vnc', NULL, NULL, NULL, '2025-08-10 00:28:09', '2025-08-10 00:28:09'),
(26, 1, '', '¡Hola! ¡Bienvenido a Facturarg! Estoy aquí para ayudarte a aprender a usar nuestro sistema. Para empezar, ¿puedes decirme qué función específica te gustaría aprender a utilizar en Facturarg? Por ejemplo, ¿te gustaría aprender a crear facturas, gestionar inventario o generar reportes? ¡Estoy aquí para ayudarte en lo que necesites!', 1, 'chat_1754774859894_mj9dg5vnc', NULL, NULL, NULL, '2025-08-10 00:28:09', '2025-08-10 00:28:09'),
(27, 1, 'Hola como estas necesito aprender a usar facturarg', '¡Hola! ¡Bienvenido a Facturarg! Estoy aquí para ayudarte a aprender a usar nuestro sistema. Para empezar, aquí tienes algunos pasos básicos para comenzar a utilizar Facturarg:\n\n1. **Crear una cuenta**: Visita nuestro sitio web oficial e inscríbete para obtener una cuenta. Proporciona la información requerida y sigue las instrucciones para configurar tu cuenta.\n\n2. **Iniciar sesión**: Una vez que tengas tu cuenta, inicia sesión en Facturarg con tus credenciales. Utiliza tu correo electrónico y contraseña para acceder a tu cuenta.\n\n3. **Explorar el panel de control**: Una vez dentro, explora el panel de control para familiarizarte con las diferentes secciones y funcionalidades que ofrece Facturarg. Aquí podrás encontrar opciones para facturación, inventario, reportes, entre otros.\n\n4. **Crear tu primera factura**: Para crear una factura, busca la opción correspondiente en el menú y sigue los pasos para completar la información requerida, como datos del cliente, productos/servicios, precios, etc.\n\n5. **Guardar y enviar la factura**: Una vez que hayas completado la factura, asegúrate de guardarla correctamente en el sistema. Después, podrás enviarla a tu cliente por correo electrónico u otros medios.\n\n6. **Explorar funciones avanzadas**: A medida que te familiarices con Facturarg, explora las funciones avanzadas como gestión de inventario, informes financieros, seguimiento de pagos, entre otros.\n\nRecuerda que siempre puedes consultar nuestra documentación oficial para obtener más detalles sobre cada función y resolver dudas específicas. ¿Hay algo en lo que pueda ayudarte más detalladamente?', 0, 'chat_1754775096757_enxf1qq6u', NULL, NULL, NULL, '2025-08-10 00:32:02', '2025-08-10 00:32:02'),
(28, 1, '', '¡Hola! ¡Bienvenido a Facturarg! Estoy aquí para ayudarte a aprender a usar nuestro sistema. Para empezar, aquí tienes algunos pasos básicos para comenzar a utilizar Facturarg:\n\n1. **Crear una cuenta**: Visita nuestro sitio web oficial e inscríbete para obtener una cuenta. Proporciona la información requerida y sigue las instrucciones para configurar tu cuenta.\n\n2. **Iniciar sesión**: Una vez que tengas tu cuenta, inicia sesión en Facturarg con tus credenciales. Utiliza tu correo electrónico y contraseña para acceder a tu cuenta.\n\n3. **Explorar el panel de control**: Una vez dentro, explora el panel de control para familiarizarte con las diferentes secciones y funcionalidades que ofrece Facturarg. Aquí podrás encontrar opciones para facturación, inventario, reportes, entre otros.\n\n4. **Crear tu primera factura**: Para crear una factura, busca la opción correspondiente en el menú y sigue los pasos para completar la información requerida, como datos del cliente, productos/servicios, precios, etc.\n\n5. **Guardar y enviar la factura**: Una vez que hayas completado la factura, asegúrate de guardarla correctamente en el sistema. Después, podrás enviarla a tu cliente por correo electrónico u otros medios.\n\n6. **Explorar funciones avanzadas**: A medida que te familiarices con Facturarg, explora las funciones avanzadas como gestión de inventario, informes financieros, seguimiento de pagos, entre otros.\n\nRecuerda que siempre puedes consultar nuestra documentación oficial para obtener más detalles sobre cada función y resolver dudas específicas. ¿Hay algo en lo que pueda ayudarte más detalladamente?', 1, 'chat_1754775096757_enxf1qq6u', NULL, NULL, NULL, '2025-08-10 00:32:02', '2025-08-10 00:32:02'),
(29, 1, 'Hola como estas necesito aprender a crear una venta', '¡Hola! ¡Estoy aquí para ayudarte a aprender a crear una venta en Facturarg! Aquí tienes los pasos detallados para crear una venta:\n\n1. **Iniciar Sesión**: Ingresa a tu cuenta Facturarg con tus credenciales.\n2. **Menú Ventas**: En la página principal, busca y haz clic en la opción \"Ventas\" en el menú principal.\n3. **Crear Nueva Venta**: Dentro de la sección de Ventas, busca y selecciona la opción \"Crear Nueva Venta\".\n4. **Agregar Detalles**: Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, precios, impuestos, etc.\n5. **Guardar la Venta**: Una vez completada la información, asegúrate de guardar la venta.\n6. **Enviar la Venta**: Si deseas enviar la venta al cliente, busca la opción para enviar por correo electrónico o descargar como PDF.\n\nRecuerda que es importante tener la información de los clientes y productos previamente configurada en tu cuenta para facilitar el proceso de creación de ventas. ¿Hay algo más en lo que pueda ayudarte? ¡Estoy aquí para responder tus preguntas!', 0, 'chat_1754775168781_65uhgh2su', NULL, NULL, NULL, '2025-08-10 00:33:14', '2025-08-10 00:33:14'),
(30, 1, '', '¡Hola! ¡Estoy aquí para ayudarte a aprender a crear una venta en Facturarg! Aquí tienes los pasos detallados para crear una venta:\n\n1. **Iniciar Sesión**: Ingresa a tu cuenta Facturarg con tus credenciales.\n2. **Menú Ventas**: En la página principal, busca y haz clic en la opción \"Ventas\" en el menú principal.\n3. **Crear Nueva Venta**: Dentro de la sección de Ventas, busca y selecciona la opción \"Crear Nueva Venta\".\n4. **Agregar Detalles**: Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, precios, impuestos, etc.\n5. **Guardar la Venta**: Una vez completada la información, asegúrate de guardar la venta.\n6. **Enviar la Venta**: Si deseas enviar la venta al cliente, busca la opción para enviar por correo electrónico o descargar como PDF.\n\nRecuerda que es importante tener la información de los clientes y productos previamente configurada en tu cuenta para facilitar el proceso de creación de ventas. ¿Hay algo más en lo que pueda ayudarte? ¡Estoy aquí para responder tus preguntas!', 1, 'chat_1754775168781_65uhgh2su', NULL, NULL, NULL, '2025-08-10 00:33:14', '2025-08-10 00:33:14'),
(31, 1, '¿Cómo generar informes?', 'Para generar informes en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Ve al menú principal y busca la opción de \"Informes\" o \"Reportes\".\n3. Dentro de la sección de informes, selecciona el tipo de informe que deseas generar, como por ejemplo: reporte de ventas, reporte de gastos, etc.\n4. Especifica los parámetros de tu informe, como el rango de fechas, categorías específicas, clientes, etc.\n5. Una vez hayas configurado los parámetros, busca la opción para generar o exportar el informe.\n6. Selecciona el formato en el que deseas guardar el informe, como PDF, Excel, u otro formato compatible.\n7. Haz clic en \"Generar\" o \"Exportar\" para crear tu informe según los parámetros establecidos.\n\nSi tienes problemas para generar un informe específico o necesitas más detalles sobre un tipo de informe en particular, no dudes en contactar al equipo de soporte de Facturarg para obtener ayuda adicional.', 0, 'chat_1754775168781_65uhgh2su', NULL, NULL, NULL, '2025-08-10 00:38:54', '2025-08-10 00:38:54'),
(32, 1, '', 'Para generar informes en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Ve al menú principal y busca la opción de \"Informes\" o \"Reportes\".\n3. Dentro de la sección de informes, selecciona el tipo de informe que deseas generar, como por ejemplo: reporte de ventas, reporte de gastos, etc.\n4. Especifica los parámetros de tu informe, como el rango de fechas, categorías específicas, clientes, etc.\n5. Una vez hayas configurado los parámetros, busca la opción para generar o exportar el informe.\n6. Selecciona el formato en el que deseas guardar el informe, como PDF, Excel, u otro formato compatible.\n7. Haz clic en \"Generar\" o \"Exportar\" para crear tu informe según los parámetros establecidos.\n\nSi tienes problemas para generar un informe específico o necesitas más detalles sobre un tipo de informe en particular, no dudes en contactar al equipo de soporte de Facturarg para obtener ayuda adicional.', 1, 'chat_1754775168781_65uhgh2su', NULL, NULL, NULL, '2025-08-10 00:38:54', '2025-08-10 00:38:54'),
(33, 1, '¿Cómo generar informes?', 'Para generar informes en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Ve al menú principal y busca la opción \"Informes\" o \"Reportes\".\n3. Dentro de la sección de informes, elige el tipo de informe que deseas generar, como por ejemplo \"Ventas\", \"Inventario\", \"Finanzas\", etc.\n4. Selecciona los filtros necesarios para personalizar tu informe, como fechas, productos, clientes, etc.\n5. Haz clic en el botón de \"Generar\" o \"Descargar\" para obtener el informe en el formato deseado, ya sea PDF, Excel, u otro formato compatible.\n\nSi necesitas más detalles sobre cómo generar informes específicos o si tienes alguna dificultad en el proceso, no dudes en consultar la documentación oficial de Facturarg o contactar al equipo de soporte para obtener ayuda adicional.', 0, '1c67869a-1826-4e59-8a7d-c3cad6e0cc7e', NULL, NULL, NULL, '2025-08-10 00:38:57', '2025-08-10 00:38:57'),
(34, 1, '', 'Para generar informes en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Ve al menú principal y busca la opción \"Informes\" o \"Reportes\".\n3. Dentro de la sección de informes, elige el tipo de informe que deseas generar, como por ejemplo \"Ventas\", \"Inventario\", \"Finanzas\", etc.\n4. Selecciona los filtros necesarios para personalizar tu informe, como fechas, productos, clientes, etc.\n5. Haz clic en el botón de \"Generar\" o \"Descargar\" para obtener el informe en el formato deseado, ya sea PDF, Excel, u otro formato compatible.\n\nSi necesitas más detalles sobre cómo generar informes específicos o si tienes alguna dificultad en el proceso, no dudes en consultar la documentación oficial de Facturarg o contactar al equipo de soporte para obtener ayuda adicional.', 1, '1c67869a-1826-4e59-8a7d-c3cad6e0cc7e', NULL, NULL, NULL, '2025-08-10 00:38:57', '2025-08-10 00:38:57'),
(35, 1, 'Hola como estas! necesito aprender a hacer una venta!', '¡Hola! ¡Estoy bien, gracias! Para hacer una venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca la opción de \"Ventas\" o \"Facturación\".\n3. Haz clic en \"Nueva Venta\" o un botón similar para crear una nueva venta.\n4. Selecciona el cliente al que le estás haciendo la venta o agrégalo si es nuevo.\n5. Agrega los productos o servicios que estás vendiendo, especificando la cantidad y precio de cada uno.\n6. Revisa la información de la venta para asegurarte de que esté correcta.\n7. Confirma la venta y genera la factura para el cliente.\n\nRecuerda que para hacer una venta, es importante tener configurados previamente tus productos o servicios en Facturarg. Si necesitas más ayuda detallada, ¡no dudes en preguntar!', 0, 'chat_1754775722551_8scdpgks9', NULL, NULL, NULL, '2025-08-10 00:42:20', '2025-08-10 00:42:20'),
(36, 1, '', '¡Hola! ¡Estoy bien, gracias! Para hacer una venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca la opción de \"Ventas\" o \"Facturación\".\n3. Haz clic en \"Nueva Venta\" o un botón similar para crear una nueva venta.\n4. Selecciona el cliente al que le estás haciendo la venta o agrégalo si es nuevo.\n5. Agrega los productos o servicios que estás vendiendo, especificando la cantidad y precio de cada uno.\n6. Revisa la información de la venta para asegurarte de que esté correcta.\n7. Confirma la venta y genera la factura para el cliente.\n\nRecuerda que para hacer una venta, es importante tener configurados previamente tus productos o servicios en Facturarg. Si necesitas más ayuda detallada, ¡no dudes en preguntar!', 1, 'chat_1754775722551_8scdpgks9', NULL, NULL, NULL, '2025-08-10 00:42:20', '2025-08-10 00:42:20'),
(37, 1, 'Hola como estas necesito que me expliques como hacer el inventario', '¡Hola! Claro, te puedo ayudar con eso. Para hacer el inventario en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, busca la opción de \"Inventario\" o \"Gestión de Inventario\".\n3. Dentro de la sección de inventario, deberías encontrar la opción para \"Agregar nuevo producto\" o \"Añadir inventario\".\n4. Haz clic en esa opción y completa la información requerida para cada producto, como nombre, descripción, cantidad, precio, etc.\n5. Una vez que hayas agregado todos tus productos al inventario, asegúrate de guardar los cambios.\n\nSi necesitas realizar un inventario físico de tus existencias, te recomendaría imprimir una lista de inventario desde el sistema y compararla con tus productos físicos para asegurarte de que todo esté correctamente registrado.\n\nSi tienes alguna pregunta específica o si necesitas más detalles sobre alguna parte del proceso, ¡házmelo saber!', 0, 'chat_1754777193527_ub8ojt5gs', NULL, NULL, NULL, '2025-08-10 01:15:07', '2025-08-10 01:15:07'),
(38, 1, '', '¡Hola! Claro, te puedo ayudar con eso. Para hacer el inventario en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, busca la opción de \"Inventario\" o \"Gestión de Inventario\".\n3. Dentro de la sección de inventario, deberías encontrar la opción para \"Agregar nuevo producto\" o \"Añadir inventario\".\n4. Haz clic en esa opción y completa la información requerida para cada producto, como nombre, descripción, cantidad, precio, etc.\n5. Una vez que hayas agregado todos tus productos al inventario, asegúrate de guardar los cambios.\n\nSi necesitas realizar un inventario físico de tus existencias, te recomendaría imprimir una lista de inventario desde el sistema y compararla con tus productos físicos para asegurarte de que todo esté correctamente registrado.\n\nSi tienes alguna pregunta específica o si necesitas más detalles sobre alguna parte del proceso, ¡házmelo saber!', 1, 'chat_1754777193527_ub8ojt5gs', NULL, NULL, NULL, '2025-08-10 01:15:07', '2025-08-10 01:15:07'),
(39, 1, 'Hola como estas necesito que me expliques como hacer el inventario', '¡Hola! ¡Claro que sí! Para hacer el inventario en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta de Facturarg con tu nombre de usuario y contraseña.\n2. En el menú principal, busca la opción \"Inventario\" o \"Productos\" y haz clic en ella.\n3. Una vez dentro, busca la opción para \"Agregar Producto\" o \"Nuevo Inventario\" y haz clic en ella.\n4. Llena los campos requeridos como nombre del producto, descripción, cantidad en stock, precio, etc.\n5. Guarda la información ingresada y ¡listo! Has añadido un nuevo producto a tu inventario.\n\nRecuerda que es importante mantener tu inventario actualizado para una gestión eficiente de tu negocio. Si necesitas más ayuda o tienes alguna otra pregunta, ¡estaré aquí para ayudarte!', 0, 'chat_1754777193527_ub8ojt5gs', NULL, NULL, NULL, '2025-08-10 01:15:45', '2025-08-10 01:15:45'),
(40, 1, '', '¡Hola! ¡Claro que sí! Para hacer el inventario en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta de Facturarg con tu nombre de usuario y contraseña.\n2. En el menú principal, busca la opción \"Inventario\" o \"Productos\" y haz clic en ella.\n3. Una vez dentro, busca la opción para \"Agregar Producto\" o \"Nuevo Inventario\" y haz clic en ella.\n4. Llena los campos requeridos como nombre del producto, descripción, cantidad en stock, precio, etc.\n5. Guarda la información ingresada y ¡listo! Has añadido un nuevo producto a tu inventario.\n\nRecuerda que es importante mantener tu inventario actualizado para una gestión eficiente de tu negocio. Si necesitas más ayuda o tienes alguna otra pregunta, ¡estaré aquí para ayudarte!', 1, 'chat_1754777193527_ub8ojt5gs', NULL, NULL, NULL, '2025-08-10 01:15:45', '2025-08-10 01:15:45'),
(41, 1, 'Hola como estas necesito aprender a usar el modulo de ventas', '¡Hola! ¡Estoy aquí para ayudarte a aprender a usar el módulo de ventas en Facturarg! Para empezar, aquí tienes los pasos básicos para utilizar este módulo:\n\n1. **Ingresar al Módulo de Ventas**:\n   - Entra a tu cuenta de Facturarg.\n   - En el menú principal, busca y selecciona el módulo de \"Ventas\" o \"Facturación\".\n\n2. **Crear un Nuevo Pedido de Venta**:\n   - Dentro del módulo de Ventas, busca la opción para crear un nuevo pedido de venta.\n   - Completa la información requerida como cliente, productos, cantidades, precios, etc.\n   - Guarda el pedido una vez que hayas ingresado todos los detalles.\n\n3. **Generar una Factura**:\n   - Una vez que el pedido esté listo para facturar, busca la opción para generar la factura correspondiente.\n   - Verifica que la información sea correcta y confirma la creación de la factura.\n\n4. **Registrar Pagos**:\n   - Si el cliente realiza un pago, puedes registrarlo dentro del mismo pedido o factura.\n   - Busca la opción para \"Registrar Pago\" y completa los detalles del pago recibido.\n\n5. **Consultar Reportes**:\n   - Para hacer un seguimiento de tus ventas, puedes acceder a los reportes de ventas dentro del módulo de Ventas.\n   - Busca las opciones de reportes disponibles para analizar tu desempeño.\n\nPara aprender más detalles sobre funciones específicas del módulo de ventas, te recomendaría consultar la documentación oficial de Facturarg. ¿Hay alguna función en particular que te gustaría aprender a utilizar dentro del módulo de ventas? ¡Estoy aquí para ayudarte en lo que necesites!', 0, 'chat_1754879421888_qh5lavzu8', NULL, NULL, NULL, '2025-08-11 05:30:40', '2025-08-11 05:30:40'),
(42, 1, '', '¡Hola! ¡Estoy aquí para ayudarte a aprender a usar el módulo de ventas en Facturarg! Para empezar, aquí tienes los pasos básicos para utilizar este módulo:\n\n1. **Ingresar al Módulo de Ventas**:\n   - Entra a tu cuenta de Facturarg.\n   - En el menú principal, busca y selecciona el módulo de \"Ventas\" o \"Facturación\".\n\n2. **Crear un Nuevo Pedido de Venta**:\n   - Dentro del módulo de Ventas, busca la opción para crear un nuevo pedido de venta.\n   - Completa la información requerida como cliente, productos, cantidades, precios, etc.\n   - Guarda el pedido una vez que hayas ingresado todos los detalles.\n\n3. **Generar una Factura**:\n   - Una vez que el pedido esté listo para facturar, busca la opción para generar la factura correspondiente.\n   - Verifica que la información sea correcta y confirma la creación de la factura.\n\n4. **Registrar Pagos**:\n   - Si el cliente realiza un pago, puedes registrarlo dentro del mismo pedido o factura.\n   - Busca la opción para \"Registrar Pago\" y completa los detalles del pago recibido.\n\n5. **Consultar Reportes**:\n   - Para hacer un seguimiento de tus ventas, puedes acceder a los reportes de ventas dentro del módulo de Ventas.\n   - Busca las opciones de reportes disponibles para analizar tu desempeño.\n\nPara aprender más detalles sobre funciones específicas del módulo de ventas, te recomendaría consultar la documentación oficial de Facturarg. ¿Hay alguna función en particular que te gustaría aprender a utilizar dentro del módulo de ventas? ¡Estoy aquí para ayudarte en lo que necesites!', 1, 'chat_1754879421888_qh5lavzu8', NULL, NULL, NULL, '2025-08-11 05:30:40', '2025-08-11 05:30:40'),
(43, 1, 'hola', '¡Hola! ¿En qué puedo ayudarte hoy? ¡Estoy aquí para responder tus preguntas!', 0, 'chat_1754879421888_qh5lavzu8', NULL, NULL, NULL, '2025-08-11 05:31:43', '2025-08-11 05:31:43'),
(44, 1, '', '¡Hola! ¿En qué puedo ayudarte hoy? ¡Estoy aquí para responder tus preguntas!', 1, 'chat_1754879421888_qh5lavzu8', NULL, NULL, NULL, '2025-08-11 05:31:43', '2025-08-11 05:31:43'),
(45, 1, 's', 'It seems like your message may have been cut off. Could you please provide more details or let me know how I can assist you today?', 0, 'chat_1754879421888_qh5lavzu8', NULL, NULL, NULL, '2025-08-11 05:33:35', '2025-08-11 05:33:35'),
(46, 1, '', 'It seems like your message may have been cut off. Could you please provide more details or let me know how I can assist you today?', 1, 'chat_1754879421888_qh5lavzu8', NULL, NULL, NULL, '2025-08-11 05:33:35', '2025-08-11 05:33:35'),
(47, 1, 'como hago una venta?', 'Para realizar una venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca y haz clic en la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y selecciona la opción para \"Crear nueva venta\" o \"Agregar venta\".\n4. Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Verifica que toda la información esté correcta y completa.\n6. Guarda la venta una vez hayas revisado todo.\n\nSi necesitas más ayuda o tienes alguna pregunta específica durante el proceso de venta, no dudes en consultar la documentación de Facturarg o contactar al equipo de soporte para asistencia adicional. ¡Estoy aquí para ayudarte en lo que necesites!', 0, 'chat_1754965468877_e5hlirqeq', NULL, NULL, NULL, '2025-08-12 05:24:43', '2025-08-12 05:24:43'),
(48, 1, '', 'Para realizar una venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca y haz clic en la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y selecciona la opción para \"Crear nueva venta\" o \"Agregar venta\".\n4. Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Verifica que toda la información esté correcta y completa.\n6. Guarda la venta una vez hayas revisado todo.\n\nSi necesitas más ayuda o tienes alguna pregunta específica durante el proceso de venta, no dudes en consultar la documentación de Facturarg o contactar al equipo de soporte para asistencia adicional. ¡Estoy aquí para ayudarte en lo que necesites!', 1, 'chat_1754965468877_e5hlirqeq', NULL, NULL, NULL, '2025-08-12 05:24:43', '2025-08-12 05:24:43'),
(49, 1, '¿Cómo gestionar el inventario?', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar al Módulo de Inventario**:\n   - Ve a la página principal y busca el módulo de \"Inventario\".\n\n2. **Agregar un Nuevo Producto al Inventario**:\n   - Haz clic en la opción para agregar un nuevo producto.\n   - Completa la información del producto como nombre, descripción, precio, cantidad inicial, etc.\n   - Guarda los cambios.\n\n3. **Actualizar la Cantidad de Producto en Inventario**:\n   - Encuentra la opción para \"Editar\" el producto en el inventario.\n   - Modifica la cantidad disponible del producto según sea necesario.\n   - Guarda los cambios.\n\n4. **Realizar un Seguimiento de las Ventas**:\n   - Revisa regularmente los informes de ventas para mantener un registro de los productos vendidos.\n   - Asegúrate de restar las ventas del inventario para mantener actualizado el stock disponible.\n\n5. **Gestionar Proveedores**:\n   - Mantén actualizada la información de tus proveedores para facilitar la reposición de inventario cuando sea necesario.\n\nSi necesitas más detalles sobre funciones específicas o tienes algún problema mientras gestionas el inventario, no dudes en contactar al equipo de soporte de Facturarg para obtener ayuda adicional. ¡Espero que esta información te haya sido útil!', 0, 'chat_1754965468877_e5hlirqeq', NULL, NULL, NULL, '2025-08-12 05:28:01', '2025-08-12 05:28:01'),
(50, 1, '', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar al Módulo de Inventario**:\n   - Ve a la página principal y busca el módulo de \"Inventario\".\n\n2. **Agregar un Nuevo Producto al Inventario**:\n   - Haz clic en la opción para agregar un nuevo producto.\n   - Completa la información del producto como nombre, descripción, precio, cantidad inicial, etc.\n   - Guarda los cambios.\n\n3. **Actualizar la Cantidad de Producto en Inventario**:\n   - Encuentra la opción para \"Editar\" el producto en el inventario.\n   - Modifica la cantidad disponible del producto según sea necesario.\n   - Guarda los cambios.\n\n4. **Realizar un Seguimiento de las Ventas**:\n   - Revisa regularmente los informes de ventas para mantener un registro de los productos vendidos.\n   - Asegúrate de restar las ventas del inventario para mantener actualizado el stock disponible.\n\n5. **Gestionar Proveedores**:\n   - Mantén actualizada la información de tus proveedores para facilitar la reposición de inventario cuando sea necesario.\n\nSi necesitas más detalles sobre funciones específicas o tienes algún problema mientras gestionas el inventario, no dudes en contactar al equipo de soporte de Facturarg para obtener ayuda adicional. ¡Espero que esta información te haya sido útil!', 1, 'chat_1754965468877_e5hlirqeq', NULL, NULL, NULL, '2025-08-12 05:28:01', '2025-08-12 05:28:01'),
(51, 1, '¿Cómo gestionar el inventario?', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar al Módulo de Inventario**:\n   - Ve a la página principal y busca la opción \"Inventario\" en el menú principal.\n   - Haz clic en \"Inventario\" para acceder al módulo.\n\n2. **Agregar un Nuevo Producto al Inventario**:\n   - Dentro del módulo de Inventario, busca la opción para \"Agregar Producto\" o \"Nuevo Producto\".\n   - Completa la información requerida como nombre del producto, descripción, precio, cantidad inicial, etc.\n   - Guarda los cambios para agregar el producto al inventario.\n\n3. **Gestionar Existencias**:\n   - Para ajustar la cantidad de un producto en existencia, busca la opción para \"Editar Existencias\" o \"Ajustar Stock\".\n   - Ingresa la cantidad actualizada del producto y guarda los cambios.\n\n4. **Realizar Seguimiento de Movimientos**:\n   - En el módulo de Inventario, busca la sección de \"Movimientos\" o \"Registro de Movimientos\".\n   - Aquí podrás ver las entradas y salidas de productos, así como ajustes de inventario realizados.\n\n5. **Generar Reportes de Inventario**:\n   - Para obtener informes detallados sobre el inventario, busca la opción de \"Reportes\" o \"Informes\" dentro del módulo de Inventario.\n   - Selecciona el tipo de informe que necesitas, como existencias actuales, movimientos recientes, etc.\n\nSi necesitas ayuda adicional o tienes alguna pregunta específica sobre la gestión de inventario, no dudes en preguntar. ¡Estoy aquí para ayudarte!', 0, '24ef3e63-6a56-4b98-814f-e9f9cd491a34', NULL, NULL, NULL, '2025-08-12 05:28:08', '2025-08-12 05:28:08'),
(52, 1, '', 'Para gestionar el inventario en Facturarg, sigue estos pasos:\n\n1. **Ingresar al Módulo de Inventario**:\n   - Ve a la página principal y busca la opción \"Inventario\" en el menú principal.\n   - Haz clic en \"Inventario\" para acceder al módulo.\n\n2. **Agregar un Nuevo Producto al Inventario**:\n   - Dentro del módulo de Inventario, busca la opción para \"Agregar Producto\" o \"Nuevo Producto\".\n   - Completa la información requerida como nombre del producto, descripción, precio, cantidad inicial, etc.\n   - Guarda los cambios para agregar el producto al inventario.\n\n3. **Gestionar Existencias**:\n   - Para ajustar la cantidad de un producto en existencia, busca la opción para \"Editar Existencias\" o \"Ajustar Stock\".\n   - Ingresa la cantidad actualizada del producto y guarda los cambios.\n\n4. **Realizar Seguimiento de Movimientos**:\n   - En el módulo de Inventario, busca la sección de \"Movimientos\" o \"Registro de Movimientos\".\n   - Aquí podrás ver las entradas y salidas de productos, así como ajustes de inventario realizados.\n\n5. **Generar Reportes de Inventario**:\n   - Para obtener informes detallados sobre el inventario, busca la opción de \"Reportes\" o \"Informes\" dentro del módulo de Inventario.\n   - Selecciona el tipo de informe que necesitas, como existencias actuales, movimientos recientes, etc.\n\nSi necesitas ayuda adicional o tienes alguna pregunta específica sobre la gestión de inventario, no dudes en preguntar. ¡Estoy aquí para ayudarte!', 1, '24ef3e63-6a56-4b98-814f-e9f9cd491a34', NULL, NULL, NULL, '2025-08-12 05:28:08', '2025-08-12 05:28:08'),
(53, 1, 'Hola necesito aprener a vender', '¡Hola! Para empezar a vender en Facturarg, primero necesitas agregar tus productos o servicios al sistema. Aquí tienes los pasos para hacerlo:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Dirígete al menú de \"Productos\" o \"Servicios\", dependiendo de lo que vendas.\n3. Haz clic en \"Agregar nuevo producto\" o \"Agregar nuevo servicio\".\n4. Completa la información requerida, como nombre, descripción, precio, impuestos, etc.\n5. Guarda los cambios.\n\nUna vez que hayas agregado tus productos o servicios, podrás crear facturas para tus clientes. Aquí tienes un resumen rápido para crear una factura:\n\n1. Ve al menú de \"Facturas\" y selecciona \"Crear nueva factura\".\n2. Selecciona el cliente al que le estás facturando.\n3. Agrega los productos o servicios que estás vendiendo seleccionándolos de tu lista.\n4. Revisa la información y haz clic en \"Guardar\".\n\nRecuerda que es importante configurar correctamente tus impuestos, formas de pago y otros detalles en la configuración de Facturarg para asegurarte de que tus ventas se registren correctamente.\n\nSi necesitas más ayuda con un paso en particular o tienes alguna otra pregunta, ¡estoy aquí para ayudarte!', 0, 'chat_1755642403809_by7v4wfpx', NULL, NULL, NULL, '2025-08-20 01:27:19', '2025-08-20 01:27:19');
INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `response`, `is_bot`, `session_id`, `documentation_id`, `rating`, `feedback`, `created_at`, `updated_at`) VALUES
(54, 1, '', '¡Hola! Para empezar a vender en Facturarg, primero necesitas agregar tus productos o servicios al sistema. Aquí tienes los pasos para hacerlo:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Dirígete al menú de \"Productos\" o \"Servicios\", dependiendo de lo que vendas.\n3. Haz clic en \"Agregar nuevo producto\" o \"Agregar nuevo servicio\".\n4. Completa la información requerida, como nombre, descripción, precio, impuestos, etc.\n5. Guarda los cambios.\n\nUna vez que hayas agregado tus productos o servicios, podrás crear facturas para tus clientes. Aquí tienes un resumen rápido para crear una factura:\n\n1. Ve al menú de \"Facturas\" y selecciona \"Crear nueva factura\".\n2. Selecciona el cliente al que le estás facturando.\n3. Agrega los productos o servicios que estás vendiendo seleccionándolos de tu lista.\n4. Revisa la información y haz clic en \"Guardar\".\n\nRecuerda que es importante configurar correctamente tus impuestos, formas de pago y otros detalles en la configuración de Facturarg para asegurarte de que tus ventas se registren correctamente.\n\nSi necesitas más ayuda con un paso en particular o tienes alguna otra pregunta, ¡estoy aquí para ayudarte!', 1, 'chat_1755642403809_by7v4wfpx', NULL, NULL, NULL, '2025-08-20 01:27:19', '2025-08-20 01:27:19'),
(55, 1, '¿Cómo creo una nueva venta?', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Ve al menú principal y selecciona la opción \"Ventas\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón que dice \"Nueva Venta\" o \"Crear Venta\".\n4. Se abrirá un formulario donde podrás ingresar los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, precio, impuestos, etc.\n5. Completa todos los campos necesarios con la información correcta.\n6. Una vez que hayas ingresado todos los detalles, revisa la información para asegurarte de que sea precisa.\n7. Finalmente, guarda la venta haciendo clic en el botón \"Guardar\" o \"Crear Venta\".\n\n¡Listo! Has creado una nueva venta en Facturarg. Si necesitas más ayuda o tienes alguna pregunta adicional, no dudes en decírmelo.', 0, 'chat_1755914444868_98ewqowwo', NULL, NULL, NULL, '2025-08-23 05:00:51', '2025-08-23 05:00:51'),
(56, 1, '', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. Ve al menú principal y selecciona la opción \"Ventas\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón que dice \"Nueva Venta\" o \"Crear Venta\".\n4. Se abrirá un formulario donde podrás ingresar los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, precio, impuestos, etc.\n5. Completa todos los campos necesarios con la información correcta.\n6. Una vez que hayas ingresado todos los detalles, revisa la información para asegurarte de que sea precisa.\n7. Finalmente, guarda la venta haciendo clic en el botón \"Guardar\" o \"Crear Venta\".\n\n¡Listo! Has creado una nueva venta en Facturarg. Si necesitas más ayuda o tienes alguna pregunta adicional, no dudes en decírmelo.', 1, 'chat_1755914444868_98ewqowwo', NULL, NULL, NULL, '2025-08-23 05:00:51', '2025-08-23 05:00:51'),
(57, 1, '¿Cómo creo una nueva venta?', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón \"Nueva Venta\" o similar.\n4. Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Verifica que toda la información esté correcta.\n6. Finalmente, guarda la venta.\n\nSi tienes alguna dificultad durante este proceso, no dudes en consultar la documentación de Facturarg sobre la creación de ventas o contáctanos para recibir ayuda adicional.', 0, '01d73d81-ec06-4e39-8c09-3b7b2a5ef919', NULL, NULL, NULL, '2025-08-23 05:00:55', '2025-08-23 05:00:55'),
(58, 1, '', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón \"Nueva Venta\" o similar.\n4. Llena los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Verifica que toda la información esté correcta.\n6. Finalmente, guarda la venta.\n\nSi tienes alguna dificultad durante este proceso, no dudes en consultar la documentación de Facturarg sobre la creación de ventas o contáctanos para recibir ayuda adicional.', 1, '01d73d81-ec06-4e39-8c09-3b7b2a5ef919', NULL, NULL, NULL, '2025-08-23 05:00:55', '2025-08-23 05:00:55'),
(59, 1, '¿Cómo creo una nueva venta?', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca el botón o enlace que diga \"Crear nueva venta\" o similar y haz clic en él.\n4. Completa los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Una vez que hayas ingresado todos los detalles, revisa la información para asegurarte de que sea correcta.\n6. Finalmente, guarda la venta haciendo clic en el botón \"Guardar\" o \"Finalizar\".\n\nSi encuentras algún problema durante este proceso, asegúrate de revisar que todos los campos obligatorios estén completos y que la información ingresada sea válida. Si necesitas más ayuda o detalles específicos, no dudes en contactar al equipo de soporte de Facturarg.', 0, '01d73d81-ec06-4e39-8c09-3b7b2a5ef919', NULL, NULL, NULL, '2025-08-23 05:00:59', '2025-08-23 05:00:59'),
(60, 1, '', 'Para crear una nueva venta en Facturarg, sigue estos pasos:\n\n1. Inicia sesión en tu cuenta de Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca el botón o enlace que diga \"Crear nueva venta\" o similar y haz clic en él.\n4. Completa los detalles de la venta, como el cliente, los productos o servicios vendidos, la cantidad, el precio, etc.\n5. Una vez que hayas ingresado todos los detalles, revisa la información para asegurarte de que sea correcta.\n6. Finalmente, guarda la venta haciendo clic en el botón \"Guardar\" o \"Finalizar\".\n\nSi encuentras algún problema durante este proceso, asegúrate de revisar que todos los campos obligatorios estén completos y que la información ingresada sea válida. Si necesitas más ayuda o detalles específicos, no dudes en contactar al equipo de soporte de Facturarg.', 1, '01d73d81-ec06-4e39-8c09-3b7b2a5ef919', NULL, NULL, NULL, '2025-08-23 05:00:59', '2025-08-23 05:00:59'),
(61, 1, 'I want to learn about Sales Management. What can you tell me about this module?', 'Sales Management is a crucial module in Facturarg that helps you effectively manage your sales processes. Here\'s an overview of what you can expect from the Sales Management module:\n\n1. **Customer Management**: You can store detailed information about your customers, including contact details, billing addresses, payment terms, and purchase history.\n\n2. **Product Catalog**: Maintain a comprehensive catalog of your products or services, including pricing, descriptions, and stock levels.\n\n3. **Sales Orders**: Create and manage sales orders for your customers, track order status, and generate invoices directly from the system.\n\n4. **Quotations**: Prepare and send professional quotations to potential customers, convert them into sales orders upon approval, and track their progress.\n\n5. **Sales Reports**: Access detailed reports on sales performance, revenue, customer trends, and more to make informed business decisions.\n\nTo get started with Sales Management in Facturarg, you can follow these steps:\n- Log in to your Facturarg account.\n- Navigate to the Sales Management module from the main dashboard.\n- Explore options like Customer Management, Product Catalog, Sales Orders, and Reports to familiarize yourself with the features.\n\nIf you need more detailed instructions on specific tasks within the Sales Management module, feel free to ask!', 0, 'e51ed3ad-7431-45bd-a803-590ffa7119f3', NULL, NULL, NULL, '2025-08-29 22:43:08', '2025-08-29 22:43:08'),
(62, 1, '', 'Sales Management is a crucial module in Facturarg that helps you effectively manage your sales processes. Here\'s an overview of what you can expect from the Sales Management module:\n\n1. **Customer Management**: You can store detailed information about your customers, including contact details, billing addresses, payment terms, and purchase history.\n\n2. **Product Catalog**: Maintain a comprehensive catalog of your products or services, including pricing, descriptions, and stock levels.\n\n3. **Sales Orders**: Create and manage sales orders for your customers, track order status, and generate invoices directly from the system.\n\n4. **Quotations**: Prepare and send professional quotations to potential customers, convert them into sales orders upon approval, and track their progress.\n\n5. **Sales Reports**: Access detailed reports on sales performance, revenue, customer trends, and more to make informed business decisions.\n\nTo get started with Sales Management in Facturarg, you can follow these steps:\n- Log in to your Facturarg account.\n- Navigate to the Sales Management module from the main dashboard.\n- Explore options like Customer Management, Product Catalog, Sales Orders, and Reports to familiarize yourself with the features.\n\nIf you need more detailed instructions on specific tasks within the Sales Management module, feel free to ask!', 1, 'e51ed3ad-7431-45bd-a803-590ffa7119f3', NULL, NULL, NULL, '2025-08-29 22:43:08', '2025-08-29 22:43:08'),
(63, 1, 'como hhago una venta', '¡Claro, te puedo ayudar con eso! Para hacer una venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón que dice \"Crear nueva venta\" o similar.\n4. Llena los detalles de la venta como cliente, productos/servicios vendidos, cantidad, precio, impuestos, etc.\n5. Una vez completados todos los detalles, confirma la venta y genera la factura correspondiente.\n\nRecuerda que es importante tener configurados previamente tus productos, clientes y ajustes de impuestos para poder realizar una venta correctamente. Si necesitas más detalles o ayuda con algún paso en particular, házmelo saber. ¡Estoy aquí para ayudarte!', 0, 'chat_1756496562520_zb2caf1ld', NULL, NULL, NULL, '2025-08-29 22:43:47', '2025-08-29 22:43:47'),
(64, 1, '', '¡Claro, te puedo ayudar con eso! Para hacer una venta en Facturarg, sigue estos pasos:\n\n1. Ingresa a tu cuenta en Facturarg.\n2. En el menú principal, busca y selecciona la opción \"Ventas\" o \"Facturación\".\n3. Dentro de la sección de ventas, busca y haz clic en el botón que dice \"Crear nueva venta\" o similar.\n4. Llena los detalles de la venta como cliente, productos/servicios vendidos, cantidad, precio, impuestos, etc.\n5. Una vez completados todos los detalles, confirma la venta y genera la factura correspondiente.\n\nRecuerda que es importante tener configurados previamente tus productos, clientes y ajustes de impuestos para poder realizar una venta correctamente. Si necesitas más detalles o ayuda con algún paso en particular, házmelo saber. ¡Estoy aquí para ayudarte!', 1, 'chat_1756496562520_zb2caf1ld', NULL, NULL, NULL, '2025-08-29 22:43:47', '2025-08-29 22:43:47'),
(65, 1, 'hola como estas', '¡Hola! ¡Estoy aquí para ayudarte! ¿En qué puedo asistirte hoy con Facturarg?', 0, 'chat_1757326727739_fb1g53hs0', NULL, NULL, NULL, '2025-09-08 13:18:56', '2025-09-08 13:18:56'),
(66, 1, '', '¡Hola! ¡Estoy aquí para ayudarte! ¿En qué puedo asistirte hoy con Facturarg?', 1, 'chat_1757326727739_fb1g53hs0', NULL, NULL, NULL, '2025-09-08 13:18:56', '2025-09-08 13:18:56'),
(67, 1, 'necesito aprender a hacer una venta', '¡Claro! Para hacer una venta en Facturarg, sigue estos pasos:\n\n1. **Iniciar sesión**: Ingresa a tu cuenta de Facturarg con tus credenciales.\n2. **Ir al Módulo de Ventas**: En el menú principal, busca y haz clic en \"Ventas\" o \"Facturación\".\n3. **Crear una Nueva Venta**:\n   - Haz clic en el botón \"Nueva Venta\" o \"Crear Venta\".\n   - Ingresa los detalles del cliente (si es un cliente nuevo, puedes crearlo en este paso).\n   - Agrega los productos o servicios que el cliente está comprando, especificando cantidad y precio.\n   - Revisa que toda la información esté correcta.\n4. **Finalizar la Venta**:\n   - Guarda la venta.\n   - Dependiendo de tu configuración, puedes imprimir la factura o enviarla por correo electrónico al cliente.\n\n¡Listo! Has realizado una venta en Facturarg. ¿Necesitas más ayuda con algún paso en particular?', 0, 'chat_1757326727739_fb1g53hs0', NULL, NULL, NULL, '2025-09-08 13:19:16', '2025-09-08 13:19:16'),
(68, 1, '', '¡Claro! Para hacer una venta en Facturarg, sigue estos pasos:\n\n1. **Iniciar sesión**: Ingresa a tu cuenta de Facturarg con tus credenciales.\n2. **Ir al Módulo de Ventas**: En el menú principal, busca y haz clic en \"Ventas\" o \"Facturación\".\n3. **Crear una Nueva Venta**:\n   - Haz clic en el botón \"Nueva Venta\" o \"Crear Venta\".\n   - Ingresa los detalles del cliente (si es un cliente nuevo, puedes crearlo en este paso).\n   - Agrega los productos o servicios que el cliente está comprando, especificando cantidad y precio.\n   - Revisa que toda la información esté correcta.\n4. **Finalizar la Venta**:\n   - Guarda la venta.\n   - Dependiendo de tu configuración, puedes imprimir la factura o enviarla por correo electrónico al cliente.\n\n¡Listo! Has realizado una venta en Facturarg. ¿Necesitas más ayuda con algún paso en particular?', 1, 'chat_1757326727739_fb1g53hs0', NULL, NULL, NULL, '2025-09-08 13:19:16', '2025-09-08 13:19:16'),
(69, 1, 'I want to learn about Sales Management. What can you tell me about this module?', 'Sales Management is a crucial module in Facturarg that helps businesses effectively manage their sales processes. Here\'s an overview of what you can expect from the Sales Management module:\n\n1. **Managing Customers**: You can easily store and access customer information, including contact details, purchase history, and preferences.\n\n2. **Creating Quotes and Invoices**: Generate professional-looking quotes and invoices for your customers directly from the system.\n\n3. **Tracking Sales Orders**: Keep track of all your sales orders, monitor their status, and manage the fulfillment process efficiently.\n\n4. **Inventory Management**: Monitor your inventory levels, receive alerts for low stock, and ensure you have the right products available to fulfill orders.\n\n5. **Sales Reports and Analytics**: Access detailed reports and analytics to track sales performance, identify trends, and make data-driven decisions.\n\nTo get started with Sales Management in Facturarg, you can follow these steps:\n- Log in to your Facturarg account.\n- Navigate to the Sales Management module, usually located in the main menu or dashboard.\n- Explore the different features such as customer management, creating quotes/invoices, managing sales orders, and accessing reports.\n\nIf you need more detailed information or step-by-step guides on specific tasks within the Sales Management module, feel free to reach out to our support team or check the documentation center for more resources.', 0, '290fcaec-d5bf-4bb0-9a1c-67bd14bd5e59', NULL, NULL, NULL, '2025-09-08 13:19:25', '2025-09-08 13:19:25'),
(70, 1, '', 'Sales Management is a crucial module in Facturarg that helps businesses effectively manage their sales processes. Here\'s an overview of what you can expect from the Sales Management module:\n\n1. **Managing Customers**: You can easily store and access customer information, including contact details, purchase history, and preferences.\n\n2. **Creating Quotes and Invoices**: Generate professional-looking quotes and invoices for your customers directly from the system.\n\n3. **Tracking Sales Orders**: Keep track of all your sales orders, monitor their status, and manage the fulfillment process efficiently.\n\n4. **Inventory Management**: Monitor your inventory levels, receive alerts for low stock, and ensure you have the right products available to fulfill orders.\n\n5. **Sales Reports and Analytics**: Access detailed reports and analytics to track sales performance, identify trends, and make data-driven decisions.\n\nTo get started with Sales Management in Facturarg, you can follow these steps:\n- Log in to your Facturarg account.\n- Navigate to the Sales Management module, usually located in the main menu or dashboard.\n- Explore the different features such as customer management, creating quotes/invoices, managing sales orders, and accessing reports.\n\nIf you need more detailed information or step-by-step guides on specific tasks within the Sales Management module, feel free to reach out to our support team or check the documentation center for more resources.', 1, '290fcaec-d5bf-4bb0-9a1c-67bd14bd5e59', NULL, NULL, NULL, '2025-09-08 13:19:25', '2025-09-08 13:19:25'),
(71, 1, 'I want to learn about Getting Started. What can you tell me about this module?', 'The \"Getting Started\" module in our system is designed to help you familiarize yourself with basic functions and navigation. Here are the key steps to get started:\r\n\r\n1. **Log in to your account**: Go to the bottom of the ecommerce page and click on \"Log in.\" Enter your username and password.\r\n   \n2. **Complete your profile**: After logging in, make sure to update your profile with accurate information.\r\n   \n3. **Explore the dashboard**: Take some time to navigate through the dashboard to understand its main functions.\r\n   \n4. **Set up your preferences**: Visit the settings section to configure your preferences according to your needs.\r\n\r\nIf you have any specific questions or need further assistance with any of these steps, feel free to ask!', 0, 'bcd060b1-8314-4660-b7cd-05972abd31d7', 2, NULL, NULL, '2025-09-12 03:57:34', '2025-09-12 03:57:34'),
(72, 1, '', 'The \"Getting Started\" module in our system is designed to help you familiarize yourself with basic functions and navigation. Here are the key steps to get started:\r\n\r\n1. **Log in to your account**: Go to the bottom of the ecommerce page and click on \"Log in.\" Enter your username and password.\r\n   \n2. **Complete your profile**: After logging in, make sure to update your profile with accurate information.\r\n   \n3. **Explore the dashboard**: Take some time to navigate through the dashboard to understand its main functions.\r\n   \n4. **Set up your preferences**: Visit the settings section to configure your preferences according to your needs.\r\n\r\nIf you have any specific questions or need further assistance with any of these steps, feel free to ask!', 1, 'bcd060b1-8314-4660-b7cd-05972abd31d7', 2, NULL, NULL, '2025-09-12 03:57:34', '2025-09-12 03:57:34');

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
-- Estructura de tabla para la tabla `color`
--

CREATE TABLE `color` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `hexadecimal` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `color`
--

INSERT INTO `color` (`id`, `name`, `status`, `hexadecimal`, `registration_date`) VALUES
(1, 'Negro', 1, '#000000', '2025-08-29 19:26:10'),
(2, 'Blanco', 1, '#ffffff', '2025-08-29 19:26:17');

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

--
-- Volcado de datos para la tabla `corte_cajero_dia`
--

INSERT INTO `corte_cajero_dia` (`idcortecaja`, `apertura_id`, `total_acomulado`, `seriefolio`, `numfolio`, `fecha`, `hora`) VALUES
(1, 1, 0.00, '20258181', '1', '2025-08-18', '11:48:25'),
(2, 2, 0.00, '20258292', '1', '2025-08-29', '13:24:47'),
(3, 3, 0.00, '2025983', '1', '2025-09-08', '04:27:39'),
(4, 4, 0.00, '20259104', '1', '2025-09-10', '07:35:04'),
(5, 5, 0.00, '20259245', '1', '2025-09-24', '12:05:03'),
(6, 6, 0.00, '20259266', '1', '2025-09-26', '17:11:59');

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
  `tipo_producto_id` varchar(255) DEFAULT NULL,
  `producto_variacion_variante_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_venta_temp`
--

INSERT INTO `detalle_venta_temp` (`iddetalletemp`, `id_user`, `idarticulo`, `codproducto`, `nombre`, `cantidad`, `precio`, `descuento`, `iva`, `price_list_id`, `original_price`, `effective_price`, `price_list_name`, `sales_price_list_id`, `typo_producto_id`, `tipo_producto_id`, `producto_variacion_variante_id`) VALUES
(9, 1, 8, '1234567891239', 'Sabana producto personalizado 2', 1.000, 30000.00, 0.00, 0.00, NULL, NULL, NULL, NULL, 1, NULL, '2', '3');

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
(4, '2020_06_09_192819_create_roles_table', 1),
(5, '2020_06_09_193225_create_role_user_table', 1),
(6, '2020_06_09_195649_create_permissions_table', 1),
(7, '2020_06_09_195931_create_permission_role_table', 1),
(8, '2020_06_17_215853_create_categories_table', 1),
(9, '2020_06_23_165625_create_productos_table', 1),
(10, '2020_06_25_020547_create_proveedores_table', 1),
(11, '2020_06_27_040026_create_ingresos_table', 1),
(12, '2020_06_27_042214_create_detalle_ingresos_table', 1),
(13, '2020_10_17_213834_create_clientes_table', 1),
(14, '2020_10_17_214026_create_aperturacajas_table', 1),
(15, '2020_10_17_214152_create_corte_cajero_dia_table', 1),
(16, '2020_10_17_214455_create_ventas_table', 1),
(17, '2020_10_17_214637_create_detalle_ventas_table', 1),
(18, '2020_10_17_215150_create_devolucion_ventas_table', 1),
(19, '2020_10_17_215305_create_detalle_devolucion_ventas_table', 1),
(20, '2020_10_17_220023_create_detalle_entrada_temp_table', 1),
(21, '2020_10_17_220249_create_detalle_venta_temp_table', 1),
(22, '2020_10_24_023819_add_corte_cajero_dia', 1),
(23, '2020_10_25_025021_add_serie_corte_cajero_dia', 1),
(24, '2020_11_08_045118_create_numero_corte_por_cajero_table', 1),
(25, '2020_11_16_234123_add_fecha_cierre', 1),
(26, '2020_12_25_032738_add_subtotal_detalle_ingresos', 1),
(27, '2021_08_25_052528_create_configuracion_table', 1),
(28, '2025_06_15_175545_create_landlord_tenants_table', 1),
(29, '2025_06_16_163440_add_user_fields_to_tenants_table', 1),
(30, '2025_06_21_000001_create_aperturacajavirtual_table', 1),
(31, '2025_06_21_000002_create_banner_ecommerce_table', 1),
(32, '2025_06_21_000003_create_capturarinventario_table', 1),
(33, '2025_06_21_000004_create_carrito_cotizacion_temp_table', 1),
(34, '2025_06_21_000005_create_color_table', 1),
(35, '2025_06_21_000006_create_cotizaciones_table', 1),
(36, '2025_06_21_000007_create_detalle_cotizacion_table', 1),
(37, '2025_06_21_000008_create_inventario_table', 1),
(38, '2025_06_21_000009_create_order_detail_ecommerce_table', 1),
(39, '2025_06_21_000010_create_order_ecommerce_table', 1),
(40, '2025_06_21_000011_create_payment_ecommerce_table', 1),
(41, '2025_06_21_000012_create_payment_methods_table', 1),
(42, '2025_06_21_000013_create_producto_integracion_variante_table', 1),
(43, '2025_06_21_000014_create_producto_variacion_variante_table', 1),
(44, '2025_06_21_183503_create_variantes_para_variaciones_table', 1),
(45, '2025_06_21_184311_add_missing_to_detale_ventas_table', 1),
(46, '2025_06_21_184850_add_missing_to_productos_table', 1),
(47, '2025_07_01_045213_create_variaciones_table', 1),
(48, '2025_07_01_045619_create_tipo_producto_table', 1),
(49, '2025_07_01_050602_add_fields_to_carrito_cotizacion_temp_table', 1),
(50, '2025_07_01_052249_add_missing_fields_to_clientes_table', 1),
(51, '2025_07_01_053650_add_missing_fields_to_categorias_table', 1),
(52, '2014_10_12_200000_add_two_factor_columns_to_users_table', 2),
(53, '2024_01_01_000001_create_documentation_table', 2),
(54, '2024_01_01_000002_create_chat_messages_table', 2),
(55, '2025_08_02_000000_add_bulk_upload_fields_to_productos_table', 2),
(56, '2025_08_09_000000_create_price_lists_table', 3),
(57, '2025_08_09_000001_create_price_list_items_table', 3),
(58, '2025_08_10_000000_add_context_to_existing_price_lists', 3),
(59, '2025_08_10_000001_add_price_list_to_sales_temp', 3),
(60, '2025_08_10_000002_add_price_list_to_ventas_table', 3),
(61, '2025_08_10_000003_add_price_list_to_detalle_ventas_table', 3),
(62, '2025_08_18_113239_create_add_detalle_venta_temp_procedure', 3),
(63, '2025_08_23_070012_add_efectivo_to_table_ventas', 3),
(64, '2025_08_23_073636_create_trigger', 3),
(65, '2025_08_25_122352_add_sales_price_list_id_to_detalle_venta_temp_table', 3),
(66, '2025_08_25_122352_add_sales_price_list_id_to_producto_table', 3),
(67, '2019_12_14_000001_create_personal_access_tokens_table', 4),
(68, '2025_08_25_122352_add_typo_producto_id_to_detalle_venta_temp_table', 5),
(69, '2025_08_25_122378_add_tipo_producto_id_to_detalle_venta_temp_table', 6),
(70, '2025_08_25_122378_add_producto_variacion_variante_id_to_detalle_venta_temp_table', 7);

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
(1, 'listar el menu principal', 'admin.index', 'un administrador puede ver el menu', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(2, 'listar el menu de almacen', 'almacen.index', 'Un usuario puede ver el menu de almacen', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(3, 'listar el menu de compras', 'compras.index', 'Un usuario puede ver el menu de compras', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(4, 'listar el menu de ventas', 'ventas.index', 'Un usuario puede ver el menu de ventas', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(5, 'listar el menu de caja', 'caja.index', 'Un usuario puede ver el menu de caja', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(6, 'listar el menu de devoluciones', 'devolucion.index', 'Un usuario puede ver el menu de devoluciones', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(7, 'listar la seccion de roles', 'admin_role.index', 'Un usuario puede ver la seccion de roles', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(8, 'listarla seccion de usuarios', 'admin_user.index', 'Un usuario puede ver la seccion de usuarios', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(9, 'listar la seccion de apertura de caja', 'caja_apertura.index', 'Un usuario puede aperturar una caja para vender', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(10, 'listar la seccion de corte de caja', 'caja_corte.index', 'Un usuario puede realizar el corte de caja', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(11, 'listar la seccion de corte parcial de caja', 'caja_parcial.index', 'Un usuario puede realizar el corte de caja parcial', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(12, 'listar la seccion de articulos', 'almacen_articulo.index', 'Un usuario puede realizar la alta de productos', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(13, 'listar la seccion de categorias', 'almacen_categoria.index', 'Un usuario puede realizar la alta de categorias', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(14, 'listar la seccion de entrada de mercancia', 'compras_entrada.index', 'Un usuario puede realizar la entrada de productos', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(15, 'listar la seccion de proveedores', 'compras_proveedor.index', 'Un usuario puede realizar el registro de un proveedor', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(16, 'listar la seccion de ventas', 'ventas_venta.index', 'Un usuario puede realizar las ventas', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(17, 'listar la seccion de clientes', 'ventas_cliente.index', 'Un usuario puede realizar el registro de los clientes', '2025-07-29 19:50:57', '2025-07-29 19:50:57'),
(18, 'listar la seccion de devoluciones', 'devolucion_producto.index', 'Un usuario puede realizar la devolucion de productos', '2025-07-29 19:50:57', '2025-07-29 19:50:57');

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

--
-- Volcado de datos para la tabla `price_lists`
--

INSERT INTO `price_lists` (`id`, `name`, `type`, `context`, `value_type`, `percentage`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 'lista 25% descuento', 'general_percentage', 'sales', 'discount', 25.00, 'lista con el 25% de descuento', 1, '2025-08-29 21:59:50', '2025-08-29 21:59:50'),
(2, '25% +', 'general_percentage', 'purchase', 'increase', 25.00, 'lista 25+', 1, '2025-09-26 22:25:22', '2025-09-26 22:25:22');

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

--
-- Volcado de datos para la tabla `price_list_items`
--

INSERT INTO `price_list_items` (`id`, `price_list_id`, `product_id`, `amount_price`, `value_type`, `percentage`, `purchase_price`, `purchase_value_type`, `purchase_percentage`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NULL, 'discount', 0.00, NULL, 'discount', 0.00, '2025-08-29 22:02:10', '2025-08-29 22:02:10'),
(2, 1, 1, NULL, 'discount', 0.00, NULL, 'discount', 0.00, '2025-08-29 22:02:10', '2025-08-29 22:02:10'),
(3, 1, 7, NULL, 'discount', 0.00, NULL, 'discount', 0.00, '2025-09-08 13:32:38', '2025-09-08 13:32:38'),
(4, 1, 8, NULL, 'discount', 0.00, NULL, 'discount', 0.00, '2025-09-08 13:48:26', '2025-09-08 13:48:26'),
(5, 2, 9, NULL, 'increase', 0.00, NULL, 'increase', 0.00, '2025-09-26 22:26:06', '2025-09-26 22:26:06');

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
(2, 1, '1234567891235', 'sabana esencial alcoyana', 3.000, 0.00, 0.00, 'sabana', 'SABANA1.jpg', 'Activo', 0.00, 0.00, 2, NULL, NULL),
(3, 2, 'LAP001', 'Laptop HP Pavilion', 10.000, 800.00, 1200.00, 'Laptop HP Pavilion con procesador Intel i5, 8GB RAM, 256GB SSD', '', 'Activo', 15.00, 21.00, 3, 'Estante A-1', 'HP'),
(4, 3, 'MOU002', 'Mouse Inalámbrico Logitech', 50.000, 15.00, 25.00, 'Mouse inalámbrico Logitech con sensor óptico de alta precisión', '', 'Activo', 10.00, 21.00, 4, 'Estante B-3', 'Logitech'),
(5, 3, 'TEC003', 'Teclado Mecánico RGB', 25.000, 45.00, 75.00, 'Teclado mecánico con switches Cherry MX y retroiluminación RGB', '', 'Activo', 5.00, 21.00, 4, 'Estante B-4', 'Corsair'),
(7, 1, '1234567891237', 'Sabana Producto personalizable', 3.000, 0.00, 0.00, 'Sabana Producto personalizable', 'colchon categoria (5).png', 'Activo', 0.00, 0.00, 2, NULL, NULL),
(8, 1, '1234567891239', 'Sabana producto personalizado 2', 5.000, 0.00, 0.00, '1234567891237', 'colchon categoria (1).png', 'Activo', 0.00, 0.00, 2, NULL, NULL),
(9, 2, '2245678939123', 'producto prueba', 0.000, 0.00, 0.00, 'prueba', 'productogeneral.png', 'Activo', 0.00, 0.00, 2, NULL, NULL);

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

--
-- Volcado de datos para la tabla `producto_integracion_variante`
--

INSERT INTO `producto_integracion_variante` (`id`, `producto_id`, `variacion_id`, `variante_id`, `descripcion`, `status`, `activo`, `registration_date`) VALUES
(1, 2, 1, 1, NULL, 'P', 1, '2025-08-29 19:03:56'),
(2, 2, 1, 2, NULL, 'P', 1, '2025-08-29 19:04:02'),
(3, 2, 1, 3, NULL, 'P', 1, '2025-08-29 19:26:46'),
(4, 7, 1, 1, NULL, 'P', 1, '2025-09-08 10:30:43'),
(5, 7, 1, 2, NULL, 'P', 1, '2025-09-08 10:30:43'),
(6, 8, 1, 1, NULL, 'P', 1, '2025-09-08 10:45:33'),
(7, 8, 1, 2, NULL, 'P', 1, '2025-09-08 10:45:33'),
(8, 9, 1, 1, NULL, 'P', 1, '2025-09-26 19:21:41'),
(9, 9, 1, 2, NULL, 'P', 1, '2025-09-26 19:21:41'),
(10, 9, 1, 3, NULL, 'P', 1, '2025-09-26 19:21:46');

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

--
-- Volcado de datos para la tabla `producto_variacion_variante`
--

INSERT INTO `producto_variacion_variante` (`id`, `color_id`, `product_integration_id`, `price`, `name_image`, `path_image`, `stock`, `active`, `registration_date`, `show_ecommerce`, `pcompra`) VALUES
(1, 1, 1, 20000.00, '164413-1200-auto.png', 'imagenes/articulo_variante/product-2', 2.00, 1, '2025-08-29 19:27:21', 1, 10000.00),
(2, 1, 4, 30000.00, 'WhatsApp Image 2025-09-04 at 2.17.53 PM.jpeg', 'imagenes/articulo_variante/product-7', 3.00, 1, '2025-09-08 10:31:31', 1, 20000.00),
(3, 1, 6, 30000.00, 'Copia de POST INSTAGRAM FACTURARG  (Post de Instagram (45)) (1).png', 'imagenes/articulo_variante/product-8', 2.00, 1, '2025-09-08 10:46:34', 0, 20000.00),
(4, 2, 7, 40000.00, 'PHOTO-2025-03-19-08-27-25 (3).jpg', 'imagenes/articulo_variante/product-8', 3.00, 1, '2025-09-08 10:46:52', 1, 30000.00);

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
(1, 'Admin', 'Admin', 'Administrador', 'yes', '2025-07-29 19:50:57', '2025-07-29 19:50:57');

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
(1, 1, 1, '2025-07-29 19:50:57', '2025-07-29 19:50:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `database` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, 'Producto personalizado', '', 1, '2024-12-23 15:34:45'),
(3, 'Laptop', 'Auto-created from bulk import', 1, '2025-08-12 06:01:09'),
(4, 'Accesorio', 'Auto-created from bulk import', 1, '2025-08-12 06:01:09');

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
(1, 'admin', 'abcd@gmail.com', '2025-07-08 16:57:13', '$2y$10$EXYkqm/mtcQHIwGvZUZTMe3TtaklMYhwfQzLtCcZd6tSl8urOOeIG', NULL, NULL, NULL, 1, NULL, '2025-07-29 19:50:57', '2025-07-29 19:50:57');

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

--
-- Volcado de datos para la tabla `variaciones`
--

INSERT INTO `variaciones` (`id`, `name`, `option_type`, `status`, `registration_date`) VALUES
(1, 'TAMAÑO', 'Boton', 1, '2025-08-29 19:03:43');

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

--
-- Volcado de datos para la tabla `variantes_para_variaciones`
--

INSERT INTO `variantes_para_variaciones` (`id`, `variacion_id`, `name`, `option_type`, `descripcion`, `status`, `registration_date`) VALUES
(1, 1, 'king', NULL, NULL, 1, '2025-08-29 19:03:43'),
(2, 1, 'queen', NULL, NULL, 1, '2025-08-29 19:03:43'),
(3, 1, 'twin', NULL, NULL, 1, '2025-08-29 19:03:43');

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
  ADD KEY `chat_messages_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcliente`);

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
  MODIFY `idapertura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `aperturacajavirtual`
--
ALTER TABLE `aperturacajavirtual`
  MODIFY `caja_virtual_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `banner_ecommerce`
--
ALTER TABLE `banner_ecommerce`
  MODIFY `banner_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `idcategoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  MODIFY `idcortecaja` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `iddetalletemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  MODIFY `iddevolucion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentation`
--
ALTER TABLE `documentation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `price_list_items`
--
ALTER TABLE `price_list_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `idarticulo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT de la tabla `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `variaciones`
--
ALTER TABLE `variaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `chat_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
