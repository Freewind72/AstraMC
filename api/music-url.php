<?php
// 音乐URL获取API端点

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
    // 创建音乐设置表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS music_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        music_url TEXT NOT NULL,
        auto_play BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查是否有默认音乐设置
    $result = $db->query("SELECT music_url FROM music_settings ORDER BY id DESC LIMIT 1");
    if (!($row = $result->fetchArray(SQLITE3_ASSOC))) {
        // 插入默认音乐URL
        $defaultMusicUrl = "https://example.com/default-music.mp3";
        $stmt = $db->prepare("INSERT INTO music_settings (music_url, auto_play) VALUES (:music_url, :auto_play)");
        $stmt->bindValue(':music_url', $defaultMusicUrl, SQLITE3_TEXT);
        $stmt->bindValue(':auto_play', 0, SQLITE3_INTEGER);
        $stmt->execute();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// 处理GET请求（获取音乐URL）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $result = $db->query("SELECT music_url FROM music_settings ORDER BY id DESC LIMIT 1");
        
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo json_encode(['url' => $row['music_url']]);
        } else {
            // 返回默认音乐URL
            echo json_encode(['url' => 'https://example.com/default-music.mp3']);
        }
        
        $db->close();
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch music URL: ' . $e->getMessage()]);
        exit();
    }
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>