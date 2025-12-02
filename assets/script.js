// 回到顶部按钮
const backToTopBtn = document.querySelector('.back-to-top');

// 保存滚动位置到cookie
function saveScrollPosition() {
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    document.cookie = `scrollPosition=${scrollPosition}; path=/; max-age=3600`;
}

// 从cookie中获取滚动位置并滚动到该位置
function restoreScrollPosition() {
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i].trim();
        if (cookie.indexOf('scrollPosition=') === 0) {
            const scrollPosition = parseInt(cookie.substring('scrollPosition='.length), 10);
            if (!isNaN(scrollPosition)) {
                window.scrollTo(0, scrollPosition);
            }
            break;
        }
    }
}

// 检查是否应该跳过加载动画
function checkSkipLoadingAnimation() {
    const skipLoadingAnimation = sessionStorage.getItem('skipLoadingAnimation');
    if (skipLoadingAnimation === 'true') {
        // 隐藏加载动画
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
        
        // 清除标记
        sessionStorage.removeItem('skipLoadingAnimation');
        return true;
    }
    return false;
}

// 页面加载完成后恢复滚动位置
window.addEventListener('load', function() {
    // 检查是否应该跳过加载动画
    if (!checkSkipLoadingAnimation()) {
        restoreScrollPosition();
    }
});

// 页面即将卸载前保存滚动位置
window.addEventListener('beforeunload', saveScrollPosition);

window.addEventListener('scroll', () => {
    if (backToTopBtn) { // 确保元素存在
        if (window.pageYOffset > 500) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    }
    
    // 滚动时保存位置（可选，用于更实时的保存）
    clearTimeout(window.scrollSaveTimer);
    window.scrollSaveTimer = setTimeout(saveScrollPosition, 100);
});

if (backToTopBtn) { // 只有当元素存在时才添加事件监听器
    backToTopBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// 平滑滚动导航
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        const targetId = this.getAttribute('href');
        if (targetId === '#') return;

        const targetSection = document.querySelector(targetId);
        if (targetSection) {
            window.scrollTo({
                top: targetSection.offsetTop - 80,
                behavior: 'smooth'
            });

            // 更新导航激活状态
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        }
    });
});

