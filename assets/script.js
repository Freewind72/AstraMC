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

// 页面加载完成后恢复滚动位置
window.addEventListener('load', restoreScrollPosition);

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
    const serverSwitchItems = document.querySelectorAll('.server-switch-item');
    
    // 当前服务器状态 (1 = 主服务器, 2 = 备用服务器)
    let currentServer = 1;
    
    // 服务器信息
    const serverInfo = {
        primary: {
            address: document.querySelector('[data-server="primary"]')?.dataset.address || 'mcda.xin',
            name: document.getElementById('primary-server-name')?.textContent || '原始大陆'
        },
        secondary: {
            address: document.querySelector('[data-server="secondary"]')?.dataset.address || 'mymcc.xin',
            name: document.getElementById('secondary-server-name')?.textContent || '备用服务器'
        }
    };
    
    // 从Cookie中获取保存的服务器选择
    function getSavedServerChoice() {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.indexOf('selectedServer=') === 0) {
                return cookie.substring('selectedServer='.length);
            }
        }
        return null;
    }
    
    // 保存服务器选择到Cookie
    function saveServerChoice(serverType) {
        // 保存30天
        const expiryDate = new Date();
        expiryDate.setTime(expiryDate.getTime() + (30 * 24 * 60 * 60 * 1000));
        document.cookie = `selectedServer=${serverType}; expires=${expiryDate.toUTCString()}; path=/`;
    }
    
    // 存储当前选中的服务器信息，用于定时更新
    window.currentSelectedServer = {
        address: serverInfo.primary.address,
        name: serverInfo.primary.name
    };
    
    // 检查是否有保存的服务器选择
    const savedServerChoice = getSavedServerChoice();
    if (savedServerChoice === 'secondary') {
        // 切换到备用服务器
        currentServer = 2;
        window.currentSelectedServer = {
            address: serverInfo.secondary.address,
            name: serverInfo.secondary.name
        };
        // 更新UI显示
        document.getElementById('server-name').textContent = serverInfo.secondary.name;
    }
    
    // 切换服务器菜单显示/隐藏
    serverSwitchBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        serverSwitchMenu.classList.toggle('show');
    });
    
    // 点击页面其他地方隐藏菜单
    document.addEventListener('click', function(e) {
        if (!serverSwitchBtn.contains(e.target) && !serverSwitchMenu.contains(e.target)) {
            serverSwitchMenu.classList.remove('show');
        }
    });
    
    // 切换服务器
    serverSwitchItems.forEach(item => {
        item.addEventListener('click', function() {
            const serverType = this.getAttribute('data-server');
            
            if (serverType === 'primary' && currentServer !== 1) {
                // 切换到主服务器
                currentServer = 1;
                switchServer(serverInfo.primary);
                // 保存选择到Cookie
                saveServerChoice('primary');
            } else if (serverType === 'secondary' && currentServer !== 2) {
                // 切换到备用服务器
                currentServer = 2;
                switchServer(serverInfo.secondary);
                // 保存选择到Cookie
                saveServerChoice('secondary');
            }
            
            // 隐藏菜单
            serverSwitchMenu.classList.remove('show');
        });
    });
    
    // 切换服务器函数
    function switchServer(server) {
        // 更新当前选中的服务器信息
        window.currentSelectedServer = {
            address: server.address,
            name: server.name
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
            document.getElementById('server-name').textContent = server.name;
            
            // 重新获取服务器状态
            fetchServerStatus(server.address, server.name);
            
            // 淡入效果
            statusCard.style.opacity = '1';
            statusInfo.style.opacity = '1';
        }, 300);
    }
});

// 获取服务器状态
const fetchServerStatus = async (serverAddress, serverName) => {
    try {
        // 如果没有提供服务器地址，则使用当前选中的服务器地址
        const address = serverAddress || (window.currentSelectedServer && window.currentSelectedServer.address) || 'mcda.xin';
        const name = serverName || (window.currentSelectedServer && window.currentSelectedServer.name) || '原始大陆';
        
        // 使用动态地址获取服务器状态
        const response = await fetch(`https://list.mczfw.cn/api/${address}`);
        const data = await response.json();

        // 更新服务器信息
        document.getElementById('server-name').textContent = name;
        document.getElementById('server-motd').textContent = data.motd;
        document.getElementById('server-ping').textContent = `${data.ping}ms`;
        document.getElementById('player-count').textContent = `${data.p} / ${data.mp}`;
        document.getElementById('max-players').textContent = data.mp;
        document.getElementById('server-location').textContent = data.city;

        // 设置服务器图标
        const motdIcon = document.getElementById('motd-icon');
        if (data.logo && data.logo.startsWith('data:image')) {
            motdIcon.src = data.logo;
        } else {
            motdIcon.src = 'https://i.postimg.cc/bJsZfHdH/server-icon.png';
        }

        console.log('服务器状态已更新:', data);
        
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
    // 确保在DOM完全加载后才开始获取服务器状态
    setTimeout(() => {
        fetchServerStatus();
    }, 100);
    
    setInterval(() => {
        // 使用当前选中的服务器信息进行更新
        if (window.currentSelectedServer) {
            fetchServerStatus(window.currentSelectedServer.address, window.currentSelectedServer.name);
        } else {
            fetchServerStatus();
        }
    }, 5000); // 每5秒刷新一次
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
                        <li><a href="#resource">资源包</a></li>
                        <li><a href="#join">加入我们</a></li>
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