<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Setting
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Mengelola Pengaturan Parameter Global, Formula Presensi 2-Tier, dan Kalender Hari Khusus
 */
class Setting extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->library('form_validation');
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
    }

    /**
     * Halaman Utama Pengaturan Aturan & Tarif Penggajian
     */
    public function index() {
        cek_menu_access();
        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Pengaturan Aturan & Tarif';
        $data['title']      = 'Pengaturan Aturan & Tarif';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        $data['setting']     = $this->payroll->get_setting_global();
        $data['hari_khusus'] = $this->payroll->get_all_hari_khusus();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/setting/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * Proses Simpan Pembaruan Setting Global
     */
    public function update_setting() {
        cek_menu_access();

        // Helper pembersih angka desimal/rupiah
        $clean_num = function($val) {
            $cleaned = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $val));
            return is_numeric($cleaned) ? (float)$cleaned : 0.00;
        };

        $jam_masuk_standar     = trim($this->input->post('jam_masuk_standar', true));
        $toleransi_menit       = (int)$this->input->post('toleransi_menit', true);
        $jam_masuk_super_early = trim($this->input->post('jam_masuk_super_early', true));
        $max_toleransi_telat   = (int)$this->input->post('max_toleransi_telat', true);
        $nominal_bonus_tier1   = $clean_num($this->input->post('nominal_bonus_tier1', true));
        $nominal_bonus_tier2   = $clean_num($this->input->post('nominal_bonus_tier2', true));
        $tarif_makan_lama      = $clean_num($this->input->post('tarif_makan_lama', true));
        $tarif_makan_baru      = $clean_num($this->input->post('tarif_makan_baru', true));
        $tarif_masa_kerja      = $clean_num($this->input->post('tarif_masa_kerja_tahunan', true));
        $nominal_wali_kelas    = $clean_num($this->input->post('nominal_wali_kelas', true));
        $rekening_debit        = trim($this->input->post('rekening_debit_yayasan', true));
        $bank_sumber           = trim($this->input->post('bank_sumber_yayasan', true));

        // Format waktu agar valid format TIME HH:MM:SS
        if (strlen($jam_masuk_standar) == 5) $jam_masuk_standar .= ':00';
        if (strlen($jam_masuk_super_early) == 5) $jam_masuk_super_early .= ':00';

        $data_update = [
            'jam_masuk_standar'        => !empty($jam_masuk_standar) ? $jam_masuk_standar : '06:45:00',
            'toleransi_menit'          => $toleransi_menit > 0 ? $toleransi_menit : 3,
            'jam_masuk_super_early'    => !empty($jam_masuk_super_early) ? $jam_masuk_super_early : '06:30:00',
            'max_toleransi_telat'      => $max_toleransi_telat >= 0 ? $max_toleransi_telat : 3,
            'nominal_bonus_tier1'      => $nominal_bonus_tier1,
            'nominal_bonus_tier2'      => $nominal_bonus_tier2,
            'tarif_makan_lama'         => $tarif_makan_lama,
            'tarif_makan_baru'         => $tarif_makan_baru,
            'tarif_masa_kerja_tahunan' => $tarif_masa_kerja,
            'nominal_wali_kelas'       => $nominal_wali_kelas,
            'rekening_debit_yayasan'   => !empty($rekening_debit) ? $rekening_debit : '219701000399305',
            'bank_sumber_yayasan'      => !empty($bank_sumber) ? $bank_sumber : 'BRI'
        ];

        $res = $this->payroll->update_setting_global($data_update);

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Pengaturan aturan presensi 2-tier dan formula tarif gaji berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Terjadi kesalahan saat menyimpan pengaturan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/setting');
    }

    /**
     * Tambah Agenda Hari Khusus Pulang Cepat
     */
    public function add_hari_khusus() {
        cek_menu_access();

        $tanggal = trim($this->input->post('tanggal', true));
        $keterangan = trim($this->input->post('keterangan', true));
        $boleh_pulang_awal = $this->input->post('boleh_pulang_awal', true) === 'n' ? 'n' : 'y';

        if (empty($tanggal) || empty($keterangan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-circle me-2"></i> Tanggal dan keterangan agenda hari khusus wajib diisi!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            redirect('gaji/setting');
            return;
        }

        $data_insert = [
            'tanggal'           => $tanggal,
            'keterangan'        => $keterangan,
            'boleh_pulang_awal' => $boleh_pulang_awal
        ];

        $res = $this->payroll->add_hari_khusus($data_insert);

        if ($res === true) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Hari khusus tanggal <b>' . date('d M Y', strtotime($tanggal)) . '</b> berhasil ditambahkan ke kalender!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else if (is_string($res)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-triangle me-2"></i> ' . $res . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Gagal menambahkan hari khusus.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/setting');
    }

    /**
     * Hapus Agenda Hari Khusus
     */
    public function delete_hari_khusus($id = null) {
        cek_menu_access();

        if ($id) {
            $this->payroll->delete_hari_khusus((int)$id);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Agenda hari khusus berhasil dihapus dari kalender.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/setting');
    }
}
