<?php
$hashed_password = '';
$original_password = '';

// 1. Kiểm tra xem người dùng đã nhấn nút "Băm mật khẩu" chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Lấy mật khẩu từ ô input
    if (isset($_POST['mat_khau']) && !empty($_POST['mat_khau'])) {
        $original_password = $_POST['mat_khau'];
        
        // 3. Băm mật khẩu bằng thuật toán mặc định (Bcrypt)
        $hashed_password = password_hash($original_password, PASSWORD_DEFAULT);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Công cụ Test Hàm Băm</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        input[type="text"] { 
            width: 300px; 
            padding: 8px; 
            font-size: 16px;
        }
        button { 
            padding: 9px 15px; 
            font-size: 16px; 
            cursor: pointer;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background-color: #eee;
            border-radius: 5px;
            /* Rất quan trọng để chuỗi hash tự xuống dòng */
            word-wrap: break-word; 
        }
        pre {
            font-family: "Courier New", monospace;
            font-size: 1.1em;
            color: #c7254e; /* Màu đỏ sậm cho dễ thấy */
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Công cụ tạo Hash (dùng `password_hash`)</h2>
        
        <form action="" method="POST">
            <label for="mat_khau">Nhập mật khẩu:</label>
            <input type="text" id="mat_khau" name="mat_khau">
            <button type="submit">Băm mật khẩu</button>
        </form>

        <?php if ($hashed_password): ?>
            <div class="result">
                <p><strong>Mật khẩu gốc bạn đã nhập:</strong></p>
                <p><?php echo htmlspecialchars($original_password); ?></p>
                
                <p><strong>Chuỗi Hash (để lưu vào database):</strong></p>
                <pre><?php echo $hashed_password; ?></pre>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>