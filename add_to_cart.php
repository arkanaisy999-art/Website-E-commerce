<?php
session_start();
include "config/koneksi.php";

// Di add_to_cart.php
if (isset($_SESSION['id_user'])) {
    // Jalankan query INSERT INTO keranjang seperti biasa
} else {
    // Simpan ke session
    $id_produk = $_POST['id_produk'];
    $qty = $_POST['quantity'];

    if (!isset($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = [];
    }

    if (isset($_SESSION['guest_cart'][$id_produk])) {
        $_SESSION['guest_cart'][$id_produk]['qty'] += $qty;
    } else {
        $_SESSION['guest_cart'][$id_produk] = ['qty' => $qty];
    }
    echo "success";
}

$id_user = $_SESSION['id_user'];
$id_produk = $_POST['id_produk'];
$qty = $_POST['quantity'];

$query = "INSERT INTO keranjang (id_pengguna, id_produk, qty) 
          VALUES ('$id_user', '$id_produk', '$qty') 
          ON DUPLICATE KEY UPDATE qty = qty + $qty";

if (mysqli_query($conn, $query)) {
    // JANGAN GUNAKAN HEADER LOCATION
    // Cukup kirim respon sukses
    echo "success";
} else {
    http_response_code(500);
    echo "error";
}