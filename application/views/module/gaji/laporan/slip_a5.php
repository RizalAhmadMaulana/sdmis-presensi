<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Slip Mukafaah Pegawai - SD Mentari Islamic School</title>
  <style>
    @page {
      size: A5 portrait;
      margin: 8mm 10mm 8mm 10mm;
    }
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      padding: 0;
      font-family: Helvetica, Arial, sans-serif;
      color: #000000;
      font-size: 8.2pt;
      line-height: 1.32;
    }
    .slip-page {
      width: 100%;
    }
    .page-break {
      page-break-after: always;
    }

    /* KOP SURAT RESMI */
    .kop-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 3px;
    }
    .kop-logo-cell {
      width: 46px;
      vertical-align: middle;
      text-align: left;
    }
    .kop-logo {
      width: 42px;
      height: 42px;
    }
    .kop-text-cell {
      vertical-align: middle;
      text-align: center;
      padding-right: 14px;
    }
    .kop-yayasan {
      font-size: 8pt;
      font-weight: bold;
      letter-spacing: 0.4px;
      text-transform: uppercase;
      color: #000000;
    }
    .kop-sekolah {
      font-size: 12pt;
      font-weight: bold;
      letter-spacing: 0.3px;
      text-transform: uppercase;
      color: #000000;
      margin: 1px 0;
    }
    .kop-alamat {
      font-size: 7.2pt;
      color: #000000;
    }
    .kop-rule-thick {
      border-top: 2px solid #000000;
      margin-top: 4px;
    }
    .kop-rule-thin {
      border-top: 0.75px solid #000000;
      margin-top: 2px;
      margin-bottom: 7px;
    }

    /* JUDUL SLIP */
    .doc-title-wrap {
      text-align: center;
      margin-bottom: 7px;
    }
    .doc-title {
      font-size: 10pt;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: underline;
      letter-spacing: 0.4px;
      color: #000000;
    }
    .doc-subtitle {
      font-size: 8pt;
      color: #000000;
      margin-top: 2px;
    }

    /* IDENTITAS PEGAWAI */
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000000;
      margin-bottom: 8px;
      font-size: 7.8pt;
    }
    .meta-table td {
      padding: 2.5px 5px;
      vertical-align: top;
      color: #000000;
    }
    .meta-lbl {
      width: 18%;
      color: #000000;
    }
    .meta-sep {
      width: 2%;
      text-align: center;
    }
    .meta-val {
      width: 30%;
      font-weight: bold;
    }

    /* TABEL RINCIAN PENERIMAAN & POTONGAN */
    .cols-layout {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .col-left {
      width: 53%;
      vertical-align: top;
      padding: 0;
    }
    .col-gap {
      width: 2%;
      padding: 0;
    }
    .col-right {
      width: 45%;
      vertical-align: top;
      padding: 0;
    }
    .detail-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 7.7pt;
    }
    .detail-table th,
    .detail-table td {
      border: 1px solid #000000;
      padding: 3px 5px;
      color: #000000;
    }
    .detail-table th {
      background-color: #ededed;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 7.5pt;
      text-align: left;
    }
    .detail-table th.num-hdr {
      text-align: right;
      width: 34%;
    }
    .detail-table td.num {
      text-align: right;
      white-space: nowrap;
    }
    .detail-table tr.total-row td {
      background-color: #f3f3f3;
      font-weight: bold;
      border-top: 1.5px solid #000000;
    }
    .empty-deduct {
      text-align: center;
      padding: 12px 5px !important;
      color: #333333 !important;
      font-style: italic;
    }

    /* KOTAK TAKE HOME PAY */
    .thp-table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000000;
      background-color: #f4f4f4;
      margin-bottom: 7px;
    }
    .thp-table td {
      padding: 6px 8px;
      vertical-align: middle;
      color: #000000;
    }
    .thp-title {
      font-size: 8.5pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .thp-terbilang {
      font-size: 7.5pt;
      font-style: italic;
      margin-top: 2px;
      color: #000000;
    }
    .thp-amount {
      text-align: right;
      font-size: 12pt;
      font-weight: bold;
      white-space: nowrap;
      width: 34%;
    }

    /* INFO TRANSFER BANK */
    .bank-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000000;
      margin-bottom: 10px;
      font-size: 7.5pt;
    }
    .bank-table td {
      padding: 4px 7px;
      color: #000000;
    }

    /* TANDA TANGAN */
    .sign-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 8pt;
    }
    .sign-table td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      color: #000000;
    }
    .sign-space {
      height: 36px;
    }
    .sign-name {
      font-weight: bold;
      text-decoration: underline;
    }

    /* FOOTER */
    .slip-footer {
      margin-top: 10px;
      padding-top: 4px;
      border-top: 1px solid #000000;
      text-align: center;
      font-size: 6.8pt;
      color: #000000;
    }
  </style>
