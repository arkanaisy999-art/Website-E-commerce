-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 10, 2026 at 07:13 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `alamat_pengiriman`
--

CREATE TABLE `alamat_pengiriman` (
  `id_alamat` int NOT NULL,
  `id_pengguna` int DEFAULT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `email_penerima` varchar(255) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `negara` varchar(100) DEFAULT NULL,
  `kota_kecamatan` varchar(255) DEFAULT NULL,
  `alamat_lengkap` text,
  `is_default` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `alamat_pengiriman`
--

INSERT INTO `alamat_pengiriman` (`id_alamat`, `id_pengguna`, `nama_penerima`, `email_penerima`, `telepon`, `negara`, `kota_kecamatan`, `alamat_lengkap`, `is_default`) VALUES
(7, 6, 'Arkan', 'arkan@gmail.com', '08222222222', 'Indonesia', 'Taman Beji', 'y', 1),
(8, 7, 'Arkan', 'arkanaisy999@gmail.com', '222222222222222', 'Singapore', 'NYU', 'Kampus Terbagus', 1),
(9, 9, 'Bio', 'bio@gmail.com', '0822222222222', 'Indonesia', 'Ujunggede', 'Gede', 1);

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int NOT NULL,
  `id_pesanan` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `jumlah`, `subtotal`) VALUES
