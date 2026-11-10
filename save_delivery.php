<?php
session_start();
include "config/koneksi.php";

// Pastikan session menggunakan id_user sesuai login_proses.php
if (!isset($_SESSION['id_user'])) { 
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit_address'])) {
    $id_user = $_SESSION['id_user'];
    
    // Ambil data dari form
    $id_alamat = mysqli_real_escape_string($conn, $_POST['id_alamat']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_penerima']);
    $email = mysqli_real_escape_string($conn, $_POST['email_penerima']);
    $telp = mysqli_real_escape_string($conn, $_POST['telepon']);
    $negara = mysqli_real_escape_string($conn, $_POST['negara']);
    $kota = mysqli_real_escape_string($conn, $_POST['kota_kecamatan']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);

    // Jika user menandai ini sebagai default, reset alamat lain milik user ini dulu (opsional)
    // Di kode ini kita asumsikan alamat baru/edit selalu jadi default (is_default = 1)
    mysqli_query($conn, "UPDATE alamat_pengiriman SET is_default = 0 WHERE id_pengguna = '$id_user'");

    // ... kode lainnya ...

    if (!empty($id_alamat)) {
        // Tanpa email_penerima dan negara
        $query = "UPDATE alamat_pengiriman SET 
                    nama_penerima = '$nama', 
                    email_penerima = '$email', 
                    telepon = '$telp', 
                    negara = '$negara', 
                    kota_kecamatan = '$kota', 
                    alamat_lengkap = '$alamat',
                    is_default = 1
                WHERE id_alamat = '$id_alamat' AND id_pengguna = '$id_user'";
    } else {
        // Tanpa email_penerima dan negara
        $query = "INSERT INTO alamat_pengiriman (id_pengguna, nama_penerima, email_penerima, telepon, negara, kota_kecamatan, alamat_lengkap, is_default) 
                VALUES ('$id_user', '$nama', '$email', '$telp', '$negara', '$kota', '$alamat', 1)";
    }

    // ... kode lainnya ...

    if (mysqli_query($conn, $query)) {
        header("Location: profile_delivery.php?status=success");
    } else {
        header("Location: profile_delivery.php?status=error");
    }
} else {
    header("Location: profile_delivery.php");
}
?>