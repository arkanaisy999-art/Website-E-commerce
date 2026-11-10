<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 PROTEKSI HALAMAN: Hanya izinkan admin masuk
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika user biasa atau belum login coba-coba masuk, hancurkan session dan lempar ke login
    $_SESSION = [];
    session_destroy();
    header("Location: account.php");
    exit;
}

require "cek_admin.php";

include "config/koneksi.php";

// Ambil data admin yang sedang login untuk keperluan profil dinamis
$id_admin = $_SESSION['id_user'] ?? 0;
$query_admin = mysqli_query($conn, "SELECT username, email, role FROM pengguna WHERE id = '$id_admin'");
$admin_data = mysqli_fetch_assoc($query_admin);

// Fallback jika data di database tidak terambil secara sempurna
$admin_name = $admin_data['username'] ?? 'Admin';
$admin_role = $admin_data['role'] ?? 'Admin';

// LOGIK GENERATOR INISIAL: Mengambil huruf pertama dari kata pertama dan kedua nama admin
$words = explode(" ", $admin_name);
$initials = strtoupper(substr($words[0], 0, 1));
if (count($words) > 1) {
    $initials .= strtoupper(substr($words[1], 0, 1));
}

// 1. HITUNG DATA UNTUK STATS GRID
$query_sales = mysqli_query($conn, "SELECT SUM(total_harga) AS total_omset FROM pesanan WHERE status_pesanan != 'cancel'");
$data_sales = mysqli_fetch_assoc($query_sales);
$total_omset = $data_sales['total_omset'] ?? 0;

$query_pending = mysqli_query($conn, "SELECT COUNT(*) AS total_pending FROM pesanan WHERE status_pesanan IN ('unpaid', 'process')");
$data_pending = mysqli_fetch_assoc($query_pending);
$total_pending = $data_pending['total_pending'] ?? 0;

$query_produk = mysqli_query($conn, "SELECT COUNT(*) AS total_produk FROM produk");
$data_produk = mysqli_fetch_assoc($query_produk);
$total_produk = $data_produk['total_produk'] ?? 0;

// GANTI BAGIAN INI: Menghitung yang role-nya pelanggan saja
$query_user = mysqli_query($conn, "SELECT COUNT(*) AS total_user FROM pengguna WHERE role = 'pelanggan'");
$data_user = mysqli_fetch_assoc($query_user);
$total_user = $data_user['total_user'] ?? 0;

// 2. AMBIL 5 PESANAN TERAKHIR
$query_recent_orders = mysqli_query($conn, "SELECT p.*, g.username, g.email 
                                            FROM pesanan p 
                                            LEFT JOIN pengguna g ON p.id_pengguna = g.id 
                                            ORDER BY p.tanggal_pesanan DESC LIMIT 5");
?>

<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dashboard Admin - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-tertiary-fixed": "#351000",
                        "primary-fixed": "#e2dfff",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a",
                        "inverse-on-surface": "#eaf1ff",
                        "background": "#f8f9ff",
                        "tertiary-fixed-dim": "#ffb695",
                        "surface-variant": "#d3e4fe",
                        "surface-dim": "#cbdbf5",
                        "on-background": "#0b1c30",
                        "on-primary-fixed-variant": "#3323cc",
                        "primary-fixed-dim": "#c3c0ff",
                        "on-error": "#ffffff",
                        "on-primary-container": "#dad7ff",
                        "secondary-fixed-dim": "#89ceff",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-fixed-variant": "#004c6e",
                        "surface-container": "#e5eeff",
                        "inverse-primary": "#c3c0ff",
                        "secondary": "#006591",
                        "on-tertiary": "#ffffff",
                        "primary": "#3525cd",
                        "surface": "#f8f9ff",
                        "surface-container-low": "#eff4ff",
                        "outline": "#777587",
                        "tertiary": "#7e3000",
                        "inverse-surface": "#213145",
                        "on-surface": "#0b1c30",
                        "error-container": "#ffdad6",
                        "tertiary-fixed": "#ffdbcc",
                        "secondary-container": "#39b8fd",
                        "outline-variant": "#c7c4d8",
                        "on-tertiary-container": "#ffd2be",
                        "on-secondary-fixed": "#001e2f",
                        "on-secondary-container": "#004666",
                        "secondary-fixed": "#c9e6ff",
                        "on-secondary": "#ffffff",
                        "tertiary-container": "#a44100",
                        "surface-container-highest": "#d3e4fe",
                        "surface-bright": "#f8f9ff",
                        "surface-container-high": "#dce9ff",
                        "on-tertiary-fixed-variant": "#7b2f00",
                        "on-primary-fixed": "#001e2f",
                        "surface-tint": "#4d44e3",
                        "primary-container": "#4f46e5",
                        "on-primary": "#ffffff",
                        "on-surface-variant": "#464555"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sidebar-width": "280px",
                        "stack-md": "1rem",
                        "container-padding": "2rem",
                        "sidebar-collapsed": "80px",
                        "gutter": "1.5rem",
                        "stack-lg": "1.5rem",
                        "stack-sm": "0.5rem"
                    }
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>

