<?php
require_once __DIR__ . '/../login/config.php';

// kiểm tra admin
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

// lấy danh sách
$sql = "SELECT id, name, price, stock, image, is_active FROM products ORDER BY created_at DESC";
$res = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Admin - Quản lý sản phẩm</title>
  <link rel="stylesheet" href="/baitap3/assets/css/bootstrap.min.css">
</head>
<body class="p-4">
  <h2>Danh sách sản phẩm <a href="admin.php?page=product_form" class="btn btn-sm btn-primary">Thêm mới</a></h2>
  <table class="table table-striped">
    <thead><tr><th>#</th><th>Tên</th><th>Giá</th><th>Kho</th><th>Ảnh</th><th>Hiển thị</th><th>Hành động</th></tr></thead>
    <tbody>
      <?php while($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= number_format($row['price'],2) ?></td>
          <td><?= $row['stock'] ?></td>
          <td>
            <?php if ($row['image']): ?>
              <img src="/baitap3/uploads/products/<?= rawurlencode($row['image']) ?>" style="height:50px">
            <?php endif; ?>
          </td>
          <td><?= $row['is_active'] ? 'Có' : 'Ẩn' ?></td>
          <td>
            <a class="btn btn-sm btn-secondary" href="admin.php?page=product_form&id=<?= $row['id'] ?>">Sửa</a>
            <a class="btn btn-sm btn-danger" href="admin.php?page=delete_product&id=<?= $row['id'] ?>" onclick="return confirm('Xoá sản phẩm?')">Xoá</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>