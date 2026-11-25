<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Manajemen Ekstra & Siswa
      </h4>
      <p class="text-muted mb-0 small">
        Tracking status pembayaran administrasi ekstrakurikuler siswa dan otomatisasi penangguhan honor guru pengampu (Carry-Over) sesuai SOP 6, 7, dan 8.
      </p>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalMasterEkstra">
        <i class="ti ti-settings me-1"></i> Master Cabang Ekstra
      </button>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
        <i class="ti ti-user-plus me-1"></i> Tambah Peserta Siswa
      </button>
    </div>
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
              <span class="text-muted small fw-semibold d-block mb-1">Siswa Lunas (SOP 7)</span>
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
              <span class="text-muted small fw-semibold d-block mb-1">Siswa Belum Lunas (SOP 8)</span>
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
      <span class="badge bg-label-info">SOP Poin 7 & 8</span>
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
            <th class="text-center" style="width: 80px;">Aksi</th>
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
                  <a href="<?=base_url('gaji/ekstra/delete_siswa/'.$row['id']);?>" 
                     class="btn btn-xs btn-outline-danger p-1" 
                     onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa <?=htmlspecialchars($row['nama_siswa']);?>?');"
                     title="Hapus Peserta">
                    <i class="ti ti-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php } else { ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="ti ti-users-minus fs-2 d-block mb-1 text-secondary"></i>
                Belum ada data siswa ekstra terdaftar pada periode bulan <?=$bulan;?> / <?=$tahun;?>.
                <div class="mt-2">
                  <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
                    <i class="ti ti-plus me-1"></i> Tambah Siswa Pertama
                  </button>
                </div>
              </td>
            </tr>
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
                Honor langsung dicairkan ke guru di bulan berjalan (SOP 7).
              </label>
            </div>
            <div class="form-check mt-1">
              <input class="form-check-input" type="radio" name="status_bayar" id="sb_pending" value="pending">
              <label class="form-check-label" for="sb_pending">
                <span class="badge bg-warning text-dark me-1">BELUM LUNAS</span> 
                Honor ditangguhkan (*Carry-Over* ke bulan berikutnya saat lunas) (SOP 8).
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

<!-- MODAL MASTER CABANG EKSTRAKURIKULER -->
<div class="modal fade" id="modalMasterEkstra" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title d-flex align-items-center">
          <i class="ti ti-settings me-2 text-primary"></i> Master Cabang Ekstra & Guru Pengampu
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <!-- FORM TAMBAH / UPDATE CABANG -->
        <form action="<?=base_url('gaji/ekstra/save_ekstra');?>" method="POST" class="p-3 bg-light rounded border mb-4">
          <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-plus me-1"></i> Tambah / Perbarui Cabang Ekstra</h6>
          <input type="hidden" name="ekstra_id" id="edit_ekstra_id" value="0">

          <div class="row g-2">
            <div class="col-md-5 col-12">
              <label class="form-label small fw-bold">Nama Cabang Ekstra <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" id="edit_nama_ekstra" name="nama_ekstra" placeholder="Panahan / Sepatu Roda / Renang" required>
            </div>

            <div class="col-md-4 col-12">
              <label class="form-label small fw-bold">Guru Pengampu</label>
              <select class="form-select form-select-sm" id="edit_pegawai_id" name="pegawai_id">
                <option value="">-- Pilih Guru Pengampu --</option>
                <?php foreach ($guru_list as $g) : ?>
                  <option value="<?=$g['pegawai_id'];?>"><?=$g['nama_pegawai'];?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3 col-12">
              <label class="form-label small fw-bold">Tipe Honor</label>
              <select class="form-select form-select-sm" id="edit_tipe_honor" name="tipe_honor">
                <option value="per_anak">Per Anak Lunas</option>
                <option value="per_sesi">Per Sesi</option>
                <option value="per_kedatangan">Per Kedatangan</option>
              </select>
            </div>

            <div class="col-md-6 col-12 mt-2">
              <label class="form-label small fw-bold">Tarif Standar (Rp) <span class="text-danger">*</span></label>
              <input type="number" step="1000" class="form-control form-control-sm" id="edit_tarif_standar" name="tarif_standar" placeholder="Contoh: 45000" required>
            </div>

            <div class="col-md-6 col-12 mt-2">
              <label class="form-label small fw-bold">Keterangan Tambahan</label>
              <input type="text" class="form-control form-control-sm" id="edit_keterangan" name="keterangan" placeholder="Honor per anak...">
            </div>

            <div class="col-12 text-end mt-3">
              <button type="submit" class="btn btn-sm btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Simpan Cabang Ekstra
              </button>
            </div>
          </div>
        </form>

        <!-- TABEL CABANG EKSTRA -->
        <h6 class="fw-bold mb-2">Daftar Cabang Ekstrakurikuler Aktif</h6>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead class="table-light">
              <tr>
                <th>Nama Ekstra</th>
                <th>Guru Pengampu</th>
                <th>Tipe Honor</th>
                <th class="text-end">Tarif Standar</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ekstras as $ek) : ?>
                <tr>
                  <td class="fw-bold text-dark"><?=$ek['nama_ekstra'];?></td>
                  <td><?=$ek['nama_pegawai'] ?: '<span class="text-muted small">Belum Dipetakan</span>';?></td>
                  <td>
                    <span class="badge bg-label-secondary small">
                      <?=str_replace('_', ' ', $ek['tipe_honor']);?>
                    </span>
                  </td>
                  <td class="text-end fw-bold">Rp <?=number_format($ek['tarif_standar'], 0, ',', '.');?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer border-top">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