// 移动端下拉菜单功能
const mobileNavSelect = document.getElementById('mobile-nav-select');
if (mobileNavSelect) {
    mobileNavSelect.addEventListener('change', function() {
        const targetId = this.value;
        
        // 特殊处理资源中心链接，确保在当前页面内导航到资源部分
        if (targetId === 'resources.php' || targetId === '#resource') {
            const targetSection = document.querySelector('#resource');
            if (targetSection) {
                window.scrollTo({
                    top: targetSection.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
            return;
        }
        
        // 检查是否是外部链接（如其他外部页面）
        if (targetId.startsWith('http') || targetId.includes('.php')) {
            // 设置标记，表示从首页前往其他页面
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
}

// 监听滚动更新导航激活状态
window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    const currentScroll = window.pageYOffset;

    // Header滚动效果
    if (currentScroll > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }

    // 导航激活状态
    document.querySelectorAll('section').forEach(section => {
        const sectionTop = section.offsetTop - 150;
        const sectionBottom = sectionTop + section.offsetHeight;
        const id = section.getAttribute('id');

        if (currentScroll >= sectionTop && currentScroll < sectionBottom) {
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${id}`) {
                    link.classList.add('active');
                }
            });
            
            // 同步更新移动端下拉菜单的选择项
            if (mobileNavSelect) {
                mobileNavSelect.value = `#${id}`;
            }
        }
    });
});

// 服务器切换功能
document.addEventListener('DOMContentLoaded', function() {
    const serverSwitchBtn = document.getElementById('server-switch-btn');
    const serverSwitchMenu = document.getElementById('server-switch-menu');
    
    // 当前服务器状态
    let currentServer = null;
    let serverList = [];
    
    // 获取服务器列表
    fetch('/api/servers.php')
        .then(response => response.json())
        .then(data => {
            if (data.servers && data.servers.length > 0) {
                serverList = data.servers;
                
                // 检查是否有记住的服务器选择
                let selectedServer = null;
                const rememberedServerId = getCookie('rememberedServerId');
                if (rememberedServerId) {
                    selectedServer = data.servers.find(server => server.id == rememberedServerId);
                }
                
                // 如果没有记住的服务器或找不到记住的服务器，则使用默认服务器
                if (!selectedServer) {
                    selectedServer = data.servers.find(server => server.is_primary == 1) || data.servers[0];
                }
                
                currentServer = selectedServer;
                window.currentSelectedServer = {
                    address: selectedServer.server_address,
                    name: selectedServer.server_name
                };
                
                // 更新UI显示
                document.getElementById('server-name').textContent = selectedServer.server_name;
                document.getElementById('current-server-name').textContent = selectedServer.server_name;
                
                // 填充下拉菜单
                createDropdownMenu(data.servers, selectedServer);
            }
        })
        .catch(error => {
            console.error('获取服务器列表失败:', error);
        });
    
    // 切换服务器菜单显示/隐藏
    if (serverSwitchBtn) {
        serverSwitchBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            // 切换下拉菜单
            toggleDropdownMenu();
        });
    }
    
    // 点击页面其他地方隐藏菜单
    document.addEventListener('click', function(e) {
        if (serverSwitchMenu && !serverSwitchMenu.contains(e.target) && !serverSwitchBtn.contains(e.target)) {
            serverSwitchMenu.classList.remove('show');
        }
    });
    
    // 切换服务器函数
    function switchServer(server) {
        // 更新当前选中的服务器信息
        window.currentSelectedServer = {
            address: server.server_address,
            name: server.server_name
        };
        
        // 添加切换动画
        const statusCard = document.querySelector('.status-card');
        const statusInfo = document.querySelector('.status-info');
        
        // 添加淡出效果
        statusCard.style.opacity = '0';
        statusInfo.style.opacity = '0';
        statusCard.style.transition = 'opacity 0.3s ease';
        statusInfo.style.transition = 'opacity 0.3s ease';
        
        // 等待淡出完成后更新内容并淡入
        setTimeout(() => {
            // 更新服务器信息
            document.getElementById('server-name').textContent = server.server_name;
            document.getElementById('current-server-name').textContent = server.server_name;
            
            // 重新获取服务器状态
            fetchServerStatus(server.server_address, server.server_name, false);
            
            // 淡入效果
            statusCard.style.opacity = '1';
            statusInfo.style.opacity = '1';
            
            // 隐藏下拉菜单
            if (serverSwitchMenu) {
                serverSwitchMenu.classList.remove('show');
            }
        }, 300);
    }
    
    // 设置Cookie的函数
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
    }
    
    // 获取Cookie的函数
    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
    
    // 创建下拉菜单
    function createDropdownMenu(servers, selectedServer) {
        const container = document.getElementById('server-switch-menu');
        if (!container) return;
        
        // 清空容器
        container.innerHTML = '';
        
        // 创建菜单项
        servers.forEach(server => {
            const item = document.createElement('div');
            item.className = 'server-switch-item';
            if (server.id == selectedServer.id) {
                item.classList.add('active');
            }
            item.setAttribute('data-server-id', server.id);
            item.setAttribute('data-address', server.server_address);
            item.setAttribute('data-name', server.server_name);
            
            const isPrimary = server.is_primary == 1 ? '<span class="primary-tag">主要</span>' : '';
            
            item.innerHTML = `
                <div class="server-icon">
                    <i class="fas fa-server"></i>
                </div>
                <div class="server-info">
                    <div class="server-name">${server.server_name}</div>
                    <div class="server-address">${server.server_address}</div>
                </div>
                ${isPrimary}
            `;
            
            item.addEventListener('click', function() {
                const serverId = this.getAttribute('data-server-id');
                const selectedServer = servers.find(server => server.id == serverId);
                
                if (selectedServer) {
                    switchServer(selectedServer);
                    setCookie('rememberedServerId', selectedServer.id, 30);
                    
                    // 更新选中状态
                    document.querySelectorAll('.server-switch-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                }
            });
            
            container.appendChild(item);
        });
    }
    
    // 切换下拉菜单
    function toggleDropdownMenu() {
        const menu = document.getElementById('server-switch-menu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }
});

// 获取服务器状态
const fetchServerStatus = async (serverAddress, serverName, showLoading = true) => {
    try {
        // 检查是否处于维护模式
        const maintenanceResponse = await fetch('/api/maintenance.php');
        const maintenanceData = await maintenanceResponse.json();
        
        if (maintenanceData.is_active) {
            // 显示维护信息
            document.getElementById('server-motd').textContent = maintenanceData.message;
            document.getElementById('player-count').textContent = '维护中';
            document.getElementById('server-version').textContent = 'N/A';
            document.getElementById('max-players').textContent = 'N/A';
            
            // 更新进度条
            document.getElementById('player-progress').style.width = '100%';
            document.getElementById('progress-text').textContent = '100%';
            document.getElementById('progress-ratio').textContent = '维护中';
            
            // 设置服务器图标为维护图标（避免404错误）
            const motdIcon = document.getElementById('motd-icon');
            motdIcon.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PHJlY3Qgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiM0NDQ0NDQiLz48dGV4dCB4PSI1MCIgeT0iNTAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiPuWbvueJhzwvdGV4dD48L3N2Zz4=';
            
            // 隐藏玩家列表
            const playersList = document.getElementById('players-list');
            if (playersList) {
                playersList.innerHTML = '<span class="no-players">服务器正在维护中</span>';
            }
            
            // 隐藏加载动画
            const statusContainer = document.querySelector('.server-status');
            const loadingElement = statusContainer.querySelector('.status-loading');
            if (loadingElement) {
                loadingElement.classList.add('fade-out');
                setTimeout(() => {
                    if (loadingElement && loadingElement.parentNode === statusContainer) {
                        statusContainer.removeChild(loadingElement);
                    }
                }, 500);
            }
            
            // 隐藏主加载动画
            if (typeof hideLoadingOverlay === 'function' && document.querySelector('.loading-overlay')) {
                hideLoadingOverlay();
                window.hideLoadingOverlay = null;
            }
            
            return;
        }
        
        // 如果没有提供服务器地址，则使用当前选中的服务器地址
        const address = serverAddress || (window.currentSelectedServer && window.currentSelectedServer.address) || 'mcda.xin';
        const name = serverName || (window.currentSelectedServer && window.currentSelectedServer.name) || '原始大陆';
        
        // 显示加载动画（仅在首次加载时显示）
        const statusContainer = document.querySelector('.server-status');
        let loadingElement = statusContainer.querySelector('.status-loading');
        
        // 只在首次加载时显示加载动画
        if (showLoading) {
            if (loadingElement) {
                loadingElement.style.opacity = '1';
                loadingElement.classList.remove('fade-out');
            } else {
                // 创建加载动画元素（首次加载时）
                loadingElement = document.createElement('div');
                loadingElement.className = 'status-loading';
                loadingElement.innerHTML = `
                    <div class="spinner"></div>
                    <div class="loading-text">数据加载中...</div>
                `;
                statusContainer.appendChild(loadingElement);
            }
        } else {
            // 自动刷新时隐藏加载动画
            if (loadingElement) {
                loadingElement.style.opacity = '0';
            }
        }
        
        // 使用新的API获取服务器状态
        const response = await fetch(`https://motd.minebbs.com/api/status?host=${address}`);
        const data = await response.json();

        // 更新服务器信息
        document.getElementById('server-name').textContent = name;
        document.getElementById('server-motd').textContent = data.pureMotd;
        document.getElementById('player-count').textContent = `${data.players.online}`;
        document.getElementById('server-version').textContent = data.version;
        document.getElementById('max-players').textContent = data.players.max;
        
        // 计算并更新在线人数百分比进度条
        const online = data.players.online;
        const max = data.players.max;
        const percentage = max > 0 ? Math.round((online / max) * 100) : 0;
        
        document.getElementById('player-progress').style.width = `${percentage}%`;
        document.getElementById('progress-text').textContent = `${percentage}%`;
        document.getElementById('progress-ratio').textContent = `${online} / ${max}`;

        // 设置服务器图标
        const motdIcon = document.getElementById('motd-icon');
        if (data.icon && data.icon.startsWith('data:image')) {
            motdIcon.src = data.icon;
        } else {
            motdIcon.src = 'https://i.postimg.cc/bJsZfHdH/server-icon.png';
        }
        
        // 显示玩家列表 - 优化头像加载逻辑
        const playersList = document.getElementById('players-list');
        if (playersList) {
            if (data.players.sample && typeof data.players.sample === 'string') {
                // 将玩家列表字符串分割为数组
                const players = data.players.sample.split(', ').slice(0, 12); // 限制显示前12个玩家
                
                // 获取当前已显示的玩家列表
                const currentPlayers = Array.from(playersList.querySelectorAll('.player-name')).map(el => el.textContent.trim());
                
                // 找出新增的玩家
                const newPlayers = players.filter(player => !currentPlayers.includes(player));
                
                // 如果是首次加载或者有新增玩家，则更新整个列表
                if (showLoading || newPlayers.length > 0 || players.length !== currentPlayers.length) {
                    playersList.innerHTML = players.map(player => 
                        `<span class="player-name">
                            <img class="player-head" src="https://api.xingzhige.com/API/get_Minecraft_skins/?name=${encodeURIComponent(player)}" alt="${player}" onerror="this.style.display='none'">
                            ${player}
                        </span>`
                    ).join('');
                }
            } else {
                playersList.innerHTML = '<span class="no-players">当前没有玩家在线</span>';
            }
        }

        console.log('服务器状态已更新:', data);
        
        // 隐藏加载动画并执行淡出效果（仅在首次加载时）
        if (showLoading && loadingElement) {
            loadingElement.classList.add('fade-out');
            // 等待淡出动画完成后移除元素
            setTimeout(() => {
                if (loadingElement && loadingElement.parentNode === statusContainer) {
                    statusContainer.removeChild(loadingElement);
                }
            }, 500);
        }
        
        // 隐藏加载动画（仅在第一次加载时）
        if (typeof hideLoadingOverlay === 'function' && document.querySelector('.loading-overlay')) {
            hideLoadingOverlay();
            // 移除该函数以避免重复调用
            window.hideLoadingOverlay = null;
        }
    } catch (error) {
        console.error('获取服务器状态失败:', error);

        // 设置默认值
        document.getElementById('server-motd').textContent = '获取服务器状态失败，正在重试...';
        document.getElementById('player-count').textContent = '? / ?';
        
        // 隐藏加载动画并执行淡出效果（仅在首次加载时）
        if (showLoading) {
            const statusContainer = document.querySelector('.server-status');
            const loadingElement = statusContainer.querySelector('.status-loading');
            if (loadingElement) {
                loadingElement.classList.add('fade-out');
                // 等待淡出动画完成后移除元素
                setTimeout(() => {
                    if (loadingElement && loadingElement.parentNode === statusContainer) {
                        statusContainer.removeChild(loadingElement);
                    }
                }, 500);
            }
        }
        
        // 即使出错也隐藏加载动画（仅在第一次加载时）
        if (typeof hideLoadingOverlay === 'function' && document.querySelector('.loading-overlay')) {
            hideLoadingOverlay();
            // 移除该函数以避免重复调用
            window.hideLoadingOverlay = null;
        }
    }
};

// 初始化服务器状态并定时更新
document.addEventListener('DOMContentLoaded', function() {
    // 检查是否应该跳过加载动画
    if (!checkSkipLoadingAnimation()) {
        // 确保在DOM完全加载后才开始获取服务器状态（首次加载显示加载动画）
        setTimeout(() => {
            fetchServerStatus(null, null, true);
        }, 100);
        
        setInterval(() => {
            // 使用当前选中的服务器信息进行更新（自动刷新不显示加载动画）
            if (window.currentSelectedServer) {
                fetchServerStatus(window.currentSelectedServer.address, window.currentSelectedServer.name, false);
            } else {
                fetchServerStatus(null, null, false);
            }
        }, 5000); // 每5秒刷新一次
    }
});

// 更多按钮动画效果
const moreBtn = document.querySelector('.more-btn');
if (moreBtn) { // 只有当元素存在时才添加事件监听器
    moreBtn.addEventListener('mouseover', () => {
        moreBtn.style.transform = 'translateY(-5px)';
        moreBtn.style.boxShadow = '0 15px 25px rgba(255, 190, 80, 0.3)';
    });

    moreBtn.addEventListener('mouseleave', () => {
        moreBtn.style.transform = 'translateY(0)';
        moreBtn.style.boxShadow = 'none';
    });
}

// 滚动动画
document.querySelectorAll('.fade-in').forEach(element => {
    element.style.opacity = 0;
    element.style.transform = 'translateY(50px)';
});

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.fade-in').forEach(element => {
    observer.observe(element);
});

// 移动端菜单功能
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            // 检查是否已经存在菜单
            const existingMenu = document.querySelector('.mobile-menu-overlay');
            if (existingMenu) {
                // 添加关闭动画
                existingMenu.classList.remove('active');
                // 延迟移除元素以显示动画
                setTimeout(() => {
                    if (document.body.contains(existingMenu)) {
                        document.body.removeChild(existingMenu);
                    }
                }, 300);
                return;
            }
            
            // 创建移动端菜单面板
            const mobileMenu = document.createElement('div');
            mobileMenu.className = 'mobile-menu-overlay';
            mobileMenu.innerHTML = `
                <div class="mobile-menu-panel">
                    <div class="mobile-menu-header">
                        <span class="close-menu">&times;</span>
                    </div>
                    <ul class="mobile-menu-items">
                        <li><a href="#home">首页</a></li>
                        <li><a href="#features">特点</a></li>
                        <li><a href="#gallery">展览</a></li>
                        <li><a href="#team">团队</a></li>
                        <li><a href="resources.php">资源中心</a></li>
                        <li><a href="#join">加入我们</a></li>
                        <li><a href="#tutorial">教程文档</a></li>
                    </ul>
                </div>
            `;
            
            // 添加到页面中
            document.body.appendChild(mobileMenu);
            
            // 触发重排，然后添加active类以启动动画
            mobileMenu.offsetHeight; // 触发重排
            mobileMenu.classList.add('active');
            
            // 添加关闭事件
            mobileMenu.addEventListener('click', function(e) {
                if (e.target === mobileMenu || e.target.classList.contains('close-menu')) {
                    mobileMenu.classList.remove('active');
                    // 延迟移除元素以显示动画
                    setTimeout(() => {
                        if (document.body.contains(mobileMenu)) {
                            document.body.removeChild(mobileMenu);
                        }
                    }, 300);
                }
            });
            
            // 为菜单项添加点击事件
            const menuItems = mobileMenu.querySelectorAll('.mobile-menu-items a');
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // 获取目标元素
                    const targetId = this.getAttribute('href');
                    // 检查是否是外部链接（如资源中心）
                    if (targetId.startsWith('http') || targetId.includes('.php')) {
                        // 设置标记，表示从首页前往其他页面
                        sessionStorage.setItem('skipLoadingAnimation', 'true');
                        window.location.href = targetId;
                        return;
                    }
                    
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        // 平滑滚动到目标元素
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                    
                    // 关闭菜单
                    mobileMenu.classList.remove('active');
                    // 延迟移除元素以显示动画
                    setTimeout(() => {
                        if (document.body.contains(mobileMenu)) {
                            document.body.removeChild(mobileMenu);
                        }
                    }, 300);
                });
            });
        });
    }
});

