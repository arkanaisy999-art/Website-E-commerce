<?php
session_start();

// 1. Hapus semua data session
$_SESSION = array();

// 2. Hancurkan session secara total
session_destroy();

// 3. Hapus cookie session (opsional tapi bagus)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Tampilkan alert lewat JavaScript, baru pindah halaman
echo "<script>
        alert('Logout Berhasil!');
        window.location.href = 'index.php';
      </script>";
exit();
?>