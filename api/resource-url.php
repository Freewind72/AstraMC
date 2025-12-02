<?php
// 资源包URL获取API端点

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
    // 创建资源包设置表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS resource_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        resource_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// 处理GET请求（获取资源包URL）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 检查是否请求默认URL
    if (isset($_GET['default']) && $_GET['default'] === 'true') {
        // 返回默认资源包URL
        echo json_encode(['url' => 'https://vip.123pan.cn/1815439627/26358598']);
        $db->close();
        exit();
    }

    $result = $db->query("SELECT resource_url FROM resource_settings ORDER BY id DESC LIMIT 1");
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo json_encode(['url' => $row['resource_url']]);
    } else {
        // 返回默认资源包URL
        echo json_encode(['url' => 'https://vip.123pan.cn/1815439627/26358598']);
    }
    
    $db->close();
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>