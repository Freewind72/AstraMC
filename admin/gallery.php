<?php
require_once 'header.php';

// 获取展览图片列表
$galleryImages = [];

try {
    // 获取展览图片列表
    $imagesResult = $db->query("SELECT * FROM gallery_images ORDER BY sort_order ASC, id ASC");
    while ($row = $imagesResult->fetchArray(SQLITE3_ASSOC)) {
        $galleryImages[] = $row;
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add_image') {
        // 添加展览图片
        $imageUrl = trim($_POST['image_url']);
        $altText = trim($_POST['alt_text']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($imageUrl) || empty($altText)) {
            $message = '图片URL和替代文本都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO gallery_images (image_url, alt_text, sort_order) VALUES (:image_url, :alt_text, :sort_order)");
                $stmt->bindValue(':image_url', $imageUrl, SQLITE3_TEXT);
                $stmt->bindValue(':alt_text', $altText, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = '展览图片已成功添加！';
                } else {
                    $message = '添加展览图片失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '添加失败：' . $e->getMessage();
            }
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'update_image') {
        // 更新展览图片
        $id = intval($_POST['image_id']);
        $imageUrl = trim($_POST['image_url']);
        $altText = trim($_POST['alt_text']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($imageUrl) || empty($altText)) {
            $message = '图片URL和替代文本都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("UPDATE gallery_images SET image_url = :image_url, alt_text = :alt_text, sort_order = :sort_order WHERE id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->bindValue(':image_url', $imageUrl, SQLITE3_TEXT);
                $stmt->bindValue(':alt_text', $altText, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = '展览图片已成功更新！';
                } else {
                    $message = '更新展览图片失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'delete_image') {
        // 删除展览图片
        $id = intval($_POST['image_id']);
        
        try {
            $stmt = $db->prepare("DELETE FROM gallery_images WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $message = '展览图片已成功删除！';
            } else {
                $message = '删除展览图片失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '删除失败：' . $e->getMessage();
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    }
}
?>

<div class="card" id="gallery-form">
    <h2 class="card-title">
        <i class="fas fa-images"></i> 精选展览管理
    </h2>
    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- 添加展览图片表单 -->
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_image">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <h3>添加新图片</h3>
        <div class="form-group">
            <label class="form-label" for="image_url">图片URL</label>
            <input 
                type="text" 
                id="image_url" 
                name="image_url" 
                class="form-input" 
                placeholder="请输入图片URL，支持本地上传或远程URL"
                required>
            <small>可以是本地上传的图片URL，也可以是远程图片URL</small>
        </div>
        <div class="form-group">
            <label class="form-label" for="alt_text">替代文本</label>
            <input 
                type="text" 
                id="alt_text" 
                name="alt_text" 
                class="form-input" 
                placeholder="请输入图片的替代文本"
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
            <i class="fas fa-plus"></i> 添加图片
        </button>
    </form>
    
    <!-- 展览图片列表 -->
    <h3>现有图片</h3>
    <?php if (empty($galleryImages)): ?>
        <p>暂无展览图片，请添加。</p>
    <?php else: ?>
        <?php foreach ($galleryImages as $index => $image): ?>
        <div class="card mb-3">
            <form method="post">
                <input type="hidden" name="action" value="update_image">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label class="form-label">图片URL</label>
                    <input 
                        type="text" 
                        name="image_url" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($image['image_url']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">替代文本</label>
                    <input 
                        type="text" 
                        name="alt_text" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($image['alt_text']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        class="form-input" 
                        value="<?php echo $image['sort_order']; ?>"
                        min="0">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> 保存
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteImage(<?php echo $image['id']; ?>)">
                        <i class="fas fa-trash"></i> 删除
                    </button>
                </div>
            </form>
            <div class="preview mt-3">
                <p>图片预览:</p>
                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function deleteImage(imageId) {
    if (confirm('确定要删除这张图片吗？')) {
        // 创建一个隐藏的表单并提交
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_image';
        form.appendChild(actionInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'image_id';
        idInput.value = imageId;
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
require_once 'footer.php';
?>