<body class="bg-background text-on-surface">
    <aside class="fixed left-0 top-0 h-full w-sidebar-width hidden md:flex flex-col z-40 shadow-sm bg-surface dark:bg-on-background transition-all duration-200 ease-in-out">
        <div class="flex flex-col h-full py-6">
            <div class="px-6 mb-8">
                <h1 class="text-xl text-primary font-bold">Admin Center</h1>
                <p class="font-body-sm text-body-xs text-sm">Operations Management</p>
            </div>
            <nav class="flex-1 space-y-1">
                <a href="dash_admin.php" class="flex items-center gap-3 px-6 py-3 bg-primary/10 text-primary border-l-4 border-primary transition-all duration-200 ease-in-out">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-body-md text-body-md">Dashboard</span>
                </a>
                <a href="CRUDproduk_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-all duration-200 ease-in-out">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span class="font-body-md text-body-md">CRUD Produk</span>
                </a>
                <a href="pesanan_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-all duration-200 ease-in-out">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span class="font-body-md text-body-md">Pesanan</span>
                </a>
            </nav>
            
            <a href="profile_admin.php" class="mt-auto px-6 pt-6 border-t border-outline-variant/20 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm tracking-wider select-none border-2 border-primary-container shadow-inner">
                    <?= $initials ?>
                </div>
                <div class="overflow-hidden">
                    <p class="font-label-bold text-on-surface truncate font-semibold"><?= htmlspecialchars($admin_name) ?></p>
                    <p class="text-medium text-sm capitalize"><?= htmlspecialchars($admin_role) ?></p>
                </div>
            </a>
        </div>
    </aside>

    <main class="md:ml-sidebar-width min-h-screen">
        <header class="fixed top-0 right-0 w-full md:w-[calc(100%-280px)] h-16 z-30 bg-surface dark:bg-on-background shadow-sm">
            <div class="flex justify-between items-center px-container-padding h-full">
                <div class="flex items-center gap-4">
                    <button class="md:hidden p-2 text-on-surface-variant">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>
                
                <a href="profile_admin.php" class="flex items-center gap-2">
                    <div class="h-8 w-px mx-2"></div>
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs tracking-wider select-none ring-2 ring-primary/20 shadow-sm">
                        <?= $initials ?>
                    </div>
                </a>
            </div>
        </header>

        <div class="pt-24 pb-12 px-container-padding">
            <div class="mb-8">
                <h2 class="font-bold text-3xl">Ringkasan Operasional</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Selamat datang kembali, berikut adalah performa bisnis Anda hari ini.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-gutter mb-8">
                <div class="bg-surface rounded-xl p-6 shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] border border-surface-container flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-3 rounded-lg bg-primary/10 text-primary">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Total Penjualan</p>
                        <p class="text-xl font-medium">Rp <?= number_format($total_omset, 0, ',', '.') ?></p>
                    </div>
                </div>

                <div class="bg-surface rounded-xl p-6 shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] border border-surface-container flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-3 rounded-lg bg-secondary-container/10 text-secondary">
                            <span class="material-symbols-outlined">shopping_basket</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Pesanan Baru</p>
                        <p class="text-xl font-medium"><?= $total_pending ?></p>
                    </div>
                </div>

                <div class="bg-surface rounded-xl p-6 shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] border border-surface-container flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-3 rounded-lg bg-tertiary-fixed/30 text-tertiary">
                            <span class="material-symbols-outlined">inventory</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Total Produk</p>
                        <p class="text-xl font-medium"><?= $total_produk ?></p>
                    </div>
                </div>

                <div class="bg-surface rounded-xl p-6 shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] border border-surface-container flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-3 rounded-lg bg-on-tertiary-fixed-variant/10 text-on-tertiary-fixed-variant">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Total Pelanggan</p>
                        <p class="text-xl font-medium"><?= $total_user ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-surface rounded-xl shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] border border-surface-container overflow-hidden">
                <div class="px-6 py-5 flex justify-between items-center border-b border-surface-container">
                    <div>
                        <h3 class="font-headline-md text-headline-md">Pesanan Terakhir</h3>
                        <p class="font-caption text-caption text-on-surface-variant mt-1">Daftar transaksi terbaru di sistem Anda</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container-low font-label-bold text-label-bold text-on-surface-variant">
                            <tr>
                                <th class="px-6 py-4">ID PESANAN</th>
                                <th class="px-6 py-4">PELANGGAN</th>
                                <th class="px-6 py-4">TANGGAL</th>
                                <th class="px-6 py-4">TOTAL</th>
                                <th class="px-6 py-4">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container">
                            <?php if (mysqli_num_rows($query_recent_orders) == 0) : ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm font-medium text-gray-400 italic">Belum ada data pesanan masuk.</td>
                                </tr>
                            <?php else : ?>
                                <?php while($order = mysqli_fetch_assoc($query_recent_orders)) : ?>
                                <tr class="hover:bg-surface-container-low transition-colors group">
                                    <td class="px-6 py-4 font-body-sm text-body-sm text-primary font-semibold">#<?= $order['id_pesanan'] ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex flex-col">
                                                <span class="font-body-sm text-body-sm font-medium"><?= htmlspecialchars($order['username'] ?? 'Guest') ?></span>
                                                <span class="text-[10px] text-gray-400"><?= htmlspecialchars($order['email'] ?? '-') ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-body-sm text-body-sm"><?= date('d M Y, H:i', strtotime($order['tanggal_pesanan'])) ?> WIB</td>
                                    <td class="px-6 py-4 font-body-sm text-body-sm font-semibold">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full font-label-bold text-[10px] uppercase
                                            <?= $order['status_pesanan'] == 'unpaid' ? 'bg-red-100 text-red-700' : '' ?>
                                            <?= $order['status_pesanan'] == 'process' ? 'bg-amber-100 text-amber-700' : '' ?>
                                            <?= $order['status_pesanan'] == 'sent' ? 'bg-blue-100 text-blue-700' : '' ?>
                                            <?= $order['status_pesanan'] == 'done' ? 'bg-green-100 text-green-700' : '' ?>
                                            <?= $order['status_pesanan'] == 'cancel' ? 'bg-gray-100 text-gray-700' : '' ?>
                                        ">
                                            <?= htmlspecialchars($order['status_pesanan']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <button class="fixed bottom-8 right-8 bg-primary text-on-primary w-14 h-14 rounded-full shadow-lg flex items-center justify-center active:scale-90 transition-transform md:hidden">
        <span class="material-symbols-outlined">add</span>
    </button>
    <script>
        document.querySelector('button.md\\:hidden').addEventListener('click', () => {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('flex');
            sidebar.classList.toggle('w-full');
        });
    </script>
</body>
</html>