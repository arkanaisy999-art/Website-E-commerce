<?php
session_start();
include "config/koneksi.php";

$is_logged_in = isset($_SESSION['id_user']);

// JIKA BELUM LOGIN, TAMPILKAN PERINGATAN DAN STOP PHP DI SINI
if (!$is_logged_in) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>body { font-family: sans-serif; background: #f9f9f9; }</style>
    </head>
    <body>
        <script>
            window.onload = function() {
                Swal.fire({
                    title: "Login Required",
                    text: "Please login to your account to continue checkout.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#000",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Login Now",
                    cancelButtonText: "Home",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "account.php";
                    } else {
                        window.location.href = "open_cart.php";
                    }
                });
            };
        </script>
    </body>
    </html>';
    exit; // MENGHENTIKAN SEMUA PROSES PHP DI BAWAHNYA
}

// ... kode session dan koneksi tetap sama ...

$id_user = $_SESSION['id_user'];

// AMBIL ALAMAT DEFAULT USER
$query_alamat = mysqli_query($conn, "SELECT * FROM alamat_pengiriman WHERE id_pengguna = '$id_user' AND is_default = 1 LIMIT 1");
$data_alamat = mysqli_fetch_assoc($query_alamat);

// Jika tidak ada default, ambil alamat terbaru sebagai cadangan
if (!$data_alamat) {
    $query_alamat = mysqli_query($conn, "SELECT * FROM alamat_pengiriman WHERE id_pengguna = '$id_user' ORDER BY id_alamat DESC LIMIT 1");
    $data_alamat = mysqli_fetch_assoc($query_alamat);
}

// ... sisa kode pengambilan produk tetap sama ...

// --- KODE DI BAWAH INI HANYA AKAN JALAN JIKA USER SUDAH LOGIN ---
$id_user = $_SESSION['id_user'];
$items = [];
$subtotal = 0;
$total_berat = 0;

