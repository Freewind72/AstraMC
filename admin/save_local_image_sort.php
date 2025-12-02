<?php
// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 连接到SQLite数据库
try {
    $db = new SQLite3('../sql/settings.db');
} catch (Exception $e) {
    die("数据库连接失败: " . $e->getMessage());
}

header('Content-Type: application/json');

// 检查是否为POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '只允许POST请求']);
    exit();
}

// 获取JSON数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 验证数据
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => '无效的数据格式']);
    exit();
}

try {
    // 确保local_image_sort表存在
    $db->exec("CREATE TABLE IF NOT EXISTS local_image_sort (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT UNIQUE NOT NULL,
        sort_order INTEGER DEFAULT 0,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 开始事务
    $db->exec('BEGIN TRANSACTION');
    
    // 更新每个文件的排序
    foreach ($data as $item) {
        if (isset($item['filename']) && isset($item['sort_order'])) {
            $stmt = $db->prepare("INSERT OR REPLACE INTO local_image_sort (filename, sort_order) VALUES (:filename, :sort_order)");
            $stmt->bindValue(':filename', $item['filename'], SQLITE3_TEXT);
            $stmt->bindValue(':sort_order', $item['sort_order'], SQLITE3_INTEGER);
            $stmt->execute();
        }
    }
    
    // 提交事务
    $db->exec('COMMIT');
    
    echo json_encode(['success' => true, 'message' => '排序已保存']);
} catch (Exception $e) {
    // 回滚事务
    $db->exec('ROLLBACK');
    echo json_encode(['success' => false, 'message' => '保存失败: ' . $e->getMessage()]);
}

$db->close();
?>