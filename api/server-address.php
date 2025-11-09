<?php
// 服务器地址获取API端点

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
    // 创建服务器设置表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS server_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_address TEXT NOT NULL,
        server_name TEXT NOT NULL DEFAULT '原始大陆',
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
    
    // 检查是否有默认服务器地址
    $result = $db->query("SELECT server_address FROM server_settings ORDER BY id DESC LIMIT 1");
    if (!($row = $result->fetchArray(SQLITE3_ASSOC))) {
        // 插入默认服务器地址
        $defaultServerAddress = "mcda.xin";
        $stmt = $db->prepare("INSERT INTO server_settings (server_address) VALUES (:server_address)");
        $stmt->bindValue(':server_address', $defaultServerAddress, SQLITE3_TEXT);
        $stmt->execute();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// 处理GET请求（获取服务器地址）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $db->query("SELECT server_address, server_name FROM server_settings ORDER BY id DESC LIMIT 1");
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo json_encode([
            'address' => $row['server_address'],
            'name' => $row['server_name']
        ]);
    } else {
        // 返回默认服务器地址和名称
        echo json_encode([
            'address' => 'mcda.xin',
            'name' => '原始大陆'
        ]);
    }
    
    $db->close();
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>