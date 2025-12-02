<?php
require_once 'header.php';

// 获取当前加入我们设置
$joinSettings = [];
$qrUrl = "";
$tutorialSettings = [];

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
    
    // 获取教程文档设置
    $tutorialResult = $db->query("SELECT * FROM tutorial_settings ORDER BY id DESC LIMIT 1");
    if ($tutorialRow = $tutorialResult->fetchArray(SQLITE3_ASSOC)) {
        $tutorialSettings = $tutorialRow;
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_join') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $joinSteps = trim($_POST['join_steps']);
        $qqGroup = trim($_POST['qq_group']);
        
        if (empty($title) || empty($description) || empty($qqGroup)) {
            $message = '标题、描述和QQ交流群都不能为空。';
        } else {
            try {
                // 检查是否已存在记录
                $checkResult = $db->query("SELECT COUNT(*) as count FROM join_settings");
                $row = $checkResult->fetchArray(SQLITE3_ASSOC);
                
                if ($row && $row['count'] > 0) {
                    // 如果存在记录，则更新第一条记录
                    $stmt = $db->prepare("UPDATE join_settings SET title = :title, description = :description, join_steps = :join_steps, qq_group = :qq_group WHERE id = (SELECT MIN(id) FROM join_settings)");
                    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                    $stmt->bindValue(':join_steps', $joinSteps, SQLITE3_TEXT);
                    $stmt->bindValue(':qq_group', $qqGroup, SQLITE3_TEXT);
                } else {
                    // 如果不存在记录，则插入新记录
                    $stmt = $db->prepare("INSERT INTO join_settings (title, description, join_steps, qq_group) VALUES (:title, :description, :join_steps, :qq_group)");
                    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                    $stmt->bindValue(':join_steps', $joinSteps, SQLITE3_TEXT);
                    $stmt->bindValue(':qq_group', $qqGroup, SQLITE3_TEXT);
                }
                
                if ($stmt->execute()) {
                    // 使用PRG模式防止重复提交
                    header('Location: join.php?message=' . urlencode('加入我们设置已成功更新！'));
                    exit();
                } else {
                    $message = '更新失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'update_qr') {
        $qrUrl = trim($_POST['qr_url']);
        
        if (empty($qrUrl)) {
            $message = '二维码链接不能为空。';
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
                    // 使用PRG模式防止重复提交
                    header('Location: join.php?message=' . urlencode('二维码链接已成功更新！'));
                    exit();
                } else {
                    $message = '更新失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'update_tutorial') {
        $title = trim($_POST['tutorial_title']);
        $content = trim($_POST['tutorial_content']);
        $buttonText = trim($_POST['tutorial_button_text']);
        $buttonUrl = trim($_POST['tutorial_button_url']);
        
        try {
            // 检查是否已存在记录
            $checkResult = $db->query("SELECT COUNT(*) as count FROM tutorial_settings");
            $row = $checkResult->fetchArray(SQLITE3_ASSOC);
            
            if ($row && $row['count'] > 0) {
                // 如果存在记录，则更新第一条记录
                $stmt = $db->prepare("UPDATE tutorial_settings SET title = :title, content = :content, button_text = :button_text, button_url = :button_url WHERE id = (SELECT MIN(id) FROM tutorial_settings)");
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':content', $content, SQLITE3_TEXT);
                $stmt->bindValue(':button_text', $buttonText, SQLITE3_TEXT);
                $stmt->bindValue(':button_url', $buttonUrl, SQLITE3_TEXT);
            } else {
                // 如果不存在记录，则插入新记录
                $stmt = $db->prepare("INSERT INTO tutorial_settings (title, content, button_text, button_url) VALUES (:title, :content, :button_text, :button_url)");
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':content', $content, SQLITE3_TEXT);
                $stmt->bindValue(':button_text', $buttonText, SQLITE3_TEXT);
                $stmt->bindValue(':button_url', $buttonUrl, SQLITE3_TEXT);
            }
            
            if ($stmt->execute()) {
                // 使用PRG模式防止重复提交
                header('Location: join.php?message=' . urlencode('教程文档设置已成功更新！'));
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
?>

<div class="card" id="join-form">
    <h2 class="card-title">
        <i class="fas fa-users"></i> 加入我们管理
    </h2>
    <?php if (isset($message) && strpos($message, '加入我们') !== false): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
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
            <label class="form-label" for="join_steps">加入步骤（支持HTML标签和换行）</label>
            <textarea 
                id="join_steps" 
                name="join_steps" 
                class="form-input" 
                rows="6" 
                placeholder="请输入加入步骤，支持HTML标签和换行"><?php echo htmlspecialchars($joinSettings['join_steps'] ?? ''); ?></textarea>
            <small>支持HTML标签，如&lt;b&gt;粗体&lt;/b&gt;、&lt;br&gt;换行等</small>
        </div>
        <div class="form-group">
            <label class="form-label" for="qq_group">QQ交流群</label>
            <textarea
                id="qq_group" 
                name="qq_group" 
                class="form-input" 
                rows="3" 
                placeholder="请输入QQ交流群号码，支持换行"><?php echo htmlspecialchars($joinSettings['qq_group'] ?? ''); ?></textarea>
            <small>支持换行，每行显示一个QQ群信息</small>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存设置
        </button>
    </form>
</div>

<div class="card" id="tutorial-form">
    <h2 class="card-title">
        <i class="fas fa-book"></i> 教程文档管理
    </h2>
    <?php if (isset($message) && strpos($message, '教程文档') !== false): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="action" value="update_tutorial">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="tutorial_title">标题</label>
            <input 
                type="text" 
                id="tutorial_title" 
                name="tutorial_title" 
                class="form-input" 
                placeholder="请输入标题" 
                value="<?php echo htmlspecialchars($tutorialSettings['title'] ?? ''); ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="tutorial_content">内容（支持换行）</label>
            <textarea 
                id="tutorial_content" 
                name="tutorial_content" 
                class="form-input" 
                rows="6" 
                placeholder="请输入教程文档内容，支持换行"><?php echo htmlspecialchars($tutorialSettings['content'] ?? ''); ?></textarea>
            <small>支持换行，每行内容将按原样显示</small>
        </div>
        <div class="form-group">
            <label class="form-label" for="tutorial_button_text">按钮文本</label>
            <input 
                type="text" 
                id="tutorial_button_text" 
                name="tutorial_button_text" 
                class="form-input" 
                placeholder="请输入按钮文本" 
                value="<?php echo htmlspecialchars($tutorialSettings['button_text'] ?? ''); ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="tutorial_button_url">按钮链接</label>
            <input 
                type="url" 
                id="tutorial_button_url" 
                name="tutorial_button_url" 
                class="form-input" 
                placeholder="请输入按钮链接（留空表示不显示按钮）" 
                value="<?php echo htmlspecialchars($tutorialSettings['button_url'] ?? ''); ?>">
            <small>这是点击按钮后跳转的链接，支持留空保存</small>
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
    <?php if (isset($message) && strpos($message, '二维码') !== false): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
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
            <strong>加入步骤:</strong> 
            <?php if (!empty($joinSettings['join_steps'])): ?>
                <div><?php echo $joinSettings['join_steps']; ?></div>
            <?php else: ?>
                <span style="color: #999;">未设置</span>
            <?php endif; ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>QQ交流群:</strong> <?php echo nl2br(htmlspecialchars($joinSettings['qq_group'] ?? '未设置')); ?></div>
        </div>
        <div style="margin-top: 10px;">
            <strong>二维码链接:</strong> <?php echo htmlspecialchars($qrUrl ?? '未设置'); ?>
        </div>
        <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee;">
            <strong>教程文档标题:</strong> <?php echo htmlspecialchars($tutorialSettings['title'] ?? '未设置'); ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>教程文档内容:</strong> 
            <?php if (!empty($tutorialSettings['content'])): ?>
                <div style="white-space: pre-line;"><?php echo htmlspecialchars($tutorialSettings['content']); ?></div>
            <?php else: ?>
                <span style="color: #999;">未设置</span>
            <?php endif; ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>按钮文本:</strong> <?php echo htmlspecialchars($tutorialSettings['button_text'] ?? '未设置'); ?>
        </div>
        <div style="margin-top: 10px;">
            <strong>按钮链接:</strong> <?php echo htmlspecialchars($tutorialSettings['button_url'] ?? '未设置'); ?>
        </div>
    </div>
</div>

<?php
// 关闭数据库连接
$db->close();

require_once 'footer.php';
?>