<?php
require_once __DIR__ . '/../../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $mysqli->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();
}

header('Location: /baitap3/php/login/admin.php?page=categories');
exit;