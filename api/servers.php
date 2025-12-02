<?php
// 服务器列表获取API端点

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 检查数据库文件是否存在且不为空，如果存在但大小为0则删除它
$dbPath = '../sql/settings.db';
if (file_exists($dbPath) && filesize($dbPath) === 0) {
    unlink($dbPath);
}

// 连接到SQLite数据库
try {
    $db = new SQLite3($dbPath);
    
    // 创建多服务器设置表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS servers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_address TEXT NOT NULL,
        server_name TEXT NOT NULL,
        is_primary BOOLEAN DEFAULT 0,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查是否有多服务器设置
    $result = $db->query("SELECT COUNT(*) as count FROM servers");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($row && $row['count'] == 0) {
        // 如果没有服务器设置，从旧表迁移数据
        $primaryResult = $db->query("SELECT server_address, server_name FROM server_settings ORDER BY id DESC LIMIT 1");
        $secondaryResult = $db->query("SELECT server_address, server_name FROM server_settings_secondary ORDER BY id DESC LIMIT 1");
        
        // 插入主服务器
        if ($primaryRow = $primaryResult->fetchArray(SQLITE3_ASSOC)) {
            $stmt = $db->prepare("INSERT INTO servers (server_address, server_name, is_primary, sort_order) VALUES (:server_address, :server_name, 1, 0)");
            $stmt->bindValue(':server_address', $primaryRow['server_address'], SQLITE3_TEXT);
            $stmt->bindValue(':server_name', $primaryRow['server_name'], SQLITE3_TEXT);
            $stmt->execute();
        }
        
        // 插入备用服务器
        if ($secondaryRow = $secondaryResult->fetchArray(SQLITE3_ASSOC)) {
            $stmt = $db->prepare("INSERT INTO servers (server_address, server_name, is_primary, sort_order) VALUES (:server_address, :server_name, 0, 1)");
            $stmt->bindValue(':server_address', $secondaryRow['server_address'], SQLITE3_TEXT);
            $stmt->bindValue(':server_name', $secondaryRow['server_name'], SQLITE3_TEXT);
            $stmt->execute();
        }
    }
    
    // 获取服务器列表
    $result = $db->query("SELECT id, server_address, server_name, is_primary, sort_order FROM servers ORDER BY sort_order ASC, id ASC");
    $servers = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $servers[] = $row;
    }
    
    echo json_encode(['servers' => $servers]);
    $db->close();
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>