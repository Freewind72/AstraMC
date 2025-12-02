<?php
ob_start();
session_start();

// 检查是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => '未授权访问']);
    exit();
}

// 引入安全管理系统
require_once 'security/SecurityManager.php';

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 连接到数据库
try {
    $db = new SQLite3('../sql/settings.db');
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '数据库连接失败: ' . $e->getMessage()]);
    exit();
}

// 初始化安全管理器
$securityManager = new SecurityManager($db);

// 检查会话是否过期
if ($securityManager->isSessionExpired()) {
    session_unset();
    session_destroy();
    echo json_encode(['success' => false, 'message' => '会话已过期，请重新登录']);
    exit();
}

// 更新最后活动时间
$_SESSION['last_activity'] = time();

// 检查请求方法和参数
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action']) || $data['action'] !== 'clear_logs') {
    echo json_encode(['success' => false, 'message' => '无效的操作']);
    exit();
}

// 清空访问记录
try {
    $db->exec("DELETE FROM admin_logs");
    echo json_encode(['success' => true, 'message' => '访问记录已清空']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '清空记录失败: ' . $e->getMessage()]);
}