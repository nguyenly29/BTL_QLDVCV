<?php
session_start();

// 1. Kết nối DB (Đảm bảo đường dẫn này đúng)
require '../functions/db_connect.php'; 

// SỬA LỖI: GỌI HÀM ĐỂ LẤY KẾT NỐI
$conn = getDbConnection();

// 2. Kiểm tra phương thức POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Lấy dữ liệu từ form (đã bỏ email)
    $username = trim($_POST['user_name']);
    $password = trim($_POST['pass_word']);

    // 3. Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($password)) {
        // Chuyển hướng về trang login với thông báo lỗi
        header("Location: ../login.php?error=Vui lòng điền đầy đủ Tên đăng nhập và Mật khẩu");
        exit();
    }

    // 4. Kiểm tra tài khoản tồn tại (chỉ kiểm tra user_name)
    $sql_check = "SELECT id_tk FROM tai_khoan WHERE user_name = ?";
    
    if ($stmt_check = $conn->prepare($sql_check)) {
        // Chỉ bind 1 biến "s" (string)
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Đã tồn tại user
            header("Location: ../login.php?error=Tên đăng nhập đã tồn tại");
            exit();
        }
        $stmt_check->close();

    } else {
        // Lỗi khi chuẩn bị câu lệnh
        header("Location: ../login.php?error=Lỗi hệ thống (check), vui lòng thử lại");
        exit();
    }

    // 5. Băm mật khẩu (Giữ nguyên)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 6. Thêm người dùng mới vào DB (đã bỏ cột email)
    // Gán mặc định trang_thai = 1 (hoạt động) và vai_tro = 'user'
    $sql_insert = "INSERT INTO tai_khoan (user_name, pass_word, trang_thai, vai_tro, ngay_tao) VALUES (?, ?, 1, 'user', NOW())";
    
    if ($stmt_insert = $conn->prepare($sql_insert)) {
        // Bind 2 biến "ss" (user, pass)
        $stmt_insert->bind_param("ss", $username, $hashed_password);
        
        if ($stmt_insert->execute()) {
            // Đăng ký thành công, tự động đăng nhập
            $_SESSION['user_name'] = $username;
            $_SESSION['user_id'] = $conn->insert_id; 
            $_SESSION['vai_tro'] = 'user'; 

            // Chuyển hướng đến trang chính
            header("Location: ../index.php");
            exit();
        } else {
            // Lỗi khi thực thi
            header("Location: ../login.php?error=Đăng ký thất bại, vui lòng thử lại");
            exit();
        }

    } else {
        // Lỗi khi chuẩn bị câu lệnh
        header("Location: ../login.php?error=Lỗi hệ thống (insert), vui lòng thử lại");
        exit();
    }
  

} else {
    // Nếu không phải là POST, đá về trang login
    header("Location: ../login.php");
    exit();
}
?>