# 🎮 PixelStation - Sistem Informasi Rental PlayStation

Aplikasi manajemen persewaan PlayStation berbasis web yang dirancang untuk mempermudah operasional rental, mulai dari booking pelanggan hingga laporan administrasi yang terintegrasi.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

---

## 👥 Pengembang (Developers)
Proyek ini dikembangkan secara kolaboratif oleh:
* **Ahmad Fadilah Syah**
* **Najwa Ramadhan**

---

## ✨ Fitur Unggulan

### 👤 Antarmuka Pengguna (User)
* **Smart Booking:** Sistem pemesanan rental PlayStation secara real-time.
* **Manajemen Akun:** Registrasi, Login (dengan Captcha), dan pengelolaan profil.
* **History:** Pelacakan riwayat booking yang pernah dilakukan.

### 🛠️ Panel Administrasi (Admin)
* **Interactive Dashboard:** Ringkasan data statistik operasional.
* **Data Management:** Kendali penuh atas data User, unit PlayStation, dan daftar Game.
* **Monitoring:** Pemantauan status booking yang aktif.
* **Reporting:** Ekspor laporan transaksi ke format **PDF** dan **Excel (XLSX)** menggunakan library PhpSpreadsheet.

---

## 🧰 Stack Teknologi
* **Core:** PHP Native
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap
* **Dependencies:** Composer (PhpSpreadsheet)

---

## 📁 Struktur Proyek
```bash
pixelstation/
├── admin/          # Modul dan logika operasional Admin
├── assets/         # Resource visual (CSS, JS, Images)
├── auth/           # Sistem keamanan (Login, Register, Captcha)
├── config/         # Konfigurasi Database & Session handling
├── user/           # Antarmuka dan fitur khusus Pelanggan
├── vendor/         # Library pihak ketiga (Composer)
├── index.php       # Landing page utama
├── rental_ps.sql   # Skema database MySQL
└── composer.json   # Konfigurasi dependensi project
