<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Ekstra
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Mengelola Manajemen Ekstrakurikuler, Tracking Pelunasan Siswa, dan Sistem Penangguhan Honor (Carry-Over)
 * Berdasarkan SOP Poin 6, 7, dan 8
 */
class Ekstra extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->library('form_validation');
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
    }

    /**
     * Halaman Utama Tracking Ekstrakurikuler & Pelunasan Siswa
     */
    public function index() {
        cek_menu_access();
        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Manajemen Ekstra & Siswa';
        $data['title']      = 'Manajemen Ekstra & Siswa';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        // Filter Cabang, Bulan, dan Tahun
        $bulan = (int)$this->input->get('bulan');
        if ($bulan < 1 || $bulan > 12) $bulan = (int)date('n'); // Default: bulan berjalan

        $tahun = (int)$this->input->get('tahun');
        if ($tahun < 2020) $tahun = (int)date('Y'); // Default: tahun berjalan

        $ekstra_id = $this->input->get('ekstra_id');
        $ekstra_id = !empty($ekstra_id) ? (int)$ekstra_id : null;

        $data['bulan'] = $bulan;
        $data['tahun'] = $tahun;
        $data['selected_ekstra_id'] = $ekstra_id;

        // Ambil daftar cabang ekstra
        $data['ekstras'] = $this->payroll->get_all_ekstra();
        
        // Ambil daftar guru aktif untuk pengampu ekstra
        $data['guru_list'] = $this->db->query("SELECT pegawai_id, nama_pegawai FROM m_pegawai WHERE is_del = 'n' AND is_status = 'y' ORDER BY nama_pegawai ASC")->result_array();

        // Ambil daftar peserta ekstra
        $data['siswas'] = $this->payroll->get_siswa_ekstra($ekstra_id, $bulan, $tahun);

        // Hitung statistik ringkas
        $total_siswa = count($data['siswas']);
        $total_lunas = 0;
        $total_pending = 0;
        $nominal_cair = 0.00;
        $nominal_pending = 0.00;

        foreach ($data['siswas'] as $s) {
            $tarif = (float)$s['nominal_tagihan'] > 0 ? (float)$s['nominal_tagihan'] : (float)$s['tarif_standar'];
            if ($s['status_bayar'] === 'lunas') {
                $total_lunas++;
                $nominal_cair += $tarif;
            } else {
                $total_pending++;
                $nominal_pending += $tarif;
            }
        }

        $data['stats'] = [
            'total_siswa'     => $total_siswa,
            'total_lunas'     => $total_lunas,
            'total_pending'   => $total_pending,
            'nominal_cair'    => $nominal_cair,
            'nominal_pending' => $nominal_pending
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/ekstra/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * 1-Click Toggle Status Pelunasan Siswa (Lunas <-> Pending)
     */
    public function toggle_status($id = null) {
        cek_menu_access();

        if (!$id) {
            redirect('gaji/ekstra');
            return;
        }

        $res = $this->payroll->toggle_status_bayar_siswa((int)$id);

        if ($this->input->is_ajax_request()) {
            echo json_encode($res);
            return;
        }

        if ($res['status']) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> ' . $res['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> ' . $res['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        $bulan = (int)$this->input->get('bulan') ?: date('n');
        $tahun = (int)$this->input->get('tahun') ?: date('Y');
        $ekstra_id = (int)$this->input->get('ekstra_id');
        $query_str = '?bulan=' . $bulan . '&tahun=' . $tahun . ($ekstra_id ? '&ekstra_id=' . $ekstra_id : '');
        redirect('gaji/ekstra' . $query_str);
    }

    /**
     * Tambah Data Siswa Peserta Ekstra
     */
    public function add_siswa() {
        cek_menu_access();

        $ekstra_id       = (int)$this->input->post('ekstra_id', true);
        $nama_siswa      = trim($this->input->post('nama_siswa', true));
        $kelas           = trim($this->input->post('kelas', true));
        $bulan           = (int)$this->input->post('bulan', true);
        $tahun           = (int)$this->input->post('tahun', true);
        $nominal_tagihan = (float)$this->input->post('nominal_tagihan', true);
        $status_bayar    = $this->input->post('status_bayar', true) === 'lunas' ? 'lunas' : 'pending';
        $tanggal_bayar   = $status_bayar === 'lunas' ? ($this->input->post('tanggal_bayar', true) ?: date('Y-m-d')) : null;
        $keterangan      = trim($this->input->post('keterangan', true));

        if (empty($nama_siswa) || $ekstra_id <= 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Nama siswa dan cabang ekstra wajib dipilih!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra');
            return;
        }

        // Jika nominal tagihan kosong / 0, ambil default dari tarif cabang ekstra
        if ($nominal_tagihan <= 0) {
            $ek = $this->db->get_where('m_ekstra', ['ekstra_id' => $ekstra_id])->row_array();
            if ($ek) $nominal_tagihan = (float)$ek['tarif_standar'];
        }

        $data_save = [
            'ekstra_id'       => $ekstra_id,
            'nama_siswa'      => $nama_siswa,
            'kelas'           => $kelas,
            'bulan'           => $bulan,
            'tahun'           => $tahun,
            'nominal_tagihan' => $nominal_tagihan,
            'status_bayar'    => $status_bayar,
            'tanggal_bayar'   => $tanggal_bayar,
            'keterangan'      => $keterangan
        ];

        $res = $this->payroll->save_siswa_ekstra($data_save);

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Data siswa <b>' . htmlspecialchars($nama_siswa) . '</b> berhasil didaftarkan ke ekstra!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menambahkan data siswa.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?ekstra_id=' . $ekstra_id . '&bulan=' . $bulan . '&tahun=' . $tahun);
    }

    /**
     * Hapus Data Siswa Ekstra
     */
    public function delete_siswa($id = null) {
        cek_menu_access();

        if ($id) {
            $this->payroll->delete_siswa_ekstra((int)$id);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Data siswa ekstra berhasil dihapus.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        $bulan = (int)$this->input->get('bulan') ?: date('n');
        $tahun = (int)$this->input->get('tahun') ?: date('Y');
        $ekstra_id = (int)$this->input->get('ekstra_id');
        $query_str = '?bulan=' . $bulan . '&tahun=' . $tahun . ($ekstra_id ? '&ekstra_id=' . $ekstra_id : '');
        redirect('gaji/ekstra' . $query_str);
    }

    /**
     * Edit / Update Data Siswa Ekstra
     */
    public function edit_siswa() {
        cek_menu_access();

        $id              = (int)$this->input->post('id', true);
        $ekstra_id       = (int)$this->input->post('ekstra_id', true);
        $nama_siswa      = trim($this->input->post('nama_siswa', true));
        $kelas           = trim($this->input->post('kelas', true));
        $bulan           = (int)$this->input->post('bulan', true);
        $tahun           = (int)$this->input->post('tahun', true);
        $nominal_tagihan = (float)$this->input->post('nominal_tagihan', true);
        $status_bayar    = $this->input->post('status_bayar', true) === 'lunas' ? 'lunas' : 'pending';
        $tanggal_bayar   = $status_bayar === 'lunas' ? ($this->input->post('tanggal_bayar', true) ?: date('Y-m-d')) : null;
        $keterangan      = trim($this->input->post('keterangan', true));

        if (!$id || empty($nama_siswa)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Data siswa tidak valid!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra');
            return;
        }

        // Jika nominal tagihan kosong / 0, ambil default dari tarif cabang ekstra
        if ($nominal_tagihan <= 0 && $ekstra_id > 0) {
            $ek = $this->db->get_where('m_ekstra', ['ekstra_id' => $ekstra_id])->row_array();
            if ($ek) $nominal_tagihan = (float)$ek['tarif_standar'];
        }

        $data_save = [
            'ekstra_id'       => $ekstra_id,
            'nama_siswa'      => $nama_siswa,
            'kelas'           => $kelas,
            'bulan'           => $bulan,
            'tahun'           => $tahun,
            'nominal_tagihan' => $nominal_tagihan,
            'status_bayar'    => $status_bayar,
            'tanggal_bayar'   => $tanggal_bayar,
            'keterangan'      => $keterangan
        ];

        $res = $this->payroll->save_siswa_ekstra($data_save, $id);

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Perubahan data siswa <b>' . htmlspecialchars($nama_siswa) . '</b> berhasil disimpan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menyimpan perubahan data siswa.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?ekstra_id=' . $ekstra_id . '&bulan=' . $bulan . '&tahun=' . $tahun);
    }

    /**
     * Hapus Cabang Ekstra
     */
    public function delete_ekstra($ekstra_id = null) {
        cek_menu_access();

        if ($ekstra_id) {
            $this->payroll->delete_ekstra((int)$ekstra_id);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Cabang ekstrakurikuler beserta data peserta terkait berhasil dihapus.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra/cabang');
    }

    /**
     * Simpan / Perbarui Data Master Cabang Ekstra
     */
    public function save_ekstra() {
        cek_menu_access();

        $ekstra_id     = (int)$this->input->post('ekstra_id', true);
        $nama_ekstra   = trim($this->input->post('nama_ekstra', true));
        $pegawai_id    = (int)$this->input->post('pegawai_id', true);
        $tipe_honor    = trim($this->input->post('tipe_honor', true));
        $tarif_standar = (float)$this->input->post('tarif_standar', true);
        $keterangan    = trim($this->input->post('keterangan', true));

        if (empty($nama_ekstra)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Nama cabang ekstra wajib diisi!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra/cabang');
            return;
        }

        $data_save = [
            'nama_ekstra'   => $nama_ekstra,
            'pegawai_id'    => $pegawai_id > 0 ? $pegawai_id : null,
            'tipe_honor'    => in_array($tipe_honor, ['per_anak', 'per_sesi', 'per_kedatangan']) ? $tipe_honor : 'per_anak',
            'tarif_standar' => $tarif_standar,
            'keterangan'    => $keterangan,
            'is_active'     => 'y'
        ];

        $res = $this->payroll->save_ekstra($data_save, $ekstra_id > 0 ? $ekstra_id : null);

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Cabang ekstra <b>' . htmlspecialchars($nama_ekstra) . '</b> berhasil disimpan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menyimpan cabang ekstra.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra/cabang');
    }

    /**
     * Halaman Khusus Master Cabang Ekstra & Guru Pengampu
     */
    public function cabang() {
        cek_menu_access();
        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Manajemen Ekstra & Siswa';
        $data['title']      = 'Master Cabang Ekstra';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        // Ambil daftar cabang ekstra
        $data['ekstras'] = $this->payroll->get_all_ekstra();

        // Ambil daftar guru aktif untuk pengampu ekstra
        $data['guru_list'] = $this->db->query("SELECT pegawai_id, nama_pegawai FROM m_pegawai WHERE is_del = 'n' AND is_status = 'y' ORDER BY nama_pegawai ASC")->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/ekstra/cabang', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }
}
