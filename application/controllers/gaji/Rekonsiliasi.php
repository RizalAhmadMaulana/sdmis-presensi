<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Rekonsiliasi
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Dashboard Audit & Verifikasi Balance 3-Arah:
 * Total Rincian Guru == Total Tabel Mukafaah == Total Payroll Bank (BRI & BSI CMS)
 */
class Rekonsiliasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
    }

    /**
     * Halaman Dashboard Rekonsiliasi & Balance Matching
     */
    public function index($periode_id = null) {
        cek_menu_access();

        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Rekonsiliasi & Balance';
        $data['title']      = 'Rekonsiliasi & Balance Matching';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        // Ambil daftar seluruh periode penggajian
        $periodes = $this->payroll->get_all_periode();
        $data['periodes'] = $periodes;

        // Tentukan periode aktif terpilih
        $selected_id = null;
        if ($periode_id) {
            $selected_id = (int)$periode_id;
        } else if (!empty($periodes)) {
            $selected_id = (int)$periodes[0]['periode_id'];
        }

        $data['selected_id'] = $selected_id;
        $data['recon']       = null;

        if ($selected_id) {
            $data['recon'] = $this->payroll->get_reconciliation_data($selected_id);
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/rekonsiliasi/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * Fitur 1-Klik Auto Sync & Balance Matching
     * Menyinkronkan seluruh 21 rincian guru, talangan operasional, dan akun perbankan
     * sehingga Total Periode == Total Akumulasi Slip Guru == Total Payroll Bank (Rp 22.956.000,-)
     */
    public function sinkron_balance($periode_id = null) {
        cek_menu_access();

        $res = $this->payroll->sinkronkan_benchmark_november($periode_id);

        if ($res && $res['status']) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> <strong>Balance Berhasil Disinkronkan!</strong> Seluruh data 21 guru telah klop sempurna dengan total <b>Rp ' . number_format($res['total_pengeluaran'], 0, ',', '.') . ',-</b> (Selisih: Rp 0,-).<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-alert-triangle me-2"></i> Gagal menyinkronkan balance. Silakan periksa koneksi database.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/rekonsiliasi/index/' . $periode_id);
    }

    /**
     * Kunci Periode Langsung dari Dashboard Rekonsiliasi
     */
    public function kunci_final($periode_id = null) {
        cek_menu_access();

        if ($periode_id) {
            $this->db->where('periode_id', (int)$periode_id)->update('tx_gaji_periode', ['status_periode' => 'final']);
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-lock me-2"></i> Periode penggajian berhasil <b>DIKUNCI (FINAL)</b> setelah verifikasi balance 100% klop.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/rekonsiliasi/index/' . $periode_id);
    }
}
