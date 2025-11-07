<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db_connect.php'; // Kết nối tới DB

// Hàm đăng nhập (ĐÃ AN TOÀN)
function loginUser($user_name, $pass_word) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM tai_khoan WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        return "Tài khoản không tồn tại!";
    }

    // *** SỬA Ở ĐÂY ***
    // Sử dụng password_verify để so sánh mật khẩu đã hash trong DB
    // với mật khẩu người dùng nhập vào.
    if (!password_verify($pass_word, $user['pass_word'])) {
        return "Sai mật khẩu!";
    }

    if ($user['trang_thai'] != 1) {
        return "Tài khoản của bạn đang bị khóa!";
    }

    // Lưu session
    $_SESSION['user_id'] = $user['id_tk'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['vai_tro'] = $user['vai_tro'];

    // Điều hướng theo vai trò
    if ($user['vai_tro'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}


// Hàm đăng ký (ĐÃ AN TOÀN)
function registerUser($user_name, $pass_word, $vai_tro = 'user') {
    $conn = getDbConnection();

    // Kiểm tra xem user đã tồn tại chưa
    $stmt = $conn->prepare("SELECT * FROM tai_khoan WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Tên đăng nhập đã tồn tại!'); window.location='../register.php';</script>";
        return;
    }

    // *** SỬA Ở ĐÂY ***
    // Mã hóa mật khẩu trước khi lưu vào DB
    $hashed_password = password_hash($pass_word, PASSWORD_DEFAULT);

    // Thêm tài khoản mới với mật khẩu đã mã hóa
    $stmt = $conn->prepare("INSERT INTO tai_khoan (user_name, pass_word, trang_thai, vai_tro, ngay_tao) VALUES (?, ?, 1, ?, NOW())");
    
    // Gửi mật khẩu đã mã hóa ($hashed_password) vào DB, không phải $pass_word
    $stmt->bind_param("sss", $user_name, $hashed_password, $vai_tro); 
    
    if ($stmt->execute()) {
        echo "<script>alert('Đăng ký thành công!'); window.location='../login.php';</script>";
    } else {
        echo "<script>alert('Đăng ký thất bại!'); window.location='../register.php';</script>";
    }

    $stmt->close();
    $conn->close();
}

// Hàm đăng xuất
function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>