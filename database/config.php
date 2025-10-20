<?php
/*
 * Thông tin kết nối Cơ sở dữ liệu
 * Thay đổi các giá trị này cho phù hợp với môi trường của bạn.
 */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Tên người dùng CSDL, thường là 'root' trên localhost
define('DB_PASSWORD', ''); // Mật khẩu CSDL, thường là trống trên XAMPP
define('DB_NAME', 'my_shop_db'); // Tên CSDL bạn đã tạo

/* Cố gắng kết nối đến CSDL MySQL */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Kiểm tra kết nối
if ($mysqli === false) {
    die("LỖI: Không thể kết nối. " . $mysqli->connect_error);
}

// Thiết lập bộ ký tự UTF-8 để hỗ trợ tiếng Việt
$mysqli->set_charset("utf8");
?>