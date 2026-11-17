<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate_face extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index() {
        $logs = [];
        $is_all_success = true;

        $db_name = $this->db->database;
        $host_name = $_SERVER['HTTP_HOST'];
        $is_local = (
            $host_name == 'localhost' || 
            $host_name == '127.0.0.1' || 
            strpos($host_name, '192.168.') === 0 || 
            strpos($host_name, '10.') === 0 || 
            strpos($host_name, '172.') === 0
        );

        // -------------------------------------------------------------
        // STEP 1: Cek & Buat Tabel m_pegawai_face
        // -------------------------------------------------------------
        $check_table = $this->db->query("SHOW TABLES LIKE 'm_pegawai_face'")->num_rows();
        if ($check_table == 0) {
            $sql_create_table = "CREATE TABLE `m_pegawai_face` (
                `face_id` INT(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` INT(11) NOT NULL,
                `descriptor_front` LONGTEXT NOT NULL COMMENT 'Array 128 Float Vektor Wajah Hadap Depan',
                `descriptor_right` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Serong Kanan',
                `descriptor_left` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Serong Kiri',
                `descriptor_up` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Hadap Atas',
                `descriptor_down` TEXT DEFAULT NULL COMMENT 'Array 128 Float Vektor Wajah Hadap Bawah',
                `foto_sample` VARCHAR(255) DEFAULT NULL COMMENT 'Path Foto Sampel Registrasi Wajah',
                `is_verified` CHAR(1) NOT NULL DEFAULT 'n' COMMENT 'y = Terverifikasi, n = Pending Approval',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`face_id`),
                KEY `idx_pegawai_face` (`pegawai_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

            if ($this->db->query($sql_create_table)) {
                $logs[] = [
                    'step' => 'Tabel Biometrik',
                    'detail' => 'Tabel `m_pegawai_face` berhasil dibuat.',
                    'status' => 'success'
                ];
            } else {
                $is_all_success = false;
                $logs[] = [
                    'step' => 'Tabel Biometrik',
                    'detail' => 'Gagal membuat tabel `m_pegawai_face`: ' . $this->db->error()['message'],
                    'status' => 'danger'
                ];
            }
        } else {
            $logs[] = [
                'step' => 'Tabel Biometrik',
                'detail' => 'Tabel `m_pegawai_face` sudah ada (tersedia).',
                'status' => 'info'
            ];
        }

        // -------------------------------------------------------------
        // STEP 2: Cek & Tambah Kolom is_face_registered di m_pegawai
        // -------------------------------------------------------------
        $check_col_pegawai = $this->db->query("SHOW COLUMNS FROM `m_pegawai` LIKE 'is_face_registered'")->num_rows();
        if ($check_col_pegawai == 0) {
            $sql_alter_pegawai = "ALTER TABLE `m_pegawai` ADD COLUMN `is_face_registered` CHAR(1) NOT NULL DEFAULT 'n' AFTER `foto_pegawai`";
            if ($this->db->query($sql_alter_pegawai)) {
                $logs[] = [
                    'step' => 'Kolom m_pegawai',
                    'detail' => 'Kolom `is_face_registered` berhasil ditambahkan di tabel `m_pegawai`.',
                    'status' => 'success'
                ];
            } else {
                $is_all_success = false;
                $logs[] = [
                    'step' => 'Kolom m_pegawai',
                    'detail' => 'Gagal menambah kolom `is_face_registered`: ' . $this->db->error()['message'],
                    'status' => 'danger'
                ];
            }
        } else {
            $logs[] = [
                'step' => 'Kolom m_pegawai',
                'detail' => 'Kolom `is_face_registered` pada `m_pegawai` sudah ada.',
                'status' => 'info'
            ];
        }

        // -------------------------------------------------------------
        // STEP 3: Cek & Tambah Kolom Audit di tx_absensi
        // -------------------------------------------------------------
        $check_col_score = $this->db->query("SHOW COLUMNS FROM `tx_absensi` LIKE 'face_similarity_score'")->num_rows();
        if ($check_col_score == 0) {
            $sql_alter_tx1 = "ALTER TABLE `tx_absensi` ADD COLUMN `face_similarity_score` VARCHAR(10) DEFAULT NULL AFTER `foto_absen_keluar`";
            if ($this->db->query($sql_alter_tx1)) {
                $logs[] = [
                    'step' => 'Audit tx_absensi',
                    'detail' => 'Kolom `face_similarity_score` berhasil ditambahkan di tabel `tx_absensi`.',
                    'status' => 'success'
                ];
            } else {
                $is_all_success = false;
                $logs[] = [
                    'step' => 'Audit tx_absensi',
                    'detail' => 'Gagal menambah kolom `face_similarity_score`: ' . $this->db->error()['message'],
                    'status' => 'danger'
                ];
            }
        } else {
            $logs[] = [
                'step' => 'Audit tx_absensi',
                'detail' => 'Kolom `face_similarity_score` pada `tx_absensi` sudah ada.',
                'status' => 'info'
            ];
        }

        $check_col_status = $this->db->query("SHOW COLUMNS FROM `tx_absensi` LIKE 'face_status'")->num_rows();
        if ($check_col_status == 0) {
            $sql_alter_tx2 = "ALTER TABLE `tx_absensi` ADD COLUMN `face_status` VARCHAR(20) DEFAULT NULL AFTER `face_similarity_score`";
            if ($this->db->query($sql_alter_tx2)) {
                $logs[] = [
                    'step' => 'Audit tx_absensi',
                    'detail' => 'Kolom `face_status` berhasil ditambahkan di tabel `tx_absensi`.',
                    'status' => 'success'
                ];
            } else {
                $is_all_success = false;
                $logs[] = [
                    'step' => 'Audit tx_absensi',
                    'detail' => 'Gagal menambah kolom `face_status`: ' . $this->db->error()['message'],
                    'status' => 'danger'
                ];
            }
        } else {
            $logs[] = [
                'step' => 'Audit tx_absensi',
                'detail' => 'Kolom `face_status` pada `tx_absensi` sudah ada.',
                'status' => 'info'
            ];
        }

        // -------------------------------------------------------------
        // STEP 4: Cek & Buat Direktori Penyimpanan Foto Wajah
        // -------------------------------------------------------------
        $dir_faces = FCPATH . 'assets/uploaded/faces/';
        if (!is_dir($dir_faces)) {
            if (@mkdir($dir_faces, 0777, true)) {
                @file_put_contents($dir_faces . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
                $logs[] = [
                    'step' => 'Direktori Storage',
                    'detail' => 'Folder `assets/uploaded/faces/` berhasil dibuat untuk menampung foto biometrik.',
                    'status' => 'success'
                ];
            } else {
                $logs[] = [
                    'step' => 'Direktori Storage',
                    'detail' => 'Peringatan: Gagal membuat folder otomatis `assets/uploaded/faces/`. Silakan buat folder secara manual.',
                    'status' => 'warning'
                ];
            }
        } else {
            $logs[] = [
                'step' => 'Direktori Storage',
                'detail' => 'Folder `assets/uploaded/faces/` sudah tersedia.',
                'status' => 'info'
            ];
        }

        // Tampilkan Dashboard Hasil Migrasi
        $this->_render_dashboard($is_all_success, $logs, $db_name, $host_name, $is_local);
    }

    private function _render_dashboard($is_all_success, $logs, $db_name, $host_name, $is_local) {
        $env_label = $is_local ? 'Localhost (Development)' : 'Hostinger (Production)';
        $badge_color = $is_local ? '#0d6efd' : '#198754';
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Migrasi Database Face Recognition - SDMIS</title>
            <style>
                * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
                body { background-color: #f4f6f9; margin: 0; padding: 30px 15px; color: #333; }
                .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
                .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 25px 30px; }
                .header h1 { margin: 0 0 8px 0; font-size: 24px; font-weight: 700; }
                .header p { margin: 0; opacity: 0.9; font-size: 14px; }
                .content { padding: 30px; }
                .meta-card { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #1e3c72; }
                .meta-item { flex: 1; min-width: 200px; }
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
                .footer { padding: 20px 30px; background: #f8f9fa; border-top: 1px solid #e9ecef; text-align: right; }
                .btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; }
                .btn:hover { background: #0b5ed7; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Auto-Migration Database: Face Recognition</h1>
                    <p>Sistem Presensi Biometrik &mdash; SD Mentari Islamic School</p>
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
                            <strong>Status Migrasi: BERHASIL SEMPURNA!</strong> &nbsp; Seluruh tabel, kolom biometrik, dan folder penyimpanan siap digunakan untuk Tahap 2.
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
                    <a href="<?= base_url('dashboard'); ?>" class="btn">Kembali ke Dashboard Admin</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
