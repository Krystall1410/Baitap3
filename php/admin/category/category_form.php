<?php
require_once __DIR__ . '/../../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = $_GET['id'] ?? null;
$category = ['name'=>''];

// Ưu tiên dùng lại dữ liệu đã nhập để form giữ trạng thái khi phát sinh lỗi xác thực
if (isset($_SESSION['form_data'])) {
    $category = array_merge($category, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
} else if ($id) {
    $stmt = $mysqli->prepare("SELECT id, name FROM categories WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) $category = $res->fetch_assoc();
    $stmt->close();
}
?>
<div class="white_shd full margin_bottom_30">
   <div class="full graph_head">
      <div class="heading1 margin_0">
         <h2><?= $id ? 'Sửa' : 'Thêm' ?> danh mục</h2>
      </div>
   </div>
   <div class="padding_infor_info">
      <?php
      if (isset($_SESSION['form_error'])) {
          echo '<div class="alert alert-danger">' . $_SESSION['form_error'] . '</div>';
          unset($_SESSION['form_error']);
      }
      ?>
      <form action="/baitap3/php/login/admin.php?page=process_category" method="post">
         <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
         <div class="form-group">
            <label>Tên danh mục</label>
            <input name="name" class="form-control" required value="<?= htmlspecialchars($category['name']) ?>">
         </div>
         <button class="btn btn-primary"><?= $id ? 'Cập nhật' : 'Tạo mới' ?></button>
         <a class="btn btn-secondary" href="/baitap3/php/login/admin.php?page=categories">Quay lại</a>
      </form>
   </div>
</div>