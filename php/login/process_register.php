<?php
// BẬT BÁO LỖI ĐỂ GỠ RỐI
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include tệp cấu hình CSDL
require_once "config.php";

// Kiểm tra xem form đã được gửi đi chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra xem các biến POST có tồn tại không
    if (!isset($_POST["username"]) || !isset($_POST["password"]) || !isset($_POST["role"])) {
        die("Lỗi: Dữ liệu từ form không đầy đủ.");
    }

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    // Kiểm tra tên đăng nhập có tồn tại chưa
    $sql_check = "SELECT id FROM users WHERE username = ?";
    if ($stmt_check = $mysqli->prepare($sql_check)) {
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            header("Location: register.php?status=exists");
            exit();
        }
        $stmt_check->close();
    } else {
        // Nếu không thể chuẩn bị câu lệnh, hiển thị lỗi
        die("Lỗi khi chuẩn bị câu lệnh kiểm tra username: " . $mysqli->error);
    }

    // Thêm người dùng mới vào CSDL
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        // Thực thi câu lệnh và kiểm tra lỗi
        if ($stmt->execute()) {
            // Nếu thành công, chuyển hướng như cũ
            header("Location: register.php?status=success");
        } else {
            // Nếu thất bại, hiển thị lỗi chính xác
            echo "Lỗi khi thực thi câu lệnh INSERT: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Nếu không thể chuẩn bị câu lệnh INSERT, hiển thị lỗi
        echo "Lỗi khi chuẩn bị câu lệnh INSERT: " . $mysqli->error;
    }
    
    $mysqli->close();
} else {
    echo "Yêu cầu không hợp lệ.";
}
?>

