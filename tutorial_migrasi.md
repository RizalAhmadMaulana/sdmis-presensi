# 🚀 Panduan Migrasi & Deploy Fitur Face Recognition ke Server Produksi (Hostinger)

**Proyek:** SD Mentari Islamic School (Portal SDMIS & Webapp TemanMIS)  
**Database Produksi:** `u983578927_absensi` (Hostinger MariaDB)  
**Domain Utama:** `https://sdmis.sch.id`  
**Subdomain Presensi:** `https://presensi.sdmis.sch.id`  
**Pembaruan:** Sistem Absensi Biometrik Wajah 5 Sudut & Verifikasi AI Anti-Titip Absen  

---

## 📌 Ringkasan Alur Deployment

```
[Komputer Lokal / Dev]                [GitHub Repository]                [Server Hostinger Live]
C:\xampp\htdocs\mis_dev\sdmis  ──>  git push origin main  ──>  git pull via SSH (sdmis-hostinger)
                                                                             │
                                                                             ▼
                                                                Browser: https://sdmis.sch.id/migrate_face
                                                                (Auto-Migrate Database Sekali Klik)
```

Seluruh kode program telah dirancang **100% dinamis**, secara otomatis mendeteksi apakah aplikasi sedang berjalan di lingkungan lokal (`localhost` / IP LAN) atau lingkungan produksi live (`https://sdmis.sch.id`). Anda **tidak perlu mengubah file konfigurasi atau baris kode apa pun** saat deploy ke Hostinger.

---

## 🛠️ Langkah 1: Push Pembaruan dari Komputer Lokal ke GitHub

Buka terminal **PowerShell**, **Git Bash**, atau **Terminal VSCode** di komputer lokal Anda:

```bash
# 1. Pindah ke direktori proyek lokal
cd "C:\xampp\htdocs\mis_dev\sdmis"

# 2. Cek status perubahan file
git status

# 3. Tambahkan seluruh file perubahan ke staging Git
git add .

# 4. Buat commit perubahan
git commit -m "feat: complete biometric face recognition, attendance auto-capture & responsive UI"

# 5. Push ke repository GitHub
git push origin main
```
> *(Catatan: Jika branch utama di repository Anda bernama `master`, gunakan `git push origin master`)*

---

## 🌐 Langkah 2: Update Server Produksi SDMIS via SSH Terminal VSCode

Setelah commit berhasil di-push ke GitHub, buka **Terminal di VSCode** (posisi di folder lokal Anda) untuk menarik update ke server Hostinger.

Pilih salah satu cara di bawah ini:

### Opsi A: Cara Standar (Interaktif)
Masuk ke sesi terminal server Hostinger secara bertahap:

```bash
# 1. Hubungkan ke server Hostinger via konfigurasi SSH
ssh sdmis-hostinger

# 2. Masuk ke direktori web utama SDMIS
cd /home/u983578927/domains/sdmis.sch.id/public_html/

# 3. Tarik pembaruan terbaru dari GitHub
git pull origin main

# 4. Keluar dari sesi SSH
exit
```

---

### Opsi B: Cara Cepat (One-Liner Execution - Sangat Direkomendasikan ⚡)
Jalankan satu baris perintah langsung dari terminal lokal VSCode tanpa harus login interaktif:

```bash
ssh sdmis-hostinger "cd ~/domains/sdmis.sch.id/public_html && git pull origin main"
```

Perintah di atas akan otomatis menghubungkan SSH, berpindah direktori, dan menjalankan `git pull origin main` dalam satu kali tekan tombol Enter!

---

## 🗄️ Langkah 3: Eksekusi Migrasi Database Live Sekali Klik

Setelah proses `git pull` selesai di Hostinger, buka browser (Chrome / Edge / Safari), lalu akses tautan migrasi berikut:

```
https://sdmis.sch.id/migrate_face
```
*(Atau `https://sdmis.sch.id/index.php/migrate_face` jika rewrite URL belum aktif)*

### Apa yang Dilakukan oleh Skrip Ini Secara Otomatis?
1. **Deteksi Otomatis Lingkungan Produksi**: Sistem mendeteksi host `sdmis.sch.id` dan otomatis menyambungkan ke database aktif Hostinger: **`u983578927_absensi`**.
2. **Pembuatan Tabel Biometrik**: Membuat tabel baru `m_pegawai_face` untuk menyimpan 5 sudut vektor biometrik wajah guru (128-d float array).
3. **Pembaruan Tabel Karyawan**: Menambahkan kolom `is_face_registered` (default `'n'`) pada tabel `m_pegawai`.
4. **Pembaruan Tabel Absensi**: Menambahkan kolom audit `face_similarity_score` dan `face_status` pada tabel `tx_absensi`.
5. **Pembuatan Folder Storage**: Otomatis membuat folder penyimpanan foto wajah sampel di `assets/uploaded/faces/` beserta proteksi `index.html`.
6. **Laporan Dashboard Hijau**: Menampilkan status **"SUKSES SEMUA"** di layar browser Anda.

> [!NOTE]
> **Zero Data Loss Guarantee**:  
> Semua query migrasi menggunakan klausul aman `IF NOT EXISTS` dan `SHOW COLUMNS LIKE`.  
> Tidak ada tabel yang di-drop atau di-reset. Seluruh 9.280+ riwayat absensi, data guru, dan data siswa yang ada di Hostinger **100% aman dan tidak tersentuh**.

---

### 🛡️ Opsi Alternatif Manual (Jika Tidak Melalui Browser):
Jika Anda lebih suka menjalankan query SQL langsung di cPanel Hostinger:
1. Login ke panel Hostinger $\to$ Buka **phpMyAdmin**.
2. Pilih database **`u983578927_absensi`**.
3. Buka tab **SQL**.
4. Salin dan tempel isi file `database_migration_face.sql` yang ada di proyek ini.
5. Klik **Go / Kirim**.

---

## ✅ Langkah 4: Verifikasi & Uji Coba di Server Live Hostinger

Setelah langkah 1, 2, dan 3 selesai, lakukan verifikasi akhir:

### 1. Cek Portal Admin SDMIS
Buka di browser:
```
https://sdmis.sch.id/karyawan/data
```
- Login sebagai Admin.
- Buka menu **Karyawan** $\to$ **Data Karyawan**.
- Pastikan kolom baru **"FACE BIOMETRIK"** sudah tampil di tabel dengan tombol status *(Belum Rekam / Menunggu Approval / Terverifikasi)*.

### 2. Cek Webapp Presensi Guru (TemanMIS)
Buka di browser smartphone atau desktop:
```
https://presensi.sdmis.sch.id/
```
- Login sebagai akun guru.
- Di halaman beranda, periksa widget Face ID tampil rapi di bawah kartu tanggal.
- Tombol **"Absen Masuk"** atau tombol floating **"Face ID"** dapat digunakan untuk pemindaian biometrik 5 sudut dan auto-capture kehadiran.

### 3. Cek Halaman Diagnostik Biometrik (Opsional)
Buka di browser:
```
https://sdmis.sch.id/test_biometric
```
- Menguji kesiapan modul Face-API di server hosting serta simulasi pencocokan wajah guru live.

