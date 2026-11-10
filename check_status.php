<?php
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

include "config/koneksi.php";
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID not found']);
    exit;
}

$id_pesanan = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT status_pesanan FROM pesanan WHERE id_pesanan = '$id_pesanan'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    // Mengirim status koneksi 'ok' dan status pesanan asli dari database ('unpaid', 'process', dll)
    echo json_encode([
        'connection' => 'ok',
        'status_pesanan' => $data['status_pesanan']
    ]);
} else {
    echo json_encode(['connection' => 'error', 'message' => 'Order not found']);
}
?>