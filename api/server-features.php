<?php
// 服务器特点列表获取API端点

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
    
    // 获取服务器特点列表
    $result = $db->query("SELECT icon_code, title, description FROM server_features ORDER BY sort_order ASC, id ASC");
    $features = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $features[] = $row;
    }
    
    echo json_encode(['features' => $features]);
    $db->close();
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>