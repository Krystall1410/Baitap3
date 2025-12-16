<?php

define('APP_ROOT', dirname(__DIR__, 2)); 
//
define('BASE_URL', '/baitap3'); 
define('BRAND_URL', BASE_URL . '/php/brand');
define('CATEGORY_URL', BASE_URL . '/php/category');
define('PRODUCT_URL', BASE_URL . '/php/products');
 
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/php/login/admin.php');
        exit;
    } else {
        header('Location: ' . BASE_URL . '/view/shop.php');
        exit;
    }
}


$saved_username = '';

if (isset($_COOKIE['username']) && !empty($_COOKIE['username'])) {
    $saved_username = $_COOKIE['username'];

    // nạp config và kiểm tra
    require_once __DIR__ . "/config.php";

    $sql = "SELECT id, username, role FROM users WHERE username = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $saved_username);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $db_username, $role);
                $stmt->fetch();
                session_regenerate_id();
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $db_username;
                $_SESSION['role'] = $role;

                if ($role === 'admin') {
                    header('Location: ' . BASE_URL . '/php/login/admin.php');
                } else {
                    header('Location: ' . BASE_URL . '/view/shop.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: #fbb710; 
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .login-container button:hover {
            background-color: #c9960c;
        }
        .remember-me {
            margin-bottom: 20px;
            text-align: left;
        }
        .error-message {
            color: #d9534f;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập tài khoản</h2>
        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error-message">Tên đăng nhập hoặc mật khẩu không chính xác!</p>';
        }
        ?>
        <form action="process_login.php" method="post">
            <input type="text" name="username" placeholder="Tên đăng nhập" value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : ''; ?>" required>
            <input type="password" name="password" placeholder="Mật khẩu" value="<?php echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : ''; ?>" required>
            <div class="remember-me">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Ghi nhớ tôi</label>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
