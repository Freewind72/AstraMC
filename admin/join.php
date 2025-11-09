<?php
require_once 'header.php';

// 获取当前加入我们设置
$joinSettings = [];
$qrUrl = "";

try {
    $result = $db->query("SELECT * FROM join_settings ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $joinSettings = $row;
    }
    
    // 获取二维码链接
    $qrResult = $db->query("SELECT qr_url FROM join_qr_settings ORDER BY id DESC LIMIT 1");
    if ($qrRow = $qrResult->fetchArray(SQLITE3_ASSOC)) {
        $qrUrl = $qrRow['qr_url'];
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_join') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $serverAddress = trim($_POST['server_address']);
        $serverVersion = trim($_POST['server_version']);
        $qqGroup = trim($_POST['qq_group']);
        
        if (empty($title) || empty($description) || empty($serverAddress) || empty($serverVersion) || empty($qqGroup)) {
            $message = '所有字段都不能为空。';
            header("Location: join.php?message=" . urlencode($message));
            exit();
        } else {
            try {
                // 检查是否已存在记录
                $checkResult = $db->query("SELECT COUNT(*) as count FROM join_settings");
                $row = $checkResult->fetchArray(SQLITE3_ASSOC);
                
                if ($row && $row['count'] > 0) {
                    // 如果存在记录，则更新第一条记录
                    $stmt = $db->prepare("UPDATE join_settings SET title = :title, description = :description, server_address = :server_address, server_version = :server_version, qq_group = :qq_group WHERE id = (SELECT MIN(id) FROM join_settings)");
                    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                    $stmt->bindValue(':server_address', $serverAddress, SQLITE3_TEXT);
                    $stmt->bindValue(':server_version', $serverVersion, SQLITE3_TEXT);
                    $stmt->bindValue(':qq_group', $qqGroup, SQLITE3_TEXT);
                } else {
                    // 如果不存在记录，则插入新记录
                    $stmt = $db->prepare("INSERT INTO join_settings (title, description, server_address, server_version, qq_group) VALUES (:title, :description, :server_address, :server_version, :qq_group)");
                    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                    $stmt->bindValue(':server_address', $serverAddress, SQLITE3_TEXT);
                    $stmt->bindValue(':server_version', $serverVersion, SQLITE3_TEXT);
                    $stmt->bindValue(':qq_group', $qqGroup, SQLITE3_TEXT);
                }
                
                if ($stmt->execute()) {
                    header("Location: join.php?message=" . urlencode('加入我们设置已成功更新！'));
                    exit();
                } else {
                    $message = '更新失败，请重试。';
                    header("Location: join.php?message=" . urlencode($message));
                    exit();
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
                header("Location: join.php?message=" . urlencode($message));
                exit();
            }
        }
    } elseif ($_POST['action'] === 'update_qr') {
        $qrUrl = trim($_POST['qr_url']);
        
        if (empty($qrUrl)) {
            $message = '二维码链接不能为空。';
            header("Location: join.php?message=" . urlencode($message));
            exit();
        } else {
            try {
                // 检查是否已存在记录
                $checkResult = $db->query("SELECT COUNT(*) as count FROM join_qr_settings");
                $row = $checkResult->fetchArray(SQLITE3_ASSOC);
                
                if ($row && $row['count'] > 0) {
                    // 如果存在记录，则更新第一条记录
                    $stmt = $db->prepare("UPDATE join_qr_settings SET qr_url = :qr_url WHERE id = (SELECT MIN(id) FROM join_qr_settings)");
                    $stmt->bindValue(':qr_url', $qrUrl, SQLITE3_TEXT);
                } else {
                    // 如果不存在记录，则插入新记录
                    $stmt = $db->prepare("INSERT INTO join_qr_settings (qr_url) VALUES (:qr_url)");
                    $stmt->bindValue(':qr_url', $qrUrl, SQLITE3_TEXT);
                }
                
                if ($stmt->execute()) {
                    header("Location: join.php?message=" . urlencode('二维码链接已成功更新！'));
                    exit();
                } else {
                    $message = '更新失败，请重试。';
                    header("Location: join.php?message=" . urlencode($message));
                    exit();
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
                header("Location: join.php?message=" . urlencode($message));
                exit();
            }
        }
    }
}
?>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-users"></i> 加入我们管理
    </h2>
    <form method="post">
        <input type="hidden" name="action" value="update_join">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="title">标题</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                class="form-input" 
                placeholder="请输入标题" 
                value="<?php echo htmlspecialchars($joinSettings['title'] ?? ''); ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="description">描述</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-input" 
                rows="3" 
                placeholder="请输入描述"
                required><?php echo htmlspecialchars($joinSettings['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="server_address">服务器地址</label>
            <input 
                type="text" 
                id="server_address" 
                name="server_address" 
                class="form-input" 
                placeholder="请输入服务器地址" 
                value="<?php echo htmlspecialchars($joinSettings['server_address'] ?? ''); ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="server_version">游戏版本</label>
            <input 
                type="text" 
                id="server_version" 
                name="server_version" 
                class="form-input" 
                placeholder="请输入游戏版本" 
                value="<?php echo htmlspecialchars($joinSettings['server_version'] ?? ''); ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="qq_group">QQ交流群</label>
            <input 
                type="text" 
                id="qq_group" 
                name="qq_group" 
                class="form-input" 
                placeholder="请输入QQ交流群号码" 
                value="<?php echo htmlspecialchars($joinSettings['qq_group'] ?? ''); ?>"
                required>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存设置
        </button>
    </form>
</div>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-qrcode"></i> 二维码链接管理
    </h2>
    <form method="post">
        <input type="hidden" name="action" value="update_qr">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="qr_url">二维码链接</label>
            <input 
                type="url" 
                id="qr_url" 
                name="qr_url" 
                class="form-input" 
                placeholder="请输入二维码链接" 
                value="<?php echo htmlspecialchars($qrUrl ?? ''); ?>"
                required>
            <small>这是QQ群的二维码链接，用于生成二维码图片</small>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存二维码链接
        </button>
    </form>
</div>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-eye"></i> 预览效果
    </h2>
    <div class="preview">
        <div class="preview-title">当前设置预览:</div>
        <div style="margin-top: 10px;">
            <strong>标题:</strong> <?php echo htmlspecialchars($joinSettings['title'] ?? '未设置'); ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>描述:</strong> <?php echo htmlspecialchars($joinSettings['description'] ?? '未设置'); ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>服务器地址:</strong> <?php echo htmlspecialchars($joinSettings['server_address'] ?? '未设置'); ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>游戏版本:</strong> <?php echo htmlspecialchars($joinSettings['server_version'] ?? '未设置'); ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>QQ交流群:</strong> <?php echo htmlspecialchars($joinSettings['qq_group'] ?? '未设置'); ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>二维码链接:</strong> <?php echo htmlspecialchars($qrUrl ?? '未设置'); ?>
        </div>
    </div>
</div>

<?php
// 关闭数据库连接
$db->close();

require_once 'footer.php';
?>