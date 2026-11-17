<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title"><?=$namalabel;?></h5>
      <div class="text-start">
        <a href="<?=base_url('karyawan/data/add');?>" class="btn btn-secondary btn-primary btn-sm"><i class="ti ti-plus me-md-1"></i> Tambah Data</a>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <?=$this->session->flashdata('message');?>
      <table class="table border-top" id="dataTable">
        <thead>
          <tr>
            <th class="w-s-n">ID Karyawan</th>
            <th class="w-s-n">Nama Lengkap</th>
            <th class="w-s-n">No WhatsApp</th>
            <th>Email</th>
            <th class="text-center">Face Biometrik</th>
            <th>Status</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; foreach ($datas as $row) : ?>
          <tr>
            <td><?= $row['id_pegawai'];?></td>
            <td><?= $row['nama_pegawai'];?></td>
            <td><?= $row['nomor_pegawai'];?></td>
            <td><?= $row['email_pegawai'];?></td>
            <td class="text-center">
              <?php if (isset($row['face_id']) && !empty($row['face_id']) && isset($row['face_verified']) && $row['face_verified'] == 'y') { ?>
                <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#faceModal<?=$row['pegawai_id'];?>" title="Wajah Terverifikasi - Klik untuk detail">
                  <i class="ti ti-scan me-1"></i> Terverifikasi
                </button>
              <?php } else if (isset($row['face_id']) && !empty($row['face_id']) && isset($row['face_verified']) && $row['face_verified'] == 'n') { ?>
                <button type="button" class="btn btn-xs btn-warning rounded-pill px-2 py-1 text-white" data-bs-toggle="modal" data-bs-target="#faceModal<?=$row['pegawai_id'];?>" title="Menunggu Persetujuan Admin - Klik untuk verifikasi">
                  <i class="ti ti-alert-circle me-1"></i> Menunggu Verifikasi
                </button>
              <?php } else { ?>
                <button type="button" class="btn btn-xs btn-label-secondary rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#faceModal<?=$row['pegawai_id'];?>" title="Belum Merekam Wajah">
                  <i class="ti ti-user-x me-1"></i> Belum Rekam
                </button>
              <?php } ?>
            </td>
            <td>
              <?php if ($row['is_status']=='y') { ?>
              <span class="badge bg-label-success">Aktif</span>
              <?php } else if ($row['is_status']=='n') { ?>
              <span class="badge bg-label-danger">Tidak Aktif</span>
              <?php } else { ?>
              <span class="badge bg-label-secondary">Unknown</span>
              <?php } ?>
            </td>
            <td align="right">
              <a href="<?=base_url('attendance_record/detail/'.$row['pegawai_id']);?>" class="btn p-1" title="Rekap Kehadiran">
                <i class="ti ti-checklist"></i>
              </a>
              <a href="<?=base_url('karyawan/data/edit/'.$row['pegawai_id']);?>" class="btn p-1">
                <i class="ti ti-edit"></i>
              </a>
              <a href="#" class="btn p-1" data-bs-toggle="modal" data-bs-target="#delRow<?=$row['pegawai_id'];?>">
                <i class="ti ti-trash"></i>
              </a>
              <!-- Modal Face Biometrik -->
              <div class="modal fade" id="faceModal<?=$row['pegawai_id'];?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content p-3">
                    <div class="modal-header border-bottom pb-2">
                      <h5 class="modal-title d-flex align-items-center">
                        <i class="ti ti-scan me-2 text-primary"></i> Biometrik Wajah: <?=htmlspecialchars($row['nama_pegawai']);?>
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center pt-3">
                      <?php if (isset($row['face_id']) && !empty($row['face_id'])) { ?>
                        <?php if (!empty($row['foto_sample'])) { ?>
                          <div class="mb-3">
                            <img src="<?=base_url($row['foto_sample']);?>" alt="Foto Sampel Wajah" class="img-fluid rounded border shadow-sm" style="max-height: 240px; object-fit: cover;">
                          </div>
                        <?php } ?>

                        <div class="mb-3">
                          <?php if (isset($row['face_verified']) && $row['face_verified'] == 'y') { ?>
                            <span class="badge bg-success fs-6"><i class="ti ti-circle-check me-1"></i> Status: Terverifikasi & Aktif</span>
                            <p class="text-muted mt-2 small">Guru ini telah disetujui dan dapat melakukan absensi dengan deteksi wajah pada web presensi TemanMIS.</p>
                          <?php } else { ?>
                            <span class="badge bg-warning text-dark fs-6"><i class="ti ti-clock me-1"></i> Menunggu Verifikasi Admin</span>
                            <p class="text-muted mt-2 small">Guru telah merekam biometrik 5 sudut wajah melalui aplikasi presensi. Silakan tinjau foto wajah di atas dan klik <b>Setujui Wajah</b>.</p>
                          <?php } ?>
                          <?php if (!empty($row['face_updated_at'])) { ?>
                            <div class="text-muted small"><i class="ti ti-calendar me-1"></i> Waktu rekam: <?=date('d M Y H:i', strtotime($row['face_updated_at']));?> WIB</div>
                          <?php } ?>
                        </div>
                      <?php } else { ?>
                        <div class="py-4">
                          <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
                            <i class="ti ti-user-x fs-1"></i>
                          </div>
                          <h6 class="mb-1">Belum Ada Data Biometrik</h6>
                          <p class="text-muted small mb-0">Guru ini belum melakukan pendaftaran rekam wajah 5 sudut di aplikasi presensi <b>TemanMIS</b>.</p>
                        </div>
                      <?php } ?>
                    </div>
                    <div class="modal-footer border-top pt-2 justify-content-between">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
                      <div>
                        <?php if (isset($row['face_id']) && !empty($row['face_id']) && isset($row['face_verified']) && $row['face_verified'] == 'n') { ?>
                          <a href="<?=base_url('karyawan/data/face_reset/'.$row['pegawai_id']);?>" class="btn btn-label-danger me-2" onclick="return confirm('Apakah Anda yakin ingin menolak & mereset pendaftaran wajah guru ini?');">
                            <i class="ti ti-x me-1"></i> Tolak & Reset
                          </a>
                          <a href="<?=base_url('karyawan/data/face_approve/'.$row['pegawai_id']);?>" class="btn btn-success">
                            <i class="ti ti-check me-1"></i> Setujui Wajah
                          </a>
                        <?php } else if (isset($row['face_id']) && !empty($row['face_id']) && isset($row['face_verified']) && $row['face_verified'] == 'y') { ?>
                          <a href="<?=base_url('karyawan/data/face_reset/'.$row['pegawai_id']);?>" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin mereset biometrik wajah guru ini? Guru harus merekam ulang scan wajah di web presensi.');">
                            <i class="ti ti-refresh me-1"></i> Reset Wajah
                          </a>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Konfirmasi Hapus -->
              <div class="modal fade" id="delRow<?=$row['pegawai_id'];?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-simple modal-enable-otp modal-dialog-centered">
                  <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Konfirmasi</h3>
                        <p>Yakin ingin menghapus data ini ?</p>
                      </div>
                      <div class="col-12 text-center pt-3">
                        <button
                          type="button"
                          class="btn btn-label-secondary me-sm-3 me-1"
                          data-bs-dismiss="modal"
                          aria-label="Close">
                          Batal
                        </button>
                        <a href="<?=base_url('karyawan/data/hapus/'.$row['pegawai_id']);?>" class="btn btn-danger">Ya, Hapus</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          <?php $no++; endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- / Content -->