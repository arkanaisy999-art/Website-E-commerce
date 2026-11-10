<?php
session_start();
include "config/koneksi.php";

// Hapus pengecekan login yang lama (header location login.php)
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

// --- LOGIKA HAPUS ---
if (isset($_GET['remove'])) {
    $id_remove = $_GET['remove'];
    if ($id_user) {
        // Hapus dari DB jika sudah login
        mysqli_query($conn, "DELETE FROM keranjang WHERE id_keranjang = '$id_remove' AND id_pengguna = '$id_user'");
    } else {
        // Hapus dari Session jika belum login
        if (isset($_SESSION['guest_cart'][$id_remove])) {
            unset($_SESSION['guest_cart'][$id_remove]);
        }
    }
    header("Location: cart.php");
    exit;
}

// --- LOGIKA UPDATE QTY ---
if (isset($_GET['action']) && isset($_GET['id_k'])) {
    $id_target = $_GET['id_k'];
    $action = $_GET['action'];

    if ($id_user) {
        // Update di DB
        if ($action == 'add') {
            mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE id_keranjang = '$id_target' AND id_pengguna = '$id_user'");
        } elseif ($action == 'reduce') {
            mysqli_query($conn, "UPDATE keranjang SET qty = qty - 1 WHERE id_keranjang = '$id_target' AND id_pengguna = '$id_user' AND qty > 1");
        }
    } else {
        // Update di Session
        if (isset($_SESSION['guest_cart'][$id_target])) {
            if ($action == 'add') {
                $_SESSION['guest_cart'][$id_target]['qty'] += 1;
            } elseif ($action == 'reduce' && $_SESSION['guest_cart'][$id_target]['qty'] > 1) {
                $_SESSION['guest_cart'][$id_target]['qty'] -= 1;
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// --- AMBIL DATA KERANJANG ---
$keranjang_data = [];
$total_belanja = 0;
$total_items = 0;

if ($id_user) {
    // Jika Login: Ambil dari DB
    $query = mysqli_query($conn, "SELECT k.id_keranjang as id, k.qty, p.nama_produk, p.harga, p.foto 
                                  FROM keranjang k 
                                  JOIN produk p ON k.id_produk = p.id_produk 
                                  WHERE k.id_pengguna = '$id_user'");
    while($item = mysqli_fetch_assoc($query)) {
        $keranjang_data[] = $item;
    }
} else {
    // Jika Belum Login: Ambil dari Session
    if (isset($_SESSION['guest_cart'])) {
        foreach ($_SESSION['guest_cart'] as $key => $val) {
            // Ambil detail produk dari DB berdasarkan ID produk di session
            $p_query = mysqli_query($conn, "SELECT id_produk, nama_produk, harga, foto FROM produk WHERE id_produk = '$key'");
            $p = mysqli_fetch_assoc($p_query);
            if ($p) {
                $keranjang_data[] = [
                    'id' => $key, // Menggunakan id_produk sebagai identifier di guest mode
                    'nama_produk' => $p['nama_produk'],
                    'harga' => $p['harga'],
                    'foto' => $p['foto'],
                    'qty' => $val['qty']
                ];
            }
        }
    }
}

// Hitung Total
foreach ($keranjang_data as $item) {
    $total_belanja += ($item['harga'] * $item['qty']);
    $total_items += $item['qty'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Glads</title>
    <link href="src/output.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
</head>
<body class="pb-24 md:pb-0">
    <nav class="bg-white/10 sticky top-0 z-30 shadow-sm">
        <div class="flex px-6 py-4 justify-between items-center">
            <div class="flex items-center space-x-6">
                <a href="index.php">
                    <i class="bx bx-arrow-left-stroke text-2xl"></i>
                </a>
                <h2 class="text-xl font-bold">Glads</h2>
            </div>
        </div>
    </nav>

    <div class="px-6 max-w-7xl mx-auto md:px-8">
        <h1 class="text-3xl font-bold py-4 mb-4 text-center">Cart</h1>

        <?php if (empty($keranjang_data)) : ?>
            <div class="text-center py-20">
                <p class="text-2xl">Your cart is empty.</p>
                <a href="shops.php" class="mt-4 inline-block text-white px-4 py-3 bg-black rounded-xl font-bold">Continue Shopping</a>
            </div>
        <?php else : ?>
            <?php foreach ($keranjang_data as $item) : 
                $subtotal = $item['harga'] * $item['qty'];
            ?>
                <div class="flex space-x-3 mt-4">
                    <div class="bg-gray-100 rounded-xl overflow-hidden shrink-0">
                        <img src="img/<?= $item['foto'] ?>" class="w-24 h-24 md:w-32 md:h-32 object-cover">
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <p class="font-bold text-sm uppercase"><?= $item['nama_produk'] ?></p>
                        </div>
                        <p class="text-sm mt-1">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                        
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-xs font-medium text-gray-700">Qty: <?= $item['qty'] ?></p>
                            <p class="font-medium">Rp <?= number_format($subtotal, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>


                <div class="flex items-center justify-between mb-8 mt-2">
                    <!-- Gunakan $item['id'] -->
                    <a href="cart.php?remove=<?= $item['id'] ?>" class="underline cursor-pointer font-medium text-sm">
                        Remove
                    </a>

                    <div class="flex items-center rounded-lg border border-gray-200 w-fit overflow-hidden">
                        <a href="cart.php?action=reduce&id_k=<?= $item['id'] ?>" 
                           class="w-10 h-10 flex items-center justify-center text-gray-600 transition hover:bg-gray-100 border-r border-gray-200">
                            −
                        </a>

                        <div class="h-10 w-12 flex items-center justify-center bg-white text-sm font-medium">
                            <?= $item['qty'] ?>
                        </div>

                        <a href="cart.php?action=add&id_k=<?= $item['id'] ?>" 
                           class="w-10 h-10 flex items-center justify-center text-gray-600 transition hover:bg-gray-100 border-l border-gray-200">
                            +
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="fixed bottom-0 left-0 w-full bg-gray-100 p-4 z-50">
                <div class="mx-auto max-w-7xl">
                    <div class="flex items-center justify-between mb-4">
                        <p class="font-medium">Total Price (<?= $total_items ?> items)</p>
                        <p class="text-xl font-medium text-black">Rp <?= number_format($total_belanja, 0, ',', '.') ?></p>
                    </div>

                    <a href="checkout.php" class="block font-medium w-full md:w-fit md:px-12 text-center bg-black text-white py-3 rounded-xl shadow-lg ml-auto">
                        Checkout
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>