// 首页加载动画控制脚本
let loadingOverlay;

document.addEventListener('DOMContentLoaded', function() {
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
    
    // 禁止页面滚动
    document.body.style.overflow = 'hidden';
});

// 定义隐藏加载动画的函数，供外部调用
function hideLoadingOverlay() {
    if (loadingOverlay) {
        // 添加一个小延迟，确保所有资源加载完毕
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
        }, 500);
    }
}