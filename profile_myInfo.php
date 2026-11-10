<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 PROTEKSI HALAMAN: Jika belum login atau yang login adalah admin, dilarang masuk
if (!isset($_SESSION['login'])) {
    header("Location: account.php");
    exit;
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $_SESSION = [];
    session_destroy();
    header("Location: account.php");
    exit;
}

include "config/koneksi.php";

// Ambil data pengguna yang sedang login berdasarkan id_user di session
$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($conn, "SELECT * FROM pengguna WHERE id = '$id_user'");

if (!$query_user || mysqli_num_rows($query_user) === 0) {
    die("Data pengguna tidak ditemukan!");
}

$user_data = mysqli_fetch_assoc($query_user);
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="bg-white/10 sticky top-0 z-30 shadow-xs">
        <div class="flex px-6 py-4 justify-between items-center">
            <div class="flex items-center space-x-6">
                <a href="account.php">
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
                    <a href="cart.php"><i class="bx bx-shopping-bag text-2xl" ></i></a>
                </div>
                <div>
                    <a href="account.php"><i class="bx bx-user text-2xl" ></i></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Menu Hamburger -->
    <div id="mobile-menu" class="hidden md:hidden fixed inset-x-0 bg-white shadow-2xl z-30">
        <div class="p-6 :p-4 space-y-4">
            <nav class="flex flex-col space-y-1 uppercase">
                <a href="index.php" class="text-gray-800 hover:text-gray-600 transition font-medium">Home</a>
                <a href="shops.php" class="text-gray-800 hover:text-gray-600 transition font-medium">Shops</a>
                <a href="about.php" class="text-gray-800 hover:text-gray-600 transition font-medium">About Us</a>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-6 sm:mt-12 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl grid grid-cols-1 gap-6 lg:grid-cols-2 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-gray-200 overflow-hidden">
                <h2 class="hidden md:block text-2xl px-4 py-2 font-medium">Settings</h2>
                <ul class="flex md:flex-col border-gray-200 md:space-y-2">
                    <li class="flex-1 md:flex-none">
                        <a href="profile_myInfo.php" class="flex md:mt-3 flex-col md:flex-row items-center justify-center md:justify-start gap-1 md:gap-2 px-4 py-3 md:py-2 bg-black md:bg-gray-100 text-white md:text-gray-700 md:rounded-xl md:mx-4">
                            <i class="bx bx-user text-2xl"></i>
                            <span class="hidden md:text-md font-medium md:block">My Profile Info</span>
                        </a>
                    </li>

                    <li class="flex-1 md:flex-none">
                        <a href="profile_delivery.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-1 md:gap-2 px-4 py-3 md:py-2 text-black hover:bg-gray-100 md:rounded-xl md:mx-4">
                            <i class="bx bx-truck text-2xl"></i>
                            <span class="hidden md:text-md font-medium md:block">Delivery Info</span>
                        </a>
                    </li>

                    <li class="flex-1 md:flex-none">
                        <a href="profile_info.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-1 md:gap-2 px-4 py-3 md:py-2 text-black hover:bg-gray-100 md:rounded-xl md:mx-4">
                            <i class="bx bx-user-square text-2xl"></i>
                            <span class="hidden md:text-md font-medium md:block">Information</span>
                        </a>
                    </li>

                    <li class="flex-1 md:flex-none">
                        <a onclick="konfirmasiLogout()" class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-1 md:gap-2 px-4 py-3 md:py-2 text-black hover:bg-gray-100 md:rounded-xl md:mx-4 cursor-pointer">
                            <i class="bx bx-arrow-out-right-square-half text-2xl"></i>
                            <span class="hidden md:text-md font-medium md:block">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="rounded-xl border py-2 border-gray-200">
                <h3 class="text-2xl px-4 font-medium">My Profile Info</h3>
                <div class="px-2">
                    <div class="ml-4 mr-4 mt-4 px-2 py-1 rounded-xl border-2 border-gray-200">
                        <p class="text-gray-700 text-xs">Name</p>
                        <p><?= htmlspecialchars($user_data['username']) ?></p>
                    </div>
                    <div class="ml-4 mr-4 mt-4 px-2 py-1 rounded-xl border-2 border-gray-200">
                        <p class="text-gray-700 text-xs">Email</p>
                        <p class="text-gray-700"><?= htmlspecialchars($user_data['email']) ?></p>
                    </div>
                    <div class="m-4 px-2 py-1 rounded-xl border-2 border-gray-200">
                        <p class="text-gray-700 text-xs">Password</p>
                        <p class="text-gray-700">••••••••</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function konfirmasiLogout() {
            Swal.fire({
                title: 'Are you sure you want to logout?', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, Logout!', borderRadius: '2rem', customClass: {
                    title: 'judul-kecil'
                }
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'logout.php'; } })
        }
    </script>
</body>
</html>