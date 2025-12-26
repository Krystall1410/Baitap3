<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';

$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_notice = '';

if (!empty($cart_items)) {
    $productIds = array_keys($cart_items);
    $productIds = array_values(array_filter(array_map('intval', $productIds), static function ($id) {
        return $id > 0;
    }));

    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $types = str_repeat('i', count($productIds));
        $stmt = $mysqli->prepare("SELECT id, price, stock, is_active FROM products WHERE id IN ($placeholders)");

        if ($stmt instanceof mysqli_stmt) {
            $stmt->bind_param($types, ...$productIds);
            $stmt->execute();
            $result = $stmt->get_result();
            $productMap = [];

            while ($row = $result->fetch_assoc()) {
                $productMap[(int)$row['id']] = $row;
            }

            $stmt->close();

            $filteredCart = [];
            $removedCount = 0;

            foreach ($cart_items as $id => $item) {
                $intId = (int)$id;
                if (!isset($productMap[$intId])) {
                    $removedCount++;
                    continue;
                }

                $productRow = $productMap[$intId];
                if ((int)$productRow['is_active'] !== 1) {
                    $removedCount++;
                    continue;
                }

                $item['price'] = isset($productRow['price']) ? (float)$productRow['price'] : ($item['price'] ?? 0);
                $item['stock'] = array_key_exists('stock', $productRow) ? ($productRow['stock'] !== null ? (int)$productRow['stock'] : null) : ($item['stock'] ?? null);
                $filteredCart[$intId] = $item;
            }

            if ($removedCount > 0) {
                $cart_notice = 'Một số sản phẩm đã bị gỡ khỏi giỏ hàng vì hiện không còn hiển thị.';
            }

            $_SESSION['cart'] = $filteredCart;
            $cart_items = $filteredCart;
        }
    }
}
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">
    

    
    <title>Nội thất đẹp Raumania|Giỏ hàng</title>

    
    <link rel="icon" href="../img/core-img/favicon.ico">

    
    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .quantity {
            display: flex;
            align-items: center;
            border: 1px solid #ebebeb;
            border-radius: 4px;
            overflow: hidden;
        }
        .quantity .qty-btn {
            background-color: #f5f5f5;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 35px;
            height: 100%;
            line-height: 40px; 
            text-align: center;
            transition: background-color 0.3s;
        }
        .quantity .qty-btn:hover {
            background-color: #e0e0e0;
        }
        .quantity .qty-text {
            border: none;
            text-align: center;
            width: 50px;
            -moz-appearance: textfield; 
        }
        .quantity .qty-text::-webkit-outer-spin-button,
        .quantity .qty-text::-webkit-inner-spin-button {
            -webkit-appearance: none; 
            margin: 0;
        }
        .quantity.reached-stock .qty-btn.qty-plus {
            background-color: #ffecec;
            cursor: not-allowed;
            color: #b30000;
        }
    </style>

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
                <a href="../index.php"><img src="../img/core-img/logo.png" alt=""></a>
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
                <a href="../index.php"><img src="../img/core-img/logo.png" alt=""></a>
            </div>
            
            <nav class="amado-nav">
                <ul>
                    <li><a href="../index.php">Trang chủ</a></li>
                    <li><a href="shop.php">Cửa hàng</a></li>
                    
                    
                    <li><a href="checkout.php">Thông tin thanh toán</a></li>
                </ul>
            </nav>
            
            <!-- <div class="amado-btn-group mt-30 mb-100">
                <a href="#" class="btn amado-btn mb-15">%Giảm giá%</a>
                <a href="#" class="btn amado-btn active">Sản phẩm mới</a>
            </div> -->
            
            <div class="cart-fav-search mb-100">
                <a href="cart.php" class="cart-nav">
                    <img src="../img/core-img/cart.png" alt=""> Giỏ hàng 
                    <span>(<?php
                        $cart_count = 0; 
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                $cart_count += isset($item['quantity']) ? (int)$item['quantity'] : 0;
                            }
                        }
                        echo $cart_count;
                    ?>)</span>
                </a>
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
                        <div class="cart-title mt-50 d-flex justify-content-between align-items-center">
                            <h2>Sản phẩm đã thêm</h2>
                            <?php if (!empty($_SESSION['cart'])): ?>
                            <form action="../php/cart/clear_cart.php" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm khỏi giỏ hàng?');">
                                <button type="submit" class="btn btn-danger">Xoá tất cả</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php if ($cart_notice !== ''): ?>
                        <div class="alert alert-warning" role="alert">
                            <?php echo htmlspecialchars($cart_notice, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <?php endif; ?>

                        <div class="cart-table clearfix">
                            <table class="table table-responsive">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Tên sản phẩm</th>
                                        <th>Đơn giá</th>
                                        <th>Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $subtotal = 0;
                                        $shipping_fee = 0; 

                                        if (empty($cart_items)):
                                    ?>
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <p style="margin-top: 20px;">Giỏ hàng của bạn đang trống.</p>
                                                <a href="shop.php" class="btn amado-btn mt-3">Bắt đầu mua sắm</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($cart_items as $index => $item): 
                                            // Bảo vệ: đảm bảo các key tồn tại, gán mặc định nếu không
                                            $price = isset($item['price']) ? (float)$item['price'] : 0.0;
                                            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                                            $name = isset($item['name']) ? $item['name'] : 'Sản phẩm';
                                            $image = isset($item['image']) ? $item['image'] : '';
                                            $id = isset($item['id']) ? $item['id'] : '';
                                            $stock = isset($item['stock']) && $item['stock'] !== null ? (int)$item['stock'] : null;
                                            $maxQuantity = ($stock !== null && $stock > 0) ? $stock : 300;

                                            $item_total = $price * $quantity;
                                            $subtotal += $item_total;
                                            $qty_id = 'qty' . $index;
                                        ?> 
                                            <tr class="cart-item">
                                                <td class="cart_product_img">
                                                        <a href="#"><img src="<?php echo htmlspecialchars($image); ?>" alt="Product" style="max-width: 100px;"></a>
                                                </td>
                                                <td class="cart_product_desc">
                                                    <h5><?php echo htmlspecialchars($name); ?></h5>
                                                </td>
                                                <td class="price" data-price="<?php echo $price; ?>">
                                                    <span><?php echo number_format($price, 0, ',', '.'); ?> VND</span>
                                                </td>
                                                <td class="qty">
                                                    <div class="qty-btn d-flex">
                                                        <div class="quantity">
                                                            <button type="button" class="qty-btn qty-minus">-</button>
                                                            <input type="number" class="qty-text" id="<?php echo $qty_id; ?>" step="1" min="1" max="<?php echo $maxQuantity; ?>" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" data-id="<?php echo htmlspecialchars($id); ?>"<?php echo ($stock !== null && $stock > 0) ? ' data-stock="' . $stock . '"' : ''; ?>>
                                                            <button type="button" class="qty-btn qty-plus">+</button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; 
                                        $total = $subtotal + $shipping_fee;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="cart-summary">
                            <h5>Phiếu thanh toán</h5>
                            <ul class="summary-table">
                                <li><span>Số tiền tạm tính:</span> <span id="subtotal"><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</span></li>
                                <li><span>Phí giao hàng:</span> <span id="shipping"><?php echo $shipping_fee == 0 ? 'Miễn phí' : number_format($shipping_fee, 0, ',', '.') . ' VND'; ?></span></li>
                                <li><span>Tổng tiền :</span> <span id="total"><?php echo number_format($total, 0, ',', '.'); ?> VND</span></li>
                            </ul>
                            <div class="cart-btn mt-100">
                                <a href="checkout.php" class="btn amado-btn w-100">Thanh toán</a>
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
                        <form action="#" method="post">
                            <input type="email" name="email" class="nl-email" placeholder="Email của bạn">
                            <input type="submit" value="Đăng ký">
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
                            <a href="../index.php"><img src="../img/core-img/logo2.png" alt=""></a>
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
                                            <a class="nav-link" href="../index.php">Trang chủ</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="shop.php">Cửa hàng</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="product-details.php">Chi tiết sản phẩm</a>
                                        </li>
                                        <li class="nav-item">
                                           
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
    document.addEventListener('DOMContentLoaded', function () {
    
        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.') + ' VND';
        }

        function clampQuantityToStock(quantityInput) {
            const maxStock = parseInt(quantityInput.getAttribute('data-stock'), 10);
            let currentVal = parseInt(quantityInput.value);

            if (isNaN(currentVal) || currentVal < 1) {
                currentVal = 1;
            }

            if (!isNaN(maxStock) && maxStock > 0 && currentVal > maxStock) {
                currentVal = maxStock;
            }

            quantityInput.value = currentVal;
            return { value: currentVal, maxStock };
        }

        function updateStockState(quantityInput, quantityWrapper) {
            const maxStock = parseInt(quantityInput.getAttribute('data-stock'), 10);
            const currentVal = parseInt(quantityInput.value);

            if (!isNaN(maxStock) && maxStock > 0 && !isNaN(currentVal) && currentVal >= maxStock) {
                quantityWrapper.classList.add('reached-stock');
                quantityWrapper.setAttribute('title', 'Đã đạt số lượng tối đa trong kho');
            } else {
                quantityWrapper.classList.remove('reached-stock');
                quantityWrapper.removeAttribute('title');
            }
        }

       
        function updateCartSummary() {
            let subtotal = 0;
            document.querySelectorAll('.cart-item').forEach(function (itemRow) {
                const priceElement = itemRow.querySelector('.price');
                const quantityInput = itemRow.querySelector('.qty-text');
                
                if (priceElement && quantityInput) {
                    const price = parseFloat(priceElement.getAttribute('data-price'));
                    const quantity = parseInt(quantityInput.value);
                    subtotal += price * quantity;
                }
            });

            const shippingFee = 0; 
            const total = subtotal + shippingFee;

            document.getElementById('subtotal').textContent = formatNumber(subtotal);
            document.getElementById('total').textContent = formatNumber(total);
        }

      
        document.querySelectorAll('.quantity').forEach(function (quantityWrapper) {
            const quantityInput = quantityWrapper.querySelector('.qty-text');
            const productId = quantityInput.getAttribute('data-id');

            clampQuantityToStock(quantityInput);
            updateStockState(quantityInput, quantityWrapper);

        
            const minusBtn = quantityWrapper.querySelector('.qty-minus');
            minusBtn.addEventListener('click', function() {
                let currentVal = parseInt(quantityInput.value);
                if (!isNaN(currentVal) && currentVal > 1) {
                    quantityInput.value = currentVal - 1;
                    updateCartSummary();
                    updateCartInSession(productId, quantityInput.value, quantityInput, quantityWrapper);
                    updateStockState(quantityInput, quantityWrapper);
                }
            });

        
            const plusBtn = quantityWrapper.querySelector('.qty-plus');
            plusBtn.addEventListener('click', function() {
                let currentVal = parseInt(quantityInput.value);
                const stockInfo = clampQuantityToStock(quantityInput);
                currentVal = stockInfo.value;

                if (!isNaN(currentVal)) {
                    if (!isNaN(stockInfo.maxStock) && stockInfo.maxStock > 0) {
                        if (currentVal >= stockInfo.maxStock) {
                            quantityInput.value = stockInfo.maxStock;
                            updateStockState(quantityInput, quantityWrapper);
                            updateCartSummary();
                            updateCartInSession(productId, quantityInput.value);
                            return;
                        }
                    }

                    quantityInput.value = currentVal + 1;
                    clampQuantityToStock(quantityInput);
                    updateCartSummary();
                    updateCartInSession(productId, quantityInput.value, quantityInput, quantityWrapper);
                    updateStockState(quantityInput, quantityWrapper);
                }
            });

            quantityInput.addEventListener('change', function() {
                clampQuantityToStock(quantityInput);
                updateStockState(quantityInput, quantityWrapper);
                updateCartSummary();
                updateCartInSession(productId, quantityInput.value, quantityInput, quantityWrapper);
            });

            quantityInput.addEventListener('input', function() {
                clampQuantityToStock(quantityInput);
                updateStockState(quantityInput, quantityWrapper);
            });
        });

        function updateCartInSession(productId, quantity, quantityInput, quantityWrapper) {
            const params = new URLSearchParams();
            params.append('product_id', productId);
            params.append('quantity', quantity);

            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (!data) {
                    return;
                }

                if (!data.success) {
                    console.warn('Không thể cập nhật giỏ hàng:', data.message || 'Unknown error');
                    return;
                }

                if (data.removed && quantityWrapper) {
                    const row = quantityWrapper.closest('.cart-item');
                    if (row) {
                        row.remove();
                    }
                }

                let rowElement = null;
                if (quantityWrapper) {
                    rowElement = quantityWrapper.closest('.cart-item');
                }

                if (rowElement && typeof data.price !== 'undefined') {
                    const priceElement = rowElement.querySelector('.price');
                    if (priceElement) {
                        priceElement.setAttribute('data-price', data.price);
                        const priceSpan = priceElement.querySelector('span');
                        if (priceSpan) {
                            priceSpan.textContent = formatNumber(data.price);
                        }
                    }
                }

                if (quantityInput && typeof data.quantity !== 'undefined') {
                    quantityInput.value = data.quantity;
                }

                if (quantityInput && typeof data.stock !== 'undefined') {
                    if (data.stock !== null && data.stock > 0) {
                        quantityInput.setAttribute('data-stock', data.stock);
                        quantityInput.setAttribute('max', data.stock);
                    } else {
                        quantityInput.removeAttribute('data-stock');
                        quantityInput.setAttribute('max', 300);
                    }
                }

                if (quantityInput && quantityWrapper) {
                    clampQuantityToStock(quantityInput);
                    updateStockState(quantityInput, quantityWrapper);
                }

                if (typeof data.subtotal !== 'undefined') {
                    document.getElementById('subtotal').textContent = formatNumber(data.subtotal);
                }

                if (typeof data.total !== 'undefined') {
                    document.getElementById('total').textContent = formatNumber(data.total);
                }

                updateCartSummary();
            })
            .catch(error => console.error('Lỗi khi cập nhật giỏ hàng:', error));
        }
    });
    </script>

</body>

</html>