<?php
include "../config/session.php";
include "../config/koneksi.php";

$user_id = $_SESSION['id'];
$ps_id = $_POST['ps_id'];
$tanggal = $_POST['tanggal'];
$jam_mulai = $_POST['jam_mulai'];
$durasi = $_POST['durasi'];
$total_harga = $_POST['total_harga'];

$query = "INSERT INTO bookings (user_id, ps_id, tanggal, jam_mulai, durasi, total_harga, status) 
          VALUES ('$user_id', '$ps_id', '$tanggal', '$jam_mulai', '$durasi', '$total_harga', 'pending')";

if (mysqli_query($conn, $query)) {
    header("Location: dashboard-user.php?success=1");
    exit;
} else {
    header("Location: dashboard-user.php?error=1");
    exit;
}
?>