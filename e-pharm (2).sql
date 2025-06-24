-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2014 at 05:14 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `E-pharm`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorization`
--

CREATE TABLE IF NOT EXISTS `authorization` (
  `Username` varchar(60) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `Passworder_id` varchar(60) NOT NULL,
  `Modul` varchar(20) NOT NULL,
  PRIMARY KEY (`Username`),
  KEY `Authorization_FKIndex1` (`id_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `authorization`
--

INSERT INTO `authorization` (`Username`, `id_pegawai`, `Passworder_id`, `Modul`) VALUES
('abdul', 4, 'abdul', 'Warehouse'),
('ami', 6, 'ami', 'HR'),
('apiladosi', 3, 'apiladosi', 'Adminwarehouse'),
('fakhry', 2, 'fakhry', 'Sales'),
('joko', 10, 'joko', 'warehouse'),
('singgih', 5, 'singgih', 'Purchase'),
('super', 99, 'super', 'superadmin'),
('Yogi', 1, 'yogi', 'Finance');

-- --------------------------------------------------------

--
-- Table structure for table `cuti`
--

CREATE TABLE IF NOT EXISTS `cuti` (
  `id_cuti` int(11) NOT NULL AUTO_INCREMENT,
  `id_pegawai` int(11) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `Departemen` varchar(40) NOT NULL,
  `Detail_cuti` int(30) NOT NULL,
  `Aksi` int(10) NOT NULL,
  `Total` int(11) NOT NULL,
  `Tanggal_Mulai` date DEFAULT NULL,
  `Tanggal_Selesai` date DEFAULT NULL,
  PRIMARY KEY (`id_cuti`),
  KEY `Cuti_FKIndex1` (`id_pegawai`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=143673 ;

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

CREATE TABLE IF NOT EXISTS `dariwarehouse` (
  `No` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(30) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `satuan` varchar(10) NOT NULL,
  `supplier` varchar(30) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `pemesan` varchar(15) NOT NULL,
  `jenis` varchar(20) NOT NULL,
  PRIMARY KEY (`No`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `dariwarehouse`
--

INSERT INTO `dariwarehouse` (`No`, `nama`, `jumlah`, `satuan`, `supplier`, `status`, `pemesan`, `jenis`) VALUES
(1, 'Bodrexin', 50, 'dos', 'Tempo Group', 1, 'Blitar', ''),
(2, 'OBH Combi Madu', 100, 'dos', 'Combiphar', 1, 'Malang', ''),
(3, 'Prenagen', 10, 'kotak', 'TempoGroup', 1, '', ''),
(4, 'Durex', 10, 'bungkus', 'Tempo Group', 0, 'Blitar', 'Obat Kuat');

-- --------------------------------------------------------

--
-- Table structure for table `detail_supplier`
--

CREATE TABLE IF NOT EXISTS `detail_supplier` (
  `id_pengeluaran` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `biaya` int(11) DEFAULT NULL,
  KEY `Detail_Supplier_FKIndex1` (`id_supplier`),
  KEY `Detail_Supplier_FKIndex2` (`id_barang`),
  KEY `Detail_Supplier_FKIndex3` (`id_pengeluaran`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gajibulan`
--

CREATE TABLE IF NOT EXISTS `gajibulan` (
  `id_gaji` int(11) NOT NULL AUTO_INCREMENT,
  `total` double NOT NULL,
  `date` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_gaji`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `gajibulan`
--

INSERT INTO `gajibulan` (`id_gaji`, `total`, `date`, `status`) VALUES
(1, 22026316, '2014-06-01', 0),
(3, 31026316, '2014-06-01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE IF NOT EXISTS `pegawai` (
  `id_pegawai` int(11) NOT NULL AUTO_INCREMENT,
  `Nama` varchar(45) DEFAULT NULL,
  `Alamat` varchar(255) DEFAULT NULL,
  `Telepon` varchar(20) DEFAULT NULL,
  `status_pegawai` varchar(20) DEFAULT NULL,
  `Jabatan` varchar(20) DEFAULT NULL,
  `Departemen` varchar(20) NOT NULL,
  `Gaji` int(11) DEFAULT NULL,
  `Tanggal_Masuk` date DEFAULT NULL,
  `Tanggal_Keluar` date DEFAULT NULL,
  `foto` varchar(100) NOT NULL,
  PRIMARY KEY (`id_pegawai`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id_pegawai`, `Nama`, `Alamat`, `Telepon`, `status_pegawai`, `Jabatan`, `Departemen`, `Gaji`, `Tanggal_Masuk`, `Tanggal_Keluar`, `foto`) VALUES
(1, 'Wahyu Sugih P', 'Jalan Gajayana', '0314187988', 'Aktif', 'Admin', 'Finance', 10000000, '2014-05-05', '2015-05-21', 'img/yogi.png'),
(2, 'Fakhry Ikhsan F', 'jalan sumbersari', '034287989', 'Aktif', 'Admin', 'Sales', 10000000, '2014-05-23', '2015-03-18', 'image/fakhry.jpeg'),
(3, 'Apiladosi Priambodo', 'jalan sigura', '034889980', 'Aktif', 'Admin', 'Warehouse', 10000000, '2014-05-22', '2015-04-10', 'apiladosi.png'),
(4, 'Abdul', 'jalan gura', '034579989', 'Aktif', 'Pegawai', 'Warehouse', 1000000, '2014-05-08', '2015-08-06', 'priambodo.png'),
(5, 'Singgih Rochmad S', 'Jalan Candi 2', '034280090', 'Aktif', 'Admin', 'Purchase', 10000000, '2014-05-15', '2015-07-09', 'singgih.png'),
(6, 'Aisyah Ami', 'Jalan Kerto', '034198989', 'Aktif', 'Admin', 'Human Resource', 10000000, '2014-05-13', '2015-05-07', 'amy.jpg'),
(7, 'kah', NULL, NULL, NULL, NULL, 'jaa', NULL, NULL, NULL, ''),
(8, 'kah', NULL, NULL, NULL, NULL, 'jaa', NULL, NULL, NULL, ''),
(9, 'klj', NULL, NULL, NULL, NULL, 'lkjlk', 20000000, NULL, NULL, ''),
(10, 'Ami ', NULL, NULL, NULL, NULL, 'HR', 900000, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `pemasukan`
--

CREATE TABLE IF NOT EXISTS `pemasukan` (
  `Kode` varchar(20) NOT NULL DEFAULT 'DB-',
  `id_pemasukan` int(11) NOT NULL AUTO_INCREMENT,
  `id_pegawai` int(11) NOT NULL,
  `Nama` varchar(45) DEFAULT NULL,
  `Tanggal` date DEFAULT NULL,
  `Keterangan` varchar(255) DEFAULT NULL,
  `Total` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pemasukan`),
  KEY `Pemasukan_FKIndex1` (`id_pegawai`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pemasukan`
--

INSERT INTO `pemasukan` (`Kode`, `id_pemasukan`, `id_pegawai`, `Nama`, `Tanggal`, `Keterangan`, `Total`) VALUES
('DB-', 1, 1, 'Penjualan Tolak Angin', '2014-05-08', 'Penjualan Obat Tolak Angin 10 kotak @ Rp. 13.000,-', 130000),
('DB-', 2, 2, 'Penjualan Obat', '2014-06-02', 'Lunas', 1000000),
('DB-', 3, 2, 'Penjualan Obat', '2014-06-02', 'Lunas', 1600000);

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE IF NOT EXISTS `pemesanan` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `namabarang` varchar(30) NOT NULL,
  `jenis` varchar(30) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` double NOT NULL,
  `satuan` varchar(10) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`order_id`, `namabarang`, `jenis`, `jumlah`, `harga`, `satuan`, `id_supplier`, `tanggal`, `status`) VALUES
(1, 'Amoxicilin', '', 10, 100000, 'Dos', 1, '2014-05-28', 1),
(2, 'Sangobion', '', 10, 300000, 'Dos', 1, '2014-05-25', 1),
(3, 'Calusol', '', 10, 1000000, 'Dos', 2, '2014-05-25', 1),
(4, 'Betadine', '', 20, 100000, 'Dos', 3, '2014-05-25', 1),
(5, 'Salonpas', '', 5, 90, 'Dos', 2, '2014-05-25', 1),
(6, 'Bodrexin', '50', 0, 900000, 'Tempo Grou', 0, '2014-06-02', 1),
(7, 'Prenagen', '10', 0, 0, 'TempoGroup', 0, '2014-06-11', 0),
(8, 'Prenagen', '10', 0, 0, 'TempoGroup', 0, '2014-06-11', 0),
(9, 'Prenagen', '10', 0, 0, 'TempoGroup', 0, '2014-06-11', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE IF NOT EXISTS `pengeluaran` (
  `Kode` varchar(20) NOT NULL DEFAULT 'KR-',
  `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_pegawai` int(11) NOT NULL,
  `Nama` varchar(255) DEFAULT NULL,
  `Tanggal` date DEFAULT NULL,
  `Keterangan` varchar(255) DEFAULT NULL,
  `Total` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pengeluaran`),
  KEY `Pengeluaran_FKIndex1` (`id_pegawai`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`Kode`, `id_pengeluaran`, `id_pegawai`, `Nama`, `Tanggal`, `Keterangan`, `Total`) VALUES
('KR-', 1, 1, 'Pembelian Amoni Fructus', '2014-05-02', 'Pembelian Bahan Amoni Fructus untuk produksi obat Tolak Angin', 200000),
('KR-', 3, 1, 'Penggajian Pegawai', '2014-05-30', 'Gaji bulanan pegawai', 22026316),
('KR-', 4, 5, 'Calusol', '2014-06-02', 'Nambah Stok', 1000000),
('KR-', 5, 1, 'Penggajian Pegawai', '2014-06-02', 'Gaji bulanan pegawai', 31026316);

-- --------------------------------------------------------

--
-- Table structure for table `penggajian`
--

CREATE TABLE IF NOT EXISTS `penggajian` (
  `id_pegawai` varchar(15) NOT NULL,
  `hari_aktif` int(2) NOT NULL,
  `cuti` int(2) NOT NULL,
  `lembur` int(2) NOT NULL,
  `total` int(12) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

CREATE TABLE IF NOT EXISTS `penjualan` (
  `id_penjualan` int(11) NOT NULL AUTO_INCREMENT,
  `id_pemasukan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_penjualan`),
  KEY `Penjualan_FKIndex1` (`id_barang`),
  KEY `Penjualan_FKIndex2` (`id_pemasukan`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `id_pemasukan`, `id_barang`, `jumlah`, `total`) VALUES
(4, 1, 11, 1, 1000000),
(5, 1, 13, 1, 300000),
(6, 2, 11, 1, 1000000),
(7, 3, 11, 1, 1000000),
(8, 3, 13, 2, 600000);

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE IF NOT EXISTS `pesan` (
  `id_pesan` int(11) NOT NULL AUTO_INCREMENT,
  `dari` int(11) NOT NULL,
  `ke` int(11) NOT NULL,
  `isi` text NOT NULL,
  `waktu` datetime NOT NULL,
  `draft` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id_pesan`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`id_pesan`, `dari`, `ke`, `isi`, `waktu`, `draft`, `status`) VALUES
(1, 2, 1, 'Halooo :D', '2014-06-01 10:00:00', 0, 1),
(2, 1, 2, 'Wa''alaikum salam :)', '2014-06-01 11:00:00', 0, 0),
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
(22, 6, 5, 'tes', '2014-06-02 14:12:27', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `recruitment`
--

CREATE TABLE IF NOT EXISTS `recruitment` (
  `id_pendaftaran` int(10) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `departemen` varchar(20) NOT NULL,
  `cv` varchar(80) NOT NULL,
  `gaji` int(9) NOT NULL,
  `aksi` varchar(2) NOT NULL,
  PRIMARY KEY (`id_pendaftaran`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8893 ;

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

CREATE TABLE IF NOT EXISTS `saldo` (
  `id_saldo` int(11) NOT NULL AUTO_INCREMENT,
  `Tanggal` date NOT NULL,
  `Jumlah` int(11) NOT NULL,
  PRIMARY KEY (`id_saldo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `saldo`
--

INSERT INTO `saldo` (`id_saldo`, `Tanggal`, `Jumlah`) VALUES
(4, '2014-01-01', 0),
(5, '2014-02-02', 0),
(6, '2014-03-01', 0),
(7, '2014-04-01', 0),
(8, '2014-05-01', 75000000),
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

CREATE TABLE IF NOT EXISTS `supplier` (
  `id_supplier` int(11) NOT NULL AUTO_INCREMENT,
  `Nama` varchar(45) DEFAULT NULL,
  `Alamat` varchar(255) DEFAULT NULL,
  `Telepon` varchar(20) DEFAULT NULL,
  `Nama_perusahaan` varchar(45) DEFAULT NULL,
  `Produk` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

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

CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` varchar(5) NOT NULL,
  `tanggal` date NOT NULL,
  `order_id` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal`, `order_id`, `id_supplier`, `status`) VALUES
('54947', '2014-05-26', 1, 1, 6),
('88783', '2014-05-26', 3, 2, 3),
('54947', '2014-05-26', 2, 1, 6),
('81998', '2014-06-02', 4, 3, 1),
('59958', '2014-06-02', 6, 0, 0),
('36990', '2014-06-02', 5, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE IF NOT EXISTS `warehouse` (
  `id_barang` int(11) NOT NULL AUTO_INCREMENT,
  `Nama` varchar(45) DEFAULT NULL,
  `Stok` int(11) DEFAULT NULL,
  `Jenis` varchar(20) DEFAULT NULL,
  `Harga` int(11) DEFAULT NULL,
  `Satuan` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `warehouse`
--

INSERT INTO `warehouse` (`id_barang`, `Nama`, `Stok`, `Jenis`, `Harga`, `Satuan`) VALUES
(11, 'Bodrexin', 50, 'Obat Pilek', 1000000, 'dos'),
(13, 'Sangobion', 17, 'Obat Penambah Darah', 600000, 'Dos'),
(14, 'Amoxicilin', 10, '', 100000, 'Dos');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
