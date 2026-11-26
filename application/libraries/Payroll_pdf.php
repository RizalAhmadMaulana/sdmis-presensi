<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payroll_pdf
 * Renderer dokumen PDF resmi SD Mentari Islamic School menggunakan Simple_pdf (PHP 8.4+ compatible):
 * - Slip Mukafaah & Gaji Pegawai (A5 Portrait, 1 halaman per slip, formal hitam-putih ber-bold tegas)
 * - Rekapitulasi Pembukuan Mukafaah Yayasan (A4 Landscape, tabel penuh + multi-halaman otomatis)
 */
class Payroll_pdf {

    public function __construct() {
        if (!class_exists('Simple_pdf', false)) {
            require_once APPPATH . 'libraries/Simple_pdf.php';
        }
    }

    /**
     * Render & download PDF Slip Gaji A5 Portrait (perorangan maupun batch)
     */
    public function download_slips($slips, $filename, $logo_path = '', $tanggal_ttd = '') {
        $pdf = new Simple_pdf();
        if (empty($tanggal_ttd)) {
            $tanggal_ttd = date('d/m/Y');
        }

        foreach ($slips as $s) {
            $pdf->add_page('A5', 'portrait');
            $this->_render_slip_a5_page($pdf, $s, $logo_path, $tanggal_ttd);
        }

        $pdf->download($filename, 'Slip Mukafaah SD Mentari Islamic School');
    }

