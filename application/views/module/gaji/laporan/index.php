<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Slip Gaji & Rekapitulasi
      </h4>
      <p class="text-muted mb-0 small">
        Pusat pencetakan dokumen resmi mukafaah: Slip Gaji perorangan format A5, cetak massal (batch print), dan Rekapitulasi Pembukuan Yayasan.
      </p>
    </div>

    <!-- Dropdown Pemilihan Periode & Tombol Cetak Massal -->
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="d-flex align-items-center gap-2">
        <label class="form-label mb-0 small fw-bold text-nowrap"><i class="ti ti-calendar-event me-1"></i> Periode:</label>
        <select class="form-select form-select-sm shadow-sm" style="min-width: 220px;" onchange="if(this.value) window.location.href='<?=base_url('gaji/laporan/index/');?>' + this.value;">
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

      <?php if (!empty($rincian)) { ?>
        <a href="<?=base_url('gaji/laporan/slip_batch/'.$selected_id);?>" target="_blank" class="btn btn-sm btn-primary shadow-sm">
          <i class="ti ti-printer me-1"></i> Cetak Semua Slip A5 (Batch)
        </a>
        <a href="<?=base_url('gaji/laporan/rekap/'.$selected_id);?>" target="_blank" class="btn btn-sm btn-outline-success shadow-sm">
          <i class="ti ti-file-analytics me-1"></i> Cetak Rekapitulasi
        </a>
      <?php } ?>
    </div>
  </div>

  <!-- Flash Toast & Modal Konfirmasi -->
  <?php $this->load->view('module/gaji/toast_and_modal'); ?>

  <?php if (empty($rincian)) { ?>
    <!-- KONDISI KOSONG -->
    <div class="card shadow-sm border-0 text-center py-5">
      <div class="card-body">
        <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 p-3 rounded-circle">
          <i class="ti ti-file-off fs-1 text-secondary"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">Data Slip Gaji Belum Dihitung</h5>
        <p class="text-muted small mb-4">
          Silakan buat dan hitung periode penggajian terlebih dahulu di menu <b>Periode & Hitung Gaji</b> untuk menghasilkan slip mukafaah guru.
        </p>
        <a href="<?=base_url('gaji/periode');?>" class="btn btn-primary px-4">
          <i class="ti ti-calendar me-1"></i> Buka Menu Periode
        </a>
      </div>
    </div>
  <?php } else { ?>

    <!-- KPI INFO SINGKAT PERIODE -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-primary border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="text-muted small fw-semibold d-block">Nama Periode</span>
            <h5 class="card-title mb-0 text-primary fw-bold mt-1"><?=htmlspecialchars($periode['nama_periode']);?></h5>
            <small class="text-muted">Bulan <?=$periode['bulan'];?> / <?=$periode['tahun'];?></small>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-success border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="text-muted small fw-semibold d-block">Total Slip Tersedia</span>
            <h4 class="card-title mb-0 text-success fw-bold mt-1"><?=count($rincian);?> Slip Guru</h4>
            <small class="text-muted">Format A5 Siap Cetak</small>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-info border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="text-muted small fw-semibold d-block">Total Pengeluaran (THP)</span>
            <h4 class="card-title mb-0 text-info fw-bold mt-1">Rp <?=number_format($periode['total_pengeluaran'], 0, ',', '.');?></h4>
            <small class="text-muted">Disbursement Yayasan</small>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-start border-warning border-4 shadow-sm">
          <div class="card-body p-3">
            <span class="text-muted small fw-semibold d-block">Status Periode</span>
            <h5 class="card-title mb-0 text-warning fw-bold mt-1"><?=strtoupper($periode['status_periode']);?></h5>
            <small class="text-muted"><?=$periode['status_periode'] === 'final' ? 'Terkunci & Valid' : 'Mode Draft / Dihitung';?></small>
          </div>
        </div>
      </div>
    </div>

    <!-- TABEL DAFTAR SLIP GAJI GURU -->
    <div class="card shadow-sm">
      <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="ti ti-file-text me-2 text-primary"></i> Daftar Slip Mukafaah Guru & Karyawan
          </h5>
          <small class="text-muted">Pilih tombol Cetak pada baris guru untuk mencetak lembar slip A5 perorangan.</small>
        </div>
      </div>

      <div class="card-datatable table-responsive">
        <table class="table table-hover border-top" id="dataTable">
          <thead class="table-light">
            <tr>
              <th style="width: 35px;">No</th>
              <th>Nama Pegawai</th>
              <th>Status & Jabatan</th>
              <th class="text-end">Penghasilan Kotor</th>
              <th class="text-end text-danger">Potongan</th>
              <th class="text-end text-success">Take Home Pay (THP)</th>
              <th>Rekening Pembayaran</th>
              <th class="text-center" style="width: 130px;">Aksi Cetak</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach ($rincian as $r) : ?>
              <tr>
                <td class="text-center fw-bold"><?=$no++;?></td>
                <td>
                  <span class="fw-bold text-dark d-block"><?=htmlspecialchars($r['nama_pegawai']);?></span>
                  <small class="text-muted font-monospace">NIP: <?=$r['id_pegawai'];?></small>
                </td>
                <td>
                  <?php if ($r['status_kepegawaian'] === 'gty') { ?>
                    <span class="badge bg-primary px-2 py-1" style="font-size: 10px;">GTY</span>
                  <?php } else { ?>
                    <span class="badge bg-secondary px-2 py-1" style="font-size: 10px;"><?=strtoupper($r['status_kepegawaian']);?></span>
                  <?php } ?>
                  <?php if ($r['is_wali_kelas'] === 'y') { ?>
                    <span class="badge bg-label-success px-2 py-1" style="font-size: 10px;">Wali</span>
                  <?php } ?>
                </td>
                <td class="text-end fw-semibold">
                  Rp <?=number_format($r['total_penghasilan_kotor'], 0, ',', '.');?>
                </td>
                <td class="text-end">
                  <?php if ($r['total_potongan'] > 0) { ?>
                    <span class="text-danger fw-bold">-Rp <?=number_format($r['total_potongan'], 0, ',', '.');?></span>
                  <?php } else { ?>
                    <span class="text-muted">Rp 0</span>
                  <?php } ?>
                </td>
                <td class="text-end fw-bold text-success fs-6">
                  Rp <?=number_format($r['take_home_pay'], 0, ',', '.');?>
                </td>
                <td>
                  <?php if (!empty($r['nomor_rekening'])) { ?>
                    <span class="badge <?=$r['nama_bank'] == 'BSI' ? 'bg-label-info' : 'bg-label-primary';?> px-2 py-1 fw-bold">
                      <?=$r['nama_bank'];?>
                    </span>
                    <span class="font-monospace small ms-1"><?=$r['nomor_rekening'];?></span>
                  <?php } else { ?>
                    <span class="badge bg-label-secondary small">Tunai / Pending</span>
                  <?php } ?>
                </td>
                <td class="text-center align-middle">
                  <a href="<?=base_url('gaji/laporan/slip/'.$r['gaji_id']);?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="ti ti-printer me-1"></i> Cetak A5
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php } ?>

</div>
