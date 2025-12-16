<?php
require_once __DIR__ . '/../../login/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

$flashMessage = $_SESSION['bill_flash'] ?? '';
unset($_SESSION['bill_flash']);

$result = $mysqli->query("SELECT id, first_name, last_name, email, phone_number, total_amount, created_at FROM invoices ORDER BY created_at DESC");
?>
<div class="white_shd full margin_bottom_30">
    <div class="full graph_head d-flex align-items-center" style="gap: 16px;">
        <div class="heading1 margin_0">
            <h2>Hoá đơn — Danh sách</h2>
        </div>
    </div>
    <div class="padding_infor_info">
        <?php if (!empty($flashMessage)): ?>
            <div class="alert alert-info" role="alert"><?= htmlspecialchars($flashMessage) ?></div>
        <?php endif; ?>
        <div class="table-responsive-sm">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Tổng tiền</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars(trim($row['last_name'] . ' ' . $row['first_name'])) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone_number']) ?></td>
                                <td><?= number_format((float)$row['total_amount'], 0, ',', '.') ?> VND</td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['created_at']))) ?></td>
                                <td style="min-width: 160px;">
                                    <a class="btn btn-sm btn-primary" href="admin.php?page=bill_detail&id=<?= (int)$row['id'] ?>">Xem</a>
                                    <a class="btn btn-sm btn-danger" href="admin.php?page=delete_bill&id=<?= (int)$row['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xoá hoá đơn này?');">Xoá</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Chưa có hoá đơn nào được ghi nhận.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
