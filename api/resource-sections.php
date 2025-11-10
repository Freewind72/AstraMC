<?php
// 资源下载部分获取API端点

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
    
    // 获取资源下载简介信息
    $introResult = $db->query("SELECT title, description FROM resource_sections WHERE section_type = 'intro' ORDER BY sort_order ASC, id ASC LIMIT 1");
    $intro = $introResult->fetchArray(SQLITE3_ASSOC);
    
    // 获取资源下载卡片信息
    $cardsResult = $db->query("SELECT title, description FROM resource_sections WHERE section_type = 'card' ORDER BY sort_order ASC, id ASC");
    $cards = [];
    while ($row = $cardsResult->fetchArray(SQLITE3_ASSOC)) {
        $cards[] = $row;
    }
    
    echo json_encode([
        'intro' => $intro,
        'cards' => $cards
    ]);
    
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