<?php
require_once 'header.php';
require_once 'server_features_display.php';

// 获取当前音乐URL
$currentMusicUrl = "";
try {
    $result = $db->query("SELECT music_url FROM music_settings ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $currentMusicUrl = $row['music_url'];
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理音乐设置表单提交
if (isset($_POST['action']) && $_POST['action'] === 'update_music') {
    $musicUrl = trim($_POST['music_url']);
    
    if (!empty($musicUrl)) {
        try {
            // 检查是否已存在记录
            $checkResult = $db->query("SELECT COUNT(*) as count FROM music_settings");
            $row = $checkResult->fetchArray(SQLITE3_ASSOC);
            
            if ($row && $row['count'] > 0) {
                // 如果存在记录，则更新第一条记录
                $stmt = $db->prepare("UPDATE music_settings SET music_url = :music_url WHERE id = (SELECT MIN(id) FROM music_settings)");
                $stmt->bindValue(':music_url', $musicUrl, SQLITE3_TEXT);
            } else {
                // 如果不存在记录，则插入新记录
                $stmt = $db->prepare("INSERT INTO music_settings (music_url, auto_play) VALUES (:music_url, :auto_play)");
                $stmt->bindValue(':music_url', $musicUrl, SQLITE3_TEXT);
                $stmt->bindValue(':auto_play', 0, SQLITE3_INTEGER); // 始终设置为0，即不自动播放
            }
            
            if ($stmt->execute()) {
                // 成功更新后显示消息但不跳转
                $message = '音乐设置已成功更新！';
            } else {
                $message = '更新失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '更新失败：' . $e->getMessage();
        }
    } else {
        $message = '请输入有效的音乐URL。';
    }
    
    // 添加JavaScript以滚动到音乐管理部分
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            window.location.hash = "music-form";
        });
    </script>';
}
?>

<div class="card" id="music-form">
    <h2 class="card-title">
        <i class="fas fa-music"></i> 音乐管理
    </h2>
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="action" value="update_music">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="music_url">音乐URL</label>
            <input 
                type="url" 
                id="music_url" 
                name="music_url" 
                class="form-input" 
                placeholder="请输入音乐文件的完整URL，支持MP3格式" 
                value="<?php echo htmlspecialchars($currentMusicUrl); ?>"
                required>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存设置
        </button>
    </form>
</div>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-info-circle"></i> 当前设置
    </h2>
    <div class="preview">
        <div class="preview-title">当前音乐URL:</div>
        <?php if ($currentMusicUrl): ?>
            <div><?php echo htmlspecialchars($currentMusicUrl); ?></div>
            <div style="margin-top: 15px;">
                <audio controls style="width: 100%;">
                    <source src="<?php echo htmlspecialchars($currentMusicUrl); ?>" type="audio/mpeg">
                    您的浏览器不支持音频播放。
                </audio>
            </div>
        <?php else: ?>
            <div>暂无设置音乐URL</div>
        <?php endif; ?>
    </div>
</div>

<?php
// 关闭数据库连接
$db->close();

require_once 'footer.php';
?>