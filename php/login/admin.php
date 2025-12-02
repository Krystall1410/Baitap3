<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập và có phải là admin không
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải, chuyển hướng về trang đăng nhập
    header("Location: login.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';


if ($page == 'process_product') {
   include('../admin/process_product.php');

} else if ($page == 'delete_product') {
   include('../admin/delete_product.php');

}


?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title>Admin trang chủ</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      
      <!-- site icon -->
      <link rel="icon" href="/baitap3/images/fevicon.png" type="image/png" />
      <link rel="icon" type="image/png" sizes="16x16"  href="/favicons/favicon-16x16.png">
      <meta name="msapplication-TileColor" content="#ffffff">
      <meta name="theme-color" content="#ffffff">

      <!-- bootstrap css -->
      <link rel="stylesheet" href="/baitap3/assets/css/bootstrap.min.css" />
      <!-- site css -->
      <link rel="stylesheet" href="/baitap3/assets/css/style.css" />
      <!-- responsive css -->
      <link rel="stylesheet" href="/baitap3/assets/css/responsive.css" />
      <!-- color css -->
      <link rel="stylesheet" href="/baitap3/assets/css/colors.css" />
      <!-- select bootstrap -->
      <link rel="stylesheet" href="/baitap3/assets/css/bootstrap-select.css" />
      <!-- scrollbar css -->
      <link rel="stylesheet" href="/baitap3/assets/css/perfect-scrollbar.css" />
      <!-- custom css -->
      <link rel="stylesheet" href="/baitap3/assets/css/custom.css" />
      <style>
         .user_profile_dd .dropdown-menu-right {
            right: 0;
            left: auto;
         }
      </style>
   </head>
   <body class="dashboard dashboard_1">
      <div class="full_container">
         <div class="inner_container">
            <!-- Sidebar  -->
            <nav id="sidebar">
               <div class="sidebar_blog_1">
                  <div class="sidebar-header">
                     <div class="logo_section">
                       
                     </div>
                  </div>
                  <div class="sidebar_user_info">
                     <div class="icon_setting"></div>
                     <div class="user_profle_side">
                        
                        <div class="user_info">
                           <h6>Admin</h6>
                           <p><span class="online_animation"></span> Online</p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="sidebar_blog_2">
                  <h4>Khu vực</h4></h4>
                  <ul class="list-unstyled components">
                     <li class="active">
                        <a href="#dashboard" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-dashboard yellow_color"></i> <span>Danh Mục</span></a>
                        <ul class="collapse list-unstyled" id="dashboard">
                           <li><a href="admin.php?page=products">Sản phẩm — Danh sách</a></li>                           
                           
                        </ul>
                     </li>
               </div>
            </nav>
            <!-- end sidebar -->
            <!-- right content -->
            <div id="content">
               
               <!-- topbar -->
               <div class="topbar">
                  <nav class="navbar navbar-expand-lg navbar-light">
                     <div class="full">
                        <button type="button" id="sidebarCollapse" class="sidebar_toggle"><img src="/baitap3/img/core-img/icon1.png"><i class="fa fa-bars"></i></button>
                        
                        <div class="right_topbar">
                           <div class="icon_info">
                              
                              <ul class="user_profile_dd">
                                 <li>
                                    <a class="dropdown-toggle" data-toggle="dropdown"><span class="name_user">Admin</span></a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                       <a class="dropdown-item" href="/baitap3/php/login/logout.php">Đăng xuất</a>
                                       <a class="dropdown-item" href="/baitap3/php/home/back_home.php">Trang chủ</a>
                                      
                                    </div>
                                 </li>
                              </ul>
                           </div>
                        </div>
                     </div>
                  </nav>
               </div>
   
               <div class="midde_cont">
                  <div class="container-fluid">
                     <?php
                        // --- THAY ĐỔI ---
                        // Dựa vào giá trị của 'page' để include file tương ứng
                        if ($page == 'products') {
                           include('../admin/products.php');
                        } else if ($page == 'product_form') {
                           // Trang thêm/sửa sản phẩm
                           include('../admin/product_form.php');
                        }
                        // Bạn có thể thêm các trường hợp khác ở đây với else if
                     ?>
                  </div>
               </div>
     
            </div>
         </div>
      </div>
      <!-- jQuery -->
      <script src="/baitap3/assets/js/jquery.min.js"></script>
      <script src="/baitap3/assets/js/popper.min.js"></script>
      <script src="/baitap3/assets/js/bootstrap.min.js"></script>
      <!-- wow animation -->
      <script src="/baitap3/assets/js/animate.js"></script>
      <!-- select country -->
      <script src="/baitap3/assets/js/bootstrap-select.js"></script>
      <!-- owl carousel -->
      <script src="/baitap3/assets/js/owl.carousel.js"></script> 
      <!-- chart js -->
      <script src="/baitap3/assets/js/Chart.min.js"></script>
      <script src="/baitap3/assets/js/Chart.bundle.min.js"></script>
      <script src="/baitap3/assets/js/utils.js"></script>
      <script src="/baitap3/assets/js/analyser.js"></script>
      <!-- nice scrollbar -->
      <script src="/baitap3/assets/js/perfect-scrollbar.min.js"></script>
      <script>
         var ps = new PerfectScrollbar('#sidebar');
      </script>
      <!-- custom js -->
      <script src="/baitap3/assets/js/custom.js"></script>
      <script src="/baitap3/assets/js/chart_custom_style1.js"></script>
   </body>
</html>
