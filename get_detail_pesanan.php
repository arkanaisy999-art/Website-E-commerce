<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
    echo "<div class='text-center py-6 text-error font-medium flex flex-col items-center justify-center gap-2'>
            <span class='material-symbols-outlined text-3xl'>error</span>
            <p class='text-sm'>ID Pesanan tidak ditemukan.</p>
          </div>";
    exit;
}

$id_pesanan = intval($_GET['id']);

// Ambil data utama pesanan, pelanggan, beserta detail kurir & resi dari DB
$query_pesanan = mysqli_query($conn, "SELECT p.*, pl.username AS nama_pelanggan, pl.email FROM pesanan p 
                                      LEFT JOIN pengguna pl ON p.id_pengguna = pl.id 
                                      WHERE p.id_pesanan = $id_pesanan");

if (mysqli_num_rows($query_pesanan) == 0) {
    echo "<div class='text-center py-6 text-on-surface-variant flex flex-col items-center justify-center gap-2'>
            <span class='material-symbols-outlined text-3xl'>database_off</span>
            <p class='text-sm'>Data transaksi tidak ditemukan.</p>
          </div>";
    exit;
}

$pesanan = mysqli_fetch_assoc($query_pesanan);

// PERBAIKAN: Sebutkan kolom secara eksplisit. 
// Ambil data item produk belanja
// Kita gunakan 'dp.jumlah AS jumlah_beli' supaya sinkron dengan kode HTML kamu
$query_item = mysqli_query($conn, "SELECT 
                                    dp.id_detail, 
                                    dp.id_pesanan, 
                                    dp.id_produk, 
                                    dp.jumlah AS jumlah_beli, 
                                    dp.subtotal,
                                    pr.nama_produk,
                                    pr.harga -- Pastikan kolom harga ada di tabel produk
                                   FROM detail_pesanan dp 
                                   JOIN produk pr ON dp.id_produk = pr.id_produk 
                                   WHERE dp.id_pesanan = $id_pesanan");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant/20 space-y-3">
        <div class="flex justify-between items-center text-body-sm">
            <span class="text-on-surface-variant flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">person</span> Pelanggan
            </span>
            <span class="font-semibold text-on-surface"><?= htmlspecialchars($pesanan['nama_pelanggan'] ?? 'Guest/Anonim'); ?></span>
        </div>

        <div class="flex justify-between items-center text-body-sm">
            <span class="text-on-surface-variant flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">calendar_month</span> Waktu
            </span>
            <span class="font-medium text-on-surface"><?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></span>
        </div>

        <div class="flex justify-between items-center text-body-sm">
            <span class="text-on-surface-variant flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">local_shipping</span> Pengiriman
            </span>
            <span class="font-medium text-on-surface">
                <?= htmlspecialchars($pesanan['shipping_method'] ?? '-'); ?>
                <?= !empty($pesanan['no_resi']) ? '(' . htmlspecialchars($pesanan['no_resi']) . ')' : '(<span class="text-slate-400">Resi Belum Ada</span>)'; ?>
            </span>
        </div>

        <div class="flex justify-between items-center pt-2.5 border-t border-outline-variant/30 text-body-sm">
            <span class="text-on-surface-variant flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">info</span> Status Pesanan
            </span>
            <?php
            $status = strtolower($pesanan['status_pesanan']);
            if ($status == 'unpaid') {
                echo '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">Unpaid</span>';
            } elseif ($status == 'process') {
                echo '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Process</span>';
            } elseif ($status == 'sent') {
                echo '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Sent</span>';
            } elseif ($status == 'done') {
                echo '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Done</span>';
            } else {
                echo '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Cancel</span>';
            }
            ?>
        </div>
    </div>

    <div class="space-y-2">
        <h4 class="text-label-bold text-on-surface-variant uppercase tracking-wider text-[11px]">Daftar Item</h4>
        <div class="divide-y divide-outline-variant/20 border border-outline-variant/30 rounded-xl overflow-hidden bg-white shadow-sm">
            <?php if (mysqli_num_rows($query_item) == 0): ?>
                <div class="p-4 text-center text-on-surface-variant text-body-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                    Tidak ada rincian item produk belanja.
                </div>
            <?php else: ?>
                <?php while ($item = mysqli_fetch_assoc($query_item)):
                    $qty = intval($item['jumlah_beli']);
                    $subtotal = intval($item['subtotal']);
                    // Menghitung harga satuan dari subtotal / jumlah jika kolom harga_satuan tidak ada
                    $harga_satuan = ($qty > 0) ? ($subtotal / $qty) : 0;
                ?>
                    <div class="p-3.5 flex justify-between items-center hover:bg-surface-container-low/40 transition-colors">
                        <div class="space-y-0.5">
                            <p class="text-body-md font-semibold text-on-surface"><?= htmlspecialchars($item['nama_produk']); ?></p>
                            <p class="text-caption text-on-surface-variant flex items-center gap-1">
                                <span class="font-medium text-primary"><?= $qty; ?>x</span>
                                <span class="text-slate-400">@</span>
                                Rp <?= number_format($harga_satuan, 0, ',', '.'); ?>
                            </p>
                        </div>
                        <p class="text-body-md font-bold text-on-surface">
                            Rp <?= number_format($subtotal, 0, ',', '.'); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-surface-container-low/30 p-4 rounded-xl border border-dashed border-outline-variant/40 space-y-2">
        <div class="flex justify-between items-center text-body-sm text-on-surface-variant">
            <span>Ongkos Kirim (<?= htmlspecialchars($pesanan['shipping_method'] ?? 'JNE'); ?>):</span>
            <span class="font-medium text-on-surface">Rp <?= number_format($pesanan['ongkir'], 0, ',', '.'); ?></span>
        </div>
        <div class="pt-2 border-t border-outline-variant/30 flex justify-between items-center">
            <span class="text-body-md font-semibold text-on-surface-variant">Total Akhir:</span>
            <span class="text-display-lg-mobile md:text-headline-md font-extrabold text-primary">
                Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?>
            </span>
        </div>
    </div>
</body>

</html>