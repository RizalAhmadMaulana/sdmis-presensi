-- ====================================================================
-- MIGRASI DATABASE: FITUR FACE RECOGNITION (SDMIS & TEMANMIS PRESENSI)
-- Database Target: db_absensi (Localhost) / u983578927_absensi (Hostinger)
-- ====================================================================

-- 1. Buat Tabel Biometrik Wajah Pegawai (m_pegawai_face)
CREATE TABLE IF NOT EXISTS `m_pegawai_face` (
  `face_id` INT(11) NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT(11) NOT NULL,
  `descriptor_front` LONGTEXT NOT NULL COMMENT 'Array 128 Float Vektor Wajah Hadap Depan',
  `descriptor_right` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Serong Kanan',
  `descriptor_left` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Serong Kiri',
  `descriptor_up` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Hadap Atas',
  `descriptor_down` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Hadap Bawah',
  `foto_sample` VARCHAR(255) DEFAULT NULL COMMENT 'Path Foto Sampel Registrasi Wajah',
  `is_verified` CHAR(1) NOT NULL DEFAULT 'n' COMMENT 'y = Terverifikasi Admin, n = Menunggu Approval',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`face_id`),
  KEY `idx_pegawai_face` (`pegawai_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tambah Flag Registrasi Wajah di m_pegawai (jika belum ada)
SET @dbname = DATABASE();
SET @tablename = "m_pegawai";
SET @columnname = "is_face_registered";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE m_pegawai ADD COLUMN is_face_registered CHAR(1) NOT NULL DEFAULT 'n' AFTER foto_pegawai;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Tambah Kolom Audit Face Matching di tx_absensi (jika belum ada)
SET @tablename = "tx_absensi";
SET @columnname = "face_similarity_score";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE tx_absensi ADD COLUMN face_similarity_score VARCHAR(10) DEFAULT NULL AFTER foto_absen_keluar, ADD COLUMN face_status VARCHAR(20) DEFAULT NULL AFTER face_similarity_score;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
