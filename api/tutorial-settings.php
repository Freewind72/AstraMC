<?php
// 教程文档设置获取API端点

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
    
    // 获取教程文档设置
    $result = $db->query("SELECT title, content, button_text, button_url FROM tutorial_settings ORDER BY id DESC LIMIT 1");
    $tutorialSettings = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($tutorialSettings) {
        echo json_encode($tutorialSettings);
    } else {
        // 返回默认设置
        $defaultSettings = [
            'title' => '教程文档',
            'content' => '查看我们的详细教程文档，了解如何安装游戏、配置客户端以及加入服务器的完整步骤',
            'button_text' => '查看教程文档',
            'button_url' => 'https://docs.example.com'
        ];
        echo json_encode($defaultSettings);
    }
    
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