// 全局变量用于存储展览图片和分页信息
let galleryImages = [];
let currentPage = 1;
const itemsPerPage = 8; // 每页显示8张图片

// 获取展览图片列表并动态加载
fetch('/api/gallery-images.php')
    .then(response => response.json())
    .then(data => {
        const galleryContainer = document.getElementById('gallery-container');
        const paginationContainer = document.getElementById('gallery-pagination');
        
        if (galleryContainer && data.images) {
            // 存储图片数据
            galleryImages = data.images;
            
            // 计算总页数
            const totalPages = Math.ceil(galleryImages.length / itemsPerPage);
            
            // 显示第一页
            showGalleryPage(1);
            
            // 创建分页控件
            createPagination(totalPages);
        }
    })
    .catch(error => {
        console.error('获取展览图片列表失败:', error);
    });

// 显示指定页的图片
function showGalleryPage(page) {
    const galleryContainer = document.getElementById('gallery-container');
    if (!galleryContainer) return;
    
    currentPage = page;
    
    // 计算当前页要显示的图片范围
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, galleryImages.length);
    
    // 生成图片HTML
    let galleryHTML = '';
    for (let i = startIndex; i < endIndex; i++) {
        const image = galleryImages[i];
        const delayClass = 'delay-' + (((i - startIndex + 3) % 4) + 1);
        galleryHTML += `
        <div class="gallery-item glass fade-in ${delayClass}">
            <img src="${image.image_url}" alt="${image.alt_text}" class="gallery-image" data-fullsize="${image.image_url}">
        </div>`;
    }
    
    // 如果不是最后一页且图片数量不足itemsPerPage，添加占位符
    if (endIndex - startIndex < itemsPerPage) {
        const placeholdersNeeded = itemsPerPage - (endIndex - startIndex);
        for (let i = 0; i < placeholdersNeeded; i++) {
            galleryHTML += `
            <div class="gallery-item placeholder">
            </div>`;
        }
    }
    
    galleryContainer.innerHTML = galleryHTML;
    
    // 添加图片点击事件监听器
    setTimeout(() => {
        attachImageClickListeners();
    }, 100);
    
    // 更新分页控件的激活状态
    updatePaginationButtons();
}

