<?php
require_once 'header.php';

// 检查管理员权限
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// 处理审核操作
if (isset($_POST['action']) && isset($_POST['image_id'])) {
    $imageId = intval($_POST['image_id']);
    
    // 获取图片信息
    $stmt = $db->prepare("SELECT filename FROM uploaded_images WHERE id = :id AND status = 'pending'");
    $stmt->bindValue(':id', $imageId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $image = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($image) {
        if ($_POST['action'] === 'approve') {
            // 批准图片 - 将图片从pending目录移动到主目录
            $pendingPath = '../assets/img/pending/' . $image['filename'];
            $approvedPath = '../assets/img/' . $image['filename'];
            
            if (file_exists($pendingPath)) {
                // 移动文件到主目录
                rename($pendingPath, $approvedPath);
                
                // 更新数据库状态
                $stmt = $db->prepare("UPDATE uploaded_images SET status = 'approved', reviewed_by = :admin_id, reviewed_at = datetime('now') WHERE id = :id");
                $stmt->bindValue(':admin_id', $_SESSION['admin_id'], SQLITE3_INTEGER);
                $stmt->bindValue(':id', $imageId, SQLITE3_INTEGER);
                $stmt->execute();
                
                $message = '图片已批准并移动到展示目录: ' . htmlspecialchars($image['filename']);
                $messageType = 'success';
            } else {
                $message = '图片文件不存在: ' . htmlspecialchars($image['filename']);
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'reject') {
            // 拒绝图片 - 删除待审核目录中的文件并更新数据库
            $imagePath = '../assets/img/pending/' . $image['filename'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            $stmt = $db->prepare("UPDATE uploaded_images SET status = 'rejected', reviewed_by = :admin_id, reviewed_at = datetime('now') WHERE id = :id");
            $stmt->bindValue(':admin_id', $_SESSION['admin_id'], SQLITE3_INTEGER);
            $stmt->bindValue(':id', $imageId, SQLITE3_INTEGER);
            $stmt->execute();
            
            $message = '图片已拒绝并删除: ' . htmlspecialchars($image['filename']);
            $messageType = 'success';
        }
    } else {
        $message = '图片不存在或已被处理';
        $messageType = 'error';
    }
}

// 获取待审核的图片列表
$pendingImages = [];
$tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='uploaded_images'");
if ($tableCheck->fetchArray(SQLITE3_ASSOC)) {
    $stmt = $db->prepare("SELECT * FROM uploaded_images WHERE status = 'pending' ORDER BY upload_time DESC");
    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // 检查文件是否存在
        $filePath = '../assets/img/pending/' . $row['filename'];
        if (file_exists($filePath)) {
            $row['file_size'] = filesize($filePath);
            $row['modified'] = filemtime($filePath);
            $pendingImages[] = $row;
        }
    }
}
?>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-image"></i> 图片审核
    </h2>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo $messageType ?? 'info'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($pendingImages)): ?>
        <div class="no-data">
            <i class="fas fa-image" style="font-size: 48px; margin-bottom: 15px; color: #666;"></i>
            <p>暂无待审核的图片</p>
        </div>
    <?php else: ?>
        <div class="image-grid">
            <?php foreach ($pendingImages as $image): ?>
            <div class="image-card">
                <div class="image-preview">
                    <img src="<?php echo htmlspecialchars('../assets/img/pending/' . $image['filename']); ?>" alt="<?php echo htmlspecialchars($image['original_name']); ?>">
                </div>
                <div class="image-info">
                    <div class="image-name"><?php echo htmlspecialchars($image['original_name']); ?></div>
                    <div class="image-size"><?php echo round($image['file_size'] / 1024, 2); ?> KB</div>
                    <div class="image-date"><?php echo date('Y-m-d H:i:s', strtotime($image['upload_time'])); ?></div>
                    <div class="image-ip">上传IP: <?php echo htmlspecialchars($image['uploader_ip']); ?></div>
                </div>
                <div class="image-actions">
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-success btn-small">
                            <i class="fas fa-check"></i> 批准
                        </button>
                    </form>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-small" onclick="return confirm('确定要拒绝并删除这张图片吗？')">
                            <i class="fas fa-times"></i> 拒绝
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.image-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: transform 0.3s ease;
}

.image-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}

.image-preview {
    height: 200px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.2);
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}

.image-info {
    padding: 15px;
}

.image-name {
    font-weight: bold;
    margin-bottom: 5px;
    word-break: break-all;
}

.image-size, .image-date {
    font-size: 12px;
    color: #aaa;
    margin-bottom: 3px;
}

.image-actions {
    padding: 0 15px 15px;
    display: flex;
    gap: 10px;
}

.btn-small {
    padding: 8px 12px;
    font-size: 12px;
}

.no-data {
    text-align: center;
    padding: 50px 20px;
    color: #999;
}

.no-data p {
    margin: 10px 0 0;
}
</style>

<?php
require_once 'footer.php';
?>










