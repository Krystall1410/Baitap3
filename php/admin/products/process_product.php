<?php
require_once __DIR__ . '/../../login/config.php';

/**
 * Di chuyển tệp đã tải lên đến thư mục chỉ định với tên duy nhất.
 *
 * @param array $file Mảng tệp từ $_FILES (ví dụ: $_FILES['image']).
 * @param string $upload_dir Đường dẫn đến thư mục để lưu tệp.
 * @param array $allowed_extensions Mảng các phần mở rộng tệp được phép.
 * @return string|null Trả về tên tệp mới nếu thành công, ngược lại trả về null.
 */
function move_upload_file(array $file, string $upload_dir, array $allowed_extensions = ['jpg', 'jpeg', 'png']): ?string
{
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['form_error'] = "Lỗi: Đã xảy ra sự cố khi tải tệp lên. Mã lỗi: " . $file['error'];
        }
        return null;
    }
    $tmp_name = $file['tmp_name'];
    $original_name = basename($file['name']);
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        $_SESSION['form_error'] = "Lỗi: Định dạng tệp ảnh không hợp lệ. Chỉ chấp nhận tệp " . implode(', ', $allowed_extensions) . ".";
        return null;
    }
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        $_SESSION['form_error'] = "Lỗi: Không thể tạo thư mục tải lên. Vui lòng kiểm tra quyền ghi.";
        return null;
    }
    $new_filename = uniqid('p_') . '.' . $extension;
    return move_uploaded_file($tmp_name, $upload_dir . '/' . $new_filename) ? $new_filename : null;
}

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$description = $_POST['description'] ?? '';
$spec_material = trim($_POST['spec_material'] ?? '');
$spec_width = $_POST['spec_width'] ?? '';
$spec_height = $_POST['spec_height'] ?? '';
$spec_warranty = $_POST['spec_warranty'] ?? '';

// Chuẩn hóa số và ghép thông số lại thành chuỗi lưu DB
$spec_width = ($spec_width !== '') ? max(0, (float)str_replace(',', '.', $spec_width)) : null;
$spec_height = ($spec_height !== '') ? max(0, (float)str_replace(',', '.', $spec_height)) : null;
$spec_warranty = ($spec_warranty !== '') ? max(0, (float)str_replace(',', '.', $spec_warranty)) : null;

$spec_parts = [];
if ($spec_material !== '') {
    $spec_parts[] = 'Chất liệu: ' . $spec_material;
}
if ($spec_width !== null && $spec_height !== null) {
    $spec_parts[] = 'Kích thước: ' . rtrim(rtrim(number_format($spec_width, 2, '.', ''), '0'), '.') . 'cm x ' . rtrim(rtrim(number_format($spec_height, 2, '.', ''), '0'), '.') . 'cm';
}
if ($spec_warranty !== null) {
    $spec_parts[] = 'Bảo hành: ' . rtrim(rtrim(number_format($spec_warranty, 1, '.', ''), '0'), '.') . ' năm';
}
$specs = implode("\n", $spec_parts);
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;

$upload_dir = dirname(__DIR__, 3) . '/uploads/products';
$image_name = isset($_FILES['image']) ? move_upload_file($_FILES['image'], $upload_dir) : null;

// Đảm bảo có cột specs để lưu riêng thông số kỹ thuật
$specsColumn = $mysqli->query("SHOW COLUMNS FROM products LIKE 'specs'");
if ($specsColumn && $specsColumn->num_rows === 0) {
    $mysqli->query("ALTER TABLE products ADD COLUMN specs TEXT NULL AFTER description");
}
if ($specsColumn instanceof mysqli_result) {
    $specsColumn->free();
}

if ($id) {
    // Chỉnh sửa sản phẩm
    $old_image = null;
    $stmt_old = $mysqli->prepare("SELECT image FROM products WHERE id = ?");
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $stmt_old->bind_result($old_image);
    $stmt_old->fetch();
    $stmt_old->close();
    
    // Nếu có ảnh mới được tải lên thành công, và có ảnh cũ, thì xóa ảnh cũ đi.
    if ($image_name !== null && $old_image) {
        $old_image_path = $upload_dir . '/' . $old_image;
        if (is_file($old_image_path)) {
            @unlink($old_image_path);
        }
    } elseif ($image_name === null) {
        // Nếu không có ảnh mới được tải lên (hoặc tải lỗi), giữ lại ảnh cũ.
        $image_name = $old_image;
    }

    $stmt = $mysqli->prepare("UPDATE products SET name=?, description=?, specs=?, price=?, stock=?, category_id=?, brand_id=?, is_active=?, image=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssdiiiisi", $name, $description, $specs, $price, $stock, $category_id, $brand_id, $is_active, $image_name, $id);
} else {
    // Thêm mới sản phẩm
    $stmt = $mysqli->prepare("INSERT INTO products (name,description,specs,price,stock,category_id,brand_id,is_active,image) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssdiiiis", $name, $description, $specs, $price, $stock, $category_id, $brand_id, $is_active, $image_name);
}

if ($stmt) {
    $ok = $stmt->execute();
    $stmt->close();
    unset($_SESSION['form_data']);
    unset($_SESSION['form_error']);
}

header('Location: /baitap3/php/login/admin.php?page=products');
exit;