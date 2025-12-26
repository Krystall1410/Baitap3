
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
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
        .register-container input[type="text"],
        .register-container input[type="password"],
        .register-container input[type="email"],
        .register-container select {
            width: 100%;
            padding: 12px;
            margin-bottom: 14px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .register-container input:focus {
            outline: none;
            border-color: #fbb710;
            box-shadow: 0 0 0 3px rgba(251, 183, 16, 0.18);
        }
        .register-container button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #ffc833, #f5b000);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 18px rgba(0,0,0,0.08);
        }
        .register-container button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(0,0,0,0.12);
        }
        .register-container button:active {
            transform: translateY(0);
            box-shadow: 0 10px 18px rgba(0,0,0,0.08);
        }
        .remember-me {
            margin-bottom: 20px;
            text-align: left;
        }
        .error-message {
            color: #d9534f;
            margin-bottom: 15px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
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
        .login-link {
            margin-top: 12px;
            color: #555;
        }
        .login-link a {
            color: #fbb710;
            text-decoration: none;
        }
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
            } elseif ($_GET['status'] == 'emailexists') {
                echo '<p class="message error">Email đã được đăng ký!</p>';
            } elseif ($_GET['status'] == 'error') {
                echo '<p class="message error">Có lỗi xảy ra, vui lòng thử lại.</p>';
            }
        }
        ?>
        <form action="process_register.php" method="post">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng ký</button>
        </form>
        <div class="login-link">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
        </div>
    </div>
</body>
</html>