<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs dengan Tombol Kembali -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <a href="<?=base_url('gaji/ekstra');?>" class="text-muted fw-light">Manajemen Ekstra & Siswa /</a> Master Cabang Ekstra
      </h4>
      <p class="text-muted mb-0 small">
        Kelola cabang ekstrakurikuler sekolah, penugasan guru pengampu, serta tarif honor standar.
      </p>
    </div>
    <div>
      <a href="<?=base_url('gaji/ekstra');?>" class="btn btn-outline-secondary shadow-sm">
        <i class="ti ti-arrow-left me-1"></i> Kembali ke Manajemen Ekstra & Siswa
      </a>
    </div>
  </div>

  <!-- Flash Toast & Modal Konfirmasi Hapus -->
  <?php $this->load->view('module/gaji/toast_and_modal'); ?>

  <!-- 2-COLUMN LAYOUT: FORM INPUT (KIRI) & TABEL DAFTAR CABANG (KANAN) -->
  <div class="row g-4">
    <!-- KOLOM KIRI: FORM TAMBAH / UPDATE CABANG EKSTRA -->
    <div class="col-lg-5 col-12">
      <div class="card border border-primary shadow-sm" id="cardFormMasterEkstra">
        <div class="card-header bg-light py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
          <h5 id="title_form_ekstra" class="fw-bold mb-0 text-primary d-flex align-items-center">
            <i class="ti ti-plus me-2 fs-4"></i> Tambah Cabang Baru
          </h5>
          <button type="button" id="btn_cancel_edit_ekstra" class="btn btn-xs btn-label-secondary rounded-pill d-none">
            <i class="ti ti-x me-1"></i> Batal Edit
          </button>
        </div>

        <div class="card-body p-4">
          <form id="formMasterEkstra" action="<?=base_url('gaji/ekstra/save_ekstra');?>" method="POST">
            <input type="hidden" name="ekstra_id" id="edit_ekstra_id" value="0">

            <div class="mb-3">
              <label class="form-label small fw-bold">Nama Cabang Ekstrakurikuler <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti ti-activity"></i></span>
                <input type="text" class="form-control" id="edit_nama_ekstra" name="nama_ekstra" placeholder="Contoh: Panahan / Renang / Robotik" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Guru Pengampu</label>
              <select class="form-select" id="edit_pegawai_id" name="pegawai_id">
                <option value="">-- Belum Dipetakan --</option>
                <?php foreach ($guru_list as $g) : ?>
                  <option value="<?=$g['pegawai_id'];?>"><?=$g['nama_pegawai'];?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6 col-12">
                <label class="form-label small fw-bold">Tipe Honor</label>
                <select class="form-select" id="edit_tipe_honor" name="tipe_honor">
                  <option value="per_anak">Per Anak Lunas</option>
                  <option value="per_sesi">Per Sesi</option>
                  <option value="per_kedatangan">Per Kedatangan</option>
                </select>
              </div>

              <div class="col-md-6 col-12">
                <label class="form-label small fw-bold">Tarif Standar (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text small">Rp</span>
                  <input type="number" step="1000" class="form-control fw-bold" id="edit_tarif_standar" name="tarif_standar" placeholder="45000" required>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold">Keterangan Tambahan</label>
              <input type="text" class="form-control" id="edit_keterangan" name="keterangan" placeholder="Catatan honor per anak / jadwal...">
            </div>

            <button type="submit" id="btn_submit_ekstra" class="btn btn-primary w-100 shadow py-2 fs-6">
              <i class="ti ti-device-floppy me-1"></i> Simpan Cabang Ekstra
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- KOLOM KANAN: DAFTAR CABANG AKTIF -->
    <div class="col-lg-7 col-12">
      <div class="card border shadow-sm">
        <div class="card-header bg-light py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
          <div>
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
              <i class="ti ti-list-check me-2 text-primary fs-4"></i> Daftar Cabang Ekstrakurikuler
            </h5>
            <small class="text-muted">Daftar cabang aktif yang tersedia untuk pendaftaran siswa.</small>
          </div>
          <span class="badge bg-label-primary fs-6"><?=count($ekstras);?> Cabang</span>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>Cabang & Honor</th>
                  <th>Guru Pengampu</th>
                  <th class="text-end">Tarif Standar</th>
                  <th class="text-center" style="width: 110px; min-width: 110px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($ekstras)) { ?>
                  <?php foreach ($ekstras as $ek) : ?>
                    <tr>
                      <td>
                        <span class="fw-bold text-dark d-block fs-6"><?=htmlspecialchars($ek['nama_ekstra']);?></span>
                        <span class="badge bg-label-secondary small" style="font-size: 11px;">
                          <?=str_replace('_', ' ', $ek['tipe_honor']);?>
                        </span>
                      </td>
                      <td>
                        <div class="small fw-semibold text-dark">
                          <i class="ti ti-user me-1 text-primary"></i>
                          <?=$ek['nama_pegawai'] ?: '<span class="text-muted">Belum Dipetakan</span>';?>
                        </div>
                      </td>
                      <td class="text-end fw-bold text-primary fs-6">
                        Rp <?=number_format($ek['tarif_standar'], 0, ',', '.');?>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1">
                          <button type="button" 
                                  class="btn btn-sm btn-icon btn-label-primary rounded-pill btn-edit-ekstra"
                                  data-id="<?=$ek['ekstra_id'];?>"
                                  data-nama="<?=htmlspecialchars($ek['nama_ekstra']);?>"
                                  data-pegawai="<?=$ek['pegawai_id'] ?: '';?>"
                                  data-tipe="<?=$ek['tipe_honor'];?>"
                                  data-tarif="<?=$ek['tarif_standar'];?>"
                                  data-ket="<?=htmlspecialchars($ek['keterangan'] ?: '');?>"
                                  title="Edit Cabang">
                            <i class="ti ti-edit"></i>
                          </button>
                          <button type="button"
                                  class="btn btn-sm btn-icon btn-label-danger rounded-pill btn-confirm-delete"
                                  data-url="<?=base_url('gaji/ekstra/delete_ekstra/'.$ek['ekstra_id']);?>"
                                  data-title="Hapus Cabang Ekstra?"
                                  data-text="Apakah Anda yakin ingin menghapus cabang <b><?=htmlspecialchars($ek['nama_ekstra']);?></b>? Seluruh data peserta siswa terkait juga akan ikut terhapus."
                                  title="Hapus Cabang">
                            <i class="ti ti-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                      <i class="ti ti-folder-off fs-1 d-block mb-2 text-secondary"></i>
                      Belum ada cabang ekstrakurikuler yang didaftarkan.
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Edit Cabang Ekstra Logic
  const editEkstraBtns = document.querySelectorAll('.btn-edit-ekstra');
  const cancelEditBtn = document.getElementById('btn_cancel_edit_ekstra');
  const cardForm = document.getElementById('cardFormMasterEkstra');

  editEkstraBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      document.getElementById('edit_ekstra_id').value = this.getAttribute('data-id');
      document.getElementById('edit_nama_ekstra').value = this.getAttribute('data-nama');
      document.getElementById('edit_pegawai_id').value = this.getAttribute('data-pegawai');
      document.getElementById('edit_tipe_honor').value = this.getAttribute('data-tipe');
      document.getElementById('edit_tarif_standar').value = this.getAttribute('data-tarif');
      document.getElementById('edit_keterangan').value = this.getAttribute('data-ket');

      document.getElementById('title_form_ekstra').innerHTML = '<i class="ti ti-edit me-2 fs-4 text-warning"></i> Edit: ' + this.getAttribute('data-nama');
      document.getElementById('btn_submit_ekstra').innerHTML = '<i class="ti ti-check me-1"></i> Update Cabang Ekstra';
      if (cardForm) {
        cardForm.classList.remove('border-primary');
        cardForm.classList.add('border-warning');
      }
      cancelEditBtn.classList.remove('d-none');

      // Scroll halus ke form jika di layar kecil/mobile
      cardForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  if (cancelEditBtn) {
    cancelEditBtn.addEventListener('click', function() {
      document.getElementById('formMasterEkstra').reset();
      document.getElementById('edit_ekstra_id').value = '0';
      document.getElementById('title_form_ekstra').innerHTML = '<i class="ti ti-plus me-2 fs-4"></i> Tambah Cabang Baru';
      document.getElementById('btn_submit_ekstra').innerHTML = '<i class="ti ti-device-floppy me-1"></i> Simpan Cabang Ekstra';
      if (cardForm) {
        cardForm.classList.add('border-primary');
        cardForm.classList.remove('border-warning');
      }
      this.classList.add('d-none');
    });
  }
});
</script>
