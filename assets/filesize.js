/**
 * 文件大小获取和直链检测功能
 */

// 直链域名白名单（这些被认为是直链）
const DIRECT_LINK_WHITELIST = [
    'vip.123pan.cn'
];

// 非直链域名黑名单（这些被认为是非直链）
const NON_DIRECT_LINK_BLACKLIST = [
    // 所有链接都尝试获取信息，不区分是否为直链
];

/**
 * 检查链接是否为直链
 * @param {string} url - 要检查的URL
 * @returns {boolean} - 如果是直链返回true，否则返回false
 */
function isDirectLink(url) {
    try {
        const urlObj = new URL(url);
        const hostname = urlObj.hostname;
        
        // 检查白名单
        for (const domain of DIRECT_LINK_WHITELIST) {
            if (hostname.includes(domain)) {
                return true;
            }
        }
        
        // 检查黑名单
        for (const domain of NON_DIRECT_LINK_BLACKLIST) {
            if (hostname.includes(domain)) {
                return true; // 修改为true，所有链接都尝试获取信息
            }
        }
        
        // 如果不在黑白名单中，默认认为是直链
        return true;
    } catch (e) {
        console.error('Invalid URL:', url);
        return true; // 即使URL无效也返回true以尝试获取信息
    }
}

/**
 * 格式化文件大小
 * @param {number} bytes - 字节数
 * @returns {string} - 格式化后的文件大小字符串
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    // 保留一位小数
    const size = parseFloat((bytes / Math.pow(k, i)).toFixed(1));
    
    return size + ' ' + sizes[i];
}

/**
 * 获取文件大小（通过HEAD请求）
 * @param {string} url - 文件URL
 * @returns {Promise<number|null>} - 文件大小（字节）或null（如果获取失败）
 */
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

/**
 * 获取并显示资源包文件大小
 */
async function displayResourceFileSize() {
    try {
        // 直接从数据库获取资源链接而不是通过API
        const response = await fetch('/api/resource-current.php', {
            headers: {
                'X-Admin-Auth': 'admin-panel'
            }
        });
        
        if (!response.ok) {
            throw new Error('无法获取资源链接');
        }
        
        const data = await response.json();
        const resourceUrl = data.url;
        
        if (!resourceUrl) {
            console.error('未找到资源链接');
            return;
        }
        
        // 检查是否为直链
        if (!isDirectLink(resourceUrl)) {
            // 不是直链，不显示文件大小
            return;
        }
        
        // 获取文件大小
        const fileSize = await getFileSize(resourceUrl);
        
        if (fileSize !== null) {
            // 格式化文件大小
            const formattedSize = formatFileSize(fileSize);
            
            // 更新下载链接文本
            const downloadLink = document.getElementById('resource-download-link');
            if (downloadLink) {
                const originalText = downloadLink.innerHTML;
                // 避免重复添加文件大小
                if (!originalText.includes('(')) {
                    downloadLink.innerHTML = originalText + ` (${formattedSize})`;
                }
            }
        }
    } catch (error) {
        console.error('获取资源包文件大小时出错:', error);
    }
}

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    // 延迟执行以确保其他脚本已完成
    setTimeout(displayResourceFileSize, 1000);
});