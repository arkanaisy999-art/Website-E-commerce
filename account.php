<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 PROTEKSI: Jika Admin masuk ke halaman ini, hapus session (Force Logout) dan refresh halaman
if (isset($_SESSION['login']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $_SESSION = [];
    session_destroy();
    header("Location: account.php");
    exit;
}

include "config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (!isset($db) && isset($conn)) {
    $db = $conn;
}

// ==========================================
// 1. PROSES REGISTER
// ==========================================
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $cek_email = mysqli_query($conn, "SELECT * FROM pengguna WHERE email = '$email'");

    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>alert('Email sudah digunakan!'); window.location='account.php';</script>";
        exit;
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO pengguna (username, email, password, role) VALUES ('$username', '$email', '$password_hashed', 'pelanggan')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='account.php';</script>";
            exit;
        } else {
            die("Gagal Register: " . mysqli_error($conn));
        }
    }
}

// ==========================================
// 2. PROSES LOGIN
// ==========================================
if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query  = mysqli_query($conn, "SELECT * FROM pengguna WHERE email = '$email'");
    if (!$query) {
        die("Query Login Gagal: " . mysqli_error($conn));
    }

    $user   = mysqli_fetch_assoc($query);

    if ($user) {
        if (password_verify($password, $user['password']) || $password === $user['password']) {

            $_SESSION['login']    = true;
            $_SESSION['id_user']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                echo "<script>alert('Selamat Datang Admin, " . $user['username'] . "!'); window.location='dash_admin.php';</script>";
            } else {
                echo "<script>alert('Selamat Datang, " . $user['username'] . "!'); window.location='account.php';</script>";
            }
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location='account.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Email tidak terdaftar!'); window.location='account.php';</script>";
        exit;
    }
}

// =========================================================================
// 3. LOGIKA AMBIL DATA PESANAN (BERSIH & AMAN)
// =========================================================================
$username = $_SESSION['username'] ?? 'My Account';
$id_user = $_SESSION['id_user'] ?? null;
$role_user = $_SESSION['role'] ?? '';
$orders = [];
$filter_status = $_GET['status'] ?? '';

// Kunci utama: Halaman ini menganggap user login HANYA jika rolenya pelanggan
$is_pelanggan_logged_in = (isset($_SESSION['login']) && $role_user === 'pelanggan');

if ($is_pelanggan_logged_in && $id_user) {
    $where_clause = "WHERE p.id_pengguna = '$id_user'";
    if (!empty($filter_status)) {
        $where_clause .= " AND p.status_pesanan = '$filter_status'";
    }

    $sql_orders = "SELECT p.*, a.nama_penerima, a.alamat_lengkap, a.kota_kecamatan 
                   FROM pesanan p 
                   LEFT JOIN alamat_pengiriman a ON p.id_alamat = a.id_alamat
                   $where_clause
                   ORDER BY p.tanggal_pesanan DESC";

    $query_orders = mysqli_query($conn, $sql_orders);

    if ($query_orders) {
        while ($row = mysqli_fetch_assoc($query_orders)) {
            $orders[] = $row;
        }
    }
}

$id_user = $_SESSION['id_user'] ?? null;
$orders = [];
$filter_status = $_GET['status'] ?? '';

