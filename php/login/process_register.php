<?php
// Bắt đầu phiên làm việc (session)
session_start();

// Nạp tệp cấu hình CSDL
require_once "config.php";

// Kiểm tra xem yêu cầu có phải là POST không
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Lấy và làm sạch dữ liệu đầu vào
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    // **Bước 1: Kiểm tra xem tên đăng nhập đã tồn tại chưa**
    $sql_check = "SELECT id FROM users WHERE username = ?";

    if ($stmt_check = $mysqli->prepare($sql_check)) {
        // Gán tham số
        $stmt_check->bind_param("s", $param_username_check);
        $param_username_check = $username;

        // Thực thi câu lệnh
        if ($stmt_check->execute()) {
            $stmt_check->store_result();

            // Nếu tên đăng nhập đã có người dùng
            if ($stmt_check->num_rows > 0) {
                // Chuyển hướng về trang đăng ký với thông báo lỗi
                header("location: register.php?status=exists");
                exit();
            }
        } else {
            echo "Oops! Đã có lỗi xảy ra. Vui lòng thử lại sau.";
        }
        // Đóng câu lệnh
        $stmt_check->close();
    }

    // **Bước 2: Nếu tên đăng nhập chưa tồn tại, tiến hành thêm người dùng mới**
    $sql_insert = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
         
    if ($stmt_insert = $mysqli->prepare($sql_insert)) {
        // Gán các biến vào câu lệnh đã chuẩn bị như là tham số
        $stmt_insert->bind_param("sss", $param_username, $param_password, $param_role);
        
        // Thiết lập tham số
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
        $param_role = $role;
        
        // Cố gắng thực thi câu lệnh đã chuẩn bị
        if ($stmt_insert->execute()) {
            // Chuyển hướng đến trang đăng ký với thông báo thành công
            header("location: register.php?status=success");
            exit();
        } else {
            echo "Oops! Đã có lỗi xảy ra. Vui lòng thử lại sau.";
        }

        // Đóng câu lệnh
        $stmt_insert->close();
    }
    
    // Đóng kết nối
    $mysqli->close();
}
?>

