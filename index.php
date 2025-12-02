<?php
define('ROOT_PATH', __DIR__);

define('DB_PATH', ROOT_PATH . '/sql/settings.db');

define('DEFAULT_SERVER_ADDRESS', 'mcda.xin');
define('DEFAULT_SERVER_NAME', '原始大陆');

$serverAddress = DEFAULT_SERVER_ADDRESS;
$serverName = DEFAULT_SERVER_NAME;
$serverSecondaryAddress = "mymcc.xin";
$serverSecondaryName = "备用服务器";

$qrUrl = "";

try {
    $db = new SQLite3(DB_PATH);
    
    $qrResult = $db->query("SELECT qr_url FROM join_qr_settings ORDER BY id DESC LIMIT 1");
    if ($qrRow = $qrResult->fetchArray(SQLITE3_ASSOC)) {
        $qrUrl = $qrRow['qr_url'];
    }
    
    $result = $db->query("SELECT favicon_url, logo_url FROM site_settings WHERE id = 1");
    $siteSettings = $result->fetchArray(SQLITE3_ASSOC);
    $db->close();
    
    $faviconUrl = $siteSettings['favicon_url'] ?? 'https://p.qlogo.cn/gh/1046193413/1046193413/640/';
    $logoUrl = $siteSettings['logo_url'] ?? '';
} catch (Exception $e) {
    $faviconUrl = 'https://p.qlogo.cn/gh/1046193413/1046193413/640/';
    $logoUrl = '';
}

// 获取真实客户端IP地址
function getClientIP() {
    // 检查各种可能包含真实IP的HTTP头
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        // Cloudflare
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        // Nginx proxy
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // 处理逗号分隔的IP列表，第一个通常是真实IP
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // 客户端IP
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        // 默认REMOTE_ADDR
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    return $ip;
}

