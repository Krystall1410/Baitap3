<?php
/*
Đây là tệp cấu hình cơ sở dữ liệu.
Hãy thay đổi các giá trị này cho phù hợp với môi trường của bạn.
*/

// Thông tin kết nối CSDL
define('DB_SERVER', '127.0.0.1:3307'); // <-- THAY ĐỔI QUAN TRỌNG Ở ĐÂY
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'my_shop_db');

/* Cố gắng kết nối đến cơ sở dữ liệu MySQL */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Kiểm tra kết nối
if($mysqli === false){
    die("LỖI: Không thể kết nối. " . $mysqli->connect_error);
}

// Thiết lập bộ ký tự UTF-8 để hỗ trợ tiếng Việt
$mysqli->set_charset("utf8");
?>

