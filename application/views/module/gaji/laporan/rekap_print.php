<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekapitulasi Mukafaah - <?=htmlspecialchars($periode['nama_periode']);?></title>
  <style>
    @page {
      size: A4 landscape;
      margin: 9mm 10mm 9mm 10mm;
    }
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      padding: 0;
      font-family: Helvetica, Arial, sans-serif;
      color: #000000;
      font-size: 7.8pt;
      line-height: 1.3;
    }

    /* KOP SURAT RESMI */
    .kop-table {
      width: 100%;
      border-collapse: collapse;
    }
    .kop-logo-cell {
      width: 52px;
      vertical-align: middle;
      text-align: left;
    }
    .kop-logo {
      width: 48px;
      height: 48px;
    }
    .kop-text-cell {
      vertical-align: middle;
      text-align: center;
      padding-right: 52px;
    }
    .kop-yayasan {
      font-size: 9pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .kop-sekolah {
      font-size: 14pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      margin: 1px 0;
    }
    .kop-alamat {
      font-size: 8pt;
    }
    .kop-rule-thick {
      border-top: 2px solid #000000;
      margin-top: 4px;
    }
    .kop-rule-thin {
      border-top: 0.75px solid #000000;
      margin-top: 2px;
      margin-bottom: 9px;
    }

    /* JUDUL DOKUMEN */
    .doc-title-wrap {
      text-align: center;
      margin-bottom: 10px;
    }
    .doc-title {
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: underline;
      letter-spacing: 0.4px;
    }
    .doc-sub {
      font-size: 8.2pt;
      margin-top: 3px;
    }

    /* TABEL REKAPITULASI */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 7.4pt;
    }
    table.data-table th,
    table.data-table td {
      border: 1px solid #000000;
      padding: 4px 4px;
      color: #000000;
      vertical-align: middle;
    }
    table.data-table thead th {
      background-color: #eaeaea;
      font-weight: bold;
      text-transform: uppercase;
      text-align: center;
      font-size: 7pt;
    }
    table.data-table td.num {
      text-align: right;
      white-space: nowrap;
    }
    table.data-table tr.total-row td {
      background-color: #eaeaea;
      font-weight: bold;
      border-top: 2px solid #000000;
      font-size: 7.8pt;
      padding: 5px 4px;
    }

    /* TERBILANG */
    .summary-box {
      margin-top: 9px;
      padding: 6px 9px;
      border: 1px solid #000000;
      background-color: #f4f4f4;
      font-size: 8pt;
      page-break-inside: avoid;
    }

    /* TANDA TANGAN */
    .sign-wrap {
      page-break-inside: avoid;
      margin-top: 14px;
    }
    .sign-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.2pt;
    }
    .sign-table td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      color: #000000;
    }
    .sign-space {
      height: 44px;
    }
    .sign-name {
      font-weight: bold;
      text-decoration: underline;
    }
  </style>
