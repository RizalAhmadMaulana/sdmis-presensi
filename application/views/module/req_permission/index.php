<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title"><?=$namalabel;?></h5>
      <div class="text-start">
        <a href="<?=base_url('req_permission/add');?>" class="btn btn-secondary btn-primary btn-sm"><i class="ti ti-plus me-md-1"></i> Tambah Data</a>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <?=$this->session->flashdata('message');?>
      <table class="table border-top" id="dataTableatt">
        <thead>
          <tr>
            <th class="w-s-n">Tanggal</th>
            <th>Kategori</th>
            <th>J.Masuk</th>
            <th>J.Keluar</th>
            <th>Status</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $no=1; foreach ($datas as $row) : 

            if ($row['tipe_request']=='s') {
              $bgs = 'Sakit';
            }else if ($row['tipe_request']=='i') {
              $bgs = 'Izin';
            }else if ($row['tipe_request']=='c') {
              $bgs = 'Cuti';
            }else if ($row['tipe_request']=='cb') {
              $bgs = 'Cuti Bersama';
            }else if ($row['tipe_request']=='ct') {
              $bgs = 'Cuti Tahunan';
            }else if ($row['tipe_request']=='csh') {
              $bgs = 'Cuti Setengah Hari';
            }else if ($row['tipe_request']=='tl') {
              $bgs = 'Tugas Luar';
            }else if ($row['tipe_request']=='lm') {
              $bgs = 'Lembur';
            }else{
              $bgs = '';
            }
          ?>
          <tr>
            <td class="w-s-n">
              <?= indo($row['tanggal_request']);?>
              <?php if ($row['tanggal_request_end']!='') { ?>
              s/d<br/><?= indo($row['tanggal_request_end']);?>
              <?php } ?>
            </td>
            <td class="w-s-n"><span class="btn btn-label-dark btn-sm ft-11"><?=$bgs;?></span></td>
            <td><?= $row['r_jam_masuk'];?></td>
            <td><?= $row['r_jam_keluar'];?></td>
            <td>
              <?php if ($row['is_status']==0) { ?>
              <span class="badge bg-label-warning">Pending</span>
              <?php } else if ($row['is_status']==1) { ?>
              <span class="badge bg-label-success">Approved</span>
              <?php } else if ($row['is_status']==2) { ?>
              <span class="badge bg-label-danger">Reject</span>
              <?php }  else { ?>
              <span class="badge bg-label-secondary">Unknown</span>
              <?php } ?>
            </td>
            <td align="right">
              <a href="<?=base_url('req_permission/edit/'.$row['request_izin_id']);?>" class="btn p-1">
                <i class="ti ti-edit"></i>
              </a>
              <a href="#" class="btn p-1" data-bs-toggle="modal" data-bs-target="#delRow<?=$row['request_izin_id'];?>">
                <i class="ti ti-trash"></i>
              </a>
              <!-- Konfirmasi Hapus -->
              <div class="modal fade" id="delRow<?=$row['request_izin_id'];?>" tabindex="-1" aria-hidden="true">
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
                        <a href="<?=base_url('req_permission/hapus/'.$row['request_izin_id']);?>" class="btn btn-danger">Ya, Hapus</a>
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