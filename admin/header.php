<?php
ob_start();
session_start();

// 检查是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    ob_end_flush();
    exit();
}

// 引入安全管理系统
require_once 'security/SecurityManager.php';

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 检查数据库文件是否存在且不为空，如果存在但大小为0则删除它
$dbPath = '../sql/settings.db';
if (file_exists($dbPath) && filesize($dbPath) === 0) {
    unlink($dbPath);
}

// 连接到数据库
try {
    $db = new SQLite3($dbPath);
    $db->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建音乐表
    $db->exec("CREATE TABLE IF NOT EXISTS music_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        music_url TEXT NOT NULL,
        auto_play BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建资源包设置表
    $db->exec("CREATE TABLE IF NOT EXISTS resource_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        resource_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建服务器设置表
    $db->exec("CREATE TABLE IF NOT EXISTS server_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_address TEXT NOT NULL,
        server_name TEXT NOT NULL DEFAULT '原始大陆',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建备用服务器设置表
    $db->exec("CREATE TABLE IF NOT EXISTS server_settings_secondary (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_address TEXT NOT NULL,
        server_name TEXT NOT NULL DEFAULT '备用服务器',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建视频背景设置表
    $db->exec("CREATE TABLE IF NOT EXISTS video_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        video_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建加入我们二维码链接设置表
    $db->exec("CREATE TABLE IF NOT EXISTS join_qr_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        qr_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 创建服务器特点设置表
    $db->exec("CREATE TABLE IF NOT EXISTS server_features (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        icon_code TEXT NOT NULL,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 创建精选展览图片设置表
    $db->exec("CREATE TABLE IF NOT EXISTS gallery_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        image_url TEXT NOT NULL,
        alt_text TEXT DEFAULT '游戏截图',
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 创建管理团队成员设置表
    $db->exec("CREATE TABLE IF NOT EXISTS team_members (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        qq_number TEXT NOT NULL,
        name TEXT NOT NULL,
        role TEXT NOT NULL,
        description TEXT NOT NULL,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 创建资源下载简介和卡片设置表
    $db->exec("CREATE TABLE IF NOT EXISTS resource_sections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        section_type TEXT NOT NULL, -- 'intro' for introduction, 'card' for cards
        title TEXT,
        description TEXT,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 创建加入我们设置表
    $db->exec("CREATE TABLE IF NOT EXISTS join_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        server_address TEXT NOT NULL,
        server_version TEXT NOT NULL,
        qq_group TEXT NOT NULL,
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
    
    // 创建管理员活动日志表
    $db->exec("CREATE TABLE IF NOT EXISTS admin_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_id INTEGER NOT NULL,
        action TEXT NOT NULL,
        details TEXT,
        ip_address TEXT NOT NULL,
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查是否需要添加 server_name 列（兼容旧版本表结构）
    $columnsResult = $db->query("PRAGMA table_info(server_settings)");
    $hasServerNameColumn = false;
    while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'server_name') {
            $hasServerNameColumn = true;
            break;
        }
    }

    if (!$hasServerNameColumn) {
        $db->exec("ALTER TABLE server_settings ADD COLUMN server_name TEXT NOT NULL DEFAULT '原始大陆'");
    }

    // 检查是否需要添加 server_name 列（兼容旧版本表结构）到备用服务器表
    $columnsResult = $db->query("PRAGMA table_info(server_settings_secondary)");
    $hasServerNameColumnSecondary = false;
    while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'server_name') {
            $hasServerNameColumnSecondary = true;
            break;
        }
    }

    if (!$hasServerNameColumnSecondary) {
        $db->exec("ALTER TABLE server_settings_secondary ADD COLUMN server_name TEXT NOT NULL DEFAULT '备用服务器'");
    }

    // 检查是否需要添加 auto_play 列（兼容旧版本表结构）
    $columnsResult = $db->query("PRAGMA table_info(music_settings)");
    $hasAutoPlayColumn = false;
    while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'auto_play') {
            $hasAutoPlayColumn = true;
            break;
        }
    }

    if (!$hasAutoPlayColumn) {
        $db->exec("ALTER TABLE music_settings ADD COLUMN auto_play BOOLEAN DEFAULT 0");
    }
    
    // 检查是否有默认服务器地址
    $result = $db->query("SELECT server_address, server_name FROM server_settings ORDER BY id DESC LIMIT 1");
    if (!($row = $result->fetchArray(SQLITE3_ASSOC))) {
        // 插入默认服务器地址
        $defaultServerAddress = "mcda.xin";
        $defaultServerName = "原始大陆";
        $stmt = $db->prepare("INSERT INTO server_settings (server_address, server_name) VALUES (:server_address, :server_name)");
        $stmt->bindValue(':server_address', $defaultServerAddress, SQLITE3_TEXT);
        $stmt->bindValue(':server_name', $defaultServerName, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    // 检查是否有默认备用服务器地址
    $resultSecondary = $db->query("SELECT server_address, server_name FROM server_settings_secondary ORDER BY id DESC LIMIT 1");
    if (!($rowSecondary = $resultSecondary->fetchArray(SQLITE3_ASSOC))) {
        // 插入默认备用服务器地址
        $defaultServerSecondaryAddress = "mymcc.xin";
        $defaultServerSecondaryName = "备用服务器";
        $stmt = $db->prepare("INSERT INTO server_settings_secondary (server_address, server_name) VALUES (:server_address, :server_name)");
        $stmt->bindValue(':server_address', $defaultServerSecondaryAddress, SQLITE3_TEXT);
        $stmt->bindValue(':server_name', $defaultServerSecondaryName, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    // 检查管理员账户
    $adminResult = $db->query("SELECT COUNT(*) as count FROM admins");
    $adminRow = $adminResult->fetchArray(SQLITE3_ASSOC);
    if ($adminRow && $adminRow['count'] == 0) {
        // 创建默认管理员账户 (用户名: admin, 密码: admin)
        $defaultPassword = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admins (username, password) VALUES ('admin', :password)");
        $stmt->bindValue(':password', $defaultPassword, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    // 获取服务器特点列表供所有页面使用
    $serverFeatures = [];
    $featuresResult = $db->query("SELECT * FROM server_features ORDER BY sort_order ASC, id ASC");
    while ($row = $featuresResult->fetchArray(SQLITE3_ASSOC)) {
        $serverFeatures[] = $row;
    }
    
    // 初始化安全管理器
    $securityManager = new SecurityManager($db);
    
    // 检查会话是否过期
    if ($securityManager->isSessionExpired()) {
        session_unset();
        session_destroy();
        header('Location: login.php?message=' . urlencode('会话已过期，请重新登录'));
        exit();
    }
    
    // 更新最后活动时间
    $_SESSION['last_activity'] = time();
    
} catch (Exception $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 定义页面标题
define('PAGE_TITLE', '管理面板 - 原始大陆');

// 生成CSRF令牌
$csrfToken = $securityManager->generateCSRFToken();

// 防止表单重复提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !$securityManager->validateCSRFToken($_POST['csrf_token'])) {
        header('Location: index.php?message=' . urlencode('无效的请求令牌，请重试'));
        exit();
    }
    
    // 检查是否是重复提交
    $requestFingerprint = md5(serialize($_POST));
    if (isset($_SESSION['last_request_fingerprint']) && 
        $_SESSION['last_request_fingerprint'] === $requestFingerprint &&
        isset($_SESSION['last_request_time']) && 
        (time() - $_SESSION['last_request_time']) < 5) { // 5秒内不允许重复提交
        header('Location: ' . $_SERVER['PHP_SELF'] . '?message=' . urlencode('请勿重复提交表单'));
        exit();
    }
    
    // 记录请求指纹和时间
    $_SESSION['last_request_fingerprint'] = $requestFingerprint;
    $_SESSION['last_request_time'] = time();
    
    // 记录管理员活动日志
    if (isset($_SESSION['admin_id']) && isset($_POST['action'])) {
        $securityManager->logAdminActivity(
            $_SESSION['admin_id'],
            $_POST['action'],
            '执行了操作: ' . $_POST['action'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title><?php echo PAGE_TITLE; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Microsoft YaHei', sans-serif;
        }

        body {
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .nav {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .nav-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            text-decoration: none;
            color: inherit;
            min-width: 120px;
            white-space: nowrap;
        }

        .nav-item.active {
            color: #3498db;
            border-bottom: 3px solid #3498db;
            background: #f8f9fa;
        }

        .nav-item:hover:not(.active) {
            background: #f8f9fa;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .form-hint {
            margin-top: 5px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .btn {
            padding: 12px 25px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #27ae60;
        }

        .btn-success:hover {
            background: #219653;
        }

        .preview {
            background: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
        }

        .preview-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #555;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
            }
            
            .nav {
                display: none;
                flex-direction: column;
            }
            
            .nav.active {
                display: flex;
            }
            
            .nav-item {
                text-align: left;
                border-bottom: 1px solid #eee;
                border-left: 3px solid transparent;
            }
            
            .nav-item.active {
                border-bottom: 1px solid #eee;
                border-left: 3px solid #3498db;
            }
            
            .hamburger {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-cogs"></i> 管理面板
            </div>
            <div class="user-info">
                <span>欢迎, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> 登出
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="nav" id="nav">
            <a href="music.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'music.php' ? 'active' : ''; ?>">
                <i class="fas fa-music"></i> 音乐管理
            </a>
            <a href="resource.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'resource.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-archive"></i> 资源包管理
            </a>
            <a href="server.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'server.php' ? 'active' : ''; ?>">
                <i class="fas fa-server"></i> 服务器设置
            </a>
            <a href="video.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'video.php' ? 'active' : ''; ?>">
                <i class="fas fa-video"></i> 视频背景
            </a>
            <a href="join.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'join.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> 加入我们
            </a>
            <a href="gallery.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'gallery.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> 精选展览
            </a>
            <a href="team.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'team.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> 管理团队
            </a>
            <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> 系统设置
            </a>
            <a href="check_password.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'check_password.php' ? 'active' : ''; ?>">
                <i class="fas fa-key"></i> 密码检查
            </a>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="message <?php echo strpos($_GET['message'], '成功') !== false || strpos($_GET['message'], '已成功') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <script>
            document.getElementById('hamburger').addEventListener('click', function() {
                const nav = document.getElementById('nav');
                nav.classList.toggle('active');
            });
        </script>