<?php
require_once 'header.php';

// 检查管理员权限
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// 确保banned_ips表存在
try {
    $db->exec("CREATE TABLE IF NOT EXISTS banned_ips (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address TEXT NOT NULL UNIQUE,
        reason TEXT,
        banned_by INTEGER,
        banned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NULL
    )");
} catch (Exception $e) {
    // 忽略表已存在的错误
}

$message = '';
$messageType = '';

// 处理封禁IP表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'ban_ip' && isset($_POST['ip_address'])) {
        $ipAddress = trim($_POST['ip_address']);
        $reason = trim($_POST['reason'] ?? '');
        $expiresAt = trim($_POST['expires_at'] ?? '');
        
        // 验证IP地址格式
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $message = '无效的IP地址格式。';
            $messageType = 'error';
        } else {
            try {
                // 检查IP是否已经封禁
                $stmt = $db->prepare("SELECT id FROM banned_ips WHERE ip_address = :ip AND (expires_at IS NULL OR expires_at > datetime('now'))");
                $stmt->bindValue(':ip', $ipAddress, SQLITE3_TEXT);
                $result = $stmt->execute();
                $existingBan = $result->fetchArray(SQLITE3_ASSOC);
                
                if ($existingBan) {
                    $message = '该IP地址已经被封禁。';
                    $messageType = 'error';
                } else {
                    // 插入新的封禁记录
                    $stmt = $db->prepare("INSERT INTO banned_ips (ip_address, reason, banned_by, expires_at) VALUES (:ip, :reason, :banned_by, :expires_at)");
                    $stmt->bindValue(':ip', $ipAddress, SQLITE3_TEXT);
                    $stmt->bindValue(':reason', $reason, SQLITE3_TEXT);
                    $stmt->bindValue(':banned_by', $_SESSION['admin_id'], SQLITE3_INTEGER);
                    
                    if (!empty($expiresAt)) {
                        $stmt->bindValue(':expires_at', $expiresAt, SQLITE3_TEXT);
                    } else {
                        $stmt->bindValue(':expires_at', null, SQLITE3_NULL);
                    }
                    
                    if ($stmt->execute()) {
                        $message = 'IP地址 ' . htmlspecialchars($ipAddress) . ' 已成功封禁。';
                        $messageType = 'success';
                    } else {
                        $message = '封禁IP地址失败，请重试。';
                        $messageType = 'error';
                    }
                }
            } catch (Exception $e) {
                $message = '操作失败: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($_POST['action'] === 'unban_ip' && isset($_POST['ban_id'])) {
        $banId = intval($_POST['ban_id']);
        
        try {
            $stmt = $db->prepare("DELETE FROM banned_ips WHERE id = :id");
            $stmt->bindValue(':id', $banId, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $message = 'IP地址封禁已解除。';
                $messageType = 'success';
            } else {
                $message = '解除封禁失败，请重试。';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = '操作失败: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// 获取封禁的IP列表
$bannedIps = [];
try {
    // 再次确保表存在
    $db->exec("CREATE TABLE IF NOT EXISTS banned_ips (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address TEXT NOT NULL UNIQUE,
        reason TEXT,
        banned_by INTEGER,
        banned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NULL
    )");
    
    $stmt = $db->prepare("SELECT bi.*, a.username as banned_by_name FROM banned_ips bi LEFT JOIN admins a ON bi.banned_by = a.id ORDER BY bi.banned_at DESC");
    $result = $stmt->execute();
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $bannedIps[] = $row;
    }
} catch (Exception $e) {
    $message = '获取封禁列表失败: ' . $e->getMessage();
    $messageType = 'error';
}
?>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-ban"></i> IP封禁管理
    </h2>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <!-- 封禁IP表单 -->
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="ban_ip">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <h3>封禁IP地址</h3>
        <div class="form-group">
            <label class="form-label" for="ip_address">IP地址</label>
            <input 
                type="text" 
                id="ip_address" 
                name="ip_address" 
                class="form-input" 
                placeholder="请输入要封禁的IP地址"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="reason">封禁原因</label>
            <input 
                type="text" 
                id="reason" 
                name="reason" 
                class="form-input" 
                placeholder="请输入封禁原因（可选）">
        </div>
        <div class="form-group">
            <label class="form-label" for="expires_at">过期时间</label>
            <input 
                type="datetime-local" 
                id="expires_at" 
                name="expires_at" 
                class="form-input">
            <small>留空表示永久封禁</small>
        </div>
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-ban"></i> 封禁IP
        </button>
    </form>
    
    <!-- 封禁列表 -->
    <h3>当前封禁列表</h3>
    <?php if (empty($bannedIps)): ?>
        <p>暂无封禁的IP地址。</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>IP地址</th>
                        <th>封禁原因</th>
                        <th>封禁者</th>
                        <th>封禁时间</th>
                        <th>过期时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bannedIps as $ban): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ban['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($ban['reason'] ?? '未指定'); ?></td>
                        <td><?php echo htmlspecialchars($ban['banned_by_name'] ?? '未知'); ?></td>
                        <td><?php echo htmlspecialchars($ban['banned_at']); ?></td>
                        <td><?php echo $ban['expires_at'] ? htmlspecialchars($ban['expires_at']) : '永久'; ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="unban_ip">
                                <input type="hidden" name="ban_id" value="<?php echo $ban['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('确定要解除对该IP的封禁吗？')">
                                    <i class="fas fa-trash"></i> 解除封禁
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.table-responsive {
    overflow-x: auto;
}

.btn-small {
    padding: 5px 10px;
    font-size: 12px;
}
</style>

<?php
require_once 'footer.php';
?>