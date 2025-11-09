<?php
// 数据库初始化脚本
// 创建必要的目录
if (!is_dir('sql')) {
    mkdir('sql', 0777, true);
}

if (!is_dir('admin')) {
    mkdir('admin', 0777, true);
}

if (!is_dir('api')) {
    mkdir('api', 0777, true);
}

// 自动创建数据库
$db = new SQLite3('sql/settings.db');

// 创建音乐表
$db->exec("CREATE TABLE IF NOT EXISTS music_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    music_url TEXT NOT NULL,
    auto_play BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建资源包设置表
$db->exec("CREATE TABLE IF NOT EXISTS resource_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resource_url TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建服务器设置表
$db->exec("CREATE TABLE IF NOT EXISTS server_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_address TEXT NOT NULL,
    server_name TEXT NOT NULL DEFAULT '原始大陆',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建服务器设置二表
$db->exec("CREATE TABLE IF NOT EXISTS server_settings_secondary (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_address TEXT NOT NULL,
    server_name TEXT NOT NULL DEFAULT '备用服务器',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建视频背景设置表
$db->exec("CREATE TABLE IF NOT EXISTS video_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    video_url TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建加入我们二维码链接设置表
$db->exec("CREATE TABLE IF NOT EXISTS join_qr_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    qr_url TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建服务器特点设置表
$db->exec("CREATE TABLE IF NOT EXISTS server_features (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    icon_code TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建精选展览图片设置表
$db->exec("CREATE TABLE IF NOT EXISTS gallery_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    image_url TEXT NOT NULL,
    alt_text TEXT DEFAULT '游戏截图',
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建管理团队成员设置表
$db->exec("CREATE TABLE IF NOT EXISTS team_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    qq_number TEXT NOT NULL,
    name TEXT NOT NULL,
    role TEXT NOT NULL,
    description TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建资源下载简介和卡片设置表
$db->exec("CREATE TABLE IF NOT EXISTS resource_sections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    section_type TEXT NOT NULL, -- 'intro' for introduction, 'card' for cards
    title TEXT,
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建加入我们设置表
$db->exec("CREATE TABLE IF NOT EXISTS join_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    server_address TEXT NOT NULL,
    server_version TEXT NOT NULL,
    qq_group TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建管理员表
$db->exec("CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建登录尝试记录表
$db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    ip_address TEXT NOT NULL,
    success INTEGER NOT NULL, -- 1 for success, 0 for failure
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建密码重置令牌表
$db->exec("CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    token TEXT NOT NULL UNIQUE,
    expiry INTEGER NOT NULL, -- Unix timestamp
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 创建管理员活动日志表
$db->exec("CREATE TABLE IF NOT EXISTS admin_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER NOT NULL,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT NOT NULL,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 检查是否需要添加 server_name 列（兼容旧版本表结构）
$columnsResult = $db->query("PRAGMA table_info(server_settings)");
$hasServerNameColumn = false;
while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
    if ($column['name'] === 'server_name') {
        $hasServerNameColumn = true;
        break;
    }
}

if (!$hasServerNameColumn) {
    $db->exec("ALTER TABLE server_settings ADD COLUMN server_name TEXT NOT NULL DEFAULT '原始大陆'");
}

// 检查是否需要添加 server_name 列（兼容旧版本表结构）到服务器设置二表
$columnsResult = $db->query("PRAGMA table_info(server_settings_secondary)");
$hasServerNameColumn = false;
while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
    if ($column['name'] === 'server_name') {
        $hasServerNameColumn = true;
        break;
    }
}

if (!$hasServerNameColumn) {
    $db->exec("ALTER TABLE server_settings_secondary ADD COLUMN server_name TEXT NOT NULL DEFAULT '备用服务器'");
}

// 检查是否需要添加 auto_play 列（兼容旧版本表结构）
$columnsResult = $db->query("PRAGMA table_info(music_settings)");
$hasAutoPlayColumn = false;
while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
    if ($column['name'] === 'auto_play') {
        $hasAutoPlayColumn = true;
        break;
    }
}

if (!$hasAutoPlayColumn) {
    $db->exec("ALTER TABLE music_settings ADD COLUMN auto_play BOOLEAN DEFAULT 0");
}

// 检查是否有默认音乐设置
$musicResult = $db->query("SELECT music_url FROM music_settings ORDER BY id DESC LIMIT 1");
$musicUrl = "";
if ($musicResult && ($row = $musicResult->fetchArray(SQLITE3_ASSOC))) {
    $musicUrl = $row['music_url'];
} else {
    // 插入默认音乐URL，但不自动播放
    $defaultMusicUrl = "https://example.com/default-music.mp3";
    $stmt = $db->prepare("INSERT INTO music_settings (music_url, auto_play) VALUES (:music_url, :auto_play)");
    $stmt->bindValue(':music_url', $defaultMusicUrl, SQLITE3_TEXT);
    $stmt->bindValue(':auto_play', 0, SQLITE3_INTEGER); // 默认设置为不自动播放
    $stmt->execute();
    $musicUrl = $defaultMusicUrl;
}

// 检查资源包设置
$resourceResult = $db->query("SELECT resource_url FROM resource_settings ORDER BY id DESC LIMIT 1");
$resourceUrl = "";
if ($resourceResult && ($row = $resourceResult->fetchArray(SQLITE3_ASSOC))) {
    $resourceUrl = $row['resource_url'];
} else {
    // 插入默认资源包URL
    $defaultResourceUrl = "https://vip.123pan.cn/1815439627/26358598";
    $stmt = $db->prepare("INSERT INTO resource_settings (resource_url) VALUES (:resource_url)");
    $stmt->bindValue(':resource_url', $defaultResourceUrl, SQLITE3_TEXT);
    $stmt->execute();
    $resourceUrl = $defaultResourceUrl;
}

// 获取服务器设置
$serverResult = $db->query("SELECT server_address, server_name FROM server_settings ORDER BY id DESC LIMIT 1");
$serverAddress = "";
$serverName = "";
if ($serverResult && ($row = $serverResult->fetchArray(SQLITE3_ASSOC))) {
    $serverAddress = $row['server_address'];
    $serverName = $row['server_name'];
} else {
    // 插入默认服务器地址和名称
    $defaultServerAddress = "mcda.xin";
    $defaultServerName = "原始大陆";
    $stmt = $db->prepare("INSERT INTO server_settings (server_address, server_name) VALUES (:server_address, :server_name)");
    $stmt->bindValue(':server_address', $defaultServerAddress, SQLITE3_TEXT);
    $stmt->bindValue(':server_name', $defaultServerName, SQLITE3_TEXT);
    $stmt->execute();
    $serverAddress = $defaultServerAddress;
    $serverName = $defaultServerName;
}

// 检查服务器设置二
$serverSecondaryResult = $db->query("SELECT server_address, server_name FROM server_settings_secondary ORDER BY id DESC LIMIT 1");
$serverSecondaryAddress = "";
$serverSecondaryName = "";
if ($serverSecondaryResult && ($row = $serverSecondaryResult->fetchArray(SQLITE3_ASSOC))) {
    $serverSecondaryAddress = $row['server_address'];
    $serverSecondaryName = $row['server_name'];
} else {
    // 插入默认服务器二地址和名称
    $defaultServerSecondaryAddress = "mymcc.xin";
    $defaultServerSecondaryName = "备用服务器";
    $stmt = $db->prepare("INSERT INTO server_settings_secondary (server_address, server_name) VALUES (:server_address, :server_name)");
    $stmt->bindValue(':server_address', $defaultServerSecondaryAddress, SQLITE3_TEXT);
    $stmt->bindValue(':server_name', $defaultServerSecondaryName, SQLITE3_TEXT);
    $stmt->execute();
    $serverSecondaryAddress = $defaultServerSecondaryAddress;
    $serverSecondaryName = $defaultServerSecondaryName;
}

// 检查视频背景设置
$videoResult = $db->query("SELECT video_url FROM video_settings ORDER BY id DESC LIMIT 1");
$videoUrl = "";
if ($videoResult && ($row = $videoResult->fetchArray(SQLITE3_ASSOC))) {
    $videoUrl = $row['video_url'];
} else {
    // 插入默认视频背景URL
    $defaultVideoUrl = "https://vip.123pan.cn/1815439627/24445722";
    $stmt = $db->prepare("INSERT INTO video_settings (video_url) VALUES (:video_url)");
    $stmt->bindValue(':video_url', $defaultVideoUrl, SQLITE3_TEXT);
    $stmt->execute();
    $videoUrl = $defaultVideoUrl;
}

// 检查加入我们二维码链接设置
$qrResult = $db->query("SELECT qr_url FROM join_qr_settings ORDER BY id DESC LIMIT 1");
$qrUrl = "";
if ($qrResult && ($row = $qrResult->fetchArray(SQLITE3_ASSOC))) {
    $qrUrl = $row['qr_url'];
} else {
    // 插入默认二维码链接URL
    $defaultQrUrl = "https://qm.qq.com/cgi-bin/qm/qr?_wv=1027&k=1dLwMxL7JdD0YtqGv-9QrG-SoG8oJ2w7&authKey=bP%2B7ZvVvUxTjVhx0n5bJ4jqY%2FVcXpJ5n1O2Sq4n1S1J%2BU%3D&noverify=0&group_code=1046193413";
    $stmt = $db->prepare("INSERT INTO join_qr_settings (qr_url) VALUES (:qr_url)");
    $stmt->bindValue(':qr_url', $defaultQrUrl, SQLITE3_TEXT);
    $stmt->execute();
    $qrUrl = $defaultQrUrl;
}

// 检查服务器特点设置
$featuresResult = $db->query("SELECT COUNT(*) as count FROM server_features");
$featuresRow = $featuresResult->fetchArray(SQLITE3_ASSOC);
if ($featuresRow && $featuresRow['count'] == 0) {
    // 插入默认服务器特点 (使用Font Awesome图标作为示例)
    $defaultFeatures = [
        ['fas fa-tree', '纯净生存', '保留最原始的生存玩法，无任何破坏平衡性的插件和MOD，带来最纯粹的游戏体验。', 1],
        ['fas fa-bolt', '生电挑战', '高科技红石挑战系统，专业红石机器，自动化农场与工厂建造，打造你的工业帝国。', 2],
        ['fas fa-bed', '起床战争', '紧张刺激的PVP游戏模式，团队协作占领资源点，摧毁敌方床并消灭所有对手获胜。', 3],
        ['fas fa-cube', '单方块生存', '极限生存挑战，从一块泥土开始，探索无限可能，感受从无到有的创造乐趣。', 4]
    ];
    
    foreach ($defaultFeatures as $index => $feature) {
        $stmt = $db->prepare("INSERT INTO server_features (icon_code, title, description, sort_order) VALUES (:icon_code, :title, :description, :sort_order)");
        $stmt->bindValue(':icon_code', $feature[0], SQLITE3_TEXT);
        $stmt->bindValue(':title', $feature[1], SQLITE3_TEXT);
        $stmt->bindValue(':description', $feature[2], SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $feature[3], SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// 检查精选展览图片设置
$galleryResult = $db->query("SELECT COUNT(*) as count FROM gallery_images");
$galleryRow = $galleryResult->fetchArray(SQLITE3_ASSOC);
if ($galleryRow && $galleryRow['count'] == 0) {
    // 插入默认展览图片
    $defaultImages = [
        ['https://free.picui.cn/free/2025/09/20/68ce35bc0ce2c.png', '游戏截图', 1],
        ['https://free.picui.cn/free/2025/09/20/68ce35c0457cf.png', '游戏截图', 2],
        ['https://free.picui.cn/free/2025/09/20/68ce35ab9a58e.png', '游戏截图', 3],
        ['https://free.picui.cn/free/2025/09/20/68ce35ab47881.png', '游戏截图', 4],
        ['https://free.picui.cn/free/2025/09/20/68ce35b13046f.png', '游戏截图', 5],
        ['https://free.picui.cn/free/2025/09/20/68ce35bc93add.png', '游戏截图', 6],
        ['https://free.picui.cn/free/2025/09/20/68ce35a6a8d77.png', '游戏截图', 7],
        ['https://free.picui.cn/free/2025/09/20/68ce35a601b9c.png', '游戏截图', 8],
        ['https://free.picui.cn/free/2025/09/20/68ce35b000d09.png', '游戏截图', 9]
    ];
    
    foreach ($defaultImages as $index => $image) {
        $stmt = $db->prepare("INSERT INTO gallery_images (image_url, alt_text, sort_order) VALUES (:image_url, :alt_text, :sort_order)");
        $stmt->bindValue(':image_url', $image[0], SQLITE3_TEXT);
        $stmt->bindValue(':alt_text', $image[1], SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $image[2], SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// 检查管理团队成员设置
$teamResult = $db->query("SELECT COUNT(*) as count FROM team_members");
$teamRow = $teamResult->fetchArray(SQLITE3_ASSOC);
if ($teamRow && $teamRow['count'] == 0) {
    // 插入默认团队成员
    $defaultMembers = [
        ['3856727842', 'WEER', '创始人 & 管理员', '负责服务器规划和运营，也是服务器腐竹（Astra表示：密码！！！）', 1],
        ['508849736', '໑ຼₒ₂₅ღ✨', '创意总监', '建筑大师，地图设计专家，曾设计多个服务器场景与活动地图', 2],
        ['997228665', 'A.', '技术顾问', '负责服务器运维，服务器bug都得找这位', 3],
        ['2458513812', 'Betokas', '社区管理', '社群运营达人与活动策划师，负责组织游戏内各种活动与玩家活动', 4],
        ['291109669', 'YuYu', '社区管理员', '负责掌管QQ群秩序，和服务器社区管理，包括作弊行为和恶意辱骂', 5],
        ['3038886380', 'Astra', '网站运维/web服务器', '负责网站浏览与开发', 6]
    ];
    
    foreach ($defaultMembers as $index => $member) {
        $stmt = $db->prepare("INSERT INTO team_members (qq_number, name, role, description, sort_order) VALUES (:qq_number, :name, :role, :description, :sort_order)");
        $stmt->bindValue(':qq_number', $member[0], SQLITE3_TEXT);
        $stmt->bindValue(':name', $member[1], SQLITE3_TEXT);
        $stmt->bindValue(':role', $member[2], SQLITE3_TEXT);
        $stmt->bindValue(':description', $member[3], SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $member[4], SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// 检查资源下载简介和卡片设置
$resourceSectionsResult = $db->query("SELECT COUNT(*) as count FROM resource_sections");
$resourceSectionsRow = $resourceSectionsResult->fetchArray(SQLITE3_ASSOC);
if ($resourceSectionsRow && $resourceSectionsRow['count'] == 0) {
    // 插入默认资源下载简介和卡片
    $defaultResourceSections = [
        ['intro', '资源下载', '获取服务器专用资源包，优化您的游戏体验', 1],
        ['card', '下载资源包', '下载我们为服务器量身定制的资源包，包含专属纹理材质、音效和模型', 1],
        ['card', '激活使用', 'PCL2版本选择，选择刚下载好的游戏包，点击启动游戏', 2],
        ['card', '不固定更新/维护', '该版本为正式版,将不定期维护，每周更新一次版本', 3]
    ];
    
    foreach ($defaultResourceSections as $index => $section) {
        $stmt = $db->prepare("INSERT INTO resource_sections (section_type, title, description, sort_order) VALUES (:section_type, :title, :description, :sort_order)");
        $stmt->bindValue(':section_type', $section[0], SQLITE3_TEXT);
        $stmt->bindValue(':title', $section[1], SQLITE3_TEXT);
        $stmt->bindValue(':description', $section[2], SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $section[3], SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// 检查加入我们设置
$joinSettingsResult = $db->query("SELECT COUNT(*) as count FROM join_settings");
$joinSettingsRow = $joinSettingsResult->fetchArray(SQLITE3_ASSOC);
if ($joinSettingsRow && $joinSettingsRow['count'] == 0) {
    // 插入默认加入我们设置
    $defaultTitle = '加入我们';
    $defaultDescription = '立即加入原始大陆，开启你的奇幻冒险之旅！';
    $defaultServerAddress = 'mcda.xin';
    $defaultServerVersion = '1.21.5 (向下兼容至1.21.1)';
    $defaultQQGroup = '1046193413';
    
    $stmt = $db->prepare("INSERT INTO join_settings (title, description, server_address, server_version, qq_group) VALUES (:title, :description, :server_address, :server_version, :qq_group)");
    $stmt->bindValue(':title', $defaultTitle, SQLITE3_TEXT);
    $stmt->bindValue(':description', $defaultDescription, SQLITE3_TEXT);
    $stmt->bindValue(':server_address', $defaultServerAddress, SQLITE3_TEXT);
    $stmt->bindValue(':server_version', $defaultServerVersion, SQLITE3_TEXT);
    $stmt->bindValue(':qq_group', $defaultQQGroup, SQLITE3_TEXT);
    $stmt->execute();
}

// 检查管理员账户
$adminResult = $db->query("SELECT COUNT(*) as count FROM admins");
$adminRow = $adminResult->fetchArray(SQLITE3_ASSOC);
if ($adminRow && $adminRow['count'] == 0) {
    // 创建默认管理员账户 (用户名: admin, 密码: admin)
    $defaultPassword = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO admins (username, password) VALUES ('admin', :password)");
    $stmt->bindValue(':password', $defaultPassword, SQLITE3_TEXT);
    $stmt->execute();
}

$db->close();

// 返回变量给调用脚本
return [
    'serverAddress' => $serverAddress,
    'serverName' => $serverName,
    'serverSecondaryAddress' => $serverSecondaryAddress,
    'serverSecondaryName' => $serverSecondaryName,
    'qrUrl' => $qrUrl
];
?>