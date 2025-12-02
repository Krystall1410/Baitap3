<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';


$subtotal = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
  
        $price = isset($item['price']) ? floatval($item['price']) : 0;
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
        $subtotal += $price * $quantity;
    }
}
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    
    <title>Nội thất đẹp Raumania|Thông tin thanh toán</title>

    
    <link rel="icon" href="../img/core-img/favicon.ico">

    
    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../style.css">

    <style>
      
        .account-nav {
            font-size: 16px;
            color: #242424;
            font-weight: 500;
            text-transform: uppercase;
            padding-left: 15px;
            display: inline-block;
        }
        .account-nav:hover, .account-nav:focus {
            color: #fbb710;
        }
        .account-nav img {
            padding-right: 5px;
        }
        .dropdown-menu {
            border: none;
            border-radius: 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            margin-top: 10px !important;
        }
        .dropdown-item:hover, .dropdown-item:focus {
            color: #fbb710;
            background-color: #f8f9fa;
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
                        <form action="#" method="get">
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
                    <li><a href="product-details.php">Chi tiết sản phẩm</a></li>
                    <li><a href="cart.php">Giỏ hàng</a></li>
                    <li class="active"><a href="checkout.php">Thông tin thanh toán</a></li>
                </ul>
            </nav>
            
            <div class="amado-btn-group mt-30 mb-100">
                <a href="#" class="btn amado-btn mb-15">%Giảm giá%</a>
                <a href="shop.php" class="btn amado-btn active">Sản phẩm mới</a>
            </div>
            
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
                <a href="#" class="fav-nav"><img src="../img/core-img/favorites.png" alt=""> Yêu thích</a>
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

                            <form action="#" method="post" id="checkoutForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" id="first_name" value="" placeholder="Tên" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" id="last_name" value="" placeholder="Họ" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control" id="company" placeholder="Tên công ty" value="">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="email" class="form-control" id="email" placeholder="Email" value="" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <select class="w-100" id="country" required>
                                        <option value="usa">Hà Nội</option>
                                        <option value="uk">Tp Hồ Chí Minh</option>
                                        <option value="ger">Quảng Ninh</option>
                                        <option value="fra">Hải Phòng</option>
                                        <option value="ind">Đà Lạt</option>
                                        <option value="aus">Đà Nẵng</option>
                                        <option value="bra">Phú Thọ</option>
                                        <option value="cana">Khác</option>
                                    </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control mb-3" id="street_address" placeholder="Quận/Huyện" value="" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control" id="city" placeholder="Chi tiết địa chỉ" value="" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" id="zipCode" placeholder="Mã ZIP" value="">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" id="phone_number"  placeholder="SĐT liên hệ" value="" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <textarea name="comment" class="form-control w-100" id="comment" cols="30" rows="10" placeholder="Chú thích cho đơn hàng của bạn"></textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="custom-control custom-checkbox d-block">
                                            <input type="checkbox" class="custom-control-input" id="customCheck3">
                                            <label class="custom-control-label" for="customCheck3">Giao tới địa chỉ khác</label>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div id="shipping_address_form" class="mt-4" style="display: none;">
                                <hr>
                                <h4 class="mb-3">Địa chỉ giao hàng khác</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" placeholder="Tên" name="shipping_first_name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" class="form-control" placeholder="Họ" name="shipping_last_name">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <select class="w-100" name="shipping_country">
                                            <option value="hanoi">Hà Nội</option>
                                            <option value="hcm">Tp Hồ Chí Minh</option>
                                            <option value="quangninh">Quảng Ninh</option>
                                            <option value="haiphong">Hải Phòng</option>
                                            <option value="dalat">Đà Lạt</option>
                                            <option value="danang">Đà Nẵng</option>
                                            <option value="phutho">Phú Thọ</option>
                                            <option value="other">Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control" placeholder="Quận/Huyện" name="shipping_street_address">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="text" class="form-control" placeholder="Chi tiết địa chỉ" name="shipping_city">
                                    </div>
                                </div>
                            </div>
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
                                    <input type="radio" class="custom-control-input" id="cod" name="payment_method" value="cod" checked>
                                    <label class="custom-control-label" for="cod">Thanh toán khi nhận hàng</label>
                                </div>
                                
                                <div class="custom-control custom-radio mr-sm-2">
                                    <input type="radio" class="custom-control-input" id="paypal" name="payment_method" value="paypal">
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

        shipToDifferentAddressCheckbox.addEventListener('change', function () {
            if (this.checked) {
                shippingAddressForm.style.display = 'block';
            } else {
                shippingAddressForm.style.display = 'none';
            }
        });

        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function (event) {
                
                if (!this.checkValidity()) {
                    event.preventDefault(); 
                }
            });
        }
    });
    </script>
</body>

</html>