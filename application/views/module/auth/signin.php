  <body>
    <!-- Layout container -->
    <div class="layout-page" style="display:block;">

      <!-- Navbar -->
      <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
          <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
              <img src="<?=base_url('assets/temp/assets/logo/client/logo.png');?>" width="70">
              <span class="d-none d-md-inline-block"><b>&nbsp;&nbsp;Mentari Islamic School</b></span>
            </div>
          </div>

          <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link"
                href="javascript:void(0);"
                data-bs-toggle="modal"
                data-bs-target="#addNewLogin"
                aria-expanded="false">
                <i class="ti ti-user-circle ti-md"></i>&nbsp;&nbsp;Login
              </a>
            </li>
          </ul>
        </div>
      </nav>
      <!-- / Navbar -->

      <!-- Content wrapper -->
      <div class="content-wrapper">
            
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">

          <?=$this->session->flashdata('message');?>

          <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Teman MIS .. /</span> Berita</h4>
          <!-- Teams Cards -->
          <div class="row g-4">
            
            <div class="col-xl-3 col-lg-4 col-md-5 col-sm-5">
              <div class="mb-3 w-100">
                <button class="btn btn-info btn-sm" type="button" data-bs-target="#instagramCarousel" data-bs-slide="prev">
                &nbsp;&nbsp;<i class="fas fa-chevron-left"></i>&nbsp;&nbsp;
                </button>
                <button class="btn btn-info btn-sm" type="button" data-bs-target="#instagramCarousel" data-bs-slide="next">
                &nbsp;&nbsp;<i class="fas fa-chevron-right"></i>&nbsp;&nbsp;
                </button>
              </div>
              <div id="instagramCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-inner">

                  <?php $no=1; foreach ($sliders as $row) : if($no==1) $exts='active'; else $exts=''; ?>
                  <?php if($row['is_tipe']==1){ ?>
                    <div class="carousel-item <?=$exts?>">
                      <blockquote class="instagram-media w-100" style="min-width:100px !important;" data-instgrm-captioned data-instgrm-permalink="<?=$row['isi_slider'];?>" data-instgrm-version="14"></blockquote>
                    </div>
                  <?php }else{ ?>
                    <div class="carousel-item <?=$exts?>">
                      <img src="<?=base_url($row['gambar_slider']);?>" class="img-fluid rounded">
                    </div>
                  <?php } ?>
                  <?php $no++; endforeach; ?>
                  
                </div>
              </div>
            </div>

            <div class="col-xl-9 col-lg-8 col-md-7 col-sm-7">
              <div class="row">
                <div class="col-xl-12 col-lg-12">
                  <div class="row">
                    <?php $no=1; foreach ($datas as $row) : ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex align-items-center mb-3">
                            <a href="<?=base_url('news/detail/'.$row['berita_id']);?>" class="d-flex align-items-center text-overflow-ellips">
                              <div class="me-2 text-body h5 mb-0"><?=$row['nama_berita'];?></div>
                            </a>
                          </div>
                          <div class="mb-3 inner-htmlinfo" style="min-height:90px; max-height:90px; overflow:hidden;">
                            <?=$row['isi_berita'];?>
                          </div>
                          <div class="d-flex align-items-center pt-1">
                            <div class="d-flex align-items-center">
                              <div class="ft-12"><i><?=indo($row['created_at']);?></i></div>
                            </div>
                            <div class="ms-auto">
                              <a href="<?=base_url('news/detail/'.$row['berita_id']);?>"><span class="badge bg-label-dark">Lihat Detail</span></a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php $no++; endforeach; ?>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!--/ Teams Cards -->
        </div>
        <!-- / Content -->

      </div>
    </div>