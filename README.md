# 🎮 PixelStation

PixelStation adalah aplikasi web rental PlayStation berbasis PHP yang dirancang untuk membantu proses pemesanan rental, pengelolaan pelanggan, dan manajemen laporan secara digital.

Project ini dibuat sebagai sistem informasi rental PS dengan fitur admin dan user yang terintegrasi.

---

# ✨ Fitur Utama

## 👤 User
- Registrasi dan login akun
- Booking rental PlayStation
- Melihat riwayat booking
- Mengelola profil pengguna

## 🛠️ Admin
- Dashboard admin
- Manajemen data user
- Manajemen data PlayStation
- Manajemen game
- Monitoring booking
- Export laporan PDF
- Export laporan Excel

---

# 🧰 Teknologi yang Digunakan

- PHP Native
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap
- PhpSpreadsheet

---

# 📁 Struktur Folder

```bash
pixelstation/
│
├── admin/                 # Halaman dan fitur admin
├── assets/                # CSS, JS, gambar, dan asset lainnya
├── auth/                  # Login, register, captcha
├── config/                # Konfigurasi database dan session
├── user/                  # Halaman user
├── vendor/                # Composer dependencies
├── index.php              # Halaman utama
├── logout.php             # Logout user
├── rental_ps.sql          # Database project
├── composer.json
└── composer.lock