</head>
<body>
<?php
$total_slips = count($slips);
foreach ($slips as $idx => $s) :
  $page_class = ($idx < $total_slips - 1) ? 'slip-page page-break' : 'slip-page';
  $nama_jabatan = !empty($s['nama_jabatan_struktural'])
    ? $s['nama_jabatan_struktural']
    : ($s['is_wali_kelas'] === 'y' ? 'Wali Kelas' : 'Guru / Karyawan');
  $no_slip = 'SLIP/MIS/' . date('Ym', strtotime($s['tanggal_awal'] ?? date('Y-m-d'))) . '/' . sprintf('%03d', (int)$s['gaji_id']);
  $tgl_ttd_display = isset($tanggal_ttd) ? $tanggal_ttd : date('d/m/Y');
?>
  <div class="<?=$page_class;?>">

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

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title-wrap">
      <div class="doc-title">SLIP MUKAFAAH &amp; GAJI PEGAWAI</div>
      <div class="doc-subtitle">
        Periode: <strong><?=htmlspecialchars($s['nama_periode']);?></strong>
        <?php if (!empty($s['tanggal_awal']) && !empty($s['tanggal_akhir'])): ?>
          (Cut-Off: <?=date('d/m/Y', strtotime($s['tanggal_awal']));?> s/d <?=date('d/m/Y', strtotime($s['tanggal_akhir']));?>)
        <?php endif; ?>
      </div>
    </div>

    <!-- METADATA PEGAWAI -->
    <table class="meta-table">
      <tr>
        <td class="meta-lbl">Nama Pegawai</td>
        <td class="meta-sep">:</td>
        <td class="meta-val"><?=htmlspecialchars($s['nama_pegawai']);?></td>
        <td class="meta-lbl">No. Slip</td>
        <td class="meta-sep">:</td>
        <td class="meta-val"><?=$no_slip;?></td>
      </tr>
      <tr>
        <td class="meta-lbl">NIP / ID</td>
        <td class="meta-sep">:</td>
        <td class="meta-val"><?=htmlspecialchars((string)$s['id_pegawai']);?></td>
        <td class="meta-lbl">Jabatan</td>
        <td class="meta-sep">:</td>
        <td class="meta-val"><?=htmlspecialchars($nama_jabatan);?></td>
      </tr>
      <tr>
        <td class="meta-lbl">Status Kerja</td>
        <td class="meta-sep">:</td>
        <td class="meta-val"><?=strtoupper((string)$s['status_kepegawaian']);?><?=$s['status_kepegawaian'] === 'gty' ? ' (' . (int)$s['masa_kerja_tahun'] . ' Thn)' : '';?></td>
        <td class="meta-lbl">Tgl Dokumen</td>
        <td class="meta-sep">:</td>
        <td class="meta-val"><?=htmlspecialchars($tgl_ttd_display);?></td>
      </tr>
    </table>

    <!-- TABEL RINCIAN PENERIMAAN & POTONGAN -->
    <table class="cols-layout">
      <tr>
        <td class="col-left">
          <table class="detail-table">
            <thead>
              <tr>
                <th>I. Penerimaan / Hak</th>
                <th class="num-hdr">Jumlah (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Gaji Pokok</td>
                <td class="num"><?=number_format($s['gaji_pokok'], 0, ',', '.');?></td>
              </tr>
              <?php if ((float)$s['tunj_masa_kerja'] > 0): ?>
                <tr>
                  <td>Tunj. Masa Kerja (<?=(int)$s['masa_kerja_tahun'];?> th)</td>
                  <td class="num"><?=number_format($s['tunj_masa_kerja'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['tunj_wali_kelas'] > 0): ?>
                <tr>
                  <td>Tunj. Wali Kelas</td>
                  <td class="num"><?=number_format($s['tunj_wali_kelas'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['tunj_struktural'] > 0): ?>
                <tr>
                  <td>Tunj. Struktural / Tugas</td>
                  <td class="num"><?=number_format($s['tunj_struktural'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <tr>
                <td>Uang Makan (<?=(int)$s['hari_hadir_presensi'];?> hari hadir)</td>
                <td class="num"><?=number_format($s['tunj_makan'], 0, ',', '.');?></td>
              </tr>
              <?php if ((float)$s['bonus_presensi_tier1'] > 0): ?>
                <tr>
                  <td>Bonus Presensi Tier 1 (&le;06.48)</td>
                  <td class="num"><?=number_format($s['bonus_presensi_tier1'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['bonus_presensi_tier2'] > 0): ?>
                <tr>
                  <td>Bonus Presensi Tier 2 (&le;06.30)</td>
                  <td class="num"><?=number_format($s['bonus_presensi_tier2'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['honor_ekstra_lunas'] > 0): ?>
                <tr>
                  <td>Honor Ekstrakurikuler</td>
                  <td class="num"><?=number_format($s['honor_ekstra_lunas'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['talangan_operasional'] > 0): ?>
                <tr>
                  <td>Talangan Operasional</td>
                  <td class="num"><?=number_format($s['talangan_operasional'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['tunj_transport'] > 0): ?>
                <tr>
                  <td>Tunjangan Transport</td>
                  <td class="num"><?=number_format($s['tunj_transport'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['tunj_kesetaraan'] > 0): ?>
                <tr>
                  <td>Tunj. Kesetaraan (ANBK/TKA)</td>
                  <td class="num"><?=number_format($s['tunj_kesetaraan'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['apresiasi_sertifikasi'] > 0): ?>
                <tr>
                  <td>Apresiasi / Tunj. Khusus</td>
                  <td class="num"><?=number_format($s['apresiasi_sertifikasi'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <tr class="total-row">
                <td>Total Penerimaan Kotor</td>
                <td class="num"><?=number_format($s['total_penghasilan_kotor'], 0, ',', '.');?></td>
              </tr>
            </tbody>
          </table>
        </td>

        <td class="col-gap"></td>

        <td class="col-right">
          <table class="detail-table">
            <thead>
              <tr>
                <th>II. Potongan / Kewajiban</th>
                <th class="num-hdr">Jumlah (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <?php if ((float)$s['potongan_kasbon'] > 0): ?>
                <tr>
                  <td>Potongan Kasbon</td>
                  <td class="num"><?=number_format($s['potongan_kasbon'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['potongan_talangan'] > 0): ?>
                <tr>
                  <td>Potongan Talangan</td>
                  <td class="num"><?=number_format($s['potongan_talangan'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['potongan_lainnya'] > 0): ?>
                <tr>
                  <td>Potongan Lain-lain</td>
                  <td class="num"><?=number_format($s['potongan_lainnya'], 0, ',', '.');?></td>
                </tr>
              <?php endif; ?>
              <?php if ((float)$s['total_potongan'] == 0): ?>
                <tr>
                  <td colspan="2" class="empty-deduct">Tidak ada potongan</td>
                </tr>
              <?php endif; ?>
              <tr class="total-row">
                <td>Total Potongan</td>
                <td class="num"><?=number_format($s['total_potongan'], 0, ',', '.');?></td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </table>

    <!-- TAKE HOME PAY -->
    <table class="thp-table">
      <tr>
        <td>
          <div class="thp-title">Mukafaah Bersih Diterima (Take Home Pay)</div>
          <div class="thp-terbilang">Terbilang: # <?=htmlspecialchars($s['terbilang']);?> #</div>
        </td>
        <td class="thp-amount">Rp <?=number_format($s['take_home_pay'], 0, ',', '.');?>,-</td>
      </tr>
    </table>

    <!-- METODE TRANSFER -->
    <table class="bank-table">
      <tr>
        <td>
          <strong>Metode Pencairan:</strong>
          <?php if (!empty($s['nomor_rekening'])): ?>
            Transfer Bank <strong><?=htmlspecialchars($s['nama_bank'] ? $s['nama_bank'] : 'BRI');?></strong>
            &mdash; No. Rek: <strong><?=htmlspecialchars($s['nomor_rekening']);?></strong>
            (a.n. <?=htmlspecialchars(!empty($s['nama_pemilik_rekening']) ? $s['nama_pemilik_rekening'] : $s['nama_pegawai']);?>)
          <?php else: ?>
            Pembayaran Tunai / Rekening Belum Terdaftar
          <?php endif; ?>
        </td>
        <?php if (!empty($s['customer_reff'])): ?>
          <td style="text-align: right; white-space: nowrap;">
            Ref: <strong><?=htmlspecialchars($s['customer_reff']);?></strong>
          </td>
        <?php endif; ?>
      </tr>
    </table>

    <!-- TANDA TANGAN -->
    <table class="sign-table">
      <tr>
        <td>
          <div>Penerima Mukafaah,</div>
          <div class="sign-space"></div>
          <div class="sign-name"><?=htmlspecialchars($s['nama_pegawai']);?></div>
        </td>
        <td>
          <div>Klaten, <?=htmlspecialchars($tgl_ttd_display);?></div>
          <div>Bendahara Yayasan,</div>
          <div class="sign-space"></div>
          <div class="sign-name">Dewi Fatmala Sari, S.Pd</div>
        </td>
      </tr>
    </table>

    <!-- FOOTER RESMI -->
    <div class="slip-footer">
      Dokumen resmi Yayasan Mentari Mandiri Sejahtera &mdash; SD Mentari Islamic School (SDMIS). Dicetak dari Sistem Penggajian Terintegrasi.
    </div>

  </div>
<?php endforeach; ?>
</body>
</html>
