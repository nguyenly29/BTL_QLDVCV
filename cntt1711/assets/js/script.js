// Lấy modal và nút đóng
    const modal = document.getElementById('modalDatVe');
    const closeBtn = modal.querySelector('.close');
    const soLuongInput = document.getElementById('so_luong');
    const tongTienInput = document.getElementById('tong_tien');
    const giaVeInput = document.getElementById('gia_ve_modal');
    const ngayMoBanInput = document.getElementById('ngay_mo_ban');
    const addCartBtn = document.getElementById('addCartBtn');

    // Hàm mở modal khi click nút đặt vé
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const idTC = this.getAttribute('href'); // id trò chơi
            const giaGoc = parseFloat(this.dataset.gia);
            const giamGia = parseFloat(this.dataset.giam) || 0;
            const giaSauGiam = giaGoc * (1 - giamGia);
            const ngayMoBan = this.dataset.ngaymoban || new Date().toLocaleDateString();

            document.getElementById('id_tc_modal').value = idTC;
            giaVeInput.value = giaSauGiam;
            soLuongInput.value = 1;
            tongTienInput.value = giaSauGiam.toLocaleString('vi-VN', { style: 'currency', currency: 'VND'});

            //soLuongInput.value = 1;
            modal.style.display = 'block';
        });
    });

    // Tính tổng tiền khi thay đổi số lượng
    soLuongInput.addEventListener('input', function() {
        const sl = parseInt(this.value)|| 1;
        const gia = parseFloat(giaVeInput.value);
        tongTienInput.value = (sl * gia).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    });

    // Đóng modal
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = e => { if(e.target == modal) modal.style.display = 'none'; }

    // Thêm giỏ hàng
    addCartBtn.addEventListener('click', function() {
        const idTC = document.getElementById('id_tc_modal').value;
        const soLuong = parseInt(document.getElementById('so_luong').value);
        const tongTien = tongTienInput.value;
        alert(`Đã thêm vào giỏ hàng:\nID: ${idTC}\nSố lượng: ${soLuong}\nTổng tiền: ${tongTien}`);
        modal.style.display = 'none';
    });







