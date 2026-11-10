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
$admin_email = $admin_data['email'] ?? 'admin@gmail.com';
$admin_role = $admin_data['role'] ?? 'Admin';

// ==========================================
// PROSES SIMPAN BIO KE SESSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bio'])) {
    // Ambil input bio, bersihkan spasi di awal/akhir
    $new_bio = trim($_POST['admin_bio']);

    // Simpan langsung ke dalam session admin tersebut
    $_SESSION['admin_bio'] = $new_bio;

    // Refresh halaman agar form tidak mengirim ulang data saat di-reload
    header("Location: profile_admin.php");
    exit();
}

// Mengambil bio dari session, jika belum ada set isi default-nya
$admin_bio = $_SESSION['admin_bio'] ?? "Managing core infrastructure for over 10 years. Expert in cloud deployment and enterprise security systems. Enthusiastic about automated workflows.";

// LOGIK GENERATOR INISIAL: Mengambil huruf pertama dari kata pertama dan kedua nama admin
$words = explode(" ", $admin_name);
$initials = strtoupper(substr($words[0], 0, 1));
if (count($words) > 1) {
    $initials .= strtoupper(substr($words[1], 0, 1));
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Profile | Admin Center</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-low": "#eff4ff",
                        "on-error-container": "#93000a",
                        "surface-container-highest": "#d3e4fe",
                        "surface-tint": "#4d44e3",
                        "tertiary-fixed-dim": "#ffb695",
                        "surface-container-high": "#dce9ff",
                        "surface-container": "#e5eeff",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#004c6e",
                        "primary-container": "#4f46e5",
                        "secondary-container": "#39b8fd",
                        "on-tertiary-fixed-variant": "#7b2f00",
                        "on-surface": "#0b1c30",
                        "error": "#ba1a1a",
                        "on-background": "#0b1c30",
                        "primary": "#3525cd",
                        "on-primary-fixed": "#0f0069",
                        "surface-dim": "#cbdbf5",
                        "outline": "#777587",
                        "tertiary-container": "#a44100",
                        "surface": "#f8f9ff",
                        "tertiary-fixed": "#ffdbcc",
                        "surface-variant": "#d3e4fe",
                        "primary-fixed-dim": "#c3c0ff",
                        "inverse-on-surface": "#eaf1ff",
                        "on-tertiary": "#ffffff",
                        "on-error": "#ffffff",
                        "secondary-fixed-dim": "#89ceff",
                        "secondary": "#006591",
                        "on-tertiary-fixed": "#351000",
                        "inverse-surface": "#213145",
                        "inverse-primary": "#c3c0ff",
                        "secondary-fixed": "#c9e6ff",
                        "primary-fixed": "#e2dfff",
                        "on-secondary-container": "#004666",
                        "on-tertiary-container": "#ffd2be",
                        "on-primary": "#ffffff",
                        "on-secondary": "#ffffff",
                        "on-secondary-fixed": "#001e2f",
                        "tertiary": "#7e3000",
                        "surface-bright": "#f8f9ff",
                        "background": "#f8f9ff",
                        "on-surface-variant": "#464555",
                        "on-primary-fixed-variant": "#3323cc",
                        "outline-variant": "#c7c4d8",
                        "on-primary-container": "#dad7ff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "gutter": "1.5rem",
                        "stack-sm": "0.5rem",
                        "stack-lg": "1.5rem",
                        "container-padding": "2rem",
                        "stack-md": "1rem",
                        "sidebar-width": "280px",
                        "sidebar-collapsed": "80px"
                    },
                    "fontFamily": {
                        "headline-md": ["Inter"],
                        "label-bold": ["Inter"],
                        "display-lg-mobile": ["Inter"],
                        "body-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "caption": ["Inter"],
                        "body-sm": ["Inter"]
                    },
                    "fontSize": {
                        "headline-md": ["20px", {
                            "lineHeight": "28px",
                            "letterSpacing": "-0.01em",
                            "fontWeight": "600"
                        }],
                        "label-bold": ["12px", {
                            "lineHeight": "16px",
                            "letterSpacing": "0.05em",
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
                        "display-lg": ["32px", {
                            "lineHeight": "40px",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "700"
                        }],
                        "caption": ["12px", {
                            "lineHeight": "16px",
                            "letterSpacing": "0",
                            "fontWeight": "400"
                        }],
                        "body-sm": ["14px", {
                            "lineHeight": "20px",
                            "letterSpacing": "0",
                            "fontWeight": "400"
                        }]
                    }
                }
            },
        }
    </script>
