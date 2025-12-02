<?php
require_once 'header.php';

// 检查并添加version字段（如果不存在）
try {
    $columnsResult = $db->query("PRAGMA table_info(announcements)");
    $hasVersionColumn = false;
    $hasAlignmentColumn = false;
    $hasFormatColumn = false;
    
    while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'version') {
            $hasVersionColumn = true;
        }
        if ($column['name'] === 'alignment') {
            $hasAlignmentColumn = true;
        }
        if ($column['name'] === 'format') {
            $hasFormatColumn = true;
        }
    }
    
    if (!$hasVersionColumn) {
        $db->exec("ALTER TABLE announcements ADD COLUMN version INTEGER DEFAULT 1");
    }
    
    if (!$hasAlignmentColumn) {
        $db->exec("ALTER TABLE announcements ADD COLUMN alignment TEXT DEFAULT 'left'");
    }
    
    if (!$hasFormatColumn) {
        $db->exec("ALTER TABLE announcements ADD COLUMN format TEXT DEFAULT 'markdown'");
    }
    
    // 检查维护模式表是否存在，不存在则创建
    $db->exec("CREATE TABLE IF NOT EXISTS maintenance (
        id INTEGER PRIMARY KEY,
        is_active INTEGER DEFAULT 0,
        message TEXT DEFAULT '服务器正在维护中，请稍后再试。',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 检查网站设置表是否存在，不存在则创建
    $db->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY,
        favicon_url TEXT DEFAULT '',
        logo_url TEXT DEFAULT '',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 插入默认维护记录（如果不存在）
    $checkMaintenance = $db->query("SELECT COUNT(*) as count FROM maintenance");
    $maintenanceRow = $checkMaintenance->fetchArray(SQLITE3_ASSOC);
    if ($maintenanceRow['count'] == 0) {
        $db->exec("INSERT INTO maintenance (id, is_active, message) VALUES (1, 0, '服务器正在维护中，请稍后再试。')");
    }
    
    // 插入默认网站设置记录（如果不存在）
    $checkSiteSettings = $db->query("SELECT COUNT(*) as count FROM site_settings");
    $siteSettingsRow = $checkSiteSettings->fetchArray(SQLITE3_ASSOC);
    if ($siteSettingsRow['count'] == 0) {
        $db->exec("INSERT INTO site_settings (id, favicon_url, logo_url) VALUES (1, '', '')");
    }
} catch (Exception $e) {
    // 忽略错误，继续执行
}

// 获取当前公告设置
$announcement = [];

try {
    $result = $db->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $announcement = $row;
    }
} catch (Exception $e) {
    $message = '获取当前公告时出错：' . $e->getMessage();
}

// 获取维护模式设置
$maintenance = [];
try {
    $result = $db->query("SELECT * FROM maintenance WHERE id = 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $maintenance = $row;
    }
} catch (Exception $e) {
    $message = '获取维护模式设置时出错：' . $e->getMessage();
}

// 获取网站设置
$siteSettings = [];
try {
    $result = $db->query("SELECT * FROM site_settings WHERE id = 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $siteSettings = $row;
    }
} catch (Exception $e) {
    $message = '获取网站设置时出错：' . $e->getMessage();
}

