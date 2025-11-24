<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập và có phải là admin không
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải, chuyển hướng về trang đăng nhập
    header("Location: login.php");
    exit;
}
?>
