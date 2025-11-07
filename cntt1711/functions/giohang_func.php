<?php
session_start();
require_once 'db_connect.php';
$conn = getDbConnection();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?err=Vui lòng đăng nhập để xem giỏ hàng!");
    exit();
}

$id_khachhang = $_SESSION['user_id'];
$message = '';

// Xóa sản phẩm khỏi giỏ
if (isset($_GET['remove'])) {
    $id_giohang = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM gio_hang WHERE id_giohang = ? AND id_khachhang = ?");
    $stmt->bind_param("ii", $id_giohang, $id_khachhang);
    $stmt->execute();
    $message = "Đã xóa sản phẩm khỏi giỏ hàng.";
    header("Location: cart.php?msg=" . urlencode($message));
    exit();
}

// Đặt hàng
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['checkout'])) {
    $stmt = $conn->prepare("UPDATE gio_hang SET trang_thai = 'ordered' WHERE id_khachhang = ? AND trang_thai = 'pending'");
    $stmt->bind_param("i", $id_khachhang);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Đặt hàng thành công! Đơn hàng của bạn đang chờ xử lý.";
    } else {
        $message = "Giỏ hàng của bạn đang trống.";
    }

    header("Location: cart.php?msg=" . urlencode($message));
    exit();
}

// Hiển thị thông báo
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Lấy dữ liệu giỏ hàng
$sql = "SELECT gh.id_giohang, gh.so_luong, 
        COALESCE(tc.ten_tc, dv.ten_dv) AS ten_sp, 
        COALESCE(tc.gia_ve, dv.gia) AS don_gia,
        (gh.so_luong * COALESCE(tc.gia_ve, dv.gia)) AS thanh_tien
        FROM gio_hang gh
        LEFT JOIN tro_choi tc ON gh.id_ve = tc.id_tc
        LEFT JOIN dich_vu dv ON gh.id_dichvu = dv.id_dv
        WHERE gh.id_khachhang = ? AND gh.trang_thai = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_khachhang);
$stmt->execute();
$result = $stmt->get_result();

$tong_tien = 0;
?>
