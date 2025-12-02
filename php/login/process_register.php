<?php
session_start();
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        header("Location: register.php?status=error");
        exit;
    }

    // kiểm tra username đã tồn tại
    $checkSql = "SELECT id FROM users WHERE username = ?";
    if ($stmt = $mysqli->prepare($checkSql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $mysqli->close();
            header("Location: register.php?status=exists");
            exit;
        }
        $stmt->close();
    }

    // role mặc định
    $role = 'user';

    // chèn user mới (mã hoá mật khẩu)
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $insertSql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    if ($stmt = $mysqli->prepare($insertSql)) {
        $stmt->bind_param("sss", $username, $hashed, $role);
        if ($stmt->execute()) {
            $stmt->close();
            $mysqli->close();
            header("Location: register.php?status=success");
            exit;
        } else {
            $stmt->close();
        }
    }

    $mysqli->close();
    header("Location: register.php?status=error");
    exit;
}
?>