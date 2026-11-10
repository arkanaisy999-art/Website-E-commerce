<?php
session_start();
include "config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (!isset($db) && isset($conn)) { $db = $conn; }

if (!isset($_SESSION['id_user'])) { 
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Info - Glads</title>
    <link href="src/output.css" rel="stylesheet">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
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

    <div class="mt-6 sm:mt-12 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl grid grid-cols-1 gap-6 lg:grid-cols-2 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-gray-200 overflow-hidden">
                <h2 class="hidden md:block text-2xl px-4 py-2 font-medium">Settings</h2>
                <ul class="flex md:flex-col border-gray-200 md:space-y-2">
                    <li class="flex-1 md:flex-none">
                        <a href="profile_myInfo.php" class="flex md:mt-3 flex-col md:flex-row items-center justify-center md:justify-start gap-1 md:gap-2 px-4 py-3 md:py-2 text-black hover:bg-gray-100 md:rounded-xl md:mx-4">
                            <i class="bx bx-user text-2xl"></i>
                            <span class="hidden md:text-md font-medium md:block">My Profile Info</span>
                        </a>
                    </li>

                    <li class="flex-1 md:flex-none">
                        <a href="profile_delivery.php" class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-1 md:gap-2 px-4 py-3 md:py-2 md:bg-gray-100 text-white bg-black md:text-gray-700 md:rounded-xl md:mx-4">
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


            <div class="rounded-xl border py-4 border-gray-200">
                <h3 class="text-2xl px-4 font-medium">Buyer - Delivery Info</h3>

                <div class="px-4 mt-4 space-y-4">
                    <div id="add-new-btn-container">
                        <button onclick="showDeliveryForm()" class="flex items-center gap-2 text-sm font-medium px-2">
                            <i class="bx bx-plus"></i><span>Add New</span>
                        </button>
                    </div>

                    <div id="address-list" class="space-y-4">
                        <?php 
                        $query_semua = mysqli_query($conn, "SELECT * FROM alamat_pengiriman WHERE id_pengguna = '$id_user' ORDER BY is_default DESC");
                        if (mysqli_num_rows($query_semua) > 0) : 
                            while ($row = mysqli_fetch_assoc($query_semua)) :
                        ?>
                            <div class="px-4 py-2 bg-gray-100/10 border border-gray-300 rounded-xl">
                                <div class="flex justify-between items-center mb-2">
                                    <p class="font-bold"><?= $row['nama_penerima'] ?></p>
                                    <?php if ($row['is_default']) : ?>
                                        <span class="text-xs text-gray-500 font-medium">Default</span>
                                    <?php else : ?>
                                        <a href="set_default_delivery.php?id=<?= $row['id_alamat'] ?>" class="text-xs font-medium text-gray-500 hover:text-gray-600 underline">Set as Default</a>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm text-gray-600"><?= $row['telepon'] ?></p>
                                    
                                    <span class="text-gray-400 text-[10px] flex items-center">&bull;</span>
                                    
                                    <p class="text-sm text-gray-600"><?= $row['email_penerima'] ?? '' ?></p>
                                </div>
                                <p class="text-sm text-gray-600 mt-1"><?= $row['alamat_lengkap'] ?></p>
                                <p class="text-sm text-gray-600"><?= $row['kota_kecamatan'] ?>, <?= $row['negara'] ?></p>
                                
                                <div class="flex space-x-4 mt-2">
                                    <button onclick='editAddress(<?= json_encode($row) ?>)' class="text-sm font-medium underline">Edit</button>
                                    <button onclick="confirmDelete(<?= $row['id_alamat'] ?>)" class="text-sm font-medium text-red-600 underline">Delete</button>
                                </div>
                            </div>
                        <?php endwhile; else : ?>
                            <p class="text-gray-700 text-sm">No address found.</p>
                        <?php endif; ?>
                    </div>

                    <form id="delivery-form" method="POST" action="save_delivery.php" class="hidden pt-4 space-y-4">
                        <h3 id="form-title" class="text-lg font-bold">Add New Address</h3>
                        <input type="hidden" name="id_alamat" id="form-id-alamat">

                        <div class="space-y-4">
                            <input type="text" name="nama_penerima" id="form-nama" placeholder="Recipient Full Name" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-black" required>
                            <input type="email" name="email_penerima" id="form-email" placeholder="Recipient Email" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-black">
                            <input type="tel" name="telepon" id="form-telp" placeholder="Recipient Phone Number" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-black" required>
                            
                            <select name="negara" id="form-negara" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:outline-none">
                                <option value="Indonesia">Indonesia</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Singapore">Singapore</option>
                            </select>

                            <input type="text" name="kota_kecamatan" id="form-kota" placeholder="Sub-district, District, City" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-black">
                            <textarea name="alamat_lengkap" id="form-alamat" placeholder="Address Details" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-black"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" onclick="hideDeliveryForm()" class="w-full py-2 border border-gray-200 rounded-xl font-medium">Cancel</button>
                            <button type="submit" name="submit_address" class="w-full py-2 bg-black text-white rounded-xl font-medium">Save Address</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDeliveryForm() {
            // Sembunyikan daftar alamat dan tombol Add New
            document.getElementById('address-list').classList.add('hidden');
            document.getElementById('add-new-btn-container').classList.add('hidden');
            
            // Siapkan form
            document.getElementById('form-id-alamat').value = "";
            document.getElementById('delivery-form').reset();
            document.getElementById('form-title').innerText = "Add New Address";
            document.getElementById('delivery-form').classList.remove('hidden');
        }

        function hideDeliveryForm() {
            // Tampilkan kembali daftar alamat
            document.getElementById('address-list').classList.remove('hidden');
            document.getElementById('add-new-btn-container').classList.remove('hidden');
            document.getElementById('delivery-form').classList.add('hidden');
        }

        function editAddress(data) {
            // Sembunyikan daftar alamat
            document.getElementById('address-list').classList.add('hidden');
            document.getElementById('add-new-btn-container').classList.add('hidden');

            // Isi form dengan data yang akan diedit
            document.getElementById('form-title').innerText = "Edit Address";
            document.getElementById('form-id-alamat').value = data.id_alamat;
            document.getElementById('form-nama').value = data.nama_penerima;
            document.getElementById('form-email').value = data.email_penerima || "";
            document.getElementById('form-telp').value = data.telepon;
            document.getElementById('form-negara').value = data.negara;
            document.getElementById('form-kota').value = data.kota_kecamatan;
            document.getElementById('form-alamat').value = data.alamat_lengkap;
            
            document.getElementById('delivery-form').classList.remove('hidden');
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Delete this address?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#000',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_delivery.php?id=${id}`;
                }
            })
        }

        function konfirmasiLogout() {
            Swal.fire({
                title: 'Are you sure you want to logout?', 
                showCancelButton: true, 
                confirmButtonColor: '#ef4444', 
                confirmButtonText: 'Yes, Logout!'
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'logout.php'; } })
        }
    </script>
</body>
</html>