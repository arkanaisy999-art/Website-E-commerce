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
// PROSES HAPUS PRODUK (DELETE)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);

    // Ambil nama file gambar lama untuk dihapus dari folder jika ada
    $query_foto = mysqli_query($conn, "SELECT foto FROM produk WHERE id_produk = $id_hapus");
    $data_foto = mysqli_fetch_assoc($query_foto);
    if (!empty($data_foto['foto']) && file_exists('img/' . $data_foto['foto'])) {
        unlink('img/' . $data_foto['foto']);
    }

    $delete_query = mysqli_query($conn, "DELETE FROM produk WHERE id_produk = $id_hapus");
    if ($delete_query) {
        echo "<script>alert('Produk berhasil dihapus!'); window.location='CRUDproduk_admin.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus produk!');</script>";
    }
}

// ==========================================
// PROSES TAMBAH & EDIT PRODUK (CREATE / UPDATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id_produk']) ? intval($_POST['id_produk']) : 0;
    $nama_produk = mysqli_real_escape_with_str($conn, $_POST['nama_produk']);
    // Cari baris ini di kode atas Anda:
    // $kategori = mysqli_real_escape_with_str($conn, $_POST['kategori']);

    // Ubah menjadi seperti ini agar format hurufnya rapi otomatis:
    $kategori = mysqli_real_escape_with_str($conn, ucwords(strtolower(trim($_POST['kategori']))));
    $berat = intval($_POST['berat']);
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);
    $deskripsi = mysqli_real_escape_with_str($conn, $_POST['deskripsi']);
    $sku = isset($_POST['sku']) ? mysqli_real_escape_with_str($conn, $_POST['sku']) : 'SKU-' . time();

    // Logika Pengunggahan Foto
    $nama_foto = "";
    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
        $target_dir = "img/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $ext = pathinfo($_FILES['foto_produk']['name'], PATHINFO_EXTENSION);
        $nama_foto = time() . '_' . uniqid() . '.' . $ext;
        $target_file = $target_dir . $nama_foto;

        // Pindahkan file ke folder img
        move_uploaded_file($_FILES['foto_produk']['tmp_name'], $target_file);
    }

    if ($id > 0) {
        // PROSES EDIT (UPDATE)
        if (!empty($nama_foto)) {
            // Jika ganti foto, hapus foto lama dari penyimpanan lokal
            $query_foto = mysqli_query($conn, "SELECT foto FROM produk WHERE id_produk = $id");
            $data_foto = mysqli_fetch_assoc($query_foto);
            if (!empty($data_foto['foto']) && file_exists('img/' . $data_foto['foto'])) {
                unlink('img/' . $data_foto['foto']);
            }
            $sql_update = "UPDATE produk SET nama_produk='$nama_produk', kategori='$kategori', berat=$berat, harga=$harga, stok=$stok, deskripsi='$deskripsi', foto='$nama_foto' WHERE id_produk=$id";
        } else {
            $sql_update = "UPDATE produk SET nama_produk='$nama_produk', kategori='$kategori', berat=$berat, harga=$harga, stok=$stok, deskripsi='$deskripsi' WHERE id_produk=$id";
        }
        $query_exec = mysqli_query($conn, $sql_update);
        $msg = "diperbarui";
    } else {
        // PROSES TAMBAH BARU (CREATE)
        $sql_insert = "INSERT INTO produk (nama_produk, kategori, berat, harga, stok, deskripsi, foto) VALUES ('$nama_produk', '$kategori', $berat, $harga, $stok, '$deskripsi', '$nama_foto')";
        $query_exec = mysqli_query($conn, $sql_insert);
        $msg = "ditambahkan";
    }

    if ($query_exec) {
        echo "<script>alert('Produk berhasil $msg!'); window.location='CRUDproduk_admin.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data ke database!');</script>";
    }
}

// Fungsi pembantu escape string
function mysqli_real_escape_with_str($conn, $string)
{
    return mysqli_real_escape_string($conn, $string);
}

// ==========================================
// HITUNG TOTAL PRODUK & AMBIL DATA UTAMA
// ==========================================
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM produk");
$count_data = mysqli_fetch_assoc($count_query);
$total_produk = $count_data['total'];

