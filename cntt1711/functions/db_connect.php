<?php
function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "1234567";
    $dbname = "qldvcv";
    $port = 3306;

    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        die("❌ Kết nối database thất bại: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
    return $conn;
}
?>