// Logika pengambilan data produk (Normal Checkout)
if (isset($_GET['buy_now'])) {
    $id_p = $_GET['buy_now'];
    $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    $query = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id_p'");
    $row = mysqli_fetch_assoc($query);
    if ($row) {
        $row['qty'] = $qty;
        $items[] = $row;
        $subtotal = $row['harga'] * $qty;
        $total_berat = $row['berat'] * $qty;
    }
} else {
    $query_cart = mysqli_query($conn, "SELECT k.*, p.nama_produk, p.harga, p.foto, p.berat 
                                       FROM keranjang k 
                                       JOIN produk p ON k.id_produk = p.id_produk 
                                       WHERE k.id_pengguna = '$id_user'");
    while ($row = mysqli_fetch_assoc($query_cart)) {
        $items[] = $row;
        $subtotal += ($row['harga'] * $row['qty']);
        $total_berat += ($row['berat'] * $row['qty']);
    }
}

// Jika keranjang kosong
if (empty($items)) {
    header("Location: shops.php");
    exit;
}

$ongkir = 19000;
$total_bayar = $subtotal + $ongkir;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="src/output.css" rel="stylesheet">

    <!-- Basic Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <!-- Filled Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <!-- Brand Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
</head>
<body>
    <!-- Navbar -->
    <nav class="bg-white/10 sticky top-0 z-30 shadow-sm">
        <div class="flex px-6 py-4 justify-between items-center">
            <div class="flex items-center space-x-6">
                <a href="shops.php">
                    <i class="bx bx-arrow-left-stroke text-2xl"></i>
                </a>
                <h2 class="text-xl font-bold">Glads</h2>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="mt-2 md:mt-6 md:px-8 px-4">
        <div class="grid grid-cols-1 gap-6 md:gap-8 lg:grid-cols-2">
            <div class="overflow-hidden space-y-8 md:space-y-8">
                <div>
                    <h2 class="text-xl md:py-2 font-bold">Address Details</h2>    
                    <div class="mt-2 px-4 py-2 rounded-xl border border-gray-200">
                        <div class="flex items-center justify-between">
                            <p class="text-md"><?= $data_alamat ? $data_alamat['nama_penerima'] : '<span class="text-red-500">No Address Found</span>' ?></p>
                            <a href="profile_delivery.php" class="text-md underline text-gray-500">change</a>
                        </div>
                            
                        <?php if ($data_alamat) : ?>
                            <div class="flex items-center gap-2">
                                <p class="text-sm text-gray-500"><?= $data_alamat['telepon'] ?></p>
                                <span class="text-gray-400 text-xs flex items-center">&bull;</span>
                                <p class="text-sm text-gray-500"><?= $data_alamat['email_penerima'] ?? '' ?></p>
                            </div>
                            <p class="text-gray-500 text-sm"><?= $data_alamat['alamat_lengkap'] ?></p>
                            <p class="text-gray-500 text-sm">
                                <?= $data_alamat['kota_kecamatan'] ?>, <?= $data_alamat['negara'] ?>
                            </p>
                        <?php else : ?>
                            <p class="text-gray-500 text-sm italic">Please add a delivery address first.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl md:py-2 font-bold">Shipment Method</h2>    
                    <div class="flex items-center justify-between mt-2 px-2 rounded-xl border border-gray-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-20 h-20 flex items-center">
                                <img src="img/jne.png" alt="">
                            </div>
                            <p class="text-gray-500 text-sm items-center">JNE</p>
                        </div>
                        <p class="font-medium text-sm px-2">Rp <?= number_format($ongkir, 0, ',', '.') ?></p>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl md:py-2 font-bold">Payment Method</h2>    
                    <div class="flex items-center justify-between mt-2 px-4 rounded-xl border border-gray-200">
                        <div class="w-20 h-20  flex items-center">
                            <img src="img/qris.png" alt="">
                        </div>
                        <p class="text-gray-500 text-sm items-center">Qris</p>
                    </div>
                </div>
            </div>

            <div class="py-2">
                <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                
                <?php foreach ($items as $item) : ?>
                <div class="mt-2 p-2 flex items-center mb-4 border-gray-200 border rounded-xl">
                    <div class="w-20 h-20 bg-white rounded-lg p-2">
                        <img src="img/<?= $item['foto'] ?>" alt="<?= $item['nama_produk'] ?>" class="w-full h-full object-contain">
                    </div>
                    <div class="w-full flex items-center justify-between">
                        <div class="w-1/2 ml-2">
                            <h3 class="font-semibold text-xs md:text-sm uppercase"><?= $item['nama_produk'] ?></h3>
                            <p class="text-xs text-gray-700">Quantity: <?= $item['qty'] ?></p>
                        </div>
                        <p class="text-sm px-2 font-medium">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="md:px-12 space-y-4">
                    <div class="text-sm flex justify-between">
                        <p>Subtotal : <?= count($items) ?> items</p>
                        <p>Rp <?= number_format($subtotal, 0, ',', '.') ?></p>
                    </div>
                    <div class="text-sm flex justify-between">
                        <p>Shipping : <?= $total_berat / 1000 ?> kg</p>
                        <p>Rp <?= number_format($ongkir, 0, ',', '.') ?></p>
                    </div>
                    <div class="border border-gray-200"></div>
                    <div class="flex justify-between">
                        <p class="font-medium">Total Payment</p>
                        <p class="font-bold text-lg">Rp <?= number_format($total_bayar, 0, ',', '.') ?></p>
                    </div>

                    <form action="proses_checkout.php" method="POST">
                        <input type="hidden" name="total_bayar" value="<?= $total_bayar ?>">
                        <input type="hidden" name="ongkir" value="<?= $ongkir ?>">

                        <input type="hidden" name="id_alamat" value="<?= $data_alamat ? $data_alamat['id_alamat'] : '' ?>">
                        
                        <?php if (isset($_GET['buy_now'])): ?>
                            <input type="hidden" name="is_buy_now" value="1">
                            <input type="hidden" name="id_produk" value="<?= $items[0]['id_produk'] ?>">
                            <input type="hidden" name="qty" value="<?= $items[0]['qty'] ?>">
                            <input type="hidden" name="harga" value="<?= $items[0]['harga'] ?>">
                        <?php endif; ?>

                        <button type="submit" class="block w-full bg-black text-white py-2 rounded-xl">Order Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>