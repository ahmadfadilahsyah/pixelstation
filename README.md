# PixelStation — Sistem Informasi Rental PlayStation

<p align="center">
  <img src="https://img.shields.io/badge/Environment-Web_Application-2f3e46?style=flat-square" alt="App Type">
  <img src="https://img.shields.io/badge/Core-PHP_Native-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-MySQL_/_MariaDB-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Frontend-Bootstrap_5-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/Dependencies-PhpSpreadsheet-885630?style=flat-square&logo=composer&logoColor=white" alt="Composer">
</p>

---

## 📌 Ikhtisar Proyek

**PixelStation** adalah platform manajemen persewaan PlayStation berbasis web yang dirancang untuk mentransformasi operasional bisnis rental konvensional ke ekosistem digital. Sistem ini menyediakan solusi ujung-ke-ujung (*end-to-end*), mulai dari manajemen reservasi mandiri secara *real-time* oleh pelanggan, hingga pengolahan data administratif serta pelaporan keuangan terpadu untuk pemilik rental.

### Resolusi Masalah:
* **Overbooking:** Mencegah terjadinya bentrok jadwal sewa unit melalui sistem validasi ketersediaan yang dinamis.
* **Integritas Data:** Mengamankan gerbang otentikasi dari otomatisasi bot berbahaya menggunakan verifikasi Captcha.
* **Efisiensi Finansial:** Memangkas waktu rekapitulasi data dengan menyediakan modul ekspor laporan siap cetak.

---

## 👥 Tim Pengembang

| Kontributor | Peran Utama | Profil GitHub |
| :--- | :--- | :--- |
| **Ahmad Fadilah Syah** | Core Developer / Backend & Database Engineer | [@ahmadfadilahsyah](https://github.com/ahmadfadilahsyah) |
| **Najwa Ramadhan** | UI/UX Designer / Frontend Engineer | [@snnajward](https://github.com/snnajward) |

---

## 📺 Media & Demonstrasi

Untuk gambaran menyeluruh mengenai alur kerja sistem, fitur, dan pengujian antarmuka aplikasi, silakan akses dokumentasi video berikut:

[![Tonton Video Demo](https://img.shields.io/badge/Video_Demonstrasi-Klik_Disini_Untuk_Memutar-ff0000?style=for-the-badge&logo=youtube&logoColor=white)](https://youtu.be/ok9XBSdNkck?si=IfUdPlACFMNPTmZ9)

---

## ⚙️ Spesifikasi & Fitur Sistem

### Modul Pelanggan (User Client)
* **Smart Booking Engine:** Antarmuka pemesanan unit PlayStation secara langsung dengan kalkulasi waktu sewa yang presisi.
* **Security & Account Management:** Autentikasi berlapis (Login/Register) terintegrasi dengan modul Captcha penangkal bot, serta manajemen profil pengguna.
* **Transaction Ledger:** Dasbor personal untuk melacak riwayat penyewaan, status verifikasi, dan histori pembayaran.

### Panel Administrasi (Admin Dashboard)
* **Analytical Dashboard:** Ringkasan eksekutif berupa grafik dan metrik statistik terkait performa rental dan unit paling produktif.
* **Data Master Control (CRUD):** Manajemen data terpusat untuk data pengguna, inventaris unit PlayStation, serta katalog judul game.
* **Live Monitoring:** Pengawasan *real-time* terhadap durasi aktif sewa dan antrean *booking* masuk.
* **Enterprise Reporting:** Modul konversi laporan transaksi periodik ke format **PDF** dan **Excel (XLSX)** berbasis *automated library streams*.

---

## 🗂️ Galeri Antarmuka

<details>
  <summary>📸 Klik untuk menampilkan detail tangkapan layar (Screenshots)</summary>
  <br>
  
  #### 1. Halaman Utama / Landing Page
  <img src="https://github.com/user-attachments/assets/40378448-ee5f-445f-8d5c-4ee76c6f3faa" width="100%" alt="PixelStation Landing Page">
  
  #### 2. Panel Manajemen Konten & Statistik
  <img src="https://github.com/user-attachments/assets/5c86efbd-d97a-44f4-ae7a-861cb094959c" width="100%" alt="PixelStation Admin Dashboard">
  
  #### 3. Formulir Transaksi & Reservasi
  <img src="https://github.com/user-attachments/assets/a8f62a07-145b-43ed-9517-40f4374b5f24" width="100%" alt="PixelStation Transaction Form">
</details>

---

## 📐 Arsitektur Direktori

```bash
pixelstation/
├── admin/          # Modul kontrol, visual, dan logika operasional Admin
├── assets/         # Resource visual statis (Kumpulan CSS, JS, dan Gambar)
├── auth/           # Gerbang keamanan aplikasi (Sistem Login, Register, Captcha)
├── config/         # Berkas konfigurasi koneksi Database & Session handling
├── user/           # Antarmuka interaktif dan fitur khusus untuk Pelanggan
├── vendor/         # Pustaka pihak ketiga yang diunduh via Composer
├── index.php       # Titik masuk utama (Landing Page) aplikasi
├── rental_ps.sql   # Blueprint/Skema basis data MySQL
└── composer.json   # Berkas manifes konfigurasi dependensi proyek
