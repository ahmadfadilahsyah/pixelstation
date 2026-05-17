<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rental_ps";   // atau "pixelstation", sesuaikan dengan database yang Anda buat

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>