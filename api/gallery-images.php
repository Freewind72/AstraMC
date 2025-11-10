<?php
// 展览图片列表获取API端点

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
    
    // 获取展览图片列表
    $result = $db->query("SELECT image_url, alt_text FROM gallery_images ORDER BY sort_order ASC, id ASC");
    $images = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $images[] = $row;
    }
    
    echo json_encode(['images' => $images]);
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