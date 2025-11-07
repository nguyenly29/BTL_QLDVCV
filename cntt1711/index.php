<?php
session_start();
include ("./templates/header.php");
// Chỉ cần kiểm tra xem họ đã đăng nhập chưa
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php"); // Cùng thư mục nên không cần ../
    exit();
}


// Lấy thông tin người dùng từ session
$userName = $_SESSION['user_name'];
$role = $_SESSION['vai_tro'];
?>


    <link rel="stylesheet" href="assets/css/main.css">

    <section class="list-content" id="list-content">
        <div class="list-header">
            <h2>Các Loại Dịch Vụ</h2>
        </div>
        <div class="card-grid">
            <div class="card">
                <img src="assets/image/tro_choi_mao_hiem.jpg" alt="Dịch vụ 1">
                <div class="image-text">Trò Chơi Mạo Hiểm</div>
            </div>
            <div class="card">
                <img src="assets/image/service_eat.avif" alt="Dịch vụ 2">
                <div class="image-text">Dịch Vụ Ăn Uống</div>
            </div>
            <div class="card">
                <img src="assets/image/thuy_cung.webp" alt="Dịch vu 3">
                <div class="image-text">Thủy Cung</div>
            </div>
            <div class="card">
                <img src="assets/image/game_group.png" alt="Dịch vụ 4">
                <div class="image-text">Tổ Hợp Trò Chơi</div>
            </div>
            <div class="card">
                <img src="assets/image/vuon_bach_thu.webp" alt="Dịch vụ 5">
                <div class="image-text">Vườn Bách Thú</div>
            </div>
        </div>
    </section>

<!--Deal Hot-->
<?php
    // Lấy tất cả các trò chơi, mới nhất lên đầu
    $sql = "SELECT tc.*, dv.ten_dv 
            FROM tro_choi tc
            LEFT JOIN dich_vu dv ON tc.id_dv = dv.id_dv
            ORDER BY tc.id_tc DESC LIMIT 8";
    $result = mysqli_query($conn, $sql);
?>
<!--Trải nghiệm mới nhất-->
<section class="deal_hot">
    <h2>Trải Nghiệm Nổi Bật</h2>
    <div class="deal_container">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="deal_box">
                <div class="deal_image">
                    <?php
                        // Nếu có ảnh thật trong DB thì dùng, ngược lại dùng ảnh mặc định
                        $hinh_anh = !empty($row['hinh_anh'])
                            ? htmlspecialchars($row['hinh_anh'])
                            : 'assets/image/default.jpg';
                    ?>
                    <img src="<?= $hinh_anh ?>" alt="<?= htmlspecialchars($row['ten_tc']) ?>">
                    <div class="deal_discount">11% OFF</div>
                </div>
                <div class="deal_text">
                    <h3><?= htmlspecialchars(string: $row['ten_tc']) ?></h3>
                    <p>Thuộc dịch vụ: <?= htmlspecialchars($row['ten_dv'] ?? 'Không') ?></p>
                    <p class="description"><?= htmlspecialchars($row['mo_ta']) ?></p>
                    <p class="price">
                        <span class="original_price"><?= number_format($row['gia_ve'], 0, ',', '.') ?>₫</span>
                        <span class="discounted_price"><?= number_format($row['gia_ve'] * (1 - 0.11), 0, ',', '.') ?>₫</span>
                    </p>
                     <a href="#" class="btn btn-datve" 
                      data-id="<?= $row['id_tc'] ?>" 
                      data-ten="<?= htmlspecialchars($row['ten_tc']) ?>" 
                      data-gia="<?= $row['gia_ve'] ?>" 
                      data-giam="0.11">Đặt vé
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<!--BUY TICKET HIDDEN-->
<!-- Modal Đặt Vé (ẩn mặc định) -->
<div id="modalDatVe" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Đặt Vé</h2><br>
        <form id="formDatVe" method="POST" action="process_datve.php">
            <input type="hidden" name="id_tc" id="id_tc_modal">
            <input type="hidden" name="gia_ve" id="gia_ve_modal">

            <label for="ten">Họ và tên</label>
            <input type="text" id="ten" name="ten_nguoi_mua" required>

            <label for="sdt">Số điện thoại</label>
            <input type="text" id="sdt" name="sdt_nguoi_mua" required>

            <label for="so_luong">Số lượng vé</label>
            <input type="number" name="so_luong" id="so_luong" min="1" value="1">

            <label for="tong_tien">Tổng tiền</label>
            <input type="text" name="tong_tien" id="tong_tien" readonly>

            <div class="form-buttons" style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit">Đặt Vé</button>
                <button type="button" id="addCartBtn">Thêm Giỏ Hàng</button>
            </div>
        </form>
    </div>
