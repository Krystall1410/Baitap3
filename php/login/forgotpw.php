<?php

define('APP_ROOT', dirname(__DIR__, 2));
define('BASE_URL', '/baitap3');

session_start();

require_once __DIR__ . '/config.php';

$resetMessage = '';
$resetMessageType = '';
$resetEmailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetEmail = trim($_POST['reset_email'] ?? '');
    $resetPassword = $_POST['reset_password'] ?? '';
    $resetEmailValue = $resetEmail;

    if ($resetEmail === '' || $resetPassword === '') {
        $resetMessage = 'Vui lòng nhập đầy đủ email và mật khẩu mới.';
        $resetMessageType = 'error';
    } elseif (!filter_var($resetEmail, FILTER_VALIDATE_EMAIL)) {
        $resetMessage = 'Email không hợp lệ.';
        $resetMessageType = 'error';
    } elseif (strlen($resetPassword) < 6) {
        $resetMessage = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        $resetMessageType = 'error';
    } else {
        $checkStmt = $mysqli->prepare('SELECT id, password FROM users WHERE email = ?');
        if ($checkStmt) {
            $checkStmt->bind_param('s', $resetEmail);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows === 1) {
                $checkStmt->bind_result($userId, $currentPasswordHash);
                $checkStmt->fetch();
                $checkStmt->free_result();

                if (password_verify($resetPassword, $currentPasswordHash)) {
                    $resetMessage = 'Không được đặt giống với mật khẩu gần đây.';
                    $resetMessageType = 'error';
                } else {
                    $newPasswordHash = password_hash($resetPassword, PASSWORD_DEFAULT);
                    $updateStmt = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
                    if ($updateStmt) {
                        $updateStmt->bind_param('si', $newPasswordHash, $userId);
                        if ($updateStmt->execute()) {
                            $resetMessage = 'Đã đặt mật khẩu mới thành công.';
                            $resetMessageType = 'success';
                            $resetEmailValue = '';
                        } else {
                            $resetMessage = 'Không thể cập nhật mật khẩu. Vui lòng thử lại sau.';
                            $resetMessageType = 'error';
                        }
                        $updateStmt->close();
                    } else {
                        $resetMessage = 'Không thể chuẩn bị truy vấn cập nhật.';
                        $resetMessageType = 'error';
                    }
                }
            } else {
                $resetMessage = 'Không tìm thấy tài khoản với email đã nhập.';
                $resetMessageType = 'error';
            }

            $checkStmt->close();
        }
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
    <title>Đặt lại mật khẩu</title>
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
        .reset-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .reset-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .reset-container p.description {
            margin-bottom: 24px;
            color: #666;
            font-size: 14px;
        }
        .reset-container input[type="email"],
        .reset-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 14px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .reset-container input:focus {
            outline: none;
            border-color: #fbb710;
            box-shadow: 0 0 0 3px rgba(251, 183, 16, 0.18);
        }
        .reset-container button {
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
        .reset-container button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(0,0,0,0.12);
        }
        .reset-container button:active {
            transform: translateY(0);
            box-shadow: 0 10px 18px rgba(0,0,0,0.08);
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
        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #555;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Đặt lại mật khẩu</h2>
        <p class="description">Nhập email đã đăng ký và mật khẩu mới để đặt lại tài khoản của bạn.</p>
        <?php if ($resetMessage !== ''): ?>
            <div class="message <?php echo $resetMessageType === 'success' ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($resetMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <?php if ($resetMessageType !== 'success'): ?>
            <form action="forgotpw.php" method="post">
                <input type="email" name="reset_email" placeholder="Email đã đăng ký" value="<?php echo htmlspecialchars($resetEmailValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                <input type="password" name="reset_password" placeholder="Mật khẩu mới" required>
                <button type="submit">Đặt lại mật khẩu</button>
            </form>
        <?php endif; ?>
        <?php if ($resetMessageType === 'success'): ?>
            <a class="back-link" href="login.php">Đăng nhập lại</a>
        <?php endif; ?>
    </div>
</body>
</html>