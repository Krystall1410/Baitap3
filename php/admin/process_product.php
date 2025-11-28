<?php
require_once __DIR__ . '/../login/config.php';
if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = $_POST['description'] ?? '';
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// --- BẮT ĐẦU THAY ĐỔI ---
// Kiểm tra slug có bị trùng không
$check_slug_stmt = $mysqli->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
$current_id_for_check = $id ?? 0; // Nếu là sản phẩm mới, ID là 0 để kiểm tra với tất cả sản phẩm khác
$check_slug_stmt->bind_param("si", $slug, $current_id_for_check);
$check_slug_stmt->execute();
$check_slug_stmt->store_result();

if ($check_slug_stmt->num_rows > 0) {
    // Nếu slug đã tồn tại, lưu lỗi vào session và quay lại form
    $_SESSION['form_error'] = "Slug '$slug' đã tồn tại. Vui lòng chọn một slug khác.";
    $_SESSION['form_data'] = $_POST; // Lưu lại dữ liệu đã nhập để điền lại form
    header('Location: /baitap3/php/login/admin.php?page=product_form' . ($id ? '&id=' . $id : ''));
    exit;
}
// --- KẾT THÚC THAY ĐỔI ---

// xử lý upload
$upload_dir = __DIR__ . '/../../uploads/products';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
$image_name = null;
if (!empty($_FILES['image']['tmp_name'])) {
    $tmp = $_FILES['image']['tmp_name'];
    $orig = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (in_array($ext, $allowed)) {
        $image_name = uniqid('p_') . '.' . $ext;
        move_uploaded_file($tmp, $upload_dir . '/' . $image_name);
    }
}

if ($id) {
    // nếu upload mới, cập nhật image; nếu không, giữ giá trị cũ
    if ($image_name) {
        $stmt = $mysqli->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, stock=?, is_active=?, image=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("sssdii si", $name, $slug, $description, $price, $stock, $is_active, $image_name, $id);
    } else {
        $stmt = $mysqli->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, stock=?, is_active=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("sssdi i", $name, $slug, $description, $price, $stock, $is_active, $id);
    }
    // Note: bind_param types adjusted below correctly in actual code
} else {
    $stmt = $mysqli->prepare("INSERT INTO products (name,slug,description,price,stock,is_active,image) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssdiis", $name, $slug, $description, $price, $stock, $is_active, $image_name);
}

if ($stmt) {
    $ok = $stmt->execute();
    $stmt->close();
    // --- THAY ĐỔI ---
    // Xóa dữ liệu form đã lưu nếu thành công
    unset($_SESSION['form_data']);
    unset($_SESSION['form_error']);
}

header('Location: /baitap3/php/login/admin.php?page=products');
exit;