<?php
session_start();
require_once __DIR__ . '/../php/login/config.php';


if (isset($_GET['add_to_cart'])) {
    $product_id = (int)$_GET['add_to_cart'];

 
    $stmt = $mysqli->prepare("SELECT id, name, price, image, stock, is_active FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product && (int)$product['is_active'] === 1) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
 
        $availableStock = isset($product['stock']) ? (int)$product['stock'] : null;

        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => '/baitap3/uploads/products/' . $product['image'],
            'quantity' => 1,
            'stock' => $availableStock,
            'added_at' => time()
        ];
    }

    header('Location: shop.php');
    exit();
}

$category_slug = $_GET['category'] ?? null;
$brand_id = $_GET['brand'] ?? null;
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : null;
$sort_order = $_GET['sort'] ?? 'newest';
?><!DOCTYPE html>
<?php
// Pagination settings
$products_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $products_per_page;
?>
<html lang="vietnamese">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">

    
    <title>Nội thất đẹp Raumania|Cửa hàng</title>

    <link rel="icon" href="../img/core-img/favicon.ico">

    <link rel="stylesheet" href="../css/core-style.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .single-product-wrapper .product-img .hover-img {
            opacity: 0;
            visibility: hidden;
            transition-duration: 400ms;
        }

        .single-product-wrapper:hover .product-img .hover-img {
            opacity: 1;
            visibility: visible;
        }

        .single-product-wrapper .product-img a {
            display: block;
            position: relative;
            overflow: hidden;
        }

        .single-product-wrapper .product-img a img {
            transition: opacity 0.3s ease;
        }

        .single-product-wrapper .product-img a:hover img,
        .single-product-wrapper .product-img a:focus img {
            opacity: 0.85;
        }

        .single-product-wrapper .product-img {
            height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .single-product-wrapper .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                    <li class="active"><a href="shop.php">Cửa hàng</a></li>
                    
                   
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
                        <img src="img/core-img/user.png" alt=""> My Account
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a class="dropdown-item" href="/baitap3/php/login/admin.php">Admin</a>
                            <?php endif; ?>
                            <a class="dropdown-item" href="/baitap3/php/login/logout.php">Đăng xuất</a>
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

        <div class="shop_sidebar_area">

            <div class="widget catagory mb-50">
                <h6 class="widget-title mb-30">Danh mục</h6>

                <div class="catagories-menu">
                    <ul>
                        <li class="<?= !$category_slug ? 'active' : '' ?>"><a href="shop.php">Tất cả</a></li>
                        <?php
                            $cat_res = $mysqli->query("SELECT name, slug FROM categories ORDER BY name ASC");
                            while($cat = $cat_res->fetch_assoc()):
                        ?>
                        <li class="<?= ($category_slug == $cat['slug']) ? 'active' : '' ?>">
                            <a href="shop.php?category=<?= htmlspecialchars($cat['slug']) ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            
            <div class="widget brands mb-50">
                
                <h6 class="widget-title mb-30">Thương hiệu</h6>

                <div class="widget-desc">
                    <?php
                        $brand_res = $mysqli->query("SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name ASC");
                        while($brand = $brand_res->fetch_assoc()):
                    ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="brand" value="<?= $brand['id'] ?>" id="brand-<?= $brand['id'] ?>" <?= ($brand_id == $brand['id']) ? 'checked' : '' ?> onchange="window.location.href='shop.php?brand='+this.value">
                        <label class="form-check-label" for="brand-<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></label>
                    </div>
                    <?php endwhile; ?>
                     <div class="form-check">
                        <input class="form-check-input" type="radio" name="brand" value="" id="brand-all" <?= !$brand_id ? 'checked' : '' ?> onchange="window.location.href='shop.php'">
                        <label class="form-check-label" for="brand-all">Tất cả thương hiệu</label>
                    </div>
                </div>
            </div>

            

            
            <div class="widget price mb-50">
                
                <h6 class="widget-title mb-30">Giá</h6>

                <div class="widget-desc">
                    <div class="slider-range">
                        <div data-min="0" data-max="50000000" data-unit="VND" class="slider-range-price ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all" data-value-min="<?= $min_price ?? 0 ?>" data-value-max="<?= $max_price ?? 50000000 ?>" data-label-result="Khoảng giá">
                            <div class="ui-slider-range ui-widget-header ui-corner-all"></div>
                            <span class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0"></span>
                            <span class="ui-slider-handle ui-state-default ui-corner-all" tabindex="0"></span>
                        </div>
                        <div class="range-price"><?= number_format($min_price ?? 0) ?>VND - <?= number_format($max_price ?? 50000000) ?>VND</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="amado_product_area section-padding-100">
            <div class="container-fluid">
                <?php
                    // --- LOGIC TRUY VẤN SẢN PHẨM (ĐÃ DI CHUYỂN LÊN TRÊN) ---
                    $sql = "SELECT p.id, p.name, p.price, p.image FROM products p";
                    $where = ['p.is_active = 1'];
                    $params = [];
                    $types = '';

                    if ($search_term) {
                        $where[] = "p.name LIKE ?";
                        $params[] = "%" . $search_term . "%";
                        $types .= 's';
                    }

                    if ($category_slug) {
                        $sql_join_cat = " JOIN categories c ON p.category_id = c.id";
                        $where[] = "c.slug = ?";
                        $params[] = $category_slug;
                        $types .= 's';
                    }
                    if ($brand_id) {
                        $where[] = "p.brand_id = ?";
                        $params[] = $brand_id;
                        $types .= 'i';
                    }
                    if ($min_price !== null) {
                        $where[] = "p.price >= ?";
                        $params[] = $min_price;
                        $types .= 'd';
                    }
                    if ($max_price !== null) {
                        $where[] = "p.price <= ?";
                        $params[] = $max_price;
                        $types .= 'd';
                    }

                    // Get total products for pagination
                    $count_sql = "SELECT COUNT(p.id) FROM products p";
                    if (isset($sql_join_cat)) $count_sql .= $sql_join_cat;
                    if (!empty($where)) $count_sql .= " WHERE " . implode(" AND ", $where);

                    $count_stmt = $mysqli->prepare($count_sql);
                    if (!empty($params)) $count_stmt->bind_param($types, ...$params);
                    $count_stmt->execute();
                    $total_products = $count_stmt->get_result()->fetch_row()[0];
                    $count_stmt->close();

                    $total_pages = ceil($total_products / $products_per_page);

                    if ($current_page > $total_pages && $total_pages > 0) {
                        $current_page = $total_pages;
                        $offset = ($current_page - 1) * $products_per_page;
                    }

                    if (isset($sql_join_cat)) $sql .= $sql_join_cat;
                    if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
                    $orderClause = " ORDER BY p.id DESC";
                    switch ($sort_order) {
                        case 'price_desc':
                            $orderClause = " ORDER BY p.price DESC";
                            break;
                        case 'price_asc':
                            $orderClause = " ORDER BY p.price ASC";
                            break;
                        case 'name_asc':
                            $orderClause = " ORDER BY p.name ASC";
                            break;
                        case 'name_desc':
                            $orderClause = " ORDER BY p.name DESC";
                            break;
                        default:
                            $orderClause = " ORDER BY p.id DESC";
                            break;
                    }

                    $sql .= $orderClause . " LIMIT ? OFFSET ?";
                    $types .= 'ii';
                    $params[] = $products_per_page;
                    $params[] = $offset;

                    $stmt = $mysqli->prepare($sql);
                    if (!empty($params)) $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>

                <div class="row">
                    <div class="col-12">
                        <div class="product-topbar d-xl-flex align-items-end justify-content-between">
                            <!-- Hiển thị số lượng sản phẩm -->
                            <div class="total-products">
                                <?php
                                    $start_product = $offset + 1;
                                    $end_product = $offset + $result->num_rows;
                                    if ($total_products > 0) {
                                        echo "<p>Hiển thị {$start_product}–{$end_product} trên tổng số {$total_products} sản phẩm</p>";
                                    } else {
                                        echo "<p>Không có sản phẩm nào</p>";
                                    }
                                ?>
                            </div>
                            <!-- Bộ lọc -->
                            <div class="product-sorting d-flex">
                                <div class="sort-by-date d-flex align-items-center mr-15">
                                    <p>Bộ Lọc</p>
                                    <form action="#" method="get">
                                        <select name="select" id="sortBydate">
                                            <option value="value">Mới nhất</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  

                <div class="row">
                    <?php
                        // --- VÒNG LẶP HIỂN THỊ SẢN PHẨM (LOGIC ĐÃ CHUYỂN LÊN TRÊN) ---
                        if ($result && $result->num_rows > 0) { 
                            while($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-12 col-sm-6 col-md-12 col-xl-6">
                        <div class="single-product-wrapper">

                            <div class="product-img">
                                <a href="product-details.php?id=<?php echo $row['id']; ?>" aria-label="Xem chi tiết sản phẩm <?php echo htmlspecialchars($row['name']); ?>">
                                    <img src="/baitap3/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">

                                    <img class="hover-img" src="img/core-img/logo.png" alt="">
                                </a>
                            </div>

                            <!-- Mô tả sản phẩm -->
                            <div class="product-description d-flex align-items-center justify-content-between">
                                <div class="product-meta-data">
                                    <div class="line"></div>
                                    <p class="product-price"><?php echo number_format($row['price'], 0, ',', '.'); ?>VND</p>
                                    <a href="product-details.php?id=<?php echo $row['id']; ?>">
                                        <h6><?php echo htmlspecialchars($row['name']); ?></h6>
                                    </a>
                                </div>
                                <!-- Đánh giá và nút thêm vào giỏ hàng -->
                                <div class="ratings-cart text-right">
                                    <!--
                                    <div class="ratings">
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                    </div>
                                    -->
                                    <div class="cart">
                                        <a href="shop.php?add_to_cart=<?php echo $row['id']; ?>" data-toggle="tooltip" data-placement="left" title="Thêm vào giỏ hàng"><img src="../img/core-img/cart.png" alt=""></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                            }
                        } elseif (!$search_term && !$category_slug && !$brand_id && $min_price === null && $max_price === null) {
                            echo "<p>Không có sản phẩm nào để hiển thị.</p>";
                        }
                        $stmt->close();
                    ?>
                </div>

                <div class="row">
                    <div class="col-12">
                        
                        <nav aria-label="navigation">
                            <ul class="pagination justify-content-end mt-50"> 
                                <?php if ($total_pages > 1): ?>
                                    <?php
                                        $query_params = $_GET;
                                        unset($query_params['page']); // Xóa tham số 'page' hiện tại để không bị trùng lặp
                                        $base_query = http_build_query($query_params);
                                        $base_url = '?' . ($base_query ? $base_query . '&' : '');
                                    ?>
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item"><a class="page-link" href="<?= $base_url ?>page=<?= $current_page - 1 ?>"><i class="fa fa-angle-left"></i></a></li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>"><a class="page-link" href="<?= $base_url ?>page=<?= $i ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></a></li>
                                    <?php endfor; ?>

                                    <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item"><a class="page-link" href="<?= $base_url ?>page=<?= $current_page + 1 ?>"><i class="fa fa-angle-right"></i></a></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </nav>
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
                            <a href="../index.php"><img src="../img/core-img/logo2.png" alt=""></a>
                        </div>
                        
                        <p class="copywrite">
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Công ty TNHH Raumania</a> & Re-distributed by <a href="https://themewagon.com/" target="_blank">Themewagon</a>

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
    // Đợi tài liệu được tải xong
    document.addEventListener('DOMContentLoaded', function () {
        // Tìm phần tử thanh trượt giá
        var priceSlider = document.querySelector('.slider-range-price');
        if (priceSlider) {
            // Sử dụng jQuery UI slider đã được khởi tạo bởi active.js
            var slider = $(priceSlider);

            // Bắt sự kiện 'stop' khi người dùng ngừng kéo
            slider.on('slidestop', function (event, ui) {
                var minPrice = ui.values[0];
                var maxPrice = ui.values[1];

                // Lấy URL hiện tại và các tham số của nó
                var currentUrl = new URL(window.location.href);
                // Đặt tham số giá mới
                currentUrl.searchParams.set('min_price', minPrice);
                currentUrl.searchParams.set('max_price', maxPrice);
                // Chuyển hướng đến URL mới để lọc sản phẩm
                window.location.href = currentUrl.toString();
            });
        }
    });
    </script>

</body>

</html>