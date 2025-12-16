<?php
// 全局配置文件

// 定义网站标题
define('SITE_NAME', '牧云山庄');
define('ADMIN_PANEL_TITLE', '管理面板 - ' . SITE_NAME);

// 定义版本信息
define('APP_VERSION', '1.0.0');

// 定义路径常量
define('ADMIN_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// 定义默认设置
define('DEFAULT_SERVER_ADDRESS', 'mcda.xin');
define('DEFAULT_SERVER_NAME', '牧云山庄');

// 定义分页设置
define('ITEMS_PER_PAGE', 20);

// 定义会话超时时间（秒）
define('SESSION_TIMEOUT', 3600); // 1小时

// 定义安全设置
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15分钟

// 定义上传设置
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// 定义数据库设置
define('DB_PATH', ROOT_PATH . '/sql/settings.db');

// 定义主题颜色
define('PRIMARY_COLOR', '#4361ee');
define('SECONDARY_COLOR', '#3f37c9');
define('SUCCESS_COLOR', '#4cc9f0');
define('WARNING_COLOR', '#f72585');
define('DANGER_COLOR', '#e63946');
define('INFO_COLOR', '#4895ef');