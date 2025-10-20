<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập và có phải là admin không
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải, chuyển hướng về trang đăng nhập
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        a { color: #fbb710; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Chào mừng, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Đây là trang quản trị. Chỉ có quản trị viên mới thấy được nội dung này.</p>
        <p><a href="logout.php">Đăng xuất</a></p>
    </div>
</body>
</html>
