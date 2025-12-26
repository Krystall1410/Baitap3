<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';
$product = null;
$favorite_notice = $_SESSION['favorite_notice'] ?? '';
unset($_SESSION['favorite_notice']);

if (!isset($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_favorites'])) {
    $favorite_product_id = (int)$_POST['add_to_favorites'];

    if ($favorite_product_id > 0) {
        $stmt = $mysqli->prepare("SELECT id, name, price, image FROM products WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $favorite_product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $favorite_product = $result->fetch_assoc();
        $stmt->close();

        if ($favorite_product) {
            $_SESSION['favorites'][$favorite_product_id] = [
                'id' => (int)$favorite_product['id'],
                'name' => $favorite_product['name'],
                'price' => (float)$favorite_product['price'],
                'image' => '/baitap3/uploads/products/' . ltrim($favorite_product['image'], '/'),
                'added_at' => time()
            ];
            $_SESSION['favorite_notice'] = 'Sản phẩm đã được thêm vào yêu thích.';
        }
    }

    header('Location: product-details.php?id=' . $favorite_product_id . '#favorite');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $remove_favorite_id = (int)$_POST['remove_favorite'];
    if (isset($_SESSION['favorites'][$remove_favorite_id])) {
        unset($_SESSION['favorites'][$remove_favorite_id]);
        $_SESSION['favorite_notice'] = 'Đã gỡ sản phẩm khỏi danh sách yêu thích.';
    }

    header('Location: product-details.php?id=' . $remove_favorite_id . '#favorite');
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addtocart'])) {
    $product_id_to_add = intval($_POST['addtocart']);
    $quantity_to_add = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id_to_add > 0 && $quantity_to_add > 0) {

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }


        // Lấy thông tin sản phẩm từ DB để lưu đầy đủ vào session (name, price, image)
        $stmt = $mysqli->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $product_id_to_add);
        $stmt->execute();
        $result = $stmt->get_result();
        $prod = $result->fetch_assoc();
        $stmt->close();

        if ($prod) {
            $availableStock = isset($prod['stock']) ? (int)$prod['stock'] : null;
            // Sử dụng key theo product id để tránh trùng nhiều entry
            if (!isset($_SESSION['cart'][$product_id_to_add])) {
                $initialQuantity = $quantity_to_add;
                if ($availableStock !== null && $availableStock > 0) {
                    $initialQuantity = min($initialQuantity, $availableStock);
                }

                $_SESSION['cart'][$product_id_to_add] = [
                    'id' => $prod['id'],
                    'name' => $prod['name'],
                    'price' => (float)$prod['price'],
                    'image' => '/baitap3/uploads/products/' . $prod['image'],
                    'quantity' => $initialQuantity,
                    'stock' => $availableStock,
                    'added_at' => time()
                ];
            } else {
                $newQuantity = $_SESSION['cart'][$product_id_to_add]['quantity'] + $quantity_to_add;
                if ($availableStock !== null && $availableStock > 0) {
                    $newQuantity = min($newQuantity, $availableStock);
                }
                $_SESSION['cart'][$product_id_to_add]['quantity'] = $newQuantity;
                $_SESSION['cart'][$product_id_to_add]['stock'] = $availableStock;
                $_SESSION['cart'][$product_id_to_add]['added_at'] = time();
            }
        }
    }
}
$is_favorited = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = intval($_GET['id']);

    $sql = "SELECT * FROM products WHERE id = ? AND is_active = 1";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $is_favorited = isset($_SESSION['favorites'][$product_id]);
        }
        $stmt->close();
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
    
    <title>Nội thất đẹp Raumania|Chi tiết sản phẩm</title>

    
    <link rel="icon" href="../img/core-img/favicon.ico">

    
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        /* Account styles moved to global css/style.css */
        .favorite-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 22px;
            min-width: 170px;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.15px;
            box-shadow: 0 10px 18px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
        }
        .favorite-btn.outline {
            background: #fff;
            color: #c28600;
            border: 1px solid #f5b000;
        }
        .favorite-btn.active {
            background: #fff;
            color: #f5b000;
            border: 1px solid #f5b000;
        }
        .favorite-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(0,0,0,0.12);
        }
        .favorite-notice {
            color: #2b8a3e;
            font-size: 14px;
        }
        /* Make product description text slightly smaller for readability */
        .short_overview p {
            font-size: 14px;
            line-height: 1.6;
        }
        .short_overview ul li {
            font-size: 14px;
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
                    <li><a href="index.php">Trang chủ</a></li>
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
        

        
        <?php if (!$product): ?>
        <div class="single-product-area section-padding-100 clearfix">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center"><h2>Vui lòng chọn một sản phẩm để xem chi tiết.</h2><p><a href="shop.php" class="btn amado-btn mt-30">Đi đến cửa hàng</a></p></div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="single-product-area section-padding-100 clearfix">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mt-50">
                                <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>
                                <li class="breadcrumb-item"><a href="shop.php">Cửa hàng</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-7">
                        <div class="single_product_thumb">
                            <div id="product_details_slider" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    <li class="active" data-target="#product_details_slider" data-slide-to="0" style="background-image: url('/baitap3/uploads/products/<?php echo htmlspecialchars($product['image']); ?>');"></li>
                                </ol>
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <a class="gallery_img" href="/baitap3/uploads/products/<?php echo htmlspecialchars($product['image']); ?>">
                                            <img class="d-block w-100" src="/baitap3/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="single_product_desc">
                            
                            <div class="product-meta-data">
                                <div class="line"></div>
                                <p class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>vnd</p>
                                <a href="product-details.php">
                                    <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                                </a>
                                
                                <div class="ratings-review mb-15 d-flex align-items-center justify-content-end">
                                    <!--
                                    <div class="ratings">
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                    </div>
                                    -->
                                    <form id="favorite" class="mb-0" action="product-details.php?id=<?php echo $product['id']; ?>#favorite" method="post">
                                        <?php if ($is_favorited): ?>
                                            <button type="submit" name="remove_favorite" value="<?php echo $product['id']; ?>" class="btn amado-btn favorite-btn active">Bỏ yêu thích</button>
                                        <?php else: ?>
                                            <button type="submit" name="add_to_favorites" value="<?php echo $product['id']; ?>" class="btn amado-btn favorite-btn outline">Yêu thích</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                                <?php if ($favorite_notice): ?>
                                     <p class="mt-2 favorite-notice"><?php echo htmlspecialchars($favorite_notice); ?></p>
                                <?php endif; ?>
                                
                                <p class="avaibility"><i class="fa fa-circle"></i> Còn hàng<?php if (isset($product['stock'])): ?> (<?= (int)$product['stock']; ?>)<?php endif; ?></p>
                            </div>
                            
                            <div class="short_overview my-5">
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                                <?php if (!empty($product['specs'])): ?>
                                    <hr>
                                    <h6>Thông số kỹ thuật</h6>
                                    <?php $specLines = array_filter(array_map('trim', preg_split('/\r?\n/', $product['specs']))); ?>
                                    <?php if ($specLines): ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($specLines as $line): ?>
                                                <li><?php echo htmlspecialchars($line); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <!-- Add to Cart Form -->

                            <form class="cart clearfix" action="product-details.php?id=<?php echo $product['id']; ?>" method="post">
                                <div class="cart-btn d-flex mb-50">
                                    <p>Số lượng</p>
                                    <div class="quantity">
                                        <span class="qty-minus" onclick="var effect = document.getElementById('qty'); var qty = effect.value; if( !isNaN( qty ) &amp;&amp; qty &gt; 1 ) effect.value--;return false;"><i class="fa fa-caret-down" aria-hidden="true"></i></span>
                                        <input type="number" class="qty-text" id="qty" step="1" min="1" max="300" name="quantity" value="1">
                                        <span class="qty-plus" onclick="var effect = document.getElementById('qty'); var qty = effect.value; if( !isNaN( qty )) effect.value++;return false;"><i class="fa fa-caret-up" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <button type="submit" name="addtocart" value="<?php echo $product['id']; ?>" class="btn amado-btn">Thêm vào giỏ</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
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
Copyright &copy;<script>document.write(new Date().getFullYear());</script>  All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Công ty TNHH Raumania</a> & Re-distributed by <a href="https://themewagon.com/" target="_blank">Themewagon</a>
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