// 记录首页访问日志
function logHomepageVisit() {
    // 确保sql目录存在
    if (!is_dir('sql')) {
        mkdir('sql', 0777, true);
    }
    
    try {
        $db = new SQLite3('sql/settings.db');
        
        // 创建访问日志表（如果不存在）
        $db->exec("CREATE TABLE IF NOT EXISTS homepage_visits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL,
            user_agent TEXT,
            visit_time DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // 获取访客信息
        $ipAddress = getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // 设置时区为中国
        date_default_timezone_set('Asia/Shanghai');
        
        // 插入访问记录（使用PHP生成的时间戳）
        $stmt = $db->prepare("INSERT INTO homepage_visits (ip_address, user_agent, visit_time) VALUES (:ip_address, :user_agent, :visit_time)");
        $stmt->bindValue(':ip_address', $ipAddress, SQLITE3_TEXT);
        $stmt->bindValue(':user_agent', $userAgent, SQLITE3_TEXT);
        $stmt->bindValue(':visit_time', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->execute();
        
        $db->close();
    } catch (Exception $e) {
        // 静默处理错误，不影响首页显示
    }
}

// 记录访问日志
logHomepageVisit();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name='description' content='原始大陆是专为Minecraft爱好者打造的Java版服务器，提供纯净生存、多人协作、公平竞技等丰富玩法。拥有专业管理团队、精美资源包和活跃社区，支持多种游戏模式。立即加入我们的冒险世界！'>
    <meta name="msvalidate.01" content="1AA6DDA94EE94268E532CDAA869D51C3" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/">
    <meta property="og:title" content="原始大陆 - Minecraft中国版Java服务器">
    <meta property="og:description" content="原始大陆是专为Minecraft爱好者打造的Java版服务器，提供纯净生存、多人协作、公平竞技等丰富玩法。拥有专业管理团队、精美资源包和活跃社区，支持多种游戏模式。立即加入我们的冒险世界！">
    <?php if (!empty($logoUrl)): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($logoUrl); ?>">
    <?php else: ?>
    <meta property="og:image" content="https://p.qlogo.cn/gh/1046193413/1046193413/640/">
    <?php endif; ?>
    <meta property="og:image:alt" content="原始大陆服务器Logo">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/">
    <meta property="twitter:title" content="原始大陆 - Minecraft中国版Java服务器">
    <meta property="twitter:description" content="原始大陆是专为Minecraft爱好者打造的Java版服务器，提供纯净生存、多人协作、公平竞技等丰富玩法。拥有专业管理团队、精美资源包和活跃社区，支持多种游戏模式。立即加入我们的冒险世界！">
    <?php if (!empty($logoUrl)): ?>
    <meta property="twitter:image" content="<?php echo htmlspecialchars($logoUrl); ?>">
    <?php else: ?>
    <meta property="twitter:image" content="https://p.qlogo.cn/gh/1046193413/1046193413/640/">
    <?php endif; ?>
    <meta property="twitter:image:alt" content="原始大陆服务器Logo">
    
    <title>原始大陆 - Minecraft中国版Java服务器</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_3875679_4l5rcn7v98e.css">
    <link rel="stylesheet" href="assets/announcement.css">
    <?php if (!empty($faviconUrl)): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($faviconUrl); ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="assets/css/default.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/loading.css">
	<script type="text/javascript" src="assets/js/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/jquery.let_it_snow.js"></script>
    <script src="assets/js/modernizr.js"></script>
    <script src="assets/openqq.js"></script>
    <script src="assets/CRCMenu.v2.js"></script>
    <script src="assets/announcement.js"></script>
    <script src="assets/loading.js"></script>
    <style>
        .step-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3498db;
        }
        
        .tutorial-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .tutorial-box {
            padding: 50px;
            margin-bottom: 40px;
        }

        .tutorial-info {
            margin-bottom: 30px;
        }
        
        .tutorial-info h3 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .tutorial-info p {
            font-size: 18px;
            color: #ddd;
            line-height: 1.6;
        }
        
        .tutorial-button {
            display: inline-block;
            text-decoration: none;
        }
        
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        input, textarea, [contenteditable="true"] {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        body {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body>
	<canvas width="100%" height="100%" class="snow"></canvas>
	<script type="text/javascript">
	$(document).ready(function () {
		$("canvas.snow").let_it_snow({
			windPower: 7,
			speed: 3
		});
	});
</script>
<script>
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i+"?ref=bwt";
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "tqin7rkpqk");
</script>
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "tqin7rkpqk");
</script>
<audio id="background-music" loop>
    您的浏览器不支持音频元素。
</audio>
<video id="video-bg" autoplay loop muted>
    Your browser does not support the video tag.
</video>
<div id="video-overlay"></div>

<header>
    <nav>
        <p class="logo fade-in">
            <?php if (!empty($logoUrl)): ?>
            <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="原始大陆图标" style="width:70px; height:auto; border-radius: 8px;">
            <?php endif; ?>
            原始大陆
        </p>
        <ul class="nav-links desktop-nav">
            <li><a href="#home" class="active">首页</a></li>
            <li><a href="#features">特点</a></li>
            <li><a href="#gallery">展览</a></li>
            <li><a href="#team">团队</a></li>
            <li><a href="#resource">资源中心</a></li>
            <li><a href="#join">加入我们</a></li>
            <li><a href="#tutorial">教程文档</a></li>
        </ul>
        <select class="mobile-nav-select" id="mobile-nav-select">
            <option value="#home">首页</option>
            <option value="#features">特点</option>
            <option value="#gallery">展览</option>
            <option value="#team">团队</option>
            <option value="#resource">资源中心</option>
            <option value="#join">加入我们</option>
            <option value="#tutorial">教程文档</option>
        </select>
        <a href="#join" class="btn fade-in delay-1">立即加入</a>
        <div class="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>
<section class="hero" id="home">
    <div class="hero-content fade-in">
        <h1>原始大陆</h1>
        <p>中国版Java我的世界纯净生存服务器 | 多人协作、公平竞技、无限探索</p>

        <div class="server-status glass fade-in delay-1">
            <div class="status-loading">
                <div class="spinner"></div>
                <div class="loading-text">数据加载中...</div>
            </div>
            <div class="status-header">
                <div class="status-card">
                    <div class="status-text">
                        <img id="motd-icon" class="motd-icon" src="" alt="服务器图标">
                        <div class="status-title" id="server-name">原始大陆</div>
                        <div class="status-motd" id="server-motd">正在加载服务器信息...</div>
                    </div>
                </div>

            </div>
            <div class="status-info">
                <div class="status-item glass-dark">
                    <div class="status-label">在线人数</div>
                    <div class="status-value" id="player-count">0</div>
                </div>
                <div class="status-item glass-dark">
                    <div class="status-label">版本</div>
                    <div class="status-value" id="server-version">Unknown</div>
                </div>
                <div class="status-item glass-dark">
                    <div class="status-label">最大在线</div>
                    <div class="status-value" id="max-players">0</div>
                </div>
                <div class="status-item glass-dark">
                    <div class="status-label">在线进度</div>
                    <div class="status-value">
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill" id="player-progress"></div>
                            </div>
                            <div class="progress-text" id="progress-text">0%</div>
                        </div>
                        <div class="progress-ratio" id="progress-ratio">0 / 0</div>
                    </div>
                </div>
            </div>
            
            <div class="players-list-container">
                <div class="players-list-header">在线玩家</div>
                <div class="players-list" id="players-list">
                    <span class="no-players">正在加载玩家列表...</span>
                </div>
            </div>
            
            <div class="server-switch">
                <div class="server-switch-btn" id="server-switch-btn">
                    <i class="fas fa-server"></i>
                    <span id="current-server-name">原始大陆</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="server-switch-menu" id="server-switch-menu">
                </div>
            </div>
        </div>

        <div class="hero-buttons">
            <a href="#resource" class="btn fade-in delay-2">
                <i class="fas fa-download"></i>获取资源包
            </a>
            <a href="#join" class="btn fade-in delay-3">
                <i class="fas fa-users"></i>立刻加入
            </a>
        </div>
    </div>
</section>
<section id="features">
    <div class="section-header">
        <h2 class="fade-in">服务器特点</h2>
        <p class="fade-in delay-1">精心设计的游戏体验，为玩家提供多种玩法，丰富的游戏内容</p>
    </div>
    <div class="features-content" id="features-container">
    </div>
</section>

<style>
    .feature-icon .svg-icon-wrapper {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .feature-icon .svg-icon-wrapper svg {
        width: 100%;
        height: 100%;
        max-width: 40px;
        max-height: 40px;
    }
</style>
<section id="gallery">
    <div class="section-header">
        <h2 class="fade-in">精选展览</h2>
        <p class="fade-in delay-1">欣赏玩家在服务器中创造的惊人建筑和精彩瞬间</p>
    </div>
    <div class="gallery-content">
        <div class="gallery-grid" id="gallery-container">
        </div>
        <div class="pagination-container" id="gallery-pagination">
        </div>
    </div>
</section>
<section id="team">
    <div class="section-header">
        <h2 class="fade-in">管理团队</h2>
        <p class="fade-in delay-1">专业的管理团队确保服务器的流畅运行和公平游戏环境</p>
    </div>
    <div class="team-members" id="team-container">
    </div>
</section>
<section id="resource">
    <div class="section-header" id="resource-intro">
    </div>
    <div class="download-content">
        <div class="download-steps" id="resource-cards">
        </div>
        <a href="resources.php" class="download-link btn fade-in delay-3">
            <i class="fas fa-file-download"></i> 访问资源中心
        </a>
    </div>
</section>
<section id="join">
    <div class="section-header">
        <?php
        $db = new SQLite3('sql/settings.db');
        $joinResult = $db->query("SELECT title, description, join_steps, qq_group FROM join_settings ORDER BY id DESC LIMIT 1");
        $joinSettings = $joinResult->fetchArray(SQLITE3_ASSOC);
        ?>
        <h2 class="fade-in"><?php echo htmlspecialchars($joinSettings['title'] ?? '加入我们'); ?></h2>
        <p class="fade-in delay-1"><?php echo htmlspecialchars($joinSettings['description'] ?? '立即加入原始大陆，开启你的奇幻冒险之旅！'); ?></p>
    </div>
    <div class="join-content">
        <div class="join-box glass fade-in">
            <?php if (!empty($joinSettings['join_steps'])): ?>
                <div class="join-info">
                    <h3>如何加入原始大陆服务器？</h3>
                    <div style="white-space: pre-line;"><?php echo $joinSettings['join_steps']; ?></div>
                </div>
            <?php endif; ?>
            <div class="status-item glass-dark" style="max-width: 400px; margin: 0 auto;">
                <div class="status-label">QQ交流群</div>
                <div class="status-value" style="font-size: 22px; white-space: pre-line;"><?php echo htmlspecialchars($joinSettings['qq_group'] ?? '1046193413'); ?></div>
            </div>
        </div>
        <div class="qrcode fade-in delay-1">
            <?php 
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrUrl ?? '');
            ?>
            <img src="<?php echo $qrCodeUrl; ?>" alt="QQ群二维码">
            <div class="qrcode-label">扫码加入QQ交流群</div>
        </div>
    </div>
</section>
<section id="tutorial">
    <div class="section-header">
        <h2 class="fade-in">教程文档</h2>
    </div>
    <div class="tutorial-content">
        <div class="tutorial-box glass fade-in">
            <div class="tutorial-info">
                <h3>如何开始游戏？</h3>
                <p style="white-space: pre-line;">查看我们的详细教程文档，了解如何安装游戏、配置客户端以及加入服务器的完整步骤</p>
            </div>
            <a href="https://docs.example.com" target="_blank" class="btn tutorial-button fade-in delay-1">
                <i class="fas fa-book"></i> 查看教程文档
            </a>
        </div>
    </div>
</section>
<footer class="glass">
    <div class="footer-content">
        <div class="footer-logo">原始大陆</div>
        <p>中国版Java版我的世界服务器 | 探索 · 创造 · 社交</p>
        <div class="copyright">
            © 2025 原始大陆 Minecraft服务器 版权所有 | 设计开发: 原始大陆技术团队
        </div>
    </div>
</footer>




<div id="music-control" class="music-control">
    <i class="fas fa-music"></i>
</div>


<a href="#" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</a>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const skipLoadingAnimation = sessionStorage.getItem('skipLoadingAnimation');
        if (skipLoadingAnimation === 'true') {
            const loadingOverlay = document.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
            
            sessionStorage.removeItem('skipLoadingAnimation');
        }
        
        const audio = document.getElementById("background-music");
        const video = document.getElementById("video-bg");
        const musicControl = document.getElementById("music-control");
        const icon = musicControl.querySelector("i");

        const mobileNavSelect = document.getElementById('mobile-nav-select');
        mobileNavSelect.addEventListener('change', function() {
            const targetId = this.value;
            if (targetId.startsWith('http') || targetId.includes('.php')) {
                sessionStorage.setItem('skipLoadingAnimation', 'true');
                window.location.href = targetId;
                return;
            }
            
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                window.scrollTo({
                    top: targetSection.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });

        function getMusicPlayState() {
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.indexOf('musicPlayState=') === 0) {
                    return cookie.substring('musicPlayState='.length) === 'play';
                }
            }
            return false;
        }

        function saveMusicPlayState(isPlaying) {
            const expiryDate = new Date();
            expiryDate.setTime(expiryDate.getTime() + (30 * 24 * 60 * 60 * 1000));
            document.cookie = `musicPlayState=${isPlaying ? 'play' : 'pause'}; expires=${expiryDate.toUTCString()}; path=/`;
        }
        if (getMusicPlayState()) {
            icon.classList.remove("fa-play");
            icon.classList.add("fa-pause");
        } else {
            icon.classList.remove("fa-music");
            icon.classList.add("fa-play");
        }


        fetch('/api/video-url.php')
            .then(response => response.json())
            .then(data => {
                if (data.url) {
                    const source = document.createElement('source');
                    source.src = data.url;
                    source.type = 'video/mp4';
                    video.appendChild(source);
                    video.load();
                }
            })
            .catch(error => {
                console.error('获取视频背景链接失败:', error);
            });


        fetch('/api/music-url.php')
            .then(response => response.json())
            .then(data => {
                if (data.url) {
                    const source = document.createElement('source');
                    source.src = data.url;
                    source.type = 'audio/mpeg';
                    audio.appendChild(source);


                    if (getMusicPlayState()) {
                        audio.play().catch(error => {
                            console.log("自动播放失败:", error);

                            icon.classList.remove("fa-pause");
                            icon.classList.add("fa-play");
                        });
                    }
                }
            })
            .catch(error => {
                console.error('获取音乐链接失败:', error);
            });


        musicControl.addEventListener("click", function() {
            if (audio.paused) {
                audio.play().then(() => {
                    icon.classList.remove("fa-play");
                    icon.classList.add("fa-pause");

                    saveMusicPlayState(true);
                }).catch(error => {
                    console.log("播放失败:", error);
                });
            } else {
                audio.pause();
                icon.classList.remove("fa-pause");
                icon.classList.add("fa-play");

                saveMusicPlayState(false);
            }
        });


        fetch('/api/resource-url.php')
            .then(response => response.json())
            .then(data => {
                const downloadLink = document.getElementById('resource-download-link');
                if (downloadLink && data.url) {
                    downloadLink.href = data.url;
                }
            })
            .catch(error => {
                console.error('获取资源包链接失败:', error);

                fetch('/api/resource-url.php?default=true')
                    .then(response => response.json())
                    .then(data => {
                        const downloadLink = document.getElementById('resource-download-link');
                        if (downloadLink && data.url) {
                            downloadLink.href = data.url;
                        }
                    })
                    .catch(err => {
                        console.error('获取默认资源包链接失败:', err);

                        const downloadLink = document.getElementById('resource-download-link');
                        if (downloadLink) {
                            downloadLink.href = 'javascript:void(0)';
                        }
                    });
            });


        fetch('/api/server-features.php')
            .then(response => response.json())
            .then(data => {
                const featuresContainer = document.getElementById('features-container');
                if (featuresContainer && data.features) {
                    let featuresHTML = '';
                    data.features.forEach((feature, index) => {
                        const delayClass = 'delay-' + ((index % 4) + 1);
                        featuresHTML += `
                        <div class="feature-card glass fade-in ${delayClass}">
                            <div class="feature-icon">
                                ${feature.icon_code.startsWith('<svg') ?
                                    `<div class="svg-icon-wrapper">${feature.icon_code.replace('<svg', '<svg style="width:100%;height:100%"')}</div>` :
                                    (feature.icon_code.startsWith('icon-') ?
                                        `<i class="iconfont ${feature.icon_code}"></i>` :
                                        `<i class="${feature.icon_code}"></i>`)}
                            </div>
                            <h3>${feature.title}</h3>
                            <p>${feature.description}</p>
                        </div>`;
                    });
                    featuresContainer.innerHTML = featuresHTML;
                }
            })
            .catch(error => {
                console.error('获取服务器特点列表失败:', error);
            });


        let galleryImages = [];
        let currentPage = 1;
        const itemsPerPage = 8;

        fetch('/api/gallery-images.php')
            .then(response => response.json())
            .then(data => {
                const galleryContainer = document.getElementById('gallery-container');
                const paginationContainer = document.getElementById('gallery-pagination');
                
                if (galleryContainer && data.images) {

                    galleryImages = data.images;
                    
                    const totalPages = Math.ceil(galleryImages.length / itemsPerPage);
                    
                    showGalleryPage(1);
                    
                    createPagination(totalPages);
                }
            })
            .catch(error => {
                console.error('获取展览图片列表失败:', error);
            });


        function showGalleryPage(page) {
            const galleryContainer = document.getElementById('gallery-container');
            if (!galleryContainer) return;
            
            currentPage = page;
            

            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, galleryImages.length);
            

            let galleryHTML = '';
            for (let i = startIndex; i < endIndex; i++) {
                const image = galleryImages[i];
                const delayClass = 'delay-' + (((i - startIndex + 3) % 4) + 1);
                galleryHTML += `
                <div class="gallery-item glass fade-in ${delayClass}">
                    <img src="${image.image_url}" alt="${image.alt_text}" class="gallery-image" data-fullsize="${image.image_url}">
                </div>`;
            }
            

            if (endIndex - startIndex < itemsPerPage) {
                const placeholdersNeeded = itemsPerPage - (endIndex - startIndex);
                for (let i = 0; i < placeholdersNeeded; i++) {
                    galleryHTML += `
                    <div class="gallery-item placeholder">
                    </div>`;
                }
            }
            
            galleryContainer.innerHTML = galleryHTML;
            
            setTimeout(() => {
                attachImageClickListeners();
            }, 100);
            
            updatePaginationButtons();
        }

        function createPagination(totalPages) {
            const paginationContainer = document.getElementById('gallery-pagination');
            if (!paginationContainer || totalPages <= 1) return;
            
            let paginationHTML = `
                <div class="page-btn prev-btn" id="prev-page">
                    <i class="fas fa-chevron-left"></i>
                </div>
            `;
            
            // 最多显示7个页码按钮，包括省略号
            let startPage = 1;
            let endPage = totalPages;
            
            if (totalPages > 7) {
                if (currentPage <= 4) {
                    endPage = 7;
                } else if (currentPage >= totalPages - 3) {
                    startPage = totalPages - 6;
                } else {
                    startPage = currentPage - 3;
                    endPage = currentPage + 3;
                }
            }
            
            if (startPage > 1) {
                paginationHTML += `<div class="page-btn" data-page="1">1</div>`;
                if (startPage > 2) {
                    paginationHTML += `<div class="page-btn disabled">...</div>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `<div class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</div>`;
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHTML += `<div class="page-btn disabled">...</div>`;
                }
                paginationHTML += `<div class="page-btn" data-page="${totalPages}">${totalPages}</div>`;
            }
            
            paginationHTML += `
                <div class="page-btn next-btn" id="next-page">
                    <i class="fas fa-chevron-right"></i>
                </div>
            `;
            
            paginationContainer.innerHTML = paginationHTML;
            
            attachPaginationListeners();
        }

        function updatePaginationButtons() {
            const paginationContainer = document.getElementById('gallery-pagination');
            if (!paginationContainer) return;
            
            const totalPages = Math.ceil(galleryImages.length / itemsPerPage);
            
            const pageButtons = paginationContainer.querySelectorAll('.page-btn:not(.prev-btn):not(.next-btn):not(.disabled)');
            pageButtons.forEach(button => {
                const page = parseInt(button.getAttribute('data-page'));
                if (page === currentPage) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
            
            const prevButton = document.getElementById('prev-page');
            const nextButton = document.getElementById('next-page');
            
            if (prevButton) {
                if (currentPage === 1) {
                    prevButton.classList.add('disabled');
                } else {
                    prevButton.classList.remove('disabled');
                }
            }
            
            if (nextButton) {
                if (currentPage === totalPages) {
                    nextButton.classList.add('disabled');
                } else {
                    nextButton.classList.remove('disabled');
                }
            }
        }

        // 添加分页按钮事件监听器
        function attachPaginationListeners() {
            const paginationContainer = document.getElementById('gallery-pagination');
            if (!paginationContainer) return;
            
            // 页码按钮点击事件
            const pageButtons = paginationContainer.querySelectorAll('.page-btn[data-page]');
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const page = parseInt(this.getAttribute('data-page'));
                    if (page !== currentPage) {
                        showGalleryPage(page);
                    }
                });
            });
            
            const prevButton = document.getElementById('prev-page');
            if (prevButton) {
                prevButton.addEventListener('click', function() {
                    if (currentPage > 1 && !this.classList.contains('disabled')) {
                        showGalleryPage(currentPage - 1);
                    }
                });
            }
            
            const nextButton = document.getElementById('next-page');
            if (nextButton) {
                nextButton.addEventListener('click', function() {
                    const totalPages = Math.ceil(galleryImages.length / itemsPerPage);
                    if (currentPage < totalPages && !this.classList.contains('disabled')) {
                        showGalleryPage(currentPage + 1);
                    }
                });
            }
        }

        // 获取团队成员列表并动态加载
        fetch('/api/team-members.php')
            .then(response => response.json())
            .then(data => {
                const teamContainer = document.getElementById('team-container');
                if (teamContainer && data.members) {
                    let teamHTML = '';
                    data.members.forEach((member, index) => {
                        const delayClass = 'delay-' + ((index % 6) + 1);
                        teamHTML += `
                        <div class="team-card glass fade-in ${delayClass}">
                            <div class="team-avatar">
                                <img src="${member.avatar_url}" alt="${member.name}头像">
                            </div>
                            <h3>${member.name}</h3>
                            <div class="team-role">${member.role}</div>
                            <p>${member.description}</p>
                        </div>`;
                    });
                    teamContainer.innerHTML = teamHTML;
                }
            })
            .catch(error => {
                console.error('获取团队成员列表失败:', error);
            });

        fetch('/api/resource-sections.php')
            .then(response => response.json())
            .then(data => {
                const resourceIntro = document.getElementById('resource-intro');
                if (resourceIntro && data.intro) {
                    resourceIntro.innerHTML = `
                        <h2 class="fade-in">${data.intro.title}</h2>
                        <p class="fade-in delay-1">${data.intro.description}</p>`;
                }

                const resourceCards = document.getElementById('resource-cards');
                if (resourceCards) {
                    resourceCards.innerHTML = `
                        <div class="step-card glass fade-in delay-1">
                            <div class="step-num">1</div>
                            <h3>查看所有资源</h3>
                            <p>访问我们的资源中心，获取服务器所需的所有资源包</p>
                        </div>
                        <div class="step-card glass fade-in delay-2">
                            <div class="step-num">2</div>
                            <h3>选择合适资源</h3>
                            <p>根据您的需要选择合适的资源包进行下载</p>
                        </div>
                        <div class="step-card glass fade-in delay-3">
                            <div class="step-num">3</div>
                            <h3>安装使用</h3>
                            <p>按照说明安装资源包，享受更好的游戏体验</p>
                        </div>`;
                }
            })
            .catch(error => {
                console.error('获取资源下载部分失败:', error);
            });
            
        // 获取教程文档设置并动态加载
        fetch('/api/tutorial-settings.php')
            .then(response => response.json())
            .then(data => {
                // 更新教程文档标题
                const tutorialHeader = document.querySelector('#tutorial .section-header');
                if (tutorialHeader) {
                    tutorialHeader.innerHTML = `<h2 class="fade-in">${data.title}</h2>`;
                }
                
                // 更新教程文档内容
                const tutorialInfo = document.querySelector('.tutorial-info p');
                if (tutorialInfo) {
                    tutorialInfo.style.whiteSpace = 'pre-line';
                    tutorialInfo.textContent = data.content || '查看我们的详细教程文档，了解如何安装游戏、配置客户端以及加入服务器的完整步骤';
                }
                
                const tutorialButton = document.querySelector('.tutorial-button');
                if (tutorialButton) {
                    tutorialButton.innerHTML = `<i class="fas fa-book"></i> ${data.button_text}`;
                    if (data.button_url) {
                        tutorialButton.href = data.button_url;
                        tutorialButton.style.display = 'inline-block';
                    } else {
                        tutorialButton.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('获取教程文档设置失败:', error);
            });
    });

function attachImageClickListeners() {
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.id = 'imageModal';
    modal.innerHTML = `
        <span class="close-modal">&times;</span>
        <img class="image-modal-content" id="modalImage">
    `;
    document.body.appendChild(modal);
    
    // 获取模态框元素
    const modalElement = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const closeBtn = document.querySelector('.close-modal');
    
    // 为所有图片添加点击事件
    const images = document.querySelectorAll('.gallery-image');
    images.forEach(img => {
        img.addEventListener('click', function() {
            modalElement.style.display = 'block';
            modalImg.src = this.getAttribute('data-fullsize') || this.src;
            document.body.style.overflow = 'hidden'; // 防止背景滚动
        });
    });
    
    // 点击关闭按钮关闭模态框
    closeBtn.onclick = function() {
        modalElement.style.display = 'none';
        document.body.style.overflow = 'auto';
    };
    
    modalElement.onclick = function(event) {
        if (event.target === modalElement) {
            modalElement.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modalElement.style.display === 'block') {
            modalElement.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
}

// 在页面加载时更新二维码图片
document.addEventListener("DOMContentLoaded", function() {
    fetch('/api/join-qr-url.php')
        .then(response => response.json())
        .then(data => {
            if (data.url) {
                const qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" + encodeURIComponent(data.url);
                const qrImage = document.querySelector('.qrcode img');
                if (qrImage) {
                    qrImage.src = qrCodeUrl;
                }
            }
        })
        .catch(error => {
            console.error('获取二维码链接失败:', error);
        });
        
    setTimeout(() => {
        const galleryItems = document.querySelectorAll('.gallery-item');
        galleryItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.animation = 'fadeInUp 0.6s ease-out forwards';
            }, index * 100);
        });
    }, 500);
});
</script>

<script src="assets/script.js"></script>
<script src="assets/jr.js"></script>
<script src="assets/deng-animation.js"></script>
</body>
</html>