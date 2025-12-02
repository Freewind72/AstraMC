// 灯笼跟随顶栏滚动效果
document.addEventListener('DOMContentLoaded', function() {
    // 获取灯笼容器
    const dengContainer = document.querySelector('.deng-container');
    
    if (dengContainer) {
        // 监听滚动事件
        window.addEventListener('scroll', function() {
            // 获取当前滚动位置
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // 根据滚动位置调整灯笼的top值
            // 顶栏在滚动时会从top: 15px变为top: 0px，并且padding减少
            // 我们需要相应地调整灯笼的位置
            
            if (scrollTop > 0) {
                // 计算顶栏收缩的偏移量
                // 最大偏移量是15px (顶栏top值的变化) + 5px (padding的变化)
                const maxOffset = 15;
                const offset = Math.min(scrollTop, maxOffset);
                
                // 应用偏移量到灯笼容器
                dengContainer.style.transform = `translateY(${-offset}px)`;
            } else {
                // 回到顶部时重置位置
                dengContainer.style.transform = 'translateY(0)';
            }
        });
    }
});