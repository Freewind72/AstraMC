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
    <div class="features-content" id="features-container">
        <!-- 动态内容将通过JavaScript加载 -->
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
        <div class="gallery-grid" id="gallery-container">
            <!-- 动态内容将通过JavaScript加载 -->
        </div>
        <div class="gallery-more" id="gallery-more-container">
            <!-- 动态内容将通过JavaScript加载 -->
        </div>
    </div>
</section>

<!-- Team Section -->
<section id="team">
    <div class="section-header">
        <h2 class="fade-in">管理团队</h2>
        <p class="fade-in delay-1">专业的管理团队确保服务器的流畅运行和公平游戏环境</p>
    </div>
    <div class="team-members" id="team-container">
        <!-- 动态内容将通过JavaScript加载 -->
    </div>
</section>

<!-- Resource Section -->
<section id="resource">
    <div class="section-header" id="resource-intro">
        <!-- 动态内容将通过JavaScript加载 -->
    </div>
    <div class="download-content">
        <div class="download-steps" id="resource-cards">
            <!-- 动态内容将通过JavaScript加载 -->
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

        // 获取服务器特点列表并动态加载
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

        // 获取展览图片列表并动态加载
        fetch('/api/gallery-images.php')
            .then(response => response.json())
            .then(data => {
                const galleryContainer = document.getElementById('gallery-container');
                const galleryMoreContainer = document.getElementById('gallery-more-container');
                if (galleryContainer && data.images) {
                    // 计算需要显示的图片数量
                    // 桌面端最多显示两排（每排4个），移动端最多显示5个
                    const maxImages = window.innerWidth <= 768 ? 5 : 8;
                    const showAllImages = data.images.length <= maxImages;
                    
                    // 生成图片HTML
                    let galleryHTML = '';
                    const imagesToShow = showAllImages ? data.images : data.images.slice(0, maxImages);
                    imagesToShow.forEach((image, index) => {
                        const delayClass = 'delay-' + (((index + 3) % 4) + 1);
                        galleryHTML += `
                        <div class="gallery-item glass fade-in ${delayClass}">
                            <img src="${image.image_url}" alt="${image.alt_text}" class="gallery-image" data-fullsize="${image.image_url}">
                        </div>`;
                    });
                    galleryContainer.innerHTML = galleryHTML;
                    
                    // 如果图片数量超过限制，添加"展开更多"按钮
                    if (!showAllImages) {
                        galleryMoreContainer.innerHTML = `
                            <button id="toggle-gallery" class="more-btn fade-in delay-4" data-expanded="false">
                                展开更多 <i class="fas fa-angle-down"></i>
                            </button>`;
                        
                        // 添加展开更多按钮事件监听器
                        const toggleButton = document.getElementById('toggle-gallery');
                        toggleButton.addEventListener('click', function() {
                            const isExpanded = this.getAttribute('data-expanded') === 'true';
                            
                            if (isExpanded) {
                                // 收起图片
                                this.innerHTML = '展开更多 <i class="fas fa-angle-down"></i>';
                                this.setAttribute('data-expanded', 'false');
                                
                                // 移除额外的图片
                                const allItems = galleryContainer.querySelectorAll('.gallery-item');
                                for (let i = maxImages; i < allItems.length; i++) {
                                    allItems[i].style.animation = 'fadeOut 0.3s ease forwards';
                                    setTimeout(() => {
                                        if (allItems[i]) {
                                            allItems[i].remove();
                                        }
                                    }, 300);
                                }
                            } else {
                                // 展开图片
                                this.innerHTML = '收起 <i class="fas fa-angle-up"></i>';
                                this.setAttribute('data-expanded', 'true');
                                
                                // 添加额外的图片
                                const imagesToAdd = data.images.slice(maxImages);
                                imagesToAdd.forEach((image, index) => {
                                    const delayClass = 'delay-' + (((index + 3) % 4) + 1);
                                    const newItem = document.createElement('div');
                                    newItem.className = `gallery-item glass fade-in ${delayClass}`;
                                    newItem.style.animation = 'fadeIn 0.3s ease forwards';
                                    newItem.innerHTML = `
                                        <img src="${image.image_url}" alt="${image.alt_text}" class="gallery-image" data-fullsize="${image.image_url}">
                                    `;
                                    galleryContainer.appendChild(newItem);
                                });
                            }
                            
                            // 重新绑定图片点击事件
                            setTimeout(() => {
                                attachImageClickListeners();
                            }, 350);
                        });
                    } else {
                        galleryMoreContainer.innerHTML = '';
                    }
                    
                    // 添加图片点击事件监听器
                    attachImageClickListeners();
                }
            })
            .catch(error => {
                console.error('获取展览图片列表失败:', error);
            });

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

        // 获取资源下载部分并动态加载
        fetch('/api/resource-sections.php')
            .then(response => response.json())
            .then(data => {
                // 加载简介部分
                const resourceIntro = document.getElementById('resource-intro');
                if (resourceIntro && data.intro) {
                    resourceIntro.innerHTML = `
                        <h2 class="fade-in">${data.intro.title}</h2>
                        <p class="fade-in delay-1">${data.intro.description}</p>`;
                }

                // 加载卡片部分
                const resourceCards = document.getElementById('resource-cards');
                if (resourceCards && data.cards) {
                    let cardsHTML = '';
                    data.cards.forEach((card, index) => {
                        const delayClass = 'delay-' + (index + 1);
                        cardsHTML += `
                        <div class="step-card glass fade-in ${delayClass}">
                            <div class="step-num">${index + 1}</div>
                            <h3>${card.title}</h3>
                            <p>${card.description}</p>
                        </div>`;
                    });
                    resourceCards.innerHTML = cardsHTML;
                }
            })
            .catch(error => {
                console.error('获取资源下载部分失败:', error);
            });
    });

// 图片放大功能
function attachImageClickListeners() {
    // 创建模态框元素
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
        document.body.style.overflow = 'auto'; // 恢复背景滚动
    };
    
    // 点击模态框背景关闭模态框
    modalElement.onclick = function(event) {
        if (event.target === modalElement) {
            modalElement.style.display = 'none';
            document.body.style.overflow = 'auto'; // 恢复背景滚动
        }
    };
    
    // 按ESC键关闭模态框
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modalElement.style.display === 'block') {
            modalElement.style.display = 'none';
            document.body.style.overflow = 'auto'; // 恢复背景滚动
        }
    });
}

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