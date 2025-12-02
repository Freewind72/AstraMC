<?php
// 公告获取API端点

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
    
    // 获取启用的公告
    $result = $db->query("SELECT id, title, content, version, alignment, format, updated_at FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $announcement = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($announcement) {
        // Markdown解析函数
        function parseMarkdown($text) {
            // 处理标题（除了h1，因为h1在header中）
            $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
            $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
            $text = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $text);
            $text = preg_replace('/^##### (.+)$/m', '<h5>$1</h5>', $text);
            
            // 处理列表
            $text = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $text);
            $text = preg_replace('/(<li>.+<\/li>)+/s', '<ul>$0</ul>', $text);
            
            // 处理加粗
            $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
            
            // 处理斜体
            $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
            
            // 处理下划线
            $text = preg_replace('/__(.+?)__/', '<u>$1</u>', $text);
            
            // 处理段落
            $text = preg_replace('/^(.+)$/m', '<p>$1</p>', $text);
            
            // 处理换行
            $text = str_replace("\n", "<br>", $text);
            
            return $text;
        }
        
        // 纯文本解析函数
        function parsePlainText($text) {
            // 转换特殊字符
            $text = htmlspecialchars($text);
            // 转换换行符为<br>标签
            $text = nl2br($text);
            // 包裹在<p>标签中
            return '<p>' . $text . '</p>';
        }
        
        // 根据格式处理内容
        if (isset($announcement['format']) && $announcement['format'] === 'plain') {
            $announcement['content'] = parsePlainText($announcement['content']);
        } else {
            $announcement['content'] = parseMarkdown(htmlspecialchars($announcement['content']));
        }
        
        echo json_encode($announcement);
    } else {
        echo json_encode(['error' => 'No active announcement found']);
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