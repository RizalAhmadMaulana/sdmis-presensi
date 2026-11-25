<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Master Gaji & Rekening
      </h4>
      <p class="text-muted mb-0 small">
        Manajemen profil gaji pokok, status Guru Tetap Yayasan (GTY), tunjangan jabatan struktural, dan nomor rekening perbankan untuk pencairan payroll.
      </p>
    </div>
    <span class="badge bg-label-primary px-3 py-2 fs-6">
      <i class="ti ti-users me-1"></i> <?=count($karyawan);?> Pegawai Terdaftar
    </span>
  </div>

  <!-- Flash Message Notifikasi -->
  <?=$this->session->flashdata('message');?>

  <!-- KPI SUMMARY CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-start border-primary border-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold d-block mb-1">Total Guru & Karyawan</span>
              <h3 class="card-title mb-0 text-primary fw-bold"><?=$stats['total_pegawai'];?></h3>
              <small class="text-muted">Terdaftar di sistem presensi</small>
            </div>
            <div class="avatar avatar-md bg-label-primary rounded p-2">
              <i class="ti ti-users fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-start border-info border-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold d-block mb-1">Guru Tetap Yayasan (GTY)</span>
              <h3 class="card-title mb-0 text-info fw-bold"><?=$stats['total_gty'];?></h3>
              <small class="text-success"><i class="ti ti-check me-1"></i>Hak Tunjangan Masa Kerja</small>
            </div>
            <div class="avatar avatar-md bg-label-info rounded p-2">
              <i class="ti ti-school fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-start border-warning border-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold d-block mb-1">Kontrak / Ekstra / Staff</span>
              <h3 class="card-title mb-0 text-warning fw-bold"><?=$stats['total_kontrak_ekstra'];?></h3>
              <small class="text-muted">Honor Ekstra / Komponen</small>
            </div>
            <div class="avatar avatar-md bg-label-warning rounded p-2">
              <i class="ti ti-briefcase fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-start border-success border-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold d-block mb-1">Rekening Siap Transfer</span>
              <h3 class="card-title mb-0 text-success fw-bold"><?=$stats['total_rekening_ready'];?></h3>
              <small class="text-muted">Bank BRI / BSI CMS Ready</small>
            </div>
            <div class="avatar avatar-md bg-label-success rounded p-2">
              <i class="ti ti-building-bank fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLE MASTER PROFIL GAJI -->
  <div class="card shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 d-flex align-items-center">
        <i class="ti ti-table me-2 text-primary"></i> Data Master Gaji & Rekening Perbankan Guru
      </h5>
      <span class="badge bg-label-secondary small">
        <i class="ti ti-info-circle me-1"></i> Klik tombol Edit pada baris guru untuk mengubah profil gaji
      </span>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover border-top" id="dataTable">
        <thead class="table-light">
          <tr>
            <th style="width: 40px;">No</th>
            <th>Pegawai / Guru</th>
            <th>Status</th>
            <th>Tugas & Jabatan</th>
            <th class="text-end">Gaji Pokok</th>
            <th>Rekening Bank & Reff CMS</th>
            <th class="text-center" style="width: 80px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; foreach ($karyawan as $row) : ?>
            <tr>
              <!-- 1. Nomor -->
              <td class="text-center fw-bold"><?=$no++;?></td>

              <!-- 2. Pegawai -->
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-2">
                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                      <?=strtoupper(substr($row['nama_pegawai'], 0, 2));?>
                    </span>
                  </div>
                  <div>
                    <span class="fw-bold text-dark d-block"><?=htmlspecialchars($row['nama_pegawai']);?></span>
                    <span class="small text-muted font-monospace me-2">ID: <?=$row['id_pegawai'];?></span>
                    <?php if (!empty($row['tanggal_mulai_kerja']) && $row['tanggal_mulai_kerja'] != '0000-00-00') { ?>
                      <span class="badge bg-label-secondary p-1 small" title="Mulai Kerja: <?=date('d/m/Y', strtotime($row['tanggal_mulai_kerja']));?>">
                        <i class="ti ti-calendar me-1"></i><?=$row['masa_kerja_tahun'];?> Thn Kerja
                      </span>
                    <?php } ?>
                  </div>
                </div>
              </td>

              <!-- 3. Status Kepegawaian -->
              <td>
                <?php if ($row['status_kepegawaian'] === 'gty') { ?>
                  <span class="badge bg-primary px-2 py-1"><i class="ti ti-award me-1"></i> GTY</span>
                  <div class="small text-muted mt-1">TMK: Rp <?=number_format($row['est_tunj_masa_kerja'], 0, ',', '.');?></div>
                <?php } else if ($row['status_kepegawaian'] === 'kontrak') { ?>
                  <span class="badge bg-warning px-2 py-1"><i class="ti ti-clock me-1"></i> Kontrak</span>
                <?php } else if ($row['status_kepegawaian'] === 'guru_ekstra') { ?>
                  <span class="badge bg-info px-2 py-1"><i class="ti ti-target me-1"></i> Guru Ekstra</span>
                <?php } else { ?>
                  <span class="badge bg-secondary px-2 py-1"><i class="ti ti-user me-1"></i> Karyawan</span>
                <?php } ?>
              </td>

              <!-- 4. Tugas & Jabatan -->
              <td>
                <?php if ($row['is_wali_kelas'] === 'y') { ?>
                  <span class="badge bg-label-success px-2 py-1 me-1 mb-1">
                    <i class="ti ti-school me-1"></i> Wali Kelas
                  </span>
                <?php } ?>
                <?php if (!empty($row['nama_jabatan_struktural'])) { ?>
                  <span class="badge bg-label-dark px-2 py-1 me-1 mb-1">
                    <i class="ti ti-briefcase me-1"></i> <?=htmlspecialchars($row['nama_jabatan_struktural']);?>
                  </span>
                  <?php if ($row['nominal_tunj_struktural'] > 0) { ?>
                    <div class="small text-muted">+Rp <?=number_format($row['nominal_tunj_struktural'], 0, ',', '.');?></div>
                  <?php } ?>
                <?php } ?>
                <?php if ($row['is_wali_kelas'] !== 'y' && empty($row['nama_jabatan_struktural'])) { ?>
                  <span class="text-muted small">-</span>
                <?php } ?>
              </td>

              <!-- 5. Gaji Pokok -->
              <td class="text-end fw-bold text-dark">
                Rp <?=number_format($row['gaji_pokok_nominal'], 0, ',', '.');?>
                <?php if ($row['tunj_khusus_tetap'] > 0) { ?>
                  <div class="small text-success fw-normal">+Rp <?=number_format($row['tunj_khusus_tetap'], 0, ',', '.');?> (Khusus)</div>
                <?php } ?>
              </td>

              <!-- 6. Rekening Bank & Reff CMS -->
              <td>
                <?php if (!empty($row['nomor_rekening'])) { ?>
                  <div>
                    <span class="badge <?=$row['nama_bank'] == 'BSI' ? 'bg-label-info' : 'bg-label-primary';?> px-2 py-1 fw-bold">
                      <?=$row['nama_bank'];?>
                    </span>
                    <span class="font-monospace fw-bold text-dark ms-1"><?=$row['nomor_rekening'];?></span>
                  </div>
                  <div class="small text-muted">
                    a.n <?=htmlspecialchars(!empty($row['nama_pemilik_rekening']) ? $row['nama_pemilik_rekening'] : $row['nama_pegawai']);?>
                    <?php if (!empty($row['customer_reff'])) { ?>
                      &bull; <span class="badge bg-label-secondary font-monospace" style="font-size: 10px;"><?=$row['customer_reff'];?></span>
                    <?php } ?>
                  </div>
                <?php } else { ?>
                  <span class="badge bg-label-danger"><i class="ti ti-alert-circle me-1"></i> Belum Ada Rekening</span>
                <?php } ?>
              </td>

              <!-- 7. Aksi Edit Modal -->
              <td class="text-center align-middle">
                <button type="button" 
                        class="btn btn-sm btn-primary rounded-pill px-3 btn-edit-gaji" 
                        data-id="<?=$row['pegawai_id'];?>"
                        data-nama="<?=htmlspecialchars($row['nama_pegawai']);?>"
                        data-idpeg="<?=$row['id_pegawai'];?>"
                        data-status="<?=$row['status_kepegawaian'];?>"
                        data-wali="<?=$row['is_wali_kelas'];?>"
                        data-jabatan="<?=htmlspecialchars(!empty($row['nama_jabatan_struktural']) ? $row['nama_jabatan_struktural'] : '');?>"
                        data-tunjab="<?=$row['nominal_tunj_struktural'];?>"
                        data-gapok="<?=$row['gaji_pokok_nominal'];?>"
                        data-tunjkhusus="<?=$row['tunj_khusus_tetap'];?>"
                        data-bank="<?=$row['nama_bank'];?>"
                        data-norek="<?=htmlspecialchars(!empty($row['nomor_rekening']) ? $row['nomor_rekening'] : '');?>"
                        data-anrek="<?=htmlspecialchars(!empty($row['nama_pemilik_rekening']) ? $row['nama_pemilik_rekening'] : $row['nama_pegawai']);?>"
                        data-cabang="<?=htmlspecialchars(!empty($row['cabang_bank']) ? $row['cabang_bank'] : 'Klaten');?>"
                        data-reff="<?=htmlspecialchars(!empty($row['customer_reff']) ? $row['customer_reff'] : '');?>"
                        data-masakerja="<?=$row['masa_kerja_tahun'];?>"
                        data-tglmulai="<?=$row['tanggal_mulai_kerja'];?>"
                        title="Edit Profil Gaji & Rekening">
                  <i class="ti ti-edit me-1"></i> Edit
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL EDIT PROFIL GAJI & REKENING PEGAWAI -->
<div class="modal fade" id="modalEditGaji" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom bg-light py-3">
        <div>
          <h5 class="modal-title d-flex align-items-center mb-0">
            <i class="ti ti-edit me-2 text-primary"></i> Edit Profil Gaji: <span id="modal_nama_pegawai" class="text-primary ms-1"></span>
          </h5>
          <small class="text-muted">ID: <span id="modal_id_pegawai" class="font-monospace"></span> &bull; Masa Kerja: <span id="modal_masa_kerja" class="fw-bold"></span> Thn</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formEditGaji" action="<?=base_url('gaji/karyawan/update_setting');?>" method="POST">
        <input type="hidden" name="pegawai_id" id="edit_pegawai_id">

        <div class="modal-body p-4">
          
          <!-- NAV TABS -->
          <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
            <li class="nav-item">
              <button type="button" class="nav-link active fw-bold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-komponen-gaji">
                <i class="ti ti-wallet me-1"></i> 1. Profil Gaji & Jabatan
              </button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link fw-bold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-rekening-bank">
                <i class="ti ti-building-bank me-1"></i> 2. Rekening Bank & CMS
              </button>
            </li>
          </ul>

          <div class="tab-content pt-2">
            <!-- TAB 1: PROFIL GAJI & JABATAN -->
            <div class="tab-pane fade show active" id="tab-komponen-gaji" role="tabpanel">
              <div class="row g-3">
                <!-- Status Kepegawaian -->
                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_status_kepegawaian">Status Kepegawaian <span class="text-danger">*</span></label>
                  <select class="form-select" id="edit_status_kepegawaian" name="status_kepegawaian" required>
                    <option value="gty">Guru Tetap Yayasan (GTY)</option>
                    <option value="kontrak">Guru Kontrak</option>
                    <option value="guru_ekstra">Guru Pengampu Ekstra</option>
                    <option value="karyawan">Karyawan / Staff Operasional</option>
                  </select>
                  <div class="form-text small">Status GTY otomatis mendapatkan Tunjangan Masa Kerja (Rp 15.000/thn).</div>
                </div>

                <!-- Gaji Pokok Nominal -->
                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_gaji_pokok_nominal">Gaji Pokok Bulanan <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="1000" class="form-control fw-bold text-end" id="edit_gaji_pokok_nominal" name="gaji_pokok_nominal" required>
                  </div>
                  <div class="form-text small">Nominal gaji pokok acuan per bulan.</div>
                </div>

                <!-- Status Wali Kelas -->
                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_is_wali_kelas">Penugasan Wali Kelas</label>
                  <select class="form-select" id="edit_is_wali_kelas" name="is_wali_kelas">
                    <option value="n">Bukan Wali Kelas (Rp 0)</option>
                    <option value="y">Wali Kelas Aktif (+Rp 25.000)</option>
                  </select>
                  <div class="form-text small">Otomatis menambahkan tunjangan wali kelas di slip gaji.</div>
                </div>

                <!-- Nama Jabatan Struktural -->
                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_nama_jabatan_struktural">Nama Tugas / Jabatan Struktural</label>
                  <input type="text" class="form-control" id="edit_nama_jabatan_struktural" name="nama_jabatan_struktural" placeholder="Misal: Waka Kurikulum / Kepala PAUD / PJ Sarpras">
                  <div class="form-text small">Kosongkan jika tidak memegang jabatan struktural.</div>
                </div>

                <!-- Nominal Tunjangan Struktural -->
                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_nominal_tunj_struktural">Nominal Tunjangan Struktural</label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="1000" class="form-control text-end" id="edit_nominal_tunj_struktural" name="nominal_tunj_struktural" value="0">
                  </div>
                  <div class="form-text small">Besaran tunjangan struktural yang melekat pada jabatan.</div>
                </div>

                <!-- Tunjangan Khusus Tetap -->
                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_tunj_khusus_tetap">Tunjangan Khusus / Tetap Lainnya</label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="1000" class="form-control text-end" id="edit_tunj_khusus_tetap" name="tunj_khusus_tetap" value="0">
                  </div>
                  <div class="form-text small">Tunjangan tambahan rutin jika ada.</div>
                </div>
              </div>
            </div>

            <!-- TAB 2: REKENING BANK & CMS PAYROLL -->
            <div class="tab-pane fade" id="tab-rekening-bank" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 col-12">
                  <label class="form-label fw-bold" for="edit_nama_bank">Nama Bank</label>
                  <select class="form-select" id="edit_nama_bank" name="nama_bank">
                    <option value="BRI">Bank BRI</option>
                    <option value="BSI">Bank BSI (Syariah)</option>
                    <option value="MANDIRI">Bank Mandiri</option>
                    <option value="BCA">Bank BCA</option>
                    <option value="BNI">Bank BNI</option>
                  </select>
                </div>

                <div class="col-md-8 col-12">
                  <label class="form-label fw-bold" for="edit_nomor_rekening">Nomor Rekening Bank</label>
                  <input type="text" class="form-control font-monospace fs-5 fw-bold" id="edit_nomor_rekening" name="nomor_rekening" placeholder="Contoh: 686701021434533">
                  <div class="form-text small">Nomor rekening tanpa spasi atau tanda hubung.</div>
                </div>

                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_nama_pemilik_rekening">Atas Nama Pemilik Rekening</label>
                  <input type="text" class="form-control" id="edit_nama_pemilik_rekening" name="nama_pemilik_rekening" placeholder="Sesuai buku tabungan">
                </div>

                <div class="col-md-6 col-12">
                  <label class="form-label fw-bold" for="edit_cabang_bank">Kantor Cabang Bank</label>
                  <input type="text" class="form-control" id="edit_cabang_bank" name="cabang_bank" value="Klaten" placeholder="Klaten / Pedan / Delanggu">
                </div>

                <div class="col-md-12 col-12">
                  <label class="form-label fw-bold" for="edit_customer_reff">Customer Reference CMS (Internet Banking)</label>
                  <input type="text" class="form-control font-monospace" id="edit_customer_reff" name="customer_reff" placeholder="Contoh: MTNOVE001">
                  <div class="form-text small">Kode referensi unik untuk format mutasi perbankan CMS BRI / BSI.</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary px-4 shadow">
            <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JAVASCRIPT LOGIC UNTUK MODAL EDIT GAJI -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof $.fn !== 'undefined' && typeof $.fn.dataTable !== 'undefined') {
    $.fn.dataTable.ext.errMode = 'none';
  }

  const editButtons = document.querySelectorAll('.btn-edit-gaji');
  const modalElement = document.getElementById('modalEditGaji');
  const bsModal = new bootstrap.Modal(modalElement);

  editButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      // Ambil data atribut dari tombol edit
      const id = this.getAttribute('data-id');
      const nama = this.getAttribute('data-nama');
      const idpeg = this.getAttribute('data-idpeg');
      const status = this.getAttribute('data-status');
      const wali = this.getAttribute('data-wali');
      const jabatan = this.getAttribute('data-jabatan');
      const tunjab = this.getAttribute('data-tunjab');
      const gapok = this.getAttribute('data-gapok');
      const tunjkhusus = this.getAttribute('data-tunjkhusus');
      const bank = this.getAttribute('data-bank');
      const norek = this.getAttribute('data-norek');
      const anrek = this.getAttribute('data-anrek');
      const cabang = this.getAttribute('data-cabang');
      const reff = this.getAttribute('data-reff');
      const masakerja = this.getAttribute('data-masakerja');

      // Isi nilai form modal
      document.getElementById('edit_pegawai_id').value = id;
      document.getElementById('modal_nama_pegawai').textContent = nama;
      document.getElementById('modal_id_pegawai').textContent = idpeg;
      document.getElementById('modal_masa_kerja').textContent = masakerja;

      document.getElementById('edit_status_kepegawaian').value = status || 'gty';
      document.getElementById('edit_is_wali_kelas').value = wali || 'n';
      document.getElementById('edit_nama_jabatan_struktural').value = jabatan || '';
      document.getElementById('edit_nominal_tunj_struktural').value = tunjab || 0;
      document.getElementById('edit_gaji_pokok_nominal').value = gapok || 500000;
      document.getElementById('edit_tunj_khusus_tetap').value = tunjkhusus || 0;

      document.getElementById('edit_nama_bank').value = bank || 'BRI';
      document.getElementById('edit_nomor_rekening').value = norek || '';
      document.getElementById('edit_nama_pemilik_rekening').value = anrek || nama;
      document.getElementById('edit_cabang_bank').value = cabang || 'Klaten';
      document.getElementById('edit_customer_reff').value = reff || '';

      // Tampilkan modal
      bsModal.show();
    });
  });
});
</script>
