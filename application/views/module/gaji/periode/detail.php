<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <a href="<?=base_url('gaji/periode');?>" class="text-muted fw-light">Periode & Hitung Gaji /</a> <?=htmlspecialchars($periode['nama_periode']);?>
      </h4>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge bg-label-primary font-monospace">
          <i class="ti ti-calendar me-1"></i> <?=date('d/m/Y', strtotime($periode['tanggal_awal']));?> s/d <?=date('d/m/Y', strtotime($periode['tanggal_akhir']));?>
        </span>
        <span class="badge bg-label-secondary">
          <i class="ti ti-briefcase me-1"></i> <?=$periode['total_hari_kerja_aktif'];?> Hari Kerja Aktif
        </span>
        <?php if ($periode['status_periode'] === 'final') { ?>
          <span class="badge bg-success"><i class="ti ti-lock me-1"></i> Status: FINAL (Terkunci)</span>
        <?php } else { ?>
          <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i> Status: <?=strtoupper($periode['status_periode']);?></span>
        <?php } ?>
      </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="d-flex gap-2">
      <!-- TOMBOL SAKTI HITUNG OTOMATIS -->
      <?php if ($periode['status_periode'] !== 'final') { ?>
        <a href="<?=base_url('gaji/periode/hitung_otomatis/'.$periode['periode_id']);?>"
           class="btn btn-warning btn-lg text-dark fw-bold shadow pulse-btn"
           title="Hitung Otomatis dari Presensi">
          <i class="ti ti-bolt me-1 fs-4"></i> ⚡ HITUNG OTOMATIS DARI PRESENSI
        </a>

        <button type="button" 
           class="btn btn-outline-success btn-confirm-delete"
           data-url="<?=base_url('gaji/periode/kunci_periode/'.$periode['periode_id']);?>"
           data-title="Kunci Periode Sebagai Final?"
           data-text="Apakah Anda yakin ingin MENGUNCI periode ini sebagai FINAL? Periode yang dikunci siap dicairkan ke bank."
           data-variant="success"
           data-icon="ti-lock"
           data-btn-text="Ya, Kunci Final"
           title="Kunci Final">
          <i class="ti ti-lock me-1"></i> Kunci Final
        </button>
      <?php } else { ?>
        <span class="badge bg-success fs-6 py-2 px-3">
          <i class="ti ti-circle-check me-1"></i> Penggajian Selesai & Terkunci
        </span>
      <?php } ?>
    </div>
  </div>

  <!-- Flash Toast & Modal Konfirmasi -->
  <?php $this->load->view('module/gaji/toast_and_modal'); ?>

  <!-- KPI SUMMARY METRICS PERIODE -->
  <div class="row g-3 mb-4">
    <!-- Total Pegawai -->
    <div class="col-sm-6 col-xl-2">
      <div class="card h-100 border-start border-primary border-4 shadow-sm">
        <div class="card-body p-3">
          <span class="text-muted small fw-semibold d-block">Guru Terhitung</span>
          <h4 class="card-title mb-0 text-primary fw-bold mt-1"><?=$stats['total_guru'];?> Pegawai</h4>
          <small class="text-muted">Master Aktif</small>
        </div>
      </div>
    </div>

    <!-- Total Bonus Presensi -->
    <div class="col-sm-6 col-xl-2">
      <div class="card h-100 border-start border-success border-4 shadow-sm">
        <div class="card-body p-3">
          <span class="text-muted small fw-semibold d-block">Bonus Presensi (2-Tier)</span>
          <h4 class="card-title mb-0 text-success fw-bold mt-1">Rp <?=number_format($stats['total_bonus_tier1'] + $stats['total_bonus_tier2'], 0, ',', '.');?></h4>
          <small class="text-success"><i class="ti ti-check me-1"></i>Tepat Waktu & Pagi</small>
        </div>
      </div>
    </div>

    <!-- Total Uang Makan -->
    <div class="col-sm-6 col-xl-2">
      <div class="card h-100 border-start border-info border-4 shadow-sm">
        <div class="card-body p-3">
          <span class="text-muted small fw-semibold d-block">Total Uang Makan</span>
          <h4 class="card-title mb-0 text-info fw-bold mt-1">Rp <?=number_format($stats['total_makan'], 0, ',', '.');?></h4>
          <small class="text-muted">Lama & Baru</small>
        </div>
      </div>
    </div>

    <!-- Total Honor Ekstra -->
    <div class="col-sm-6 col-xl-2">
      <div class="card h-100 border-start border-warning border-4 shadow-sm">
        <div class="card-body p-3">
          <span class="text-muted small fw-semibold d-block">Honor Ekstra Lunas</span>
          <h4 class="card-title mb-0 text-warning fw-bold mt-1">Rp <?=number_format($stats['total_ekstra'], 0, ',', '.');?></h4>
          <small class="text-muted">Iuran Siswa</small>
        </div>
      </div>
    </div>

    <!-- Talangan & Kasbon -->
    <div class="col-sm-6 col-xl-2">
      <div class="card h-100 border-start border-secondary border-4 shadow-sm">
        <div class="card-body p-3">
          <span class="text-muted small fw-semibold d-block">Talangan / Kasbon</span>
          <h4 class="card-title mb-0 text-dark fw-bold mt-1">Rp <?=number_format($stats['total_talangan'] - $stats['total_kasbon'], 0, ',', '.');?></h4>
          <small class="text-muted">Adjustment Net</small>
        </div>
      </div>
    </div>

    <!-- GRAND TOTAL PENGELUARAN (THP) -->
    <div class="col-sm-6 col-xl-2">
      <div class="card h-100 border-start border-danger border-4 shadow-sm bg-label-danger">
        <div class="card-body p-3">
          <span class="text-dark small fw-bold d-block">TOTAL MUKAFAAH (THP)</span>
          <h4 class="card-title mb-0 text-danger fw-bold mt-1">Rp <?=number_format($stats['total_thp'], 0, ',', '.');?></h4>
          <small class="text-dark">Disbursement Bank</small>
        </div>
      </div>
    </div>
  </div>

  <!-- TABEL RINCIAN PENGGAJIAN 26 GURU -->
  <div class="card shadow-sm">
    <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="ti ti-table me-2 text-primary"></i> Rincian Slip Mukafaah Guru & Karyawan
        </h5>
        <small class="text-muted">Hasil evaluasi otomatis presensi 2-tier, masa kerja, uang makan, ekstra, dan penyesuaian operasional.</small>
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPenyesuaian">
        <i class="ti ti-plus me-1"></i> + Penyesuaian Manual (Talangan / Kasbon)
      </button>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover border-top" id="dataTable">
        <thead class="table-light">
          <tr>
            <th style="width: 30px;">No</th>
            <th>Guru / Pegawai</th>
            <th>Gaji Pokok & TMK</th>
            <th>Presensi & Bonus 2-Tier</th>
            <th>Uang Makan</th>
            <th>Honor Ekstra</th>
            <th>Penyesuaian (Talangan/Kasbon)</th>
            <th class="text-end">Take Home Pay (THP)</th>
            <th class="text-center" style="width: 70px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rincian)) { ?>
            <?php $no = 1; foreach ($rincian as $r) : ?>
              <tr>
                <td class="text-center fw-bold"><?=$no++;?></td>

                <!-- Guru / Pegawai -->
                <td>
                  <span class="fw-bold text-dark d-block"><?=htmlspecialchars($r['nama_pegawai']);?></span>
                  <div class="d-flex align-items-center gap-1 mt-1">
                    <?php if ($r['status_kepegawaian'] === 'gty') { ?>
                      <span class="badge bg-primary" style="font-size: 10px;">GTY</span>
                    <?php } else { ?>
                      <span class="badge bg-warning" style="font-size: 10px;"><?=strtoupper($r['status_kepegawaian']);?></span>
                    <?php } ?>
                    <?php if ($r['is_wali_kelas'] === 'y') { ?>
                      <span class="badge bg-label-success" style="font-size: 10px;">Wali Kelas</span>
                    <?php } ?>
                    <?php if (!empty($r['nama_jabatan_struktural'])) { ?>
                      <span class="badge bg-label-dark" style="font-size: 10px;"><?=htmlspecialchars($r['nama_jabatan_struktural']);?></span>
                    <?php } ?>
                  </div>
                </td>

                <!-- Gaji Pokok & TMK -->
                <td>
                  <div class="fw-bold text-dark">Rp <?=number_format($r['gaji_pokok'], 0, ',', '.');?></div>
                  <?php if ($r['tunj_masa_kerja'] > 0) { ?>
                    <div class="small text-success">+Rp <?=number_format($r['tunj_masa_kerja'], 0, ',', '.');?> (TMK <?=$r['masa_kerja_tahun'];?> th)</div>
                  <?php } ?>
                  <?php if ($r['tunj_wali_kelas'] > 0) { ?>
                    <div class="small text-info">+Rp <?=number_format($r['tunj_wali_kelas'], 0, ',', '.');?> (Wali)</div>
                  <?php } ?>
                  <?php if ($r['tunj_struktural'] > 0) { ?>
                    <div class="small text-muted">+Rp <?=number_format($r['tunj_struktural'], 0, ',', '.');?> (Struktural)</div>
                  <?php } ?>
                  <?php if ($r['apresiasi_sertifikasi'] > 0) { ?>
                    <div class="small text-primary">+Rp <?=number_format($r['apresiasi_sertifikasi'], 0, ',', '.');?> (Khusus / Apresiasi)</div>
                  <?php } ?>
                </td>

                <!-- Presensi & Bonus 2-Tier -->
                <td>
                  <div class="small fw-semibold">
                    <i class="ti ti-check text-success"></i> Hadir: <b><?=$r['hari_hadir_presensi'];?></b> hari
                  </div>
                  <div class="small text-muted">
                    Telat &gt;06.48: <?=$r['telat_tier1_count'];?>x &bull; &gt;06.30: <?=$r['datang_lewat_0630_count'];?>x
                  </div>
                  <div class="mt-1">
                    <?php if ($r['bonus_presensi_tier1'] > 0) { ?>
                      <span class="badge bg-success p-1 small" title="Tier 1: Telat <= 3x">+Rp 50rb (T1)</span>
                    <?php } else { ?>
                      <span class="badge bg-label-danger p-1 small" title="Tier 1 Hangus (Telat > 3x)">T1 Hangus</span>
                    <?php } ?>

                    <?php if ($r['bonus_presensi_tier2'] > 0) { ?>
                      <span class="badge bg-success p-1 small" title="Tier 2: Hadir <= 06.30">+Rp 50rb (T2)</span>
                    <?php } ?>
                  </div>
                </td>

                <!-- Uang Makan -->
                <td>
                  <div class="fw-bold text-dark">Rp <?=number_format($r['tunj_makan'], 0, ',', '.');?></div>
                  <div class="small text-muted"><?=$r['hari_hadir_presensi'];?> x tarif</div>
                </td>

                <!-- Honor Ekstra -->
                <td>
                  <?php if ($r['honor_ekstra_lunas'] > 0) { ?>
                    <div class="fw-bold text-success">Rp <?=number_format($r['honor_ekstra_lunas'], 0, ',', '.');?></div>
                    <div class="small text-muted">Siswa Lunas</div>
                  <?php } else { ?>
                    <span class="text-muted small">-</span>
                  <?php } ?>
                  <?php if ($r['honor_ekstra_pending'] > 0) { ?>
                    <div class="small text-warning" title="Ditangguhkan karena siswa belum bayar">
                      <i class="ti ti-clock"></i> Rp <?=number_format($r['honor_ekstra_pending'], 0, ',', '.');?> (Tertahan)
                    </div>
                  <?php } ?>
                </td>

                <!-- Penyesuaian Manual (Talangan / Kasbon) -->
                <td>
                  <?php if (!empty($r['penyesuaian'])) { ?>
                    <?php foreach ($r['penyesuaian'] as $pny) : ?>
                      <div class="small mb-1 d-flex justify-content-between align-items-center">
                        <span>
                          <?php if ($pny['jenis'] === 'penambahan') { ?>
                            <span class="badge bg-label-success p-1" style="font-size: 10px;">+</span>
                          <?php } else { ?>
                            <span class="badge bg-label-danger p-1" style="font-size: 10px;">-</span>
                          <?php } ?>
                          <?=htmlspecialchars($pny['keterangan']);?>: <b>Rp <?=number_format($pny['nominal'], 0, ',', '.');?></b>
                        </span>
                        <?php if ($periode['status_periode'] !== 'final') { ?>
                          <button type="button" 
                                  class="btn btn-xs p-0 text-danger ms-1 border-0 bg-transparent btn-confirm-delete" 
                                  data-url="<?=base_url('gaji/periode/delete_penyesuaian/'.$pny['id'].'/'.$periode['periode_id']);?>" 
                                  data-title="Hapus Penyesuaian?" 
                                  data-text="Hapus penyesuaian <b><?=htmlspecialchars($pny['keterangan']);?></b>?" 
                                  title="Hapus">
                            <i class="ti ti-x" style="font-size: 12px;"></i>
                          </button>
                        <?php } ?>
                      </div>
                    <?php endforeach; ?>
                  <?php } else { ?>
                    <span class="text-muted small">-</span>
                  <?php } ?>
                </td>

                <!-- Take Home Pay (THP) -->
                <td class="text-end">
                  <div class="fs-6 fw-bold text-primary">
                    Rp <?=number_format($r['take_home_pay'], 0, ',', '.');?>
                  </div>
                  <?php if (!empty($r['nomor_rekening'])) { ?>
                    <small class="text-muted font-monospace"><?=$r['nama_bank'];?>: <?=$r['nomor_rekening'];?></small>
                  <?php } ?>
                </td>

                <!-- Aksi: Tambah Penyesuaian Perorangan -->
                <td class="text-center align-middle">
                  <?php if ($periode['status_periode'] !== 'final') { ?>
                    <button type="button" 
                            class="btn btn-xs btn-outline-primary p-1 rounded-pill btn-tambah-adj"
                            data-gaji-id="<?=$r['gaji_id'];?>"
                            data-nama-pegawai="<?=htmlspecialchars($r['nama_pegawai']);?>"
                            title="Tambah Penyesuaian">
                      <i class="ti ti-plus"></i> Adjust
                    </button>
                  <?php } else { ?>
                    <i class="ti ti-lock text-muted"></i>
                  <?php } ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if (empty($rincian)) { ?>
      <div class="card-body text-center py-5 border-top">
        <i class="ti ti-calculator fs-1 d-block mb-2 text-warning"></i>
        <h5 class="fw-bold mb-1">Data Gaji Belum Dikalkulasi</h5>
        <p class="text-muted mb-3">Klik tombol di bawah untuk memproses dan mengevaluasi presensi biometrik seluruh guru secara otomatis.</p>
        <a href="<?=base_url('gaji/periode/hitung_otomatis/'.$periode['periode_id']);?>" class="btn btn-warning btn-lg text-dark fw-bold px-4 shadow pulse-btn">
          <i class="ti ti-bolt me-1 fs-4"></i> ⚡ HITUNG OTOMATIS DARI PRESENSI
        </a>
      </div>
    <?php } ?>
  </div>
