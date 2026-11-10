<?php
session_start();
include "config/koneksi.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_pesanan = $_GET['id'];

// 1. TAMBAHKAN KODE INI DI SINI
if (isset($_POST['simulasi_bayar_success'])) {
    $update_query = mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'process' WHERE id_pesanan = '$id_pesanan'");
    
    if ($update_query) {
        echo "<script>alert('Simulasi Berhasil! Status pesanan diubah ke process.'); window.location='account.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui status: " . mysqli_error($conn) . "');</script>";
    }
}

$id_user = $_SESSION['id_user'] ?? null;

$query = mysqli_query($conn, "SELECT * FROM pesanan WHERE id_pesanan = '$id_pesanan'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Order not found.";
    exit;
}

// Cek jika status pesanan di database sudah expired
$status_pesanan = $data['status_pesanan'];

// Hitung sisa waktu pembayaran (30 menit = 1800 detik)
$waktu_pesan = strtotime($data['tanggal_pesanan']);
$batas_waktu = $waktu_pesan + 1800; 
$sisa_detik = $batas_waktu - time();

if ($sisa_detik < 0) {
    $sisa_detik = 0;
    // Jika waktu habis dan status masih unpaid, update otomatis ke expired di database
    if ($status_pesanan == 'unpaid') {
        mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'expired' WHERE id_pesanan = '$id_pesanan'");
        $status_pesanan = 'expired';
    }
}

// ==========================================
// PROSES PEMBATALAN PESANAN OLEH PEMBELI
// ==========================================
if (isset($_POST['batalkan_pesanan'])) {
    $update_cancel = mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'cancel' WHERE id_pesanan = '$id_pesanan' AND id_pengguna = '$id_user'");
    
    if ($update_cancel) {
        echo "<script>alert('Pesanan berhasil dibatalkan.'); window.location='account.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal membatalkan pesanan: " . mysqli_error($conn) . "');</script>";
    }
}

// Ambil URL QR Code Midtrans dari Session (hanya diambil jika belum expired)
$qr_code_url = ($status_pesanan == 'unpaid') ? ($_SESSION['qris_url_' . $id_pesanan] ?? '') : '';

