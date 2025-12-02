<?php
// 资源列表获取API端点

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

// 连接到SQLite数据库
try {
    $db = new SQLite3('../sql/settings.db');
    
    // 创建资源表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS resources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        icon TEXT NOT NULL,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        url TEXT NOT NULL,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 获取资源列表
    $result = $db->query("SELECT icon, name, description, url FROM resources ORDER BY sort_order ASC, id ASC");
    $resources = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $resources[] = $row;
    }
    
    echo json_encode(['resources' => $resources]);
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