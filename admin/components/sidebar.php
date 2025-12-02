<?php
// 侧边栏组件
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-cogs"></i> 管理面板</h2>
        <button class="sidebar-close" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="fas fa-home"></i>
                <span>仪表盘</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'announcement.php' ? 'active' : ''; ?>">
            <a href="announcement.php">
                <i class="fas fa-bullhorn"></i>
                <span>公告管理</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'music.php' ? 'active' : ''; ?>">
            <a href="music.php">
                <i class="fas fa-music"></i>
                <span>音乐管理</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'resource.php' ? 'active' : ''; ?>">
            <a href="resource.php">
                <i class="fas fa-file-download"></i>
                <span>资源包管理</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'server.php' ? 'active' : ''; ?>">
            <a href="server.php">
                <i class="fas fa-server"></i>
                <span>服务器设置</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'video.php' ? 'active' : ''; ?>">
            <a href="video.php">
                <i class="fas fa-video"></i>
                <span>视频背景</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'join.php' ? 'active' : ''; ?>">
            <a href="join.php">
                <i class="fas fa-users"></i>
                <span>加入我们</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'gallery.php' ? 'active' : ''; ?>">
            <a href="gallery.php">
                <i class="fas fa-images"></i>
                <span>精选展览</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'team.php' ? 'active' : ''; ?>">
            <a href="team.php">
                <i class="fas fa-users"></i>
                <span>管理团队</span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php">
                <i class="fas fa-cog"></i>
                <span>系统设置</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="image_review.php">
                <i class="fas fa-image"></i>
                <span>图片审核</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="ip_ban.php">
                <i class="fas fa-ban"></i>
                <span>IP封禁</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <p>&copy; 2025 原始大陆</p>
    </div>
</nav>
<div class="sidebar-overlay" id="sidebarOverlay"></div>