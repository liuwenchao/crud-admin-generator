-- MySQL dump 10.13  Distrib 8.0.17, for osx10.14 (x86_64)
--
-- Host: localhost    Database: qidian
-- ------------------------------------------------------
-- Server version	8.0.17

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `boom`
--

DROP TABLE IF EXISTS `boom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `boom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(8) COLLATE utf8mb4_general_ci NOT NULL COMMENT '名字',
  `code` char(12) COLLATE utf8mb4_general_ci NOT NULL COMMENT '编码',
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boom`
--

LOCK TABLES `boom` WRITE;
/*!40000 ALTER TABLE `boom` DISABLE KEYS */;
/*!40000 ALTER TABLE `boom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brand`
--

DROP TABLE IF EXISTS `brand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand`
--

LOCK TABLES `brand` WRITE;
/*!40000 ALTER TABLE `brand` DISABLE KEYS */;
INSERT INTO `brand` VALUES (1,'暖岛');
/*!40000 ALTER TABLE `brand` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category` (
  `id` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `name` char(8) COLLATE utf8mb4_general_ci NOT NULL,
  `gmt_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gmt_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES ('A','容器','2019-12-08 10:28:13','2019-12-08 10:28:13'),('Y','整体样品','2019-12-08 06:16:42','2019-12-08 06:16:42');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material`
--

DROP TABLE IF EXISTS `material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material` (
  `id` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `name` char(8) COLLATE utf8mb4_general_ci NOT NULL,
  `gmt_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gmt_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material`
--

LOCK TABLES `material` WRITE;
/*!40000 ALTER TABLE `material` DISABLE KEYS */;
INSERT INTO `material` VALUES ('JS','金属','2019-12-08 06:19:17','2019-12-08 06:19:17'),('SL','塑料','2019-12-08 06:19:07','2019-12-08 06:19:07');
/*!40000 ALTER TABLE `material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL COMMENT '供应商',
  `category_id` char(1) COLLATE utf8mb4_general_ci NOT NULL COMMENT '供应品类',
  `size` int(11) NOT NULL COMMENT '主规格数量',
  `unit` varchar(2) COLLATE utf8mb4_general_ci NOT NULL COMMENT '主规格单位',
  `category2` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '二级规格类型',
  `size2` int(11) DEFAULT NULL COMMENT '二级规格数量',
  `unit2` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '二级规格单位',
  `package_code` char(14) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '包材代码',
  `bottle` blob COMMENT '瓶器图片',
  `material_id` char(2) COLLATE utf8mb4_general_ci NOT NULL COMMENT '材质',
  `minimal_order` int(11) DEFAULT NULL COMMENT '起订量',
  `pre_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '税前价格',
  `full_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '含税含运价格',
  `open_mould_period` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '开模周期',
  `sample_period` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '打样周期',
  `payment_method` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '付款方式',
  `supply_period` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '供货周期',
  `memo` text COLLATE utf8mb4_general_ci COMMENT '备注',
  `brand_id` int(11) NOT NULL COMMENT '品牌',
  `code` char(8) COLLATE utf8mb4_general_ci NOT NULL COMMENT '成品编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,1,'Y',130,'ML',NULL,NULL,NULL,'2NDA001A04AZ',NULL,'SL',100,2.35,5.35,'2周','1周','中国银行','1月','这个品需要严控质量',1,'0NDA003A'),(2,1,'Y',150,'ML',NULL,NULL,NULL,'2NDA001A04AP',_binary '5deccbe120965.jpeg','JS',100,2.35,8.35,'2周','1周','中国银行','1月','双十二',1,'0NDA002A'),(3,1,'Y',180,'ML',NULL,NULL,NULL,'2NDA001A04AO',_binary '5decce882348d.jpeg','JS',200,2.35,18.00,'2周','1周','中国银行','1月','无',1,'0NDA001A');
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider`
--

DROP TABLE IF EXISTS `provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `license` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider`
--

LOCK TABLES `provider` WRITE;
/*!40000 ALTER TABLE `provider` DISABLE KEYS */;
INSERT INTO `provider` VALUES (1,'SEEFU','金华惜福制造有限公司','datada','金华二服路188号','王惜福','17375757575'),(2,'WANCA','余姚旺财制造有限公司','datada','余姚大发路39号','李旺财','17353535353');
/*!40000 ALTER TABLE `provider` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-12-08 20:22:00
