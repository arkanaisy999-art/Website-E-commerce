<?php
session_start();
include "config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// 1. Ambil ID dari URL
$id = $_GET['id'];

// 2. Cari data produk berdasarkan ID
$result = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id'");
$p = mysqli_fetch_assoc($result);

// Jika ID tidak ditemukan, kembalikan ke index
if (!$p) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="src/output.css" rel="stylesheet">

    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="pb-24 md:pb-0">
    <nav class="bg-white/10 sticky top-0 z-30 shadow-xs">
        <div class="flex px-6 py-4 justify-between items-center">
            <div class="flex items-center space-x-6">
                <a href="shops.php">
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

    <div id="mobile-menu" class="hidden md:hidden fixed inset-x-0 bg-white shadow-2xl z-30">
        <div class="p-6 :p-4 space-y-4">
            <nav class="flex flex-col space-y-1 uppercase">
                <a href="index.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">Home</a>
                <a href="shops.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">Shops</a>
                <a href="about.php" class="p-3 text-sm hover:bg-gray-50 rounded-lg font-medium">About Us</a>
            </nav>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded">
            <div class="container mx-auto flex items-center justify-center h-full">
                <img src="img/<?= $p['foto'] ?>" class="w-full object-cover rounded-lg" alt="<?= $p['nama_produk'] ?>">
            </div>
        </div>

        <div class="py-8 px-4 flex flex-col">
            <div class="rounded">
                <div class="flex inline-flex items-center gap-3 mb-2">
                    <?php if ($p['stok'] > 0): ?>
                        <span class="inline-flex items-center rounded-xs gap-1.5 px-3 py-1.7 text-sm font-medium text-white bg-black">
                            In Stock
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center rounded-xs gap-1.5 px-3 py-1.7 text-sm font-medium text-white bg-black ">
                            Sold Out
                        </span>
                    <?php endif; ?>
                    <p class="inline-flex items-center gap-1.5 rounded-xs px-3 py-1.7 text-sm font-medium text-white bg-black uppercase">
                        <?= !empty($p['kategori']) ? $p['kategori'] : 'Uncategorized'; ?>
                    </p>
                </div>

                <p class="uppercase font-bold text-lg"><?= $p['nama_produk'] ?></p>
                <p class="font-medium text-lg mb-3">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>

            </div>

            <form id="formAddToCart" action="add_to_cart.php" method="POST">
                <input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>">

                <div class="mt-2">
                    <label for="Quantity" class="block text-sm font-medium text-gray-700 mb-2"> Quantity </label>
                    <div class="flex items-center rounded-lg border border-gray-200 w-fit overflow-hidden">
                        <button onclick="changeQty(-1)" type="button" class="w-10 h-10 leading-10 text-gray-600" <?= $p['stok'] < 1 ? 'disabled' : '' ?>>−</button>

                        <input type="number" name="quantity" id="Quantity" value="<?= $p['stok'] > 0 ? 1 : 0 ?>" min="<?= $p['stok'] > 0 ? 1 : 0 ?>" max="<?= $p['stok'] ?>"
                            class="h-10 w-12 border-x border-gray-200 text-center focus:outline-none" <?= $p['stok'] < 1 ? 'readonly' : '' ?> />

                        <button onclick="changeQty(1)" type="button" class="w-10 h-10 leading-10 text-gray-600" <?= $p['stok'] < 1 ? 'disabled' : '' ?>>+</button>
                    </div>
                </div>

                <div class="fixed bottom-0 left-0 w-full p-2 bg-white/10 z-30 md:static md:p-0 md:border-none md:mt-8">
                    <?php if ($p['stok'] > 0): ?>
                        <button type="submit" class="block w-full text-center bg-black text-white border px-5 text-sm py-3 md:py-2 rounded-xl hover:bg-neutral-800 transition">
                            Add to Cart
                        </button>
                    <?php else: ?>
                        <button type="button" class="block w-full text-center bg-gray-300 text-gray-500 border px-5 text-sm py-3 md:py-2 rounded-xl cursor-not-allowed" disabled>
                            Stok Tidak Tersedia
                        </button>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($p['stok'] > 0): ?>
                <a id="directBuyNow" href="#" class="md:block hidden mt-2 w-full text-center bg-black text-white text-sm px-5 py-2 rounded-xl hover:bg-white border hover:text-black transition">
                    Buy It Now
                </a>
            <?php endif; ?>

            <div class="mt-6 bg-white border p-2 rounded-lg space-y-3">
                <h4>Delivery</h4>
                <div class="justify-between flex text-sm">
                    <p>Weight</p>
                    <p><?= $p['berat'] ?>g</p>
                </div>
                <div class="text-gray-600 text-sm">
                    <p>Shipped within 3 days,</p>
                    <p>(Upon confirmation of payment)</p>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-sm">REFUND AKAN BERLAKU JIKA ADA BUKTI VIDEO SAAT UNBOXING DAN JIKA ADA KESALAHAN ATAU BARANG RUSAK DARI KAMI, TERIMA KASIH ATAS PERHATIANNYA</p>
            </div>

            <a class="flex items-center justify-center mt-6 bg-white space-x-2 border group p-2 rounded-xl hover:text-white hover:bg-black transition-all" href="https://wa.me/6282325924829?text=Halo%20saya%20ingin%20bertanya" target="_blank">
                <i class="bx bx-message-circle-dots group-hover:text-white"></i>
                <p class="group-hover:text-white">Message Us</p>
            </a>
        </div>
    </div>

    <div id="cart-modal-overlay" class="fixed inset-0 bg-black/50 z-[40] hidden transition-opacity"></div>

    <div id="cart-modal" class="fixed z-[50] hidden transition-all duration-300 inset-x-0 bottom-0 w-full rounded-xl translate-y-full md:inset-0 md:m-auto md:w-[32rem] md:h-fit md:rounded-3xl md:translate-y-0 md:scale-95">
        <div class="bg-white p-6 relative rounded-2xl">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold uppercase">Product Added to Cart</h2>
                <button onclick="closeCartModal()" class="text-2xl"><i class="bx bx-x"></i></button>
            </div>

            <div class="bg-gray-50 rounded-2xl p-4 flex items-center space-x-4 mb-6">
                <div class="w-20 h-20 bg-white rounded-lg p-2">
                    <img src="img/<?= $p['foto'] ?>" class="w-full object-cover rounded-lg" alt="<?= $p['nama_produk'] ?>">
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-xs uppercase"><?= $p['nama_produk'] ?></h3>
                    <p class="text-xs">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                    <p class="text-xs">1 items</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-2">
                <a href="cart.php" class="w-full text-center hover:bg-black hover:text-white border py-3 rounded-xl text-sm">View Cart</a>
                <a id="buyNowLink" href="checkout.php" class="w-full text-center bg-black text-white py-3 rounded-xl text-sm">Buy It Now</a>
            </div>
        </div>
    </div>

    <script>
        const maxStok = <?= (int)$p['stok'] ?>;

        function updateBuyNowLinks() {
            const qty = document.getElementById('Quantity').value;
            const productId = <?= $p['id_produk'] ?>;
            const url = `checkout.php?buy_now=${productId}&qty=${qty}`;

            // Hanya update jika elemen ada di halaman (stok > 0)
            if (document.getElementById('buyNowLink')) {
                document.getElementById('buyNowLink').href = url;
            }
            if (document.getElementById('directBuyNow')) {
                document.getElementById('directBuyNow').href = url;
            }
        }

        // Inisialisasi saat halaman pertama kali dimuat
        updateBuyNowLinks();

        const cartModal = document.getElementById('cart-modal');
        const cartOverlay = document.getElementById('cart-modal-overlay');
        const formAdd = document.getElementById('formAddToCart');

        // Tangkap kejadian submit form (hanya berjalan jika form disubmit)
        if (formAdd) {
            formAdd.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(formAdd);

                fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        showPopUp();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal menambah ke keranjang');
                    });
            });
        }

        function showPopUp() {
            cartModal.classList.remove('hidden');
            cartOverlay.classList.remove('hidden');

            setTimeout(() => {
                cartModal.classList.remove('translate-y-full');
                cartModal.classList.remove('md:scale-95');
                cartModal.classList.add('md:scale-100');
            }, 10);

            document.body.style.overflow = 'hidden';
        }

        function closeCartModal() {
            cartModal.classList.add('translate-y-full');
            cartModal.classList.add('md:scale-95');
            cartModal.classList.remove('md:scale-100');
            cartOverlay.classList.add('hidden');

            setTimeout(() => {
                cartModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }

        if (cartOverlay) {
            cartOverlay.addEventListener('click', closeCartModal);
        }
    </script>

    <script>
        function changeQty(amount) {
            const qtyInput = document.getElementById('Quantity');
            let currentValue = parseInt(qtyInput.value);

            let newValue = currentValue + amount;

            // Validasi batas minimum dan stok maksimum
            if (newValue < 1 && maxStok > 0) {
                newValue = 1;
            } else if (newValue > maxStok) {
                newValue = maxStok;
            }

            qtyInput.value = newValue;

            updateBuyNowLinks();

            // Update tampilan di Pop-up Modal
            const modalQtyText = document.querySelector('#cart-modal .flex-1 p:last-child');
            if (modalQtyText) {
                modalQtyText.innerText = newValue + ' items';
            }

            const unitPrice = <?= $p['harga'] ?>;
            const modalPriceText = document.querySelector('#cart-modal .flex-1 p:nth-child(2)');
            if (modalPriceText) {
                let totalPrice = unitPrice * newValue;
                modalPriceText.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            }
        }

        // Memastikan semua data update jika user mengetik angka manual di input
        document.getElementById('Quantity').addEventListener('input', function() {
            if (maxStok === 0) {
                this.value = 0;
                return;
            }

            if (this.value === "" || this.value < 1) {
                this.value = 1;
            } else if (parseInt(this.value) > maxStok) {
                this.value = maxStok;
            }

            const newValue = parseInt(this.value);

            updateBuyNowLinks();

            const modalQtyText = document.querySelector('#cart-modal .flex-1 p:last-child');
            if (modalQtyText) {
                modalQtyText.innerText = newValue + ' items';
            }

            const unitPrice = <?= $p['harga'] ?>;
            const modalPriceText = document.querySelector('#cart-modal .flex-1 p:nth-child(2)');
            if (modalPriceText) {
                let totalPrice = unitPrice * newValue;
                modalPriceText.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            }
        });
    </script>
</body>

</html>