<!--
/*
//                            _ooOoo_
//                           o8888888o
//                           88" . "88
//                           (| -_- |)
//                            O\ = /O
//                        ____/`---'\____
//                      .   ' \\| |// `.
//                       / \\||| : |||// \
//                     / _||||| -:- |||||- \
//                       | | \\\ - /// | |
//                     | \_| ''\---/'' | |
//                      \ .-\__ `-` ___/-. /
//                   ___`. .' /--.--\ `. . __
//                ."" '< `.___\_<|>_/___.' >'"".
//               | | : `- \`.;`\ _ /`;.`/ - ` : | |
//                 \ \ `-. \_ __\ /__ _/ .-` / /
//         ======`-.____`-.___\_____/___.-`____.-'======
//                            `=---='
//
//         .............................................
//                  佛祖保佑             永无BUG
//          佛曰:
//                  写字楼里写字间，写字间里程序员；
//                  程序人员写程序，又拿程序换酒钱。
//                  酒醒只在网上坐，酒醉还来网下眠；
//                  酒醉酒醒日复日，网上网下年复年。
//                  但愿老死电脑间，不愿鞠躬老板前；
//                  奔驰宝马贵者趣，公交自行程序员。
//                  别人笑我忒疯癫，我笑自己命太贱；
//                  不见满街漂亮妹，哪个归得程序员？                            -->
<?php
// 引入数据库初始化脚本
$dbVars = include 'install/db_init.php';

// 提取变量
$serverAddress = $dbVars['serverAddress'];
$serverName = $dbVars['serverName'];
$serverSecondaryAddress = $dbVars['serverSecondaryAddress'];
$serverSecondaryName = $dbVars['serverSecondaryName'];
$qrUrl = $dbVars['qrUrl'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name='description' content='原始大陆 - Minecraft中国版Java服务器，是一个开放性Minecraft服务器'>
    <meta name="msvalidate.01" content="1AA6DDA94EE94268E532CDAA869D51C3" />
    <title>原始大陆 - Minecraft中国版Java服务器</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_3875679_4l5rcn7v98e.css">
    <link rel="icon" href="https://p.qlogo.cn/gh/1046193413/1046193413/640/" type="image/x-icon">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/loading.css">
    <script src="assets/openqq.js"></script>
    <script src="assets/CRCMenu.v2.js"></script>
    <script src="assets/loading.js"></script>
    <script src="assets/filesize.js"></script>
    <style>
        /* 添加步骤图标样式 */
        .step-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3498db;
        }
    </style>
</head>
<!-- Clarity tracking code for https://mcysdl.top/ -->
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
            <img src="https://p.qlogo.cn/gh/1046193413/1046193413/640/" style="width:70px; height:auto; border-radius: 8px;"></img>
            原始大陆
        </p>
        <!-- 桌面端导航 -->
        <ul class="nav-links desktop-nav">
            <li><a href="#home" class="active">首页</a></li>
            <li><a href="#features">特点</a></li>
            <li><a href="#gallery">展览</a></li>
            <li><a href="#team">团队</a></li>
            <li><a href="#resource">资源包</a></li>
            <li><a href="#join">加入我们</a></li>
        </ul>
        <!-- 移动端下拉菜单 -->
        <select class="mobile-nav-select" id="mobile-nav-select">
            <option value="#home">首页</option>
            <option value="#features">特点</option>
            <option value="#gallery">展览</option>
            <option value="#team">团队</option>
            <option value="#resource">资源包</option>
            <option value="#join">加入我们</option>
        </select>
        <a href="#join" class="btn fade-in delay-1">立即加入</a>
        <!-- 移动端菜单按钮（已移除功能，仅保留样式） -->
        <div class="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-content fade-in">
        <h1>原始大陆</h1>
        <p>中国版Java我的世界纯净生存服务器 | 多人协作、公平竞技、无限探索</p>

        <div class="server-status glass fade-in delay-1">
            <div class="status-header">
                <div class="status-card">
                    <img id="motd-icon" class="motd-icon" src="" alt="服务器图标">
                    <div class="status-text">
                        <div class="status-title" id="server-name">原始大陆</div>
                        <div class="status-motd" id="server-motd">正在加载服务器信息...</div>
                        <h6>(这里的延时不代表你的实机的连接速度)</h6>
                    </div>
                </div>
                <!-- 服务器切换下拉菜单 -->
                <div class="server-switch">
                    <button id="server-switch-btn" class="server-switch-btn">
                        <i class="fas fa-server"></i>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div id="server-switch-menu" class="server-switch-menu">
                        <div class="server-switch-item" data-server="primary" data-address="<?php echo htmlspecialchars($serverAddress); ?>">
                            <i class="fas fa-server"></i>
                            <span id="primary-server-name"><?php echo htmlspecialchars($serverName); ?></span>
                        </div>
                        <div class="server-switch-item" data-server="secondary" data-address="<?php echo htmlspecialchars($serverSecondaryAddress); ?>">
                            <i class="fas fa-server"></i>
                            <span id="secondary-server-name"><?php echo htmlspecialchars($serverSecondaryName); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="status-info">
                <div class="status-item glass-dark">
                    <div class="status-label">在线人数</div>
                    <div class="status-value" id="player-count">0</div>
                </div>
                <div class="status-item glass-dark">
                    <div class="status-label">延迟</div>
                    <div class="status-value" id="server-ping">0ms</div>
                </div>
                <div class="status-item glass-dark">
                    <div class="status-label">最大在线</div>
                    <div class="status-value" id="max-players">0</div>
                </div>
                <div class="status-item glass-dark">
                    <div class="status-label">地理位置</div>
                    <div class="status-value" id="server-location">中国</div>
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

