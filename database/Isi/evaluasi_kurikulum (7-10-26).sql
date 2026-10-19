-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Okt 2026 pada 14.49
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `evaluasi_kurikulum`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bahan_kajian`
--

CREATE TABLE `bahan_kajian` (
  `id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama_id` varchar(150) NOT NULL,
  `nama_en` varchar(150) DEFAULT NULL,
  `kategori_kompetensi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bahan_kajian`
--

INSERT INTO `bahan_kajian` (`id`, `kode`, `nama_id`, `nama_en`, `kategori_kompetensi_id`) VALUES
(1, 'BK01', 'Dasar-dasar Sistem Informasi', 'Foundation of Information Systems', NULL),
(2, 'BK02', 'Manajemen Data dan Informasi', 'Data / Information Management', 1),
(3, 'BK03', 'Infrastruktur TI', 'IT Infrastructure', 4),
(4, 'BK04', 'Manajemen Proyek SI', 'IS Project Management', 5),
(5, 'BK05', 'Analisis dan Perancangan Sistem', 'Systems Analysis & Design', 3),
(6, 'BK06', 'Manajemen dan Strategi SI', 'IS Management and Strategy', 5),
(7, 'BK07', 'Pengembangan Aplikasi / Pemrograman', 'Application Development / Programming', 3),
(8, 'BK08', 'Keamanan Komputasi', 'Secure Computing', 4),
(9, 'BK09', 'Etika, Penggunaan, dan Dampak bagi Masyarakat', 'Ethics, use and implications for society', NULL),
(10, 'BK10', 'Praktikum', 'Praktikum', NULL),
(11, 'BK11', 'Matematika dan Statistika', 'Mathematics and statistics', 1),
(12, 'BK12', 'Analitik Data / Bisnis', 'Data / Business Analytics', 1),
(13, 'BK13', 'Arsitektur Enterprise', 'Enterprise Architecture', 2),
(14, 'BK14', 'Perancangan Antarmuka Pengguna', 'User Interface Design', 3),
(15, 'BK15', 'Inovasi Digital', 'Digital Innovation', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jawaban`
--

