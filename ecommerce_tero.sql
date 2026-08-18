-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-06-2025 a las 03:50:07
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
-- Base de datos: `ecommerce_tero`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_entrada_tmp` (`id_user_tmp` INT, `idarticulo_tmp` INT, `codigo_tmp` VARCHAR(50), `nombre_tmp` VARCHAR(500), `cantidad_tmp` DECIMAL(11,3), `pcompra_tmp` DECIMAL(11,2), `pventa_tmp` DECIMAL(11,2), `tipoProductoId_tmp` INT, `producto_variacion_variante_id_tmp` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_venta_temp` (`id_user_tmp` INT, `idarticulo_tmp` INT, `codigo_tmp` VARCHAR(50), `nombre_tmp` VARCHAR(500), `cantidad_tmp` DECIMAL(11,3), `precio_tmp` DECIMAL(11,2), `descuento_tmp` DECIMAL(11,2), `iva_tmp` DECIMAL(11,2), `tipoProductoId_tmp` INT, `producto_variacion_variante_id_tmp` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_detalle_venta_temp` (`id_detalle_articulo_temp` INT, `id_user_temp` INT, `id_articulo` INT, `tipo_producto_id_temp` INT, `producto_variacion_variante_id_temp` INT)   BEGIN
     	DELETE FROM detalle_venta_temp WHERE id_user=id_user_temp AND idarticulo=id_articulo and tipo_producto_id = tipo_producto_id_temp and producto_variacion_variante_id = producto_variacion_variante_id_temp;
        SELECT *, SUM(tmp.cantidad) AS total_articulos FROM detalle_venta_temp tmp WHERE tmp.id_user=id_user_temp GROUP BY tmp.idarticulo, tmp.iddetalletemp ORDER BY tmp.iddetalletemp DESC;
        
     END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_prod_entrada_tmp` (`idart_tmp` INT, `id_user_tmp` INT, `id_articulo` INT, `tipo_producto_id_temp` INT, `producto_variacion_variante_id_temp` INT)   BEGIN
DELETE FROM detalle_entrada_temp WHERE id_user=id_user_tmp AND idarticulo=id_articulo AND tipo_producto_id = tipo_producto_id_temp AND producto_variacion_variante_id = producto_variacion_variante_id_temp;
SELECT *, SUM(tmp.cantidad) AS total_articulos FROM detalle_entrada_temp tmp WHERE tmp.id_user=id_user_tmp GROUP BY tmp.idarticulo, tmp.identradatemp ORDER BY tmp.identradatemp DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `processStockAfterOrderPaid` (`order_id` INT)   BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_producto_variacion_variante_id INT DEFAULT NULL;
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aperturacajas`
--

CREATE TABLE `aperturacajas` (
  `idapertura` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_inicial` decimal(11,2) NOT NULL,
  `cantidad_final` decimal(11,2) NOT NULL,
  `estatus` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `fecha_hora_cierre` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `aperturacajas`
--

INSERT INTO `aperturacajas` (`idapertura`, `user_id`, `cantidad_inicial`, `cantidad_final`, `estatus`, `fecha_hora`, `fecha_hora_cierre`) VALUES
(1, 1, 0.00, 0.00, 'Abierta', '2025-06-12 20:47:29', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aperturacajavirtual`
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `aperturacajavirtual`
--

INSERT INTO `aperturacajavirtual` (`caja_virtual_id`, `initial_amount`, `final_amount`, `total`, `start_date_time`, `end_date_time`, `status`, `status_box`) VALUES
(1, 0.00, 0.00, 0.00, '2025-06-06 00:00:00', '2025-06-11 20:46:44', 1, 'virtual_box_closed'),
(2, 0.00, 0.00, 0.00, '2025-06-11 00:00:00', '2025-06-11 20:46:44', 1, 'virtual_box_closed');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `banner_ecommerce`
--

CREATE TABLE `banner_ecommerce` (
  `banner_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_image` varchar(255) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `banner_date` timestamp NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1,
  `name_image_movil` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `banner_ecommerce`
--

INSERT INTO `banner_ecommerce` (`banner_id`, `name`, `name_image`, `description`, `banner_date`, `status`, `name_image_movil`) VALUES
(1, 'banner_1', 'CINTIA HOLZ (2).png', NULL, '2025-06-11 03:15:41', 1, 'Bienvenido.png'),
(2, 'Banner_2', 'CINTIA HOLZ (3).png', NULL, '2025-06-11 05:31:12', 1, 'Bienvenido (1).png'),
(3, 'Banner_3', 'CINTIA HOLZ (4).png', NULL, '2025-06-11 05:33:23', 1, 'Bienvenido (2).png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capturarinventario`
--

CREATE TABLE `capturarinventario` (
  `idcaptura` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_cotizacion_temp`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `idcategoria` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `name_imagen` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`idcategoria`, `nombre`, `descripcion`, `estatus`, `name_imagen`) VALUES
(1, 'Sabanas', 'Sabanas', 1, 'SABANA1.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `idcliente` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estatus` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `number_exterior` int(11) DEFAULT NULL,
  `number_interior` varchar(100) DEFAULT NULL,
  `materno` varchar(255) DEFAULT NULL,
  `paterno` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`idcliente`, `nombre`, `direccion`, `telefono`, `email`, `estatus`, `number_exterior`, `number_interior`, `materno`, `paterno`) VALUES
(1, 'Publico general', 'Argentina', '2222332231', 'argentina@gmail.com', 'Activo', 103, '122', '', ''),
(2, 'Ignacio Nicolas', 'Balcarce', '3517481000', 'nachoprogramando@gmail.com', 'Activo', 136, '5B', 'Villagra Holz', NULL),
(3, 'Juan ', 'bedoya', '3517689557', 'markojajuan@gmail.com', 'Activo', 291, 'Ggggg', 'Markoja', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `color`
--

CREATE TABLE `color` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `hexadecimal` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `color`
--

INSERT INTO `color` (`id`, `name`, `status`, `hexadecimal`, `registration_date`) VALUES
(1, 'Negro', 1, '#0a0a0a', '2025-06-12 23:06:51'),
(2, 'Amarillo', 1, '#d4ff00', '2025-06-12 23:06:59'),
(3, 'Rosa', 1, '#d400ff', '2025-06-12 23:07:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `adress` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `name`, `image`, `adress`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Chemin', 'Diseño sin título (3).png', 'Argentina', 'argentina@gmail.com', '7937937957', '2023-02-18 18:19:32', '2025-06-13 00:41:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `corte_cajero_dia`
--

CREATE TABLE `corte_cajero_dia` (
  `idcortecaja` bigint(20) UNSIGNED NOT NULL,
  `apertura_id` bigint(20) UNSIGNED NOT NULL,
  `total_acomulado` decimal(11,2) NOT NULL,
  `seriefolio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `numfolio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `corte_cajero_dia`
--

INSERT INTO `corte_cajero_dia` (`idcortecaja`, `apertura_id`, `total_acomulado`, `seriefolio`, `numfolio`, `fecha`, `hora`) VALUES
(1, 1, 180000.00, '20256121', '2', '2025-06-12', '20:47:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cotizacion`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_devolucion_ventas`
--

CREATE TABLE `detalle_devolucion_ventas` (
  `iddetalledevolucion` bigint(20) UNSIGNED NOT NULL,
  `devolucion_id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL,
  `motivo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Disparadores `detalle_devolucion_ventas`
--
DELIMITER $$
CREATE TRIGGER `trigger_deleteProductoOnDetalleVentas` AFTER INSERT ON `detalle_devolucion_ventas` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_entrada_temp`
--

CREATE TABLE `detalle_entrada_temp` (
  `identradatemp` bigint(20) UNSIGNED NOT NULL,
  `id_user` int(11) NOT NULL,
  `idarticulo` int(11) NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `tipo_producto_id` int(11) NOT NULL,
  `producto_variacion_variante_id` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
  `subtotal` decimal(11,2) NOT NULL,
  `tipo_producto_id` int(11) NOT NULL,
  `producto_variacion_variante_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `detalle_ingresos`
--

INSERT INTO `detalle_ingresos` (`iddetalle_ingreso`, `ingreso_id`, `articulo_id`, `cantidad`, `precio_compra`, `precio_venta`, `subtotal`, `tipo_producto_id`, `producto_variacion_variante_id`) VALUES
(1, 1, 2, 3.000, 45000.00, 55000.00, 135000.00, 2, 2),
(2, 1, 2, 1.000, 30000.00, 45000.00, 30000.00, 2, 1),
(3, 1, 1, 6.000, 35000.00, 45000.00, 210000.00, 1, NULL),
(4, 2, 2, 1.000, 30000.00, 45000.00, 30000.00, 2, 1),
(5, 3, 1, 1.000, 35000.00, 45000.00, 35000.00, 1, NULL),
(6, 4, 2, 3.000, 45000.00, 55000.00, 135000.00, 2, 2),
(7, 4, 2, 2.000, 30000.00, 45000.00, 60000.00, 2, 1);

--
-- Disparadores `detalle_ingresos`
--
DELIMITER $$
CREATE TRIGGER `trigger_updateStockProducto` AFTER INSERT ON `detalle_ingresos` FOR EACH ROW BEGIN
	if NEW.tipo_producto_id = 2 AND new.tipo_producto_id IS NOT NULL then
		UPDATE producto_variacion_variante SET stock=stock + NEW.cantidad, pcompra=NEW.precio_compra, price=NEW.precio_venta 
		WHERE id = NEW.producto_variacion_variante_id;
	ELSEIF NEW.tipo_producto_id = 1 THEN
		UPDATE productos SET stock=stock + NEW.cantidad, pcompra=NEW.precio_compra, pventa=NEW.precio_venta 
		WHERE productos.idarticulo = NEW.articulo_id;
	END IF;
    END
$$
DELIMITER ;

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
  `producto_variacion_variante_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`iddetalle_venta`, `venta_id`, `articulo_id`, `apertura_id`, `cantidad`, `precio_venta`, `descuento`, `subtotal`, `tipo_producto_id`, `producto_variacion_variante_id`) VALUES
(1, 1, 1, 1, 4.000, 45000.00, 0.00, 180000.00, 1, NULL);

--
-- Disparadores `detalle_ventas`
--
DELIMITER $$
CREATE TRIGGER `trigger_updateStockVenta` AFTER INSERT ON `detalle_ventas` FOR EACH ROW BEGIN
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
  `codproducto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cantidad` decimal(11,3) NOT NULL,
  `precio` decimal(11,2) NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `iva` decimal(11,2) NOT NULL,
  `tipo_producto_id` int(11) NOT NULL,
  `producto_variacion_variante_id` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devolucion_ventas`
--

CREATE TABLE `devolucion_ventas` (
  `iddevolucion` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `observacion` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `idingreso` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `proveedor_id` bigint(20) UNSIGNED NOT NULL,
  `folio_comprobante` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `total_ingreso` decimal(11,2) NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `ingresos`
--

INSERT INTO `ingresos` (`idingreso`, `user_id`, `proveedor_id`, `folio_comprobante`, `fecha_hora`, `total_ingreso`, `estado`) VALUES
(1, 1, 1, '12 - 6 - 2025 - 0', '2025-06-12 17:09:38', 375000.00, 'Activo'),
(2, 1, 1, '12 - 6 - 2025 - 2', '2025-06-12 20:40:25', 30000.00, 'Activo'),
(3, 1, 1, '12 - 6 - 2025 - 3', '2025-06-12 20:40:56', 35000.00, 'Activo'),
(4, 1, 1, '12 - 6 - 2025 - 4', '2025-06-12 20:41:46', 195000.00, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `idinventario` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estatus` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Disparadores `numero_corte_por_cajero`
--
DELIMITER $$
CREATE TRIGGER `trigger_updatecantidadacomulado` AFTER INSERT ON `numero_corte_por_cajero` FOR EACH ROW BEGIN
	UPDATE corte_cajero_dia SET total_acomulado=total_acomulado-NEW.cantidad
	WHERE corte_cajero_dia.idcortecaja=NEW.cortecaja_id;
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_detail_ecommerce`
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `order_detail_ecommerce`
--

INSERT INTO `order_detail_ecommerce` (`order_detail_id`, `order_ecommerce_id`, `product_id`, `quantity`, `price`, `total`, `active`, `producto_variacion_variante_id`) VALUES
(1, 1, 2, 2, 45000.00, 90000.00, 1, 1),
(2, 1, 2, 6, 55000.00, 330000.00, 1, 2),
(3, 2, 1, 1, 45000.00, 45000.00, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_ecommerce`
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `order_ecommerce`
--

INSERT INTO `order_ecommerce` (`order_id`, `status_order_id`, `cliente_id`, `order_date`, `subtotal_amount`, `total_amount`, `active`, `additional_info`) VALUES
(1, 1, 2, '2025-06-13 02:46:46', 420000.00, 420000.00, 1, 'Departamento'),
(2, 1, 3, '2025-06-13 02:59:18', 45000.00, 45000.00, 1, 'Ggggg');

--
-- Disparadores `order_ecommerce`
--
DELIMITER $$
CREATE TRIGGER `trigger_afterOrderPaid` AFTER UPDATE ON `order_ecommerce` FOR EACH ROW BEGIN
		DECLARE caja_open_id INT;
		
    	IF NEW.status_order_id = 2 AND OLD.status_order_id <> 2 THEN 	-- Check if the status was updated to 'Paid'
    		SELECT caja_virtual_id INTO caja_open_id FROM aperturacajavirtual WHERE STATUS = 1 AND end_date_time IS NULL AND status_box = 'virtual_box_open';
    		IF caja_open_id IS NOT NULL THEN
    		   UPDATE aperturacajavirtual SET total = total + NEW.total_amount WHERE caja_virtual_id = caja_open_id; -- Update the total
    		   CALL processStockAfterOrderPaid(NEW.order_id); -- llama al procedimiento para restar el stock al producto simple y variante
		   UPDATE payment_ecommerce SET status_payment = 'Completado' WHERE order_id = NEW.order_id;
    	   ELSE
    	      UPDATE payment_ecommerce SET status_payment = 'Fallido' WHERE order_id = NEW.order_id;
			END IF;  
    	END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_cancelOrderEcommerce` AFTER UPDATE ON `order_ecommerce` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_ecommerce`
--

CREATE TABLE `payment_ecommerce` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `total` decimal(11,2) NOT NULL,
  `status_payment` enum('Pendiente','Completado','Fallido','Reembolsado','Cancelado') DEFAULT 'Pendiente',
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `payment_ecommerce`
--

INSERT INTO `payment_ecommerce` (`payment_id`, `order_id`, `payment_method_id`, `payment_date`, `total`, `status_payment`, `status`) VALUES
(1, 1, NULL, '2025-06-13 02:46:46', 420000.00, 'Pendiente', 1),
(2, 2, NULL, '2025-06-13 02:59:18', 45000.00, 'Pendiente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `method_name`, `status`) VALUES
(1, 'Tarjeta de crédito', 1),
(2, 'PayPal', 1),
(3, 'Transferencia bancaria', 1),
(4, 'Efectivo', 1),
(5, 'Tarjeta de debito', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'listar el menu principal', 'admin.index', 'un administrador puede ver menu', '2020-10-18 00:47:42', '2020-10-18 00:47:42'),
(2, 'listar el menu de almacen', 'almacen.index', 'Un usuario puede ver menu de alamcen', '2020-10-18 00:47:42', '2020-10-18 00:47:42'),
(3, 'listar el menu de compras', 'compras.index', 'Un usuario puede ver menu de compras', '2020-10-18 00:47:42', '2020-10-18 00:47:42'),
(4, 'listar el menu de ventas', 'ventas.index', 'Un usuario puede ver menu de ventas', '2020-10-18 00:47:42', '2020-10-18 00:47:42'),
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
(23, 'listar la seccion de historico de cajas', 'caja_historicolist.index', 'Un usuario puede vel la seccion de historico de cajas', '2023-02-27 02:42:50', '2023-02-27 02:42:50'),
(24, 'Listar la seccion de cotizacion', 'cotizaciones.index', 'Un usuario puede ver el menu de cotizaciones', NULL, NULL),
(25, 'Crear una cotizacion', 'cotizaciones_cliente.index', 'Un usuario puede crear una cotizacion', NULL, NULL),
(26, 'Listar las cotizaciones', 'cotizaciones_cotizacion.index', 'Un usuario puede ver las cotizaciones', NULL, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `idarticulo` bigint(20) UNSIGNED NOT NULL,
  `categoria_id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stock` double(11,3) NOT NULL,
  `pcompra` decimal(11,2) NOT NULL,
  `pventa` decimal(11,2) NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `imagen` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descuento` decimal(11,2) NOT NULL,
  `iva` decimal(11,2) NOT NULL,
  `tipo_producto_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `productos`
-- 

INSERT INTO `productos` (`idarticulo`, `categoria_id`, `codigo`, `nombre`, `stock`, `pcompra`, `pventa`, `descripcion`, `imagen`, `estado`, `descuento`, `iva`, `tipo_producto_id`) VALUES
(1, 1, '1234567891234', 'Sabana Essencial Alcoyana', 3.000, 35000.00, 45000.00, 'La sábana Essencial de Alcoyana combina suavidad, frescura y durabilidad en una pieza ideal para el descanso diario. Confeccionada con materiales de alta calidad, ofrece una textura suave al tacto y una excelente resistencia al uso y los lavados. Su diseño simple y elegante se adapta a cualquier estilo de habitación, brindando confort y funcionalidad para un sueño placentero. Perfecta para quienes buscan calidad y confort en cada detalle.', 'SABANA1.jpg', 'Activo', 0.00, 0.00, 1),
(2, 1, '1234567891235', 'sabana esencial alcoyana', 1.000, 0.00, 0.00, 'sabana', 'SABANA1.jpg', 'Activo', 0.00, 0.00, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_integracion_variante`
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `producto_integracion_variante`
--

INSERT INTO `producto_integracion_variante` (`id`, `producto_id`, `variacion_id`, `variante_id`, `descripcion`, `status`, `activo`, `registration_date`) VALUES
(1, 2, 1, 1, NULL, 'P', 1, '2025-06-12 23:07:30'),
(2, 2, 1, 2, NULL, 'P', 1, '2025-06-12 23:07:30'),
(3, 2, 1, 3, NULL, 'P', 1, '2025-06-12 23:07:30'),
(4, 2, 1, 4, NULL, 'P', 1, '2025-06-12 23:07:30'),
(5, 2, 1, 5, NULL, 'P', 1, '2025-06-12 23:07:30'),
(6, 2, 1, 6, NULL, 'P', 1, '2025-06-12 23:07:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_variacion_variante`
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
  `show_ecommerce` tinyint(1) DEFAULT 0,
  `pcompra` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `producto_variacion_variante`
--

INSERT INTO `producto_variacion_variante` (`id`, `color_id`, `product_integration_id`, `price`, `name_image`, `path_image`, `stock`, `active`, `registration_date`, `show_ecommerce`, `pcompra`) VALUES
(1, 1, 1, 45000.00, 'SABANA1.jpg', 'imagenes/articulo_variante/product-2', 4.00, 1, '2025-06-12 23:08:13', 1, 30000.00),
(2, 2, 2, 55000.00, 'SABANA2.jpg', 'imagenes/articulo_variante/product-2', 6.00, 1, '2025-06-12 23:08:36', 0, 45000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `idproveedor` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`idproveedor`, `nombre`, `direccion`, `telefono`, `email`, `estado`) VALUES
(1, 'Proveedor general', 'Proveedor direccion', '3510001111', 'proveedorgeneral@gmail.com', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `full-access` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `full-access`, `estatus`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Admin', 'Administrador', 'yes', 1, '2023-02-18 18:25:32', '2023-02-18 18:25:32');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `role_user`
--

INSERT INTO `role_user` (`id`, `role_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2023-02-18 18:26:57', '2023-02-18 18:26:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_orders_ecommerce`
--

CREATE TABLE `status_orders_ecommerce` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `status_orders_ecommerce`
--

INSERT INTO `status_orders_ecommerce` (`status_id`, `status_name`, `active`, `registration_date`) VALUES
(1, 'Pendiente', 1, '2024-12-23 15:37:24'),
(2, 'Pagado', 1, '2024-12-23 15:37:24'),
(3, 'Enviado', 1, '2024-12-23 15:37:24'),
(4, 'Entregado', 1, '2024-12-23 15:37:24'),
(5, 'Cancelado', 1, '2024-12-23 15:37:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto`
--

CREATE TABLE `tipo_producto` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tipo_producto`
--

INSERT INTO `tipo_producto` (`id`, `name`, `descripcion`, `status`, `registration_date`) VALUES
(1, 'Producto simple', '', 1, '2024-12-23 15:34:31'),
(2, 'Producto personalizado', '', 1, '2024-12-23 15:34:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `estatus`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@gmail.com', NULL, '$2y$10$XF4As8Nz9lr3UCBjo3YxKOpe4dHSqK4DrwPPqjA5JJwrfZXRe.p0.', 1, '0ABo1BXHWfd4fb1ohhuWpOmhHhLR2U4uZPP6HRjycB2RRQTJfoalHjfXsJfv', '2023-02-18 18:00:57', '2023-09-15 07:23:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variaciones`
--

CREATE TABLE `variaciones` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `variaciones`
--

INSERT INTO `variaciones` (`id`, `name`, `option_type`, `status`, `registration_date`) VALUES
(1, 'Tamaño', 'Boton', 1, '2025-06-12 23:06:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variantes_para_variaciones`
--

CREATE TABLE `variantes_para_variaciones` (
  `id` int(11) NOT NULL,
  `variacion_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `option_type` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `variantes_para_variaciones`
--

INSERT INTO `variantes_para_variaciones` (`id`, `variacion_id`, `name`, `option_type`, `descripcion`, `status`, `registration_date`) VALUES
(1, 1, '1', NULL, NULL, 1, '2025-06-12 23:06:33'),
(2, 1, '1 1/2', NULL, NULL, 1, '2025-06-12 23:06:33'),
(3, 1, '2', NULL, NULL, 1, '2025-06-12 23:06:33'),
(4, 1, '2 1/2', NULL, NULL, 1, '2025-06-12 23:06:33'),
(5, 1, 'KING', NULL, NULL, 1, '2025-06-12 23:06:33'),
(6, 1, 'QUEEN', NULL, NULL, 1, '2025-06-12 23:06:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `idventa` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_comprobante` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `num_folio` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `efectivo` decimal(11,2) NOT NULL,
  `total_venta` decimal(11,2) NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`idventa`, `user_id`, `cliente_id`, `tipo_comprobante`, `num_folio`, `fecha_hora`, `efectivo`, `total_venta`, `estado`) VALUES
(1, 1, 1, 'Ticket', '202561211', '2025-06-12 21:01:12', 180000.00, 180000.00, 'Activo');

--
-- Índices para tablas volcadas
--

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
  ADD PRIMARY KEY (`idcaptura`),
  ADD KEY `capturarinventario_articulo_id_foreign` (`articulo_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cotizacion` (`id_cotizacion`),
  ADD KEY `id_producto` (`id_producto`);

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
  ADD KEY `detalle_ingresos_articulo_id_foreign` (`articulo_id`),
  ADD KEY `fk_tipo_producto` (`tipo_producto_id`),
  ADD KEY `fk_producto_variacion_variante` (`producto_variacion_variante_id`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`iddetalle_venta`),
  ADD KEY `detalle_ventas_venta_id_foreign` (`venta_id`),
  ADD KEY `detalle_ventas_articulo_id_foreign` (`articulo_id`),
  ADD KEY `detalle_ventas_apertura_id_foreign` (`apertura_id`),
  ADD KEY `fk_tipo_producto_detalle_ventas` (`tipo_producto_id`),
  ADD KEY `fk_producto_variacion_variante_detalle_ventas` (`producto_variacion_variante_id`);

--
-- Indices de la tabla `detalle_venta_temp`
--
ALTER TABLE `detalle_venta_temp`
  ADD PRIMARY KEY (`iddetalletemp`);

--
-- Indices de la tabla `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  ADD PRIMARY KEY (`iddevolucion`),
  ADD KEY `devolucion_ventas_venta_id_foreign` (`venta_id`);

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
  ADD PRIMARY KEY (`idinventario`),
  ADD KEY `inventario_user_id_foreign` (`user_id`);

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
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_ecommerce_id` (`order_ecommerce_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_producto_variacion_variante_id` (`producto_variacion_variante_id`);

--
-- Indices de la tabla `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `status_order_id` (`status_order_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indices de la tabla `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indices de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`),
  ADD UNIQUE KEY `method_name` (`method_name`);

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
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`idarticulo`),
  ADD KEY `productos_categoria_id_foreign` (`categoria_id`),
  ADD KEY `fk_tipo_producto_id` (`tipo_producto_id`);

--
-- Indices de la tabla `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `variacion_id` (`variacion_id`),
  ADD KEY `variante_id` (`variante_id`);

--
-- Indices de la tabla `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `color_id` (`color_id`),
  ADD KEY `product_integration_id` (`product_integration_id`);

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
-- Indices de la tabla `status_orders_ecommerce`
--
ALTER TABLE `status_orders_ecommerce`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `variacion_id` (`variacion_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`idventa`),
  ADD KEY `ventas_user_id_foreign` (`user_id`),
  ADD KEY `ventas_cliente_id_foreign` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aperturacajas`
--
ALTER TABLE `aperturacajas`
  MODIFY `idapertura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `aperturacajavirtual`
--
ALTER TABLE `aperturacajavirtual`
  MODIFY `caja_virtual_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `banner_ecommerce`
--
ALTER TABLE `banner_ecommerce`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `capturarinventario`
--
ALTER TABLE `capturarinventario`
  MODIFY `idcaptura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrito_cotizacion_temp`
--
ALTER TABLE `carrito_cotizacion_temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `idcategoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  MODIFY `idcortecaja` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `identradatemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  MODIFY `iddetalle_ingreso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `iddetalle_venta` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_venta_temp`
--
ALTER TABLE `detalle_venta_temp`
  MODIFY `iddetalletemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `devolucion_ventas`
--
ALTER TABLE `devolucion_ventas`
  MODIFY `iddevolucion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `idingreso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `idinventario` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  MODIFY `idnumerocorte` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `order_detail_ecommerce`
--
ALTER TABLE `order_detail_ecommerce`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `permission_role`
--
ALTER TABLE `permission_role`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idproveedor` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT de la tabla `status_orders_ecommerce`
--
ALTER TABLE `status_orders_ecommerce`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `variaciones`
--
ALTER TABLE `variaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `idventa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aperturacajas`
--
ALTER TABLE `aperturacajas`
  ADD CONSTRAINT `aperturacajas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `capturarinventario`
--
ALTER TABLE `capturarinventario`
  ADD CONSTRAINT `capturarinventario_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `corte_cajero_dia`
--
ALTER TABLE `corte_cajero_dia`
  ADD CONSTRAINT `corte_cajero_dia_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD CONSTRAINT `detalles_cotizacion_ibfk_1` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_cotizacion_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `detalle_ingresos_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `ingresos` (`idingreso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_producto_variacion_variante` FOREIGN KEY (`producto_variacion_variante_id`) REFERENCES `producto_variacion_variante` (`id`),
  ADD CONSTRAINT `fk_tipo_producto` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`);

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_apertura_id_foreign` FOREIGN KEY (`apertura_id`) REFERENCES `aperturacajas` (`idapertura`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `productos` (`idarticulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`idventa`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_producto_variacion_variante_detalle_ventas` FOREIGN KEY (`producto_variacion_variante_id`) REFERENCES `producto_variacion_variante` (`id`),
  ADD CONSTRAINT `fk_tipo_producto_detalle_ventas` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`);

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
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `numero_corte_por_cajero`
--
ALTER TABLE `numero_corte_por_cajero`
  ADD CONSTRAINT `numero_corte_por_cajero_cortecaja_id_foreign` FOREIGN KEY (`cortecaja_id`) REFERENCES `corte_cajero_dia` (`idcortecaja`) ON DELETE CASCADE;

--
-- Filtros para la tabla `order_detail_ecommerce`
--
ALTER TABLE `order_detail_ecommerce`
  ADD CONSTRAINT `fk_producto_variacion_variante_id` FOREIGN KEY (`producto_variacion_variante_id`) REFERENCES `producto_variacion_variante` (`id`),
  ADD CONSTRAINT `order_detail_ecommerce_ibfk_1` FOREIGN KEY (`order_ecommerce_id`) REFERENCES `order_ecommerce` (`order_id`),
  ADD CONSTRAINT `order_detail_ecommerce_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `productos` (`idarticulo`);

--
-- Filtros para la tabla `order_ecommerce`
--
ALTER TABLE `order_ecommerce`
  ADD CONSTRAINT `order_ecommerce_ibfk_1` FOREIGN KEY (`status_order_id`) REFERENCES `status_orders_ecommerce` (`status_id`),
  ADD CONSTRAINT `order_ecommerce_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`idcliente`);

--
-- Filtros para la tabla `payment_ecommerce`
--
ALTER TABLE `payment_ecommerce`
  ADD CONSTRAINT `payment_ecommerce_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_ecommerce` (`order_id`),
  ADD CONSTRAINT `payment_ecommerce_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

--
-- Filtros para la tabla `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_tipo_producto_id` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`),
  ADD CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`idcategoria`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_integracion_variante`
--
ALTER TABLE `producto_integracion_variante`
  ADD CONSTRAINT `producto_integracion_variante_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`idarticulo`),
  ADD CONSTRAINT `producto_integracion_variante_ibfk_2` FOREIGN KEY (`variacion_id`) REFERENCES `variaciones` (`id`),
  ADD CONSTRAINT `producto_integracion_variante_ibfk_3` FOREIGN KEY (`variante_id`) REFERENCES `variantes_para_variaciones` (`id`);

--
-- Filtros para la tabla `producto_variacion_variante`
--
ALTER TABLE `producto_variacion_variante`
  ADD CONSTRAINT `producto_variacion_variante_ibfk_1` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`),
  ADD CONSTRAINT `producto_variacion_variante_ibfk_2` FOREIGN KEY (`product_integration_id`) REFERENCES `producto_integracion_variante` (`id`);

--
-- Filtros para la tabla `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `variantes_para_variaciones`
--
ALTER TABLE `variantes_para_variaciones`
  ADD CONSTRAINT `variantes_para_variaciones_ibfk_1` FOREIGN KEY (`variacion_id`) REFERENCES `variaciones` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
