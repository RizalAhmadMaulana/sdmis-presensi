<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate_payroll extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index() {
        $logs = [];
        $is_all_success = true;

        $db_name = $this->db->database;
        $host_name = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $is_local = (
            $host_name == 'localhost' || 
            $host_name == '127.0.0.1' || 
            strpos($host_name, '192.168.') === 0 || 
            strpos($host_name, '10.') === 0 || 
            strpos($host_name, '172.') === 0
        );

        // =============================================================
        // STEP 1: Tabel m_gaji_setting_global
        // =============================================================
        $check_t1 = $this->db->query("SHOW TABLES LIKE 'm_gaji_setting_global'")->num_rows();
        if ($check_t1 == 0) {
            $sql_t1 = "CREATE TABLE `m_gaji_setting_global` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t1)) {
                $logs[] = ['step' => 'Tabel Setting Global', 'detail' => 'Tabel `m_gaji_setting_global` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Setting Global', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Setting Global', 'detail' => 'Tabel `m_gaji_setting_global` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 2: Tabel m_gaji_hari_khusus (Waiver Pulang Cepat)
        // =============================================================
        $check_t2 = $this->db->query("SHOW TABLES LIKE 'm_gaji_hari_khusus'")->num_rows();
        if ($check_t2 == 0) {
            $sql_t2 = "CREATE TABLE `m_gaji_hari_khusus` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `tanggal` DATE NOT NULL COMMENT 'Tanggal Hari Khusus Agenda Sekolah',
                `keterangan` VARCHAR(255) NOT NULL COMMENT 'Contoh: Rapat Kerja, Pembagian Rapor, Ujian',
                `boleh_pulang_awal` ENUM('y','n') NOT NULL DEFAULT 'y' COMMENT 'y = Bebas sanksi pulang awal',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_tgl_khusus` (`tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t2)) {
                $logs[] = ['step' => 'Tabel Hari Khusus', 'detail' => 'Tabel `m_gaji_hari_khusus` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Hari Khusus', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Hari Khusus', 'detail' => 'Tabel `m_gaji_hari_khusus` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 3: Tabel m_pegawai_gaji_setting
        // =============================================================
        $check_t3 = $this->db->query("SHOW TABLES LIKE 'm_pegawai_gaji_setting'")->num_rows();
        if ($check_t3 == 0) {
            $sql_t3 = "CREATE TABLE `m_pegawai_gaji_setting` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t3)) {
                $logs[] = ['step' => 'Tabel Profil Gaji Pegawai', 'detail' => 'Tabel `m_pegawai_gaji_setting` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Profil Gaji Pegawai', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Profil Gaji Pegawai', 'detail' => 'Tabel `m_pegawai_gaji_setting` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 4: Tabel m_ekstra (Master Ekstrakurikuler)
        // =============================================================
        $check_t4 = $this->db->query("SHOW TABLES LIKE 'm_ekstra'")->num_rows();
        if ($check_t4 == 0) {
            $sql_t4 = "CREATE TABLE `m_ekstra` (
                `ekstra_id` INT(11) NOT NULL AUTO_INCREMENT,
                `nama_ekstra` VARCHAR(100) NOT NULL,
                `pegawai_id` INT(11) DEFAULT NULL COMMENT 'ID Guru Pengampu',
                `tipe_honor` ENUM('per_anak','per_sesi','per_kedatangan') NOT NULL DEFAULT 'per_anak',
                `tarif_standar` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `keterangan` VARCHAR(255) DEFAULT NULL,
                `is_active` ENUM('y','n') NOT NULL DEFAULT 'y',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`ekstra_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t4)) {
                $logs[] = ['step' => 'Tabel Master Ekstra', 'detail' => 'Tabel `m_ekstra` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Master Ekstra', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Master Ekstra', 'detail' => 'Tabel `m_ekstra` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 5: Tabel tx_ekstra_siswa (Tracking Pelunasan & Carry-Over)
        // =============================================================
        $check_t5 = $this->db->query("SHOW TABLES LIKE 'tx_ekstra_siswa'")->num_rows();
        if ($check_t5 == 0) {
            $sql_t5 = "CREATE TABLE `tx_ekstra_siswa` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t5)) {
                $logs[] = ['step' => 'Tabel Ekstra Siswa', 'detail' => 'Tabel `tx_ekstra_siswa` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Ekstra Siswa', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Ekstra Siswa', 'detail' => 'Tabel `tx_ekstra_siswa` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 6: Tabel tx_gaji_periode (Transaksi Periode Gaji)
        // =============================================================
        $check_t6 = $this->db->query("SHOW TABLES LIKE 'tx_gaji_periode'")->num_rows();
        if ($check_t6 == 0) {
            $sql_t6 = "CREATE TABLE `tx_gaji_periode` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t6)) {
                $logs[] = ['step' => 'Tabel Periode Gaji', 'detail' => 'Tabel `tx_gaji_periode` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Periode Gaji', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Periode Gaji', 'detail' => 'Tabel `tx_gaji_periode` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 7: Tabel tx_gaji_karyawan (Slip Rincian Gaji Bulanan)
        // =============================================================
        $check_t7 = $this->db->query("SHOW TABLES LIKE 'tx_gaji_karyawan'")->num_rows();
        if ($check_t7 == 0) {
            $sql_t7 = "CREATE TABLE `tx_gaji_karyawan` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t7)) {
                $logs[] = ['step' => 'Tabel Gaji Karyawan', 'detail' => 'Tabel `tx_gaji_karyawan` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Gaji Karyawan', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Gaji Karyawan', 'detail' => 'Tabel `tx_gaji_karyawan` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 8: Tabel tx_gaji_komponen_manual (Adjustment)
        // =============================================================
        $check_t8 = $this->db->query("SHOW TABLES LIKE 'tx_gaji_komponen_manual'")->num_rows();
        if ($check_t8 == 0) {
            $sql_t8 = "CREATE TABLE `tx_gaji_komponen_manual` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `gaji_id` INT(11) NOT NULL,
                `jenis` ENUM('penambahan','potongan') NOT NULL,
                `kategori` VARCHAR(50) NOT NULL COMMENT 'talangan, ekskul, transport, kasbon, kesetaraan',
                `keterangan` VARCHAR(255) NOT NULL,
                `nominal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_gaji_id` (`gaji_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_t8)) {
                $logs[] = ['step' => 'Tabel Adjustment Manual', 'detail' => 'Tabel `tx_gaji_komponen_manual` berhasil dibuat.', 'status' => 'success'];
            } else {
                $is_all_success = false;
                $logs[] = ['step' => 'Tabel Adjustment Manual', 'detail' => 'Gagal: ' . $this->db->error()['message'], 'status' => 'danger'];
            }
        } else {
            $logs[] = ['step' => 'Tabel Adjustment Manual', 'detail' => 'Tabel `tx_gaji_komponen_manual` sudah ada.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 9: Pendaftaran Menu Baru di m_menu & m_role_access
        // =============================================================
        // Cek apakah parent menu Penggajian sudah ada
        $check_parent_menu = $this->db->query("SELECT * FROM m_menu WHERE nama_menu='Penggajian'")->row_array();
        if (!$check_parent_menu) {
            $data_parent = [
                'nama_menu' => 'Penggajian',
                'link_url'  => '#',
                'icon'      => 'menu-icon tf-icons ti ti-wallet',
                'parent'    => NULL,
                'tipe'      => 1, // Tipe 1 = Dropdown Menu
                'urutan'    => 8,
                'is_active' => '1'
            ];
            $this->db->insert('m_menu', $data_parent);
            $parent_menu_id = $this->db->insert_id();

            // Beri akses Superadmin (role_id = 1)
            $this->db->insert('m_role_access', ['id_menu' => $parent_menu_id, 'id_role' => 1]);
            // Beri akses role Keuangan/Bendahara jika ada (role_id = 2)
            $check_role2 = $this->db->query("SELECT * FROM m_role WHERE role_id=2")->num_rows();
            if ($check_role2 > 0) {
                $this->db->insert('m_role_access', ['id_menu' => $parent_menu_id, 'id_role' => 2]);
            }

            $logs[] = ['step' => 'Parent Menu', 'detail' => 'Parent menu `Penggajian` berhasil didaftarkan (ID: '.$parent_menu_id.').', 'status' => 'success'];
        } else {
            $parent_menu_id = $check_parent_menu['menu_id'];
            $logs[] = ['step' => 'Parent Menu', 'detail' => 'Parent menu `Penggajian` sudah terdaftar (ID: '.$parent_menu_id.').', 'status' => 'info'];
        }

        // Daftar Submenu Penggajian
        $submenus = [
            ['nama' => 'Pengaturan Aturan & Tarif', 'url' => 'gaji/setting/',      'urutan' => 1],
            ['nama' => 'Master Gaji & Rekening',    'url' => 'gaji/karyawan/',     'urutan' => 2],
            ['nama' => 'Manajemen Ekstra & Siswa',  'url' => 'gaji/ekstra/',       'urutan' => 3],
            ['nama' => 'Periode & Hitung Gaji',     'url' => 'gaji/periode/',      'urutan' => 4],
            ['nama' => 'Rekonsiliasi & Balance',    'url' => 'gaji/rekonsiliasi/', 'urutan' => 5],
            ['nama' => 'Slip Gaji & Rekapitulasi',  'url' => 'gaji/laporan/',      'urutan' => 6],
            ['nama' => 'Export Mutasi Bank',        'url' => 'gaji/export_bank/',  'urutan' => 7]
        ];

        foreach ($submenus as $sm) {
            $check_sm = $this->db->query("SELECT * FROM m_menu WHERE link_url='".$sm['url']."'")->row_array();
            if (!$check_sm) {
                $data_sm = [
                    'nama_menu' => $sm['nama'],
                    'link_url'  => $sm['url'],
                    'icon'      => '',
                    'parent'    => $parent_menu_id,
                    'tipe'      => 2, // Direct Link
                    'urutan'    => $sm['urutan'],
                    'is_active' => '1'
                ];
                $this->db->insert('m_menu', $data_sm);
                $sm_id = $this->db->insert_id();

                // Role access Superadmin (1)
                $this->db->insert('m_role_access', ['id_menu' => $sm_id, 'id_role' => 1]);
                if (isset($check_role2) && $check_role2 > 0) {
                    $this->db->insert('m_role_access', ['id_menu' => $sm_id, 'id_role' => 2]);
                }
                $logs[] = ['step' => 'Submenu ' . $sm['nama'], 'detail' => 'Submenu `'.$sm['nama'].'` berhasil didaftarkan.', 'status' => 'success'];
            } else {
                $logs[] = ['step' => 'Submenu ' . $sm['nama'], 'detail' => 'Submenu `'.$sm['nama'].'` sudah ada.', 'status' => 'info'];
            }
        }

        // =============================================================
        // STEP 10: Seeder Default Setting Global
        // =============================================================
        $check_set = $this->db->query("SELECT * FROM m_gaji_setting_global WHERE setting_id=1")->num_rows();
        if ($check_set == 0) {
            $data_global_default = [
                'setting_id'               => 1,
                'jam_masuk_standar'        => '06:45:00',
                'toleransi_menit'          => 3,
                'jam_masuk_super_early'    => '06:30:00',
                'max_toleransi_telat'      => 3,
                'nominal_bonus_tier1'      => 50000.00,
                'nominal_bonus_tier2'      => 50000.00,
                'tarif_makan_lama'         => 5000.00,
                'tarif_makan_baru'         => 2500.00,
                'tarif_masa_kerja_tahunan' => 15000.00,
                'nominal_wali_kelas'       => 25000.00,
                'rekening_debit_yayasan'   => '219701000399305',
                'bank_sumber_yayasan'      => 'BRI'
            ];
            $this->db->insert('m_gaji_setting_global', $data_global_default);
            $logs[] = ['step' => 'Seeder Setting Global', 'detail' => 'Parameter default (jam 06.45+3m, 06.30, bonus 2-tier) berhasil diisi.', 'status' => 'success'];
        } else {
            $logs[] = ['step' => 'Seeder Setting Global', 'detail' => 'Parameter setting global sudah terisi.', 'status' => 'info'];
        }

        // =============================================================
        // STEP 11: Seeder Master Ekstrakurikuler
        // =============================================================
        $ekstra_default = [
            ['nama' => 'Panahan', 'tipe' => 'per_anak', 'tarif' => 45000.00, 'ket' => 'Honor Rp 45.000 per anak terbayar lunas'],
            ['nama' => 'Sepatu Roda', 'tipe' => 'per_anak', 'tarif' => 60000.00, 'ket' => 'Honor Rp 60.000 per anak terbayar lunas'],
            ['nama' => 'Renang', 'tipe' => 'per_sesi', 'tarif' => 125000.00, 'ket' => 'Honor Rp 125.000 per sesi kehadiran'],
            ['nama' => 'Tahfidz & Tasmi', 'tipe' => 'per_kedatangan', 'tarif' => 25000.00, 'ket' => 'Honor Rp 25.000 per kedatangan']
        ];

        foreach ($ekstra_default as $ed) {
            $cek_ek = $this->db->query("SELECT * FROM m_ekstra WHERE nama_ekstra='".$ed['nama']."'")->num_rows();
            if ($cek_ek == 0) {
                $this->db->insert('m_ekstra', [
                    'nama_ekstra'   => $ed['nama'],
                    'tipe_honor'    => $ed['tipe'],
                    'tarif_standar' => $ed['tarif'],
                    'keterangan'    => $ed['ket'],
                    'is_active'     => 'y'
                ]);
            }
        }
        $logs[] = ['step' => 'Seeder Master Ekstra', 'detail' => 'Master cabang ekstra (Panahan, Sepatu Roda, Renang, Tahfidz) siap digunakan.', 'status' => 'success'];

        // =============================================================
        // STEP 12: Seeder Data 21 Pegawai & Gaji Default (Dari Excel Nov 2026)
        // =============================================================
        // Bersihkan baris dengan pegawai_id = 0 jika ada dari percobaan sebelumnya
        $this->db->query("DELETE FROM m_pegawai_gaji_setting WHERE pegawai_id = 0");

        $pegawai_seed = [
            [
                'id_pegawai' => 'AJ9315.2012.1',
                'nama' => 'Guruh Suasana Eddy',
                'keyword' => 'Guruh',
                'email' => 'guruhsuasana@gmail.com',
                'nomor' => '081234567890',
                'jk' => 'L',
                'tgl_mulai' => '2012-07-01',
                'status_pegawai' => 'gty',
                'gaji_pokok' => 2000000.00,
                'jabatan' => 'Kepala SD',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '359101000105507',
                'pemilik' => 'GURUH SUASANA EDDY',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE001'
            ],
            [
                'id_pegawai' => 'AJ9315.2014.3',
                'nama' => 'Siti Nurhayati Isnaini',
                'keyword' => 'Siti Nurhayati',
                'email' => 'nurhayatisiti862@gmail.com',
                'nomor' => '08812852497',
                'jk' => 'P',
                'tgl_mulai' => '2014-07-01',
                'status_pegawai' => 'gty',
                'gaji_pokok' => 525000.00,
                'jabatan' => 'Kepala PAUD dan TK B MK',
                'tunj_jabatan' => 200000.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '003501092708504',
                'pemilik' => 'Siti Nurhayati Isnaini',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE002'
            ],
            [
                'id_pegawai' => 'AJ9315.2015.4',
                'nama' => 'Samini',
                'keyword' => 'Samini',
                'email' => 'bundamini508@gmail.com',
                'nomor' => '082140955435',
                'jk' => 'P',
                'tgl_mulai' => '2015-07-01',
                'status_pegawai' => 'gty',
                'gaji_pokok' => 500000.00,
                'jabatan' => 'Guru Kelas 1 & Waka',
                'tunj_jabatan' => 250000.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '675401020510535',
                'pemilik' => 'Samini',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE003'
            ],
            [
                'id_pegawai' => 'AJ9315.2016.5',
                'nama' => 'Sri Marsini',
                'keyword' => 'Sri Marsini',
                'email' => 'putriayushenie@gmail.com',
                'nomor' => '081393847291',
                'jk' => 'P',
                'tgl_mulai' => '2016-07-01',
                'status_pegawai' => 'gty',
                'gaji_pokok' => 500000.00,
                'jabatan' => 'Guru PAUD MIS',
                'tunj_jabatan' => 30000.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '675401003851536',
                'pemilik' => 'Sri Marsini',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE004'
            ],
            [
                'id_pegawai' => 'AJ9315.2017.6',
                'nama' => 'Sri Purwanti',
                'keyword' => 'Sri Purwanti',
                'email' => 's.purwanti223@gmail.com',
                'nomor' => '081298471239',
                'jk' => 'P',
                'tgl_mulai' => '2017-07-01',
                'status_pegawai' => 'gty',
                'gaji_pokok' => 500000.00,
                'jabatan' => 'Guru PAUD MIS',
                'tunj_jabatan' => 30000.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '674601028523537',
                'pemilik' => 'Sri Purwanti',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE005'
            ],
            [
                'id_pegawai' => 'AJ9315.2018.8',
                'nama' => 'Anwar Yusup',
                'keyword' => 'Anwar',
                'email' => 'yusupanwar819@gmail.com',
                'nomor' => '085728391823',
                'jk' => 'L',
                'tgl_mulai' => '2018-07-01',
                'status_pegawai' => 'gty',
                'gaji_pokok' => 1500000.00,
                'jabatan' => 'Waka Sarpras & Admin',
                'tunj_jabatan' => 300000.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '673201025319532',
                'pemilik' => 'Sumarsih',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE006'
            ],
            [
                'id_pegawai' => 'AJ9315.2022.10',
                'nama' => 'Nuki Rifan Yuniardi',
                'keyword' => 'Nuki',
                'email' => 'paknukirifan@cyberschool.sch.id',
                'nomor' => '082243619388',
                'jk' => 'L',
                'tgl_mulai' => '2022-07-01',
                'status_pegawai' => 'gty',
                'gaji_pokok' => 500000.00,
                'jabatan' => 'Guru Kelas & Admin IT',
                'tunj_jabatan' => 1100000.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '674401022351536',
                'pemilik' => 'Nuki Rifan Yuniardi',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE007'
            ],
            [
                'id_pegawai' => 'AJ9315.2022.11',
                'nama' => 'Dewi Fatmala Sari',
                'keyword' => 'Dewi Fatmala',
                'email' => 'fatmade78@gmail.com',
                'nomor' => '08156166650',
                'jk' => 'P',
                'tgl_mulai' => '2022-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 550000.00,
                'jabatan' => 'Guru Mapel & Admin',
                'tunj_jabatan' => 100000.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '389501039726532',
                'pemilik' => 'Dewi Fatmala Sari',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE008'
            ],
            [
                'id_pegawai' => 'AJ9315.2023.13',
                'nama' => 'Giyanti',
                'keyword' => 'Giyanti',
                'email' => 'giyanti8871@gmail.com',
                'nomor' => '085819283741',
                'jk' => 'P',
                'tgl_mulai' => '2023-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 550000.00,
                'jabatan' => 'Guru PAUD MK',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '675401024146532',
                'pemilik' => 'Giyanti',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE009'
            ],
            [
                'id_pegawai' => 'AJ9315.2024.14',
                'nama' => 'Eka Fitri Nurhayati',
                'keyword' => 'Eka Fitri',
                'email' => 'ekafitrinurhayati0998@gmail.com',
                'nomor' => '089618293847',
                'jk' => 'P',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 550000.00,
                'jabatan' => 'Guru Kelas',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '673701022223538',
                'pemilik' => 'Eka Fitri Nurhayati',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE010'
            ],
            [
                'id_pegawai' => 'AJ9315.2024.15',
                'nama' => 'Neta Arum Sholikhah',
                'keyword' => 'Neta',
                'email' => 'netasukamane@gmail.com',
                'nomor' => '087819283746',
                'jk' => 'P',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 550000.00,
                'jabatan' => 'Guru Kelas',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '674601028497532',
                'pemilik' => 'Neta Arum Sholikhah',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE011'
            ],
            [
                'id_pegawai' => 'AJ9315.2024.17',
                'nama' => 'Atii Tasmiyaturrosyadi',
                'keyword' => 'Atii',
                'email' => 'miarosyadi@gmail.com',
                'nomor' => '081928374655',
                'jk' => 'P',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 550000.00,
                'jabatan' => 'Guru Mapel',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '674601017873533',
                'pemilik' => 'Atii Tasmiyaturrosyadi',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE012'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.20',
                'nama' => 'Afkhan Rico Toriq Ibrahim',
                'keyword' => 'Rico',
                'email' => 'afkhanricoxtlb02@gmail.com',
                'nomor' => '082193847562',
                'jk' => 'L',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 550000.00,
                'jabatan' => 'Guru SD & Koordinator Tahfidz',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '690201016686533',
                'pemilik' => 'AFKHAN RICO TORIQ IBRAHIM',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE013'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.23',
                'nama' => 'Hanivia Anggraheni Damayanti',
                'keyword' => 'Hanivia',
                'email' => 'damayantihanivia@gmail.com',
                'nomor' => '085619283746',
                'jk' => 'P',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 500000.00,
                'jabatan' => 'Guru PAUD',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '674601029611537',
                'pemilik' => 'Hanivia Anggraheni Damayanti',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE014'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.26',
                'nama' => 'Habila Hajar Hanifah',
                'keyword' => 'Habila',
                'email' => 'habilahajar@gmail.com',
                'nomor' => '081392847561',
                'jk' => 'P',
                'tgl_mulai' => '2024-10-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 550000.00,
                'jabatan' => 'Pengajar & Wali Kelas 3',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'y',
                'bank' => 'BRI',
                'rekening' => '389301010097500',
                'pemilik' => 'Habila Hajar Hanifah',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE015'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.30',
                'nama' => 'Aan Sholeh',
                'keyword' => 'Aan Sholeh',
                'email' => 'aansholeh23@gmail.com',
                'nomor' => '087719283745',
                'jk' => 'L',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'kontrak',
                'gaji_pokok' => 525000.00,
                'jabatan' => 'Guru PAUD',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '798901011399538',
                'pemilik' => 'Aan Sholeh',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE016'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.31',
                'nama' => 'Anang Yulianto',
                'keyword' => 'Anang',
                'email' => 'lembahmanahbarokah@gmail.com',
                'nomor' => '081298374612',
                'jk' => 'L',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'guru_ekstra',
                'gaji_pokok' => 0.00,
                'jabatan' => 'Guru Ekstra Panahan',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BSI',
                'rekening' => '7218928883',
                'pemilik' => 'Anang Yulianto',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE017'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.32',
                'nama' => 'Wahyu Anggoro Jati',
                'keyword' => 'Wahyu',
                'email' => 'wahyuanggoro891@gmail.com',
                'nomor' => '085819283742',
                'jk' => 'L',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'guru_ekstra',
                'gaji_pokok' => 0.00,
                'jabatan' => 'Guru Sepatu Roda',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '003501026559537',
                'pemilik' => 'Wahyu Anggoro Jati',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE018'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.33',
                'nama' => 'Kak Annas',
                'keyword' => 'Annas',
                'email' => 'kakannas.tahfidz@cyberschool.sch.id',
                'nomor' => '081928374100',
                'jk' => 'L',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'guru_ekstra',
                'gaji_pokok' => 0.00,
                'jabatan' => 'Guru Tahfidz PAUD & SD',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '137201001651509',
                'pemilik' => 'GURUH SUASANA EDDY',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE019'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.34',
                'nama' => 'Trisuyoto',
                'keyword' => 'Trisuyoto',
                'email' => 'trisuyoto@gmail.com',
                'nomor' => '081298471201',
                'jk' => 'L',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'karyawan',
                'gaji_pokok' => 1500000.00,
                'jabatan' => 'Guru PJOK & Ekstra Renang',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BRI',
                'rekening' => '205501004867530',
                'pemilik' => 'TRISUYOTO',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE020'
            ],
            [
                'id_pegawai' => 'AJ9315.2025.35',
                'nama' => 'Ayu Prastiwi',
                'keyword' => 'Ayu Prastiwi',
                'email' => 'ayuprastiwi.pko@gmail.com',
                'nomor' => '081392847102',
                'jk' => 'P',
                'tgl_mulai' => '2024-07-01',
                'status_pegawai' => 'guru_ekstra',
                'gaji_pokok' => 500000.00,
                'jabatan' => 'Ekstra Renang',
                'tunj_jabatan' => 0.00,
                'is_wali' => 'n',
                'bank' => 'BSI',
                'rekening' => '7116214169',
                'pemilik' => 'Ayu Prastiwi',
                'cabang' => 'Klaten',
                'reff' => 'MTNOVE021'
            ]
        ];

        $inserted_pegawai = 0;
        $updated_setting = 0;

        // Ambil ID integer tertinggi di m_pegawai untuk auto-numbering pegawai baru
        $max_id_q = $this->db->query("SELECT MAX(CAST(id_pegawai AS UNSIGNED)) as max_id FROM m_pegawai")->row_array();
        $current_next_id = (isset($max_id_q['max_id']) && (int)$max_id_q['max_id'] > 0) ? ((int)$max_id_q['max_id'] + 1) : 100;

        foreach ($pegawai_seed as $ps) {
            $keyword = !empty($ps['keyword']) ? $ps['keyword'] : $ps['nama'];

            // Smart Matching:
            // 1. Cek email pegawai
            // 2. Cek keyword nama (misal "Siti Nurhayati", "Dewi Fatmala")
            // 3. Cek apakah nama_pegawai di DB terkandung di dalam nama seeder
            $q_peg = $this->db->query(
                "SELECT * FROM m_pegawai 
                 WHERE (email_pegawai = ? AND email_pegawai != '')
                    OR nama_pegawai LIKE ? 
                    OR ? LIKE CONCAT('%', nama_pegawai, '%')
                 LIMIT 1",
                [$ps['email'], '%' . $keyword . '%', $ps['nama']]
            )->row_array();

            if (!$q_peg) {
                // Daftarkan pegawai baru dengan id_pegawai berupa integer angka valid
                $data_ins = [
                    'id_pegawai'          => $current_next_id,
                    'nama_pegawai'        => $ps['nama'],
                    'email_pegawai'       => $ps['email'],
                    'nomor_pegawai'       => $ps['nomor'],
                    'jenis_kelamin'       => $ps['jk'],
                    'tanggal_mulai_kerja' => $ps['tgl_mulai'],
                    'password_pegawai'    => password_hash('123456', PASSWORD_DEFAULT),
                    'foto_pegawai'        => 'assets/uploaded/users/default-logo.png',
                    'is_status'           => 'y',
                    'is_del'              => 'n',
                    'created_at'          => date('Y-m-d H:i:s')
                ];
                $this->db->insert('m_pegawai', $data_ins);
                $pid = (int)$this->db->insert_id();
                $current_next_id++;
                $inserted_pegawai++;
            } else {
                $pid = (int)$q_peg['pegawai_id'];
                // Update tanggal mulai kerja jika belum akurat
                if (empty($q_peg['tanggal_mulai_kerja']) || $q_peg['tanggal_mulai_kerja'] == '0000-00-00') {
                    $this->db->where('pegawai_id', $pid)->update('m_pegawai', ['tanggal_mulai_kerja' => $ps['tgl_mulai']]);
                }
            }

            if ($pid > 0) {
                // Insert / Update setting gaji pegawai
                $q_set = $this->db->query("SELECT * FROM m_pegawai_gaji_setting WHERE pegawai_id = ?", [$pid])->row_array();
                $data_gaji_set = [
                    'pegawai_id'              => $pid,
                    'status_kepegawaian'      => $ps['status_pegawai'],
                    'is_wali_kelas'           => $ps['is_wali'],
                    'nama_jabatan_struktural' => $ps['jabatan'],
                    'nominal_tunj_struktural' => $ps['tunj_jabatan'],
                    'gaji_pokok_nominal'      => $ps['gaji_pokok'],
                    'nama_bank'               => $ps['bank'],
                    'nomor_rekening'          => $ps['rekening'],
                    'nama_pemilik_rekening'   => $ps['pemilik'],
                    'cabang_bank'             => $ps['cabang'],
                    'customer_reff'           => $ps['reff']
                ];

                if (!$q_set) {
                    $this->db->insert('m_pegawai_gaji_setting', $data_gaji_set);
                    $updated_setting++;
                } else {
                    $this->db->where('pegawai_id', $pid)->update('m_pegawai_gaji_setting', $data_gaji_set);
                    $updated_setting++;
                }
            }
        }

        $logs[] = [
            'step' => 'Seeder 21 Guru & Rekening',
            'detail' => 'Berhasil sinkronisasi profil 21 guru dan data perbankan (Insert Pegawai: '.$inserted_pegawai.', Setting Gaji: '.$updated_setting.').',
            'status' => 'success'
        ];

        // Tampilkan Laporan HTML Migrasi
        $this->_render_ui($logs, $is_all_success, $host_name, $db_name, $is_local);
    }

    private function _render_ui($logs, $is_all_success, $host_name, $db_name, $is_local) {
        $env_label = $is_local ? 'LOKAL / PENGEMBANGAN' : 'PRODUKSI / HOSTINGER LIVE';
        $badge_color = $is_local ? '#0d6efd' : '#198754';
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Auto-Migration: Modul Penggajian SDMIS</title>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: 'Public Sans', sans-serif; background-color: #f4f5fa; color: #566a7f; padding: 40px 20px; }
                .container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1); overflow: hidden; }
                .header { background: linear-gradient(135deg, #0d6efd, #0b5ed7); padding: 30px; color: #fff; }
                .header h1 { font-size: 24px; font-weight: 700; margin-bottom: 6px; }
                .header p { font-size: 14px; opacity: 0.9; }
                .content { padding: 30px; }
                .meta-card { display: flex; gap: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px 20px; margin-bottom: 25px; flex-wrap: wrap; }
                .meta-item { flex: 1; min-width: 150px; }
                .meta-label { font-size: 12px; text-transform: uppercase; color: #6c757d; font-weight: 600; margin-bottom: 4px; }
                .meta-val { font-size: 15px; font-weight: 600; color: #212529; }
                .env-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; color: #fff; background: <?= $badge_color; ?>; }
                .table-logs { width: 100%; border-collapse: collapse; margin-top: 15px; }
                .table-logs th, .table-logs td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e9ecef; font-size: 14px; }
                .table-logs th { background-color: #f1f3f5; color: #495057; font-weight: 600; }
                .status-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
                .status-success { background: #d1e7dd; color: #0f5132; }
                .status-info { background: #cff4fc; color: #055160; }
                .status-warning { background: #fff3cd; color: #664d03; }
                .status-danger { background: #f8d7da; color: #842029; }
                .banner { padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; }
                .banner-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
                .banner-danger { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
                .footer { padding: 20px 30px; background: #f8f9fa; border-top: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
                .btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; }
                .btn:hover { background: #0b5ed7; }
                .btn-secondary { background: #6c757d; }
                .btn-secondary:hover { background: #5c636a; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Auto-Migration Database: Modul Penggajian & Mukafaah</h1>
                    <p>Sistem Penggajian Terintegrasi Presensi & Pelunasan Ekstra &mdash; SD Mentari Islamic School</p>
                </div>
                <div class="content">
                    <div class="meta-card">
                        <div class="meta-item">
                            <div class="meta-label">Environment</div>
                            <div class="meta-val"><span class="env-badge"><?= $env_label; ?></span></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Host Server</div>
                            <div class="meta-val"><?= htmlspecialchars($host_name); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Database Aktif</div>
                            <div class="meta-val"><?= htmlspecialchars($db_name); ?></div>
                        </div>
                    </div>

                    <?php if ($is_all_success): ?>
                        <div class="banner banner-success">
                            <strong>Status Migrasi: BERHASIL SEMPURNA!</strong> &nbsp; Seluruh 8 tabel penggajian, menu portal, dan seeder 21 guru siap digunakan untuk Tahap 2.
                        </div>
                    <?php else: ?>
                        <div class="banner banner-danger">
                            <strong>Status Migrasi: DITEMUKAN MASALAH.</strong> &nbsp; Periksa pesan error pada tabel log di bawah.
                        </div>
                    <?php endif; ?>

                    <table class="table-logs">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Komponen</th>
                                <th>Detail Operasi</th>
                                <th style="width: 15%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($log['step']); ?></strong></td>
                                    <td><?= htmlspecialchars($log['detail']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $log['status']; ?>">
                                            <?= strtoupper($log['status'] == 'info' ? 'TERSEDIA' : ($log['status'] == 'success' ? 'SUKSES' : $log['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="footer">
                    <span>SD Mentari Islamic School &copy; 2026</span>
                    <a href="<?= base_url('dashboard'); ?>" class="btn">Kembali ke Dashboard Admin</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
