<?php
// 维护模式检查API端点

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
    
    // 获取维护模式设置
    $result = $db->query("SELECT is_active, message FROM maintenance WHERE id = 1");
    $maintenance = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($maintenance) {
        echo json_encode([
            'is_active' => (bool)$maintenance['is_active'],
            'message' => $maintenance['message']
        ]);
    } else {
        echo json_encode([
            'is_active' => false,
            'message' => '服务器正在维护中，请稍后再试。'
        ]);
    }
    
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