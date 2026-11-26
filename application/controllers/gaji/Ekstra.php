<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Ekstra
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Arsitektur 2-Tab:
 *   Tab 1: Ekstra Per Anak & Tunggakan Siswa (Input Cepat Jumlah Anak Lunas + Daftar Tunggakan & Carry-Over)
 *   Tab 2: Rekap Sesi & Kedatangan Guru (Log Multi-Baris per_sesi & per_kedatangan)
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
     * Halaman Utama Manajemen Ekstra & Honor Pengampu (2-Tab)
     */
    public function index() {
        cek_menu_access();
        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Manajemen Ekstra & Siswa';
        $data['title']      = 'Manajemen Ekstra & Honor Pengampu';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        // Filter Cabang, Bulan, Tahun, dan Active Tab
        $bulan = (int)$this->input->get('bulan');
        if ($bulan < 1 || $bulan > 12) $bulan = (int)date('n');

        $tahun = (int)$this->input->get('tahun');
        if ($tahun < 2020) $tahun = (int)date('Y');

        $ekstra_id = $this->input->get('ekstra_id');
        $ekstra_id = !empty($ekstra_id) ? (int)$ekstra_id : null;

        $active_tab = trim((string)$this->input->get('tab', true));
        if (!in_array($active_tab, ['per_anak', 'per_sesi'])) {
            $active_tab = 'per_anak';
        }

        // Ambil daftar cabang ekstra (semua & terpisah per tipe untuk masing-masing tab)
        $all_ekstras        = $this->payroll->get_all_ekstra();
        $ekstras_per_anak   = $this->payroll->get_ekstra_by_tipe(['per_anak']);
        $ekstras_per_sesi   = $this->payroll->get_ekstra_by_tipe(['per_sesi', 'per_kedatangan']);

        // Tentukan filter spesifik tab (jika cabang terpilih bertipe sesi, arahkan otomatis ke Tab 2 jika belum dipilih tab eksplisit)
        $ekstra_id_anak = null;
        $ekstra_id_sesi = null;
        if ($ekstra_id) {
            $selected_cabang = $this->payroll->get_ekstra_by_id($ekstra_id);
            if ($selected_cabang) {
                if ($selected_cabang['tipe_honor'] === 'per_anak') {
                    $ekstra_id_anak = $ekstra_id;
                    if (!$this->input->get('tab')) {
                        $active_tab = 'per_anak';
                    }
                } else {
                    $ekstra_id_sesi = $ekstra_id;
                    if (!$this->input->get('tab')) {
                        $active_tab = 'per_sesi';
                    }
                }
            }
        }

        $data['bulan']              = $bulan;
        $data['tahun']              = $tahun;
        $data['selected_ekstra_id'] = $ekstra_id;
        $data['active_tab']         = $active_tab;
        $data['ekstras']            = $all_ekstras;
        $data['ekstras_per_anak']   = $ekstras_per_anak;
        $data['ekstras_per_sesi']   = $ekstras_per_sesi;

        // Data Tab 1: Blok A (Rekap Cepat Anak Lunas) & Blok B (Tunggakan & Carry-Over)
        $rekap_anak     = $this->payroll->get_rekap_ekstra_per_anak($bulan, $tahun, $ekstra_id_anak);
        $tunggakan_list = $this->payroll->get_piutang_siswa_ekstra($ekstra_id_anak, $bulan, $tahun);

        // Data Tab 2: Ringkasan per Cabang + Log Multi-Baris Sesi & Kedatangan
        $ringkasan_sesi = $this->payroll->get_ringkasan_sesi_per_cabang($bulan, $tahun, $ekstra_id_sesi);
        $sesi_list      = $this->payroll->get_sesi_ekstra($ekstra_id_sesi, $bulan, $tahun);

        // Hitung Statistik Global untuk KPI Cards
        $total_anak_lunas_rekap = 0;
        $total_carry_over_anak  = 0;
        $honor_anak_cair        = 0.00;
        $total_tunggakan        = 0;
        $nominal_tunggakan      = 0.00;

        foreach ($rekap_anak as $rk) {
            $total_anak_lunas_rekap += (int)$rk['jumlah_lunas'];
            $total_carry_over_anak  += (int)$rk['carry_over_count'];
            $honor_anak_cair        += (float)$rk['total_honor_cair'];
            $total_tunggakan        += (int)$rk['pending_count'];
            $nominal_tunggakan      += (float)$rk['pending_nominal'];
        }

        $total_sesi_disetujui = 0;
        $honor_sesi_cair      = 0.00;
        $total_sesi_pending   = 0;
        $nominal_sesi_pending = 0.00;

        foreach ($ringkasan_sesi as $rs) {
            $total_sesi_disetujui += (int)$rs['disetujui_sesi'];
            $honor_sesi_cair      += (float)$rs['disetujui_honor'];
            $total_sesi_pending   += (int)$rs['pending_sesi'];
            $nominal_sesi_pending += (float)$rs['pending_honor'];
        }

        $data['rekap_anak']     = $rekap_anak;
        $data['tunggakan_list'] = $tunggakan_list;
        $data['ringkasan_sesi'] = $ringkasan_sesi;
        $data['sesi_list']      = $sesi_list;

        $data['stats'] = [
            'total_anak_rekap'      => $total_anak_lunas_rekap,
            'total_carry_over'      => $total_carry_over_anak,
            'total_anak_cair'       => $total_anak_lunas_rekap + $total_carry_over_anak,
            'honor_anak_cair'       => $honor_anak_cair,
            'total_sesi_disetujui'  => $total_sesi_disetujui,
            'honor_sesi_cair'       => $honor_sesi_cair,
            'total_sesi_pending'    => $total_sesi_pending,
            'nominal_sesi_pending'  => $nominal_sesi_pending,
            'total_tunggakan'       => $total_tunggakan,
            'nominal_tunggakan'     => $nominal_tunggakan,
            'total_honor_cair'      => $honor_anak_cair + $honor_sesi_cair,
            'total_honor_tertahan'  => $nominal_tunggakan + $nominal_sesi_pending
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/ekstra/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * TAB 1 BLOK A: Simpan Batch Input Cepat Jumlah Anak Lunas Per Anak
     */
    public function save_rekap_anak() {
        cek_menu_access();

        $bulan            = (int)$this->input->post('bulan');
        $tahun            = (int)$this->input->post('tahun');
        $filter_ekstra_id = (string)$this->input->post('filter_ekstra_id', true);
        $rekap_input      = $this->input->post('rekap');

        if ($bulan < 1 || $bulan > 12) $bulan = (int)date('n');
        if ($tahun < 2020) $tahun = (int)date('Y');

        $updated = $this->payroll->save_rekap_ekstra_batch($bulan, $tahun, $rekap_input);

        if ($updated > 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Rekap cepat jumlah siswa lunas (' . $updated . ' cabang ekstra) bulan ' . sprintf('%02d', $bulan) . '/' . $tahun . ' berhasil disimpan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Tidak ada cabang ekstra Per Anak yang diperbarui.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $bulan . '&tahun=' . $tahun);
    }

    /**
     * TAB 1 BLOK B: Catat Tunggakan Siswa Belum Lunas (Khusus Cabang per_anak)
     */
    public function add_siswa() {
        cek_menu_access();

        $ekstra_id       = (int)$this->input->post('ekstra_id');
        $nama_siswa      = trim((string)$this->input->post('nama_siswa', true));
        $kelas           = trim((string)$this->input->post('kelas', true));
        $bulan           = (int)$this->input->post('bulan');
        $tahun           = (int)$this->input->post('tahun');
        $nominal_tagihan = (float)$this->input->post('nominal_tagihan');
        $status_bayar    = trim((string)$this->input->post('status_bayar', true));
        $keterangan      = trim((string)$this->input->post('keterangan', true));

        $filter_ekstra_id = (string)$this->input->post('filter_ekstra_id', true);
        $filter_bulan     = (int)$this->input->post('filter_bulan') ?: $bulan;
        $filter_tahun     = (int)$this->input->post('filter_tahun') ?: $tahun;

        if ($ekstra_id <= 0 || empty($nama_siswa)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Nama siswa dan cabang ekstra wajib diisi!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
            return;
        }

        $ekstra = $this->payroll->get_ekstra_by_id($ekstra_id);
        if (!$ekstra || $ekstra['tipe_honor'] !== 'per_anak') {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Tunggakan siswa hanya berlaku untuk cabang Ekstra Per Anak!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
            return;
        }

        if ($nominal_tagihan <= 0) {
            $nominal_tagihan = (float)$ekstra['tarif_standar'];
        }

        $is_lunas = ($status_bayar === 'lunas');
        $data_insert = [
            'ekstra_id'       => $ekstra_id,
            'nama_siswa'      => $nama_siswa,
            'kelas'           => $kelas,
            'bulan'           => $bulan,
            'tahun'           => $tahun,
            'status_bayar'    => $is_lunas ? 'lunas' : 'pending',
            'tanggal_bayar'   => $is_lunas ? date('Y-m-d') : null,
            'bulan_cair'      => $is_lunas ? $filter_bulan : null,
            'tahun_cair'      => $is_lunas ? $filter_tahun : null,
            'nominal_tagihan' => $nominal_tagihan,
            'keterangan'      => $keterangan !== '' ? $keterangan : null
        ];

        $res = $this->payroll->save_siswa_ekstra($data_insert);

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Catatan tunggakan ananda <b>' . htmlspecialchars($nama_siswa) . '</b> berhasil disimpan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menyimpan catatan tunggakan siswa.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
    }

    /**
     * TAB 1 BLOK B: Edit Data Tunggakan Siswa
     */
    public function edit_siswa() {
        cek_menu_access();

        $id              = (int)$this->input->post('id');
        $ekstra_id       = (int)$this->input->post('ekstra_id');
        $nama_siswa      = trim((string)$this->input->post('nama_siswa', true));
        $kelas           = trim((string)$this->input->post('kelas', true));
        $bulan           = (int)$this->input->post('bulan');
        $tahun           = (int)$this->input->post('tahun');
        $nominal_tagihan = (float)$this->input->post('nominal_tagihan');
        $keterangan      = trim((string)$this->input->post('keterangan', true));

        $filter_ekstra_id = (string)$this->input->post('filter_ekstra_id', true);
        $filter_bulan     = (int)$this->input->post('filter_bulan') ?: $bulan;
        $filter_tahun     = (int)$this->input->post('filter_tahun') ?: $tahun;

        if ($id <= 0 || $ekstra_id <= 0 || empty($nama_siswa)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Data siswa tidak lengkap!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
            return;
        }

        $ekstra = $this->payroll->get_ekstra_by_id($ekstra_id);
        if (!$ekstra || $ekstra['tipe_honor'] !== 'per_anak') {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Cabang ekstra harus bertipe Per Anak!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
            return;
        }

        if ($nominal_tagihan <= 0) {
            $nominal_tagihan = (float)$ekstra['tarif_standar'];
        }

        $data_save = [
            'ekstra_id'       => $ekstra_id,
            'nama_siswa'      => $nama_siswa,
            'kelas'           => $kelas,
            'bulan'           => $bulan,
            'tahun'           => $tahun,
            'nominal_tagihan' => $nominal_tagihan,
            'keterangan'      => $keterangan !== '' ? $keterangan : null
        ];

        $res = $this->payroll->save_siswa_ekstra($data_save, $id);

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Perubahan data tunggakan <b>' . htmlspecialchars($nama_siswa) . '</b> berhasil disimpan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menyimpan perubahan data.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
    }

    /**
     * TAB 1 BLOK B: Melunasi Tunggakan & Menentukan Periode Pencairan Honor (Carry-Over)
     */
    public function lunasi_tunggakan() {
        cek_menu_access();

        $id            = (int)$this->input->post('id');
        $bulan_cair    = (int)$this->input->post('bulan_cair');
        $tahun_cair    = (int)$this->input->post('tahun_cair');
        $tanggal_bayar = trim((string)$this->input->post('tanggal_bayar', true));

        $filter_ekstra_id = (string)$this->input->post('filter_ekstra_id', true);
        $filter_bulan     = (int)$this->input->post('filter_bulan') ?: $bulan_cair;
        $filter_tahun     = (int)$this->input->post('filter_tahun') ?: $tahun_cair;

        if ($bulan_cair < 1 || $bulan_cair > 12) $bulan_cair = $filter_bulan;
        if ($tahun_cair < 2020) $tahun_cair = $filter_tahun;

        $res = $this->payroll->lunasi_piutang_siswa($id, $bulan_cair, $tahun_cair, $tanggal_bayar);

        $alert_cls = $res['status'] ? 'alert-success' : 'alert-danger';
        $icon_cls  = $res['status'] ? 'ti-check' : 'ti-x';
        $this->session->set_flashdata('message', '<div class="alert ' . $alert_cls . ' alert-dismissible p-3 mb-4" role="alert"><i class="ti ' . $icon_cls . ' me-2"></i> ' . $res['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

        redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
    }

    /**
     * TAB 1 BLOK B: Batalkan Status Lunas (Kembalikan ke Daftar Tunggakan Pending)
     */
    public function batal_lunas($id = null) {
        cek_menu_access();

        $bulan     = (int)$this->input->get('bulan') ?: (int)date('n');
        $tahun     = (int)$this->input->get('tahun') ?: (int)date('Y');
        $ekstra_id = (string)$this->input->get('ekstra_id', true);

        if ($id) {
            $res = $this->payroll->batalkan_lunas_piutang((int)$id);
            $alert_cls = $res['status'] ? 'alert-info' : 'alert-danger';
            $this->session->set_flashdata('message', '<div class="alert ' . $alert_cls . ' alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-rotate-clockwise me-2"></i> ' . $res['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($ekstra_id) . '&bulan=' . $bulan . '&tahun=' . $tahun);
    }

    /**
     * Toggle Status Pelunasan (AJAX / Kompatibilitas)
     */
    public function toggle_bayar($id = null) {
        cek_menu_access();
        if (!$id) {
            redirect('gaji/ekstra');
            return;
        }

        $bulan_cair = (int)$this->input->get('bulan_cair') ?: null;
        $tahun_cair = (int)$this->input->get('tahun_cair') ?: null;
        $res = $this->payroll->toggle_status_bayar_siswa($id, $bulan_cair, $tahun_cair);

        if ($this->input->is_ajax_request()) {
            echo json_encode($res);
            return;
        }

        $alert_cls = $res['status'] ? 'alert-success' : 'alert-danger';
        $this->session->set_flashdata('message', '<div class="alert ' . $alert_cls . ' alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> ' . $res['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode((string)$this->input->get('ekstra_id')) . '&bulan=' . ((int)$this->input->get('bulan') ?: date('n')) . '&tahun=' . ((int)$this->input->get('tahun') ?: date('Y')));
    }

    /**
     * TAB 1 BLOK B: Hapus Catatan Tunggakan Siswa
     */
    public function delete_siswa($id = null) {
        cek_menu_access();

        $bulan     = (int)$this->input->get('bulan') ?: (int)date('n');
        $tahun     = (int)$this->input->get('tahun') ?: (int)date('Y');
        $ekstra_id = (string)$this->input->get('ekstra_id', true);

        if ($id) {
            $this->payroll->delete_siswa_ekstra((int)$id);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Catatan tunggakan siswa berhasil dihapus.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?tab=per_anak&ekstra_id=' . urlencode($ekstra_id) . '&bulan=' . $bulan . '&tahun=' . $tahun);
    }

    /**
     * TAB 2: Simpan / Edit Baris Log Sesi & Kedatangan Guru (per_sesi / per_kedatangan)
     */
    public function save_sesi() {
        cek_menu_access();

        $sesi_id        = (int)$this->input->post('sesi_id');
        $ekstra_id      = (int)$this->input->post('ekstra_id');
        $bulan          = (int)$this->input->post('bulan');
        $tahun          = (int)$this->input->post('tahun');
        $tanggal_sesi   = trim((string)$this->input->post('tanggal_sesi', true));
        $jumlah_sesi    = max(1, (int)$this->input->post('jumlah_sesi'));
        $tarif_per_sesi = (float)$this->input->post('tarif_per_sesi');
        $keterangan     = trim((string)$this->input->post('keterangan', true));
        $status         = trim((string)$this->input->post('status', true));

        $filter_ekstra_id = (string)$this->input->post('filter_ekstra_id', true);
        $filter_bulan     = (int)$this->input->post('filter_bulan') ?: $bulan;
        $filter_tahun     = (int)$this->input->post('filter_tahun') ?: $tahun;

        if ($ekstra_id <= 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Pilih cabang ekstra per sesi / per kedatangan terlebih dahulu!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra?tab=per_sesi&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
            return;
        }

        $ekstra = $this->payroll->get_ekstra_by_id($ekstra_id);
        if (!$ekstra || !in_array($ekstra['tipe_honor'], ['per_sesi', 'per_kedatangan'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Cabang yang dipilih bukan bertipe Per Sesi / Per Kedatangan!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/ekstra?tab=per_sesi&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
            return;
        }

        if ($tarif_per_sesi <= 0) {
            $tarif_per_sesi = (float)$ekstra['tarif_standar'];
        }
        $total_honor = $jumlah_sesi * $tarif_per_sesi;

        // Sinkronkan bulan & tahun dari tanggal_sesi jika tanggal dipilih & modal tidak mengunci terpisah
        if ($bulan < 1 || $bulan > 12) $bulan = $filter_bulan;
        if ($tahun < 2020) $tahun = $filter_tahun;

        $data_save = [
            'ekstra_id'      => $ekstra_id,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'tanggal_sesi'   => $tanggal_sesi !== '' ? $tanggal_sesi : null,
            'jumlah_sesi'    => $jumlah_sesi,
            'tarif_per_sesi' => $tarif_per_sesi,
            'total_honor'    => $total_honor,
            'keterangan'     => $keterangan !== '' ? $keterangan : null,
            'status'         => in_array($status, ['disetujui', 'pending']) ? $status : 'disetujui'
        ];

        $res = $this->payroll->save_sesi_ekstra($data_save, $sesi_id > 0 ? $sesi_id : null);

        if ($res) {
            $aksi_txt = $sesi_id > 0 ? 'diperbarui' : 'dicatat';
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Log sesi <b>' . htmlspecialchars($ekstra['nama_ekstra']) . '</b> (' . $jumlah_sesi . 'x = Rp ' . number_format($total_honor, 0, ',', '.') . ') berhasil ' . $aksi_txt . '!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menyimpan data sesi & kedatangan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?tab=per_sesi&ekstra_id=' . urlencode($filter_ekstra_id) . '&bulan=' . $filter_bulan . '&tahun=' . $filter_tahun);
    }

    /**
     * TAB 2: Hapus Baris Log Sesi & Kedatangan
     */
    public function delete_sesi($sesi_id = null) {
        cek_menu_access();

        $bulan     = (int)$this->input->get('bulan') ?: (int)date('n');
        $tahun     = (int)$this->input->get('tahun') ?: (int)date('Y');
        $ekstra_id = (string)$this->input->get('ekstra_id', true);

        if ($sesi_id) {
            $this->payroll->delete_sesi_ekstra((int)$sesi_id);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Baris log sesi/kedatangan berhasil dihapus.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra?tab=per_sesi&ekstra_id=' . urlencode($ekstra_id) . '&bulan=' . $bulan . '&tahun=' . $tahun);
    }

    /**
     * Hapus Cabang Ekstra
     */
    public function delete_ekstra($ekstra_id = null) {
        cek_menu_access();

        if ($ekstra_id) {
            $this->payroll->delete_ekstra((int)$ekstra_id);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Cabang ekstrakurikuler beserta data rekap, tunggakan, dan log sesi terkait berhasil dihapus.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/ekstra/cabang');
    }

    /**
     * Simpan / Perbarui Data Master Cabang Ekstra
     */
    public function save_ekstra() {
        cek_menu_access();

        $ekstra_id     = (int)$this->input->post('ekstra_id', true);
        $nama_ekstra   = trim((string)$this->input->post('nama_ekstra', true));
        $pegawai_id    = (int)$this->input->post('pegawai_id', true);
        $tipe_honor    = trim((string)$this->input->post('tipe_honor', true));
        $tarif_standar = (float)$this->input->post('tarif_standar', true);
        $keterangan    = trim((string)$this->input->post('keterangan', true));

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

        $data['ekstras'] = $this->payroll->get_all_ekstra();
        $data['guru_list'] = $this->db->query("SELECT pegawai_id, nama_pegawai FROM m_pegawai WHERE is_del = 'n' AND is_status = 'y' ORDER BY nama_pegawai ASC")->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/ekstra/cabang', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }
}
