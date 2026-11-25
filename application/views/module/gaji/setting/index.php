<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Header & Breadcrumbs -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold py-1 mb-1">
        <span class="text-muted fw-light">Penggajian /</span> Pengaturan Aturan & Tarif
      </h4>
      <p class="text-muted mb-0 small">
        Konfigurasi parameter jam kerja, formula bonus kedisiplinan 2-tier presensi biometrik, tarif tunjangan, dan kalender hari khusus pulang awal.
      </p>
    </div>
    <span class="badge bg-label-primary px-3 py-2 fs-6">
      <i class="ti ti-shield-check me-1"></i> SOP Mentari v2.0
    </span>
  </div>

  <!-- Flash Toast & Modal Konfirmasi -->
  <?php $this->load->view('module/gaji/toast_and_modal'); ?>

  <div class="row">
    <!-- KOLOM KIRI: FORM PENGATURAN GLOBAL -->
    <div class="col-xl-8 col-lg-7 col-12 mb-4">
      <form action="<?=base_url('gaji/setting/update_setting');?>" method="POST">
        
        <!-- CARD 1: FORMULA BONUS PRESENSI 2-TIER -->
        <div class="card mb-4 border-top border-primary border-3">
          <div class="card-header pb-2 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 d-flex align-items-center text-primary">
              <i class="ti ti-clock-check me-2 fs-4"></i> Aturan Presensi 2-Tier & Bonus Kedisiplinan
            </h5>
            <span class="badge bg-primary">SOP Poin 3-4</span>
          </div>
          <div class="card-body">
            <div class="alert alert-info py-2 px-3 mb-3 small d-flex align-items-center">
              <i class="ti ti-info-circle me-2 fs-5 flex-shrink-0"></i>
              <div>
                <strong>Formula Apresiasi Bertingkat:</strong> Guru yang hadir sebelum <b>06.30</b> (lewat &le; 3x) berhak memperoleh <b>Tier 1 (Rp 50.000)</b> + <b>Tier 2 (Rp 50.000)</b> = <b>Total Rp 100.000,-</b> per bulan.
              </div>
            </div>

            <div class="row g-3">
              <!-- Tier 1 Jam Acuan -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="jam_masuk_standar">
                  <i class="ti ti-clock me-1 text-primary"></i> Jam Masuk Acuan Standar (Tier 1)
                </label>
                <div class="input-group">
                  <span class="input-group-text"><i class="ti ti-alarm"></i></span>
                  <input type="text" class="form-control" id="jam_masuk_standar" name="jam_masuk_standar" 
                         value="<?=htmlspecialchars(isset($setting['jam_masuk_standar']) ? substr($setting['jam_masuk_standar'], 0, 5) : '06:45');?>" 
                         placeholder="06:45" required>
                  <span class="input-group-text">WIB</span>
                </div>
                <div class="form-text small">Standar kedatangan resmi SD Mentari (06.45 WIB).</div>
              </div>

              <!-- Toleransi Keterlambatan -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="toleransi_menit">
                  <i class="ti ti-hourglass-low me-1 text-warning"></i> Toleransi Keterlambatan
                </label>
                <div class="input-group">
                  <input type="number" min="0" max="60" class="form-control" id="toleransi_menit" name="toleransi_menit" 
                         value="<?=htmlspecialchars(isset($setting['toleransi_menit']) ? $setting['toleransi_menit'] : 3);?>" required>
                  <span class="input-group-text">Menit</span>
                </div>
                <div class="form-text small">Toleransi dispensasi (06.45 + 3 mnt = <b>06.48 WIB</b>).</div>
              </div>

              <!-- Tier 2 Jam Acuan Super Early -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="jam_masuk_super_early">
                  <i class="ti ti-sun me-1 text-warning"></i> Jam Hadir Pagi Super Early (Tier 2)
                </label>
                <div class="input-group">
                  <span class="input-group-text"><i class="ti ti-brightness-up"></i></span>
                  <input type="text" class="form-control" id="jam_masuk_super_early" name="jam_masuk_super_early" 
                         value="<?=htmlspecialchars(isset($setting['jam_masuk_super_early']) ? substr($setting['jam_masuk_super_early'], 0, 5) : '06:30');?>" 
                         placeholder="06:30" required>
                  <span class="input-group-text">WIB</span>
                </div>
                <div class="form-text small">Acuan kedatangan pagi super early (06.30 WIB).</div>
              </div>

              <!-- Batas Maksimal Pelanggaran -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="max_toleransi_telat">
                  <i class="ti ti-alert-triangle me-1 text-danger"></i> Batas Maksimal Pelanggaran / Bulan
                </label>
                <div class="input-group">
                  <input type="number" min="0" max="10" class="form-control" id="max_toleransi_telat" name="max_toleransi_telat" 
                         value="<?=htmlspecialchars(isset($setting['max_toleransi_telat']) ? $setting['max_toleransi_telat'] : 3);?>" required>
                  <span class="input-group-text">Kali</span>
                </div>
                <div class="form-text small">Maksimal 3 kali telat/lewat jam acuan dalam sebulan.</div>
              </div>

              <!-- Nominal Bonus Tier 1 -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="nominal_bonus_tier1">
                  <i class="ti ti-coin me-1 text-success"></i> Nominal Bonus Tier 1 (Tepat Waktu)
                </label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control text-end fw-bold" id="nominal_bonus_tier1" name="nominal_bonus_tier1" 
                         value="<?=number_format(isset($setting['nominal_bonus_tier1']) ? $setting['nominal_bonus_tier1'] : 50000, 0, ',', '.');?>" required>
                </div>
                <div class="form-text small">Diberikan jika telat &gt; 06.48 &le; 3 kali.</div>
              </div>

              <!-- Nominal Bonus Tier 2 -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="nominal_bonus_tier2">
                  <i class="ti ti-award me-1 text-success"></i> Nominal Bonus Tier 2 (Super Early)
                </label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control text-end fw-bold" id="nominal_bonus_tier2" name="nominal_bonus_tier2" 
                         value="<?=number_format(isset($setting['nominal_bonus_tier2']) ? $setting['nominal_bonus_tier2'] : 50000, 0, ',', '.');?>" required>
                </div>
                <div class="form-text small">Tambahan jika datang &gt; 06.30 &le; 3 kali.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 2: TARIF UANG MAKAN & MASA KERJA -->
        <div class="card mb-4 border-top border-info border-3">
          <div class="card-header pb-2 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 d-flex align-items-center text-info">
              <i class="ti ti-meat me-2 fs-4"></i> Tarif Uang Makan, Masa Kerja & Tunjangan Jabatan
            </h5>
            <span class="badge bg-info">SOP Formula Tetap</span>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <!-- Uang Makan Guru Lama -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="tarif_makan_lama">
                  <i class="ti ti-user-check me-1 text-primary"></i> Uang Makan Guru Lama (&gt; 3 Tahun)
                </label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control text-end fw-bold" id="tarif_makan_lama" name="tarif_makan_lama" 
                         value="<?=number_format(isset($setting['tarif_makan_lama']) ? $setting['tarif_makan_lama'] : 5000, 0, ',', '.');?>" required>
                  <span class="input-group-text">/ Hadir</span>
                </div>
                <div class="form-text small">Dikalikan akumulasi hadir fisik guru bermasa kerja &gt; 3 thn.</div>
              </div>

              <!-- Uang Makan Guru Baru -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="tarif_makan_baru">
                  <i class="ti ti-user-plus me-1 text-info"></i> Uang Makan Guru Baru (&le; 3 Tahun)
                </label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control text-end fw-bold" id="tarif_makan_baru" name="tarif_makan_baru" 
                         value="<?=number_format(isset($setting['tarif_makan_baru']) ? $setting['tarif_makan_baru'] : 2500, 0, ',', '.');?>" required>
                  <span class="input-group-text">/ Hadir</span>
                </div>
                <div class="form-text small">Dikalikan akumulasi hadir fisik guru bermasa kerja &le; 3 thn.</div>
              </div>

              <!-- Tarif Masa Kerja GTY -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="tarif_masa_kerja_tahunan">
                  <i class="ti ti-calendar-time me-1 text-success"></i> Tunjangan Masa Kerja GTY (Per Tahun)
                </label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control text-end fw-bold" id="tarif_masa_kerja_tahunan" name="tarif_masa_kerja_tahunan" 
                         value="<?=number_format(isset($setting['tarif_masa_kerja_tahunan']) ? $setting['tarif_masa_kerja_tahunan'] : 15000, 0, ',', '.');?>" required>
                  <span class="input-group-text">/ Tahun</span>
                </div>
                <div class="form-text small">Khusus Guru Tetap Yayasan (GTY): Rp 15.000 x Lama Kerja.</div>
              </div>

              <!-- Tunjangan Wali Kelas -->
              <div class="col-md-6 col-12">
                <label class="form-label fw-bold" for="nominal_wali_kelas">
                  <i class="ti ti-school me-1 text-warning"></i> Tunjangan Wali Kelas (Bulanan)
                </label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="text" class="form-control text-end fw-bold" id="nominal_wali_kelas" name="nominal_wali_kelas" 
                         value="<?=number_format(isset($setting['nominal_wali_kelas']) ? $setting['nominal_wali_kelas'] : 25000, 0, ',', '.');?>" required>
                  <span class="input-group-text">/ Bulan</span>
                </div>
                <div class="form-text small">Tunjangan tetap bulanan bagi guru penanggung jawab kelas.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 3: REKENING SUMBER DISBURSEMENT YAYASAN -->
        <div class="card mb-4 border-top border-success border-3">
          <div class="card-header pb-2 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 d-flex align-items-center text-success">
              <i class="ti ti-building-bank me-2 fs-4"></i> Rekening Sumber Operasional Yayasan (CMS Payroll)
            </h5>
            <span class="badge bg-success">Disbursement</span>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4 col-12">
                <label class="form-label fw-bold" for="bank_sumber_yayasan">
                  <i class="ti ti-credit-card me-1"></i> Bank Sumber Payroll
                </label>
                <select class="form-select" id="bank_sumber_yayasan" name="bank_sumber_yayasan">
                  <option value="BRI" <?=(isset($setting['bank_sumber_yayasan']) && $setting['bank_sumber_yayasan'] == 'BRI') ? 'selected' : '';?>>Bank BRI</option>
                  <option value="BSI" <?=(isset($setting['bank_sumber_yayasan']) && $setting['bank_sumber_yayasan'] == 'BSI') ? 'selected' : '';?>>Bank BSI</option>
                  <option value="MANDIRI" <?=(isset($setting['bank_sumber_yayasan']) && $setting['bank_sumber_yayasan'] == 'MANDIRI') ? 'selected' : '';?>>Bank Mandiri</option>
                  <option value="BCA" <?=(isset($setting['bank_sumber_yayasan']) && $setting['bank_sumber_yayasan'] == 'BCA') ? 'selected' : '';?>>Bank BCA</option>
                </select>
                <div class="form-text small">Akun bank resmi yayasan.</div>
              </div>

              <div class="col-md-8 col-12">
                <label class="form-label fw-bold" for="rekening_debit_yayasan">
                  <i class="ti ti-numbers me-1"></i> Nomor Rekening Debit Yayasan
                </label>
                <input type="text" class="form-control fw-bold font-monospace fs-5" id="rekening_debit_yayasan" name="rekening_debit_yayasan" 
                       value="<?=htmlspecialchars(isset($setting['rekening_debit_yayasan']) ? $setting['rekening_debit_yayasan'] : '219701000399305');?>" required>
                <div class="form-text small">Nomor rekening debet untuk file transfer mutasi bank (BRI CMS).</div>
              </div>
            </div>
          </div>
        </div>

        <!-- TOMBOL SIMPAN -->
        <div class="text-end mb-4">
          <button type="submit" class="btn btn-primary px-4 py-2 fs-6 shadow">
            <i class="ti ti-device-floppy me-2"></i> Simpan Seluruh Pengaturan
          </button>
        </div>
      </form>
    </div>

    <!-- KOLOM KANAN: KALENDER HARI KHUSUS PULANG CEPAT -->
    <div class="col-xl-4 col-lg-5 col-12 mb-4">
      <div class="card border-top border-warning border-3 h-100">
        <div class="card-header pb-2 d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0 d-flex align-items-center text-warning">
              <i class="ti ti-calendar-event me-2 fs-4"></i> Kalender Hari Khusus
            </h5>
            <small class="text-muted">Waiver Pulang Lebih Awal (SOP 3.a)</small>
          </div>
          <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahHariKhusus">
            <i class="ti ti-plus me-1"></i> Tambah
          </button>
        </div>

        <div class="card-body pt-2">
          <p class="small text-muted mb-3">
            Guru yang pulang mendahului jadwal pada tanggal terdaftar di bawah ini <b>TIDAK DIKENAKAN PENALTI</b> (bebas sanksi pelanggaran pulang awal) karena adanya agenda resmi sekolah (Raker, Rapor, Ujian, dll).
          </p>

          <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
            <table class="table table-sm table-hover border">
              <thead class="table-light">
                <tr>
                  <th>Tanggal</th>
                  <th>Keterangan Agenda</th>
                  <th class="text-center" style="width: 50px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($hari_khusus)) { ?>
                  <?php foreach ($hari_khusus as $hk) : ?>
                    <tr>
                      <td class="text-nowrap">
                        <span class="fw-bold text-dark"><?=date('d/m/Y', strtotime($hk['tanggal']));?></span>
                        <div class="small text-muted"><?=date('D', strtotime($hk['tanggal']));?></div>
                      </td>
                      <td>
                        <span class="text-dark small fw-semibold"><?=htmlspecialchars($hk['keterangan']);?></span>
                        <div>
                          <span class="badge bg-label-success p-1 small" style="font-size: 10px;">
                            <i class="ti ti-circle-check"></i> Bebas Sanksi
                          </span>
                        </div>
                      </td>
                      <td class="text-center align-middle">
                        <button type="button" 
                                class="btn btn-sm btn-icon btn-label-danger rounded-pill btn-confirm-delete" 
                                data-url="<?=base_url('gaji/setting/delete_hari_khusus/'.$hk['id']);?>" 
                                data-title="Hapus Hari Khusus?" 
                                data-text="Apakah Anda yakin ingin menghapus tanggal <b><?=date('d/m/Y', strtotime($hk['tanggal']));?></b> dari Kalender Hari Khusus?" 
                                title="Hapus Agenda">
                          <i class="ti ti-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                      <i class="ti ti-calendar-off fs-2 d-block mb-1 text-secondary"></i>
                      Belum ada tanggal agenda hari khusus yang didaftarkan.
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

