<?php
require_once 'header.php';

// 查询所有管理员账户
$result = $db->query("SELECT id, username, password FROM admins");

echo "<div class='card'>";
echo "<h2 class='card-title'>管理员账户密码状态检查</h2>\n";
echo "<table style='width:100%; border-collapse: collapse;'>\n";
echo "<tr style='background-color: #f2f2f2;'><th style='border: 1px solid #ddd; padding: 8px;'>ID</th><th style='border: 1px solid #ddd; padding: 8px;'>用户名</th><th style='border: 1px solid #ddd; padding: 8px;'>密码</th><th style='border: 1px solid #ddd; padding: 8px;'>密码类型</th></tr>\n";

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
    echo "<td style='border: 1px solid #ddd; padding: 8px;'>$id</td>\n";
    echo "<td style='border: 1px solid #ddd; padding: 8px;'>$username</td>\n";
    echo "<td style='border: 1px solid #ddd; padding: 8px;'>$password</td>\n";
    echo "<td style='border: 1px solid #ddd; padding: 8px;'>$passwordType</td>\n";
    echo "</tr>\n";
}

echo "</table>\n";
echo "</div>";

require_once 'footer.php';
?>