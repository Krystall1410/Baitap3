<?php
session_start();

// Hủy tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Xóa cookie "remember me" nếu có
if (isset($_COOKIE['username'])) {
    unset($_COOKIE['username']);
    setcookie('username', '', time() - 3600, '/'); 
}
if (isset($_COOKIE['password'])) {
    unset($_COOKIE['password']);
    setcookie('password', '', time() - 3600, '/');
}


// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit;
?>
