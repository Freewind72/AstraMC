<?php
require_once 'header.php';

// 检查并创建灯笼设置表
try {
    $db->exec("CREATE TABLE IF NOT EXISTS deng_settings (
        id INTEGER PRIMARY KEY,
        deng_text TEXT DEFAULT '圣诞快乐',
        is_enabled BOOLEAN DEFAULT 1,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 插入默认记录（如果不存在）
    $checkDengSettings = $db->query("SELECT COUNT(*) as count FROM deng_settings");
    $dengSettingsRow = $checkDengSettings->fetchArray(SQLITE3_ASSOC);
    if ($dengSettingsRow['count'] == 0) {
        $db->exec("INSERT INTO deng_settings (id, deng_text, is_enabled) VALUES (1, '圣诞快乐', 1)");
    }
} catch (Exception $e) {
    $message = '创建灯笼设置表时出错：' . $e->getMessage();
}

// 获取灯笼设置
$dengSettings = [];
try {
    $result = $db->query("SELECT * FROM deng_settings WHERE id = 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $dengSettings = $row;
    }
} catch (Exception $e) {
    $message = '获取灯笼设置时出错：' . $e->getMessage();
}

// 处理表单提交
if (isset($_POST['action']) && $_POST['action'] === 'update_deng_settings') {
    // 验证 CSRF Token
    if (!isset($_POST['csrf_token']) || !$securityManager->validateCSRFToken($_POST['csrf_token'])) {
        $message = '无效的请求令牌。';
    } else {
        $dengText = trim($_POST['deng_text']);
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        
        if (empty($dengText)) {
            $message = '灯笼文字不能为空。';
        } else {
            try {
                $stmt = $db->prepare("UPDATE deng_settings SET deng_text = :deng_text, is_enabled = :is_enabled, updated_at = datetime('now') WHERE id = 1");
                $stmt->bindValue(':deng_text', $dengText, SQLITE3_TEXT);
                $stmt->bindValue(':is_enabled', $isEnabled, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    // 使用PRG模式防止重复提交
                    header('Location: deng.php?message=' . urlencode('灯笼设置已成功更新！'));
                    exit();
                } else {
                    $message = '更新失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
    }
}

// 检查是否有通过URL传递的消息
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<div class="container">
    <div class="card">
        <h2 class="card-title">
            <i class="fas fa-lightbulb"></i> 灯笼设置
        </h2>
        <form method="POST" class="form">
            <input type="hidden" name="action" value="update_deng_settings">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            
            <div class="form-group">
                <label for="deng_text" class="form-label">灯笼文字:</label>
                <input type="text" id="deng_text" name="deng_text" class="form-input" 
                       value="<?php echo isset($dengSettings['deng_text']) ? htmlspecialchars($dengSettings['deng_text']) : '圣诞快乐'; ?>" 
                       placeholder="请输入灯笼上显示的文字（最多4个字符）" maxlength="4">
                <small class="form-text">设置将在页面刷新后生效，最多支持4个字符</small>
            </div>
            
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_enabled" id="is_enabled" 
                           <?php echo (!isset($dengSettings['is_enabled']) || $dengSettings['is_enabled']) ? 'checked' : ''; ?>>
                    <span>启用首页灯笼展示</span>
                </label>
                <small class="form-text">控制是否在首页显示灯笼</small>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> 保存灯笼设置
            </button>
        </form>
    </div>

    <div class="card">
        <h2 class="card-title">
            <i class="fas fa-eye"></i> 预览
        </h2>
        <div class="preview">
            <div class="preview-title">灯笼效果预览:</div>
            <div class="deng-preview-container">
                <?php 
                $dengText = isset($dengSettings['deng_text']) ? $dengSettings['deng_text'] : '圣诞快乐';
                $texts = mb_str_split($dengText, 1, 'UTF-8');
                // 确保只显示4个灯笼
                $texts = array_slice($texts, 0, 4);
                foreach ($texts as $index => $text): ?>
                <div class="deng-preview-box">
                    <div class="deng-preview">
                        <div class="deng-a-preview">
                            <div class="deng-b-preview">
                                <div class="deng-t-preview"><?php echo htmlspecialchars($text); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <style>
                .deng-preview-container {
                    display: flex;
                    justify-content: center;
                    gap: 20px;
                    margin-top: 20px;
                    flex-wrap: wrap;
                }
                
                .deng-preview-box {
                    position: relative;
                    width: 80px;
                }
                
                .deng-preview {
                    position: relative;
                    width: 80px;
                    height: 60px;
                    background: rgba(216, 0, 15, .8);
                    border-radius: 50% 50%;
                    box-shadow: -3px 3px 30px 2px #fa6c00;
                }
                
                .deng-a-preview { 
                    width: 65px; 
                    height: 60px; 
                    background: rgba(216, 0, 15, .1); 
                    border-radius: 50%;  
                    border: 2px solid #dc8f03; 
                    margin-left: 5px; 
                    display: flex; 
                    justify-content: center; 
                }
                
                .deng-b-preview { 
                    width: 45px; 
                    height: 55px; 
                    background: rgba(216, 0, 15, .1); 
                    border-radius: 60%; 
                    border: 2px solid #dc8f03; 
                }
                
                .deng-t-preview { 
                    font-family: '华文行楷', Arial, Lucida Grande, Tahoma, sans-serif; 
                    font-size: 24px; 
                    color: #dc8f03; 
                    font-weight: 700; 
                    line-height: 55px; 
                    text-align: center; 
                }
            </style>
        </div>
    </div>
</div>

<?php 
$db->close(); // 关闭数据库连接
include 'footer.php'; 
?>