// 处理表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_announcement') {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $alignment = $_POST['alignment'] ?? 'left';
        $format = $_POST['format'] ?? 'markdown';
        
        // 只检查标题是否为空，允许内容为空
        if (empty($title)) {
            $message = '标题不能为空。';
        } else {
            try {
                // 检查是否已存在记录
                $checkResult = $db->query("SELECT COUNT(*) as count, version FROM announcements ORDER BY id DESC LIMIT 1");
                $row = $checkResult->fetchArray(SQLITE3_ASSOC);
                    
                    if ($row && $row['count'] > 0) {
                        // 如果存在记录，则更新记录并增加版本号
                        $newVersion = ($row['version'] ?? 0) + 1;
                        $stmt = $db->prepare("UPDATE announcements SET title = :title, content = :content, is_active = :is_active, version = :version, alignment = :alignment, format = :format, updated_at = datetime('now') WHERE id = (SELECT MIN(id) FROM announcements)");
                        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
                        $stmt->bindValue(':is_active', $isActive, SQLITE3_INTEGER);
                        $stmt->bindValue(':version', $newVersion, SQLITE3_INTEGER);
                        $stmt->bindValue(':alignment', $alignment, SQLITE3_TEXT);
                        $stmt->bindValue(':format', $format, SQLITE3_TEXT);
                    } else {
                        // 如果不存在记录，则插入新记录
                        $stmt = $db->prepare("INSERT INTO announcements (title, content, is_active, version, alignment, format) VALUES (:title, :content, :is_active, :version, :alignment, :format)");
                        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
                        $stmt->bindValue(':is_active', $isActive, SQLITE3_INTEGER);
                        $stmt->bindValue(':version', 1, SQLITE3_INTEGER);
                        $stmt->bindValue(':alignment', $alignment, SQLITE3_TEXT);
                        $stmt->bindValue(':format', $format, SQLITE3_TEXT);
                    }
                
                if ($stmt->execute()) {
                    // 使用PRG模式防止重复提交
                    header('Location: announcement.php?message=' . urlencode('公告已成功更新！'));
                    exit();
                } else {
                    $message = '更新失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'update_maintenance') {
        $isMaintenanceActive = isset($_POST['maintenance_active']) ? 1 : 0;
        $maintenanceMessage = trim($_POST['maintenance_message']);
        
        try {
            $stmt = $db->prepare("UPDATE maintenance SET is_active = :is_active, message = :message, updated_at = datetime('now') WHERE id = 1");
            $stmt->bindValue(':is_active', $isMaintenanceActive, SQLITE3_INTEGER);
            $stmt->bindValue(':message', $maintenanceMessage, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                // 使用PRG模式防止重复提交
                header('Location: announcement.php?message=' . urlencode('维护模式设置已成功更新！'));
                exit();
            } else {
                $message = '更新失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '更新失败：' . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'update_site_settings') {
        $faviconUrl = trim($_POST['favicon_url']);
        $logoUrl = trim($_POST['logo_url']);
        
        try {
            $stmt = $db->prepare("UPDATE site_settings SET favicon_url = :favicon_url, logo_url = :logo_url, updated_at = datetime('now') WHERE id = 1");
            $stmt->bindValue(':favicon_url', $faviconUrl, SQLITE3_TEXT);
            $stmt->bindValue(':logo_url', $logoUrl, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                // 使用PRG模式防止重复提交
                header('Location: announcement.php?message=' . urlencode('网站设置已成功更新！'));
                exit();
            } else {
                $message = '更新失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '更新失败：' . $e->getMessage();
        }
    }
}

// 检查是否有通过URL传递的消息
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Markdown解析函数
function parseMarkdown($text) {
    // 处理标题
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $text);
    $text = preg_replace('/^##### (.+)$/m', '<h5>$1</h5>', $text);
    
    // 处理列表
    $text = preg_replace('/^(\s*)\* (.+)$/m', '$1<ul><li>$2</li></ul>', $text);
    $text = preg_replace('/<\/ul>\s*<ul>/m', '', $text); // 合并相邻的ul标签
    
    // 处理加粗
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    
    // 处理斜体
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    
    // 处理下划线
    $text = preg_replace('/__(.+?)__/', '<u>$1</u>', $text);
    
    // 处理段落
    $text = preg_replace('/^(.+)$/m', '<p>$1</p>', $text);
    
    // 处理换行
    $text = str_replace("\n", "<br>", $text);
    
    return $text;
}

// 纯文本解析函数
function parsePlainText($text) {
    // 转换特殊字符
    $text = htmlspecialchars($text);
    // 转换换行符为<br>标签
    $text = nl2br($text);
    // 包裹在<p>标签中
    $text = '<p>' . $text . '</p>';
    return $text;
}
?>

<div class="card" id="announcement-form">
    <h2 class="card-title">
        <i class="fas fa-bullhorn"></i> 公告管理
    </h2>
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <input type="hidden" name="action" value="update_announcement">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <div class="form-group">
            <label class="form-label" for="title">公告标题</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                class="form-input" 
                placeholder="请输入公告标题" 
                value="<?php echo isset($announcement['title']) ? htmlspecialchars($announcement['title']) : ''; ?>"
                required>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="content">公告内容</label>
            <textarea 
                id="content" 
                name="content" 
                class="form-input" 
                rows="6" 
                placeholder="请输入公告内容"><?php echo isset($announcement['content']) ? htmlspecialchars($announcement['content']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">内容格式</label>
            <div>
                <label class="form-radio">
                    <input type="radio" name="format" value="plain" <?php echo (!isset($announcement['format']) || $announcement['format'] !== 'markdown') ? 'checked' : ''; ?>>
                    <span>纯文本</span>
                </label>
                <label class="form-radio">
                    <input type="radio" name="format" value="markdown" <?php echo (isset($announcement['format']) && $announcement['format'] === 'markdown') ? 'checked' : ''; ?>>
                    <span>Markdown</span>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">对齐方式</label>
            <div>
                <label class="form-radio">
                    <input type="radio" name="alignment" value="left" <?php echo (!isset($announcement['alignment']) || $announcement['alignment'] !== 'center') ? 'checked' : ''; ?>>
                    <span>左对齐</span>
                </label>
                <label class="form-radio">
                    <input type="radio" name="alignment" value="center" <?php echo (isset($announcement['alignment']) && $announcement['alignment'] === 'center') ? 'checked' : ''; ?>>
                    <span>居中</span>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-checkbox">
                <input type="checkbox" name="is_active" value="1" <?php echo (isset($announcement['is_active']) && $announcement['is_active']) ? 'checked' : ''; ?>>
                <span>启用公告</span>
            </label>
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存公告
        </button>
    </form>
</div>

<div class="card" id="site-settings-form">
    <h2 class="card-title">
        <i class="fas fa-cog"></i> 网站设置
    </h2>
    
    <form method="post">
        <input type="hidden" name="action" value="update_site_settings">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <div class="form-group">
            <label class="form-label" for="favicon_url">浏览器标签图标 (Favicon) URL</label>
            <input 
                type="url" 
                id="favicon_url" 
                name="favicon_url" 
                class="form-input" 
                placeholder="请输入favicon图片URL" 
                value="<?php echo isset($siteSettings['favicon_url']) ? htmlspecialchars($siteSettings['favicon_url']) : ''; ?>">
            <small>建议使用 16x16 或 32x32 像素的PNG或ICO格式图片</small>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="logo_url">网站Logo URL</label>
            <input 
                type="url" 
                id="logo_url" 
                name="logo_url" 
                class="form-input" 
                placeholder="请输入Logo图片URL" 
                value="<?php echo isset($siteSettings['logo_url']) ? htmlspecialchars($siteSettings['logo_url']) : ''; ?>">
            <small>建议使用高度为70px左右的透明PNG图片</small>
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存设置
        </button>
    </form>
</div>

<div class="card" id="maintenance-form">
    <h2 class="card-title">
        <i class="fas fa-tools"></i> 维护模式设置
    </h2>
    
    <form method="post">
        <input type="hidden" name="action" value="update_maintenance">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <div class="form-group">
            <label class="form-checkbox">
                <input type="checkbox" name="maintenance_active" value="1" <?php echo (isset($maintenance['is_active']) && $maintenance['is_active']) ? 'checked' : ''; ?>>
                <span>启用维护模式</span>
            </label>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="maintenance_message">维护提示信息</label>
            <textarea 
                id="maintenance_message" 
                name="maintenance_message" 
                class="form-input" 
                rows="3" 
                placeholder="请输入维护提示信息"><?php echo isset($maintenance['message']) ? htmlspecialchars($maintenance['message']) : '服务器正在维护中，请稍后再试。'; ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存设置
        </button>
    </form>
</div>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-eye"></i> 预览
    </h2>
    <div class="preview">
        <div class="preview-title">公告预览:</div>
        <?php if (isset($announcement['title']) && $announcement['title']): ?>
            <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
            <?php if (isset($announcement['content']) && $announcement['content']): ?>
                <div>
                    <?php 
                    if (isset($announcement['format']) && $announcement['format'] === 'markdown') {
                        echo parseMarkdown($announcement['content']);
                    } else {
                        echo parsePlainText($announcement['content']);
                    }
                    ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div>暂无公告</div>
        <?php endif; ?>
        
        <div class="preview-title" style="margin-top: 20px;">网站Logo预览:</div>
        <?php if (isset($siteSettings['logo_url']) && $siteSettings['logo_url']): ?>
            <div>
                <img src="<?php echo htmlspecialchars($siteSettings['logo_url']); ?>" alt="Logo预览" style="max-height: 70px; width: auto;">
            </div>
        <?php else: ?>
            <div>暂未设置Logo</div>
        <?php endif; ?>
        
        <div class="preview-title" style="margin-top: 20px;">维护模式预览:</div>
        <?php if (isset($maintenance['is_active']) && $maintenance['is_active']): ?>
            <div style="color: #e74c3c; font-weight: bold;">
                <?php echo htmlspecialchars($maintenance['message']); ?>
            </div>
        <?php else: ?>
            <div>维护模式未启用</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>