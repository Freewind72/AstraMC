<?php
// 数据库连接配置
$db_path = __DIR__ . '/../sql/settings.db';

// 检查数据库文件是否存在
if (!file_exists($db_path)) {
    die("数据库文件不存在: " . $db_path);
}

try {
    // 创建 SQLite 数据库连接
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 不再输出任何内容，避免在引用此文件的页面显示消息
// echo "数据库初始化完成！";
?>