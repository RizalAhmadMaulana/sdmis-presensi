<?php
/**
 * Toast Notification & Confirmation Modal Component
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Menyediakan floating toast modern (auto-dismiss 3 detik) & konfirmasi hapus interaktif (tanpa dialog browser)
 */

$toast = $this->session->flashdata('gaji_toast');
$legacy_msg = $this->session->flashdata('message');

// Langsung hapus dari session agar TIDAK PERNAH muncul lagi di halaman lain
$this->session->unset_userdata('gaji_toast');
$this->session->unset_userdata('message');

$toast_type = '';
$toast_text = '';

if (!empty($toast) && is_array($toast)) {
    $toast_type = $toast['type'] ?? 'success';
    $toast_text = $toast['message'] ?? '';
} elseif (!empty($legacy_msg)) {
    if (strpos($legacy_msg, 'alert-danger') !== false) {
        $toast_type = 'danger';
    } elseif (strpos($legacy_msg, 'alert-warning') !== false) {
        $toast_type = 'warning';
    } else {
        $toast_type = 'success';
    }
    // Bersihkan tag HTML
    $toast_text = trim(strip_tags($legacy_msg));
    $toast_text = str_replace(["\r", "\n"], ' ', $toast_text);
    $toast_text = preg_replace('/\s+/', ' ', $toast_text);
}
?>

<style>
/* Toast Floating Pill Modern - Sesuai Permintaan User */
.gaji-toast-container {
  position: fixed;
  top: 24px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 999999;
  pointer-events: none;
}
.gaji-toast-pill {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background-color: #1e293b; /* Dark Slate Charcoal */
  color: #ffffff;
  padding: 10px 24px;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35), 0 8px 10px -6px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.15);
  animation: gajiToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  pointer-events: auto;
}
.gaji-toast-icon {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  flex-shrink: 0;
}
.gaji-toast-pill.success .gaji-toast-icon {
  background-color: #10b981;
  color: #ffffff;
}
.gaji-toast-pill.danger .gaji-toast-icon {
  background-color: #ef4444;
  color: #ffffff;
}
.gaji-toast-pill.warning .gaji-toast-icon {
  background-color: #f59e0b;
  color: #ffffff;
}
@keyframes gajiToastSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@keyframes gajiToastSlideOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-20px);
  }
}
</style>

<?php if (!empty($toast_text)) : ?>
<div class="gaji-toast-container" id="gajiToastBox">
  <div class="gaji-toast-pill <?=$toast_type;?>">
    <div class="gaji-toast-icon">
      <?php if ($toast_type === 'success') { ?>
        <i class="ti ti-check"></i>
      <?php } elseif ($toast_type === 'danger') { ?>
        <i class="ti ti-x"></i>
      <?php } else { ?>
        <i class="ti ti-alert-circle"></i>
      <?php } ?>
    </div>
    <div class="gaji-toast-text">
      <?=htmlspecialchars($toast_text);?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(function() {
    var box = document.getElementById('gajiToastBox');
    if (box) {
      var pill = box.querySelector('.gaji-toast-pill');
      if (pill) {
        pill.style.animation = 'gajiToastSlideOut 0.4s ease forwards';
      }
      setTimeout(function() {
        if (box && box.parentNode) box.parentNode.removeChild(box);
      }, 400);
    }
  }, 3200); // Tampil selama 3.2 detik lalu otomatis menghilang
});
</script>
<?php endif; ?>

<style>
/* Pastikan Modal Konfirmasi selalu di paling atas dan tombolnya 100% clickable */
#modalConfirmDeleteGaji {
  z-index: 99999 !important;
}
#modalConfirmDeleteGaji .modal-dialog {
  z-index: 100000 !important;
  pointer-events: auto !important;
}
#modalConfirmDeleteGaji .modal-content {
  pointer-events: auto !important;
  position: relative;
  z-index: 100001 !important;
}
#modalConfirmDeleteGaji .btn {
  pointer-events: auto !important;
  cursor: pointer !important;
  position: relative;
  z-index: 100002 !important;
}
.modal-backdrop.gaji-confirm-backdrop {
  z-index: 99998 !important;
}
</style>

<!-- MODAL KONFIRMASI HAPUS INTERAKTIF (MENGGANTIKAN DIALOG BROWSER LOCALHOST SAYS) -->
<div class="modal fade" id="modalConfirmDeleteGaji" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-body text-center p-4">
        <div class="avatar avatar-xl bg-label-danger mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
          <i class="ti ti-trash fs-1 text-danger"></i>
        </div>
        <h5 class="fw-bold mb-2 text-dark" id="modalConfirmDeleteTitle">Konfirmasi Hapus</h5>
        <p class="text-muted small mb-4" id="modalConfirmDeleteText">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-label-secondary px-3 rounded-pill" data-bs-dismiss="modal">
            Batal
          </button>
          <a href="#" id="modalConfirmDeleteBtn" class="btn btn-danger px-4 rounded-pill shadow-sm">
            <i class="ti ti-trash me-1"></i> Ya, Hapus
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var modalEl = document.getElementById('modalConfirmDeleteGaji');

  // Pindahkan modal langsung ke <body> agar sejajar dengan .modal-backdrop
  // Ini mencegah modal terjebak di dalam stacking context .container-xxl / .layout-page
  if (modalEl && modalEl.parentNode !== document.body) {
    document.body.appendChild(modalEl);
  }

  // Atur z-index backdrop saat modal ditampilkan
  if (modalEl) {
    modalEl.addEventListener('show.bs.modal', function() {
      setTimeout(function() {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 0) {
          var latestBackdrop = backdrops[backdrops.length - 1];
          latestBackdrop.style.zIndex = '99998';
          latestBackdrop.classList.add('gaji-confirm-backdrop');
        }
      }, 15);
    });
  }

  // Event listener tombol konfirmasi "Ya, Hapus"
  var confirmBtn = document.getElementById('modalConfirmDeleteBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var targetUrl = this.getAttribute('data-href') || this.getAttribute('href');
      if (targetUrl && targetUrl !== '#' && targetUrl !== '') {
        window.location.href = targetUrl;
      }
    });
  }

  // Delegate event click untuk semua tombol yang memiliki class .btn-confirm-delete
  document.body.addEventListener('click', function(e) {
    var target = e.target.closest('.btn-confirm-delete');
    if (target) {
      e.preventDefault();
      e.stopPropagation();

      var url = target.getAttribute('data-url') || target.getAttribute('href');
      var title = target.getAttribute('data-title') || 'Konfirmasi Hapus';
      var text = target.getAttribute('data-text') || 'Apakah Anda yakin ingin menghapus data ini?';

      document.getElementById('modalConfirmDeleteTitle').textContent = title;
      document.getElementById('modalConfirmDeleteText').innerHTML = text;

      var btnHapus = document.getElementById('modalConfirmDeleteBtn');
      btnHapus.setAttribute('href', url);
      btnHapus.setAttribute('data-href', url);

      if (modalEl) {
        var bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl, {
          backdrop: true,
          keyboard: true,
          focus: true
        });
        bsModal.show();
      }
    }
  });
});
</script>
