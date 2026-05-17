<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /pixelstation/auth/login.php");
    exit;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>