    /**
     * Render & download PDF Rekapitulasi Pembukuan Yayasan A4 Landscape
     */
    public function download_rekap($recon, $terbilang_total, $filename, $logo_path = '', $tanggal_ttd = '') {
        $pdf = new Simple_pdf();
        if (empty($tanggal_ttd)) {
            $tanggal_ttd = date('d/m/Y');
        }

        $periode = $recon['periode'];
        $rincian = $recon['rincian'];
        $totals  = $recon['totals'];

        $margin_x = 10.0;
        $content_w = 277.0; // 297 - 20
        $max_y = 197.0;

        $pdf->add_page('A4', 'landscape');
        $y = $this->_draw_kop($pdf, $margin_x, 9.0, $content_w, $logo_path, true);

        // Judul Dokumen
        $pdf->set_text_color(0, 0, 0);
        $pdf->set_font('B', 11.0);
        $pdf->cell($margin_x, $y, $content_w, 5.5, 'REKAPITULASI PEMBUKUAN MUKAFAAH GURU & KARYAWAN', 'C', 0, true);
        $y += 5.5;

        $pdf->set_font('', 8.2);
        $cutoff = date('d/m/Y', strtotime($periode['tanggal_awal'])) . ' s/d ' . date('d/m/Y', strtotime($periode['tanggal_akhir']));
        $sub = 'Periode: ' . $periode['nama_periode'] . '  |  Rentang Cut-Off: ' . $cutoff . '  |  Hari Kerja Aktif: ' . (int)$periode['total_hari_kerja_aktif'] . ' Hari';
        $pdf->cell($margin_x, $y, $content_w, 4.5, $sub, 'C');
        $y += 7.0;

        // Definisi 14 Kolom Tabel Rekapitulasi (Total tepat 277.0 mm)
        $cols = [
            ['title' => 'NO',            'w' => 7.0,  'align' => 'C'],
            ['title' => 'NAMA PEGAWAI',  'w' => 41.0, 'align' => 'L'],
            ['title' => 'STATUS',        'w' => 16.0, 'align' => 'C'],
            ['title' => 'GAJI POKOK',    'w' => 18.0, 'align' => 'R'],
            ['title' => 'TUNJ. TMK',     'w' => 16.0, 'align' => 'R'],
            ['title' => 'WALI / STR',    'w' => 17.0, 'align' => 'R'],
            ['title' => 'U. MAKAN',      'w' => 16.0, 'align' => 'R'],
            ['title' => 'BONUS',         'w' => 16.0, 'align' => 'R'],
            ['title' => 'EKSTRA',        'w' => 17.0, 'align' => 'R'],
            ['title' => 'TALANGAN / ADJ', 'w' => 18.0, 'align' => 'R'],
            ['title' => 'TOTAL KOTOR',   'w' => 20.0, 'align' => 'R'],
            ['title' => 'POTONGAN',      'w' => 16.0, 'align' => 'R'],
            ['title' => 'TAKE HOME PAY', 'w' => 22.0, 'align' => 'R'],
            ['title' => 'REKENING BANK', 'w' => 37.0, 'align' => 'L'],
        ];

        $y = $this->_draw_rekap_header($pdf, $margin_x, $y, $cols);

        $no = 1;
        foreach ($rincian as $r) {
            // Cek wrap nama pegawai
            $pdf->set_font('B', 7.2);
            $nama_lines = $pdf->wrap_lines($r['nama_pegawai'], $cols[1]['w'] - 2.0);
            $row_h = max(7.2, (count($nama_lines) * 3.3) + 3.6);

            if ($y + $row_h > $max_y) {
                $pdf->add_page('A4', 'landscape');
                $y = 10.0;
                $y = $this->_draw_rekap_header($pdf, $margin_x, $y, $cols);
            }

            $wali_str = (float)$r['tunj_wali_kelas'] + (float)$r['tunj_struktural'];
            $adj_plus = (float)$r['talangan_operasional'] + (float)$r['tunj_transport'] + (float)$r['tunj_kesetaraan'] + (float)$r['apresiasi_sertifikasi'];
            $pot      = (float)$r['total_potongan'];

            $bank_txt = !empty($r['nomor_rekening'])
                ? strtoupper((string)$r['nama_bank']) . ': ' . $r['nomor_rekening']
                : 'Tunai / Pending';

            $vals = [
                (string)$no++,
                $r['nama_pegawai'],
                strtoupper((string)$r['status_kepegawaian']),
                $this->_fmt($r['gaji_pokok']),
                $this->_fmt($r['tunj_masa_kerja']),
                $this->_fmt($wali_str),
                $this->_fmt($r['tunj_makan']),
                $this->_fmt($r['total_tunj_presensi']),
                $this->_fmt($r['honor_ekstra_lunas']),
                $this->_fmt($adj_plus),
                $this->_fmt($r['total_penghasilan_kotor']),
                $pot > 0 ? $this->_fmt($pot) : '0',
                $this->_fmt($r['take_home_pay']),
                $bank_txt
            ];

            $pdf->set_draw_color(0, 0, 0);
            $cx = $margin_x;
            foreach ($cols as $idx => $col) {
                $pdf->rect($cx, $y, $col['w'], $row_h, 'D', 0.45);
                if ($idx === 1) {
                    $pdf->set_font('B', 7.1);
                    $pdf->multi_text($cx, $y + 0.8, $col['w'], 3.2, $r['nama_pegawai'], 'L', 1.0);
                    $nip_y = $y + (count($nama_lines) * 3.2) + 0.7;
                    $pdf->set_font('', 6.2);
                    $pdf->cell($cx, $nip_y, $col['w'], 2.8, 'NIP: ' . $r['id_pegawai'], 'L', 1.0);
                } elseif ($idx === 10 || $idx === 12) {
                    $pdf->set_font('B', 7.2);
                    $pdf->cell($cx, $y, $col['w'], $row_h, $vals[$idx], $col['align'], 1.0);
                } elseif ($idx === 13) {
                    $pdf->set_font('', 6.6);
                    $pdf->multi_text($cx, $y + 1.5, $col['w'], 3.2, $vals[$idx], 'L', 1.0);
                } else {
                    $pdf->set_font('', 7.1);
                    $pdf->cell($cx, $y, $col['w'], $row_h, $vals[$idx], $col['align'], 1.0);
                }
                $cx += $col['w'];
            }
            $y += $row_h;
        }

        // Baris Grand Total Yayasan
        $gt_h = 7.2;
        if ($y + $gt_h > $max_y) {
            $pdf->add_page('A4', 'landscape');
            $y = 10.0;
            $y = $this->_draw_rekap_header($pdf, $margin_x, $y, $cols);
        }

        $tot_wali_str = (float)$totals['tunj_wali_kelas'] + (float)$totals['tunj_struktural'];
        $tot_adj_plus = (float)$totals['talangan_operasional'] + (float)$totals['tunj_transport'] + (float)$totals['tunj_kesetaraan'] + (float)$totals['apresiasi_sertifikasi'];
        $tot_pot      = (float)$totals['total_potongan'];

        $merged_w = $cols[0]['w'] + $cols[1]['w'] + $cols[2]['w'];
        $pdf->set_fill_color(234, 234, 234);
        $pdf->set_draw_color(0, 0, 0);
        $pdf->set_font('B', 7.5);
        $pdf->rect($margin_x, $y, $merged_w, $gt_h, 'DF', 0.8);
        $pdf->cell($margin_x, $y, $merged_w, $gt_h, 'GRAND TOTAL YAYASAN', 'C');

        $gt_vals = [
            3  => $this->_fmt($totals['gaji_pokok']),
            4  => $this->_fmt($totals['tunj_masa_kerja']),
            5  => $this->_fmt($tot_wali_str),
            6  => $this->_fmt($totals['tunj_makan']),
            7  => $this->_fmt($totals['total_tunj_presensi']),
            8  => $this->_fmt($totals['honor_ekstra_lunas']),
            9  => $this->_fmt($tot_adj_plus),
            10 => $this->_fmt($totals['total_penghasilan_kotor']),
            11 => $tot_pot > 0 ? $this->_fmt($tot_pot) : '0',
            12 => 'Rp ' . $this->_fmt($totals['take_home_pay']),
            13 => ''
        ];

        $cx = $margin_x + $merged_w;
        for ($i = 3; $i < count($cols); $i++) {
            $pdf->rect($cx, $y, $cols[$i]['w'], $gt_h, 'DF', 0.8);
            $pdf->cell($cx, $y, $cols[$i]['w'], $gt_h, $gt_vals[$i], $cols[$i]['align'], 1.0);
            $cx += $cols[$i]['w'];
        }
        $pdf->line($margin_x, $y, $margin_x + $content_w, $y, 1.3);
        $y += $gt_h + 4.0;

        // Pastikan Kotak Terbilang + Tanda Tangan tidak terpotong halaman
        if ($y + 39.0 > $max_y) {
            $pdf->add_page('A4', 'landscape');
            $y = 14.0;
        }

        // Kotak Terbilang Total
        $pdf->set_fill_color(244, 244, 244);
        $pdf->set_draw_color(0, 0, 0);
        $pdf->rect($margin_x, $y, $content_w, 7.5, 'DF', 0.7);
        $pdf->set_font('B', 8.0);
        $lbl_tb = 'Terbilang Total Disbursement: ';
        $pdf->cell($margin_x, $y, 50.0, 7.5, $lbl_tb, 'L', 2.5);
        $pdf->set_font('I', 8.0);
        $pdf->cell($margin_x + 48.0, $y, $content_w - 50.0, 7.5, '# ' . $terbilang_total . ' #', 'L', 1.5);
        $y += 12.5;

        // Blok Tanda Tangan Resmi
        $half = $content_w / 2.0;
        $pdf->set_font('', 8.5);
        $pdf->cell($margin_x, $y, $half, 4.2, 'Mengetahui,', 'C');
        $pdf->cell($margin_x + $half, $y, $half, 4.2, 'Klaten, ' . $tanggal_ttd, 'C');
        $y += 4.2;

        $pdf->cell($margin_x, $y, $half, 4.2, 'Kepala SD Mentari Islamic School', 'C');
        $pdf->cell($margin_x + $half, $y, $half, 4.2, 'Bendahara Yayasan,', 'C');
        $y += 17.5;

        $pdf->set_font('B', 8.5);
        $pdf->cell($margin_x, $y, $half, 4.5, 'Guruh Suasana Eddy, S.Pd', 'C', 0, true);
        $pdf->cell($margin_x + $half, $y, $half, 4.5, 'Dewi Fatmala Sari, S.Pd', 'C', 0, true);

        $pdf->download($filename, 'Rekapitulasi Mukafaah - ' . $periode['nama_periode']);
    }

