-- =========================================================================
-- SQL MIGRATION SCRIPT: MODUL PENGGAJIAN & MUKAFAAH SDMIS
-- Proyek   : SD Mentari Islamic School
-- Database : db_absensi (Lokal) / u983578927_absensi (Hostinger Live)
-- Tanggal  : 25 November 2026
-- Sifat    : 100% AMAN (Klausul IF NOT EXISTS - Zero Data Loss)
-- =========================================================================

-- 1. TABEL SETTING GLOBAL FORMULA GAJI
CREATE TABLE IF NOT EXISTS `m_gaji_setting_global` (
    `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
    `jam_masuk_standar` TIME NOT NULL DEFAULT '06:45:00' COMMENT 'Jam Masuk Kerja Acuan Standar',
    `toleransi_menit` INT(11) NOT NULL DEFAULT 3 COMMENT 'Toleransi Menit Keterlambatan (06:48:00)',
    `jam_masuk_super_early` TIME NOT NULL DEFAULT '06:30:00' COMMENT 'Jam Acuan Hadir Super Early Tier 2',
    `max_toleransi_telat` INT(11) NOT NULL DEFAULT 3 COMMENT 'Maksimal Pelanggaran per Bulan',
    `nominal_bonus_tier1` DECIMAL(12,2) NOT NULL DEFAULT 50000.00 COMMENT 'Bonus Hadir Tepat Waktu Standar (<=06.48)',
    `nominal_bonus_tier2` DECIMAL(12,2) NOT NULL DEFAULT 50000.00 COMMENT 'Bonus Hadir Pagi Super Early (<=06.30)',
    `tarif_makan_lama` DECIMAL(12,2) NOT NULL DEFAULT 5000.00 COMMENT 'Uang Makan Guru Lama >3 thn per kehadiran',
    `tarif_makan_baru` DECIMAL(12,2) NOT NULL DEFAULT 2500.00 COMMENT 'Uang Makan Guru Baru <=3 thn per kehadiran',
    `tarif_masa_kerja_tahunan` DECIMAL(12,2) NOT NULL DEFAULT 15000.00 COMMENT 'Tarif per tahun masa kerja GTY',
    `nominal_wali_kelas` DECIMAL(12,2) NOT NULL DEFAULT 25000.00 COMMENT 'Tunjangan Wali Kelas bulanan',
    `rekening_debit_yayasan` VARCHAR(50) DEFAULT '219701000399305' COMMENT 'Rekening Sumber Operasional Yayasan',
    `bank_sumber_yayasan` VARCHAR(50) DEFAULT 'BRI' COMMENT 'Nama Bank Sumber Yayasan',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. TABEL HARI KHUSUS (WAIVER PULANG CEPAT)
CREATE TABLE IF NOT EXISTS `m_gaji_hari_khusus` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tanggal` DATE NOT NULL COMMENT 'Tanggal Hari Khusus Agenda Sekolah',
    `keterangan` VARCHAR(255) NOT NULL COMMENT 'Contoh: Rapat Kerja, Pembagian Rapor, Ujian',
    `boleh_pulang_awal` ENUM('y','n') NOT NULL DEFAULT 'y' COMMENT 'y = Bebas sanksi pulang awal',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_tgl_khusus` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. TABEL MASTER GAJI & REKENING PEGAWAI
CREATE TABLE IF NOT EXISTS `m_pegawai_gaji_setting` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pegawai_id` INT(11) NOT NULL,
    `status_kepegawaian` ENUM('gty','kontrak','guru_ekstra','karyawan') NOT NULL DEFAULT 'gty',
    `is_wali_kelas` ENUM('y','n') NOT NULL DEFAULT 'n',
    `nama_jabatan_struktural` VARCHAR(100) DEFAULT NULL COMMENT 'Kepala Sekolah / Waka / PJ Sarpras',
    `nominal_tunj_struktural` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `gaji_pokok_nominal` DECIMAL(12,2) NOT NULL DEFAULT 500000.00,
    `tunj_khusus_tetap` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Tunjangan Khusus / Tugas Tambahan',
    `nama_bank` VARCHAR(50) DEFAULT 'BRI',
    `nomor_rekening` VARCHAR(50) DEFAULT NULL,
    `nama_pemilik_rekening` VARCHAR(150) DEFAULT NULL,
    `cabang_bank` VARCHAR(100) DEFAULT 'Klaten',
    `customer_reff` VARCHAR(50) DEFAULT NULL COMMENT 'Kode Reff Internet Banking, e.g. MTNOVE001',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_pegawai_gaji` (`pegawai_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. TABEL MASTER EKSTRAKURIKULER
CREATE TABLE IF NOT EXISTS `m_ekstra` (
    `ekstra_id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_ekstra` VARCHAR(100) NOT NULL,
    `pegawai_id` INT(11) DEFAULT NULL COMMENT 'ID Guru Pengampu',
    `tipe_honor` ENUM('per_anak','per_sesi','per_kedatangan') NOT NULL DEFAULT 'per_anak',
    `tarif_standar` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `keterangan` VARCHAR(255) DEFAULT NULL,
    `is_active` ENUM('y','n') NOT NULL DEFAULT 'y',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ekstra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. TABEL TRACKING PELUNASAN EKSTRA SISWA & CARRY-OVER
CREATE TABLE IF NOT EXISTS `tx_ekstra_siswa` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `ekstra_id` INT(11) NOT NULL,
    `nama_siswa` VARCHAR(150) NOT NULL,
    `kelas` VARCHAR(50) DEFAULT NULL,
    `bulan` TINYINT(2) NOT NULL,
    `tahun` YEAR NOT NULL,
    `status_bayar` ENUM('lunas','pending') NOT NULL DEFAULT 'pending',
    `tanggal_bayar` DATE DEFAULT NULL,
    `nominal_tagihan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `keterangan` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ekstra_bulan` (`ekstra_id`, `bulan`, `tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. TABEL HEADER TRANSAKSI PERIODE GAJI
CREATE TABLE IF NOT EXISTS `tx_gaji_periode` (
    `periode_id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama_periode` VARCHAR(100) NOT NULL COMMENT 'Contoh: Mukafaah November 2026',
    `bulan` TINYINT(2) NOT NULL,
    `tahun` YEAR NOT NULL,
    `tanggal_awal` DATE NOT NULL COMMENT 'Awal Cut-Off Evaluasi Presensi',
    `tanggal_akhir` DATE NOT NULL COMMENT 'Akhir Cut-Off Evaluasi Presensi',
    `total_hari_kerja_aktif` INT(11) NOT NULL DEFAULT 20,
    `total_pengeluaran` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status_periode` ENUM('draft','dihitung','final','terbayar') NOT NULL DEFAULT 'draft',
    `created_by` INT(11) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`periode_id`),
    UNIQUE KEY `idx_bulan_tahun` (`bulan`, `tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. TABEL DETAIL SLIP GAJI KARYAWAN
CREATE TABLE IF NOT EXISTS `tx_gaji_karyawan` (
    `gaji_id` INT(11) NOT NULL AUTO_INCREMENT,
    `periode_id` INT(11) NOT NULL,
    `pegawai_id` INT(11) NOT NULL,
    `gaji_pokok` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `masa_kerja_tahun` INT(11) NOT NULL DEFAULT 0,
    `tunj_masa_kerja` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tunj_wali_kelas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tunj_struktural` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `hari_hadir_presensi` INT(11) NOT NULL DEFAULT 0,
    `tunj_makan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `telat_tier1_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Telat Datang > 06.48',
    `datang_lewat_0630_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Datang > 06.30',
    `bonus_presensi_tier1` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Rp 50.000 jika telat <= 3x',
    `bonus_presensi_tier2` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Rp 50.000 jika datang > 06.30 <= 3x',
    `total_tunj_presensi` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Tier 1 + Tier 2 (Rp 0 s/d Rp 100.000)',
    `honor_ekstra_lunas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `honor_ekstra_pending` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Ditangguhkan karena siswa belum lunas',
    `tunj_kesetaraan` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'ANBK, TKA, Upload Data',
    `tunj_transport` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Luar Kota, Klaten, Jatinom, Tulung',
    `apresiasi_sertifikasi` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `talangan_operasional` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Penggantian dana talangan sekolah',
    `total_penghasilan_kotor` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `potongan_kasbon` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `potongan_talangan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `potongan_lainnya` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_potongan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `take_home_pay` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `catatan_penyesuaian` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`gaji_id`),
    UNIQUE KEY `idx_periode_pegawai` (`periode_id`, `pegawai_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. TABEL ADJUSTMENT MANUAL (TALANGAN, KASBON, TRANSPORT)
CREATE TABLE IF NOT EXISTS `tx_gaji_komponen_manual` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `gaji_id` INT(11) NOT NULL,
    `jenis` ENUM('penambahan','potongan') NOT NULL,
    `kategori` VARCHAR(50) NOT NULL COMMENT 'talangan, ekskul, transport, kasbon, kesetaraan',
    `keterangan` VARCHAR(255) NOT NULL,
    `nominal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gaji_id` (`gaji_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. INSERT SETTING GLOBAL DEFAULT
INSERT INTO `m_gaji_setting_global` (`setting_id`, `jam_masuk_standar`, `toleransi_menit`, `jam_masuk_super_early`, `max_toleransi_telat`, `nominal_bonus_tier1`, `nominal_bonus_tier2`, `tarif_makan_lama`, `tarif_makan_baru`, `tarif_masa_kerja_tahunan`, `nominal_wali_kelas`, `rekening_debit_yayasan`, `bank_sumber_yayasan`)
SELECT 1, '06:45:00', 3, '06:30:00', 3, 50000.00, 50000.00, 5000.00, 2500.00, 15000.00, 25000.00, '219701000399305', 'BRI'
FROM DUAL
WHERE NOT EXISTS (SELECT * FROM `m_gaji_setting_global` WHERE `setting_id` = 1);
