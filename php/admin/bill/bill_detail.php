<?php
require_once __DIR__ . '/../../login/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

$invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invoiceId <= 0) {
    header('Location: /baitap3/php/login/admin.php?page=bills');
    exit;
}

$hasShippingStatus = (function (mysqli $db): bool {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $check = $db->query("SHOW COLUMNS FROM invoices LIKE 'shipping_status'");
    if ($check instanceof mysqli_result) {
        $cached = $check->num_rows > 0;
        $check->free();
    } else {
        $cached = false;
    }
    return $cached;
})($mysqli);

$statusSelect = $hasShippingStatus ? "COALESCE(shipping_status, 'pending')" : "'pending'";
$stmt = $mysqli->prepare("SELECT invoices.*, {$statusSelect} AS shipping_status FROM invoices WHERE id = ?");
$stmt->bind_param('i', $invoiceId);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    $_SESSION['bill_flash'] = 'Không tìm thấy hoá đơn.';
    header('Location: /baitap3/php/login/admin.php?page=bills');
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

function labelFromCode(array $map, ?string $code): string
{
    if ($code === null) {
        return 'Không xác định';
    }

    $normalizedCode = (string)$code;

    return $map[$normalizedCode] ?? 'Không xác định';
}

function moneyFormat(float $amount): string
{
    return number_format($amount, 0, ',', '.') . ' VND';
}

$provinceMap = loadProvinceMap();
?>
<div class="row column1">
    <div class="col-md-12">
        <div class="white_shd full margin_bottom_30">
            <div class="full graph_head d-flex align-items-center justify-content-between">
                <div class="heading1 margin_0">
                    <h2>Hoá đơn #<?= (int)$invoice['id'] ?></h2>
                    <p class="mt-1 mb-0">Tạo lúc: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($invoice['created_at']))) ?></p>
                    <?php
                    $status = $invoice['shipping_status'] ?? 'pending';
                    $statusMap = [
                        'pending' => ['text' => 'Đang xử lý', 'class' => 'badge badge-warning'],
                        'shipping' => ['text' => 'Đang giao', 'class' => 'badge badge-info'],
                        'completed' => ['text' => 'Đã giao', 'class' => 'badge badge-success'],
                        'cancelled' => ['text' => 'Đã huỷ', 'class' => 'badge badge-secondary'],
                    ];
                    $meta = $statusMap[$status] ?? ['text' => ucfirst($status), 'class' => 'badge badge-light'];
                    ?>
                    <span class="<?= $meta['class'] ?> mt-2"><?= htmlspecialchars($meta['text']) ?></span>
                </div>
                <div>
                    <a href="admin.php?page=bills" class="btn btn-secondary">Trở lại danh sách</a>
                </div>
            </div>
            <div class="full padding_infor_info">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Thông tin khách hàng</h5>
                        <p><strong>Họ tên:</strong> <?= htmlspecialchars(trim($invoice['last_name'] . ' ' . $invoice['first_name'])) ?></p>
                        <?php if (!empty($invoice['company'])): ?>
                            <p><strong>Công ty:</strong> <?= htmlspecialchars($invoice['company']) ?></p>
                        <?php endif; ?>
                        <p><strong>Email:</strong> <?= htmlspecialchars($invoice['email']) ?></p>
                        <p><strong>SĐT:</strong> <?= htmlspecialchars($invoice['phone_number']) ?></p>
                        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($invoice['street_address']) ?>, <?= htmlspecialchars($invoice['city']) ?></p>
                        <p><strong>Khu vực:</strong> <?= htmlspecialchars(labelFromCode($provinceMap, $invoice['country'])) ?></p>
                        <?php if (!empty($invoice['zip_code'])): ?>
                            <p><strong>Mã ZIP:</strong> <?= htmlspecialchars($invoice['zip_code']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5>Thông tin giao hàng</h5>
                        <?php if (!empty($invoice['shipping_street_address']) || !empty($invoice['shipping_city']) || !empty($invoice['shipping_country'])): ?>
                            <p><strong>Họ tên:</strong> <?= htmlspecialchars(trim(($invoice['shipping_last_name'] ?? '') . ' ' . ($invoice['shipping_first_name'] ?? ''))) ?></p>
                            <?php if (!empty($invoice['shipping_street_address'])): ?>
                                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($invoice['shipping_street_address']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($invoice['shipping_city'])): ?>
                                <p><strong>Chi tiết:</strong> <?= htmlspecialchars($invoice['shipping_city']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($invoice['shipping_country'])): ?>
                                <p><strong>Khu vực:</strong> <?= htmlspecialchars(labelFromCode($provinceMap, $invoice['shipping_country'])) ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>Giống với thông tin thanh toán.</p>
                        <?php endif; ?>
                        <p><strong>Phương thức thanh toán:</strong> <?= $invoice['payment_method'] === 'paypal' ? 'Thẻ tín dụng' : 'Thanh toán khi nhận hàng' ?></p>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-striped">
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
                                    <td colspan="5" class="text-center">Không có sản phẩm nào trong hoá đơn.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $idx => $item): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= (int)$item['quantity'] ?></td>
                                        <td><?= moneyFormat((float)$item['unit_price']) ?></td>
                                        <td><?= moneyFormat((float)$item['total_price']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <?php if (!empty($invoice['comment'])): ?>
                            <h5>Ghi chú của khách</h5>
                            <div class="alert alert-secondary" role="alert"><?= nl2br(htmlspecialchars($invoice['comment'])) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <p><strong>Tạm tính:</strong> <?= moneyFormat((float)$invoice['subtotal']) ?></p>
                        <p><strong>Phí giao hàng:</strong> <?= moneyFormat((float)$invoice['shipping_fee']) ?></p>
                        <p><strong>Tổng thanh toán:</strong> <?= moneyFormat((float)$invoice['total_amount']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
