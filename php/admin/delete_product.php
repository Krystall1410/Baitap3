<?php
require_once __DIR__ . '/../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    // lấy tên ảnh để xóa file
    $stmt = $mysqli->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->bind_result($image);
    $stmt->fetch();
    $stmt->close();

    $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();

    if (!empty($image)) {
        $file = __DIR__ . '/../../uploads/products/' . $image;
        if (is_file($file)) @unlink($file);
    }
}

header('Location: /baitap3/php/login/admin.php?page=products');
exit;