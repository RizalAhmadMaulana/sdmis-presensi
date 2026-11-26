<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Export Mutasi Bank
      </h4>
      <p class="text-muted mb-0 small">
        Pusat unduh berkas mutasi payroll perbankan resmi format Excel (.xlsx) rapi sesuai template Payroll November (dengan border tabel penuh &amp; format nomor rekening teks).
      </p>
    </div>

    <!-- Dropdown Pemilihan Periode -->
    <div class="d-flex align-items-center gap-2">
      <label class="form-label mb-0 small fw-bold text-nowrap"><i class="ti ti-calendar-event me-1"></i> Pilih Periode:</label>
      <select class="form-select form-select-sm shadow-sm" style="min-width: 220px;" onchange="if(this.value) window.location.href='<?=base_url('gaji/export_bank/index/');?>' + this.value;">
        <?php if (empty($periodes)) { ?>
          <option value="">-- Belum ada periode --</option>
        <?php } else { ?>
          <?php foreach ($periodes as $p) : ?>
            <option value="<?=$p['periode_id'];?>" <?=$selected_id == $p['periode_id'] ? 'selected' : '';?>>
              <?=htmlspecialchars($p['nama_periode']);?> (<?=$p['status_periode'];?>)
            </option>
          <?php endforeach; ?>
        <?php } ?>
      </select>
    </div>
  </div>

  <!-- Flash Toast & Modal Konfirmasi -->
  <?php $this->load->view('module/gaji/toast_and_modal'); ?>

  <?php if (!$recon || empty($recon['rincian'])) { ?>
    <!-- KONDISI KOSONG -->
    <div class="card shadow-sm border-0 text-center py-5">
      <div class="card-body">
        <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 p-3 rounded-circle">
          <i class="ti ti-building-bank fs-1 text-secondary"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">Data Payroll Belum Tersedia untuk Diexport</h5>
        <p class="text-muted small mb-4">
          Silakan buat dan lakukan perhitungan presensi pada menu Periode & Hitung Gaji terlebih dahulu.
        </p>
        <a href="<?=base_url('gaji/periode');?>" class="btn btn-primary px-4">
          <i class="ti ti-calendar me-1"></i> Buka Menu Periode
        </a>
      </div>
    </div>
  <?php } else { ?>

    <!-- PUSAT DOWNLOAD MUTASI PERBANKAN (3 ACTION CARDS) -->
    <div class="row g-3 mb-4">
      
      <!-- CARD 1: DOWNLOAD CMS BANK BRI -->
      <div class="col-lg-4 col-md-6 col-12">
        <div class="card h-100 border-top border-primary border-4 shadow-sm hover-elevate">
          <div class="card-header pb-2 d-flex justify-content-between align-items-center">
            <span class="badge bg-primary px-3 py-1 fs-6">
              <i class="ti ti-building-bank me-1"></i> Bank BRI CMS
            </span>
            <span class="badge bg-label-primary font-monospace">Excel .XLSX</span>
          </div>
          <div class="card-body pt-2 d-flex flex-column">
            <h5 class="fw-bold text-dark mb-1">File Mutasi BRI Corporate</h5>
            <p class="small text-muted mb-3">
              Format persis template Payroll November lengkap garis tabel penuh, format rekening Teks (anti-notasi ilmiah), serta kontrol COUNT, TOTAL, CHECK, VALUEDATE, dan DEBITACCOUNT.
            </p>
            <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom mb-3 small mt-auto">
              <span class="text-muted">Total Transfer BRI:</span>
              <span class="fw-bold text-primary fs-6">Rp <?=number_format($recon['bank_summary']['BRI']['total'], 0, ',', '.');?></span>
            </div>
            <a href="<?=base_url('gaji/export_bank/bri/'.$recon['periode']['periode_id']);?>" class="btn btn-primary w-100 shadow mb-2">
              <i class="ti ti-download me-1"></i> Unduh File CMS BRI (Excel)
            </a>
            <a href="<?=base_url('gaji/export_bank/bri/'.$recon['periode']['periode_id'].'?format=csv');?>" class="btn btn-sm btn-outline-secondary w-100">
              <i class="ti ti-file-text me-1"></i> Unduh CSV Mentah (.csv)
            </a>
          </div>
        </div>
      </div>

      <!-- CARD 2: DOWNLOAD CMS BANK BSI -->
      <div class="col-lg-4 col-md-6 col-12">
        <div class="card h-100 border-top border-info border-4 shadow-sm hover-elevate">
          <div class="card-header pb-2 d-flex justify-content-between align-items-center">
            <span class="badge bg-info px-3 py-1 fs-6">
              <i class="ti ti-building-bank me-1"></i> Bank BSI CMS
            </span>
            <span class="badge bg-label-info font-monospace">Excel .XLSX</span>
          </div>
          <div class="card-body pt-2 d-flex flex-column">
            <h5 class="fw-bold text-dark mb-1">File Mutasi BSI Corporate</h5>
            <p class="small text-muted mb-3">
              Format Excel rapi dengan border tabel lengkap untuk penerima rekening Bank Syariah Indonesia (BSI), nomor rekening utuh tanpa terpotong.
            </p>
            <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom mb-3 small mt-auto">
              <span class="text-muted">Total Transfer BSI:</span>
              <span class="fw-bold text-info fs-6">Rp <?=number_format($recon['bank_summary']['BSI']['total'], 0, ',', '.');?></span>
            </div>
            <a href="<?=base_url('gaji/export_bank/bsi/'.$recon['periode']['periode_id']);?>" class="btn btn-info w-100 shadow mb-2">
              <i class="ti ti-download me-1"></i> Unduh File CMS BSI (Excel)
            </a>
            <a href="<?=base_url('gaji/export_bank/bsi/'.$recon['periode']['periode_id'].'?format=csv');?>" class="btn btn-sm btn-outline-secondary w-100">
              <i class="ti ti-file-text me-1"></i> Unduh CSV Mentah (.csv)
            </a>
          </div>
        </div>
      </div>

      <!-- CARD 3: DOWNLOAD MASTER REKAP PAYROLL (EXCEL .XLSX) -->
      <div class="col-lg-4 col-md-12 col-12">
        <div class="card h-100 border-top border-success border-4 shadow-sm hover-elevate">
          <div class="card-header pb-2 d-flex justify-content-between align-items-center">
            <span class="badge bg-success px-3 py-1 fs-6">
              <i class="ti ti-file-spreadsheet me-1"></i> Master Excel
            </span>
            <span class="badge bg-label-success font-monospace">Arsip Yayasan</span>
          </div>
          <div class="card-body pt-2 d-flex flex-column">
            <h5 class="fw-bold text-dark mb-1">Master Spreadsheet Lengkap</h5>
            <p class="small text-muted mb-3">
              Workbook Excel ber-border rapi berisi 27 kolom komponen penggajian lengkap (Gaji Pokok, TMK, Makan, Tier 1/2, Ekstra, Talangan, Potongan, THP) &amp; Grand Total.
            </p>
            <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom mb-3 small mt-auto">
              <span class="text-muted">Grand Total Mukafaah:</span>
              <span class="fw-bold text-success fs-6">Rp <?=number_format($recon['totals']['take_home_pay'], 0, ',', '.');?></span>
            </div>
            <a href="<?=base_url('gaji/export_bank/master_csv/'.$recon['periode']['periode_id']);?>" class="btn btn-success w-100 shadow mb-2">
              <i class="ti ti-file-download me-1"></i> Unduh Master Rekap (Excel)
            </a>
            <a href="<?=base_url('gaji/export_bank/master_csv/'.$recon['periode']['periode_id'].'?format=csv');?>" class="btn btn-sm btn-outline-secondary w-100">
              <i class="ti ti-file-text me-1"></i> Unduh CSV Mentah (.csv)
            </a>
          </div>
        </div>
      </div>

    </div>

    <!-- TABEL PREVIEW DATA TRANSFER BANK PEGAWAI -->
    <div class="card shadow-sm">
      <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="ti ti-credit-card me-2 text-primary"></i> Preview Daftar Transfer Rekening Payroll
          </h5>
          <small class="text-muted">Data penerima transfer yang akan dimasukkan ke dalam file mutasi perbankan.</small>
        </div>
        <div class="d-flex gap-2">
          <span class="badge bg-label-primary">
            <i class="ti ti-users me-1"></i> Total: <?=$recon['totals']['jml_pegawai'];?> Pegawai
          </span>
          <span class="badge bg-label-success">
            <i class="ti ti-circle-check me-1"></i> Balance: Rp <?=number_format($recon['totals']['take_home_pay'], 0, ',', '.');?>
          </span>
        </div>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table table-hover border-top" id="dataTable">
          <thead class="table-light">
            <tr>
              <th style="width: 35px;">No</th>
              <th>Nama Pegawai</th>
              <th>Bank Penerima</th>
              <th>Nomor Rekening</th>
              <th>Nama Pemilik Rekening</th>
              <th class="text-end">Nominal Transfer (THP)</th>
              <th>Customer Reff</th>
              <th class="text-center" style="width: 80px;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach ($recon['rincian'] as $r) : ?>
              <tr>
                <td class="text-center fw-bold"><?=$no++;?></td>
                <td>
                  <span class="fw-bold text-dark d-block"><?=htmlspecialchars($r['nama_pegawai']);?></span>
                  <small class="text-muted">NIP: <?=$r['id_pegawai'];?></small>
                </td>
                <td>
                  <span class="badge <?=$r['nama_bank'] == 'BSI' ? 'bg-label-info' : 'bg-label-primary';?> px-2 py-1 fw-bold">
                    <?=$r['nama_bank'] ? $r['nama_bank'] : 'BRI';?>
                  </span>
                </td>
                <td class="font-monospace fw-bold text-dark">
                  <?=$r['nomor_rekening'] ? $r['nomor_rekening'] : '<span class="text-warning">Belum diisi</span>';?>
                </td>
                <td>
                  <span class="text-dark fw-semibold">
                    <?=htmlspecialchars(!empty($r['nama_pemilik_rekening']) ? $r['nama_pemilik_rekening'] : $r['nama_pegawai']);?>
                  </span>
                </td>
                <td class="text-end fw-bold text-success fs-6">
                  Rp <?=number_format($r['take_home_pay'], 0, ',', '.');?>
                </td>
                <td>
                  <?php if (!empty($r['customer_reff'])) { ?>
                    <span class="badge bg-label-secondary font-monospace"><?=$r['customer_reff'];?></span>
                  <?php } else { ?>
                    <span class="text-muted small">-</span>
                  <?php } ?>
                </td>
                <td class="text-center align-middle">
                  <?php if (!empty($r['nomor_rekening'])) { ?>
                    <span class="badge bg-label-success p-1 rounded-pill" title="Rekening Valid">
                      <i class="ti ti-check"></i> Siap
                    </span>
                  <?php } else { ?>
                    <span class="badge bg-label-warning p-1 rounded-pill" title="Belum Ada Rekening">
                      <i class="ti ti-alert-triangle"></i> Pending
                    </span>
                  <?php } ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php } ?>

</div>
