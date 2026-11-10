<?php
session_start();
include "config/koneksi.php";

if (!isset($_SESSION['id_user'])) { exit; }

$id_user = $_SESSION['id_user'];
$id_alamat = $_GET['id'];

// 1. Cek apakah yang dihapus adalah alamat default
$check = mysqli_query($conn, "SELECT is_default FROM alamat_pengiriman WHERE id_alamat = '$id_alamat'");
$data = mysqli_fetch_assoc($check);

// 2. Hapus alamat
$delete = mysqli_query($conn, "DELETE FROM alamat_pengiriman WHERE id_alamat = '$id_alamat' AND id_pengguna = '$id_user'");

if ($delete) {
    // 3. Jika yang dihapus adalah default, cari satu alamat lain untuk jadi default
    if ($data['is_default'] == 1) {
        $get_next = mysqli_query($conn, "SELECT id_alamat FROM alamat_pengiriman WHERE id_pengguna = '$id_user' LIMIT 1");
        if (mysqli_num_rows($get_next) > 0) {
            $next = mysqli_fetch_assoc($get_next);
            $new_id = $next['id_alamat'];
            mysqli_query($conn, "UPDATE alamat_pengiriman SET is_default = 1 WHERE id_alamat = '$new_id'");
        }
    }
    header("Location: profile_delivery.php?status=success");
} else {
    header("Location: profile_delivery.php?status=error");
}