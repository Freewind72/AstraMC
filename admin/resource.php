<?php
require_once 'header.php';

// 获取所有资源
$resources = [];
try {
    $resourcesResult = $db->query("SELECT * FROM resources ORDER BY sort_order ASC, id ASC");
    while ($row = $resourcesResult->fetchArray(SQLITE3_ASSOC)) {
        $resources[] = $row;
    }
} catch (Exception $e) {
    $message = '获取资源列表时出错：' . $e->getMessage();
}

// 处理资源表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add_resource') {
        // 添加新资源
        $icon = trim($_POST['icon']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $url = trim($_POST['url']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($icon) || empty($name) || empty($description) || empty($url)) {
            $message = '所有字段都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO resources (icon, name, description, url, sort_order) VALUES (:icon, :name, :description, :url, :sort_order)");
                $stmt->bindValue(':icon', $icon, SQLITE3_TEXT);
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':url', $url, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    // 使用PRG模式防止重复提交
                    header('Location: resource.php?message=' . urlencode('资源已成功添加！'));
                    exit();
                } else {
                    $message = '添加资源失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '添加失败：' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'update_resource') {
        // 更新资源
        $id = intval($_POST['resource_id']);
        $icon = trim($_POST['icon']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $url = trim($_POST['url']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($icon) || empty($name) || empty($description) || empty($url)) {
            $message = '所有字段都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("UPDATE resources SET icon = :icon, name = :name, description = :description, url = :url, sort_order = :sort_order WHERE id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->bindValue(':icon', $icon, SQLITE3_TEXT);
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':url', $url, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    // 使用PRG模式防止重复提交
                    header('Location: resource.php?message=' . urlencode('资源已成功更新！'));
                    exit();
                } else {
                    $message = '更新资源失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'delete_resource') {
        // 删除资源
        $id = intval($_POST['resource_id']);
        
        try {
            $stmt = $db->prepare("DELETE FROM resources WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                // 使用PRG模式防止重复提交
                header('Location: resource.php?message=' . urlencode('资源已成功删除！'));
                exit();
            } else {
                $message = '删除资源失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '删除失败：' . $e->getMessage();
        }
    }
}

// 检查是否有通过URL传递的消息
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<div class="card" id="resources-section">
    <h2 class="card-title">
        <i class="fas fa-cubes"></i> 资源管理
    </h2>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- 添加资源表单 -->
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_resource">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <h3>添加新资源</h3>
        <div class="form-group">
            <label class="form-label" for="icon">图标 (Font Awesome类名)</label>
            <input 
                type="text" 
                id="icon" 
                name="icon" 
                class="form-input" 
                placeholder="例如: fas fa-download"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="name">资源名称</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-input" 
                placeholder="请输入资源名称"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="description">资源描述</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-input" 
                rows="3" 
                placeholder="请输入资源描述"
                required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="url">资源URL</label>
            <input 
                type="url" 
                id="url" 
                name="url" 
                class="form-input" 
                placeholder="请输入资源文件的完整URL"
                required>
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
            <i class="fas fa-plus"></i> 添加资源
        </button>
    </form>
    
    <!-- 资源列表 -->
    <h3>现有资源</h3>
    <?php if (empty($resources)): ?>
        <p>暂无资源，请添加。</p>
    <?php else: ?>
        <?php foreach ($resources as $index => $resource): ?>
        <div class="card mb-3">
            <form method="post">
                <input type="hidden" name="action" value="update_resource">
                <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label class="form-label">图标 (Font Awesome类名)</label>
                    <input 
                        type="text" 
                        name="icon" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($resource['icon']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">资源名称</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($resource['name']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">资源描述</label>
                    <textarea 
                        name="description" 
                        class="form-input" 
                        rows="3"
                        required><?php echo htmlspecialchars($resource['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">资源URL</label>
                    <input 
                        type="url" 
                        name="url" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($resource['url']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input type="number" 
                        name="sort_order" 
                        class="form-input" 
                        value="<?php echo $resource['sort_order']; ?>"
                        min="0">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> 保存
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteResource(<?php echo $resource['id']; ?>)">
                        <i class="fas fa-trash"></i> 删除
                    </button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function deleteResource(resourceId) {
    if (confirm('确定要删除这个资源吗？')) {
        // 创建一个隐藏的表单并提交
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_resource';
        form.appendChild(actionInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'resource_id';
        idInput.value = resourceId;
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
</script>

<?php include 'footer.php'; ?>