    private function _draw_rekap_header($pdf, $margin_x, $y, $cols) {
        $hdr_h = 8.5;
        $pdf->set_fill_color(232, 232, 232);
        $pdf->set_draw_color(0, 0, 0);
        $pdf->set_text_color(0, 0, 0);
        $pdf->set_font('B', 6.8);

        $cx = $margin_x;
        foreach ($cols as $col) {
            $pdf->rect($cx, $y, $col['w'], $hdr_h, 'DF', 0.6);
            $lines = $pdf->wrap_lines($col['title'], $col['w'] - 1.4);
            if (count($lines) > 1) {
                $pdf->multi_text($cx, $y + 1.0, $col['w'], 3.2, $col['title'], 'C', 0.7);
            } else {
                $pdf->cell($cx, $y, $col['w'], $hdr_h, $col['title'], 'C', 0.7);
            }
            $cx += $col['w'];
        }
        return $y + $hdr_h;
    }

    /**
     * Merender 1 lembar Slip Gaji A5 Portrait resmi hitam-putih
     */
    private function _render_slip_a5_page($pdf, $s, $logo_path, $tanggal_ttd) {
        $mx = 10.0;
        $cw = 128.0; // 148 - 20

        // 1. KOP SURAT RESMI + LOGO
        $y = $this->_draw_kop($pdf, $mx, 8.5, $cw, $logo_path, false);

        // 2. JUDUL DOKUMEN
        $pdf->set_text_color(0, 0, 0);
        $pdf->set_font('B', 10.0);
        $pdf->cell($mx, $y, $cw, 5.0, 'SLIP MUKAFAAH & GAJI PEGAWAI', 'C', 0, true);
        $y += 5.0;

        $cutoff_txt = (!empty($s['tanggal_awal']) && !empty($s['tanggal_akhir']))
            ? ' (Cut-Off: ' . date('d/m/Y', strtotime($s['tanggal_awal'])) . ' - ' . date('d/m/Y', strtotime($s['tanggal_akhir'])) . ')'
            : '';
        $pdf->set_font('B', 7.8);
        $pdf->cell($mx, $y, $cw, 4.2, 'Periode: ' . $s['nama_periode'] . $cutoff_txt, 'C');
        $y += 6.0;

        // 3. KOTAK IDENTITAS PEGAWAI
        $nama_jabatan = !empty($s['nama_jabatan_struktural'])
            ? $s['nama_jabatan_struktural']
            : ($s['is_wali_kelas'] === 'y' ? 'Wali Kelas' : 'Guru / Karyawan');
        $no_slip = 'SLIP/MIS/' . date('Ym', strtotime($s['tanggal_awal'] ?? date('Y-m-d'))) . '/' . sprintf('%03d', (int)$s['gaji_id']);
        $status_txt = strtoupper((string)$s['status_kepegawaian']) . ($s['status_kepegawaian'] === 'gty' ? ' (' . (int)$s['masa_kerja_tahun'] . ' Thn)' : '');

        $pdf->set_font('B', 7.5);
        $nama_lines = $pdf->wrap_lines($s['nama_pegawai'], 38.0);
        $jab_lines  = $pdf->wrap_lines($nama_jabatan, 38.0);

        $row1_h = max(4.5, count($nama_lines) * 3.5 + 1.0);
        $row2_h = max(4.5, count($jab_lines) * 3.5 + 1.0);
        $row3_h = 4.5;
        $meta_h = $row1_h + $row2_h + $row3_h + 2.5;

        $pdf->set_draw_color(0, 0, 0);
        $pdf->rect($mx, $y, $cw, $meta_h, 'D', 0.6);

        $lx = $mx + 1.5;
        $rx = $mx + 65.5;
        $my = $y + 1.2;

        // Baris 1: Nama & No Slip
        $pdf->set_font('', 7.5);
        $pdf->cell($lx, $my, 21.0, 4.0, 'Nama Pegawai', 'L', 0.5);
        $pdf->cell($lx + 20.5, $my, 3.0, 4.0, ':', 'C', 0);
        $pdf->set_font('B', 7.5);
        $pdf->multi_text($lx + 23.0, $my + 0.3, 39.5, 3.4, $s['nama_pegawai'], 'L', 0.3);

        $pdf->set_font('', 7.5);
        $pdf->cell($rx, $my, 19.0, 4.0, 'No. Slip', 'L', 0.5);
        $pdf->cell($rx + 18.5, $my, 3.0, 4.0, ':', 'C', 0);
        $pdf->set_font('B', 7.5);
        $pdf->cell($rx + 21.0, $my, 40.0, 4.0, $no_slip, 'L', 0.3);
        $my += $row1_h;

        // Baris 2: NIP & Jabatan
        $pdf->set_font('', 7.5);
        $pdf->cell($lx, $my, 21.0, 4.0, 'NIP / ID', 'L', 0.5);
        $pdf->cell($lx + 20.5, $my, 3.0, 4.0, ':', 'C', 0);
        $pdf->set_font('B', 7.5);
        $pdf->cell($lx + 23.0, $my, 39.5, 4.0, (string)$s['id_pegawai'], 'L', 0.3);

        $pdf->set_font('', 7.5);
        $pdf->cell($rx, $my, 19.0, 4.0, 'Jabatan', 'L', 0.5);
        $pdf->cell($rx + 18.5, $my, 3.0, 4.0, ':', 'C', 0);
        $pdf->set_font('B', 7.5);
        $pdf->multi_text($rx + 21.0, $my + 0.3, 40.0, 3.4, $nama_jabatan, 'L', 0.3);
        $my += $row2_h;

        // Baris 3: Status Kerja & Tgl Dokumen
        $pdf->set_font('', 7.5);
        $pdf->cell($lx, $my, 21.0, 4.0, 'Status Kerja', 'L', 0.5);
        $pdf->cell($lx + 20.5, $my, 3.0, 4.0, ':', 'C', 0);
        $pdf->set_font('B', 7.5);
        $pdf->cell($lx + 23.0, $my, 39.5, 4.0, $status_txt, 'L', 0.3);

        $pdf->set_font('', 7.5);
        $pdf->cell($rx, $my, 19.0, 4.0, 'Tgl Dokumen', 'L', 0.5);
        $pdf->cell($rx + 18.5, $my, 3.0, 4.0, ':', 'C', 0);
        $pdf->set_font('B', 7.5);
        $pdf->cell($rx + 21.0, $my, 40.0, 4.0, $tanggal_ttd, 'L', 0.3);

        $y += $meta_h + 4.0;

        // 4. TABEL RINCIAN 2-KOLOM (PENERIMAAN VS POTONGAN)
        $left_x = $mx;
        $left_lbl_w = 46.0;
        $left_val_w = 22.0; // Total kiri = 68mm
        $right_x = $mx + 71.0; // Gap 3mm
        $right_lbl_w = 36.0;
        $right_val_w = 21.0; // Total kanan = 57mm

        $hdr_h = 6.0;
        $row_h = 5.0;

        // Header Kolom Kiri
        $pdf->set_fill_color(235, 235, 235);
        $pdf->set_font('B', 7.4);
        $pdf->rect($left_x, $y, $left_lbl_w, $hdr_h, 'DF', 0.6);
        $pdf->cell($left_x, $y, $left_lbl_w, $hdr_h, 'I. PENERIMAAN / HAK', 'L', 1.5);
        $pdf->rect($left_x + $left_lbl_w, $y, $left_val_w, $hdr_h, 'DF', 0.6);
        $pdf->cell($left_x + $left_lbl_w, $y, $left_val_w, $hdr_h, 'JUMLAH (Rp)', 'R', 1.5);

        // Header Kolom Kanan
        $pdf->rect($right_x, $y, $right_lbl_w, $hdr_h, 'DF', 0.6);
        $pdf->cell($right_x, $y, $right_lbl_w, $hdr_h, 'II. POTONGAN', 'L', 1.5);
        $pdf->rect($right_x + $right_lbl_w, $y, $right_val_w, $hdr_h, 'DF', 0.6);
        $pdf->cell($right_x + $right_lbl_w, $y, $right_val_w, $hdr_h, 'JUMLAH (Rp)', 'R', 1.5);

        // Susun item Penerimaan
        $income_items = [
            ['Gaji Pokok', $s['gaji_pokok']]
        ];
        if ((float)$s['tunj_masa_kerja'] > 0)       $income_items[] = ['Tunj. Masa Kerja (' . (int)$s['masa_kerja_tahun'] . ' th)', $s['tunj_masa_kerja']];
        if ((float)$s['tunj_wali_kelas'] > 0)       $income_items[] = ['Tunj. Wali Kelas', $s['tunj_wali_kelas']];
        if ((float)$s['tunj_struktural'] > 0)       $income_items[] = ['Tunj. Struktural / Tugas', $s['tunj_struktural']];
        $income_items[] = ['Uang Makan (' . (int)$s['hari_hadir_presensi'] . ' hr)', $s['tunj_makan']];
        if ((float)$s['bonus_presensi_tier1'] > 0)  $income_items[] = ['Bonus Tier 1 (<=06.48)', $s['bonus_presensi_tier1']];
        if ((float)$s['bonus_presensi_tier2'] > 0)  $income_items[] = ['Bonus Tier 2 (<=06.30)', $s['bonus_presensi_tier2']];
        if ((float)$s['honor_ekstra_lunas'] > 0)    $income_items[] = ['Honor Ekstrakurikuler', $s['honor_ekstra_lunas']];
        if ((float)$s['talangan_operasional'] > 0)  $income_items[] = ['Talangan Operasional', $s['talangan_operasional']];
        if ((float)$s['tunj_transport'] > 0)        $income_items[] = ['Tunjangan Transport', $s['tunj_transport']];
        if ((float)$s['tunj_kesetaraan'] > 0)       $income_items[] = ['Tunj. Kesetaraan', $s['tunj_kesetaraan']];
        if ((float)$s['apresiasi_sertifikasi'] > 0) $income_items[] = ['Apresiasi / Tunj. Khusus', $s['apresiasi_sertifikasi']];

        $yl = $y + $hdr_h;
        $pdf->set_font('', 7.4);
        foreach ($income_items as $item) {
            $pdf->rect($left_x, $yl, $left_lbl_w, $row_h, 'D', 0.45);
            $pdf->cell($left_x, $yl, $left_lbl_w, $row_h, $item[0], 'L', 1.5);
            $pdf->rect($left_x + $left_lbl_w, $yl, $left_val_w, $row_h, 'D', 0.45);
            $pdf->cell($left_x + $left_lbl_w, $yl, $left_val_w, $row_h, $this->_fmt($item[1]), 'R', 1.5);
            $yl += $row_h;
        }
        // Baris Total Penerimaan Kotor
        $pdf->set_fill_color(242, 242, 242);
        $pdf->set_font('B', 7.5);
        $pdf->rect($left_x, $yl, $left_lbl_w, 5.8, 'DF', 0.65);
        $pdf->cell($left_x, $yl, $left_lbl_w, 5.8, 'Total Penerimaan Kotor', 'L', 1.5);
        $pdf->rect($left_x + $left_lbl_w, $yl, $left_val_w, 5.8, 'DF', 0.65);
        $pdf->cell($left_x + $left_lbl_w, $yl, $left_val_w, 5.8, $this->_fmt($s['total_penghasilan_kotor']), 'R', 1.5);
        $yl += 5.8;

        // Susun item Potongan
        $deduct_items = [];
        if ((float)$s['potongan_kasbon'] > 0)   $deduct_items[] = ['Potongan Kasbon', $s['potongan_kasbon']];
        if ((float)$s['potongan_talangan'] > 0) $deduct_items[] = ['Potongan Talangan', $s['potongan_talangan']];
        if ((float)$s['potongan_lainnya'] > 0)  $deduct_items[] = ['Potongan Lain-lain', $s['potongan_lainnya']];

        $yr = $y + $hdr_h;
        if (empty($deduct_items)) {
            $pdf->set_font('I', 7.3);
            $pdf->rect($right_x, $yr, $right_lbl_w + $right_val_w, 10.0, 'D', 0.45);
            $pdf->cell($right_x, $yr, $right_lbl_w + $right_val_w, 10.0, 'Tidak ada potongan', 'C');
            $yr += 10.0;
        } else {
            $pdf->set_font('', 7.4);
            foreach ($deduct_items as $di) {
                $pdf->rect($right_x, $yr, $right_lbl_w, $row_h, 'D', 0.45);
                $pdf->cell($right_x, $yr, $right_lbl_w, $row_h, $di[0], 'L', 1.5);
                $pdf->rect($right_x + $right_lbl_w, $yr, $right_val_w, $row_h, 'D', 0.45);
                $pdf->cell($right_x + $right_lbl_w, $yr, $right_val_w, $row_h, $this->_fmt($di[1]), 'R', 1.5);
                $yr += $row_h;
            }
        }
        // Baris Total Potongan (selalu tampil angka bersih, misal 0)
        $tot_pot = (float)$s['total_potongan'];
        $pdf->set_fill_color(242, 242, 242);
        $pdf->set_font('B', 7.5);
        $pdf->rect($right_x, $yr, $right_lbl_w, 5.8, 'DF', 0.65);
        $pdf->cell($right_x, $yr, $right_lbl_w, 5.8, 'Total Potongan', 'L', 1.5);
        $pdf->rect($right_x + $right_lbl_w, $yr, $right_val_w, 5.8, 'DF', 0.65);
        $pdf->cell($right_x + $right_lbl_w, $yr, $right_val_w, 5.8, $tot_pot > 0 ? $this->_fmt($tot_pot) : '0', 'R', 1.5);
        $yr += 5.8;

        $y = max($yl, $yr) + 4.5;

        // 5. KOTAK TAKE HOME PAY (THP)
        $thp_left_w = 84.0;
        $terbilang_str = 'Terbilang: # ' . $s['terbilang'] . ' #';
        $pdf->set_font('I', 7.2);
        $tb_lines = $pdf->wrap_lines($terbilang_str, $thp_left_w - 4.0);
        $thp_h = max(13.0, 7.5 + (count($tb_lines) * 3.3));

        $pdf->set_fill_color(244, 244, 244);
        $pdf->set_draw_color(0, 0, 0);
        $pdf->rect($mx, $y, $cw, $thp_h, 'DF', 1.1);

        $pdf->set_font('B', 8.3);
        $pdf->cell($mx + 2.0, $y + 1.5, $thp_left_w, 4.5, 'MUKAFAAH BERSIH DITERIMA (TAKE HOME PAY)', 'L', 0.5);
        $pdf->set_font('I', 7.2);
        $pdf->multi_text($mx + 2.0, $y + 6.2, $thp_left_w, 3.2, $terbilang_str, 'L', 0.5);

        $pdf->set_font('B', 11.8);
        $pdf->cell($mx + $thp_left_w, $y, $cw - $thp_left_w - 2.0, $thp_h, 'Rp ' . $this->_fmt($s['take_home_pay']) . ',-', 'R', 1.5);

        $y += $thp_h + 3.5;

        // 6. INFORMASI TRANSFER REKENING
        $pemilik = !empty($s['nama_pemilik_rekening']) ? $s['nama_pemilik_rekening'] : $s['nama_pegawai'];
        if (!empty($s['nomor_rekening'])) {
            $bank_txt = 'Metode Pencairan: Transfer Bank ' . ($s['nama_bank'] ? $s['nama_bank'] : 'BRI')
                . ' - No. Rek: ' . $s['nomor_rekening'] . ' (a.n. ' . $pemilik . ')';
        } else {
            $bank_txt = 'Metode Pencairan: Pembayaran Tunai / Rekening Belum Terdaftar';
        }
        $reff_txt = !empty($s['customer_reff']) ? ('Ref: ' . $s['customer_reff']) : '';

        $pdf->set_font('', 7.2);
        $bank_left_w = $reff_txt !== '' ? ($cw - 30.0) : ($cw - 3.0);
        $b_lines = $pdf->wrap_lines($bank_txt, $bank_left_w);
        $bank_h = max(6.5, (count($b_lines) * 3.3) + 2.8);

        $pdf->rect($mx, $y, $cw, $bank_h, 'D', 0.55);
        $pdf->multi_text($mx + 1.5, $y + 1.4, $bank_left_w, 3.3, $bank_txt, 'L', 0.5);
        if ($reff_txt !== '') {
            $pdf->set_font('B', 7.2);
            $pdf->cell($mx + $bank_left_w, $y, 28.5, $bank_h, $reff_txt, 'R', 1.0);
        }

        $y += $bank_h + 6.5;

        // 7. BLOK TANDA TANGAN RESMI
        $half = $cw / 2.0;
        $pdf->set_font('', 8.0);
        $pdf->cell($mx, $y, $half, 4.0, 'Penerima Mukafaah,', 'C');
        $pdf->cell($mx + $half, $y, $half, 4.0, 'Klaten, ' . $tanggal_ttd, 'C');
        $y += 4.0;
        $pdf->cell($mx + $half, $y, $half, 4.0, 'Bendahara Yayasan,', 'C');
        $y += 14.0;

        $pdf->set_font('B', 8.0);
        $pdf->cell($mx, $y, $half, 4.5, $s['nama_pegawai'], 'C', 0, true);
        $pdf->cell($mx + $half, $y, $half, 4.5, 'Dewi Fatmala Sari, S.Pd', 'C', 0, true);

        // 8. FOOTER HALAMAN BAWAH
        $foot_y = 197.0;
        $pdf->set_draw_color(0, 0, 0);
        $pdf->line($mx, $foot_y, $mx + $cw, $foot_y, 0.45);
        $pdf->set_font('', 6.6);
        $pdf->cell($mx, $foot_y + 1.0, $cw, 4.0, 'Dokumen resmi Yayasan Mentari Mandiri Sejahtera - SD Mentari Islamic School (SDMIS).', 'C');
    }

