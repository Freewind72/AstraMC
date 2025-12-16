<?php
header('Content-Type: application/json');

try {
    // 数据库路径
    $dbPath = __DIR__ . '/../sql/settings.db';
    
    // 连接数据库
    $db = new SQLite3($dbPath);
    
    // 查询网站设置
    $result = $db->query("SELECT grayscale_mode, auto_grayscale_dates FROM site_settings WHERE id = 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // 检查今天是否是自动启用灰色模式的日期
        $autoDates = explode(',', $row['auto_grayscale_dates']);
        $today = date('m-d');
        $shouldEnable = (bool)$row['grayscale_mode'] || in_array($today, $autoDates);
        
        echo json_encode([
            'success' => true,
            'enabled' => $shouldEnable,
            'manual' => (bool)$row['grayscale_mode'],
            'auto_date' => in_array($today, $autoDates)
        ]);
    } else {
        // 默认不启用
        echo json_encode([
            'success' => true,
            'enabled' => false,
            'manual' => false,
            'auto_date' => false
        ]);
    }
    
    $db->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '获取灰色模式设置失败: ' . $e->getMessage()
    ]);
}
?>