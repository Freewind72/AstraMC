// 现代化后台管理面板 JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // 获取DOM元素
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // 打开侧边栏
    function openSidebar() {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // 关闭侧边栏
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // 绑定事件监听器
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', openSidebar);
    }
    
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // ESC键关闭侧边栏
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
    
    // 点击侧边栏外部关闭侧边栏（移动端优化）
    document.addEventListener('click', function(e) {
        // 检查点击是否发生在overlay上
        if (sidebarOverlay && sidebarOverlay.classList.contains('active') && e.target === sidebarOverlay) {
            closeSidebar();
        }
        
        // 在移动端，点击侧边栏外的任何地方都会关闭侧边栏
        if (window.innerWidth <= 992 && 
            sidebar.classList.contains('active') && 
            !sidebar.contains(e.target) && 
            e.target !== sidebarToggle && 
            !sidebarToggle.contains(e.target)) {
            closeSidebar();
        }
    });
    
    // 表单提交确认
    const forms = document.querySelectorAll('form[data-confirm]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm') || '确定要执行此操作吗？';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // 消息自动隐藏
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
    
    // 动态表单字段添加/删除（用于团队成员、服务器特性等）
    const addButtons = document.querySelectorAll('[data-add-field]');
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-add-field');
            const template = document.querySelector(`[data-template="${target}"]`);
            if (template) {
                const clone = template.cloneNode(true);
                clone.style.display = '';
                clone.removeAttribute('data-template');
                
                // 清空输入值
                const inputs = clone.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
                
                // 添加删除按钮
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-danger btn-sm';
                removeBtn.innerHTML = '<i class="fas fa-trash"></i> 删除';
                removeBtn.addEventListener('click', function() {
                    clone.remove();
                });
                
                const removeContainer = document.createElement('div');
                removeContainer.className = 'form-group';
                removeContainer.appendChild(removeBtn);
                clone.appendChild(removeContainer);
                
                template.parentNode.insertBefore(clone, template);
            }
        });
    });
    
    // 响应式处理：窗口大小改变时
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            // 在大屏幕上确保侧边栏可见
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// 图片预览功能
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// 确认删除操作
function confirmDelete(message = '确定要删除此项吗？此操作不可撤销。') {
    return confirm(message);
}