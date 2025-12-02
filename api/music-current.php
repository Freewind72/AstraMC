<?php
// 当前音乐URL获取API端点（供管理后台使用）

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Admin-Auth');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 检查是否来自管理后台的请求
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$isAdminRequest = strpos($referer, '/admin/') !== false;

// 也允许通过特定header进行身份验证
$authHeader = $_SERVER['HTTP_X_ADMIN_AUTH'] ?? '';
$isAuthorized = $isAdminRequest || $authHeader === 'admin-panel';

if (!$isAuthorized) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 检查数据库文件是否存在且不为空，如果存在但大小为0则删除它
$dbPath = '../sql/visits.db';
if (file_exists($dbPath) && filesize($dbPath) === 0) {
    unlink($dbPath);
}

// 连接到SQLite数据库
try {
    $db = new SQLite3($dbPath);
    // 创建音乐设置表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS music_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        music_url TEXT NOT NULL,
        auto_play BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// 处理GET请求（获取音乐URL）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $db->query("SELECT music_url FROM music_settings ORDER BY id DESC LIMIT 1");
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo json_encode(['url' => $row['music_url']]);
    } else {
        // 返回默认音乐URL
        echo json_encode(['url' => 'https://example.com/default-music.mp3']);
    }
    
    $db->close();
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>