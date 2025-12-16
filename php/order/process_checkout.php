<?php
session_start();
require_once __DIR__ . '/../login/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /baitap3/view/checkout.php');
    exit;
}

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['checkout_error'] = 'Vui lòng đăng nhập trước khi thanh toán.';
    header('Location: /baitap3/php/login/login.php?redirect_url=/baitap3/view/checkout.php');
    exit;
}

$cartItems = $_SESSION['cart'] ?? [];
if (empty($cartItems)) {
    $_SESSION['checkout_error'] = 'Giỏ hàng của bạn đang trống.';
    header('Location: /baitap3/view/cart.php');
    exit;
}

$fields = [
    'first_name',
    'last_name',
    'company',
    'email',
    'country',
    'street_address',
    'city',
    'zip_code',
    'phone_number',
    'comment',
    'shipping_first_name',
    'shipping_last_name',
    'shipping_country',
    'shipping_street_address',
    'shipping_city'
];

$data = [];
foreach ($fields as $field) {
    $data[$field] = trim($_POST[$field] ?? '');
}

$shipToDifferent = isset($_POST['ship_to_different']);
$paymentMethod = $_POST['payment_method'] ?? 'cod';
$allowedMethods = ['cod', 'paypal'];
if (!in_array($paymentMethod, $allowedMethods, true)) {
    $paymentMethod = 'cod';
}

$_SESSION['checkout_form'] = $data;
$_SESSION['checkout_form']['ship_to_different'] = $shipToDifferent ? '1' : '0';
$_SESSION['checkout_form']['payment_method'] = $paymentMethod;

$requiredFields = ['first_name', 'last_name', 'email', 'country', 'street_address', 'city', 'phone_number'];
$errors = [];
foreach ($requiredFields as $field) {
    if ($data[$field] === '') {
        $errors[] = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
        break;
    }
}
if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ.';
}
if ($data['phone_number'] === '') {
    $errors[] = 'Vui lòng cung cấp số điện thoại.';
}

$subtotal = 0.0;
foreach ($cartItems as $item) {
    $price = isset($item['price']) ? (float)$item['price'] : 0.0;
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
    if ($quantity < 1) {
        $quantity = 1;
    }
    $subtotal += $price * $quantity;
}
$subtotal = max($subtotal, 0);
$shippingFee = 0.0;
$totalAmount = $subtotal + $shippingFee;

if ($subtotal <= 0) {
    $errors[] = 'Không thể tạo hoá đơn cho đơn hàng rỗng.';
}

if (!empty($errors)) {
    $_SESSION['checkout_error'] = implode(' ', array_unique($errors));
    header('Location: /baitap3/view/checkout.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$transactionStarted = false;

try {
    $mysqli->query('SET NAMES utf8mb4');

    $mysqli->begin_transaction();
    $transactionStarted = true;

    $userId = $_SESSION['id'] ?? null;
    $stmt = $mysqli->prepare("INSERT INTO invoices (
        user_id, first_name, last_name, company, email, country, street_address, city, zip_code, phone_number,
        comment, shipping_first_name, shipping_last_name, shipping_country, shipping_street_address, shipping_city,
        payment_method, subtotal, shipping_fee, total_amount
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $company = $data['company'] !== '' ? $data['company'] : null;
    $zipCode = $data['zip_code'] !== '' ? $data['zip_code'] : null;
    $comment = $data['comment'] !== '' ? $data['comment'] : null;
    $shippingFirst = $shipToDifferent && $data['shipping_first_name'] !== '' ? $data['shipping_first_name'] : null;
    $shippingLast = $shipToDifferent && $data['shipping_last_name'] !== '' ? $data['shipping_last_name'] : null;
    $shippingCountry = $shipToDifferent && $data['shipping_country'] !== '' ? $data['shipping_country'] : null;
    $shippingStreet = $shipToDifferent && $data['shipping_street_address'] !== '' ? $data['shipping_street_address'] : null;
    $shippingCity = $shipToDifferent && $data['shipping_city'] !== '' ? $data['shipping_city'] : null;

    $typeString = 'i' . str_repeat('s', 16) . 'ddd';
    $stmt->bind_param(
        $typeString,
        $userId,
        $data['first_name'],
        $data['last_name'],
        $company,
        $data['email'],
        $data['country'],
        $data['street_address'],
        $data['city'],
        $zipCode,
        $data['phone_number'],
        $comment,
        $shippingFirst,
        $shippingLast,
        $shippingCountry,
        $shippingStreet,
        $shippingCity,
        $paymentMethod,
        $subtotal,
        $shippingFee,
        $totalAmount
    );
    $stmt->execute();
    $invoiceId = $mysqli->insert_id;
    $stmt->close();

    $itemStmt = $mysqli->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, unit_price, quantity, total_price) VALUES (?,?,?,?,?,?)");
    $stockStmt = $mysqli->prepare("UPDATE products SET stock = CASE WHEN stock IS NULL THEN NULL ELSE stock - ? END WHERE id = ? AND (stock IS NULL OR stock >= ?)");

    foreach ($cartItems as $item) {
        $productId = isset($item['id']) ? (int)$item['id'] : null;
        $name = isset($item['name']) ? $item['name'] : 'Sản phẩm';
        $price = isset($item['price']) ? (float)$item['price'] : 0.0;
        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
        if ($quantity < 1) {
            $quantity = 1;
        }
        $lineTotal = $price * $quantity;

        if ($productId) {
            $stockStmt->bind_param('iii', $quantity, $productId, $quantity);
            $stockStmt->execute();
            if ($stockStmt->affected_rows === 0) {
                throw new RuntimeException('Sản phẩm "' . $name . '" không đủ số lượng trong kho.');
            }
        }

        $itemStmt->bind_param('iisdid', $invoiceId, $productId, $name, $price, $quantity, $lineTotal);
        $itemStmt->execute();
    }
    $itemStmt->close();
    $stockStmt->close();

    $mysqli->commit();

    unset($_SESSION['checkout_form']);
    unset($_SESSION['checkout_error']);
    $_SESSION['last_invoice_id'] = $invoiceId;
    $_SESSION['cart'] = [];

    header('Location: /baitap3/view/invoice.php?id=' . $invoiceId);
    exit;
} catch (Throwable $e) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }
    error_log('Checkout error: ' . $e->getMessage());
    $customerMessage = 'Không thể xử lý đơn hàng. Vui lòng thử lại.';
    if ($e instanceof RuntimeException) {
        $customerMessage = $e->getMessage();
    }
    $_SESSION['checkout_error'] = $customerMessage;
    header('Location: /baitap3/view/checkout.php');
    exit;
}
