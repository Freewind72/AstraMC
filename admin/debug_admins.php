<?php
// 调试脚本：列出所有管理员账户（仅在调试模式下可用）
try {
    // 连接到SQLite数据库
    $db = new SQLite3('../sql/settings.db');
    
    // 检查admins表是否存在
    $tablesQuery = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='admins'");
    $tableExists = $tablesQuery->fetchArray(SQLITE3_ASSOC);
    
    if ($tableExists) {
        // 获取所有管理员账户
        $result = $db->query("SELECT id, username, created_at FROM admins ORDER BY id");
        
        echo "<h2>管理员账户列表</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>用户名</th><th>创建时间</th></tr>";
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>未找到管理员表</p>";
    }
    
    $db->close();
} catch (Exception $e) {
    // 发生错误
    echo "<p>发生错误: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>