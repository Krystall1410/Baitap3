<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';

$checkoutForm = $_SESSION['checkout_form'] ?? [];
$checkoutError = $_SESSION['checkout_error'] ?? '';
unset($_SESSION['checkout_form'], $_SESSION['checkout_error']);

$provinceFallbackList = [
    ['value' => 'hanoi', 'label' => 'Hà Nội'],
    ['value' => 'hcm', 'label' => 'Tp Hồ Chí Minh'],
    ['value' => 'quangninh', 'label' => 'Quảng Ninh'],
    ['value' => 'haiphong', 'label' => 'Hải Phòng'],
    ['value' => 'dalat', 'label' => 'Đà Lạt'],
    ['value' => 'danang', 'label' => 'Đà Nẵng'],
    ['value' => 'phutho', 'label' => 'Phú Thọ'],
    ['value' => 'other', 'label' => 'Khác']
];

function renderProvinceOptions(array $options, ?string $selected): string
{
    $selected = (string)($selected ?? '');
    $html = '<option value="" disabled' . ($selected === '' ? ' selected' : '') . '>Chọn tỉnh/thành</option>';

    foreach ($options as $item) {
        if (empty($item['value']) || empty($item['label'])) {
            continue;
        }

        $rawValue = (string)$item['value'];
        $valueAttr = htmlspecialchars($rawValue, ENT_QUOTES, 'UTF-8');
        $labelAttr = htmlspecialchars((string)$item['label'], ENT_QUOTES, 'UTF-8');
        $isSelected = $selected === $rawValue ? ' selected' : '';
        $html .= '<option value="' . $valueAttr . '"' . $isSelected . '>' . $labelAttr . '</option>';
    }

    return $html;
}


$cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
$checkoutNotice = '';

