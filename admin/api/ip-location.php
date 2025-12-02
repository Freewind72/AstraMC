<?php
// IP归属地查询代理API

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 开启错误报告（仅用于调试）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取IP参数
$ip = $_GET['ip'] ?? '';

if (empty($ip)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing IP parameter']);
    exit();
}

// 验证IP地址格式
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid IP address']);
    exit();
}

// 构建API URL
$apiUrl = "https://ip9.com.cn/get?ip=" . urlencode($ip);

// 使用cURL获取数据
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$errno = curl_errno($ch);
curl_close($ch);

if ($errno) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error (' . $errno . '): ' . $error]);
    exit();
}

if (!$response) {
    http_response_code(500);
    echo json_encode(['error' => 'Empty response from API']);
    exit();
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'API request failed with HTTP code: ' . $httpCode]);
    exit();
}

// 尝试解析JSON响应
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to parse JSON response: ' . json_last_error_msg(),
        'raw_response' => substr($response, 0, 200) // 只返回前200个字符用于调试
    ]);
    exit();
}

// 返回API响应
echo $response;
?>