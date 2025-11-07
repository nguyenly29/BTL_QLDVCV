<?php
// PHẦN 1: BẢO MẬT VÀ KHỞI TẠO
// Luôn bắt đầu session ở đầu file
session_start();

// Kiểm tra: Nếu chưa đăng nhập HOẶC đăng nhập nhưng KHÔNG PHẢI admin
if (!isset($_SESSION['user_name']) || $_SESSION['vai_tro'] !== 'admin') {
    // Đá về trang login (lưu ý dùng ../ để lùi ra 1 cấp)
    header("Location: ../login.php");
    exit();
}

// Nếu đúng là admin, ta kết nối DB để lấy số liệu thống kê
require_once '../functions/db_connect.php';
$conn = getDbConnection();

// PHẦN 2: TRUY VẤN DỮ LIỆU THỐNG KÊ
// (Bạn hãy thay 'ten_bang_...' bằng tên bảng thực tế trong DB của bạn)

// Đếm tổng số tài khoản
$result_users = $conn->query("SELECT COUNT(*) as total FROM tai_khoan");
$total_users = $result_users->fetch_assoc()['total'];

// Đếm tổng số trò chơi
$result_games = $conn->query("SELECT COUNT(*) as total FROM tro_choi"); // <-- Thay 'tro_choi'
$total_games = $result_games->fetch_assoc()['total'];

// Đếm tổng số đơn hàng/vé
$result_orders = $conn->query("SELECT COUNT(*) as total FROM ve"); // <-- Thay 've' hoặc 'don_hang'
$total_orders = $result_orders->fetch_assoc()['total'];

// Đếm tổng số dịch vụ
$result_services = $conn->query("SELECT COUNT(*) as total FROM dich_vu"); // <-- Thay 'dich_vu'
$total_services = $result_services->fetch_assoc()['total'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin_style.css">

</head>
<body>

    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <nav>
            <a href="dashboard.php" class="active">
                <i class="fa-solid fa-chart-line"></i> Tổng quan
            </a>
            <a href="quanly_taikhoan.php">
                <i class="fa-solid fa-users"></i> Quản lý Tài khoản
            </a>
            <a href="quanly_trochoi.php">
                <i class="fa-solid fa-gamepad"></i> Quản lý Trò chơi
            </a>
            <a href="quanly_dichvu.php">
                <i class="fa-solid fa-briefcase"></i> Quản lý Dịch vụ
            </a>
            <a href="xemdonhang.php">
                <i class="fa-solid fa-ticket"></i> Xem Đơn hàng
            </a>
            </nav>
        
        <div class="logout">
            <a href="/cntt1711/handle/logout.php" style="text-decoration:none;">
                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Tổng quan Dashboard</h1>
            <div class="user-info">
                Chào, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
            </div>
        </header>

        <section class="stat-boxes">
            
            <div class="stat-box">
                <div class="icon icon-users">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="info">
                    <h3>Tổng Tài khoản</h3>
                    <span><?php echo $total_users; ?></span>
                </div>
            </div>

            <div class="stat-box">
                <div class="icon icon-games">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <div class="info">
                    <h3>Tổng Trò chơi</h3>
                    <span><?php echo $total_games; ?></span>
                </div>
            </div>

            <div class="stat-box">
                <div class="icon icon-orders">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="info">
                    <h3>Tổng Đơn hàng/Vé</h3>
                    <span><?php echo $total_orders; ?></span>
                </div>
            </div>

            <div class="stat-box">
                <div class="icon icon-services">
                    <i class="fa-solid fa-bell-concierge"></i>
                </div>
                <div class="info">
                    <h3>Tổng Dịch vụ</h3>
                    <span><?php echo $total_services; ?></span>
                </div>
            </div>

        </section>

        </main>

</body>
</html>