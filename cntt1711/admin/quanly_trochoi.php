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

// Giả định: Lấy danh sách khu vực để làm dropdown
// Bạn cần có bảng `khu_vuc` (với id_kv, ten_kv)
$khu_vuc_list = [];
$result_kv = $conn->query("SELECT id_kv, ten_kv FROM khu_vuc");
if ($result_kv) {
    while ($row = $result_kv->fetch_assoc()) {
        $khu_vuc_list[] = $row;
    }
}

$message = ''; // Biến lưu thông báo
$edit_mode = false; // Biến kiểm soát chế độ Sửa

// Dữ liệu mặc định cho form Thêm mới (đã cập nhật theo CSDL)
$game_to_edit = [
    'id_tc' => '',
    'id_kv' => '',
    'ten_tc' => '',
    'gio_open' => '08:00',
    'gio_close' => '21:00',
    'gia_ve' => '',
    'mo_ta' => ''
];

// PHẦN 2: XỬ LÝ CÁC HÀNH ĐỘNG (POST & GET)

// 2.1. Xử lý khi admin gửi form (THÊM hoặc SỬA)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lấy dữ liệu mới từ CSDL của bạn
    $ten_tc = trim($_POST['ten_tc']);
    $id_kv = (int)$_POST['id_kv'];
    $gio_open = trim($_POST['gio_open']);
    $gio_close = trim($_POST['gio_close']);
    $gia_ve = (float)$_POST['gia_ve']; // Dùng float cho giá vé
    $mo_ta = trim($_POST['mo_ta']);

        // --- Xử lý upload hình ảnh ---
    $hinh_anh = "";
    if (!empty($_FILES['hinh_anh']['name'])) {
        $target_dir = __DIR__ . "/uploads/"; // Thư mục lưu ảnh (trong admin)
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["hinh_anh"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
            // Lưu đường dẫn tương đối để truy cập trên web
            $hinh_anh = "admin/uploads/" . $file_name;
        }
    }


    // SỬA trò chơi (vì có 'id_tc' được gửi lên)
    if (isset($_POST['id_tc']) && !empty($_POST['id_tc'])) {
        $id_tc = (int)$_POST['id_tc'];

        // Kiểm tra nếu có upload ảnh mới
        if (!empty($_FILES['hinh_anh']['name'])) {
            // Đường dẫn tuyệt đối tới thư mục "uploads" trong thư mục "admin"
            $target_dir = __DIR__ . "/uploads/";

            // Tạo thư mục nếu chưa có
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Tạo tên file duy nhất
            $file_name = time() . "_" . basename($_FILES["hinh_anh"]["name"]);
            $target_file = $target_dir . $file_name;

            // Di chuyển file tạm sang thư mục đích
            if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
                // Lưu đường dẫn tương đối (để hiển thị trên web)
                $hinh_anh = "uploads/" . $file_name;
            } else {
                echo "<p style='color:red;'>Lỗi: Không thể di chuyển file tải lên!</p>";
            }
        }

        // Nếu có ảnh mới thì cập nhật cả cột hinh_anh
        if (!empty($hinh_anh)) {
            $stmt = $conn->prepare("UPDATE tro_choi 
                SET ten_tc = ?, id_kv = ?, gio_open = ?, gio_close = ?, gia_ve = ?, mo_ta = ?, hinh_anh = ?
                WHERE id_tc = ?");
            $stmt->bind_param("sissdssi", $ten_tc, $id_kv, $gio_open, $gio_close, $gia_ve, $mo_ta, $hinh_anh, $id_tc);
        } else {
            // Không đổi ảnh
            $stmt = $conn->prepare("UPDATE tro_choi 
                SET ten_tc = ?, id_kv = ?, gio_open = ?, gio_close = ?, gia_ve = ?, mo_ta = ?
                WHERE id_tc = ?");
            $stmt->bind_param("sissdsi", $ten_tc, $id_kv, $gio_open, $gio_close, $gia_ve, $mo_ta, $id_tc);
        }

        $message = "Cập nhật trò chơi thành công!";

    } 
    // THÊM trò chơi mới
    else {
        $stmt = $conn->prepare("INSERT INTO tro_choi (ten_tc, id_kv, gio_open, gio_close, gia_ve, mo_ta, hinh_anh) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissdss", $ten_tc, $id_kv, $gio_open, $gio_close, $gia_ve, $mo_ta, $hinh_anh);
        $message = "Thêm trò chơi mới thành công!";
    }

    if ($stmt->execute()) {
        header("Location: quanly_trochoi.php?msg=" . urlencode($message));
    } else {
        $message = "Lỗi: " . $stmt->error;
    }
    $stmt->close();
    exit();
}

// 2.2. Xử lý khi admin nhấn nút (XÓA hoặc SỬA)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Đây là id_tc

    // XÓA trò chơi
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM tro_choi WHERE id_tc = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: quanly_trochoi.php?msg=" . urlencode("Đã xóa trò chơi!"));
        exit();
    }
    
    // Tải dữ liệu để SỬA
    if ($_GET['action'] === 'edit') {
        $stmt = $conn->prepare("SELECT * FROM tro_choi WHERE id_tc = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $game_to_edit = $result->fetch_assoc();
            $edit_mode = true; // Bật chế độ Sửa
        }
        $stmt->close();
    }
}

