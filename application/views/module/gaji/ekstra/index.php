<?php
$nama_bulan_list = [
  1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
  5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
  9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$nama_bulan_pendek = [
  1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
  5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
  9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];
$label_bulan_aktif = ($nama_bulan_list[$bulan] ?? $bulan) . ' ' . $tahun;
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Manajemen Ekstra &amp; Honor Pengampu
      </h4>
      <p class="text-muted mb-0 small">
        Input cepat rekap ekstra per anak, pelacakan tunggakan siswa (<i>Carry-Over</i>), serta log sesi &amp; kedatangan guru pengampu.
      </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="<?=base_url('gaji/ekstra/cabang');?>" class="btn btn-outline-primary shadow-sm">
        <i class="ti ti-settings me-1"></i> Master Cabang Ekstra
      </a>
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
              <span class="text-muted small fw-semibold d-block mb-1">Anak Ekstra Lunas (Tab 1)</span>
              <h3 class="card-title mb-0 text-primary fw-bold"><?=$stats['total_anak_cair'];?> Anak</h3>
              <small class="text-muted">Rekap: <?=$stats['total_anak_rekap'];?> | Carry-Over: +<?=$stats['total_carry_over'];?></small>
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
              <span class="text-muted small fw-semibold d-block mb-1">Sesi &amp; Kedatangan (Tab 2)</span>
              <h3 class="card-title mb-0 text-info fw-bold"><?=$stats['total_sesi_disetujui'];?> Sesi</h3>
              <small class="text-info fw-medium">Honor Sesi: Rp <?=number_format($stats['honor_sesi_cair'], 0, ',', '.');?></small>
            </div>
            <div class="avatar avatar-md bg-label-info rounded p-2">
              <i class="ti ti-calendar-check fs-3"></i>
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
              <span class="text-muted small fw-semibold d-block mb-1">Tunggakan Belum Lunas</span>
              <h3 class="card-title mb-0 text-warning fw-bold"><?=$stats['total_tunggakan'];?> Siswa</h3>
              <small class="text-warning"><i class="ti ti-clock me-1"></i>Ditahan: Rp <?=number_format($stats['nominal_tunggakan'], 0, ',', '.');?></small>
            </div>
            <div class="avatar avatar-md bg-label-warning rounded p-2">
              <i class="ti ti-clock-pause fs-3"></i>
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
              <span class="text-muted small fw-semibold d-block mb-1">Total Honor Ekstra Cair</span>
              <h3 class="card-title mb-0 text-success fw-bold">Rp <?=number_format($stats['total_honor_cair'], 0, ',', '.');?></h3>
              <small class="text-success"><i class="ti ti-check me-1"></i>Masuk Mukafaah <?=$nama_bulan_pendek[$bulan] ?? $bulan;?> <?=$tahun;?></small>
            </div>
            <div class="avatar avatar-md bg-label-success rounded p-2">
              <i class="ti ti-wallet fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FILTER PERIODE & CABANG EKSTRA -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3">
      <form action="<?=base_url('gaji/ekstra');?>" method="GET" class="row g-3 align-items-end">
        <input type="hidden" name="tab" id="filter_active_tab" value="<?=htmlspecialchars($active_tab);?>">
        <div class="col-md-5">
          <label class="form-label fw-semibold small text-muted">Filter Cabang Ekstrakurikuler</label>
          <select class="form-select" name="ekstra_id">
            <option value="">-- Semua Cabang Ekstra --</option>
            <?php if (!empty($ekstras_per_anak)): ?>
              <optgroup label="Tab 1: Ekstra Per Anak">
                <?php foreach ($ekstras_per_anak as $ek): ?>
                  <option value="<?=$ek['ekstra_id'];?>" <?=$selected_ekstra_id == $ek['ekstra_id'] ? 'selected' : '';?>>
                    <?=htmlspecialchars($ek['nama_ekstra']);?> (<?=htmlspecialchars($ek['nama_pegawai'] ?: 'Belum Diset');?>)
                  </option>
                <?php endforeach; ?>
              </optgroup>
            <?php endif; ?>
            <?php if (!empty($ekstras_per_sesi)): ?>
              <optgroup label="Tab 2: Ekstra Per Sesi / Kedatangan">
                <?php foreach ($ekstras_per_sesi as $ek): ?>
                  <option value="<?=$ek['ekstra_id'];?>" <?=$selected_ekstra_id == $ek['ekstra_id'] ? 'selected' : '';?>>
                    <?=htmlspecialchars($ek['nama_ekstra']);?> (<?=htmlspecialchars($ek['nama_pegawai'] ?: 'Belum Diset');?>)
                  </option>
                <?php endforeach; ?>
              </optgroup>
            <?php endif; ?>
          </select>
        </div>

        <div class="col-6 col-md-3">
          <label class="form-label fw-semibold small text-muted">Bulan Periode</label>
          <select class="form-select" name="bulan">
            <?php foreach ($nama_bulan_list as $bln_num => $bln_name): ?>
              <option value="<?=$bln_num;?>" <?=$bulan == $bln_num ? 'selected' : '';?>><?=$bln_name;?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-6 col-md-2">
          <label class="form-label fw-semibold small text-muted">Tahun</label>
          <select class="form-select" name="tahun">
            <?php $cur_y = (int)date('Y'); for ($y = $cur_y - 1; $y <= $cur_y + 2; $y++): ?>
              <option value="<?=$y;?>" <?=$tahun == $y ? 'selected' : '';?>><?=$y;?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100 shadow-sm">
            <i class="ti ti-filter me-1"></i> Tampilkan
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- NAVIGASI 2-TAB EKSTRAKURIKULER -->
  <ul class="nav nav-pills mb-4 gap-2" id="ekstraTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link px-4 py-2 fw-semibold shadow-sm <?=$active_tab === 'per_anak' ? 'active' : 'bg-white text-dark border';?>"
              id="tab-btn-per-anak"
              data-bs-toggle="tab"
              data-bs-target="#pane-per-anak"
              data-tab-name="per_anak"
              type="button" role="tab">
        <i class="ti ti-users me-2"></i> Tab 1: Ekstra Per Anak &amp; Tunggakan Siswa
        <span class="badge rounded-pill ms-2 <?=$active_tab === 'per_anak' ? 'bg-white text-primary' : 'bg-label-primary';?>">
          <?=count($rekap_anak);?> Cabang
        </span>
        <?php if ($stats['total_tunggakan'] > 0): ?>
          <span class="badge rounded-pill bg-warning text-dark ms-1"><?=$stats['total_tunggakan'];?> Tunggakan</span>
        <?php endif; ?>
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link px-4 py-2 fw-semibold shadow-sm <?=$active_tab === 'per_sesi' ? 'active' : 'bg-white text-dark border';?>"
              id="tab-btn-per-sesi"
              data-bs-toggle="tab"
              data-bs-target="#pane-per-sesi"
              data-tab-name="per_sesi"
              type="button" role="tab">
        <i class="ti ti-calendar-stats me-2"></i> Tab 2: Rekap Sesi &amp; Kedatangan Guru
        <span class="badge rounded-pill ms-2 <?=$active_tab === 'per_sesi' ? 'bg-white text-primary' : 'bg-label-info';?>">
          <?=count($sesi_list);?> Catatan
        </span>
      </button>
    </li>
  </ul>

  <!-- ISI TAB PANELS -->
  <div class="tab-content p-0">

    <!-- =================================================================== -->
    <!-- TAB 1: EKSTRA PER ANAK (BLOK A REKAP CEPAT + BLOK B TUNGGAKAN)      -->
    <!-- =================================================================== -->
    <div class="tab-pane fade <?=$active_tab === 'per_anak' ? 'show active' : '';?>" id="pane-per-anak" role="tabpanel">

      <!-- BLOK A: INPUT CEPAT REKAP JUMLAH ANAK LUNAS -->
      <div class="card shadow-sm border-0 mb-4">
        <form action="<?=base_url('gaji/ekstra/save_rekap_anak');?>" method="POST" id="formRekapAnak">
          <input type="hidden" name="bulan" value="<?=$bulan;?>">
          <input type="hidden" name="tahun" value="<?=$tahun;?>">
          <input type="hidden" name="filter_ekstra_id" value="<?=htmlspecialchars((string)$selected_ekstra_id);?>">

          <div class="card-header bg-transparent border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <div>
              <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="ti ti-bolt me-1"></i> Blok A: Input Cepat Jumlah Anak Lunas (<?=$label_bulan_aktif;?>)
              </h5>
              <small class="text-muted">Ketik angka jumlah siswa lunas bulan ini per cabang tanpa perlu menginput nama anak satu per satu.</small>
            </div>
            <?php if (!empty($rekap_anak)): ?>
              <button type="submit" class="btn btn-primary shadow-sm">
                <i class="ti ti-device-floppy me-1"></i> Simpan Rekap Lunas
              </button>
            <?php endif; ?>
          </div>

          <div class="card-body p-0">
            <?php if (!empty($rekap_anak)): ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="4%" class="text-center">No</th>
                      <th width="20%">Cabang Ekstra</th>
                      <th width="16%">Guru Pengampu</th>
                      <th width="11%" class="text-end">Tarif / Anak</th>
                      <th width="14%" class="text-center">Jml Anak Lunas</th>
                      <th width="15%">Carry-Over (Blok B)</th>
                      <th width="12%" class="text-end">Total Honor Cair</th>
                      <th width="12%">Catatan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $no = 1; foreach ($rekap_anak as $rk): ?>
                      <tr class="rekap-row"
                          data-tarif="<?=$rk['tarif_satuan'];?>"
                          data-carry-nom="<?=$rk['carry_over_nominal'];?>">
                        <td class="text-center text-muted"><?=$no++;?></td>
                        <td>
                          <span class="fw-bold text-dark d-block"><?=htmlspecialchars($rk['nama_ekstra']);?></span>
                          <?php if ($rk['pending_count'] > 0): ?>
                            <small class="text-warning fw-medium">
                              <i class="ti ti-alert-triangle me-1"></i><?=$rk['pending_count'];?> tunggakan (Rp <?=number_format($rk['pending_nominal'], 0, ',', '.');?>)
                            </small>
                          <?php else: ?>
                            <small class="text-muted">Tipe: Per Anak Lunas</small>
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="text-dark fw-medium"><?=htmlspecialchars($rk['nama_pegawai'] ?: 'Belum Diset');?></span>
                        </td>
                        <td class="text-end fw-semibold text-muted">
                          Rp <?=number_format($rk['tarif_satuan'], 0, ',', '.');?>
                        </td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="number"
                                   class="form-control text-center fw-bold input-rekap-jml"
                                   name="rekap[<?=$rk['ekstra_id'];?>][jumlah_lunas]"
                                   value="<?=$rk['jumlah_lunas'];?>"
                                   min="0"
                                   step="1"
                                   required>
                            <span class="input-group-text">anak</span>
                          </div>
                        </td>
                        <td>
                          <?php if ($rk['carry_over_count'] > 0): ?>
                            <span class="badge bg-label-success fw-semibold">
                              +<?=$rk['carry_over_count'];?> siswa (Rp <?=number_format($rk['carry_over_nominal'], 0, ',', '.');?>)
                            </span>
                            <small class="d-block text-muted text-truncate" style="max-width: 170px;" title="<?=htmlspecialchars($rk['carry_over_names']);?>">
                              <?=htmlspecialchars($rk['carry_over_names']);?>
                            </small>
                          <?php else: ?>
                            <span class="text-muted small">0 pelunasan</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-end fw-bold text-success fs-6 cell-rekap-total">
                          Rp <?=number_format($rk['total_honor_cair'], 0, ',', '.');?>
                        </td>
                        <td>
                          <input type="text"
                                 class="form-control form-control-sm"
                                 name="rekap[<?=$rk['ekstra_id'];?>][keterangan]"
                                 value="<?=htmlspecialchars((string)$rk['keterangan_rekap']);?>"
                                 placeholder="Keterangan...">
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <th colspan="6" class="text-end fw-bold text-dark">Total Honor Ekstra Per Anak Cair (<?=$label_bulan_aktif;?>):</th>
                      <th class="text-end fw-bold text-success fs-6" id="total-rekap-anak-cair">
                        Rp <?=number_format($stats['honor_anak_cair'], 0, ',', '.');?>
                      </th>
                      <th></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div class="card-footer bg-transparent border-top d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                <small class="text-muted">
                  <i class="ti ti-info-circle me-1 text-primary"></i>
                  Setelah menyimpan Rekap atau melunasi Tunggakan, klik <b>⚡ HITUNG OTOMATIS DARI PRESENSI</b> di menu Periode Gaji untuk menarik nilai honor terbaru ke slip guru.
                </small>
                <button type="submit" class="btn btn-primary btn-sm px-3">
                  <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan Rekap
                </button>
              </div>
            <?php else: ?>
              <div class="text-center py-4 px-3">
                <p class="text-muted mb-0">Belum ada cabang ekstra bertipe <b>Per Anak</b> yang sesuai filter.</p>
              </div>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- BLOK B: DAFTAR TUNGGAKAN SISWA BELUM LUNAS & CARRY-OVER -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
          <div>
            <h5 class="card-title mb-0 fw-bold">
              <i class="ti ti-list-details me-1 text-warning"></i> Blok B: Daftar Tunggakan Siswa Belum Lunas (Piutang &amp; Carry-Over)
            </h5>
            <small class="text-muted">Khusus mencatat siswa yang <b>belum bayar</b> agar honor guru ditangguhkan sampai tombol <b>Lunasi</b> diklik pada bulan pencairan.</small>
          </div>
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group input-group-sm" style="width: 210px;">
              <span class="input-group-text"><i class="ti ti-search"></i></span>
              <input type="text" class="form-control" id="searchTunggakan" placeholder="Cari siswa / cabang...">
            </div>
            <button type="button" class="btn btn-warning text-dark fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
              <i class="ti ti-user-plus me-1"></i> Catat Tunggakan Siswa
            </button>
          </div>
        </div>

        <div class="card-body">
          <?php if (!empty($tunggakan_list)): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" id="tblTunggakan">
                <thead class="table-light">
                  <tr>
                    <th width="4%">No</th>
                    <th>Siswa &amp; Kelas</th>
                    <th>Cabang Ekstra &amp; Pengampu</th>
                    <th>Bulan Tunggakan</th>
                    <th class="text-end">Nominal</th>
                    <th class="text-center">Status &amp; Pencairan</th>
                    <th width="16%" class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $no = 1; foreach ($tunggakan_list as $s):
                    $nominal = (float)$s['nominal_tagihan'] > 0 ? (float)$s['nominal_tagihan'] : (float)$s['tarif_standar'];
                    $is_lunas = ($s['status_bayar'] === 'lunas');
                    $bln_cair_txt = !empty($s['bulan_cair']) ? ($nama_bulan_pendek[(int)$s['bulan_cair']] ?? $s['bulan_cair']) . ' ' . $s['tahun_cair'] : $label_bulan_aktif;
                  ?>
                    <tr class="row-tunggakan">
                      <td class="text-muted"><?=$no++;?></td>
                      <td>
                        <span class="fw-bold text-dark d-block"><?=htmlspecialchars($s['nama_siswa']);?></span>
                        <small class="text-muted">Kelas: <?=htmlspecialchars($s['kelas'] ?: '-');?><?= !empty($s['keterangan']) ? ' • ' . htmlspecialchars($s['keterangan']) : ''; ?></small>
                      </td>
                      <td>
                        <span class="badge bg-label-primary mb-1"><?=htmlspecialchars($s['nama_ekstra']);?></span>
                        <small class="d-block text-muted"><?=htmlspecialchars($s['nama_guru_pengampu'] ?: '-');?></small>
                      </td>
                      <td>
                        <span class="fw-medium text-dark"><?=$nama_bulan_pendek[(int)$s['bulan']] ?? $s['bulan'];?> <?=$s['tahun'];?></span>
                      </td>
                      <td class="text-end fw-bold text-dark">
                        Rp <?=number_format($nominal, 0, ',', '.');?>
                      </td>
                      <td class="text-center">
                        <?php if ($is_lunas): ?>
                          <span class="badge bg-success rounded-pill px-3">
                            <i class="ti ti-check me-1"></i> LUNAS (Carry-Over)
                          </span>
                          <small class="d-block text-success mt-1 fw-medium">
                            Cair ke Mukafaah <?=$bln_cair_txt;?>
                          </small>
                        <?php else: ?>
                          <span class="badge bg-label-warning text-dark border border-warning rounded-pill px-3">
                            <i class="ti ti-clock me-1"></i> BELUM LUNAS
                          </span>
                          <small class="d-block text-muted mt-1">Honor ditangguhkan</small>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="d-inline-flex gap-1 align-items-center">
                          <?php if (!$is_lunas): ?>
                            <button type="button"
                                    class="btn btn-sm btn-success rounded-pill px-2 btn-lunasi-siswa"
                                    data-id="<?=$s['id'];?>"
                                    data-nama="<?=htmlspecialchars($s['nama_siswa']);?>"
                                    data-ekstra="<?=htmlspecialchars($s['nama_ekstra']);?>"
                                    data-periode="<?=$nama_bulan_pendek[(int)$s['bulan']] . ' ' . $s['tahun'];?>"
                                    data-nominal="Rp <?=number_format($nominal, 0, ',', '.');?>"
                                    title="Lunasi dan cairkan honor ke bulan terpilih">
                              <i class="ti ti-cash me-1"></i> Lunasi
                            </button>
                          <?php else: ?>
                            <a href="<?=base_url('gaji/ekstra/batal_lunas/'.$s['id'].'?ekstra_id='.$selected_ekstra_id.'&bulan='.$bulan.'&tahun='.$tahun);?>"
                               class="btn btn-sm btn-icon btn-outline-warning btn-confirm-delete"
                               data-url="<?=base_url('gaji/ekstra/batal_lunas/'.$s['id'].'?ekstra_id='.$selected_ekstra_id.'&bulan='.$bulan.'&tahun='.$tahun);?>"
                               data-title="Batalkan Pelunasan?"
                               data-text="Status ananda <b><?=htmlspecialchars($s['nama_siswa']);?></b> akan dikembalikan ke daftar tunggakan <b>BELUM LUNAS</b>."
                               data-variant="warning"
                               data-icon="ti-rotate-clockwise"
                               data-btn-text="Ya, Batalkan"
                               title="Batalkan Lunas">
                              <i class="ti ti-rotate-clockwise"></i>
                            </a>
                          <?php endif; ?>

                          <button type="button"
                                  class="btn btn-sm btn-icon btn-outline-primary btn-edit-siswa"
                                  data-id="<?=$s['id'];?>"
                                  data-ekstra="<?=$s['ekstra_id'];?>"
                                  data-nama="<?=htmlspecialchars($s['nama_siswa']);?>"
                                  data-kelas="<?=htmlspecialchars((string)$s['kelas']);?>"
                                  data-tagihan="<?=$nominal;?>"
                                  data-bulan="<?=$s['bulan'];?>"
                                  data-tahun="<?=$s['tahun'];?>"
                                  data-ket="<?=htmlspecialchars((string)$s['keterangan']);?>"
                                  title="Edit Data Tunggakan">
                            <i class="ti ti-pencil"></i>
                          </button>

                          <button type="button"
                                  class="btn btn-sm btn-icon btn-outline-danger btn-confirm-delete"
                                  data-url="<?=base_url('gaji/ekstra/delete_siswa/'.$s['id'].'?ekstra_id='.$selected_ekstra_id.'&bulan='.$bulan.'&tahun='.$tahun);?>"
                                  data-title="Hapus Data Tunggakan?"
                                  data-text="Hapus catatan tunggakan ananda <b><?=htmlspecialchars($s['nama_siswa']);?></b> secara permanen?"
                                  title="Hapus Data">
                            <i class="ti ti-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <div class="avatar avatar-lg bg-label-success mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="ti ti-checks fs-2"></i>
              </div>
              <h6 class="fw-bold mb-1">Tidak Ada Tunggakan Siswa Belum Lunas</h6>
              <p class="text-muted small mb-3">Jika seluruh siswa ekstra per anak sudah membayar lunas, cukup isikan jumlah total anak lunas di <b>Blok A</b> di atas.</p>
              <button type="button" class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
                <i class="ti ti-user-plus me-1"></i> Catat Siswa Belum Lunas
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- =================================================================== -->
    <!-- TAB 2: REKAP SESI & KEDATANGAN GURU (MULTI-BARIS PER BULAN)         -->
    <!-- =================================================================== -->
    <div class="tab-pane fade <?=$active_tab === 'per_sesi' ? 'show active' : '';?>" id="pane-per-sesi" role="tabpanel">

      <!-- RINGKASAN PER CABANG / PENGAMPU -->
      <div class="row g-3 mb-4">
        <?php if (!empty($ringkasan_sesi)): ?>
          <?php foreach ($ringkasan_sesi as $rs):
            $satuan_label = $rs['tipe_honor'] === 'per_kedatangan' ? 'Kedatangan' : 'Sesi';
            $badge_tipe   = $rs['tipe_honor'] === 'per_kedatangan' ? 'bg-label-info' : 'bg-label-primary';
          ?>
            <div class="col-md-6 col-xl-4">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <span class="badge <?=$badge_tipe;?> mb-1"><?=ucwords(str_replace('_', ' ', $rs['tipe_honor']));?></span>
                      <h6 class="fw-bold mb-0 text-dark"><?=htmlspecialchars($rs['nama_ekstra']);?></h6>
                      <small class="text-muted"><i class="ti ti-user me-1"></i><?=htmlspecialchars($rs['nama_pegawai'] ?: 'Belum Diset');?></small>
                    </div>
                    <button type="button"
                            class="btn btn-sm btn-primary rounded-pill btn-open-create-sesi"
                            data-ekstra-id="<?=$rs['ekstra_id'];?>"
                            data-tarif="<?=$rs['tarif_standar'];?>"
                            title="Tambah catatan sesi untuk <?=htmlspecialchars($rs['nama_ekstra']);?>">
                      <i class="ti ti-plus"></i> Catat
                    </button>
                  </div>
                  <hr class="my-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <small class="text-muted d-block">Tarif Standar</small>
                      <span class="fw-semibold text-dark">Rp <?=number_format($rs['tarif_standar'], 0, ',', '.');?> / <?=strtolower($satuan_label);?></span>
                    </div>
                    <div class="text-end">
                      <small class="text-muted d-block">Akumulasi Disetujui (<?=$rs['disetujui_sesi'];?> <?=$satuan_label;?>)</small>
                      <span class="fw-bold text-success fs-6">Rp <?=number_format($rs['disetujui_honor'], 0, ',', '.');?></span>
                    </div>
                  </div>
                  <?php if ($rs['pending_sesi'] > 0): ?>
                    <div class="mt-2 pt-2 border-top small text-warning">
                      <i class="ti ti-clock me-1"></i> <?=$rs['pending_sesi'];?> <?=$satuan_label;?> status pending (Rp <?=number_format($rs['pending_honor'], 0, ',', '.');?>)
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info mb-0">
              Belum ada cabang ekstra bertipe <b>Per Sesi</b> atau <b>Per Kedatangan</b> pada Master Cabang Ekstra.
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- TABEL LOG MULTI-BARIS SESI & KEDATANGAN -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
          <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
              <i class="ti ti-calendar-event me-1"></i> Log Sesi &amp; Kedatangan Guru — <?=$label_bulan_aktif;?>
            </h5>
            <small class="text-muted">Mendukung pencatatan multi-baris dalam sebulan (per tanggal pertemuan, atau dipisah per jenjang PAUD &amp; SD tanpa input daftar siswa).</small>
          </div>
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group input-group-sm" style="width: 210px;">
              <span class="input-group-text"><i class="ti ti-search"></i></span>
              <input type="text" class="form-control" id="searchSesi" placeholder="Cari cabang / catatan...">
            </div>
            <button type="button" class="btn btn-primary shadow-sm btn-open-create-sesi" data-ekstra-id="" data-tarif="">
              <i class="ti ti-calendar-plus me-1"></i> Catat Sesi / Kedatangan
            </button>
          </div>
        </div>

        <div class="card-body">
          <?php if (!empty($sesi_list)): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" id="tblSesi">
                <thead class="table-light">
                  <tr>
                    <th width="4%">No</th>
                    <th>Tanggal</th>
                    <th>Cabang Ekstra</th>
                    <th>Guru Pengampu</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-end">Tarif Satuan</th>
                    <th class="text-end">Total Honor</th>
                    <th>Keterangan</th>
                    <th class="text-center">Status</th>
                    <th width="11%" class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $no = 1; foreach ($sesi_list as $ss):
                    $satuan_txt = $ss['tipe_honor'] === 'per_kedatangan' ? 'kedatangan' : 'sesi';
                    $is_ok = ($ss['status'] === 'disetujui');
                  ?>
                    <tr class="row-sesi">
                      <td class="text-muted"><?=$no++;?></td>
                      <td>
                        <?php if (!empty($ss['tanggal_sesi'])): ?>
                          <span class="fw-medium text-dark"><?=date('d/m/Y', strtotime($ss['tanggal_sesi']));?></span>
                        <?php else: ?>
                          <span class="text-muted small">Rekap <?=$nama_bulan_pendek[(int)$ss['bulan']] ?? $ss['bulan'];?> <?=$ss['tahun'];?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge bg-label-primary"><?=htmlspecialchars($ss['nama_ekstra']);?></span>
                      </td>
                      <td class="fw-medium text-dark">
                        <?=htmlspecialchars($ss['nama_guru_pengampu'] ?: '-');?>
                      </td>
                      <td class="text-center">
                        <span class="fw-bold text-dark"><?=$ss['jumlah_sesi'];?></span>
                        <small class="text-muted"><?=$satuan_txt;?></small>
                      </td>
                      <td class="text-end text-muted">
                        Rp <?=number_format($ss['tarif_per_sesi'], 0, ',', '.');?>
                      </td>
                      <td class="text-end fw-bold <?=$is_ok ? 'text-success' : 'text-warning';?>">
                        Rp <?=number_format($ss['total_honor'], 0, ',', '.');?>
                      </td>
                      <td>
                        <small class="text-dark"><?=htmlspecialchars($ss['keterangan'] ?: '-');?></small>
                      </td>
                      <td class="text-center">
                        <?php if ($is_ok): ?>
                          <span class="badge bg-success rounded-pill px-2">Disetujui</span>
                        <?php else: ?>
                          <span class="badge bg-label-warning text-dark border border-warning rounded-pill px-2">Pending</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="d-inline-flex gap-1">
                          <button type="button"
                                  class="btn btn-sm btn-icon btn-outline-primary btn-edit-sesi"
                                  data-sesi-id="<?=$ss['sesi_id'];?>"
                                  data-ekstra-id="<?=$ss['ekstra_id'];?>"
                                  data-bulan="<?=$ss['bulan'];?>"
                                  data-tahun="<?=$ss['tahun'];?>"
                                  data-tanggal="<?=$ss['tanggal_sesi'];?>"
                                  data-jumlah="<?=$ss['jumlah_sesi'];?>"
                                  data-tarif="<?=(int)$ss['tarif_per_sesi'];?>"
                                  data-keterangan="<?=htmlspecialchars((string)$ss['keterangan']);?>"
                                  data-status="<?=$ss['status'];?>"
                                  title="Edit Log Sesi">
                            <i class="ti ti-pencil"></i>
                          </button>
                          <button type="button"
                                  class="btn btn-sm btn-icon btn-outline-danger btn-confirm-delete"
                                  data-url="<?=base_url('gaji/ekstra/delete_sesi/'.$ss['sesi_id'].'?ekstra_id='.$selected_ekstra_id.'&bulan='.$bulan.'&tahun='.$tahun);?>"
                                  data-title="Hapus Baris Sesi?"
                                  data-text="Hapus catatan <b><?=htmlspecialchars($ss['nama_ekstra']);?></b> (<?=$ss['jumlah_sesi'];?>x = Rp <?=number_format($ss['total_honor'], 0, ',', '.');?>)?"
                                  title="Hapus Log Sesi">
                            <i class="ti ti-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <div class="avatar avatar-lg bg-label-info mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="ti ti-calendar-plus fs-2"></i>
              </div>
              <h6 class="fw-bold mb-1">Belum Ada Catatan Sesi / Kedatangan Bulan <?=$label_bulan_aktif;?></h6>
              <p class="text-muted small mb-3">Klik tombol di bawah untuk mencatat kehadiran guru pengampu per sesi atau per kedatangan (misalnya Renang Pak Tri atau Tahfidz Kak Annas).</p>
              <button type="button" class="btn btn-primary btn-open-create-sesi" data-ekstra-id="" data-tarif="">
                <i class="ti ti-plus me-1"></i> Catat Sesi / Kedatangan Pertama
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
<!-- / Content -->

<!-- =================================================================== -->
<!-- MODAL 1: CATAT TUNGGAKAN SISWA BELUM LUNAS (BLOK B TAB 1)           -->
<!-- =================================================================== -->
<div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold">
          <i class="ti ti-user-plus text-warning me-2"></i> Catat Tunggakan Siswa (Belum Lunas)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?=base_url('gaji/ekstra/add_siswa');?>" method="POST">
        <input type="hidden" name="filter_ekstra_id" value="<?=htmlspecialchars((string)$selected_ekstra_id);?>">
        <input type="hidden" name="filter_bulan" value="<?=$bulan;?>">
        <input type="hidden" name="filter_tahun" value="<?=$tahun;?>">
        <input type="hidden" name="status_bayar" value="pending">

        <div class="modal-body p-4">
          <div class="alert alert-warning py-2 px-3 small mb-3 border-0 bg-label-warning text-dark">
            <i class="ti ti-info-circle me-1"></i> Siswa yang dicatat di sini berstatus <b>BELUM LUNAS</b> dan honornya ditangguhkan sampai Anda mengklik tombol <b>Lunasi</b>.
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Cabang Ekstrakurikuler (Per Anak) <span class="text-danger">*</span></label>
            <select class="form-select" name="ekstra_id" id="input_ekstra_id" required>
              <?php foreach ($ekstras_per_anak as $ek): ?>
                <option value="<?=$ek['ekstra_id'];?>"
                        data-tarif="<?=(float)$ek['tarif_standar'];?>"
                        <?=$selected_ekstra_id == $ek['ekstra_id'] ? 'selected' : '';?>>
                  <?=htmlspecialchars($ek['nama_ekstra']);?> — <?=htmlspecialchars($ek['nama_pegawai'] ?: 'Tanpa Pengampu');?> (Rp <?=number_format($ek['tarif_standar'], 0, ',', '.');?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nama_siswa" placeholder="Contoh: Muhammad Zaki" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Kelas</label>
              <input type="text" class="form-control" name="kelas" placeholder="Misal: 4A / 5B">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nominal Tagihan (Rp)</label>
              <input type="number" class="form-control fw-bold" id="input_nominal_tagihan" name="nominal_tagihan" placeholder="Otomatis dari tarif cabang" min="0" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Bulan Tunggakan</label>
              <select class="form-select" name="bulan">
                <?php foreach ($nama_bulan_list as $bln_num => $bln_name): ?>
                  <option value="<?=$bln_num;?>" <?=$bulan == $bln_num ? 'selected' : '';?>><?=$bln_name;?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tahun</label>
              <input type="number" class="form-control" name="tahun" value="<?=$tahun;?>" required>
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label fw-semibold">Catatan (Opsional)</label>
            <input type="text" class="form-control" name="keterangan" placeholder="Mis: Janji wali murid bayar awal bulan depan">
          </div>
        </div>

        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning text-dark fw-semibold px-4">
            <i class="ti ti-device-floppy me-1"></i> Simpan Tunggakan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 2: EDIT TUNGGAKAN SISWA                                       -->
<!-- =================================================================== -->
<div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold">
          <i class="ti ti-pencil text-primary me-2"></i> Edit Catatan Tunggakan Siswa
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?=base_url('gaji/ekstra/edit_siswa');?>" method="POST">
        <input type="hidden" name="id" id="edit_siswa_id">
        <input type="hidden" name="filter_ekstra_id" value="<?=htmlspecialchars((string)$selected_ekstra_id);?>">
        <input type="hidden" name="filter_bulan" value="<?=$bulan;?>">
        <input type="hidden" name="filter_tahun" value="<?=$tahun;?>">

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Cabang Ekstrakurikuler (Per Anak) <span class="text-danger">*</span></label>
            <select class="form-select" name="ekstra_id" id="edit_siswa_ekstra_id" required>
              <?php foreach ($ekstras_per_anak as $ek): ?>
                <option value="<?=$ek['ekstra_id'];?>" data-tarif="<?=(float)$ek['tarif_standar'];?>">
                  <?=htmlspecialchars($ek['nama_ekstra']);?> (Tarif: Rp <?=number_format($ek['tarif_standar'], 0, ',', '.');?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_siswa_nama" name="nama_siswa" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Kelas</label>
              <input type="text" class="form-control" id="edit_siswa_kelas" name="kelas">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nominal Tagihan (Rp)</label>
              <input type="number" class="form-control fw-bold" id="edit_siswa_tagihan" name="nominal_tagihan" min="0" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Bulan Tunggakan</label>
              <select class="form-select" id="edit_siswa_bulan" name="bulan">
                <?php foreach ($nama_bulan_list as $bln_num => $bln_name): ?>
                  <option value="<?=$bln_num;?>"><?=$bln_name;?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tahun</label>
              <input type="number" class="form-control" id="edit_siswa_tahun" name="tahun" required>
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label fw-semibold">Keterangan</label>
            <input type="text" class="form-control" id="edit_siswa_keterangan" name="keterangan" placeholder="Catatan...">
          </div>
        </div>

        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 3: LUNASI TUNGGAKAN & PILIH BULAN PENCAIRAN (CARRY-OVER)       -->
<!-- =================================================================== -->
<div class="modal fade" id="modalLunasiTunggakan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-bottom bg-label-success">
        <h6 class="modal-title fw-bold text-success">
          <i class="ti ti-cash me-1"></i> Lunasi &amp; Cairkan Honor
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?=base_url('gaji/ekstra/lunasi_tunggakan');?>" method="POST">
        <input type="hidden" name="id" id="lunasi_siswa_id">
        <input type="hidden" name="filter_ekstra_id" value="<?=htmlspecialchars((string)$selected_ekstra_id);?>">
        <input type="hidden" name="filter_bulan" value="<?=$bulan;?>">
        <input type="hidden" name="filter_tahun" value="<?=$tahun;?>">

        <div class="modal-body p-3">
          <div class="bg-light rounded p-3 mb-3 border">
            <div class="fw-bold text-dark" id="lunasi_nama_siswa">-</div>
            <div class="small text-muted" id="lunasi_info_ekstra">-</div>
            <div class="fw-bold text-success mt-1" id="lunasi_nominal">-</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Cairkan Honor ke Mukafaah Bulan</label>
            <div class="row g-2">
              <div class="col-7">
                <select class="form-select form-select-sm" name="bulan_cair" id="lunasi_bulan_cair">
                  <?php foreach ($nama_bulan_list as $bln_num => $bln_name): ?>
                    <option value="<?=$bln_num;?>" <?=$bulan == $bln_num ? 'selected' : '';?>><?=$bln_name;?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-5">
                <input type="number" class="form-control form-control-sm" name="tahun_cair" id="lunasi_tahun_cair" value="<?=$tahun;?>" required>
              </div>
            </div>
            <small class="text-muted" style="font-size: 11px;">Otomatis menambah total honor guru pengampu di bulan pencairan tersebut.</small>
          </div>

          <div class="mb-0">
            <label class="form-label fw-semibold small">Tanggal Bayar Siswa</label>
            <input type="date" class="form-control form-control-sm" name="tanggal_bayar" value="<?=date('Y-m-d');?>">
          </div>
        </div>

        <div class="modal-footer border-top py-2">
          <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-success px-3">
            <i class="ti ti-check me-1"></i> Konfirmasi Lunas
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 4: CATAT / EDIT SESI & KEDATANGAN GURU (TAB 2)                -->
<!-- =================================================================== -->
<div class="modal fade" id="modalCatatSesi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="modalCatatSesiTitle">
          <i class="ti ti-calendar-plus text-primary me-2"></i> Catat Sesi / Kedatangan Guru
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?=base_url('gaji/ekstra/save_sesi');?>" method="POST">
        <input type="hidden" name="sesi_id" id="sesi_id" value="0">
        <input type="hidden" name="filter_ekstra_id" value="<?=htmlspecialchars((string)$selected_ekstra_id);?>">
        <input type="hidden" name="filter_bulan" value="<?=$bulan;?>">
        <input type="hidden" name="filter_tahun" value="<?=$tahun;?>">

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Cabang Ekstra (Per Sesi / Kedatangan) <span class="text-danger">*</span></label>
            <select class="form-select" name="ekstra_id" id="sesi_ekstra_id" required>
              <?php foreach ($ekstras_per_sesi as $ek): ?>
                <option value="<?=$ek['ekstra_id'];?>"
                        data-tarif="<?=(float)$ek['tarif_standar'];?>"
                        data-tipe="<?=$ek['tipe_honor'];?>"
                        <?=$selected_ekstra_id == $ek['ekstra_id'] ? 'selected' : '';?>>
                  <?=htmlspecialchars($ek['nama_ekstra']);?> — <?=htmlspecialchars($ek['nama_pegawai'] ?: 'Tanpa Pengampu');?> (Rp <?=number_format($ek['tarif_standar'], 0, ',', '.');?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold">Bulan Periode</label>
              <select class="form-select" name="bulan" id="sesi_bulan">
                <?php foreach ($nama_bulan_list as $bln_num => $bln_name): ?>
                  <option value="<?=$bln_num;?>" <?=$bulan == $bln_num ? 'selected' : '';?>><?=$bln_name;?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tahun</label>
              <input type="number" class="form-control" name="tahun" id="sesi_tahun" value="<?=$tahun;?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Tanggal (Opsional)</label>
              <input type="date" class="form-control" name="tanggal_sesi" id="sesi_tanggal">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold" id="lbl_jumlah_sesi">Jumlah Sesi / Kedatangan <span class="text-danger">*</span></label>
              <input type="number" class="form-control fw-bold" name="jumlah_sesi" id="sesi_jumlah" value="1" min="1" required>
            </div>
            <div class="col-md-7">
              <label class="form-label fw-semibold">Tarif per Sesi / Kedatangan (Rp)</label>
              <input type="number" class="form-control fw-bold" name="tarif_per_sesi" id="sesi_tarif" min="0" required>
            </div>
          </div>

          <div class="alert alert-primary py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
            <span class="small fw-semibold">Estimasi Total Honor Baris Ini:</span>
            <span class="fw-bold fs-5 text-primary" id="sesi_preview_total">Rp 0</span>
          </div>

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Keterangan</label>
              <input type="text" class="form-control" name="keterangan" id="sesi_keterangan" placeholder="Mis: Renang Putra/Putri atau Tahfidz PAUD 17x">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select" name="status" id="sesi_status">
                <option value="disetujui">Disetujui (Cair)</option>
                <option value="pending">Pending</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-device-floppy me-1"></i> Simpan Catatan Sesi
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

  // Pindahkan seluruh modal ke <body> agar tidak tertutup backdrop
  ['modalTambahSiswa', 'modalEditSiswa', 'modalLunasiTunggakan', 'modalCatatSesi'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el && el.parentNode !== document.body) {
      document.body.appendChild(el);
    }
  });

  function formatRupiah(num) {
    return 'Rp ' + (parseInt(num, 10) || 0).toLocaleString('id-ID');
  }

  // 1. Sinkronisasi Tab aktif ke hidden filter & URL
  var tabButtons = document.querySelectorAll('#ekstraTabs button[data-bs-toggle="tab"]');
  var filterActiveTab = document.getElementById('filter_active_tab');
  tabButtons.forEach(function(btn) {
    btn.addEventListener('shown.bs.tab', function() {
      var tabName = this.getAttribute('data-tab-name');
      if (filterActiveTab) filterActiveTab.value = tabName;

      tabButtons.forEach(function(b) {
        if (b === btn) {
          b.classList.add('active');
          b.classList.remove('bg-white', 'text-dark', 'border');
        } else {
          b.classList.remove('active');
          b.classList.add('bg-white', 'text-dark', 'border');
        }
      });

      if (window.history && window.history.replaceState) {
        var url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url.toString());
      }
    });
  });

  // 2. Kalkulasi Live Blok A (Input Cepat Anak Lunas)
  function recalcBlokA() {
    var grandTotal = 0;
    document.querySelectorAll('.rekap-row').forEach(function(row) {
      var tarif = parseFloat(row.getAttribute('data-tarif')) || 0;
      var carryNom = parseFloat(row.getAttribute('data-carry-nom')) || 0;
      var inputEl = row.querySelector('.input-rekap-jml');
      var jml = inputEl ? Math.max(0, parseInt(inputEl.value, 10) || 0) : 0;
      var subTotal = (jml * tarif) + carryNom;
      grandTotal += subTotal;

      var cellTotal = row.querySelector('.cell-rekap-total');
      if (cellTotal) cellTotal.textContent = formatRupiah(subTotal);
    });
    var grandEl = document.getElementById('total-rekap-anak-cair');
    if (grandEl) grandEl.textContent = formatRupiah(grandTotal);
  }

  document.querySelectorAll('.input-rekap-jml').forEach(function(inp) {
    inp.addEventListener('input', recalcBlokA);
  });

  // 3. Auto-Fill Nominal Tagihan pada Modal Tambah & Edit Tunggakan
  function syncTarifFromSelect(selectEl, inputEl) {
    if (!selectEl || !inputEl) return;
    var opt = selectEl.options[selectEl.selectedIndex];
    var tarif = opt ? opt.getAttribute('data-tarif') : '';
    inputEl.value = (tarif !== null && tarif !== '') ? (parseInt(tarif, 10) || 0) : '';
  }

  var selectTambahEkstra = document.getElementById('input_ekstra_id');
  var inputTambahTagihan = document.getElementById('input_nominal_tagihan');
  var modalTambahEl = document.getElementById('modalTambahSiswa');
  if (selectTambahEkstra && inputTambahTagihan) {
    selectTambahEkstra.addEventListener('change', function() {
      syncTarifFromSelect(selectTambahEkstra, inputTambahTagihan);
    });
  }
  if (modalTambahEl) {
    modalTambahEl.addEventListener('show.bs.modal', function() {
      syncTarifFromSelect(selectTambahEkstra, inputTambahTagihan);
    });
  }

  var selectEditEkstra = document.getElementById('edit_siswa_ekstra_id');
  var inputEditTagihan = document.getElementById('edit_siswa_tagihan');
  if (selectEditEkstra && inputEditTagihan) {
    selectEditEkstra.addEventListener('change', function() {
      syncTarifFromSelect(selectEditEkstra, inputEditTagihan);
    });
  }

  // 4. Edit Tunggakan Modal Trigger
  var modalEditSiswaEl = document.getElementById('modalEditSiswa');
  var bsModalEditSiswa = modalEditSiswaEl ? new bootstrap.Modal(modalEditSiswaEl) : null;
  document.querySelectorAll('.btn-edit-siswa').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('edit_siswa_id').value = this.getAttribute('data-id');
      document.getElementById('edit_siswa_ekstra_id').value = this.getAttribute('data-ekstra');
      document.getElementById('edit_siswa_nama').value = this.getAttribute('data-nama');
      document.getElementById('edit_siswa_kelas').value = this.getAttribute('data-kelas');
      document.getElementById('edit_siswa_tagihan').value = parseInt(this.getAttribute('data-tagihan'), 10) || 0;
      document.getElementById('edit_siswa_bulan').value = this.getAttribute('data-bulan');
      document.getElementById('edit_siswa_tahun').value = this.getAttribute('data-tahun');
      document.getElementById('edit_siswa_keterangan').value = this.getAttribute('data-ket');
      if (bsModalEditSiswa) bsModalEditSiswa.show();
    });
  });

  // 5. Modal Lunasi Tunggakan (Carry-Over) Trigger
  var modalLunasiEl = document.getElementById('modalLunasiTunggakan');
  var bsModalLunasi = modalLunasiEl ? new bootstrap.Modal(modalLunasiEl) : null;
  document.querySelectorAll('.btn-lunasi-siswa').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('lunasi_siswa_id').value = this.getAttribute('data-id');
      document.getElementById('lunasi_nama_siswa').textContent = this.getAttribute('data-nama');
      document.getElementById('lunasi_info_ekstra').textContent = this.getAttribute('data-ekstra') + ' • Tunggakan ' + this.getAttribute('data-periode');
      document.getElementById('lunasi_nominal').textContent = this.getAttribute('data-nominal');
      if (bsModalLunasi) bsModalLunasi.show();
    });
  });

  // 6. Modal Catat & Edit Sesi / Kedatangan Guru (Tab 2)
  var modalSesiEl = document.getElementById('modalCatatSesi');
  var bsModalSesi = modalSesiEl ? new bootstrap.Modal(modalSesiEl) : null;
  var sesiEkstraEl = document.getElementById('sesi_ekstra_id');
  var sesiJumlahEl = document.getElementById('sesi_jumlah');
  var sesiTarifEl = document.getElementById('sesi_tarif');
  var sesiPreviewEl = document.getElementById('sesi_preview_total');

  function updateSesiPreview() {
    var jml = Math.max(0, parseInt(sesiJumlahEl ? sesiJumlahEl.value : 0, 10) || 0);
    var trf = Math.max(0, parseFloat(sesiTarifEl ? sesiTarifEl.value : 0) || 0);
    if (sesiPreviewEl) sesiPreviewEl.textContent = formatRupiah(jml * trf);
  }

  if (sesiEkstraEl) {
    sesiEkstraEl.addEventListener('change', function() {
      syncTarifFromSelect(sesiEkstraEl, sesiTarifEl);
      updateSesiPreview();
    });
  }
  if (sesiJumlahEl) sesiJumlahEl.addEventListener('input', updateSesiPreview);
  if (sesiTarifEl) sesiTarifEl.addEventListener('input', updateSesiPreview);

  document.querySelectorAll('.btn-open-create-sesi').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('modalCatatSesiTitle').innerHTML = '<i class="ti ti-calendar-plus text-primary me-2"></i> Catat Sesi / Kedatangan Guru';
      document.getElementById('sesi_id').value = '0';
      var eid = this.getAttribute('data-ekstra-id');
      if (eid && sesiEkstraEl) {
        sesiEkstraEl.value = eid;
      }
      document.getElementById('sesi_bulan').value = '<?=$bulan;?>';
      document.getElementById('sesi_tahun').value = '<?=$tahun;?>';
      document.getElementById('sesi_tanggal').value = '';
      document.getElementById('sesi_jumlah').value = '1';
      document.getElementById('sesi_keterangan').value = '';
      document.getElementById('sesi_status').value = 'disetujui';
      syncTarifFromSelect(sesiEkstraEl, sesiTarifEl);
      updateSesiPreview();
      if (bsModalSesi) bsModalSesi.show();
    });
  });

  document.querySelectorAll('.btn-edit-sesi').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('modalCatatSesiTitle').innerHTML = '<i class="ti ti-pencil text-primary me-2"></i> Edit Catatan Sesi / Kedatangan';
      document.getElementById('sesi_id').value = this.getAttribute('data-sesi-id');
      document.getElementById('sesi_ekstra_id').value = this.getAttribute('data-ekstra-id');
      document.getElementById('sesi_bulan').value = this.getAttribute('data-bulan');
      document.getElementById('sesi_tahun').value = this.getAttribute('data-tahun');
      document.getElementById('sesi_tanggal').value = this.getAttribute('data-tanggal') || '';
      document.getElementById('sesi_jumlah').value = this.getAttribute('data-jumlah');
      document.getElementById('sesi_tarif').value = this.getAttribute('data-tarif');
      document.getElementById('sesi_keterangan').value = this.getAttribute('data-keterangan') || '';
      document.getElementById('sesi_status').value = this.getAttribute('data-status') || 'disetujui';
      updateSesiPreview();
      if (bsModalSesi) bsModalSesi.show();
    });
  });

  // 7. Instant Search Tabel Blok B & Tabel Tab 2
  function attachTableFilter(inputId, rowSelector) {
    var inp = document.getElementById(inputId);
    if (!inp) return;
    inp.addEventListener('input', function() {
      var q = this.value.toLowerCase().trim();
      document.querySelectorAll(rowSelector).forEach(function(tr) {
        tr.style.display = tr.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }
  attachTableFilter('searchTunggakan', '.row-tunggakan');
  attachTableFilter('searchSesi', '.row-sesi');
});
</script>
