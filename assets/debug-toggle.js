(function() {
    // 跟踪调试模式是否已启用
    let debugModeEnabled = false;
    
    // 检查是否存在调试模式的Cookie
    function checkDebugModeCookie() {
        const name = 'debugModeEnabled';
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length + 1, c.length) === 'true';
            }
        }
        return false;
    }
    
    // 设置调试模式Cookie
    function setDebugModeCookie(enabled) {
        const d = new Date();
        d.setTime(d.getTime() + (24 * 60 * 60 * 1000)); // 24小时
        const expires = "expires=" + d.toUTCString();
        document.cookie = "debugModeEnabled=" + enabled + ";" + expires + ";path=/";
    }
    
    // 页面加载时检查Cookie
    debugModeEnabled = checkDebugModeCookie();

    // 监听键盘事件
    document.addEventListener('keydown', function(e) {
        // 检测F9键
        if (e.keyCode === 120 && !debugModeEnabled) { // F9 key
            e.preventDefault();
            showDebugConfirmation();
        }
    });

    function showDebugConfirmation() {
        // 检查是否已经显示了调试确认弹窗
        if (document.querySelector('.debug-confirmation-modal')) {
            return;
        }

        // 创建调试确认弹窗
        const modal = document.createElement('div');
        modal.className = 'announcement-modal show debug-confirmation-modal';
        modal.innerHTML = `
            <div class="announcement-content">
                <div class="announcement-header">
                    <h1>调试模式确认</h1>
                    <span class="announcement-close">&times;</span>
                </div>
                <div class="announcement-body center">
                    <p style="font-size: 18px; color: #4ECDC4; margin: 20px 0;">
                        <i class="fas fa-question-circle" style="margin-right: 10px;"></i>
                        确定启用调试模式？
                    </p>
                    <p style="color: #ddd; margin: 10px 0;">
                        启用后将取消F12开发者工具拦截
                    </p>
                </div>
                <div class="announcement-footer">
                    <button class="announcement-button" id="cancel-debug" style="background: rgba(255, 87, 34, 0.8);">
                        取消
                    </button>
                    <button class="announcement-button" id="confirm-debug" style="background: rgba(76, 175, 80, 0.8);">
                        确认
                    </button>
                </div>
            </div>
        `;

        // 添加到页面
        document.body.appendChild(modal);

        // 确保弹窗在最上层
        modal.style.zIndex = '100001';

        // 添加关闭事件
        const closeBtn = modal.querySelector('.announcement-close');
        const cancelBtn = modal.querySelector('#cancel-debug');
        const confirmBtn = modal.querySelector('#confirm-debug');

        function closeDebugModal() {
            modal.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                    document.body.style.overflow = '';
                }
            }, 300);
        }

        // 取消按钮事件
        cancelBtn.addEventListener('click', closeDebugModal);
        
        // 确认按钮事件
        confirmBtn.addEventListener('click', function() {
            // 启用调试模式
            debugModeEnabled = true;
            
            // 设置Cookie，有效期24小时
            setDebugModeCookie(true);
            
            // 移除F12拦截
            removeF12Interception();
            
            // 关闭弹窗
            closeDebugModal();
            
            // 显示启用成功提示
            showDebugEnabledToast();
        });

        // 点击背景关闭（取消操作）
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeDebugModal();
            }
        });

        // ESC键关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.body.contains(modal)) {
                closeDebugModal();
            }
        });

        // 阻止背景滚动
        document.body.style.overflow = 'hidden';
    }

    function removeF12Interception() {
        // 移除F12按键监听
        document.removeEventListener('keydown', f12KeyDownHandler);
        
        // 移除开发者工具检测
        if (window.devToolsDetectionInterval) {
            clearInterval(window.devToolsDetectionInterval);
        }
        
        // 移除announcement.js中的F12监听器
        document.removeEventListener('keydown', f12FromAnnouncementListener);
        
        // 停止开发者工具检测循环
        if (window.f12DevToolsDetectionInterval) {
            clearInterval(window.f12DevToolsDetectionInterval);
        }
        
        // 覆盖showDeveloperWarning函数，使其不执行任何操作
        window.showDeveloperWarning = function() {};
        
        // 覆盖main函数，使其不执行任何操作
        window.main = function() {};
        
        // 清除所有已存在的keydown事件监听器，然后添加一个新的监听器
        disableF12Interception();
        
        // 确保覆盖所有可能的警告函数
        overrideWarningFunctions();
    }

    function showDebugEnabledToast() {
        // 创建启用成功提示
        const toast = document.createElement('div');
        toast.className = 'debug-toast';
        toast.textContent = '调试模式已启用';
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(76, 175, 80, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            z-index: 100002;
            font-size: 14px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        `;

        document.body.appendChild(toast);

        // 显示动画
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);

        // 3秒后自动消失
        setTimeout(() => {
            toast.style.transform = 'translateX(120%)';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // F12按键处理函数（用于移除事件监听）
    function f12KeyDownHandler(e) {
        // 检测F12键 (keyCode 123) 或 Ctrl+Shift+I (开发者工具快捷键)
        if (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && e.keyCode === 73)) {
            if (!debugModeEnabled) {
                e.preventDefault();
                // 调用原有的显示开发者警告函数
                if (typeof showDeveloperWarning === 'function') {
                    showDeveloperWarning();
                }
            }
        }
    }

    // 保存announcement.js中的F12监听器引用
    let f12FromAnnouncementListener;
    
    // 保存原有的keydown事件监听器
    const originalAddEventListener = document.addEventListener;
    
    // 包装addEventListener以捕获F12监听器
    document.addEventListener = function(type, listener, options) {
        if (type === 'keydown') {
            // 检查是否是来自announcement.js的F12监听器
            if (listener && listener.toString().includes('e.keyCode === 123')) {
                f12FromAnnouncementListener = listener;
            }
        }
        return originalAddEventListener.apply(this, arguments);
    };
    
    // 禁用F12拦截
    function disableF12Interception() {
        // 添加一个高优先级的keydown监听器来阻止其他F12监听器执行
        document.addEventListener('keydown', function(e) {
            // 如果调试模式已启用，并且是F12或Ctrl+Shift+I
            if (debugModeEnabled && (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && e.keyCode === 73))) {
                // 阻止事件冒泡，这样其他监听器就不会执行
                e.stopImmediatePropagation();
                // 不调用preventDefault，允许默认行为
                return;
            }
            
            // 同样处理F11键
            if (debugModeEnabled && e.keyCode === 122) {
                e.stopImmediatePropagation();
                return;
            }
        }, true); // 使用捕获阶段，确保优先执行
    }
    
    // 覆盖所有可能的警告函数
    function overrideWarningFunctions() {
        // 覆盖可能的警告函数
        const warningFunctions = ['showDeveloperWarning', 'showFullscreenConfirm'];
        
        warningFunctions.forEach(funcName => {
            if (window[funcName]) {
                window[funcName] = function() {
                    // 调试模式启用时不执行任何操作
                    if (debugModeEnabled) {
                        return;
                    }
                    // 否则调用原始函数（如果需要）
                };
            }
        });
        
        // 查找并覆盖可能存在于announcement.js中的其他警告函数
        Object.keys(window).forEach(key => {
            if (typeof window[key] === 'function' && key.includes('show') && key.includes('Warning')) {
                const originalFunc = window[key];
                window[key] = function() {
                    if (debugModeEnabled) {
                        return;
                    }
                    return originalFunc.apply(this, arguments);
                };
            }
        });
    }

    // 在DOMContentLoaded之后处理
    document.addEventListener('DOMContentLoaded', function() {
        // 延迟执行以确保原始脚本已加载
        setTimeout(function() {
            // 如果调试模式未启用，则添加F12拦截
            if (!debugModeEnabled) {
                document.addEventListener('keydown', f12KeyDownHandler);
            } else {
                // 如果调试模式已启用，直接移除拦截
                removeF12Interception();
            }
            
            // 设置开发者工具检测循环
            window.f12DevToolsDetectionInterval = setInterval(() => {
                // 如果调试模式已启用，则停止检测
                if (debugModeEnabled) {
                    clearInterval(window.f12DevToolsDetectionInterval);
                }
            }, 500);
            
            // 添加DOM内容加载完成后的额外保护措施
            addAdditionalProtection();
        }, 1000);
    });
    
    // 添加额外保护措施
    function addAdditionalProtection() {
        // 通过MutationObserver监控DOM变化，及时阻止警告弹窗的创建
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    // 检查是否添加了开发者警告模态框
                    if (node.nodeType === 1 && node.classList && 
                        (node.classList.contains('developer-warning-modal') || 
                         (node.querySelector && node.querySelector('.developer-warning-modal')))) {
                        // 如果调试模式已启用，移除节点
                        if (debugModeEnabled) {
                            node.remove();
                        }
                    }
                });
            });
        });
        
        // 开始观察DOM变化
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();