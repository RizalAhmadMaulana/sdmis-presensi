<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Manajemen Ekstra & Siswa
      </h4>
      <p class="text-muted mb-0 small">
        Tracking status pembayaran administrasi ekstrakurikuler siswa dan otomatisasi penangguhan honor guru pengampu (Carry-Over).
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?=base_url('gaji/ekstra/cabang');?>" class="btn btn-outline-primary shadow-sm">
        <i class="ti ti-settings me-1"></i> Master Cabang Ekstra
      </a>
      <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
        <i class="ti ti-user-plus me-1"></i> Tambah Peserta Siswa
      </button>
    </div>
  </div>

  <!-- Flash Toast & Modal Konfirmasi -->
  <?php $this->load->view('module/gaji/toast_and_modal'); ?>

  <!-- KPI SUMMARY CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-start border-primary border-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold d-block mb-1">Total Siswa Terdaftar</span>
              <h3 class="card-title mb-0 text-primary fw-bold"><?=$stats['total_siswa'];?> Siswa</h3>
              <small class="text-muted">Bulan <?=$bulan;?> / <?=$tahun;?></small>
            </div>
            <div class="avatar avatar-md bg-label-primary rounded p-2">
              <i class="ti ti-users fs-3"></i>
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
              <span class="text-muted small fw-semibold d-block mb-1">Siswa Lunas</span>
              <h3 class="card-title mb-0 text-success fw-bold"><?=$stats['total_lunas'];?> Siswa</h3>
              <small class="text-success"><i class="ti ti-check me-1"></i>Honor Masuk Mukafaah</small>
            </div>
            <div class="avatar avatar-md bg-label-success rounded p-2">
              <i class="ti ti-circle-check fs-3"></i>
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
              <span class="text-muted small fw-semibold d-block mb-1">Siswa Belum Lunas</span>
              <h3 class="card-title mb-0 text-warning fw-bold"><?=$stats['total_pending'];?> Siswa</h3>
              <small class="text-warning"><i class="ti ti-clock me-1"></i>Honor Ditangguhkan</small>
            </div>
            <div class="avatar avatar-md bg-label-warning rounded p-2">
              <i class="ti ti-clock-pause fs-3"></i>
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
              <span class="text-muted small fw-semibold d-block mb-1">Honor Siap Cair</span>
              <h3 class="card-title mb-0 text-info fw-bold">Rp <?=number_format($stats['nominal_cair'], 0, ',', '.');?></h3>
              <small class="text-muted">Tertahan: Rp <?=number_format($stats['nominal_pending'], 0, ',', '.');?></small>
            </div>
            <div class="avatar avatar-md bg-label-info rounded p-2">
              <i class="ti ti-wallet fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FILTER TOOLBAR -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
      <form action="<?=base_url('gaji/ekstra');?>" method="GET" class="row g-2 align-items-center">
        <!-- Filter Cabang Ekstra -->
        <div class="col-md-4 col-12">
          <label class="form-label small fw-bold mb-1">Cabang Ekstrakurikuler</label>
          <select class="form-select form-select-sm" name="ekstra_id">
            <option value="">-- Semua Cabang Ekstra --</option>
            <?php foreach ($ekstras as $ek) : ?>
              <option value="<?=$ek['ekstra_id'];?>" <?=$selected_ekstra_id == $ek['ekstra_id'] ? 'selected' : '';?>>
                <?=$ek['nama_ekstra'];?> (Pengampu: <?=$ek['nama_pegawai'] ?: 'Belum Diatur';?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Bulan -->
        <div class="col-md-3 col-6">
          <label class="form-label small fw-bold mb-1">Bulan Periode</label>
          <select class="form-select form-select-sm" name="bulan">
            <?php 
              $nama_bulan = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
              foreach ($nama_bulan as $m_num => $m_name) : 
            ?>
              <option value="<?=$m_num;?>" <?=$bulan == $m_num ? 'selected' : '';?>><?=$m_name;?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Tahun -->
        <div class="col-md-3 col-6">
          <label class="form-label small fw-bold mb-1">Tahun</label>
          <select class="form-select form-select-sm" name="tahun">
            <?php for ($y = 2024; $y <= 2028; $y++) : ?>
              <option value="<?=$y;?>" <?=$tahun == $y ? 'selected' : '';?>><?=$y;?></option>
            <?php endfor; ?>
          </select>
        </div>

        <!-- Tombol Terapkan Filter -->
        <div class="col-md-2 col-12 text-end mt-md-4">
          <button type="submit" class="btn btn-sm btn-primary w-100">
            <i class="ti ti-filter me-1"></i> Filter Data
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- TABEL PESERTA SISWA EKSTRAKURIKULER -->
  <div class="card shadow-sm">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="ti ti-checklist me-2 text-primary"></i> Checklist Pelunasan Iuran Ekstra Siswa
        </h5>
        <small class="text-muted">Klik tombol status pelunasan untuk mengubah status Lunas atau Belum Lunas secara langsung.</small>
      </div>
      <?php if (empty($siswas)) { ?>
        <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
          <i class="ti ti-plus me-1"></i> Tambah Siswa Pertama
        </button>
      <?php } ?>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover border-top" id="dataTable">
        <thead class="table-light">
          <tr>
            <th style="width: 40px;">No</th>
            <th>Siswa & Kelas</th>
            <th>Cabang Ekstra</th>
            <th>Guru Pengampu</th>
            <th class="text-end">Tarif Tagihan</th>
            <th class="text-center">Status Pelunasan</th>
            <th class="text-center">Tanggal Bayar</th>
            <th class="text-center" style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($siswas)) { ?>
            <?php $no = 1; foreach ($siswas as $row) : ?>
              <tr>
                <td class="text-center fw-bold"><?=$no++;?></td>
                
                <td>
                  <span class="fw-bold text-dark d-block"><?=htmlspecialchars($row['nama_siswa']);?></span>
                  <small class="text-muted">Kelas: <?=htmlspecialchars($row['kelas'] ?: '-');?></small>
                </td>

                <td>
                  <span class="badge bg-label-primary px-2 py-1 fw-bold">
                    <?=htmlspecialchars($row['nama_ekstra']);?>
                  </span>
                </td>

                <td>
                  <span class="text-dark small fw-semibold">
                    <?=htmlspecialchars($row['nama_guru_pengampu'] ?: 'Belum Dipetakan');?>
                  </span>
                </td>

                <td class="text-end fw-bold text-dark">
                  Rp <?=number_format($row['nominal_tagihan'] > 0 ? $row['nominal_tagihan'] : $row['tarif_standar'], 0, ',', '.');?>
                </td>

                <!-- Status Pelunasan dengan Tombol Toggle -->
                <td class="text-center align-middle">
                  <?php if ($row['status_bayar'] === 'lunas') { ?>
                    <a href="<?=base_url('gaji/ekstra/toggle_status/'.$row['id'].'?bulan='.$bulan.'&tahun='.$tahun.'&ekstra_id='.$selected_ekstra_id);?>" 
                       class="btn btn-xs btn-success rounded-pill px-3 py-1 shadow-sm"
                       title="Klik untuk mengubah menjadi Belum Lunas">
                      <i class="ti ti-check me-1"></i> LUNAS (Cair)
                    </a>
                  <?php } else { ?>
                    <a href="<?=base_url('gaji/ekstra/toggle_status/'.$row['id'].'?bulan='.$bulan.'&tahun='.$tahun.'&ekstra_id='.$selected_ekstra_id);?>" 
                       class="btn btn-xs btn-warning rounded-pill px-3 py-1 text-white shadow-sm"
                       title="Klik untuk menandai Lunas">
                      <i class="ti ti-clock me-1"></i> BELUM LUNAS
                    </a>
                    <div class="text-muted small mt-1" style="font-size: 10px;">Carry-Over</div>
                  <?php } ?>
                </td>

                <td class="text-center small text-muted">
                  <?=$row['tanggal_bayar'] ? date('d/m/Y', strtotime($row['tanggal_bayar'])) : '-';?>
                </td>

                <td class="text-center align-middle">
                  <div class="d-inline-flex align-items-center justify-content-center gap-1">
                    <button type="button" 
                            class="btn btn-sm btn-icon btn-label-primary rounded-pill btn-edit-siswa"
                            data-id="<?=$row['id'];?>"
                            data-ekstra="<?=$row['ekstra_id'];?>"
                            data-nama="<?=htmlspecialchars($row['nama_siswa']);?>"
                            data-kelas="<?=htmlspecialchars($row['kelas'] ?: '');?>"
                            data-tagihan="<?=$row['nominal_tagihan'] > 0 ? $row['nominal_tagihan'] : $row['tarif_standar'];?>"
                            data-bulan="<?=$row['bulan'];?>"
                            data-tahun="<?=$row['tahun'];?>"
                            data-status="<?=$row['status_bayar'];?>"
                            data-tglbayar="<?=$row['tanggal_bayar'] ?: '';?>"
                            data-ket="<?=htmlspecialchars($row['keterangan'] ?: '');?>"
                            title="Edit Siswa">
                      <i class="ti ti-edit"></i>
                    </button>
                    <button type="button" 
                            class="btn btn-sm btn-icon btn-label-danger rounded-pill btn-confirm-delete" 
                            data-url="<?=base_url('gaji/ekstra/delete_siswa/'.$row['id'].'?bulan='.$bulan.'&tahun='.$tahun.'&ekstra_id='.$selected_ekstra_id);?>" 
                            data-title="Hapus Peserta Siswa?"
                            data-text="Apakah Anda yakin ingin menghapus data siswa <b><?=htmlspecialchars($row['nama_siswa']);?></b>?"
                            title="Hapus Siswa">
                      <i class="ti ti-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL TAMBAH PESERTA SISWA -->
<div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title d-flex align-items-center">
          <i class="ti ti-user-plus me-2 text-primary"></i> Tambah Peserta Ekstrakurikuler Siswa
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?=base_url('gaji/ekstra/add_siswa');?>" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold" for="input_ekstra_id">Cabang Ekstrakurikuler <span class="text-danger">*</span></label>
            <select class="form-select" id="input_ekstra_id" name="ekstra_id" required>
              <option value="">-- Pilih Cabang Ekstra --</option>
              <?php foreach ($ekstras as $ek) : ?>
                <option value="<?=$ek['ekstra_id'];?>" data-tarif="<?=$ek['tarif_standar'];?>">
                  <?=$ek['nama_ekstra'];?> (Tarif: Rp <?=number_format($ek['tarif_standar'], 0, ',', '.');?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="input_nama_siswa">Nama Lengkap Siswa <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="input_nama_siswa" name="nama_siswa" placeholder="Contoh: Muhammad Al-Fatih" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="input_kelas">Kelas</label>
              <input type="text" class="form-control" id="input_kelas" name="kelas" placeholder="Contoh: 3A / 4B">
            </div>

            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="input_nominal_tagihan">Nominal Tagihan (Rp)</label>
              <input type="number" step="1000" class="form-control" id="input_nominal_tagihan" name="nominal_tagihan" placeholder="Default tarif cabang">
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="input_bulan">Bulan Periode</label>
              <select class="form-select" id="input_bulan" name="bulan">
                <?php foreach ($nama_bulan as $m_num => $m_name) : ?>
                  <option value="<?=$m_num;?>" <?=$bulan == $m_num ? 'selected' : '';?>><?=$m_name;?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="input_tahun">Tahun</label>
              <input type="number" class="form-control" id="input_tahun" name="tahun" value="<?=$tahun;?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Status Pembayaran Administrasi</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="status_bayar" id="sb_lunas" value="lunas" checked>
              <label class="form-check-label" for="sb_lunas">
                <span class="badge bg-success me-1">LUNAS</span> 
                Honor langsung dicairkan ke guru di bulan berjalan.
              </label>
            </div>
            <div class="form-check mt-1">
              <input class="form-check-input" type="radio" name="status_bayar" id="sb_pending" value="pending">
              <label class="form-check-label" for="sb_pending">
                <span class="badge bg-warning text-dark me-1">BELUM LUNAS</span> 
                Honor ditangguhkan (*Carry-Over* ke bulan berikutnya saat lunas).
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-plus me-1"></i> Simpan Siswa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT PESERTA SISWA -->
<div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title d-flex align-items-center">
          <i class="ti ti-edit me-2 text-primary"></i> Edit Data Peserta Siswa Ekstra
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?=base_url('gaji/ekstra/edit_siswa');?>" method="POST">
        <input type="hidden" name="id" id="edit_siswa_id">

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold" for="edit_siswa_ekstra_id">Cabang Ekstrakurikuler <span class="text-danger">*</span></label>
            <select class="form-select" id="edit_siswa_ekstra_id" name="ekstra_id" required>
              <?php foreach ($ekstras as $ek) : ?>
                <option value="<?=$ek['ekstra_id'];?>"><?=$ek['nama_ekstra'];?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="edit_siswa_nama">Nama Lengkap Siswa <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_siswa_nama" name="nama_siswa" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="edit_siswa_kelas">Kelas</label>
              <input type="text" class="form-control" id="edit_siswa_kelas" name="kelas">
            </div>

            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="edit_siswa_tagihan">Nominal Tagihan (Rp)</label>
              <input type="number" step="1000" class="form-control" id="edit_siswa_tagihan" name="nominal_tagihan" required>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="edit_siswa_bulan">Bulan Periode</label>
              <select class="form-select" id="edit_siswa_bulan" name="bulan">
                <?php foreach ($nama_bulan as $m_num => $m_name) : ?>
                  <option value="<?=$m_num;?>"><?=$m_name;?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6 col-12">
              <label class="form-label fw-bold" for="edit_siswa_tahun">Tahun</label>
              <input type="number" class="form-control" id="edit_siswa_tahun" name="tahun" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="edit_siswa_status">Status Pembayaran</label>
            <select class="form-select" id="edit_siswa_status" name="status_bayar">
              <option value="lunas">LUNAS (Cair)</option>
              <option value="pending">BELUM LUNAS (Carry-Over)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="edit_siswa_keterangan">Keterangan Tambahan</label>
            <input type="text" class="form-control" id="edit_siswa_keterangan" name="keterangan" placeholder="Catatan...">
          </div>
        </div>

        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan Siswa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Matikan alert popup bawaan DataTables tn18
  if (typeof $.fn !== 'undefined' && typeof $.fn.dataTable !== 'undefined') {
    $.fn.dataTable.ext.errMode = 'none';
  }

  // Edit Siswa Logic
  const editSiswaBtns = document.querySelectorAll('.btn-edit-siswa');
  const modalEditSiswaEl = document.getElementById('modalEditSiswa');
  const bsModalEditSiswa = new bootstrap.Modal(modalEditSiswaEl);

  editSiswaBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      document.getElementById('edit_siswa_id').value = this.getAttribute('data-id');
      document.getElementById('edit_siswa_ekstra_id').value = this.getAttribute('data-ekstra');
      document.getElementById('edit_siswa_nama').value = this.getAttribute('data-nama');
      document.getElementById('edit_siswa_kelas').value = this.getAttribute('data-kelas');
      document.getElementById('edit_siswa_tagihan').value = this.getAttribute('data-tagihan');
      document.getElementById('edit_siswa_bulan').value = this.getAttribute('data-bulan');
      document.getElementById('edit_siswa_tahun').value = this.getAttribute('data-tahun');
      document.getElementById('edit_siswa_status').value = this.getAttribute('data-status');
      document.getElementById('edit_siswa_keterangan').value = this.getAttribute('data-ket');
      bsModalEditSiswa.show();
    });
  });
});
</script>
