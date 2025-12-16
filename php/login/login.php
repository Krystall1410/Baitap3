<?php

define('APP_ROOT', dirname(__DIR__, 2)); 
//
define('BASE_URL', '/baitap3'); 
define('BRAND_URL', BASE_URL . '/php/brand');
define('CATEGORY_URL', BASE_URL . '/php/category');
define('PRODUCT_URL', BASE_URL . '/php/products');
 
session_start();

require_once __DIR__ . '/config.php';

$resetMessage = '';
$resetMessageType = '';
$resetUsernameValue = '';
$shouldShowResetForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_action'])) {
    $resetUsername = trim($_POST['reset_username'] ?? '');
    $resetPassword = $_POST['reset_password'] ?? '';
    $resetUsernameValue = $resetUsername;

    if ($resetUsername === '' || $resetPassword === '') {
        $resetMessage = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu mới.';
        $resetMessageType = 'error';
    } elseif (strlen($resetPassword) < 6) {
        $resetMessage = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        $resetMessageType = 'error';
    } else {
        $checkStmt = $mysqli->prepare('SELECT id FROM users WHERE username = ?');
        if ($checkStmt) {
            $checkStmt->bind_param('s', $resetUsername);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows === 1) {
                $checkStmt->bind_result($userId);
                $checkStmt->fetch();
                $checkStmt->free_result();

                $newPasswordHash = password_hash($resetPassword, PASSWORD_DEFAULT);
                $updateStmt = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
                if ($updateStmt) {
                    $updateStmt->bind_param('si', $newPasswordHash, $userId);
                    if ($updateStmt->execute()) {
                        $resetMessage = 'Đã cập nhật mật khẩu mới. Vui lòng đăng nhập lại.';
                        $resetMessageType = 'success';
                        $resetUsernameValue = '';
                    } else {
                        $resetMessage = 'Không thể cập nhật mật khẩu. Vui lòng thử lại sau.';
                        $resetMessageType = 'error';
                    }
                    $updateStmt->close();
                } else {
                    $resetMessage = 'Không thể chuẩn bị truy vấn cập nhật.';
                    $resetMessageType = 'error';
                }
            } else {
                $resetMessage = 'Không tìm thấy tài khoản với tên đăng nhập đã nhập.';
                $resetMessageType = 'error';
            }

            $checkStmt->close();
        } else {
            $resetMessage = 'Không thể chuẩn bị truy vấn kiểm tra tài khoản.';
            $resetMessageType = 'error';
        }
    }
}

$shouldShowResetForm = $resetMessage !== '';

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_COOKIE['username']) && !empty($_COOKIE['username'])) {
    $saved_username = $_COOKIE['username'];

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
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            text-align: left;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #555;
            margin: 0;
        }
        .forgot-toggle {
            display: inline-block;
            color: #555;
            font-size: 13px;
            cursor: pointer;
            padding: 0;
            margin: 0;
        }
        .forgot-toggle:hover,
        .forgot-toggle:focus {
            color: #555;
            text-decoration: underline;
            outline: none;
        }
        .error-message {
            color: #d9534f;
            margin-bottom: 15px;
        }
        .forgot-password {
            margin-top: 35px;
            text-align: left;
            display: none;
        }
        .forgot-password.visible {
            display: block;
        }
        .forgot-password h3 {
            margin-bottom: 12px;
            color: #333;
            font-size: 18px;
        }
        .forgot-password p.description {
            margin: 0 0 12px;
            color: #666;
            font-size: 14px;
        }
        .forgot-password input[type="text"],
        .forgot-password input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .forgot-password button {
            margin-top: 2px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        .message.success {
            background: #e6f7ea;
            color: #2b8a3e;
            border: 1px solid #c7eed1;
        }
        .message.error {
            background: #fff0f0;
            color: #d9534f;
            border: 1px solid #f3c7c7;
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
            <input type="text" name="username" placeholder="Tên đăng nhập" value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            <input type="password" name="password" placeholder="Mật khẩu" value="<?php echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            <div class="remember-me">
                <label for="remember" class="remember-label">
                    <input type="checkbox" name="remember" id="remember">
                    Ghi nhớ tôi
                </label>
                <span class="forgot-toggle" id="forgotToggle" role="button" tabindex="0">Quên mật khẩu?</span>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
        <div class="forgot-password<?php echo $shouldShowResetForm ? ' visible' : ''; ?>" id="forgotSection">
            <h3>Quên mật khẩu?</h3>
            <p class="description">Nhập tên đăng nhập của bạn để đặt lại mật khẩu mới.</p>
            <?php if ($resetMessage !== ''): ?>
                <p class="message <?php echo $resetMessageType === 'success' ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($resetMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <input type="hidden" name="reset_action" value="1">
                <input type="text" name="reset_username" placeholder="Tên đăng nhập" value="<?php echo htmlspecialchars($resetUsernameValue ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                <input type="password" name="reset_password" placeholder="Mật khẩu mới" required>
                <button type="submit">Đặt lại mật khẩu</button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('forgotToggle');
            const resetSection = document.getElementById('forgotSection');
            function toggleReset() {
                resetSection.classList.toggle('visible');
            }

            if (toggleBtn && resetSection) {
                toggleBtn.addEventListener('click', toggleReset);
                toggleBtn.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggleReset();
                    }
                });
            }
        });
    </script>
</body>
</html>
