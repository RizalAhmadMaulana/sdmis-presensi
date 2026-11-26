<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Laporan
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Mengelola Unduhan PDF Resmi (PHP 8.4+ Native Pure-PHP Vector PDF Engine via Payroll_pdf):
 * - Slip Gaji Perorangan A5 Portrait
 * - Batch Semua Slip Gaji A5 Portrait
 * - Rekapitulasi Pembukuan Yayasan A4 Landscape
 */
class Laporan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
        $this->load->library('simple_pdf');
        $this->load->library('payroll_pdf');
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
     * Unduh File PDF Slip Gaji Resmi A5 Satu Pegawai
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

        $nama_file   = $this->_sanitize_filename($slip['nama_pegawai']);
        $periode_tag = str_pad($slip['bulan'] ?? date('n'), 2, '0', STR_PAD_LEFT) . '_' . ($slip['tahun'] ?? date('Y'));
        $filename    = 'Slip_Mukafaah_' . $nama_file . '_' . $periode_tag . '.pdf';

        $logo_path   = $this->_get_logo_path();
        $tanggal_ttd = $this->_format_tanggal_indo(date('Y-m-d'));

        $this->payroll_pdf->download_slips([$slip], $filename, $logo_path, $tanggal_ttd);
    }

    /**
     * Unduh Semua Slip Gaji Sekaligus dalam 1 File PDF (Format A5 per halaman)
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
            $this->session->set_flashdata('message', '<div class="alert alert-warning p-3">Belum ada rincian slip gaji untuk diunduh pada periode ini.</div>');
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
        unset($row);

        $periode_slug = $this->_sanitize_filename($periode['nama_periode']);
        $filename     = 'Slip_Mukafaah_Semua_' . $periode_slug . '.pdf';

        $logo_path   = $this->_get_logo_path();
        $tanggal_ttd = $this->_format_tanggal_indo(date('Y-m-d'));

        $this->payroll_pdf->download_slips($rincian, $filename, $logo_path, $tanggal_ttd);
    }

    /**
     * Unduh Rekapitulasi Pembukuan Mukafaah Bulanan Yayasan (PDF A4 Landscape)
     */
    public function rekap($periode_id = null) {
        cek_menu_access();

        if (!$periode_id) {
            redirect('gaji/laporan');
            return;
        }

        $recon = $this->payroll->get_reconciliation_data((int)$periode_id);
        if (!$recon || empty($recon['rincian'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning p-3">Belum ada data rekapitulasi untuk diunduh pada periode ini.</div>');
            redirect('gaji/laporan/index/' . $periode_id);
            return;
        }

        $terbilang_total = $this->payroll->terbilang($recon['totals']['take_home_pay']);
        $periode_slug    = $this->_sanitize_filename($recon['periode']['nama_periode']);
        $filename        = 'Rekapitulasi_Mukafaah_' . $periode_slug . '.pdf';

        $logo_path   = $this->_get_logo_path();
        $tanggal_ttd = $this->_format_tanggal_indo(date('Y-m-d'));

        $this->payroll_pdf->download_rekap($recon, $terbilang_total, $filename, $logo_path, $tanggal_ttd);
    }

    /**
     * Mengambil path file logo resmi SDMIS pada server
     */
    private function _get_logo_path() {
        $candidates = [
            FCPATH . 'assets/temp/assets/logo/client/favicon.png',
            FCPATH . 'assets/temp/assets/logo/client/logo.png'
        ];
        foreach ($candidates as $path) {
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
        }
        return '';
    }

    /**
     * Format tanggal Indonesia resmi untuk tanda tangan dokumen
     */
    private function _format_tanggal_indo($date_str) {
        $bulan_indo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $ts = strtotime($date_str);
        if (!$ts) return date('d/m/Y');
        $d = (int)date('j', $ts);
        $m = (int)date('n', $ts);
        $y = date('Y', $ts);
        return $d . ' ' . ($bulan_indo[$m] ?? date('F', $ts)) . ' ' . $y;
    }

    /**
     * Membersihkan teks untuk nama file aman
     */
    private function _sanitize_filename($str) {
        $clean = preg_replace('/[^A-Za-z0-9]+/', '_', trim((string)$str));
        $clean = trim($clean, '_');
        return $clean !== '' ? $clean : 'Dokumen';
    }
}
