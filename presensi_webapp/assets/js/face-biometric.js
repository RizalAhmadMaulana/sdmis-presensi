/**
 * Face Biometric System - SD Mentari Islamic School (TemanMIS)
 * Multi-Angle Guided Scanner & Attendance Verification Bridge
 * Dynamically handles Localhost vs Production (Hostinger)
 */
(function(window) {
  'use strict';

  // 1. Dynamic API Base URL (Localhost, LAN IP, & Hostinger Production)
  function getDynamicApiBase() {
    const host = window.location.hostname;
    const isLocal = host === 'localhost' || host === '127.0.0.1' || host.startsWith('192.168.') || host.startsWith('10.') || host.startsWith('172.');
    if (isLocal) {
      const path = window.location.pathname;
      const sdmisIdx = path.indexOf('/sdmis/');
      const rootPrefix = sdmisIdx !== -1 ? path.substring(0, sdmisIdx + 7) : '/mis_dev/sdmis/';
      return window.location.origin + rootPrefix + 'api/';
    }
    return 'https://sdmis.sch.id/api/';
  }
  const API_BASE = getDynamicApiBase();
  const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

  // 2. Global State
  const state = {
    isModelsLoaded: false,
    currentUser: null,
    biometricStatus: null, // { is_registered, is_verified, foto_sample }
    activeStream: null,
    currentStepIndex: 0,
    lastAttendanceVerification: null,
    capturedData: {
      front: null,
      right: null,
      left: null,
      up: null,
      down: null,
      foto_sample: null
    },
    steps: [
      { id: 'front', label: 'Hadap Depan', title: 'Hadap Lurus ke Depan', desc: 'Posisikan wajah tepat di lingkaran dan tatap lurus kamera' },
      { id: 'right', label: 'Tengok Kanan', title: 'Tengok Sedikit ke Kanan ➡️', desc: 'Putar kepala Anda perlahan sedikit ke arah kanan' },
      { id: 'left', label: 'Tengok Kiri', title: 'Tengok Sedikit ke Kiri ⬅️', desc: 'Putar kepala Anda perlahan sedikit ke arah kiri' },
      { id: 'up', label: 'Hadap Atas', title: 'Angkat Kepala / Dagu ⬆️', desc: 'Angkat dagu Anda sedikit ke atas' },
      { id: 'down', label: 'Hadap Bawah', title: 'Tundukkan Kepala / Dagu ⬇️', desc: 'Tundukkan kepala Anda sedikit ke arah bawah' }
    ]
  };

  // 3. Audio Chime (Web Audio API Synthesizer)
  function playTone(freq = 880, duration = 0.15) {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(freq, ctx.currentTime);
      gain.gain.setValueAtTime(0.2, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + duration);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + duration);
      setTimeout(() => ctx.close(), (duration * 1000) + 100);
    } catch (e) {
      console.warn('Audio chime error:', e);
    }
  }

  // 3b. Custom Floating Toast Notification (Auto-Dismissing Modern Pop-up)
  function showToast(title, message, type = 'success', duration = 4500) {
    let container = document.getElementById('face-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'face-toast-container';
      container.className = 'face-toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `face-toast-card ${type}`;

    let iconSvg = '';
    if (type === 'success') {
      iconSvg = `<div class="face-toast-icon success">
        <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
      </div>`;
    } else if (type === 'warning') {
      iconSvg = `<div class="face-toast-icon warning">
        <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>`;
    } else {
      iconSvg = `<div class="face-toast-icon error">
        <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
      </div>`;
    }

    toast.innerHTML = `
      ${iconSvg}
      <div class="face-toast-body">
        <div class="face-toast-title">${title}</div>
        <div class="face-toast-msg">${message}</div>
      </div>
      <button class="face-toast-close" type="button" aria-label="Tutup">✕</button>
      <div class="face-toast-progress" style="animation-duration: ${duration}ms;"></div>
    `;

    container.appendChild(toast);

    let isDismissed = false;
    const dismiss = () => {
      if (isDismissed) return;
      isDismissed = true;
      toast.classList.add('hide');
      setTimeout(() => {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
      }, 350);
    };

    const closeBtn = toast.querySelector('.face-toast-close');
    if (closeBtn) closeBtn.onclick = (e) => { e.stopPropagation(); dismiss(); };

    setTimeout(dismiss, duration);
  }

  // 4. Model Loader
  async function loadFaceModels() {
    if (state.isModelsLoaded) return true;
    if (typeof faceapi === 'undefined') {
      console.error('FaceAPI script is not yet loaded.');
      return false;
    }
    try {
      console.log('Loading Face-API neural network models...');
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
      ]);
      state.isModelsLoaded = true;
      console.log('Face-API models successfully loaded.');
      return true;
    } catch (err) {
      console.warn('Failed loading models from primary CDN, retrying...', err);
      try {
        await Promise.all([
          faceapi.nets.tinyFaceDetector.loadFromUri('https://raw.githubusercontent.com/vladmandic/face-api/master/model/'),
          faceapi.nets.faceLandmark68Net.loadFromUri('https://raw.githubusercontent.com/vladmandic/face-api/master/model/'),
          faceapi.nets.faceRecognitionNet.loadFromUri('https://raw.githubusercontent.com/vladmandic/face-api/master/model/')
        ]);
        state.isModelsLoaded = true;
        return true;
      } catch (e2) {
        console.error('All Face-API model load attempts failed:', e2);
        return false;
      }
    }
  }

  // 5. Auth / User Session Retriever
  async function getCurrentUser() {
    if (state.currentUser && state.currentUser.pegawai_id) {
      return state.currentUser;
    }

    // Try reading localStorage directly
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && key.includes('storage_auth')) {
        try {
          const val = JSON.parse(localStorage.getItem(key));
          if (val && val.pegawai_id) {
            state.currentUser = val;
            return val;
          }
        } catch (e) {}
      }
    }

    // Try IndexedDB _ionicstorage
    try {
      const req = indexedDB.open('_ionicstorage');
      await new Promise(resolve => {
        req.onsuccess = (e) => {
          const db = e.target.result;
          if (!db.objectStoreNames.contains('_ionickv')) return resolve();
          const tx = db.transaction('_ionickv', 'readonly');
          const store = tx.objectStore('_ionickv');
          const getReq = store.get('storage_auth');
          getReq.onsuccess = () => {
            if (getReq.result && getReq.result.pegawai_id) {
              state.currentUser = getReq.result;
            }
            resolve();
          };
          getReq.onerror = () => resolve();
        };
        req.onerror = () => resolve();
      });
    } catch (e) {}

    return state.currentUser;
  }

  // 6. Check Face Status from Backend
  async function fetchFaceStatus(userId) {
    if (!userId) return null;
    try {
      const res = await fetch(API_BASE + 'rest_post/face_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId })
      });
      const data = await res.json();
      state.biometricStatus = data;
      updateUIStatus(data);
      return data;
    } catch (e) {
      console.warn('Error fetching face status:', e);
      return null;
    }
  }

  // 7. UI: Update Home Card and FAB
  function getOrInjectHomeCard() {
    let card = document.getElementById('face-id-home-card');
    if (card) return card;

    const homeContent = document.querySelector('app-home ion-content') || 
                        document.querySelector('ion-router-outlet ion-content') || 
                        document.querySelector('ion-content');
    if (!homeContent) return null;

    card = document.createElement('div');
    card.id = 'face-id-home-card';
    card.className = 'face-id-home-card';
    card.style.display = 'none';

    const target = homeContent.querySelector('ion-grid') || 
                   homeContent.querySelector('.bg-putih') || 
                   homeContent.firstElementChild;
    if (target && target.parentNode) {
      target.parentNode.insertBefore(card, target);
    } else {
      homeContent.appendChild(card);
    }
    return card;
  }

  function updateUIStatus(statusData) {
    const fab = document.getElementById('face-id-fab');
    if (fab) {
      fab.classList.remove('verified', 'unregistered');
      if (statusData && statusData.is_verified) {
        fab.classList.add('verified');
      } else if (!statusData || !statusData.is_registered) {
        fab.classList.add('unregistered');
      }
    }

    const homeCard = getOrInjectHomeCard();
    if (homeCard) {
      if (!statusData || !statusData.is_registered) {
        homeCard.className = 'face-id-home-card danger';
        homeCard.innerHTML = `
          <div class="card-text">
            <h4>📸 Pendaftaran Wajah Diperlukan</h4>
            <p>Silakan scan biometrik wajah 5 sudut untuk mengaktifkan fitur absensi.</p>
          </div>
          <button class="btn-scan-face" onclick="window.FaceBiometric.openScanner()">Scan Wajah</button>
        `;
        homeCard.style.display = 'flex';
      } else if (statusData && !statusData.is_verified) {
        homeCard.className = 'face-id-home-card warning';
        homeCard.innerHTML = `
          <div class="card-text">
            <h4>⏳ Menunggu Verifikasi Admin</h4>
            <p>Data wajah Anda telah terkirim dan sedang menunggu persetujuan admin portal SDMIS.</p>
          </div>
          <button class="btn-scan-face" style="background:#d97706;" onclick="window.FaceBiometric.openScanner()">Scan Ulang</button>
        `;
        homeCard.style.display = 'flex';
      } else if (statusData && statusData.is_verified) {
        homeCard.className = 'face-id-home-card success';
        homeCard.innerHTML = `
          <div class="card-text">
            <h4>✅ Face ID Terverifikasi</h4>
            <p>Biometrik wajah Anda aktif. Anda dapat melakukan absensi dengan kamera.</p>
          </div>
          <button class="btn-scan-face" style="background:#16a34a;" onclick="window.FaceBiometric.openScanner()">Detail</button>
        `;
        homeCard.style.display = 'flex';
      }
    }
  }

  // 8. Inject DOM Elements (FAB & Modal)
  function injectElements() {
    if (document.getElementById('face-id-fab')) return;

    // A. Floating Button
    const fab = document.createElement('button');
    fab.id = 'face-id-fab';
    fab.className = 'face-id-fab unregistered';
    fab.innerHTML = `
      <svg class="fab-icon" viewBox="0 0 24 24">
        <path d="M4 8V6a2 2 0 0 1 2-2h2M4 16v2a2 2 0 0 0 2 2h2M16 4h2a2 2 0 0 1 2 2v2M16 20h2a2 2 0 0 0 2-2v-2M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 0 0 5 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
      </svg>
      <span>Face ID</span>
      <span class="fab-badge"></span>
    `;
    fab.onclick = () => window.FaceBiometric.openScanner();
    document.body.appendChild(fab);

    // B. Fullscreen Scanner Modal
    const modal = document.createElement('div');
    modal.id = 'face-scanner-modal';
    modal.className = 'face-scanner-overlay';
    modal.innerHTML = `
      <div class="face-scanner-header">
        <div>
          <h3>Perekaman Biometrik Wajah</h3>
          <small style="color:#94a3b8;">Panduan Pemindaian 5 Sudut (Anti Titip Absen)</small>
        </div>
        <button class="face-scanner-close" onclick="window.FaceBiometric.closeScanner()">✕</button>
      </div>

      <div class="face-angle-stepper" id="face-stepper">
        <div class="angle-step-pill active" id="step-pill-0">1. Depan</div>
        <div class="angle-step-pill" id="step-pill-1">2. Kanan</div>
        <div class="angle-step-pill" id="step-pill-2">3. Kiri</div>
        <div class="angle-step-pill" id="step-pill-3">4. Atas</div>
        <div class="angle-step-pill" id="step-pill-4">5. Bawah</div>
      </div>

      <div class="face-viewfinder-container">
        <video id="face-scanner-video" autoplay playsinline muted></video>
        <canvas id="face-scanner-canvas"></canvas>
        <div class="face-scanner-radar scanning" id="face-scanner-radar"></div>
      </div>

      <div class="face-prompt-box">
        <div class="prompt-angle-title" id="face-prompt-title">Hadap Lurus ke Depan</div>
        <p class="prompt-instruction" id="face-prompt-desc">Posisikan wajah tepat di lingkaran dan tatap lurus kamera.</p>
        <div id="face-status-text" style="font-size:11px; color:#38bdf8; margin-top:6px; font-weight:600;">Menyiapkan kamera & model AI...</div>
      </div>

      <div class="face-scanner-actions">
        <button class="btn-cancel-scan" onclick="window.FaceBiometric.closeScanner()">Batal</button>
        <button class="btn-manual-snap" id="btn-manual-snap" onclick="window.FaceBiometric.snapCurrentAngle()">Ambil Sudut Ini</button>
      </div>
    `;
    document.body.appendChild(modal);
  }

  // 9. Camera Lifecycle for Scanner
  async function startCamera() {
    const video = document.getElementById('face-scanner-video');
    if (!video) return;

    try {
      const constraints = {
        video: {
          facingMode: 'user',
          width: { ideal: 640 },
          height: { ideal: 480 }
        },
        audio: false
      };
      const stream = await navigator.mediaDevices.getUserMedia(constraints);
      state.activeStream = stream;
      video.srcObject = stream;
      await new Promise(res => { video.onloadedmetadata = () => res(); });
      video.play();
      return true;
    } catch (err) {
      showToast('Izin Kamera Dibutuhkan', 'Gagal mengakses kamera: ' + err.message + '. Pastikan izin kamera telah diberikan di browser.', 'warning', 5000);
      closeScanner();
      return false;
    }
  }

  function stopCamera() {
    if (state.activeStream) {
      state.activeStream.getTracks().forEach(track => track.stop());
      state.activeStream = null;
    }
    const video = document.getElementById('face-scanner-video');
    if (video) video.srcObject = null;
  }

  // 10. Open & Close Guided Scanner
  async function openScanner() {
    const user = await getCurrentUser();
    if (!user || !user.pegawai_id) {
      showToast('Perlu Login', 'Silakan login terlebih dahulu untuk mengakses pendaftaran wajah.', 'warning', 4000);
      return;
    }

    const modal = document.getElementById('face-scanner-modal');
    if (!modal) return;

    modal.classList.add('active');
    state.currentStepIndex = 0;
    state.capturedData = { front: null, right: null, left: null, up: null, down: null, foto_sample: null };
    updateStepUI();

    const statusText = document.getElementById('face-status-text');
    if (statusText) statusText.innerText = 'Memuat modul AI biometrik wajah...';

    const okCamera = await startCamera();
    if (!okCamera) return;

    const okModels = await loadFaceModels();
    if (!okModels) {
      if (statusText) statusText.innerText = 'Gagal memuat model Face-API. Periksa koneksi internet Anda.';
      return;
    }

    if (statusText) statusText.innerText = 'Wajah terdeteksi! Silakan ikuti instruksi sudut.';
    startScanningLoop();
  }

  function closeScanner() {
    const modal = document.getElementById('face-scanner-modal');
    if (modal) modal.classList.remove('active');
    stopCamera();
  }

  function updateStepUI() {
    const step = state.steps[state.currentStepIndex];
    if (!step) return;

    document.getElementById('face-prompt-title').innerText = step.title;
    document.getElementById('face-prompt-desc').innerText = step.desc;

    for (let i = 0; i < state.steps.length; i++) {
      const pill = document.getElementById('step-pill-' + i);
      if (pill) {
        pill.classList.remove('active', 'completed');
        if (i < state.currentStepIndex) pill.classList.add('completed');
        else if (i === state.currentStepIndex) pill.classList.add('active');
      }
    }
  }

  // 11. Scanner Real-Time Loop & Angle Estimation
  let isProcessingFrame = false;
  let angleHoldCounter = 0;

  async function startScanningLoop() {
    const video = document.getElementById('face-scanner-video');
    const radar = document.getElementById('face-scanner-radar');
    const statusText = document.getElementById('face-status-text');

    async function frame() {
      if (!state.activeStream) return;

      if (!isProcessingFrame && state.isModelsLoaded && video.readyState === 4) {
        isProcessingFrame = true;
        try {
          const detection = await faceapi.detectSingleFace(
            video,
            new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
          ).withFaceLandmarks().withFaceDescriptor();

          if (detection) {
            radar.classList.add('scanning');
            const landmarks = detection.landmarks;
            const nose = landmarks.getNose()[3]; // Tip of nose
            const leftEye = landmarks.getLeftEye()[0];
            const rightEye = landmarks.getRightEye()[3];
            const jaw = landmarks.getJawOutline();

            const distLeft = Math.abs(nose.x - leftEye.x);
            const distRight = Math.abs(nose.x - rightEye.x);
            const ratioYaw = distLeft / (distRight || 1); // Left vs right eye ratio

            const eyeMidY = (leftEye.y + rightEye.y) / 2;
            const chinY = jaw[8].y;
            const pitchRatio = (nose.y - eyeMidY) / ((chinY - nose.y) || 1);

            const currentStep = state.steps[state.currentStepIndex].id;
            let angleMatches = false;

            if (currentStep === 'front') {
              angleMatches = (ratioYaw >= 0.75 && ratioYaw <= 1.35) && (pitchRatio >= 0.7 && pitchRatio <= 1.3);
            } else if (currentStep === 'right') {
              angleMatches = ratioYaw > 1.4; // Nose closer to right
            } else if (currentStep === 'left') {
              angleMatches = ratioYaw < 0.7; // Nose closer to left
            } else if (currentStep === 'up') {
              angleMatches = pitchRatio < 0.75; // Chin lower, nose higher
            } else if (currentStep === 'down') {
              angleMatches = pitchRatio > 1.35; // Chin higher, nose lower
            }

            if (angleMatches) {
              angleHoldCounter++;
              statusText.innerText = `Sudut pas! Tahan posisi... (${angleHoldCounter}/4)`;
              if (angleHoldCounter >= 4) {
                // Auto snap this angle!
                snapCurrentAngle(detection.descriptor);
                angleHoldCounter = 0;
              }
            } else {
              angleHoldCounter = 0;
              statusText.innerText = 'Posisikan kepala sesuai instruksi di atas.';
            }
          } else {
            radar.classList.remove('scanning');
            statusText.innerText = 'Wajah tidak terdeteksi. Harap hadapkan wajah ke kamera.';
          }
        } catch (e) {
          console.warn('Frame processing error:', e);
        }
        isProcessingFrame = false;
      }

      requestAnimationFrame(frame);
    }

    requestAnimationFrame(frame);
  }

  // 12. Snap Current Angle Descriptor
  async function snapCurrentAngle(directDescriptor = null) {
    const video = document.getElementById('face-scanner-video');
    const statusText = document.getElementById('face-status-text');
    const radar = document.getElementById('face-scanner-radar');

    let desc = directDescriptor;
    if (!desc) {
      if (statusText) statusText.innerText = 'Mengambil biometrik wajah...';
      try {
        const detection = await faceapi.detectSingleFace(
          video,
          new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
        ).withFaceLandmarks().withFaceDescriptor();
        if (detection) desc = detection.descriptor;
      } catch (e) {}
    }

    if (!desc) {
      showToast('Wajah Kurang Jelas', 'Wajah tidak terdeteksi secara jelas! Harap pastikan pencahayaan cukup.', 'warning', 3500);
      return;
    }

    // Audio & visual feedback
    playTone(1046, 0.18); // High C chime
    if (radar) {
      radar.classList.add('matched');
      setTimeout(() => radar.classList.remove('matched'), 500);
    }

    const stepId = state.steps[state.currentStepIndex].id;
    state.capturedData[stepId] = Array.from(desc);

    // If front angle, also capture high-resolution photo sample
    if (stepId === 'front' || !state.capturedData.foto_sample) {
      const snapCanvas = document.createElement('canvas');
      snapCanvas.width = video.videoWidth || 640;
      snapCanvas.height = video.videoHeight || 480;
      const ctx = snapCanvas.getContext('2d');
      // Mirror to match preview
      ctx.translate(snapCanvas.width, 0);
      ctx.scale(-1, 1);
      ctx.drawImage(video, 0, 0, snapCanvas.width, snapCanvas.height);
      state.capturedData.foto_sample = snapCanvas.toDataURL('image/jpeg', 0.85);
    }

    state.currentStepIndex++;
    if (state.currentStepIndex < state.steps.length) {
      updateStepUI();
    } else {
      // All 5 angles completed! Submit to backend
      submitBiometricRegistration();
    }
  }

  // 13. Verification Loader Helpers
  function showVerifyLoader(text = 'Memverifikasi wajah biometrik...') {
    let el = document.getElementById('face-verify-loader');
    if (!el) {
      el = document.createElement('div');
      el.id = 'face-verify-loader';
      el.className = 'face-verify-overlay';
      el.innerHTML = `
        <div class="face-verify-card">
          <div class="face-verify-spinner"></div>
          <h4 id="face-verify-loader-text">${text}</h4>
          <p>Menganalisis kemiripan biometrik & anti-titip absen...</p>
        </div>
      `;
      document.body.appendChild(el);
    } else {
      const t = document.getElementById('face-verify-loader-text');
      if (t) t.innerText = text;
      el.style.display = 'flex';
    }
  }

  function hideVerifyLoader() {
    const el = document.getElementById('face-verify-loader');
    if (el) el.style.display = 'none';
  }

  // 14. Submit 5-Angle Registration to Backend
  async function submitBiometricRegistration() {
    const statusText = document.getElementById('face-status-text');
    if (statusText) statusText.innerText = '✅ Perekaman 5 sudut selesai! Mengunggah ke server...';

    const user = await getCurrentUser();
    const payload = {
      id: user.pegawai_id,
      image_sample: state.capturedData.foto_sample,
      foto_sample: state.capturedData.foto_sample,
      descriptor_front: JSON.stringify(state.capturedData.front),
      descriptor_right: JSON.stringify(state.capturedData.right || state.capturedData.front),
      descriptor_left: JSON.stringify(state.capturedData.left || state.capturedData.front),
      descriptor_up: JSON.stringify(state.capturedData.up || state.capturedData.front),
      descriptor_down: JSON.stringify(state.capturedData.down || state.capturedData.front)
    };

    try {
      const res = await fetch(API_BASE + 'rest_post/face_register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      closeScanner();

      playTone(1318, 0.3); // High E confirmation
      const successMsg = (data && data.msg) ? data.msg : 'Perekaman wajah biometrik berhasil disimpan! Menunggu verifikasi dan persetujuan dari Admin/HRD sekolah.';
      showToast('Perekaman Wajah Berhasil', successMsg, 'success', 5000);
      fetchFaceStatus(user.pegawai_id);
    } catch (err) {
      showToast('Gagal Mengunggah', 'Gagal mengunggah data biometrik: ' + err.message, 'error', 4500);
      closeScanner();
    }
  }

  // 15. Attendance Verification Bridge (Used by CameraPage before submit)
  async function verifyAttendance(userId, base64Image) {
    if (!userId) {
      return { allowed: false, message: 'ID Karyawan tidak valid. Silakan login kembali.' };
    }
    if (!base64Image) {
      return { allowed: false, message: 'Foto absensi tidak ditemukan. Harap ambil foto terlebih dahulu.' };
    }

    // 0. Gunakan hasil verifikasi sinkronisasi live jika baru diverifikasi (< 60 detik)
    if (state.lastAttendanceVerification && (Date.now() - state.lastAttendanceVerification.timestamp) < 60000) {
      return state.lastAttendanceVerification;
    }

    showVerifyLoader('Memvalidasi biometrik wajah...');

    try {
      // 1. Fetch employee registered descriptors from backend
      let descData = null;
      try {
        const res = await fetch(API_BASE + 'rest_post/face_get_descriptors', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: userId })
        });
        descData = await res.json();
      } catch (e) {
        console.warn('Network error fetching face descriptors:', e);
        // Fallback: If network issue to verify endpoint, allow graceful fallback
        return { allowed: true, similarity: 0, status: 'bypassed', message: 'Offline fallback' };
      }

      if (!descData || !descData.is_registered) {
        return {
          allowed: false,
          message: '⚠️ Wajah Anda BELUM TERDAFTAR dalam sistem biometrik. Silakan klik tombol Face ID untuk melakukan scan wajah 5 sudut terlebih dahulu.'
        };
      }

      if (!descData.is_verified) {
        return {
          allowed: false,
          message: '⏳ Biometrik wajah Anda masih MENUNGGU VERIFIKASI dari Admin SDMIS. Absensi belum dapat dilakukan sebelum disetujui admin.'
        };
      }

      // 2. Load models
      const okModels = await loadFaceModels();
      if (!okModels) {
        // If AI models cannot be loaded, permit graceful bypass so teachers aren't blocked from work
        return { allowed: true, similarity: 0, status: 'bypassed' };
      }

      // 3. Load image
      const img = new Image();
      img.src = base64Image;
      await new Promise(r => { img.onload = () => r(); });

      // 4. Detect face on the captured attendance photo
      const detection = await faceapi.detectSingleFace(
        img,
        new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 })
      ).withFaceLandmarks().withFaceDescriptor();

      if (!detection) {
        return {
          allowed: false,
          message: '⛔ Wajah tidak terdeteksi pada foto absensi! Pastikan wajah Anda terlihat jelas, tidak tertutup, dan berada di tempat terang.'
        };
      }

      const inputDesc = detection.descriptor;
      const registered = descData.result;
      const targets = [
        registered.descriptor_front,
        registered.descriptor_right,
        registered.descriptor_left,
        registered.descriptor_up,
        registered.descriptor_down
      ].filter(Boolean);

      // Compute minimum Euclidean distance across the 5 recorded angles
      let minDistance = 999;
      for (const targetDesc of targets) {
        const d = faceapi.euclideanDistance(inputDesc, targetDesc);
        if (d < minDistance) minDistance = d;
      }

      const threshold = 0.54; // Optimal Euclidean distance threshold for 128-dim vectors
      const similarity = Math.max(0.1, Math.min(0.99, 1 - (minDistance * 0.8)));

      if (minDistance <= threshold) {
        // MATCH!
        playTone(1046, 0.15);
        return {
          allowed: true,
          similarity: similarity.toFixed(2),
          status: 'verified',
          distance: minDistance.toFixed(3),
          message: `Wajah terverifikasi cocok! (Akurasi: ${Math.round(similarity * 100)}%)`
        };
      } else {
        // MISMATCH! (Titip absen / foto orang lain / foto dari hp lain)
        playTone(300, 0.35); // Error low buzz
        return {
          allowed: false,
          similarity: similarity.toFixed(2),
          status: 'mismatch',
          distance: minDistance.toFixed(3),
          message: `⛔ ABSENSI DITOLAK!\nWajah pada foto TIDAK SESUAI dengan biometrik wajah Anda yang terdaftar di sekolah (Kecocokan rendah: ${Math.round(similarity * 100)}%). Dilarang menitipkan absensi!`
        };
      }
    } finally {
      hideVerifyLoader();
    }
  }

  // 15b. Real-Time Live Attendance Face Watcher & Auto-Capture (Opsi 1)
  const liveWatcher = {
    isActive: false,
    intervalId: null,
    consecutiveMatches: 0,
    requiredMatches: 3, // ~1.2s - 1.5s match stabil
    isCapturing: false,
    cachedUserDescriptors: null,
    targetPegawaiId: null
  };

  function stopLiveAttendanceWatcher() {
    if (liveWatcher.intervalId) {
      clearInterval(liveWatcher.intervalId);
      liveWatcher.intervalId = null;
    }
    liveWatcher.isActive = false;
    liveWatcher.isCapturing = false;
    liveWatcher.consecutiveMatches = 0;
    const hud = document.getElementById('face-live-hud');
    if (hud) hud.remove();
    const fab = document.getElementById('face-id-fab');
    if (fab) fab.style.display = 'flex';
  }

  async function checkAndAttachLiveAttendanceWatcher() {
    const href = window.location.href;
    const isCameraRoute = href.includes('/camera/');

    const captureParent = document.querySelector('.cordova-camera-capture');
    const video = captureParent ? captureParent.querySelector('video') : document.querySelector('video');

    if (!isCameraRoute || !captureParent || !video) {
      if (liveWatcher.isActive && !captureParent) {
        stopLiveAttendanceWatcher();
      }
      return;
    }

    // Sembunyikan floating button Face ID saat kamera absensi sedang aktif
    const fab = document.getElementById('face-id-fab');
    if (fab) fab.style.display = 'none';

    if (liveWatcher.isActive) return;

    liveWatcher.isActive = true;
    liveWatcher.consecutiveMatches = 0;
    liveWatcher.isCapturing = false;

    // Sembunyikan tombol capture default agar otomatis terkontrol oleh AI
    const captureBtn = captureParent.querySelector('button');
    if (captureBtn) {
      captureBtn.style.opacity = '0';
      captureBtn.style.pointerEvents = 'none';
      captureBtn.style.position = 'absolute';
    }

    video.style.borderRadius = '16px';

    // Inject HUD Overlay ke dalam container video
    let hud = document.getElementById('face-live-hud');
    if (!hud) {
      hud = document.createElement('div');
      hud.id = 'face-live-hud';
      hud.className = 'face-live-hud-overlay';
      hud.innerHTML = `
        <div id="face-live-pill" class="face-live-status-pill searching">
          <span class="pill-dot"></span>
          <span id="face-live-pill-text">🔍 Memuat Face Recognition...</span>
        </div>
        <div id="face-live-radar" class="face-live-radar-box searching">
          <div class="face-live-laser-line"></div>
          <div id="face-live-countdown" class="face-live-countdown-text" style="display:none;"></div>
        </div>
        <div class="face-live-status-pill" style="margin-bottom:15px; font-size:12px; opacity:0.85;">
          <span>Posisikan wajah tepat di lingkaran untuk Auto-Capture</span>
        </div>
      `;
      captureParent.appendChild(hud);
    }

    // Load AI models
    await loadFaceModels();

    // Dapatkan data pegawai yang login
    const user = await getCurrentUser();
    if (!user || !user.pegawai_id) {
      liveWatcher.isActive = false;
      return;
    }

    // Ambil deskriptor guru dari server
    let descData = null;
    try {
      const res = await fetch(API_BASE + 'rest_post/face_get_descriptors', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: user.pegawai_id })
      });
      descData = await res.json();
    } catch (e) {
      console.warn('Gagal memuat deskriptor guru:', e);
    }

    // Skenario 1: Belum Merekam Face ID
    if (!descData || !descData.is_registered) {
      hud.innerHTML = `
        <div class="face-live-gate-card danger">
          <div class="gate-icon">⚠️</div>
          <h4>Belum Rekam Face ID</h4>
          <p>Anda belum merekam data biometrik wajah. Silakan rekam wajah 5 sudut terlebih dahulu untuk dapat melakukan absensi.</p>
          <button class="btn-gate-action" onclick="window.FaceBiometric.openScanner()">
            📸 Rekam Wajah Sekarang
          </button>
          <button class="btn-gate-close" onclick="window.history.back()">Batal / Kembali</button>
        </div>
      `;
      return;
    }

    // Skenario 2: Menunggu Verifikasi Admin
    if (!descData.is_verified) {
      hud.innerHTML = `
        <div class="face-live-gate-card warning">
          <div class="gate-icon">⏳</div>
          <h4>Menunggu Verifikasi Admin</h4>
          <p>Data biometrik wajah Anda telah tersimpan dan sedang menunggu persetujuan dari Admin / HRD SDMIS.</p>
          <button class="btn-gate-action" style="background:#f59e0b;" onclick="window.history.back()">
            Kembali ke Beranda
          </button>
        </div>
      `;
      return;
    }

    // Skenario 3: Siap Verifikasi Live Wajah
    const registered = descData.result;
    liveWatcher.cachedUserDescriptors = [
      registered.descriptor_front,
      registered.descriptor_right,
      registered.descriptor_left,
      registered.descriptor_up,
      registered.descriptor_down
    ].filter(Boolean);

    const pill = document.getElementById('face-live-pill');
    const pillText = document.getElementById('face-live-pill-text');
    const radar = document.getElementById('face-live-radar');
    const countdown = document.getElementById('face-live-countdown');

    function updateHUD(type, msg) {
      if (pill) pill.className = `face-live-status-pill ${type}`;
      if (pillText) pillText.innerText = msg;
      if (radar) radar.className = `face-live-radar-box ${type}`;
    }

    updateHUD('searching', '🔍 Posisikan wajah di lingkaran...');

    // Live Detection & Auto-Capture Loop (Setiap 380ms)
    liveWatcher.intervalId = setInterval(async () => {
      if (!liveWatcher.isActive || liveWatcher.isCapturing) return;
      if (!video || video.paused || video.ended || video.readyState < 2) return;

      try {
        const detection = await faceapi.detectSingleFace(
          video,
          new faceapi.TinyFaceDetectorOptions({ inputSize: 256, scoreThreshold: 0.5 })
        ).withFaceLandmarks().withFaceDescriptor();

        if (!detection) {
          liveWatcher.consecutiveMatches = 0;
          if (countdown) countdown.style.display = 'none';
          updateHUD('searching', '🔍 Arahkan wajah ke lingkaran...');
          return;
        }

        const inputDesc = detection.descriptor;
        let minDistance = 999;
        for (const targetDesc of liveWatcher.cachedUserDescriptors) {
          const d = faceapi.euclideanDistance(inputDesc, targetDesc);
          if (d < minDistance) minDistance = d;
        }

        const threshold = 0.54;
        const similarity = Math.max(0.1, Math.min(0.99, 1 - (minDistance * 0.8)));
        const simPercent = Math.round(similarity * 100);

        if (minDistance > threshold) {
          // MISMATCH (Bukan Guru Bersangkutan / Titip Absen)
          liveWatcher.consecutiveMatches = 0;
          if (countdown) countdown.style.display = 'none';
          updateHUD('mismatch', `⛔ Wajah Tidak Cocok (${simPercent}%)`);
          playTone(300, 0.1);
        } else {
          // MATCH! (Guru Terverifikasi)
          liveWatcher.consecutiveMatches++;
          updateHUD('matched', `✅ Wajah Cocok (${simPercent}%) - Tahan Posisi...`);

          if (countdown) {
            countdown.style.display = 'block';
            const count = Math.max(1, 4 - liveWatcher.consecutiveMatches);
            countdown.innerText = count <= 1 ? '📸' : count.toString();
          }

          playTone(880, 0.08); // Feedback sinkronisasi

          if (liveWatcher.consecutiveMatches >= 3 && !liveWatcher.isCapturing) {
            // MATCH STABIL 3 FRAME (~1.2s)! AUTO-CAPTURE TERPICU
            liveWatcher.isCapturing = true;
            playTone(1318, 0.25); // Nada shutter sukses

            state.lastAttendanceVerification = {
              allowed: true,
              similarity: similarity.toFixed(2),
              status: 'verified',
              distance: minDistance.toFixed(3),
              message: `Wajah terverifikasi cocok! (Akurasi: ${simPercent}%)`,
              timestamp: Date.now()
            };

            // Trigger capture
            if (captureBtn) {
              captureBtn.click();
            } else {
              const anyBtn = captureParent.querySelector('button');
              if (anyBtn) anyBtn.click();
            }

            stopLiveAttendanceWatcher();
          }
        }
      } catch (err) {
        console.warn('Face detection live loop error:', err);
      }
    }, 380);
  }

  // 16. Auto-Initialize
  async function init() {
    injectElements();

    // Check user & fetch status
    const user = await getCurrentUser();
    if (user && user.pegawai_id) {
      fetchFaceStatus(user.pegawai_id);
    }

    // Periodically re-check login, refresh UI, and monitor camera presence
    setInterval(async () => {
      checkAndAttachLiveAttendanceWatcher();

      if (!state.currentUser) {
        const u = await getCurrentUser();
        if (u && u.pegawai_id) fetchFaceStatus(u.pegawai_id);
      } else if (state.biometricStatus) {
        updateUIStatus(state.biometricStatus);
      }
    }, 800);

    window.addEventListener('popstate', () => {
      if (state.biometricStatus) setTimeout(() => updateUIStatus(state.biometricStatus), 400);
      setTimeout(checkAndAttachLiveAttendanceWatcher, 300);
    });
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Export public API
  window.FaceBiometric = {
    openScanner,
    closeScanner,
    snapCurrentAngle,
    verifyAttendance,
    fetchFaceStatus,
    getCurrentUser,
    showToast,
    checkAndAttachLiveAttendanceWatcher,
    stopLiveAttendanceWatcher
  };

})(window);

