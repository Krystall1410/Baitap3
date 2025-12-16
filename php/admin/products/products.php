<?php
require_once __DIR__ . '/../../login/config.php';

// kiểm tra admin
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

// phân trang
$perPage = 6;
$currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($currentPage < 1) {
   $currentPage = 1;
}

$totalResult = $mysqli->query("SELECT COUNT(*) AS total FROM products");
$totalRow = $totalResult ? $totalResult->fetch_assoc() : ['total' => 0];
$totalProducts = (int)($totalRow['total'] ?? 0);
$totalPages = $totalProducts > 0 ? (int)ceil($totalProducts / $perPage) : 1;
if ($currentPage > $totalPages) {
   $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;

// lấy danh sách phân trang
$stmt = $mysqli->prepare("SELECT p.id, p.name, p.price, p.stock, p.image, p.is_active, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$res = $stmt->get_result();
$stt = $offset + 1; // Bắt đầu số thứ tự theo trang
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
      <?php
      if (isset($stmt) && $stmt instanceof mysqli_stmt) {
         $stmt->close();
      }
      if (isset($totalResult) && $totalResult instanceof mysqli_result) {
         $totalResult->free();
      }
      ?>
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
      <?php if ($totalPages > 1): ?>
      <nav aria-label="Danh sách sản phẩm" class="mt-3">
         <ul class="pagination justify-content-end mb-0">
            <?php if ($currentPage > 1): ?>
            <li class="page-item">
               <a class="page-link" href="admin.php?page=products&p=<?= $currentPage - 1 ?>" aria-label="Trang trước">
                  <span aria-hidden="true">&laquo;</span>
               </a>
            </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
               <a class="page-link" href="admin.php?page=products&p=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
               <a class="page-link" href="admin.php?page=products&p=<?= $currentPage + 1 ?>" aria-label="Trang tiếp">
                  <span aria-hidden="true">&raquo;</span>
               </a>
            </li>
            <?php endif; ?>
         </ul>
      </nav>
      <?php endif; ?>
   </div>
</div>