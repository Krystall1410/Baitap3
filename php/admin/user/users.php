<?php
require_once __DIR__ . '/../../login/config.php';

if (empty($_SESSION['loggedin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /baitap3/php/login/login.php');
    exit;
}

$status = $_SESSION['user_role_status'] ?? null;
if (!is_array($status)) {
   $status = null;
}
unset($_SESSION['user_role_status']);

$users = [];
$query = $mysqli->query("SELECT id, username, email, role FROM users ORDER BY id ASC");
if ($query instanceof mysqli_result) {
    $users = $query->fetch_all(MYSQLI_ASSOC);
    $query->free();
}
?>
<div class="white_shd full margin_bottom_30">
   <div class="full graph_head">
      <div class="heading1 margin_0">
         <h2>Tài khoản người dùng</h2>
      </div>
   </div>
   <div class="table_section padding_infor_info">
      <?php if ($status !== null): ?>
         <?php
            $statusType = $status['type'] ?? 'info';
            $alertClass = 'info';
            if ($statusType === 'success') {
                $alertClass = 'success';
            } elseif ($statusType === 'error') {
                $alertClass = 'danger';
            }
         ?>
         <div class="alert alert-<?php echo $alertClass; ?>" role="alert">
            <?php echo htmlspecialchars($status['message'], ENT_QUOTES, 'UTF-8'); ?>
         </div>
      <?php endif; ?>
      <div class="table-responsive-sm">
         <table class="table table-hover">
            <thead>
               <tr>
                  <th>#</th>
                  <th>Tên đăng nhập</th>
                  <th>Email</th>
                  <th>Quyền hiện tại</th>
                  <th class="text-center">Cập nhật quyền</th>
               </tr>
            </thead>
            <tbody>
               <?php if (empty($users)): ?>
               <tr>
                  <td colspan="5" class="text-center">Chưa có tài khoản nào.</td>
               </tr>
               <?php else: ?>
                  <?php foreach ($users as $index => $user): ?>
                  <tr>
                     <td><?php echo $index + 1; ?></td>
                     <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                     <td><?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                     <td>
                        <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                           <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                     </td>
                     <td>
                        <div class="d-flex align-items-center justify-content-end">
                           <form class="d-flex align-items-center mb-0" method="post" action="admin.php?page=process_user_role">
                              <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                              <select class="form-control form-control-sm mr-2" name="role">
                                 <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                 <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                              </select>
                              <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                           </form>
                           <form class="d-inline-block mb-0 ml-2" method="post" action="admin.php?page=delete_user&id=<?php echo $user['id']; ?>" onsubmit="return confirm('Bạn có chắc muốn xóa tài khoản này?');">
                              <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                           </form>
                        </div>
                     </td>
                  </tr>
                  <?php endforeach; ?>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
      <p class="text-muted small mb-0">Lưu ý: Không thể hạ quyền tài khoản của chính bạn xuống user để tránh mất quyền truy cập.</p>
   </div>
</div>
