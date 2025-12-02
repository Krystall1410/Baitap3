<?php
session_start();


require_once "config.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Chuẩn bị câu lệnh SELECT để tránh lỗi SQL Injection
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        // Gắn biến 
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            // Lưu kết quả
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // Gắn kết quả 
                $stmt->bind_result($id, $db_username, $hashed_password, $role);
                if ($stmt->fetch()) {
  
                    if (password_verify($password, $hashed_password)) {
                        
                        session_regenerate_id();

                        $_SESSION['loggedin'] = true;
                        $_SESSION['id'] = $id;
                        $_SESSION['username'] = $db_username;
                        $_SESSION['role'] = $role;

                        if (!empty($_POST['remember'])) {
                            setcookie('username', $username, time() + (86400 * 30), "/");
                        } else {
                            if(isset($_COOKIE['username'])) {
                                setcookie('username', '', time() - 3600, "/");
                            }
                        }

                       if ($role === 'admin') {
                           header("Location: /baitap3/php/login/admin.php");
                            exit;
                        } else {
                            header("Location: /baitap3/view/index.php");
                            exit;
                        }

                    }
                }
            }
        }
        // Nếu không có dòng nào được trả về hoặc mật khẩu sai, chuyển hướng với lỗi
        // Đặt câu lệnh chuyển hướng ở đây để xử lý cả hai trường hợp: sai tên người dùng hoặc sai mật khẩu.
        $stmt->close();
        $mysqli->close();
        header("Location: login.php?error=1"); // Lỗi: Tên người dùng hoặc mật khẩu không đúng
        exit;
    }
    
    $mysqli->close();
}
?>
