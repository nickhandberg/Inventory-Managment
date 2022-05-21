-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 05, 2021 at 12:20 AM
-- Server version: 8.0.27
-- PHP Version: 7.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `guitarshopinventory`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `addProduct` (IN `name` VARCHAR(25), IN `qty` INT, IN `price` FLOAT, IN `page` VARCHAR(150), IN `sale` TINYINT, IN `dep` INT, IN `sty` INT)  BEGIN
INSERT INTO product 
(productName, productQty, productPrice, productPage,
onSale, department_id) 
VALUES
(name, qty, price, page, sale, dep);
INSERT INTO productstyle
(product_id, style_id)
VALUES
(@@Identity, sty);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `checkDuplicate` (IN `name` VARCHAR(25))  BEGIN
SELECT productName
FROM product
WHERE productName = name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteProduct` (IN `id` INT)  BEGIN
DELETE 
FROM product 
WHERE product_id = id;
DELETE
FROM productstyle
WHERE product_id = id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateProduct` (IN `prodId` INT, IN `name` VARCHAR(25), IN `qty` INT, IN `price` FLOAT, IN `page` VARCHAR(150), IN `sale` TINYINT, IN `dep` INT, IN `sty` INT)  BEGIN
UPDATE product 
SET
    productName = name,
    productQty = qty,
    productPrice = price,
    productPage = page,
    onSale = sale,
    department_id = dep
WHERE product_id = prodId;
UPDATE productstyle
SET
	style_id = sty
WHERE product_id = prodID;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int UNSIGNED NOT NULL,
  `departmentName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `departmentName`) VALUES
(1, 'Guitar'),
(2, 'Piano'),
(3, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int UNSIGNED NOT NULL,
  `productName` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `productQty` int NOT NULL,
  `productPrice` float NOT NULL,
  `productPage` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `onSale` tinyint(1) DEFAULT NULL,
  `department_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `productName`, `productQty`, `productPrice`, `productPage`, `onSale`, `department_id`) VALUES
(1, 'Fender Stratocaster', 11, 999.99, 'test', 1, 1),
(2, 'Tuner', 52, 15.99, 'test', 0, 3),
(3, 'Piano', 23, 599.99, 'test', 1, 2),
(4, 'Picks 12pk', 837, 3.99, 'test', 0, 3),
(9, 'Test', 50, 4.5, 'www.Test+URL.com%2FTest-Test', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `productstyle`
--

CREATE TABLE `productstyle` (
  `product_id` int NOT NULL,
  `style_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productstyle`
--

INSERT INTO `productstyle` (`product_id`, `style_id`) VALUES
(1, 3),
(2, 2),
(3, 1),
(4, 1),
(9, 2);

-- --------------------------------------------------------

--
-- Table structure for table `style`
--

CREATE TABLE `style` (
  `style_id` int UNSIGNED NOT NULL,
  `style` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `style`
--

INSERT INTO `style` (`style_id`, `style`) VALUES
(1, 'Black'),
(2, 'White'),
(3, 'Wooden');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `style`
--
ALTER TABLE `style`
  ADD PRIMARY KEY (`style_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `style`
--
ALTER TABLE `style`
  MODIFY `style_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
