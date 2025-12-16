<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /baitap3/php/login/login.php?redirect_url=/baitap3/view/checkout.php');
    exit;
}

$invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invoiceId <= 0) {
    header('Location: /baitap3/view/checkout.php');
    exit;
}

$stmt = $mysqli->prepare('SELECT * FROM invoices WHERE id = ?');
$stmt->bind_param('i', $invoiceId);
$stmt->execute();
$invoiceResult = $stmt->get_result();
$invoice = $invoiceResult->fetch_assoc();
$stmt->close();

if (!$invoice) {
    header('Location: /baitap3/view/checkout.php');
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userId = $_SESSION['id'] ?? null;
if (!$isAdmin && $invoice['user_id'] !== $userId) {
    header('Location: /baitap3/view/checkout.php');
    exit;
}

$itemStmt = $mysqli->prepare('SELECT product_name, unit_price, quantity, total_price FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC');
$itemStmt->bind_param('i', $invoiceId);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();
$items = $itemResult->fetch_all(MYSQLI_ASSOC);
$itemStmt->close();

function loadProvinceMap(): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $cache = [
        'hanoi' => 'Hà Nội',
        'hcm' => 'Tp Hồ Chí Minh',
        'quangninh' => 'Quảng Ninh',
        'haiphong' => 'Hải Phòng',
        'dalat' => 'Đà Lạt',
        'danang' => 'Đà Nẵng',
        'phutho' => 'Phú Thọ',
        'other' => 'Khác'
    ];

    $apiUrl = 'https://provinces.open-api.vn/api/p/';

    $response = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    if ($response === null || $response === false) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
            ]
        ]);
        $response = @file_get_contents($apiUrl, false, $context);
    }

    if ($response !== false && $response !== null) {
        $decoded = json_decode($response, true);
        $items = [];

        if (is_array($decoded)) {
            $items = $decoded;
        } elseif (is_array($decoded['results'] ?? null)) {
            $items = $decoded['results'];
        }

        foreach ($items as $province) {
            if (!is_array($province)) {
                continue;
            }
            $code = isset($province['code']) ? (string)$province['code'] : '';
            $name = $province['name'] ?? ($province['full_name'] ?? '');
            if ($code !== '' && $name !== '') {
                $cache[$code] = $name;
            }
            $codename = isset($province['codename']) ? (string)$province['codename'] : '';
            if ($codename !== '' && $name !== '' && !isset($cache[$codename])) {
                $cache[$codename] = $name;
            }
        }
    }

    return $cache;
}

function resolveLabel(array $map, ?string $key, string $default = 'Không xác định'): string
{
    if ($key === null) {
        return $default;
    }

    $normalizedKey = (string)$key;

    return $map[$normalizedKey] ?? $default;
}

function formatCurrency(float $amount): string
{
    return number_format($amount, 0, ',', '.') . ' VND';
}

$provinceMap = loadProvinceMap();
$billingCountry = resolveLabel($provinceMap, $invoice['country']);
$shippingCountry = $invoice['shipping_country'] ? resolveLabel($provinceMap, $invoice['shipping_country']) : null;

?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Hoá đơn #<?= htmlspecialchars($invoiceId) ?></title>
    <link rel="icon" href="../img/core-img/favicon.ico">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .invoice-wrapper {
            max-width: 960px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        @media print {
            body { background: #fff; }
            .invoice-wrapper { box-shadow: none; margin: 0; }
            .no-print { display: none !important; }
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .invoice-header h2 { margin: 0; }
        .info-block { margin-bottom: 25px; }
        .info-block h5 { text-transform: uppercase; letter-spacing: 1px; font-size: 14px; color: #888; margin-bottom: 10px; }
        .info-block p { margin: 0 0 6px; }
        .table thead th { background: #f8f9fa; border-top: none; }
        .table td, .table th { padding: 12px; font-size: 15px; }
        .total-row td { font-weight: 600; }
    </style>
</head>
<body style="background: #f4f4f4;">
    <div class="invoice-wrapper">
        <div class="invoice-header">
            <div>
                <h2>Hoá đơn #<?= htmlspecialchars($invoiceId) ?></h2>
                <p>Ngày tạo: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($invoice['created_at']))) ?></p>
            </div>
            <div class="no-print">
                <button class="btn amado-btn" onclick="window.print();">In hoá đơn</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 info-block">
                <h5>Thông tin thanh toán</h5>
                <p><?= htmlspecialchars($invoice['last_name'] . ' ' . $invoice['first_name']) ?></p>
                <?php if (!empty($invoice['company'])): ?>
                    <p>Công ty: <?= htmlspecialchars($invoice['company']) ?></p>
                <?php endif; ?>
                <p>Email: <?= htmlspecialchars($invoice['email']) ?></p>
                <p>Điện thoại: <?= htmlspecialchars($invoice['phone_number']) ?></p>
                <p>Địa chỉ: <?= htmlspecialchars($invoice['street_address']) ?>, <?= htmlspecialchars($invoice['city']) ?></p>
                <p>Khu vực: <?= htmlspecialchars($billingCountry) ?></p>
                <?php if (!empty($invoice['zip_code'])): ?>
                    <p>Mã ZIP: <?= htmlspecialchars($invoice['zip_code']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6 info-block">
                <h5>Thông tin giao hàng</h5>
                <?php if ($shippingCountry !== null || !empty($invoice['shipping_street_address'])): ?>
                    <p><?= htmlspecialchars(trim(($invoice['shipping_last_name'] ?? '') . ' ' . ($invoice['shipping_first_name'] ?? ''))) ?></p>
                    <?php if (!empty($invoice['shipping_street_address'])): ?>
                        <p>Địa chỉ: <?= htmlspecialchars($invoice['shipping_street_address']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($invoice['shipping_city'])): ?>
                        <p>Chi tiết: <?= htmlspecialchars($invoice['shipping_city']) ?></p>
                    <?php endif; ?>
                    <?php if ($shippingCountry !== null): ?>
                        <p>Khu vực: <?= htmlspecialchars($shippingCountry) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                <p>Phương thức: <?= $invoice['payment_method'] === 'paypal' ? 'Thẻ tín dụng' : 'Thanh toán khi nhận hàng' ?></p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Hoá đơn chưa có sản phẩm.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td><?= formatCurrency((float)$item['unit_price']) ?></td>
                                <td><?= formatCurrency((float)$item['total_price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" class="text-right">Tạm tính</td>
                        <td><?= formatCurrency((float)$invoice['subtotal']) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" class="text-right">Phí giao hàng</td>
                        <td><?= formatCurrency((float)$invoice['shipping_fee']) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" class="text-right">Tổng thanh toán</td>
                        <td><?= formatCurrency((float)$invoice['total_amount']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (!empty($invoice['comment'])): ?>
            <div class="info-block">
                <h5>Ghi chú</h5>
                <p><?= nl2br(htmlspecialchars($invoice['comment'])) ?></p>
            </div>
        <?php endif; ?>

        <div class="no-print" style="margin-top: 30px;">
            <a href="/baitap3/view/shop.php" class="btn amado-btn">Tiếp tục mua sắm</a>
            <?php if ($isAdmin): ?>
                <a href="/baitap3/php/login/admin.php?page=bills" class="btn amado-btn" style="margin-left: 10px;">Quản lí hoá đơn</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/jquery/jquery-2.2.4.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>