// 创建分页控件
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
    
    // 添加第一页和省略号
    if (startPage > 1) {
        paginationHTML += `<div class="page-btn" data-page="1">1</div>`;
        if (startPage > 2) {
            paginationHTML += `<div class="page-btn disabled">...</div>`;
        }
    }
    
    // 添加页码按钮
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `<div class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</div>`;
    }
    
    // 添加最后一页和省略号
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
    
    // 添加分页按钮事件监听器
    attachPaginationListeners();
}

// 更新分页控件的激活状态
function updatePaginationButtons() {
    const paginationContainer = document.getElementById('gallery-pagination');
    if (!paginationContainer) return;
    
    const totalPages = Math.ceil(galleryImages.length / itemsPerPage);
    
    // 更新页码按钮的激活状态
    const pageButtons = paginationContainer.querySelectorAll('.page-btn:not(.prev-btn):not(.next-btn):not(.disabled)');
    pageButtons.forEach(button => {
        const page = parseInt(button.getAttribute('data-page'));
        if (page === currentPage) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
    
    // 更新上一页/下一页按钮的禁用状态
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
    
    // 上一页按钮点击事件
    const prevButton = document.getElementById('prev-page');
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            if (currentPage > 1 && !this.classList.contains('disabled')) {
                showGalleryPage(currentPage - 1);
            }
        });
    }
    
    // 下一页按钮点击事件
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
