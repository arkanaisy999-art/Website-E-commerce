<?php
session_start();
require "cek_admin.php"; 
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $id_admin = $_SESSION['id_user'] ?? 0;
    
    $current_password = $_POST['password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validasi awal
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_msg'] = "Semua kolom password wajib diisi!";
        header("Location: profile_admin.php");
        exit();
    }

    // 2. Validasi kecocokan password baru
    if ($new_password !== $confirm_password) {
        $_SESSION['error_msg'] = "Konfirmasi password baru tidak cocok!";
        header("Location: profile_admin.php");
        exit();
    }

    // 3. Validasi panjang karakter
    if (strlen($new_password) < 8) {
        $_SESSION['error_msg'] = "Password baru minimal harus 8 karakter!";
        header("Location: profile_admin.php");
        exit();
    }

    // 4. Ambil password dari database
    $stmt = mysqli_prepare($conn, "SELECT password FROM pengguna WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_admin);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        // ====================================================================
        // PERBAIKAN: Bandingkan teks polos secara langsung (Tanpa password_verify)
        // ====================================================================
        if ($current_password === $user['password']) {
            
            // PERBAIKAN: Simpan password baru sebagai teks polos juga (Tanpa password_hash)
            $plain_password = $new_password; 

            // 7. Update password baru ke database
            $update_stmt = mysqli_prepare($conn, "UPDATE pengguna SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "si", $plain_password, $id_admin);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $_SESSION['success_msg'] = "Password berhasil diperbarui!";
            } else {
                $_SESSION['error_msg'] = "Gagal memperbarui database. Silakan coba lagi.";
            }
            mysqli_stmt_close($update_stmt);

        } else {
            $_SESSION['error_msg'] = "Password saat ini (lama) yang Anda masukkan salah!";
        }
    } else {
        $_SESSION['error_msg'] = "Pengguna tidak ditemukan.";
    }

    header("Location: profile_admin.php");
    exit();
} else {
    header("Location: profile_admin.php");
    exit();
}
?>