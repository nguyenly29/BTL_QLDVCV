<?php
session_start();
require_once '../functions/auth.php';

// Chỉ xử lý nếu người dùng gửi form bằng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lỗi là do các dấu cách "lạ" ở đầu dòng, đã được sửa:
    $user_name = trim($_POST['user_name']);
    $pass_word = trim($_POST['pass_word']);

    // Gọi hàm xử lý trong auth.php
    $loginResult = loginUser($user_name, $pass_word);

    // Nếu đăng nhập thành công, hàm loginUser() đã tự exit() rồi.
    // Nếu code chạy được đến đây, CÓ NGHĨA LÀ $loginResult
    // đang chứa một chuỗi thông báo LỖI.

    // Vì vậy, ta chỉ cần chuyển hướng người dùng về trang login
    // kèm theo thông báo lỗi đó là xong.
    header("Location: ../login.php?error=" . urlencode($loginResult));
    exit();

} else {
    // Nếu ai đó cố tình truy cập file này trực tiếp (không qua POST)
    // thì đá họ về trang đăng nhập.
    header("Location: ../login.php");
    exit();
}
?>