-- MySQL dump 10.13  Distrib 8.0.31, for Win64 (x86_64)
--
-- Host: localhost    Database: corephpadmin
-- ------------------------------------------------------
-- Server version	8.0.31

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `dict_list`
--

DROP TABLE IF EXISTS `dict_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dict_list` (
  `id` int NOT NULL COMMENT 'ИД',
  `name` text NOT NULL COMMENT 'Наименование словаря',
  `dict_author` text NOT NULL COMMENT 'Автор',
  `year_pub` date NOT NULL COMMENT 'Дата публикации',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dict_list`
--

LOCK TABLES `dict_list` WRITE;
/*!40000 ALTER TABLE `dict_list` DISABLE KEYS */;
INSERT INTO `dict_list` VALUES (1,'РУССКО-АБХАЗСКИЙ ЭКОНОМИЧЕСКИЙ СЛОВАРЬ','А. Д. ХЕЦИЯ','2017-01-01',NULL,'2022-11-21 05:59:31'),(2,'АБХАЗСКО-РУССКИЙ СЛОВАРЬ','В.А. Касландзия','2005-01-01',NULL,NULL),(3,'Русско-абхазский словарь','В. А. Касландзия, Б. Г. Джонуа','2016-01-01',NULL,NULL),(4,'АУРЫС – АԤСУА, АԤСУА – АУРЫС АФИЗИКА-МАТЕМАТИКАТӘ ЖӘАР','А. Ҳ. ЧАМАГӘУА','2022-01-01',NULL,NULL),(5,'СЛОВАРЬ МАТЕМАТИЧЕСКИХ ТЕРМИНОВ (РУССКО-АБХАЗСКИЙ, АБХАЗСКО – РУССКИЙ)',' Н. Л. ПАЧУЛИА','2013-01-01',NULL,NULL);
/*!40000 ALTER TABLE `dict_list` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-11-22 16:38:13
