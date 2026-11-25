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





