// 首页加载动画控制脚本
let loadingOverlay;
let loadingStartTime;

document.addEventListener('DOMContentLoaded', function() {
    // 记录加载开始时间
    loadingStartTime = Date.now();
    
    // 创建加载动画元素
    loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    
    const loadingText = document.createElement('div');
    loadingText.className = 'loading-text';
    loadingText.textContent = '正在加载中...';
    
    loadingOverlay.appendChild(spinner);
    loadingOverlay.appendChild(loadingText);
    
    // 将加载动画添加到页面
    document.body.appendChild(loadingOverlay);
    
    // 确保加载动画在最上层
    loadingOverlay.style.zIndex = '99999';
    
    // 禁止页面滚动
    document.body.style.overflow = 'hidden';
});

// 定义隐藏加载动画的函数，供外部调用
function hideLoadingOverlay() {
    if (loadingOverlay) {
        // 计算已加载时间
        const elapsedTime = Date.now() - loadingStartTime;
        // 确保至少显示1秒钟
        const minDisplayTime = 1000;
        const remainingTime = Math.max(0, minDisplayTime - elapsedTime);
        
        // 添加延迟，确保最少显示1秒
        setTimeout(function() {
            loadingOverlay.classList.add('hidden');
            
            // 恢复页面滚动
            document.body.style.overflow = '';
            
            // 完全隐藏后从DOM中移除
            setTimeout(function() {
                if (loadingOverlay.parentNode) {
                    loadingOverlay.parentNode.removeChild(loadingOverlay);
                }
            }, 500);
        }, remainingTime);
    }
}