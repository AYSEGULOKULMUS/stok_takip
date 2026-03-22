-- ============================================================
-- Sipariş Modülü — Veritabanı Tabloları
-- stok_sistemi veritabanına çalıştırın
-- ============================================================

CREATE TABLE IF NOT EXISTS `siparisler` (
  `id`                INT(11) NOT NULL AUTO_INCREMENT,
  `tedarikci`         VARCHAR(200) NOT NULL,
  `depo_id`           INT(11) DEFAULT NULL,
  `hedef_depo_adi`    VARCHAR(200) DEFAULT '',
  `notlar`            TEXT DEFAULT NULL,
  `durum`             ENUM('beklemede','onaylandi','teslim_edildi','iptal') NOT NULL DEFAULT 'beklemede',
  `kullanici_id`      INT(11) DEFAULT NULL,
  `olusturma_tarihi`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_siparis_depo` (`depo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `siparis_kalemleri` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `siparis_id`  INT(11) NOT NULL,
  `urun_id`     INT(11) NOT NULL,
  `miktar`      INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_kalem_siparis` (`siparis_id`),
  KEY `fk_kalem_urun`    (`urun_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Foreign key kısıtlamaları (isteğe bağlı)
-- ALTER TABLE `siparisler`
--   ADD CONSTRAINT `fk_siparis_depo` FOREIGN KEY (`depo_id`) REFERENCES `depolar` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `siparis_kalemleri`
--   ADD CONSTRAINT `fk_kalem_siparis` FOREIGN KEY (`siparis_id`) REFERENCES `siparisler` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_kalem_urun`    FOREIGN KEY (`urun_id`)    REFERENCES `urunler`    (`id`) ON DELETE RESTRICT;
