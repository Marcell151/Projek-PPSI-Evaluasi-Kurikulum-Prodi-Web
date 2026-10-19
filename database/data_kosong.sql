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
-- Table structure for table `bahan_kajian`
--

DROP TABLE IF EXISTS `bahan_kajian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bahan_kajian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama_id` varchar(150) NOT NULL,
  `nama_en` varchar(150) DEFAULT NULL,
  `kategori_kompetensi_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `kategori_kompetensi_id` (`kategori_kompetensi_id`),
  CONSTRAINT `bahan_kajian_ibfk_1` FOREIGN KEY (`kategori_kompetensi_id`) REFERENCES `kategori_kompetensi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jawaban`
--

DROP TABLE IF EXISTS `jawaban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jawaban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengisian_id` int(11) NOT NULL,
  `pertanyaan_id` int(11) NOT NULL,
  `mata_kuliah_id` int(11) DEFAULT NULL,
  `nilai_skala` tinyint(4) DEFAULT NULL,
  `opsi_id` int(11) DEFAULT NULL,
  `teks` text DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `mata_kuliah_key` int(11) GENERATED ALWAYS AS (ifnull(`mata_kuliah_id`,0)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_jawaban` (`pengisian_id`,`pertanyaan_id`,`mata_kuliah_key`),
  KEY `pengisian_id` (`pengisian_id`),
  KEY `pertanyaan_id` (`pertanyaan_id`),
  KEY `mata_kuliah_id` (`mata_kuliah_id`),
  KEY `opsi_id` (`opsi_id`),
  CONSTRAINT `jawaban_ibfk_1` FOREIGN KEY (`pengisian_id`) REFERENCES `pengisian` (`id`),
  CONSTRAINT `jawaban_ibfk_2` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan` (`id`),
  CONSTRAINT `jawaban_ibfk_3` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`),
  CONSTRAINT `jawaban_ibfk_4` FOREIGN KEY (`opsi_id`) REFERENCES `opsi_pertanyaan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=512 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jawaban_multi`
--

DROP TABLE IF EXISTS `jawaban_multi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jawaban_multi` (
  `jawaban_id` int(11) NOT NULL,
  `opsi_id` int(11) NOT NULL,
  PRIMARY KEY (`jawaban_id`,`opsi_id`),
  KEY `opsi_id` (`opsi_id`),
  CONSTRAINT `jawaban_multi_ibfk_1` FOREIGN KEY (`jawaban_id`) REFERENCES `jawaban` (`id`),
  CONSTRAINT `jawaban_multi_ibfk_2` FOREIGN KEY (`opsi_id`) REFERENCES `opsi_pertanyaan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kategori_kompetensi`
--

DROP TABLE IF EXISTS `kategori_kompetensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_kompetensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_impor`
--

DROP TABLE IF EXISTS `log_impor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_impor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `nama_berkas` varchar(255) NOT NULL,
  `jumlah_berhasil` int(11) DEFAULT 0,
  `jumlah_gagal` int(11) DEFAULT 0,
  `ringkasan_galat` text DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `log_impor_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mata_kuliah`
--

DROP TABLE IF EXISTS `mata_kuliah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi_singkat` text DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `sks` int(11) NOT NULL,
  `jenis` enum('wajib','pilihan') DEFAULT 'wajib',
  `kelompok` enum('universitas','fakultas','prodi') DEFAULT 'prodi',
  `kurikulum` varchar(10) DEFAULT '2024',
  `aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mata_kuliah_bk`
--

DROP TABLE IF EXISTS `mata_kuliah_bk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mata_kuliah_bk` (
  `mata_kuliah_id` int(11) NOT NULL,
  `bahan_kajian_id` int(11) NOT NULL,
  `utama` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`mata_kuliah_id`,`bahan_kajian_id`),
  KEY `bahan_kajian_id` (`bahan_kajian_id`),
  CONSTRAINT `mata_kuliah_bk_ibfk_1` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`),
  CONSTRAINT `mata_kuliah_bk_ibfk_2` FOREIGN KEY (`bahan_kajian_id`) REFERENCES `bahan_kajian` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `opsi_pertanyaan`
--

DROP TABLE IF EXISTS `opsi_pertanyaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opsi_pertanyaan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pertanyaan_id` int(11) NOT NULL,
  `teks_opsi` varchar(255) NOT NULL,
  `kategori_kompetensi_id` int(11) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `aktif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `pertanyaan_id` (`pertanyaan_id`),
  KEY `kategori_kompetensi_id` (`kategori_kompetensi_id`),
  CONSTRAINT `opsi_pertanyaan_ibfk_1` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan` (`id`),
  CONSTRAINT `opsi_pertanyaan_ibfk_2` FOREIGN KEY (`kategori_kompetensi_id`) REFERENCES `kategori_kompetensi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `pengisian`
--

DROP TABLE IF EXISTS `pengisian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengisian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengguna_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `status` enum('draft','final') DEFAULT 'draft',
  `tab_terakhir` int(11) DEFAULT 1,
  `terakhir_disimpan` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `waktu_submit` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengguna_id` (`pengguna_id`,`periode_id`),
  KEY `periode_id` (`periode_id`),
  CONSTRAINT `pengisian_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `pengisian_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periode_evaluasi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengisian_matkul`
--

DROP TABLE IF EXISTS `pengisian_matkul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengisian_matkul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengisian_id` int(11) NOT NULL,
  `mata_kuliah_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengisian_id` (`pengisian_id`,`mata_kuliah_id`),
  KEY `mata_kuliah_id` (`mata_kuliah_id`),
  CONSTRAINT `pengisian_matkul_ibfk_1` FOREIGN KEY (`pengisian_id`) REFERENCES `pengisian` (`id`),
  CONSTRAINT `pengisian_matkul_ibfk_2` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periode_evaluasi`
--

DROP TABLE IF EXISTS `periode_evaluasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periode_evaluasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun_akademik` varchar(50) DEFAULT NULL,
  `status` enum('draft','buka','tutup') DEFAULT 'draft',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pertanyaan`
--

DROP TABLE IF EXISTS `pertanyaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pertanyaan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) DEFAULT NULL,
  `teks` text NOT NULL,
  `bantuan` text DEFAULT NULL,
  `tipe` enum('pilihan_ganda','skala','teks','pilihan_ganda_banyak') NOT NULL,
  `bagian` enum('matkul','kompetensi') NOT NULL,
  `peran_sasaran` enum('mahasiswa','alumni','perusahaan','dosen','semua') NOT NULL,
  `kategori_kompetensi_id` int(11) DEFAULT NULL,
  `wajib` tinyint(1) DEFAULT 1,
  `label_skala` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`label_skala`)),
  `syarat_tampil` varchar(255) DEFAULT NULL,
  `penanda` varchar(255) DEFAULT NULL,
  `maks_pilihan` int(11) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `aktif` tinyint(1) DEFAULT 1,
  `default_aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `alasan_jika_rendah` tinyint(1) DEFAULT 0,
  `untuk_peringkat` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `kategori_kompetensi_id` (`kategori_kompetensi_id`),
  CONSTRAINT `pertanyaan_ibfk_1` FOREIGN KEY (`kategori_kompetensi_id`) REFERENCES `kategori_kompetensi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `tiket`
--

DROP TABLE IF EXISTS `tiket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengguna_id` int(11) NOT NULL,
  `subjek` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `status` enum('baru','diproses','selesai') DEFAULT 'baru',
  `balasan_admin` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pengguna_id` (`pengguna_id`),
  CONSTRAINT `tiket_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-05 17:41:32
