<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap&subset=vietnamese" rel="stylesheet">
    <style>
        /* Sử dụng lại style của trang đăng nhập */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .register-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .register-container input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .register-container button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .register-container button:hover {
            background-color: #218838;
        }
        .message {
            margin-bottom: 15px;
        }
        .error { color: #d9534f; }
        .success { color: #28a745; }
        .login-link { margin-top: 15px; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Tạo tài khoản mới</h2>
        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo '<p class="message success">Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.</p>';
            } elseif ($_GET['status'] == 'exists') {
                echo '<p class="message error">Tên đăng nhập đã tồn tại!</p>';
            } elseif ($_GET['status'] == 'error') {
                echo '<p class="message error">Có lỗi xảy ra, vui lòng thử lại.</p>';
            }
        }
        ?>
        <form action="process_register.php" method="post">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <!-- Thêm trường role cho admin, có thể ẩn đi sau này -->
            <div>
                <label for="role">Vai trò:</label>
                <select name="role" id="role">
                    <option value="customer">Khách hàng</option>
                    <option value="admin">Quản trị viên (Admin)</option>
                </select>
            </div>
            <br>
            <button type="submit">Đăng ký</button>
        </form>
        <div class="login-link">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
        </div>
    </div>
</body>
</html>
