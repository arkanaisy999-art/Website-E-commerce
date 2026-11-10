<?php
session_start();
include "config/koneksi.php";
require_once dirname(__FILE__) . '/vendor/autoload.php'; // Panggil library Midtrans (Composer)

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-L7rVWWWxT~0aneTugZJT6mUH'; // Ganti dengan Server Key Midtrans Anda
\Midtrans\Config::$isProduction = false; // Ubah ke true jika sudah go-live/uang asli
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

if (!isset($_SESSION['id_user'])) {
    header("Location: account.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_alamat = mysqli_real_escape_string($conn, $_POST['id_alamat']);
$total_bayar = mysqli_real_escape_string($conn, $_POST['total_bayar']);
$ongkir = mysqli_real_escape_string($conn, $_POST['ongkir']);
$tanggal = date("Y-m-d H:i:s");

// 1. Validasi Alamat
if (empty($id_alamat)) {
    echo "<script>alert('Please select or add a delivery address first!'); window.history.back();</script>";
    exit;
}

// 2. GENERATE NOMOR RESI ACAK (JNE-XXXXXXXXX)
$angka_acak = mt_rand(100000000, 999999999); 
$no_resi = "JNE-" . $angka_acak;

// 3. Simpan ke tabel pesanan
$query_pesanan = "INSERT INTO pesanan (id_pengguna, id_alamat, tanggal_pesanan, total_harga, shipping_method, ongkir, no_resi, status_pesanan) 
                  VALUES ('$id_user', '$id_alamat', '$tanggal', '$total_bayar', 'JNE', '$ongkir', '$no_resi', 'unpaid')";

if (mysqli_query($conn, $query_pesanan)) {
    $id_pesanan_baru = mysqli_insert_id($conn); // Mengambil ID Pesanan yang baru saja dibuat

    // 4. Logika Simpan ke Tabel DETAIL_PESANAN
    if (isset($_POST['is_buy_now'])) {
        $id_produk = mysqli_real_escape_string($conn, $_POST['id_produk']);
        $qty = (int)$_POST['qty'];
        $harga = (int)$_POST['harga'];
        $subtotal = $harga * $qty;

        $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, subtotal) 
                         VALUES ('$id_pesanan_baru', '$id_produk', '$qty', '$subtotal')";
        mysqli_query($conn, $query_detail);

    } else {
        $query_keranjang = mysqli_query($conn, "SELECT k.*, p.harga FROM keranjang k 
                                               JOIN produk p ON k.id_produk = p.id_produk 
                                               WHERE k.id_pengguna = '$id_user'");
        
        while ($row = mysqli_fetch_assoc($query_keranjang)) {
            $id_p = $row['id_produk'];
            $qty = (int)$row['qty'];
            $subtotal = (int)$row['harga'] * $qty;

            $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, subtotal) 
                             VALUES ('$id_pesanan_baru', '$id_p', '$qty', '$subtotal')";
            mysqli_query($conn, $query_detail);
        }

        // 5. Hapus Keranjang setelah Checkout berhasil
        mysqli_query($conn, "DELETE FROM keranjang WHERE id_pengguna = '$id_user'");
    }

    // =========================================================================
    // 6. INTEGRASI MIDTRANS CORE API (REQUEST QRIS DINAMIS)
    // =========================================================================
    $params = [
        'payment_type' => 'qris',
        'transaction_details' => [
            'order_id' => 'GLADS-' . $id_pesanan_baru . '-' . time(),
            'gross_amount' => (int)$total_bayar,
        ],
        'qris' => [
            'acquirer' => 'gopay'
        ]
    ];

    try {
        $response = \Midtrans\CoreApi::charge($params);
        $qr_code_url = '';
        
        // Ambil URL QR Code dari respons Midtrans
        foreach($response->actions as $action) {
            if($action->name == 'generate-qr-code') {
                $qr_code_url = $action->url;
            }
        }

        // Simpan URL QR Code ke dalam Session agar bisa dibaca di halaman order_success.php
        $_SESSION['qris_url_' . $id_pesanan_baru] = $qr_code_url;

    } catch (Exception $e) {
        echo "Gagal membuat QRIS via Midtrans: " . $e->getMessage();
        exit;
    }

    // 7. Redirect ke halaman Sukses/Pembayaran dengan membawa ID pesanan
    header("Location: order_success.php?id=" . $id_pesanan_baru);
    exit;

} else {
    echo "Error: " . mysqli_error($conn);
}
?>