</div>
<!-- Dịch vụ Hot -->
<section class="deal_hot">
    <h2>Dịch Vụ Hot</h2>
    <div class="deal_container">
        <?php
       
        // Lấy các dịch vụ có loại "hot"
        $sql = "SELECT * FROM dich_vu ORDER BY id_dv DESC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                // Nếu có ảnh thật thì lấy, không thì ảnh mặc định
                $hinh_anh = !empty($row['hinh_anh'])
                ?'/cntt1711/admin/'. htmlspecialchars($row['hinh_anh'])
                : 'assets/image/default.jpg';

        ?>
                <div class="deal_box">
                    <div class="deal_image">
                        <img src="<?= $hinh_anh ?>" alt="<?= htmlspecialchars($row['ten_dv']) ?>">
                        <div class="deal_discount">HOT</div>
                    </div>
                    <div class="deal_text">
                        <h3><?= htmlspecialchars($row['ten_dv']) ?></h3>
                        <p class="description"><?= htmlspecialchars($row['mo_ta']) ?></p>
                        <!-- Thêm vào trong hot_item_info, dưới hot_item_total -->
                    </div>
                </div>
        <?php
            endwhile;
        else:
            echo "<p>Hiện chưa có dịch vụ hot nào.</p>";
        endif;

        mysqli_close($conn);
        ?>
    </div>
</section>




    <!--Tin tức-->
    <section class="news-section">
  <div class="news-header">
    <h2>Tin tức</h2>
  </div>

  <div class="news-container">
    <!-- Tin tức nổi bật -->
    <div class="news-featured">
      <img src="assets/image/tintuc1.jpg" alt="Babilon Paradise Hotel">
      <h3>Babilon Paradise Hotel - Trải nghiệm lưu trú tiện nghi giữa lòng Thiên đường Giải trí Babilon</h3>
      <div class="news-meta">
        <i class="fa-regular fa-clock"></i> 24/10/2025
      </div>
      <p>
        Chào mừng bạn đến với Babilon Paradise – không gian giải trí đẳng cấp, nơi hội tụ giữa thiên nhiên xanh mát, trò chơi hiện đại và dịch vụ sang trọng bậc nhất dành cho mọi lứa tuổi.
        Được lấy cảm hứng từ vườn treo Babylon huyền thoại, Babilon Paradise mang đến một thế giới vừa lãng mạn, vừa đầy năng lượng – nơi mọi khoảnh khắc đều trở thành trải nghiệm đáng nhớ.
      </p>
    </div>

    <!-- Danh sách tin tức -->
    <div class="news-list">
      <div class="news-item">
        <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=400" alt="Babilon Paradise Event">
        <div class="news-info">
          <h4>Babilon Paradise Event - Hệ sinh thái tổ chức sự kiện toàn diện tại Hà Nội</h4>
          <div class="news-meta"><i class="fa-regular fa-clock"></i> 05/11/2025</div>
          <p>Trải qua hành trình phát triển mạnh mẽ, Babilon Paradise Event đã trở thành lựa chọn hàng đầu cho các sự kiện, hội nghị và lễ hội văn hóa tại khu vực phía Bắc.</p>
        </div>
      </div>

      <div class="news-item">
        <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&q=80&w=400" alt="Thông báo đóng cửa">
        <div class="news-info">
          <h4>Thông báo đóng cửa khu vui chơi Babilon Paradise ngày 31/10/2025</h4>
          <div class="news-meta"><i class="fa-regular fa-clock"></i> 01/10/2025</div>
          <p>Do ảnh hưởng của thời tiết xấu và mưa lớn, Babilon Paradise tạm dừng đón khách trong ngày 2/10/2025 để đảm bảo an toàn cho du khách và nhân viên.</p>
        </div>
      </div>

      <div class="news-item">
        <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=400" alt="Điều chỉnh lịch hoạt động">
        <div class="news-info">
          <h4>Thông báo điều chỉnh lịch hoạt động và giá vé khu du lịch Babilon Paradise ngày 5/10/2025</h4>
          <div class="news-meta"><i class="fa-regular fa-clock"></i> 2/11/2025</div>
          <p>Babilon Paradise xin thông báo về việc điều chỉnh lịch hoạt động và bảng giá vé mới, mang đến nhiều ưu đãi hấp dẫn dành cho khách hàng trong mùa du lịch thu 2025.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="why-choose">
  <div class="why-container">
    <h2>Vì Sao Chọn <span>BABILON PARADISE</span>?</h2>
    <div class="why-grid">
      <div class="why-item">
        <i class="fa-solid fa-star"></i>
        <h4>Đẳng Cấp & Sang Trọng</h4>
        <p>Không gian giải trí hiện đại, thiết kế tinh tế mang phong cách châu Âu.</p>
      </div>
      <div class="why-item">
        <i class="fa-solid fa-umbrella-beach"></i>
        <h4>Trải Nghiệm Đa Dạng</h4>
        <p>Hàng chục trò chơi, khu nghỉ dưỡng, nhà hàng và khu chụp ảnh tuyệt đẹp.</p>
      </div>
      <div class="why-item">
        <i class="fa-solid fa-users"></i>
        <h4>Dịch Vụ Chu Đáo</h4>
        <p>Đội ngũ nhân viên tận tâm, sẵn sàng phục vụ để mang lại trải nghiệm hoàn hảo nhất.</p>
      </div>
    </div>
  </div>
</section>
<script src="assets/js/script.js"></script>


<?php
include ("./templates/footer.php");
?>