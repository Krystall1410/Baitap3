<?php
require_once __DIR__ . '/../../login/config.php';

// kiểm tra admin
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

// Lấy toàn bộ thương hiệu để hiển thị trong bảng quản trị
$sql = "SELECT id, name, is_active FROM brands ORDER BY name ASC";
$res = $mysqli->query($sql);
$stt = 1; // Bắt đầu số thứ tự từ 1
?>
<div class="white_shd full margin_bottom_30">
   <div class="full graph_head">
      <div class="heading1 margin_0">
         <h2>Danh sách thương hiệu</h2>
      </div>
      <div class="ml-auto">
         <a href="admin.php?page=brand_form" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Thêm mới</a>
      </div>
   </div>
   <div class="table_section padding_infor_info">
      <div class="table-responsive-sm">
         <table class="table table-hover">
            <thead><tr><th>#</th><th>Tên</th><th>Hiển thị</th><th>Hành động</th></tr></thead>
            <tbody>
               <?php while($row = $res->fetch_assoc()): ?>
               <tr>
                  <td><?= $stt++ ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= $row['is_active'] ? 'Có' : 'Ẩn' ?></td>
                  <td>
                     <a class="btn btn-sm btn-secondary" href="admin.php?page=brand_form&id=<?= $row['id'] ?>">Sửa</a>
                     <a class="btn btn-sm btn-danger" href="admin.php?page=delete_brand&id=<?= $row['id'] ?>" onclick="return confirm('Xoá thương hiệu này?')">Xoá</a>
                  </td>
               </tr>
               <?php endwhile; ?>
            </tbody>
         </table>
      </div>
   </div>
</div>