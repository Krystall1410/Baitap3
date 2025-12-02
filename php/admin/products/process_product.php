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
    $destination = $upload_dir . '/' . $new_filename;

    if (move_uploaded_file($tmp_name, $destination)) {
        return $new_filename;
    } else {
        $_SESSION['form_error'] = "Lỗi: Không thể di chuyển tệp đã tải lên. Vui lòng kiểm tra quyền ghi của thư mục '{$upload_dir}'.";
        return null;
    }
}

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php'); exit;
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$description = $_POST['description'] ?? '';
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;

$upload_dir = __DIR__ . '/../../uploads/products';
$image_name = isset($_FILES['image']) ? move_upload_file($_FILES['image'], $upload_dir) : null;

// Nếu có lỗi xảy ra trong quá trình tải tệp (ví dụ: sai định dạng, không có quyền ghi),
// hàm move_upload_file sẽ trả về null và đặt một thông báo lỗi trong $_SESSION['form_error'].
// Chúng ta cần dừng xử lý, lưu lại dữ liệu đã nhập và chuyển hướng người dùng trở lại form.
if (isset($_SESSION['form_error'])) {
    // Lưu lại dữ liệu người dùng đã nhập vào session để điền lại form
    $_SESSION['form_data'] = $_POST;
    // Chuyển hướng trở lại trang form (thêm hoặc sửa)
    $redirect_url = '/baitap3/php/login/admin.php?page=product_form';
    if ($id) {
        $redirect_url .= '&id=' . $id;
    }
    header('Location: ' . $redirect_url);
    exit;
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

    $stmt = $mysqli->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, brand_id=?, is_active=?, image=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssdiisisi", $name, $description, $price, $stock, $category_id, $brand_id, $is_active, $image_name, $id);
} else {
    // Thêm mới sản phẩm
    $stmt = $mysqli->prepare("INSERT INTO products (name,description,price,stock,category_id,brand_id,is_active,image) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssdiisis", $name, $description, $price, $stock, $category_id, $brand_id, $is_active, $image_name);
}

if ($stmt) {
    $ok = $stmt->execute();
    $stmt->close();
    // Xóa dữ liệu form và lỗi chỉ khi thực thi thành công
    unset($_SESSION['form_data']);
    unset($_SESSION['form_error']);
}

header('Location: /baitap3/php/login/admin.php?page=products');
exit;