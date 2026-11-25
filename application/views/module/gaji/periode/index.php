<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Periode & Hitung Gaji
      </h4>
      <p class="text-muted mb-0 small">
        Siklus penggajian bulanan yayasan terintegrasi evaluasi presensi biometrik otomatis, uang makan, masa kerja GTY, dan pencairan ekstra.
      </p>
    </div>
    <button type="button" class="btn btn-primary shadow" data-bs-toggle="modal" data-bs-target="#modalBuatPeriode">
      <i class="ti ti-calendar-plus me-1"></i> Buat Periode Baru
    </button>
  </div>

  <!-- Flash Message Notifikasi -->
  <?=$this->session->flashdata('message');?>

  <!-- TABEL DAFTAR PERIODE PENGGAJIAN -->
  <div class="card shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 d-flex align-items-center">
        <i class="ti ti-calendar-event me-2 text-primary"></i> Riwayat Periode Penggajian Bulanan
      </h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover border-top" id="dataTable">
        <thead class="table-light">
          <tr>
            <th style="width: 40px;">No</th>
            <th>Nama Periode</th>
            <th>Rentang Cut-Off Presensi</th>
            <th class="text-center">Hari Kerja</th>
            <th class="text-center">Pegawai Dihitung</th>
            <th class="text-end">Total Pengeluaran (THP)</th>
            <th class="text-center">Status</th>
            <th class="text-center" style="width: 120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($periodes)) { ?>
            <?php $no = 1; foreach ($periodes as $p) : ?>
              <tr>
                <td class="text-center fw-bold"><?=$no++;?></td>
                
                <td>
                  <span class="fw-bold text-dark fs-6 d-block"><?=htmlspecialchars($p['nama_periode']);?></span>
                  <small class="text-muted font-monospace">Bulan: <?=$p['bulan'];?> / Tahun: <?=$p['tahun'];?></small>
                </td>

                <td>
                  <div class="small fw-semibold text-dark">
                    <i class="ti ti-calendar me-1 text-primary"></i>
                    <?=date('d M Y', strtotime($p['tanggal_awal']));?> s/d <?=date('d M Y', strtotime($p['tanggal_akhir']));?>
                  </div>
                </td>

                <td class="text-center">
                  <span class="badge bg-label-secondary"><?=$p['total_hari_kerja_aktif'];?> Hari</span>
                </td>

                <td class="text-center">
                  <?php if ($p['total_pegawai_dihitung'] > 0) { ?>
                    <span class="badge bg-label-success fw-bold px-2 py-1">
                      <i class="ti ti-users me-1"></i> <?=$p['total_pegawai_dihitung'];?> Guru
                    </span>
                  <?php } else { ?>
                    <span class="badge bg-label-warning text-dark px-2 py-1">Belum Dihitung</span>
                  <?php } ?>
                </td>

                <td class="text-end fw-bold text-primary fs-6">
                  Rp <?=number_format($p['total_pengeluaran'], 0, ',', '.');?>
                </td>

                <td class="text-center">
                  <?php if ($p['status_periode'] === 'final') { ?>
                    <span class="badge bg-success px-2 py-1"><i class="ti ti-lock me-1"></i> FINAL</span>
                  <?php } else if ($p['status_periode'] === 'dihitung') { ?>
                    <span class="badge bg-info px-2 py-1"><i class="ti ti-calculator me-1"></i> DIHITUNG</span>
                  <?php } else if ($p['status_periode'] === 'terbayar') { ?>
                    <span class="badge bg-primary px-2 py-1"><i class="ti ti-circle-check me-1"></i> TERBAYAR</span>
                  <?php } else { ?>
                    <span class="badge bg-secondary px-2 py-1"><i class="ti ti-file-text me-1"></i> DRAFT</span>
                  <?php } ?>
                </td>

                <td class="text-center align-middle">
                  <a href="<?=base_url('gaji/periode/detail/'.$p['periode_id']);?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                    <i class="ti ti-eye me-1"></i> Rincian Gaji
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if (empty($periodes)) { ?>
      <div class="card-body text-center py-5 border-top">
        <i class="ti ti-calendar-off fs-1 d-block mb-2 text-secondary"></i>
        <p class="text-muted mb-2">Belum ada periode penggajian yang dibuat.</p>
        <button type="button" class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBuatPeriode">
          <i class="ti ti-plus me-1"></i> Buat Periode Pertama
        </button>
      </div>
    <?php } ?>
  </div>
</div>

<!-- MODAL BUAT PERIODE BARU -->
<div class="modal fade" id="modalBuatPeriode" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title d-flex align-items-center">
          <i class="ti ti-calendar-plus me-2 text-primary"></i> Buat Periode Penggajian Bulanan Baru
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?=base_url('gaji/periode/create');?>" method="POST">
        <div class="modal-body">
          <div class="alert alert-info py-2 px-3 small mb-3">
            <i class="ti ti-info-circle me-1"></i> <strong>Periode Penggajian:</strong> Evaluasi presensi dilakukan tanggal 1 s/d 30/31 bulan terkait.
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-7 col-12">
              <label class="form-label fw-bold" for="input_bulan">Bulan Penggajian <span class="text-danger">*</span></label>
              <select class="form-select" id="input_bulan" name="bulan" required>
                <?php 
                  $nama_bulan = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                  $cur_m = (int)date('n');
                  foreach ($nama_bulan as $m_num => $m_name) : 
                ?>
                  <option value="<?=$m_num;?>" <?=$cur_m == $m_num ? 'selected' : '';?>><?=$m_name;?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-5 col-12">
              <label class="form-label fw-bold" for="input_tahun">Tahun <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="input_tahun" name="tahun" value="<?=date('Y');?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="input_nama_periode">Nama Label Periode</label>
            <input type="text" class="form-control" id="input_nama_periode" name="nama_periode" placeholder="Contoh: Mukafaah November 2026">
            <div class="form-text small">Kosongkan untuk otomatis mengisi nama bulan & tahun.</div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="input_tgl_awal">Tanggal Awal Cut-Off <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="input_tgl_awal" name="tanggal_awal" value="<?=date('Y-m-01');?>" required>
            </div>

            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="input_tgl_akhir">Tanggal Akhir Cut-Off <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="input_tgl_akhir" name="tanggal_akhir" value="<?=date('Y-m-t');?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="input_hari_kerja">Total Hari Kerja Aktif (Hari)</label>
            <input type="number" class="form-control" id="input_hari_kerja" name="total_hari_kerja_aktif" value="20" required>
            <div class="form-text small">Jumlah hari operasional KBM sekolah dalam bulan tersebut.</div>
          </div>
        </div>

        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4 shadow">
            <i class="ti ti-plus me-1"></i> Buat Periode & Lanjut
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof $.fn !== 'undefined' && typeof $.fn.dataTable !== 'undefined') {
    $.fn.dataTable.ext.errMode = 'none';
  }
});
</script>