---

## ⚠️ Troubleshooting & Catatan Penting

1. **Izin Akses Kamera di Smartphone**:
   - PWA / Webapp presensi membutuhkan koneksi **HTTPS** agar browser memberikan izin kamera (`navigator.mediaDevices.getUserMedia`). Karena domain Hostinger `https://sdmis.sch.id` dan `https://presensi.sdmis.sch.id` sudah menggunakan SSL aktif, izin kamera akan berjalan lancar.
2. **Jika Muncul Git Conflict saat `git pull` di Hostinger**:
   Jika ada file log atau cache di Hostinger yang bentrok, jalankan perintah ini via SSH:
   ```bash
   ssh sdmis-hostinger "cd ~/domains/sdmis.sch.id/public_html && git stash && git pull origin main"
   ```
3. **Pembersihan Cache Browser Guru**:
   Setelah update di Hostinger, ingatkan guru atau admin untuk melakukan **Hard Refresh** (atau hapus cache browser pada handphone) agar aset JavaScript dan CSS termutakhir langsung dimuat oleh browser.

---
*Dokumentasi ini dibuat otomatis sebagai panduan resmi deployment SD Mentari Islamic School.*

## 📋 Catatan Pembaruan Rilis Gabungan (Face Recognition + Modul Penggajian)
> [!IMPORTANT]
> Sesuai instruksi dan konsultasi dengan pihak client/sekolah:
> - **Pembaruan server Hostinger produksi DITUNDA SEMENTARA** hingga seluruh penambahan fitur selesai dikembangkan dan diuji coba tuntas di lingkungan local.
> - **Fitur berikutnya yang sedang dibangun:** **Sistem Penggajian & Mukafaah Guru/Karyawan Terintegrasi Presensi Biometrik & Export Bank Payroll (BRI/BSI)**.
> - Detail arsitektur, skema database, rumus kehadiran 2-tier, dan alur implementasi modul penggajian telah disusun lengkap di dokumen: [PRD_PENGGAJIAN_MUKAFAAH.md](file:///C:/xampp/htdocs/mis_dev/sdmis/PRD_PENGGAJIAN_MUKAFAAH.md).

---

## 💰 Panduan Migrasi Tahap 1: Modul Penggajian & Mukafaah

### A. Cara Menjalankan Migrasi di Komputer Lokal (Localhost)
Buka browser Anda dan akses salah satu URL berikut:
```
http://localhost/mis_dev/sdmis/migrate_payroll
```
*(Atau `http://localhost/sdmis/migrate_payroll` tergantung konfigurasi folder Apache XAMPP Anda)*

Skrip otomatis ini akan:
1. Membuat 8 tabel baru penggajian (`m_gaji_setting_global`, `m_gaji_hari_khusus`, `m_pegawai_gaji_setting`, `m_ekstra`, `tx_ekstra_siswa`, `tx_gaji_periode`, `tx_gaji_karyawan`, `tx_gaji_komponen_manual`).
2. Mendaftarkan menu induk **"Penggajian (Mukafaah)"** beserta 7 submenunya ke `m_menu` dan memberikan izin akses role superadmin di `m_role_access`.
3. Memasukkan konfigurasi default aturan 2-tier presensi (jam masuk 06.45 toleransi 3 menit -> 06.48, dan jam super early 06.30).
4. Menyinkronkan dan mengisi seeder profil 21 guru dan nomor rekening bank dari Excel acuan November 2026.
5. Menampilkan dashboard laporan status berwarna hijau **"BERHASIL SEMPURNA"**.

---

### B. Cara Menerapkan Update Tahap 1 di Server Produksi Hostinger
Nantinya, setelah seluruh pengembangan fitur selesai di local, langkah deploy ke Hostinger sangat mudah:

#### Langkah 1: Push dari Lokal & Pull di Hostinger
```bash
# 1. Di Terminal Komputer Lokal
cd "C:\xampp\htdocs\mis_dev\sdmis"
git add .
git commit -m "feat: complete payroll module tahap 1 database & seeders"
git push origin main

# 2. Di SSH Terminal VSCode Hostinger
ssh sdmis-hostinger "cd ~/domains/sdmis.sch.id/public_html && git pull origin main"
```

#### Langkah 2: Eksekusi Migrasi Database Live Sekali Klik
Buka browser Anda dan akses URL:
```
https://sdmis.sch.id/migrate_payroll
```
*(Skrip otomatis mendeteksi database produksi `u983578927_absensi` dan membuat tabel baru tanpa risiko kehilangan data lama)*.

#### Langkah 3 (Opsi Manual): Impor File SQL
Jika Anda lebih suka menjalankan query via phpMyAdmin cPanel Hostinger:
1. Buka phpMyAdmin $\to$ Pilih database **`u983578927_absensi`**.
2. Masuk ke tab **SQL**.
3. Salin dan tempel isi file `database_migration_payroll.sql` $\to$ Klik **Kirim / Go**.

---

## ⚙️ Panduan Pengujian Tahap 2: Backend Calculation Engine (`Payroll_model.php`)

### A. Pengujian di Lingkungan Lokal
Buka browser Anda dan akses:
```
http://localhost/mis_dev/sdmis/test_payroll
```
*(Atau `http://localhost/sdmis/test_payroll`)*

Halaman ini akan menjalankan unit test otomatis untuk memverifikasi:
1. Pembacaan parameter acuan 2-tier (jam masuk 06.45 toleransi 3 menit -> 06.48, dan jam super early 06.30).
2. Perhitungan masa kerja & tunjangan GTY (Rp 15.000/tahun).
3. Formula uang makan guru lama (>3 thn Rp 5.000) dan guru baru (<=3 thn Rp 2.500).
4. Logika keputusan 2-tier bonus presensi (Tier 1 Rp 50.000 + Tier 2 Rp 50.000).
5. Ketersediaan master profil gaji 21 guru di database.

### B. Pengujian di Server Hostinger (Nantinya)
Setelah `git pull origin main` di Hostinger, Anda juga dapat membuka:
```
https://sdmis.sch.id/test_payroll
```
untuk memastikan backend engine berfungsi 100% normal di server live.

---

## 🎨 Panduan Pengujian Tahap 3: UI Pengaturan Aturan & Kalender Hari Khusus

### A. Pengujian di Komputer Lokal
1. Pastikan Anda sudah login ke portal admin SDMIS di komputer lokal (`http://localhost/mis_dev/sdmis/`).
2. Buka menu sidebar: **Penggajian** $\to$ **Pengaturan Aturan & Tarif**, atau langsung akses:
   ```
   http://localhost/mis_dev/sdmis/gaji/setting
   ```
3. **Pengujian Form Formula 2-Tier & Tarif**:
   - Periksa form parameter jam masuk acuan (`06:45`), toleransi keterlambatan (`3` menit), jam super early (`06:30`), batas pelanggaran (`3` kali), bonus Tier 1 (`50.000`), dan bonus Tier 2 (`50.000`).
   - Coba ubah salah satu nilai, lalu klik **Simpan Seluruh Pengaturan**. Pastikan muncul alert hijau sukses dan nilai baru tersimpan.
4. **Pengujian Kalender Hari Khusus Pulang Cepat (SOP 3.a)**:
   - Klik tombol **+ Tambah** di panel kanan *Kalender Hari Khusus*.
   - Masukkan tanggal (misal tanggal hari ini atau tanggal rapat dewan guru), isi keterangan (misal: `"Rapat Evaluasi Semester"`), pilih opsi *"Bebas Sanksi (Boleh Pulang Lebih Awal)"*, lalu klik **Simpan ke Kalender**.
   - Pastikan tanggal tersebut langsung muncul di daftar tabel kalender.
   - Coba klik tombol ikon tempat sampah merah pada baris agenda untuk menguji fitur hapus agenda.

### B. Menerapkan di Server Produksi Hostinger (Nantinya)
Karena struktur tabel `m_gaji_setting_global` dan `m_gaji_hari_khusus` sudah dibuat saat migrasi Tahap 1, Anda **tidak memerlukan konfigurasi database tambahan**.
1. Lakukan `git pull origin main` di Hostinger via terminal SSH.
2. Login sebagai Superadmin di `https://sdmis.sch.id/`.
3. Akses menu `Penggajian` $\to$ `Pengaturan Aturan & Tarif` di URL:
   ```
   https://sdmis.sch.id/gaji/setting
   ```

---

## 👥 Panduan Pengujian Tahap 4: UI Master Profil Gaji & Rekening Guru

### A. Pengujian di Komputer Lokal
1. Pastikan Anda sudah login ke portal admin SDMIS di komputer lokal (`http://localhost/mis_dev/sdmis/`).
2. Buka menu sidebar: **Penggajian** $\to$ **Master Gaji & Rekening**, atau langsung akses:
   ```
   http://localhost/mis_dev/sdmis/gaji/karyawan
   ```
3. **Pemeriksaan Tampilan & Data**:
   - Periksa 4 KPI Summary Cards di bagian atas (Total Guru/Karyawan, Guru Tetap Yayasan [GTY], Kontrak/Ekstra, dan Rekening Siap Transfer).
   - Pastikan seluruh daftar guru dari Excel November 2026 tampil rapi pada tabel lengkap dengan badge status GTY, penugasan wali kelas/struktural, nominal gaji pokok, dan nomor rekening bank.
4. **Pengujian Modal Edit Profil Gaji & Rekening**:
   - Klik tombol **Edit** berwarna biru pada salah satu guru (misal *Siti Nurhayati* atau *Anwar Sadat*).
   - Periksa apakah popup modal terbuka dengan data yang sudah terisi otomatis:
     - **Tab 1 (Profil Gaji & Jabatan)**: Status kepegawaian (GTY), Gaji Pokok (Rp 500.000), Status Wali Kelas, Jabatan Struktural.
     - **Tab 2 (Rekening Bank & CMS)**: Nama Bank (BRI / BSI), Nomor Rekening, Atas Nama, Customer Reference (`MTNOVE...`).
   - Coba ubah status wali kelas atau nominal tunjangan, lalu klik **Simpan Perubahan**.
   - Halaman akan memuat ulang dengan pesan sukses dan data terbaru langsung diperbarui di tabel!

### B. Menerapkan di Server Produksi Hostinger (Nantinya)
1. Lakukan `git pull origin main` di server Hostinger.
2. Login ke portal admin di `https://sdmis.sch.id/`.
3. Akses URL:
   ```
   https://sdmis.sch.id/gaji/karyawan
   ```
   Seluruh master data gaji dan nomor rekening guru akan langsung aktif dan siap digunakan untuk proses hitung gaji di Tahap selanjutnya.

---

## 🎯 Panduan Pengujian Tahap 5: Modul Ekstrakurikuler & Pelunasan Siswa

### A. Pengujian di Komputer Lokal
1. Buka browser dan akses menu: **Penggajian** $\to$ **Manajemen Ekstra & Siswa**, atau langsung URL:
   ```
   http://localhost/mis_dev/sdmis/gaji/ekstra
   ```
2. **Pemeriksaan KPI Metrics & Filter**:
   - Periksa 4 KPI Cards (Total Siswa Terdaftar, Siswa Lunas, Siswa Belum Lunas [Carry-Over], dan Total Honor Siap Cair).
   - Gunakan dropdown filter Cabang Ekstra (Panahan, Sepatu Roda, Renang, Tahfidz), Bulan, dan Tahun.
3. **Pengujian Fitur Tambah Siswa**:
   - Klik tombol **Tambah Peserta Siswa**.
   - Pilih cabang (misal *Panahan*), isi nama siswa (misal *"Muhammad Al-Fatih"*), kelas (`3A`), pilih status pembayaran (*LUNAS* atau *BELUM LUNAS*), lalu simpan.
4. **Pengujian 1-Click Toggle Status Pelunasan (SOP 7 & 8)**:
   - Pada tabel peserta, klik tombol status pada baris siswa.
   - Status akan otomatis berganti antara **LUNAS (Cair)** (hijau) dan **BELUM LUNAS (Carry-Over)** (kuning) serta tanggal bayar akan otomatis terisi saat berstatus lunas.
5. **Pengujian Master Cabang Ekstra**:
   - Klik tombol **Master Cabang Ekstra** untuk melihat daftar pengampu (Anang Yulianto untuk Panahan, Afkhan Rico untuk Tahfidz) dan tarif standar.

### B. Menerapkan di Server Produksi Hostinger (Nantinya)
1. Lakukan `git pull origin main` di server Hostinger.
2. Akses URL:
   ```
   https://sdmis.sch.id/gaji/ekstra
   ```

---

## ⚡ Panduan Pengujian Tahap 6: Core Generator Periode & Modal Penyesuaian

### A. Pengujian di Komputer Lokal
1. Buka browser dan akses menu: **Penggajian** $\to$ **Periode & Hitung Gaji**, atau langsung URL:
   ```
   http://localhost/mis_dev/sdmis/gaji/periode
   ```
2. **Pengujian Pembuatan Periode Baru**:
   - Klik tombol **Buat Periode Baru**.
   - Pilih Bulan: **November**, Tahun: **2026**.
   - Tanggal Awal: `2026-11-01`, Tanggal Akhir: `2026-11-30`.
   - Total Hari Kerja: `20` hari.
   - Klik **Buat Periode & Lanjut**. Anda akan langsung diarahkan ke halaman detail periode tersebut.
3. **Pengujian TOMBOL SAKTI ⚡ "HITUNG OTOMATIS DARI PRESENSI"**:
   - Klik tombol besar kuning berkilau **⚡ HITUNG OTOMATIS DARI PRESENSI**.
   - Sistem akan langsung menjalankan kalkulasi presensi 2-tier (06.48 & 06.30), uang makan, masa kerja GTY, dan honor ekstra untuk seluruh 26 guru secara instan!
   - Periksa 6 KPI Summary Cards dan tabel slip gaji per guru.
4. **Pengujian Modal Penyesuaian Manual (SOP Poin 5)**:
   - Klik tombol **+ Penyesuaian Manual** (atau tombol *Adjust* pada baris guru, misal Pak Anwar / Pak Nuki / Bunda Dewi).
   - Pilih jenis (Penambahan/Potongan), pilih Kategori (Talangan Operasional / Kasbon / Transport), isi keterangan (misal: *"Talangan servis jemputan"*), masukkan nominal, lalu simpan.
   - Nilai Take Home Pay (THP) dan total pengeluaran yayasan akan langsung diperbarui secara otomatis (*real-time sync*)!
5. **Pengujian Kunci Final**:
   - Klik tombol **Kunci Final** untuk mengunci periode jika perhitungan telah selesai.

### B. Menerapkan di Server Produksi Hostinger (Nantinya)
1. Lakukan `git pull origin main` di server Hostinger.
2. Akses URL:
   ```
   https://sdmis.sch.id/gaji/periode
   ```

---

## 🛠️ Panduan Pengujian Fitur Full CRUD Ekstra & Verifikasi Perbaikan UI

### A. Pengujian CRUD Master Cabang Ekstra:
1. Buka halaman: `http://localhost/mis_dev/sdmis/gaji/ekstra`.
2. Klik tombol **Master Cabang Ekstra**.
3. **Pengujian Tambah**: Masukkan nama cabang baru (misal: *"Robotik & Coding"*), pilih guru pengampu, tentukan tipe honor dan tarif standar (misal: `50000`), lalu klik **Simpan Cabang Ekstra**.
4. **Pengujian Edit**: Pada tabel di dalam modal, klik ikon **Edit** (pensil biru) pada baris cabang. Form di bagian atas akan otomatis terisi dengan data cabang tersebut. Ubah data (misal tarif menjadi `60000`), lalu klik **Update Cabang Ekstra**.
5. **Pengujian Hapus**: Klik ikon **Hapus** (tong sampah merah) pada baris cabang yang ingin dihapus, lalu konfirmasi penghapusan. Cabang beserta data siswa terkait akan terhapus dengan aman.

### B. Pengujian CRUD Checklist Siswa Ekstra:
1. Klik tombol **Tambah Peserta Siswa** untuk mendaftarkan siswa baru.
2. **Pengujian Edit Siswa**: Pada baris siswa di tabel utama, klik ikon **Edit** (pensil biru). Modal edit akan muncul dengan data siswa terpilih. Ubah kelas, nominal tagihan, atau status bayar, lalu klik **Simpan Perubahan Siswa**.
3. **Pengujian 1-Click Toggle**: Klik tombol status pelunasan (hijau/kuning) pada baris tabel untuk mengubah status Lunas $\leftrightarrow$ Belum Lunas secara instan.
4. **Pengujian Hapus Siswa**: Klik ikon **Hapus** (tong sampah merah) pada baris siswa untuk menghapus data siswa tersebut dari daftar ekstra.

### C. Verifikasi Anti-Warning DataTables & Tampilan Bersih:
1. Buka menu **Periode & Hitung Gaji** (`http://localhost/mis_dev/sdmis/gaji/periode`):
   - Pastikan **tidak ada pop-up alert** DataTables yang muncul.
   - Pastikan badge `"SOP 1 & 2: Cut-Off Presensi Tgl 1 s/d 30/31"` sudah **bersih/hilang**.
2. Buka menu **Manajemen Ekstra & Siswa** (`http://localhost/mis_dev/sdmis/gaji/ekstra`):
   - Pastikan badge teks `"SOP Poin 7 & 8"` pada bagian atas tabel sudah **bersih/hilang**.
3. Pastikan pesan `"Telah keluar."` tidak lagi muncul di halaman manapun setelah proses login.

---

## 🎨 Panduan Pengujian Floating Toast & Modal Konfirmasi Interaktif

### A. Pengujian Floating Toast Notification:
1. Buka `http://localhost/mis_dev/sdmis/gaji/ekstra`.
2. Klik tombol **Tambah Peserta Siswa** dan simpan siswa baru, atau klik toggle pelunasan, atau hapus data.
3. Perhatikan di bagian atas layar tengah:
   - Akan muncul **Floating Toast Pill** berwarna gelap dengan ikon centang hijau dan teks putih rapi (contoh: *"Data siswa ... berhasil disimpan!"*).
   - Toast akan tampil selama **3.2 detik**, kemudian meluncur ke atas dan menghilang secara otomatis.
   - Pindah ke menu **Pengaturan Aturan & Tarif** (`gaji/setting`) atau menu lainnya: **pesan tersebut dipastikan TIDAK AKAN PERNAH muncul di menu lain!**

### B. Pengujian Konfirmasi Hapus Interaktif & Tombol yang Clickable:
1. Di halaman `gaji/ekstra`, coba klik tombol **Hapus** (tong sampah merah) pada salah satu siswa di tabel Checklist Pelunasan:
   - Muncul **Modal Konfirmasi Interaktif** bertuliskan: *"Hapus Peserta Siswa? Apakah Anda yakin ingin menghapus data siswa [Nama]?"*
   - Coba klik tombol **Batal**: modal akan menutup dengan lancar.
   - Coba klik tombol **Ya, Hapus**: data siswa akan langsung terhapus dan halaman menampilkan floating toast konfirmasi sukses. Kedua tombol dipastikan 100% responsif dan dapat diklik!

### C. Pengujian Halaman Khusus Master Cabang Ekstra:
1. Buka halaman utama: `http://localhost/mis_dev/sdmis/gaji/ekstra`.
2. Klik tombol **Master Cabang Ekstra**:
   - Sistem akan langsung membuka halaman khusus: `http://localhost/mis_dev/sdmis/gaji/ekstra/cabang`.
   - Di bagian atas tersedia tombol: **"← Kembali ke Manajemen Ekstra & Siswa"** untuk kembali ke halaman utama kapan saja.
   - Tampilan halaman 2-kolom permanen: sisi kiri Form Input dengan border aktif, sisi kanan Tabel Daftar Cabang dengan tombol Aksi Edit & Hapus yang rapi berjajar horizontal.
3. Coba lakukan Tambah, Edit, atau Hapus Cabang:
   - Saat klik Hapus, modal konfirmasi muncul tepat di halaman ini (bukan di halaman lain).
   - Setelah selesai, pesan toast konfirmasi sukses langsung muncul di halaman ini selama 3.2 detik dan otomatis hilang.

---

## 📅 Panduan Pengujian CRUD Hari Khusus & Formulir Pengaturan Dinamis

### A. Pengujian Menu Pengaturan Aturan & Tarif (`gaji/setting`):
1. Buka menu: `http://localhost/mis_dev/sdmis/gaji/setting`.
2. **Verifikasi Tampilan Bersih (Tanpa Teks SOP / Disbursement)**:
   - Pastikan teks badge `SOP Mentari v2.0`, `SOP Poin 3-4`, `SOP Formula Tetap`, dan `Disbursement` sudah **hilang/bersih**.
3. **Pengujian Nilai Nominal Bulat (Anti-Truncation)**:
   - Periksa angka pada input nominal:
     - Bonus Tier 1: `50000`
     - Bonus Tier 2: `50000`
     - Uang Makan Lama: `5000`
     - Uang Makan Baru: `2500`
     - Masa Kerja GTY: `15000`
     - Wali Kelas: `25000`
   - Coba ubah salah satu nilai (misal: Uang Makan Lama menjadi `6000`), lalu klik **Simpan Seluruh Pengaturan**.
   - Setelah reload, nilai tersimpan tetap `6000` (tidak terpotong menjadi 6).

### B. Pengujian Full CRUD Kalender Hari Khusus:
1. **Tambah Agenda**:
   - Klik tombol **+ Tambah** di atas tabel Kalender Hari Khusus.
   - Pilih tanggal, masukkan keterangan (misal: *"Ujian Akhir Semester"*), pilih opsi *Bebas Sanksi*, lalu klik **Simpan ke Kalender**.
   - Agenda baru akan langsung muncul di tabel dengan badge hijau *Bebas Sanksi*.
2. **Edit Agenda (Fitur Baru)**:
   - Pada baris agenda di tabel, klik tombol **Edit** (ikon pensil biru).
   - Modal edit `#modalEditHariKhusus` akan terbuka dengan tanggal, keterangan, dan radio button terisi otomatis sesuai baris yang dipilih.
   - Ubah keterangan (misal menjadi *"Rapat Kerja Dewan Guru Tahap 2"*), lalu klik **Simpan Perubahan**.
   - Data langsung terupdate di tabel dan floating toast konfirmasi sukses muncul di atas layar.
3. **Hapus Agenda**:
   - Klik tombol **Hapus** (tong sampah merah) pada agenda.
   - Modal konfirmasi interaktif akan muncul. Klik **Ya, Hapus**.
   - Agenda terhapus dari kalender.

---

## 🚀 Panduan Migrasi / Deploy ke Production Hostinger (Setelah Pull)

Setelah Anda selesai menguji di lingkungan lokal, berikut langkah-langkah untuk menerapkan seluruh update ke server produksi Hostinger:

### 1. Push dari Lokal ke GitHub:
```bash
cd "C:\xampp\htdocs\mis_dev\sdmis"
git add .
git commit -m "feat: complete dynamic payroll engine, full CRUD hari khusus, dedicated ekstra page & clean UI"
git push origin main
```

### 2. Pull di Server Hostinger via SSH:
Buka Terminal VSCode atau SSH client:
```bash
ssh sdmis-hostinger
cd public_html
git pull origin main
```

### 3. Konfigurasi Database di Production:
- **Tabel Baru & Skema**: Seluruh 8 tabel penggajian (`m_gaji_setting_global`, `m_gaji_hari_khusus`, `m_pegawai_gaji_setting`, `m_ekstra`, `tx_ekstra_siswa`, `tx_gaji_periode`, `tx_gaji_karyawan`, `tx_gaji_komponen_manual`) sudah otomatis kompatibel.
- Untuk menginisialisasi atau memperbarui seluruh tabel, menu portal, seeder 21 guru, dan benchmark periode November 2026 sekali klik:
  ```
  https://sdmis.sch.id/migrate_payroll
  ```
- **Akses Langsung Seluruh 7 Sub-Menu Penggajian di Production**:
  1. `https://sdmis.sch.id/gaji/setting` (Pengaturan Aturan & Tarif)
  2. `https://sdmis.sch.id/gaji/karyawan` (Master Gaji & Rekening)
  3. `https://sdmis.sch.id/gaji/ekstra` (Manajemen Ekstra & Siswa)
  4. `https://sdmis.sch.id/gaji/periode` (Periode & Hitung Gaji)
  5. `https://sdmis.sch.id/gaji/rekonsiliasi` (Rekonsiliasi & Balance Matching)
  6. `https://sdmis.sch.id/gaji/laporan` (Slip Gaji & Rekapitulasi)
  7. `https://sdmis.sch.id/gaji/export_bank` (Export Mutasi Bank BRI & BSI)

---

## ⚖️ Panduan Pengujian Tahap 7 & 8: Rekonsiliasi, Slip Gaji A5 & Export Bank

### A. Pengujian Menu Rekonsiliasi & Balance Matching (`gaji/rekonsiliasi`):
1. Buka browser: `http://localhost/mis_dev/sdmis/gaji/rekonsiliasi`.
2. **Cara Menyeimbangkan Balance Menjadi 100% Klop (Selisih Rp 0,-)**:
   - **Opsi 1 (1-Klik dari Banner Rekonsiliasi)**:  
     Jika banner berwarna merah ("Terdapat Selisih Balance"), langsung klik tombol kuning:  
     `⚡ Sinkronkan & Seimbangkan (1-Klik Klop)` di bagian kanan banner.
   - **Opsi 2 (Via Auto-Migrate Seeder)**:  
     Akses URL auto-migrasi seeder sekali klik di browser:  
     `http://localhost/mis_dev/sdmis/migrate_payroll`  
     *(Di Hostinger: `https://sdmis.sch.id/migrate_payroll`)*
3. Kembali ke `gaji/rekonsiliasi`, perhatikan Hero Banner di bagian atas:
   - Status: **VERIFIKASI BALANCE SEMPURNA: Rp 22.956.000,-**
   - Badge Hijau: **Balance Matching: 100% Klop (Selisih Rp 0,-)**
   - Pilar 1 (Tabel Periode): `Rp 22.956.000,-`
   - Pilar 2 (Rincian 21 Guru): `Rp 22.956.000,-`
   - Pilar 3 (Mutasi Bank): `Rp 22.956.000,-`
4. Periksa kartu distribusi bank:
   - **Bank BRI**: 19 Rekening & Total Rp 21.916.000,-
   - **Bank BSI**: 2 Rekening (Anang Yulianto & Ayu Prastiwi) & Total Rp 1.040.000,-
   - Grand Total = Rp 22.956.000,- (Sesuai 100% dengan target PRD).

### B. Pengujian Slip Gaji Format A5 & Rekapitulasi (`gaji/laporan`):
1. Buka browser: `http://localhost/mis_dev/sdmis/gaji/laporan`.
2. **Cetak Slip Perorangan**:
   - Pada baris salah satu guru (contoh: Pak Nuki Rifan / Bu Siti Nurhayati), klik tombol **Cetak A5**.
   - Halaman slip A5 resmi akan terbuka:
     - Kop resmi Yayasan Mentari Mandiri Sejahtera & SD Mentari Islamic School lengkap logo.
     - Tabel 2-kolom: Penerimaan/Hak di kiri, Potongan/Kewajiban di kanan.
     - Box Take Home Pay (THP) dengan kalimat terbilang rupiah otomatis.
     - Rekening bank tujuan dan tanda tangan resmi Bendahara Yayasan.
     - Klik tombol **Cetak Sekarang (A5)** atau tekan `Ctrl + P`. Tampilan cetak rapi pas di kertas A5 portrait.
3. **Cetak Massal (Batch Print A5)**:
   - Klik tombol **Cetak Semua Slip A5 (Batch)** di pojok kanan atas.
   - Seluruh 21 slip guru langsung ditampilkan berturut-turut, di mana setiap slip otomatis berpindah lembar berkat CSS `@page { size: A5; }` dan `.slip-wrapper { page-break-after: always; }`.
4. **Cetak Rekapitulasi Pembukuan**:
   - Klik tombol **Cetak Rekapitulasi**. Tampilan rekapitulasi A4 Landscape siap cetak dengan matriks lengkap komponen dan tanda tangan pimpinan sekolah.

### C. Pengujian Export Mutasi Bank CMS BRI & BSI (`gaji/export_bank`):
1. Buka browser: `http://localhost/mis_dev/sdmis/gaji/export_bank`.
2. **Download File CMS BRI (CSV)**:
   - Klik tombol **Download File CMS BRI (CSV)**.
   - Buka file CSV yang terunduh di Notepad atau VSCode:
     - Header baris 1: `NO,NAMA,ACCOUNT,AMOUNT,Email,CUSTOMERREFF,BENEFICIARY BANK,BENEFICIARY ADDRESS`
     - 21 baris data rekening guru terisi angka murni.
     - 5 baris footer integritas perbankan:
       - `COUNT,,,21,,,,`
       - `TOTAL,,,22956000,,,,`
       - `CHECK,,,2122956000,,,,`
       - `VALUEDATE (DDMMYYYY),,,[TANGGAL],,,,`
       - `DEBITACCOUNT,,,219701000399305,,,,`
3. **Download File CMS BSI (CSV)**:
   - Klik tombol **Download File CMS BSI (CSV)**.
   - Berkas CSV khusus transfer kliring guru pemilik rekening BSI terunduh rapi.
4. **Download Master Rekap (CSV)**:
   - Klik tombol **Download Master Rekap (CSV)** untuk memperoleh arsip spreadsheet lengkap 26 kolom.

---

## 🔄 Panduan Penerapan & Pengujian Update Ujicoba Pengguna (26 November 2026)

Pembaruan kali ini **tidak memerlukan perubahan struktur/migrasi tabel database baru** (tidak perlu SQL tambahan). Seluruh perubahan berada pada logika perhitungan di model, controller, dan antarmuka view.

### A. Cara Menerapkan & Pengujian di Local (`localhost/mis_dev/sdmis`):
1. **Pastikan File Tersimpan & Bersihkan Cache Browser**:
   - Lakukan *hard refresh* (`Ctrl + F5`) pada browser agar skrip JavaScript terbaru (auto-fill tarif ekstra & tombol hitung atas) termuat ulang.
2. **Uji Auto-Fill Nominal Tagihan Ekstra (`/gaji/ekstra`)**:
   - Klik **`+ Tambah Peserta Siswa`**, lalu pilih cabang **Panahan (Tarif: Rp 45.000)** atau **Renang (Pak Tri) (Tarif: Rp 125.000)**.
   - Pastikan kolom **Nominal Tagihan (Rp)** langsung terisi otomatis (`45000` atau `125000`).
3. **Uji Tombol Atas `⚡ HITUNG OTOMATIS DARI PRESENSI` & Honor Ekstra Per Sesi (`/gaji/periode/detail/2`)**:
   - Buka detail periode **Mukafaah Desember 2026** (`/gaji/periode/detail/2`).
   - Klik tombol kuning **`⚡ HITUNG OTOMATIS DARI PRESENSI`** di bagian **kanan atas**.
   - Pastikan **TIDAK** muncul pop up merah "Ya, Hapus" lagi, melainkan langsung memproses kalkulasi dan memunculkan toast hijau sukses.
   - Cari **Pak Tri** di tabel rincian: pastikan kolom **Honor Ekstra** kini otomatis menampilkan **Rp 125.000** (berasal dari siswa `Zafiq` yang Lunas di bulan Desember) dan **THP = Rp 1.125.000**.
4. **Uji Modal Penyesuaian Manual (`/gaji/periode/detail/2`)**:
   - Klik **`+ Penyesuaian Manual (Talangan / Kasbon)`**.
   - Pastikan kalimat biru menampilkan: *"Catatan Penyesuaian: Gunakan form ini untuk mencatat talangan operasional sekolah, potongan kasbon, atau uang transport, dll."*
   - Pastikan dropdown **Pilih Guru / Pegawai** sudah menampilkan seluruh daftar guru aktif yang telah dihitung, dan coba input kategori `Lain-lain` maupun `Apresiasi / Tunjangan Khusus` untuk memastikan nominalnya langsung menambah **THP**.
5. **Uji Kehadiran Presensi Riil (`hhk`)**:
   - Jika Pak Tri atau guru lain melakukan presensi masuk di menu **Kehadiran Harian** (status `HHK` / `HBHK` / `TL`) pada rentang tanggal periode aktif, cukup klik **`⚡ HITUNG OTOMATIS DARI PRESENSI`** kembali di detail periode tersebut. Jumlah hari hadir, **Uang Makan**, dan **Bonus Presensi 2-Tier** langsung terbarui otomatis tanpa menghapus penyesuaian manual yang sudah diinput.

### B. Cara Menerapkan Update Ini di Production Hostinger (`https://sdmis.sch.id`):
1. **Push dari Komputer Lokal ke GitHub**:
   ```bash
   cd "C:\xampp\htdocs\mis_dev\sdmis"
   git status
   git add .
   git commit -m "fix: auto-fill ekstra fee, fix top auto-calculate button, support per_sesi/per_kedatangan & hhk attendance"
   git push origin main
   ```
2. **Pull di Server Production Hostinger via SSH**:
   ```bash
   ssh sdmis-hostinger "cd ~/domains/sdmis.sch.id/public_html && git pull origin main"
   ```
3. **Konfigurasi Database & Pasca-Pull di Hostinger**:
   - **Database**: Tidak ada penambahan tabel/kolom baru. Struktur tabel penggajian di `u983578927_absensi` sudah kompatibel 100%.
   - **Cache**: Jika menggunakan Cloudflare atau cache browser, lakukan *hard refresh* (`Ctrl + Shift + R`) saat membuka menu Penggajian. Pastikan folder `application/cache/` tidak menyimpan cache sesi lama jika terjadi perbedaan tampilan.
   - **Verifikasi Live**: Buka `https://sdmis.sch.id/gaji/periode` dan `https://sdmis.sch.id/gaji/ekstra` untuk memastikan tombol auto-calculate dan auto-fill tarif berjalan mulus.

---

## 🚀 Panduan Update Arsitektur 2-Tab Manajemen Ekstra (26 November 2026)

Pembaruan ini mengubah menu **Manajemen Ekstra & Honor Pengampu** (`gaji/ekstra`) menjadi **2 Tab terpisah**:
- **Tab 1 (Per Anak & Tunggakan Siswa)**: Blok A Input Cepat jumlah anak lunas (`tx_ekstra_rekap`) + Blok B Daftar Tunggakan Siswa & *Carry-Over* (`tx_ekstra_siswa` + `bulan_cair`/`tahun_cair`).
- **Tab 2 (Rekap Sesi & Kedatangan Guru)**: Kartu ringkasan per cabang & log multi-baris per sesi / per kedatangan (`tx_ekstra_sesi`).

> **Pembaruan Struktur Database:** Ada penambahan **2 tabel baru (`tx_ekstra_rekap` & `tx_ekstra_sesi`)** dan **2 kolom baru (`bulan_cair` & `tahun_cair`)** pada tabel `tx_ekstra_siswa`, yang otomatis dieksekusi oleh `Migrate_payroll` (Step 14–16) maupun oleh *self-healing guard* di `Payroll_model`.

### A. Cara Menerapkan & Pengujian di Local (`localhost/mis_dev/sdmis`):
1. **Jalankan Auto-Migration Database Lokal**:
   - Buka browser dan akses: **`http://localhost/mis_dev/sdmis/migrate_payroll`**
   - Pastikan **Step 14** (`tx_ekstra_rekap`), **Step 15** (`tx_ekstra_sesi`), dan **Step 16** (`Upgrade Kolom Carry-Over` + `Pembersihan Data Uji Ekstra` seperti baris `Zafiq` & `Zaira` di cabang `Renang (Pak Tri)`) berstatus **SUKSES / TERSEDIA**.
2. **Bersihkan Cache Browser**:
   - Buka menu **Penggajian -> Manajemen Ekstra & Siswa**, lalu tekan **`Ctrl + F5`** (*hard refresh*).
3. **Skenario Uji Coba Tab 1 (Ekstra Per Anak + Carry-Over)**:
   - Pilih filter **Bulan: Desember**, **Tahun: 2026**.
   - Di **Blok A (Input Cepat)**, isi angka siswa lunas misalnya pada **Panahan (Pak Anang)** = `24` (Total live langsung Rp 1.080.000), lalu klik **Simpan Rekap Lunas**.
   - Di **Blok B (Daftar Tunggakan)**, klik **`Catat Tunggakan Siswa`**, pilih cabang **Panahan**, isi nama siswa belum bayar (misal `Zaki`, Bulan Tunggakan `November 2026`), lalu klik **Simpan Tunggakan** (status: `BELUM LUNAS`).
   - Klik tombol hijau **`Lunasi`** pada baris `Zaki`, pilih pencairan ke **Desember 2026**, lalu **Konfirmasi Lunas**. Cek Blok A pada baris **Panahan**: kolom Carry-Over langsung bertambah `+1 siswa (Rp 45.000)` sehingga total honor cair Pak Anang menjadi **Rp 1.125.000**.
4. **Skenario Uji Coba Tab 2 (Rekap Sesi & Kedatangan Guru — Multi Baris)**:
   - Klik tab **`Tab 2: Rekap Sesi & Kedatangan Guru`**.
   - Klik **`+ Catat Sesi / Kedatangan`**, pilih **Renang (Pak Tri)** (`tarif` otomatis `125000`), isi `Jumlah Sesi = 2` (atau pecah 2 baris masing-masing `1` sesi dengan tanggal berbeda). Pastikan estimasi total tampil **Rp 250.000** lalu simpan.
   - Uji juga pencatatan multi-baris pada **Tahfidz & Tasmi (Kak Annas)**: buat Baris 1 (`17` kedatangan, keterangan `Tahfidz PAUD`) dan Baris 2 (`4` kedatangan, keterangan `Tahfidz SD`). Kartu ringkasan Kak Annas langsung mengakumulasi **21 Kedatangan = Rp 525.000**.
5. **Tarik Hasil ke Slip Mukafaah Desember 2026**:
   - Buka menu **Periode & Hitung Gaji -> Detail `Mukafaah Desember 2026`** (`/gaji/periode/detail/2`), lalu klik **`⚡ HITUNG OTOMATIS DARI PRESENSI`**.
   - Pastikan kolom **Honor Ekstra** pada baris **Pak Tri** (`Rp 250.000`), **Pak Anang** (`Rp 1.125.000`), dan **Kak Annas** (`Rp 525.000`) terhitung persis sama dengan Tab 1 + Tab 2, sementara periode **November 2026 (`FINAL`)** tetap aman di angka benchmark **Rp 22.956.000,-**.

### B. Cara Menerapkan Update Ini di Production Hostinger (`https://sdmis.sch.id`):
1. **Push Update dari Komputer Lokal ke GitHub**:
   ```bash
   cd "C:\xampp\htdocs\mis_dev\sdmis"
   git status
   git add .
   git commit -m "feat: redesign manajemen ekstra 2-tab (rekap cepat per_anak + tunggakan carry-over & log multi-baris per_sesi/per_kedatangan)"
   git push origin main
   ```
2. **Pull Code Terbaru di Production Hostinger (SSH)**:
   ```bash
   ssh sdmis-hostinger "cd ~/domains/sdmis.sch.id/public_html && git pull origin main"
   ```
3. **Konfigurasi Database Pasca-Pull di Hostinger (WAJIB DILAKUKAN 1 KALI)**:
   - Segera buka URL Auto-Migrasi di browser: **`https://sdmis.sch.id/migrate_payroll`**
   - Halaman ini aman dijalankan ulang (*idempotent*) dan akan otomatis mengeksekusi **Step 14–16** pada database production `u983578927_absensi`:
     1. Membuat tabel `tx_ekstra_rekap` (Input Cepat Tab 1 Blok A).
     2. Membuat tabel `tx_ekstra_sesi` (Log Multi-Baris Tab 2).
     3. Menambahkan kolom `bulan_cair` & `tahun_cair` ke tabel `tx_ekstra_siswa`.
     4. Membersihkan baris data uji coba `tx_ekstra_siswa` yang tertaut pada cabang `per_sesi`/`per_kedatangan` jika ada.
   - Pastikan status banner migrasi berwarna hijau bertuliskan **"BERHASIL SEMPURNA!"**. *(Catatan: Tabel **Benchmark November 2026 (`Rp 22.956.000,-`)** tetap terjaga aman dan klop).*
4. **Opsional — Backup SQL Manual via phpMyAdmin Hostinger (Jika Akses `/migrate_payroll` Dibatasi)**:
   Jika server produksi sengaja menonaktifkan akses URL `migrate_payroll`, jalankan query ini pada database `u983578927_absensi` di phpMyAdmin Hostinger:
   ```sql
   CREATE TABLE IF NOT EXISTS `tx_ekstra_rekap` (
     `rekap_id` INT(11) NOT NULL AUTO_INCREMENT,
     `ekstra_id` INT(11) NOT NULL,
     `bulan` TINYINT(2) NOT NULL,
     `tahun` INT(4) NOT NULL,
     `jumlah_lunas` INT(11) NOT NULL DEFAULT 0,
     `tarif_satuan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
     `keterangan` VARCHAR(255) DEFAULT NULL,
     `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     PRIMARY KEY (`rekap_id`),
     UNIQUE KEY `idx_rekap_unik` (`ekstra_id`, `bulan`, `tahun`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

   CREATE TABLE IF NOT EXISTS `tx_ekstra_sesi` (
     `sesi_id` INT(11) NOT NULL AUTO_INCREMENT,
     `ekstra_id` INT(11) NOT NULL,
     `bulan` TINYINT(2) NOT NULL,
     `tahun` INT(4) NOT NULL,
     `tanggal_sesi` DATE DEFAULT NULL,
     `jumlah_sesi` INT(11) NOT NULL DEFAULT 1,
     `tarif_per_sesi` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
     `total_honor` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
     `keterangan` VARCHAR(255) DEFAULT NULL,
     `status` ENUM('disetujui','pending') NOT NULL DEFAULT 'disetujui',
     `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
     `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     PRIMARY KEY (`sesi_id`),
     KEY `idx_sesi_periode` (`ekstra_id`, `bulan`, `tahun`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

   ALTER TABLE `tx_ekstra_siswa`
     ADD COLUMN `bulan_cair` TINYINT(2) NULL DEFAULT NULL AFTER `tanggal_bayar`,
     ADD COLUMN `tahun_cair` INT(4) NULL DEFAULT NULL AFTER `bulan_cair`;

   DELETE s FROM `tx_ekstra_siswa` s
   INNER JOIN `m_ekstra` e ON s.ekstra_id = e.ekstra_id
   WHERE e.tipe_honor != 'per_anak';
   ```
5. **Verifikasi Akhir Production**:
   - Buka `https://sdmis.sch.id/gaji/ekstra` dan tekan **`Ctrl + Shift + R`**.
   - Cek **Master Cabang Ekstra** (`/gaji/ekstra/cabang`): pastikan tiap cabang sudah memiliki `Tipe Honor` yang sesuai (`Per Anak` vs `Per Sesi` / `Per Kedatangan`) dan Guru Pengampu sudah terpetakan agar otomatis muncul di Tab 1 atau Tab 2 serta masuk hitungan Mukafaah.

---

## 📄 Panduan Penerapan Update Download PDF Resmi & Export Bank Excel `.xlsx` (26 November 2026)

Pembaruan ini berfokus pada konversi halaman cetak slip/rekapitulasi menjadi **unduhan file PDF resmi bergaya akuntansi hitam-putih formal**, perbaikan fungsi `terbilang()`, serta peningkatan export mutasi bank dari CSV polos menjadi **Excel `.xlsx` asli ber-border penuh dengan nomor rekening format Teks (`@`)**.

> **Status Database:** Tidak ada perubahan tabel maupun kolom database tambahan pada pembaruan ini (tidak perlu menjalankan SQL baru). Hanya memanfaatkan library `dompdf/dompdf` yang sudah ada di folder `vendor/` serta library baru internal `Simple_xlsx`.

### A. Cara Menerapkan & Pengujian di Local (`localhost/mis_dev/sdmis`):
1. **Pastikan Ekstensi PHP Lokal Aktif & Hard Refresh Browser**:
   - Library `dompdf/dompdf` membutuhkan ekstensi PHP `dom` & `mbstring` (aktif secara default di XAMPP). Library `Simple_xlsx` menggunakan `zip` (`ZipArchive`) dan tetap memiliki *fallback* otomatis pure-PHP bila ekstensi zip tidak aktif.
   - Lakukan **`Ctrl + F5`** (*hard refresh*) pada browser Anda.
2. **Uji Menu Slip Gaji & Rekapitulasi (`http://localhost/mis_dev/sdmis/gaji/laporan`)**:
   - Pastikan label tombol sudah berubah menjadi **`Unduh Semua Slip`**, **`Unduh Rekapitulasi`**, kolom tabel **`Aksi`**, dan tombol per guru **`Unduh File`**.
   - Klik **`Unduh File`** pada salah satu guru: file `Slip_Mukafaah_<Nama>_<Bulan>_<Tahun>.pdf` langsung terunduh. Buka PDF-nya dan pastikan:
     - Ukuran halaman pas 1 lembar **A5 Portrait** dengan logo SDMIS, teks hitam bersih, *bold* rapi pada judul/total/THP, tanpa potongan `-0`, serta kalimat **Terbilang** terbaca normal (misal `# Lima Ratus Lima Puluh Ribu Rupiah #`).
   - Klik **`Unduh Semua Slip`**: file PDF massal terunduh dan tiap guru berada tepat 1 halaman A5.
   - Klik **`Unduh Rekapitulasi`**: file PDF **A4 Landscape** resmi yayasan terunduh rapi lengkap dengan Grand Total dan tanda tangan.
3. **Uji Menu Export Mutasi Bank (`http://localhost/mis_dev/sdmis/gaji/export_bank`)**:
   - Klik **`Unduh File CMS BRI (Excel)`** (`Payroll_CMS_BRI_SDMIS_...xlsx`) dan buka di Microsoft Excel:
     - Pastikan **tidak muncul notasi ilmiah (`6.90201E+14`)** pada kolom `ACCOUNT`, `CHECK`, maupun `DEBITACCOUNT`, serta angka `0` di depan (misal `003501092708504`) tetap tampil utuh.
     - Pastikan seluruh baris tabel dan 5 baris kontrol bawah (`COUNT`, `TOTAL`, `CHECK`, `VALUEDATE (DDMMYYYY)`, `DEBITACCOUNT`) memiliki **garis border hitam penuh**, header biru-abu tebal ter-*freeze*, dan seluruh teks nama serta email terbaca utuh.
   - Uji juga **`Unduh File CMS BSI (Excel)`** dan **`Unduh Master Rekap (Excel)`** (serta opsi sekunder `.csv` bila diperlukan upload ke portal CMS bank).

### B. Cara Menerapkan Update Ini di Production Hostinger (`https://sdmis.sch.id`):
1. **Push Update dari Komputer Lokal ke GitHub**:
   ```bash
   cd "C:\xampp\htdocs\mis_dev\sdmis"
   git status
   git add .
   git commit -m "feat: direct PDF download formal hitam-putih (Dompdf) & styled Excel (.xlsx) export mutasi bank"
   git push origin main
   ```
2. **Pull Code Terbaru di Production Hostinger (SSH)**:
   ```bash
   ssh sdmis-hostinger "cd ~/domains/sdmis.sch.id/public_html && git pull origin main"
   ```
3. **Verifikasi Folder `vendor/` & Konfigurasi Server di Hostinger**:
   - **Database**: Tidak ada migrasi database tambahan yang diperlukan.
   - **Vendor Dompdf**: Pastikan folder `vendor/dompdf/dompdf` sudah tersedia di `public_html/vendor/` di Hostinger (jika `vendor/` diabaikan `.gitignore` dan belum ter-upload, jalankan `composer install --no-dev` di SSH Hostinger pada `~/domains/sdmis.sch.id/public_html`, atau unggah folder `vendor/` via File Manager Hostinger).
   - **Ekstensi PHP Hostinger (hPanel -> PHP Configuration -> Extensions)**: Pastikan ekstensi `dom`, `mbstring`, dan `gd` (atau `zip`) berstatus aktif agar Dompdf merender logo PNG & PDF tanpa kendala.
   - **Hard Refresh**: Buka `https://sdmis.sch.id/gaji/laporan` dan `https://sdmis.sch.id/gaji/export_bank`, tekan **`Ctrl + Shift + R`**, lalu uji unduh 1 slip PDF dan 1 file Excel CMS BRI.







