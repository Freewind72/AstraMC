<?php
// 安全管理类

class SecurityManager {
    private $config;
    private $db;
    
    public function __construct($database) {
        $this->config = $this->loadConfig();
        $this->db = $database;
        $this->applySecurityHeaders();
    }
    
    // 加载配置
    private function loadConfig() {
        // 默认配置
        $defaultConfig = [
            'salt' => defined('SECURITY_SALT') ? SECURITY_SALT : 'freemc_admin_salt_2025',
            'max_login_attempts' => defined('MAX_LOGIN_ATTEMPTS') ? MAX_LOGIN_ATTEMPTS : 5,
            'login_lockout_time' => defined('LOGIN_LOCKOUT_TIME') ? LOGIN_LOCKOUT_TIME : 900,
            'session_timeout' => defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600,
            'password_min_length' => defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8,
            'allowed_ips' => [],
            'security_headers' => [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'Referrer-Policy' => 'strict-origin-when-cross-origin'
            ]
        ];
        
        // 尝试加载自定义配置文件
        $configFile = __DIR__ . '/config.php';
        if (file_exists($configFile)) {
            $customConfig = include $configFile;
            if (is_array($customConfig)) {
                return array_merge($defaultConfig, $customConfig);
            }
        }
        
        return $defaultConfig;
    }
    
    // 应用安全头
    private function applySecurityHeaders() {
        if (isset($this->config['security_headers']) && is_array($this->config['security_headers'])) {
            foreach ($this->config['security_headers'] as $header => $value) {
                header("$header: $value");
            }
        }
    }
    
    // 生成CSRF令牌
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // 验证CSRF令牌
    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // 检查IP地址是否被允许
    public function isIPAllowed($ip) {
        if (empty($this->config['allowed_ips'])) {
            return true; // 如果没有设置允许的IP，则允许所有IP
        }
        return in_array($ip, $this->config['allowed_ips']);
    }
    
    // 记录登录尝试
    public function logLoginAttempt($username, $ip, $success) {
        try {
            $stmt = $this->db->prepare("INSERT INTO login_attempts (username, ip_address, success, attempt_time) VALUES (:username, :ip, :success, datetime('now'))");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
            $stmt->bindValue(':success', $success ? 1 : 0, SQLITE3_INTEGER);
            $stmt->execute();
        } catch (Exception $e) {
            // 静默处理日志记录错误
        }
    }
    
    // 记录管理员活动
    public function logAdminActivity($adminId, $action, $details = '', $ipAddress = null, $userAgent = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent) VALUES (:admin_id, :action, :details, :ip_address, :user_agent)");
            $stmt->bindValue(':admin_id', $adminId, SQLITE3_INTEGER);
            $stmt->bindValue(':action', $action, SQLITE3_TEXT);
            $stmt->bindValue(':details', $details, SQLITE3_TEXT);
            $stmt->bindValue(':ip_address', $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), SQLITE3_TEXT);
            $stmt->bindValue(':user_agent', $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? ''), SQLITE3_TEXT);
            $stmt->execute();
        } catch (Exception $e) {
            // 静默处理日志记录错误
        }
    }
    
    // 检查是否被锁定
    public function isAccountLocked($username, $ip) {
        try {
            // 清理过期的登录尝试记录
            $this->cleanupLoginAttempts();
            
            // 检查用户是否被锁定
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE username = :username AND success = 0 AND attempt_time > datetime('now', '-" . $this->config['login_lockout_time'] . " seconds')");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($row && $row['count'] >= $this->config['max_login_attempts']) {
                return true;
            }
            
            // 检查IP是否被锁定
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = :ip AND success = 0 AND attempt_time > datetime('now', '-" . $this->config['login_lockout_time'] . " seconds')");
            $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($row && $row['count'] >= $this->config['max_login_attempts']) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false; // 出错时允许登录
        }
    }
    
    // 清理过期的登录尝试记录
    private function cleanupLoginAttempts() {
        try {
            $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE attempt_time < datetime('now', '-" . ($this->config['login_lockout_time'] * 2) . " seconds')");
            $stmt->execute();
        } catch (Exception $e) {
            // 静默处理
        }
    }
    
    // 检查会话是否过期
    public function isSessionExpired() {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return false;
        }
        
        if ((time() - $_SESSION['last_activity']) > $this->config['session_timeout']) {
            return true;
        }
        
        $_SESSION['last_activity'] = time();
        return false;
    }
    
    // 生成密码重置令牌
    public function generatePasswordResetToken($username) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 3600; // 1小时后过期
        
        try {
            // 删除旧的令牌
            $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE username = :username");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->execute();
            
            // 插入新令牌
            $stmt = $this->db->prepare("INSERT INTO password_reset_tokens (username, token, expiry) VALUES (:username, :token, :expiry)");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->bindValue(':expiry', $expiry, SQLITE3_INTEGER);
            $stmt->execute();
            
            return $token;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 验证密码重置令牌
    public function validatePasswordResetToken($token) {
        try {
            $stmt = $this->db->prepare("SELECT username, expiry FROM password_reset_tokens WHERE token = :token LIMIT 1");
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($row && $row['expiry'] > time()) {
                return $row['username'];
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 清理密码重置令牌
    public function clearPasswordResetToken($token) {
        try {
            $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE token = :token");
            $stmt->bindValue(':token', $token, SQLITE3_TEXT);
            $stmt->execute();
        } catch (Exception $e) {
            // 静默处理
        }
    }
    
    // 验证密码强度
    public function isPasswordStrong($password) {
        // 检查密码长度
        if (strlen($password) < $this->config['password_min_length']) {
            return false;
        }
        
        // 检查是否包含数字
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // 检查是否包含小写字母
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // 检查是否包含大写字母
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // 检查是否包含特殊字符
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
}
?>