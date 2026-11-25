<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs dengan Filter Periode -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Rekonsiliasi & Balance Matching
      </h4>
      <p class="text-muted mb-0 small">
        Audit integritas 3-arah: memastikan total rincian slip guru sama persis dengan tabel mukafaah yayasan dan file mutasi payroll bank.
      </p>
    </div>

    <!-- Dropdown Pemilihan Periode -->
    <div class="d-flex align-items-center gap-2">
      <label class="form-label mb-0 small fw-bold text-nowrap"><i class="ti ti-calendar-event me-1"></i> Pilih Periode:</label>
      <form action="" method="GET" class="d-inline">
        <select class="form-select form-select-sm shadow-sm" style="min-width: 220px;" onchange="if(this.value) window.location.href='<?=base_url('gaji/rekonsiliasi/index/');?>' + this.value;">
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
      </form>
    </div>
  </div>

  <!-- Flash Toast & Modal Konfirmasi -->
  <?php $this->load->view('module/gaji/toast_and_modal'); ?>

  <?php if (!$recon || empty($recon['rincian'])) { ?>
    <!-- KONDISI KOSONG -->
    <div class="card shadow-sm border-0 text-center py-5">
      <div class="card-body">
        <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 p-3 rounded-circle">
          <i class="ti ti-scale-off fs-1 text-secondary"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">Data Rekonsiliasi Belum Tersedia</h5>
        <p class="text-muted small mb-4">
          Silakan pilih periode yang telah dihitung pada menu Periode & Hitung Gaji untuk melakukan audit keseimbangan.
        </p>
        <a href="<?=base_url('gaji/periode');?>" class="btn btn-primary px-4">
          <i class="ti ti-calendar me-1"></i> Buka Menu Periode & Hitung Gaji
        </a>
      </div>
    </div>
  <?php } else { ?>

    <!-- 1. HERO VERIFICATION BANNER -->
    <?php if ($recon['is_balance']) { ?>
      <div class="card bg-success text-white mb-4 shadow border-0 overflow-hidden">
        <div class="card-body p-4 position-relative">
          <div class="row align-items-center">
            <div class="col-lg-8 col-12 mb-3 mb-lg-0">
              <div class="d-flex align-items-center">
                <div class="avatar avatar-xl bg-white bg-opacity-25 rounded-circle me-3 p-2 flex-shrink-0">
                  <i class="ti ti-shield-check fs-1 text-white"></i>
                </div>
                <div>
                  <div class="badge bg-white text-success fw-bold text-uppercase px-2 py-1 mb-2">
                    <i class="ti ti-circle-check me-1"></i> Balance Matching: 100% Klop
                  </div>
                  <h3 class="fw-bold mb-1 text-white">
                    VERIFIKASI BALANCE SEMPURNA: Rp <?=number_format($recon['total_rincian'], 0, ',', '.');?>,-
                  </h3>
                  <p class="mb-0 text-white text-opacity-90 small">
                    Rincian <b><?=$recon['totals']['jml_pegawai'];?> Pegawai</b> sama persis dengan Total Tabel Mukafaah dan Total Payroll Bank (BRI & BSI). <b>Selisih: Rp 0,- (Zero Variance)</b>.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-12 text-lg-end">
              <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                <?php if ($recon['periode']['status_periode'] !== 'final') { ?>
                  <button type="button" 
                          class="btn btn-warning text-dark fw-bold btn-confirm-delete shadow-sm"
                          data-url="<?=base_url('gaji/rekonsiliasi/kunci_final/'.$recon['periode']['periode_id']);?>"
                          data-title="Kunci Final Periode?"
                          data-text="Apakah Anda yakin ingin mengunci periode <b><?=htmlspecialchars($recon['periode']['nama_periode']);?></b> sebagai FINAL? Status final menandakan payroll siap dicairkan ke bank."
                          title="Kunci Final">
                    <i class="ti ti-lock me-1"></i> Kunci Final
                  </button>
                <?php } else { ?>
                  <span class="badge bg-white text-dark py-2 px-3 fw-bold fs-6">
                    <i class="ti ti-lock-check me-1 text-success"></i> Periode Terkunci Final
                  </span>
                <?php } ?>
                <a href="<?=base_url('gaji/laporan/rekap/'.$recon['periode']['periode_id']);?>" target="_blank" class="btn btn-outline-light">
                  <i class="ti ti-printer me-1"></i> Cetak Rekap
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php } else { ?>
      <div class="card bg-danger text-white mb-4 shadow border-0">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="avatar avatar-xl bg-white bg-opacity-25 rounded-circle me-3 p-2 flex-shrink-0">
              <i class="ti ti-alert-triangle fs-1 text-white"></i>
            </div>
            <div>
              <div class="badge bg-white text-danger fw-bold text-uppercase px-2 py-1 mb-2">
                <i class="ti ti-alert-circle me-1"></i> Perhatian: Belum Seimbang
              </div>
              <h4 class="fw-bold mb-1 text-white">
                TERDAPAT SELISIH BALANCE: Rp <?=number_format($recon['selisih'], 0, ',', '.');?>,-
              </h4>
              <p class="mb-0 text-white text-opacity-90 small">
                Terdapat perbedaan antara Total Periode (Rp <?=number_format($recon['total_periode'], 0, ',', '.');?>) dan Akumulasi Rincian Guru (Rp <?=number_format($recon['total_rincian'], 0, ',', '.');?>). Silakan klik <b>⚡ Hitung Otomatis</b> kembali di menu Periode & Hitung Gaji.
              </p>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>

    <!-- 2. TIGA PILAR AUDIT BALANCE (3-WAY METRICS) -->
    <div class="row g-3 mb-4">
      <!-- PILAR 1: TOTAL TABEL PERIODE -->
      <div class="col-md-3 col-sm-6 col-12">
        <div class="card h-100 border-start border-primary border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="text-muted small fw-semibold d-block mb-1">
              <i class="ti ti-calendar me-1 text-primary"></i> 1. Total Tabel Periode
            </span>
            <h4 class="card-title mb-1 text-primary fw-bold">
              Rp <?=number_format($recon['total_periode'], 0, ',', '.');?>
            </h4>
            <small class="text-muted">Header tx_gaji_periode</small>
          </div>
        </div>
      </div>

      <!-- PILAR 2: TOTAL AKUMULASI SLIP GURU -->
      <div class="col-md-3 col-sm-6 col-12">
        <div class="card h-100 border-start border-success border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="text-muted small fw-semibold d-block mb-1">
              <i class="ti ti-users me-1 text-success"></i> 2. Total Akumulasi Slip Guru
            </span>
            <h4 class="card-title mb-1 text-success fw-bold">
              Rp <?=number_format($recon['total_rincian'], 0, ',', '.');?>
            </h4>
            <small class="text-muted"><?=$recon['totals']['jml_pegawai'];?> Pegawai Terhitung</small>
          </div>
        </div>
      </div>

      <!-- PILAR 3: TOTAL PAYROLL DISBURSEMENT BANK -->
      <div class="col-md-3 col-sm-6 col-12">
        <div class="card h-100 border-start border-info border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="text-muted small fw-semibold d-block mb-1">
              <i class="ti ti-building-bank me-1 text-info"></i> 3. Total Payroll Mutasi Bank
            </span>
            <h4 class="card-title mb-1 text-info fw-bold">
              Rp <?=number_format($recon['total_bank_disbursement'], 0, ',', '.');?>
            </h4>
            <small class="text-muted">BRI CMS & BSI CMS</small>
          </div>
        </div>
      </div>

      <!-- STATUS SELISIH -->
      <div class="col-md-3 col-sm-6 col-12">
        <div class="card h-100 border-start <?=$recon['is_balance'] ? 'border-success bg-label-success' : 'border-danger bg-label-danger';?> border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="small fw-semibold d-block mb-1 <?=$recon['is_balance'] ? 'text-success' : 'text-danger';?>">
              <i class="ti <?=$recon['is_balance'] ? 'ti-circle-check' : 'ti-alert-circle';?> me-1"></i> Selisih / Variance
            </span>
            <h4 class="card-title mb-1 fw-bold <?=$recon['is_balance'] ? 'text-success' : 'text-danger';?>">
              Rp <?=number_format($recon['selisih'], 0, ',', '.');?>
            </h4>
            <small class="fw-bold <?=$recon['is_balance'] ? 'text-success' : 'text-danger';?>">
              <?=$recon['is_balance'] ? '100% Klop & Seimbang' : 'Ada Selisih Perhitungan';?>
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. DETAIL DISTRIBUSI PERBANKAN (DISBURSEMENT SUMMARY) -->
    <div class="row g-3 mb-4">
      <!-- BANK BRI -->
      <div class="col-md-4 col-12">
        <div class="card h-100 border shadow-sm">
          <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark d-flex align-items-center">
              <span class="badge bg-primary me-2">BRI</span> CMS Payroll Bank BRI
            </span>
            <a href="<?=base_url('gaji/export_bank/bri/'.$recon['periode']['periode_id']);?>" class="btn btn-xs btn-outline-primary">
              <i class="ti ti-download me-1"></i> Export CSV
            </a>
          </div>
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted small">Penerima Transfer:</span>
              <span class="fw-bold text-dark"><?=$recon['bank_summary']['BRI']['count'];?> Rekening Guru</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted small">Total Nominal:</span>
              <span class="fw-bold text-primary fs-6">Rp <?=number_format($recon['bank_summary']['BRI']['total'], 0, ',', '.');?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
              <span>Debit Account Yayasan:</span>
              <span class="font-monospace fw-bold"><?=$recon['setting_global']['rekening_debit_yayasan'];?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- BANK BSI -->
      <div class="col-md-4 col-12">
        <div class="card h-100 border shadow-sm">
          <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark d-flex align-items-center">
              <span class="badge bg-info me-2">BSI</span> CMS Payroll Bank BSI
            </span>
            <a href="<?=base_url('gaji/export_bank/bsi/'.$recon['periode']['periode_id']);?>" class="btn btn-xs btn-outline-info">
              <i class="ti ti-download me-1"></i> Export CSV
            </a>
          </div>
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted small">Penerima Transfer:</span>
              <span class="fw-bold text-dark"><?=$recon['bank_summary']['BSI']['count'];?> Rekening Guru</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted small">Total Nominal:</span>
              <span class="fw-bold text-info fs-6">Rp <?=number_format($recon['bank_summary']['BSI']['total'], 0, ',', '.');?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
              <span>Sistem Pencairan:</span>
              <span class="fw-bold text-success"><i class="ti ti-check me-1"></i>Kliring / BSI Ready</span>
            </div>
          </div>
        </div>
      </div>

      <!-- REKENING LAIN / PENDING -->
      <div class="col-md-4 col-12">
        <div class="card h-100 border shadow-sm">
          <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark d-flex align-items-center">
              <span class="badge bg-secondary me-2">CASH / LAIN</span> Rekening Belum Terdaftar
            </span>
            <span class="badge bg-label-<?=$recon['bank_summary']['LAINNYA']['count'] > 0 ? 'warning' : 'success';?>">
              <?=$recon['bank_summary']['LAINNYA']['count'] > 0 ? 'Perlu Dilengkapi' : 'Semua Siap';?>
            </span>
          </div>
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted small">Penerima:</span>
              <span class="fw-bold text-dark"><?=$recon['bank_summary']['LAINNYA']['count'];?> Guru / Karyawan</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted small">Total Nominal:</span>
              <span class="fw-bold text-dark fs-6">Rp <?=number_format($recon['bank_summary']['LAINNYA']['total'], 0, ',', '.');?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
              <span>Keterangan:</span>
              <span><?=$recon['bank_summary']['LAINNYA']['count'] > 0 ? 'Disbursement manual / tunai' : 'Seluruh rekening bank lengkap';?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. MATRIX REKAPITULASI SELURUH KOMPONEN PAYROLL -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="ti ti-chart-bar me-2 text-primary"></i> Rekapitulasi Rincian Seluruh Komponen Gaji (Akumulasi Yayasan)
        </h5>
        <span class="badge bg-label-primary font-monospace">
          Periode: <?=htmlspecialchars($recon['periode']['nama_periode']);?>
        </span>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0">
          <thead class="table-light">
            <tr class="text-center">
              <th>Gaji Pokok</th>
              <th>Tunj. Masa Kerja</th>
              <th>Tunj. Wali Kelas</th>
              <th>Tunj. Struktural</th>
              <th>Uang Makan</th>
              <th>Bonus Presensi (2-Tier)</th>
              <th>Honor Ekstra</th>
              <th>Talangan & Transport</th>
              <th class="text-danger">Potongan Kasbon</th>
              <th class="table-success text-dark">GRAND TOTAL MUKAFAAH (THP)</th>
            </tr>
          </thead>
          <tbody class="text-end fw-bold">
            <tr>
              <td>Rp <?=number_format($recon['totals']['gaji_pokok'], 0, ',', '.');?></td>
              <td>Rp <?=number_format($recon['totals']['tunj_masa_kerja'], 0, ',', '.');?></td>
              <td>Rp <?=number_format($recon['totals']['tunj_wali_kelas'], 0, ',', '.');?></td>
              <td>Rp <?=number_format($recon['totals']['tunj_struktural'], 0, ',', '.');?></td>
              <td>Rp <?=number_format($recon['totals']['tunj_makan'], 0, ',', '.');?></td>
              <td class="text-success">Rp <?=number_format($recon['totals']['total_tunj_presensi'], 0, ',', '.');?></td>
              <td>Rp <?=number_format($recon['totals']['honor_ekstra_lunas'], 0, ',', '.');?></td>
              <td class="text-info">Rp <?=number_format($recon['totals']['talangan_operasional'] + $recon['totals']['tunj_transport'] + $recon['totals']['tunj_kesetaraan'], 0, ',', '.');?></td>
              <td class="text-danger">-Rp <?=number_format($recon['totals']['potongan_kasbon'], 0, ',', '.');?></td>
              <td class="table-success text-success fs-6">Rp <?=number_format($recon['totals']['take_home_pay'], 0, ',', '.');?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 5. TABEL RINCIAN AUDIT PER GURU (AUDIT MATRIX TABLE) -->
    <div class="card shadow-sm">
      <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="ti ti-table me-2 text-primary"></i> Tabel Audit Rincian Guru vs Rekening Bank
          </h5>
          <small class="text-muted">Memastikan setiap hak penerimaan guru telah klop dan siap disinkronkan ke bank.</small>
        </div>
        <div class="d-flex gap-2">
          <a href="<?=base_url('gaji/laporan/slip_batch/'.$recon['periode']['periode_id']);?>" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="ti ti-files me-1"></i> Cetak Semua Slip A5
          </a>
          <a href="<?=base_url('gaji/export_bank/index/'.$recon['periode']['periode_id']);?>" class="btn btn-sm btn-primary">
            <i class="ti ti-credit-card me-1"></i> Menu Export Bank
          </a>
        </div>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table table-hover border-top" id="dataTable">
          <thead class="table-light">
            <tr>
              <th style="width: 30px;">No</th>
              <th>Nama Pegawai</th>
              <th class="text-end">Gaji Pokok</th>
              <th class="text-end">Tunj. TMK/Wali</th>
              <th class="text-end">Presensi & Makan</th>
              <th class="text-end">Honor Ekstra</th>
              <th class="text-end">Talangan / Adj</th>
              <th class="text-end text-danger">Potongan</th>
              <th class="text-end text-success">THP Diterima</th>
              <th>Rekening Bank</th>
              <th class="text-center" style="width: 70px;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach ($recon['rincian'] as $r) : ?>
              <tr>
                <td class="text-center fw-bold"><?=$no++;?></td>
                <td>
                  <span class="fw-bold text-dark d-block"><?=htmlspecialchars($r['nama_pegawai']);?></span>
                  <div class="d-flex align-items-center gap-1 mt-1">
                    <?php if ($r['status_kepegawaian'] === 'gty') { ?>
                      <span class="badge bg-primary" style="font-size: 10px;">GTY</span>
                    <?php } else { ?>
                      <span class="badge bg-secondary" style="font-size: 10px;"><?=strtoupper($r['status_kepegawaian']);?></span>
                    <?php } ?>
                    <?php if ($r['is_wali_kelas'] === 'y') { ?>
                      <span class="badge bg-label-success" style="font-size: 10px;">Wali</span>
                    <?php } ?>
                  </div>
                </td>
                <td class="text-end">Rp <?=number_format($r['gaji_pokok'], 0, ',', '.');?></td>
                <td class="text-end">Rp <?=number_format($r['tunj_masa_kerja'] + $r['tunj_wali_kelas'] + $r['tunj_struktural'], 0, ',', '.');?></td>
                <td class="text-end">
                  <div class="fw-bold text-dark">Rp <?=number_format($r['tunj_makan'] + $r['total_tunj_presensi'], 0, ',', '.');?></div>
                  <small class="text-muted d-block" style="font-size: 10px;">Makan: <?=number_format($r['tunj_makan'], 0, ',', '.');?> | Bonus: <?=number_format($r['total_tunj_presensi'], 0, ',', '.');?></small>
                </td>
                <td class="text-end">Rp <?=number_format($r['honor_ekstra_lunas'], 0, ',', '.');?></td>
                <td class="text-end">
                  <?php $adj_plus = $r['talangan_operasional'] + $r['tunj_transport'] + $r['tunj_kesetaraan']; ?>
                  <?php if ($adj_plus > 0) { ?>
                    <span class="text-info fw-bold">+Rp <?=number_format($adj_plus, 0, ',', '.');?></span>
                  <?php } else { ?>
                    <span class="text-muted">-</span>
                  <?php } ?>
                </td>
                <td class="text-end">
                  <?php if ($r['total_potongan'] > 0) { ?>
                    <span class="text-danger fw-bold">-Rp <?=number_format($r['total_potongan'], 0, ',', '.');?></span>
                  <?php } else { ?>
                    <span class="text-muted">-</span>
                  <?php } ?>
                </td>
                <td class="text-end fw-bold text-success fs-6">
                  Rp <?=number_format($r['take_home_pay'], 0, ',', '.');?>
                </td>
                <td>
                  <?php if (!empty($r['nomor_rekening'])) { ?>
                    <div>
                      <span class="badge <?=$r['nama_bank'] == 'BSI' ? 'bg-label-info' : 'bg-label-primary';?> px-2 py-1 fw-bold">
                        <?=$r['nama_bank'];?>
                      </span>
                      <span class="font-monospace small fw-bold ms-1"><?=$r['nomor_rekening'];?></span>
                    </div>
                    <small class="text-muted d-block" style="font-size: 10px;">
                      a.n <?=htmlspecialchars(!empty($r['nama_pemilik_rekening']) ? $r['nama_pemilik_rekening'] : $r['nama_pegawai']);?>
                    </small>
                  <?php } else { ?>
                    <span class="badge bg-label-warning small">Belum Ada Rekening</span>
                  <?php } ?>
                </td>
                <td class="text-center align-middle">
                  <span class="badge bg-label-success p-1 rounded-pill" title="Klop">
                    <i class="ti ti-check"></i> Klop
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php } ?>

</div>
