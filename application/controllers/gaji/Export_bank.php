<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Export_bank
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Generator File Mutasi Payroll Perbankan:
 * 1. CMS BRI (CSV) sesuai template resmi Bank BRI Corporate Internet Banking
 * 2. CMS BSI (CSV / Excel) untuk penerima rekening Bank Syariah Indonesia
 * 3. Master Rekapitulasi Payroll Yayasan (CSV Spreadsheet)
 */
class Export_bank extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
    }

    /**
     * Halaman Dashboard Export Mutasi Bank
     */
    public function index($periode_id = null) {
        cek_menu_access();

        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Export Mutasi Bank';
        $data['title']      = 'Export Mutasi Bank Payroll';
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

        $data['selected_id']     = $selected_id;
        $data['recon']           = null;
        $data['setting_global']  = $this->payroll->get_setting_global();

        if ($selected_id) {
            $data['recon'] = $this->payroll->get_reconciliation_data($selected_id);
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/export_bank/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * 1-Click Download File Mutasi CMS Bank BRI (CSV)
     * Format sesuai Standar Bank BRI Corporate Internet Banking (CMS Payroll)
     */
    public function bri($periode_id = null) {
        cek_menu_access();

        if (!$periode_id) {
            redirect('gaji/export_bank');
            return;
        }

        $recon = $this->payroll->get_reconciliation_data((int)$periode_id);
        if (!$recon || empty($recon['rincian'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning p-3">Data periode belum memiliki rincian gaji untuk diexport.</div>');
            redirect('gaji/export_bank/index/' . $periode_id);
            return;
        }

        $periode = $recon['periode'];
        $setting = $recon['setting_global'];
        $debit_account = !empty($setting['rekening_debit_yayasan']) ? $setting['rekening_debit_yayasan'] : '219701000399305';

        // Filter daftar penerima (semua yang ditransfer via BRI atau gabungan payroll)
        $list = $recon['rincian'];

        $count = count($list);
        $total_amount = 0;

        foreach ($list as $r) {
            $total_amount += (int)round((float)$r['take_home_pay']);
        }

        $check_val = $count . $total_amount;
        $value_date = date('dmY'); // e.g. 10112026 atau tanggal hari ini DDMMYYYY

        // Nama file download
        $filename = 'PAYROLL_CMS_BRI_SDMIS_' . str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT) . '_' . $periode['tahun'] . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // 1. Header CSV BRI CMS
        fputcsv($out, [
            'NO',
            'NAMA',
            'ACCOUNT',
            'AMOUNT',
            'Email',
            'CUSTOMERREFF',
            'BENEFICIARY BANK',
            'BENEFICIARY ADDRESS'
        ]);

        // 2. Baris Data Guru
        $no = 1;
        foreach ($list as $r) {
            $amt = (int)round((float)$r['take_home_pay']);
            $acc = preg_replace('/[^0-9]/', '', $r['nomor_rekening']);
            $bank_code = (strtoupper(trim($r['nama_bank'] ?? '')) === 'BSI') ? 'BSMDIDJA' : '';
            $reff = !empty($r['customer_reff']) ? $r['customer_reff'] : ('MT' . strtoupper(substr(date('M', mktime(0,0,0,$periode['bulan'],1)), 0, 4)) . sprintf('%03d', $no));

            // Ambil email dari tabel m_pegawai atau default
            $email = !empty($r['email']) ? $r['email'] : 'keuangan@sdmis.sch.id';

            fputcsv($out, [
                $no++,
                strtoupper($r['nama_pemilik_rekening'] ? $r['nama_pemilik_rekening'] : $r['nama_pegawai']),
                $acc,
                $amt,
                $email,
                $reff,
                $bank_code,
                ''
            ]);
        }

        // 3. Trailer / Footer Baris Kontrol Integritas BRI CMS
        fputcsv($out, ['COUNT', '', '', $count, '', '', '', '']);
        fputcsv($out, ['TOTAL', '', '', $total_amount, '', '', '', '']);
        fputcsv($out, ['CHECK', '', '', $check_val, '', '', '', '']);
        fputcsv($out, ['VALUEDATE (DDMMYYYY)', '', '', $value_date, '', '', '', '']);
        fputcsv($out, ['DEBITACCOUNT', '', '', $debit_account, '', '', '', '']);

        fclose($out);
        exit();
    }

    /**
     * 1-Click Download File Mutasi CMS Bank BSI (CSV)
     * Format khusus penerima rekening Bank BSI (Kliring / Clearing Transfer)
     */
    public function bsi($periode_id = null) {
        cek_menu_access();

        if (!$periode_id) {
            redirect('gaji/export_bank');
            return;
        }

        $recon = $this->payroll->get_reconciliation_data((int)$periode_id);
        if (!$recon || empty($recon['rincian'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning p-3">Data periode belum memiliki rincian gaji untuk diexport.</div>');
            redirect('gaji/export_bank/index/' . $periode_id);
            return;
        }

        $periode = $recon['periode'];
        $bsi_items = $recon['bank_summary']['BSI']['items'];

        $filename = 'PAYROLL_CMS_BSI_SDMIS_' . str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT) . '_' . $periode['tahun'] . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // Header BSI CMS
        fputcsv($out, [
            'NO',
            'BENEFICIARY_NAME',
            'BENEFICIARY_ACCOUNT',
            'AMOUNT',
            'BANK_NAME',
            'CUSTOMER_REFF',
            'REMARKS'
        ]);

        $no = 1;
        $total_bsi = 0;
        foreach ($bsi_items as $r) {
            $amt = (int)round((float)$r['take_home_pay']);
            $total_bsi += $amt;
            $acc = preg_replace('/[^0-9]/', '', $r['nomor_rekening']);
            $reff = !empty($r['customer_reff']) ? $r['customer_reff'] : ('BSI' . sprintf('%03d', $no));

            fputcsv($out, [
                $no++,
                strtoupper($r['nama_pemilik_rekening'] ? $r['nama_pemilik_rekening'] : $r['nama_pegawai']),
                $acc,
                $amt,
                'BANK SYARIAH INDONESIA (BSI)',
                $reff,
                'MUKAFAAH SDMIS ' . strtoupper($periode['nama_periode'])
            ]);
        }

        // Summary row
        fputcsv($out, ['TOTAL BSI', count($bsi_items) . ' REKENING', '', $total_bsi, '', '', '']);

        fclose($out);
        exit();
    }

    /**
     * Download Master Rekapitulasi Spreadsheet Lengkap (CSV / Excel Compatible)
     */
    public function master_csv($periode_id = null) {
        cek_menu_access();

        if (!$periode_id) {
            redirect('gaji/export_bank');
            return;
        }

        $recon = $this->payroll->get_reconciliation_data((int)$periode_id);
        if (!$recon || empty($recon['rincian'])) {
            redirect('gaji/export_bank/index/' . $periode_id);
            return;
        }

        $periode = $recon['periode'];
        $filename = 'MASTER_PAYROLL_SDMIS_' . str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT) . '_' . $periode['tahun'] . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // Header Master
        fputcsv($out, [
            'NO',
            'NIP_PEGAWAI',
            'NAMA_PEGAWAI',
            'STATUS_KEPEGAWAIAN',
            'MASA_KERJA_TAHUN',
            'GAJI_POKOK',
            'TUNJ_MASA_KERJA',
            'TUNJ_WALI_KELAS',
            'TUNJ_STRUKTURAL',
            'HARI_HADIR',
            'TUNJ_MAKAN',
            'BONUS_TIER1',
            'BONUS_TIER2',
            'TOTAL_BONUS_PRESENSI',
            'HONOR_EKSTRA_LUNAS',
            'TALANGAN_OPERASIONAL',
            'TUNJ_TRANSPORT',
            'TUNJ_KESETARAAN',
            'TOTAL_PENGHASILAN_KOTOR',
            'POTONGAN_KASBON',
            'TOTAL_POTONGAN',
            'TAKE_HOME_PAY',
            'NAMA_BANK',
            'NOMOR_REKENING',
            'NAMA_PEMILIK_REKENING',
            'CUSTOMER_REFF'
        ]);

        $no = 1;
        foreach ($recon['rincian'] as $r) {
            fputcsv($out, [
                $no++,
                $r['id_pegawai'],
                $r['nama_pegawai'],
                strtoupper($r['status_kepegawaian']),
                $r['masa_kerja_tahun'],
                $r['gaji_pokok'],
                $r['tunj_masa_kerja'],
                $r['tunj_wali_kelas'],
                $r['tunj_struktural'],
                $r['hari_hadir_presensi'],
                $r['tunj_makan'],
                $r['bonus_presensi_tier1'],
                $r['bonus_presensi_tier2'],
                $r['total_tunj_presensi'],
                $r['honor_ekstra_lunas'],
                $r['talangan_operasional'],
                $r['tunj_transport'],
                $r['tunj_kesetaraan'],
                $r['total_penghasilan_kotor'],
                $r['potongan_kasbon'],
                $r['total_potongan'],
                $r['take_home_pay'],
                $r['nama_bank'],
                $r['nomor_rekening'],
                $r['nama_pemilik_rekening'] ? $r['nama_pemilik_rekening'] : $r['nama_pegawai'],
                $r['customer_reff']
            ]);
        }

        fclose($out);
        exit();
    }
}
