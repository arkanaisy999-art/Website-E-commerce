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

</head>

<body>
    <!-- Navbar -->
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
    <div class="relative mx-auto max-w-7xl min-h-screen px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold">About Us</h2>
        <div class="mt-4 space-y-8">
            <p>
                <strong>Gladz</strong> adalah representasi dari perpaduan antara obsesi terhadap desain yang bersih (clean design) dan gairah tinggi pada detail yang sempurna. Lahir sebagai alternatif cerdas di industri modern, Gladz membawa energi segar yang memancarkan identitas kuat dan eksklusif.
            </p>
            <p>
                Setiap produk dan layanan yang dikembangkan oleh Gladz menggabungkan inovasi kreatif terkini dengan keahlian yang teruji. Melalui komitmen terhadap kualitas terbaik, kami terus bergerak maju untuk memberikan pengalaman yang luar biasa bagi setiap pengguna.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100">
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
                    <div class="flex justify-center text-teal-600 lg:justify-start">
                        <!-- img -->
                    </div>

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

    <!-- Script JS -->
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