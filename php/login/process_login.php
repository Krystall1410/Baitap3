<?php
// Bắt đầu session
session_start();

// Include tệp cấu hình CSDL
require_once "config.php";

// Kiểm tra xem dữ liệu đã được gửi đi chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Chuẩn bị câu lệnh SELECT để tránh lỗi SQL Injection
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        // Gắn biến vào câu lệnh
        $stmt->bind_param("s", $username);

        // Thực thi câu lệnh
        if ($stmt->execute()) {
            // Lưu kết quả
            $stmt->store_result();

            // Kiểm tra xem tên đăng nhập có tồn tại không
            if ($stmt->num_rows == 1) {
                // Gắn kết quả vào các biến
                $stmt->bind_result($id, $db_username, $hashed_password, $role);
                if ($stmt->fetch()) {
                    // Xác thực mật khẩu
                    if (password_verify($password, $hashed_password)) {
                        // Mật khẩu chính xác, bắt đầu một session mới
                        
                        // Hủy session cũ và tạo session mới để bảo mật (chống session fixation)
                        session_regenerate_id();

                        // Lưu dữ liệu vào biến session
                        $_SESSION['loggedin'] = true;
                        $_SESSION['id'] = $id;
                        $_SESSION['username'] = $db_username;
                        $_SESSION['role'] = $role;

                        // Xử lý "Ghi nhớ tôi" (tương tự như trước)
                        if (!empty($_POST['remember'])) {
                            setcookie('username', $username, time() + (86400 * 30), "/");
                        } else {
                            if(isset($_COOKIE['username'])) {
                                setcookie('username', '', time() - 3600, "/");
                            }
                        }

                        // Chuyển hướng dựa trên vai trò
                       if ($role === 'admin') {
                           header("Location: admin.php");
                           header("Location: /baitap3/php/login/admin.php");
                            exit;
                        } else {
                            header("Location: index.html");
                           header("Location: /baitap3/index.html");
                            exit;
                        }

                    }   
                }
            }
        }
        $stmt->close();
    }
    
    // Nếu tên đăng nhập hoặc mật khẩu sai
    header("Location: login.php?error=1");
    exit;

    // Đóng kết nối
    $mysqli->close();
}
?>

