<?php
require_once 'header.php';

// 获取当前服务器设置
$currentServers = [];
$serverFeatures = [];

try {
    // 检查 servers 表是否存在
    $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='servers'");
    $tableExists = $tableCheck->fetchArray(SQLITE3_ASSOC);
    
    if ($tableExists) {
        // 获取所有服务器设置
        $result = $db->query("SELECT id, server_address, server_name, is_primary, sort_order FROM servers ORDER BY sort_order ASC, id ASC");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $currentServers[] = $row;
        }
    } else {
        // 如果 servers 表不存在，创建它并迁移数据
        $db->exec("CREATE TABLE IF NOT EXISTS servers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            server_address TEXT NOT NULL,
            server_name TEXT NOT NULL,
            is_primary BOOLEAN DEFAULT 0,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // 从旧表迁移数据
        $primaryResult = $db->query("SELECT server_address, server_name FROM server_settings ORDER BY id DESC LIMIT 1");
        $secondaryResult = $db->query("SELECT server_address, server_name FROM server_settings_secondary ORDER BY id DESC LIMIT 1");
        
        // 插入主服务器
        if ($primaryRow = $primaryResult->fetchArray(SQLITE3_ASSOC)) {
            $stmt = $db->prepare("INSERT INTO servers (server_address, server_name, is_primary, sort_order) VALUES (:server_address, :server_name, 1, 0)");
            $stmt->bindValue(':server_address', $primaryRow['server_address'], SQLITE3_TEXT);
            $stmt->bindValue(':server_name', $primaryRow['server_name'], SQLITE3_TEXT);
            $stmt->execute();
        }
        
        // 插入备用服务器
        if ($secondaryRow = $secondaryResult->fetchArray(SQLITE3_ASSOC)) {
            $stmt = $db->prepare("INSERT INTO servers (server_address, server_name, is_primary, sort_order) VALUES (:server_address, :server_name, 0, 1)");
            $stmt->bindValue(':server_address', $secondaryRow['server_address'], SQLITE3_TEXT);
            $stmt->bindValue(':server_name', $secondaryRow['server_name'], SQLITE3_TEXT);
            $stmt->execute();
        }
        
        // 重新获取服务器设置
        $result = $db->query("SELECT id, server_address, server_name, is_primary, sort_order FROM servers ORDER BY sort_order ASC, id ASC");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $currentServers[] = $row;
        }
    }
    
    // 获取服务器特点列表
    $featuresResult = $db->query("SELECT * FROM server_features ORDER BY sort_order ASC, id ASC");
    while ($row = $featuresResult->fetchArray(SQLITE3_ASSOC)) {
        $serverFeatures[] = $row;
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理服务器设置表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_servers') {
        $hasError = false;
        
        try {
            // 开始事务
            $db->exec('BEGIN TRANSACTION');
            
            // 删除所有现有服务器
            $db->exec("DELETE FROM servers");
            
            // 插入新服务器列表
            if (isset($_POST['servers']) && is_array($_POST['servers'])) {
                $stmt = $db->prepare("INSERT INTO servers (server_address, server_name, is_primary, sort_order) VALUES (:server_address, :server_name, :is_primary, :sort_order)");
                
                foreach ($_POST['servers'] as $index => $server) {
                    $serverAddress = trim($server['address']);
                    $serverName = trim($server['name']);
                    $isPrimary = isset($server['is_primary']) ? 1 : 0;
                    
                    // 验证服务器设置
                    if (empty($serverAddress) || empty($serverName)) {
                        $message = '服务器地址和名称都不能为空。';
                        $hasError = true;
                        break;
                    }
                    
                    $stmt->bindValue(':server_address', $serverAddress, SQLITE3_TEXT);
                    $stmt->bindValue(':server_name', $serverName, SQLITE3_TEXT);
                    $stmt->bindValue(':is_primary', $isPrimary, SQLITE3_INTEGER);
                    $stmt->bindValue(':sort_order', $index, SQLITE3_INTEGER);
                    $stmt->execute();
                }
            } else {
                $message = '至少需要添加一个服务器。';
                $hasError = true;
            }
            
            if (!$hasError) {
                // 提交事务
                $db->exec('COMMIT');
                $message = '服务器设置已成功更新！';
                
                // 重新加载服务器列表
                $currentServers = [];
                $result = $db->query("SELECT id, server_address, server_name, is_primary, sort_order FROM servers ORDER BY sort_order ASC, id ASC");
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $currentServers[] = $row;
                }
            } else {
                // 回滚事务
                $db->exec('ROLLBACK');
            }
        } catch (Exception $e) {
            // 回滚事务
            $db->exec('ROLLBACK');
            $message = '更新失败：' . $e->getMessage();
        }
        
        // 添加JavaScript以滚动到服务器设置部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "server-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'add_feature') {
        // 添加服务器特点
        $iconCode = trim($_POST['icon_code']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($iconCode) || empty($title) || empty($description)) {
            $message = '所有字段都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO server_features (icon_code, title, description, sort_order) VALUES (:icon_code, :title, :description, :sort_order)");
                $stmt->bindValue(':icon_code', $iconCode, SQLITE3_TEXT);
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = '服务器特点已成功添加！';
                    // 重新加载服务器特点列表
                    $serverFeatures = [];
                    $featuresResult = $db->query("SELECT * FROM server_features ORDER BY sort_order ASC, id ASC");
                    while ($row = $featuresResult->fetchArray(SQLITE3_ASSOC)) {
                        $serverFeatures[] = $row;
                    }
                } else {
                    $message = '添加服务器特点失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '添加失败：' . $e->getMessage();
            }
        }
        
        // 添加JavaScript以滚动到服务器特点管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "server-features-section";
            });
        </script>';
    } elseif ($_POST['action'] === 'update_feature') {
        // 更新服务器特点
        $id = intval($_POST['feature_id']);
        $iconCode = trim($_POST['icon_code']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($iconCode) || empty($title) || empty($description)) {
            $message = '所有字段都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("UPDATE server_features SET icon_code = :icon_code, title = :title, description = :description, sort_order = :sort_order WHERE id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->bindValue(':icon_code', $iconCode, SQLITE3_TEXT);
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = '服务器特点已成功更新！';
                    // 重新加载服务器特点列表
                    $serverFeatures = [];
                    $featuresResult = $db->query("SELECT * FROM server_features ORDER BY sort_order ASC, id ASC");
                    while ($row = $featuresResult->fetchArray(SQLITE3_ASSOC)) {
                        $serverFeatures[] = $row;
                    }
                } else {
                    $message = '更新服务器特点失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
        
        // 添加JavaScript以滚动到服务器特点管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "server-features-section";
            });
        </script>';
    } elseif ($_POST['action'] === 'delete_feature') {
        // 删除服务器特点
        $id = intval($_POST['feature_id']);
        
        try {
            $stmt = $db->prepare("DELETE FROM server_features WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $message = '服务器特点已成功删除！';
                // 重新加载服务器特点列表
                $serverFeatures = [];
                $featuresResult = $db->query("SELECT * FROM server_features ORDER BY sort_order ASC, id ASC");
                while ($row = $featuresResult->fetchArray(SQLITE3_ASSOC)) {
                    $serverFeatures[] = $row;
                }
            } else {
                $message = '删除服务器特点失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '删除失败：' . $e->getMessage();
        }
        
        // 添加JavaScript以滚动到服务器特点管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "server-features-section";
            });
        </script>';
    }
}
?>

