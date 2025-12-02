<?php
/*
PHẦN NÀY TẠM THỜI ĐƯỢC VÔ HIỆU HÓA
Chức năng này sẽ cập nhật số lượng sản phẩm trong biến $_SESSION['cart'] của PHP.
Khi cần sử dụng, chỉ cần bỏ các dấu ghi chú này đi và đảm bảo logic bên trong
phù hợp với yêu cầu của bạn.
*/
 
// Trả về một phản hồi JSON trống để tránh lỗi ở phía client
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Functionality disabled.']);