<?php
require_once 'header.php';
require_once 'server_features_display.php';

// 获取当前视频背景URL
$currentVideoUrl = "";
try {
    $result = $db->query("SELECT video_url FROM video_settings ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $currentVideoUrl = $row['video_url'];
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理视频背景设置表单提交
if (isset($_POST['action']) && $_POST['action'] === 'update_video') {
    $videoUrl = trim($_POST['video_url']);
    
    if (!empty($videoUrl)) {
        try {
            // 检查是否已存在记录
            $checkResult = $db->query("SELECT COUNT(*) as count FROM video_settings");
            $row = $checkResult->fetchArray(SQLITE3_ASSOC);
            
            if ($row && $row['count'] > 0) {
                // 如果存在记录，则更新第一条记录
                $stmt = $db->prepare("UPDATE video_settings SET video_url = :video_url WHERE id = (SELECT MIN(id) FROM video_settings)");
                $stmt->bindValue(':video_url', $videoUrl, SQLITE3_TEXT);
            } else {
                // 如果不存在记录，则插入新记录
                $stmt = $db->prepare("INSERT INTO video_settings (video_url) VALUES (:video_url)");
                $stmt->bindValue(':video_url', $videoUrl, SQLITE3_TEXT);
            }
            
            if ($stmt->execute()) {
                $message = '视频背景设置已成功更新！';
            } else {
                $message = '更新失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '更新失败：' . $e->getMessage();
        }
    } else {
        $message = '请输入有效的视频背景URL。';
    }
    
    // 添加JavaScript以滚动到视频背景设置部分
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            window.location.hash = "video-form";
        });
    </script>';
}
?>

<div class="card" id="video-form">
    <h2 class="card-title">
        <i class="fas fa-video"></i> 视频背景设置
    </h2>
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="action" value="update_video">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="video_url">视频背景URL</label>
            <input 
                type="url" 
                id="video_url" 
                name="video_url" 
                class="form-input" 
                placeholder="请输入视频文件的完整URL，支持MP4格式" 
                value="<?php echo htmlspecialchars($currentVideoUrl); ?>"
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
        <div class="preview-title">当前视频背景URL:</div>
        <?php if ($currentVideoUrl): ?>
            <div><?php echo htmlspecialchars($currentVideoUrl); ?></div>
            <div style="margin-top: 15px;">
                <video controls style="width: 100%;">
                    <source src="<?php echo htmlspecialchars($currentVideoUrl); ?>" type="video/mp4">
                    您的浏览器不支持视频播放。
                </video>
            </div>
        <?php else: ?>
            <div>暂无设置视频背景URL</div>
        <?php endif; ?>
    </div>
</div>

<?php
// 关闭数据库连接
$db->close();

require_once 'footer.php';
?>