<div class="card" id="server-form">
    <h2 class="card-title">
        <i class="fas fa-server"></i> 服务器设置
    </h2>
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="action" value="update_servers">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <h3>服务器列表</h3>
        <div id="servers-container">
            <?php if (empty($currentServers)): ?>
                <div class="server-item">
                    <div class="form-group">
                        <label class="form-label">服务器地址</label>
                        <input type="text" name="servers[0][address]" class="form-input" placeholder="请输入服务器地址，例如: mcda.xin" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">服务器名称</label>
                        <input type="text" name="servers[0][name]" class="form-input" placeholder="请输入服务器名称，例如: 原始大陆">
                    </div>
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="servers[0][is_primary]" value="1" checked>
                            <span>设为主要服务器</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">排序</label>
                        <input type="number" name="servers[0][sort_order]" class="form-input" value="0" min="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-server-btn" onclick="removeServer(this)">删除服务器</button>
                </div>
            <?php else: ?>
                <?php foreach ($currentServers as $index => $server): ?>
                <div class="server-item">
                    <div class="form-group">
                        <label class="form-label">服务器地址</label>
                        <input type="text" name="servers[<?php echo $index; ?>][address]" class="form-input" placeholder="请输入服务器地址，例如: mcda.xin" value="<?php echo htmlspecialchars($server['server_address']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">服务器名称</label>
                        <input type="text" name="servers[<?php echo $index; ?>][name]" class="form-input" placeholder="请输入服务器名称，例如: 原始大陆" value="<?php echo htmlspecialchars($server['server_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="servers[<?php echo $index; ?>][is_primary]" value="1" <?php echo $server['is_primary'] ? 'checked' : ''; ?>>
                            <span>设为主要服务器</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">排序</label>
                        <input type="number" name="servers[<?php echo $index; ?>][sort_order]" class="form-input" value="<?php echo $server['sort_order']; ?>" min="0">
                    </div>
                    <button type="button" class="btn btn-danger remove-server-btn" onclick="removeServer(this)">删除服务器</button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button type="button" class="btn btn-secondary add-server-btn" onclick="addServer()">
            <i class="fas fa-plus"></i> 添加服务器
        </button>
        
        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> 保存设置
            </button>
        </div>
    </form>
</div>

