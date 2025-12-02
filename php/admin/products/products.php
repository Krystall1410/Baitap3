<?php
require_once __DIR__ . '/../../login/config.php';

// kiểm tra admin
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

// lấy danh sách
$sql = "
    SELECT p.id, p.name, p.price, p.stock, p.image, p.is_active, b.name as brand_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    ORDER BY p.created_at DESC
";
$res = $mysqli->query($sql);
$stt = 1; // Bắt đầu số thứ tự từ 1
?> 
<div class="white_shd full margin_bottom_30">
   <div class="full graph_head">
      <div class="heading1 margin_0">
         <h2>Danh sách sản phẩm</h2>
      </div>
      <div class="ml-auto">
         <a href="admin.php?page=product_form" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Thêm mới</a>
      </div>
   </div>
   <div class="table_section padding_infor_info">
      <div class="table-responsive-sm">
         <table class="table table-hover">
            <thead><tr><th>#</th><th>Tên</th><th>Thương hiệu</th><th>Giá</th><th>Kho</th><th>Ảnh</th><th>Hiển thị</th><th>Hành động</th></tr></thead>
            <tbody>
               <?php while($row = $res->fetch_assoc()): ?>
               <tr>
                  <td><?= $stt++ ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['brand_name'] ?? 'N/A') ?></td>
                  <td><?= number_format($row['price'], 0, ',', '.') ?> VND</td>
                  <td><?= $row['stock'] ?></td>
                  <td><?php if ($row['image']): ?><img src="/baitap3/uploads/products/<?= rawurlencode($row['image']) ?>" style="height:50px; background: #f0f0f0;"><?php endif; ?></td>
                  <td><?= $row['is_active'] ? 'Có' : 'Ẩn' ?></td>
                  <td>
                     <a class="btn btn-sm btn-secondary" href="admin.php?page=product_form&id=<?= $row['id'] ?>">Sửa</a>
                     <a class="btn btn-sm btn-danger" href="admin.php?page=delete_product&id=<?= $row['id'] ?>" onclick="return confirm('Xoá sản phẩm?')">Xoá</a>
                  </td>
               </tr>
               <?php endwhile; ?>
            </tbody>
         </table>
      </div>
   </div>
</div>