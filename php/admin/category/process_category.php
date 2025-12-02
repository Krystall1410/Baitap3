<?php

define('APP_ROOT', dirname(__DIR__, 3)); 
define('BASE_URL', '/baitap3');

require_once APP_ROOT . '/php/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/php/login/login.php');
    exit;
}

function create_slug($string) {
    $search = [
        'à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ằ', 'ắ', 'ẳ', 'ẵ', 'ặ', 'â', 'ầ', 'ấ', 'ẩ', 'ẫ', 'ậ',
        'đ',
        'è', 'é', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ề', 'ế', 'ể', 'ễ', 'ệ',
        'ì', 'í', 'ỉ', 'ĩ', 'ị',
        'ò', 'ó', 'ỏ', 'õ', 'ọ', 'ô', 'ồ', 'ố', 'ổ', 'ỗ', 'ộ', 'ơ', 'ờ', 'ớ', 'ở', 'ỡ', 'ợ',
        'ù', 'ú', 'ủ', 'ũ', 'ụ', 'ư', 'ừ', 'ứ', 'ử', 'ữ', 'ự',
        'ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ',
    ];
    $replace = [
        'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
        'd',
        'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
        'i', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
        'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
        'y', 'y', 'y', 'y', 'y',
    ];
    $string = str_replace($search, $replace, strtolower($string));
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$slug = create_slug($name);


$stmt_check = $mysqli->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
$check_id = $id ?? -1;
$stmt_check->bind_param("si", $slug, $check_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($res_check->num_rows > 0) {
    $_SESSION['form_error'] = "Lỗi: Tên danh mục này đã tồn tại (tạo ra slug '{$slug}' bị trùng). Vui lòng chọn tên khác.";
    $_SESSION['form_data'] = ['name' => $name];
    header('Location: ' . BASE_URL . '/php/admin.php?page=category_form' . ($id ? '&id='.$id : ''));
    exit;
}

if ($id) {

    $stmt = $mysqli->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $slug, $id);
} else {
  
    $stmt = $mysqli->prepare("INSERT INTO categories (name, slug) VALUES (?,?)");
    $stmt->bind_param("ss", $name, $slug);
}

if ($stmt) {
    $stmt->execute();
    $stmt->close();
    unset($_SESSION['form_data']);
    unset($_SESSION['form_error']);
}

header('Location: ' . BASE_URL . '/php/admin.php?page=categories');
exit;