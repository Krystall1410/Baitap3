<?php
require_once __DIR__ . '/../../login/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

if ($id) {
    // Chỉnh sửa
    $stmt = $mysqli->prepare("UPDATE brands SET name=?, is_active=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sii", $name, $is_active, $id);
} else {
    // Thêm mới
    $stmt = $mysqli->prepare("INSERT INTO brands (name, is_active) VALUES (?,?)");
    $stmt->bind_param("si", $name, $is_active);
}

if ($stmt) {
    $stmt->execute();
    $stmt->close();
    unset($_SESSION['form_data']);
    unset($_SESSION['form_error']);
}

header('Location: /baitap3/php/login/admin.php?page=brands');
exit;