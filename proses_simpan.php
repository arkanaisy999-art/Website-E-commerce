<?php
include 'config/koneksi.php';

if (isset($_POST['simpan'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    
    // Mengambil input teks kategori dan mengubah huruf pertama menjadi kapital
    $kategori = mysqli_real_escape_string($conn, ucwords(strtolower($_POST['kategori'])));

    // Query insert ke tabel data Anda
    $query = "INSERT INTO produk (nama_barang, kategori) VALUES ('$nama_barang', '$kategori')";
    $hasil = mysqli_query($conn, $query);

    if ($hasil) {
        echo "<script>alert('Data berhasil disimpan!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data.');</script>";
    }
}
?>