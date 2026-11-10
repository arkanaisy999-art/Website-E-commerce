<?php
session_start();
$_SESSION = array();
if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

// Alihkan ke account.php sambil membawa parameter status logout
header("Location: account.php?status=logout_success");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    // Cek apakah ada parameter status=logout_success di URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'logout_success') {
        Swal.fire({
            title: 'Logout Berhasil!',
            text: 'Anda telah keluar dari sistem secara aman.',
            icon: 'success',
            confirmButtonColor: '#3525cd', // Menyesuaikan warna tema utama Anda
            confirmButtonText: 'Oke'
        }).then(() => {
            // Bersihkan parameter di URL browser agar tidak muncul lagi saat di-refresh
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
</script>
</body>
</html>