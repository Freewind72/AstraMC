<?php
require_once 'header.php';
require_once 'server_features_display.php';

// 处理更改密码请求
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // 验证输入
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = '所有字段都是必填的。';
            header("Location: settings.php?message=" . urlencode($message));
            exit();
        } elseif ($newPassword !== $confirmPassword) {
            $message = '新密码和确认密码不匹配。';
            header("Location: settings.php?message=" . urlencode($message));
            exit();
        } elseif (strlen($newPassword) < 6) {
            $message = '新密码至少需要6个字符。';
            header("Location: settings.php?message=" . urlencode($message));
            exit();
        } else {
            try {
                // 验证当前密码
                $stmt = $db->prepare("SELECT id, password FROM admins WHERE username = :username LIMIT 1");
                $stmt->bindValue(':username', $_SESSION['username'], SQLITE3_TEXT);
                $result = $stmt->execute();
                $admin = $result->fetchArray(SQLITE3_ASSOC);
                
                if ($admin) {
                    // 兼容处理：检查密码是否为哈希密码或者明文密码
                    $isAdminPasswordVerified = false;
                    if (password_get_info($admin['password'])['algo'] !== null) {
                        // 如果是哈希密码，使用 password_verify 验证
                        $isAdminPasswordVerified = password_verify($currentPassword, $admin['password']);
                    } else {
                        // 如果是明文密码，直接比较
                        $isAdminPasswordVerified = ($currentPassword === $admin['password']);
                    }
                    
                    if ($isAdminPasswordVerified) {
                        // 更新密码（始终使用哈希方式存储新密码）
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $updateStmt = $db->prepare("UPDATE admins SET password = :password WHERE id = :id");
                        $updateStmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
                        $updateStmt->bindValue(':id', $admin['id'], SQLITE3_INTEGER);
                        
                        if ($updateStmt->execute()) {
                            // 销毁会话，强制用户重新登录
                            session_unset();
                            session_destroy();
                            // 重定向到登录页面
                            header("Location: login.php?message=" . urlencode('密码已更改，请重新登录'));
                            exit();
                        } else {
                            $message = '更改密码时出错，请重试。';
                            header("Location: settings.php?message=" . urlencode($message));
                            exit();
                        }
                    } else {
                        $message = '当前密码不正确。';
                        header("Location: settings.php?message=" . urlencode($message));
                        exit();
                    }
                } else {
                    $message = '当前密码不正确。';
                    header("Location: settings.php?message=" . urlencode($message));
                    exit();
                }
            } catch (Exception $e) {
                $message = '更改密码时出错：' . $e->getMessage();
                header("Location: settings.php?message=" . urlencode($message));
                exit();
            }
        }
    } elseif ($_POST['action'] === 'change_username') {
        $newUsername = trim($_POST['new_username'] ?? '');
        
        // 验证输入
        if (empty($newUsername)) {
            $message = '用户名不能为空。';
            header("Location: settings.php?message=" . urlencode($message));
            exit();
        } else {
            try {
                // 检查新用户名是否已存在
                $stmt = $db->prepare("SELECT id FROM admins WHERE username = :username AND username != :current_username LIMIT 1");
                $stmt->bindValue(':username', $newUsername, SQLITE3_TEXT);
                $stmt->bindValue(':current_username', $_SESSION['username'], SQLITE3_TEXT);
                $result = $stmt->execute();
                $existingUser = $result->fetchArray(SQLITE3_ASSOC);
                
                if ($existingUser) {
                    $message = '该用户名已被使用，请选择其他用户名。';
                    header("Location: settings.php?message=" . urlencode($message));
                    exit();
                } else {
                    // 更新用户名
                    $updateStmt = $db->prepare("UPDATE admins SET username = :username WHERE username = :current_username");
                    $updateStmt->bindValue(':username', $newUsername, SQLITE3_TEXT);
                    $updateStmt->bindValue(':current_username', $_SESSION['username'], SQLITE3_TEXT);
                    
                    if ($updateStmt->execute()) {
                        // 更新会话中的用户名
                        $_SESSION['username'] = $newUsername;
                        header("Location: settings.php?message=" . urlencode('用户名已更改成功！'));
                        exit();
                    } else {
                        $message = '更改用户名时出错，请重试。';
                        header("Location: settings.php?message=" . urlencode($message));
                        exit();
                    }
                }
            } catch (Exception $e) {
                $message = '更改用户名时出错：' . $e->getMessage();
                header("Location: settings.php?message=" . urlencode($message));
                exit();
            }
        }
    }
}
?>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-user-edit"></i> 修改用户名
    </h2>
    <form method="post">
        <input type="hidden" name="action" value="change_username">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="new_username">新用户名</label>
            <input 
                type="text" 
                id="new_username" 
                name="new_username" 
                class="form-input" 
                placeholder="请输入新用户名" 
                value="<?php echo htmlspecialchars($_SESSION['username']); ?>"
                required>
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 更改用户名
        </button>
    </form>
</div>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-lock"></i> 更改密码
    </h2>
    <form method="post">
        <input type="hidden" name="action" value="change_password">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="current_password">当前密码</label>
            <input 
                type="password" 
                id="current_password" 
                name="current_password" 
                class="form-input" 
                placeholder="请输入当前密码" 
                required>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="new_password">新密码</label>
            <input 
                type="password" 
                id="new_password" 
                name="new_password" 
                class="form-input" 
                placeholder="请输入新密码（至少6个字符）" 
                required>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="confirm_password">确认新密码</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                class="form-input" 
                placeholder="请再次输入新密码" 
                required>
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 更改密码
        </button>
    </form>
</div>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-info-circle"></i> 系统信息
    </h2>
    <div class="preview">
        <div class="preview-title">当前管理员账户:</div>
        <div><?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div style="margin-top: 10px;">
            <strong>安全提示:</strong> 请定期更改密码以确保系统安全
        </div>
    </div>
</div>

<?php
// 关闭数据库连接
$db->close();

require_once 'footer.php';
?>