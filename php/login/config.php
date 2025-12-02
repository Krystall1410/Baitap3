<?php

define('DB_SERVER', '127.0.0.1:3307'); 
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

