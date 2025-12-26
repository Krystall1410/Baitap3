<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';

$featuredProducts = [];
$stmtFeatured = $mysqli->prepare("SELECT id, name, price, image FROM products ORDER BY id DESC LIMIT 9");
if ($stmtFeatured) {
    $stmtFeatured->execute();
    $resultFeatured = $stmtFeatured->get_result();
    if ($resultFeatured) {
        while ($row = $resultFeatured->fetch_assoc()) {
            $featuredProducts[] = $row;
        }
    }
    $stmtFeatured->close();
}
?><!DOCTYPE html>
<html lang="vietnamese">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">
    
    <title>Nội thất đẹp Raumania|Trang chủ</title>

    
    <link rel="icon" href="../img/core-img/favicon.ico">

    
    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .single-products-catagory .hover-content {
            color: #fbb710;
        }
        .single-products-catagory .hover-content p,
        .single-products-catagory .hover-content h4 {
            color: #fbb710;
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
                    <li class="active"><a href="index.php">Trang chủ</a></li>
                    <li><a href="shop.php">Cửa hàng</a></li>
                    
                 
                    <li><a href="checkout.php">Thông tin thanh toán</a></li>
                </ul>
            </nav>
            
            <!-- <div class="amado-btn-group mt-30 mb-100">
                <a href="#" class="btn amado-btn mb-15">%Giảm giá%</a>
                <a href="shop.php" class="btn amado-btn active">Sản phẩm mới</a>
            </div> -->
            
            <div class="cart-fav-search mb-100">
                <a href="cart.php" class="cart-nav"><img src="../img/core-img/cart.png" alt=""> Giỏ hàng <span>(<?php
                        $cart_count = 0; // Khởi tạo biến đếm
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                // Cộng dồn số lượng của từng sản phẩm
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
        
        <div class="products-catagories-area clearfix">
            <div class="amado-pro-catagory clearfix">
                <?php if (!empty($featuredProducts)): ?>
                    <?php foreach ($featuredProducts as $product):
                        $productId = isset($product['id']) ? (int)$product['id'] : 0;
                        $productName = isset($product['name']) ? $product['name'] : 'Sản phẩm';
                        $priceValue = isset($product['price']) ? (float)$product['price'] : 0;
                        $priceLabel = $priceValue > 0 ? 'Từ ' . number_format($priceValue, 0, ',', '.') . ' VND' : 'Giá đang cập nhật';
                        $imagePath = !empty($product['image']) ? '/baitap3/uploads/products/' . ltrim($product['image'], '/') : '../img/bg-img/sofa.jpg';
                        $productLink = $productId > 0 ? 'product-details.php?id=' . $productId : 'shop.php';
                    ?>
                    <div class="single-products-catagory clearfix">
                        <a href="<?= htmlspecialchars($productLink); ?>">
                            <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($productName); ?>">
                            <div class="hover-content">
                                <div class="line"></div>
                                <p><?= htmlspecialchars($priceLabel); ?></p>
                                <h4><?= htmlspecialchars($productName); ?></h4>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="single-products-catagory clearfix">
                        <a href="shop.php">
                            <img src="../img/bg-img/sofa.jpg" alt="Tiếp tục mua sắm">
                            <div class="hover-content">
                                <div class="line"></div>
                                <p>Chưa có sản phẩm để hiển thị</p>
                                <h4>Khám phá cửa hàng</h4>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    

    
    <section class="newsletter-area section-padding-100-0">
        <div class="container">
            <div class="row align-items-center">
                
                <div class="col-12 col-lg-6 col-xl-7">
                    <div class="newsletter-text mb-100">
                        <h2>Đăng ký để nhận <span>GIẢM GIÁ 25%</span></h2>
                        <p>Công ty TNHH Raumania là đơn vị chuyên cung cấp đồ nội thất, setup bàn ghế văn phòng, gaming... trên toàn quốc. Cam kết uy tín 36%, giá thành tốt - chất lượng cao</p>
                    </div>
                </div>
                
                <div class="col-12 col-lg-6 col-xl-5">
                    <div class="newsletter-form mb-100">
                        <form action="#" method="post">
                            <input type="email" name="email" class="nl-email" placeholder=" Email của bạn">
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

</body>

</html>