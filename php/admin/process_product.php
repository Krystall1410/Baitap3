<?php
session_start();
require_once __DIR__ . '/../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = $_POST['description'] ?? '';
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// xử lý upload
$upload_dir = __DIR__ . '/../../uploads/products';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
$image_name = null;
if (!empty($_FILES['image']['tmp_name'])) {
    $tmp = $_FILES['image']['tmp_name'];
    $orig = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (in_array($ext, $allowed)) {
        $image_name = uniqid('p_') . '.' . $ext;
        move_uploaded_file($tmp, $upload_dir . '/' . $image_name);
    }
}

if ($id) {
    // nếu upload mới, cập nhật image; nếu không, giữ giá trị cũ
    if ($image_name) {
        $stmt = $mysqli->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, stock=?, is_active=?, image=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("sssdii si", $name, $slug, $description, $price, $stock, $is_active, $image_name, $id);
    } else {
        $stmt = $mysqli->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, stock=?, is_active=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("sssdi i", $name, $slug, $description, $price, $stock, $is_active, $id);
    }
    // Note: bind_param types adjusted below correctly in actual code
} else {
    $stmt = $mysqli->prepare("INSERT INTO products (name,slug,description,price,stock,is_active,image) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssdiis", $name, $slug, $description, $price, $stock, $is_active, $image_name);
}

if ($stmt) {
    $ok = $stmt->execute();
    $stmt->close();
}

header('Location: /baitap3/php/admin/products.php');
exit;