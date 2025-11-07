<?php
// PHẦN 1: BẢO MẬT VÀ KHỞI TẠO
session_start();
require_once '../functions/db_connect.php';
$conn = getDbConnection();

// Bảo vệ: Kiểm tra nếu chưa đăng nhập HOẶC không phải admin
if (!isset($_SESSION['user_name']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = ''; // Biến lưu thông báo

// PHẦN 2: XỬ LÝ HÀNH ĐỘNG (DUYỆT, HỦY)
// Sử dụng Primary Key 'id_giohang' của bảng 'gio_hang'
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id_giohang = (int)$_GET['id'];
    $new_status = '';

    // Giả định trạng thái: 'completed' = đã duyệt, 'cancelled' = đã hủy
    if ($action === 'approve') {
        $new_status = 'completed'; // Đặt trạng thái mới
        $message = "Đã duyệt đơn hàng thành công!";
    } elseif ($action === 'cancel') {
        $new_status = 'cancelled'; // Đặt trạng thái mới
        $message = "Đã hủy đơn hàng!";
    }

    if (!empty($new_status)) {
        // Cập nhật 'trang_thai' trong bảng 'gio_hang'
        $stmt = $conn->prepare("UPDATE gio_hang SET trang_thai = ? WHERE id_giohang = ?");
        $stmt->bind_param("si", $new_status, $id_giohang);
        
        if (!$stmt->execute()) {
            $message = "Lỗi: " . $stmt->error;
        }
        $stmt->close();
        header("Location: xemdonhang.php?msg=" . urlencode($message));
        exit();
    }
}

// Lấy thông báo từ URL
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// PHẦN 3: LẤY DỮ LIỆU ĐỂ HIỂN THỊ BẢNG
// Câu lệnh SQL JOIN phức tạp để lấy thông tin từ nhiều bảng
$sql = "SELECT 
            gh.id_giohang, 
            gh.so_luong, 
            gh.trang_thai,
            tk.user_name,
            -- Dùng COALESCE để lấy tên từ 1 trong 2 bảng (trò chơi hoặc dịch vụ)
            COALESCE(tc.ten_tc, dv.ten_dv) AS ten_san_pham,
            -- Lấy giá từ 1 trong 2 bảng và nhân với số lượng
            (gh.so_luong * COALESCE(tc.gia_ve, dv.gia)) AS thanh_tien
        FROM gio_hang AS gh
        -- Join với bảng tài khoản để lấy tên khách hàng
        LEFT JOIN tai_khoan AS tk ON gh.id_khachhang = tk.id_tk
        -- Join với bảng trò chơi để lấy tên/giá vé
        LEFT JOIN tro_choi AS tc ON gh.id_ve = tc.id_tc
        -- Join với bảng dịch vụ để lấy tên/giá dịch vụ
        LEFT JOIN dich_vu AS dv ON gh.id_dichvu = dv.id_dv
        -- Lọc ra các đơn đã đặt (không lấy các món còn 'pending' trong giỏ)
        WHERE gh.trang_thai != 'pending' 
        ORDER BY gh.id_giohang DESC"; // Sắp xếp đơn mới nhất lên đầu

$result_list = $conn->query($sql);
if (!$result_list) {
    // Nếu có lỗi (do sai tên cột/bảng), code sẽ dừng và báo lỗi
    die("Lỗi SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Xem Đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <nav>
            <a href="dashboard.php">
                <i class="fa-solid fa fa-chart-line"></i> Tổng quan
            </a>
            <a href="quanly_taikhoan.php">
                <i class="fa-solid fa fa-users"></i> Quản lý Tài khoản
            </a>
            <a href="quanly_trochoi.php">
                <i class="fa-solid fa fa-gamepad"></i> Quản lý Trò chơi
            </a>
            <a href="quanly_dichvu.php">
                <i class="fa-solid fa-briefcase"></i> Quản lý Dịch vụ
            </a>

            <a href="xemdonhang.php" class="active">
                <i class="fa-solid fa fa-ticket"></i> Xem Đơn hàng
            </a>
        </nav>
        <div class="logout">
            <a href="/cntt1711/handle/logout.php" style="text-decoration:none;">
                <i class="fa-solid fa-right-from-bracket" ></i> Đăng xuất
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Xem Đơn hàng</h1>
            <div class="user-info">
                Chào, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
            </div>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'Lỗi') !== false) ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <h2>Danh sách Đơn hàng</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm / Dịch vụ</th>
                    <th>Số lượng</th>
                    <th>Thành tiền (VND)</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_list && $result_list->num_rows > 0):
                    while($row = $result_list->fetch_assoc()):
                        
                        // Gán class CSS dựa trên trạng thái
                        $status_class = '';
                        switch ($row['trang_thai']) {
                            case 'completed':
                                $status_class = 'status-completed';
                                break;
                            case 'ordered':
                                $status_class = 'status-ordered';
                                break;
                            case 'cancelled':
                                $status_class = 'status-cancelled';
                                break;
                            default:
                                $status_class = 'status-pending';
                        }
                ?>
                    <tr>
                        <td><?php echo $row['id_giohang']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_san_pham'] ?? 'Sản phẩm đã xóa'); ?></td>
                        <td><?php echo $row['so_luong']; ?></td>
                        <td><?php echo number_format($row['thanh_tien'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="status <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($row['trang_thai']); ?>
                            </span>
                        </td>
                        
                        <td class="action-links">
                            <?php 
                            // Chỉ hiển thị nút nếu đơn hàng đang ở trạng thái 'ordered'
                            if ($row['trang_thai'] === 'ordered'): 
                            ?>
                                <a href="?action=approve&id=<?php echo $row['id_giohang']; ?>" class="action-approve" onclick="return confirm('Duyệt đơn hàng này?');">Duyệt</a>
                                <a href="?action=cancel&id=<?php echo $row['id_giohang']; ?>" class="action-cancel" onclick="return confirm('Hủy đơn hàng này?');">Hủy</a>
                            <?php else: ?>
                                <span class="note">Đã xử lý</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Không có đơn hàng nào (ngoài các giỏ hàng 'pending').</td>
                    </tr>
                <?php
                endif;
                // Đóng kết nối
                $conn->close();
                ?>
            </tbody>
        </table>

    </main>

</body>
</html>