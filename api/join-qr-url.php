<?php
// 加入我们二维码链接获取API端点

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
    // 创建加入我们二维码设置表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS join_qr_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        qr_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查是否有默认二维码链接
    $result = $db->query("SELECT qr_url FROM join_qr_settings ORDER BY id DESC LIMIT 1");
    if (!($row = $result->fetchArray(SQLITE3_ASSOC))) {
        // 插入默认二维码链接
        $defaultQrUrl = "https://qm.qq.com/cgi-bin/qm/qr?_wv=1027&k=1dLwMxL7JdD0YtqGv-9QrG-SoG8oJ2w7&authKey=bP%2B7ZvVvUxTjVhx0n5bJ4jqY%2FVcXpJ5n1O2Sq4n1S1J%2BU%3D&noverify=0&group_code=1046193413";
        $stmt = $db->prepare("INSERT INTO join_qr_settings (qr_url) VALUES (:qr_url)");
        $stmt->bindValue(':qr_url', $defaultQrUrl, SQLITE3_TEXT);
        $stmt->execute();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// 处理GET请求（获取二维码链接）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $db->query("SELECT qr_url FROM join_qr_settings ORDER BY id DESC LIMIT 1");
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo json_encode([
            'url' => $row['qr_url']
        ]);
    } else {
        // 返回默认二维码链接
        echo json_encode([
            'url' => 'https://qm.qq.com/cgi-bin/qm/qr?_wv=1027&k=1dLwMxL7JdD0YtqGv-9QrG-SoG8oJ2w7&authKey=bP%2B7ZvVvUxTjVhx0n5bJ4jqY%2FVcXpJ5n1O2Sq4n1S1J%2BU%3D&noverify=0&group_code=1046193413'
        ]);
    }
    
    $db->close();
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>