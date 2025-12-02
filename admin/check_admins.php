<?php
// 检查管理员账户是否存在
try {
    // 连接到SQLite数据库
    $db = new SQLite3('../sql/settings.db');
    
    // 检查admins表是否存在
    $tablesQuery = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='admins'");
    $tableExists = $tablesQuery->fetchArray(SQLITE3_ASSOC);
    
    if ($tableExists) {
        // 检查是否有任何管理员账户
        $result = $db->query("SELECT COUNT(*) as count FROM admins");
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($row['count'] > 0) {
            // 存在管理员账户
            echo json_encode(['hasAdmin' => true]);
        } else {
            // 不存在管理员账户
            echo json_encode(['hasAdmin' => false]);
        }
    } else {
        // 表不存在
        echo json_encode(['hasAdmin' => false]);
    }
    
    $db->close();
} catch (Exception $e) {
    // 发生错误
    echo json_encode(['hasAdmin' => false, 'error' => $e->getMessage()]);
}
?>