<!-- 服务器特点管理 -->
<div class="card" id="server-features-section">
    <h2 class="card-title">
        <i class="fas fa-star"></i> 服务器特点管理
    </h2>
    
    <!-- 添加服务器特点表单 -->
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_feature">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <h3>添加新特点</h3>
        <div class="form-group">
            <label class="form-label" for="icon_code">图标代码</label>
            <textarea 
                id="icon_code" 
                name="icon_code" 
                class="form-input" 
                rows="4"
                placeholder="请输入阿里巴巴矢量图标SVG代码，例如: <svg>...</svg>，或者图标类名，例如: fas fa-tree"
                required></textarea>
            <small>
                可以输入完整的SVG代码或图标类名。支持阿里巴巴矢量图标库和Font Awesome图标库。<br>
                注意：SVG图标将在前端显示为40x40像素大小。
            </small>
        </div>
        <div class="form-group">
            <label class="form-label" for="title">特点标题</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                class="form-input" 
                placeholder="请输入特点标题"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="description">特点描述</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-input" 
                rows="3" 
                placeholder="请输入特点描述"
                required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="sort_order">排序</label>
            <input 
                type="number" 
                id="sort_order" 
                name="sort_order" 
                class="form-input" 
                value="0"
                min="0">
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-plus"></i> 添加特点
        </button>
    </form>
    
    <!-- 服务器特点列表 -->
    <h3>现有特点</h3>
    <?php if (empty($serverFeatures)): ?>
        <p>暂无服务器特点，请添加。</p>
    <?php else: ?>
        <?php foreach ($serverFeatures as $index => $feature): ?>
        <div class="card mb-3">
            <form method="post">
                <input type="hidden" name="action" value="update_feature">
                <input type="hidden" name="feature_id" value="<?php echo $feature['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label class="form-label">图标代码</label>
                    <textarea 
                        name="icon_code" 
                        class="form-input" 
                        rows="4"
                        required><?php echo htmlspecialchars($feature['icon_code']); ?></textarea>
                    <small>
                        注意：SVG图标将在前端显示为40x40像素大小。
                    </small>
                </div>
                <div class="form-group">
                    <label class="form-label">特点标题</label>
                    <input 
                        type="text" 
                        name="title" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($feature['title']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">特点描述</label>
                    <textarea 
                        name="description" 
                        class="form-input" 
                        rows="3"
                        required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input type="number" 
                        name="sort_order" 
                        class="form-input" 
                        value="<?php echo $feature['sort_order']; ?>"
                        min="0">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> 保存
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteFeature(<?php echo $feature['id']; ?>)">
                        <i class="fas fa-trash"></i> 删除
                    </button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function addServer() {
    const container = document.getElementById('servers-container');
    const serverCount = container.querySelectorAll('.server-item').length;
    
    const serverItem = document.createElement('div');
    serverItem.className = 'server-item';
    serverItem.innerHTML = `
        <div class="form-group">
            <label class="form-label">服务器地址</label>
            <input type="text" name="servers[${serverCount}][address]" class="form-input" placeholder="请输入服务器地址，例如: mcda.xin" required>
        </div>
        <div class="form-group">
            <label class="form-label">服务器名称</label>
            <input type="text" name="servers[${serverCount}][name]" class="form-input" placeholder="请输入服务器名称，例如: 原始大陆">
        </div>
        <div class="form-group">
            <label class="form-checkbox">
                <input type="checkbox" name="servers[${serverCount}][is_primary]" value="1">
                <span>设为主要服务器</span>
            </label>
        </div>
        <div class="form-group">
            <label class="form-label">排序</label>
            <input type="number" name="servers[${serverCount}][sort_order]" class="form-input" value="${serverCount}" min="0">
        </div>
        <button type="button" class="btn btn-danger remove-server-btn" onclick="removeServer(this)">删除服务器</button>
    `;
    
    container.appendChild(serverItem);
}

function removeServer(button) {
    if (confirm('确定要删除这个服务器吗？')) {
        const serverItem = button.closest('.server-item');
        serverItem.remove();
    }
}

function deleteFeature(featureId) {
    if (confirm('确定要删除这个特点吗？')) {
        // 创建一个隐藏的表单并提交
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_feature';
        form.appendChild(actionInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'feature_id';
        idInput.value = featureId;
        form.appendChild(idInput);
        
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo htmlspecialchars($csrfToken); ?>';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// 页面加载后，如果有hash，则滚动到对应位置
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash) {
        var targetElement = document.querySelector(window.location.hash);
        if (targetElement) {
            targetElement.scrollIntoView();
        }
    }
});
</script>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-info-circle"></i> 当前设置
    </h2>
    <div class="preview">
        <div class="preview-title">服务器列表:</div>
        <?php if (empty($currentServers)): ?>
            <div>暂无服务器设置</div>
        <?php else: ?>
            <?php foreach ($currentServers as $server): ?>
            <div style="margin: 10px 0;">
                <strong><?php echo htmlspecialchars($server['server_name']); ?>:</strong> 
                <?php echo htmlspecialchars($server['server_address']); ?>
                <?php if ($server['is_primary']): ?>
                    <span style="color: green;">(主要)</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>