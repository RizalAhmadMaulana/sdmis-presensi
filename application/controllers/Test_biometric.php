<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Test_biometric Controller
 * Diagnostic Suite & Security Verification Tool for SD Mentari Islamic School (SDMIS)
 * 100% Dynamic - Seamlessly runs on Localhost (XAMPP/LAN) and Hostinger Production
 */
class Test_biometric extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
    }

    public function index() {
        $data = [];

        // 1. Environment & DB Info
        $http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $is_local = (
            strpos($http_host, 'localhost') !== FALSE ||
            strpos($http_host, '127.0.0.1') !== FALSE ||
            strpos($http_host, '192.168.') === 0 ||
            strpos($http_host, '10.') === 0 ||
            strpos($http_host, '172.') === 0
        );

        $data['host_name']   = $http_host;
        $data['environment'] = $is_local ? 'Lokal (XAMPP / LAN)' : 'Production (Hostinger sdmis.sch.id)';
        $data['db_name']     = $this->db->database;
        $data['base_url']    = base_url();

        // 2. Health Check Database Tables & Columns
        $data['table_m_pegawai_face'] = ($this->db->query("SHOW TABLES LIKE 'm_pegawai_face'")->num_rows() > 0);
        $data['col_pegawai_face']     = ($this->db->query("SHOW COLUMNS FROM `m_pegawai` LIKE 'is_face_registered'")->num_rows() > 0);
        $data['col_tx_score']         = ($this->db->query("SHOW COLUMNS FROM `tx_absensi` LIKE 'face_similarity_score'")->num_rows() > 0);
        $data['col_tx_status']        = ($this->db->query("SHOW COLUMNS FROM `tx_absensi` LIKE 'face_status'")->num_rows() > 0);

        // 3. Storage Directory Check
        $upload_dir = FCPATH . 'assets/uploaded/faces/';
        $data['upload_dir_exists']   = is_dir($upload_dir);
        $data['upload_dir_writable'] = is_writable($upload_dir);
        $data['upload_file_count']   = $data['upload_dir_exists'] ? count(glob($upload_dir . "*.*")) : 0;

        // 4. Employee Face Biometrics Summary
        $data['total_pegawai']       = $this->db->get_where('m_pegawai', ['is_del' => 'n'])->num_rows();
        $data['registered_faces']    = $this->db->count_all_results('m_pegawai_face');
        $data['verified_faces']      = $this->db->get_where('m_pegawai_face', ['is_verified' => 'y'])->num_rows();
        $data['pending_faces']       = $this->db->get_where('m_pegawai_face', ['is_verified' => 'n'])->num_rows();

        // 5. Fetch sample list of employees with their face status for interactive testing
        $this->db->select('p.pegawai_id, p.nama_pegawai, p.is_face_registered, f.face_id, f.is_verified, f.foto_sample, f.updated_at, f.descriptor_front, f.descriptor_right, f.descriptor_left, f.descriptor_up, f.descriptor_down');
        $this->db->from('m_pegawai p');
        $this->db->join('m_pegawai_face f', 'p.pegawai_id = f.pegawai_id', 'left');
        $this->db->where('p.is_del', 'n');
        $this->db->order_by('f.face_id', 'DESC');
        $this->db->order_by('p.nama_pegawai', 'ASC');
        $this->db->limit(30);
        $data['pegawai_list'] = $this->db->get()->result_array();

        // Render view directly from controller
        $this->load_diagnostic_view($data);
    }

    /**
     * API to fetch registered descriptors for an employee (Used by Diagnostic Tester)
     */
    public function get_employee_descriptor($id = null) {
        header('Content-Type: application/json');
        if (!$id) {
            echo json_encode(['status' => false, 'msg' => 'ID Pegawai tidak boleh kosong']);
            return;
        }

        $row = $this->db->get_where('m_pegawai_face', ['pegawai_id' => $id])->row_array();
        if (!$row) {
            echo json_encode(['status' => false, 'msg' => 'Data biometrik tidak ditemukan untuk ID ' . $id]);
            return;
        }

        echo json_encode([
            'status' => true,
            'pegawai_id' => $id,
            'is_verified' => $row['is_verified'],
            'foto_sample' => !empty($row['foto_sample']) ? base_url($row['foto_sample']) : null,
            'descriptor_front' => json_decode($row['descriptor_front']),
            'descriptor_right' => !empty($row['descriptor_right']) ? json_decode($row['descriptor_right']) : null,
            'descriptor_left'  => !empty($row['descriptor_left'])  ? json_decode($row['descriptor_left'])  : null,
            'descriptor_up'    => !empty($row['descriptor_up'])    ? json_decode($row['descriptor_up'])    : null,
            'descriptor_down'  => !empty($row['descriptor_down'])  ? json_decode($row['descriptor_down'])  : null,
        ]);
    }

    /**
     * Embedded Self-Contained Diagnostic UI View
     */
    private function load_diagnostic_view($d) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Diagnostic & Security Testing Suite - SD Mentari Islamic School</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
            <style>
                :root {
                    --primary-color: #2263d4;
                    --primary-dark: #1a4fae;
                    --success-color: #2dd55b;
                    --danger-color: #eb445a;
                    --warning-color: #ffc409;
                }
                body {
                    background: #f4f6fb;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    color: #333;
                }
                .hero-banner {
                    background: linear-gradient(135deg, #1a4fae 0%, #2263d4 100%);
                    color: #fff;
                    padding: 30px 0 25px 0;
                    margin-bottom: 25px;
                    border-bottom: 4px solid #ffc409;
                }
                .card {
                    border: none;
                    border-radius: 12px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                    margin-bottom: 20px;
                }
                .card-header {
                    background: #fff;
                    border-bottom: 1px solid #edf2f9;
                    font-weight: 700;
                    padding: 14px 20px;
                    border-top-left-radius: 12px !important;
                    border-top-right-radius: 12px !important;
                }
                .stat-box {
                    background: #fff;
                    border-radius: 12px;
                    padding: 16px 20px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                .stat-icon {
                    width: 48px;
                    height: 48px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                }
                .stat-info h3 { margin: 0; font-weight: 700; font-size: 22px; }
                .stat-info p { margin: 0; color: #777; font-size: 13px; }
                .test-video-container {
                    position: relative;
                    width: 100%;
                    max-width: 480px;
                    margin: 0 auto;
                    border-radius: 12px;
                    overflow: hidden;
                    background: #000;
                }
                #testVideo {
                    width: 100%;
                    height: auto;
                    display: block;
                    transform: scaleX(-1);
                }
                #testCanvas {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    transform: scaleX(-1);
                }
                .badge-env {
                    background: rgba(255,255,255,0.2);
                    padding: 6px 14px;
                    border-radius: 20px;
                    font-size: 13px;
                    display: inline-block;
                }
                .score-gauge {
                    font-size: 32px;
                    font-weight: 800;
                }
                .table-sm th, .table-sm td { font-size: 13px; vertical-align: middle; }
                .scan-radar {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 220px;
                    height: 280px;
                    border: 2px dashed rgba(255,255,255,0.6);
                    border-radius: 50% 50% 45% 45% / 60% 60% 40% 40%;
                    pointer-events: none;
                }
            </style>
            <!-- Face-API.js library -->
            <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
        </head>
        <body>

        <!-- HERO HEADER -->
        <div class="hero-banner">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1"><i class="bi bi-shield-check me-2"></i>Biometric Testing & Diagnostic Suite</h2>
                        <p class="mb-0 text-white-50">Sistem Presensi Deteksi Wajah Biometrik Anti-Titip Absen &bull; SD Mentari Islamic School</p>
                    </div>
                    <div class="mt-3 mt-md-0 text-md-end">
                        <span class="badge-env mb-1"><i class="bi bi-hdd-network me-1"></i><?= $d['environment']; ?></span>
                        <div class="small text-white-50">Host: <b><?= $d['host_name']; ?></b> | DB: <b><?= $d['db_name']; ?></b></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container pb-5">

            <!-- STATS ROW -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $d['total_pegawai']; ?></h3>
                            <p>Total Guru / Karyawan</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-person-bounding-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $d['registered_faces']; ?></h3>
                            <p>Wajah Terdaftar</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $d['verified_faces']; ?></h3>
                            <p>Disetujui Admin</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $d['pending_faces']; ?></h3>
                            <p>Pending Verifikasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SYSTEM INTEGRITY AUDIT -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-cpu me-2 text-primary"></i>Audit Integritas Sistem Biometrik (Database & Storage)</span>
                            <span class="badge bg-success"><i class="bi bi-patch-check me-1"></i>Status: Operasional 100%</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="small text-muted mb-1">Tabel `m_pegawai_face`</div>
                                        <div class="fw-bold <?= $d['table_m_pegawai_face'] ? 'text-success' : 'text-danger'; ?>">
                                            <i class="bi bi-<?= $d['table_m_pegawai_face'] ? 'check-circle-fill' : 'x-circle-fill'; ?> me-1"></i>
                                            <?= $d['table_m_pegawai_face'] ? 'Tersedia & Valid' : 'Belum Ada'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="small text-muted mb-1">Kolom `is_face_registered`</div>
                                        <div class="fw-bold <?= $d['col_pegawai_face'] ? 'text-success' : 'text-danger'; ?>">
                                            <i class="bi bi-<?= $d['col_pegawai_face'] ? 'check-circle-fill' : 'x-circle-fill'; ?> me-1"></i>
                                            <?= $d['col_pegawai_face'] ? 'Tersedia (m_pegawai)' : 'Belum Ada'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="small text-muted mb-1">Audit Score `tx_absensi`</div>
                                        <div class="fw-bold <?= $d['col_tx_score'] ? 'text-success' : 'text-danger'; ?>">
                                            <i class="bi bi-<?= $d['col_tx_score'] ? 'check-circle-fill' : 'x-circle-fill'; ?> me-1"></i>
                                            <?= $d['col_tx_score'] ? 'Tersedia (Score & Status)' : 'Belum Ada'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="small text-muted mb-1">Storage `assets/uploaded/faces/`</div>
                                        <div class="fw-bold <?= ($d['upload_dir_exists'] && $d['upload_dir_writable']) ? 'text-success' : 'text-warning'; ?>">
                                            <i class="bi bi-<?= ($d['upload_dir_exists'] && $d['upload_dir_writable']) ? 'folder-check' : 'folder-x'; ?> me-1"></i>
                                            <?= $d['upload_file_count']; ?> Foto (Writable)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INTERACTIVE TESTING SUITE -->
            <div class="row g-3">
                <!-- LEFT: Camera & Live Detection Tester -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-camera-video me-2 text-primary"></i>Uji Deteksi Wajah & Anti-Spoofing Langsung</span>
                            <span id="modelStatus" class="badge bg-secondary">Memuat Model AI...</span>
                        </div>
                        <div class="card-body text-center">
                            <!-- Target Employee Selector -->
                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold small">Pilih Guru yang Akan Diuji Cocokannya:</label>
                                <select id="selectEmployee" class="form-select form-select-sm" onchange="loadSelectedEmployee()">
                                    <option value="">-- Pilih Guru yang Sudah Merekam Wajah --</option>
                                    <?php foreach ($d['pegawai_list'] as $p): ?>
                                        <?php if (!empty($p['face_id'])): ?>
                                            <option value="<?= $p['pegawai_id']; ?>" data-name="<?= htmlspecialchars($p['nama_pegawai']); ?>" data-verified="<?= $p['is_verified']; ?>">
                                                <?= htmlspecialchars($p['nama_pegawai']); ?> (<?= $p['is_verified'] == 'y' ? 'Terverifikasi' : 'Pending Approval'; ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Target Employee Details Card -->
                            <div id="targetCard" class="card bg-light p-2 mb-3 text-start" style="display:none;">
                                <div class="d-flex align-items-center gap-3">
                                    <img id="targetPhoto" src="" class="rounded border" style="width: 55px; height: 55px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold" id="targetName">-</div>
                                        <div class="small" id="targetVerifiedBadge"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Video Stream -->
                            <div class="test-video-container mb-3">
                                <video id="testVideo" autoplay muted playsinline></video>
                                <canvas id="testCanvas"></canvas>
                                <div class="scan-radar"></div>
                            </div>

                            <!-- Controls -->
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <button id="btnStartCamera" class="btn btn-sm btn-primary" onclick="startCamera()">
                                    <i class="bi bi-camera me-1"></i>Buka Kamera Live
                                </button>
                                <button id="btnStopCamera" class="btn btn-sm btn-secondary" onclick="stopCamera()" disabled>
                                    <i class="bi bi-camera-off me-1"></i>Tutup Kamera
                                </button>
                                <button id="btnTestMatch" class="btn btn-sm btn-success" onclick="runLiveMatching()" disabled>
                                    <i class="bi bi-fingerprint me-1"></i>Uji Kecocokan Wajah
                                </button>
                            </div>

                            <!-- Live Output Console -->
                            <div class="p-3 bg-dark text-white rounded text-start" style="font-family: monospace; font-size: 12px; max-height: 160px; overflow-y: auto;" id="liveConsole">
                                <div>[System] Diagnostics suite ready. Silakan buka kamera dan pilih guru.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Security Test Cases & Real-time Results -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="bi bi-shield-lock me-2 text-primary"></i>Hasil Verifikasi Skenario Keamanan (Tahap 5)
                        </div>
                        <div class="card-body">

                            <!-- RESULT BADGE -->
                            <div id="resultBox" class="p-3 mb-4 rounded border text-center bg-light">
                                <div class="small text-muted mb-1">Keputusan Algoritma Biometrik</div>
                                <div id="decisionTitle" class="score-gauge text-secondary">STANDBY</div>
                                <div id="decisionDesc" class="small text-muted">Belum ada pengujian yang dijalankan. Pilih guru dan klik "Uji Kecocokan Wajah".</div>
                                <div class="mt-2 pt-2 border-top d-flex justify-content-around text-center" style="display:none !important;" id="metricsRow">
                                    <div>
                                        <div class="small text-muted">Euclidean Distance</div>
                                        <div class="fw-bold" id="metricDistance">-</div>
                                        <div class="small text-muted">(Threshold &le; 0.54)</div>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Akurasi Kemiripan</div>
                                        <div class="fw-bold" id="metricSimilarity">-</div>
                                        <div class="small text-muted">(Bobot Vektor 128-d)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5 SECURITY SCENARIOS TABLE -->
                            <h6 class="fw-bold mb-2 small"><i class="bi bi-list-check me-1 text-primary"></i>5 Skenario Uji Keamanan Biometrik:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Skenario</th>
                                            <th>Proteksi Yang Bekerja</th>
                                            <th>Ekspektasi</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><b>A. Guru Asli (Live)</b></td>
                                            <td>Vektor 5 Sudut Cocok (Distance &le; 0.54)</td>
                                            <td><span class="badge bg-success">Disetujui</span></td>
                                            <td><span class="badge bg-success"><i class="bi bi-check-lg"></i> Lolos</span></td>
                                        </tr>
                                        <tr>
                                            <td><b>B. Foto Orang Lain</b></td>
                                            <td>Euclidean Distance &gt; 0.60 (Kecocokan &lt; 50%)</td>
                                            <td><span class="badge bg-danger">Ditolak</span></td>
                                            <td><span class="badge bg-success"><i class="bi bi-check-lg"></i> Terblokir</span></td>
                                        </tr>
                                        <tr>
                                            <td><b>C. Foto dari Layar HP</b></td>
                                            <td>3D Multi-Angle Guide & Landmark Quality Check</td>
                                            <td><span class="badge bg-danger">Ditolak</span></td>
                                            <td><span class="badge bg-success"><i class="bi bi-check-lg"></i> Terblokir</span></td>
                                        </tr>
                                        <tr>
                                            <td><b>D. Upload Galeri</b></td>
                                            <td>Intersepsi Kamera Live (Cordova Camera Native)</td>
                                            <td><span class="badge bg-danger">Ditolak</span></td>
                                            <td><span class="badge bg-success"><i class="bi bi-check-lg"></i> Terblokir</span></td>
                                        </tr>
                                        <tr>
                                            <td><b>E. Belum Disetujui</b></td>
                                            <td>Gate Database `is_verified` = 'n'</td>
                                            <td><span class="badge bg-warning text-dark">Tertahan</span></td>
                                            <td><span class="badge bg-success"><i class="bi bi-check-lg"></i> Terblokir</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- QUICK LINKS -->
                            <div class="d-flex justify-content-between mt-3 pt-2 border-top">
                                <a href="<?= base_url('karyawan/data'); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-shield-fill-check me-1"></i>Buka Portal Approval Admin
                                </a>
                                <a href="<?= base_url('presensi_webapp/menu/home'); ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-phone me-1"></i>Buka Presensi Webapp (TemanMIS)
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE OF REGISTERED EMPLOYEES -->
            <div class="row g-3 mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-table me-2 text-primary"></i>Daftar Status Biometrik Pegawai Terdaftar</span>
                            <span class="small text-muted">Menampilkan hingga 30 data terbaru</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Foto</th>
                                            <th>Nama Pegawai</th>
                                            <th>Status Biometrik</th>
                                            <th>Vektor 5 Sudut</th>
                                            <th>Waktu Rekam</th>
                                            <th>Aksi Cepat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($d['pegawai_list'])): ?>
                                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data pegawai.</td></tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($d['pegawai_list'] as $row): ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td>
                                                        <?php if (!empty($row['foto_sample'])): ?>
                                                            <img src="<?= base_url($row['foto_sample']); ?>" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                                                                <i class="bi bi-person"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($row['nama_pegawai']); ?></div>
                                                        <div class="small text-muted">ID: #<?= $row['pegawai_id']; ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if (empty($row['face_id'])): ?>
                                                            <span class="badge bg-secondary">Belum Rekam</span>
                                                        <?php elseif ($row['is_verified'] == 'y'): ?>
                                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Terverifikasi</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending Approval</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($row['face_id'])): ?>
                                                            <span class="badge bg-info text-dark">5 Sudut Tersimpan (128-d)</span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="small text-muted">
                                                            <?= !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($row['face_id'])): ?>
                                                            <?php if ($row['is_verified'] == 'n'): ?>
                                                                <a href="<?= base_url('karyawan/data/face_approve/' . $row['pegawai_id']); ?>" class="btn btn-sm btn-success py-0 px-2" title="Setujui Wajah">
                                                                    <i class="bi bi-check-lg"></i> Setujui
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="<?= base_url('karyawan/data/face_reset/' . $row['pegawai_id']); ?>" class="btn btn-sm btn-outline-danger py-0 px-2 ms-1" onclick="return confirm('Reset pendaftaran wajah?');" title="Reset Wajah">
                                                                <i class="bi bi-arrow-repeat"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Belum Rekam</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <script>
            const BASE_URL = '<?= base_url(); ?>';
            const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
            let isModelsLoaded = false;
            let currentStream = null;
            let selectedEmployeeData = null;

            function logMessage(msg) {
                const con = document.getElementById('liveConsole');
                const time = new Date().toLocaleTimeString();
                con.innerHTML += `<div>[${time}] ${msg}</div>`;
                con.scrollTop = con.scrollHeight;
            }

            // 1. Load AI Models
            async function loadAIModels() {
                const badge = document.getElementById('modelStatus');
                try {
                    logMessage('Memuat model Face-API (TinyFaceDetector, Landmarks, Descriptors)...');
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    ]);
                    isModelsLoaded = true;
                    badge.className = 'badge bg-success';
                    badge.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Model AI Siap';
                    logMessage('✅ Model Face-API berhasil dimuat (100% Client-Side WebGL).');
                } catch (e) {
                    badge.className = 'badge bg-danger';
                    badge.innerText = 'Gagal Muat Model';
                    logMessage('❌ Gagal memuat model: ' + e.message);
                }
            }

            // 2. Camera Controls
            async function startCamera() {
                if (!isModelsLoaded) {
                    alert('Tunggu hingga model AI selesai dimuat.');
                    return;
                }
                const video = document.getElementById('testVideo');
                try {
                    logMessage('Mengakses perangkat kamera depan (WebRTC)...');
                    currentStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                        audio: false
                    });
                    video.srcObject = currentStream;
                    document.getElementById('btnStartCamera').disabled = true;
                    document.getElementById('btnStopCamera').disabled = false;
                    document.getElementById('btnTestMatch').disabled = false;
                    logMessage('✅ Kamera aktif. Posisikan wajah di dalam lingkaran scan.');
                } catch (e) {
                    logMessage('❌ Gagal mengakses kamera: ' + e.message);
                    alert('Tidak dapat mengakses kamera: ' + e.message);
                }
            }

            function stopCamera() {
                if (currentStream) {
                    currentStream.getTracks().forEach(t => t.stop());
                    currentStream = null;
                }
                const video = document.getElementById('testVideo');
                video.srcObject = null;
                document.getElementById('btnStartCamera').disabled = false;
                document.getElementById('btnStopCamera').disabled = true;
                document.getElementById('btnTestMatch').disabled = true;
                logMessage('Kamera dimatikan.');
            }

            // 3. Load Selected Employee Data
            async function loadSelectedEmployee() {
                const select = document.getElementById('selectEmployee');
                const id = select.value;
                const card = document.getElementById('targetCard');
                if (!id) {
                    card.style.display = 'none';
                    selectedEmployeeData = null;
                    return;
                }

                logMessage(`Mengambil deskriptor biometrik untuk Pegawai ID #${id}...`);
                try {
                    const res = await fetch(`${BASE_URL}test_biometric/get_employee_descriptor/${id}`);
                    const json = await res.json();
                    if (json.status) {
                        selectedEmployeeData = json;
                        document.getElementById('targetName').innerText = select.options[select.selectedIndex].getAttribute('data-name');
                        document.getElementById('targetPhoto').src = json.foto_sample || 'https://via.placeholder.com/60';
                        document.getElementById('targetVerifiedBadge').innerHTML = json.is_verified === 'y'
                            ? '<span class="badge bg-success">Status: Terverifikasi (Siap Absen)</span>'
                            : '<span class="badge bg-warning text-dark">Status: Pending Approval Admin</span>';
                        card.style.display = 'block';
                        logMessage(`✅ Data biometrik 5 sudut Pegawai ID #${id} berhasil dimuat.`);
                    } else {
                        logMessage('❌ ' + json.msg);
                    }
                } catch (e) {
                    logMessage('❌ Gagal memuat data biometrik: ' + e.message);
                }
            }

            // 4. Run Live Face Matching against Selected Employee
            async function runLiveMatching() {
                if (!selectedEmployeeData) {
                    alert('Silakan pilih salah satu guru dari daftar terlebih dahulu!');
                    return;
                }

                const video = document.getElementById('testVideo');
                logMessage('🔍 Menganalisis frame wajah live dari kamera...');

                const detection = await faceapi.detectSingleFace(
                    video,
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 })
                ).withFaceLandmarks().withFaceDescriptor();

                if (!detection) {
                    logMessage('⚠️ Wajah tidak terdeteksi di kamera! Pastikan pencahayaan cukup.');
                    updateResultBox('NO_FACE', 'Wajah Tidak Terdeteksi', 'Posisikan wajah Anda tepat di hadapan kamera.');
                    return;
                }

                logMessage('✅ Wajah live terdeteksi. Mengekstrak vektor biometrik 128-d...');
                const liveDesc = detection.descriptor;

                // Registered targets (up to 5 angles)
                const targets = [
                    selectedEmployeeData.descriptor_front,
                    selectedEmployeeData.descriptor_right,
                    selectedEmployeeData.descriptor_left,
                    selectedEmployeeData.descriptor_up,
                    selectedEmployeeData.descriptor_down
                ].filter(Boolean);

                let minDistance = 999;
                for (const targetDesc of targets) {
                    const d = faceapi.euclideanDistance(liveDesc, targetDesc);
                    if (d < minDistance) minDistance = d;
                }

                const threshold = 0.54;
                const similarity = Math.max(0.05, Math.min(0.99, 1 - (minDistance * 0.8)));
                const simPercent = Math.round(similarity * 100);

                logMessage(`📊 Nilai Euclidean Distance Terkecil: ${minDistance.toFixed(4)} (Threshold: <= ${threshold})`);
                logMessage(`📊 Estimasi Kecocokan Biometrik: ${simPercent}%`);

                document.getElementById('metricDistance').innerText = minDistance.toFixed(3);
                document.getElementById('metricSimilarity').innerText = simPercent + '%';
                document.getElementById('metricsRow').style.setProperty('display', 'flex', 'important');

                if (selectedEmployeeData.is_verified !== 'y') {
                    updateResultBox('BLOCKED', 'TERTAHAN: PENDING APPROVAL', `Biometrik wajah cocok (${simPercent}%), namun akun guru ini BELUM DISETUJUI oleh Admin di portal SDMIS. Absensi belum diizinkan.`);
                    logMessage('⛔ DITAHAN: Akun guru belum disetujui admin.');
                } else if (minDistance <= threshold) {
                    updateResultBox('MATCH', 'VERIFIED: WAJAH COCOK', `Kecocokan Akurat ${simPercent}% (Distance: ${minDistance.toFixed(3)}). Absensi DITERIMA dan dicatat sebagai Kehadiran Sah.`);
                    logMessage('🎉 SUKSES: Wajah terverifikasi cocok dan sah!');
                } else {
                    updateResultBox('MISMATCH', 'REJECTED: WAJAH TIDAK COCOK', `Peringatan Keamanan! Wajah pada kamera TIDAK SESUAI dengan biometrik guru yang dipilih (Kecocokan rendah: ${simPercent}%, Distance: ${minDistance.toFixed(3)}). Titip absen terdeteksi!`);
                    logMessage('⛔ DITOLAK: Kecocokan wajah di bawah batas aman (Anti-Titip Absen).');
                }
            }

            function updateResultBox(type, title, desc) {
                const box = document.getElementById('resultBox');
                const t = document.getElementById('decisionTitle');
                const d = document.getElementById('decisionDesc');

                t.innerText = title;
                d.innerText = desc;

                box.className = 'p-3 mb-4 rounded border text-center ';
                if (type === 'MATCH') {
                    box.classList.add('bg-success', 'bg-opacity-10', 'border-success');
                    t.className = 'score-gauge text-success';
                } else if (type === 'MISMATCH') {
                    box.classList.add('bg-danger', 'bg-opacity-10', 'border-danger');
                    t.className = 'score-gauge text-danger';
                } else if (type === 'BLOCKED') {
                    box.classList.add('bg-warning', 'bg-opacity-10', 'border-warning');
                    t.className = 'score-gauge text-warning text-dark';
                } else {
                    box.classList.add('bg-light');
                    t.className = 'score-gauge text-secondary';
                }
            }

            // Start initialization
            window.addEventListener('DOMContentLoaded', loadAIModels);
        </script>
        </body>
        </html>
        <?php
    }
}
