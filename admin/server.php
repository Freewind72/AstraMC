<?php
require_once 'header.php';

// 获取当前服务器设置
$currentServerAddress = "";
$currentServerName = "";
$currentServerSecondaryAddress = "";
$currentServerSecondaryName = "";

// 获取服务器特点列表
$serverFeatures = [];

try {
    // 获取主服务器设置
    $result = $db->query("SELECT server_address, server_name FROM server_settings ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $currentServerAddress = $row['server_address'];
        $currentServerName = $row['server_name'];
    }
    
    // 获取备用服务器设置
    $resultSecondary = $db->query("SELECT server_address, server_name FROM server_settings_secondary ORDER BY id DESC LIMIT 1");
    if ($rowSecondary = $resultSecondary->fetchArray(SQLITE3_ASSOC)) {
        $currentServerSecondaryAddress = $rowSecondary['server_address'];
        $currentServerSecondaryName = $rowSecondary['server_name'];
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
    if ($_POST['action'] === 'update_server') {
        $serverAddress = trim($_POST['server_address']);
        $serverName = trim($_POST['server_name']);
        $serverSecondaryAddress = trim($_POST['server_secondary_address']);
        $serverSecondaryName = trim($_POST['server_secondary_name']);
        
        $hasError = false;
        
        // 验证主服务器设置
        if (empty($serverAddress) || empty($serverName)) {
            $message = '服务器地址和名称都不能为空。';
            $hasError = true;
        }
        
        // 验证备用服务器设置
        if (empty($serverSecondaryAddress) || empty($serverSecondaryName)) {
            $message = '备用服务器地址和名称都不能为空。';
            $hasError = true;
        }
        
        if (!$hasError) {
            try {
                // 更新主服务器设置
                $checkResult = $db->query("SELECT COUNT(*) as count FROM server_settings");
                $row = $checkResult->fetchArray(SQLITE3_ASSOC);
                
                if ($row && $row['count'] > 0) {
                    // 如果存在记录，则更新第一条记录
                    $stmt = $db->prepare("UPDATE server_settings SET server_address = :server_address, server_name = :server_name WHERE id = (SELECT MIN(id) FROM server_settings)");
                    $stmt->bindValue(':server_address', $serverAddress, SQLITE3_TEXT);
                    $stmt->bindValue(':server_name', $serverName, SQLITE3_TEXT);
                } else {
                    // 如果不存在记录，则插入新记录
                    $stmt = $db->prepare("INSERT INTO server_settings (server_address, server_name) VALUES (:server_address, :server_name)");
                    $stmt->bindValue(':server_address', $serverAddress, SQLITE3_TEXT);
                    $stmt->bindValue(':server_name', $serverName, SQLITE3_TEXT);
                }
                
                if (!$stmt->execute()) {
                    $message = '主服务器更新失败，请重试。';
                    $hasError = true;
                }
                
                // 更新备用服务器设置
                if (!$hasError) {
                    $checkResultSecondary = $db->query("SELECT COUNT(*) as count FROM server_settings_secondary");
                    $rowSecondary = $checkResultSecondary->fetchArray(SQLITE3_ASSOC);
                    
                    if ($rowSecondary && $rowSecondary['count'] > 0) {
                        // 如果存在记录，则更新第一条记录
                        $stmtSecondary = $db->prepare("UPDATE server_settings_secondary SET server_address = :server_address, server_name = :server_name WHERE id = (SELECT MIN(id) FROM server_settings_secondary)");
                        $stmtSecondary->bindValue(':server_address', $serverSecondaryAddress, SQLITE3_TEXT);
                        $stmtSecondary->bindValue(':server_name', $serverSecondaryName, SQLITE3_TEXT);
                    } else {
                        // 如果不存在记录，则插入新记录
                        $stmtSecondary = $db->prepare("INSERT INTO server_settings_secondary (server_address, server_name) VALUES (:server_address, :server_name)");
                        $stmtSecondary->bindValue(':server_address', $serverSecondaryAddress, SQLITE3_TEXT);
                        $stmtSecondary->bindValue(':server_name', $serverSecondaryName, SQLITE3_TEXT);
                    }
                    
                    if (!$stmtSecondary->execute()) {
                        $message = '备用服务器更新失败，请重试。';
                        $hasError = true;
                    }
                }
                
                if (!$hasError) {
                    $message = '服务器设置已成功更新！';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
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
        <input type="hidden" name="action" value="update_server">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <h3>主服务器设置</h3>
        <div class="form-group">
            <label class="form-label" for="server_address">服务器地址</label>
            <input 
                type="text" 
                id="server_address" 
                name="server_address" 
                class="form-input" 
                placeholder="请输入服务器地址，例如: mcda.xin" 
                value="<?php echo htmlspecialchars($currentServerAddress); ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="server_name">服务器名称</label>
            <input 
                type="text" 
                id="server_name" 
                name="server_name" 
                class="form-input" 
                placeholder="请输入服务器名称，例如: 原始大陆" 
                value="<?php echo htmlspecialchars($currentServerName); ?>">
        </div>
        
        <h3>备用服务器设置</h3>
        <div class="form-group">
            <label class="form-label" for="server_secondary_address">备用服务器地址</label>
            <input 
                type="text" 
                id="server_secondary_address" 
                name="server_secondary_address" 
                class="form-input" 
                placeholder="请输入备用服务器地址，例如: mymcc.xin" 
                value="<?php echo htmlspecialchars($currentServerSecondaryAddress); ?>"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="server_secondary_name">备用服务器名称</label>
            <input 
                type="text" 
                id="server_secondary_name" 
                name="server_secondary_name" 
                class="form-input" 
                placeholder="请输入备用服务器名称，例如: 备用服务器" 
                value="<?php echo htmlspecialchars($currentServerSecondaryName); ?>">
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> 保存设置
        </button>
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
        <div class="preview-title">当前服务器地址:</div>
        <div><?php echo htmlspecialchars($currentServerAddress); ?></div>
        <div class="preview-title" style="margin-top: 15px;">当前服务器名称:</div>
        <div><?php echo htmlspecialchars($currentServerName); ?></div>
        
        <div class="preview-title" style="margin-top: 25px;">备用服务器地址:</div>
        <div><?php echo htmlspecialchars($currentServerSecondaryAddress); ?></div>
        <div class="preview-title" style="margin-top: 15px;">备用服务器名称:</div>
        <div><?php echo htmlspecialchars($currentServerSecondaryName); ?></div>
    </div>
</div>

<?php
require_once 'footer.php';
?>