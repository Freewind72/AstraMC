<?php
ob_start();
session_start();

// 检查是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    ob_end_flush();
    exit();
}

// 引入安全管理系统
require_once 'security/SecurityManager.php';

// 确保sql目录存在
if (!is_dir('../sql')) {
    mkdir('../sql', 0777, true);
}

// 连接到数据库
try {
    $db = new SQLite3('../sql/settings.db');
} catch (Exception $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 初始化安全管理器
$securityManager = new SecurityManager($db);

// 检查会话是否过期
if ($securityManager->isSessionExpired()) {
    session_unset();
    session_destroy();
    header('Location: login.php?message=' . urlencode('会话已过期，请重新登录'));
    exit();
}

// 更新最后活动时间
$_SESSION['last_activity'] = time();

// 定义页面标题
define('PAGE_TITLE', '管理面板 - 原始大陆');

// 获取各种统计数据
// 1. 公告信息
$announcement = [];
try {
    $result = $db->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $announcement = $row;
    }
} catch (Exception $e) {
    // 忽略错误
}

// 2. 资源包数量
$resourceCount = 0;
try {
    $result = $db->query("SELECT COUNT(*) as count FROM resources");
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $resourceCount = $row['count'];
    }
} catch (Exception $e) {
    // 忽略错误
}

// 3. 服务器设置状态
$serverSettings = [];
try {
    // 检查是否使用多服务器设置
    $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='servers'");
    if ($tableCheck->fetchArray(SQLITE3_ASSOC)) {
        $result = $db->query("SELECT COUNT(*) as count FROM servers");
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $serverSettings['server_count'] = $row['count'];
        }
    } else {
        // 使用旧的单服务器设置
        $result = $db->query("SELECT server_address FROM server_settings ORDER BY id DESC LIMIT 1");
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $serverSettings['server_count'] = 1;
            $serverSettings['address'] = $row['server_address'];
        }
    }
} catch (Exception $e) {
    // 忽略错误
}

// 4. 精选照片数量和待审核数量
$totalGalleryImages = 0;
$pendingImages = 0;
try {
    // 总照片数量
    $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='gallery_images'");
    if ($tableCheck->fetchArray(SQLITE3_ASSOC)) {
        $result = $db->query("SELECT COUNT(*) as count FROM gallery_images");
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $totalGalleryImages = $row['count'];
        }
    }
    
    // 待审核图片数量
    $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='uploaded_images'");
    if ($tableCheck->fetchArray(SQLITE3_ASSOC)) {
        $result = $db->query("SELECT COUNT(*) as count FROM uploaded_images WHERE status = 'pending'");
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $pendingImages = $row['count'];
        }
    }
} catch (Exception $e) {
    // 忽略错误
}

// 5. 团队成员信息
$teamMembers = [];
$totalTeamMembers = 0;
try {
    $result = $db->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $teamMembers[] = $row;
        $totalTeamMembers++;
    }
} catch (Exception $e) {
    // 忽略错误
}

