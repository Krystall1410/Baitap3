<?php
require_once __DIR__ . '/../../login/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currentAdminId = (int)($_SESSION['id'] ?? 0);
$status = ['type' => 'error', 'message' => 'Không thể xóa tài khoản. Vui lòng thử lại.'];

if ($userId <= 0) {
    $status['message'] = 'Thiếu mã tài khoản để xóa.';
    $_SESSION['user_role_status'] = $status;
    header('Location: /baitap3/php/login/admin.php?page=users');
    exit;
}

if ($userId === $currentAdminId) {
    $status['message'] = 'Bạn không thể tự xóa tài khoản của chính mình.';
    $_SESSION['user_role_status'] = $status;
    header('Location: /baitap3/php/login/admin.php?page=users');
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $status = ['type' => 'success', 'message' => 'Đã xóa tài khoản thành công.'];
    } else {
        $status = ['type' => 'error', 'message' => 'Không tìm thấy tài khoản để xóa.'];
    }

    $stmt->close();
} catch (Throwable $e) {
    error_log('Xoa tai khoan that bai: ' . $e->getMessage());
    $status = ['type' => 'error', 'message' => 'Không thể xóa tài khoản. Vui lòng thử lại.'];
}

$_SESSION['user_role_status'] = $status;
header('Location: /baitap3/php/login/admin.php?page=users');
exit;
