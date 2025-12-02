<?php
// 安全配置文件

// 定义安全常量（仅在尚未定义时定义）
if (!defined('SECURITY_SALT')) {
    define('SECURITY_SALT', 'freemc_admin_salt_2025'); // 用于生成CSRF令牌等
}
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5); // 最大登录尝试次数
}
if (!defined('LOGIN_LOCKOUT_TIME')) {
    define('LOGIN_LOCKOUT_TIME', 900); // 登录锁定时间（秒），这里设置为15分钟
}
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600); // 会话超时时间（秒），这里设置为1小时
}
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 8); // 密码最小长度
}

// 允许的IP地址列表（留空表示不限制）
$allowedIPs = [];

// 安全头设置
$securityHeaders = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin'
];

return [
    'salt' => SECURITY_SALT,
    'max_login_attempts' => MAX_LOGIN_ATTEMPTS,
    'login_lockout_time' => LOGIN_LOCKOUT_TIME,
    'session_timeout' => SESSION_TIMEOUT,
    'password_min_length' => PASSWORD_MIN_LENGTH,
    'allowed_ips' => $allowedIPs,
    'security_headers' => $securityHeaders
];
?>