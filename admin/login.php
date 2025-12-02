<?php
session_start();

// 引入安全管理系统
require_once 'security/SecurityManager.php';

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 连接到SQLite数据库
try {
    $db = new SQLite3('../sql/settings.db');
    // 创建管理员表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 创建登录尝试记录表
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        ip_address TEXT NOT NULL,
        success INTEGER NOT NULL, -- 1 for success, 0 for failure
        attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 创建密码重置令牌表
    $db->exec("CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expiry INTEGER NOT NULL, -- Unix timestamp
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查是否已经存在管理员账户，如果不存在则创建默认账户
    $adminResult = $db->query("SELECT COUNT(*) as count FROM admins");
    $adminRow = $adminResult->fetchArray(SQLITE3_ASSOC);
    if ($adminRow['count'] == 0) {
        // 创建默认管理员账户 (用户名: admin, 密码: admin)
        $defaultPassword = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admins (username, password) VALUES ('admin', :password)");
        $stmt->bindValue(':password', $defaultPassword, SQLITE3_TEXT);
        $stmt->execute();
    }
} catch (Exception $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 初始化安全管理器
$securityManager = new SecurityManager($db);

$error = '';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// 检查账户是否被锁定
if (isset($_POST['username']) && $securityManager->isAccountLocked($_POST['username'], $ipAddress)) {
    $error = '账户已被锁定，请稍后再试';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证CSRF令牌
    if (!isset($_POST['csrf_token']) || !$securityManager->validateCSRFToken($_POST['csrf_token'])) {
        $error = '无效的请求令牌';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (!empty($username) && !empty($password)) {
            // 从数据库中获取用户信息
            $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = :username LIMIT 1");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $result = $stmt->execute();
            $admin = $result->fetchArray(SQLITE3_ASSOC);
            
            // 验证密码（兼容处理：支持明文密码和哈希密码）
            $isAdminPasswordVerified = false;
            if ($admin) {
                if (password_get_info($admin['password'])['algo'] !== null) {
                    // 如果是哈希密码，使用 password_verify 验证
                    $isAdminPasswordVerified = password_verify($password, $admin['password']);
                } else {
                    // 如果是明文密码，直接比较
                    $isAdminPasswordVerified = ($password === $admin['password']);
                }
            }
            
            if ($admin && $isAdminPasswordVerified) {
                // 登录成功
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['last_activity'] = time();
                $securityManager->logLoginAttempt($username, $ipAddress, true);
                // 重新生成CSRF令牌
                unset($_SESSION['csrf_token']);
                header('Location: index.php');
                exit();
            } else {
                // 登录失败
                $securityManager->logLoginAttempt($username, $ipAddress, false);
                $error = '用户名或密码错误';
            }
        } else {
            $error = '请填写用户名和密码';
        }
    }
}

// 生成CSRF令牌
$csrfToken = $securityManager->generateCSRFToken();

$db->close();

// 获取消息
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>管理登录 - 原始大陆</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin-styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            animation: fadeInUp 0.5s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #7f8c8d;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #4361ee;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #3a56e4;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #ffcdd2;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c8e6c9;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-lock"></i> 管理登录</h1>
            <p>请输入您的登录凭证</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="form-group">
                <label class="form-label" for="username">用户名</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input" 
                    placeholder="请输入用户名" 
                    required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">密码</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="请输入密码" 
                    required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> 登录
            </button>
        </form>
        
        <div class="footer">
            &copy; 2025 原始大陆管理系统
        </div>
    </div>
</body>
</html>