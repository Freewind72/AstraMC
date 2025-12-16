<?php
header('Content-Type: application/json');

try {
    // 数据库路径
    $dbPath = __DIR__ . '/../sql/settings.db';
    
    // 连接数据库
    $db = new SQLite3($dbPath);
    
    // 查询灯笼设置
    $result = $db->query("SELECT deng_text, is_enabled FROM deng_settings WHERE id = 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo json_encode([
            'success' => true,
            'text' => $row['deng_text'],
            'enabled' => (bool)$row['is_enabled']
        ]);
    } else {
        // 默认文字和启用状态
        echo json_encode([
            'success' => true,
            'text' => '圣诞快乐',
            'enabled' => true
        ]);
    }
    
    $db->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '获取灯笼文字失败: ' . $e->getMessage()
    ]);
}
?>