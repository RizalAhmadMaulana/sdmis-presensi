<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekapitulasi Mukafaah - <?=htmlspecialchars($periode['nama_periode']);?></title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background: #f8fafc;
      color: #0f172a;
      font-size: 10px;
    }
    .no-print-bar {
      background: #0f172a;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
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
    .btn-secondary { background: #475569; color: #fff; }

    .sheet {
      background: #fff;
      max-width: 297mm;
      min-height: 210mm;
      margin: 20px auto;
      padding: 12mm 15mm;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    /* KOP SURAT */
    .kop {
      display: flex;
      align-items: center;
      border-bottom: 2.5px solid #0f172a;
      padding-bottom: 8px;
      margin-bottom: 12px;
    }
    .kop-logo {
      width: 55px;
      height: 55px;
      margin-right: 15px;
      object-fit: contain;
    }
    .kop-title {
      flex: 1;
      text-align: center;
    }
    .kop-title h3 {
      font-size: 12px;
      font-weight: 700;
      color: #334155;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    .kop-title h1 {
      font-size: 18px;
      font-weight: 900;
      color: #0f172a;
      letter-spacing: 0.5px;
      margin: 2px 0;
    }
    .kop-title p {
      font-size: 10px;
      color: #64748b;
    }

    /* TITLE */
    .doc-title {
      text-align: center;
      margin-bottom: 12px;
    }
    .doc-title h2 {
      font-size: 14px;
      font-weight: 800;
      text-transform: uppercase;
      color: #1e3a8a;
      letter-spacing: 0.5px;
    }
    .doc-title .sub {
      font-size: 11px;
      color: #475569;
      font-weight: 600;
    }

    /* TABLE */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #cbd5e1;
      padding: 5px 6px;
    }
    table.data-table th {
      background: #f1f5f9;
      font-weight: 800;
      text-transform: uppercase;
      text-align: center;
      color: #1e293b;
    }
    table.data-table td.num {
      text-align: right;
      font-family: monospace;
      font-weight: 600;
    }
    table.data-table tr.total-row td {
      background: #f0fdf4;
      font-weight: 900;
      border-top: 2px solid #0f172a;
      font-size: 10px;
    }

    /* TERBILANG & SUMMARY */
    .summary-box {
      margin-top: 10px;
      padding: 8px 12px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      font-size: 10px;
    }

    /* SIGNATURE */
    .signs {
      display: flex;
      justify-content: space-between;
      margin-top: 25px;
      padding: 0 40px;
    }
    .sign-col {
      text-align: center;
      width: 200px;
      font-size: 10px;
    }
    .sign-col .role {
      font-weight: 600;
      color: #475569;
      margin-bottom: 50px;
    }
    .sign-col .name {
      font-weight: 800;
      color: #0f172a;
      border-bottom: 1px solid #0f172a;
      padding-bottom: 2px;
    }

    @media print {
      body { background: #fff; }
      .no-print-bar { display: none !important; }
      .sheet {
        margin: 0;
        padding: 0;
        box-shadow: none;
        max-width: 100%;
      }
      @page {
        size: A4 landscape;
        margin: 8mm;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Print Bar -->
  <div class="no-print-bar">
    <div>
      <strong>Rekapitulasi Mukafaah Yayasan</strong> &bull;
      <span>Periode: <?=htmlspecialchars($periode['nama_periode']);?></span>
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="javascript:history.back()" class="btn btn-secondary">&larr; Kembali</a>
      <button onclick="window.print()" class="btn btn-primary">&#128438; Cetak Dokumen (Landscape)</button>
    </div>
  </div>

  <div class="sheet">
    
    <!-- KOP -->
    <div class="kop">
      <img src="<?=base_url('assets/temp/assets/logo/client/favicon.png');?>" class="kop-logo" onerror="this.style.display='none';">
      <div class="kop-title">
        <h3>Yayasan Mentari Mandiri Sejahtera</h3>
        <h1>SD MENTARI ISLAMIC SCHOOL (SDMIS)</h1>
        <p>Jl. Ngaran, Polanharjo, Kabupaten Klaten, Jawa Tengah &bull; Telp/WA: 0822-4361-9388</p>
      </div>
    </div>

    <!-- TITLE -->
    <div class="doc-title">
      <h2>REKAPITULASI PEMBUKUAN MUKAFAAH GURU & KARYAWAN</h2>
      <div class="sub">
        Periode: <b><?=htmlspecialchars($periode['nama_periode']);?></b> &bull;
        Rentang Cut-Off: <b><?=date('d/m/Y', strtotime($periode['tanggal_awal']));?> s/d <?=date('d/m/Y', strtotime($periode['tanggal_akhir']));?></b> &bull;
        Hari Kerja Aktif: <b><?=$periode['total_hari_kerja_aktif'];?> Hari</b>
      </div>
    </div>

    <!-- TABEL DATA -->
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 25px;">No</th>
          <th>Nama Pegawai</th>
          <th>Status</th>
          <th>Gaji Pokok</th>
          <th>Tunj. TMK</th>
          <th>Wali / Str</th>
          <th>Uang Makan</th>
          <th>Bonus Presensi</th>
          <th>Honor Ekstra</th>
          <th>Talangan/Adj</th>
          <th>Total Kotor</th>
          <th style="color: #b91c1c;">Potongan</th>
          <th style="background: #e2e8f0;">Take Home Pay</th>
          <th>Rekening Bank</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach ($recon['rincian'] as $r) : ?>
          <tr>
            <td style="text-align: center;"><?=$no++;?></td>
            <td>
              <b><?=htmlspecialchars($r['nama_pegawai']);?></b>
              <div style="color: #64748b; font-size: 8px;">NIP: <?=$r['id_pegawai'];?></div>
            </td>
            <td style="text-align: center; text-transform: uppercase; font-size: 8px;">
              <?=$r['status_kepegawaian'];?>
            </td>
            <td class="num"><?=number_format($r['gaji_pokok'], 0, ',', '.');?></td>
            <td class="num"><?=number_format($r['tunj_masa_kerja'], 0, ',', '.');?></td>
            <td class="num"><?=number_format($r['tunj_wali_kelas'] + $r['tunj_struktural'], 0, ',', '.');?></td>
            <td class="num"><?=number_format($r['tunj_makan'], 0, ',', '.');?></td>
            <td class="num" style="color: #15803d;"><?=number_format($r['total_tunj_presensi'], 0, ',', '.');?></td>
            <td class="num"><?=number_format($r['honor_ekstra_lunas'], 0, ',', '.');?></td>
            <td class="num"><?=number_format($r['talangan_operasional'] + $r['tunj_transport'] + $r['tunj_kesetaraan'] + $r['apresiasi_sertifikasi'], 0, ',', '.');?></td>
            <td class="num"><?=number_format($r['total_penghasilan_kotor'], 0, ',', '.');?></td>
            <td class="num" style="color: #b91c1c;">-<?=number_format($r['total_potongan'], 0, ',', '.');?></td>
            <td class="num" style="background: #f0fdf4; font-weight: 800; color: #166534;">
              <?=number_format($r['take_home_pay'], 0, ',', '.');?>
            </td>
            <td>
              <?php if (!empty($r['nomor_rekening'])) { ?>
                <b><?=$r['nama_bank'];?></b>: <?=$r['nomor_rekening'];?>
              <?php } else { ?>
                <span style="color: #94a3b8;">Tunai</span>
              <?php } ?>
            </td>
          </tr>
        <?php endforeach; ?>

        <!-- BARIS GRAND TOTAL -->
        <tr class="total-row">
          <td colspan="3" style="text-align: center;">GRAND TOTAL YAYASAN</td>
          <td class="num"><?=number_format($recon['totals']['gaji_pokok'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($recon['totals']['tunj_masa_kerja'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($recon['totals']['tunj_wali_kelas'] + $recon['totals']['tunj_struktural'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($recon['totals']['tunj_makan'], 0, ',', '.');?></td>
          <td class="num" style="color: #15803d;"><?=number_format($recon['totals']['total_tunj_presensi'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($recon['totals']['honor_ekstra_lunas'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($recon['totals']['talangan_operasional'] + $recon['totals']['tunj_transport'] + $recon['totals']['tunj_kesetaraan'] + $recon['totals']['apresiasi_sertifikasi'], 0, ',', '.');?></td>
          <td class="num"><?=number_format($recon['totals']['total_penghasilan_kotor'], 0, ',', '.');?></td>
          <td class="num" style="color: #b91c1c;">-<?=number_format($recon['totals']['total_potongan'], 0, ',', '.');?></td>
          <td class="num" style="background: #dcfce7; color: #14532d; font-size: 11px;">
            Rp <?=number_format($recon['totals']['take_home_pay'], 0, ',', '.');?>
          </td>
          <td></td>
        </tr>
      </tbody>
    </table>

    <!-- TERBILANG -->
    <div class="summary-box">
      <b>Terbilang Total Disbursement:</b> 
      <i># <?=htmlspecialchars($terbilang_total);?> #</i>
    </div>

    <!-- TANDA TANGAN -->
    <div class="signs">
      <div class="sign-col">
        <div class="role">Mengetahui,<br>Kepala SD Mentari Islamic School</div>
        <div class="name">Guruh Suasana Eddy, S.Pd</div>
      </div>
      <div class="sign-col">
        <div class="role">Klaten, <?=date('d F Y');?><br>Bendahara Yayasan,</div>
        <div class="name">Dewi Fatmala Sari, S.Pd</div>
      </div>
    </div>

  </div>

</body>
</html>
