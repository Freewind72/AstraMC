(function() {
    // 检查是否有调试权限的函数
    function hasDebugPermission() {
        // 检查cookie中是否有debug权限
        const name = "debug_enabled=";
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length) === "true";
            }
        }
        return false;
    }

    // 设置调试权限cookie
    function setDebugPermission(enabled) {
        const d = new Date();
        // 设置cookie有效期为12小时
        d.setTime(d.getTime() + (12 * 60 * 60 * 1000));
        const expires = "expires="+ d.toUTCString();
        document.cookie = "debug_enabled=" + enabled + ";" + expires + ";path=/";
    }

    // 检查是否已经有调试权限
    let debugEnabled = hasDebugPermission();
    
    // F12按键计数器和定时器
    let f12PressCount = 0;
    let f12PressTimer = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            showAnnouncementAfterLoadingAnimation();
        }, 100);
    });
    
    function showAnnouncementAfterLoadingAnimation() {
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.target === loadingOverlay && 
                        (mutation.type === 'attributes' && mutation.target.classList.contains('hidden'))) {
                        setTimeout(showAnnouncementIfNeeded, 100);
                        observer.disconnect();
                    }
                });
            });
            
            observer.observe(loadingOverlay, {
                attributes: true,
                attributeFilter: ['class']
            });
        } else {
            showAnnouncementIfNeeded();
        }
    }
    
    function showAnnouncementIfNeeded() {
        // 获取公告
        fetch('/api/announcement.php')
            .then(response => response.json())
            .then(data => {
                if (data && data.id && !data.error) {
                    // 检查用户是否已经确认过此版本的公告
                    const announcementKey = 'announcement_' + data.id + '_v' + data.version;
                    const hasConfirmed = localStorage.getItem(announcementKey);
                    
                    if (!hasConfirmed) {
                        showAnnouncement(data);
                    }
                }
            })
            .catch(error => {
                console.error('获取公告失败:', error);
            });
    }
    
    function showAnnouncement(announcement) {
        // 创建公告弹窗
        const modal = document.createElement('div');
        modal.className = 'announcement-modal show';
        modal.innerHTML = `
            <div class="announcement-content">
                <div class="announcement-header">
                    <h1>${announcement.title}</h1>
                    <span class="announcement-close">&times;</span>
                </div>
                <div class="announcement-body">${announcement.content}</div>
                <div class="announcement-footer">
                    <button class="announcement-button" id="confirm-announcement">确定</button>
                </div>
            </div>
        `;
        
        // 添加到页面
        document.body.appendChild(modal);
        
        // 确保公告弹窗在最上层
        modal.style.zIndex = '100000';
        
        // 根据对齐方式设置内容对齐
        const body = modal.querySelector('.announcement-body');
        if (announcement.alignment === 'center') {
            body.classList.remove('left');
            body.classList.add('center');
        } else {
            body.classList.remove('center');
            body.classList.add('left');
        }
        
        // 添加关闭事件
        const closeBtn = modal.querySelector('.announcement-close');
        const confirmBtn = modal.querySelector('#confirm-announcement');
        const content = modal.querySelector('.announcement-content');
        
        function closeAnnouncement() {
            modal.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                    // 恢复背景滚动
                    document.body.style.overflow = '';
                }
            }, 300);
        }
        
        closeBtn.addEventListener('click', closeAnnouncement);
        confirmBtn.addEventListener('click', function() {
            // 保存确认状态到localStorage
            const announcementKey = 'announcement_' + announcement.id + '_v' + announcement.version;
            localStorage.setItem(announcementKey, 'confirmed');
            closeAnnouncement();
        });
        
        // 点击背景关闭
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAnnouncement();
            }
        });
        
        // ESC键关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.body.contains(modal)) {
                closeAnnouncement();
            }
        });
        
        // 阻止背景滚动
        document.body.style.overflow = 'hidden';
        
        // 防止弹窗内容滚动时影响背景
        body.addEventListener('wheel', function(e) {
            const scrollTop = body.scrollTop;
            const scrollHeight = body.scrollHeight;
            const height = body.clientHeight;
            const delta = e.deltaY;
            
            if ((delta < 0 && scrollTop === 0) || 
                (delta > 0 && scrollTop + height >= scrollHeight)) {
                e.preventDefault();
            }
        });
        
        // 防止触摸滚动穿透
        let touchStartY = 0;
        body.addEventListener('touchstart', function(e) {
            touchStartY = e.touches[0].clientY;
        });
        
        body.addEventListener('touchmove', function(e) {
            const deltaY = e.touches[0].clientY - touchStartY;
            const scrollTop = body.scrollTop;
            const scrollHeight = body.scrollHeight;
            const height = body.clientHeight;
            
            if ((deltaY > 0 && scrollTop === 0) || 
                (deltaY < 0 && scrollTop + height >= scrollHeight)) {
                e.preventDefault();
            }
        });
    }
    
    // 添加淡出动画CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .announcement-modal[style*="fadeOut"] {
            animation: fadeOut 0.3s ease-out forwards;
        }
    `;
    document.head.appendChild(style);
    
    // 禁止右键菜单
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });
    
    // 禁止文本选中
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    // 禁止拖拽
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    // 禁止F1帮助键
    document.addEventListener('keydown', function(e) {
        if (e.keyCode === 112) { // F1
            e.preventDefault();
            return false;
        }
    });
    
    // 防止Ctrl+A全选
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.keyCode === 65) { // Ctrl+A
            e.preventDefault();
            return false;
        }
    });
    
    // 防止Ctrl+S保存
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.keyCode === 83) { // Ctrl+S
            e.preventDefault();
            return false;
        }
    });
    
    // 防止Ctrl+U查看源码
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.keyCode === 85) { // Ctrl+U
            e.preventDefault();
            return false;
        }
    });
    
    // 防止Ctrl+P打印
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.keyCode === 80) { // Ctrl+P
            e.preventDefault();
            return false;
        }
    });
    
    // F12开发者工具检测和弹窗
    document.addEventListener('keydown', function(e) {
        // 检测F12键 (keyCode 123)
        if (e.keyCode === 123) {
            // 如果已经有调试权限，则不阻止F12
            if (debugEnabled) {
                return;
            }
            
            // 增加F12按键计数
            f12PressCount++;
            
            // 清除之前的定时器
            if (f12PressTimer) {
                clearTimeout(f12PressTimer);
            }
            
            // 重置计数器，500毫秒内没有再次按下F12就重置计数
            f12PressTimer = setTimeout(() => {
                f12PressCount = 0;
            }, 500);
            
            // 如果连续按了3次F12
            if (f12PressCount >= 3) {
                // 重置计数器
                f12PressCount = 0;
                
                // 启用调试模式
                debugEnabled = true;
                setDebugPermission(true);
                
                // 显示提示弹窗
                showDebugEnabledNotice();
                
                // 不阻止这次F12按键，让用户可以正常使用开发者工具
                return;
            }
            
            // 如果还没到3次，继续阻止F12
            e.preventDefault();
            showDeveloperWarning();
        }
        
        // 检测 Ctrl+Shift+I (开发者工具快捷键)
        if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
            // 如果已经有调试权限，则不阻止
            if (debugEnabled) {
                return;
            }
            
            e.preventDefault();
            showDeveloperWarning();
        }
        
        // 检测F11键 (keyCode 122)
        if (e.keyCode === 122) {
            e.preventDefault();
            showFullscreenConfirm();
        }
    });
    
    // 显示调试模式启用提示弹窗
    function showDebugEnabledNotice() {
        // 检查是否已经显示了提示弹窗
        if (document.querySelector('.debug-enabled-notice-modal')) {
            return;
        }
        
        // 关闭其他所有弹窗
        closeAllOtherModals();
        
        // 创建提示弹窗
        const modal = document.createElement('div');
        modal.className = 'announcement-modal show debug-enabled-notice-modal';
        modal.innerHTML = `
            <div class="announcement-content">
                <div class="announcement-header">
                    <h1>系统提示</h1>
                    <span class="announcement-close">&times;</span>
                </div>
                <div class="announcement-body center">
                    <p style="font-size: 18px; color: #4CAF50; margin: 20px 0;">
                        <i class="fas fa-check-circle" style="margin-right: 10px;"></i>
                        调试模式已启用
                    </p>
                    <p style="color: #ddd; margin: 10px 0;">
                        您已获得临时调试权限，12小时内有效
                    </p>
                </div>
                <div class="announcement-footer">
                    <button class="announcement-button" id="close-notice">我知道了</button>
                </div>
            </div>
        `;
        
        // 添加到页面
        document.body.appendChild(modal);
        
        // 确保弹窗永远在最上层
        modal.style.zIndex = '999999';
        
        // 添加关闭事件
        const closeBtn = modal.querySelector('.announcement-close');
        const confirmBtn = modal.querySelector('#close-notice');
        
        function closeNotice() {
            modal.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                    document.body.style.overflow = '';
                }
            }, 300);
        }
        
        closeBtn.addEventListener('click', closeNotice);
        confirmBtn.addEventListener('click', closeNotice);
        
        // 点击背景关闭
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeNotice();
            }
        });
        
        // 阻止背景滚动
        document.body.style.overflow = 'hidden';
        
        // 持续确保弹窗在最上层
        ensureHighestZIndex(modal);
    }
    
    // 检测开发者工具打开的状态
    let devtools = { open: false, orientation: null };
    const threshold = 160;
    
    const emitEvent = (state, orientation) => {
        if (devtools.open !== state) {
            devtools.open = state;
            devtools.orientation = orientation;
            // 只有在没有调试权限时才显示警告
            if (state && !debugEnabled) {
                showDeveloperWarning();
            }
        }
    };
    
    const main = ({ emitEvents = true } = {}) => {
        const widthThreshold = window.outerWidth - window.innerWidth > threshold;
        const heightThreshold = window.outerHeight - window.innerHeight > threshold;
        const orientation = widthThreshold ? 'vertical' : 'horizontal';
        
        if (
            !(heightThreshold && widthThreshold) &&
            ((window.Firebug && window.Firebug.chrome && window.Firebug.chrome.isInitialized) ||
                widthThreshold ||
                heightThreshold)
        ) {
            if (emitEvents) {
                emitEvent(true, orientation);
            }
        } else {
            if (emitEvents) {
                emitEvent(false, null);
            }
        }
    };
    
    setInterval(main, 500);
    
    function showDeveloperWarning() {
        // 检查是否已经显示了警告弹窗
        if (document.querySelector('.developer-warning-modal')) {
            return;
        }
        
        // 关闭其他所有弹窗，确保F12弹窗在最上层
        closeAllOtherModals();
        
        // 创建警告弹窗
        const modal = document.createElement('div');
        modal.className = 'announcement-modal show developer-warning-modal';
        modal.innerHTML = `
            <div class="announcement-content">
                <div class="announcement-header">
                    <h1>系统提示</h1>
                    <span class="announcement-close">&times;</span>
                </div>
                <div class="announcement-body center">
                    <p style="font-size: 18px; color: #ff6b6b; margin: 20px 0;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i>
                        你看的懂吗你就看
                    </p>
                    <p style="color: #ddd; margin: 10px 0;">
                        此操作已被记录
                    </p>
                </div>
                <div class="announcement-footer">
                    <button class="announcement-button" id="close-warning">我知道了</button>
                </div>
            </div>
        `;
        
        // 添加到页面
        document.body.appendChild(modal);
        
        // 确保弹窗永远在最上层
        modal.style.zIndex = '999999';
        
        // 添加关闭事件
        const closeBtn = modal.querySelector('.announcement-close');
        const confirmBtn = modal.querySelector('#close-warning');
        
        function closeWarning() {
            modal.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                    document.body.style.overflow = '';
                }
            }, 300);
        }
        
        closeBtn.addEventListener('click', closeWarning);
        confirmBtn.addEventListener('click', closeWarning);
        
        // 点击背景关闭
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeWarning();
            }
        });
        
        // 阻止背景滚动
        document.body.style.overflow = 'hidden';
        
        // 记录日志（可选）
        console.warn('%c开发者工具访问警告', 'color: red; font-size: 16px; font-weight: bold;');
        console.warn('%c用户尝试打开开发者工具', 'color: orange; font-size: 14px;');
        
        // 持续确保F12弹窗在最上层
        ensureHighestZIndex(modal);
    }
    
    // 关闭其他所有弹窗
    function closeAllOtherModals() {
        const modals = document.querySelectorAll('.announcement-modal:not(.developer-warning-modal):not(.debug-enabled-notice-modal)');
        modals.forEach(modal => {
            if (modal && modal.parentNode) {
                modal.style.animation = 'fadeOut 0.2s ease-out forwards';
                setTimeout(() => {
                    if (document.body.contains(modal)) {
                        document.body.removeChild(modal);
                    }
                }, 200);
            }
        });
    }
    
    // 确保指定弹窗永远在最上层
    function ensureHighestZIndex(targetModal) {
        const checkInterval = setInterval(() => {
            if (!document.body.contains(targetModal)) {
                clearInterval(checkInterval);
                return;
            }
            
            // 获取所有可能影响层级的元素
            const allModals = document.querySelectorAll('.announcement-modal, .modal, [style*="z-index"]');
            let maxZIndex = 999999;
            
            allModals.forEach(element => {
                if (element !== targetModal && element.style.zIndex) {
                    const zIndex = parseInt(element.style.zIndex);
                    if (zIndex >= maxZIndex) {
                        maxZIndex = zIndex + 1;
                    }
                }
            });
            
            targetModal.style.zIndex = maxZIndex.toString();
        }, 100);
        
        // 10秒后停止检查
        setTimeout(() => {
            clearInterval(checkInterval);
        }, 10000);
    }
    
    function showFullscreenConfirm() {
        // 检查是否已经显示了全屏确认弹窗
        if (document.querySelector('.fullscreen-confirm-modal')) {
            return;
        }
        
        // 创建全屏确认弹窗
        const modal = document.createElement('div');
        modal.className = 'announcement-modal show fullscreen-confirm-modal';
        modal.innerHTML = `
            <div class="announcement-content">
                <div class="announcement-header">
                    <h1>系统提示</h1>
                    <span class="announcement-close">&times;</span>
                </div>
                <div class="announcement-body center">
                    <p style="font-size: 18px; color: #4CAF50; margin: 20px 0;">
                        <i class="fas fa-expand" style="margin-right: 10px;"></i>
                        确定要全屏吗？
                    </p>
                </div>
                <div class="announcement-footer">
                    <button class="announcement-button" id="confirm-fullscreen" style="background: rgba(76, 175, 80, 0.8);">
                        确定
                    </button>
                </div>
            </div>
        `;
        
        // 添加到页面
        document.body.appendChild(modal);
        
        // 确保弹窗在最上层
        modal.style.zIndex = '100002';
        
        // 添加关闭事件
        const closeBtn = modal.querySelector('.announcement-close');
        const confirmBtn = modal.querySelector('#confirm-fullscreen');
        
        function closeFullscreenModal() {
            modal.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                    document.body.style.overflow = '';
                }
            }, 300);
        }
        
        function enterFullscreen() {
            // 进入全屏
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) { /* Safari */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) { /* IE11 */
                elem.msRequestFullscreen();
            }
            closeFullscreenModal();
        }
        
        closeBtn.addEventListener('click', closeFullscreenModal);
        confirmBtn.addEventListener('click', enterFullscreen);
        
        // 点击背景关闭（取消全屏）
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeFullscreenModal();
            }
        });
        
        // 阻止背景滚动
        document.body.style.overflow = 'hidden';
        
        // 记录日志
        console.info('%c用户尝试进入全屏模式', 'color: blue; font-size: 14px;');
    }
})();