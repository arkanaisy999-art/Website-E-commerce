<?php
session_start();
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

// ==========================================
// PROSES UPDATE STATUS PESANAN
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status_pesanan'])) {
    $id_pesanan = intval($_GET['id']);
    $status_baru = mysqli_real_escape_string($conn, $_GET['status_pesanan']);

    // Validasi nilai status yang diperbolehkan sesuai ENUM database
    $allowed_status = ['unpaid', 'process', 'sent', 'done', 'cancel'];
    if (in_array($status_baru, $allowed_status)) {
        $update_query = mysqli_query($conn, "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = $id_pesanan");
        if ($update_query) {
            echo "<script>alert('Status pesanan berhasil diperbarui!'); window.location='pesanan_admin.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui status pesanan!');</script>";
        }
    }
}

// ==========================================
// HITUNG DATA STATISTIK (BENTO CARD)
// ==========================================
$stat_unpaid  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'unpaid'"))['total'];
$stat_proses  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'process'"))['total'];
$stat_dikirim = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'sent'"))['total'];
$stat_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'done'"))['total'];
$stat_batal   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'cancel'"))['total'];

// ==========================================
// AMBIL DATA TRANSAKSI UTAMA
// ==========================================
$order_query = mysqli_query($conn, "SELECT p.*, pl.username AS nama_pelanggan FROM pesanan p 
                                    LEFT JOIN pengguna pl ON p.id_pengguna = pl.id 
                                    ORDER BY p.tanggal_pesanan DESC, p.id_pesanan DESC");

?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pesanan Admin - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
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
                        "on-primary-fixed": "#0f0069",
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
                    },
                    "fontFamily": {
                        "headline-md": ["Inter"],
                        "display-lg-mobile": ["Inter"],
                        "body-md": ["Inter"],
                        "body-sm": ["Inter"],
                        "display-lg": ["Inter"],
                        "label-bold": ["Inter"],
                        "caption": ["Inter"]
                    },
                    "fontSize": {
                        "headline-md": ["20px", {
                            "lineHeight": "28px",
                            "letterSpacing": "-0.01em",
                            "fontWeight": "600"
                        }],
                        "display-lg-mobile": ["24px", {
                            "lineHeight": "32px",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "700"
                        }],
                        "body-md": ["16px", {
                            "lineHeight": "24px",
                            "letterSpacing": "0",
                            "fontWeight": "400"
                        }],
                        "body-sm": ["14px", {
                            "lineHeight": "20px",
                            "letterSpacing": "0",
                            "fontWeight": "400"
                        }],
                        "display-lg": ["32px", {
                            "lineHeight": "40px",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "700"
                        }],
                        "label-bold": ["12px", {
                            "lineHeight": "16px",
                            "letterSpacing": "0.05em",
                            "fontWeight": "600"
                        }],
                        "caption": ["12px", {
                            "lineHeight": "16px",
                            "letterSpacing": "0",
                            "fontWeight": "400"
                        }]
                    }
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9ff;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d3e4fe;
            border-radius: 10px;
        }
    </style>
</head>

