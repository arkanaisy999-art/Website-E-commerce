<?php
session_start();
include "config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

$query = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shops - Glads</title>
    <link href="src/output.css" rel="stylesheet">

    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">
</head>

<body class="flex flex-col min-h-screen">
    
    <nav class="bg-white/10 sticky top-0 z-30 shadow-xs">
        <div class="flex px-6 py-4 justify-between items-center">
            <div class="flex items-center space-x-6">
                <div class="md:hidden">
                    <button id="hamburger" class="text-gray-900"><i class="bx bx-menu text-2xl"></i></button>
                </div>
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

    <div id="mobile-menu" class="hidden md:hidden fixed inset-x-0 bg-white shadow-2xl z-30">
        <div class="p-6 space-y-4">
            <nav class="flex flex-col space-y-1 uppercase">
                <a href="index.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">Home</a>
                <a href="shops.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">Shops</a>
                <a href="about.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">About Us</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 max-w-7xl w-full md:mx-auto py-6 px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php while ($row = mysqli_fetch_assoc($query)) : ?>
                <a href="open_cart.php?id=<?= $row['id_produk'] ?>" class="group block relative">
                    
                    <div class="relative overflow-hidden aspect-square w-full rounded-sm">
                        <img src="img/<?= $row['foto'] ?>"
                            alt="<?= $row['nama_produk'] ?>"
                            class="w-full h-full object-cover">

                        <?php if ($row['stok'] <= 0) : ?>
                            <div class="absolute inset-0 bg-gray-900/10 p-2 md:p-3">
                                <span class="inline-block bg-black text-white text-[9px] md:text-[10px] tracking-wider px-2 py-1 font-medium uppercase rounded-none">
                                    Sold Out
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <h3 class="font-medium text-sm text-gray-900 group-hover:underline group-hover:underline-offset-4">
                            <?= $row['nama_produk'] ?>
                        </h3>
                        <p class="mt-1 text-sm text-gray-800">
                            Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                        </p>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <footer class="bg-gray-100 mt-auto">
        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:pt-24">
            <div class="absolute end-4 top-4 sm:end-6 sm:top-6 lg:end-8 lg:top-8">
                <a class="inline-block rounded-full bg-black p-2 text-white shadow-sm sm:p-3 lg:p-4" href="#">
                    <span class="sr-only">Back to top</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>

            <div class="lg:flex lg:items-end lg:justify-between">
                <div>
                    <div class="flex justify-center text-teal-600 lg:justify-start"></div>
                    <p class="mt-6 text-center text-xl font-extrabold lg:text-right">
                        Glads De Lavain
                    </p>
                </div>

                <ul class="mt-6 flex flex-wrap justify-center gap-6 md:gap-8 lg:mt-0 lg:justify-end lg:gap-12">
                    <li><a class="transition hover:text-gray-700/75" href="#"> Shop </a></li>
                    <li><a class="transition hover:text-gray-700/75" href="#"> About Us </a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        const menuToggle = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = menuToggle.querySelector('i');

        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            if (menuIcon.classList.contains('bx-menu')) {
                menuIcon.classList.replace('bx-menu', 'bx-x');
            } else {
                menuIcon.classList.replace('bx-x', 'bx-menu');
            }
        });
    </script>
</body>

</html>