<!-- MODAL TAMBAH HARI KHUSUS -->
<div class="modal fade" id="modalTambahHariKhusus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title d-flex align-items-center">
          <i class="ti ti-calendar-plus me-2 text-primary"></i> Tambah Agenda Hari Khusus Pulang Cepat
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?=base_url('gaji/setting/add_hari_khusus');?>" method="POST">
        <div class="modal-body">
          <div class="alert alert-info py-2 px-3 small mb-3">
            <i class="ti ti-info-circle me-1"></i> Agenda pada tanggal ini akan mengecualikan pelanggaran kepulangan cepat di mesin evaluasi penggajian.
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="input_tanggal">Tanggal Agenda Sekolah <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="input_tanggal" name="tanggal" value="<?=date('Y-m-d');?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold" for="input_keterangan">Keterangan / Nama Agenda <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="input_keterangan" name="keterangan" placeholder="Contoh: Rapat Kerja Dewan Guru / Pembagian Rapor" required>
            <div class="form-text small">Tuliskan nama kegiatan resmi sekolah.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Perlakuan Absen Pulang</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="boleh_pulang_awal" id="bpa_y" value="y" checked>
              <label class="form-check-label" for="bpa_y">
                <strong>Bebas Sanksi (Boleh Pulang Lebih Awal)</strong> - Rekomendasi
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="boleh_pulang_awal" id="bpa_n" value="n">
              <label class="form-check-label" for="bpa_n">
                Tetap Wajib Mengikuti Jam Kerja Normal
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Simpan ke Kalender
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
