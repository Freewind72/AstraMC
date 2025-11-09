<?php
// 连接到SQLite数据库
try {
    $db = new SQLite3('sql/settings.db');
    
    // 查询所有管理员账户
    $result = $db->query("SELECT id, username, password FROM admins");
    
    echo "<h2>管理员账户密码状态检查</h2>\n";
    echo "<table border='1' cellpadding='10'>\n";
    echo "<tr><th>ID</th><th>用户名</th><th>密码</th><th>密码类型</th></tr>\n";
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $id = htmlspecialchars($row['id']);
        $username = htmlspecialchars($row['username']);
        $password = htmlspecialchars($row['password']);
        
        // 检查密码是否是哈希值
        $passwordType = '未知';
        if (password_get_info($row['password'])['algo'] !== null) {
            $passwordType = '哈希密码';
        } elseif (strlen($row['password']) < 60) {
            $passwordType = '可能是明文';
        } else {
            $passwordType = '可能是哈希';
        }
        
        echo "<tr>\n";
        echo "<td>$id</td>\n";
        echo "<td>$username</td>\n";
        echo "<td>$password</td>\n";
        echo "<td>$passwordType</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    $db->close();
} catch (Exception $e) {
    echo "数据库连接失败: " . $e->getMessage();
}
?>