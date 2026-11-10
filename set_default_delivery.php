<?php
session_start();
include "config/koneksi.php";

if (isset($_GET['id']) && isset($_SESSION['id_user'])) {
    $id_alamat = $_GET['id'];
    $id_user = $_SESSION['id_user'];

    // 1. Reset semua alamat user ini menjadi NOT default (0)
    mysqli_query($conn, "UPDATE alamat_pengiriman SET is_default = 0 WHERE id_pengguna = '$id_user'");

    // 2. Set alamat yang dipilih menjadi default (1)
    $query = mysqli_query($conn, "UPDATE alamat_pengiriman SET is_default = 1 WHERE id_alamat = '$id_alamat' AND id_pengguna = '$id_user'");

    if ($query) {
        header("Location: profile_delivery.php?status=success");
    } else {
        header("Location: profile_delivery.php?status=error");
    }
} else {
    header("Location: profile_delivery.php");
}
?>