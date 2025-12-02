<?php
require_once __DIR__ . '/../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = $_GET['id'] ?? null;
$product = ['name'=>'','slug'=>'','description'=>'','price'=>'0.00','stock'=>0,'is_active'=>1,'image'=>null];

// --- BẮT ĐẦU THAY ĐỔI ---
// Nếu có lỗi từ session (do slug trùng), lấy lại dữ liệu cũ
if (isset($_SESSION['form_data'])) {
    $product = array_merge($product, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
} else if ($id) {
    // Nếu không có lỗi và có ID, lấy dữ liệu từ DB
    $stmt = $mysqli->prepare("SELECT id,name,slug,description,price,stock,is_active,image FROM products WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) $product = $res->fetch_assoc();
    $stmt->close();
}
// --- KẾT THÚC THAY ĐỔI ---
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?= $id ? 'Sửa' : 'Thêm' ?> sản phẩm</title>
  <link rel="stylesheet" href="/baitap3/assets/css/bootstrap.min.css">
</head>
<body class="p-4">
  <h2><?= $id ? 'Sửa' : 'Thêm' ?> sản phẩm</h2>
  <?php
    // --- THAY ĐỔI ---
    // Hiển thị thông báo lỗi nếu có
    if (isset($_SESSION['form_error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['form_error'] . '</div>';
        unset($_SESSION['form_error']);
    }
  ?>
  <form action="/baitap3/php/login/admin.php?page=process_product" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
    <div class="form-group">
      <label>Tên</label>
      <input name="name" class="form-control" required value="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="form-group">
      <label>Slug</label>
      <input name="slug" class="form-control" required value="<?= htmlspecialchars($product['slug']) ?>">
    </div>
    <div class="form-group">
      <label>Mô tả ngắn</label>
      <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>
    <div class="form-row">
      <div class="col"><label>Giá</label><input name="price" class="form-control" required value="<?= htmlspecialchars($product['price']) ?>"></div>
      <div class="col"><label>Kho</label><input name="stock" class="form-control" required value="<?= htmlspecialchars($product['stock']) ?>"></div>
      <div class="col"><label>Hiển thị</label>
        <select name="is_active" class="form-control">
          <option value="1" <?= $product['is_active'] ? 'selected' : '' ?>>Có</option>
          <option value="0" <?= !$product['is_active'] ? 'selected' : '' ?>>Ẩn</option>
        </select>
      </div>
    </div>
    <div class="form-group mt-2">
      <label>Ảnh chính (jpg/png)</label>
      <?php if (!empty($product['image'])): ?>
        <div><img src="/baitap3/uploads/products/<?= rawurlencode($product['image']) ?>" style="height:80px"></div>
      <?php endif; ?>
      <input type="file" name="image" accept="image/*" class="form-control-file">
    </div>
    <button class="btn btn-primary"><?= $id ? 'Cập nhật' : 'Tạo mới' ?></button>
    <a class="btn btn-secondary" href="/baitap3/php/login/admin.php?page=products">Quay lại</a>
  </form>
</body>
</html>