// 资源中心页面 JavaScript

// 添加返回首页时跳过加载动画的功能
document.addEventListener('DOMContentLoaded', function() {
    // 为所有返回首页的链接添加点击事件
    const homeLinks = document.querySelectorAll('a[href="index.php#home"], a[href="index.php#features"], a[href="index.php#gallery"], a[href="index.php#team"], a[href="index.php#join"]');
    homeLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // 设置会话存储标记，表示返回首页时应跳过加载动画
            sessionStorage.setItem('skipLoadingAnimation', 'true');
        });
    });
    
    // 移动端下拉菜单处理
    const mobileNavSelect = document.getElementById('mobile-nav-select');
    if (mobileNavSelect) {
        mobileNavSelect.addEventListener('change', function() {
            const targetId = this.value;
            if (targetId.startsWith('index.php')) {
                // 设置会话存储标记，表示返回首页时应跳过加载动画
                sessionStorage.setItem('skipLoadingAnimation', 'true');
            }
        });
    }
    
    // 初始化回到顶部按钮
    initBackToTop();
});

// 初始化回到顶部按钮
function initBackToTop() {
    const backToTopBtn = document.querySelector('.back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 500) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        backToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 初始化资源页面功能
    initResourcesPage();
    
    // 添加悬停效果
    addHoverEffects();
    
    // 添加滚动动画
    initScrollAnimations();
    
    // 获取资源列表
    fetchResources();
});

function initResourcesPage() {
    // 为下载按钮添加点击效果
    document.addEventListener('click', function(e) {
        if (e.target.closest('.download-btn')) {
            const button = e.target.closest('.download-btn');
            const resourceId = button.getAttribute('data-resource-id');
            // 可以在这里添加下载统计等功能
            console.log('下载资源 ID:', resourceId);
        }
    });
}

function addHoverEffects() {
    // 使用事件委托处理悬停效果
    const resourcesContainer = document.getElementById('resources-container');
    if (resourcesContainer) {
        resourcesContainer.addEventListener('mouseenter', function(e) {
            if (e.target.closest('.resource-card')) {
                const card = e.target.closest('.resource-card');
                card.style.transform = 'translateY(-10px)';
            }
        }, true);
        
        resourcesContainer.addEventListener('mouseleave', function(e) {
            if (e.target.closest('.resource-card')) {
                const card = e.target.closest('.resource-card');
                card.style.transform = 'translateY(0)';
            }
        }, true);
        
        // 为资源图标添加特殊效果
        resourcesContainer.addEventListener('mouseenter', function(e) {
            if (e.target.closest('.resource-icon')) {
                const icon = e.target.closest('.resource-icon');
                icon.style.animation = 'none';
                setTimeout(() => {
                    icon.style.animation = 'float 3s ease-in-out infinite';
                }, 10);
            }
        }, true);
    }
}

function initScrollAnimations() {
    // 滚动动画
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // 观察所有资源卡片
    const resourcesContainer = document.getElementById('resources-container');
    if (resourcesContainer) {
        // 等待资源加载完成后再观察
        setTimeout(() => {
            const cards = resourcesContainer.querySelectorAll('.resource-card');
            cards.forEach(card => {
                card.style.animationPlayState = 'paused';
                observer.observe(card);
            });
        }, 1000);
    }
}

// 获取资源列表
async function fetchResources() {
    try {
        const response = await fetch('/api/resources.php');
        const data = await response.json();
        
        if (data.resources) {
            renderResources(data.resources);
            // 获取文件大小信息
            setTimeout(getResourceFileSizes, 1500);
        }
    } catch (error) {
        console.error('获取资源列表失败:', error);
        document.getElementById('resources-container').innerHTML = '<div class="no-resources"><p>加载资源失败</p></div>';
    }
}

