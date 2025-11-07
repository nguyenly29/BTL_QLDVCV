<?php
// PHẦN 1: BẢO MẬT VÀ KHỞI TẠO
session_start();

// Bảo vệ: Kiểm tra nếu chưa đăng nhập HOẶC không phải admin
if (!isset($_SESSION['user_name']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Kết nối database
require_once '../functions/db_connect.php';
$conn = getDbConnection();

$current_admin_id = $_SESSION['user_id']; // Lấy ID của admin đang đăng nhập
$message = ''; // Biến để lưu thông báo thành công/lỗi

// PHẦN 2: XỬ LÝ HÀNH ĐỘNG

// ========== BẮT ĐẦU CODE MỚI (XỬ LÝ TẠO TÀI KHOẢN) ==========
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $new_role = $_POST['new_role'];

    // 1. Kiểm tra đầu vào
    if (empty($new_username) || empty($new_password) || empty($new_role)) {
        $message = "Lỗi: Vui lòng nhập đầy đủ Tên đăng nhập, Mật khẩu và Vai trò.";
    } else {
        // 2. Kiểm tra xem tên đăng nhập đã tồn tại chưa
        $stmt_check = $conn->prepare("SELECT id_tk FROM tai_khoan WHERE user_name = ?");
        $stmt_check->bind_param("s", $new_username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Lỗi: Tên đăng nhập '$new_username' đã tồn tại. Vui lòng chọn tên khác.";
        } else {
            // 3. Tên đăng nhập hợp lệ, tiến hành tạo
            // Băm mật khẩu để bảo mật
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Mặc định trang_thai = 1 (hoạt động) và ngay_tao = NOW()
            $stmt_insert = $conn->prepare("INSERT INTO tai_khoan (user_name, pass_word, vai_tro, trang_thai, ngay_tao) VALUES (?, ?, ?, 1, NOW())");
            
            // Chỗ này giả định cột mật khẩu của bạn tên là 'user_password'
            // Nếu tên khác (vd: 'password', 'mat_khau'), bạn hãy sửa lại
            $stmt_insert->bind_param("sss", $new_username, $hashed_password, $new_role);

            if ($stmt_insert->execute()) {
                $message = "Tạo tài khoản '$new_username' thành công!";
            } else {
                $message = "Lỗi khi tạo tài khoản: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
    
    // Tải lại trang để hiển thị thông báo và cập nhật danh sách
    header("Location: quanly_taikhoan.php?msg=" . urlencode($message));
    exit();
}
// ========== KẾT THÚC CODE MỚI ==========

// PHẦN 2: XỬ LÝ HÀNH ĐỘNG (KHÓA, MỞ, PHÂN QUYỀN, XÓA)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id']; // Ép kiểu về số nguyên cho an toàn

    // KIỂM TRA AN TOÀN: Không cho admin tự thao tác chính mình
    if ($user_id === $current_admin_id) {
        $message = "Lỗi: Bạn không thể tự thao tác trên tài khoản của chính mình!";
    } else {
        $stmt = null;
        // Dùng switch-case cho rõ ràng
        switch ($action) {
            case 'lock':
                $stmt = $conn->prepare("UPDATE tai_khoan SET trang_thai = 0 WHERE id_tk = ?");
                $message = "Đã khóa tài khoản thành công!";
                break;
            case 'unlock':
                $stmt = $conn->prepare("UPDATE tai_khoan SET trang_thai = 1 WHERE id_tk = ?");
                $message = "Đã mở khóa tài khoản thành công!";
                break;
            case 'promote':
                $stmt = $conn->prepare("UPDATE tai_khoan SET vai_tro = 'admin' WHERE id_tk = ?");
                $message = "Đã thăng cấp tài khoản lên Admin!";
                break;
            case 'demote':
                $stmt = $conn->prepare("UPDATE tai_khoan SET vai_tro = 'user' WHERE id_tk = ?");
                $message = "Đã hạ cấp tài khoản xuống User!";
                break;
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM tai_khoan WHERE id_tk = ?");
                $message = "Đã xóa tài khoản thành công!";
                break;
        }

        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                $message = "Lỗi: " . $stmt->error;
            }
            $stmt->close();
            // Tải lại trang để xóa các tham số GET và hiển thị thông báo
            header("Location: quanly_taikhoan.php?msg=" . urlencode($message));
            exit();
        }
    }
}

// Lấy thông báo từ URL (nếu có)
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// PHẦN 3: LẤY DỮ LIỆU TÀI KHOẢN ĐỂ HIỂN THỊ
$sql = "SELECT id_tk, user_name, vai_tro, trang_thai, ngay_tao FROM tai_khoan ORDER BY id_tk ASC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý Tài khoản</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <nav>
            <a href="dashboard.php">
                <i class="fa-solid fa-chart-line"></i> Tổng quan
            </a>
            <a href="quanly_taikhoan.php" class="active">
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
            <h1>Quản lý Tài khoản</h1>
            <div class="user-info">
                Chào, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
            </div>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'Lỗi') !== false) ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <div class="form-container">
    <h2>Thêm tài khoản mới</h2>
    
    <form action="quanly_taikhoan.php" method="POST">
        <!---->
        <div class="form-row">
            <div class="form-group">
                <label for="new_username">Tên đăng nhập:</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Mật khẩu:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="new_role">Vai trò:</label>
            <select id="new_role" name="new_role">
                <option value="admin">Quản lý (Admin)</option>
            </select>
        </div>
        
        <div class="form-buttons">
            <button type="submit" name="create_user">Tạo tài khoản</button>
        </div>

    </form>
</div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['id_tk']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td>
                            <?php 
                            if ($row['vai_tro'] === 'admin') {
                                echo '<span class="role-admin">Admin</span>';
                            } else {
                                echo '<span class="role-user">User</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($row['trang_thai'] == 1) {
                                echo '<span class="status-active">Hoạt động</span>';
                            } else {
                                echo '<span class="status-locked">Bị khóa</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo $row['ngay_tao']; ?></td>
                        
                        <td class="action-links">
                            <?php
                            // Kiểm tra an toàn: Không cho admin tự thao tác chính mình
                            if ($row['id_tk'] === $current_admin_id):
                            ?>
                                <span class="self-note">(Bạn)</span>
                            <?php else: ?>
                                
                                <?php if ($row['trang_thai'] == 1): ?>
                                    <a href="?action=lock&id=<?php echo $row['id_tk']; ?>" class="action-lock" onclick="return confirm('Khóa tài khoản này?');">Khóa</a>
                                <?php else: ?>
                                    <a href="?action=unlock&id=<?php echo $row['id_tk']; ?>" class="action-unlock" onclick="return confirm('Mở khóa tài khoản này?');">Mở</a>
                                <?php endif; ?>
                                
                                <?php if ($row['vai_tro'] === 'user'): ?>
                                    <a href="?action=promote&id=<?php echo $row['id_tk']; ?>" class="action-promote" onclick="return confirm('Thăng cấp tài khoản này lên Admin?');">Thăng Admin</a>
                                <?php else: ?>
                                    <a href="?action=demote&id=<?php echo $row['id_tk']; ?>" onclick="return confirm('Hạ cấp tài khoản này xuống User?');">Hạ User</a>
                                <?php endif; ?>

                                <a href="?action=delete&id=<?php echo $row['id_tk']; ?>" class="action-delete" onclick="return confirm('!!! BẠN CÓ CHẮC MUỐN XÓA TÀI KHOẢN NÀY? HÀNH ĐỘNG NÀY KHÔNG THỂ HOÀN TÁC.');">Xóa</a>

                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Không có tài khoản nào trong hệ thống.</td>
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