(35, 28, 8, 2, 400000),
(36, 29, 8, 1, 200000),
(37, 30, 8, 5, 1000000),
(38, 31, 1, 6, 2700000),
(39, 32, 1, 1, 450000),
(40, 33, 1, 1, 450000),
(41, 34, 1, 2, 900000),
(42, 35, 6, 1, 250000),
(43, 36, 1, 1, 450000),
(44, 37, 1, 1, 450000),
(45, 38, 1, 1, 450000),
(46, 39, 6, 1, 250000),
(47, 40, 6, 1, 250000),
(48, 41, 6, 1, 1),
(49, 42, 6, 1, 1),
(50, 43, 6, 1, 1),
(51, 44, 6, 1, 1),
(52, 45, 6, 1, 1),
(53, 46, 1, 1, 450000),
(54, 47, 1, 1, 450000),
(55, 48, 1, 1, 450000),
(56, 49, 6, 1, 250000),
(57, 50, 6, 1, 250000),
(58, 51, 6, 2, 500000),
(59, 52, 1, 1, 450000),
(60, 53, 6, 1, 250000),
(61, 54, 1, 1, 450000),
(62, 55, 6, 1, 250000),
(63, 56, 1, 1, 450000),
(64, 57, 6, 1, 250000),
(65, 58, 6, 1, 250000),
(66, 59, 6, 1, 250000),
(67, 60, 6, 1, 250000),
(68, 61, 6, 1, 250000),
(69, 62, 6, 1, 250000);

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int NOT NULL,
  `id_pengguna` int NOT NULL,
  `id_produk` int NOT NULL,
  `qty` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keranjang`
--

INSERT INTO `keranjang` (`id_keranjang`, `id_pengguna`, `id_produk`, `qty`, `created_at`) VALUES
(37, 6, 8, 2, '2026-06-09 16:15:50'),
(38, 7, 8, 8, '2026-06-10 01:37:19'),
(45, 7, 6, 2, '2026-06-10 05:21:35'),
(46, 8, 6, 3, '2026-06-10 05:56:38'),
(47, 7, 1, 1, '2026-06-11 03:45:42'),
(48, 4, 1, 2, '2026-11-09 11:57:32'),
(50, 9, 6, 2, '2026-11-10 01:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pelanggan','admin') NOT NULL DEFAULT 'pelanggan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `username`, `email`, `password`, `role`) VALUES
(2, 'Vabio', 'vabio@gmail.com', '$2y$10$vdifJXh0FWTh7s1dIZd42.Q2/nFBH/8JcDPZuH3AkPyfkYFWvcr.y', 'pelanggan'),
(4, 'admin_toko', 'admin@gmail.com', 'admin123', 'admin'),
(5, 'coba', 'coba@gmail.com', '$2y$10$07q9Or6UrvUqnLvUeoFs4Ov2o9mu3MzHUCqa/orz3UqdZCrzMhwJq', 'pelanggan'),
(6, 'Arkan', 'arkan@gmail.com', '$2y$10$9h7.TZigT8T2ozswDlKuM.HSzGfY/7wm/LGhdkG3BVpOkoHT6Nz/W', 'pelanggan'),
(7, 'byan', 'arkanaisy999@gmail.com', '$2y$10$LEvhxrm2e4BxZlBwSEO3sOgGmtNbqIwYW.n57HynsRHhcfb3NoKRS', 'pelanggan'),
(8, 'malik', 'malik@gmail.com', '$2y$10$tAw1Mq6BuWOyKo5lThaqb.RtObKvGPvoG98AdDO/2IM0O3alKDT3a', 'pelanggan'),
(9, 'Bio', 'bio@gmail.com', '$2y$10$f/WB7tzEzVrnF/4f9.K/d.NTdvhau2p03dsU2hi5WwC/JtvtVCKAq', 'pelanggan');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int NOT NULL,
  `id_pengguna` int DEFAULT NULL,
  `id_alamat` int DEFAULT NULL,
  `tanggal_pesanan` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_harga` int NOT NULL,
  `shipping_method` varchar(10) DEFAULT 'JNE',
  `ongkir` int DEFAULT '0',
  `no_resi` varchar(50) DEFAULT NULL,
  `status_pesanan` enum('unpaid','process','expired','sent','done','cancel') DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_pengguna`, `id_alamat`, `tanggal_pesanan`, `total_harga`, `shipping_method`, `ongkir`, `no_resi`, `status_pesanan`) VALUES
(28, 6, 7, '2026-06-09 16:15:53', 419000, 'JNE', 19000, 'JNE-566069800', 'unpaid'),
(29, 6, 7, '2026-06-09 16:22:56', 219000, 'JNE', 19000, 'JNE-321229673', 'unpaid'),
(30, 6, 7, '2026-06-09 16:29:27', 1019000, 'JNE', 19000, 'JNE-785144849', 'unpaid'),
(31, 7, 8, '2026-06-11 03:16:07', 2719000, 'JNE', 19000, 'JNE-206120143', 'unpaid'),
(32, 7, 8, '2026-06-11 03:45:45', 469000, 'JNE', 19000, 'JNE-327622869', 'unpaid'),
(33, 6, 7, '2026-07-28 01:11:10', 469000, 'JNE', 19000, 'JNE-835694453', 'unpaid'),
(34, 9, 9, '2026-11-09 07:34:49', 919000, 'JNE', 19000, 'JNE-174375804', 'process'),
(35, 9, 9, '2026-11-09 12:06:42', 269000, 'JNE', 19000, 'JNE-502572630', 'unpaid'),
(36, 9, 9, '2026-11-09 12:20:39', 469000, 'JNE', 19000, 'JNE-602199474', 'unpaid'),
(37, 9, 9, '2026-11-09 12:28:27', 469000, 'JNE', 19000, 'JNE-765109400', 'unpaid'),
(38, 9, 9, '2026-11-09 12:43:09', 469000, 'JNE', 19000, 'JNE-384315455', 'unpaid'),
(39, 9, 9, '2026-11-09 13:08:02', 269000, 'JNE', 19000, 'JNE-422799245', 'unpaid'),
(40, 9, 9, '2026-11-09 13:11:27', 269000, 'JNE', 19000, 'JNE-156713469', 'unpaid'),
(41, 9, 9, '2026-11-09 13:12:45', 19001, 'JNE', 19000, 'JNE-209332433', 'unpaid'),
(42, 9, 9, '2026-11-09 13:14:26', 2, 'JNE', 1, 'JNE-511409365', 'unpaid'),
(43, 9, 9, '2026-11-09 13:19:21', 2, 'JNE', 1, 'JNE-575274365', 'unpaid'),
(44, 9, 9, '2026-11-09 13:20:57', 2, 'JNE', 1, 'JNE-955681783', 'unpaid'),
(45, 9, 9, '2026-11-09 13:23:48', 2, 'JNE', 1, 'JNE-615828861', 'expired'),
(46, 9, 9, '2026-11-09 13:26:25', 469000, 'JNE', 19000, 'JNE-686861838', 'expired'),
(47, 9, 9, '2026-11-09 13:27:52', 469000, 'JNE', 19000, 'JNE-413584378', 'expired'),
(48, 9, 9, '2026-11-09 13:35:37', 469000, 'JNE', 19000, 'JNE-578103372', 'expired'),
(49, 9, 9, '2026-11-09 14:30:30', 269000, 'JNE', 19000, 'JNE-105523363', 'unpaid'),
(50, 9, 9, '2026-11-09 14:31:30', 269000, 'JNE', 19000, 'JNE-159085073', 'process'),
(51, 9, 9, '2026-11-09 14:38:55', 519000, 'JNE', 19000, 'JNE-460872084', 'process'),
(52, 9, 9, '2026-11-09 14:39:13', 469000, 'JNE', 19000, 'JNE-125867347', 'process'),
(53, 9, 9, '2026-11-10 01:59:16', 269000, 'JNE', 19000, 'JNE-678611576', 'process'),
(54, 9, 9, '2026-11-10 02:10:48', 469000, 'JNE', 19000, 'JNE-522352485', 'cancel'),
(55, 9, 9, '2026-11-10 02:26:39', 269000, 'JNE', 19000, 'JNE-742246936', 'cancel'),
(56, 9, 9, '2026-11-10 02:28:51', 469000, 'JNE', 19000, 'JNE-360254368', 'unpaid'),
(57, 9, 9, '2026-11-10 04:14:31', 269000, 'JNE', 19000, 'JNE-458968882', 'unpaid'),
(58, 9, 9, '2026-11-10 04:14:32', 269000, 'JNE', 19000, 'JNE-151642667', 'unpaid'),
(59, 9, 9, '2026-11-10 04:14:32', 269000, 'JNE', 19000, 'JNE-116131699', 'unpaid'),
(60, 9, 9, '2026-11-10 04:14:33', 269000, 'JNE', 19000, 'JNE-314405879', 'unpaid'),
(61, 9, 9, '2026-11-10 04:14:38', 269000, 'JNE', 19000, 'JNE-892855965', 'unpaid'),
(62, 9, 9, '2026-11-10 04:14:55', 269000, 'JNE', 19000, 'JNE-504903273', 'unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `deskripsi` text,
  `berat` int DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default.jpg',
  `kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga`, `stok`, `deskripsi`, `berat`, `foto`, `kategori`) VALUES
(1, 'Bagpack Chambredelavain - Word Black', 450000, 20, 'High quality bagpack with elegant design.', 900, 'bagpack.webp', 'Bagpack'),
(6, 'T-shirt Chambredelavain - I Hate', 250000, 9, 'High quality', 900, 't-shirt(1).webp', 'T-shirt'),
(8, 'Trucker Chambredelavain - Violet', 200000, 0, 'High Quality', 500, '1780487650_6a2015e25a9a4.webp', 'Hats');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  ADD PRIMARY KEY (`id_alamat`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD UNIQUE KEY `unique_user_product` (`id_pengguna`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `no_resi` (`no_resi`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  MODIFY `id_alamat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  ADD CONSTRAINT `alamat_pengiriman_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
