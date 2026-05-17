<div align="center">

# 🎮 PixelStation

**Sistem Informasi Manajemen Persewaan PlayStation**

*Solusi digital untuk pengelolaan rental PS4 & PS5 secara efisien dan terorganisir*

<br>

![Version](https://img.shields.io/badge/Version-1.0.0-blue?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-22c55e?style=for-the-badge)

<br>

[![YouTube Demo](https://img.shields.io/badge/Demo-YouTube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://youtu.be/ok9XBSdNkck?si=IfUdPlACFMNPTmZ9)

<br>

<a href="https://youtu.be/ok9XBSdNkck?si=IfUdPlACFMNPTmZ9">
  <img src="https://img.youtube.com/vi/ok9XBSdNkck/maxresdefault.jpg" width="720" alt="Demo Video PixelStation">
</a>

</div>

---

## 📖 Tentang Proyek

**PixelStation** adalah aplikasi web berbasis PHP Native untuk mengelola persewaan PlayStation (PS4 & PS5) secara digital dan terpusat. Sistem ini dirancang untuk mempermudah proses booking dari sisi pelanggan, sekaligus memberikan kendali penuh kepada admin dalam memantau unit, katalog game, transaksi, dan laporan keuangan — semuanya dalam satu platform.

Dikembangkan oleh **Ahmad Fadilah Syah** ([@ahmadfadilahsyah](https://github.com/ahmadfadilahsyah)) dan **Najwa Ramadhan** ([@snnajward](https://github.com/snnajward)) sebagai proyek sistem informasi rental PlayStation.

---

## ✨ Fitur Utama

### 👤 Pelanggan
- Registrasi & login dengan verifikasi Captcha
- Melihat daftar unit PS4/PS5 yang tersedia
- Booking unit dengan pemilihan durasi sewa
- Riwayat booking dan ringkasan transaksi pribadi

### 🛠️ Administrator
- Dashboard statistik real-time
- Manajemen data PlayStation (CRUD)
- Manajemen katalog game (CRUD)
- Pengelolaan seluruh data booking dan pengguna
- Ekspor laporan keuangan ke format Excel (XLSX)

---

## 🖼️ Tampilan Aplikasi

<div align="center">
  <table>
    <tr>
      <td align="center">
        <strong>🏠 Landing Page</strong><br><br>
        <img src="assets/index.jpeg" width="360" alt="Landing Page">
      </td>
      <td align="center">
        <strong>🔐 Login + Captcha</strong><br><br>
        <img src="assets/login.jpeg" width="360" alt="Login">
      </td>
    </tr>
    <tr>
      <td align="center"><br></td>
      <td align="center"><br></td>
    </tr>
    <tr>
      <td align="center">
        <strong>👤 Dashboard Pengguna</strong><br><br>
        <img src="assets/dbuser.jpeg" width="360" alt="User Dashboard">
      </td>
      <td align="center">
        <strong>📊 Dashboard Admin</strong><br><br>
        <img src="assets/dbadmin.jpeg" width="360" alt="Admin Dashboard">
      </td>
    </tr>
  </table>
</div>

> Semua gambar tersimpan di folder `assets/`

---

## ⚙️ Teknologi

| Kategori | Detail |
|----------|--------|
| **Backend** | PHP Native (Procedural) |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, CSS3, Bootstrap 5.3 |
| **Library** | PhpSpreadsheet (ekspor Excel) |
| **Keamanan** | `password_hash()`, Captcha sederhana |

---

## 📁 Struktur Folder

```
pixelstation/
├── admin/              # Halaman & logika administrator
├── assets/             # Aset statis (gambar, CSS, JS)
│   ├── dbadmin.jpeg
│   ├── dbuser.jpeg
│   ├── index.jpeg
│   └── login.jpeg
├── auth/               # Autentikasi (login, register, captcha)
├── config/             # Konfigurasi koneksi database
├── user/               # Halaman & logika pengguna
├── vendor/             # Dependensi Composer
├── composer.json       # Manajemen dependensi
├── composer.lock       # Lock file Composer
├── index.php           # Entry point / landing page
├── logout.php          # Proses logout
├── rental_ps.sql       # Skema database
└── README.md           # Dokumentasi proyek
```

---

## 🚀 Instalasi & Menjalankan

**Prasyarat:** PHP 8.x, MySQL 8.0, Composer, dan web server (Apache/Nginx atau XAMPP/Laragon).

```bash
# 1. Clone repositori
git clone https://github.com/username/pixelstation.git
cd pixelstation

# 2. Install dependensi Composer
composer install

# 3. Import database
#    Buat database baru, lalu import file skema:
mysql -u root -p nama_database < rental_ps.sql

# 4. Konfigurasi koneksi database
#    Edit file config/database.php sesuai kredensial lokal Anda

# 5. Jalankan aplikasi
#    Akses melalui browser: http://localhost/pixelstation
```

---

<div align="center">
  <br>
  <sub>Made with ❤️ by the PixelStation Team</sub>
</div>
