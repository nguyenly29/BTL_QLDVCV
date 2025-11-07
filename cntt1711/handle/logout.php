<?php
session_start();

// Hủy toàn bộ session
session_unset();
session_destroy();

// Xóa cookie phiên (nếu có)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Quay về trang đăng nhập
header("Location: login.php");
exit();
?>