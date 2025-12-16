<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode([
		'success' => false,
		'message' => 'Invalid request method.'
	]);
	exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$requestedQuantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($productId <= 0 || $requestedQuantity <= 0) {
	echo json_encode([
		'success' => false,
		'message' => 'Dữ liệu không hợp lệ.'
	]);
	exit;
}

if (!isset($_SESSION['cart'][$productId])) {
	echo json_encode([
		'success' => false,
		'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'
	]);
	exit;
}

require_once __DIR__ . '/../php/login/config.php';

$stmt = $mysqli->prepare('SELECT price, stock FROM products WHERE id = ? LIMIT 1');
if (!$stmt) {
	echo json_encode([
		'success' => false,
		'message' => 'Không thể chuẩn bị truy vấn.'
	]);
	exit;
}

$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
	echo json_encode([
		'success' => false,
		'message' => 'Không tìm thấy sản phẩm.'
	]);
	exit;
}

$availableStock = isset($product['stock']) ? (int)$product['stock'] : null;

if ($availableStock !== null) {
	if ($availableStock <= 0) {
		unset($_SESSION['cart'][$productId]);
		$subtotal = 0;
		foreach ($_SESSION['cart'] as $item) {
			$itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
			$itemQty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
			$subtotal += $itemPrice * $itemQty;
		}

		echo json_encode([
			'success' => true,
			'removed' => true,
			'subtotal' => $subtotal,
			'total' => $subtotal,
			'stock' => 0,
		]);
		exit;
	}

	if ($requestedQuantity > $availableStock) {
		$requestedQuantity = $availableStock;
	}
}

$_SESSION['cart'][$productId]['quantity'] = $requestedQuantity;
$_SESSION['cart'][$productId]['price'] = (float)$product['price'];
$_SESSION['cart'][$productId]['stock'] = $availableStock;

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
	$itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
	$itemQty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
	$subtotal += $itemPrice * $itemQty;
}

$response = [
	'success' => true,
	'quantity' => $requestedQuantity,
	'stock' => $availableStock,
	'price' => (float)$product['price'],
	'subtotal' => $subtotal,
	'total' => $subtotal
];

echo json_encode($response);