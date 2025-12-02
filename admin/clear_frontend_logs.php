<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => '未授权访问']);
    exit();
}

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

try {
    $db = new SQLite3('../sql/settings.db');
    
    // 删除所有前端访问日志
    $result = $db->exec("DELETE FROM frontend_logs");
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => '访问记录已清空']);
    } else {
        echo json_encode(['success' => false, 'message' => '清空记录失败']);
    }
    
    $db->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '数据库操作失败: ' . $e->getMessage()]);
}
?>