    /**
     * Menggambar Kop Surat Resmi SDMIS dengan Logo + Double Horizontal Rule
     */
    private function _draw_kop($pdf, $x, $y, $w, $logo_path = '', $is_landscape = false) {
        $logo_size = $is_landscape ? 14.0 : 12.5;
        $has_logo = ($logo_path !== '' && $pdf->add_image_file($logo_path, $x + 1.0, $y, $logo_size, $logo_size));

        $text_x = $has_logo ? ($x + $logo_size + 2.0) : $x;
        $text_w = $has_logo ? ($w - ($logo_size * 2) - 4.0) : $w;
        if ($text_w < 60) {
            $text_w = $w - $logo_size - 2.0;
        }

        $pdf->set_text_color(0, 0, 0);
        $pdf->set_font('B', $is_landscape ? 9.0 : 7.8);
        $pdf->cell($text_x, $y + 0.5, $text_w, 4.0, 'YAYASAN MENTARI MANDIRI SEJAHTERA', 'C');

        $pdf->set_font('B', $is_landscape ? 13.5 : 11.2);
        $pdf->cell($text_x, $y + 4.4, $text_w, 5.2, 'SD MENTARI ISLAMIC SCHOOL (SDMIS)', 'C');

        $pdf->set_font('', $is_landscape ? 7.8 : 6.9);
        $pdf->cell($text_x, $y + 9.4, $text_w, 3.6, 'Jl. Ngaran, Polanharjo, Kabupaten Klaten, Jawa Tengah | Telp/WA: 0822-4361-9388', 'C');

        $line_y = $y + ($is_landscape ? 15.0 : 14.0);
        $pdf->set_draw_color(0, 0, 0);
        $pdf->line($x, $line_y, $x + $w, $line_y, 1.5);
        $pdf->line($x, $line_y + 0.85, $x + $w, $line_y + 0.85, 0.5);

        return $line_y + 3.5;
    }

    private function _fmt($num) {
        return number_format((float)$num, 0, ',', '.');
    }
}
