<?php
// Include tệp cấu hình CSDL
require_once "config.php";

// Kiểm tra xem form đã được gửi đi chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    // --- KIỂM TRA TÊN ĐĂNG NHẬP CÓ TỒN TẠI CHƯA ---
    $sql_check = "SELECT id FROM users WHERE username = ?";
    if ($stmt_check = $mysqli->prepare($sql_check)) {
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Tên đăng nhập đã tồn tại
            header("Location: register.php?status=exists");
            exit();
        }
        $stmt_check->close();
    }
    // ---------------------------------------------

    // --- THÊM NGƯỜI DÙNG MỚI VÀO CSDL ---
    // Chuẩn bị câu lệnh INSERT
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        // Mã hóa mật khẩu để tăng cường bảo mật
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Gắn các biến vào câu lệnh đã chuẩn bị
        // "sss" có nghĩa là 3 tham số đều là kiểu string (chuỗi)
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        // Thực thi câu lệnh
        if ($stmt->execute()) {
            // Đăng ký thành công, chuyển hướng về trang đăng ký với thông báo
            header("Location: register.php?status=success");
        } else {
            // Có lỗi xảy ra
            header("Location: register.php?status=error");
        }

        // Đóng câu lệnh
        $stmt->close();
    }
    // -----------------------------------------

    // Đóng kết nối
    $mysqli->close();
}
?>
