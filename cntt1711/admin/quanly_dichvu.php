<?php
// ===== PHẦN 1: BẢO MẬT & KHỞI TẠO =====
session_start();
require_once '../functions/db_connect.php';
$conn = getDbConnection();

if (!isset($_SESSION['user_name']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Lấy danh sách khu vực để chọn (fk_id_kv)
$khu_vuc_list = [];
$result_kv = $conn->query("SELECT id_kv, ten_kv FROM khu_vuc");
if ($result_kv) {
    while ($row = $result_kv->fetch_assoc()) {
        $khu_vuc_list[] = $row;
    }
}

// Biến hỗ trợ
$message = '';
$edit_mode = false;
$service_to_edit = [
    'id_dv' => '',
    'ten_dv' => '',
    'loai_dv' => '',
    'gia' => '',
    'fk_id_kv' => '',
    'hinh_anh' => '',
    'mo_ta' => ''
];

// ===== PHẦN 2: XỬ LÝ FORM =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dv = trim($_POST['ten_dv']);
    $loai_dv = trim($_POST['loai_dv']);
    $gia = (float)$_POST['gia'];
    $fk_id_kv = (int)$_POST['fk_id_kv'];
    $mo_ta = trim($_POST['mo_ta']);
    $hinh_anh = "";

    // --- Upload ảnh ---
    if (!empty($_FILES['hinh_anh']['name'])) {
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["hinh_anh"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
            $hinh_anh = "uploads/" . $file_name;
        }
    }

    // Cập nhật
    if (!empty($_POST['id_dv'])) {
        $id_dv = (int)$_POST['id_dv'];
        if (!empty($hinh_anh)) {
            $stmt = $conn->prepare("UPDATE dich_vu 
                SET ten_dv=?, loai_dv=?, gia=?, fk_id_kv=?, hinh_anh=?, mo_ta=?
                WHERE id_dv=?");
            $stmt->bind_param("ssdis si", $ten_dv, $loai_dv, $gia, $fk_id_kv, $hinh_anh, $mo_ta, $id_dv);
        } else {
            $stmt = $conn->prepare("UPDATE dich_vu 
                SET ten_dv=?, loai_dv=?, gia=?, fk_id_kv=?, mo_ta=?
                WHERE id_dv=?");
            $stmt->bind_param("ssdisi", $ten_dv, $loai_dv, $gia, $fk_id_kv, $mo_ta, $id_dv);
        }
        $message = "Cập nhật dịch vụ thành công!";
    } else {
        // Thêm mới
        $stmt = $conn->prepare("INSERT INTO dich_vu (ten_dv, loai_dv, gia, fk_id_kv, hinh_anh, mo_ta)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $ten_dv, $loai_dv, $gia, $fk_id_kv, $hinh_anh, $mo_ta);
        $message = "Thêm dịch vụ mới thành công!";
    }

    if ($stmt->execute()) {
        header("Location: quanly_dichvu.php?msg=" . urlencode($message));
        exit();
    } else {
        $message = "Lỗi: " . $stmt->error;
    }
    $stmt->close();
}

// ===== PHẦN 3: XỬ LÝ EDIT / DELETE =====
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM dich_vu WHERE id_dv = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: quanly_dichvu.php?msg=" . urlencode("Đã xóa dịch vụ!"));
        exit();
    }
    if ($_GET['action'] === 'edit') {
        $stmt = $conn->prepare("SELECT * FROM dich_vu WHERE id_dv = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $service_to_edit = $result->fetch_assoc();
            $edit_mode = true;
        }
        $stmt->close();
    }
}

if (isset($_GET['msg'])) $message = htmlspecialchars($_GET['msg']);

// ===== PHẦN 4: LẤY DANH SÁCH =====
$sql = "SELECT dv.*, kv.ten_kv 
        FROM dich_vu dv
        LEFT JOIN khu_vuc kv ON dv.fk_id_kv = kv.id_kv
        ORDER BY dv.id_dv DESC";
$result_list = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Dịch vụ</title>
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
            <a href="quanly_dichvu.php" class="active">
                <i class="fa-solid fa-briefcase"></i> Quản lý Dịch vụ
            </a>

            <a href="xemdonhang.php">
                <i class="fa-solid fa fa-ticket"></i> Xem Đơn hàng
            </a>
    </nav>
    <div class="logout">
            <a href="/cntt1711/handle/logout.php" style="text-decoration:none;">
                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </a>
        </div>
</aside>

<main class="main-content">
    <h1>Quản lý Dịch vụ</h1>
    <?php if(!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <div class="form-container">
        <h2><?= $edit_mode ? 'Chỉnh sửa Dịch vụ' : 'Thêm Dịch vụ mới' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if($edit_mode): ?>
                <input type="hidden" name="id_dv" value="<?= $service_to_edit['id_dv'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="ten_dv">Tên dịch vụ</label>
                <input type="text" name="ten_dv" id="ten_dv" value="<?= htmlspecialchars($service_to_edit['ten_dv']) ?>" required>
            </div>

            <div class="form-group">
                <label for="loai_dv">Loại dịch vụ</label>
                <input type="text" name="loai_dv" id="loai_dv" value="<?= htmlspecialchars($service_to_edit['loai_dv']) ?>" required>
            </div>

            <div class="form-group">
                <label for="gia">Giá (VND)</label>
                <input type="number" name="gia" id="gia" step="1000" min="0" value="<?= htmlspecialchars($service_to_edit['gia']) ?>">
            </div>

            <div class="form-group">
                <label for="fk_id_kv">Khu vực</label>
                <select name="fk_id_kv" id="fk_id_kv">
                    <option value="">-- Chọn khu vực --</option>
                    <?php foreach($khu_vuc_list as $kv): ?>
                        <option value="<?= $kv['id_kv'] ?>" <?= $service_to_edit['fk_id_kv'] == $kv['id_kv'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kv['ten_kv']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="hinh_anh">Ảnh dịch vụ</label>
                <input type="file" name="hinh_anh" id="hinh_anh" accept="image/*">
                <?php if (!empty($service_to_edit['hinh_anh'])): ?>
                    <img src="<?= $service_to_edit['hinh_anh'] ?>" alt="Ảnh dịch vụ" width="100">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="mo_ta">Mô tả</label>
                <textarea name="mo_ta" id="mo_ta"><?= htmlspecialchars($service_to_edit['mo_ta']) ?></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit"><?= $edit_mode ? 'Cập nhật' : 'Thêm mới' ?></button>
                <?php if($edit_mode): ?><a href="quanly_dichvu.php">Hủy</a><?php endif; ?>
            </div>
        </form>
    </div>

    <h2>Danh sách Dịch vụ</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Dịch vụ</th>
                <th>Loại</th>
                <th>Giá</th>
                <th>Khu vực</th>
                <th>Ảnh</th>
                <th>Mô tả</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result_list && $result_list->num_rows > 0): 
                while($row = $result_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_dv'] ?></td>
                    <td><?= htmlspecialchars($row['ten_dv']) ?></td>
                    <td><?= htmlspecialchars($row['loai_dv']) ?></td>
                    <td><?= number_format($row['gia'], 0, ',', '.') ?>₫</td>
                    <td><?= htmlspecialchars($row['ten_kv'] ?? 'N/A') ?></td>
                    <td>
                        <?php if (!empty($row['hinh_anh'])): ?>
                            <img src="<?= '/cntt1711/admin/' . $row['hinh_anh']; ?>" alt="Ảnh" width="80" height="60">                            
                        <?php else: ?>
                            <span>Chưa có</span>
                        <?php endif; ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($row['mo_ta'])) ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $row['id_dv'] ?>">Sửa</a> | 
                        <a href="?action=delete&id=<?= $row['id_dv'] ?>" onclick="return confirm('Xóa dịch vụ này?')">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8" style="text-align:center;">Chưa có dịch vụ nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
