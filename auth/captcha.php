<?php
session_start();

// Generate random string 5 karakter (campur huruf besar, kecil, angka)
$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
$captcha = '';
for ($i = 0; $i < 5; $i++) {
    $captcha .= $characters[rand(0, strlen($characters) - 1)];
}

// Simpan ke session (case-sensitive asli, nanti dibanding case-insensitive)
$_SESSION['captcha'] = $captcha;

// Buat gambar
$width = 150;
$height = 50;
$image = imagecreatetruecolor($width, $height);

// Warna background (putih susu)
$bg_color = imagecolorallocate($image, 255, 251, 245);
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Warna teks (pink gelap)
$text_color = imagecolorallocate($image, 232, 154, 174);

// Warna garis dan noise (pink soft)
$line_color = imagecolorallocate($image, 255, 181, 194);

// Tambah garis acak
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Tambah titik-titik noise
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $line_color);
}

// Gunakan font bawaan (5 = ukuran kecil)
$font = 5;
$font_width = imagefontwidth($font);
$font_height = imagefontheight($font);
$text_width = strlen($captcha) * $font_width;
$x = ($width - $text_width) / 2;
$y = ($height - $font_height) / 2;

// Tulis teks captcha
imagestring($image, $font, $x, $y, $captcha, $text_color);

// Header dan output
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>