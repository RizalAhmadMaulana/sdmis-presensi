<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Laporan
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Mengelola Cetak Slip Gaji Resmi Format A5 (Single & Batch) serta Rekapitulasi Pembukuan Yayasan
 */
class Laporan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
    }

    /**
     * Halaman Utama: Daftar Slip Gaji Guru per Periode & Rekapitulasi
     */
    public function index($periode_id = null) {
        cek_menu_access();

        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Slip Gaji & Rekapitulasi';
        $data['title']      = 'Slip Gaji & Rekapitulasi';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        $periodes = $this->payroll->get_all_periode();
        $data['periodes'] = $periodes;

        $selected_id = null;
        if ($periode_id) {
            $selected_id = (int)$periode_id;
        } else if (!empty($periodes)) {
            $selected_id = (int)$periodes[0]['periode_id'];
        }

        $data['selected_id'] = $selected_id;
        $data['periode']     = null;
        $data['rincian']     = [];

        if ($selected_id) {
            $data['periode'] = $this->payroll->get_periode_by_id($selected_id);
            $data['rincian'] = $this->payroll->get_rincian_gaji_periode($selected_id);
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/laporan/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * Cetak Slip Gaji Resmi A5 Satu Pegawai
     */
    public function slip($gaji_id = null) {
        cek_menu_access();

        if (!$gaji_id) {
            redirect('gaji/laporan');
            return;
        }

        $slip = $this->payroll->get_gaji_karyawan_by_id((int)$gaji_id);
        if (!$slip) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger p-3">Data slip gaji tidak ditemukan.</div>');
            redirect('gaji/laporan');
            return;
        }

        $slip['terbilang'] = $this->payroll->terbilang($slip['take_home_pay']);

        $data['slips']          = [$slip];
        $data['is_batch']       = false;
        $data['setting_global'] = $this->payroll->get_setting_global();

        $this->load->view('module/gaji/laporan/slip_a5', $data);
    }

    /**
     * Cetak Semua Slip Gaji Sekaligus (Batch Print A5 Otomatis Page Break)
     */
    public function slip_batch($periode_id = null) {
        cek_menu_access();

        if (!$periode_id) {
            redirect('gaji/laporan');
            return;
        }

        $periode = $this->payroll->get_periode_by_id((int)$periode_id);
        $rincian = $this->payroll->get_rincian_gaji_periode((int)$periode_id);

        if (!$periode || empty($rincian)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning p-3">Belum ada rincian slip gaji untuk dicetak pada periode ini.</div>');
            redirect('gaji/laporan/index/' . $periode_id);
            return;
        }

        foreach ($rincian as &$row) {
            $row['nama_periode']  = $periode['nama_periode'];
            $row['bulan']         = $periode['bulan'];
            $row['tahun']         = $periode['tahun'];
            $row['tanggal_awal']  = $periode['tanggal_awal'];
            $row['tanggal_akhir'] = $periode['tanggal_akhir'];
            $row['terbilang']     = $this->payroll->terbilang($row['take_home_pay']);
        }

        $data['slips']          = $rincian;
        $data['is_batch']       = true;
        $data['periode']        = $periode;
        $data['setting_global'] = $this->payroll->get_setting_global();

        $this->load->view('module/gaji/laporan/slip_a5', $data);
    }

    /**
     * Cetak Rekapitulasi Pembukuan Mukafaah Bulanan Yayasan (Landscape Format)
     */
    public function rekap($periode_id = null) {
        cek_menu_access();

        if (!$periode_id) {
            redirect('gaji/laporan');
            return;
        }

        $recon = $this->payroll->get_reconciliation_data((int)$periode_id);
        if (!$recon) {
            redirect('gaji/laporan');
            return;
        }

        $data['recon']          = $recon;
        $data['periode']        = $recon['periode'];
        $data['setting_global'] = $recon['setting_global'];
        $data['terbilang_total'] = $this->payroll->terbilang($recon['totals']['take_home_pay']);

        $this->load->view('module/gaji/laporan/rekap_print', $data);
    }
}
