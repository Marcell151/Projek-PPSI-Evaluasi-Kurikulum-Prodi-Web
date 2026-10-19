-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: evaluasi_kurikulum
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `peran` enum('admin','mahasiswa','alumni','perusahaan','dosen') NOT NULL,
  `wajib_ganti_sandi` tinyint(1) DEFAULT 1,
  `aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expired` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengguna`
--

LOCK TABLES `pengguna` WRITE;
/*!40000 ALTER TABLE `pengguna` DISABLE KEYS */;
INSERT INTO `pengguna` VALUES (1,'Administrator Prodi','admin@machung.ac.id','$2y$10$eCzqCB.io3lIaPnrwOOqPOIBoD4AMnI7teaR0k9dV3WD9d/0lIjXe','admin',0,1,'2026-10-05 09:27:29',NULL,NULL),(2,'Carlo Imanuel Suryahasilaga','322310001@student.machung.ac.id','$2y$10$7LaIXuwrzjIwpPbB/zKzwueg1zZQPTceKrQj6c1FzA3sauBK92LXC','mahasiswa',0,1,'2026-10-05 09:27:29',NULL,NULL),(3,'William Christopher Linardi','322310020@student.machung.ac.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','mahasiswa',1,1,'2026-10-05 09:27:29',NULL,NULL),(4,'Kurniawan Michael Bimantara','322310013@student.machung.ac.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','mahasiswa',1,1,'2026-10-05 09:27:29',NULL,NULL),(5,'Marcell Chandra Kenchana','322310015@student.machung.ac.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','mahasiswa',1,1,'2026-10-05 09:27:29',NULL,NULL),(6,'Daniel Saputra','daniel.saputra@machung.ac.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','dosen',1,1,'2026-10-05 09:27:29',NULL,NULL),(7,'Budi Santoso','budi.santoso@machung.ac.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','dosen',1,1,'2026-10-05 09:27:29',NULL,NULL),(8,'Siti Aminah','siti.aminah@machung.ac.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','dosen',1,1,'2026-10-05 09:27:29',NULL,NULL),(9,'Rina Wijaya','rina.wijaya@machung.ac.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','dosen',1,1,'2026-10-05 09:27:29',NULL,NULL),(10,'Andi Gunawan','andi.alumni@gmail.com','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','alumni',1,1,'2026-10-05 09:27:29',NULL,NULL),(11,'Reza Pratama','reza.alumni@yahoo.com','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','alumni',1,1,'2026-10-05 09:27:29',NULL,NULL),(12,'Maya Sari','maya.alumni@hotmail.com','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','alumni',1,1,'2026-10-05 09:27:29',NULL,NULL),(13,'Kevin Sanjaya','kevin.alumni@gmail.com','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','alumni',1,1,'2026-10-05 09:27:29',NULL,NULL),(14,'HRD PT Teknologi Maju','hrd@tekmaju.com','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','perusahaan',1,1,'2026-10-05 09:27:29',NULL,NULL),(15,'Recruiter Tokopedia','recruiter@tokopedia.com','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','perusahaan',1,1,'2026-10-05 09:27:29',NULL,NULL),(16,'Talent Gojek','talent@gojek.com','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','perusahaan',1,1,'2026-10-05 09:27:29',NULL,NULL),(17,'HR Bank BCA','hr@bca.co.id','$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO','perusahaan',1,1,'2026-10-05 09:27:29',NULL,NULL);
/*!40000 ALTER TABLE `pengguna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profil_pengguna`
--

DROP TABLE IF EXISTS `profil_pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profil_pengguna` (
  `pengguna_id` int(11) NOT NULL,
  `nomor_induk` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `posisi_pekerjaan` varchar(100) DEFAULT NULL,
  `instansi` varchar(100) DEFAULT NULL,
  `tahun_lulus` year(4) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pengguna_id`),
  CONSTRAINT `profil_pengguna_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profil_pengguna`
--

LOCK TABLES `profil_pengguna` WRITE;
/*!40000 ALTER TABLE `profil_pengguna` DISABLE KEYS */;
INSERT INTO `profil_pengguna` VALUES (1,'ADM-001','','','','',NULL,NULL),(2,'322310001',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(3,'322310020',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(4,'322310013',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(5,'322310015',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(6,'0712038401',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(7,'0712038402',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(8,'0712038403',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(9,'0712038404',NULL,NULL,NULL,'Universitas Ma Chung',NULL,NULL),(10,NULL,NULL,NULL,NULL,'PT Teknologi Maju',2021,NULL),(11,NULL,NULL,NULL,NULL,'Tokopedia',2022,NULL),(12,NULL,NULL,NULL,NULL,'Gojek',2020,NULL),(13,NULL,NULL,NULL,NULL,'Bank BCA',2023,NULL),(14,NULL,NULL,NULL,NULL,'PT Teknologi Maju',NULL,NULL),(15,NULL,NULL,NULL,NULL,'Tokopedia',NULL,NULL),(16,NULL,NULL,NULL,NULL,'Gojek',NULL,NULL),(17,NULL,NULL,NULL,NULL,'Bank BCA',NULL,NULL);
/*!40000 ALTER TABLE `profil_pengguna` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-05 17:41:32