CREATE TABLE `jawaban` (
  `id` int(11) NOT NULL,
  `pengisian_id` int(11) NOT NULL,
  `pertanyaan_id` int(11) NOT NULL,
  `mata_kuliah_id` int(11) DEFAULT NULL,
  `nilai_skala` tinyint(4) DEFAULT NULL,
  `opsi_id` int(11) DEFAULT NULL,
  `teks` text DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `mata_kuliah_key` int(11) GENERATED ALWAYS AS (ifnull(`mata_kuliah_id`,0)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jawaban`
--

INSERT INTO `jawaban` (`id`, `pengisian_id`, `pertanyaan_id`, `mata_kuliah_id`, `nilai_skala`, `opsi_id`, `teks`, `alasan`) VALUES
(1, 1, 1, 5, 4, NULL, NULL, NULL),
(2, 1, 5, 5, 4, NULL, NULL, NULL),
(3, 1, 6, 5, 5, NULL, NULL, NULL),
(4, 1, 7, 5, 4, NULL, NULL, NULL),
(5, 1, 8, 5, 4, NULL, NULL, NULL),
(6, 1, 1, 27, 3, NULL, NULL, NULL),
(7, 1, 5, 27, 4, NULL, NULL, NULL),
(8, 1, 6, 27, 4, NULL, NULL, NULL),
(9, 1, 7, 27, 4, NULL, NULL, NULL),
(10, 1, 8, 27, 4, NULL, NULL, NULL),
(11, 1, 1, 31, 5, NULL, NULL, NULL),
(12, 1, 5, 31, 5, NULL, NULL, NULL),
(13, 1, 6, 31, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(14, 1, 7, 31, 4, NULL, NULL, NULL),
(15, 1, 8, 31, 3, NULL, NULL, NULL),
(16, 1, 33, NULL, 3, NULL, NULL, NULL),
(17, 1, 34, NULL, 3, NULL, 'Kompetensi ini sangat penting. Lulusan diharapkan memiliki inisiatif tinggi.', NULL),
(18, 1, 35, NULL, 3, NULL, NULL, NULL),
(19, 1, 36, NULL, 4, NULL, NULL, NULL),
(20, 1, 37, NULL, 5, NULL, NULL, NULL),
(21, 1, 38, NULL, 4, NULL, 'Kompetensi ini sangat penting. Lulusan diharapkan memiliki inisiatif tinggi.', NULL),
(22, 1, 39, NULL, 4, NULL, NULL, NULL),
(23, 1, 40, NULL, NULL, NULL, NULL, NULL),
(24, 2, 1, 10, 5, NULL, NULL, NULL),
(25, 2, 5, 10, 4, NULL, NULL, NULL),
(26, 2, 6, 10, 5, NULL, NULL, NULL),
(27, 2, 7, 10, 4, NULL, NULL, NULL),
(28, 2, 8, 10, 5, NULL, NULL, NULL),
(29, 2, 1, 27, 3, NULL, NULL, NULL),
(30, 2, 5, 27, 4, NULL, NULL, NULL),
(31, 2, 6, 27, 4, NULL, NULL, NULL),
(32, 2, 7, 27, 4, NULL, NULL, NULL),
(33, 2, 8, 27, 4, NULL, NULL, NULL),
(34, 2, 1, 44, 3, NULL, NULL, NULL),
(35, 2, 5, 44, 5, NULL, NULL, NULL),
(36, 2, 6, 44, 4, NULL, NULL, NULL),
(37, 2, 7, 44, 5, NULL, NULL, NULL),
(38, 2, 8, 44, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(39, 2, 33, NULL, 4, NULL, NULL, NULL),
(40, 2, 34, NULL, 4, NULL, NULL, NULL),
(41, 2, 35, NULL, 5, NULL, NULL, NULL),
(42, 2, 36, NULL, 5, NULL, NULL, NULL),
(43, 2, 37, NULL, 4, NULL, NULL, NULL),
(44, 2, 38, NULL, 4, NULL, NULL, NULL),
(45, 2, 39, NULL, 3, NULL, NULL, NULL),
(46, 2, 40, NULL, NULL, NULL, NULL, NULL),
(47, 3, 1, 8, 5, NULL, NULL, NULL),
(48, 3, 5, 8, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(49, 3, 6, 8, 5, NULL, NULL, NULL),
(50, 3, 7, 8, 5, NULL, NULL, NULL),
(51, 3, 8, 8, 3, NULL, NULL, NULL),
(52, 3, 1, 28, 5, NULL, NULL, NULL),
(53, 3, 5, 28, 4, NULL, NULL, NULL),
(54, 3, 6, 28, 5, NULL, NULL, NULL),
(55, 3, 7, 28, 4, NULL, NULL, NULL),
(56, 3, 8, 28, 4, NULL, NULL, NULL),
(57, 3, 1, 42, 3, NULL, NULL, NULL),
(58, 3, 5, 42, 4, NULL, NULL, NULL),
(59, 3, 6, 42, 3, NULL, NULL, NULL),
(60, 3, 7, 42, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(61, 3, 8, 42, 4, NULL, NULL, NULL),
(62, 3, 33, NULL, 5, NULL, NULL, NULL),
(63, 3, 34, NULL, 4, NULL, NULL, NULL),
(64, 3, 35, NULL, 4, NULL, NULL, NULL),
(65, 3, 36, NULL, 4, NULL, NULL, NULL),
(66, 3, 37, NULL, 5, NULL, NULL, NULL),
(67, 3, 38, NULL, 4, NULL, NULL, NULL),
(68, 3, 39, NULL, 5, NULL, NULL, NULL),
(69, 3, 40, NULL, NULL, NULL, NULL, NULL),
(70, 4, 1, 39, 5, NULL, NULL, NULL),
(71, 4, 5, 39, 5, NULL, NULL, NULL),
(72, 4, 6, 39, 3, NULL, NULL, NULL),
(73, 4, 7, 39, 4, NULL, NULL, NULL),
(74, 4, 8, 39, 4, NULL, NULL, NULL),
(75, 4, 1, 42, 3, NULL, NULL, NULL),
(76, 4, 5, 42, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(77, 4, 6, 42, 4, NULL, NULL, NULL),
(78, 4, 7, 42, 3, NULL, NULL, NULL),
(79, 4, 8, 42, 4, NULL, NULL, NULL),
(80, 4, 1, 47, 4, NULL, NULL, NULL),
(81, 4, 5, 47, 4, NULL, NULL, NULL),
(82, 4, 6, 47, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(83, 4, 7, 47, 4, NULL, NULL, NULL),
(84, 4, 8, 47, 5, NULL, NULL, 'Sangat baik, namun durasi praktik mungkin bisa diperbanyak.'),
(85, 4, 33, NULL, 3, NULL, NULL, NULL),
(86, 4, 34, NULL, 4, NULL, NULL, NULL),
(87, 4, 35, NULL, 4, NULL, NULL, NULL),
(88, 4, 36, NULL, 3, NULL, NULL, NULL),
(89, 4, 37, NULL, 4, NULL, NULL, NULL),
(90, 4, 38, NULL, 5, NULL, NULL, NULL),
(91, 4, 39, NULL, 4, NULL, NULL, NULL),
(92, 4, 40, NULL, NULL, NULL, NULL, NULL),
(93, 5, 4, 26, 3, NULL, NULL, NULL),
(94, 5, 20, 26, 4, NULL, NULL, NULL),
(95, 5, 23, 26, 5, NULL, NULL, NULL),
(96, 5, 24, 26, 3, NULL, NULL, NULL),
(97, 5, 4, 34, 3, NULL, NULL, NULL),
(98, 5, 20, 34, 5, NULL, NULL, NULL),
(99, 5, 23, 34, 5, NULL, NULL, NULL),
(100, 5, 24, 34, 5, NULL, NULL, NULL),
(101, 5, 4, 37, 5, NULL, NULL, NULL),
(102, 5, 20, 37, 5, NULL, NULL, NULL),
(103, 5, 23, 37, 3, NULL, NULL, NULL),
(104, 5, 24, 37, 5, NULL, NULL, NULL),
(105, 5, 63, NULL, 5, NULL, NULL, NULL),
(106, 5, 64, NULL, 4, NULL, NULL, NULL),
(107, 5, 65, NULL, 4, NULL, NULL, NULL),
(108, 5, 66, NULL, 3, NULL, NULL, NULL),
(109, 5, 67, NULL, 5, NULL, NULL, NULL),
(110, 5, 68, NULL, 4, NULL, NULL, NULL),
(111, 5, 69, NULL, 5, NULL, NULL, NULL),
(112, 5, 70, NULL, NULL, NULL, NULL, NULL),
(113, 6, 2, 29, 4, NULL, NULL, NULL),
(114, 6, 10, 29, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(115, 6, 11, 29, 5, NULL, NULL, NULL),
(116, 6, 12, 29, 4, NULL, NULL, NULL),
(117, 6, 13, 29, 4, NULL, NULL, NULL),
(118, 6, 2, 35, 4, NULL, NULL, NULL),
(119, 6, 10, 35, 3, NULL, NULL, NULL),
(120, 6, 11, 35, 4, NULL, NULL, NULL),
(121, 6, 12, 35, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(122, 6, 13, 35, 4, NULL, NULL, NULL),
(123, 6, 2, 37, 4, NULL, NULL, NULL),
(124, 6, 10, 37, 4, NULL, NULL, NULL),
(125, 6, 11, 37, 4, NULL, NULL, NULL),
(126, 6, 12, 37, 5, NULL, NULL, 'Sangat baik, namun durasi praktik mungkin bisa diperbanyak.'),
(127, 6, 13, 37, 4, NULL, NULL, NULL),
(128, 6, 43, NULL, 5, NULL, NULL, NULL),
(129, 6, 44, NULL, 5, NULL, NULL, NULL),
(130, 6, 45, NULL, 4, NULL, NULL, NULL),
(131, 6, 46, NULL, 5, NULL, NULL, NULL),
(132, 6, 47, NULL, 4, NULL, NULL, NULL),
(133, 6, 48, NULL, 5, NULL, NULL, NULL),
(134, 6, 49, NULL, NULL, NULL, NULL, NULL),
(135, 7, 2, 2, 4, NULL, NULL, NULL),
(136, 7, 10, 2, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(137, 7, 11, 2, 5, NULL, NULL, NULL),
(138, 7, 12, 2, 3, NULL, NULL, NULL),
(139, 7, 13, 2, 5, NULL, NULL, NULL),
(140, 7, 2, 22, 3, NULL, NULL, NULL),
(141, 7, 10, 22, 5, NULL, NULL, NULL),
(142, 7, 11, 22, 4, NULL, NULL, NULL),
(143, 7, 12, 22, 4, NULL, NULL, NULL),
(144, 7, 13, 22, 4, NULL, NULL, NULL),
(145, 7, 2, 37, 4, NULL, NULL, NULL),
(146, 7, 10, 37, 5, NULL, NULL, NULL),
(147, 7, 11, 37, 3, NULL, NULL, NULL),
(148, 7, 12, 37, 4, NULL, NULL, NULL),
(149, 7, 13, 37, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(150, 7, 43, NULL, 3, NULL, 'Kompetensi ini sangat penting. Lulusan diharapkan memiliki inisiatif tinggi.', NULL),
(151, 7, 44, NULL, 5, NULL, NULL, NULL),
(152, 7, 45, NULL, 4, NULL, 'Kompetensi ini sangat penting. Lulusan diharapkan memiliki inisiatif tinggi.', NULL),
(153, 7, 46, NULL, 4, NULL, 'Kompetensi ini sangat penting. Lulusan diharapkan memiliki inisiatif tinggi.', NULL),
(154, 7, 47, NULL, 3, NULL, NULL, NULL),
(155, 7, 48, NULL, 4, NULL, NULL, NULL),
(156, 7, 49, NULL, NULL, NULL, NULL, NULL),
(157, 8, 3, 14, 4, NULL, NULL, NULL),
(158, 8, 15, 14, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(159, 8, 16, 14, 4, NULL, NULL, NULL),
(160, 8, 17, 14, 4, NULL, NULL, NULL),
(161, 8, 3, 23, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(162, 8, 15, 23, 5, NULL, NULL, NULL),
(163, 8, 16, 23, 5, NULL, NULL, NULL),
(164, 8, 17, 23, 5, NULL, NULL, NULL),
(165, 8, 3, 29, 5, NULL, NULL, NULL),
(166, 8, 15, 29, 5, NULL, NULL, NULL),
(167, 8, 16, 29, 3, NULL, NULL, NULL),
(168, 8, 17, 29, 3, NULL, NULL, 'Materi kurang *up-to-date* dengan kebutuhan industri saat ini.'),
(169, 8, 53, NULL, 3, NULL, NULL, NULL),
(170, 8, 54, NULL, 4, NULL, NULL, NULL),
(171, 8, 55, NULL, 4, NULL, 'Kompetensi ini sangat penting. Lulusan diharapkan memiliki inisiatif tinggi.', NULL),
(172, 8, 56, NULL, 5, NULL, NULL, NULL),
(173, 8, 57, NULL, 5, NULL, NULL, NULL),
(174, 8, 58, NULL, 5, NULL, NULL, NULL),
(175, 8, 59, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jawaban_multi`
--

CREATE TABLE `jawaban_multi` (
  `jawaban_id` int(11) NOT NULL,
  `opsi_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jawaban_multi`
--

INSERT INTO `jawaban_multi` (`jawaban_id`, `opsi_id`) VALUES
(23, 35),
(23, 38),
(46, 34),
(46, 35),
(69, 37),
(92, 37),
(92, 38),
(112, 49),
(112, 51),
(134, 40),
(134, 43),
(156, 41),
(175, 44);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_kompetensi`
--

CREATE TABLE `kategori_kompetensi` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_kompetensi`
--

INSERT INTO `kategori_kompetensi` (`id`, `nama`, `deskripsi`, `aktif`) VALUES
(1, 'K1 Data, Analitik & Kecerdasan Buatan', 'Pengelolaan data, analitik bisnis, data science, dan kecerdasan buatan.', 1),
(2, 'K2 Enterprise Systems & Transformasi Bisnis', 'Sistem ERP, arsitektur enterprise, e-bisnis, dan inovasi digital.', 1),
(3, 'K3 Rekayasa Perangkat Lunak & Pengembangan Aplikasi', 'Analisis kebutuhan, perancangan, pemrograman, pengujian, dan antarmuka pengguna.', 1),
(4, 'K4 Infrastruktur, Jaringan & Keamanan Siber', 'Infrastruktur TI, jaringan, dan keamanan sistem.', 1),
(5, 'K5 Manajemen Proyek, Tata Kelola & Audit TI', 'Manajemen proyek, strategi, risiko, layanan, dan audit TI.', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_impor`
--

CREATE TABLE `log_impor` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `nama_berkas` varchar(255) NOT NULL,
  `jumlah_berhasil` int(11) DEFAULT 0,
  `jumlah_gagal` int(11) DEFAULT 0,
  `ringkasan_galat` text DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi_singkat` text DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `sks` int(11) NOT NULL,
  `jenis` enum('wajib','pilihan') DEFAULT 'wajib',
  `kelompok` enum('universitas','fakultas','prodi') DEFAULT 'prodi',
  `kurikulum` varchar(10) DEFAULT '2024',
  `aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id`, `kode`, `nama`, `deskripsi_singkat`, `semester`, `sks`, `jenis`, `kelompok`, `kurikulum`, `aktif`, `dibuat_pada`) VALUES
(1, 'SSI1140', 'Konsep dan Pengantar SI/TI', NULL, 1, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(2, 'SSI1141', 'Antar Muka dan Pengalaman Pengguna', NULL, 1, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(3, 'SSI1142', 'Konsep Basis Data', NULL, 1, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(4, 'SSI1143', 'Matematika Diskrit', NULL, 1, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(5, 'SSI1144', 'Algoritma dan Pengantar Pemrograman', NULL, 1, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(6, 'SSI1145', 'Praktikum Algoritma dan Pengantar Pemrograman', NULL, 1, 1, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(7, 'SSI1146', 'Desain dan Manajemen Proses Bisnis', NULL, 1, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(8, 'SSI1240', 'Bahasa Pemrograman', NULL, 2, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(9, 'SSI1241', 'Praktikum Bahasa Pemrograman', NULL, 2, 1, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(10, 'SSI1242', 'Sistem dan Administrasi Basis Data', NULL, 2, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(11, 'SSI1243', 'Analisa dan Kebutuhan Perangkat Lunak', NULL, 2, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(12, 'SSI1244', 'Manajemen dan Proses TI', NULL, 2, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(13, 'SSI1245', 'Pengantar Akuntansi dan Keuangan', NULL, 2, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(14, 'SSI2140', 'Statistika dan Probabilitas', NULL, 3, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(15, 'SSI2141', 'Praktikum Statistika dan Probabilitas', NULL, 3, 1, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(16, 'SSI2142', 'Arsitektur Enterprise', NULL, 3, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(17, 'SSI2143', 'Enterprise System', NULL, 3, 5, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(18, 'SSI2144', 'Desain dan Deskripsi Perangkat Lunak', NULL, 3, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(19, 'SSI2145', 'Perencanaan Strategis SI/TI', NULL, 3, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(20, 'SSI2146', 'E-Bisnis', NULL, 3, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(21, 'SSI2240', 'Manajemen Proyek Sistem Informasi', NULL, 4, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(22, 'SSI2241', 'Sistem Informasi Manajemen', NULL, 4, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(23, 'SSI2242', 'Manajemen Resiko SI/TI', NULL, 4, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(24, 'SSI2243', 'Keamanan Sistem Informasi', NULL, 4, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(25, 'SSI2244', 'Kecerdasan Bisnis', NULL, 4, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(26, 'SSI2245', 'Manajemen Layanan Teknologi Informasi', NULL, 4, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(27, 'SSI2246', 'Manajemen & Organisasi', NULL, 4, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(28, 'SSI3140', 'Pemrograman Bergerak', NULL, 5, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(29, 'SSI3141', 'Praktikum Pemrograman Bergerak', NULL, 5, 1, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(30, 'SSI3142', 'Transformasi Digital', NULL, 5, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(31, 'SSI3143', 'Testing dan Dokumentasi Perangkat Lunak', NULL, 5, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(32, 'SSI3144', 'Audit SI/TI', NULL, 5, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(33, 'SSI3145', 'Metodologi Penelitian dan Penulisan Ilmiah', NULL, 5, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(34, 'SSI3146', 'Teknologi Keuangan', NULL, 5, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(35, 'SSI3240', 'Desain dan Keamanan Jaringan', NULL, 6, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(36, 'SSI3241', 'Komunikasi dan Negosiasi', NULL, 6, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(37, 'SSI3244', 'Manajemen Data', NULL, 6, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(38, 'SSI3242', 'Etika Profesi dan Profesional', NULL, 6, 2, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(39, 'SSI3245', 'Pengukuran dan Kualitas Perangkat Lunak', NULL, 6, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(40, 'SSI4140', 'Proyek Pengembangan Sistem Informasi', NULL, 7, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(41, 'SSI4141', 'Kerja Praktek / Magang', NULL, 7, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(42, 'SSI4143', 'Technopreneurship', NULL, 7, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(43, 'SSI4144', 'Sistem Informasi Akuntansi', NULL, 7, 3, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(44, 'SSI4240', 'Tugas Akhir / Skripsi', NULL, 8, 6, 'wajib', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(45, 'SSI5130', 'Sistem Informasi Geografis', NULL, 7, 3, 'pilihan', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(46, 'SSI5131', 'Kualitas Data', NULL, 7, 3, 'pilihan', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(47, 'SSI5132', 'Sistem Informasi Manufaktur', NULL, 7, 3, 'pilihan', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(48, 'SSI5133', 'Pemrograman Game', NULL, 7, 3, 'pilihan', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(49, 'SSI5134', 'Pemrograman Robotika', NULL, 7, 3, 'pilihan', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(50, 'SSI5135', 'Sistem Pakar', NULL, 7, 3, 'pilihan', 'prodi', '2024', 1, '2026-10-05 09:27:29'),
(51, 'MPK403', 'Bahasa Inggris 1', NULL, 1, 1, 'wajib', 'universitas', '2024', 1, '2026-10-05 09:27:29'),
(52, 'MPK404', 'Bahasa Inggris 2', NULL, 2, 1, 'wajib', 'universitas', '2024', 1, '2026-10-05 09:27:29'),
(53, 'MPK01', 'Agama', NULL, 1, 2, 'wajib', 'universitas', '2024', 0, '2026-10-05 09:27:29'),
(54, 'MPK02', 'Pancasila', NULL, 1, 2, 'wajib', 'universitas', '2024', 0, '2026-10-05 09:27:29'),
(55, 'MPK03', 'Kewarganegaraan', NULL, 2, 2, 'wajib', 'universitas', '2024', 0, '2026-10-05 09:27:29'),
(56, 'MPK04', 'Bahasa Indonesia', NULL, 2, 2, 'wajib', 'universitas', '2024', 0, '2026-10-05 09:27:29'),
(57, 'MPK05', 'Pendidikan Karakter 1', NULL, 1, 1, 'wajib', 'universitas', '2024', 1, '2026-10-05 09:27:29'),
(58, 'MPK06', 'Pendidikan Karakter 2', NULL, 2, 1, 'wajib', 'universitas', '2024', 1, '2026-10-05 09:27:29'),
(59, 'FT01', 'Algoritma Pemrograman', NULL, 1, 3, 'wajib', 'fakultas', '2024', 1, '2026-10-05 09:27:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mata_kuliah_bk`
--

CREATE TABLE `mata_kuliah_bk` (
  `mata_kuliah_id` int(11) NOT NULL,
  `bahan_kajian_id` int(11) NOT NULL,
  `utama` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mata_kuliah_bk`
--

INSERT INTO `mata_kuliah_bk` (`mata_kuliah_id`, `bahan_kajian_id`, `utama`) VALUES
(1, 1, 0),
(2, 14, 0),
(3, 2, 0),
(4, 11, 0),
(5, 7, 0),
(6, 10, 0),
(7, 5, 0),
(8, 7, 0),
(9, 10, 0),
(10, 2, 0),
(11, 5, 0),
(12, 6, 0),
(13, 9, 0),
(14, 11, 0),
(15, 10, 0),
(16, 13, 0),
(17, 13, 0),
(18, 5, 0),
(19, 6, 0),
(20, 15, 0),
(21, 4, 0),
(22, 6, 0),
(23, 6, 0),
(24, 8, 0),
(25, 12, 0),
(26, 6, 0),
(27, 9, 0),
(28, 7, 0),
(29, 10, 0),
(30, 6, 0),
(31, 5, 0),
(32, 6, 0),
(33, 10, 0),
(34, 15, 0),
(35, 3, 1),
(35, 8, 0),
(36, 9, 0),
(37, 2, 0),
(38, 9, 0),
(39, 5, 0),
(40, 4, 0),
(41, 10, 0),
(42, 15, 0),
(43, 10, 0),
(44, 10, 0),
(45, 2, 0),
(46, 7, 0),
(47, 9, 0),
(48, 7, 0),
(49, 7, 0),
(50, 12, 0),
(59, 7, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `opsi_pertanyaan`
--

CREATE TABLE `opsi_pertanyaan` (
  `id` int(11) NOT NULL,
  `pertanyaan_id` int(11) NOT NULL,
  `teks_opsi` varchar(255) NOT NULL,
  `kategori_kompetensi_id` int(11) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `opsi_pertanyaan`
--

INSERT INTO `opsi_pertanyaan` (`id`, `pertanyaan_id`, `teks_opsi`, `kategori_kompetensi_id`, `urutan`, `aktif`) VALUES
(1, 18, 'Perlu diperdalam', NULL, 1, 1),
(2, 18, 'Sudah sesuai', NULL, 2, 1),
(3, 18, 'Cukup sebagai pengantar', NULL, 3, 1),
(4, 18, 'Tidak lagi diperlukan', NULL, 4, 1),
(5, 21, 'Sudah sesuai; cukup 1 semester dengan bobot SKS saat ini', NULL, 1, 1),
(6, 21, 'Bobot SKS perlu ditambah', NULL, 2, 1),
(7, 21, 'Bobot SKS perlu dikurangi', NULL, 3, 1),
(8, 21, 'Sebaiknya diperpanjang menjadi 2 semester (dasar dan lanjutan)', NULL, 4, 1),
(9, 26, 'Sebaiknya lebih awal', NULL, 1, 1),
(10, 26, 'Sudah tepat', NULL, 2, 1),
(11, 26, 'Sebaiknya lebih akhir', NULL, 3, 1),
(12, 27, 'Sebaiknya lebih awal', NULL, 1, 1),
(13, 27, 'Sudah tepat', NULL, 2, 1),
(14, 27, 'Sebaiknya lebih akhir', NULL, 3, 1),
(15, 28, 'Sebaiknya lebih awal', NULL, 1, 1),
(16, 28, 'Sudah tepat', NULL, 2, 1),
(17, 28, 'Sebaiknya lebih akhir', NULL, 3, 1),
(18, 29, 'Tetap wajib', NULL, 1, 1),
(19, 29, 'Dijadikan pilihan', NULL, 2, 1),
(20, 29, 'Digabung dengan mata kuliah lain', NULL, 3, 1),
(21, 29, 'Dihentikan', NULL, 4, 1),
(22, 30, 'Tetap wajib', NULL, 1, 1),
(23, 30, 'Dijadikan pilihan', NULL, 2, 1),
(24, 30, 'Digabung dengan mata kuliah lain', NULL, 3, 1),
(25, 30, 'Dihentikan', NULL, 4, 1),
(26, 31, 'Tetap wajib', NULL, 1, 1),
(27, 31, 'Dijadikan pilihan', NULL, 2, 1),
(28, 31, 'Digabung dengan mata kuliah lain', NULL, 3, 1),
(29, 31, 'Dihentikan', NULL, 4, 1),
(30, 32, 'Tetap wajib', NULL, 1, 1),
(31, 32, 'Dijadikan pilihan', NULL, 2, 1),
(32, 32, 'Digabung dengan mata kuliah lain', NULL, 3, 1),
(33, 32, 'Dihentikan', NULL, 4, 1),
(34, 40, 'K1 Data, Analitik & Kecerdasan Buatan', 1, 1, 1),
(35, 40, 'K2 Enterprise Systems & Transformasi Bisnis', 2, 2, 1),
(36, 40, 'K3 Rekayasa Perangkat Lunak & Pengembangan Aplikasi', 3, 3, 1),
(37, 40, 'K4 Infrastruktur, Jaringan & Keamanan Siber', 4, 4, 1),
(38, 40, 'K5 Manajemen Proyek, Tata Kelola & Audit TI', 5, 5, 1),
(39, 49, 'K1 Data, Analitik & Kecerdasan Buatan', 1, 1, 1),
(40, 49, 'K2 Enterprise Systems & Transformasi Bisnis', 2, 2, 1),
(41, 49, 'K3 Rekayasa Perangkat Lunak & Pengembangan Aplikasi', 3, 3, 1),
(42, 49, 'K4 Infrastruktur, Jaringan & Keamanan Siber', 4, 4, 1),
(43, 49, 'K5 Manajemen Proyek, Tata Kelola & Audit TI', 5, 5, 1),
(44, 59, 'K1 Data, Analitik & Kecerdasan Buatan', 1, 1, 1),
(45, 59, 'K2 Enterprise Systems & Transformasi Bisnis', 2, 2, 1),
(46, 59, 'K3 Rekayasa Perangkat Lunak & Pengembangan Aplikasi', 3, 3, 1),
(47, 59, 'K4 Infrastruktur, Jaringan & Keamanan Siber', 4, 4, 1),
(48, 59, 'K5 Manajemen Proyek, Tata Kelola & Audit TI', 5, 5, 1),
(49, 70, 'K1 Data, Analitik & Kecerdasan Buatan', 1, 1, 1),
(50, 70, 'K2 Enterprise Systems & Transformasi Bisnis', 2, 2, 1),
(51, 70, 'K3 Rekayasa Perangkat Lunak & Pengembangan Aplikasi', 3, 3, 1),
(52, 70, 'K4 Infrastruktur, Jaringan & Keamanan Siber', 4, 4, 1),
(53, 70, 'K5 Manajemen Proyek, Tata Kelola & Audit TI', 5, 5, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `peran` enum('admin','mahasiswa','alumni','perusahaan','dosen') NOT NULL,
  `wajib_ganti_sandi` tinyint(1) DEFAULT 1,
  `aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expired` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `email`, `password_hash`, `peran`, `wajib_ganti_sandi`, `aktif`, `dibuat_pada`, `reset_token`, `reset_token_expired`) VALUES
(1, 'Administrator Prodi', 'admin@machung.ac.id', '$2y$10$eCzqCB.io3lIaPnrwOOqPOIBoD4AMnI7teaR0k9dV3WD9d/0lIjXe', 'admin', 0, 1, '2026-10-05 09:27:29', NULL, NULL),
(2, 'Carlo Imanuel Suryahasilaga', '322310001@student.machung.ac.id', '$2y$10$7LaIXuwrzjIwpPbB/zKzwueg1zZQPTceKrQj6c1FzA3sauBK92LXC', 'mahasiswa', 0, 1, '2026-10-05 09:27:29', NULL, NULL),
(3, 'William Christopher Linardi', '322310020@student.machung.ac.id', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'mahasiswa', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(4, 'Kurniawan Michael Bimantara', '322310013@student.machung.ac.id', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'mahasiswa', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(5, 'Marcell Chandra Kenchana', '322310015@student.machung.ac.id', '$2y$10$sPS4SP3FY3YmPAptFiZEj.njN47JDOdCMpdARvBEPdrgC5rHsGOhS', 'mahasiswa', 0, 1, '2026-10-05 09:27:29', NULL, NULL),
(6, 'Daniel Saputra', 'daniel.saputra@machung.ac.id', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'dosen', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(7, 'Budi Santoso', 'budi.santoso@machung.ac.id', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'dosen', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(8, 'Siti Aminah', 'siti.aminah@machung.ac.id', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'dosen', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(9, 'Rina Wijaya', 'rina.wijaya@machung.ac.id', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'dosen', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(10, 'Andi Gunawan', 'andi.alumni@gmail.com', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'alumni', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(11, 'Reza Pratama', 'reza.alumni@yahoo.com', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'alumni', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(12, 'Maya Sari', 'maya.alumni@hotmail.com', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'alumni', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(13, 'Kevin Sanjaya', 'kevin.alumni@gmail.com', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'alumni', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(14, 'HRD PT Teknologi Maju', 'hrd@tekmaju.com', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'perusahaan', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(15, 'Recruiter Tokopedia', 'recruiter@tokopedia.com', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'perusahaan', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(16, 'Talent Gojek', 'talent@gojek.com', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'perusahaan', 1, 1, '2026-10-05 09:27:29', NULL, NULL),
(17, 'HR Bank BCA', 'hr@bca.co.id', '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO', 'perusahaan', 1, 1, '2026-10-05 09:27:29', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengisian`
--

CREATE TABLE `pengisian` (
  `id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `status` enum('draft','final') DEFAULT 'draft',
  `tab_terakhir` int(11) DEFAULT 1,
  `terakhir_disimpan` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `waktu_submit` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengisian`
--

INSERT INTO `pengisian` (`id`, `pengguna_id`, `periode_id`, `status`, `tab_terakhir`, `terakhir_disimpan`, `waktu_submit`) VALUES
(1, 2, 1, 'final', 2, '2026-10-05 10:32:13', '2025-06-06 03:30:00'),
(2, 3, 1, 'final', 2, '2025-06-06 03:30:00', '2025-06-06 03:30:00'),
(3, 4, 1, 'final', 2, '2025-06-08 03:30:00', '2025-06-08 03:30:00'),
(4, 5, 1, 'final', 2, '2025-06-02 03:30:00', '2025-06-02 03:30:00'),
(5, 9, 1, 'final', 2, '2025-06-07 03:30:00', '2025-06-07 03:30:00'),
(6, 10, 1, 'final', 2, '2025-06-08 03:30:00', '2025-06-08 03:30:00'),
(7, 13, 1, 'final', 2, '2025-06-06 03:30:00', '2025-06-06 03:30:00'),
(8, 17, 1, 'final', 2, '2025-06-05 03:30:00', '2025-06-05 03:30:00'),
(25, 5, 2, 'draft', 2, '2026-10-05 15:00:17', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengisian_matkul`
--

CREATE TABLE `pengisian_matkul` (
  `id` int(11) NOT NULL,
  `pengisian_id` int(11) NOT NULL,
  `mata_kuliah_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengisian_matkul`
--

INSERT INTO `pengisian_matkul` (`id`, `pengisian_id`, `mata_kuliah_id`) VALUES
(1, 1, 5),
(2, 1, 27),
(3, 1, 31),
(4, 2, 10),
(5, 2, 27),
(6, 2, 44),
(7, 3, 8),
(8, 3, 28),
(9, 3, 42),
(10, 4, 39),
(11, 4, 42),
(12, 4, 47),
(13, 5, 26),
(14, 5, 34),
(15, 5, 37),
(16, 6, 29),
(17, 6, 35),
(18, 6, 37),
(19, 7, 2),
(20, 7, 22),
(21, 7, 37),
(22, 8, 14),
(23, 8, 23),
(24, 8, 29),
(73, 25, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `periode_evaluasi`
--

CREATE TABLE `periode_evaluasi` (
  `id` int(11) NOT NULL,
  `tahun_akademik` varchar(50) DEFAULT NULL,
  `status` enum('draft','buka','tutup') DEFAULT 'draft',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `periode_evaluasi`
--

INSERT INTO `periode_evaluasi` (`id`, `tahun_akademik`, `status`, `dibuat_pada`) VALUES
(1, '2024/2025 Genap', 'tutup', '2026-10-05 09:27:29'),
(2, '2025/2026 Ganjil', 'buka', '2026-10-05 09:27:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pertanyaan`
--

CREATE TABLE `pertanyaan` (
  `id` int(11) NOT NULL,
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
  `untuk_peringkat` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pertanyaan`
--

INSERT INTO `pertanyaan` (`id`, `kode`, `teks`, `bantuan`, `tipe`, `bagian`, `peran_sasaran`, `kategori_kompetensi_id`, `wajib`, `label_skala`, `syarat_tampil`, `penanda`, `maks_pilihan`, `urutan`, `aktif`, `default_aktif`, `dibuat_pada`, `alasan_jika_rendah`, `untuk_peringkat`) VALUES
(1, 'A1', 'Mata kuliah ini layak dipertahankan dalam kurikulum program studi.', 'Nilai 1 atau 2 memerlukan alasan.', 'skala', 'matkul', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, 'untuk_peringkat', NULL, 1, 1, 1, '2026-10-05 09:27:29', 1, 1),
(2, 'A1', 'Mata kuliah ini layak dipertahankan dalam kurikulum program studi.', 'Nilai 1 atau 2 memerlukan alasan.', 'skala', 'matkul', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, 'untuk_peringkat', NULL, 1, 1, 1, '2026-10-05 09:27:29', 1, 1),
(3, 'A1', 'Mata kuliah ini layak dipertahankan dalam kurikulum program studi.', 'Nilai 1 atau 2 memerlukan alasan.', 'skala', 'matkul', 'perusahaan', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, 'untuk_peringkat', NULL, 1, 1, 1, '2026-10-05 09:27:29', 1, 1),
(4, 'A1', 'Mata kuliah ini layak dipertahankan dalam kurikulum program studi.', 'Nilai 1 atau 2 memerlukan alasan.', 'skala', 'matkul', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, 'untuk_peringkat', NULL, 1, 1, 1, '2026-10-05 09:27:29', 1, 1),
(5, 'A2', 'Seberapa mudah materi mata kuliah ini untuk dipahami?', 'Nilai berdasarkan penjelasan, bahan ajar, dan tugas.', 'skala', 'matkul', 'mahasiswa', NULL, 1, '[\"Sangat sulit\", \"Sulit\", \"Cukup\", \"Mudah\", \"Sangat mudah\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(6, 'A3', 'Beban materi dan tugas pada mata kuliah ini sebanding dengan bobot SKS-nya.', NULL, 'skala', 'matkul', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(7, 'A4', 'Setelah mengikuti mata kuliah ini, saya memahami konsep dasar bidangnya.', NULL, 'skala', 'matkul', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(8, 'A5', 'Mata kuliah ini membantu saya mengikuti mata kuliah lanjutan.', 'Pilih Netral bila Anda belum mengambil mata kuliah lanjutannya.', 'skala', 'matkul', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(9, 'A6', 'Saran atau catatan Anda untuk mata kuliah ini.', 'Misalnya bagian yang terasa terlalu sulit atau terlalu mudah.', 'teks', 'matkul', 'mahasiswa', NULL, 0, NULL, NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(10, 'A2', 'Seberapa berguna materi mata kuliah ini dalam pekerjaan Anda saat ini?', 'Nilai berdasarkan pengalaman kerja, bukan kesan saat kuliah.', 'skala', 'matkul', 'alumni', NULL, 1, '[\"Tidak berguna\", \"Kurang berguna\", \"Cukup berguna\", \"Berguna\", \"Sangat berguna\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(11, 'A3', 'Seberapa sering Anda menggunakan materi mata kuliah ini dalam pekerjaan?', NULL, 'skala', 'matkul', 'alumni', NULL, 1, '[\"Tidak pernah\", \"Jarang\", \"Kadang-kadang\", \"Sering\", \"Sangat sering\"]', NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(12, 'A4', 'Kedalaman materi mata kuliah ini cukup sebagai bekal bekerja.', NULL, 'skala', 'matkul', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(13, 'A5', 'Materi mata kuliah ini sejalan dengan praktik yang berlaku di tempat kerja Anda.', NULL, 'skala', 'matkul', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(14, 'A6', 'Saran atau catatan Anda untuk mata kuliah ini.', 'Misalnya materi yang perlu ditambah atau dikurangi.', 'teks', 'matkul', 'alumni', NULL, 0, NULL, NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(15, 'A2', 'Seberapa sesuai bidang yang dicakup mata kuliah ini dengan kebutuhan kerja di perusahaan Anda saat ini?', 'Bila nama mata kuliahnya kurang dikenal, gunakan keterangan Bahan Kajian pada kartu.', 'skala', 'matkul', 'perusahaan', NULL, 1, '[\"Tidak sesuai\", \"Kurang sesuai\", \"Cukup sesuai\", \"Sesuai\", \"Sangat sesuai\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(16, 'A3', 'Lulusan yang menguasai bidang ini lebih siap bekerja di perusahaan kami.', NULL, 'skala', 'matkul', 'perusahaan', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(17, 'A4', 'Bidang ini tetap dibutuhkan perusahaan kami dalam 3 sampai 5 tahun ke depan.', NULL, 'skala', 'matkul', 'perusahaan', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(18, 'A5', 'Bagaimana sebaiknya porsi bidang ini dalam kurikulum?', NULL, 'pilihan_ganda', 'matkul', 'perusahaan', NULL, 1, NULL, NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(19, 'A6', 'Keahlian terkait yang menurut Anda belum tercakup oleh mata kuliah ini.', NULL, 'teks', 'matkul', 'perusahaan', NULL, 0, NULL, NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(20, 'A2', 'Seberapa mutakhir silabus dan materi mata kuliah ini dibandingkan kebutuhan industri saat ini?', 'Bandingkan dengan perkembangan teknologi dan praktik industri terbaru.', 'skala', 'matkul', 'dosen', NULL, 1, '[\"Sangat tertinggal\", \"Tertinggal\", \"Cukup mutakhir\", \"Mutakhir\", \"Sangat mutakhir\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(21, 'A3', 'Mata kuliah ini saat ini berbobot {sks} SKS. Bagaimana rekomendasi Anda terhadap bobot SKS dan durasinya?', 'Pertimbangkan kedalaman materi dan jam tatap muka.', 'pilihan_ganda', 'matkul', 'dosen', NULL, 1, NULL, NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(22, 'A4', 'Berapa usulan bobot atau pembagian semesternya, dan apa alasannya?', NULL, 'teks', 'matkul', 'dosen', NULL, 0, NULL, 'A3_BUKAN_OPSI_1', NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(23, 'A5', 'Materi mata kuliah ini selaras dengan capaian pembelajaran dan mata kuliah lanjutannya.', NULL, 'skala', 'matkul', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(24, 'A6', 'Mahasiswa umumnya memiliki bekal awal yang cukup untuk mengikuti mata kuliah ini.', NULL, 'skala', 'matkul', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(25, 'A7', 'Bab, teori, atau studi kasus yang sebaiknya diperbarui atau dihapus.', 'Boleh dikosongkan bila materi sudah sesuai.', 'teks', 'matkul', 'dosen', NULL, 0, NULL, NULL, NULL, NULL, 7, 1, 1, '2026-10-05 09:27:29', 0, 0),
(26, 'AX1', 'Menurut Anda, posisi semester mata kuliah ini sudah tepat?', NULL, 'pilihan_ganda', 'matkul', 'mahasiswa', NULL, 1, NULL, NULL, NULL, NULL, 90, 0, 0, '2026-10-05 09:27:29', 0, 0),
(27, 'AX1', 'Menurut Anda, posisi semester mata kuliah ini sudah tepat?', NULL, 'pilihan_ganda', 'matkul', 'alumni', NULL, 1, NULL, NULL, NULL, NULL, 90, 0, 0, '2026-10-05 09:27:29', 0, 0),
(28, 'AX1', 'Menurut Anda, posisi semester mata kuliah ini sudah tepat?', NULL, 'pilihan_ganda', 'matkul', 'dosen', NULL, 1, NULL, NULL, NULL, NULL, 90, 0, 0, '2026-10-05 09:27:29', 0, 0),
(29, 'AX2', 'Bagaimana status mata kuliah ini sebaiknya dalam kurikulum?', NULL, 'pilihan_ganda', 'matkul', 'mahasiswa', NULL, 1, NULL, NULL, NULL, NULL, 91, 0, 0, '2026-10-05 09:27:29', 0, 0),
(30, 'AX2', 'Bagaimana status mata kuliah ini sebaiknya dalam kurikulum?', NULL, 'pilihan_ganda', 'matkul', 'alumni', NULL, 1, NULL, NULL, NULL, NULL, 91, 0, 0, '2026-10-05 09:27:29', 0, 0),
(31, 'AX2', 'Bagaimana status mata kuliah ini sebaiknya dalam kurikulum?', NULL, 'pilihan_ganda', 'matkul', 'perusahaan', NULL, 1, NULL, NULL, NULL, NULL, 91, 0, 0, '2026-10-05 09:27:29', 0, 0),
(32, 'AX2', 'Bagaimana status mata kuliah ini sebaiknya dalam kurikulum?', NULL, 'pilihan_ganda', 'matkul', 'dosen', NULL, 1, NULL, NULL, NULL, NULL, 91, 0, 0, '2026-10-05 09:27:29', 0, 0),
(33, 'B1', 'Seberapa mudah Anda beradaptasi dengan cara dan beban belajar di program studi ini?', 'Pikirkan pengalaman Anda secara keseluruhan.', 'skala', 'kompetensi', 'mahasiswa', NULL, 1, '[\"Sangat sulit\", \"Sulit\", \"Cukup\", \"Mudah\", \"Sangat mudah\"]', NULL, NULL, NULL, 1, 1, 1, '2026-10-05 09:27:29', 0, 0),
(34, 'B2', 'Urutan mata kuliah dari semester ke semester membantu saya memahami materi secara bertahap.', NULL, 'skala', 'kompetensi', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(35, 'B3', 'Kurikulum menyeimbangkan teori dan praktik dengan baik.', NULL, 'skala', 'kompetensi', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(36, 'B4', 'Kurikulum membantu saya menyiapkan diri untuk bekerja di bidang SI/TI.', NULL, 'skala', 'kompetensi', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(37, 'B5', 'Saya memahami keterkaitan antar mata kuliah di program studi ini.', NULL, 'skala', 'kompetensi', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(38, 'B6', 'Beban belajar per semester (jumlah SKS dan tugas) masih dapat saya tangani.', NULL, 'skala', 'kompetensi', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(39, 'B7', 'Saya percaya diri dengan kemampuan teknis yang saya peroleh sejauh ini.', NULL, 'skala', 'kompetensi', 'mahasiswa', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 7, 1, 1, '2026-10-05 09:27:29', 0, 0),
(40, 'B8', 'Bidang kompetensi mana yang paling ingin Anda perdalam?', 'Pilih paling banyak 2.', 'pilihan_ganda_banyak', 'kompetensi', 'mahasiswa', NULL, 1, NULL, NULL, NULL, 2, 8, 1, 1, '2026-10-05 09:27:29', 0, 0),
(41, 'B9', 'Mata kuliah atau topik yang menurut Anda perlu ditambahkan ke kurikulum.', NULL, 'teks', 'kompetensi', 'mahasiswa', NULL, 0, NULL, NULL, NULL, NULL, 9, 1, 1, '2026-10-05 09:27:29', 0, 0),
(42, 'B10', 'Saran lain untuk kurikulum program studi.', NULL, 'teks', 'kompetensi', 'mahasiswa', NULL, 0, NULL, NULL, NULL, NULL, 10, 1, 1, '2026-10-05 09:27:29', 0, 0),
(43, 'B1', 'Secara keseluruhan, seberapa relevan kurikulum program studi dengan tuntutan pekerjaan Anda saat ini?', 'Pikirkan seluruh bekal yang Anda dapat selama kuliah.', 'skala', 'kompetensi', 'alumni', NULL, 1, '[\"Sangat tidak relevan\", \"Kurang relevan\", \"Cukup relevan\", \"Relevan\", \"Sangat relevan\"]', NULL, NULL, NULL, 1, 1, 1, '2026-10-05 09:27:29', 0, 0),
(44, 'B2', 'Bekal kemampuan teknis (hardskill) dari kampus cukup untuk memulai pekerjaan pertama saya.', NULL, 'skala', 'kompetensi', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(45, 'B3', 'Bekal kemampuan non-teknis (softskill) dari kampus cukup untuk memulai pekerjaan pertama saya.', NULL, 'skala', 'kompetensi', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(46, 'B4', 'Saya dapat beradaptasi dengan cepat di lingkungan kerja berkat bekal dari kampus.', NULL, 'skala', 'kompetensi', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(47, 'B5', 'Kurikulum memberi dasar yang kuat bagi saya untuk terus mempelajari teknologi baru.', NULL, 'skala', 'kompetensi', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(48, 'B6', 'Kerja praktik, magang, dan proyek selama kuliah membantu kesiapan kerja saya.', NULL, 'skala', 'kompetensi', 'alumni', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(49, 'B7', 'Kelompok kompetensi apa yang paling sering Anda gunakan dalam pekerjaan?', 'Pilih paling banyak 2 yang paling dominan.', 'pilihan_ganda_banyak', 'kompetensi', 'alumni', NULL, 1, NULL, NULL, 'WARNA_PRODI', 2, 7, 1, 1, '2026-10-05 09:27:29', 0, 0),
(50, 'B8', 'Materi yang dulu terasa kurang penting, tetapi ternyata berguna di dunia kerja.', 'Sebutkan materi atau mata kuliahnya.', 'teks', 'kompetensi', 'alumni', NULL, 0, NULL, NULL, NULL, NULL, 8, 1, 1, '2026-10-05 09:27:29', 0, 0),
(51, 'B9', 'Teknologi atau materi yang menurut Anda sudah usang dan perlu diperbarui.', NULL, 'teks', 'kompetensi', 'alumni', NULL, 0, NULL, NULL, NULL, NULL, 9, 1, 1, '2026-10-05 09:27:29', 0, 0),
(52, 'B10', 'Saran lain untuk kurikulum program studi.', NULL, 'teks', 'kompetensi', 'alumni', NULL, 0, NULL, NULL, NULL, NULL, 10, 1, 1, '2026-10-05 09:27:29', 0, 0),
(53, 'B1', 'Seberapa puas Anda dengan kemampuan teknis (hardskill) lulusan program studi kami?', 'Nilai berdasarkan lulusan yang Anda ketahui langsung.', 'skala', 'kompetensi', 'perusahaan', NULL, 1, '[\"Sangat tidak puas\", \"Tidak puas\", \"Cukup puas\", \"Puas\", \"Sangat puas\"]', NULL, NULL, NULL, 1, 1, 1, '2026-10-05 09:27:29', 0, 0),
(54, 'B2', 'Bagaimana penilaian Anda terhadap kemampuan non-teknis (softskill) lulusan kami, seperti komunikasi, inisiatif, etika, dan kerja sama tim?', NULL, 'skala', 'kompetensi', 'perusahaan', NULL, 1, '[\"Sangat kurang\", \"Kurang\", \"Cukup\", \"Baik\", \"Sangat baik\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(55, 'B3', 'Lulusan kami mampu beradaptasi dengan cepat di lingkungan kerja.', NULL, 'skala', 'kompetensi', 'perusahaan', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(56, 'B4', 'Lulusan kami menguasai dasar-dasar SI/TI yang dibutuhkan perusahaan.', NULL, 'skala', 'kompetensi', 'perusahaan', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(57, 'B5', 'Lulusan kami mampu menjembatani kebutuhan bisnis dan solusi teknologi.', NULL, 'skala', 'kompetensi', 'perusahaan', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(58, 'B6', 'Bagaimana kemampuan lulusan kami mempelajari teknologi baru di tempat kerja?', NULL, 'skala', 'kompetensi', 'perusahaan', NULL, 1, '[\"Sangat kurang\", \"Kurang\", \"Cukup\", \"Baik\", \"Sangat baik\"]', NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(59, 'B7', 'Kompetensi apa yang paling Anda butuhkan dari lulusan IT dalam beberapa tahun ke depan?', 'Pilih paling banyak 2 yang paling Anda prioritaskan. Jawaban ini membentuk arah kurikulum program studi.', 'pilihan_ganda_banyak', 'kompetensi', 'perusahaan', NULL, 1, NULL, NULL, 'WARNA_PRODI', 2, 7, 1, 1, '2026-10-05 09:27:29', 0, 0),
(60, 'B8', 'Kesenjangan kompetensi (skill gap) yang paling nyata pada lulusan kami saat mulai bekerja.', 'Sebutkan contoh yang Anda temui.', 'teks', 'kompetensi', 'perusahaan', NULL, 0, NULL, NULL, NULL, NULL, 8, 1, 1, '2026-10-05 09:27:29', 0, 0),
(61, 'B9', 'Perangkat lunak, framework, atau standar yang sebaiknya diajarkan agar lulusan langsung siap pakai di perusahaan Anda.', 'Sebutkan nama spesifiknya.', 'teks', 'kompetensi', 'perusahaan', NULL, 0, NULL, NULL, NULL, NULL, 9, 1, 1, '2026-10-05 09:27:29', 0, 0),
(62, 'B10', 'Masukan lain untuk kurikulum program studi.', NULL, 'teks', 'kompetensi', 'perusahaan', NULL, 0, NULL, NULL, NULL, NULL, 10, 1, 1, '2026-10-05 09:27:29', 0, 0),
(63, 'B1', 'Kurikulum program studi saat ini selaras dengan perkembangan kebutuhan industri.', NULL, 'skala', 'kompetensi', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 1, 1, 1, '2026-10-05 09:27:29', 0, 0),
(64, 'B2', 'Capaian pembelajaran lulusan sudah tercermin dalam rangkaian mata kuliah.', NULL, 'skala', 'kompetensi', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 2, 1, 1, '2026-10-05 09:27:29', 0, 0),
(65, 'B3', 'Urutan mata kuliah antar semester sudah logis dan bertahap.', NULL, 'skala', 'kompetensi', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 3, 1, 1, '2026-10-05 09:27:29', 0, 0),
(66, 'B4', 'Keseimbangan antara teori dan praktik dalam kurikulum sudah baik.', NULL, 'skala', 'kompetensi', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 4, 1, 1, '2026-10-05 09:27:29', 0, 0),
(67, 'B5', 'Tidak ada tumpang tindih materi yang berarti antar mata kuliah.', NULL, 'skala', 'kompetensi', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 5, 1, 1, '2026-10-05 09:27:29', 0, 0),
(68, 'B6', 'Beban SKS per semester sudah proporsional.', NULL, 'skala', 'kompetensi', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 6, 1, 1, '2026-10-05 09:27:29', 0, 0),
(69, 'B7', 'Kurikulum memberi ruang yang cukup bagi mahasiswa untuk mendalami minat melalui mata kuliah pilihan.', NULL, 'skala', 'kompetensi', 'dosen', NULL, 1, '[\"Sangat tidak setuju\", \"Tidak setuju\", \"Netral\", \"Setuju\", \"Sangat setuju\"]', NULL, NULL, NULL, 7, 1, 1, '2026-10-05 09:27:29', 0, 0),
(70, 'B8', 'Bidang apa yang sebaiknya diperkuat dalam kurikulum?', NULL, 'pilihan_ganda_banyak', 'kompetensi', 'dosen', NULL, 1, NULL, NULL, NULL, 2, 8, 1, 1, '2026-10-05 09:27:29', 0, 0),
(71, 'B9', 'Usulan mata kuliah baru atau perubahan struktur kurikulum.', NULL, 'teks', 'kompetensi', 'dosen', NULL, 0, NULL, NULL, NULL, NULL, 9, 1, 1, '2026-10-05 09:27:29', 0, 0),
(72, 'B10', 'Saran lain untuk kurikulum program studi.', NULL, 'teks', 'kompetensi', 'dosen', NULL, 0, NULL, NULL, NULL, NULL, 10, 1, 1, '2026-10-05 09:27:29', 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `profil_pengguna`
--

CREATE TABLE `profil_pengguna` (
  `pengguna_id` int(11) NOT NULL,
  `nomor_induk` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `posisi_pekerjaan` varchar(100) DEFAULT NULL,
  `instansi` varchar(100) DEFAULT NULL,
  `tahun_lulus` year(4) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `profil_pengguna`
--

INSERT INTO `profil_pengguna` (`pengguna_id`, `nomor_induk`, `alamat`, `telepon`, `posisi_pekerjaan`, `instansi`, `tahun_lulus`, `foto`) VALUES
(1, 'ADM-001', '', '', '', '', NULL, NULL),
(2, '322310001', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(3, '322310020', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(4, '322310013', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(5, '322310015', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(6, '0712038401', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(7, '0712038402', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(8, '0712038403', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(9, '0712038404', NULL, NULL, NULL, 'Universitas Ma Chung', NULL, NULL),
(10, NULL, NULL, NULL, NULL, 'PT Teknologi Maju', '2021', NULL),
(11, NULL, NULL, NULL, NULL, 'Tokopedia', '2022', NULL),
(12, NULL, NULL, NULL, NULL, 'Gojek', '2020', NULL),
(13, NULL, NULL, NULL, NULL, 'Bank BCA', '2023', NULL),
(14, NULL, NULL, NULL, NULL, 'PT Teknologi Maju', NULL, NULL),
(15, NULL, NULL, NULL, NULL, 'Tokopedia', NULL, NULL),
(16, NULL, NULL, NULL, NULL, 'Gojek', NULL, NULL),
(17, NULL, NULL, NULL, NULL, 'Bank BCA', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tiket`
--

CREATE TABLE `tiket` (
  `id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `subjek` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `status` enum('baru','diproses','selesai') DEFAULT 'baru',
  `balasan_admin` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bahan_kajian`
--
ALTER TABLE `bahan_kajian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `kategori_kompetensi_id` (`kategori_kompetensi_id`);

--
-- Indeks untuk tabel `jawaban`
--
ALTER TABLE `jawaban`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_jawaban` (`pengisian_id`,`pertanyaan_id`,`mata_kuliah_key`),
  ADD KEY `pengisian_id` (`pengisian_id`),
  ADD KEY `pertanyaan_id` (`pertanyaan_id`),
  ADD KEY `mata_kuliah_id` (`mata_kuliah_id`),
  ADD KEY `opsi_id` (`opsi_id`);

--
-- Indeks untuk tabel `jawaban_multi`
--
ALTER TABLE `jawaban_multi`
  ADD PRIMARY KEY (`jawaban_id`,`opsi_id`),
  ADD KEY `opsi_id` (`opsi_id`);

--
-- Indeks untuk tabel `kategori_kompetensi`
--
ALTER TABLE `kategori_kompetensi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `log_impor`
--
ALTER TABLE `log_impor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indeks untuk tabel `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indeks untuk tabel `mata_kuliah_bk`
--
ALTER TABLE `mata_kuliah_bk`
  ADD PRIMARY KEY (`mata_kuliah_id`,`bahan_kajian_id`),
  ADD KEY `bahan_kajian_id` (`bahan_kajian_id`);

--
-- Indeks untuk tabel `opsi_pertanyaan`
--
ALTER TABLE `opsi_pertanyaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pertanyaan_id` (`pertanyaan_id`),
  ADD KEY `kategori_kompetensi_id` (`kategori_kompetensi_id`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `pengisian`
--
ALTER TABLE `pengisian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengguna_id` (`pengguna_id`,`periode_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indeks untuk tabel `pengisian_matkul`
--
ALTER TABLE `pengisian_matkul`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengisian_id` (`pengisian_id`,`mata_kuliah_id`),
  ADD KEY `mata_kuliah_id` (`mata_kuliah_id`);

--
-- Indeks untuk tabel `periode_evaluasi`
--
ALTER TABLE `periode_evaluasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pertanyaan`
--
ALTER TABLE `pertanyaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_kompetensi_id` (`kategori_kompetensi_id`);

--
-- Indeks untuk tabel `profil_pengguna`
--
ALTER TABLE `profil_pengguna`
  ADD PRIMARY KEY (`pengguna_id`);

--
-- Indeks untuk tabel `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bahan_kajian`
--
ALTER TABLE `bahan_kajian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `jawaban`
--
ALTER TABLE `jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT untuk tabel `kategori_kompetensi`
--
ALTER TABLE `kategori_kompetensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `log_impor`
--
ALTER TABLE `log_impor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `opsi_pertanyaan`
--
ALTER TABLE `opsi_pertanyaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `pengisian`
--
ALTER TABLE `pengisian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `pengisian_matkul`
--
ALTER TABLE `pengisian_matkul`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT untuk tabel `periode_evaluasi`
--
ALTER TABLE `periode_evaluasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pertanyaan`
--
ALTER TABLE `pertanyaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT untuk tabel `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bahan_kajian`
--
ALTER TABLE `bahan_kajian`
  ADD CONSTRAINT `bahan_kajian_ibfk_1` FOREIGN KEY (`kategori_kompetensi_id`) REFERENCES `kategori_kompetensi` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `jawaban`
--
ALTER TABLE `jawaban`
  ADD CONSTRAINT `jawaban_ibfk_1` FOREIGN KEY (`pengisian_id`) REFERENCES `pengisian` (`id`),
  ADD CONSTRAINT `jawaban_ibfk_2` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan` (`id`),
  ADD CONSTRAINT `jawaban_ibfk_3` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`),
  ADD CONSTRAINT `jawaban_ibfk_4` FOREIGN KEY (`opsi_id`) REFERENCES `opsi_pertanyaan` (`id`);

--
-- Ketidakleluasaan untuk tabel `jawaban_multi`
--
ALTER TABLE `jawaban_multi`
  ADD CONSTRAINT `jawaban_multi_ibfk_1` FOREIGN KEY (`jawaban_id`) REFERENCES `jawaban` (`id`),
  ADD CONSTRAINT `jawaban_multi_ibfk_2` FOREIGN KEY (`opsi_id`) REFERENCES `opsi_pertanyaan` (`id`);

--
-- Ketidakleluasaan untuk tabel `log_impor`
--
ALTER TABLE `log_impor`
  ADD CONSTRAINT `log_impor_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `mata_kuliah_bk`
--
ALTER TABLE `mata_kuliah_bk`
  ADD CONSTRAINT `mata_kuliah_bk_ibfk_1` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`),
  ADD CONSTRAINT `mata_kuliah_bk_ibfk_2` FOREIGN KEY (`bahan_kajian_id`) REFERENCES `bahan_kajian` (`id`);

--
-- Ketidakleluasaan untuk tabel `opsi_pertanyaan`
--
ALTER TABLE `opsi_pertanyaan`
  ADD CONSTRAINT `opsi_pertanyaan_ibfk_1` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan` (`id`),
  ADD CONSTRAINT `opsi_pertanyaan_ibfk_2` FOREIGN KEY (`kategori_kompetensi_id`) REFERENCES `kategori_kompetensi` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengisian`
--
ALTER TABLE `pengisian`
  ADD CONSTRAINT `pengisian_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `pengisian_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periode_evaluasi` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengisian_matkul`
--
ALTER TABLE `pengisian_matkul`
  ADD CONSTRAINT `pengisian_matkul_ibfk_1` FOREIGN KEY (`pengisian_id`) REFERENCES `pengisian` (`id`),
  ADD CONSTRAINT `pengisian_matkul_ibfk_2` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`);

--
-- Ketidakleluasaan untuk tabel `pertanyaan`
--
ALTER TABLE `pertanyaan`
  ADD CONSTRAINT `pertanyaan_ibfk_1` FOREIGN KEY (`kategori_kompetensi_id`) REFERENCES `kategori_kompetensi` (`id`);

--
-- Ketidakleluasaan untuk tabel `profil_pengguna`
--
ALTER TABLE `profil_pengguna`
  ADD CONSTRAINT `profil_pengguna_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `tiket`
--
ALTER TABLE `tiket`
  ADD CONSTRAINT `tiket_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
