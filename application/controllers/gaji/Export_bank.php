<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Export_bank
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Generator File Mutasi Payroll Perbankan Resmi (Excel .xlsx Rapi Ber-border & Anti Scientific-Notation + Opsi CSV):
 * 1. CMS BRI (Excel .xlsx persis gaya Payroll November 2026 / CSV Mentah)
 * 2. CMS BSI (Excel .xlsx / CSV)
 * 3. Master Rekapitulasi Payroll Yayasan 26 Kolom (Excel .xlsx / CSV)
 */
class Export_bank extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
        $this->load->library('simple_xlsx');
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
     * Download File Mutasi CMS Bank BRI (Default: Excel .xlsx rapi sesuai template Payroll November; opsi ?format=csv)
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
        $debit_account = !empty($setting['rekening_debit_yayasan']) ? preg_replace('/[^0-9]/', '', $setting['rekening_debit_yayasan']) : '219701000399305';
        $list = $recon['rincian'];

        $count = count($list);
        $total_amount = 0;
        foreach ($list as $r) {
            $total_amount += (int)round((float)$r['take_home_pay']);
        }

        $check_val = (string)$count . (string)$total_amount;
        $value_date = date('dmY');
        $format = strtolower((string)$this->input->get('format'));

        $bulan_tag = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
        $base_name = 'Payroll_CMS_BRI_SDMIS_' . $bulan_tag . '_' . $periode['tahun'];

        // Opsi fallback CSV mentah jika admin membutuhkan upload TXT/CSV langsung ke portal BRI
        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $base_name . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $out = fopen('php://output', 'w');
            fputcsv($out, ['NO', 'NAMA', 'ACCOUNT', 'AMOUNT', 'Email', 'CUSTOMERREFF', 'BENEFICIARY BANK', 'BENEFICIARY ADDRESS']);

            $no = 1;
            foreach ($list as $r) {
                $amt = (int)round((float)$r['take_home_pay']);
                $acc = preg_replace('/[^0-9]/', '', (string)$r['nomor_rekening']);
                $bank_code = (strtoupper(trim($r['nama_bank'] ?? '')) === 'BSI') ? 'BSMDIDJA' : '';
                $reff = !empty($r['customer_reff']) ? $r['customer_reff'] : ('MT' . strtoupper(substr(date('M', mktime(0, 0, 0, $periode['bulan'], 1)), 0, 4)) . sprintf('%03d', $no));
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

            fputcsv($out, ['COUNT', '', '', $count, '', '', '', '']);
            fputcsv($out, ['TOTAL', '', '', $total_amount, '', '', '', '']);
            fputcsv($out, ['CHECK', '', '', $check_val, '', '', '', '']);
            fputcsv($out, ['VALUEDATE (DDMMYYYY)', '', '', $value_date, '', '', '', '']);
            fputcsv($out, ['DEBITACCOUNT', '', '', $debit_account, '', '', '', '']);
            fclose($out);
            exit();
        }

        // Default: Generate Excel (.xlsx) rapi dengan border penuh, lebar kolom pas, & nomor rekening format teks '@'
        $columns = [
            ['title' => 'NO',                  'type' => 'int',      'align' => 'center', 'min_width' => 6],
            ['title' => 'NAMA',                'type' => 'text',     'align' => 'left',   'min_width' => 24],
            ['title' => 'ACCOUNT',             'type' => 'text',     'align' => 'left',   'min_width' => 20],
            ['title' => 'AMOUNT',              'type' => 'currency', 'align' => 'right',  'min_width' => 14],
            ['title' => 'Email',               'type' => 'text',     'align' => 'left',   'min_width' => 28],
            ['title' => 'CUSTOMERREFF',        'type' => 'text',     'align' => 'center', 'min_width' => 15],
            ['title' => 'BENEFICIARY BANK',    'type' => 'text',     'align' => 'center', 'min_width' => 18],
            ['title' => 'BENEFICIARY ADDRESS', 'type' => 'text',     'align' => 'left',   'min_width' => 22],
        ];

        $rows = [];
        $no = 1;
        foreach ($list as $r) {
            $amt = (int)round((float)$r['take_home_pay']);
            $acc = preg_replace('/[^0-9]/', '', (string)$r['nomor_rekening']);
            $bank_code = (strtoupper(trim($r['nama_bank'] ?? '')) === 'BSI') ? 'BSMDIDJA' : '';
            $reff = !empty($r['customer_reff']) ? $r['customer_reff'] : ('MT' . strtoupper(substr(date('M', mktime(0, 0, 0, $periode['bulan'], 1)), 0, 4)) . sprintf('%03d', $no));
            $email = !empty($r['email']) ? trim((string)$r['email']) : 'keuangan@sdmis.sch.id';
            $nama_pemilik = strtoupper(trim((string)($r['nama_pemilik_rekening'] ? $r['nama_pemilik_rekening'] : $r['nama_pegawai'])));

            $rows[] = [
                $no++,
                $nama_pemilik,
                $acc,
                $amt,
                $email,
                $reff,
                $bank_code,
                ''
            ];
        }

        $footer_rows = [
            [
                'cells'  => ['COUNT', '', '', $count, '', '', '', ''],
                'types'  => [0 => 'label', 3 => 'int'],
                'merges' => [[0, 2]]
            ],
            [
                'cells'  => ['TOTAL', '', '', $total_amount, '', '', '', ''],
                'types'  => [0 => 'label', 3 => 'currency'],
                'merges' => [[0, 2]]
            ],
            [
                'cells'  => ['CHECK', '', '', $check_val, '', '', '', ''],
                'types'  => [0 => 'label', 3 => 'text_right'],
                'merges' => [[0, 2]]
            ],
            [
                'cells'  => ['VALUEDATE (DDMMYYYY)', '', '', $value_date, '', '', '', ''],
                'types'  => [0 => 'label', 3 => 'text_right'],
                'merges' => [[0, 2]]
            ],
            [
                'cells'  => ['DEBITACCOUNT', '', '', $debit_account, '', '', '', ''],
                'types'  => [0 => 'label', 3 => 'text_right'],
                'merges' => [[0, 2]]
            ],
        ];

        $this->simple_xlsx->download($base_name . '.xlsx', $columns, $rows, $footer_rows, 'CMS BRI');
    }

    /**
     * Download File Mutasi CMS Bank BSI (Default: Excel .xlsx; opsi ?format=csv)
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
        $format = strtolower((string)$this->input->get('format'));

        $bulan_tag = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
        $base_name = 'Payroll_CMS_BSI_SDMIS_' . $bulan_tag . '_' . $periode['tahun'];

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $base_name . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $out = fopen('php://output', 'w');
            fputcsv($out, ['NO', 'BENEFICIARY_NAME', 'BENEFICIARY_ACCOUNT', 'AMOUNT', 'BANK_NAME', 'CUSTOMER_REFF', 'REMARKS']);

            $no = 1;
            $total_bsi = 0;
            foreach ($bsi_items as $r) {
                $amt = (int)round((float)$r['take_home_pay']);
                $total_bsi += $amt;
                $acc = preg_replace('/[^0-9]/', '', (string)$r['nomor_rekening']);
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

            fputcsv($out, ['TOTAL BSI', count($bsi_items) . ' REKENING', '', $total_bsi, '', '', '']);
            fclose($out);
            exit();
        }

        $columns = [
            ['title' => 'NO',                  'type' => 'int',      'align' => 'center', 'min_width' => 6],
            ['title' => 'BENEFICIARY_NAME',    'type' => 'text',     'align' => 'left',   'min_width' => 24],
            ['title' => 'BENEFICIARY_ACCOUNT', 'type' => 'text',     'align' => 'left',   'min_width' => 20],
            ['title' => 'AMOUNT',              'type' => 'currency', 'align' => 'right',  'min_width' => 14],
            ['title' => 'BANK_NAME',           'type' => 'text',     'align' => 'center', 'min_width' => 28],
            ['title' => 'CUSTOMER_REFF',       'type' => 'text',     'align' => 'center', 'min_width' => 15],
            ['title' => 'REMARKS',             'type' => 'text',     'align' => 'left',   'min_width' => 28],
        ];

        $rows = [];
        $no = 1;
        $total_bsi = 0;
        foreach ($bsi_items as $r) {
            $amt = (int)round((float)$r['take_home_pay']);
            $total_bsi += $amt;
            $acc = preg_replace('/[^0-9]/', '', (string)$r['nomor_rekening']);
            $reff = !empty($r['customer_reff']) ? $r['customer_reff'] : ('BSI' . sprintf('%03d', $no));

            $rows[] = [
                $no++,
                strtoupper(trim((string)($r['nama_pemilik_rekening'] ? $r['nama_pemilik_rekening'] : $r['nama_pegawai']))),
                $acc,
                $amt,
                'BANK SYARIAH INDONESIA (BSI)',
                $reff,
                'MUKAFAAH SDMIS ' . strtoupper($periode['nama_periode'])
            ];
        }

        $footer_rows = [
            [
                'cells' => ['TOTAL BSI', count($bsi_items) . ' REKENING', '', $total_bsi, '', '', ''],
                'types' => [0 => 'label', 1 => 'label', 3 => 'currency']
            ]
        ];

        $this->simple_xlsx->download($base_name . '.xlsx', $columns, $rows, $footer_rows, 'CMS BSI');
    }

    /**
     * Download Master Rekapitulasi Spreadsheet Lengkap (Default: Excel .xlsx rapi ber-border; opsi ?format=csv)
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
        $format = strtolower((string)$this->input->get('format'));
        $bulan_tag = str_pad($periode['bulan'], 2, '0', STR_PAD_LEFT);
        $base_name = 'Master_Payroll_SDMIS_' . $bulan_tag . '_' . $periode['tahun'];

        $headers = [
            'NO', 'NIP_PEGAWAI', 'NAMA_PEGAWAI', 'STATUS_KEPEGAWAIAN', 'MASA_KERJA_TAHUN',
            'GAJI_POKOK', 'TUNJ_MASA_KERJA', 'TUNJ_WALI_KELAS', 'TUNJ_STRUKTURAL', 'HARI_HADIR',
            'TUNJ_MAKAN', 'BONUS_TIER1', 'BONUS_TIER2', 'TOTAL_BONUS_PRESENSI', 'HONOR_EKSTRA_LUNAS',
            'TALANGAN_OPERASIONAL', 'TUNJ_TRANSPORT', 'TUNJ_KESETARAAN', 'APRESIASI_KHUSUS',
            'TOTAL_PENGHASILAN_KOTOR', 'POTONGAN_KASBON', 'TOTAL_POTONGAN', 'TAKE_HOME_PAY',
            'NAMA_BANK', 'NOMOR_REKENING', 'NAMA_PEMILIK_REKENING', 'CUSTOMER_REFF'
        ];

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $base_name . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

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
                    $r['apresiasi_sertifikasi'],
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

        $columns = [
            ['title' => 'NO',                      'type' => 'int',      'align' => 'center', 'min_width' => 6],
            ['title' => 'NIP_PEGAWAI',             'type' => 'text',     'align' => 'center', 'min_width' => 15],
            ['title' => 'NAMA_PEGAWAI',            'type' => 'text',     'align' => 'left',   'min_width' => 24],
            ['title' => 'STATUS_KEPEGAWAIAN',      'type' => 'text',     'align' => 'center', 'min_width' => 14],
            ['title' => 'MASA_KERJA_TAHUN',        'type' => 'int',      'align' => 'center', 'min_width' => 10],
            ['title' => 'GAJI_POKOK',              'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'TUNJ_MASA_KERJA',         'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'TUNJ_WALI_KELAS',         'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'TUNJ_STRUKTURAL',         'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'HARI_HADIR',              'type' => 'int',      'align' => 'center', 'min_width' => 10],
            ['title' => 'TUNJ_MAKAN',              'type' => 'currency', 'align' => 'right',  'min_width' => 12],
            ['title' => 'BONUS_TIER1',             'type' => 'currency', 'align' => 'right',  'min_width' => 12],
            ['title' => 'BONUS_TIER2',             'type' => 'currency', 'align' => 'right',  'min_width' => 12],
            ['title' => 'TOTAL_BONUS_PRESENSI',    'type' => 'currency', 'align' => 'right',  'min_width' => 14],
            ['title' => 'HONOR_EKSTRA_LUNAS',      'type' => 'currency', 'align' => 'right',  'min_width' => 14],
            ['title' => 'TALANGAN_OPERASIONAL',    'type' => 'currency', 'align' => 'right',  'min_width' => 14],
            ['title' => 'TUNJ_TRANSPORT',          'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'TUNJ_KESETARAAN',         'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'APRESIASI_KHUSUS',        'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'TOTAL_PENGHASILAN_KOTOR', 'type' => 'currency', 'align' => 'right',  'min_width' => 16],
            ['title' => 'POTONGAN_KASBON',         'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'TOTAL_POTONGAN',          'type' => 'currency', 'align' => 'right',  'min_width' => 13],
            ['title' => 'TAKE_HOME_PAY',           'type' => 'currency', 'align' => 'right',  'min_width' => 15],
            ['title' => 'NAMA_BANK',               'type' => 'text',     'align' => 'center', 'min_width' => 10],
            ['title' => 'NOMOR_REKENING',          'type' => 'text',     'align' => 'left',   'min_width' => 20],
            ['title' => 'NAMA_PEMILIK_REKENING',   'type' => 'text',     'align' => 'left',   'min_width' => 24],
            ['title' => 'CUSTOMER_REFF',           'type' => 'text',     'align' => 'center', 'min_width' => 14],
        ];

        $rows = [];
        $no = 1;
        foreach ($recon['rincian'] as $r) {
            $rows[] = [
                $no++,
                (string)$r['id_pegawai'],
                (string)$r['nama_pegawai'],
                strtoupper((string)$r['status_kepegawaian']),
                (int)$r['masa_kerja_tahun'],
                (int)round((float)$r['gaji_pokok']),
                (int)round((float)$r['tunj_masa_kerja']),
                (int)round((float)$r['tunj_wali_kelas']),
                (int)round((float)$r['tunj_struktural']),
                (int)$r['hari_hadir_presensi'],
                (int)round((float)$r['tunj_makan']),
                (int)round((float)$r['bonus_presensi_tier1']),
                (int)round((float)$r['bonus_presensi_tier2']),
                (int)round((float)$r['total_tunj_presensi']),
                (int)round((float)$r['honor_ekstra_lunas']),
                (int)round((float)$r['talangan_operasional']),
                (int)round((float)$r['tunj_transport']),
                (int)round((float)$r['tunj_kesetaraan']),
                (int)round((float)$r['apresiasi_sertifikasi']),
                (int)round((float)$r['total_penghasilan_kotor']),
                (int)round((float)$r['potongan_kasbon']),
                (int)round((float)$r['total_potongan']),
                (int)round((float)$r['take_home_pay']),
                (string)$r['nama_bank'],
                preg_replace('/[^0-9]/', '', (string)$r['nomor_rekening']),
                (string)($r['nama_pemilik_rekening'] ? $r['nama_pemilik_rekening'] : $r['nama_pegawai']),
                (string)$r['customer_reff']
            ];
        }

        $tot = $recon['totals'];
        $footer_rows = [
            [
                'cells' => [
                    'GRAND TOTAL', '', count($recon['rincian']) . ' Pegawai', '', '',
                    (int)round($tot['gaji_pokok']),
                    (int)round($tot['tunj_masa_kerja']),
                    (int)round($tot['tunj_wali_kelas']),
                    (int)round($tot['tunj_struktural']),
                    '',
                    (int)round($tot['tunj_makan']),
                    (int)round($tot['bonus_presensi_tier1']),
                    (int)round($tot['bonus_presensi_tier2']),
                    (int)round($tot['total_tunj_presensi']),
                    (int)round($tot['honor_ekstra_lunas']),
                    (int)round($tot['talangan_operasional']),
                    (int)round($tot['tunj_transport']),
                    (int)round($tot['tunj_kesetaraan']),
                    (int)round($tot['apresiasi_sertifikasi']),
                    (int)round($tot['total_penghasilan_kotor']),
                    (int)round($tot['potongan_kasbon']),
                    (int)round($tot['total_potongan']),
                    (int)round($tot['take_home_pay']),
                    '', '', '', ''
                ],
                'types' => [
                    0 => 'label', 2 => 'label',
                    5 => 'currency', 6 => 'currency', 7 => 'currency', 8 => 'currency',
                    10 => 'currency', 11 => 'currency', 12 => 'currency', 13 => 'currency',
                    14 => 'currency', 15 => 'currency', 16 => 'currency', 17 => 'currency',
                    18 => 'currency', 19 => 'currency', 20 => 'currency', 21 => 'currency', 22 => 'currency'
                ]
            ]
        ];

        $this->simple_xlsx->download($base_name . '.xlsx', $columns, $rows, $footer_rows, 'Master Payroll');
    }

    /**
     * Alias method ke master_csv untuk kompatibilitas URL master_excel
     */
    public function master_excel($periode_id = null) {
        $this->master_csv($periode_id);
    }
}