if (!empty($cartItems)) {
    $ids = array_values(array_filter(array_map('intval', array_keys($cartItems)), static function ($id) {
        return $id > 0;
    }));

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $mysqli->prepare("SELECT id, price, stock, is_active FROM products WHERE id IN ($placeholders)");

        if ($stmt instanceof mysqli_stmt) {
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();
            $productData = [];

            while ($row = $result->fetch_assoc()) {
                $productData[(int)$row['id']] = $row;
            }

            $stmt->close();

            $validItems = [];
            $removed = 0;

            foreach ($cartItems as $rawId => $cartItem) {
                $intId = (int)$rawId;
                if (!isset($productData[$intId])) {
                    $removed++;
                    continue;
                }

                $prodRow = $productData[$intId];
                if ((int)$prodRow['is_active'] !== 1) {
                    $removed++;
                    continue;
                }

                $cartItem['price'] = isset($prodRow['price']) ? (float)$prodRow['price'] : ($cartItem['price'] ?? 0);
                $cartItem['stock'] = array_key_exists('stock', $prodRow) ? ($prodRow['stock'] !== null ? (int)$prodRow['stock'] : null) : ($cartItem['stock'] ?? null);
                $validItems[$intId] = $cartItem;
            }

            if ($removed > 0) {
                $checkoutNotice = 'Một số sản phẩm đã bị loại khỏi đơn hàng vì hiện không còn hiển thị.';
            }

            $_SESSION['cart'] = $validItems;
            $cartItems = $validItems;
        }
    }
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = isset($item['price']) ? (float)$item['price'] : 0;
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
    $subtotal += $price * $quantity;
}
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">

    
    <title>Nội thất đẹp Raumania|Thông tin thanh toán</title>

    
    <link rel="icon" href="../img/core-img/favicon.ico">

    
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>
    
    <div class="search-wrapper section-padding-100">
        <div class="search-close">
            <i class="fa fa-close" aria-hidden="true"></i>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="search-content">
                        <form action="shop.php" method="get">
                            <input type="search" name="search" id="search" placeholder="Nhập từ khóa...">
                            <button type="submit"><img src="../img/core-img/search.png" alt=""></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    
    <div class="main-content-wrapper d-flex clearfix">

        
        <div class="mobile-nav">
            
            <div class="amado-navbar-brand">
                <a href="index.php"><img src="../img/core-img/logo.png" alt=""></a>
            </div>
            
            <div class="amado-navbar-toggler">
                <span></span><span></span><span></span>
            </div>
        </div>

        
        <header class="header-area clearfix">
            
            <div class="nav-close">
                <i class="fa fa-close" aria-hidden="true"></i>
            </div>
            
            <div class="logo">
                <a href="index.php"><img src="../img/core-img/logo.png" alt=""></a>
            </div>
            
            <nav class="amado-nav">
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="shop.php">Cửa hàng</a></li>
                    
                    
                    <li class="active"><a href="checkout.php">Thông tin thanh toán</a></li>
                </ul>
            </nav>
            
            <!-- <div class="amado-btn-group mt-30 mb-100">
                <a href="#" class="btn amado-btn mb-15">%Giảm giá%</a>
                <a href="shop.php" class="btn amado-btn active">Sản phẩm mới</a>
            </div> -->
            
            <div class="cart-fav-search mb-100">
                <a href="cart.php" class="cart-nav"><img src="../img/core-img/cart.png" alt=""> Giỏ hàng <span>(<?php
                        $cart_count = 0; 
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                
                                $cart_count += isset($item['quantity']) ? (int)$item['quantity'] : 0;
                            }
                        }
                        echo $cart_count;
                    ?>)</span></a>
                <a href="favorites.php" class="fav-nav"><img src="../img/core-img/favorites.png" alt=""> Yêu thích</a>
                <a href="#" class="search-nav"><img src="../img/core-img/search.png" alt=""> Tìm kiếm</a>
                <div class="dropdown" style="display: inline-block;">
                    <a href="#" class="account-nav dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="../img/core-img/user.png" alt=""> My Account
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a class="dropdown-item" href="/baitap3/php/login/admin.php">Admin</a>
                            <?php endif; ?>
                            <a class="dropdown-item" href="../php/login/logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a class="dropdown-item" href="../php/login/login.php">Đăng nhập</a>
                            <a class="dropdown-item" href="../php/login/register.php">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="social-info d-flex justify-content-between">
                <a href="#"><i class="fa fa-pinterest" aria-hidden="true"></i></a>
                <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                <a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>
            </div>
        </header>
        

        <div class="cart-table-area section-padding-100">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <div class="checkout_details_area mt-50 clearfix">

                            <div class="cart-title">
                                <h2>Thông tin thanh toán</h2>
                            </div>

                            <?php if (!empty($checkoutError)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($checkoutError) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($checkoutNotice !== ''): ?>
                                <div class="alert alert-warning" role="alert">
                                    <?= htmlspecialchars($checkoutNotice) ?>
                                </div>
                            <?php endif; ?>

                            <form action="../php/order/process_checkout.php" method="post" id="checkoutForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($checkoutForm['first_name'] ?? '') ?>" placeholder="Tên" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($checkoutForm['last_name'] ?? '') ?>" placeholder="Họ" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control" id="company" name="company" placeholder="Tên công ty" value="<?= htmlspecialchars($checkoutForm['company'] ?? '') ?>">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($checkoutForm['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <select
                                            class="w-100"
                                            id="country"
                                            name="country"
                                            required
                                        >
                                            <?= renderProvinceOptions($provinceFallbackList, $checkoutForm['country'] ?? '') ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control mb-3" id="street_address" name="street_address" placeholder="Quận/Huyện" value="<?= htmlspecialchars($checkoutForm['street_address'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control" id="city" name="city" placeholder="Chi tiết địa chỉ" value="<?= htmlspecialchars($checkoutForm['city'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" id="zipCode" name="zip_code" placeholder="Mã ZIP" value="<?= htmlspecialchars($checkoutForm['zip_code'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input
                                            type="tel"
                                            class="form-control"
                                            id="phone_number"
                                            name="phone_number"
                                            placeholder="SĐT liên hệ"
                                            value="<?= htmlspecialchars($checkoutForm['phone_number'] ?? '') ?>"
                                            inputmode="numeric"
                                            pattern="^[0-9]+$"
                                            title="Vui lòng chỉ nhập số"
                                            required
                                        >
                                    </div>
                                    <div class="col-12 mb-3">
                                        <textarea name="comment" class="form-control w-100" id="comment" cols="30" rows="10" placeholder="Chú thích cho đơn hàng của bạn"><?= htmlspecialchars($checkoutForm['comment'] ?? '') ?></textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="custom-control custom-checkbox d-block">
                                            <input type="checkbox" class="custom-control-input" id="customCheck3" name="ship_to_different" <?= (isset($checkoutForm['ship_to_different']) && $checkoutForm['ship_to_different'] === '1') ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="customCheck3">Giao tới địa chỉ khác</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4" id="shipping_address_form" style="display: none;">
                                        <hr>
                                        <h4 class="mb-3">Địa chỉ giao hàng khác</h4>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <input type="text" class="form-control" placeholder="Tên" name="shipping_first_name" value="<?= htmlspecialchars($checkoutForm['shipping_first_name'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <input type="text" class="form-control" placeholder="Họ" name="shipping_last_name" value="<?= htmlspecialchars($checkoutForm['shipping_last_name'] ?? '') ?>">
                                            </div>
                                            <div class="col-12 mb-3">
                                                <select
                                                    class="w-100"
                                                    name="shipping_country"
                                                >
                                                    <?= renderProvinceOptions($provinceFallbackList, $checkoutForm['shipping_country'] ?? '') ?>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <input type="text" class="form-control" placeholder="Quận/Huyện" name="shipping_street_address" value="<?= htmlspecialchars($checkoutForm['shipping_street_address'] ?? '') ?>">
                                            </div>
                                            <div class="col-12 mb-3">
                                                <input type="text" class="form-control" placeholder="Chi tiết địa chỉ" name="shipping_city" value="<?= htmlspecialchars($checkoutForm['shipping_city'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="cart-summary">
                            <h5>Phiếu thanh toán</h5>
                            <ul class="summary-table">
                                <li><span>Số tiền tạm tính:</span> <span><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span></li>
                                <li><span>Phí giao hàng:</span> <span>Miễn phí</span></li>
                                <li><span>Tổng tiền:</span> <span><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span></li>
                            </ul>

                            <div class="payment-method">
                                
                                <div class="custom-control custom-radio mr-sm-2">
                                    <input type="radio" class="custom-control-input" id="cod" name="payment_method" value="cod" <?= (($checkoutForm['payment_method'] ?? 'cod') === 'cod') ? 'checked' : '' ?> form="checkoutForm">
                                    <label class="custom-control-label" for="cod">Thanh toán khi nhận hàng</label>
                                </div>
                                
                                <div class="custom-control custom-radio mr-sm-2">
                                    <input type="radio" class="custom-control-input" id="paypal" name="payment_method" value="paypal" <?= (($checkoutForm['payment_method'] ?? 'cod') === 'paypal') ? 'checked' : '' ?> form="checkoutForm">
                                    <label class="custom-control-label" for="paypal">Thanh toán bằng thẻ tín dụng <img class="ml-15" src="../img/core-img/paypal.png" alt=""></label>
                                </div>
                            </div>

                            <div class="cart-btn mt-100">
                                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                                    <button type="submit" form="checkoutForm" class="btn amado-btn w-100">Thanh toán</button>
                                <?php else: ?>
                                    <a href="../php/login/login.php?redirect_url=/baitap3/view/checkout.php" class="btn amado-btn w-100">Đăng nhập để thanh toán</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    
    <section class="newsletter-area section-padding-100-0">
        <div class="container">
            <div class="row align-items-center">
                
                <div class="col-12 col-lg-6 col-xl-7">
                    <div class="newsletter-text mb-100">
                        <h2>Đăng ký để nhận <span>Giảm giá 25%</span></h2>
                        <p>Công ty TNHH Raumania là đơn vị chuyên cung cấp đồ nội thất, setup bàn ghế văn phòng, gaming... trên toàn quốc. Cam kết uy tín 36%, giá thành tốt - chất lượng cao</p>
                    </div>
                </div>
                
                <div class="col-12 col-lg-6 col-xl-5">
                    <div class="newsletter-form mb-100">
                        <form action="../php/login/register.php" method="get">
                            <input type="email" name="email" class="nl-email" placeholder="Email của bạn">
                            <button type="submit" class="btn amado-btn">Đăng ký</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    

    
    <footer class="footer_area clearfix">
        <div class="container">
            <div class="row align-items-center">
                
                <div class="col-12 col-lg-4">
                    <div class="single_widget_area">
                        
                        <div class="footer-logo mr-50">
                            <a href="index.php"><img src="../img/core-img/logo2.png" alt=""></a>
                        </div>
                        
                        <p class="copywrite">
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Công ty TNHH Raumania</a> & Re-distributed by <a href="https://themewagon.com/" target="_blank">Themewagon</a>
</p>
                    </div>
                </div>
                
                <div class="col-12 col-lg-8">
                    <div class="single_widget_area">
                        
                        <div class="footer_menu">
                            <nav class="navbar navbar-expand-lg justify-content-end">
                                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#footerNavContent" aria-controls="footerNavContent" aria-expanded="false" aria-label="Toggle navigation"><i class="fa fa-bars"></i></button>
                                <div class="collapse navbar-collapse" id="footerNavContent">
                                    <ul class="navbar-nav ml-auto">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="index.php">Trang chủ</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="shop.php">Cửa hàng</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="product-details.php">Chi tiết sản phẩm</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="cart.php">Giỏ hàng</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="checkout.php">Thông tin thanh toán</a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    

    
    <script src="../js/jquery/jquery-2.2.4.min.js"></script>
    
    <script src="../js/popper.min.js"></script>
    
    <script src="../js/bootstrap.min.js"></script>
    
    <script src="../js/plugins.js"></script>
    
    <script src="../js/active.js"></script>

    <script>
        // kiểm tra required
    document.addEventListener('DOMContentLoaded', function () {
        const shipToDifferentAddressCheckbox = document.getElementById('customCheck3');
        const shippingAddressForm = document.getElementById('shipping_address_form');

        if (shipToDifferentAddressCheckbox && shippingAddressForm) {
            shipToDifferentAddressCheckbox.addEventListener('change', function () {
                shippingAddressForm.style.display = this.checked ? 'block' : 'none';
            });

            if (shipToDifferentAddressCheckbox.checked) {
                shippingAddressForm.style.display = 'block';
            }
        }

        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function (event) {
                if (!this.checkValidity()) {
                    event.preventDefault();
                }
            });
        }

        const phoneInput = document.getElementById('phone_number');
        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });
    </script>
</body>

</html>