<!-- Features Section -->
<section id="features">
    <div class="section-header">
        <h2 class="fade-in">服务器特点</h2>
        <p class="fade-in delay-1">精心设计的游戏体验，为玩家提供多种玩法，丰富的游戏内容</p>
    </div>
    <div class="features-content">
        <?php
        // 获取服务器特点列表用于前端显示
        $dbVars = include 'install/db_init.php';
        $db = new SQLite3('sql/settings.db');
        $featuresResult = $db->query("SELECT * FROM server_features ORDER BY sort_order ASC, id ASC");
        $features = [];
        while ($row = $featuresResult->fetchArray(SQLITE3_ASSOC)) {
            $features[] = $row;
        }
        $db->close();
        
        // 显示特征卡片
        foreach ($features as $index => $feature):
        ?>
        <div class="feature-card glass fade-in <?php echo 'delay-' . (($index % 4) + 1); ?>">
            <div class="feature-icon">
                <?php if (preg_match('/^<svg/', $feature['icon_code'])): ?>
                    <div class="svg-icon-wrapper">
                        <?php 
                        // 为SVG图标添加固定大小的样式
                        $svgCode = $feature['icon_code'];
                        if (strpos($svgCode, 'style=') === false) {
                            $svgCode = str_replace('<svg', '<svg style="width:100%;height:100%"', $svgCode);
                        }
                        echo $svgCode;
                        ?>
                    </div>
                <?php elseif (strpos($feature['icon_code'], 'icon-') === 0): ?>
                    <i class="iconfont <?php echo htmlspecialchars($feature['icon_code']); ?>"></i>
                <?php else: ?>
                    <i class="<?php echo htmlspecialchars($feature['icon_code']); ?>"></i>
                <?php endif; ?>
            </div>
            <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
            <p><?php echo htmlspecialchars($feature['description']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
    /* 为SVG图标设置固定大小 */
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

<!-- Gallery Section -->
<section id="gallery">
    <div class="section-header">
        <h2 class="fade-in">精选展览</h2>
        <p class="fade-in delay-1">欣赏玩家在服务器中创造的惊人建筑和精彩瞬间</p>
    </div>
    <div class="gallery-content">
        <div class="gallery-grid">
            <?php
            // 获取展览图片列表用于前端显示
            $dbVars = include 'install/db_init.php';
            $db = new SQLite3('sql/settings.db');
            $imagesResult = $db->query("SELECT * FROM gallery_images ORDER BY sort_order ASC, id ASC");
            $images = [];
            while ($row = $imagesResult->fetchArray(SQLITE3_ASSOC)) {
                $images[] = $row;
            }
            $db->close();
            
            // 显示展览图片
            foreach ($images as $index => $image):
            ?>
            <div class="gallery-item glass fade-in <?php echo 'delay-' . ((($index + 3) % 4) + 1); ?>">
                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <div class="gallery-more">
            <a href="https://picui.cn/share/xvspdy5oMpGZ" target="_blank" class="more-btn fade-in delay-4">浏览更多精彩瞬间 <i class="fas fa-angle-right"></i></a>
        </div>
    </div>
</section>

<!-- Team Section -->
<section id="team">
    <div class="section-header">
        <h2 class="fade-in">管理团队</h2>
        <p class="fade-in delay-1">专业的管理团队确保服务器的流畅运行和公平游戏环境</p>
    </div>
    <div class="team-members">
        <?php
        // 获取管理团队成员列表用于前端显示
        $dbVars = include 'install/db_init.php';
        $db = new SQLite3('sql/settings.db');
        $membersResult = $db->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC");
        $members = [];
        while ($row = $membersResult->fetchArray(SQLITE3_ASSOC)) {
            $members[] = $row;
        }
        $db->close();
        
        // 显示团队成员
        foreach ($members as $index => $member):
        ?>
        <div class="team-card glass fade-in <?php echo 'delay-' . (($index % 6) + 1); ?>">
            <div class="team-avatar">
                <img src="https://imgapi.cn/qq.php?qq=<?php echo htmlspecialchars($member['qq_number']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>头像">
            </div>
            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
            <div class="team-role"><?php echo htmlspecialchars($member['role']); ?></div>
            <p><?php echo htmlspecialchars($member['description']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Resource Section -->
<section id="resource">
    <div class="section-header">
        <?php
        // 获取资源下载简介信息
        $db = new SQLite3('sql/settings.db');
        $introResult = $db->query("SELECT title, description FROM resource_sections WHERE section_type = 'intro' ORDER BY sort_order ASC, id ASC LIMIT 1");
        $intro = $introResult->fetchArray(SQLITE3_ASSOC);
        ?>
        <h2 class="fade-in"><?php echo htmlspecialchars($intro['title'] ?? '资源下载'); ?></h2>
        <p class="fade-in delay-1"><?php echo htmlspecialchars($intro['description'] ?? '获取服务器专用资源包，优化您的游戏体验'); ?></p>
    </div>
    <div class="download-content">
        <div class="download-steps">
            <?php
            // 获取资源下载卡片信息
            $cardsResult = $db->query("SELECT title, description FROM resource_sections WHERE section_type = 'card' ORDER BY sort_order ASC, id ASC");
            $delay = 0;
            while ($card = $cardsResult->fetchArray(SQLITE3_ASSOC)) {
                $delay++;
                ?>
                <div class="step-card glass fade-in delay-<?php echo $delay; ?>">
                    <div class="step-num"><?php echo $delay; ?></div>
                    <h3><?php echo htmlspecialchars($card['title']); ?></h3>
                    <p><?php echo htmlspecialchars($card['description']); ?></p>
                </div>
                <?php
            }
            $db->close();
            ?>
        </div>
        <a href="javascript:void(0);" id="resource-download-link" class="download-link btn fade-in delay-3" target="_blank">
            <i class="fas fa-file-download"></i> 下载资源包
        </a>
    </div>
</section>

<!-- Join Section -->
<section id="join">
    <div class="section-header">
        <?php
        // 获取加入我们设置
        $db = new SQLite3('sql/settings.db');
        $joinResult = $db->query("SELECT title, description, server_address, server_version, qq_group FROM join_settings ORDER BY id DESC LIMIT 1");
        $joinSettings = $joinResult->fetchArray(SQLITE3_ASSOC);
        ?>
        <h2 class="fade-in"><?php echo htmlspecialchars($joinSettings['title'] ?? '加入我们'); ?></h2>
        <p class="fade-in delay-1"><?php echo htmlspecialchars($joinSettings['description'] ?? '立即加入原始大陆，开启你的奇幻冒险之旅！'); ?></p>
    </div>
    <div class="join-content">
        <div class="join-box glass fade-in">
            <div class="join-info">
                <h3>如何加入原始大陆服务器？</h3>
                <p>打开我的世界（中国版Java版），点击"多人模式" → "添加服务器"</p>
                <p>服务器地址: <b><?php echo htmlspecialchars($joinSettings['server_address'] ?? 'mcda.xin'); ?></b></p>
                <p>游戏版本: <b><?php echo htmlspecialchars($joinSettings['server_version'] ?? '1.21.5 (向下兼容至1.21.1)'); ?></b></p>
                <p>生存服实际版本:1.21.4|单方块1.19--1.21.4|</p>
                <p>起床战争1.12.2--1.21.4|生存服1.21.1---1.21.5|<b></p>
            </div>
            <div class="status-item glass-dark" style="max-width: 400px; margin: 0 auto;">
                <div class="status-label">QQ交流群</div>
                <div class="status-value" style="font-size: 22px;"><?php echo htmlspecialchars($joinSettings['qq_group'] ?? '1046193413'); ?></div>
            </div>
        </div>
        <div class="qrcode fade-in delay-1">
            <?php 
            // 生成二维码链接变量
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrUrl);
            ?>
            <img src="<?php echo $qrCodeUrl; ?>" alt="QQ群二维码">
            <div class="qrcode-label">扫码加入QQ交流群</div>
        </div>
    </div>
</section>
<!-- Footer -->
<footer>
    <div class="footer-logo">原始大陆</div>
    <p>中国版Java版我的世界服务器 | 探索 · 创造 · 社交</p>
    <div class="copyright">
        © 2025 原始大陆 Minecraft服务器 版权所有 | 设计开发: 原始大陆技术团队
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
        
        // 移动端下拉菜单功能
        const mobileNavSelect = document.getElementById('mobile-nav-select');
        mobileNavSelect.addEventListener('change', function() {
            const targetId = this.value;
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                window.scrollTo({
                    top: targetSection.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
        
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
        
        // 获取资源包真实链接并设置下载按钮
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
                // 失败时通过API获取默认链接
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
                        // 如果还失败，则不设置链接
                        const downloadLink = document.getElementById('resource-download-link');
                        if (downloadLink) {
                            downloadLink.href = 'javascript:void(0)';
                        }
                    });
            });
    });

// 在页面加载时更新二维码图片
document.addEventListener("DOMContentLoaded", function() {
    // 获取二维码链接并更新二维码图片
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
});
</script>

<script src="assets/script.js"></script>
</body>
</html>