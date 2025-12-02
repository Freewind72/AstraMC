<?php
// 视频背景URL获取API端点

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
    // 创建视频背景设置表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS video_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        video_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查是否有默认视频背景设置
    $result = $db->query("SELECT video_url FROM video_settings ORDER BY id DESC LIMIT 1");
    if (!($row = $result->fetchArray(SQLITE3_ASSOC))) {
        // 插入默认视频背景URL
        $defaultVideoUrl = "https://vip.123pan.cn/1815439627/24445722";
        $stmt = $db->prepare("INSERT INTO video_settings (video_url) VALUES (:video_url)");
        $stmt->bindValue(':video_url', $defaultVideoUrl, SQLITE3_TEXT);
        $stmt->execute();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// 处理GET请求（获取视频背景URL）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $result = $db->query("SELECT video_url FROM video_settings ORDER BY id DESC LIMIT 1");
        
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo json_encode(['url' => $row['video_url']]);
        } else {
            // 返回默认视频背景URL
            echo json_encode(['url' => 'https://vip.123pan.cn/1815439627/24445722']);
        }
        
        $db->close();
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch video URL: ' . $e->getMessage()]);
        exit();
    }
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>