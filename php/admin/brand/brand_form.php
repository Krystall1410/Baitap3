<?php
require_once __DIR__ . '/../../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = $_GET['id'] ?? null;
$brand = ['name'=>'','is_active'=>1];

if (isset($_SESSION['form_data'])) {
    $brand = array_merge($brand, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
} else if ($id) {
    $stmt = $mysqli->prepare("SELECT id, name, is_active FROM brands WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) $brand = $res->fetch_assoc();
    $stmt->close();
}
?>
<div class="white_shd full margin_bottom_30">
   <div class="full graph_head">
      <div class="heading1 margin_0">
         <h2><?= $id ? 'Sửa' : 'Thêm' ?> thương hiệu</h2>
      </div>
   </div>
   <div class="padding_infor_info">
      <?php
      if (isset($_SESSION['form_error'])) {
          echo '<div class="alert alert-danger">' . $_SESSION['form_error'] . '</div>';
          unset($_SESSION['form_error']);
      }
      ?>
      <form action="/baitap3/php/login/admin.php?page=process_brand" method="post">
         <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
         <div class="form-group">
            <label>Tên thương hiệu</label>
            <input name="name" class="form-control" required value="<?= htmlspecialchars($brand['name']) ?>">
         </div>
         <div class="form-group">
            <label>Hiển thị</label>
            <select name="is_active" class="form-control">
               <option value="1" <?= $brand['is_active'] ? 'selected' : '' ?>>Có</option>
               <option value="0" <?= !$brand['is_active'] ? 'selected' : '' ?>>Ẩn</option>
            </select>
         </div>
         <button class="btn btn-primary"><?= $id ? 'Cập nhật' : 'Tạo mới' ?></button>
         <a class="btn btn-secondary" href="/baitap3/php/login/admin.php?page=brands">Quay lại</a>
      </form>
   </div>
</div>