</div>

<!-- MODAL PENYESUAIAN MANUAL (TALANGAN / KASBON / TRANSPORT) -->
<div class="modal fade" id="modalPenyesuaian" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title d-flex align-items-center">
          <i class="ti ti-adjustments me-2 text-primary"></i> Penyesuaian Manual Slip Gaji
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?=base_url('gaji/periode/add_penyesuaian');?>" method="POST">
        <input type="hidden" name="periode_id" value="<?=$periode['periode_id'];?>">
        
        <div class="modal-body">
          <div class="alert alert-info py-2 px-3 small mb-3">
            <i class="ti ti-info-circle me-1"></i> <strong>Catatan Penyesuaian:</strong> Gunakan form ini untuk mencatat talangan operasional sekolah, potongan kasbon, atau uang transport, dll.
          </div>

          <!-- Pilih Pegawai -->
          <div class="mb-3">
            <label class="form-label fw-bold" for="adj_gaji_id">Pilih Guru / Pegawai <span class="text-danger">*</span></label>
            <select class="form-select" id="adj_gaji_id" name="gaji_id" required>
              <?php if (empty($rincian)) { ?>
                <option value="">-- Belum ada data slip (Klik Hitung Otomatis dahulu) --</option>
              <?php } else { ?>
                <option value="">-- Pilih Guru / Pegawai --</option>
                <?php foreach ($rincian as $r) : ?>
                  <option value="<?=$r['gaji_id'];?>"><?=$r['nama_pegawai'];?></option>
                <?php endforeach; ?>
              <?php } ?>
            </select>
          </div>

          <!-- Jenis Penyesuaian -->
          <div class="row g-2 mb-3">
            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="adj_jenis">Jenis Penyesuaian <span class="text-danger">*</span></label>
              <select class="form-select" id="adj_jenis" name="jenis" required>
                <option value="penambahan">Penambahan (+ Penghasilan)</option>
                <option value="potongan">Potongan (- Potong Gaji)</option>
              </select>
            </div>

            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="adj_kategori">Kategori Penyesuaian <span class="text-danger">*</span></label>
              <select class="form-select" id="adj_kategori" name="kategori" required>
                <option value="talangan">Talangan Operasional Sekolah</option>
                <option value="kasbon">Potongan Kasbon / Pinjaman</option>
                <option value="transport">Tunjangan Transport Luar Kota / Klaten</option>
                <option value="kesetaraan">Insentif ANBK / TKA / Data</option>
                <option value="sertifikasi">Apresiasi / Tunjangan Khusus</option>
                <option value="lainnya">Lain-lain</option>
              </select>
            </div>
          </div>

          <!-- Keterangan -->
          <div class="mb-3">
            <label class="form-label fw-bold" for="adj_keterangan">Keterangan Penyesuaian <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="adj_keterangan" name="keterangan" placeholder="Contoh: Talangan servis mobil jemputan / Tambal ban pikap" required>
            <div class="form-text small">Rincian detail peruntukan talangan atau potongan.</div>
          </div>

          <!-- Nominal -->
          <div class="mb-3">
            <label class="form-label fw-bold" for="adj_nominal">Nominal Penyesuaian (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" step="1000" class="form-control fw-bold text-end fs-5" id="adj_nominal" name="nominal" placeholder="0" required>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4 shadow">
            <i class="ti ti-device-floppy me-1"></i> Simpan Penyesuaian
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

  const adjButtons = document.querySelectorAll('.btn-tambah-adj');
  const modalAdjEl = document.getElementById('modalPenyesuaian');
  const bsModalAdj = new bootstrap.Modal(modalAdjEl);

  adjButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const gajiId = this.getAttribute('data-gaji-id');
      document.getElementById('adj_gaji_id').value = gajiId;
      bsModalAdj.show();
    });
  });
});
</script>
