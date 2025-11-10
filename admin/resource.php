<?php
require_once 'header.php';
require_once 'server_features_display.php';

// 获取当前资源包URL
$currentResourceUrl = "";
$resourceSections = [];

try {
    $result = $db->query("SELECT resource_url FROM resource_settings ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $currentResourceUrl = $row['resource_url'];
    }
    
    // 获取资源下载简介和卡片列表
    $sectionsResult = $db->query("SELECT * FROM resource_sections ORDER BY section_type DESC, sort_order ASC, id ASC");
    while ($row = $sectionsResult->fetchArray(SQLITE3_ASSOC)) {
        $resourceSections[] = $row;
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理资源包设置表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_resource') {
        $resourceUrl = trim($_POST['resource_url']);
        
        if (!empty($resourceUrl)) {
            try {
                // 检查是否已存在记录
                $checkResult = $db->query("SELECT COUNT(*) as count FROM resource_settings");
                $row = $checkResult->fetchArray(SQLITE3_ASSOC);
                
                if ($row && $row['count'] > 0) {
                    // 如果存在记录，则更新第一条记录
                    $stmt = $db->prepare("UPDATE resource_settings SET resource_url = :resource_url WHERE id = (SELECT MIN(id) FROM resource_settings)");
                    $stmt->bindValue(':resource_url', $resourceUrl, SQLITE3_TEXT);
                } else {
                    // 如果不存在记录，则插入新记录
                    $stmt = $db->prepare("INSERT INTO resource_settings (resource_url) VALUES (:resource_url)");
                    $stmt->bindValue(':resource_url', $resourceUrl, SQLITE3_TEXT);
                }
                
                if ($stmt->execute()) {
                    $message = '资源包设置已成功更新！';
                } else {
                    $message = '更新失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        } else {
            $message = '请输入有效的资源包URL。';
        }
        
        // 添加JavaScript以滚动到资源包管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "resource-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'add_section') {
        // 添加资源下载简介或卡片
        $sectionType = trim($_POST['section_type']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($title) || empty($description)) {
            $message = '标题和描述都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO resource_sections (section_type, title, description, sort_order) VALUES (:section_type, :title, :description, :sort_order)");
                $stmt->bindValue(':section_type', $sectionType, SQLITE3_TEXT);
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = '资源下载内容已成功添加！';
                } else {
                    $message = '添加资源下载内容失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '添加失败：' . $e->getMessage();
            }
        }
        
        // 添加JavaScript以滚动到资源下载管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "resource-download-section";
            });
        </script>';
    } elseif ($_POST['action'] === 'update_section') {
        // 更新资源下载简介或卡片
        $id = intval($_POST['section_id']);
        $sectionType = trim($_POST['section_type']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($title) || empty($description)) {
            $message = '标题和描述都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("UPDATE resource_sections SET section_type = :section_type, title = :title, description = :description, sort_order = :sort_order WHERE id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->bindValue(':section_type', $sectionType, SQLITE3_TEXT);
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = '资源下载内容已成功更新！';
                } else {
                    $message = '更新资源下载内容失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
        
        // 添加JavaScript以滚动到资源下载管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "resource-download-section";
            });
        </script>';
    } elseif ($_POST['action'] === 'delete_section') {
        // 删除资源下载简介或卡片
        $id = intval($_POST['section_id']);
        
        try {
            $stmt = $db->prepare("DELETE FROM resource_sections WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $message = '资源下载内容已成功删除！';
            } else {
                $message = '删除资源下载内容失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '删除失败：' . $e->getMessage();
        }
        
        // 添加JavaScript以滚动到资源下载管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "resource-download-section";
            });
        </script>';
    }
}
?>

<div class="card" id="resource-form">
    <h2 class="card-title">
        <i class="fas fa-file-archive"></i> 资源包管理
    </h2>
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="action" value="update_resource">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <div class="form-group">
            <label class="form-label" for="resource_url">资源包URL</label>
            <input 
                type="url" 
                id="resource_url" 
                name="resource_url" 
                class="form-input" 
                placeholder="请输入资源包文件的完整URL" 
                value="<?php echo htmlspecialchars($currentResourceUrl); ?>"
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
        <div class="preview-title">当前资源包URL:</div>
        <?php if ($currentResourceUrl): ?>
            <div><?php echo htmlspecialchars($currentResourceUrl); ?></div>
            <div style="margin-top: 15px;">
                <a href="<?php echo htmlspecialchars($currentResourceUrl); ?>" class="btn" target="_blank">
                    <i class="fas fa-external-link-alt"></i> 测试链接
                </a>
            </div>
        <?php else: ?>
            <div>暂无设置资源包URL</div>
        <?php endif; ?>
    </div>
</div>

<div class="card" id="resource-download-section">
    <h2 class="card-title">
        <i class="fas fa-file-download"></i> 资源下载管理
    </h2>
    
    <!-- 添加资源下载内容表单 -->
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_section">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <h3>添加新内容</h3>
        <div class="form-group">
            <label class="form-label" for="section_type">内容类型</label>
            <select id="section_type" name="section_type" class="form-input" required>
                <option value="intro">简介</option>
                <option value="card">卡片</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="title">标题</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                class="form-input" 
                placeholder="请输入标题"
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
            <i class="fas fa-plus"></i> 添加内容
        </button>
    </form>
    
    <!-- 资源下载内容列表 -->
    <h3>现有内容</h3>
    <?php if (empty($resourceSections)): ?>
        <p>暂无资源下载内容，请添加。</p>
    <?php else: ?>
        <?php foreach ($resourceSections as $index => $section): ?>
        <div class="card mb-3">
            <form method="post">
                <input type="hidden" name="action" value="update_section">
                <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label class="form-label">内容类型</label>
                    <select name="section_type" class="form-input" required>
                        <option value="intro" <?php echo $section['section_type'] === 'intro' ? 'selected' : ''; ?>>简介</option>
                        <option value="card" <?php echo $section['section_type'] === 'card' ? 'selected' : ''; ?>>卡片</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">标题</label>
                    <input 
                        type="text" 
                        name="title" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($section['title']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">描述</label>
                    <textarea 
                        name="description" 
                        class="form-input" 
                        rows="3"
                        required><?php echo htmlspecialchars($section['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        class="form-input" 
                        value="<?php echo $section['sort_order']; ?>"
                        min="0">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> 保存
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteSection(<?php echo $section['id']; ?>)">
                        <i class="fas fa-trash"></i> 删除
                    </button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function deleteSection(sectionId) {
    if (confirm('确定要删除这个资源下载内容吗？')) {
        // 创建一个隐藏的表单并提交
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_section';
        form.appendChild(actionInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'section_id';
        idInput.value = sectionId;
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

<?php
// 关闭数据库连接
$db->close();

require_once 'footer.php';
?>