// Hanya jalankan logika ambil data pesanan jika user sudah login sebagai pelanggan
if ($id_user && ($_SESSION['role'] ?? '') === 'pelanggan') {
    $where_clause = "WHERE p.id_pengguna = '$id_user'";
    if (!empty($filter_status)) {
        $where_clause .= " AND p.status_pesanan = '$filter_status'";
    }

    $query_orders = mysqli_query($conn, "SELECT p.*, a.nama_penerima, a.alamat_lengkap, a.kota_kecamatan 
                                         FROM pesanan p 
                                         LEFT JOIN alamat_pengiriman a ON p.id_alamat = a.id_alamat
                                         $where_clause
                                         ORDER BY p.tanggal_pesanan DESC");

    if ($query_orders) {
        while ($row = mysqli_fetch_assoc($query_orders)) {
            $orders[] = $row;
        }
    }
}

// Logika Filter Status
$filter_status = $_GET['status'] ?? '';
$where_clause = "WHERE p.id_pengguna = '$id_user'";

if (!empty($filter_status)) {
    $where_clause .= " AND p.status_pesanan = '$filter_status'";
}

// Ambil data order milik user yang login dengan filter
$orders = [];
if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    $query_orders = mysqli_query($conn, "SELECT p.*, a.nama_penerima, a.alamat_lengkap, a.kota_kecamatan 
                                         FROM pesanan p 
                                         LEFT JOIN alamat_pengiriman a ON p.id_alamat = a.id_alamat
                                         $where_clause
                                         ORDER BY p.tanggal_pesanan DESC");

    while ($row = mysqli_fetch_assoc($query_orders)) {
        $orders[] = $row;
    }
}

// ==========================================
// PROSES SIMULASI BAYAR (KHUSUS TESTING)
// ==========================================
if (isset($_POST['simulasi_bayar'])) {
    $id_simulasi = mysqli_real_escape_string($conn, $_POST['id_pesanan_simulasi']);
    
    // Ubah status pesanan di database menjadi 'process'
    $update_simulasi = mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'process' WHERE id_pesanan = '$id_simulasi'");
    
    if ($update_simulasi) {
        echo "<script>alert('Simulasi berhasil! Status pesanan #$id_simulasi diubah menjadi process.'); window.location='account.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal mengubah status: " . mysqli_error($conn) . "');</script>";
    }
}

// Cek jika ada kiriman parameter dari logout.php
$show_alert = isset($_GET['status']) && $_GET['status'] === 'logout_success';
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <?php if ($show_alert): ?>
        <div id="logout-alert" class="mb-6 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm transition-all duration-500 shadow-sm opacity-100 uppercase-none">
            <div>
                <span class="font-semibold">Logout Berhasil!</span> Sesi Anda telah diakhiri dengan aman.
            </div>
        </div>
    <?php endif; ?>
    <!-- Navbar -->
    <nav class="bg-white/10 sticky top-0 z-30 shadow-xs">
        <div class="flex px-6 py-4 justify-between items-center">
            <div class="flex items-center space-x-6">
                <a href="index.php">
                    <i class="bx bx-arrow-left-stroke text-2xl"></i>
                </a>
                <h2 class="text-xl font-bold">Glads</h2>
            </div>

            <div class="hidden md:flex space-x-8 items-center">
                <div class="flex space-x-6">
                    <a href="index.php" class="text-gray-800 hover:text-gray-600 transition font-medium">Home</a>
                    <a href="shops.php" class="text-gray-800 hover:text-gray-600 transition font-medium">Shops</a>
                    <a href="about.php" class="text-gray-800 hover:text-gray-600 transition font-medium">About Us</a>
                </div>
            </div>

            <div class="flex space-x-4">
                <div>
                    <a href="cart.php"><i class="bx bx-shopping-bag text-2xl"></i></a>
                </div>
                <div>
                    <a href="account.php"><i class="bx bx-user text-2xl"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Menu Hamburger -->
    <div id="mobile-menu" class="hidden md:hidden fixed inset-x-0 bg-white shadow-2xl z-30">
        <div class="p-6 :p-4 space-y-4">
            <nav class="flex flex-col space-y-1 uppercase">
                <a href="index.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">Home</a>
                <a href="shops.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">Shops</a>
                <a href="about.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">About Us</a>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between px-6 py-6 md:py-4">
            <?php if (!isset($_SESSION['login'])) : ?>
                <!-- Jika belum login, tampilkan tombol ini -->
                <h2 class="text-2xl font-medium"><?= $username ?></h2>
            <?php else : ?>
                <!-- Jika sudah login, tampilkan logout atau menu akun -->
                <h2 class="text-2xl font-medium">Hai <?= $username ?></h2>
                <a href="profile_myInfo.php" class="border px-3 py-1 md:py-2 md:px-4 rounded-xl hover:text-white hover:bg-black">
                    <p>Settings</p>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!isset($_SESSION['login'])) : ?>
            <!-- Jika belum login, tampilkan tombol ini -->
            <div class="bg-gray-100 m-4 pb-6 rounded-xl">
                <div class="py-4 px-4">
                    <p class="text-lg">Log In to Your Account</p>
                    <p class="text-gray-700">Please log in or create a new account to view your order history, track shipping status, and manage your profile information instantly.</p>
                </div>
                <div class="px-4 mt-2 space-x-2">
                    <button onclick="openAuthModal('login')" class="border items-center px-5 py-2 rounded-xl bg-white cursor-pointer hover:bg-black hover:text-white">Login</button>
                    <button onclick="openAuthModal('signup')" class="items-center px-4 py-2 rounded-xl bg-black text-white cursor-pointer">Sign up</button>
                </div>
            </div>

            <div class="bg-gray-100 md:m-4 pb-6 md:rounded-xl">
                <div class="py-4 px-4">
                    <div class="flex justify-between items-center">
                        <p class="text-md font-medium">My Orders (0)</p>
                        <div class="flex items-center gap-2">
                            <label for="status-filter" class="text-md font-medium">Filter Status:</label>
                            <select id="status-filter"
                                onchange="window.location.href='?status=' + this.value"
                                class="text-xs border border-gray-300 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-black bg-white cursor-pointer">
                                <option value="">All Status</option>
                                <option value="unpaid" <?= $filter_status == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                <option value="process" <?= $filter_status == 'process' ? 'selected' : '' ?>>Process</option>
                                <option value="sent" <?= $filter_status == 'sent' ? 'selected' : '' ?>>Sent</option>
                                <option value="done" <?= $filter_status == 'done' ? 'selected' : '' ?>>Done</option>
                                <option value="cancel" <?= $filter_status == 'cancel' ? 'selected' : '' ?>>Cancel</option>
                            </select>
                        </div>
                    </div>
                    <div class="py-8 justify-center text-center">
                        <i class="fa-solid fa-box-open text-5xl text-gray-600"></i>
                        <p class="font-medium mt-4 text-black">No Orders Found</p>
                        <p class="text-sm font-medium text-gray-800">Place an order to see it listed here.</p>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="bg-gray-50 md:m-4 pb-6 md:rounded-xl">
                <div class="py-4 px-4">
                    <div class="flex justify-between items-center">
                        <p class="text-md font-medium">My Orders (<?= count($orders) ?>)</p>
                        <div class="flex items-center gap-2">
                            <label for="status-filter" class="text-md font-medium">Filter Status:</label>
                            <select id="status-filter"
                                onchange="window.location.href='?status=' + this.value"
                                class="text-xs border border-gray-300 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-black bg-white cursor-pointer">
                                <option value="">All Status</option>
                                <option value="unpaid" <?= $filter_status == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                <option value="process" <?= $filter_status == 'process' ? 'selected' : '' ?>>Process</option>
                                <option value="sent" <?= $filter_status == 'sent' ? 'selected' : '' ?>>Sent</option>
                                <option value="done" <?= $filter_status == 'done' ? 'selected' : '' ?>>Done</option>
                                <option value="cancel" <?= $filter_status == 'cancel' ? 'selected' : '' ?>>Cancel</option>
                            </select>
                        </div>
                    </div>
                    <?php if (empty($orders)) : ?>
                        <div class="py-8 justify-center text-center">
                            <i class="fa-solid fa-box-open text-5xl text-gray-600"></i>
                            <p class="font-medium mt-4 text-black">No Orders Found</p>
                            <p class="text-sm font-medium text-gray-700">Place an order to see it listed here.</p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($orders as $order) : ?>
                            <div class="bg-white rounded-xl border border-gray-300 p-4 mt-4">
                                <div class="px-4">
                                    <div class="flex justify-between">
                                        <p>Order #<?= $order['id_pesanan'] ?></p>
                                        <!-- Tampilan teks status tetap seperti semula -->
                                        <p><?= $order['status_pesanan'] ?></p>
                                    </div>
                                    <p class="text-xs text-gray-500"><?= date('M d, Y', strtotime($order['tanggal_pesanan'])) ?></p>
                                </div>

                                <?php
                                $this_id_order = $order['id_pesanan'];
                                $query_items = mysqli_query($conn, "SELECT dp.*, pr.nama_produk, pr.foto 
                                                                    FROM detail_pesanan dp 
                                                                    JOIN produk pr ON dp.id_produk = pr.id_produk 
                                                                    WHERE dp.id_pesanan = '$this_id_order'");
                                $total_items = 0;
                                ?>

                                <?php while ($item = mysqli_fetch_assoc($query_items)) :
                                    $total_items += $item['jumlah'];
                                ?>
                                    <div class="mt-2 p-2 flex items-center">
                                        <div class="w-16 h-16 bg-white rounded-lg p-2">
                                            <img src="img/<?= !empty($item['foto']) ? $item['foto'] : 'default.jpg' ?>" alt="" class="w-full h-full object-contain">
                                        </div>
                                        <div class="w-full flex items-center justify-between">
                                            <div class="ml-2">
                                                <h3 class="font-medium text-xs md:text-sm uppercase"><?= $item['nama_produk'] ?></h3>
                                                <p class="text-xs text-gray-700">Quantity: <?= $item['jumlah'] ?></p>
                                            </div>
                                            <p class="text-sm px-2 font-medium">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>

                                <div class="flex items-center justify-between mb-4 px-4">
                                    <p class="text-sm"></p>
                                    <p class="text-sm font-medium"><span class="text-sm text-gray-500"><?= $total_items ?> items :</span> Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></p>
                                </div>

                                <div>
                                    <div class="rounded-xl bg-gray-50 p-4">
                                        <div class="flex items-center text-sm">
                                            <p>Nomor Resi :</p>
                                            <p class="ml-2"><?= $order['no_resi'] ?? 'Pending' ?></p>
                                        </div>
                                        <p class="text-sm">Shipping Address</p>
                                        <div class="text-gray-500 text-xs space-y-0">
                                            <p>Delivery to <?= $order['nama_penerima'] ?></p>
                                            <p><?= $order['alamat_lengkap'] ?></p>
                                            <p><?= $order['kota_kecamatan'] ?>, Indonesia.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- TOMBOL LANJUTKAN PEMBAYARAN: Hanya muncul jika status pesanan adalah 'unpaid' -->
                                <?php if (strtolower($order['status_pesanan']) === 'unpaid') : ?>
                                    <div class="mt-3 space-y-2">
                                        <!-- Tombol Asli untuk Lanjut Bayar QRIS -->
                                        <a href="order_success.php?id=<?= $order['id_pesanan'] ?>" class="block w-full text-center bg-black text-white py-2.5 rounded-xl text-xs font-semibold hover:bg-gray-800 transition">
                                            Lanjutkan Pembayaran
                                        </a>

                                        <!-- TOMBOL SIMULASI (Khusus Testing Sandbox / Localhost) -->
                                        <form action="" method="POST">
                                            <!-- Input tersembunyi untuk membawa ID pesanan yang sedang diklik -->
                                            <input type="hidden" name="id_pesanan_simulasi" value="<?= $order['id_pesanan'] ?>">
                                            <button type="submit" name="simulasi_bayar" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-xl text-xs font-bold uppercase transition cursor-pointer">
                                                Simulasi Bayar Berhasil (Ubah ke Process)
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Overlay Background -->
    <div id="modal-overlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>

    <!-- Modal Container -->
    <div id="modal-auth" class="fixed z-40 hidden transition-all duration-300 
        /* Mobile: Muncul dari bawah */
        inset-x-0 bottom-0 w-full rounded-lg translate-y-full
        /* Desktop: Muncul di tengah */
        md:inset-0 md:m-auto md:w-[28rem] md:h-fit md:rounded-3xl md:translate-y-0 md:scale-95">

        <div class="bg-white p-8 relative shadow-2xl rounded-2xl md:rounded-3xl">
            <!-- Tombol Close Desktop -->
            <button onclick="closeAuthModal()" class="absolute top-4 right-6 text-2xl">&times;</button>

            <!-- Konten Login -->
            <div id="content-login" class="hidden">
                <h2 class="text-xl font-bold uppercase mb-4">Login</h2>
                <form class="space-y-4" method="POST">
                    <div class="relative">
                        <i class="bx bx-user absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" placeholder="Email" class="w-full p-4 pl-12 border rounded-xl outline-none focus:ring-0 focus:ring-black">
                    </div>
                    <div class="relative">
                        <i class="bx bx-lock absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password" placeholder="Password" class="w-full p-4 pl-12 border rounded-xl outline-none focus:ring-0 focus:ring-black">
                    </div>
                    <button type="submit" name="login" class="w-full bg-black text-white font-bold py-4 rounded-xl transition active:scale-95">Login</button>
                </form>
                <p class="text-center mt-6 text-sm">Don't have account? <button onclick="toggleAuth('signup')" class="font-bold underline">Signup here</button></p>
            </div>

            <!-- Konten Signup -->
            <div id="content-signup" class="hidden">
                <h2 class="text-xl font-bold uppercase mb-4">Register</h2>
                <form class="space-y-4" method="POST">
                    <input type="text" name="username" placeholder="Full Name" class="w-full p-4 border rounded-xl outline-none focus:ring-0 focus:ring-black">
                    <div class="relative">
                        <i class="bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" placeholder="Your Email" class="w-full p-4 pl-12 border rounded-xl outline-none focus:ring-0 focus:ring-black">
                    </div>
                    <div class="relative">
                        <i class="bx bx-lock absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="password" placeholder="Password" class="w-full p-4 pl-12 border rounded-xl focus:ring-0 focus:ring-black">
                    </div>
                    <button type="submit" name="register" class="w-full bg-black text-white font-bold py-4 rounded-xl transition active:scale-95">Create New Account</button>
                </form>
                <p class="text-center mt-6 text-sm">Already have account? <button onclick="toggleAuth('login')" class="font-bold underline">Login here</button></p>
            </div>

        </div>
    </div>

    <!-- Script JS -->
    <script>
        const modalAuth = document.getElementById('modal-auth');
        const modalOverlay = document.getElementById('modal-overlay');
        const contentLogin = document.getElementById('content-login');
        const contentSignup = document.getElementById('content-signup');

        function openAuthModal(type) {
            // Tampilkan overlay dan container
            modalOverlay.classList.remove('hidden');
            modalAuth.classList.remove('hidden');

            // Pilih konten
            toggleAuth(type);

            // Animasi masuk (sedikit delay agar transisi CSS berjalan)
            setTimeout(() => {
                modalAuth.classList.remove('translate-y-full', 'md:scale-95');
                modalAuth.classList.add('translate-y-0', 'md:scale-100');
            }, 10);

            document.body.style.overflow = 'hidden'; // Kunci scroll
        }

        function closeAuthModal() {
            // Animasi keluar
            modalAuth.classList.add('translate-y-full', 'md:scale-95');
            modalAuth.classList.remove('translate-y-0', 'md:scale-100');

            setTimeout(() => {
                modalOverlay.classList.add('hidden');
                modalAuth.classList.add('hidden');
                document.body.style.overflow = 'auto'; // Aktifkan scroll
            }, 300);
        }

        function toggleAuth(type) {
            if (type === 'login') {
                contentLogin.classList.remove('hidden');
                contentSignup.classList.add('hidden');
            } else {
                contentSignup.classList.remove('hidden');
                contentLogin.classList.add('hidden');
            }
        }

        // Tutup jika klik overlay
        modalOverlay.addEventListener('click', closeAuthModal);

        // Jalankan skrip setelah seluruh dokumen selesai dimuat
        document.addEventListener("DOMContentLoaded", function() {
            const alertBanner = document.getElementById('logout-alert');

            // Jika elemen alert ditemukan di halaman
            if (alertBanner) {
                // Atur waktu tunggu (contoh: 4000 milidetik = 4 detik)
                setTimeout(() => {
                    // Berikan efek transisi memudar halus menggunakan Tailwind
                    alertBanner.classList.remove('opacity-100');
                    alertBanner.classList.add('opacity-0', 'scale-95');

                    // Tunggu 500ms (durasi transisi pudar) lalu hilangkan total dari layout
                    setTimeout(() => {
                        alertBanner.remove();

                        // Opsional: Bersihkan parameter '?status=logout_success' di URL browser 
                        // agar alert tidak muncul lagi jika user me-refresh halaman login.
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }, 500);

                }, 4000); // <--- Ubah angka ini (dalam milidetik) jika ingin mengatur durasi tampilnya
            }
        });
    </script>
</body>

</html>