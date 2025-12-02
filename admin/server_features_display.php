<?php
// 显示服务器特征的组件
function displayServerFeatures($db = null) {
    // 如果没有传入数据库连接，则创建一个新的连接
    $shouldCloseDb = false;
    if ($db === null) {
        $db = new SQLite3('../sql/settings.db');
        $shouldCloseDb = true;
    }
    
    // 获取服务器特点列表
    $serverFeatures = [];
    try {
        $featuresResult = $db->query("SELECT * FROM server_features ORDER BY sort_order ASC, id ASC");
        while ($row = $featuresResult->fetchArray(SQLITE3_ASSOC)) {
            $serverFeatures[] = $row;
        }
    } catch (Exception $e) {
        // 如果出现错误，不显示任何内容
        if ($shouldCloseDb) {
            $db->close();
        }
        return;
    }

    // 如果没有特点，不显示任何内容
    if (empty($serverFeatures)) {
        if ($shouldCloseDb) {
            $db->close();
        }
        return;
    }

    echo '<div class="card">';
    echo '    <h2 class="card-title">';
    echo '        <i class="fas fa-star"></i> 服务器特点预览';
    echo '    </h2>';
    echo '    <div class="features-preview">';
    
    foreach ($serverFeatures as $feature) {
        echo '    <div class="feature-item">';
        echo '        <div class="feature-icon">';
        if (preg_match('/^<svg/', $feature['icon_code'])) {
            // 为SVG图标添加固定大小的包装器
            echo '<div class="svg-icon-wrapper">';
            $svgCode = $feature['icon_code'];
            if (strpos($svgCode, 'style=') === false) {
                $svgCode = str_replace('<svg', '<svg style="width:100%;height:100%"', $svgCode);
            }
            echo $svgCode;
            echo '</div>';
        } elseif (strpos($feature['icon_code'], 'icon-') === 0) {
            // 使用阿里巴巴矢量图标类名
            echo '<i class="iconfont ' . htmlspecialchars($feature['icon_code']) . '"></i>';
        } else {
            // 使用Font Awesome或其他图标类名
            echo '<i class="' . htmlspecialchars($feature['icon_code']) . '"></i>';
        }
        echo '        </div>';
        echo '        <div class="feature-content">';
        echo '            <h3>' . htmlspecialchars($feature['title']) . '</h3>';
        echo '            <p>' . htmlspecialchars($feature['description']) . '</p>';
        echo '        </div>';
        echo '    </div>';
    }
    
    echo '    </div>';
    echo '</div>';
    
    // 添加样式
    echo '<style>';
    echo '    .features-preview {';
    echo '        display: grid;';
    echo '        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));';
    echo '        gap: 20px;';
    echo '        margin-top: 20px;';
    echo '    }';
    echo '    .feature-item {';
    echo '        display: flex;';
    echo '        align-items: flex-start;';
    echo '        gap: 15px;';
    echo '        padding: 15px;';
    echo '        background: #f8f9fa;';
    echo '        border-radius: 8px;';
    echo '        border: 1px solid #eee;';
    echo '    }';
    echo '    .feature-icon {';
    echo '        font-size: 24px;';
    echo '        color: #3498db;';
    echo '        min-width: 40px;';
    echo '        text-align: center;';
    echo '    }';
    echo '    .feature-icon .svg-icon-wrapper {';
    echo '        width: 24px;';
    echo '        height: 24px;';
    echo '        display: flex;';
    echo '        align-items: center;';
    echo '        justify-content: center;';
    echo '        margin: 0 auto;';
    echo '    }';
    echo '    .feature-icon .svg-icon-wrapper svg {';
    echo '        width: 100%;';
    echo '        height: 100%;';
    echo '        max-width: 24px;';
    echo '        max-height: 24px;';
    echo '    }';
    echo '    .feature-content h3 {';
    echo '        margin: 0 0 8px 0;';
    echo '        color: #2c3e50;';
    echo '        font-size: 18px;';
    echo '    }';
    echo '    .feature-content p {';
    echo '        margin: 0;';
    echo '        color: #7f8c8d;';
    echo '        font-size: 14px;';
    echo '        line-height: 1.4;';
    echo '    }';
    echo '</style>';
    
    if ($shouldCloseDb) {
        $db->close();
    }
}

// 显示展览图片的组件
function displayGalleryImages($db = null) {
    // 如果没有传入数据库连接，则创建一个新的连接
    $shouldCloseDb = false;
    if ($db === null) {
        $db = new SQLite3('../sql/settings.db');
        $shouldCloseDb = true;
    }
    
    // 获取展览图片列表
    $galleryImages = [];
    try {
        $imagesResult = $db->query("SELECT * FROM gallery_images ORDER BY sort_order ASC, id ASC LIMIT 6");
        while ($row = $imagesResult->fetchArray(SQLITE3_ASSOC)) {
            $galleryImages[] = $row;
        }
    } catch (Exception $e) {
        // 如果出现错误，不显示任何内容
        if ($shouldCloseDb) {
            $db->close();
        }
        return;
    }

    // 如果没有图片，不显示任何内容
    if (empty($galleryImages)) {
        if ($shouldCloseDb) {
            $db->close();
        }
        return;
    }

    echo '<div class="card">';
    echo '    <h2 class="card-title">';
    echo '        <i class="fas fa-images"></i> 精选展览预览';
    echo '    </h2>';
    echo '    <div class="gallery-preview">';
    
    foreach ($galleryImages as $image) {
        echo '    <div class="gallery-item-preview">';
        echo '        <img src="' . htmlspecialchars($image['image_url']) . '" alt="' . htmlspecialchars($image['alt_text']) . '">';
        echo '    </div>';
    }
    
    echo '    </div>';
    echo '</div>';
    
    // 添加样式
    echo '<style>';
    echo '    .gallery-preview {';
    echo '        display: grid;';
    echo '        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));';
    echo '        gap: 10px;';
    echo '        margin-top: 20px;';
    echo '    }';
    echo '    .gallery-item-preview {';
    echo '        width: 100px;';
    echo '        height: 100px;';
    echo '        border: 1px solid #ddd;';
    echo '        border-radius: 4px;';
    echo '        overflow: hidden;';
    echo '    }';
    echo '    .gallery-item-preview img {';
    echo '        width: 100%;';
    echo '        height: 100%;';
    echo '        object-fit: cover;';
    echo '    }';
    echo '</style>';
    
    if ($shouldCloseDb) {
        $db->close();
    }
}
?>