$query_items = mysqli_query($conn, "SELECT dp.*, p.nama_produk, p.foto, p.harga 
                                    FROM detail_pesanan dp 
                                    JOIN produk p ON dp.id_produk = p.id_produk 
                                    WHERE dp.id_pesanan = '$id_pesanan'") 
                                    or die(mysqli_error($conn));

$ongkir = $data['ongkir'];
$total_tagihan = $data['total_harga'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Glads</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-gray-50">

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
            
            <!-- Kolom Kiri: Detail Pesanan & Countdown -->
            <div class="flex-[1.2] p-6 md:p-10 flex flex-col justify-between border-b md:border-b-0 md:border-r border-gray-100">
                <div>
                    <div class="flex justify-between items-start mb-6">
                        <a href="shops.php" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-black">
                            <span>&larr; Back to Shop</span>
                        </a>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-red-500 uppercase tracking-wider">Batas Waktu Bayar</p>
                            <p id="Countdown" class="text-lg font-extrabold text-gray-900">00:00</p>
                        </div>
                    </div>

                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Payment Details</h1>
                    <p class="text-gray-500 text-xs mt-1">Order ID: <span class="text-gray-900 font-semibold">#<?= $data['id_pesanan'] ?></span></p>

                    <!-- List Item -->
                    <div class="space-y-4 my-6 max-h-[220px] overflow-y-auto pr-2">
                        <?php while($item = mysqli_fetch_assoc($query_items)): ?>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-50 rounded-lg overflow-hidden border border-gray-200 shrink-0">
                                <img src="img/<?= !empty($item['foto']) ? $item['foto'] : "default.jpg" ?>" alt="" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xs font-bold uppercase text-gray-800"><?= $item['nama_produk'] ?></h3>
                                <p class="text-[11px] text-gray-500"><?= $item['jumlah'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                            </div>
                            <p class="text-xs font-semibold text-gray-900">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                        </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Rincian Biaya -->
                    <div class="space-y-2 pt-4 border-t border-dashed border-gray-200 text-sm">
                        <div class="flex justify-between text-gray-600 text-xs">
                            <span>Subtotal</span>
                            <span>Rp <?= number_format($total_tagihan - $ongkir, 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600 text-xs">
                            <span>Shipping (JNE)</span>
                            <span>Rp <?= number_format($ongkir, 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-dashed border-gray-200">
                            <span class="text-sm font-bold text-gray-900">Total Payment</span>
                            <span class="text-xl font-extrabold text-black">Rp <?= number_format($total_tagihan, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-blue-900 space-y-1">
                    <p class="font-bold">ℹ️ Informasi:</p>
                    <p class="text-[11px] text-blue-800 leading-relaxed">Scan QRIS di samping menggunakan aplikasi m-Banking atau e-wallet apa saja. Nominal sudah otomatis terkunci.</p>
                </div>
            </div>

            <!-- Kolom Kanan: QRIS Dinamis Midtrans & Status -->
            <div class="flex-1 p-6 md:p-10 flex flex-col items-center justify-center bg-gray-50/50 text-center">
                <div class="w-full max-w-[280px]">

                    
                    <div class="mb-3">
                        <span class="inline-block px-3 py-1 bg-black text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-1">Glads De Lavain</span>
                    </div>

                    
                    <!-- Area QR Code / Tampilan Expired -->
                    <div id="qris-container" class="bg-white p-3 rounded-2xl border border-gray-200 shadow-md inline-block mb-2 w-full">
                        <?php if ($status_pesanan == 'expired'): ?>
                            <div id="expired-box" class="py-10 px-4 text-center">
                                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-xl">✕</div>
                                <p class="text-xs font-bold text-red-600 uppercase">Waktu Pembayaran Habis</p>
                                <p class="text-[11px] text-gray-500 mt-1">Pesanan ini telah kedaluwarsa.</p>
                            </div>
                            <?php elseif (!empty($qr_code_url)): ?>
                                <img id="qris-image" src="<?= $qr_code_url ?>" alt="QRIS Dinamis" class="w-48 h-48 object-contain rounded-xl mx-auto">
                                <?php else: ?>
                                    <p class="text-xs text-red-500 p-8">Gagal memuat QR Code. Silakan ulangi checkout.</p>
                            <?php endif; ?>
                            </div>
                            
                            <!-- TOMBOL ALTERNATIF YANG PASTI RESPONSIF DI HP -->
                            <?php if ($status_pesanan === 'unpaid' && !empty($qr_code_url)) : ?>
                                <div class="mt-2 space-y-2">
                                    <!-- Tombol untuk membuka gambar QRIS secara penuh agar mudah disimpan manual -->
                                    <a href="<?= $qr_code_url ?>" target="_blank" class="block w-full text-center bg-black hover:bg-gray-800 text-white py-2.5 px-4 rounded-xl text-xs font-semibold transition shadow-sm">
                                        Buka QRIS
                                    </a>
                                </div>
                            <?php endif; ?>
                    <div class="mb-2 bg-amber-50 border mt-4 border-amber-200 py-2 px-3 rounded-xl">
                        <p class="text-[11px] text-amber-800 uppercase font-bold">Total Tagihan:</p>
                        <p class="text-base font-extrabold text-amber-950">Rp <?= number_format($total_tagihan, 0, ',', '.') ?></p>
                    </div>
                    

                    <!-- Status Pembayaran -->
                    <div class="space-y-3">
                        <div id="payment-status-box" class="p-3 <?= ($status_pesanan == 'expired') ? 'bg-red-50 border-red-200 text-red-700' : 'bg-amber-50 border-amber-200 text-amber-800' ?> rounded-xl text-xs font-medium flex items-center justify-center gap-2">
                            <?php if ($status_pesanan == 'expired'): ?>
                                <span class="font-bold">Status: Pembayaran Kedaluwarsa</span>
                                <?php else: ?>
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menunggu Pembayaran...
                            <?php endif; ?>
                        </div>

                        <!-- TOMBOL SIMULASI BAYAR BERHASIL (Khusus Testing Sandbox) -->
                        <?php if ($status_pesanan === 'unpaid') : ?>
                            <form action="" method="POST">
                                <button type="submit" name="simulasi_bayar_success" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 px-4 rounded-xl text-xs font-semibold transition cursor-pointer shadow-sm">
                                    Simulasi Bayar Berhasil
                                </button>
                            </form>
                        <?php endif; ?>
                        <!-- TOMBOL BATALKAN PESANAN: Hanya muncul jika statusnya masih unpaid -->
                        <?php if ($status_pesanan === 'unpaid') : ?>
                            <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                <button type="submit" name="batalkan_pesanan" class="w-full bg-red-100 hover:bg-red-200 text-red-700 py-2.5 px-4 rounded-xl text-xs font-semibold transition cursor-pointer shadow-sm">
                                    Batalkan Pesanan
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Script Countdown Timer & Auto Expired UI -->
    <script>
        let sisaDetik = <?= $sisa_detik ?>;
        const display = document.getElementById('Countdown');
        const qrisContainer = document.getElementById('qris-container');
        const statusBox = document.getElementById('payment-status-box');

        function startTimer() {
            const interval = setInterval(function () {
                let minutes = Math.floor(sisaDetik / 60);
                let seconds = sisaDetik % 60;
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                display.textContent = minutes + ":" + seconds;
f
                if (sisaDetik <= 0) {
                    clearInterval(interval);
                    display.textContent = "00:00";
                    display.classList.add('text-red-600');

                    // Ubah tampilan QR Code menjadi kotak Kedaluwarsa secara instan
                    qrisContainer.innerHTML = `
                        <div class="py-10 px-4 text-center">
                            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-xl">✕</div>
                            <p class="text-xs font-bold text-red-600 uppercase">Waktu Pembayaran Habis</p>
                            <p class="text-[11px] text-gray-500 mt-1">Pesanan ini telah kedaluwarsa.</p>
                        </div>
                    `;

                    // Ubah kotak status pembayaran
                    statusBox.className = "p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-bold flex items-center justify-center gap-2";
                    statusBox.innerHTML = "Status: Pembayaran Kedaluwarsa";

                    // Opsional: Lakukan request background sederhana ke server untuk update database jika diperlukan
                    fetch('update_expired.php?id=<?= $id_pesanan ?>');
                } else {
                    sisaDetik--;
                }
            }, 1000);
        }

        if (sisaDetik > 0) {
            startTimer();
        }
    </script>
</body>
</html>