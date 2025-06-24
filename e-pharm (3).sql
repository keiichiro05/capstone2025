-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 04:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e-pharm`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorization`
--

CREATE TABLE `authorization` (
  `Username` varchar(60) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `Passworder_id` varchar(60) NOT NULL,
  `Modul` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `authorization`
--

INSERT INTO `authorization` (`Username`, `id_pegawai`, `Passworder_id`, `Modul`) VALUES
('abdul', 4, 'abdul', 'Adminwarehouse'),
('deby', 4, 'deby', 'Warehouse'),
('elvin', 2, 'elvin', 'Sales'),
('finance', 1, 'finance', 'Finance'),
('gading', 5, 'gading', 'Purchase'),
('super', 99, 'super', 'superadmin');

-- --------------------------------------------------------

--
-- Table structure for table `cuti`
--

CREATE TABLE `cuti` (
  `id_cuti` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `Departemen` varchar(40) NOT NULL,
  `Detail_cuti` int(30) NOT NULL,
  `Aksi` int(10) NOT NULL,
  `Total` int(11) NOT NULL,
  `Tanggal_Mulai` date DEFAULT NULL,
  `Tanggal_Selesai` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cuti`
--

INSERT INTO `cuti` (`id_cuti`, `id_pegawai`, `Nama`, `Departemen`, `Detail_cuti`, `Aksi`, `Total`, `Tanggal_Mulai`, `Tanggal_Selesai`) VALUES
(143666, 5, 'Singgih Rochmad S', 'Purchase', 3, 1, 0, '2014-06-11', '2014-06-18'),
(143667, 3, 'Apiladosi Priambodo', 'Warehouse', 1, 2, 0, '2014-06-19', '2014-06-30'),
(143668, 4, 'Abdul', 'Warehouse', 1, 0, 0, '2014-06-02', '2014-06-24'),
(143669, 2, 'Fakhry Ikhsan F', 'Sales', 1, 2, 0, '2014-06-12', '2014-06-27'),
(143670, 5, 'Singgih Rochmad S', 'Purchase', 1, 1, 0, '2014-06-24', '2014-06-30'),
(143671, 1, 'Wahyu Sugih P', 'Finance', 2, 2, 0, '2014-06-04', '2014-06-05'),
(143672, 5, 'Singgih Rochmad S', 'Purchase', 1, 1, 0, '2014-06-25', '2014-06-27');

-- --------------------------------------------------------

--
-- Table structure for table `dariwarehouse`
--

CREATE TABLE `dariwarehouse` (
  `no` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `nama` varchar(30) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `reorder_id` int(11) NOT NULL,
  `satuan` varchar(10) NOT NULL,
  `supplier` varchar(30) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `cabang` varchar(15) NOT NULL,
  `kategori` varchar(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_supplier`
--

CREATE TABLE `detail_supplier` (
  `id_pengeluaran` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `biaya` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gajibulan`
--

CREATE TABLE `gajibulan` (
  `id_gaji` int(11) NOT NULL,
  `total` double NOT NULL,
  `date` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `gajibulan`
--

INSERT INTO `gajibulan` (`id_gaji`, `total`, `date`, `status`) VALUES
(1, 22026316, '2014-06-01', 0),
(3, 31026316, '2014-06-01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(4, 'ubiqu'),
(7, 'Aqua');

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id_pegawai` int(11) NOT NULL,
  `Nama` varchar(45) DEFAULT NULL,
  `Alamat` varchar(255) DEFAULT NULL,
  `Telepon` varchar(20) DEFAULT NULL,
  `status_pegawai` varchar(20) DEFAULT NULL,
  `Jabatan` varchar(20) DEFAULT NULL,
  `Departemen` varchar(20) NOT NULL,
  `Gaji` int(11) DEFAULT NULL,
  `Tanggal_Masuk` date DEFAULT NULL,
  `Tanggal_Keluar` date DEFAULT NULL,
  `foto` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id_pegawai`, `Nama`, `Alamat`, `Telepon`, `status_pegawai`, `Jabatan`, `Departemen`, `Gaji`, `Tanggal_Masuk`, `Tanggal_Keluar`, `foto`) VALUES
(1, 'Wahyu Sugih P', 'Jalan Gajayana', '0314187988', 'Aktif', 'Admin', 'Finance', 10000000, '2014-05-05', '2015-05-21', 'img/yogi.png'),
(2, 'Fakhry Ikhsan F', 'jalan sumbersari', '034287989', 'Aktif', 'Admin', 'Sales', 10000000, '2014-05-23', '2015-03-18', 'image/fakhry.jpeg'),
(3, 'Deby Ayu Putri B', 'Cikarang Utara', '034889980', 'Aktif', 'Admin', 'Warehouse', 10000000, '2014-05-22', '2015-04-10', 'apiladosi.png'),
(4, 'Abdul', 'jalan gura', '034579989', 'Aktif', 'Pegawai', 'Warehouse', 1000000, '2014-05-08', '2015-08-06', 'priambodo.png'),
(5, 'Gading Dwi', 'Jalan Candi 2', '034280090', 'Aktif', 'Admin', 'Purchase', 10000000, '2014-05-15', '2015-07-09', 'gading.png'),
(6, 'Aisyah Ami', 'Jalan Kerto', '034198989', 'Aktif', 'Admin', 'Human Resource', 10000000, '2014-05-13', '2015-05-07', 'amy.jpg'),
(7, 'kah', NULL, NULL, NULL, NULL, 'jaa', NULL, NULL, NULL, ''),
(8, 'kah', NULL, NULL, NULL, NULL, 'jaa', NULL, NULL, NULL, ''),
(9, 'klj', NULL, NULL, NULL, NULL, 'lkjlk', 20000000, NULL, NULL, ''),
(10, 'Ami ', NULL, NULL, NULL, NULL, 'HR', 900000, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `pemasukan`
--

CREATE TABLE `pemasukan` (
  `Kode` varchar(20) NOT NULL DEFAULT 'DB-',
  `id_pemasukan` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `Nama` varchar(45) DEFAULT NULL,
  `Tanggal` date DEFAULT NULL,
  `Keterangan` varchar(255) DEFAULT NULL,
  `Total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pemasukan`
--

INSERT INTO `pemasukan` (`Kode`, `id_pemasukan`, `id_pegawai`, `Nama`, `Tanggal`, `Keterangan`, `Total`) VALUES
('DB-', 1, 1, 'Penjualan Tolak Angin', '2014-05-08', 'Penjualan Obat Tolak Angin 10 kotak @ Rp. 13.000,-', 130000),
('DB-', 2, 2, 'Penjualan Obat', '2014-06-02', 'Lunas', 1000000),
('DB-', 3, 2, 'Penjualan Obat', '2014-06-02', 'Lunas', 1600000),
('DB-', 4, 2, 'Penjualan Obat', '2025-04-18', 'Lunas', 23000000),
('DB-', 5, 2, 'Penjualan Obat', '2025-04-18', 'Lunas', 6600000);

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE `pemesanan` (
  `order_id` int(11) NOT NULL,
  `code` int(11) NOT NULL,
  `namabarang` varchar(30) NOT NULL,
  `kategori` varchar(30) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` double NOT NULL,
  `satuan` varchar(10) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` int(11) NOT NULL,
  `cabang` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`order_id`, `code`, `namabarang`, `kategori`, `jumlah`, `harga`, `satuan`, `id_supplier`, `tanggal`, `status`, `cabang`) VALUES
(1, 0, 'Amoxicilin', '', 10, 100000, 'Dos', 1, '2014-05-27 17:00:00', 1, ''),
(2, 0, 'Sangobion', '', 10, 300000, 'Dos', 1, '2014-05-24 17:00:00', 1, ''),
(3, 0, 'Calusol', '', 10, 1000000, 'Dos', 2, '2014-05-24 17:00:00', 1, ''),
(4, 0, 'Betadine', '', 20, 100000, 'Dos', 3, '2014-05-24 17:00:00', 1, ''),
(5, 0, 'Salonpas', '', 5, 90, 'Dos', 2, '2014-05-24 17:00:00', 1, ''),
(6, 0, 'Bodrexin', 'ubiqu', 10, 900000, 'dus', 0, '2014-06-01 17:00:00', 1, ''),
(7, 0, 'Prenagen', '10', 0, 213131, 'TempoGroup', 0, '2014-06-10 17:00:00', 1, ''),
(8, 0, 'Prenagen', '10', 0, 2131313, 'TempoGroup', 0, '2014-06-10 17:00:00', 1, ''),
(9, 0, 'Prenagen', '10', 0, 3242342, 'TempoGroup', 0, '2014-06-10 17:00:00', 1, ''),
(10, 0, 'Durex', '', 10, 2323423, 'bungkus', 0, '2025-04-17 17:00:00', 1, ''),
(11, 0, 'bodrex', '', 23, 23400, 'pcs', 1, '2025-04-17 17:00:00', 1, ''),
(12, 0, 'Antena', '', 200, 0, 'pcs', 0, '2025-04-17 17:00:00', 2, ''),
(13, 0, 'Antena', '', 200, 0, 'pcs', 0, '2025-04-17 17:00:00', 2, ''),
(14, 0, 'Antena', '', 200, 0, 'pcs', 0, '2025-04-17 17:00:00', 2, ''),
(15, 0, 'sssa', 'ubiqu', 12, 382984, 'ds', 0, '2025-04-17 17:00:00', 1, ''),
(16, 0, 'Mcd', 'ubiqu', 10, 231, 'dus', 0, '2025-04-17 17:00:00', 1, ''),
(17, 0, 'qw', 'lol', 1, 0, 's', 0, '2025-04-17 17:00:00', 2, ''),
(18, 0, 'gu', 'ubiqu', 11, 0, '23', 0, '2025-04-18 17:00:00', 2, ''),
(19, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(20, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(21, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(22, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(23, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(24, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(25, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(26, 0, 'dsd', 'ubiqu', 6, 0, 'pcs', 0, '2025-04-18 17:00:00', 2, ''),
(27, 0, 'dsd', 'ubiqu', 6, 2313, 'pcs', 0, '2025-04-18 17:00:00', 1, ''),
(28, 12423, 'aqua', 'ubiqu', 632, 0, 'dus', 0, '0000-00-00 00:00:00', 2, 'Blitar'),
(29, 2132, 'debyyyyy', 'ubiqu', 231, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar'),
(30, 13231, 'Ubiqu Diruma', 'ubiqu', 50, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar'),
(31, 0, 'error', '', 0, 0, '', 0, '0000-00-00 00:00:00', 2, 'Blitar'),
(32, 3424, 'tricia', 'lol', 23, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar'),
(33, 4567890, 'deby keren', 'lol', 60, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar'),
(34, 37913, 'gading', 'ubiqu', 109, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar'),
(35, 23424, 'Aqua', 'ubiqu', 7, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar'),
(36, 2345, 'cincin', 'ubiqu', 32, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar'),
(37, 21345, 'aqua', 'Aqua', 242, 0, 'dus', 0, '0000-00-00 00:00:00', 2, 'Blitar'),
(38, 213454, 'kwasongggg', 'ubiqu', 1231, 0, 'kg', 0, '0000-00-00 00:00:00', 1, 'Blitar');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `Kode` varchar(20) NOT NULL DEFAULT 'KR-',
  `id_pengeluaran` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `Nama` varchar(255) DEFAULT NULL,
  `Tanggal` date DEFAULT NULL,
  `Keterangan` varchar(255) DEFAULT NULL,
  `Total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`Kode`, `id_pengeluaran`, `id_pegawai`, `Nama`, `Tanggal`, `Keterangan`, `Total`) VALUES
('KR-', 1, 1, 'Pembelian Amoni Fructus', '2014-05-02', 'Pembelian Bahan Amoni Fructus untuk produksi obat Tolak Angin', 200000),
('KR-', 3, 1, 'Penggajian Pegawai', '2014-05-30', 'Gaji bulanan pegawai', 22026316),
('KR-', 4, 5, 'Calusol', '2014-06-02', 'Nambah Stok', 1000000),
('KR-', 5, 1, 'Penggajian Pegawai', '2014-06-02', 'Gaji bulanan pegawai', 31026316),
('KR-', 6, 5, 'Salonpas', '2025-04-18', 'ok', 90),
('KR-', 7, 5, 'Betadine', '2025-04-18', 'ok', 100000),
('KR-', 8, 5, 'sssa', '2025-05-09', 'ok', 382984),
('KR-', 9, 5, 'Mcd', '2025-05-09', 'ok', 231);

-- --------------------------------------------------------

--
-- Table structure for table `penggajian`
--

CREATE TABLE `penggajian` (
  `id_pegawai` varchar(15) NOT NULL,
  `hari_aktif` int(2) NOT NULL,
  `cuti` int(2) NOT NULL,
  `lembur` int(2) NOT NULL,
  `total` int(12) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `penggajian`
--

INSERT INTO `penggajian` (`id_pegawai`, `hari_aktif`, `cuti`, `lembur`, `total`, `date`) VALUES
('3', 20, 2, 5, 11500000, '2014-06-01'),
('2', 19, 3, 4, 10526316, '2014-06-01'),
('6', 20, 2, 0, 9000000, '2014-06-01'),
('8', 20, 2, 10, 0, '2014-06-01');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `id_pemasukan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `id_pemasukan`, `id_barang`, `jumlah`, `total`) VALUES
(4, 1, 11, 1, 1000000),
(5, 1, 13, 1, 300000),
(6, 2, 11, 1, 1000000),
(7, 3, 11, 1, 1000000),
(8, 3, 13, 2, 600000),
(9, 4, 11, 22, 22000000),
(10, 4, 14, 10, 1000000),
(11, 5, 13, 11, 6600000);

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id_pesan` int(11) NOT NULL,
  `dari` int(11) NOT NULL,
  `ke` int(11) NOT NULL,
  `isi` text NOT NULL,
  `waktu` datetime NOT NULL,
  `draft` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`id_pesan`, `dari`, `ke`, `isi`, `waktu`, `draft`, `status`) VALUES
(1, 2, 1, 'Halooo :D', '2014-06-01 10:00:00', 0, 1),
(2, 1, 2, 'Wa\'alaikum salam :)', '2014-06-01 11:00:00', 0, 0),
(4, 1, 2, 'hahaha', '2014-06-01 20:03:04', 0, 0),
(5, 1, 2, 'Test lagi :)', '2014-06-01 20:04:57', 0, 0),
(6, 1, 2, 'Haloo juga maaf baru bales :D', '2014-06-01 20:08:27', 0, 0),
(7, 1, 2, 'lalalalaa', '2014-06-01 20:57:45', 1, 0),
(8, 1, 2, 'oyiii', '2014-06-01 21:05:36', 1, 0),
(9, 1, 2, 'wesss', '2014-06-01 21:20:25', 0, 0),
(10, 1, 2, 'wenakk', '2014-06-01 23:13:57', 0, 0),
(11, 1, 2, 'test draft', '2014-06-01 23:14:12', 1, 0),
(12, 1, 2, '??', '2014-06-01 23:22:52', 0, 0),
(13, 1, 5, 'gjjjh', '2014-06-02 01:30:33', 0, 1),
(14, 5, 1, 'Meow', '2014-06-02 01:32:28', 0, 0),
(15, 6, 4, 'Ayok makan', '2014-06-02 09:56:29', 0, 1),
(16, 3, 5, 'Good Idea', '2014-06-02 10:08:53', 0, 1),
(17, 4, 6, 'okeee', '2014-06-02 13:50:11', 0, 0),
(18, 4, 6, 'test2', '2014-06-02 13:51:59', 1, 0),
(19, 4, 1, 'test yo', '2014-06-02 13:55:34', 0, 1),
(20, 5, 1, 'test', '2014-06-02 13:57:34', 0, 1),
(21, 1, 5, 'iki yoo', '2014-06-02 13:58:08', 0, 0),
(22, 6, 5, 'tes', '2014-06-02 14:12:27', 0, 0),
(23, 4, 3, 'dsjkdna', '2025-04-18 08:04:43', 0, 0),
(24, 4, 1, 'lol', '2025-04-18 08:05:05', 0, 0),
(25, 2, 4, '2025 bangun', '2025-04-18 08:26:28', 0, 0),
(26, 2, 4, '2025', '2025-04-18 08:26:35', 0, 0),
(27, 2, 4, 'hello dul 22025', '2025-04-18 15:14:00', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `recruitment`
--

CREATE TABLE `recruitment` (
  `id_pendaftaran` int(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `departemen` varchar(20) NOT NULL,
  `cv` varchar(80) NOT NULL,
  `gaji` int(9) NOT NULL,
  `aksi` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `recruitment`
--

INSERT INTO `recruitment` (`id_pendaftaran`, `nama`, `departemen`, `cv`, `gaji`, `aksi`) VALUES
(990, 'Ami ', 'HR', 'ERP - MAP.docx', 0, ''),
(4656, 'ka', 'isk', 'Safety Earth.docx', 0, ''),
(8889, 'ixj', 'ss', 'Safety Earth.docx', 0, ''),
(8890, 'sdk', 'sadliha', 'Safety Earth.docx', 0, ''),
(8892, 'asoldikzjh', 'paosikjzuhg', 'Safety Earth.docx', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `saldo`
--

CREATE TABLE `saldo` (
  `id_saldo` int(11) NOT NULL,
  `Tanggal` date NOT NULL,
  `Jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `saldo`
--

INSERT INTO `saldo` (`id_saldo`, `Tanggal`, `Jumlah`) VALUES
(4, '2014-01-01', 0),
(5, '2014-02-02', 0),
(6, '2014-03-01', 0),
(7, '2014-04-01', 0),
(8, '2014-05-01', 23000000),
(9, '2014-06-01', 52903684),
(10, '2014-07-01', 53903684),
(11, '2014-08-01', 0),
(12, '2014-09-01', 0),
(13, '2014-10-01', 0),
(24, '2014-11-01', 0),
(25, '2014-12-01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `Nama` varchar(45) DEFAULT NULL,
  `Alamat` varchar(255) DEFAULT NULL,
  `Telepon` varchar(20) DEFAULT NULL,
  `Nama_perusahaan` varchar(45) DEFAULT NULL,
  `Produk` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `Nama`, `Alamat`, `Telepon`, `Nama_perusahaan`, `Produk`) VALUES
(1, 'PT. OBAT FARMA', NULL, NULL, 'PT. OBAT FARMA', NULL),
(2, 'PT. OBAT KERAS', NULL, NULL, 'PT. OBAT KERAS', NULL),
(3, 'PT. OBAT MERAH', NULL, NULL, 'PT. OBAT MERAH', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` varchar(5) NOT NULL,
  `tanggal` date NOT NULL,
  `order_id` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal`, `order_id`, `id_supplier`, `status`) VALUES
('54947', '2014-05-26', 1, 1, 6),
('88783', '2014-05-26', 3, 2, 3),
('54947', '2014-05-26', 2, 1, 6),
('81998', '2014-06-02', 4, 3, 3),
('36990', '2014-06-02', 5, 2, 3),
('47290', '2025-04-18', 15, 0, 3),
('47290', '2025-04-18', 16, 0, 3),
('74055', '2025-05-09', 27, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE `warehouse` (
  `id_barang` int(11) NOT NULL,
  `Nama` varchar(45) DEFAULT NULL,
  `Stok` int(11) DEFAULT NULL,
  `Kategori` varchar(20) DEFAULT NULL,
  `Harga` int(11) DEFAULT NULL,
  `Satuan` varchar(20) DEFAULT NULL,
  `reorder_id_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `warehouse`
--

INSERT INTO `warehouse` (`id_barang`, `Nama`, `Stok`, `Kategori`, `Harga`, `Satuan`, `reorder_id_level`) VALUES
(13, 'Sangobion', 12, 'Obat Penambah Darah', 600000, 'Dos', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authorization`
--
ALTER TABLE `authorization`
  ADD PRIMARY KEY (`Username`),
  ADD KEY `Authorization_FKIndex1` (`id_pegawai`);

--
-- Indexes for table `cuti`
--
ALTER TABLE `cuti`
  ADD PRIMARY KEY (`id_cuti`),
  ADD KEY `Cuti_FKIndex1` (`id_pegawai`);

--
-- Indexes for table `dariwarehouse`
--
ALTER TABLE `dariwarehouse`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `detail_supplier`
--
ALTER TABLE `detail_supplier`
  ADD KEY `Detail_Supplier_FKIndex1` (`id_supplier`),
  ADD KEY `Detail_Supplier_FKIndex2` (`id_barang`),
  ADD KEY `Detail_Supplier_FKIndex3` (`id_pengeluaran`);

--
-- Indexes for table `gajibulan`
--
ALTER TABLE `gajibulan`
  ADD PRIMARY KEY (`id_gaji`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id_pegawai`);

--
-- Indexes for table `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD PRIMARY KEY (`id_pemasukan`),
  ADD KEY `Pemasukan_FKIndex1` (`id_pegawai`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `Pengeluaran_FKIndex1` (`id_pegawai`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD KEY `Penjualan_FKIndex1` (`id_barang`),
  ADD KEY `Penjualan_FKIndex2` (`id_pemasukan`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id_pesan`);

--
-- Indexes for table `recruitment`
--
ALTER TABLE `recruitment`
  ADD PRIMARY KEY (`id_pendaftaran`);

--
-- Indexes for table `saldo`
--
ALTER TABLE `saldo`
  ADD PRIMARY KEY (`id_saldo`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD PRIMARY KEY (`id_barang`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cuti`
--
ALTER TABLE `cuti`
  MODIFY `id_cuti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143673;

--
-- AUTO_INCREMENT for table `dariwarehouse`
--
ALTER TABLE `dariwarehouse`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `gajibulan`
--
ALTER TABLE `gajibulan`
  MODIFY `id_gaji` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id_pegawai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pemasukan`
--
ALTER TABLE `pemasukan`
  MODIFY `id_pemasukan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id_pesan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `recruitment`
--
ALTER TABLE `recruitment`
  MODIFY `id_pendaftaran` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8893;

--
-- AUTO_INCREMENT for table `saldo`
--
ALTER TABLE `saldo`
  MODIFY `id_saldo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `warehouse`
--
ALTER TABLE `warehouse`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
