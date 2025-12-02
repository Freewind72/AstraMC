<?php
// 引入数据库初始化脚本
require_once 'install/init_db.php';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="msvalidate.01" content="1AA6DDA94EE94268E532CDAA869D51C3" />
    <meta name='description' content='访问原始大陆Minecraft服务器的资源中心，下载最新的游戏资源包、材质包和地图文件。提供高质量的游戏资源，提升您的游戏体验。'>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/resources.php">
    <meta property="og:title" content="资源中心 - 原始大陆">
    <meta property="og:description" content="访问原始大陆Minecraft服务器的资源中心，下载最新的游戏资源包、材质包和地图文件。提供高质量的游戏资源，提升您的游戏体验。">
    <meta property="og:image" content="https://p.qlogo.cn/gh/1046193413/1046193413/640/">
    <meta property="og:image:alt" content="原始大陆服务器资源中心">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/resources.php">
    <meta property="twitter:title" content="资源中心 - 原始大陆">
    <meta property="twitter:description" content="访问原始大陆Minecraft服务器的资源中心，下载最新的游戏资源包、材质包和地图文件。提供高质量的游戏资源，提升您的游戏体验。">
    <meta property="twitter:image" content="https://p.qlogo.cn/gh/1046193413/1046193413/640/">
    <meta property="twitter:image:alt" content="原始大陆服务器资源中心">
    
    <title>资源中心 - 原始大陆</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_3875679_4l5rcn7v98e.css">
    <?php 
    // 获取数据库连接
    $db_path = __DIR__ . '/sql/settings.db';
    $faviconUrl = '';
    $logoUrl = '';
    
    if (file_exists($db_path)) {
        try {
            $db = new SQLite3($db_path);
            // 获取favicon URL
            $faviconResult = $db->query("SELECT favicon_url FROM site_settings WHERE id = 1 LIMIT 1");
            if ($faviconResult) {
                $faviconRow = $faviconResult->fetchArray(SQLITE3_ASSOC);
                $faviconUrl = $faviconRow ? $faviconRow['favicon_url'] : '';
            }

            // 获取logo URL
            $logoResult = $db->query("SELECT logo_url FROM site_settings WHERE id = 1 LIMIT 1");
            if ($logoResult) {
                $logoRow = $logoResult->fetchArray(SQLITE3_ASSOC);
                $logoUrl = $logoRow ? $logoRow['logo_url'] : '';
            }
            
            $db->close();
        } catch (Exception $e) {
            // 出现异常时使用默认值
            $faviconUrl = '';
            $logoUrl = '';
        }
    }
    
    if (!empty($faviconUrl)): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($faviconUrl); ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="assets/css/default.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/loading.css">
    <link rel="stylesheet" href="assets/resources.css">
    <script src="assets/CRCMenu.v2.js"></script>
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/jquery.let_it_snow.js"></script>
    <script src="assets/js/modernizr.js"></script>
</head>
<body>
	<canvas width="100%" height="100%" class="snow"></canvas>
	</div>
</body>
<script type="text/javascript">
	$(document).ready(function () {
		$("canvas.snow").let_it_snow({
			windPower: 7,
			speed: 3
		});
	});

