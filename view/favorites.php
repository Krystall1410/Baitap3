<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';

if (!isset($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

$favorites = $_SESSION['favorites'];
$favorite_notice = $_SESSION['favorite_notice'] ?? '';
unset($_SESSION['favorite_notice']);

if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    if (isset($_SESSION['favorites'][$remove_id])) {
        unset($_SESSION['favorites'][$remove_id]);
        $_SESSION['favorite_notice'] = 'Đã gỡ sản phẩm khỏi danh sách yêu thích.';
    }
    header('Location: favorites.php');
    exit();
}

$favorite_items = [];

if (!empty($favorites)) {
    $favorite_ids = array_values(array_filter(array_map('intval', array_keys($favorites)), static function ($id) {
        return $id > 0;
    }));

    if (!empty($favorite_ids)) {
        $placeholders = implode(',', array_fill(0, count($favorite_ids), '?'));
        $types = str_repeat('i', count($favorite_ids));
        $stmt = $mysqli->prepare("SELECT id, name, price, image, is_active FROM products WHERE id IN ($placeholders)");

        if ($stmt instanceof mysqli_stmt) {
            $stmt->bind_param($types, ...$favorite_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            $product_map = [];

            while ($row = $result->fetch_assoc()) {
                $product_map[(int)$row['id']] = $row;
            }

            $stmt->close();

            foreach ($favorites as $fav_id => $fav_item) {
                $int_id = (int)$fav_id;
                if (!isset($product_map[$int_id]) || (int)$product_map[$int_id]['is_active'] !== 1) {
                    continue;
                }

                $product_row = $product_map[$int_id];
                $favorite_items[$int_id] = [
                    'id' => $product_row['id'],
                    'name' => $product_row['name'],
                    'price' => (float)$product_row['price'],
                    'image' => '/baitap3/uploads/products/' . ltrim($product_row['image'], '/'),
                ];
            }

            $_SESSION['favorites'] = $favorite_items;
        }
    }
}
?><!DOCTYPE html>
<html lang="vietnamese">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">

    <title>Nội thất đẹp Raumania|Yêu thích</title>

    <link rel="icon" href="../img/core-img/favicon.ico">

    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            grid-gap: 18px;
        }
        .favorite-card {
            border: 1px solid #f1f1f1;
            padding: 18px;
            transition: box-shadow 0.2s ease;
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .favorite-card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .favorite-card .favorite-img {
            width: 100%;
            height: 220px;
            overflow: hidden;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafafa;
        }
        .favorite-card .favorite-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .favorite-actions {
            margin-top: auto;
            display: flex;
            flex-wrap: nowrap;
            gap: 6px;
            justify-content: flex-end;
            overflow: visible;
        }
        .favorite-price {
            font-weight: 600;
            color: #fbb710;
        }
        .favorite-empty {
            padding: 30px;
            border: 1px dashed #ddd;
            text-align: center;
        }
        .favorite-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 8px 12px;
            min-width: 96px;
            min-height: 36px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0;
            text-align: center;
            white-space: nowrap;
            box-shadow: 0 6px 14px rgba(0,0,0,0.07);
            transition: all 0.18s ease;
            flex: 1 1 0;
        }
        .favorite-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(0,0,0,0.11);
        }
        .favorite-actions .btn.fav-secondary {
            background: #fff;
            color: #1f2933;
            border: 1px solid #e5e7eb;
        }
        .favorite-actions .btn.fav-primary {
            background: linear-gradient(135deg, #ffc74a, #f5a600);
            color: #fff;
            border: 1px solid #f0aa00;
        }
        .favorite-actions .btn.fav-tertiary {
            background: #f6f7fb;
            color: #4b5563;
            border: 1px solid #e7e9f0;
        }
        .favorite-badge {
            padding: 6px 12px;
            background: #fef4e4;
            color: #d67f00;
            border-radius: 999px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 8px;
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
                    <div class="col-12">
                        <div class="cart-title mt-50 d-flex justify-content-between align-items-center">
                            <h2>Danh sách yêu thích</h2>
                            <?php if ($favorite_notice): ?>
                                <span style="color: #28a745; font-size: 14px;"><?php echo htmlspecialchars($favorite_notice); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($favorite_items)): ?>
                    <div class="favorites-grid mt-30">
                        <?php foreach ($favorite_items as $item): ?>
                            <div class="favorite-card">
                                <div class="favorite-img">
                                    <a href="product-details.php?id=<?php echo $item['id']; ?>">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </a>
                                </div>
                                <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="favorite-price"><?php echo number_format($item['price'], 0, ',', '.'); ?> VND</p>
                                <div class="favorite-actions d-flex align-items-center justify-content-end">
                                    <a class="btn amado-btn fav-secondary" href="product-details.php?id=<?php echo $item['id']; ?>">Xem chi tiết</a>
                                    <a class="btn amado-btn fav-primary" href="shop.php?add_to_cart=<?php echo $item['id']; ?>&from=favorites">Thêm vào giỏ</a>
                                    <form action="favorites.php" method="get" class="mb-0">
                                        <input type="hidden" name="remove" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn amado-btn fav-tertiary">Bỏ yêu thích</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="favorite-empty mt-30">
                                <p>Bạn chưa yêu thích sản phẩm nào.</p>
                                <a class="btn amado-btn mt-15" href="shop.php">Khám phá sản phẩm</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
                                        <li class="nav-item">
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
