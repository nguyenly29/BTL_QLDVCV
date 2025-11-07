// --- Lấy modal và các element ---
const modalDatVe = document.getElementById('modalDatVe');
const modalXemChiTiet = document.getElementById('modalXemChiTiet');

const closeBtns = document.querySelectorAll('.modal .close');
const soLuongInput = document.getElementById('so_luong');
const tongTienInput = document.getElementById('tong_tien');
const giaVeInput = document.getElementById('gia_ve_modal');
const ngayMoBanInput = document.getElementById('ngay_mo_ban');
const addCartBtn = document.getElementById('addCartBtn');

// --- Event Delegation cho tất cả button ---
document.addEventListener('click', function(e){

    // --- Đặt Vé ---
    if(e.target.matches('.btn-datve')){
        e.preventDefault();
        const btn = e.target;
        const id = btn.dataset.id;
        const ten = btn.dataset.ten;
        const gia = parseFloat(btn.dataset.gia) || 0;
        const giam = parseFloat(btn.dataset.giam) || 0;

        // Cập nhật modal
        document.getElementById('id_tc_modal').value = id;
        giaVeInput.value = gia * (1 - giam);
        tongTienInput.value = (gia * (1 - giam)).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
        ngayMoBanInput.textContent = new Date().toLocaleDateString('vi-VN');
        soLuongInput.value = 1;

        modalDatVe.style.display = 'block';
    }

    // --- Xem Chi Tiết Dịch Vụ ---
    if(e.target.matches('.btn-xem')){
        e.preventDefault();
        const btn = e.target;
        const id = btn.dataset.id;
        const ten = btn.dataset.ten;
        
        // TODO: nếu có combo nhiều trò chơi, load AJAX hoặc DOM dynamically
        document.getElementById('xct_ten_dv').textContent = ten;
        modalXemChiTiet.style.display = 'block';
    }

    // --- Thêm Giỏ Hàng ---
    if(e.target.matches('#addCartBtn')){
        const id = document.getElementById('id_tc_modal').value;
        const soLuong = parseInt(soLuongInput.value);
        const tongTien = tongTienInput.value;
        alert(`Đã thêm vào giỏ hàng:\nID: ${id}\nSố lượng: ${soLuong}\nTổng tiền: ${tongTien}`);
        modalDatVe.style.display = 'none';
    }
});

// --- Tính tổng tiền khi thay đổi số lượng ---
soLuongInput.addEventListener('input', function(){
    const sl = parseInt(this.value);
    const gia = parseFloat(giaVeInput.value) || 0;
    tongTienInput.value = (sl * gia).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
});

// --- Đóng modal ---
closeBtns.forEach(btn => {
    btn.addEventListener('click', function(){
        this.closest('.modal').style.display = 'none';
    });
});

window.addEventListener('click', function(e){
    if(e.target.classList.contains('modal')){
        e.target.style.display = 'none';
    }
});

const modalCombo = document.getElementById('modalCombo');
const closeCombo = modalCombo.querySelector('.close');

document.querySelectorAll('.btn-xem').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const idDV = this.dataset.id;
        const tenDV = this.dataset.ten;

        document.getElementById('combo_ten').textContent = tenDV;
        document.getElementById('combo_mo_ta').textContent = "";

        const gamesContainer = document.getElementById('combo_games');
        gamesContainer.innerHTML = "Đang tải...";

        fetch(`get_combo.php?id_dv=${idDV}`)
        .then(res => res.json())
        .then(data => {
            const gamesContainer = document.getElementById('gamesContainer');
            if(data.length === 0) {
                gamesContainer.innerHTML = "<p>Combo hiện chưa có trò chơi nào</p>";
                return;
            }
            gamesContainer.innerHTML = '';
            data.forEach(game => {
                const div = document.createElement('div');
                div.className = 'game-item';
                div.innerHTML = `
                    <p>${game.ten_tc} - ${game.dia_diem} - ${parseFloat(game.gia_ve).toLocaleString('vi-VN')}₫</p>
                    <button class="buyNowBtn">Mua ngay</button>
                    <button class="addCartBtn">Thêm vào giỏ</button>
                `;
                gamesContainer.appendChild(div);
            });
        })
        .catch(err => {
            console.error(err);
            document.getElementById('gamesContainer').innerHTML = "<p>Lỗi khi tải dữ liệu</p>";
        });

    });
});

// Đóng modal
closeCombo.onclick = () => modalCombo.style.display = 'none';
window.onclick = e => { if(e.target == modalCombo) modalCombo.style.display = 'none'; }
