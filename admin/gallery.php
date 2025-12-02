<?php
require_once 'header.php';

// 获取展览设置
$gallerySettings = [];
try {
    // 检查 gallery_settings 表是否存在，如果不存在则创建
    $checkTable = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='gallery_settings'");
    if (!$checkTable->fetchArray(SQLITE3_ASSOC)) {
        // 创建 gallery_settings 表
        $db->exec("CREATE TABLE gallery_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            use_local_images BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
    
    $settingsResult = $db->query("SELECT * FROM gallery_settings ORDER BY id DESC LIMIT 1");
    $gallerySettings = $settingsResult->fetchArray(SQLITE3_ASSOC);
    
    // 如果没有设置记录，创建默认设置
    if (!$gallerySettings) {
        $gallerySettings = [
            'id' => 0,
            'use_local_images' => 0  // 默认不使用本地图片
        ];
    }
} catch (Exception $e) {
    $message = '获取展览设置时出错：' . $e->getMessage();
}

// 分页相关变量
$itemsPerPage = 8; // 每页显示8张图片
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

// 处理表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_settings') {
        // 更新展览设置
        $useLocalImages = isset($_POST['use_local_images']) ? 1 : 0;
        
        try {
            if ($gallerySettings['id'] > 0) {
                // 更新现有设置
                $stmt = $db->prepare("UPDATE gallery_settings SET use_local_images = :use_local_images WHERE id = :id");
                $stmt->bindValue(':id', $gallerySettings['id'], SQLITE3_INTEGER);
            } else {
                // 插入新设置
                $stmt = $db->prepare("INSERT INTO gallery_settings (use_local_images) VALUES (:use_local_images)");
            }
            
            $stmt->bindValue(':use_local_images', $useLocalImages, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $message = '展览设置已成功更新！';
                // 重新加载设置
                $settingsResult = $db->query("SELECT * FROM gallery_settings ORDER BY id DESC LIMIT 1");
                $gallerySettings = $settingsResult->fetchArray(SQLITE3_ASSOC);
            } else {
                $message = '更新展览设置失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '更新失败：' . $e->getMessage();
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'add_image') {
        // 添加展览图片（仅在未启用本地图片时可用）
        if ($gallerySettings['use_local_images'] == 1) {
            $message = '已启用本地图片模式，无法添加新的图片URL。请关闭本地图片模式后再添加。';
        } else {
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
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'update_image') {
        // 更新展览图片（仅在未启用本地图片时可用）
        if ($gallerySettings['use_local_images'] == 1) {
            $message = '已启用本地图片模式，无法编辑图片URL。请关闭本地图片模式后再编辑。';
        } else {
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
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'update_local_image_sort') {
        // 更新本地图片排序（仅在启用本地图片时可用）
        if ($gallerySettings['use_local_images'] == 1) {
            $filename = $_POST['filename'] ?? '';
            $sortOrder = intval($_POST['sort_order']);
            
            if (!empty($filename)) {
                // 创建或更新本地图片排序表
                $db->exec("CREATE TABLE IF NOT EXISTS local_image_sort (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    filename TEXT UNIQUE NOT NULL,
                    sort_order INTEGER DEFAULT 0,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
                
                try {
                    $stmt = $db->prepare("INSERT OR REPLACE INTO local_image_sort (filename, sort_order) VALUES (:filename, :sort_order)");
                    $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
                    $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                    
                    if ($stmt->execute()) {
                        $message = '本地图片排序已更新！';
                    } else {
                        $message = '更新本地图片排序失败，请重试。';
                    }
                } catch (Exception $e) {
                    $message = '更新失败：' . $e->getMessage();
                }
            } else {
                $message = '无效的文件名。';
            }
        } else {
            $message = '未启用本地图片模式。';
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    } elseif ($_POST['action'] === 'delete_image') {
        // 删除展览图片
        if ($gallerySettings['use_local_images'] == 1) {
            // 删除本地图片
            $filename = $_POST['filename'] ?? '';
            if (!empty($filename)) {
                $filepath = '../assets/img/' . $filename;
                if (file_exists($filepath) && is_file($filepath)) {
                    if (unlink($filepath)) {
                        // 同时删除排序记录
                        $db->exec("DELETE FROM local_image_sort WHERE filename = '" . SQLite3::escapeString($filename) . "'");
                        $message = '本地图片已成功删除！';
                    } else {
                        $message = '删除本地图片失败，请重试。';
                    }
                } else {
                    $message = '图片文件不存在。';
                }
            } else {
                $message = '无效的文件名。';
            }
        } else {
            // 删除数据库中的图片
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
        }
        
        // 添加JavaScript以滚动到精选展览管理部分
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                window.location.hash = "gallery-form";
            });
        </script>';
    }
}

// 获取展览图片列表（分页）
$galleryImages = [];
$totalImages = 0;

try {
    // 获取展览图片总数
    $countResult = $db->query("SELECT COUNT(*) as count FROM gallery_images");
    $countRow = $countResult->fetchArray(SQLITE3_ASSOC);
    $totalImages = $countRow['count'];
    
    // 获取当前页的展览图片
    $imagesResult = $db->query("SELECT * FROM gallery_images ORDER BY sort_order ASC, id ASC LIMIT $itemsPerPage OFFSET $offset");
    while ($row = $imagesResult->fetchArray(SQLITE3_ASSOC)) {
        $galleryImages[] = $row;
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 计算总页数
$totalPages = ceil($totalImages / $itemsPerPage);
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
    
    <!-- 展览设置 -->
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="update_settings">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <h3>展览设置</h3>
        <div class="form-group">
            <label class="form-checkbox">
                <input type="checkbox" name="use_local_images" <?php echo $gallerySettings['use_local_images'] == 1 ? 'checked' : ''; ?>>
                <span class="checkmark"></span>
                使用本地图片（启用后将展示 assets/img/ 目录下的所有图片）
            </label>
            <small>启用此选项后，将忽略下面添加的图片URL，改为展示服务器本地图片</small>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> 保存设置
        </button>
    </form>
    
    <!-- 添加展览图片表单 -->
    <form method="post" class="mb-4" <?php echo $gallerySettings['use_local_images'] == 1 ? 'style="display:none;"' : ''; ?>>
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
                placeholder="请输入图片的替代文本，应描述图片内容"
                required>
            <small>请输入描述性的替代文本，有助于搜索引擎理解图片内容并提高可访问性</small>
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
    <?php if ($gallerySettings['use_local_images'] == 1): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 已启用本地图片模式，将展示 assets/img/ 目录下的所有图片。
        </div>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 拖拽图片调整顺序，然后点击"保存排序"按钮保存更改。
        </div>
        
        <?php 
        // 获取本地图片列表用于显示和删除
        $localImages = [];
        $imgDir = '../assets/img/';
        
        // 确保local_image_sort表存在
        $db->exec("CREATE TABLE IF NOT EXISTS local_image_sort (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT UNIQUE NOT NULL,
            sort_order INTEGER DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // 获取所有本地图片
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                // 跳过目录和待审核目录
                if ($file === '.' || $file === '..' || $file === 'pending') {
                    continue;
                }
                
                // 只处理.png和.jpg文件
                if (preg_match('/\.(png|jpg|jpeg)$/i', $file)) {
                    // 获取排序信息
                    $sortOrder = 0;
                    $sortResult = $db->query("SELECT sort_order FROM local_image_sort WHERE filename = '" . SQLite3::escapeString($file) . "'");
                    if ($sortResult && $sortRow = $sortResult->fetchArray(SQLITE3_ASSOC)) {
                        $sortOrder = $sortRow['sort_order'];
                    }
                    
                    $localImages[] = [
                        'filename' => $file,
                        'filepath' => '../assets/img/' . $file,  // 修复路径，相对于admin目录
                        'alt_text' => pathinfo($file, PATHINFO_FILENAME),
                        'sort_order' => $sortOrder
                    ];
                }
            }
            
            // 按排序字段排序
            usort($localImages, function($a, $b) {
                if ($a['sort_order'] == $b['sort_order']) {
                    return 0;
                }
                return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
            });
        }
        
        // 计算本地图片分页
        $totalLocalImages = count($localImages);
        $totalLocalPages = ceil($totalLocalImages / $itemsPerPage);
        $localImagesPage = max(1, min($page, $totalLocalPages));
        $localOffset = ($localImagesPage - 1) * $itemsPerPage;
        $paginatedLocalImages = array_slice($localImages, $localOffset, $itemsPerPage);
        ?>
        
        <?php if (empty($localImages)): ?>
            <p>暂无本地图片。</p>
        <?php else: ?>
            <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px; min-height: 500px;">
                <?php foreach ($paginatedLocalImages as $index => $image): ?>
                <div class="gallery-item" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff;">
                    <div class="preview" style="height: 180px; display: flex; align-items: center; justify-content: center;">
                        <img src="<?php echo htmlspecialchars($image['filepath']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;">
                    </div>
                    <div style="padding: 10px;">
                        <div style="margin-bottom: 10px;">
                            <small style="display: block; color: #666; margin-bottom: 5px;">文件名:</small>
                            <div style="font-size: 12px; word-break: break-all;"><?php echo htmlspecialchars($image['filename']); ?></div>
                        </div>
                        <div style="display: flex; justify-content: center;">
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="action" value="delete_image">
                                <input type="hidden" name="filename" value="<?php echo htmlspecialchars($image['filename']); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('确定要删除这张图片吗？此操作不可恢复！')">
                                    <i class="fas fa-trash"></i> 删除
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- 添加占位符以保持固定高度 -->
                <?php 
                $placeholdersNeeded = $itemsPerPage - count($paginatedLocalImages);
                for ($i = 0; $i < $placeholdersNeeded; $i++): ?>
                <div class="gallery-item placeholder" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; visibility: hidden;">
                    <div class="preview" style="height: 180px;"></div>
                    <div style="padding: 10px; height: 80px;"></div>
                </div>
                <?php endfor; ?>
            </div>
            
            <!-- 本地图片分页控件 -->
            <?php if ($totalLocalPages > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; margin-top: 20px; gap: 5px;">
                <?php if ($localImagesPage > 1): ?>
                    <a href="?page=<?php echo $localImagesPage - 1; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;">
                        <i class="fas fa-chevron-left"></i> 上一页
                    </a>
                <?php endif; ?>
                
                <?php 
                // 显示页码
                $startPage = max(1, $localImagesPage - 2);
                $endPage = min($totalLocalPages, $localImagesPage + 2);
                
                if ($startPage > 1): ?>
                    <a href="?page=1" class="btn btn-primary btn-sm" style="padding: 5px 10px;">1</a>
                    <?php if ($startPage > 2): ?>
                        <span style="padding: 5px 10px;">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i == $localImagesPage): ?>
                        <span class="btn btn-primary btn-sm" style="padding: 5px 10px; background: #4361ee;"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalLocalPages): ?>
                    <?php if ($endPage < $totalLocalPages - 1): ?>
                        <span style="padding: 5px 10px;">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $totalLocalPages; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;"><?php echo $totalLocalPages; ?></a>
                <?php endif; ?>
                
                <?php if ($localImagesPage < $totalLocalPages): ?>
                    <a href="?page=<?php echo $localImagesPage + 1; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;">
                        下一页 <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (empty($galleryImages)): ?>
        <?php if ($gallerySettings['use_local_images'] != 1): ?>
            <p>暂无展览图片，请添加。</p>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($gallerySettings['use_local_images'] != 1): ?>
            <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px; min-height: 500px;">
                <?php foreach ($galleryImages as $index => $image): ?>
                <div class="gallery-item" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff;">
                    <div class="preview" style="height: 180px; display: flex; align-items: center; justify-content: center;">
                        <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;">
                    </div>
                    <div style="padding: 10px;">
                        <div style="margin-bottom: 10px;">
                            <small style="display: block; color: #666; margin-bottom: 5px;">替代文本:</small>
                            <input type="text" name="alt_text" value="<?php echo htmlspecialchars($image['alt_text']); ?>" style="width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;" placeholder="描述图片内容">
                        </div>
                        <div style="margin-bottom: 10px;">
                            <small style="display: block; color: #666; margin-bottom: 5px;">排序:</small>
                            <input type="number" name="sort_order" value="<?php echo $image['sort_order']; ?>" min="0" style="width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                        </div>
                        <div style="display: flex; gap: 5px;">
                            <button type="button" class="btn btn-success btn-sm" onclick="updateImage(<?php echo $image['id']; ?>, this)">
                                <i class="fas fa-save"></i> 保存
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteImage(<?php echo $image['id']; ?>)">
                                <i class="fas fa-trash"></i> 删除
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- 添加占位符以保持固定高度 -->
                <?php 
                $placeholdersNeeded = $itemsPerPage - count($galleryImages);
                for ($i = 0; $i < $placeholdersNeeded; $i++): ?>
                <div class="gallery-item placeholder" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; visibility: hidden;">
                    <div class="preview" style="height: 180px;"></div>
                    <div style="padding: 10px; height: 160px;"></div>
                </div>
                <?php endfor; ?>
            </div>
            
            <!-- 分页控件 -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; margin-top: 20px; gap: 5px;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;">
                        <i class="fas fa-chevron-left"></i> 上一页
                    </a>
                <?php endif; ?>
                
                <?php 
                // 显示页码
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1): ?>
                    <a href="?page=1" class="btn btn-primary btn-sm" style="padding: 5px 10px;">1</a>
                    <?php if ($startPage > 2): ?>
                        <span style="padding: 5px 10px;">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="btn btn-primary btn-sm" style="padding: 5px 10px; background: #4361ee;"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span style="padding: 5px 10px;">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $totalPages; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;"><?php echo $totalPages; ?></a>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px;">
                        下一页 <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// 拖拽排序功能
document.addEventListener('DOMContentLoaded', function() {
    const sortableContainer = document.getElementById('sortable-images');
    if (sortableContainer) {
        let draggedItem = null;
        
        // 为每个可排序项添加事件监听器
        const items = sortableContainer.querySelectorAll('.sortable-item');
        items.forEach(item => {
            item.addEventListener('dragstart', function(e) {
                draggedItem = this;
                setTimeout(() => {
                    this.style.opacity = '0.5';
                    this.style.transform = 'scale(0.95)';
                    this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
                }, 0);
            });
            
            item.addEventListener('dragend', function() {
                setTimeout(() => {
                    this.style.opacity = '1';
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                    draggedItem = null;
                }, 0);
            });
            
            item.addEventListener('dragover', function(e) {
                e.preventDefault();
            });
            
            item.addEventListener('dragenter', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '#e1f5fe';
                this.style.transform = 'scale(1.05)';
            });
            
            item.addEventListener('dragleave', function() {
                this.style.backgroundColor = '#f9f9f9';
                this.style.transform = 'scale(1)';
            });
            
            item.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '#f9f9f9';
                this.style.transform = 'scale(1)';
                if (draggedItem !== this) {
                    const allItems = Array.from(sortableContainer.querySelectorAll('.sortable-item'));
                    const thisIndex = allItems.indexOf(this);
                    const draggedIndex = allItems.indexOf(draggedItem);
                    
                    if (draggedIndex < thisIndex) {
                        sortableContainer.insertBefore(draggedItem, this.nextSibling);
                    } else {
                        sortableContainer.insertBefore(draggedItem, this);
                    }
                }
            });
        });
        
        // 保存排序
        document.getElementById('save-sort').addEventListener('click', function() {
            const items = sortableContainer.querySelectorAll('.sortable-item');
            const sortOrder = [];
            
            items.forEach((item, index) => {
                sortOrder.push({
                    filename: item.getAttribute('data-filename'),
                    sort_order: index
                });
            });
            
            // 发送排序数据到服务器
            fetch('save_local_image_sort.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(sortOrder)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('排序已保存！');
                } else {
                    alert('保存排序失败：' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('保存排序时发生错误');
            });
        });
    }
});

function updateImage(imageId, button) {
    // 获取当前图片的表单数据
    const item = button.closest('.gallery-item');
    const imageUrl = item.querySelector('img').src;
    const altText = item.querySelector('input[name="alt_text"]').value;
    const sortOrder = item.querySelector('input[name="sort_order"]').value;
    
    // 创建一个隐藏的表单并提交
    var form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    var actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'update_image';
    form.appendChild(actionInput);
    
    var idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'image_id';
    idInput.value = imageId;
    form.appendChild(idInput);
    
    var urlInput = document.createElement('input');
    urlInput.type = 'hidden';
    urlInput.name = 'image_url';
    urlInput.value = imageUrl;
    form.appendChild(urlInput);
    
    var altInput = document.createElement('input');
    altInput.type = 'hidden';
    altInput.name = 'alt_text';
    altInput.value = altText;
    form.appendChild(altInput);
    
    var sortInput = document.createElement('input');
    sortInput.type = 'hidden';
    sortInput.name = 'sort_order';
    sortInput.value = sortOrder;
    form.appendChild(sortInput);
    
    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo htmlspecialchars($csrfToken); ?>';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
}

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