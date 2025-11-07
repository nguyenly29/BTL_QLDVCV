<?php
require_once __DIR__ . '/../functions/db_connect.php';
$conn = getDbConnection();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Babilon Paradise</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <!-- Logo -->
            <div class="logo">
                <h5>BABILON PARADISE</h5>
            </div>

            <!-- Menu -->
            <div class="menu">
                <ul>
                    <li><a href="cntt1711/index.php" onclick="window.location.href='/cntt1711/index.php'; return false;">Trang Chủ</a></li>
                    <li><a href="cntt1711/hotro.php" onclick="window.location.href='/cntt1711/hotro.php'; return false;">Hỗ Trợ</a></li>

                    <?php if (isset($_SESSION['user_name'])): ?>
                        <li><a href="handle/logout.php">Đăng Xuất</a></li>
                    <?php else: ?>
                        <li><a href="handle/login.php">Đăng Nhập</a></li>
                    <?php endif; ?>

                    <li><a href="/cntt1711/views/cart.php" ><i class="fa-solid fa-bag-shopping"></i></a></li>
                </ul>

            </div>
        </div>
    </header>

    <!-- Banner -->
    <banner> <div class="banner"> 
        <div class="slide"></div> 
        <div class="content-banner"> 
                <h3>Tổ Hợp Dịch Vụ Công Viên Giải Trí</h3> 
                <p>Trải nghiệm không gian giải trí đẳng cấp và độc đáo</p> 
            </div> 
        </div> 
    </banner>


        <div class="search-suggestions">
            <div class="suggestion-item">Vé vào cổng</div>
            <div class="suggestion-item">Dịch vụ ẩm thực</div>
            <div class="suggestion-item">Khu vui chơi trẻ em</div>
            <div class="suggestion-item">Sự kiện đặc biệt</div>
        </div>
    </div>

<main>
