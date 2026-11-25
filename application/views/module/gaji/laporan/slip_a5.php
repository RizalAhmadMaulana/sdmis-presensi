<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Slip Gaji - SD Mentari Islamic School</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background-color: #f3f4f6;
      color: #1e293b;
      font-size: 11px;
      line-height: 1.4;
    }
    .no-print-bar {
      background: #0f172a;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      border: none;
      text-decoration: none;
    }
    .btn-primary { background: #2563eb; color: #fff; }
    .btn-primary:hover { background: #1d4ed8; }
    .btn-secondary { background: #475569; color: #fff; }
    .btn-secondary:hover { background: #334155; }

    /* A5 Page Container */
    .slip-container {
      max-width: 148mm;
      margin: 20px auto;
    }
    .slip-wrapper {
      background: #ffffff;
      width: 148mm;
      min-height: 210mm;
      padding: 12mm 14mm;
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
      margin-bottom: 24px;
      position: relative;
    }

    /* KOP SURAT */
    .kop-header {
      display: flex;
      align-items: center;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 8px;
      margin-bottom: 10px;
    }
    .kop-logo {
      width: 52px;
      height: 52px;
      margin-right: 12px;
      object-fit: contain;
    }
    .kop-text {
      flex: 1;
      text-align: center;
    }
    .kop-text .yayasan {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.5px;
      color: #334155;
      text-transform: uppercase;
    }
    .kop-text .sekolah {
      font-size: 15px;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: 0.5px;
      margin: 1px 0;
    }
    .kop-text .alamat {
      font-size: 9px;
      color: #64748b;
    }

    /* TITLE */
    .slip-title {
      text-align: center;
      margin: 10px 0;
    }
    .slip-title h2 {
      font-size: 13px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #1e3a8a;
      margin-bottom: 2px;
    }
    .slip-title .periode {
      font-size: 10px;
      font-weight: 600;
      color: #475569;
    }

    /* METADATA */
    .meta-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 8px 12px;
      margin-bottom: 12px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
      font-size: 10px;
    }
    .meta-item {
      display: flex;
    }
    .meta-label {
      width: 90px;
      color: #64748b;
      font-weight: 600;
    }
    .meta-value {
      flex: 1;
      color: #0f172a;
      font-weight: 700;
    }

    /* TABLE RINCIAN */
    .table-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 12px;
    }
    .col-table {
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      overflow: hidden;
    }
    .col-header {
      background: #f1f5f9;
      padding: 6px 10px;
      font-weight: 800;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 1px solid #cbd5e1;
    }
    .col-header.plus { color: #15803d; border-top: 3px solid #16a34a; }
    .col-header.minus { color: #b91c1c; border-top: 3px solid #dc2626; }
    .table-list {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.5px;
    }
    .table-list td {
      padding: 4px 8px;
      border-bottom: 1px solid #f1f5f9;
    }
    .table-list tr:last-child td {
      border-bottom: none;
    }
    .table-list .val {
      text-align: right;
      font-family: monospace;
      font-weight: 700;
      white-space: nowrap;
    }
    .subtotal-row {
      background: #f8fafc;
      font-weight: 800;
      border-top: 1px solid #cbd5e1 !important;
    }

    /* TAKE HOME PAY BOX */
    .thp-box {
      background: #f0fdf4;
      border: 1.5px solid #86efac;
      border-radius: 6px;
      padding: 10px 14px;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .thp-label {
      font-size: 11px;
      font-weight: 800;
      color: #166534;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .thp-terbilang {
      font-size: 9px;
      font-style: italic;
      color: #15803d;
      margin-top: 2px;
      font-weight: 600;
    }
    .thp-amount {
      font-size: 16px;
      font-weight: 900;
      color: #15803d;
      font-family: monospace;
    }

    /* REKENING INFO */
    .bank-info {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 6px;
      padding: 6px 10px;
      margin-bottom: 16px;
      font-size: 9px;
      color: #1e40af;
      display: flex;
      justify-content: space-between;
    }

    /* SIGNATURES */
    .signature-section {
      display: flex;
      justify-content: space-between;
      margin-top: 18px;
      padding-top: 10px;
    }
    .sign-box {
      width: 45%;
      text-align: center;
      font-size: 9.5px;
    }
    .sign-role {
      font-weight: 600;
      color: #475569;
      margin-bottom: 40px;
    }
    .sign-name {
      font-weight: 800;
      color: #0f172a;
      border-bottom: 1px solid #94a3b8;
      padding-bottom: 2px;
      display: inline-block;
      min-width: 140px;
    }

    /* FOOTER */
    .slip-footer {
      position: absolute;
      bottom: 8mm;
      left: 14mm;
      right: 14mm;
      text-align: center;
      font-size: 8px;
      color: #94a3b8;
      border-top: 1px dashed #cbd5e1;
      padding-top: 4px;
    }

    /* PRINT RULES */
    @media print {
      body {
        background: #ffffff;
      }
      .no-print-bar {
        display: none !important;
      }
      .slip-container {
        max-width: 100%;
        margin: 0;
      }
      .slip-wrapper {
        box-shadow: none;
        margin: 0;
        width: 100%;
        min-height: 205mm;
        page-break-after: always;
      }
      .slip-wrapper:last-child {
        page-break-after: auto;
      }
      @page {
        size: A5 portrait;
        margin: 6mm;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Print Action Bar -->
  <div class="no-print-bar">
    <div>
      <strong>Slip Mukafaah SD Mentari Islamic School</strong> &bull;
      <span><?=count($slips);?> Lembar Slip Siap Cetak (Format A5)</span>
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="javascript:history.back()" class="btn btn-secondary">
        &larr; Kembali
      </a>
      <button onclick="window.print()" class="btn btn-primary">
        &#128438; Cetak Sekarang (A5)
      </button>
    </div>
  </div>

  <div class="slip-container">
    <?php foreach ($slips as $s) : ?>
      <div class="slip-wrapper">
        
        <!-- KOP SURAT -->
        <div class="kop-header">
          <img src="<?=base_url('assets/temp/assets/logo/client/favicon.png');?>" class="kop-logo" onerror="this.style.display='none';">
          <div class="kop-text">
            <div class="yayasan">Yayasan Mentari Mandiri Sejahtera</div>
            <div class="sekolah">SD MENTARI ISLAMIC SCHOOL (SDMIS)</div>
            <div class="alamat">Jl. Ngaran, Polanharjo, Kabupaten Klaten, Jawa Tengah &bull; Telp/WA: 0822-4361-9388</div>
          </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="slip-title">
          <h2>Slip Mukafaah & Gaji Pegawai</h2>
          <div class="periode">
            Periode: <b><?=htmlspecialchars($s['nama_periode']);?></b> 
            <?php if (!empty($s['tanggal_awal']) && !empty($s['tanggal_akhir'])) { ?>
              (Cut-Off: <?=date('d/m/Y', strtotime($s['tanggal_awal']));?> - <?=date('d/m/Y', strtotime($s['tanggal_akhir']));?>)
            <?php } ?>
          </div>
        </div>

        <!-- METADATA PEGAWAI -->
        <div class="meta-box">
          <div>
            <div class="meta-item">
              <span class="meta-label">Nama Pegawai</span>
              <span class="meta-value">: <?=htmlspecialchars($s['nama_pegawai']);?></span>
            </div>
            <div class="meta-item">
              <span class="meta-label">NIP / ID</span>
              <span class="meta-value">: <?=$s['id_pegawai'];?></span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Status Kerja</span>
              <span class="meta-value">: <?=strtoupper($s['status_kepegawaian']);?> <?=$s['status_kepegawaian'] === 'gty' ? '('.$s['masa_kerja_tahun'].' Thn)' : '';?></span>
            </div>
          </div>
          <div>
            <div class="meta-item">
              <span class="meta-label">No. Slip</span>
              <span class="meta-value">: SLIP/MIS/<?=date('Ym', strtotime($s['tanggal_awal'] ?? date('Y-m-d')));?>/<?=sprintf('%03d', $s['gaji_id']);?></span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Tugas / Jabatan</span>
              <span class="meta-value">: <?=htmlspecialchars(!empty($s['nama_jabatan_struktural']) ? $s['nama_jabatan_struktural'] : ($s['is_wali_kelas'] === 'y' ? 'Wali Kelas' : 'Guru / Karyawan'));?></span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Tgl Cetak</span>
              <span class="meta-value">: <?=date('d/m/Y H:i');?> WIB</span>
            </div>
          </div>
        </div>

        <!-- TABEL RINCIAN 2-KOLOM (PENERIMAAN VS POTONGAN) -->
        <div class="table-section">
          <!-- KOLOM PENERIMAAN -->
          <div class="col-table">
            <div class="col-header plus">Penerimaan / Hak (Rp)</div>
            <table class="table-list">
              <tbody>
                <tr>
                  <td>Gaji Pokok</td>
                  <td class="val"><?=number_format($s['gaji_pokok'], 0, ',', '.');?></td>
                </tr>
                <?php if ($s['tunj_masa_kerja'] > 0) { ?>
                  <tr>
                    <td>Tunj. Masa Kerja (<?=$s['masa_kerja_tahun'];?> th)</td>
                    <td class="val"><?=number_format($s['tunj_masa_kerja'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['tunj_wali_kelas'] > 0) { ?>
                  <tr>
                    <td>Tunj. Wali Kelas</td>
                    <td class="val"><?=number_format($s['tunj_wali_kelas'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['tunj_struktural'] > 0) { ?>
                  <tr>
                    <td>Tunj. Struktural / Tugas</td>
                    <td class="val"><?=number_format($s['tunj_struktural'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td>Uang Makan (Hadir <?=$s['hari_hadir_presensi'];?> hr)</td>
                  <td class="val"><?=number_format($s['tunj_makan'], 0, ',', '.');?></td>
                </tr>
                <?php if ($s['bonus_presensi_tier1'] > 0) { ?>
                  <tr>
                    <td>Bonus Presensi Tier 1 (&le;06.48)</td>
                    <td class="val"><?=number_format($s['bonus_presensi_tier1'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['bonus_presensi_tier2'] > 0) { ?>
                  <tr>
                    <td>Bonus Presensi Tier 2 (&le;06.30)</td>
                    <td class="val"><?=number_format($s['bonus_presensi_tier2'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['honor_ekstra_lunas'] > 0) { ?>
                  <tr>
                    <td>Honor Ekstrakurikuler (Lunas)</td>
                    <td class="val"><?=number_format($s['honor_ekstra_lunas'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['talangan_operasional'] > 0) { ?>
                  <tr>
                    <td>Talangan Operasional Sekolah</td>
                    <td class="val"><?=number_format($s['talangan_operasional'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['tunj_transport'] > 0) { ?>
                  <tr>
                    <td>Tunjangan Transport</td>
                    <td class="val"><?=number_format($s['tunj_transport'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['tunj_kesetaraan'] > 0) { ?>
                  <tr>
                    <td>Tunj. Kesetaraan (ANBK/TKA)</td>
                    <td class="val"><?=number_format($s['tunj_kesetaraan'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['apresiasi_sertifikasi'] > 0) { ?>
                  <tr>
                    <td>Apresiasi Karyawan Lama</td>
                    <td class="val"><?=number_format($s['apresiasi_sertifikasi'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <tr class="subtotal-row">
                  <td><b>Total Penerimaan Kotor</b></td>
                  <td class="val"><b><?=number_format($s['total_penghasilan_kotor'], 0, ',', '.');?></b></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- KOLOM POTONGAN -->
          <div class="col-table">
            <div class="col-header minus">Potongan / Kewajiban (Rp)</div>
            <table class="table-list">
              <tbody>
                <?php if ($s['potongan_kasbon'] > 0) { ?>
                  <tr>
                    <td>Potongan Kasbon Yayasan</td>
                    <td class="val"><?=number_format($s['potongan_kasbon'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['potongan_talangan'] > 0) { ?>
                  <tr>
                    <td>Potongan Talangan / Pinjaman</td>
                    <td class="val"><?=number_format($s['potongan_talangan'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['potongan_lainnya'] > 0) { ?>
                  <tr>
                    <td>Potongan Lain-lain</td>
                    <td class="val"><?=number_format($s['potongan_lainnya'], 0, ',', '.');?></td>
                  </tr>
                <?php } ?>
                <?php if ($s['total_potongan'] == 0) { ?>
                  <tr>
                    <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px 0;">Tidak ada potongan</td>
                  </tr>
                <?php } ?>
                <tr class="subtotal-row" style="margin-top: auto;">
                  <td><b>Total Potongan</b></td>
                  <td class="val text-danger"><b>-<?=number_format($s['total_potongan'], 0, ',', '.');?></b></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- BOX TAKE HOME PAY BERSIH -->
        <div class="thp-box">
          <div>
            <div class="thp-label">Mukafaah Bersih Diterima (THP)</div>
            <div class="thp-terbilang"># <?=htmlspecialchars($s['terbilang']);?> #</div>
          </div>
          <div class="thp-amount">
            Rp <?=number_format($s['take_home_pay'], 0, ',', '.');?>,-
          </div>
        </div>

        <!-- REKENING BANK PENCAIRAN -->
        <div class="bank-info">
          <div>
            <b>Metode Pencairan:</b> Transfer Payroll Bank 
            <b><?=$s['nama_bank'] ? $s['nama_bank'] : 'BRI';?></b> - Rek: <b><?=$s['nomor_rekening'] ? $s['nomor_rekening'] : '-';?></b>
            (a.n <?=htmlspecialchars(!empty($s['nama_pemilik_rekening']) ? $s['nama_pemilik_rekening'] : $s['nama_pegawai']);?>)
          </div>
          <?php if (!empty($s['customer_reff'])) { ?>
            <div>Reff: <b><?=$s['customer_reff'];?></b></div>
          <?php } ?>
        </div>

        <!-- TANDA TANGAN -->
        <div class="signature-section">
          <div class="sign-box">
            <div class="sign-role">Penerima Mukafaah,</div>
            <div class="sign-name"><?=htmlspecialchars($s['nama_pegawai']);?></div>
          </div>
          <div class="sign-box">
            <div class="sign-role">Klaten, <?=date('d F Y');?><br>Bendahara Yayasan,</div>
            <div class="sign-name">Dewi Fatmala Sari, S.Pd</div>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="slip-footer">
          Dokumen ini diterbitkan secara sah oleh Sistem Penggajian SD Mentari Islamic School (SDMIS) &bull; Harap simpan slip ini sebagai bukti resmi.
        </div>

      </div>
    <?php endforeach; ?>
  </div>

</body>
</html>