</script>
<body>
    <!-- Hidden audio element for background music -->
    <audio id="background-music" loop>
        您的浏览器不支持音频元素。
    </audio>
    <!-- Video Background -->
    <video id="video-bg" autoplay loop muted>
        Your browser does not support the video tag.
    </video>
    <div id="video-overlay"></div>

    <!-- Header Navigation -->
    <header>
        <nav>
            <p class="logo fade-in">
                <?php if (!empty($logoUrl)): ?>
                <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="原始大陆服务器Logo" style="width:70px; height:auto; border-radius: 8px;">
                <?php else: ?>
                <img src="https://p.qlogo.cn/gh/1046193413/1046193413/640/" alt="原始大陆服务器默认Logo" style="width:70px; height:auto; border-radius: 8px;"></img>
                <?php endif; ?>
                原始大陆
            </p>
            <a href="index.php#join" class="btn fade-in delay-1" id="join-button">返回首页</a>
        </nav>
    </header>

    <!-- Resources Section -->
    <section class="resources" id="resources">
        <div class="section-header">
            <h1 class="fade-in">资源中心</h1>
            <p class="fade-in delay-1">获取服务器专用资源，优化您的游戏体验</p>
        </div>
        
        <div class="resources-content">
            <div class="resources-grid" id="resources-container">
                <!-- 动态内容将通过JavaScript加载 -->
                <div class="loading-placeholder">
                    <p>正在加载资源...</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="glass">
        <div class="footer-content">
            <div class="footer-logo">原始大陆</div>
            <p>中国版Java版我的世界服务器 | 探索 · 创造 · 社交</p>
            <div class="copyright">
                © 2025 原始大陆 Minecraft服务器 版权所有 | 设计开发: 原始大陆技术团队
            </div>
        </div>
    </footer>

    <!-- Music Control Button (above back-to-top) -->
    <div id="music-control" class="music-control">
        <i class="fas fa-music"></i>
    </div>

    <!-- Back To Top Button -->
    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
    // 初始化音乐播放器
    document.addEventListener("DOMContentLoaded", function() {
        const audio = document.getElementById("background-music");
        const video = document.getElementById("video-bg");
        const musicControl = document.getElementById("music-control");
        const icon = musicControl.querySelector("i");

        // 从Cookie中获取保存的音乐播放状态
        function getMusicPlayState() {
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.indexOf('musicPlayState=') === 0) {
                    return cookie.substring('musicPlayState='.length) === 'play';
                }
            }
            return false; // 默认为暂停状态
        }

        // 保存音乐播放状态到Cookie
        function saveMusicPlayState(isPlaying) {
            // 保存30天
            const expiryDate = new Date();
            expiryDate.setTime(expiryDate.getTime() + (30 * 24 * 60 * 60 * 1000));
            document.cookie = `musicPlayState=${isPlaying ? 'play' : 'pause'}; expires=${expiryDate.toUTCString()}; path=/`;
        }

        // 页面加载时根据保存的状态设置初始图标
        if (getMusicPlayState()) {
            icon.classList.remove("fa-play");
            icon.classList.add("fa-pause");
        } else {
            icon.classList.remove("fa-music");
            icon.classList.add("fa-play");
        }

        // 获取视频背景链接并设置视频源
        fetch('/api/video-url.php')
            .then(response => response.json())
            .then(data => {
                if (data.url) {
                    const source = document.createElement('source');
                    source.src = data.url;
                    source.type = 'video/mp4';
                    video.appendChild(source);
                    video.load(); // 重新加载视频元素
                }
            })
            .catch(error => {
                console.error('获取视频背景链接失败:', error);
            });

        // 获取音乐链接并设置音频源
        fetch('/api/music-url.php')
            .then(response => response.json())
            .then(data => {
                if (data.url) {
                    const source = document.createElement('source');
                    source.src = data.url;
                    source.type = 'audio/mpeg';
                    audio.appendChild(source);

                    // 页面加载完成后，如果保存的状态是播放，则自动播放音乐
                    if (getMusicPlayState()) {
                        audio.play().catch(error => {
                            console.log("自动播放失败:", error);
                            // 如果自动播放失败，恢复图标状态
                            icon.classList.remove("fa-pause");
                            icon.classList.add("fa-play");
                        });
                    }
                }
            })
            .catch(error => {
                console.error('获取音乐链接失败:', error);
            });

        // 切换播放/暂停
        musicControl.addEventListener("click", function() {
            if (audio.paused) {
                audio.play().then(() => {
                    icon.classList.remove("fa-play");
                    icon.classList.add("fa-pause");
                    // 保存播放状态
                    saveMusicPlayState(true);
                }).catch(error => {
                    console.log("播放失败:", error);
                });
            } else {
                audio.pause();
                icon.classList.remove("fa-pause");
                icon.classList.add("fa-play");
                // 保存暂停状态
                saveMusicPlayState(false);
            }
        });
        
        // 添加返回首页时跳过加载动画的功能
        // 为返回首页按钮添加点击事件
        const joinButton = document.getElementById('join-button');
        if (joinButton) {
            joinButton.addEventListener('click', function(e) {
                // 设置会话存储标记，表示返回首页时应跳过加载动画
                sessionStorage.setItem('skipLoadingAnimation', 'true');
            });
        }
    });
    </script>

    <script src="assets/resources.js"></script>
    <!-- 移除了 assets/script.js 的加载，避免冲突 -->
</body>
</html>