</head>

<body class="bg-surface text-on-surface min-h-screen">
    <aside class="fixed left-0 top-0 h-full w-sidebar-width hidden md:flex flex-col z-40 shadow-sm bg-surface dark:bg-on-background border-r border-outline-variant/30">
        <div class="flex flex-col h-full py-6">
            <div class="px-6 mb-8">
                <h1 class="text-xl text-primary font-bold">Admin Center</h1>
                <p class="font-body-sm text-body-xs text-sm">Operations Management</p>
            </div>
            <nav class="flex-1">
                <a href="dash_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-colors font-body-md text-body-md">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <a href="CRUDproduk_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-colors font-body-md text-body-md">
                    <span class="material-symbols-outlined">inventory_2</span>
                    CRUD Produk
                </a>
                <a href="pesanan_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-colors font-body-md text-body-md">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    Pesanan
                </a>
            </nav>
            <a href="profile_admin.php" class="mt-auto px-6 pt-6 border-t border-outline-variant/20 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm tracking-wider select-none border-2 border-primary-container shadow-inner">
                    <?= $initials ?>
                </div>
                <div class="overflow-hidden">
                    <p class="font-label-bold text-on-surface truncate font-semibold"><?= $admin_name ?></p>
                    <p class="text-medium text-sm capitalize"><?= $admin_role ?></p>
                </div>
            </a>
        </div>
    </aside>
    <main class="md:ml-sidebar-width min-h-screen transition-all duration-300">
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
        <div class="pt-24 pb-12 px-container-padding max-w-7xl mx-auto">
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div id="alert-notification" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3 transition-all duration-500">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span class="text-sm font-medium"><?= $_SESSION['success_msg'];
                                                        unset($_SESSION['success_msg']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div id="alert-notification" class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl flex items-center gap-3 transition-all duration-500">
                    <span class="material-symbols-outlined">error</span>
                    <span class="text-sm font-medium"><?= $_SESSION['error_msg'];
                                                        unset($_SESSION['error_msg']); ?></span>
                </div>
            <?php endif; ?>
            <section class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-8 mb-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 rounded-full bg-primary text-white flex items-center justify-center font-bold text-4xl tracking-wider select-none ring-2 ring-primary/20 shadow-sm">
                            <?= $initials ?>
                        </div>
                        <div>
                            <h2 class="font-display-lg text-display-lg text-on-surface capitalize"><?= $admin_name ?></h2>
                            <p class="text-on-surface-variant font-body-md flex items-center gap-2">
                                <?= $admin_email ?>
                            </p>
                            <div class="flex gap-2 mt-2">
                                <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-[11px] font-bold"><?= $admin_role ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <span class="material-symbols-outlined text-primary">person</span>
                            <h3 class="font-headline-md text-headline-md">Account Information</h3>
                        </div>

                        <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="font-label-bold text-on-surface-variant text-body-sm ml-1">Full Name</label>
                                <input class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all text-body-md bg-surface" type="text" value="<?= $admin_name ?>" readonly />
                            </div>
                            <div class="space-y-2">
                                <label class="font-label-bold text-on-surface-variant text-body-sm ml-1">Email Address</label>
                                <input class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all text-body-md bg-surface" type="email" value="<?= $admin_email ?>" readonly />
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="font-label-bold text-on-surface-variant text-body-sm ml-1">Bio</label>
                                <textarea name="admin_bio" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all text-body-md bg-surface resize-none" rows="4"><?= htmlspecialchars($admin_bio) ?></textarea>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" name="save_bio" class="px-6 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-all text-sm shadow-sm flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">save</span>
                                    Simpan Bio
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
                <div class="space-y-8">
                    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="material-symbols-outlined text-primary">security</span>
                            <h3 class="font-headline-md text-headline-md">Security</h3>
                        </div>
                        <div>
                            <button id="open-password-modal" class="w-full flex items-center justify-center gap-2 px-6 py-3 border border-outline-variant text-on-surface font-label-bold rounded-lg hover:bg-surface-container-low transition-all">
                                <span class="material-symbols-outlined text-body-md">lock_reset</span>
                                Change Password
                            </button>
                        </div>
                    </div>

                    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="material-symbols-outlined text-primary">logout</span>
                            <h3 class="font-headline-md text-headline-md">Account Session</h3>
                        </div>
                        <div class="space-y-4">
                            <p class="text-body-sm text-on-surface-variant">Sign out of your current session on this device.</p>
                            <button type="button" id="open-logout-modal" class="w-full flex items-center justify-center gap-2 px-6 py-3 border border-error text-error font-semibold rounded-lg hover:bg-error/5 transition-all active:scale-95">
                                <span class="material-symbols-outlined text-body-md">logout</span>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="px-container-padding pb-8 pt-4 text-center">
            <p class="text-caption text-on-surface-variant">© 2024 Admin Center Operations. All rights reserved.</p>
        </footer>
    </main>

    <div id="logout-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div id="modal-backdrop" class="absolute inset-0 bg-black/10 backdrop-blur-sm transition-opacity"></div>

        <div id="modal-box" class="relative bg-surface-container-lowest bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-outline-variant/30 transform transition-all scale-95 duration-200">
            <div class="flex items-center gap-3 text-error mb-4">
                <span class="material-symbols-outlined text-3xl">warning</span>
                <h3 class="text-headline-md font-bold text-on-surface">Konfirmasi Keluar</h3>
            </div>

            <p class="text-body-md text-on-surface-variant mb-6">
                Apakah Anda yakin ingin keluar dari sesi admin saat ini? Anda harus login kembali untuk mengakses halaman dashboard.
            </p>

            <div class="flex items-center justify-end gap-3">
                <button type="button" id="close-logout-modal" class="px-5 py-2.5 border border-outline-variant text-on-surface font-semibold rounded-lg hover:bg-surface-container-low transition-all text-sm">
                    Cancel
                </button>
                <a href="logout_admin.php" class="px-5 py-2.5 bg-error text-white font-semibold rounded-lg hover:bg-error/90 transition-all text-sm shadow-sm">
                    Ya, Logout
                </a>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-[100] hidden items-center justify-center p-4" id="passwordModal">
        <div class="absolute inset-0 bg-on-background/60" id="passwordModalBackdrop"></div>
        <div class="relative w-full max-w-md bg-surface-container-lowest bg-white rounded-lg shadow-2xl overflow-hidden transform transition-all scale-95 duration-200" id="passwordModalBox">
            <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/30">
                <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Ganti Kata Sandi</h2>
                <button class="text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low p-2 rounded-full transition-colors" id="close-password-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="update_password.php" method="POST" class="p-6 space-y-5 m-0">
                <div>
                    <label class="block font-body-sm text-body-sm font-medium text-on-surface mb-2">Kata Sandi Saat Ini</label>
                    <div class="relative">
                        <input name="password" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-lg font-body-md text-body-md focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all" placeholder="Masukkan kata sandi lama" type="password" required />
                        <button type="button" class="toggle-password absolute right-3 top-2.5 text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                        </button>
                    </div>
                </div>
                <div class="h-px bg-outline-variant/20 my-2"></div>
                <div>
                    <label class="block font-body-sm text-body-sm font-medium text-on-surface mb-2">Kata Sandi Baru</label>
                    <div class="relative">
                        <input name="new_password" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-lg font-body-md text-body-md focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all" placeholder="Min. 8 karakter" type="password" minlength="8" required />
                        <button type="button" class="toggle-password absolute right-3 top-2.5 text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block font-body-sm text-body-sm font-medium text-on-surface mb-2">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <input name="confirm_password" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-lg font-body-md text-body-md focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all" placeholder="Ulangi kata sandi baru" type="password" minlength="8" required />
                        <button type="button" class="toggle-password absolute right-3 top-2.5 text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                        </button>
                    </div>
                </div>
                <div class="bg-surface-container p-3 rounded-lg flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-[20px]">info</span>
                    <p class="font-caption text-caption text-on-surface-variant">Pastikan kata sandi baru Anda menggunakan kombinasi huruf besar, kecil, angka, dan simbol untuk keamanan maksimal.</p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-outline-variant/30">
                    <button type="button" id="cancel-password-modal" class="px-5 py-2.5 border border-outline-variant text-on-surface font-semibold rounded-lg hover:bg-surface-container-low transition-all text-sm">
                        Batal
                    </button>
                    <button type="submit" name="update_password" class="px-5 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-all text-sm shadow-sm">
                        Simpan Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simple Interaction: Smooth input highlight
        document.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('focus', () => {
                el.parentElement.querySelector('label')?.classList.add('text-primary');
            });
            el.addEventListener('blur', () => {
                el.parentElement.querySelector('label')?.classList.remove('text-primary');
            });
        });

        // Toggle Sidebar for Mobile
        const mobileMenuBtn = document.querySelector('button.md\\:hidden');
        const sidebar = document.querySelector('aside');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('flex');
            });
        }

        // ==========================================
        // LOGIKA POP UP MODAL LOGOUT (Sudah Diperbaiki)
        // ==========================================
        const logoutModal = document.getElementById('logout-modal');
        const modalBox = document.getElementById('modal-box');
        const openModalBtn = document.getElementById('open-logout-modal');
        const closeModalBtn = document.getElementById('close-logout-modal');
        const modalBackdrop = document.getElementById('modal-backdrop');

        // Fungsi Membuka Modal
        openModalBtn.addEventListener('click', () => {
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
            // Menghilangkan scale-95 agar modal membesar halus saat muncul
            setTimeout(() => {
                modalBox.classList.remove('scale-95');
            }, 10);
        });

        // Fungsi Menutup Modal
        function closeModal() {
            modalBox.classList.add('scale-95');
            setTimeout(() => {
                logoutModal.classList.remove('flex');
                logoutModal.classList.add('hidden');
            }, 150);
        }

        // Trigger Tutup Modal (Tombol Cancel & Klik Luar Area Modal)
        closeModalBtn.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', closeModal);

        // ==========================================
        // LOGIKA MODAL GANTI PASSWORD
        // ==========================================
        const passwordModal = document.getElementById('passwordModal');
        const passwordModalBox = document.getElementById('passwordModalBox');
        const openPasswordBtn = document.getElementById('open-password-modal');
        const closePasswordBtn = document.getElementById('close-password-modal');
        const cancelPasswordBtn = document.getElementById('cancel-password-modal');
        const passwordBackdrop = document.getElementById('passwordModalBackdrop');

        // Buka Modal Password
        openPasswordBtn.addEventListener('click', () => {
            passwordModal.classList.remove('hidden');
            passwordModal.classList.add('flex');
            setTimeout(() => {
                passwordModalBox.classList.remove('scale-95');
            }, 10);
        });

        // Tutup Modal Password
        function closePasswordModal() {
            passwordModalBox.classList.add('scale-95');
            setTimeout(() => {
                passwordModal.classList.remove('flex');
                passwordModal.classList.add('hidden');
            }, 150);
        }

        closePasswordBtn.addEventListener('click', closePasswordModal);
        cancelPasswordBtn.addEventListener('click', closePasswordModal);
        passwordBackdrop.addEventListener('click', closePasswordModal);

        // Fitur Show / Hide Password (Ikon Mata)
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('.material-symbols-outlined');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = 'visibility';
                } else {
                    input.type = 'password';
                    icon.textContent = 'visibility_off';
                }
            });
        });

        // ==========================================
        // LOGIKA AUTO-HIDE NOTIFIKASI (4 DETIK)
        // ==========================================
        const notification = document.getElementById('alert-notification');
        if (notification) {
            setTimeout(() => {
                // Efek animasi menghilang (fade-out)
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';

                // Benar-benar menghapus elemen dari layout setelah animasi selesai
                setTimeout(() => {
                    notification.remove();
                }, 500); // Waktu transisi animasi (.duration-500)
            }, 4000); // 4000 milidetik = 4 detik
        }
    </script>
</body>

</html>