$product_query = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
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
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .modal-overlay {
            background-color: rgba(11, 28, 48, 0.4);
            backdrop-filter: blur(4px);
        }
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
                <a href="dash_admin.php" class="flex items-center gap-3 px-6 py-3 text-on-surface-variant hover:bg-surface-container-low transition-all duration-200 ease-in-out">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-body-md text-body-md">Dashboard</span>
                </a>
                <a href="CRUDproduk_admin.php" class="flex items-center gap-3 px-6 py-3 bg-primary/10 text-primary border-l-4 border-primary font-bold transition-all duration-200 ease-in-out">
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

        <div class="pt-24 pb-12 px-container-padding max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="font-display-lg text-display-lg text-on-surface">Kelola Produk</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant">Tambah, edit, atau hapus produk dari katalog toko Anda.</p>
                </div>
                <button class="flex items-center justify-center gap-2 px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-bold text-body-md shadow-sm hover:shadow-md transition-all active:scale-95" onclick="bukaModalTambah()">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Produk
                </button>
            </div>

            <div class="flex flex-col mb-8">
                <div class="bg-surface p-6 rounded-xl shadow-sm border border-surface-container-highest max-w-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">inventory</span>
                        </div>
                        <span class="text-xs font-label-bold text-on-surface-variant">Total Produk</span>
                    </div>
                    <p class="font-display-lg text-display-lg"><?= number_format($total_produk); ?></p>
                </div>
            </div>

            <div class="bg-surface rounded-xl shadow-sm border border-surface-container-highest overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Foto</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Nama Produk</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Kategori</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Harga</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase">Stok</th>
                                <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant uppercase text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <?php if (mysqli_num_rows($product_query) == 0): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant font-body-md">Belum ada produk di database.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($product_query)): ?>
                                    <tr class="hover:bg-surface-container-lowest transition-colors group">
                                        <td class="px-6 py-4">
                                            <?php
                                            $foto_src = !empty($row['foto']) && file_exists('img/' . $row['foto']) ? 'img/' . $row['foto'] : 'https://via.placeholder.com/150?text=No+Image';
                                            ?>
                                            <img alt="Product Thumbnail" class="w-12 h-12 rounded-lg object-cover shadow-sm" src="<?= $foto_src; ?>" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium"><?= htmlspecialchars($row['nama_produk']); ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-surface-container-highest text-primary rounded-full">
                                                <?= htmlspecialchars($row['kategori'] ?? 'Umum'); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-body-md text-body-md">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-body-sm text-body-sm"><?= $row['stok']; ?> unit</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" title="Lihat Detail"
                                                    onclick="bukaModalRead(<?= htmlspecialchars(json_encode($row)); ?>)">
                                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                                </button>
                                                <button class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit"
                                                    onclick="bukaModalEdit(<?= htmlspecialchars(json_encode($row)); ?>)">
                                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                                </button>
                                                <button class="p-2 text-error hover:bg-error/10 rounded-lg transition-colors" title="Hapus"
                                                    onclick="bukaModalHapus(<?= $row['id_produk']; ?>, '<?= htmlspecialchars(addslashes($row['nama_produk'])); ?>')">
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
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

    <div class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" id="modal-tambah">
        <div class="modal-overlay absolute inset-0" onclick="toggleModal('modal-tambah')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl px-4">
            <div class="bg-surface rounded-2xl shadow-xl overflow-hidden transform transition-transform duration-300 scale-95" id="modal-content">
                <div class="px-8 py-6 flex items-center justify-between border-b border-outline-variant">
                    <h2 class="font-headline-md text-headline-md text-on-surface" id="modal-title">Tambah Produk Baru</h2>
                    <button class="text-on-surface-variant hover:text-error transition-colors" onclick="toggleModal('modal-tambah')" type="button">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="form-produk" class="p-8 space-y-6" method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id_produk" id="form-id-produk" value="0" />
                    <input type="hidden" name="sku" id="form-sku-produk" value="" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block font-body-sm text-body-sm font-semibold mb-1.5 text-on-surface">Nama Produk</label>
                                <input name="nama_produk" id="form-nama" class="w-full px-4 py-2.5 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-body-md" placeholder="Masukkan nama produk..." type="text" required />
                            </div>
                            <div>
                                <label class="block font-body-sm text-body-sm font-semibold mb-1.5 text-on-surface">Kategori</label>

                                <input name="kategori" id="form-kategori" class="w-full px-4 py-2.5 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-body-md" placeholder="Ketik atau pilih kategori..." type="text" list="list_kategori" required />

                                <datalist id="list_kategori">
                                    <?php
                                    // Mengambil daftar kategori unik yang sudah ada di database
                                    $query_list_kategori = mysqli_query($conn, "SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
                                    while ($kat_row = mysqli_fetch_assoc($query_list_kategori)):
                                    ?>
                                        <option value="<?= htmlspecialchars($kat_row['kategori']); ?>"></option>
                                    <?php endwhile; ?>
                                </datalist>
                            </div>
                            <div>
                                <label class="block font-body-sm text-body-sm font-semibold mb-1.5 text-on-surface">Berat (gram)</label>
                                <input name="berat" id="form-berat" class="w-full px-4 py-2.5 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-body-md" placeholder="Masukkan berat produk..." type="number" required />
                            </div>
                            <div>
                                <label class="block font-body-sm text-body-sm font-semibold mb-1.5 text-on-surface">Harga</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-on-surface-variant text-body-sm font-bold">Rp</span>
                                    <input name="harga" id="form-harga" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-body-md" placeholder="0" type="number" required />
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block font-body-sm text-body-sm font-semibold mb-1.5 text-on-surface">Stok</label>
                                <input name="stok" id="form-stok" class="w-full px-4 py-2.5 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-body-md" placeholder="0" type="number" required />
                            </div>
                            <div>
                                <label class="block font-body-sm text-body-sm font-semibold mb-1.5 text-on-surface">Foto Produk</label>
                                <div class="relative group border-2 border-dashed border-outline-variant rounded-xl p-4 flex flex-col items-center justify-center text-center hover:border-primary transition-colors h-[120px]">
                                    <span class="material-symbols-outlined text-[32px] text-outline mb-1">add_a_photo</span>
                                    <p class="text-[10px] text-on-surface-variant px-4" id="upload-label">Seret & lepas foto atau klik untuk unggah (Maks. 2MB)</p>
                                    <input name="foto_produk" class="absolute inset-0 opacity-0 cursor-pointer" type="file" accept="image/*" onchange="previewNamaFile(this)" />
                                </div>
                            </div>
                            <div>
                                <label class="block font-body-sm text-body-sm font-semibold mb-1.5 text-on-surface">Deskripsi</label>
                                <textarea name="deskripsi" id="form-deskripsi" class="w-full px-4 py-2.5 rounded-lg border border-outline-variant focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all text-body-md resize-none" placeholder="Tulis deskripsi detail produk..." rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant">
                        <button class="px-6 py-2.5 text-primary font-label-bold hover:bg-primary/10 rounded-lg transition-colors" onclick="toggleModal('modal-tambah')" type="button">
                            Batal
                        </button>
                        <button class="px-10 py-2.5 bg-primary text-on-primary rounded-lg font-label-bold shadow-sm hover:shadow-md transition-all active:scale-95" type="submit">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" id="modal-read">
        <div class="modal-overlay absolute inset-0" onclick="toggleModal('modal-read')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md px-4">
            <div class="bg-surface rounded-2xl shadow-xl overflow-hidden transform transition-transform duration-300 scale-95" id="modal-read-content">
                <div class="px-6 py-4 flex items-center justify-between border-b border-outline-variant bg-surface-container-low">
                    <h2 class="font-headline-md text-base text-on-surface font-bold flex items-center gap-2">
                        Detail Data Produk
                    </h2>
                    <button class="text-on-surface-variant hover:text-error transition-colors" onclick="toggleModal('modal-read')">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-center mb-2">
                        <img id="read-foto" src="" alt="Foto Produk" class="w-32 h-32 rounded-xl object-cover shadow-md border border-outline-variant" />
                    </div>
                    <div class="grid grid-cols-3 gap-y-2.5 text-body-sm">
                        <div class="text-on-surface-variant font-medium">Nama Produk</div>
                        <div class="col-span-2 text-on-surface font-semibold" id="read-nama">-</div>

                        <div class="text-on-surface-variant font-medium">Kategori</div>
                        <div class="col-span-2 text-on-surface font-semibold" id="read-kategori">-</div>

                        <div class="text-on-surface-variant font-medium">Berat</div>
                        <div class="col-span-2 text-on-surface font-semibold" id="read-berat">-</div>

                        <div class="text-on-surface-variant font-medium">Harga</div>
                        <div class="col-span-2 text-primary font-bold" id="read-harga">-</div>

                        <div class="text-on-surface-variant font-medium">Stok</div>
                        <div class="col-span-2 text-on-surface" id="read-stok">-</div>

                        <div class="text-on-surface-variant font-medium">Deskripsi</div>
                        <div class="col-span-2 text-on-surface text-xs whitespace-pre-line bg-surface-container-low p-2 rounded-lg border border-outline-variant" id="read-deskripsi">-</div>
                    </div>
                </div>
                <div class="px-6 py-4 flex justify-end bg-surface-container-low border-t border-outline-variant">
                    <button class="px-6 py-2 bg-primary text-on-primary rounded-lg font-label-bold text-sm shadow-sm active:scale-95 transition-all" onclick="toggleModal('modal-read')">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" id="modal-hapus">
        <div class="modal-overlay absolute inset-0" onclick="toggleModal('modal-hapus')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm px-4">
            <div class="bg-surface rounded-2xl shadow-xl overflow-hidden transform transition-transform duration-300 scale-95" id="modal-hapus-content">
                <div class="p-6 text-center space-y-4">
                    <div class="w-14 h-14 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-[32px]">delete_forever</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-on-surface">Hapus Produk?</h3>
                        <p class="text-sm text-on-surface-variant mt-1">Apakah Anda yakin ingin menghapus produk <span id="hapus-nama-produk" class="font-semibold text-error"></span>?</p>
                    </div>
                </div>
                <div class="px-6 py-4 flex items-center justify-end gap-3 bg-surface-container-low border-t border-outline-variant">
                    <button class="px-4 py-2 text-on-surface-variant font-label-bold text-sm hover:bg-surface-container-high rounded-lg transition-colors" onclick="toggleModal('modal-hapus')">
                        Batal
                    </button>
                    <a id="btn-konfirmasi-hapus" href="#" class="px-6 py-2 bg-error text-on-error font-label-bold text-sm rounded-lg shadow-sm hover:shadow-md transition-all active:scale-95">
                        Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Utama Pengatur Animasi Buka/Tutup Modal
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const content = modal.querySelector('[id$="-content"]') || modal.querySelector('#modal-content');

            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.add('opacity-100');
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }, 10);
            } else {
                modal.classList.remove('opacity-100');
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        // Fungsi Buka Modal Tambah Baru (Reset Form)
        function bukaModalTambah() {
            document.getElementById('modal-title').innerText = "Tambah Produk Baru";
            document.getElementById('form-produk').reset();
            document.getElementById('form-id-produk').value = "0";
            document.getElementById('upload-label').innerText = "Seret & lepas foto atau klik untuk unggah (Maks. 2MB)";
            toggleModal('modal-tambah');
        }

        // Fungsi Buka Modal Edit (Set data lama ke Form)
        function bukaModalEdit(data) {
            document.getElementById('modal-title').innerText = "Ubah Produk";
            document.getElementById('form-id-produk').value = data.id_produk;
            document.getElementById('form-nama').value = data.nama_produk;
            document.getElementById('form-kategori').value = data.kategori || ""; // <-- Set kategori lama ke dropdown
            document.getElementById('form-berat').value = data.berat;
            document.getElementById('form-harga').value = data.harga;
            document.getElementById('form-stok').value = data.stok;
            document.getElementById('form-deskripsi').value = data.deskripsi;
            document.getElementById('upload-label').innerText = data.foto ? "File: " + data.foto : "Ganti foto jika perlu (Maks. 2MB)";
            toggleModal('modal-tambah');
        }

        // Fungsi Buka Modal Detail (Read)
        function bukaModalRead(data) {
            document.getElementById('read-nama').innerText = data.nama_produk;
            document.getElementById('read-kategori').innerText = data.kategori || "Umum"; // <-- Tampilkan kategori detail
            document.getElementById('read-berat').innerText = data.berat + " gram";
            document.getElementById('read-harga').innerText = "Rp " + parseInt(data.harga).toLocaleString('id-ID');
            document.getElementById('read-stok').innerText = data.stok + " unit";
            document.getElementById('read-deskripsi').innerText = data.deskripsi || "Tidak ada deskripsi.";

            const imgEl = document.getElementById('read-foto');
            if (data.foto) {
                imgEl.src = 'img/' + data.foto;
            } else {
                imgEl.src = 'https://via.placeholder.com/150?text=No+Image';
            }
            toggleModal('modal-read');
        }

        // Fungsi Buka Modal Konfirmasi Hapus
        function bukaModalHapus(id, nama) {
            document.getElementById('hapus-nama-produk').innerText = nama;
            document.getElementById('btn-konfirmasi-hapus').href = 'CRUDproduk_admin.php?action=delete&id=' + id;
            toggleModal('modal-hapus');
        }

        // Preview nama file upload
        function previewNamaFile(input) {
            const label = document.getElementById('upload-label');
            if (input.files && input.files[0]) {
                label.innerText = "Terpilih: " + input.files[0].name;
            }
        }
    </script>
</body>

</html>