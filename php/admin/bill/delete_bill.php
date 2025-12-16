<?php
require_once __DIR__ . '/../../login/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

$invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invoiceId <= 0) {
    $_SESSION['bill_flash'] = 'Thiếu mã hoá đơn để xoá.';
    header('Location: /baitap3/php/login/admin.php?page=bills');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$transactionStarted = false;

try {
    $mysqli->begin_transaction();
    $transactionStarted = true;

    $checkStmt = $mysqli->prepare('SELECT id FROM invoices WHERE id = ?');
    $checkStmt->bind_param('i', $invoiceId);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        $checkStmt->close();
        $mysqli->rollback();
        $_SESSION['bill_flash'] = 'Không tìm thấy hoá đơn cần xoá.';
        header('Location: /baitap3/php/login/admin.php?page=bills');
        exit;
    }
    $checkStmt->close();

    $itemStmt = $mysqli->prepare('DELETE FROM invoice_items WHERE invoice_id = ?');
    $itemStmt->bind_param('i', $invoiceId);
    $itemStmt->execute();
    $itemStmt->close();

    $billStmt = $mysqli->prepare('DELETE FROM invoices WHERE id = ?');
    $billStmt->bind_param('i', $invoiceId);
    $billStmt->execute();
    $billStmt->close();

    $mysqli->commit();
    $_SESSION['bill_flash'] = 'Đã xoá hoá đơn thành công.';
} catch (Throwable $e) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }
    error_log('Xoa hoa don that bai: ' . $e->getMessage());
    $_SESSION['bill_flash'] = 'Không thể xoá hoá đơn. Vui lòng thử lại.';
}

header('Location: /baitap3/php/login/admin.php?page=bills');
exit;