// 渲染资源列表
function renderResources(resources) {
    const container = document.getElementById('resources-container');
    
    if (resources.length === 0) {
        container.innerHTML = '<div class="no-resources"><p>暂无可用资源</p></div>';
        return;
    }
    
    let html = '';
    resources.forEach((resource, index) => {
        const delayClass = 'delay-' + ((index % 5) + 1);
        html += `
        <div class="resource-card glass fade-in ${delayClass}">
            <div class="resource-icon">
                ${getIconHtml(resource.icon)}
            </div>
            <h3>${escapeHtml(resource.name)}</h3>
            <p>${escapeHtml(resource.description)}</p>
            <div class="resource-size" data-resource-url="${escapeHtml(resource.url)}">
                <i class="fas fa-spinner fa-spin"></i> 正在获取文件大小...
            </div>
            <a href="${escapeHtml(resource.url)}" class="download-btn btn" target="_blank" data-resource-url="${escapeHtml(resource.url)}">
                <i class="fas fa-download"></i> 下载资源
            </a>
        </div>`;
    });
    
    container.innerHTML = html;
}

// 根据图标类型生成HTML
function getIconHtml(icon) {
    if (icon.startsWith('<svg')) {
        return `<div class="svg-icon-wrapper">${icon.replace('<svg', '<svg style="width:100%;height:100%"')}</div>`;
    } else if (icon.startsWith('icon-')) {
        return `<i class="iconfont ${icon}"></i>`;
    } else {
        return `<i class="${icon}"></i>`;
    }
}

// 转义HTML特殊字符
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// 页面加载完成后获取文件大小信息
async function getResourceFileSizes() {
    try {
        // 获取所有资源大小显示元素
        const sizeElements = document.querySelectorAll('.resource-size');
        
        // 为每个元素获取对应的文件大小
        for (let i = 0; i < sizeElements.length; i++) {
            const element = sizeElements[i];
            const resourceUrl = element.getAttribute('data-resource-url');
            
            if (resourceUrl) {
                // 检查是否为直链
                if (isDirectLink(resourceUrl)) {
                    // 获取文件大小
                    const fileSize = await getFileSize(resourceUrl);
                    
                    if (fileSize !== null) {
                        // 格式化文件大小
                        const formattedSize = formatFileSize(fileSize);
                        
                        // 更新显示内容
                        element.innerHTML = `<i class="fas fa-weight-hanging"></i> 文件大小: ${formattedSize}`;
                    } else {
                        element.innerHTML = '<i class="fas fa-exclamation-circle"></i> 无法获取文件大小';
                    }
                } else {
                    element.innerHTML = '<i class="fas fa-link"></i> 外部链接';
                }
            }
        }
    } catch (error) {
        console.error('获取资源文件大小时出错:', error);
        // 更新所有大小显示元素为错误状态
        const sizeElements = document.querySelectorAll('.resource-size');
        sizeElements.forEach(element => {
            element.innerHTML = '<i class="fas fa-exclamation-circle"></i> 获取大小失败';
        });
    }
}

// 检查链接是否为直链
function isDirectLink(url) {
    try {
        const urlObj = new URL(url);
        const hostname = urlObj.hostname;
        
        // 直链域名白名单
        const DIRECT_LINK_WHITELIST = [
            'vip.123pan.cn'
        ];
        
        // 检查白名单
        for (const domain of DIRECT_LINK_WHITELIST) {
            if (hostname.includes(domain)) {
                return true;
            }
        }
        
        // 如果不在黑白名单中，默认认为是直链
        return true;
    } catch (e) {
        console.error('Invalid URL:', url);
        return true;
    }
}

// 格式化文件大小
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    // 保留一位小数
    const size = parseFloat((bytes / Math.pow(k, i)).toFixed(1));
    
    return size + ' ' + sizes[i];
}

// 获取文件大小（通过HEAD请求）
async function getFileSize(url) {
    try {
        const response = await fetch(url, {
            method: 'HEAD',
            mode: 'cors'
        });
        
        if (!response.ok) {
            return null;
        }
        
        const contentLength = response.headers.get('Content-Length');
        if (contentLength) {
            return parseInt(contentLength, 10);
        }
        
        // 如果没有Content-Length头部，尝试GET请求获取部分内容
        const partialResponse = await fetch(url, {
            method: 'GET',
            headers: {
                'Range': 'bytes=0-0'
            }
        });
        
        if (partialResponse.status === 206) { // Partial Content
            const contentRange = partialResponse.headers.get('Content-Range');
            if (contentRange) {
                const match = contentRange.match(/bytes 0-0\/(\d+)/);
                if (match) {
                    return parseInt(match[1], 10);
                }
            }
        }
        
        return null;
    } catch (error) {
        console.error('获取文件大小失败:', error);
        return null;
    }
}