<body class="text-on-surface">
    <aside class="fixed left-0 top-0 h-full w-sidebar-width hidden md:flex flex-col z-40 shadow-sm bg-surface dark:bg-on-background transition-all duration-200 ease-in-out">
        <div class="flex flex-col h-full py-6">
            <div class="px-6 mb-8">
                <h1 class="text-xl text-primary font-bold">Admin Center</h1>
                <p class="font-body-sm text-body-xs text-sm">Operations Management</p>
            </div>
            <nav class="flex-1 space-y-1">
                <a href="dash_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-all duration-200 ease-in-out">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-body-md text-body-md">Dashboard</span>
                </a>
                <a href="CRUDproduk_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-all duration-200 ease-in-out">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span class="font-body-md text-body-md">CRUD Produk</span>
                </a>
                <a href="pesanan_admin.php" class="flex items-center gap-3 px-6 py-3 bg-primary/10 text-primary border-l-4 border-primary font-bold transition-all duration-200 ease-in-out">
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

    <main class="md:ml-sidebar-width pt-16 min-h-screen">
        <div class="p-container-padding">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h2 class="font-display-lg text-display-lg text-on-surface mb-1">Manajemen Pesanan</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">Pantau dan kelola status transaksi pelanggan Anda.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-gutter mb-8 lg:grid-cols-5">
                <div class="bg-surface rounded-xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">credit_card</span>
                    </div>
                    <div>
                        <p class="font-caption text-caption text-on-surface-variant">Unpaid</p>
                        <p class="font-headline-md text-headline-md text-on-surface"><?= $stat_unpaid; ?></p>
                    </div>
                </div>
                <div class="bg-surface rounded-xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">refresh</span>
                    </div>
                    <div>
                        <p class="font-caption text-caption text-on-surface-variant">Process</p>
                        <p class="font-headline-md text-headline-md text-on-surface"><?= $stat_proses; ?></p>
                    </div>
                </div>
                <div class="bg-surface rounded-xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">local_shipping</span>
                    </div>
                    <div>
                        <p class="font-caption text-caption text-on-surface-variant">Sent</p>
                        <p class="font-headline-md text-headline-md text-on-surface"><?= $stat_dikirim; ?></p>
                    </div>
                </div>
                <div class="bg-surface rounded-xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <div>
                        <p class="font-caption text-caption text-on-surface-variant">Done</p>
                        <p class="font-headline-md text-headline-md text-on-surface"><?= $stat_selesai; ?></p>
                    </div>
                </div>
                <div class="bg-surface rounded-xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">cancel</span>
                    </div>
                    <div>
                        <p class="font-caption text-caption text-on-surface-variant">Cancel</p>
                        <p class="font-headline-md text-headline-md text-on-surface"><?= $stat_batal; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-surface rounded-xl shadow-sm border border-slate-100 pb-32">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-label-bold text-label-bold text-lg uppercase">Daftar Transaksi Terbaru</h3>
                </div>

                <div class="overflow-x-auto pb-28">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Order ID</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Pelanggan</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Tanggal</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase text-center">Total</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase text-center">Status</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (mysqli_num_rows($order_query) == 0): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant font-body-md">Belum ada data transaksi masuk.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($order_query)): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="px-6 py-5 font-body-sm text-body-sm text-primary font-bold">#<?= htmlspecialchars($row['id_pesanan']); ?></td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div>
                                                    <p class="font-body-md text-body-md text-on-surface"><?= htmlspecialchars($row['nama_pelanggan'] ?? 'Guest / Anonim'); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 font-body-sm text-body-sm text-on-surface-variant">
                                            <?= date('d M Y, H:i', strtotime($row['tanggal_pesanan'])); ?>
                                        </td>
                                        <td class="px-6 py-5 font-body-md text-body-md text-on-surface text-center font-semibold">
                                            Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <?php
                                            $status = strtolower($row['status_pesanan']);
                                            if ($status == 'unpaid') {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">unpaid</span>';
                                            } elseif ($status == 'process') {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">process</span>';
                                            } elseif ($status == 'sent') {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">sent</span>';
                                            } elseif ($status == 'done') {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">done</span>';
                                            } else {
                                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">cancel</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <!-- Bagian Tombol Aksi di dalam <tbody> -->
                                            <div class="relative inline-block text-left" id="dropdown-<?= $row['id_pesanan']; ?>">
                                                <button class="p-2 hover:bg-slate-200 rounded-full transition-colors" onclick="toggleDropdown('dropdown-menu-<?= $row['id_pesanan']; ?>', this)">
                                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                                </button>

                                                <!-- PERBAIKAN: Menggunakan right-6 (geser kiri) dan mt-1 agar pas di bawah tombol tanpa terpotong atas/samping -->
                                                <div class="hidden absolute right-6 top-full mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-xl z-[999] py-2" id="dropdown-menu-<?= $row['id_pesanan']; ?>">

                                                    <button onclick="openModalDetail(<?= $row['id_pesanan']; ?>)" class="w-full text-left px-4 py-2 text-body-sm hover:bg-slate-50 text-on-surface flex items-center gap-2 font-medium">
                                                        <span class="material-symbols-outlined text-[18px]">visibility</span> Detail Pesanan
                                                    </button>

                                                    <hr class="my-1 border-slate-100" />

                                                    <p class="px-4 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-left">Ubah Status</p>

                                                    <a href="pesanan_admin.php?action=update_status&id=<?= $row['id_pesanan']; ?>&status_pesanan=process" class="w-full text-left px-4 py-2 text-body-sm hover:bg-blue-50 text-blue-600 flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Process
                                                    </a>
                                                    <a href="pesanan_admin.php?action=update_status&id=<?= $row['id_pesanan']; ?>&status_pesanan=sent" class="w-full text-left px-4 py-2 text-body-sm hover:bg-orange-50 text-orange-600 flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-orange-500"></span> Sent
                                                    </a>
                                                    <a href="pesanan_admin.php?action=update_status&id=<?= $row['id_pesanan']; ?>&status_pesanan=done" class="w-full text-left px-4 py-2 text-body-sm hover:bg-emerald-50 text-emerald-600 flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Done
                                                    </a>

                                                    <hr class="my-1 border-slate-100" />

                                                    <a href="pesanan_admin.php?action=update_status&id=<?= $row['id_pesanan']; ?>&status_pesanan=cancel" class="w-full text-left px-4 py-2 text-body-sm hover:bg-rose-50 text-rose-600 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-[16px]">cancel</span> Cancel
                                                    </a>
                                                </div>
                                            </div>
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

    <div id="modal-detail" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl animate-in fade-in zoom-in duration-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-on-surface">Detail Transaksi & Item</h3>
                <button onclick="closeModalDetail()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="content-detail" class="space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <p class="text-center text-slate-500 text-sm py-4">Memuat data...</p>
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown(id, buttonEl) {
            const dropdown = document.getElementById(id);
            const isHidden = dropdown.classList.contains('hidden');

            // Tutup semua dropdown lain yang sedang terbuka
            document.querySelectorAll('[id^="dropdown-menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });

            // Jika tadinya tertutup, sekarang tampilkan
            if (isHidden) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        // Opsional: Menutup dropdown otomatis jika user mengklik di luar menu
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.relative.inline-block.text-left')) {
                document.querySelectorAll('[id^="dropdown-menu-"]').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });

        // AJAX FETCH DATA KE MODAL BARU
        function openModalDetail(idPesanan) {
            const modal = document.getElementById('modal-detail');
            const content = document.getElementById('content-detail');

            content.innerHTML = '<p class="text-center text-slate-500 text-sm py-4">Memuat detail pesanan...</p>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            fetch('get_detail_pesanan.php?id=' + idPesanan)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(err => {
                    content.innerHTML = '<p class="text-center text-rose-500 text-sm py-4">Gagal memuat data.</p>';
                });
        }

        function closeModalDetail() {
            const modal = document.getElementById('modal-detail');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.grid > div');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.4s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>

</html>