function timeAgo($datetime) {
    // 设置时区为中国
    date_default_timezone_set('Asia/Shanghai');
    
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 0) {
        // 如果时间差为负数，说明是未来时间，显示为"刚刚"
        return '刚刚';
    } elseif ($diff < 60) {
        return '刚刚';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '分钟前';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '小时前';
    } else {
        return floor($diff / 86400) . '天前';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title><?php echo PAGE_TITLE; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin-styles.css">
</head>
<body>
    <?php include 'components/topbar.php'; ?>
    <?php include 'components/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-home"></i> 欢迎使用管理面板
                </h2>
                <p>请选择左侧的导航项来管理相应的内容。</p>
            </div>
            
            <div class="stats-grid">
                <!-- 公告信息卡片 -->
                <div class="stat-card full-width">
                    <div class="stat-header">
                        <h3><i class="fas fa-bullhorn"></i> 最新公告</h3>
                    </div>
                    <div class="stat-content">
                        <?php if (!empty($announcement)): ?>
                            <p class="stat-title"><?php echo htmlspecialchars($announcement['title']); ?></p>
                            <p class="stat-desc"><?php echo htmlspecialchars($announcement['content']); ?></p>
                            <div class="stat-status">
                                <span class="status <?php echo $announcement['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $announcement['is_active'] ? '已启用' : '已禁用'; ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <p class="stat-desc">暂无公告</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 最近访问记录卡片 -->
                <div class="stat-card full-width">
                    <div class="stat-header">
                        <h3><i class="fas fa-history"></i> 最近访问</h3>
                        <button id="clear-logs-btn" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> 清空记录
                        </button>
                    </div>
                    <div class="stat-content">
                        <div id="loading-message" class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i> 正在加载访问记录...
                        </div>
                        <div class="access-logs-container" id="access-logs-container" style="display: none;">
                            <!-- 访问记录将通过JavaScript动态加载 -->
                        </div>
                        <p class="stat-desc" id="no-logs-message" style="display: none;">暂无访问记录</p>
                    </div>
                </div>
                
                <!-- 资源包数量卡片 -->
                <div class="stat-card">
                    <div class="stat-header">
                        <h3><i class="fas fa-file-download"></i> 资源包</h3>
                    </div>
                    <div class="stat-content">
                        <p class="stat-number"><?php echo $resourceCount; ?></p>
                        <p class="stat-desc">个资源包</p>
                    </div>
                </div>
                
                <!-- 服务器设置状态卡片 -->
                <div class="stat-card">
                    <div class="stat-header">
                        <h3><i class="fas fa-server"></i> 服务器</h3>
                    </div>
                    <div class="stat-content">
                        <?php if (!empty($serverSettings)): ?>
                            <p class="stat-number"><?php echo $serverSettings['server_count']; ?></p>
                            <p class="stat-desc">个服务器配置</p>
                        <?php else: ?>
                            <p class="stat-desc">未配置</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 精选照片卡片 -->
                <div class="stat-card">
                    <div class="stat-header">
                        <h3><i class="fas fa-images"></i> 精选展览</h3>
                    </div>
                    <div class="stat-content">
                        <p class="stat-number"><?php echo $totalGalleryImages; ?></p>
                        <p class="stat-desc">张照片</p>
                        <div class="stat-pending">
                            <?php if ($pendingImages > 0): ?>
                                <span class="pending-badge"><?php echo $pendingImages; ?> 待审核</span>
                            <?php else: ?>
                                <span class="pending-badge pending-complete">暂无待审核</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 团队成员卡片 -->
                <div class="stat-card">
                    <div class="stat-header">
                        <h3><i class="fas fa-users"></i> 管理团队</h3>
                    </div>
                    <div class="stat-content">
                        <p class="stat-number"><?php echo $totalTeamMembers; ?></p>
                        <p class="stat-desc">名成员</p>
                        <?php if (!empty($teamMembers)): ?>
                            <div class="stat-avatars">
                                <?php foreach ($teamMembers as $member): 
                                    $avatarUrl = 'https://q1.qlogo.cn/g?b=qq&nk=' . urlencode($member['qq_number']) . '&s=100';
                                ?>
                                    <img src="<?php echo $avatarUrl; ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="avatar" title="<?php echo htmlspecialchars($member['name']); ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/admin-scripts.js"></script>
    <script>
    // 异步获取首页访问记录
    document.addEventListener('DOMContentLoaded', function() {
        // 获取访问记录
        fetch('api/homepage-visits.php')
            .then(response => response.json())
            .then(data => {
                // 隐藏加载消息
                document.getElementById('loading-message').style.display = 'none';
                
                const accessLogsContainer = document.getElementById('access-logs-container');
                const noLogsMessage = document.getElementById('no-logs-message');
                
                if (data.visits && data.visits.length > 0) {
                    let logsHTML = '';
                    data.visits.forEach(visit => {
                        const timeAgo = getTimeAgo(visit.visit_time);
                        const visitCountText = visit.visit_count > 1 ? ` (访问${visit.visit_count}次)` : '';
                        logsHTML += `
                            <div class="access-item" data-ip="${htmlspecialchars(visit.ip_address)}">
                                <div class="access-info">
                                    <span class="access-action">首页访问</span>
                                    <span class="access-time">${timeAgo}${visitCountText}</span>
                                </div>
                                <div class="access-location">
                                    <span class="location-text">查询中...</span>
                                    <span class="access-ip">${htmlspecialchars(visit.ip_address)}</span>
                                </div>
                            </div>
                        `;
                    });
                    
                    accessLogsContainer.innerHTML = logsHTML;
                    accessLogsContainer.style.display = 'block';
                    
                    // 获取IP归属地信息
                    getIPLocations();
                } else {
                    noLogsMessage.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('获取访问记录失败:', error);
                document.getElementById('loading-message').style.display = 'none';
                document.getElementById('no-logs-message').style.display = 'block';
            });
        
        // 清空记录按钮功能
        const clearLogsBtn = document.getElementById('clear-logs-btn');
        if (clearLogsBtn) {
            clearLogsBtn.addEventListener('click', function() {
                if (confirm('确定要清空所有访问记录吗？此操作不可恢复。')) {
                    // 发送请求清空记录
                    fetch('clear_homepage_visits.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'clear_visits'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 清空记录容器
                            const accessLogsContainer = document.getElementById('access-logs-container');
                            if (accessLogsContainer) {
                                accessLogsContainer.innerHTML = '<p class="stat-desc">暂无访问记录</p>';
                            }
                            alert('访问记录已清空');
                        } else {
                            alert('清空记录失败: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('清空记录时发生错误');
                    });
                }
            });
        }
    });
    
    // 获取IP归属地信息
    function getIPLocations() {
        const accessItems = document.querySelectorAll('.access-item');
        
        accessItems.forEach(item => {
            const ip = item.getAttribute('data-ip');
            const locationText = item.querySelector('.location-text');
            
            if (ip && locationText) {
                // 使用代理API接口
                fetch(`api/ip-location.php?ip=${encodeURIComponent(ip)}`)
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                throw new Error(`HTTP error! status: ${response.status}, message: ${errorData.error || 'Unknown error'}`);
                            }).catch(e => {
                                // 如果无法解析错误JSON，则抛出通用错误
                                throw new Error(`HTTP error! status: ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.ret === 200 && data.data) {
                            // 根据新接口返回的数据结构调整处理逻辑
                            // 优先使用 country + prov + city + area，如果没有则使用 country
                            let location = '';
                            if (data.data.country && data.data.prov && data.data.city) {
                                location = data.data.country + data.data.prov + data.data.city + (data.data.area || '');
                            } else {
                                location = data.data.country || '未知位置';
                            }
                            
                            // 如果所有字段都为空，则显示默认信息
                            if (!location.trim()) {
                                location = '未知位置';
                            }
                            
                            // 直接设置文本内容，让浏览器处理编码
                            locationText.textContent = location;
                        } else {
                            locationText.textContent = '未知位置';
                        }
                    })
                    .catch(error => {
                        console.error('获取IP归属地失败:', error);
                        // 显示更具体的错误信息
                        locationText.textContent = '获取失败';
                    });
            }
        });
    }
    
    // 计算时间差
    function getTimeAgo(datetime) {
        // 创建日期对象并考虑时区
        const visitDate = new Date(datetime.replace(' ', 'T'));
        const now = new Date();
        const diff = now.getTime() - visitDate.getTime();
        
        if (diff < 0) {
            // 如果时间差为负，说明是未来时间，显示为"刚刚"
            return '刚刚';
        } else if (diff < 60000) { // 小于1分钟
            return '刚刚';
        } else if (diff < 3600000) { // 小于1小时
            return Math.floor(diff / 60000) + '分钟前';
        } else if (diff < 86400000) { // 小于1天
            return Math.floor(diff / 3600000) + '小时前';
        } else {
            return Math.floor(diff / 86400000) + '天前';
        }
    }
    
    // 简单的HTML转义函数
    function htmlspecialchars(str) {
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
    </script>
</body>
</html>