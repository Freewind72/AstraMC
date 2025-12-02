<?php
session_start();
require_once '../admin/security/SecurityManager.php';

// 检查用户是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.0 403 Forbidden');
    die('需要登录才能访问此页面');
}

// 检查CSRF令牌
$securityManager = new SecurityManager();
if (!$securityManager->validateCsrfToken($_POST['csrf_token'] ?? '')) {
    header('HTTP/1.0 403 Forbidden');
    die('无效的请求令牌');
}

// 检查是否有文件上传
if (!isset($_FILES['image'])) {
    header('HTTP/1.0 400 Bad Request');
    die('没有文件被上传');
}

// 验证文件类型
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['image']['type'], $allowedTypes)) {
    header('HTTP/1.0 400 Bad Request');
    die('只允许上传JPEG、PNG或GIF格式的图片');
}

// 验证文件大小（限制为5MB）
$maxFileSize = 5 * 1024 * 1024;
if ($_FILES['image']['size'] > $maxFileSize) {
    header('HTTP/1.0 400 Bad Request');
    die('文件大小不能超过5MB');
}

// 生成唯一的文件名
$fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$newFileName = uniqid() . '.' . $fileExtension;
$uploadDir = '../uploads/';
$uploadPath = $uploadDir . $newFileName;

// 确保上传目录存在
if (!is_dir('../uploads/')) {
    mkdir('../uploads/', 0755, true);
}

// 移动上传的文件
if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
    // 返回成功的JSON响应
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '图片上传成功',
        'file_url' => $uploadPath,
        'file_name' => $newFileName
    ]);
} else {
    header('HTTP/1.0 500 Internal Server Error');
    die('文件上传失败');
}
?>