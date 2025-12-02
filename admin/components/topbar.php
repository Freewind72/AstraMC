<?php
// 顶部导航栏组件
?>
<header class="topbar">
    <div class="topbar-container">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo">
                <i class="fas fa-cogs"></i> 管理面板
            </div>
        </div>
        <div class="topbar-right">
            <div class="user-info">
                <span>欢迎, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> 登出
                </a>
            </div>
        </div>
    </div>
</header>