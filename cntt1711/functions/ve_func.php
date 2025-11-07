<?php
session_start();
include './db_connect.php';
$conn = getDbConnection();

$id_dv = $_POST['id_dv'];
$so_luong = $_POST['so_luong'];
$ngay_su_dung = $_POST['ngay_su_dung'];
$ghi_chu = $_POST['ghi_chu'];

$sql = "INSERT INTO ve (id_dv, so_luong, ngay_su_dung, ghi_chu, ngay_dat)
        VALUES ('$id_dv', '$so_luong', '$ngay_su_dung', '$ghi_chu', NOW())";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Đặt vé thành công!'); window.location.href='../dich_vu.php';</script>";
} else {
    echo "Lỗi: " . mysqli_error($conn);
}
