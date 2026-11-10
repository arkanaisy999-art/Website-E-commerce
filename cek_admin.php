<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 PROTEKSI HALAMAN: Hanya izinkan admin masuk
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    
    // Ambil riwayat halaman terakhir yang diakses user, jika tidak ada default ke index.php
    $kembali_ke = $_SESSION['halaman_terakhir'] ?? 'index.php';
    
    // Tendang balik ke halaman yang sedang mereka akses tadi
    header("Location: " . $kembali_ke);
    exit;
}
?>