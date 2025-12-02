<?php
// 图片上传页面

// 连接到数据库
try {
    // 确保sql目录存在
    if (!is_dir('sql')) {
        mkdir('sql', 0777, true);
    }
    
    $db = new SQLite3('sql/settings.db');
    
    // 确保banned_ips表存在
    $db->exec("CREATE TABLE IF NOT EXISTS banned_ips (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address TEXT NOT NULL UNIQUE,
        reason TEXT,
        banned_by INTEGER,
        banned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NULL
    )");
} catch (Exception $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 检查IP是否被封禁
$userIp = $_SERVER['REMOTE_ADDR'];
$stmt = $db->prepare("SELECT * FROM banned_ips WHERE ip_address = :ip AND (expires_at IS NULL OR expires_at > datetime('now'))");
$stmt->bindValue(':ip', $userIp, SQLITE3_TEXT);
$result = $stmt->execute();
$bannedIp = $result->fetchArray(SQLITE3_ASSOC);

if ($bannedIp) {
    http_response_code(403);
    die("您的IP地址已被封禁，原因: " . htmlspecialchars($bannedIp['reason'] ?? '未指定') . "。如有疑问请联系管理员。");
}

// 确保uploaded_images表存在
try {
    $db->exec("CREATE TABLE IF NOT EXISTS uploaded_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT NOT NULL,
        original_name TEXT NOT NULL,
        file_size INTEGER NOT NULL,
        upload_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        status TEXT DEFAULT 'pending', -- pending, approved, rejected
        uploader_ip TEXT,
        reviewed_by INTEGER,
        reviewed_at DATETIME
    )");
} catch (Exception $e) {
    // 表可能已经存在，忽略错误
}

// 限制上传频率 - 每小时最多上传10张图片
// 从数据库获取用户上传记录
$oneHourAgo = time() - 3600;
$stmt = $db->prepare("SELECT COUNT(*) as count FROM uploaded_images WHERE uploader_ip = :ip AND upload_time > datetime(:time, 'unixepoch')");
$stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
$stmt->bindValue(':time', $oneHourAgo, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$upload_count = $row['count'];
$upload_limit = 10; // 每小时限制10张

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 再次检查IP是否被封禁（防止在页面加载和提交之间的窗口期）
    $stmt = $db->prepare("SELECT * FROM banned_ips WHERE ip_address = :ip AND (expires_at IS NULL OR expires_at > datetime('now'))");
    $stmt->bindValue(':ip', $userIp, SQLITE3_TEXT);
    $result = $stmt->execute();
    $bannedIp = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($bannedIp) {
        $message = "您的IP地址已被封禁，原因: " . htmlspecialchars($bannedIp['reason'] ?? '未指定');
        $message_type = 'error';
    } else {
        // 检查上传频率限制
        if ($upload_count >= $upload_limit) {
            $message = '上传频率过高，请稍后再试。每小时最多上传' . $upload_limit . '张图片。';
            $message_type = 'error';
        } else if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            // 处理批量上传
            $files = $_FILES['images'];
            $uploaded_count = 0;
            $total_files = count($files['name']);
            $max_file_size = 15 * 1024 * 1024; // 15MB限制
            
            // 计算还能上传多少张图片
            $remaining_uploads = $upload_limit - $upload_count;
            
            if ($total_files > $remaining_uploads) {
                $message = '超出上传限制。您还能上传 ' . $remaining_uploads . ' 张图片。';
                $message_type = 'error';
            } else {
                $success_files = [];
                $error_files = [];
                
                for ($i = 0; $i < $total_files; $i++) {
                    // 检查是否有上传错误
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                        $error_files[] = $files['name'][$i] . ' (上传错误)';
                        continue;
                    }
                    
                    // 创建单个文件数组用于处理
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    // 验证文件类型 - 使用替代方法检查MIME类型
                    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                    $file_type = '';
                    
                    // 方法1: 使用fileinfo扩展（如果可用）
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $file_type = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                    } 
                    // 方法2: 使用getimagesize函数检查（备用方法）
                    else if (function_exists('getimagesize')) {
                        $image_info = getimagesize($file['tmp_name']);
                        if ($image_info !== false) {
                            $file_type = $image_info['mime'];
                        }
                    } 
                    // 方法3: 根据文件扩展名猜测（最后的备用方法）
                    else {
                        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        switch ($extension) {
                            case 'jpg':
                            case 'jpeg':
                                $file_type = 'image/jpeg';
                                break;
                            case 'png':
                                $file_type = 'image/png';
                                break;
                            default:
                                $file_type = 'unknown';
                                break;
                        }
                    }
                    
                    if (!in_array($file_type, $allowed_types)) {
                        $error_files[] = $file['name'] . ' (文件类型不支持)';
                    } else if ($file['size'] > $max_file_size) { // 15MB限制
                        $error_files[] = $file['name'] . ' (文件大小超过15MB)';
                    } else {
                        // 生成唯一文件名
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = uniqid('img_', true) . '.' . $extension;
                        $upload_path = 'assets/img/pending/' . $filename;
                        
                        // 移动文件到目标目录
                        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                            // 记录上传信息到数据库
                            $stmt = $db->prepare("INSERT INTO uploaded_images (filename, original_name, file_size, uploader_ip, status) VALUES (:filename, :original_name, :file_size, :uploader_ip, 'pending')");
                            $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
                            $stmt->bindValue(':original_name', $file['name'], SQLITE3_TEXT);
                            $stmt->bindValue(':file_size', $file['size'], SQLITE3_INTEGER);
                            $stmt->bindValue(':uploader_ip', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
                            $stmt->execute();
                            
                            $success_files[] = $file['name'];
                            $uploaded_count++;
                        } else {
                            $error_files[] = $file['name'] . ' (上传失败)';
                        }
                    }
                }
                
                // 构建结果消息
                if (!empty($success_files)) {
                    $message = '成功上传 ' . count($success_files) . ' 张图片';
                    if (!empty($error_files)) {
                        $message .= '，' . count($error_files) . ' 张图片上传失败';
                    }
                    $message .= '，等待管理员审核。';
                    $message_type = 'success';
                } else if (!empty($error_files)) {
                    $message = '所有图片上传失败: ' . implode(', ', $error_files);
                    $message_type = 'error';
                } else {
                    $message = '没有图片被上传。';
                    $message_type = 'error';
                }
            }
        } else {
            $message = '请选择要上传的图片。';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>图片上传 - 原始大陆</title>
    <meta name='description' content='向原始大陆Minecraft服务器上传您的游戏截图和创作作品。支持JPG和PNG格式，每小时最多可上传10张图片。上传的图片将经过管理员审核后展示在网站上。'>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/upload_image.php">
    <meta property="og:title" content="图片上传 - 原始大陆">
    <meta property="og:description" content="向原始大陆Minecraft服务器上传您的游戏截图和创作作品。支持JPG和PNG格式，每小时最多可上传10张图片。上传的图片将经过管理员审核后展示在网站上。">
    <meta property="og:image" content="https://p.qlogo.cn/gh/1046193413/1046193413/640/">
    <meta property="og:image:alt" content="原始大陆服务器上传页面">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/upload_image.php">
    <meta property="twitter:title" content="图片上传 - 原始大陆">
    <meta property="twitter:description" content="向原始大陆Minecraft服务器上传您的游戏截图和创作作品。支持JPG和PNG格式，每小时最多可上传10张图片。上传的图片将经过管理员审核后展示在网站上。">
    <meta property="twitter:image" content="https://p.qlogo.cn/gh/1046193413/1046193413/640/">
    <meta property="twitter:image:alt" content="原始大陆服务器上传页面">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: #0a1525;
            color: white;
            font-family: 'Microsoft YaHei', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .card {
            background: rgba(15, 30, 45, 0.8);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card-title {
            font-size: 28px;
            margin-bottom: 20px;
            color: #ffbe50;
            text-align: center;
        }
        
        .upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #ffbe50;
            background: rgba(255, 190, 80, 0.1);
        }
        
        .upload-area i {
            font-size: 48px;
            color: #ffbe50;
            margin-bottom: 15px;
        }
        
        .upload-area p {
            margin: 10px 0;
            color: #ddd;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 28px;
            background: #51a865;
            color: white;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.4s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            background: #25783c;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        
        .success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid #2ecc71;
            color: #2ecc71;
        }
        
        .error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #e74c3c;
        }
        
        .file-input {
            display: none;
        }
        
        .file-name {
            margin: 10px 0;
            font-size: 14px;
            color: #aaa;
        }
        
        .rules {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .rules h3 {
            color: #ffbe50;
            margin-top: 0;
        }
        
        .rules ul {
            padding-left: 20px;
        }
        
        .rules li {
            margin: 10px 0;
            color: #ddd;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #ffbe50;
            text-decoration: none;
        }
        
        .back-link i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 class="card-title">
                <i class="fas fa-cloud-upload-alt"></i> 图片上传
            </h1>
            
            <div style="background: rgba(52, 152, 219, 0.2); border: 1px solid #3498db; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                <p style="margin: 0; color: #3498db;">
                    <i class="fas fa-info-circle"></i> <strong>说明：</strong>这是一个公共上传页面，任何人都可以上传图片。上传的图片需要经过管理员审核后才会显示在网站上。
                </p>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($upload_count >= $upload_limit): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i> 
                    您已达到本小时上传限制 (<?php echo $upload_limit; ?> 张图片)，请稍后再试。
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" id="upload-form">
                <div class="upload-area" id="upload-area">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>点击选择图片或拖拽图片到此处</p>
                    <p>支持 JPG、PNG 格式，单张图片不超过 15MB</p>
                    <input type="file" name="images[]" id="file-input" class="file-input" accept="image/jpeg, image/png, image/jpg" multiple required>
                </div>
                
                <div class="file-name" id="file-name">未选择文件</div>
                
                <div style="text-align: center;">
                    <button type="submit" class="btn" id="upload-btn" <?php echo ($upload_count >= $upload_limit) ? 'disabled' : ''; ?>>
                        <i class="fas fa-upload"></i> 上传图片
                    </button>
                </div>
            </form>
            
            <div class="rules">
                <h3><i class="fas fa-info-circle"></i> 上传规则</h3>
                <ul>
                    <li>只允许上传 JPG 和 PNG 格式的图片</li>
                    <li>单张图片大小不能超过 15MB</li>
                    <li>每小时最多上传 <?php echo $upload_limit; ?> 张图片</li>
                    <li>支持批量上传多个图片（按住 Ctrl 或 Shift 键选择多个文件）</li>
                    <li>上传的图片需要管理员审核后才会显示在首页</li>
                    <li>禁止上传违法、色情、暴力等相关内容</li>
                    <li>恶意上传将导致账号被封禁</li>
                </ul>
            </div>
            
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> 返回首页
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('upload-area');
            const fileInput = document.getElementById('file-input');
            const fileName = document.getElementById('file-name');
            const uploadBtn = document.getElementById('upload-btn');
            const form = document.getElementById('upload-form');
            
            // 点击上传区域选择文件
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            // 文件选择后显示文件名
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    if (this.files.length === 1) {
                        fileName.textContent = '已选择: ' + this.files[0].name;
                    } else {
                        fileName.textContent = '已选择 ' + this.files.length + ' 个文件';
                    }
                } else {
                    fileName.textContent = '未选择文件';
                }
            });
            
            // 拖拽上传功能
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = '#ffbe50';
                this.style.backgroundColor = 'rgba(255, 190, 80, 0.2)';
            });
            
            uploadArea.addEventListener('dragleave', function() {
                this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                this.style.backgroundColor = 'transparent';
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                this.style.backgroundColor = 'transparent';
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    if (e.dataTransfer.files.length === 1) {
                        fileName.textContent = '已选择: ' + e.dataTransfer.files[0].name;
                    } else {
                        fileName.textContent = '已选择 ' + e.dataTransfer.files.length + ' 个文件';
                    }
                }
            });
            
            // 表单提交前验证
            form.addEventListener('submit', function(e) {
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('请选择要上传的图片');
                    return false;
                }
                // 如果有文件，则允许表单正常提交
            });
        });
    </script>
</body>
</html>