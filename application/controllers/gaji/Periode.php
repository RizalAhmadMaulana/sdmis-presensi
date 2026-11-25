<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Periode
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Mengelola Siklus Periode Penggajian Bulanan, Tombol Sakti Hitung Otomatis Presensi, dan Modal Penyesuaian Manual
 */
class Periode extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->library('form_validation');
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
    }

    /**
     * Halaman Daftar Periode Penggajian Bulanan
     */
    public function index() {
        cek_menu_access();
        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Periode & Hitung Gaji';
        $data['title']      = 'Periode & Hitung Gaji';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        $data['periodes'] = $this->payroll->get_all_periode();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/periode/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * Buat Periode Baru
     */
    public function create() {
        cek_menu_access();

        $bulan         = (int)$this->input->post('bulan', true);
        $tahun         = (int)$this->input->post('tahun', true);
        $nama_periode  = trim($this->input->post('nama_periode', true));
        $tanggal_awal  = $this->input->post('tanggal_awal', true);
        $tanggal_akhir = $this->input->post('tanggal_akhir', true);
        $hari_kerja    = (int)$this->input->post('total_hari_kerja_aktif', true) ?: 20;

        // Auto name jika kosong
        if (empty($nama_periode)) {
            $nama_bulan = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
            $nama_periode = 'Mukafaah ' . $nama_bulan[$bulan] . ' ' . $tahun;
        }

        // Cek duplikasi periode
        $cek = $this->db->get_where('tx_gaji_periode', ['bulan' => $bulan, 'tahun' => $tahun])->row_array();
        if ($cek) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Periode bulan ' . $bulan . ' tahun ' . $tahun . ' sudah ada! Anda langsung dialihkan ke detail periode.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/periode/detail/' . $cek['periode_id']);
            return;
        }

        $data_save = [
            'nama_periode'           => $nama_periode,
            'bulan'                  => $bulan,
            'tahun'                  => $tahun,
            'tanggal_awal'           => $tanggal_awal,
            'tanggal_akhir'          => $tanggal_akhir,
            'total_hari_kerja_aktif' => $hari_kerja,
            'total_pengeluaran'      => 0.00,
            'status_periode'         => 'draft',
            'created_by'             => (int)$this->session->userdata('u_id') ?: 1
        ];

        $periode_id = $this->payroll->save_periode($data_save);

        if ($periode_id) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Periode <b>' . htmlspecialchars($nama_periode) . '</b> berhasil dibuat. Silakan klik tombol <b>⚡ Hitung Otomatis dari Presensi</b>.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/periode/detail/' . $periode_id);
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal membuat periode.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/periode');
        }
    }

    /**
     * Halaman Detail Rincian Gaji 26 Guru & Tombol Sakti Hitung Presensi
     */
    public function detail($periode_id = null) {
        cek_menu_access();

        if (!$periode_id) {
            redirect('gaji/periode');
            return;
        }

        $periode = $this->payroll->get_periode_by_id((int)$periode_id);
        if (!$periode) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Periode tidak ditemukan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/periode');
            return;
        }

        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Periode & Hitung Gaji';
        $data['title']      = 'Detail Penggajian - ' . $periode['nama_periode'];
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        $data['periode'] = $periode;
        $data['rincian'] = $this->payroll->get_rincian_gaji_periode((int)$periode_id);
        $data['setting_global'] = $this->payroll->get_setting_global();

        // Hitung statistik periode
        $total_guru = count($data['rincian']);
        $total_bonus_tier1 = 0.00;
        $total_bonus_tier2 = 0.00;
        $total_makan       = 0.00;
        $total_ekstra      = 0.00;
        $total_talangan    = 0.00;
        $total_kasbon      = 0.00;
        $total_thp         = 0.00;

        foreach ($data['rincian'] as $r) {
            $total_bonus_tier1 += (float)$r['bonus_presensi_tier1'];
            $total_bonus_tier2 += (float)$r['bonus_presensi_tier2'];
            $total_makan       += (float)$r['tunj_makan'];
            $total_ekstra      += (float)$r['honor_ekstra_lunas'];
            $total_talangan    += (float)$r['talangan_operasional'];
            $total_kasbon      += (float)$r['potongan_kasbon'];
            $total_thp         += (float)$r['take_home_pay'];
        }

        $data['stats'] = [
            'total_guru'        => $total_guru,
            'total_bonus_tier1' => $total_bonus_tier1,
            'total_bonus_tier2' => $total_bonus_tier2,
            'total_makan'       => $total_makan,
            'total_ekstra'      => $total_ekstra,
            'total_talangan'    => $total_talangan,
            'total_kasbon'      => $total_kasbon,
            'total_thp'         => $total_thp
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/periode/detail', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * TOMBOL SAKTI: 1-Klik Hitung Otomatis dari Presensi Biometrik
     */
    public function hitung_otomatis($periode_id) {
        cek_menu_access();

        $user_id = (int)$this->session->userdata('u_id') ?: 1;
        $res = $this->payroll->batch_generate_payroll((int)$periode_id, $user_id);

        if ($res['status']) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-bolt me-2"></i> <strong>Kalkulasi Otomatis Berhasil!</strong> Seluruh data presensi 2-tier (06.48 & 06.30), tunjangan masa kerja, uang makan, dan honor ekstra untuk <b>' . $res['processed_count'] . ' pegawai</b> telah berhasil dihitung. Total pengeluaran: <b>Rp ' . number_format($res['total_pengeluaran'], 0, ',', '.') . '</b><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Terjadi kesalahan saat memproses kalkulasi presensi.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/periode/detail/' . $periode_id);
    }

    /**
     * Tambah Penyesuaian Manual (Talangan, Kasbon, Transport, dll)
     */
    public function add_penyesuaian() {
        cek_menu_access();

        $gaji_id    = (int)$this->input->post('gaji_id', true);
        $periode_id = (int)$this->input->post('periode_id', true);
        $jenis      = $this->input->post('jenis', true) === 'potongan' ? 'potongan' : 'penambahan';
        $kategori   = trim($this->input->post('kategori', true));
        $keterangan = trim($this->input->post('keterangan', true));
        $nominal    = (float)$this->input->post('nominal', true);

        if (empty($keterangan) || $nominal <= 0 || !$gaji_id) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Keterangan dan nominal penyesuaian wajib diisi valid!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/periode/detail/' . $periode_id);
            return;
        }

        $data_save = [
            'gaji_id'    => $gaji_id,
            'jenis'      => $jenis,
            'kategori'   => $kategori,
            'keterangan' => $keterangan,
            'nominal'    => $nominal
        ];

        $res = $this->payroll->add_penyesuaian_manual($data_save);

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Penyesuaian manual (<b>' . htmlspecialchars($keterangan) . '</b>) sebesar Rp ' . number_format($nominal, 0, ',', '.') . ' berhasil ditambahkan dan THP otomatis disinkronkan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menambahkan penyesuaian.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/periode/detail/' . $periode_id);
    }

    /**
     * Hapus Penyesuaian Manual
     */
    public function delete_penyesuaian($adj_id = null, $periode_id = null) {
        cek_menu_access();

        if ($adj_id) {
            $this->payroll->delete_penyesuaian_manual((int)$adj_id);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Penyesuaian manual berhasil dihapus dan THP otomatis diperbarui.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/periode/detail/' . $periode_id);
    }

    /**
     * Kunci Periode Penggajian (Status Final)
     */
    public function kunci_periode($periode_id = null) {
        cek_menu_access();

        if ($periode_id) {
            $this->db->where('periode_id', (int)$periode_id)->update('tx_gaji_periode', ['status_periode' => 'final']);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-lock me-2"></i> Periode penggajian berhasil <b>DIKUNCI (FINAL)</b> untuk proses audit dan pencairan bank.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/periode/detail/' . $periode_id);
    }
}
