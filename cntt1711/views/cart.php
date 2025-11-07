<?php
include __DIR__ . '/../functions/giohang_func.php';
include __DIR__ . '/../templates/header.php';

?>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/giohang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="cart-container">
    <h1><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng của bạn</h1>

    <?php if ($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <table class="cart-table">
        <thead>
            <tr>
                <th>Sản phẩm / Dịch vụ</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                    $tong_tien += $row['thanh_tien'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ten_sp']); ?></td>
                        <td><?php echo number_format($row['don_gia'], 0, ',', '.'); ?> ₫</td>
                        <td><?php echo $row['so_luong']; ?></td>
                        <td class="price"><?php echo number_format($row['thanh_tien'], 0, ',', '.'); ?> ₫</td>
                        <td>
                            <a href="cart.php?remove=<?php echo $row['id_giohang']; ?>" class="btn-remove" onclick="return confirm('Bạn chắc muốn xóa sản phẩm này?');">
                                <i class="fa-solid fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="empty">Giỏ hàng của bạn trống.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($tong_tien > 0): ?>
    <div class="cart-summary">
        <h2>Tổng cộng: <span><?php echo number_format($tong_tien, 0, ',', '.'); ?> ₫</span></h2>
        <form method="POST">
            <button type="submit" name="checkout" class="btn-checkout">
                <i class="fa-solid fa-credit-card"></i> Đặt hàng ngay
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>



<?php
include __DIR__ . '/../templates/footer.php';
$stmt->close();
$conn->close();
?>
