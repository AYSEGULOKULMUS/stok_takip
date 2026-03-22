-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 22 Mar 2026, 11:51:55
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `stok_sistemi`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `depolar`
--

CREATE TABLE `depolar` (
  `id` int(11) NOT NULL,
  `depo_adi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `depolar`
--

INSERT INTO `depolar` (`id`, `depo_adi`) VALUES
(1, 'Havaleli Depo'),
(2, 'İlaç Depo');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hareketler`
--

CREATE TABLE `hareketler` (
  `id` int(11) NOT NULL,
  `urun_id` int(11) DEFAULT NULL,
  `depo_id` int(11) DEFAULT NULL,
  `islem` varchar(50) DEFAULT NULL,
  `miktar` int(11) DEFAULT NULL,
  `tarih` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `hareketler`
--

INSERT INTO `hareketler` (`id`, `urun_id`, `depo_id`, `islem`, `miktar`, `tarih`) VALUES
(1, 1, 1, 'stok_giris', 20, '2026-03-09 09:17:39'),
(2, 1234, 0, 'transfer', 0, '2026-03-09 09:17:50'),
(3, 1, 1, 'stok_giris', 20, '2026-03-09 09:55:36'),
(4, 1, 1, 'stok_giris', 6, '2026-03-10 06:48:29'),
(5, 1, 1, 'transfer', 4, '2026-03-10 06:56:54'),
(6, 1, 2, 'transfer', 4, '2026-03-10 06:59:13'),
(7, 1, 2, 'transfer', 4, '2026-03-10 07:00:56'),
(8, 1, 2, 'transfer', 4, '2026-03-10 07:00:58'),
(9, 1, 2, 'transfer', 4, '2026-03-10 07:00:58'),
(10, 1, 2, 'transfer', 4, '2026-03-10 07:00:58'),
(11, 1, 2, 'transfer', 5, '2026-03-10 07:01:04'),
(12, 1, 2, 'transfer', 9, '2026-03-10 07:06:53'),
(13, 1, 1, 'transfer', 4, '2026-03-10 07:09:55'),
(14, 2, 2, 'transfer', 6, '2026-03-10 08:33:34'),
(15, 1, 1, 'transfer', 6, '2026-03-10 08:34:51'),
(16, 1, 1, 'transfer', 4, '2026-03-10 08:40:12'),
(17, 1, 1, 'transfer', 5, '2026-03-10 08:44:22'),
(18, 1, 1, 'transfer', 5, '2026-03-10 09:02:13'),
(19, 1, 1, 'transfer', 5, '2026-03-10 10:36:58'),
(20, 1, 1, 'transfer', 3, '2026-03-10 10:43:29'),
(21, 1, 1, 'transfer', 5, '2026-03-12 15:50:46'),
(22, 5, 1, 'stok_giris', 8, '2026-03-12 22:32:47'),
(23, 2, 1, 'transfer', 5, '2026-03-14 21:55:51'),
(24, 6, 1, 'stok_giris', 100000, '2026-03-14 22:13:29'),
(25, 7, 2, 'stok_giris', 9000, '2026-03-14 22:14:15'),
(26, 8, 1, 'stok_giris', 9000, '2026-03-14 22:14:56'),
(27, 7, 2, 'stok_giris', 3000, '2026-03-14 22:17:56'),
(28, 2, 1, 'stok_giris', 2147483647, '2026-03-14 22:19:47'),
(29, 10, 1, 'stok_giris', 300, '2026-03-14 22:57:02'),
(30, 1, 1, 'stok_giris', 90000000, '2026-03-14 22:57:21'),
(31, 1, 1, 'transfer', 9000000, '2026-03-14 22:59:30'),
(32, 8, 1, 'transfer', 100, '2026-03-14 23:09:36'),
(33, 8, 1, 'transfer', 8, '2026-03-17 14:01:41'),
(34, 6, 1, 'transfer', 5, '2026-03-17 14:04:15'),
(35, 11, 2, 'stok_giris', 100000, '2026-03-18 22:05:03'),
(36, 7, 2, 'transfer', 90, '2026-03-18 22:07:29'),
(37, 2, 1, 'transfer', 900, '2026-03-19 16:57:55'),
(38, 1, 1, 'transfer', 7, '2026-03-22 13:12:09'),
(39, 1, 1, 'transfer', 7, '2026-03-22 13:12:44'),
(40, 2, 1, 'stok_giris', 4, '2026-03-22 13:13:10'),
(41, 7, 1, 'stok_giris', 8, '2026-03-22 13:34:20');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siparisler`
--

CREATE TABLE `siparisler` (
  `id` int(11) NOT NULL,
  `tedarikci` varchar(200) NOT NULL,
  `depo_id` int(11) DEFAULT NULL,
  `hedef_depo_adi` varchar(200) DEFAULT '',
  `notlar` text DEFAULT NULL,
  `durum` enum('beklemede','onaylandi','teslim_edildi','iptal') NOT NULL DEFAULT 'beklemede',
  `kullanici_id` int(11) DEFAULT NULL,
  `olusturma_tarihi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `siparisler`
--

INSERT INTO `siparisler` (`id`, `tedarikci`, `depo_id`, `hedef_depo_adi`, `notlar`, `durum`, `kullanici_id`, `olusturma_tarihi`) VALUES
(1, 'ASDASDASD', 1, 'Havaleli Depo', ':)', 'teslim_edildi', 1, '2026-03-22 13:24:48'),
(2, 'DKDKDKDKDKD', 2, 'İlaç Depo', 'kolay gelsin , iyi çalışmalar :)', 'iptal', 1, '2026-03-22 13:33:26'),
(3, 'YTYTYTYTYTY', 1, 'Havaleli Depo', ':)', 'teslim_edildi', 1, '2026-03-22 13:34:16');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siparis_kalemleri`
--

CREATE TABLE `siparis_kalemleri` (
  `id` int(11) NOT NULL,
  `siparis_id` int(11) NOT NULL,
  `urun_id` int(11) NOT NULL,
  `miktar` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `siparis_kalemleri`
--

INSERT INTO `siparis_kalemleri` (`id`, `siparis_id`, `urun_id`, `miktar`) VALUES
(1, 1, 2, 11),
(2, 1, 12, 14),
(3, 2, 8, 8),
(4, 3, 7, 8);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `stok`
--

CREATE TABLE `stok` (
  `id` int(11) NOT NULL,
  `urun_id` int(11) DEFAULT NULL,
  `depo_id` int(11) DEFAULT NULL,
  `miktar` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `stok`
--

INSERT INTO `stok` (`id`, `urun_id`, `depo_id`, `miktar`) VALUES
(1, 1, 1, -9000001),
(2, 1, 1, -9000001),
(3, 1, 1, -9000015),
(4, 5, 1, 8),
(5, 6, 1, 99995),
(6, 7, 2, 8910),
(7, 8, 1, 8892),
(8, 7, 2, 2910),
(9, 2, 1, 2147482747),
(10, 10, 1, 300),
(11, 1, 1, 80999986),
(12, 8, 2, 100),
(13, 8, 2, 8),
(14, 6, 2, 5),
(15, 11, 2, 100000),
(16, 7, 1, 90),
(17, 2, 2, 900),
(18, 2, 1, 4),
(19, 7, 1, 8);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `id` int(11) NOT NULL,
  `barkod` varchar(50) DEFAULT NULL,
  `urun_adi` varchar(100) DEFAULT NULL,
  `aciklama` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `urunler`
--

INSERT INTO `urunler` (`id`, `barkod`, `urun_adi`, `aciklama`) VALUES
(1, '123', 'elma', '50 kg elma teslim alınmıştır.'),
(2, '123', 'armut', '40 kg armut  eklendi'),
(5, '123', 'xzczc', 'zxczxc'),
(6, '1234567890', 'PAROL', '30\\02\\2006'),
(7, '0987654321', 'ARVELES', '20\\02\\2027'),
(8, '9876543210', 'İBROMİN', '20\\02\\2028'),
(10, '1234567890', 'PAROL', 'xxx'),
(11, '909090909098989', 'ilaç', 'slmflmfldklf'),
(12, '90909090', 'PAROL', 'nnmönmönöm');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '1234');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `depolar`
--
ALTER TABLE `depolar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `hareketler`
--
ALTER TABLE `hareketler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `siparisler`
--
ALTER TABLE `siparisler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_siparis_depo` (`depo_id`);

--
-- Tablo için indeksler `siparis_kalemleri`
--
ALTER TABLE `siparis_kalemleri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kalem_siparis` (`siparis_id`),
  ADD KEY `fk_kalem_urun` (`urun_id`);

--
-- Tablo için indeksler `stok`
--
ALTER TABLE `stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `urun_id` (`urun_id`),
  ADD KEY `depo_id` (`depo_id`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `depolar`
--
ALTER TABLE `depolar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `hareketler`
--
ALTER TABLE `hareketler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Tablo için AUTO_INCREMENT değeri `siparisler`
--
ALTER TABLE `siparisler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `siparis_kalemleri`
--
ALTER TABLE `siparis_kalemleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `stok`
--
ALTER TABLE `stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `stok`
--
ALTER TABLE `stok`
  ADD CONSTRAINT `stok_ibfk_1` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`id`),
  ADD CONSTRAINT `stok_ibfk_2` FOREIGN KEY (`depo_id`) REFERENCES `depolar` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
