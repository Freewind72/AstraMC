<?php
// 首页访问记录获取API端点

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
if (!is_dir('../../sql')) {
    mkdir('../../sql', 0777, true);
}

// 连接到SQLite数据库
try {
    $db = new SQLite3('../../sql/settings.db');
    
    // 检查homepage_visits表是否存在
    $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='homepage_visits'");
    if (!$tableCheck->fetchArray(SQLITE3_ASSOC)) {
        // 表不存在，返回空数组
        echo json_encode(['visits' => []]);
        exit();
    }
    
    // 获取每个IP的访问次数和最新访问时间
    $result = $db->query("SELECT ip_address, user_agent, MAX(visit_time) as latest_visit, COUNT(*) as visit_count FROM homepage_visits GROUP BY ip_address ORDER BY latest_visit DESC LIMIT 20");
    $visits = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $visits[] = [
            'ip_address' => $row['ip_address'],
            'user_agent' => $row['user_agent'],
            'visit_time' => $row['latest_visit'],
            'visit_count' => $row['visit_count']
        ];
    }
    
    echo json_encode(['visits' => $visits]);
    
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