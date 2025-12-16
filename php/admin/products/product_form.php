<?php
require_once __DIR__ . '/../../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = $_GET['id'] ?? null;
$product = ['name'=>'','description'=>'','price'=>'0.00','stock'=>0,'is_active'=>1,'image'=>null, 'category_id' => null, 'brand_id' => null];

// --- BẮT ĐẦU THAY ĐỔI ---
// Nếu có lỗi từ session (do slug trùng), lấy lại dữ liệu cũ
if (isset($_SESSION['form_data'])) {
    $product = array_merge($product, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
} else if ($id) {
    // Nếu không có lỗi và có ID, lấy dữ liệu từ DB
    $stmt = $mysqli->prepare("SELECT id,name,description,price,stock,is_active,image,category_id,brand_id FROM products WHERE id = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) $product = $res->fetch_assoc();
    $stmt->close();
}

// Lấy danh sách danh mục
$categories = [];
$cat_res = $mysqli->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($cat_row = $cat_res->fetch_assoc()) $categories[] = $cat_row;

// Lấy danh sách thương hiệu
$brands = [];
$brand_res = $mysqli->query("SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name ASC");
while ($brand_row = $brand_res->fetch_assoc()) $brands[] = $brand_row;

// --- KẾT THÚC THAY ĐỔI ---
?>
<div class="white_shd full margin_bottom_30">
   <div class="full graph_head">
      <div class="heading1 margin_0">
         <h2><?= $id ? 'Sửa' : 'Thêm' ?> sản phẩm</h2>
      </div>
   </div>
   <div class="padding_infor_info">
      <?php
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
            <label>Mô tả ngắn</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
         </div>
         <div class="form-group">
            <label>Danh mục</label>
            <select name="category_id" class="form-control">
               <option value="">-- Chọn danh mục --</option>
               <?php foreach ($categories as $cat): ?>
               <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
               <?= htmlspecialchars($cat['name']) ?>
               </option>
               <?php endforeach; ?>
            </select>
         </div>
         <div class="form-group">
            <label>Thương hiệu</label>
            <select name="brand_id" class="form-control">
               <option value="">-- Chọn thương hiệu --</option>
               <?php foreach ($brands as $brand): ?>
               <option value="<?= $brand['id'] ?>" <?= ($product['brand_id'] == $brand['id']) ? 'selected' : '' ?>>
               <?= htmlspecialchars($brand['name']) ?>
               </option>
               <?php endforeach; ?>
            </select>
         </div>
         <div class="form-row">
            <div class="col"><label>Giá</label><input type="number" min=0 name="price" class="form-control" required value="<?= htmlspecialchars($product['price']) ?>"></div>

            <div class="col"><label>Kho</label><input type="number" min=0 name="stock" class="form-control" required value="<?= htmlspecialchars($product['stock']) ?>"></div>
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
            <div id="image-preview-container">
               <img id="image-preview" src="/baitap3/uploads/products/<?= rawurlencode($product['image']) ?>?v=<?= time() ?>" style="height:80px; background: #f0f0f0; border: 1px solid #ddd; padding: 2px;">
            </div>
            <?php else: ?>
            <div id="image-preview-container" style="display: none;">
               <img id="image-preview" src="#" style="height:80px; background: #f0f0f0; border: 1px solid #ddd; padding: 2px;">
            </div>
            <?php endif; ?>
            <input type="file" name="image" id="image-input" accept="image/jpeg,image/png" class="form-control-file mt-2">
         </div>
         <button class="btn btn-primary"><?= $id ? 'Cập nhật' : 'Tạo mới' ?></button>
         <a class="btn btn-secondary" href="/baitap3/php/login/admin.php?page=products">Quay lại</a>
      </form>
   </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    const imagePreviewContainer = document.getElementById('image-preview-container');

    imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            imagePreview.src = URL.createObjectURL(file);
            imagePreviewContainer.style.display = 'block';
        }
    });
});
</script>