// 2.3. Lấy thông báo từ URL
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// PHẦN 3: LẤY DỮ LIỆU ĐỂ HIỂN THỊ BẢNG
// Đã sửa 'id_tro_choi' thành 'id_tc' theo CSDL của bạn
$sql = "SELECT tc.*, kv.ten_kv 
        FROM tro_choi as tc 
        LEFT JOIN khu_vuc as kv ON tc.id_kv = kv.id_kv 
        ORDER BY tc.id_tc DESC";
$result_list = $conn->query($sql);
if (!$result_list) {
    // Nếu có lỗi, in ra để debug
    die("Lỗi SQL: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý Trò chơi</title>
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
            <a href="quanly_taikhoan.php">
                <i class="fa-solid fa-users"></i> Quản lý Tài khoản
            </a>
            <a href="quanly_trochoi.php" class="active">
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
            <h1>Quản lý Trò chơi</h1>
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
            <h2><?php echo $edit_mode ? 'Chỉnh sửa Trò chơi' : 'Thêm Trò chơi mới'; ?></h2>
            <form action="quanly_trochoi.php" method="POST" enctype="multipart/form-data">
                
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id_tc" value="<?php echo $game_to_edit['id_tc']; ?>">
                <?php endif; ?>

                <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label for="ten_tc">Tên trò chơi</label>
                    <input type="text" id="ten_tc" name="ten_tc" 
                        value="<?php echo htmlspecialchars($game_to_edit['ten_tc']); ?>" required>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label for="id_kv">Khu vực</label>
                    <select id="id_kv" name="id_kv" required>
                        <option value="">-- Chọn khu vực --</option>
                        <?php foreach ($khu_vuc_list as $kv): ?>
                            <option value="<?php echo $kv['id_kv']; ?>" 
                                <?php if ($game_to_edit['id_kv'] == $kv['id_kv']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($kv['ten_kv']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="hinh_anh">Ảnh trò chơi</label>
                    <input type="file" id="hinh_anh" name="hinh_anh" accept="image/*">
                    <?php if (!empty($game_to_edit['hinh_anh'])): ?>
                        <img src="<?php echo $game_to_edit['hinh_anh']; ?>" alt="Ảnh trò chơi" class="preview-img">
                    <?php endif; ?>
                </div>
            </div>

                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gio_open">Giờ mở cửa</label>
                        <input type="time" id="gio_open" name="gio_open" 
                               value="<?php echo htmlspecialchars($game_to_edit['gio_open']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gio_close">Giờ đóng cửa</label>
                        <input type="time" id="gio_close" name="gio_close" 
                               value="<?php echo htmlspecialchars($game_to_edit['gio_close']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gia_ve">Giá vé (VND)</label>
                        <input type="number" id="gia_ve" name="gia_ve" step="1000" min="0"
                               value="<?php echo htmlspecialchars($game_to_edit['gia_ve']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="mo_ta">Mô tả</label>
                    <textarea id="mo_ta" name="mo_ta"><?php echo htmlspecialchars($game_to_edit['mo_ta']); ?></textarea>
                </div>
                
                <div class="form-buttons">
                    <?php if ($edit_mode): ?>
                        <button type="submit" name="edit_game">Cập nhật</button>
                        <a href="quanly_trochoi.php">Hủy (về Thêm mới)</a>
                    <?php else: ?>
                        <button type="submit" name="add_game">Thêm mới</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h2>Danh sách Trò chơi hiện có</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên trò chơi</th>
                    <th>Khu vực</th>
                    <th>Giờ hoạt động</th>
                    <th>Giá vé (VND)</th>
                    <th>Ảnh</th>
                    <th>Mô tả</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_list && $result_list->num_rows > 0):
                    while($row = $result_list->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['id_tc']; ?></td>
                        <td><?php echo htmlspecialchars($row['ten_tc']); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_kv'] ?? 'N/A'); // ten_kv từ JOIN ?></td>
                        <td><?php echo $row['gio_open'] . ' - ' . $row['gio_close']; ?></td>
                        <td><?php echo number_format($row['gia_ve'], 0, ',', '.'); // Định dạng tiền Việt ?></td>
                        <td>
                            <?php if (!empty($row['hinh_anh'])): ?>
                                <img src="/cntt1711/<?php echo $row['hinh_anh']; ?>" alt="Ảnh" width="80" height="60">
                            <?php else: ?>
                                <span>Chưa có ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo nl2br(htmlspecialchars($row['mo_ta'])); ?></td>
                        
                        <td class="action-links">
                            <a href="?action=edit&id=<?php echo $row['id_tc']; ?>" class="action-edit">Sửa</a>
                            <a href="?action=delete&id=<?php echo $row['id_tc']; ?>" class="action-delete" 
                               onclick="return confirm('Bạn có chắc chắn muốn xóa trò chơi này?');">Xóa</a>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Chưa có trò chơi nào.</td>
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