<?php
require_once __DIR__ . '/../../login/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$newRole = trim($_POST['role'] ?? '');
$validRoles = ['admin', 'user'];

$status = ['type' => 'error', 'message' => 'Không thể cập nhật quyền. Vui lòng thử lại.'];

if (!$userId || !in_array($newRole, $validRoles, true)) {
    $status['message'] = 'Dữ liệu không hợp lệ.';
    $_SESSION['user_role_status'] = $status;
    header('Location: /baitap3/php/login/admin.php?page=users');
    exit;
}

$currentAdminId = (int)($_SESSION['id'] ?? 0);
if ($userId === $currentAdminId && $newRole !== 'admin') {
    $status['message'] = 'Bạn không thể hạ quyền tài khoản của chính mình.';
    $_SESSION['user_role_status'] = $status;
    header('Location: /baitap3/php/login/admin.php?page=users');
    exit;
}

$updateStmt = $mysqli->prepare('UPDATE users SET role = ? WHERE id = ?');
if ($updateStmt instanceof mysqli_stmt) {
    $updateStmt->bind_param('si', $newRole, $userId);

    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            $status = [
                'type' => 'success',
                'message' => 'Đã cập nhật quyền người dùng thành công.'
            ];
        } else {
            $status = [
                'type' => 'info',
                'message' => 'Quyền người dùng không thay đổi.'
            ];
        }
    } else {
        $status['message'] = 'Lỗi khi lưu thay đổi vào cơ sở dữ liệu.';
    }

    $updateStmt->close();
}

$_SESSION['user_role_status'] = $status;
header('Location: /baitap3/php/login/admin.php?page=users');
exit;
