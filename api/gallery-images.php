<?php
// 展览图片列表获取API端点

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 连接到SQLite数据库
try {
    $db = new SQLite3('../sql/settings.db');
    
    // 检查是否启用了本地图片模式
    $settingsResult = $db->query("SELECT use_local_images FROM gallery_settings ORDER BY id DESC LIMIT 1");
    $useLocalImages = 0; // 默认不使用本地图片
    
    if ($settingsRow = $settingsResult->fetchArray(SQLITE3_ASSOC)) {
        $useLocalImages = $settingsRow['use_local_images'];
    }
    
    if ($useLocalImages == 1) {
        // 使用本地图片模式 - 扫描 assets/img 目录
        $images = [];
        $imgDir = '../assets/img/';
        $pendingDir = '../assets/img/pending/';
        
        // 先添加已审核通过的图片 (直接放在 assets/img 目录下的图片)
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                // 跳过目录和待审核目录
                if ($file === '.' || $file === '..' || $file === 'pending') {
                    continue;
                }
                
                // 只处理.png和.jpg文件
                if (preg_match('/\.(png|jpg|jpeg)$/i', $file)) {
                    // 为本地图片创建更具描述性的alt文本
                    $altText = pathinfo($file, PATHINFO_FILENAME);
                    // 如果文件名看起来像哈希值，添加通用描述
                    if (preg_match('/^[a-f0-9]{32}$/', $altText) || preg_match('/^img_[a-f0-9]+/', $altText)) {
                        $altText = "Minecraft游戏截图";
                    } else {
                        // 否则将其转换为更自然的语言
                        $altText = str_replace(['-', '_'], ' ', $altText);
                        $altText = ucwords($altText);
                    }
                    
                    $images[] = [
                        'filename' => $file,
                        'image_url' => 'assets/img/' . $file,
                        'alt_text' => $altText
                    ];
                }
            }
        }
        
        // 再添加已审核通过的图片 (从数据库中标记为已批准的图片)
        $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='uploaded_images'");
        if ($tableCheck->fetchArray(SQLITE3_ASSOC)) {
            // 获取已批准的图片
            $stmt = $db->prepare("SELECT filename FROM uploaded_images WHERE status = 'approved' ORDER BY upload_time DESC");
            $result = $stmt->execute();
            
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $filePath = $pendingDir . $row['filename'];
                if (file_exists($filePath)) {
                    // 为上传的图片创建更具描述性的alt文本
                    $altText = pathinfo($row['filename'], PATHINFO_FILENAME);
                    // 如果文件名看起来像哈希值，添加通用描述
                    if (preg_match('/^[a-f0-9]{32}$/', $altText) || preg_match('/^img_[a-f0-9]+/', $altText)) {
                        $altText = "Minecraft游戏截图";
                    } else {
                        // 否则将其转换为更自然的语言
                        $altText = str_replace(['-', '_'], ' ', $altText);
                        $altText = ucwords($altText);
                    }
                    
                    $images[] = [
                        'filename' => $row['filename'],
                        'image_url' => 'assets/img/pending/' . $row['filename'],
                        'alt_text' => $altText
                    ];
                }
            }
        }
        
        // 获取排序信息
        $sortTableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='local_image_sort'");
        if ($sortTableCheck->fetchArray(SQLITE3_ASSOC)) {
            // 为每个图片添加排序信息
            foreach ($images as &$image) {
                $sortResult = $db->query("SELECT sort_order FROM local_image_sort WHERE filename = '" . SQLite3::escapeString($image['filename']) . "'");
                if ($sortRow = $sortResult->fetchArray(SQLITE3_ASSOC)) {
                    $image['sort_order'] = $sortRow['sort_order'];
                } else {
                    $image['sort_order'] = 0;
                }
            }
            
            // 按排序字段排序
            usort($images, function($a, $b) {
                if ($a['sort_order'] == $b['sort_order']) {
                    return 0;
                }
                return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
            });
        }
        
        // 移除filename和sort_order字段，只保留前端需要的字段
        foreach ($images as &$image) {
            unset($image['filename']);
            unset($image['sort_order']);
        }
        
        echo json_encode(['images' => $images]);
    } else {
        // 使用数据库中的图片
        $result = $db->query("SELECT image_url, alt_text FROM gallery_images ORDER BY sort_order ASC, id ASC");
        $images = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $images[] = $row;
        }
        
        echo json_encode(['images' => $images]);
    }
    
    $db->close();
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>