</head>
<body>
<?php $tgl_ttd_display = isset($tanggal_ttd) ? $tanggal_ttd : date('d/m/Y'); ?>

  <!-- KOP SURAT -->
  <table class="kop-table">
    <tr>
      <?php if (!empty($logo_data_uri)): ?>
        <td class="kop-logo-cell">
          <img src="<?=$logo_data_uri;?>" class="kop-logo" alt="Logo SDMIS">
        </td>
      <?php endif; ?>
      <td class="kop-text-cell">
        <div class="kop-yayasan">Yayasan Mentari Mandiri Sejahtera</div>
        <div class="kop-sekolah">SD MENTARI ISLAMIC SCHOOL (SDMIS)</div>
        <div class="kop-alamat">Jl. Ngaran, Polanharjo, Kabupaten Klaten, Jawa Tengah &bull; Telp/WA: 0822-4361-9388</div>
      </td>
    </tr>
  </table>
  <div class="kop-rule-thick"></div>
  <div class="kop-rule-thin"></div>

  <!-- JUDUL -->
  <div class="doc-title-wrap">
    <div class="doc-title">REKAPITULASI PEMBUKUAN MUKAFAAH GURU &amp; KARYAWAN</div>
    <div class="doc-sub">
      Periode: <strong><?=htmlspecialchars($periode['nama_periode']);?></strong> &bull;
      Rentang Cut-Off: <strong><?=date('d/m/Y', strtotime($periode['tanggal_awal']));?> s/d <?=date('d/m/Y', strtotime($periode['tanggal_akhir']));?></strong> &bull;
      Hari Kerja Aktif: <strong><?=(int)$periode['total_hari_kerja_aktif'];?> Hari</strong>
    </div>
  </div>

  <!-- TABEL DATA -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 24px;">No</th>
        <th>Nama Pegawai</th>
        <th style="width: 55px;">Status</th>
        <th>Gaji Pokok</th>
        <th>Tunj. TMK</th>
        <th>Wali / Str</th>
        <th>Uang Makan</th>
        <th>Bonus Presensi</th>
        <th>Honor Ekstra</th>
        <th>Talangan / Adj</th>
        <th>Total Kotor</th>
        <th>Potongan</th>
        <th>Take Home Pay</th>
        <th>Rekening Bank</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach ($recon['rincian'] as $r) :
        $wali_str = (float)$r['tunj_wali_kelas'] + (float)$r['tunj_struktural'];
        $adj_plus = (float)$r['talangan_operasional'] + (float)$r['tunj_transport'] + (float)$r['tunj_kesetaraan'] + (float)$r['apresiasi_sertifikasi'];
        $pot      = (float)$r['total_potongan'];
      ?>
        <tr>
          <td style="text-align: center;"><?=$no++;?></td>
          <td>
            <strong><?=htmlspecialchars($r['nama_pegawai']);?></strong>
            <div style="font-size: 6.8pt;">NIP: <?=htmlspecialchars((string)$r['id_pegawai']);?></div>
          </td>
          <td style="text-align: center; text-transform: uppercase; font-size: 6.8pt;">
            <?=htmlspecialchars((string)$r['status_kepegawaian']);?>
          </td>
          <td class="num"><?=number_format($r['gaji_pokok'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($r['tunj_masa_kerja'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($wali_str, 0, ',', '.');?></td>
          <td class="num"><?=number_format($r['tunj_makan'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($r['total_tunj_presensi'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($r['honor_ekstra_lunas'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($adj_plus, 0, ',', '.');?></td>
          <td class="num"><strong><?=number_format($r['total_penghasilan_kotor'], 0, ',', '.');?></strong></td>
          <td class="num"><?=$pot > 0 ? number_format($pot, 0, ',', '.') : '0';?></td>
          <td class="num"><strong><?=number_format($r['take_home_pay'], 0, ',', '.');?></strong></td>
          <td style="font-size: 7pt;">
            <?php if (!empty($r['nomor_rekening'])): ?>
              <strong><?=htmlspecialchars((string)$r['nama_bank']);?>:</strong> <?=htmlspecialchars((string)$r['nomor_rekening']);?>
            <?php else: ?>
              Tunai
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>

      <!-- BARIS GRAND TOTAL -->
      <?php
        $tot_wali_str = (float)$recon['totals']['tunj_wali_kelas'] + (float)$recon['totals']['tunj_struktural'];
        $tot_adj_plus = (float)$recon['totals']['talangan_operasional'] + (float)$recon['totals']['tunj_transport'] + (float)$recon['totals']['tunj_kesetaraan'] + (float)$recon['totals']['apresiasi_sertifikasi'];
        $tot_pot      = (float)$recon['totals']['total_potongan'];
      ?>
      <tr class="total-row">
        <td colspan="3" style="text-align: center;">GRAND TOTAL YAYASAN</td>
        <td class="num"><?=number_format($recon['totals']['gaji_pokok'], 0, ',', '.');?></td>
        <td class="num"><?=number_format($recon['totals']['tunj_masa_kerja'], 0, ',', '.');?></td>
        <td class="num"><?=number_format($tot_wali_str, 0, ',', '.');?></td>
        <td class="num"><?=number_format($recon['totals']['tunj_makan'], 0, ',', '.');?></td>
        <td class="num"><?=number_format($recon['totals']['total_tunj_presensi'], 0, ',', '.');?></td>
        <td class="num"><?=number_format($recon['totals']['honor_ekstra_lunas'], 0, ',', '.');?></td>
        <td class="num"><?=number_format($tot_adj_plus, 0, ',', '.');?></td>
        <td class="num"><?=number_format($recon['totals']['total_penghasilan_kotor'], 0, ',', '.');?></td>
        <td class="num"><?=$tot_pot > 0 ? number_format($tot_pot, 0, ',', '.') : '0';?></td>
        <td class="num">Rp <?=number_format($recon['totals']['take_home_pay'], 0, ',', '.');?></td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <!-- TERBILANG -->
  <div class="summary-box">
    <strong>Terbilang Total Disbursement:</strong>
    <em># <?=htmlspecialchars($terbilang_total);?> #</em>
  </div>

  <!-- TANDA TANGAN -->
  <div class="sign-wrap">
    <table class="sign-table">
      <tr>
        <td>
          <div>Mengetahui,</div>
          <div>Kepala SD Mentari Islamic School</div>
          <div class="sign-space"></div>
          <div class="sign-name">Guruh Suasana Eddy, S.Pd</div>
        </td>
        <td>
          <div>Klaten, <?=htmlspecialchars($tgl_ttd_display);?></div>
          <div>Bendahara Yayasan,</div>
          <div class="sign-space"></div>
          <div class="sign-name">Dewi Fatmala Sari, S.Pd</div>
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
