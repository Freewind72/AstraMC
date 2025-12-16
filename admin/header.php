<?php
ob_start();
session_start();

// 引入配置文件
require_once 'config.php';

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
        $defaultServerAddress = DEFAULT_SERVER_ADDRESS;
        $defaultServerName = DEFAULT_SERVER_NAME;
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
    
    // 检查是否需要添加grayscale_mode和auto_grayscale_dates字段到site_settings表
    $columnsResult = $db->query("PRAGMA table_info(site_settings)");
    $hasGrayscaleModeColumn = false;
    $hasAutoGrayscaleDatesColumn = false;
    
    while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'grayscale_mode') {
            $hasGrayscaleModeColumn = true;
        }
        if ($column['name'] === 'auto_grayscale_dates') {
            $hasAutoGrayscaleDatesColumn = true;
        }
    }
    
    if (!$hasGrayscaleModeColumn) {
        $db->exec("ALTER TABLE site_settings ADD COLUMN grayscale_mode INTEGER DEFAULT 0");
    }
    
    if (!$hasAutoGrayscaleDatesColumn) {
        $db->exec("ALTER TABLE site_settings ADD COLUMN auto_grayscale_dates TEXT DEFAULT '12-13'");
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
define('PAGE_TITLE', ADMIN_PANEL_TITLE);

// 生成CSRF令牌
$csrfToken = $securityManager->generateCSRFToken();

// 防止表单重复提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !$securityManager->validateCSRFToken($_POST['csrf_token'])) {
        // 使用JavaScript在当前页面显示错误，而不是跳转
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                alert("无效的请求令牌，请重试");
                window.location.hash = "";
            });
        </script>';
        // 终止脚本执行，但不跳转
        exit();
    }
    
    // 检查是否是重复提交
    $requestFingerprint = md5(serialize($_POST));
    if (isset($_SESSION['last_request_fingerprint']) && 
        $_SESSION['last_request_fingerprint'] === $requestFingerprint &&
        isset($_SESSION['last_request_time']) && 
        (time() - $_SESSION['last_request_time']) < 5) { // 5秒内不允许重复提交
        // 使用JavaScript在当前页面显示错误，而不是跳转
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                alert("请勿重复提交表单");
                if (window.location.hash) {
                    window.location.hash = window.location.hash;
                } else {
                    window.location.hash = "";
                }
            });
        </script>';
        // 终止脚本执行，但不跳转
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
    <?php 
    // 获取favicon URL
    $faviconUrl = '';
    try {
        $faviconResult = $db->query("SELECT favicon_url FROM site_settings WHERE id = 1 LIMIT 1");
        if ($faviconResult) {
            $faviconRow = $faviconResult->fetchArray(SQLITE3_ASSOC);
            $faviconUrl = $faviconRow ? $faviconRow['favicon_url'] : '';
        }
    } catch (Exception $e) {
        $faviconUrl = '';
    }
    
    if (!empty($faviconUrl)): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($faviconUrl); ?>" type="image/x-icon">
    <?php endif; ?>
    <title><?php echo PAGE_TITLE; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin-styles.css">
</head>
<body>
    <?php include 'components/topbar.php'; ?>
    <?php include 'components/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <?php if (isset($_GET['message'])): ?>
                <div class="message <?php echo strpos($_GET['message'], '成功') !== false || strpos($_GET['message'], '已成功') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>